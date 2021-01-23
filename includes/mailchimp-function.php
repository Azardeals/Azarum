<?php

require_once '../application-top.php';
require_once 'mailchimp/Mailchimp.php';
$list_id = CONF_MAILCHIMP_LIST_ID;
$api_key = CONF_MAILCHIMP_API_KEY;
$option = array('debug');
$inst = new Mailchimp($api_key, $option);
try {
    $inst->helper->ping($api_key);
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
    exit();
}

function mailchimpSetting()
{
    if (!defined('CONF_EMAIL_SENDING_METHOD_PROMOTIONAL') || CONF_EMAIL_SENDING_METHOD_PROMOTIONAL != 1) {
        return false;
    }
    if (!defined('CONF_MAILCHIMP_LIST_ID') || strlen(trim(CONF_MAILCHIMP_LIST_ID)) < 2) {
        return false;
        //
    }
    return true;
}

function fetchMaindealInfo($arrayInfo = [])
{
    global $db;
    $SERVER_NAME = CONF_SERVER_NAME;
    //GET MAIN DEAL INFO
    if (!empty($arrayInfo['main_deal_id'])) {
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_id', '=', $arrayInfo['main_deal_id']);
        $upcomingDeal = $srch->getResultSet();
        while ($row = $db->fetch($upcomingDeal)) {
            $array_replace = setDealParams($row);
            // print_r($array_replace);
            $template = file_get_contents("templates/main_deal.php", true);
            $main_deal_data = str_replace(array_keys($array_replace), array_values($array_replace), $template);
        }
    }
    //Main Deal ends
    //Other deal Starts
    if (!empty($arrayInfo['other_deal_id'])) {
        $srch1 = new SearchBase('tbl_deals', 'd');
        $srch1->addCondition('deal_id', 'IN', $arrayInfo['other_deal_id']);
        $upcomingDeal = $srch1->getResultSet();
        if ($db->total_records($upcomingDeal) > 0) {
            $templateOther = file_get_contents("templates/other_deal.php", true);
            $sPattern = "/{{REPEAT_START}}(.*?){{REPEAT_END}}/s";
            preg_match($sPattern, $templateOther, $aMatch);
            $templateForSingleDeal = $aMatch[1];
            $combinedOtherDealData = "";
            $counter = 0;
            while ($row1 = $db->fetch($upcomingDeal)) {
                if ($counter % 2 == 0) {
                    if ($counter !== 0) {
                        $combinedOtherDealData .= "</tr>";
                    }
                    $combinedOtherDealData .= "<tr>";
                }
                $array_replace = setDealParams($row1);
                $single_deal_data = str_replace(array_keys($array_replace), array_values($array_replace), $templateForSingleDeal);
                $combinedOtherDealData .= $single_deal_data;
                $counter++;
            }
            $finalOtherTemplate = preg_replace($sPattern, $combinedOtherDealData, $templateOther);
            $finalOtherTemplate = str_replace(array_keys($array_replace), array_values($array_replace), $finalOtherTemplate);
            //replace Tags
            $finalOtherTemplate = str_replace("{{REPEAT_START}}", '', $finalOtherTemplate);
            $finalOtherTemplate = str_replace("{{REPEAT_END}}", '', $finalOtherTemplate);
        }
    }
    $description = $arrayInfo['template'];
    $maindeal = "xxmaindealxx";
    $otherdeal = "xxotherdealxx";
//echo $message;
    $description = str_replace($maindeal, $main_deal_data, $description);
//echo $msg1;
    $description = str_replace($otherdeal, $finalOtherTemplate, $description);
    $html = '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<style type="text/css">
			body{margin:0; padding:0; background:#fbf3d1;}
		</style>
	</head>
                            <body>' . $description . '</body></html>';
    return $description;
}

function setDealParams($row = [])
{
    $array_replace = [];
    if (!empty($row)) {
        $SERVER_NAME = CONF_SERVER_NAME;
        $dealUrlMain = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal.php?deal=' . $row['deal_id'] . '&type=main';
        $saving_amt = (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
        $price = $row['deal_original_price'] - $saving_amt;
        $deal_image = $row['deal_img_name'];
        $orignal_price = CONF_CURRENCY . " " . number_format($row['deal_original_price'], 2) . ' ' . CONF_CURRENCY_RIGHT;
        $offer_price = CONF_CURRENCY . " " . number_format($price, 2) . ' ' . CONF_CURRENCY_RIGHT;
        $off = number_format((($saving_amt / $row['deal_original_price']) * 100), 2) . '%';
        $saving = CONF_CURRENCY . ' ' . number_format($saving_amt, 2) . ' ' . CONF_CURRENCY_RIGHT;
        $deal_name = $row['deal_name' . $_SESSION['lang_fld_prefix']];
        //Get Deal Image
        if ($deal_image == '') {
            $deal_image = 'http://' . $SERVER_NAME . DEAL_IMAGES_URL . 'no-image.jpg';
        } else {
            $deal_image = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=emailmain';
        }
        $subtitle = substr($row['deal_subtitle' . $_SESSION['lang_fld_prefix']], 0, 40);
        $text_saving = t_lang('M_TXT_SAVING');
        $text_offer = t_lang('M_TXT_VALUE');
        $text_off = t_lang('M_TXT_OFF');
        $text_view_offer = t_lang('M_TXT_VIEW_OFFER');
        $array_replace = array(
            "{{DEAL_NAME}}" => $deal_name,
            "{{DEAL_SUB_TITLE}}" => $subtitle,
            "{{DEAL_SAVING}}" => $saving,
            "{{DEAL_OFF}}" => $off,
            "{{DEAL_OFFER_PRICE}}" => $offer_price,
            "{{DEAL_ORIGNAL_PRICE}}" => $orignal_price,
            "{{DEAL_IMAGE}}" => $deal_image,
            "{{DEAL_URL}}" => $dealUrlMain,
            "{{TEXT_SAVING}}" => $text_saving,
            "{{TEXT_VALUE}}" => $text_offer,
            "{{TEXT_OFF}}" => $text_off,
            "{{TEXT_VIEW_OFFER}}" => $text_view_offer,
        );
    }
    return $array_replace;
}

function fetchMaindealInfo1($arrayInfo = [])
{
    global $db;
    $SERVER_NAME = CONF_SERVER_NAME;
    $msg1 = "";
    if (!empty($arrayInfo['other_deal_id'])) {
        $srch1 = new SearchBase('tbl_deals', 'd');
        $srch1->addCondition('deal_id', 'IN', $arrayInfo['other_deal_id']);
        $upcomingDeal = $srch1->getResultSet();
        if ($db->total_records($upcomingDeal) > 0) {
            $msg1 = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
            $k = 1;
            $deal_arr = [];
            while ($row1 = $db->fetch($upcomingDeal)) {
                $deal_arr[] = $row1['deal_id'];
                $row1['price'] = $row1['deal_original_price'] - (($row1['deal_discount_is_percent'] == 1) ? $row1['deal_original_price'] * $row1['deal_discount'] / 100 : $row1['deal_discount']);
                $name1 = $row1['deal_img_name'];
                if ($name1 == '') {
                    $name1 = 'http://' . $SERVER_NAME . DEAL_IMAGES_URL . 'no-image.jpg';
                } else {
                    $name1 = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row1['deal_id'] . '&type=emailupcoming';
                }
                $deal_url = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal.php?deal=' . $row1['deal_id'] . '&type=main';
                $price1 = $row1['deal_original_price'] - (($row1['deal_discount_is_percent'] == 1) ? $row1['deal_original_price'] * $row1['deal_discount'] / 100 : $row1['deal_discount']);
                if ($k % 2 != 0) {
                    $msg1 .= '<tr>';
                }
                $msg1 .= '<td width="48%" style="background:#fff; padding:10px;">
	<table width="100%" border="0">
		<tr>
			<td>
				<a target="_blank" href="' . $deal_url . '"><img src="' . $name1 . '" style="display:block; border:1px solid #ddd;" width="100%" height="300" >
				</a>
			</td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #ddd; padding:8px 0 8px 0;"> <a href="#" style="font-family: Arial; font-size: 16px; color: #009eba; font-weight: bold; text-decoration:none;">' . $row1['deal_name' . $_SESSION['lang_fld_prefix']] . '</a></td>
									</tr>
									<tr>
										<td style="border-bottom:1px solid #ddd; padding:8px 0 8px 0; font-size:13px;">' . substr($row1['deal_subtitle' . $_SESSION['lang_fld_prefix']], 0, 40) . '</td>
									</tr>
									 <tr>
                                      <td style="padding:5px 0 0; ">
                                      	<table width="100%" border="0" cellpadding="0" cellspacing="0">

									<tr>
									<td valign="top" style="font-size:18px; color:#000;">
                                              <del style="color:#999; padding:0 8px 0 0;">' . CONF_CURRENCY . number_format($row1['deal_original_price'], 2) . CONF_CURRENCY_RIGHT . '</del>' . $price1 . '</td>
										<td align="right"><a target="_blank" href="' . $deal_url . '" style="width: 100px; font-family: Arial; line-height: 30px; height: 30px; background: none repeat scroll 0% 0% rgb(255, 155, 12); color: rgb(255, 255, 255); text-align: center; text-decoration: none; padding: 0pt 10px; font-weight: bold; float: left; margin: 0pt 0pt 0pt 10px;">' . t_lang('M_TXT_VIEW_DEAL') . '</a></td>
									</tr>
</table>
</td>
</tr>
									<tr>
										<td>&nbsp;</td>
									</tr>
							</table>
						</td>';
                if ($k % 2 != 0) {
                    $msg1 .= '<td>&nbsp;</td>';
                }
                if ($k % 2 == 0) {
                    $msg1 .= '</tr><tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>';
                }
                $k++;
            }
            '</table>';
        }
    }
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_id', '=', $arrayInfo['main_deal_id']);
    $upcomingDeal = $srch->getResultSet();
    while ($row = $db->fetch($upcomingDeal)) {
        $dealUrlMain = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal.php?deal=' . $row['deal_id'] . '&type=main';
        $saving_amt = (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
        $price = $row['deal_original_price'] - $saving_amt;
        $name = $row['deal_img_name'];
        if ($name == '') {
            $name = 'http://' . $SERVER_NAME . DEAL_IMAGES_URL . 'no-image.jpg';
        } else {
            $name = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=emailmain';
        }
        $message = '<table width="100%" border="0"  cellpadding="0" cellspacing="0" bgcolor="#fbf3d1" style="font-family:Arial; color:#333;">
        <tr>
            <td>
                <table width="700" border="0" cellpadding="0" cellspacing="0" align="center">
                    <tr>
                        <td>
                        <!--body start here-->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:15px; background:#fff;">
                                    <!--main deal start here-->
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width:300px">
                                                    <a target="_blank" href="' . $dealUrlMain . '"><img src="' . $name . '" style="display:block; border:1px solid #ddd;" width="100%" height="300"></a>
                                                </td>
                                                <td style="width:15px;"></td>
                                                <td valign="top">
                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="font-size:22px; color:#333; padding:0 0 10px 0;">
                                                                    <a style="font-size:22px; color:#333; text-decoration:none;" href="' . $dealUrlMain . '"> ' . $row['deal_name' . $_SESSION['lang_fld_prefix']] . '</a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:14px; line-height:18px; color:#333; padding:0 0 15px 0;">' . $row['deal_subtitle' . $_SESSION['lang_fld_prefix']] . '</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <table width="100%" border="0" style="border:1px solid #ddd; border-collapse:collapse;">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . CONF_CURRENCY . number_format($saving_amt, 2) . CONF_CURRENCY_RIGHT . '
                                                                                <span style="display:block; font-weight:normal; font-size:13px; color:#666;">' . t_lang('M_TXT_SAVING') . '</span>
                                                                            </td>
                                                                            <td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . CONF_CURRENCY . number_format($row['deal_original_price'], 2) . CONF_CURRENCY_RIGHT . ' <span style="display:block; font-weight:normal; font-size:13px; color:#666;">' . t_lang('M_TXT_VALUE') . '</span>
                                                                            </td>
                                                                            <td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . number_format((($saving_amt / $row['deal_original_price']) * 100), 2) . '%' . '<span style="display:block; font-weight:normal;font-size:13px; color:#666;">' . t_lang('M_TXT_OFF') . '</span>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                                <td style="padding:15px 0; font-size:24px; color:#009eba;"><del style="color:#666; padding:0 15px 0 0;">' . CONF_CURRENCY . number_format($row['deal_original_price'], 2) . CONF_CURRENCY_RIGHT . '</del>' . CONF_CURRENCY . number_format($price, 2) . CONF_CURRENCY_RIGHT . '
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <td>
                                                                        <a href="' . $dealUrlMain . '" style="display:inline-block; padding:10px 25px; background:#cf1e36; text-transform:uppercase; text-decoration:none; color:#fff; font-size:14px; font-weight:600;">' . t_lang('M_TXT_VIEW_OFFER') . '</a>
                                                                </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!--main deal end here-->
                                    </td>
                                </tr>
                            </table>
                            <!--body end here-->
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
	</table>';
    }
    $description = $arrayInfo['template'];
    $maindeal = "xxmaindealxx";
    $otherdeal = "xxotherdealxx";
//
//echo $message;
    $description = str_replace($maindeal, $message, $description);
//echo $msg1;
    $description = str_replace($otherdeal, $msg1, $description);
    $html = '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<style type="text/css">
			body{margin:0; padding:0; background:#fbf3d1;}
		</style>
	</head>
                            <body>' . $description . '</body></html>';
    return $description;
}

function getCityName($id)
{
    global $db;
    $srch = new SearchBase('tbl_cities');
    $srch->addMultipleFields(array('city_id', 'city_name'));
    $srch->addCondition('city_id', '=', $id);
    $srch->addCondition('city_active', '=', 1);
    $srch->addCondition('city_deleted', '=', 0);
    $rs = $srch->getResultSet();
    $arr_city = $db->fetch($rs);
    return $arr_city['city_name'];
}

function fetchCategoryListName($categoryId)
{
    global $list_id;
    $groups = getGroups($list_id);
    /* category Array */
    $groupArr = $groups['options']['interests-17097'];
    global $db;
    $srch = new SearchBase('tbl_deal_categories', 'dc');
    $srch->addCondition('dc.cat_id', '=', $categoryId);
    $srch->addMultipleFields(array('dc.cat_code'));
    $rs = $srch->getResultSet();
    $code = $db->fetch($rs);
    $srch1 = new SearchBase('tbl_deal_categories', 'dc');
    $srch1->addCondition('dc.cat_code', 'LIKE', $code['cat_code'] . '%');
    //$srch1->addMultipleFields(array('GROUP_CONCAT(`cat_name` SEPARATOR ",") As cat_name'));
    $srch1->addMultipleFields(array('cat_id,cat_name'));
    $rs1 = $srch1->getResultSet();
    //$srch1->getQuery();
    $categories = $db->fetch_all_assoc($rs1);
    $catArray = [];
    foreach ($categories as $key => $val) {
        if (array_key_exists($val, $groupArr)) {
            $catArray[] = $groupArr[$val];
        }
    }
    return $catArray;
}

function createSegment($list_id, $emailArr = [])
{
    global $inst;
    $emails = [];
    $segment_name = genRandomString();
    global $db;
    $srch = new SearchBase('tbl_mailchimp_user_desc', 'dc');
    $srch->addCondition('dc.mc_sub_email', 'IN', $emailArr);
    $rs = $srch->getResultSet();
    $userInfo = $db->fetch_all($rs);
    if (!empty($userInfo)) {
        foreach ($userInfo as $key => $val) {
            $data = $val['sub_email'];
            $batch = array('email' => $val['mc_sub_email'], 'euid' => $val['mc_euid'], 'leid' => $val['mc_leid']);
            $emails[$key] = $batch;
        }
    }
    $segments = $inst->lists->staticSegmentAdd($list_id, $segment_name);
    $segmentId = $segments['id'];
    $create = $inst->lists->staticSegmentMembersAdd($list_id, $segmentId, $emails);
    return $segmentId;
}

/* for creating static segment */

function fetchUsers($cat_id = '', $city_id = '')
{
    global $db;
    if ($cat_id != '') {
        $catCode = fetchCatCode($cat_id);
        $query = "SELECT `subs_email` FROM `tbl_newsletter_subscription` AS sub
INNER JOIN tbl_newsletter_category AS sub_cat ON nc_subs_id = subs_id WHERE ( nc_cat_id ={$cat_id} OR nc_cat_id IN (SELECT cat_id FROM tbl_deal_categories WHERE cat_code LIKE '{$catCode}%'))";
        if ($city_id != '') {
            $query .= "AND `subs_city` = {$city_id}";
        }
        $query .= " GROUP BY `subs_email`";
        $query .= " UNION ";
    }
    $query .= "(SELECT  `subs_email` FROM `tbl_newsletter_subscription` AS sub2 ";
    if ($city_id != '' || $cat_id != '') {
        $query .= " WHERE";
    }
    if ($cat_id != '') {
        $query .= "  subs_id NOT IN ( SELECT nc_subs_id FROM tbl_newsletter_category )";
    }
    if ($city_id != '' && $cat_id != '') {
        $query .= " AND";
    }
    if ($city_id != '') {
        $query .= "  `subs_city` = {$city_id}";
    }
    $query .= " GROUP BY `subs_email`)";
    $rs = $db->query($query);
    if ($db->total_records($rs) > 0) {
        $result = $db->fetch_all($rs);
        $emailArray = [];
        foreach ($result as $key => $val) {
            $emailArray[] = $val['subs_email'];
        }
        return $emailArray;
    } else {
        return false;
    }
}

function fetchNewDeals($cityid = '', $deal_id = "", $catId = "")
{
    global $db;
    $srch = new SearchBase('tbl_deals', 'd');
    if ($cityid != '') {
        $srch->addCondition('d.deal_city', '=', $cityid);
    }
    if ($deal_id != '') {
        $srch->addCondition('d.deal_id', '!=', $deal_id);
    }
    if ($catId != '') {
        $code = fetchCatCode($catId);
        $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id = doc.dc_deal_id', 'doc');
        $srch->joinTable('tbl_deal_categories', 'RIGHT JOIN', 'dc.cat_id = doc.dc_cat_id', 'dc');
        $srch->addCondition('dc.cat_code', 'LIKE', $code . '%');
    }
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_status', '<', 2);
    $srch->addCondition('deal_deleted', '=', 0);
    //echo $srch->getQuery();
    $upcomingDeal = $srch->getResultSet();
    $deal_arr = [];
    if ($db->total_records($upcomingDeal) > 0) {
        $deal_arr = [];
        while ($row1 = $db->fetch($upcomingDeal)) {
            $deal_arr[$row1['deal_id']] = $row1['deal_name'];
        }
    }
    return $deal_arr;
}

function getCategoryList()
{
    global $db;
    $srch = new SearchBase('tbl_deal_categories');
    $srch->addOrder('cat_display_order');
    $srch->addMultipleFields(array('cat_id', 'cat_name' . $_SESSION['lang_fld_prefix']));
    $srch->addCondition('cat_active', '=', 1);
    $srch->addCondition('cat_parent_id', 'IN', array(0, 1));
    $srch->doNotLimitRecords();
    $rs = $srch->getResultSet();
    $arr_cats = $db->fetch_all_assoc($rs);
    return $arr_cats;
}

function getCityList()
{
    global $db;
    $srch = new SearchBase('tbl_cities');
    $srch->addMultipleFields(array('city_id', 'city_name' . $_SESSION['lang_fld_prefix']));
    $srch->addCondition('city_active', '=', 1);
    $srch->addCondition('city_deleted', '=', 0);
    $srch->addOrder('city_name' . $_SESSION['lang_fld_prefix'], 'asc');
    $rs = $srch->getResultSet();
    $arr_city = $db->fetch_all_assoc($rs);
    return $arr_city;
}

function getList($list_id)
{
    global $inst;
    $set = $inst->campaigns->getList(array('list_id' => $list_id));
    return $set;
}

function segmentTest($list_id, $options)
{
    global $inst;
    $total = $inst->campaigns->segmentTest($list_id, $options);
    return $total;
}

function scheduleCampaign($id, $time)
{
    global $inst;
    try {
        $set = $inst->campaigns->schedule($id, $time);
    } catch (Exception $e) {
        $set = $e->getMessage();
    }
    return $set;
}

function sendCampaign($id)
{
    global $inst;
    try {
        $send = $inst->campaigns->send($id);
    } catch (Exception $e) {
        $send = $e->getMessage();
    }
    return $send;
}

function createCampaign($type, $options, $content, $segment_opts)
{
    global $inst;
    global $msg;
    try {
        $create = $inst->campaigns->create($type, $options, $content, $segment_opts);
    } catch (Exception $e) {
        $msg->addError($e->getMessage());
        return false;
    }
    return $create;
}

function fetchallSegment()
{
    global $db;
    $srch = new SearchBase('tbl_mc_segments');
    $srch->addMultipleFields(array('segment_id', 'segment_name'));
    $rs = $srch->getResultSet();
    if (!$row = $db->fetch_all_assoc($rs)) {
        return false;
    }
    return $row;
}

function memberInfo($list_id, $emails)
{
    global $inst;
    $groupsData = $inst->lists->memberInfo($list_id, $emails);
    return $groupsData;
}

function staticSegmentMembersAdd($list_id, $segmentId, $emails = [])
{
    global $inst;
    $inst->lists->staticSegmentMembersAdd($list_id, $segmentId, $emails);
}

function staticSegmentAdd($list_id, $segment_name)
{
    global $inst;
    $segment = $inst->lists->staticSegmentAdd($list_id, $segment_name);
    return $segment;
}

function getGroups($list_id)
{
    global $inst;
    $groupsData = $inst->lists->interestGroupings($list_id);
    $groups = [];
    $options = [];
    foreach ($groupsData as $key => $value) {
        $id = "interests-" . $value['id'];
        $groups[$id] = "Group-" . $value['name'];
        foreach ($value['groups'] as $option) {
            $options[$id][$option['name']] = $option['bit'];
        }
    }
    return compact('groups', 'options');
}

function fetchgroup_type()
{
    global $db;
    $srch = new SearchBase('tbl_mc_group_types');
    $srch->addCondition('type_status', '=', 'A');
    $city_to_show = '';
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $city_to_show = '_lang1';
    }
    $srch->addMultipleFields(array('type_id', 'type_name' . $city_to_show));
    $rs = $srch->getResultSet();
    if (!$row = $db->fetch_all_assoc($rs)) {
        return false;
    }
    return $row;
}

function getSegements($list_id)
{
    global $inst;
    $segments = [];
    $segments = $inst->lists->segments($list_id);
    return $segments;
}

function addSegment($list_id, $options = [])
{
    global $inst;
    $segments = $inst->lists->segmentAdd($list_id, $options);
    return $segments;
}

function getMergeVars($list_id)
{
    global $inst;
    $groupsData = $inst->lists->mergeVars(array($list_id));
    $merges = [];
    $mergesType = [];
    foreach ($groupsData['data'] as $data) {
        foreach ($data['merge_vars'] as $key => $tags) {
            $key = "MERGE" . $tags['id'];
            $value = $tags['name'];
            $merges[$key] = $value;
            if ($tags['choices']) {
                $mergesType[$key] = $tags['choices'];
            }
        }
    }
    return compact('merges', 'mergesType');
}

function options_drop($list_id, $type, $id)
{
    if ($type == 'group') {
        $groups = getGroups($list_id);
    } else {
        $mergeTags = getMergeVars($list_id);
    }
    $operators['groups'] = array('one', 'none', 'all');
    $operators['merge'] = array('eq', 'ne', 'like', 'nlike');
    $operator = "";
    if ($type == 'group') {
        $operator .= "<select name='op'>";
        foreach ($operators['groups'] as $value) {
            $operator .= "<option value='$value'>$value</option>";
        }
        $operator .= "</select>";
        $condition .= "<select name='value' >";
        foreach ($groups['options'][$id] as $values) {
            $condition .= "<option value='$values'>$values</option>";
        }
        $condition .= "</select>";
    } else {
        $operator .= "<select name='op'>";
        foreach ($operators['merge'] as $key => $value) {
            $operator .= "<option value='$value'>$value</option>";
        }
        $operator .= "</select>";
        if (isset($mergeTags['mergesType'][$id])) {
            $condition .= "<select name='value' >";
            foreach ($mergeTags['mergesType'][$id] as $value) {
                $condition .= "<option value='$value'>$value</option>";
            }
            $condition .= "</select>";
        } else {
            $condition .= "<input type='text' name='value'>";
        }
    }
    echo json_encode(array('op' => $operator, 'value' => $condition));
}
