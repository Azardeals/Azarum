
<?php if ($db->total_records($rs_listing) == 0) { ?>
    <table class="tbl_data" width="100%" >
        <thead>
            <tr>
                <?php
                foreach ($arr_listing_fields as $val) {
                    echo '<th>' . $val . '</th>';
                }
                ?>
            </tr>
        </thead>
    <?php } ?>
    <?php
    for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
        echo '<div class="content">
                    <div class="right-links">
                      <ul>';
        echo '<li><a href="' . CONF_WEBROOT_URL . 'preview-deal.php?deal=' . $row[$primaryKey] . '&mode=preview" target="_blank" title="' . t_lang('M_TXT_PREVIEW') . '">	' . t_lang('M_TXT_PREVIEW') . '</a></li> ';
        if ($row['deal_status'] == 5) {
            echo '<li><a href="add-deals.php?edit=' . $row[$primaryKey] . '&page=' . $page . '&status=' . $_GET['status'] . '" title="' . t_lang('M_TXT_EDIT') . '">' . t_lang('M_TXT_EDIT') . '</a></li> ';
            echo '<li><a href="javascript:void(0);" onclick="deleteDeal(' . $row[$primaryKey] . ')" title="' . t_lang('M_TXT_DELETE_DEAL') . '">' . t_lang('M_TXT_DELETE_DEAL') . '</a></li> ';
        }
        if ($_REQUEST['status'] == 'expired' && $row['deal_is_duplicate'] == 0) {
            echo '<li><a href="' . CONF_WEBROOT_URL . 'merchant/company-deals.php?status=expired&old_deal_id=' . $row[$primaryKey] . '&page=1" title="' . t_lang('M_TEXT_REPOST') . '">	' . t_lang('M_TEXT_REPOST') . '</a></li> ';
        }
        if ($row['deal_tipped_at'] == "") {
            if ($row['deal_status'] != 3) {
                echo '<li><a href="javascript:void(0);" onclick="cancelDeal(' . $row[$primaryKey] . ')" title="Cancel Deal">' . t_lang('M_TXT_CANCEL_DEAL') . '</a></li> ';
            }
        }
        if ($row['deal_paid'] == 1) {
            echo '<li><a href="javascript:void(0);"   title="' . t_lang('M_TXT_SETTLED') . '" >' . t_lang('M_TXT_SETTLED') . '</a></li>';
        }
        echo '</ul></div><table cellspacing="0" cellpadding="0" border="0" width="100%" class="tbl_dealz"><tbody><tr>';
        foreach ($arr_listing_fields as $key => $val) {
            switch ($key) {
                case 'listserial':
                    echo $listserial;
                    break;
                case 'deal_img_name':
                    $imagePath = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=admin&time=' . time();
                    $imagePathPopUp = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=adminPopUp&time=' . time();
                    if ($row['deal_img_name'] != "") {
                        $imagePathPop = DEAL_IMAGES_URL . $row['deal_img_name'];
                    } else {
                        $imagePathPop = DEAL_IMAGES_URL . 'no-image.jpg';
                    }
                    $ratingRs = $db->query("select * from tbl_reviews where reviews_type=1 AND reviews_deal_id=" . $row['deal_id'] . " AND reviews_parent_id=0");
                    $totalRates = $db->total_records($ratingRs);
                    while ($rowRate = $db->fetch($ratingRs)) {
                        $totalRateValue += $rowRate['reviews_rating'];
                    }
                    if ($totalRates == 0) {
                        $rateClass = 'rate_0';
                    } else {
                        $avg = round($totalRateValue / $totalRates);
                        $rateClass = 'rate_' . $avg;
                    }
                    echo '<td width="189"><div class="deal-pic "> <a rel="prettyPhoto[gallery' . $row['deal_id'] . ']" href="' . $imagePathPop . '"><img alt="" src="' . $imagePath . '"><br/>
                            ' . t_lang('M_TXT_CLICK_TO_ENLARGE') . '</a> </div>
							<div class="rating"><span class="' . $rateClass . '"> </span></div></td>';
                    break;
                case 'deal_name':
                    echo '<td class="border-left"><table cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tbody><tr>
							
					
                              <td class="titles">' . $arr_lang_name[0] . ':' . ' ' . appendPlainText($row['deal_name']) . '<br/> 
												 ' . $arr_lang_name[1] . ':' . ' ' . appendPlainText($row['deal_name_lang1']) . '<br/>
                                <span class="sub-titles">' . $row['deal_subtitle' . $_SESSION['lang_fld_prefix']] . '</span></td>
                            </tr>
                            <tr>
                              <td class="deal_desc">' . t_lang('M_TXT_COMPANY') . ': ' . $row['company_name' . $_SESSION['lang_fld_prefix']] . '</td>
                            </tr>
                            <tr>
                              <td class="deal_desc">' . t_lang('M_FRM_CITY') . ': ' . $row['city_name' . $_SESSION['lang_fld_prefix']] . '</td>
                            </tr>
                            <tr>
                              <td class="deal_desc">' . t_lang('M_TXT_DEAL_START_ON') . ': ' . displayDate($row['deal_start_time'], true) . '</td>
                            </tr>
                            <tr>
                              <td class="deal_desc">' . t_lang('M_FRM_DEAL_ENDS_ON') . ': ' . displayDate($row['deal_end_time'], true) . '</td>
                            </tr>
                            <tr>
                              <td class="deal_desc noborder-bottom">' . t_lang('M_TXT_TIPPING_POINT') . ': ' . $row['deal_min_coupons'] . '</td>
                            </tr>
                          </tbody></table></td>';
                    break;
                case 'deal_status':
                    echo $arr_deal_status[$row[$key]];
                    break;
                case 'deal_tipped_at':
                    $row[$key] = displayDate($row[$key], true);
                    echo(($row[$key] == '') ? '---' : $row[$key]);
                    break;
                case 'action':
                    $objDeal = new DealInfo($row['deal_id']);
                    $sold = $objDeal->getFldValue('sold');
                    $objDeal->getFldValue('deal_discount_is_percent') == 1 ? $sign = $objDeal->getFldValue('deal_discount') . '%' : $sign = CONF_CURRENCY . $objDeal->getFldValue('deal_discount') . CONF_CURRENCY_RIGHT;
                    echo '<td class="gray-side" width="25%"><ul class="links-action navigation_vert">
                            <li> <a  href="javascript:void(0);"> ' . t_lang('M_TXT_PRICING') . '  </a>
                              <div class="dropdown" >
							  
                                  <table width="100%" class="tbl_dark" cellspacing="0" cellpadding="0"> 
								  <tr><td>' . t_lang('M_TXT_PRICE') . '</td><td>' . CONF_CURRENCY . number_format($objDeal->getFldValue('deal_original_price'), 2) . CONF_CURRENCY_RIGHT . '</td></tr>
								  <tr><td>' . t_lang('M_TXT_DISCOUNT') . '</td><td>' . $sign . '</td></tr>
								  <tr><td>' . t_lang('M_TXT_SAVINGS') . '</td><td>' . CONF_CURRENCY . number_format($objDeal->getFldValue('deal_original_price') - $objDeal->getFldValue('price'), 2) . CONF_CURRENCY_RIGHT . '</td></tr>
								  <tr><td>' . t_lang('M_TXT_DEAL_PRICE') . '</td><td>' . CONF_CURRENCY . number_format($objDeal->getFldValue('price'), 2) . CONF_CURRENCY_RIGHT . '</td></tr>
								  </table>
                              </div>
                            </li>
                            <li> <a class="navlink" href="#"> ' . t_lang('M_TXT_SALES') . ' <span>(' . $sold . '/' . CONF_CURRENCY . number_format($objDeal->getFldValue('sold_amount'), 2) . CONF_CURRENCY_RIGHT . ')</span></a>
                              <div class="dropdown" >
                                 
                                <p>';
                    $saleSummary = '';
                    /* $saleSummary .= '<strong>Sale Summary of "' . $objDeal->getFldValue('deal_name') . '"</strong><br/>'; */
                    $saleSummary .= '<strong>' . t_lang('M_TXT_VOUCHER_SOLD') . ':</strong> ' . $sold . '<br/>';
                    $saleSummary .= '<strong>' . t_lang('M_TXT_DEAL_PRICE') . ' :</strong> ' . CONF_CURRENCY . number_format($objDeal->getFldValue('price'), 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    $saleSummary .= '<strong>' . t_lang('M_TXT_TOTAL_SALES') . ' (' . t_lang('M_TXT_INCLUDING_ATTRIBUTES') . '):</strong> ' . CONF_CURRENCY . number_format($objDeal->getFldValue('sold_amount'), 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    $commission = $objDeal->getFldValue('sold_amount') * $objDeal->getFldValue('deal_commission_percent') / 100;
                    $saleSummary .= '<strong>' . t_lang('M_TXT_COMMISSION_EARNED') . ' @ ' . $objDeal->getFldValue('deal_commission_percent') . '% :</strong> ' . CONF_CURRENCY . number_format($commission, 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    $saleSummary .= '<strong>' . t_lang('M_FRM_BONUS') . ':</strong> ' . $objDeal->getFldValue('deal_bonus') . '<br/>';
                    $company = $commission + $objDeal->getFldValue('deal_bonus');
                    $srch = new SearchBase('tbl_charity_history', 'ch');
                    $srch->addCondition('ch_deal_id', '=', $row['deal_id']);
                    $srch->addFld("sum(ch_amount) as totalCharity");
                    $rs_listing1 = $srch->getResultSet();
                    $row1 = $db->fetch($rs_listing1);
                    $charity_amount = $row1['totalCharity'];
                    if ($charity_amount > 0) {
                        $saleSummary .= '<strong>' . t_lang('M_TXT_PAYABLE_TO_CHARITY') . ':</strong> ' . CONF_CURRENCY . number_format($charity_amount, 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    }
                    $srchAffiliate = new SearchBase('tbl_affiliate_wallet_history', 'awh');
                    $srchAffiliate->addCondition('wh_untipped_deal_id', '=', $row['deal_id']);
                    $srchAffiliate->addFld("sum(wh_amount) as totalAffiliateAmount");
                    $rs_afflisting = $srchAffiliate->getResultSet();
                    $rowaff = $db->fetch($rs_afflisting);
                    $affiliate_amount = $rowaff['totalAffiliateAmount'];
                    if ($affiliate_amount > 0) {
                        $saleSummary .= '<strong>' . t_lang('M_TXT_PAYABLE_TO_AFFILIATE') . ':</strong>&nbsp;' . CONF_CURRENCY . number_format($affiliate_amount, 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    }
                    if ($sold > 0) {
                        $saleSummary .= '<strong>' . t_lang('M_TXT_TOTAL_EARNING') . ':</strong> ' . CONF_CURRENCY . number_format($commission + $objDeal->getFldValue('deal_bonus') - $affiliate_amount, 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    } else {
                        $saleSummary .= '<strong>' . t_lang('M_TXT_TOTAL_EARNING') . ':</strong> ' . CONF_CURRENCY . number_format($commission - $affiliate_amount, 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    }
                    $merchant = $objDeal->getFldValue('sold_amount') - $commission - $objDeal->getFldValue('deal_bonus') - $charity_amount;
                    if ($merchant < 0) {
                        $merchant = 0;
                    }
                    $saleSummary .= '<strong>' . t_lang('M_TXT_PAYABLE_TO_MERCHANT') . ':</strong>&nbsp;' . CONF_CURRENCY . number_format($merchant, 2) . CONF_CURRENCY_RIGHT . '<br/>';
                    if ($merchant > 0) {
                        $merchant = $objDeal->getFldValue('sold_amount') - $commission - $objDeal->getFldValue('deal_bonus');
                    } else {
                        $merchant = '0.00';
                    }
                    $tipped_at = displayDate($objDeal->getFldValue('deal_tipped_at'), true);
                    if ($tipped_at == '') {
                        if (($objDeal->getFldValue('deal_min_coupons') - $sold) > 0) {
                            $saleSummary .= '<div style="color: black;padding:10px;">' . t_lang('M_TXT_DEAL_IS_NOT_TIPPED_YET') . ' ' . ($objDeal->getFldValue('deal_min_coupons') - $sold) . ' ' . t_lang('M_TXT_MORE_TO_BE_SOLD') . '</div> ';
                        }
                    } else {
                        $saleSummary .= '<strong>' . t_lang('M_TXT_TIPPED_AT') . ':</strong>&nbsp;' . $tipped_at;
                    }
                    if ($charity < 0) {
                        $charity = 0;
                    }
                    if ($merchant < 0) {
                        $merchant = 0;
                    }
                    echo $saleSummary;
                    echo '</p>
                              </div>
                            </li>';
                    /* charity calculation */
                    $srchCharity = new SearchBase('tbl_charity_history', 'ch');
                    $srchCharity->addCondition('ch_deal_id', '=', $row['deal_id']);
                    $srchCharity->addFld(" sum(ch_amount) as charityTotal");
                    $rsCharity = $srchCharity->getResultSet();
                    $rowCharity = $db->fetch($rsCharity);
                    $srchCharity1 = new SearchBase('tbl_charity_history', 'ch');
                    $srchCharity1->addCondition('ch_deal_id', '=', $row['deal_id']);
                    $srchCharity1->setPageSize(2);
                    $rsCharity1 = $srchCharity1->getResultSet();
                    echo '<li> <a href="javascript:void(0);"> ' . t_lang('M_TXT_CHARITY') . ' <span> (' . CONF_CURRENCY . number_format($rowCharity['charityTotal'], 2) . CONF_CURRENCY_RIGHT . ')</span></a>
                              <div class="dropdown" > ';
                    $charityList = '';
                    $charityList .= '<table class="tbl_dark" width="100%">
										<thead>
										<tr>';
                    $charityList .= '<th>' . t_lang('M_TXT_PARTICULARS') . '</th>';
                    $charityList .= '</tr></thead>';
                    while ($rowCharity1 = $db->fetch($rsCharity1)) {
                        $charityList .= '<tr>';
                        $charityList .= '<td>' . $rowCharity1['ch_particulars'] . '</td>';
                        $charityList .= '</tr>';
                    }
                    if ($db->total_records($rsCharity1) == 0)
                        $charityList .= '<tr><td colspan="2">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                    $charityList .= '</table>';
                    echo $charityList;
                    echo '</div>
                            </li>';
                    /* buyers and vouchers code */
                    $srchBuyers = new SearchBase('tbl_coupon_mark', 'cm');
                    $srchBuyers->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes 
							LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
                    $srchBuyers->addCondition('od.od_deal_id', '=', $row['deal_id']);
                    $srchBuyers->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id and o.order_payment_status>0', 'o');
                    $srchBuyers->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
                    $srchBuyers->addMultipleFields(array('od.od_order_id', 'od.od_to_name', 'u.user_name', 'u.user_email', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status', 'cm.cm_counpon_no'));
                    $srchBuyers->addOrder('o.order_date', 'DESC');
                    $srchBuyers->setPageSize(3);
                    $resultBuyer = $srchBuyers->getResultSet();
                    $arr_listing_fields1 = array(
                        'user_name' => 'User Name',
                        'order_id' => 'Voucher Code'
                    );
                    echo '<li><a href="tipped-members.php?deal_id=' . $row[$primaryKey] . '"   title="' . t_lang('M_TXT_UPDATE_VOUCHER') . '">' . t_lang('M_TXT_VOUCHERS') . '</a>
								<div class="dropdown" >
                                <h4><a href="tipped-members.php?deal_id=' . $row[$primaryKey] . '"> ' . t_lang('M_TXT_MANAGE_VOUCHERS') . '</a></h4>';
                    $buyers = '';
                    $buyers .= '<table class="tbl_dark" width="100%">
										<thead>
										<tr>';
                    $buyers .= '<th>' . t_lang('M_TXT_USER_NAME') . '</th><th>' . t_lang('M_TXT_VOUCHER_CODE') . '</th>';
                    $buyers .= '</tr></thead>';
                    while ($rowBuyer = $db->fetch($resultBuyer)) {
                        foreach ($arr_listing_fields1 as $key => $val) {
                            $buyers .= '<td>';
                            switch ($key) {
                                case 'order_id':
                                    $buyers .= $rowBuyer['od_order_id'] . $rowBuyer['cm_counpon_no'];
                                    break;
                                case 'od_qty':
                                    $buyers .= 1;
                                    break;
                                default:
                                    $buyers .= $rowBuyer[$key];
                                    break;
                            }
                            $buyers .= '</td>';
                        }
                        $buyers .= '</tr>';
                    }
                    if ($db->total_records($resultBuyer) == 0) {
                        $buyers .= '<tr><td colspan="2">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                    }
                    $buyers .= '</table>';
                    echo $buyers;
                    echo '</div>
								</li>';
                    $reviewQr = $db->query("select count(*) as total from tbl_reviews where reviews_type=1 AND reviews_deal_company_id = " . intval($_SESSION['logged_user']['company_id']) . " AND reviews_deal_id=" . $row['deal_id'] . " AND reviews_parent_id=0");
                    $totalRev = $db->fetch($reviewQr);
                    echo '<li><a href="deals-review.php?deal_id=' . $row[$primaryKey] . '" title="' . t_lang('M_TXT_REVIEWS') . '">' . t_lang('M_TXT_REVIEWS') . ' (' . $totalRev['total'] . ')</a></li>';
                    $srchImage = new SearchBase('tbl_deals_images', 'di');
                    $srchImage->addCondition('dimg_deal_id', '=', $row['deal_id']);
                    $rsImage = $srchImage->getResultSet();
                    $totalImages = $db->total_records($rsImage);
                    if ($row['deal_status'] == 5) {
                        echo '<li class="noborder-bottom"><a href="deals-images.php?deal_id=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_MANAGE_IMAGES') . '">' . t_lang('M_TXT_MANAGE_IMAGES') . ' (' . $totalImages . ')</a></li>';
                    }
                    echo '<li  class="noborder-bottom"><a href="company-transactions.php?company=' . $row['deal_company'] . '&deal=' . $row['deal_id'] . '"   title="' . t_lang('M_TXT_DEAL_WISE_TRANSACTION') . '">' . t_lang('M_TXT_DEAL_WISE_TRANSACTION') . '</a></li>';
                    echo '</ul></td>';
                default:
                    echo $row[$key];
                    break;
            }
            echo '</td>';
        }
        echo '</tbody></table>
                    <div class="clear"></div>
                  </div>';
    }
    if ($db->total_records($rs_listing) == 0) {
        echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr></table>';
    }
    