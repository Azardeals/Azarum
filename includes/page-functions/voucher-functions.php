<?php 

function fetchVoucherObj($type,$purchase, $page=1, $pagesize=10){
	global $db;
	$srch = new SearchBase('tbl_orders', 'o');
	$srch->addCondition('o.order_user_id', '=', $_SESSION['logged_user']['user_id']);
	$srch->joinTable('tbl_order_shipping_details', 'LEFT OUTER JOIN', 'osd.osd_order_id = o.order_id', 'osd');
	$srch->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'osd.osd_country_id = co.country_id', 'co');
	$srch->joinTable('tbl_states', 'LEFT OUTER JOIN', 'osd.osd_state_id=state.state_id', 'state');
	//$srch->joinTable('tbl_cities', 'LEFT OUTER JOIN', 'osd.osd_city_id=city.city_id', 'city');

	$srch->addFld('CONCAT(osd_recipient_name, "\n", osd_address_line1, "\n", osd_address_line2, "\n", osd_city_name, ", ", state.state_name, ", ", co.country_name) as shipping_details');

	$srch->addFld('CASE 
			WHEN cm.cm_shipping_status = 0 THEN "PENDING"
			WHEN cm.cm_shipping_status = 1 THEN "SHIPPED"
			WHEN cm.cm_shipping_status = 2 THEN "DELIVERED"
			ELSE NULL
			END as shipping_status
		');

	$srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id', 'od');
	$srch->joinTable('tbl_digital_product_extras', 'LEFT JOIN', 'od.od_deal_id=dpe.dpe_deal_id', 'dpe');
	$srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
	$q_patch = ' AND (od_voucher_suffixes like cm_counpon_no OR od_voucher_suffixes like concat("%, ",cm_counpon_no) OR od_voucher_suffixes like concat(cm_counpon_no,",%") OR od_voucher_suffixes like concat("%, ",cm_counpon_no,",%") OR od_cancelled_voucher_suffixes like cm_counpon_no OR od_cancelled_voucher_suffixes like concat("%, ",cm_counpon_no) OR od_cancelled_voucher_suffixes like concat(cm_counpon_no,",%") OR od_cancelled_voucher_suffixes like concat("%, ",cm_counpon_no,",%"))';
	$srch->joinTable('tbl_coupon_mark', 'INNER JOIN', 'od.od_order_id = cm.cm_order_id' . $q_patch, 'cm');
	## Payment Status as per client Requirement ############
	$srch->addMultipleFields(array('d.deal_sub_type', 'd.deal_type', 'dpe.dpe_product_file_name', 'dpe.dpe_product_external_url'));
	$srch->addCondition('o.order_payment_status', 'IN', array(-1, 0, 1, 2));
	$srch->addGroupBy('cm_id');
	## Payment Status as per client Requirement ############
	//$srch->addCondition('od.od_to_email', '=', '');

	if (isset($type) && $type !='-1') {
		if (isset($type)) {
		   $srch->addCondition('d.deal_type', '=', $type);
		} else {
		   $srch->addCondition('d.deal_type', '=', 0);
		}
	}
	
	if (isset($purchase)) {
		$srch->addOrder('order_date', $purchase);
	} else {
		$srch->addOrder('order_date', 'desc');
	}


	$srch->addMultipleFields(array('o.*', 'cm.*', 'od.*', 'd.deal_tipped_at', 'd.deal_img_name', 'd.deal_type', 'd.deal_sub_type', 'od.od_id', 'd.deal_id'));
	//echo $srch->getQuery();
	$srch->setPageNumber($page);
	$srch->setPageSize($pagesize);
	return $srch;
}


function fetchRecordObj($page, $pagesize){
	$srch=new SearchBase('tbl_user_wallet_history', 'wh');
	$srch->addCondition('wh_user_id', '=', $_SESSION['logged_user']['user_id']);
	$srch->addOrder('wh_time', 'desc');
	$srch->addOrder('wh_amount', 'asc');
	$srch->addFld('wh.*');
	$srch->addFld('CASE WHEN wh_amount > 0 THEN wh_amount ELSE 0 END as added');
	$srch->addFld('CASE WHEN wh_amount <= 0 THEN ABS(wh_amount) ELSE 0 END as used');
	$srch->setPageNumber($page);
	$srch->setPageSize($pagesize);
	return $srch;
}


function purchasedhistoryBalanceObj(){
	$srch_bal=new SearchBase('tbl_user_wallet_history', 'wh');
	$srch_bal->addCondition('wh_user_id', '=', $_SESSION['logged_user']['user_id']);
	$srch_bal->addOrder('wh_time', 'desc');
	$srch_bal->addOrder('wh_amount', 'asc');
	$srch_bal->addFld('wh_amount');
	$srch_bal->doNotCalculateRecords();
	$srch_bal->doNotLimitRecords();
	return $srch_bal;
}

?>