<?php

require_once './application-top.php';
$SERVER_NAME = CONF_SERVER_NAME;

//For main deal selection
function getMaindealId($row_subscriber)
{
    global $db;
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('d.deal_company', '=', $row_subscriber['company_id']);
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_status', '=', 1);
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'tc.company_id =d.deal_company and tc.company_id=' . $row_subscriber['company_id'], 'tc');
    if ($row_subscriber['user_id'] > 0) {
        $srch->addGroupBy('d.deal_id');
        $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id = dc.dc_deal_id', 'dc');
    }
    $srch->doNotCalculateRecords();
    if (CONF_EMAIL_NUMBER > 0) {
        $pagesize = CONF_EMAIL_NUMBER;
        $srch->setPageSize($pagesize);
    }
    $srch->addOrder('deal_id', 'desc');
    $rs_listing = $srch->getResultSet();
    return $rs_listing;
}

$rs_subscribers = $db->query("select * from tbl_users_favorite");
while ($row_subscriber = $db->fetch($rs_subscribers)) {
    //fetch deals according to the company
    $rs_listing = getMaindealId($row_subscriber);
    $slider_arrow = 'background: url(http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'images/slider_arrow.jpg) no-repeat;';
    while ($row = $db->fetch($rs_listing)) {
        $company_id = $row_subscriber['company_id'];
        $user_id = $row_subscriber['user_id'];
        $getUserDetail = 'select user_email from tbl_users where user_id = ' . $user_id;
        $userQry = $db->query("$getUserDetail");
        $userRow = $db->fetch($userQry);
        $email = $userRow['user_email'];
        $srch1 = new SearchBase('tbl_deals', 'd');
        $rs = getMaindealId($row_subscriber);
        $maindealIdArray = [];
        if ($rs->num_rows > 0) {
            $data = $db->fetch_all($rs);
            foreach ($data as $rsval) {
                $maindealIdArray[] = $rsval['deal_id'];
            }
        }
        $srch1->addCondition('deal_id', 'NOT IN', $maindealIdArray);
        $srch1->addCondition('d.deal_company', '=', $row_subscriber['company_id']);
        $srch1->addCondition('deal_status', '=', 1);
        $srch1->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
        $dealsentArray = [];
        $srch2 = new SearchBase('tbl_users_favorite_sent', 'tns');
        $srch2->addCondition('ufs_company_id', '=', $row_subscriber['company_id']);
        $srch2->addCondition('ufs_user_id', '=', $row_subscriber['user_id']);
        $srch2->addMultipleFields(array('ufs_company_id', 'ufs_deal_id'));
        $dealsent = $srch2->getResultSet();
        if ($dealsent->num_rows > 0) {
            $data = $db->fetch_all($dealsent);
            foreach ($data as $rsval) {
                $dealsentArray[] = $rsval['deal_id'];
            }
        }
        array_push($dealsentArray, $row['deal_id']);
        if (!empty($dealsentArray)) {
            $srch1->addCondition('deal_id', 'NOT IN', $dealsentArray);
        }
        //add condition news letter sent
        if ($row_subscriber['user_id'] > 0) {
            $srch1->addGroupBy('d.deal_id');
        }
        $srch1->addMultipleFields(array('d.*', 'c.*'));
        $srch1->addOrder('deal_status');
        $srch1->setPageSize(4);
        //echo '<BR>'.$srch1->getQuery().'<BR>'; 
        $upcomingDeal = $srch1->getResultSet();
        if ($db->total_records($upcomingDeal) > 0) {
            $msg1 = '';
            $k = 1;
            $deal_arr = [];
            while ($row1 = $db->fetch($upcomingDeal)) {
                $deal_id = $row1['deal_id'];
                $deal_arr[] = $row1['deal_id'];
                $row1['price'] = $row1['deal_original_price'] - (($row1['deal_discount_is_percent'] == 1) ? $row1['deal_original_price'] * $row1['deal_discount'] / 100 : $row1['deal_discount']);
                $name1 = $row1['deal_img_name'];
                if ($name1 == '') {
                    $name1 = 'http://' . $SERVER_NAME . DEAL_IMAGES_URL . 'no-image.jpg';
                } else {
                    $name1 = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row1['deal_id'] . '&type=emailupcoming';
                }
                $deal_url = 'http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'deal.php?deal=' . $row1['deal_id'] . '&type=main';
                if ($k % 2 != 0) {
                    $msg1 .= '<tr>';
                }
                $msg1 .= '<td width="47%">
                        <table bgcolor="#e8e8e8" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td style="padding: 5px;"><a target="_blank" href="' . $deal_url . '"><img src="' . $name1 . '" style="border: medium none;" ></a></td>
                                </tr>
                                <tr>
                                    <td style="font-family: Arial; font-size: 13px; color: rgb(67, 67, 67); font-weight: bold; padding: 5px 0pt 10px 10px;">' . $row1['deal_name'] . '</td>
                                </tr>
                                <tr>
                                    <td style="font-family: Arial; font-size: 13px; color: rgb(67, 67, 67); font-weight: normal; padding: 5px 0pt 0pt 10px;">' . substr($row1['deal_subtitle'], 0, 40) . '</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td><a target="_blank" href="' . $deal_url . '" style="width: 100px; font-family: Arial; line-height: 30px; height: 30px; background: none repeat scroll 0% 0% rgb(255, 155, 12); color: rgb(255, 255, 255); text-align: center; text-decoration: none; padding: 0pt 10px; font-weight: bold; float: left; margin: 0pt 0pt 0pt 10px;">View Deal</a></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
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
        } else {
            $msg1 = '';
        }
        $deal_name = $row['deal_name'];
        $deal_id = $row['deal_id'];
        $deal_original_price = $row['deal_original_price'];
        $deal_desc = $row['deal_desc'];
        $deal_highlights = $row['deal_highlights'];
        $name = $row['deal_img_name'];
        $deal_start_time = $row['deal_start_time'];
        $user_id = $row_subscriber['user_id'];
        $company_id = $row_subscriber['company_id'];
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
        if ($row['company_address2'] != '')
            $address .= '<br/>' . $row['company_address2'];
        if ($row['company_address3'] != '')
            $address .= '<br/>' . $row['company_address3'];
        $price = $row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
        $priceSave = (number_format($row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']), 2));
        $company_profile_enabled = $row['company_profile_enabled'];
        if ($company_profile_enabled == 1) {
            $company_profile = $row['company_profile'];
        }
        $city = $row['subs_city'];
        $subject = "Favourite Merchant (" . $row['company_name'] . ") - " . $deal_name;
        $message = '<html><body><table align="center"  bgcolor="#5894cd" border="0" cellpadding="0" cellspacing="0" width="900">
			<tbody>
			<tr>
                        <td><table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#313131">
                            <tbody><tr>
                            <td style="font-family:Arial; text-align:center; padding:5px 0 0 0; color:#fff; font-size:11px; font-weight:normal;">&nbsp;</td>
                            </tr>
                            <tr>
                              <td style="font-family:Arial; text-align:center; padding:0 0 5px 0; color:#b9b9b9; font-size:11px; font-weight:normal;">' . t_lang('M_EMAIL_BE_SURE_TO_ADD') . ' <a style="color:#2875be;" href="mailto:' . CONF_EMAILS_FROM . '">' . CONF_EMAILS_FROM . '</a> ' . t_lang('M_EMAIL_BE_SURE_TO_ADD_CONCATENATE') . '</td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                            </tr>
                            <tr>
                              <td height="1" bgcolor="#5894cd"></td>
                            </tr>
                            </tbody></table>
                        </td>
			</tr>
	 
			<tr>
                        <td valign="top"><table align="center" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="600">
                                <tbody>

                                <tr>
                                <td width="15" bgcolor="#313131"></td>
                                <td width="570" bgcolor="#313131"><table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#313131">
                                  <tbody><tr>
                                    <td width="250" style="padding:0 0 15px 0;"><img width="224" height="77" alt="" src="http://' . $SERVER_NAME . LOGO_URL . CONF_EMAIL_LOGO . '"></td>
                                    <td valign="top" align="left"><table width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tbody><tr>
                                              <td align="center" style="font-family:Arial; padding:10px 0 0 0; color:#fff; font-size:20px; font-weight:bold;">' . $city_name . '</td>
                                            </tr>
                                            <tr>
                                              <td align="center" style="font-family:Arial; padding:0 0 5px 0; color:#fff; font-size:16px; font-weight:normal;">' . t_lang('M_EMAIL_EXCLUSIVE_OFFER') . '</td>
                                            </tr>
                                    </tbody></table></td>
                                    <td valign="top" align="left" style="font-family:Arial; padding:5px 0 5px 0; color:#fff; font-size:13px; font-weight:normal;"><table width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tbody><tr>
                                              <td align="right" style="font-family:Arial; padding:5px 0 5px 0; color:#fff; font-size:13px; font-weight:normal;">' . date("l, F d Y") . '</td>
                                            </tr>
                                            <tr>
                                              <td align="right" style=" padding:5px 0 5px 0;"><a href="' . CONF_FACEBOOK_URL . '"><img width="24" height="24" style="border:none;" src="http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'images/facebook.png"></a>&nbsp; <a href="' . CONF_TWITTER_USER . '"><img width="24" height="24" style="border:none;" src="http://' . $SERVER_NAME . CONF_WEBROOT_URL . 'images/twitter.png"></a></td>
                                            </tr>
                                    </tbody></table></td>
                                  </tr>
                                </tbody></table></td>
                                <td width="15" bgcolor="#313131">&nbsp;</td>
				  </tr>
				  
				<tr>
					<td colspan="3" bgcolor="#313131" height="3"></td>
				</tr>
					
				  <tr>
					<td>&nbsp;</td>
					<td>' . CONF_EMAIL_HEADER_TEXT . '</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td><table align="center" border="0" cellpadding="0" cellspacing="0" width="570">
					  <tbody><tr>
						<td colspan="2" valign="top"><a href="' . $dealUrlMain . '"><img src="' . $name . '" alt="" style="border: 1px solid rgb(200, 200, 200);"  ></a></td>
					  </tr>
					  <tr>
						<td valign="top">&nbsp;</td>
						<td align="right">&nbsp;</td>
					  </tr>
					  <tr>
						<td style="padding: 0pt 0pt 10px;" valign="top"><a style=" text-decoration:none; font-weight:bold; font-family:Arial; padding:0 0 0 0; color:#616161; font-size:24px; font-weight:bold;"  href="' . $dealUrlMain . '"> ' . $deal_name . '</a><br/>
						  <a style="font-family:Arial; color:#10759A; text-align:center; text-decoration:none; padding:0 0 0 0; font-style:italic;" href="' . $dealUrlMain . '">' . $row['deal_subtitle'] . '</a></td>
						<td align="right" width="25%"><a href="' . $dealUrlMain . '" style="width: 100px; font-family: Arial; line-height: 40px; height: 40px; background: none repeat scroll 0% 0% rgb(255, 155, 12); color: rgb(255, 255, 255); text-align: center; text-decoration: none; padding: 0pt 20px; font-weight: bold; float: right;">' . t_lang('M_TXT_VIEW_OFFER') . '</a></td>
					  </tr>
					  <tr>
						<td><table style="border: 1px solid rgb(26, 26, 26);" border="0" cellpadding="0" cellspacing="0" width="98%">
							<tbody><tr>
							  <td style="font-family: Arial; padding: 6px 0pt; background: none repeat scroll 0% 0% rgb(47, 47, 47); color: rgb(255, 255, 255); font-size: 14px; font-weight: bold; text-transform: uppercase;" align="center">' . t_lang('M_TXT_VALUE') . '</td>
							  <td style="font-family: Arial; padding: 6px 0pt; background: none repeat scroll 0% 0% rgb(47, 47, 47); color: rgb(255, 255, 255); font-size: 14px; font-weight: bold; text-transform: uppercase;" align="center">' . t_lang('M_TXT_OFF') . '</td>
							  <td style="font-family: Arial; padding: 6px 0pt; background: none repeat scroll 0% 0% rgb(47, 47, 47); color: rgb(255, 255, 255); font-size: 14px; font-weight: bold; text-transform: uppercase;" align="center">' . t_lang('M_TXT_SAVING') . '</td>
							</tr>
							<tr>
							  <td style="font-family: Arial; padding: 6px 0pt; border-right: 1px solid rgb(26, 26, 26); color: rgb(26, 26, 26); font-size: 14px; font-weight: normal;" align="center">' . CONF_CURRENCY . $deal_original_price . CONF_CURRENCY_RIGHT . '</td>
							  <td style="font-family: Arial; padding: 6px 0pt; border-right: 1px solid rgb(26, 26, 26); color: rgb(26, 26, 26); font-size: 14px; font-weight: normal;" align="center">' . (($row['deal_discount_is_percent'] == 1) ? '' : CONF_CURRENCY) . $row['deal_discount'] . (($row['deal_discount_is_percent'] == 1) ? '%' : '') . '</td>
							  <td style="font-family: Arial; padding: 6px 0pt; color: rgb(26, 26, 26); font-size: 14px; font-weight: normal;" align="center">' . CONF_CURRENCY . number_format($row['deal_original_price'] - $priceSave, 2) . CONF_CURRENCY_RIGHT . '</td>
							</tr>
						</tbody></table></td>
						<td style="color: rgb(134, 193, 33); text-align: center; padding: 0pt 0pt 10px; font-size: 36px; font-family: Arial; font-weight: bold;" align="center" valign="top">' . CONF_CURRENCY . number_format($price, 2) . CONF_CURRENCY_RIGHT . '</td>
					  </tr>
					  <tr>
						<td colspan="2">&nbsp;</td>
					  </tr>
					  <tr>
						<td colspan="2" bgcolor="#c8c8c8" height="1"></td>
					  </tr>
					</tbody></table></td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td valign="top"><table border="0" cellpadding="0" cellspacing="0" width="100%">
					  <tbody>
								' . $msg1 . '
								</tbody></table></td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				 
				  <tr>
					<td>&nbsp;</td>
					<td style="color: rgb(97, 97, 97); font-size: 12px;" align="right">&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td style="font-size: 14px; font-weight: bold; color: rgb(49, 49, 49); font-family: Arial; padding: 0pt 0pt 5px;" valign="top">Thanks<br>
					  The ' . CONF_SITE_NAME . ' Team<br>
			  <a target="_blank" href="http://' . $SERVER_NAME . CONF_WEBROOT_URL . '" style="color: rgb(0, 102, 204); text-decoration: none;">' . $_SERVER['SERVER_NAME'] . '</a></td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td colspan="3"><table bgcolor="#313131" border="0" cellpadding="0" cellspacing="0" width="100%">
						<tbody><tr>
						 <td align="center" style="font-size:13px; font-family:Arial; color:#FFFFFF; padding:15px 0 15px 0;">' . t_lang('M_EMAIL_NEED_HELP') . ' <a style="color:#2875be; text-decoration:none;" href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'contact-us.php">  ' . CONF_SITE_NAME . '</a></td>
						</tr>
					</tbody></table></td>
				  </tr>
				</tbody></table></td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
			  </tr>
			  <tr>
				<td style="font-family: Arial; font-size: 12px; color: rgb(255, 255, 255); font-weight: normal;" align="center">&copy; ' . date("Y") . ' ' . CONF_SITE_NAME . '. ' . t_lang('M_TXT_RIGHT_RESERVE') . '</td>
			  </tr>
			  <tr>
				<td style="font-family: Arial; padding: 5px 0pt 0pt; font-size: 11px; color: rgb(255, 255, 255); font-weight: normal;" align="center">' . t_lang('M_TXT_UNSUBSCRIBE_FROM_MERCHANT') . '<a href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'newsletter-subscription.php?code=' . $subs_code . '&email=' . $email . '" style="color: rgb(51, 51, 51);">' . t_lang('M_TXT_CLICK_HERE') . '</a></td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
			  </tr>
			</tbody></table>
			</body></html>';
        if ($user_id != 0 && $user_id != '') {
            $newsQry = 'select * from   tbl_users_favorite_sent where 	ufs_deal_id = ' . $deal_id . ' and  ufs_user_id = ' . $user_id . ' and  ufs_company_id = ' . $company_id;
            $newsletterSent = $db->query("$newsQry");
            $row = $db->fetch($newsletterSent);
            if ($db->total_records($newsletterSent) == 0) {
                $db->query("INSERT IGNORE INTO tbl_users_favorite_sent VALUES ($user_id, $deal_id, $company_id, '1');");
                foreach ($deal_arr as $key => $val) {
                    $newsQry = 'select * from   tbl_users_favorite_sent where 	ufs_deal_id = ' . $val . ' and  ufs_user_id = ' . $user_id . ' and  ufs_company_id = ' . $company_id;
                    $newsletterSent = $db->query("$newsQry");
                    $row = $db->fetch($newsletterSent);
                    if ($db->total_records($newsletterSent) == 0) {
                        $db->query("INSERT IGNORE INTO tbl_users_favorite_sent VALUES ($user_id, $val, $company_id, '1');");
                    }
                }
                $checkPer = $db->query("select * from tbl_email_notification where en_user_id=" . $user_id);
                $row_per = $db->fetch($checkPer);
                if ($row_per['en_favourite_merchant'] == 1) {
                    if (sendMail($email, $subject, $message, $headers)) {
                        echo "Mail to " . $email . "<br/>" . $message . "<br/><hr>";
                    }
                    echo "Mail to " . $email . "<br/>" . $message . "<br/><hr>";
                }
                #echo "Mail to ".$email."<br/>".$message."<br/><hr>";
                //die(print_r($deal_arr)); 
            }
        }
    }
}
