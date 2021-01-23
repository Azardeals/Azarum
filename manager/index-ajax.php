<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(5);
$post = getPostedData();
$mode = $post['mode'];
switch ($mode) {
    case 'puchased':
        $reg_status .= ' <table width="100%" class="tbl_data">
                        <tr>
                                <th >' . t_lang('M_TXT_TOTAL_SAVED') . ':</th>
                                <th >' . t_lang('M_TXT_TOTAL_COUPON_BOUGHT') . ':</th>
                                <th >' . t_lang('M_TXT_TOTAL_ADMIN_EARNINGS') . ':</th>
                        </tr>';
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_deleted', '=', 0);
        $cnd = $srch->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
        $srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
        $srch->addOrder('company.company_id');
        $rs_listingdeal = $srch->getResultSet();
        $companyArray = [];
        while ($row = $db->fetch($rs_listingdeal)) {
            $companyArray[$row['company_id']]['company_name'] = $row['company_name'];
            $price = $row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
            $srch1 = new SearchBase('tbl_order_deals', 'od');
            $srch1->addCondition('od.od_deal_id', '=', $row['deal_id']);
            $srch1->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
            $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
            $srch1->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
            $srch1->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date>'" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
            $rs1 = $srch1->getResultSet();
            if (!$row_sold = $db->fetch($rs1))
                $row_sold = array('sold' => 0, 'payment_pending' => 0);
            $companyArray[$row['company_id']]['coupon'] += $row_sold['sold'];
            $TotalCoupon += $row_sold['sold'];
            $Total += $price * $row_sold['sold'];
            $companyArray[$row['company_id']]['AmtSaved'] += $price * $row_sold['sold'];
            /* ADMIN TOTAL SALES */
            $objDeal = new DealInfo($row['deal_id']);
            $sold = $objDeal->getFldValue('sold');
            if ($sold > 0) {
                $commission = $sold * $objDeal->getFldValue('price') * $objDeal->getFldValue('deal_commission_percent') / 100;
                $saleSummary += ($commission + $objDeal->getFldValue('deal_bonus') );
                $amt = $commission + $objDeal->getFldValue("deal_bonus");
                $companyArray[$row['company_id']]['amt'] += $amt;
            }
            /* ADMIN TOTAL SALES */
        }
        $reg_status .= '<tr><td>' . CONF_CURRENCY . (($Total == '') ? '0.00' : number_format($Total, 2)) . CONF_CURRENCY_RIGHT . '</td>
		<td>' . (($TotalCoupon == '') ? '0' : $TotalCoupon) . '</td><td><a href="javascript:void(0)" 
		onclick="$(\'#companyVoucherDiv\').toggle();"> ' . CONF_CURRENCY . number_format($saleSummary, 2) . CONF_CURRENCY_RIGHT . '</a></td></tr></table>';
        echo '<div class="title"> ' . t_lang('M_TXT_TOTAL_VOUCHERS_PURCHASED') . ' </div><div class="content">' . $reg_status . '</div>';
        $reg_status1 = '';
        $reg_status1 .= ' <table width="100%" class="tbl_data">
						<tr>
							<th >' . t_lang('M_FRM_COMPNAY_NAME') . ':</th>
							<th >' . t_lang('M_TXT_TOTAL_COUPON_BOUGHT') . ':</th>
							<th >' . t_lang('M_TXT_TOTAL_SAVED') . ':</th>
							<th >' . t_lang('M_TXT_TOTAL_ADMIN_EARNINGS') . ':</th>
						</tr>';
        foreach ($companyArray as $key => $val) {
            $reg_status1 .= '<tr><td><a href="javascript:void(0);" onclick="searchCoupon(' . $key . ')">' . $val['company_name'] . '</a></td>';
            $reg_status1 .= '<td>' . $val['coupon'] . '</td>';
            $reg_status1 .= '<td>' . $val['AmtSaved'] . '</td>';
            $reg_status1 .= '<td>' . $val['amt'] . '</td></tr>';
        }
        $reg_status1 .= '<tr><td></td><th>' . (($TotalCoupon == '') ? '0' : $TotalCoupon) . '</th><th>' . CONF_CURRENCY . (($Total == '') ? '0.00' : $Total) . CONF_CURRENCY_RIGHT . '</th><th>' . CONF_CURRENCY . number_format($saleSummary, 2) . CONF_CURRENCY_RIGHT . '</th></tr></table>';
        echo '<div id="companyVoucherDiv"><div class="title "> ' . t_lang('M_TXT_TOTAL_VOUCHERS_PURCHASED') . ' </div><div class="content">' . $reg_status1 . '</div></div>';
        break;
    case 'searchCoupon':
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_deleted', '=', 0);
        $cnd = $srch->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
        $srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
        $cnd = $srch->addCondition('company.company_id', '=', $post['id']);
        $srch->addOrder('company.company_id');
        $rs_listingdeal = $srch->getResultSet();
        $companyArray = [];
        while ($row = $db->fetch($rs_listingdeal)) {
            $company_name = $row['company_name'];
            $companyArray[$row['deal_id']]['deal_id'] = $row['deal_id'];
            $companyArray[$row['deal_id']]['deal_name'] = $row['deal_name'];
            $companyArray[$row['deal_id']]['deal_start_time'] = date('d-m-Y h:i:s', strtotime($row['deal_start_time']));
            $price = $row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
            $moneysaveByuser = $row['deal_original_price'] - $price;
            $srch1 = new SearchBase('tbl_order_deals', 'od');
            $srch1->addCondition('od.od_deal_id', '=', $row['deal_id']);
            $srch1->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
            $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
            $srch1->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
            $srch1->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date>'" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
            $srch1->addFld("od.od_deal_price");
            $rs1 = $srch1->getResultSet();
            if (!$row_sold = $db->fetch($rs1))
                $row_sold = array('sold' => 0, 'payment_pending' => 0);
            $companyArray[$row['deal_id']]['price'] = $price;
            $companyArray[$row['deal_id']]['coupon'] += $row_sold['sold'];
            $TotalCoupon += $row_sold['sold'];
            $Total += $price * $row_sold['sold'];
            //       $companyArray[$row['deal_id']]['AmtSaved']+=$row_sold['od_deal_price'] * $row_sold['sold'];
            $companyArray[$row['deal_id']]['AmtSaved'] += $moneysaveByuser * $row_sold['sold'];
            /* ADMIN TOTAL SALES */
            $objDeal = new DealInfo($row['deal_id']);
            $sold = $objDeal->getFldValue('sold');
            if ($sold > 0) {
                $commission = $sold * $objDeal->getFldValue('price') * $objDeal->getFldValue('deal_commission_percent') / 100;
                $saleSummary += ($commission + $objDeal->getFldValue('deal_bonus') );
                $amt = $commission + $objDeal->getFldValue("deal_bonus");
                $companyArray[$row['deal_id']]['amt'] += $amt;
            }
            /* ADMIN TOTAL SALES */
        }
        $reg_status1 = '';
        $reg_status1 .= ' <table width="100%" class="tbl_data">
						<tr>
							<th >' . t_lang('M_TXT_DEAL_NAME') . ':</th>
							<th >' . t_lang('M_TXT_DEAL_START') . ':</th>
							<th >' . t_lang('M_TXT_DEAL_PRICE') . ':</th>
							<th >' . t_lang('M_TXT_TOTAL_COUPON_BOUGHT') . ':</th>
							<th >' . t_lang('M_TXT_TOTAL_SAVED') . ' ' . t_lang('M_TXT_BY_BUYER') . ':</th>
							<th >' . t_lang('M_TXT_TOTAL_ADMIN_EARNINGS') . ':</th>
						</tr>';
        foreach ($companyArray as $key => $val) {
            $reg_status1 .= '<tr><td>' . $val['deal_name'] . '</td>';
            $reg_status1 .= '<td>' . $val['deal_start_time'] . '</td>';
            $reg_status1 .= '<td>' . $val['price'] . '</td>';
            $reg_status1 .= '<td>' . $val['coupon'] . '</td>';
            $reg_status1 .= '<td>' . $val['AmtSaved'] . '</td>';
            $reg_status1 .= '<td>' . $val['amt'] . '</td></tr>';
        }
        $reg_status1 .= '<tr><td></td><td></td><td></td><th>' . (($TotalCoupon == '') ? '0' : $TotalCoupon) . '</th><th>' . CONF_CURRENCY . (($Total == '') ? '0.00' : $Total) . CONF_CURRENCY_RIGHT . '</th><th>' . CONF_CURRENCY . number_format($saleSummary, 2) . CONF_CURRENCY_RIGHT . '</th></tr></table>';
        echo '<div class="title "> ' . $company_name . ' ';
        ?>
        <div style="float:right;"><a onclick="totalCoupon('puchased');
                        setTimeout(function () {
                            $('#companyVoucherDiv').show();
                            $(this).addClass('selected');
                        }, 100);" href="javascript:void(0)" title=" <?php echo t_lang('M_TXT_TOTAL_COUPON_TOOLTIP'); ?>" ><?php echo t_lang('M_TXT_BACK'); ?></a> </div></div>
        <?php
        echo'<div class="content">' . $reg_status1 . '</div>';
        break;
    case 'dealPurchased':
        //Today dealPurchased
        $comDate = date("Y-m-d H:m:s", mktime(23, 0, 0, date('m'), date('d') - 1, date('Y')));
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_deleted', '=', 0);
        $srch->addCondition('o.order_date', '>', $comDate);
        $srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
        $srch->addMultipleFields(array('d.deal_id', 'deal_side_deal', 'deal_name', 'deal_status',
            'deal_start_time', 'deal_end_time', 'c.city_name', 'company.company_name',
            'd.deal_tipped_at', 'od.od_qty', 'od.od_gift_qty'
        ));
        $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'od');
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status=1', 'o');
        $srch->addGroupBy('d.deal_id');
        $rs_listing = $srch->getResultSet();
        //echo $srch->getQuery();
        $reg_status .= ' <table width="100%" class="tbl_data">
								<tr>
								<th width="40%">' . t_lang('M_TXT_DEAL_NAME') . '</th>
								<th width="15%">' . t_lang('M_TXT_CITY_NAME') . '</th>
								<th width="15%">' . t_lang('M_FRM_COMPNAY_NAME') . '</th>
								<th width="15%">' . t_lang('M_TXT_DEAL_TIPPED') . '</th>
								<th width="15%">' . t_lang('M_TXT_DEAL_MEMBER') . '</th>	 
								</tr>';
        while ($row = $db->fetch($rs_listing)) {
            $reg_status .= '<tr>
								<td>' . $row['deal_name' . $_SESSION['lang_fld_prefix']] . '</td>
								<td>' . $row['city_name' . $_SESSION['lang_fld_prefix']] . '</td>
								<td>' . $row['company_name' . $_SESSION['lang_fld_prefix']] . '</td>
								<td>' . displayDate($row['deal_tipped_at'], true) . '</strong></td>
								<td>';
            if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                $reg_status .= '<ul class="actions"><li><a href="tipped-members.php?deal_id=' . $row['deal_id'] . '"  title="' . t_lang('M_TXT_TIPPED_MEMBERS') . '"><i class="ion-person-stalker icon"></i></a></li></ul>';
            }
            $reg_status .= '</td>
								</tr>';
        }
        if ($db->total_records($rs_listing) == 0)
            $reg_status .= '<tr><td colspan="5">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        $reg_status .= '</table>';
        echo '<div class="title"> ' . t_lang('M_TXT_TODAY_DEAL_PURCHASED') . ' </div><div class="content">' . $reg_status . '</div>';
        break;
    case 'saved':
        $srch = new SearchBase('tbl_companies', 'c');
        $srch->joinTable('tbl_company_coupon_purchased', 'INNER JOIN', 'c.company_id=ccp.ccp_company_id', 'ccp');
        $rs_listingdeal = $srch->getResultSet();
        $arr_listing_fields = array(
            'listserial' => 'S.N.',
            'company_name' => t_lang('M_TXT_COMPANY'),
            'ccp_coupon' => t_lang('M_TXT_VOUCHER_SOLD'),
            'ccp_amount' => t_lang('M_TXT_PRICE')
        );
        $reg_status .= '<table class="tbl_data" width="100%">
<thead>
<tr>';
        foreach ($arr_listing_fields as $val)
            $reg_status .= '<th>' . $val . '</th>';
        $reg_status .= '</tr></thead>';
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listingdeal); $listserial++) {
            $reg_status .= '<tr>';
            foreach ($arr_listing_fields as $key => $val) {
                $reg_status .= '<td>';
                switch ($key) {
                    case 'listserial':
                        $reg_status .= $listserial;
                        break;
                    case 'ccp_coupon':
                        $reg_status .= $row['ccp_coupon'];
                        $total_Coupon += $row['ccp_coupon'];
                        break;
                    case 'ccp_amount':
                        $reg_status .= $row['ccp_amount'];
                        $total_Amount += $row['ccp_amount'];
                        break;
                    default:
                        $reg_status .= $row[$key];
                        break;
                }
                $reg_status .= '</td>';
            }
            $reg_status .= '</tr>';
        }
        if ($db->total_records($rs_listingdeal) == 0) {
            $reg_status .= '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        } else {
            $reg_status .= '<tr><td>&nbsp;</td><td>' . t_lang('M_TXT_TOTAL_VOUCHERS_AND_AMOUNT') . '</td><td>' . $total_Coupon . '</td><td>' . $total_Amount . '</td></tr>';
        }
        $reg_status .= '</table>';
        echo '<div class="title"> Vouchers Updated By The Merchants </div><div class="content">' . $reg_status . '</div>';
        break;
    case 'savedByMerchant':
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_deleted', '=', 0);
        $cnd = $srch->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
        $srch1 = new SearchBase('tbl_order_deals', 'od');
        $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'od');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
        $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) AS totalAmount");
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        $totalAmount1 = $row['totalAmount'];
        $voucherSold = $row['sold'];
        $page = (is_numeric($post['page']) ? $post['page'] : 1);
        $pagesize = 15;
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_deleted', '=', 0);
        $cnd = $srch->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
        $srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs_listingdeal = $srch->getResultSet();
        $pages = $srch->pages();
        $pagestring .= '<div class="pagination" style=""><ul>';
        $pageStringContent .= '<a href="javascript:void(0);">Displaying records ' . (($page - 1) * $pagesize + 1) .
                ' to ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' of ' . $srch->recordCount() . '</a>';
        $pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO_PAGE') . ' </a></li>
			' . getPageString('<li><a href="javascript:void(0);" onclick="totalSavedByMerchantPagination(\'savedByMerchant\',xxpagexx);">xxpagexx</a> </li> '
                        , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>', '<li>...</li>');
        $pagestring .= '</div>';
        $arr_listing_fields = array(
            'listserial' => t_lang('M_TXT_SR_NO'),
            'deal_name' => t_lang('M_FRM_NAME'),
            'company_name' => t_lang('M_TXT_COMPANY'),
            'city_name' => t_lang('M_FRM_CITY'),
            'sold' => t_lang('M_TXT_VOUCHER_SOLD'),
            'price' => t_lang('M_TXT_PRICE')
        );
        //  $reg_status.= $pagestring;
        $reg_status .= '<table class="tbl_data table " width="100%" >
			<thead> <tr>';
        $TotalCoupon = 0;
        $Total = 0;
        foreach ($arr_listing_fields as $val)
            $reg_status .= '<th>' . $val . '</th>';
        $reg_status .= '</tr></thead>';
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listingdeal); $listserial++) {
            $price = $row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
            //   if ($price > 0) {
            $srch1 = new SearchBase('tbl_order_deals', 'od');
            $srch1->addCondition('od.od_deal_id', '=', $row['deal_id']);
            $srch1->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
            $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
            $srch1->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
            $srch1->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date>'" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
            $srch1->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN (od.od_qty+od.od_gift_qty)*od.od_deal_price ELSE 0 END) AS dealAmount");
            $rs1 = $srch1->getResultSet();
            if (!$row_sold = $db->fetch($rs1))
                $row_sold = array('sold' => 0, 'payment_pending' => 0);
            $reg_status .= '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '>';
            foreach ($arr_listing_fields as $key => $val) {
                $reg_status .= '<td>';
                switch ($key) {
                    case 'listserial':
                        $reg_status .= $listserial;
                        break;
                    case 'sold':
                        $reg_status .= $row_sold['sold'];
                        $TotalCoupon += $row_sold['sold'];
                        break;
                    case 'price':
                        if ($row_sold['sold'] > 0) {
                            $reg_status .= CONF_CURRENCY . number_format($row_sold['dealAmount'], 2) . CONF_CURRENCY_RIGHT;
                            $Total += $price * $row_sold['sold'];
                        }
                        break;
                    default:
                        $reg_status .= $row[$key];
                        break;
                }
                $reg_status .= '</td>';
            }
            $reg_status .= '</tr>';
            // }
        }
        $reg_status .= '';
        if ($db->total_records($rs_listingdeal) == 0) {
            $reg_status .= '<tr><td colspan="5">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr></table>';
        } else {
            $reg_status .= '<table class="tbl_data" width="100%" style="border: 1px solid rgb(222, 222, 222);padding-left:20px;"><thead><tr><th colspan="2" >&nbsp;</th><th>' . t_lang('M_TXT_TOTAL_VOUCHERS') . '</th><th>' . $voucherSold . '</th><th>' . t_lang('M_TXT_TOTAL_AMOUNT') . '</th><th>' . CONF_CURRENCY . number_format($totalAmount1, 2) . CONF_CURRENCY_RIGHT . '</th></tr>';
        }
        $reg_status .= '</table>';
        echo '<div class="title"> ' . t_lang('M_TXT_ACTUAL_VOUCHER_PURCHASED_AMOUNT') . ' </div><div class="content">' . $reg_status . '</div>';
        if ($srch->pages() > 1) {
            echo '<div class="footinfo">';
            echo '<aside class="grid_1">';
            echo $pagestring;
            echo '</aside>';
            echo '<aside class="grid_2"><span class="info">' . $pageStringContent . '</span></aside>';
        }
        break;
    case 'alldealPurchased':
        /*         * ***
          Instruction :
          While making any change in this section, please make sure that changes are also made in "all-deals-purchased.php" file
         * * */
        //Today dealPurchased
        $post = getPostedData();
        $comDate = date("Y-m-d H:m:s", mktime(23, 0, 0, date('m'), date('d') - 1, date('Y')));
        //Search Form
        $rsc = $db->query("SELECT  company_id, company_name  FROM `tbl_companies` WHERE company_active=1 and company_deleted = 0");
        $companyArray = [];
        while ($arrs = $db->fetch($rsc)) {
            $companyArray[$arrs['company_id']] = htmlentities($arrs['company_name'], ENT_QUOTES, 'UTF-8');
        }
        $dealrsc = $db->query("SELECT  deal_id, deal_name  FROM `tbl_deals` WHERE  deal_deleted = 0");
        $dealArray = [];
        while ($arrs = $db->fetch($dealrsc)) {
            $dealArray[htmlentities($arrs['deal_name'], ENT_QUOTES, 'UTF-8')] = $arrs['deal_name'];
        }
        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
        $Src_frm = new Form('Src_frm', 'Src_frm');
        $Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
        $Src_frm->setFieldsPerRow(3);
        $Src_frm->captionInSameCell(true);
        //$Src_frm->addTextBox('Company Name', 'company_name', '', '','');
        $Src_frm->addSelectBox(t_lang('M_FRM_COMPNAY_NAME'), 'company_name', $companyArray, $value, '', 'Select', 'company_name');
        //$Src_frm->addTextBox('Deal Name', 'deal_name', '', '','');
        //	$dealArray = array_map("addslashes", $dealArray);
        $Src_frm->addSelectBox(t_lang('M_TXT_DEAL_NAME'), 'deal_name', $dealArray, $value, '', 'Select', 'deal_name');
        $Src_frm->addTextBox(t_lang('M_TXT_USER_NAME'), 'user_name', '', '', '');
        $Src_frm->addTextBox(t_lang('M_TXT_VOUCHER_CODE'), 'order_id', '', '', '');
        $Src_frm->addTextBox(t_lang('M_FRM_EMAIL_ADDRESS'), 'user_email', '', '', '');
        $Src_frm->addHiddenField('', 'mode1', 'search');
        /* $Src_frm->addHiddenField('','refund', 1 ,'refund'); */
        $fld1 = $Src_frm->addButton('', 'btn_search', t_lang('M_TXT_CLEAR_SEARCH'), '', '  onclick=location.href="index.php?mode=cancel"');
        $fld = $Src_frm->addSubmitButton('', 'btn_cancel', t_lang('M_TXT_SEARCH'), '', '')->attachField($fld1);
        $srch = new SearchBase('tbl_order_deals', 'od');
        //paging
        $srch->setPageSize(15);
        $page = ($post['page'] > 1) ? $post['page'] : 1;
        $srch->setPageNumber($page);
        //pagingtbl_coupon_mark
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
        $srch->addCondition('order_payment_status', '>', 0);
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'd');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'c.company_id=d.deal_company', 'c');
        $srch->joinTable('tbl_coupon_mark', 'LEFT JOIN', 'cm.cm_order_id=od.od_order_id AND cm_deal_id = od_deal_id', 'cm');
        $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
        if ($post['mode1'] == 'search' AND!isset($post['btn_cancel'])) {
            #$cnd=$srch->addDirectCondition('0');
            if ($post['order_id'] != '') {
                $id = $post['order_id'];
                $length = strlen($id);
                if ($length > 13) {
                    $order_id = substr($id, 0, 13);
                    $LastVouvherNo = ($length - 13);
                    $voucher_no = substr($id, 13, $LastVouvherNo);
                } else {
                    $order_id = $post['order_id'];
                }
                #$keyword = explode('-',$post['keyword']);
                $cnd = $srch->addDirectCondition('0');
                $cnd->attachCondition('od_order_id', '=', $order_id, 'OR ');
                $cnd->attachCondition('od_voucher_suffixes', 'like', '%' . $voucher_no . '%', 'AND');
            }
            if ($post['user_email'] != '') {
                $cnd = $srch->addDirectCondition('0');
                $cnd->attachCondition('u.user_email', '=', $post['user_email'], 'OR');
                #$cnd->attachCondition('u.user_email',  'like', '%' .$post['user_email'] . '%' ,'OR');
            }
            if ($post['company_name'] != '') {
                $cnd = $srch->addDirectCondition('0');
                $cnd->attachCondition('d.deal_company', '=', $post['company_name'], 'OR');
                #$cnd->attachCondition('c.company_name',  'like', '%' . $post['company_name']. '%' ,'OR');
            }
            if ($post['deal_name'] != '') {
                $deal = addslashes(html_entity_decode($post['deal_name'], ENT_QUOTES, 'UTF-8'));
                $cnd = $srch->addDirectCondition('0');
                $cnd->attachCondition('d.deal_name' . $_SESSION['lang_fld_prefix'], 'like', $deal, 'OR');
                #$cnd->attachCondition('d.deal_name', 'like', '%' . $post['deal_name'] . '%' ,'OR');
            }
            if ($post['user_name'] != '') {
                $cnd = $srch->addDirectCondition('0');
                $cnd->attachCondition('u.user_name', '=', $post['user_name'], 'OR');
                #$cnd->attachCondition('u.user_name', 'like', '%' . $post['user_name'] . '%' ,'OR');
            }
            $Src_frm->fill($post);
        }
        $srch->addCondition('od.od_voucher_suffixes', '!=', '');
        $srch->addOrder('o.order_date', 'desc');
        $srch->addMultipleFields(array('cm.cm_id, cm_counpon_no as od_voucher_suffixes,company_name,deal_name' . $_SESSION["lang_fld_prefix"] . ',company_name' . $_SESSION["lang_fld_prefix"] . ',user_name,o.order_id,user_email,od_qty,o.order_date,od_to_name,order_payment_status,od_gift_qty'));
        // $srch->addGroupBy('od_voucher_suffixes');
        //echo $srch->getQuery();
        $rs_listing = $srch->getResultSet();
        //paging
        $pagestring = '';
        $pagesize = 15;
        $pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page'), array('page' => ''));
        $pagestring .= '<div class="pagination "><ul>';
        $pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) . ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
        $pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> ', $srch->pages(), $page, '<li class="selected"><a href="javascript:void(0);"  class="active">xxpagexx</a></li>', '<li>...</li>');
        $pagestring .= '</div><div class="clear"></div>';
        //paging
        $arr_listing_fields = array(
            /* 'listserial'=>'S.N.', */
            'company_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_COMPNAY_NAME'),
            'deal_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_DEAL') . '/' . t_lang('M_TXT_PRODUCT') . ' ' . t_lang('M_TXT_NAME'),
            'user_name' => t_lang('M_TXT_USER_NAME'),
            'order_id' => t_lang('M_TXT_VOUCHER_CODE'),
            'user_email' => t_lang('M_FRM_EMAIL_ADDRESS'),
            'od_qty' => t_lang('M_TXT_QUANTITY'),
            'order_date' => t_lang('M_TXT_ORDRED_DATE'),
            'od_to_name' => t_lang('M_TXT_GIFTED_TO_FRIEND'),
            /* 'order_payment_mode'=>t_lang('M_TXT_MODE_OF_PAYMENT'), */
            'order_payment_status' => t_lang('M_TXT_PAYMENT_STATUS'),
                /* 'action' => t_lang('M_TXT_ACTION') */
        );
        echo '<div class="box searchform_filter">  <div class="title"> ' . t_lang('M_TXT_SEARCH_VOUCHERS') . ' </div><div class="content togglewrap" style="display:none;">' . $Src_frm->getFormHtml() . '</div></div>';
        echo '<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tbl_data table companieslist table-striped">
	 <tbody><tr>';
        foreach ($arr_listing_fields as $key => $val) {
            echo '<th ' . (( $key == 'deal_name') ? ' width="20%" ' : 'width="8%"') . '>' . $val . '</th>';
        }
        for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
            $row['od_to_name'] = htmlentities($row['od_to_name'], ENT_QUOTES, 'UTF-8');
            $row['deal_name'] = htmlentities($row['deal_name'], ENT_QUOTES, 'UTF-8');
            if (($row['od_qty'] + $row['od_gift_qty']) > 0) {
                $od_voucher_suffixes = explode(', ', $row['od_voucher_suffixes']);
                foreach ($od_voucher_suffixes as $voucher) {
                    echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '  id="' . $row['order_id'] . $voucher . '" >';
                    foreach ($arr_listing_fields as $key => $val) {
                        $order_id = $row['order_id'];
                        echo '<td ' . (( $key == 'deal_name') ? ' width="20%" ' : 'width="8%"') . '>';
                        switch ($key) {
                            case 'order_payment_status':
                                if ($row[$key] == 1) {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_PAID') . '</span>';
                                } else if ($row[$key] == 0) {
                                    echo '<span class="label label-info">' . t_lang('M_TXT_PENDING') . '</span>';
                                } else {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_REFUND') . '</span>';
                                }
                                break;
                            case 'order_id':
                                if (($row['od_qty']) > 0) {
                                    echo $row['order_id'] . $voucher;
                                } else if (($row['od_gift_qty']) > 0) {
                                    echo $row['order_id'] . $voucher;
                                } else {
                                    echo $row['order_id'] . $voucher;
                                }
                                break;
                            case 'od_qty':
                                echo '1';
                                break;
                            case 'action':
                                $srch1 = new SearchBase('tbl_coupon_mark', 'cm');
                                $srch1->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id='" . $row['order_id'] . "' AND od.od_voucher_suffixes LIKE CONCAT('%', " . $voucher . ", '%')", 'od');
                                $srch1->addCondition('cm.cm_status', '=', 1);
                                $rs_listing1 = $srch1->getResultSet();
                                if ($db->total_records($rs_listing1) == 0) {
                                    if ($row['order_payment_status'] == 1) {
                                        $order = "'" . $row['order_id'] . $voucher . "'";
                                        $order_id = "'" . $row['order_id'] . "'";
                                        if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                            echo '&nbsp;<a href="javascript:void(0);" title="' . t_lang('M_TXT_REFUND') . '" onclick="return checkRefundAbility(' . $order . ');" class="btn delete">' . t_lang('M_TXT_REFUND') . '</a> ';
                                        }
                                    }
                                }
                                break;
                            default:
                                echo $row[$key];
                                break;
                        }
                        echo '</td>';
                    }
                    echo '</tr>';
                }
            }
        }
        if ($db->total_records($rs_listing) == 0)
            echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        echo ' </tbody></table>';
        if ($srch->pages() > 1) {
            echo '<div class="footinfo">';
            echo '<aside class="grid_1">';
            echo $pagestring;
            echo '</aside>';
            echo '<aside class="grid_2"><span class="info">' . $pageStringContent . '</span></aside>';
        }
        echo '</div>';
        break;
    case 'dealExpire':
        //Today dealexpire
        $comDate = date("Y-m-d H:m:s", mktime(23, 0, 0, date('m'), date('d') - 1, date('Y')));
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('deal_deleted', '=', 0);
        $srch->addCondition('deal_end_time', '<', date("Y-m-d H:m:s"));
        $srch->addCondition('deal_end_time', '>', $comDate);
        $rs_listing = $srch->getResultSet();
        $reg_status .= ' <table width="100%" class="tbl_data">
                            <tr>
                                <th width="40%">' . t_lang('M_TXT_DEAL_NAME') . '</th>
                                <th width="20%">' . t_lang('M_TXT_DEAL_TIPPED') . '</th>	 
                            </tr>';
        while ($row = $db->fetch($rs_listing)) {
            $reg_status .= '<tr>
                                <td>' . $row['deal_name' . $_SESSION['lang_fld_prefix']] . '</td>
                                <td>' . displayDate($row['deal_tipped_at'], true) . '</strong></td>
                            </tr>';
        }
        if ($db->total_records($rs_listing) == 0)
            $reg_status .= '<tr><td colspan="2">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        $reg_status .= '</table>';
        echo '<div class="title"> ' . t_lang('M_TXT_TODAY_EXPIRED_DEALS') . '</div><div class="content">' . $reg_status . '</div>';
        break;
    case 'charity':
        $srch = new SearchBase('tbl_company_charity', 'cc');
        $srch->joinTable('tbl_companies', 'INNER JOIN', 'cc.charity_company_id=company.company_id', 'company');
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'company.company_id=d.deal_company', 'd');
        $srch->addMultipleFields(array('charity_name', 'd.deal_id', 'charity_percentage', 'company.company_name', 'd.deal_name', 'd.deal_tipped_at', 'od.od_deal_price'
        ));
        $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'od');
        $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status=1', 'o');
        $probation_time = date('Y-m-d H:i:s', strtotime(ORDER_PROBATION_TIME));
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date>'" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
        $srch->addGroupBy('cc.charity_company_id');
        $rs_listing = $srch->getResultSet();
        $reg_status .= ' <table width="100%" class="tbl_data">
                            <tr>
                            <th width="15%">Charity Name</th>
                            <th width="15%">Company Name</th>	 
                            <th width="30%">Deal Name</th>	 
                            <th width="10%">Deal Tipped</th>
                            <th width="10%">Price</th>								
                            <th width="5%">Percentage</th>
                            <th width="5%">Quantity</th>
                            <th width="5%">Charity</th>

                            </tr>';
        $price = 0;
        $charityPrice = 0;
        while ($row = $db->fetch($rs_listing)) {
            $deal_id = $row['deal_id'];
            $od_deal_price = $row['od_deal_price'];
            $price = ($price + $row['od_deal_price']);
            $reg_status .= '<tr>
                                <td>' . $row['charity_name'] . '</td>
                                <td>' . $row['company_name'] . '</td>
                                <td>' . $row['deal_name'] . '</td>
                                <td>' . displayDate($row['deal_tipped_at'], true) . '</strong></td>
                                <td>' . $row['od_deal_price'] . '</td>
                                <td>' . $row['charity_percentage'] . '</td>
                                <td>' . $row['sold'] . '</td>';
            if (displayDate($row['deal_tipped_at'], true) != "") {
                $charityPrice = $charityPrice + ((($row['od_deal_price'] * $row['sold']) * $row['charity_percentage']) / 100);
                $reg_status .= '<td>' . ((($row['od_deal_price'] * $row['sold']) * $row['charity_percentage']) / 100) . '</td>';
            } else {
                $reg_status .= '<td>---</td>';
            }
            $reg_status .= '</tr>';
        }
        $reg_status .= '<tr>
								<td colspan="8" align="right">Total Charity Price = ' . $charityPrice . ' <br/>   </td> </tr>';
        if ($db->total_records($rs_listing) == 0)
            $reg_status .= '<tr><td colspan="2">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
        $reg_status .= '</table>';
        echo $reg_status;
        break;
    case 'checkforrefund':
        $length = strlen($post['v']);
        if ($length > 13) {
            $order_no = substr($post['v'], 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($post['v'], 13, $LastVouvherNo);
        } else {
            die(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $srch = new SearchBase('tbl_coupon_mark');
        $srch->addCondition('cm_order_id', '=', $order_no);
        $srch->addMultipleFields(array('cm_counpon_no', 'cm_status'));
        $rs = $srch->getResultSet();
        $used = 0;
        $unused = 0;
        $refunded = 0;
        while ($row = $db->fetch($rs)) {
            if (intval($row['cm_status']) === 3)
                $refunded++;
            else if (intval($row['cm_status']) === 1)
                $used++;
            else
                $unused++;
        }
        $patch = '';
        $resp = '';
        if ($refunded > 0) {
            $resp .= 'Some of the vouchers related to the order are already refunded<br />';
            $patch .= ' remaining';
            $refund = $row['od_order_id'];
        }
        if ($used > 0) {
            $resp .= 'Some of the vouchers related to the order are already Used<br />';
            $patch .= ' except used';
            $refund = $row['od_order_id'];
        }
        if (isset($post['req']) && $post['req'] == 'dashboard') {
            if ($unused > 1) {
                $resp .= '<a class="btn green" href="javascript:void(0)" onclick="if(requestPopup(this,\'' . t_lang('M_TXT_REFUND_ALEART') . '\',1)) doRefundVoucher(\'' . $order_no . '\');">Refund all' . $patch . ' vouchers related to the order</a><br />';
            }
            $resp .= '<a class="btn green" href="javascript:void(0)" onclick="if(requestPopup(this,\'' . t_lang('M_TXT_REFUND_ALEART') . '\',1)) doRefundVoucher(\'' . $order_no . $voucher_no . '\');">Refund ' . $order_no . $voucher_no;
        } else {
            $deal = intval($post['d']);
            $page = intval($post['p']);
            if (intval($post['d']) <= 0)
                $deal = '';
            if (intval($post['p']) <= 0)
                $page = '';
            if ($unused > 1) {
                $resp .= '<a class="btn green" href="?deal_id=' . $deal . '&refund=' . $order_no . '&page=' . $page . '" onclick="requestPopup(this,\'' . t_lang('M_TXT_REFUND_ALEART') . '\',1);">Refund all' . $patch . ' vouchers related to the order</a><br />';
            }
            $resp .= '<a class="btn green" href="?deal_id=' . $deal . '&refund=' . $order_no . $voucher_no . '&page=' . $page . '" onclick="requestPopup(this,\'' . t_lang('M_TXT_REFUND_ALEART') . '\',1);">Refund ' . $order_no . $voucher_no;
        }
        die($resp);
        break;
    case 'dorefundvoucher':
        if ($post['v'] != "" && strlen(trim($post['v'])) > 1) {
            $order_id = $post['v'];
            /* function is placed in the site function.php and this function also used in the tipped-members.php file for refund */
            voucherRefund($order_id);
            #redirectUser('index.php?page='.$post['page']);
            die($msg->display());
        }
        break;
    case 'ShippingDetails':
        $length = strlen($post['v']);
        if ($length > 13) {
            $order_no = substr($post['v'], 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($post['v'], 13, $LastVouvherNo);
        } else {
            die(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $srch = new SearchBase('tbl_coupon_mark');
        $srch->addCondition('cm_order_id', '=', $order_no);
        $srch->addCondition('cm_counpon_no', '=', $voucher_no);
        $srch->addMultipleFields(array('cm_counpon_no', 'cm_order_id', 'cm_status', 'cm_id', 'cm_shipping_status', 'cm_shipping_date', 'cm_shipping_details'));
        $rs = $srch->getResultSet();
        if (!$row = $db->fetch($rs)) {
            die(t_lang('M_ERROR_NO_RECORD_FOUND'));
        }
        $row['cm_shipping_date'] = date('d-m-Y', strtotime($row['cm_shipping_date']));
        $frm = new Form('frmShipping', 'frmShipping');
        $frm->setValidatorJsObjectName('frmShippingValidator');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" class="tbl_form"');
        $frm->setRequiredStarWith('caption');
        $arr_options = array(
            0 => 'Pending',
            1 => 'Shipped',
            2 => 'Delivered'
        );
        $frm->addSelectBox('Shipping Status', 'cm_shipping_status', $arr_options, '', '', '', '');
        $frm->addDateField('Shipping Date', 'cm_shipping_date', '', '', '');
        $frm->addTextArea('Shipping Detail/Information', 'cm_shipping_details', '', '', 'rows = "8" cols="50"');
        $arr_options = array(
            $row['cm_order_id'] => 'All Vouchers Related to the Order',
            $row['cm_order_id'] . $row['cm_counpon_no'] => 'Only for Voucher ' . $row['cm_order_id'] . $row['cm_counpon_no']
        );
        $frm->addRadioButtons('Effect to', 'shipping_effect_to', $arr_options, '', '', '', '')->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit', '');
        $frm->addHiddenField('', 'mode', 'saveShippingDetails', 'saveShippingDetails', '');
        $frm->addHiddenField('', 'cm_order_id', '', '', '');
        $frm->addHiddenField('', 'cm_counpon_no', '', '', '');
        $frm->setOnSubmit('shippingSubmit(this, frmShippingValidator); return(false);');
        if ($row['cm_shipping_date'] == '0000-00-00' || $row['cm_shipping_date'] == '01-01-1970' || $row['cm_shipping_date'] == '30-11--0001') {
            unset($row['cm_shipping_date']);
        }
        $frm->fill($row);
        die($frm->getFormHtml());
        break;
    case 'saveShippingDetails':
        $order_id = $post['cm_order_id'];
        $coupon_no = $post['cm_counpon_no'];
        $shipping_effect_to = $post['shipping_effect_to'];
        if (!isset($order_id) || !isset($coupon_no) || $order_id == '' || $coupon_no == '') {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $length = strlen($shipping_effect_to);
        if ($length > 13) {
            $order_no = substr($shipping_effect_to, 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($shipping_effect_to, 13, $LastVouvherNo);
        } else if ($length == 13) {
            $order_no = $shipping_effect_to;
        } else {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
            return false;
        }
        $data_to_update = array(
            'cm_shipping_status' => $post['cm_shipping_status'],
            'cm_shipping_details' => $post['cm_shipping_details']
        );
        if ($post['cm_shipping_date'] == '') {
            $data_to_update['cm_shipping_date'] = '0000-00-00';
        } else {
            $data_to_update['cm_shipping_date'] = date('Y-m-d', strtotime($post['cm_shipping_date']));
        }
        if ($length > 13) {
            $arr_whr = array('smt' => 'cm_order_id = ? AND cm_counpon_no = ?', 'vals' => array($post['cm_order_id'], $post['cm_counpon_no']), 'execute_mysql_functions' => false);
        }
        if ($length == 13) {
            $arr_whr = array('smt' => 'cm_order_id = ?', 'vals' => array($post['cm_order_id']), 'execute_mysql_functions' => false);
        }
        $db->update_from_array('tbl_coupon_mark', $data_to_update, $arr_whr, true);
        switch ($post['cm_shipping_status']) {
            case '0':
                $shipping_status_text = 'Pending';
                break;
            case '1':
                $shipping_status_text = 'Shipped';
                break;
            case '2':
                $shipping_status_text = 'Delivered';
                break;
            default:
                $shipping_status_text = '';
        }
        $arr = array('status' => 1, 'msg' => t_lang('M_TXT_SHIPPING_DETAILS_UPDATED'), 'shipping_status_text' => $shipping_status_text);
        if ($length == 13) {
            $arr['page_reload'] = 1;
        } else {
            $arr['page_reload'] = 0;
        }
        die(convertToJson($arr));
        break;
    case 'sendlinkForm':
        $frm = new Form('user_msg_form', 'user_msg_form');
        $frm->setValidatorJsObjectName('frmSendlinkValidator');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" class="tbl_form"');
        $frm->setRequiredStarWith('caption');
        $frm->addRequiredField(t_lang('M_FRM_EMAIL_ADDRESS'), 'recipients', $post['useremail'], 'recipients');
        $frm->addTextArea(t_lang('M_FRM_YOUR_MESSAGE_SUCCESS_PAGE'), 'email_message', '', 'email_message', 'rows = "5" cols="50"')->requirements()->setRequired();
        $frm->addHiddenField('', 'email_subject', 'email_subject', 'email_subject');
        $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SEND'), 'btn_submit', '');
        $frm->setOnSubmit('sendLinkInfoSubmit(this.email_subject.value, this.recipients.value, this.email_message.value,frmSendlinkValidator); return(false);');
        echo $frm->getFormHtml();
        break;
    case 'sendLinkInfoSubmit':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['recipients'] != '' && $_POST['email_message']) {
                $recipients = $_POST['recipients'];
                $recipients = str_replace(' ', '', $recipients);
                $recipients_arr = explode(',', $recipients);
                $error = 0;
                foreach ($recipients_arr as $key => $val)
                    $recipients_arr[$key] = trim($val);
                foreach ($recipients_arr as $val) {
                    if (!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $val)) {
                        $error = 1;
                    }
                }
                $subject = $_POST['email_subject'];
                $rs1 = $db->query("select * from tbl_email_templates where tpl_id=48");
                $row_tpl = $db->fetch($rs1);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxuser_namexx' => $_POST['recipients'],
                    'xxdownloadablelinkxx' => $_POST['email_message'],
                    'xxrecipientxx' => $row_deal['user_name'],
                    'xxemail_addressxx' => $row_deal['user_email'],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($error != 1) {
                    foreach ($recipients_arr as $val) {
                        sendMail($_POST['recipients'], $subject . ' ' . $order_id, emailTemplateSuccess($message));
                    }
                    die(t_lang('M_TXT_MAIL_SENT'));
                } else {
                    die(t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID'));
                }
            } else {
                die(t_lang('M_ERROR_ENTER_EMAIL_ADDRESS_AND_MESSAGE'));
            }
        }
        break;
    case 'getTaxRule':
        global $db;
        if (!is_numeric($post['taxrate_id']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
        $selectedRate = $post['taxrate_id'];
        $selectedBasedOn = $post['basedOn'];
        $srch = new SearchBase('tbl_tax_rates');
        $srch->addCondition('taxrate_active', '=', '1');
        // $srch->addCondition('taxrate_id', '=', $post['country']);
        $srch->addOrder('taxrate_name', 'asc');
        $srch->addMultipleFields(array('taxrate_id', 'taxrate_name'));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        $selected = ($post['selected'] > 0) ? $post['selected'] : '0';
        $fld = new FormField('select', 'data[][taxrule_taxrate_id]', 'taxrule_taxrate_id');
        $fld->requirements()->setRequired();
        $fld->options = $arr_states;
        $fld->selectCaption = t_lang('M_TXT_SELECT');
        $fld->value = $selectedRate;
        $arrayBased0n = array("0" => "Store Address", "1" => "Billing Address", "2" => "Shipping Address");
        $fld1 = new FormField('select', 'data[][taxrule_tax_based_on]', 'taxrule_tax_based_on');
        $fld1->requirements()->setRequired();
        $fld1->options = $arrayBased0n;
        $fld1->selectCaption = t_lang('M_TXT_SELECT');
        $fld1->value = $selectedBasedOn;
        $str = '<tr><td>' . $fld->getHTML() . '</td><td>' . $fld1->getHTML() . '</td><td class="left"><a class="button" onclick="$(\'#tax-rule-row0\').remove();">Remove</a></td><tr>';
        echo $str;
        break;
    case 'getTaxRate':
        global $db;
        $id = [];
        $id = explode($post['taxrate_id']);
        $selectedRate = $post['taxrate_id'];
        $srch = new SearchBase('tbl_tax_rates');
        $srch->addCondition('taxrate_active', '=', '1');
        $srch->addCondition('taxrate_id', 'NOT IN', explode(',', $_POST['taxrate_id']));
        $srch->addOrder('taxrate_name', 'asc');
        $srch->addMultipleFields(array('taxrate_id', 'taxrate_name'));
        $rs = $srch->getResultSet();
        $arr_option = $db->fetch_all($rs);
        if (empty($arr_option)) {
            $options = '<option value="">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</option>';
        }
        foreach ($arr_option as $option_value) {
            $options .= '<option value="' . $option_value['taxrate_id'] . '">' . $option_value['taxrate_name'] . '</option>';
        }
        die($options);
        break;
    case 'getBasedOnOption':
        $selectedRate = $post['taxrate_id'];
        $srch = new SearchBase('tbl_tax_rates');
        $srch->addCondition('taxrate_active', '=', '1');
        // $srch->addCondition('taxrate_id', '=', $post['country']);
        $srch->addOrder('taxrate_name', 'asc');
        $srch->addMultipleFields(array('taxrate_id', 'taxrate_name'));
        $rs = $srch->getResultSet();
        $arr_option = $db->fetch_all_assoc($rs);
        return $arr_option;
        break;
    case 'deleteTaxRuleRecord':
        $taxrule_id = intval($post['taxrule_id']);
        $whr = array('smt' => 'taxrule_id = ?', 'vals' => array($taxrule_id));
        if ($db->deleteRecords('tbl_tax_rules', $whr)) {
            dieJsonSuccess(t_lang('M_TXT_RECORD_DELETED'));
        } else {
            $db->getError();
            $arr = array('status' => 0, 'msg' => $db->getError());
            die(convertToJson($arr));
        }
        break;
}		