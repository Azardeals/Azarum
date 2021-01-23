<?php
require_once './application-top.php';
$SERVER_NAME = CONF_SERVER_NAME;
if ($_GET['code'] == 'htmlcode') {
    $cityMaxDeal = $db->query("SELECT deal_city, count( * ) AS total FROM `tbl_deals` WHERE deal_status < 2 GROUP BY deal_city ORDER BY total DESC LIMIT 0 , 1");
    $maxCity = $db->fetch($cityMaxDeal);
}
$rs_subscribers = $db->query("select * from tbl_newsletter_subscription");
while ($row_subscriber = $db->fetch($rs_subscribers)) {
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('d.deal_city', '=', $row_subscriber['subs_city']);
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_status', '=', 1);
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'tc.company_id =d.deal_company', 'tc');
    if ($_GET['code'] == 'htmlcode') {
        $srch->addCondition('deal_city', '=', $maxCity['deal_city']);
    }
    if ($row_subscriber['subs_user_id'] > 0) {
        $srch->addGroupBy('d.deal_id');
        $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id = dc.dc_deal_id', 'dc');
        $srch->joinTable('tbl_newsletter_category', 'INNER JOIN', 'nc.nc_subs_id = ' . $row_subscriber['subs_id'] . ' AND dc.dc_cat_id = nc.nc_cat_id', 'nc');
    }
    $srch->doNotCalculateRecords();
    if (CONF_EMAIL_NUMBER > 0) {
        $pagesize = CONF_EMAIL_NUMBER;
        $srch->setPageSize($pagesize);
    }
    $srch->addOrder('deal_id', 'desc');
    $rs_listing = $srch->getResultSet();
    while ($row = $db->fetch($rs_listing)) {
        $srch1 = new SearchBase('tbl_deals', 'd');
        $srch1->addCondition('deal_city', '=', $row_subscriber['subs_city']);
        $srch1->addCondition('deal_status', '=', 1);
        $srch1->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
        $dealsentArray = [];
        $srch2 = new SearchBase('tbl_newsletter_sent', 'tns');
        $srch2->addCondition('newsletter_subs_id', '=', $row_subscriber['subs_id']);
        $srch2->addMultipleFields(array('newsletter_subs_id', 'newsletter_deal_id'));
        $dealsent = $srch2->getResultSet();
        if ($dealsent->num_rows > 0) {
            $data = $db->fetch_all($dealsent);
            foreach ($data as $rsval) {
                $dealsentArray[] = $rsval['newsletter_deal_id'];
            }
        }
        array_push($dealsentArray, $row['deal_id']);
        if (!empty($dealsentArray)) {
            $srch1->addCondition('deal_id', 'NOT IN', $dealsentArray);
        }
        if ($row_subscriber['subs_user_id'] > 0) {
            $srch1->addGroupBy('d.deal_id');
            $srch1->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id = dc.dc_deal_id', 'dc');
            $srch1->joinTable('tbl_newsletter_category', 'INNER JOIN', 'nc.nc_subs_id = ' . $row_subscriber['subs_id'] . ' AND dc.dc_cat_id = nc.nc_cat_id', 'nc');
        }
        $srch1->addMultipleFields(array('d.*', 'c.*'));
        $srch1->addOrder('deal_status');
        //$srch1->setPageSize(4);  
        if (CONF_DEALS_PER_EMAIL) {
            $pagesize = CONF_DEALS_PER_EMAIL;
            $srch1->setPageSize($pagesize);
        }
        //echo '<BR>'.$srch1->getQuery().'<BR>'; 
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
                $price = $row1['deal_original_price'] - (($row1['deal_discount_is_percent'] == 1) ? $row1['deal_original_price'] * $row1['deal_discount'] / 100 : $row1['deal_discount']);
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
                                              <del style="color:#999; padding:0 8px 0 0;">' . CONF_CURRENCY . number_format($row1['deal_original_price'], 2) . CONF_CURRENCY_RIGHT . '</del>' . CONF_CURRENCY . number_format($price, 2) . CONF_CURRENCY_RIGHT . '</td>
										<td align="right"><a target="_blank" href="' . $deal_url . '" style="width: 100px; font-family: Arial; line-height: 30px; height: 30px; background: none repeat scroll 0% 0% rgb(255, 155, 12);  color:#000; text-align: center; text-decoration: none; padding: 0pt 10px; font-weight: bold; float: left; margin: 0pt 0pt 0pt 10px;">' . t_lang('M_TXT_VIEW_DEAL') . '</a></td>
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
            $msg1 .= '</table>';
        } else {
            $msg1 = '';
        }
        $deal_name = $row['deal_name' . $_SESSION['lang_fld_prefix']];
        $email = $row_subscriber['subs_email'];
        $subs_code = $row_subscriber['subs_code'];
        $deal_city = $row_subscriber['subs_city'];
        $city_name = $row['city_name' . $_SESSION['lang_fld_prefix']];
        $deal_id = $row['deal_id'];
        $deal_original_price = $row['deal_original_price'];
        $deal_desc = $row['deal_desc' . $_SESSION['lang_fld_prefix']];
        $deal_highlights = $row['deal_highlights' . $_SESSION['lang_fld_prefix']];
        $name = $row['deal_img_name'];
        $deal_start_time = $row['deal_start_time'];
        $newsletter_subs_id = $row_subscriber['subs_id'];
        $dealUrlMain = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal.php?deal=' . $row['deal_id'] . '&type=main';
        if ($row['company_url'] != "") {
            $company_url = $row['company_url'];
        } else {
            $company_url = 'javascript:void(0);';
        }
        if ($name == '') {
            $name = 'http://' . $SERVER_NAME . DEAL_IMAGES_URL . 'no-image.jpg';
        } else {
            $name = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=emailmain';
        }
        if ($msg1 == '') {
            $width = 'width:630px;';
        } else {
            $width = '';
        }
        $address = '';
        if ($row['company_address2' . $_SESSION['lang_fld_prefix']] != '')
            $address .= '<br/>' . $row['company_address2' . $_SESSION['lang_fld_prefix']];
        if ($row['company_address3' . $_SESSION['lang_fld_prefix']] != '')
            $address .= '<br/>' . $row['company_address3' . $_SESSION['lang_fld_prefix']];
        $saving_amt = (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
        $price = $row['deal_original_price'] - $saving_amt;
        $company_profile_enabled = $row['company_profile_enabled'];
        if ($company_profile_enabled == 1) {
            $company_profile = $row['company_profile' . $_SESSION['lang_fld_prefix']];
        }
        $city = $row['subs_city'];
        $subject = $deal_name;
        $message = '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<style type="text/css">
			body{margin:0; padding:0; background:#fbf3d1;}
		</style>
	</head>
	<body>
	<table width="100%" border="0"  cellpadding="0" cellspacing="0" bgcolor="#fbf3d1" style="font-family:Arial; color:#333;">
        <tr>
            <td>
                <table width="700" border="0" cellpadding="0" cellspacing="0" align="center">
                    <tr>
                        <td style=" text-align:center;padding:10px; font-size:13px; color:#333;">' . t_lang('M_EMAIL_BE_SURE_TO_ADD') . ' <a style="color:#d71732; text-decoration: underline;" href="mailto:' . CONF_EMAILS_FROM . '">' . CONF_EMAILS_FROM . '</a> ' . t_lang('M_EMAIL_BE_SURE_TO_ADD_CONCATENATE') . '
                        </td>
                    </tr>

                    <tr>
                    <td>
                    <!--header start here-->
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                        <td valign="top" style="border-top:5px solid #0db8d6; padding:15px; background:#0db8d6;">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <img width="219" height="54" alt="bitFATDeals" src="http://' . $SERVER_NAME . LOGO_URL . CONF_EMAIL_LOGO . '">
                                    </td>
                                    <td align="center" style="font-family:Arial; padding:0 0 5px 0; color:#fff; font-size:16px; font-weight:normal;">' . $city_name . '
                                    </td>
                                    <td>
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="right" style="font-size:14px; color:#fff; padding:0 0 10px 0;">' . date("l, F d Y") . '</td>
                                            </tr>
                                            <tr>
                                                <td align="right">
                                                        <a href="' . CONF_FACEBOOK_URL . '" style="margin:2px;"><img width="24" height="24" style="border:none;" src="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'images/facebook.png"></a>&nbsp; <a style="margin:2px;" href="' . CONF_TWITTER_USER . '"><img width="24" height="24" style="border:none;" src="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'images/twitter.png"></a>
                                                </td>
                                            </tr>

                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    </table> 
                    <!--header end here-->
                    </td>
                    </tr>
                    <tr>
                        <td>
                        <!--body start here-->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                        <td style="padding:15px; background:#fff;">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                                <td>' . CONF_EMAIL_HEADER_TEXT . '</td>
                                                                <td>&nbsp;</td>
                                                        </tr>
                                                </table>
                                        </td>
                                </tr>

                                <tr>
                                        <td style="height:15px;"></td>
                                </tr>
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
                                                                        <a style="font-size:22px; color:#333; text-decoration:none;" href="' . $dealUrlMain . '"> ' . $deal_name . '</a>
                                                                </td>
                                                        </tr>
                                                        <tr>
                                                                <td style="font-size:14px; line-height:18px; color:#333; padding:0 0 15px 0;">
                                                                        ' . $row['deal_subtitle' . $_SESSION['lang_fld_prefix']] . '</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <table width="100%" border="0" style="border:1px solid #ddd; border-collapse:collapse;">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . CONF_CURRENCY . number_format($saving_amt, 2) . CONF_CURRENCY_RIGHT . '
                                                                                <span style="display:block; font-weight:normal; font-size:13px; color:#666;">' . t_lang('M_TXT_SAVING') . '</span>
                                                                            </td>
                                                                            <td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . CONF_CURRENCY . number_format($row['deal_original_price'], 2) . CONF_CURRENCY_RIGHT . ' <span style="display:block; font-weight:normal; font-size:13px; color:#666;">' . t_lang('M_TXT_VALUE') . '</span></td>
                                                                            <td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . number_format((($saving_amt / $row['deal_original_price']) * 100), 2) . '%' . '<span style="display:block; font-weight:normal;font-size:13px; color:#666;">' . t_lang('M_TXT_OFF') . '</span></td>
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
                                                            <td><a href="' . $dealUrlMain . '" style="display:inline-block; padding:10px 25px; background:#cf1e36; text-transform:uppercase; text-decoration:none; color:#fff; font-size:14px; font-weight:600;">' . t_lang('M_TXT_VIEW_OFFER') . '</a></td>
                                                        </tr>
                                                        <tr><td>&nbsp;</td></tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!--main deal end here-->
                                    </td>
                                </tr>
                                <tr><td style="height:15px;"></td></tr>
                                <tr><td colspan="2">&nbsp;</td></tr>
                                <tr><td>' . $msg1 . '</td></tr>
                                <tr><td style="height:15px;"></td></tr>
                                <tr>
                                    <td style="padding:10px; background:#fff;">
                                        <table width="100%" border="0">
                                            <tr>
                                                <td valign="top" style="font-size:14px; font-weight:bold; color:#313131; font-family:Arial; padding:0 0 5px 0;">' . t_lang('M_TXT_THANKS') . '<br/>
                                                The ' . CONF_SITE_NAME . ' Team<br>
                                                <a target="_blank" href="http://' . $SERVER_NAME . CONF_WEBROOT_URL . '" style="color:#d71732; text-decoration:none;">' . $SERVER_NAME . '</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px; background:#111;">
                                        <table width="100%" border="0">
                                            <tr>
                                                <td valign="top" style=" text-align:center;font-size:14px;color:#fff; font-family:Arial; padding:0 0 5px 0;">' . t_lang('M_EMAIL_NEED_HELP') . ' <a style="color:#d71732; text-decoration:none;" href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'contact-us.php">  ' . CONF_SITE_NAME . '</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr>
                                    <td style="font-family: Arial; font-size: 12px; color:#000; font-weight: normal;" align="center">&copy; ' . date("Y") . ' ' . CONF_SITE_NAME . '. ' . t_lang('M_TXT_RIGHT_RESERVE') . '
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: Arial; padding: 5px 0pt 0pt; font-size: 11px;  color:#000; font-weight: normal;" align="center">' . t_lang('M_TXT_UNSUBSCRIBE_FROM_EMAIL') . '<a href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'newsletter-subscription.php?code=' . $subs_code . '&email=' . $email . '" style=" color:#000;"> ' . t_lang('M_TXT_CLICK_HERE') . '</a>
                                    </td>
                                </tr>
                            </table>
                            <!--body end here-->
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
	</table>
	</body>
</html>';
        if ($newsletter_subs_id != 0 && $newsletter_subs_id != '') {
            $newsQry = 'select * from   tbl_newsletter_sent where 	newsletter_deal_id = ' . $deal_id . ' and  newsletter_subs_id = ' . $newsletter_subs_id;
            $newsletterSent = $db->query("$newsQry");
            $row = $db->fetch($newsletterSent);
            if ($db->total_records($newsletterSent) == 0) {
                $db->query("INSERT INTO tbl_newsletter_sent VALUES ($newsletter_subs_id, $deal_id, '1');");
                foreach ($deal_arr as $key => $val) {
                    $newsQry = 'select * from   tbl_newsletter_sent where 	newsletter_deal_id = ' . $val . ' and  newsletter_subs_id = ' . $newsletter_subs_id;
                    $newsletterSent = $db->query("$newsQry");
                    $row = $db->fetch($newsletterSent);
                    if ($db->total_records($newsletterSent) == 0) {
                        $db->query("INSERT INTO tbl_newsletter_sent VALUES ($newsletter_subs_id, $val, '1');");
                    }
                }
                if ($row_Subscriber['subs_user_id'] > 0) {
                    $checkPer = $db->query("select * from tbl_email_notification where en_user_id=" . $row_Subscriber['subs_user_id']);
                    $row_per = $db->fetch($checkPer);
                    if ($row_per['en_city_subscriber'] == 1) {
                        if (sendMail($email, $subject, $message)) {
                            echo "Mail to " . $email . "<br/>" . $message . "<br/><hr>";
                        }
                        echo "Mail to " . $email . "<br/>" . $message . "<br/><hr>";
                    }
                } else {
                    if (sendMail($email, $subject, $message)) {
                        echo "Mail to " . $email . "<br/>" . $message . "<br/><hr>";
                    }
                    echo "Mail to " . $email . "<br/>" . $message . "<br/><hr>";
                }
            }
        }
        if ($total == 0 && $_GET['code'] == 'htmlcode') {
            echo '<table align="center"><tbody><tr><td>HTML CODE</td></tr><tr align="center"><td><textarea rows="15" cols="110">' . $message . '</textarea></td></tr></tbody></table>';
            die();
        }
    }
}
?>
<style type="text/css">     body{margin:0; padding:0; background:#fbf3d1;} </style>