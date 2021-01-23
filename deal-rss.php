<?php

require_once './application-top.php';

function convertStringToFriendlyUrl($strRecord)
{
    $strRecord = str_replace('.', ' ', $strRecord);
    $strRecord = trim($strRecord);
    $strRecord = strtolower(preg_replace('/ +(?=)/', '-', $strRecord));
    $strRecord = preg_replace('/[^A-Za-z0-9_\.-]+/', '', $strRecord);
    $myStr_array = explode("-", $strRecord);
    for ($jVal = 0; $jVal < count($myStr_array); $jVal++) {
        if ($jVal < count($myStr_array) - 1) {
            $strdisplay = $strdisplay . $myStr_array[$jVal] . "-";
        } else {
            if (($jVal == count($myStr_array) - 1) && (!is_numeric($myStr_array[$jVal]) == false))
                $strdisplay = substr($strdisplay, 0, strlen($strdisplay) - 1);
            $strdisplay = $strdisplay . $myStr_array[$jVal];
        }
    }
    return $strdisplay;
}

function friendlyUrl($str)
{
    global $db;
    if (strpos($str, 'deals-images.php?deal_id=') == true) {
        return CONF_WEBROOT_URL . "deals-images/" . UrlRewriteFormat($str);
    }
    if (strpos($str, 'deal.php?deal=') == true) {
        $str1 = explode('?deal=', $str);
        $id = $str1[1];
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('d.deal_id', '=', $id);
        $srch->addCondition('d.deal_deleted', '=', 0);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        $deal_name = $row['deal_name'];
        $deal_id = $row['deal_id'];
        $deal_name = convertStringToFriendlyUrl($deal_name);
        return CONF_WEBROOT_URL . "deal/" . $deal_id . "/" . $deal_name;
    }
}

function UrlRewriteFormat($str)
{
    $paramEx = explode('?', $str);
    if (count($paramEx) > 1) {
        foreach ($paramEx as $f) {
            $p = explode('&', $f);
        }
        $sid = "";
        $counter = 1;
        for ($i = 0; $i < count($p); $i++) {
            $sid .= substr($p[$i], -(strlen(strstr($p[$i], '=')) - 1));
            if (count($p) == $counter) {
                $counter = 0;
            } else {
                $sid .= "/";
            }
            $counter++;
        }
        return $sid;
    }
}

$get = getQueryStringData();
if ($_GET['city'] >= 0) {
    $cityList = $db->query("select * from tbl_cities where city_active=1 and city_deleted=0 and city_id=" . $_GET['city']);
    $Cityrow = $db->fetch($cityList); {
        $cityname = $Cityrow['city_name'];
    }
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_city', '=', $_GET['city']);
    $srch->addCondition('deal_start_time', '<=', 'mysql_func_now()', 'AND', true);
    $srch->addCondition('deal_end_time', '>', 'mysql_func_now()', 'AND', true);
    $srch->addCondition('deal_status', '<=', 2);
    if ($_GET['deal'] > 0) {
        $srch->addCondition('deal_id', '=', $_GET['deal']);
    }
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->addGroupBy('d.deal_id');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty ELSE 0 END) AS sold");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_gift_qty ELSE 0 END) AS giftSold");
    $srch->addHaving('mysql_func_sold', '<=', 'mysql_func_(deal_max_coupons)', 'AND', true);
    /* Consider user preferences ends */
    $srch->addOrder('RAND()');
    $srch->addMultipleFields(array('d.deal_id', 'deal_max_coupons', 'deal_min_buy'));
    $rs_deal_list = $srch->getResultSet();
    $countRecords = $srch->recordCount();
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
      <channel>
        <title><![CDATA[' . CONF_SITE_NAME . ']]></title>
            <atom:link href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'deal-rss.php" rel="self" type="application/rss+xml" />
        <link>http://www.' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/</link>
        <description><![CDATA[' . CONF_SITE_NAME . ']]></description>
        <lastBuildDate>' . date("r") . '</lastBuildDate>
       ';
    while ($row = $db->fetch($rs_deal_list)) {
        $deal = $row['deal_id'];
        $objDeal = new DealInfo($deal);
        $dealUrl = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'deal.php?deal=' . $objDeal->getFldValue('deal_id');
        $arr = explode(" ", $objDeal->getFldValue('deal_start_time'));
        $d = explode("-", $arr['0']);
        $t = explode(":", $arr['1']);
        $xml .= '<item>
          <title><![CDATA[' . $objDeal->getFldValue('deal_name') . ']]></title>
          <link><![CDATA[' . $dealUrl . ']]></link>
          <description><![CDATA[<table><tr><td>' . $objDeal->getFldValue('deal_desc') . '</td></tr><tr><td><a  href="' . $dealUrl . '"><img width="232" border="0" height="209" src="http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'deal-image.php?id=' . $objDeal->getFldValue('deal_id') . '&type=list' . '"  alt="' . $objDeal->getFldValue('deal_name') . '"></a></td></tr></table><br>Deal ID: ' . $deal . '<br>Regular Price: ' . CONF_CURRENCY . $objDeal->getFldValue('price') . CONF_CURRENCY_RIGHT . '<br>Actual Price: ' . CONF_CURRENCY . $objDeal->getFldValue('deal_original_price') . CONF_CURRENCY_RIGHT . '<br>Discount: ' . (($objDeal->getFldValue('deal_discount_is_percent') == 1) ? '' : CONF_CURRENCY ) . $objDeal->getFldValue('deal_discount') . (($objDeal->getFldValue('deal_discount_is_percent') == 1) ? '%' : '' ) . '<br>Your Savings: ' . CONF_CURRENCY . number_format($objDeal->getFldValue('deal_original_price') - ($objDeal->getFldValue('price')), 2) . CONF_CURRENCY_RIGHT . '<br>City: ' . $cityname . ' <br>Deal End Time : ' . $objDeal->getFldValue('deal_end_time') . ']]></description>
          <dealid><![CDATA[' . $deal . ']]></dealid>
          <dealdescription><![CDATA[' . $objDeal->getFldValue('deal_desc') . ']]></dealdescription>
          <dealimage><![CDATA[<a  href="' . $dealUrl . '"><img width="232" border="0" height="209" src="http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'deal-image.php?id=' . $objDeal->getFldValue('deal_id') . '&type=list' . '"  alt="' . $objDeal->getFldValue('deal_name') . '"></a>]]></dealimage>
          <dealprice><![CDATA[' . $objDeal->getFldValue('price') . ']]></dealprice>
          <dealactualprice><![CDATA[' . CONF_CURRENCY . number_format($objDeal->getFldValue('price'), 2) . CONF_CURRENCY_RIGHT . ']]></dealactualprice>
          <dealdiscount><![CDATA[' . (($objDeal->getFldValue('deal_discount_is_percent') == 1) ? '' : CONF_CURRENCY ) . $objDeal->getFldValue('deal_discount') . (($objDeal->getFldValue('deal_discount_is_percent') == 1) ? '%' : '' ) . ']]></dealdiscount>
          <yoursaving><![CDATA[' . CONF_CURRENCY . number_format($objDeal->getFldValue('deal_original_price') - ($objDeal->getFldValue('price')), 2) . CONF_CURRENCY_RIGHT . ']]></yoursaving>
          <couponsold><![CDATA[' . $objDeal->getFldValue('sold') . ']]></couponsold>
              <dealcity><![CDATA[' . $cityname . ']]></dealcity>
          <dealendtime><![CDATA[' . $objDeal->getFldValue('deal_end_time') . ']]></dealendtime>
          <pubDate>' . date("r", mktime($t[0], $t[1], 0, $d[1], $d[2], $d[0])) . '</pubDate>
                    <guid>' . $dealUrl . '</guid>
        </item>';
    }
    $xml .= '</channel>
    </rss> ';
    header('Content-Type: application/rss+xml');
    echo trim($xml);
}
