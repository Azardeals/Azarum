<?php

require_once './application-top.php';
checkAdminPermission(5);
$SERVER_NAME = $_SERVER['SERVER_NAME'];
$srch = new SearchBase('tbl_deals', 'd');
$srch->joinTable('tbl_cities', 'LEFT OUTER JOIN', 'd.deal_city=c.city_id', 'c');
$srch->joinTable('tbl_companies', 'LEFT OUTER JOIN', 'tc.company_id =d.deal_company', 'tc');
$srch->joinTable('tbl_companies', 'LEFT OUTER JOIN', 'tc.company_id =d.deal_company', 'tc');
$srch->addCondition('d.deal_id', '=', $_GET['id']);
$srch->addCondition('deal_deleted', '=', 0);
$srch->doNotCalculateRecords();
$rs_listing = $srch->getResultSet();
$row = $db->fetch($rs_listing);
$deal_name = $row['deal_name'];
$email = $row['subs_email'];
$subs_code = $row['subs_code'];
$deal_city = $row['subs_city'];
$city_name = $row['city_name'];
$deal_id = $row['deal_id'];
$deal_original_price = $row['deal_original_price'];
$saving_amt = (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
$price = $row['deal_original_price'] - $saving_amt;
if ($row['deal_is_subdeal'] == 1) {
    $subdeal = new SearchBase('tbl_sub_deals');
    $subdeal->addCondition('sdeal_deal_id', '=', $row['deal_id']);
    $subdeal->addCondition('sdeal_active', '=', 1);
    $subdeal->addOrder('sdeal_original_price', 'asc');
    $res = $subdeal->getResultSet();
    $subdealData = $db->fetch($res);
    $saving_amt = (($subdealData['sdeal_discount_is_percentage'] == 1) ? $subdealData['sdeal_original_price'] * $subdealData['sdeal_discount'] / 100 : $subdealData['sdeal_discount']);
    $price = $subdealData['sdeal_original_price'] - $saving_amt;
    $deal_original_price = $subdealData['sdeal_original_price'];
}
$deal_desc = $row['deal_desc'];
$deal_highlights = $row['deal_highlights'];
$name = $row['deal_img_name'];
$deal_start_time = $row['deal_start_time'];
$newsletter_subs_id = $row['subs_id'];
$dealUrlMain = 'http://' . $SERVER_NAME . '/deal.php?deal=' . $row['deal_id'] . '&type=main';
if ($row['company_url'] != "") {
    $company_url = $row['company_url'];
} else {
    $company_url = 'javascript:void(0);';
}
if ($name == '') {
    $name = 'http://' . $SERVER_NAME . DEAL_IMAGES_URL . 'no-image.jpg';
} else {
    $name = 'http://' . $SERVER_NAME . '/deal-image-crop.php?id=' . $row['deal_id'] . '&type=emailmain';
}


if ($msg2 == '') {
    $width = 'width:630px;';
} else {
    $width = '';
}
$address = '';
if ($row['company_address2'] != '')
    $address .= '<br/>' . $row['company_address2'];
if ($row['company_address3'] != '')
    $address .= '<br/>' . $row['company_address3'];
$company_profile_enabled = $row['company_profile_enabled'];
if ($company_profile_enabled == 1) {
    $company_profile = $row['company_profile'];
}

$city = $row['subs_city'];
$subject = $deal_name;
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$fromemail = CONF_EMAILS_FROM;
$fromname = CONF_EMAILS_FROM_NAME;
$headers .= "From: " . $fromname . " <" . $fromemail . ">\r\n";
$headers .= 'Reply-To: ' . CONF_EMAILS_FROM . "\r\n";

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
																<a href="' . CONF_FACEBOOK_URL . '" style="margin:2px;"><img width="24" height="24" style="border:none;" src="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'images/icon_social_1.png"></a>&nbsp; <a style="margin:2px;" href="' . CONF_TWITTER_USER . '"><img width="24" height="24" style="border:none;" src="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'images/icon_social_3.png"></a>
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
																				<span style="display:block; font-weight:normal; font-size:13px; color:#666;">' . t_lang('M_TXT_SAVING') . '
																				</span>
																			</td>
						
																			<td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . CONF_CURRENCY . number_format($deal_original_price, 2) . CONF_CURRENCY_RIGHT . ' <span style="display:block; font-weight:normal; font-size:13px; color:#666;">' . t_lang('M_TXT_VALUE') . '</span>
																			</td>
																			<td style="border:1px solid #ddd; font-size:18px; color:#333; font-weight:bold; border-collapse:collapse; padding:10px; text-align:center;" width="33%">' . number_format((($saving_amt / $deal_original_price) * 100), 2) . '%' . '<span style="display:block; font-weight:normal;font-size:13px; color:#666;">' . t_lang('M_TXT_OFF') . '</span>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
														<tr>
															<td style="padding:15px 0; font-size:24px; color:#009eba;"><del style="color:#666; padding:0 15px 0 0;">' . CONF_CURRENCY . number_format($deal_original_price, 2) . CONF_CURRENCY_RIGHT . '</del>' . CONF_CURRENCY . number_format($price, 2) . CONF_CURRENCY_RIGHT . '
															</td>
														</tr>	
														<tr>
															<td>
																<a href="' . $dealUrlMain . '" style="display:inline-block; padding:10px 25px; background:#cf1e36; text-transform:uppercase; text-decoration:none; color:#fff; font-size:14px; font-weight:600;">' . t_lang('M_TXT_VIEW_OFFER') . '</a>
															</td>
														</tr>
														<tr>
															<td>&nbsp;</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
										<!--main deal end here-->
									</td>
								</tr>
								<tr>
									<td style="height:15px;"></td>
								</tr>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr>
									<td> 
										' . $msg2 . '
									</td>
								</tr>
								<tr>
									<td style="height:15px;"></td>
								</tr>
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
echo "<form method='post'>";
echo '<script type="text/javascript" src="/ckeditor-lnk/ckeditor.js "></script>';
echo '<table align="center"><tbody><tr><td width="1000">HTML CODE</td></tr><tr align="center"><td><textarea name="html_code_txt1" id="html_code_txt" rows="80" cols="200" width="600px" height="500px">' . $message . '</textarea></td></tr>';
echo '</tbody></table>';
echo "<script type='text/javascript'>CKEDITOR.replace( 'html_code_txt',{height:'400', width:'1000'} );</script>";
echo "</form>";
die();
