<?php

function getDealCategories($data = [])
{
    global $db;
    $cat = new SearchBase('tbl_deal_categories', 'cat');
    $cat_active = 1; /* default show active categories */
    $pageSize = 1; /* deafult pageSize = 1 */
    $limit_records = false;
    if (isset($data) && count($data)) {
        foreach ($data as $key => $val) {
            if ($val == '') {
                continue;
            }
            switch ($key) {
                case 'cat_active':
                case 'active':
                    $cat_active = (int) $val;
                    break;
                case 'cat_highlighted':
                case 'highlighted':
                    $cat_highlighted = (int) $val;
                    $cat->addCondition('cat.cat_highlighted', '=', $cat_highlighted);
                    break;
                case 'page_size':
                case 'pageSize':
                    $pageSize = (int) $val;
                    break;
                case 'cat_is_featured':
                case 'featured':
                    $val = (int) $val;
                    $cat->addCondition('cat.cat_is_featured', '=', $val);
                    break;
                case 'limit_records':
                    $limit_records = $val;
                    break;
            }
        }
    }
    $cat->addCondition('cat.cat_active', '=', $cat_active);
    $cat->addMultipleFields(array('cat_id, cat_name' . $_SESSION['lang_fld_prefix'] . ' as cat_name, cat_image' . $_SESSION['lang_fld_prefix'] . ' as cat_image, cat.cat_highlighted'));
    if ($limit_records) {
        $cat->setPageSize($pageSize);
    }
    if (isset($data['sort_by']) && $data['sort_by'] != '') {
        $cat->addOrder($data['sort_by']);
    } else {
        $cat->addOrder('cat.cat_display_order');
    }
    $cat_rs = $cat->getResultSet();
    if ($db->total_records($cat_rs)) {
        $data = [];
        while ($row = $db->fetch($cat_rs)) {
            $data[] = $row;
        }
        return $data;
    } else {
        return false;
    }
}

function getTotalDeals($data = [])
{
    global $db;
    $srch = new SearchBase('tbl_deal_to_category', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id = d.deal_id', 'd');
    $srch->addCondition('dtc.dc_cat_id', '=', $data['cat_id']);
    /* $srch->addCondition('d.deal_city', '=', $_SESSION['city'] ,'OR');
      $srch->addCondition('d.deal_city', '=',0 ); */
    $srch->addCondition('d.deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('d.deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('d.deal_status', '<', 2);
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_complete', '=', 1);
    $srch->addMultipleFields(array('count(d.deal_id) as total'));
    $srch->addOrder('deal_id', 'desc');
    $deal_rs = $srch->getResultSet();
    /* echo $srch->getQuery();
      die(); */
    $data = $db->fetch($deal_rs);
    return $data['total'];
}

function getRecentViewdDeals()
{
    global $db;
    $out = '';
    $deals = isset($_COOKIE['viewed_deals']) ? $_COOKIE['viewed_deals'] : false;
    if ($deals != false) {
        $deals_arr = explode("d", $deals);
        $srch = new SearchBase('tbl_deal_to_category', 'dtc');
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id = d.deal_id', 'd');
        $srch->addCondition('d.deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
        $srch->addCondition('d.deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
        $srch->addCondition('d.deal_status', '<', 2);
        $srch->addCondition('d.deal_deleted', '=', 0);
        $srch->addCondition('d.deal_complete', '=', 1);
        $srch->addCondition('d.deal_id', 'IN', $deals_arr);
        $srch->addMultipleFields(array('DISTINCT d.deal_id'));
        /* $srch->setPageSize(4); */
        $deal_rs = $srch->getResultSet();
        if ($db->total_records($deal_rs)) {
            $out .= '
						<div class="section-head">
							<h2>Recently Viewed Deals</h2>
						</div>';
            $inner_html = '';
            while ($deal = $db->fetch($deal_rs)) {
                $deals_arr2[$deal['deal_id']] = $deal['deal_id'];
            }
            $counter = 1;
            $deals_arr = array_reverse($deals_arr);
            foreach ($deals_arr as $deal) {
                if (!array_key_exists($deal, $deals_arr2)) {
                    continue;
                }
                $deal_obj = new DealInfo($deals_arr2[$deal]);
                $d_url = CONF_WEBROOT_URL . 'deal.php?deal=' . $deal_obj->getFldValue('deal_id') . '&type=main';
                $inner_html .= '<div class="pro-grid">
                                    <div class="prod-pic">
                                        <a href="' . friendlyUrl($d_url) . '" title="' . $deal_obj->getFldValue('deal_name') . '"><img alt="' . $deal_obj->getFldValue('deal_name') . '" src="' . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $deal_obj->getFldValue('deal_id') . '&type=nyafudetail&width=300&height=180"></a>
                                        <div class="overlay">
                                            <a class="green-button" href="' . friendlyUrl($d_url) . '">' . t_lang('M_TXT_VIEW_DEAL') . '</a>
                                        </div>
                                    </div>
                                    <div class="desc">
                                        <h2 class="deal_hdng">
                                            <a href="' . friendlyUrl($d_url) . '">' . $deal_obj->getFldValue('deal_name') . '</a>
                                        </h2>
                                        <h5 class="locator">' . $deal_obj->getFldValue('deal_location') . '</h5>
                                        <div class="pro_pricing"><h6 class="pricing fr"><span>' . CONF_CURRENCY . number_format($deal_obj->getFldValue('deal_original_price'), 0) . '</span> ' . CONF_CURRENCY . number_format($deal_obj->getFldValue('price'), 0) . '</h6></div>
                                    </div>
                                </div>';
                if ($counter >= 4) {
                    break;
                }
                $counter++;
            }
            $out .= $inner_html;
        }
        return $out;
    }
}

function getSettledUnsettledDealData($company_id, $deal_id = 0)
{
    global $db;
    $srch_amt = new SearchBase('tbl_coupon_mark', 'cm');
    $srch_amt->addDirectCondition('cm.cm_status IN(' . CONF_MERCHANT_VOUCHER . ')');
    $srch_amt->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=cm.cm_deal_id AND d.deal_company=' . intval($company_id), 'd');
    $srch_amt->joinTable('tbl_orders', 'INNER JOIN', 'o.order_id=cm.cm_order_id AND order_payment_status=1', 'o');
    $srch_amt->joinTable('tbl_order_deals', 'INNER JOIN', 'od.od_order_id=o.order_id AND od.od_deal_id=cm.cm_deal_id', 'od');
    if (intval($deal_id) > 0) {
        $srch_amt->addCondition('d.deal_id', '=', $deal_id);
    }
    $srch_amt->addFld('(od_deal_price - (IFNULL(deal_commission_percent,0)/100*od_deal_price) - (CASE
                        WHEN (d.`deal_charity_discount` IS NOT NULL) 
                        THEN
                          (CASE
                        WHEN d.deal_charity_discount_is_percent=1
                              THEN d. deal_charity_discount/100*od_deal_price
                              ELSE deal_charity_discount    
                          END )
                        ELSE 0
                        END)) as calculated_deal_amount');
    $srch_amt->addMultipleFields(array('cm_counpon_no', 'deal_paid', 'deal_status', 'deal_bonus', 'deal_id'));
    $srch_amt->doNotCalculateRecords();
    $srch_amt->doNotLimitRecords();
    $srch_amt->addGroupBy('cm_counpon_no');
    $query = "SELECT SUM(`deal_amount` - `deal_bonus`) as deal_amount, deal_status, `deal_paid` FROM (SELECT SUM(calculated_deal_amount) as deal_amount, `deal_paid`, `deal_bonus`, deal_status,`deal_id` FROM (" . $srch_amt->getQuery() . ") temp group by `deal_id`) t2 GROUP BY `deal_paid`";
    $rsVoucher = $db->query($query);
    while ($arrs = $db->fetch($rsVoucher)) {
        if ($arrs['deal_paid'] == 0) {
            $totalUnsettledPrice = $arrs['deal_amount'];
        } else {
            $totalSettledPrice = $arrs['deal_amount'];
        }
    }
    $arr['totalUnsettledPrice'] = floatval($totalUnsettledPrice);
    $arr['totalSettledPrice'] = floatval($totalSettledPrice);
    return $arr;
}
