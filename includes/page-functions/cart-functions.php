<?php 


function fetchdealInfo11($deal_id){
	/*Not used*/
	global $db;
	$city_to_show = '';
	if ($_SESSION['lang_fld_prefix'] == '_lang1')
	$city_to_show = ',city_name_lang1';
	$query = "select d.*, city_name" . $city_to_show . " from tbl_deals d,tbl_cities c where c.city_id=d.deal_city AND deal_id=" . intval($deal_id);
	$rs = $db->query($query);
	$row= $db->fetch($rs);
	if($row){
		return $row;	
	}else{
		return false;
	}	
}

function addUpdateBookingDates($data=array(), &$error){
	global $db;
	$record = new TableRecord('tbl_cart_item_booking_dates');
	$record->assignValues($data,false);
	if(!$record->addNew()) {
		$error = $db->getError();
		return false;
	}
	return true;
}	
function addUpdateCartItem($data=array(), &$error=""){
	//
	if(empty($data)){
		return false;
	}
	global $db;
	$record = new TableRecord('tbl_cart_items');
	$record->assignValues($data,false);
	if(!isset($data['cart_item_id'])) {
		if(!$record->addNew()) {
			$error = $db->getError();
			return false;
		}
		return $record->getId();
     } else {
		 error_reporting(0);
		if(!$record->update(array('smt' =>'cart_item_id=?', 'vals' => array($data['cart_item_id'])))) {
				$error = $db->getError();
				return false;
			}
			return true;
	}
}


function checkExistingCartItem($data){

    $srch = new SearchBase('tbl_cart_items', 'ct');
	if(isset($data['cart_item_option'])){
		$srch->addCondition('ct.cart_item_option', 'LIKE', $data['cart_item_option'], 'AND');
	}	
	$srch->addCondition('ct.cart_item_deal_id', '=', $data['cart_item_deal_id']);
	$srch->addCondition('ct.cart_item_sub_deal_id', '=', $data['cart_item_sub_deal_id']);
	$srch->addCondition('ct.cart_item_user_id', '=', $data['cart_item_user_id']);
	$srch->getResultSet();
	if($srch->recordCount()>0){
		return true;
	}
	else{
		return false;
	}
}



	
function loadValidItemData($deal_id=0, $deal_cart=array(), $new_qty = 0, $chqQty = 0, &$error) {
	// @newQuantity= updated Qty
		// @chqQty= new quantity of selected product + existing qty of another same product in cart
	if(empty($deal_cart)){
		return false;
	}
	$subdeals = "";
	if (isset($deal_cart['cart_item_option'])) {
		$deal_options = unserialize(base64_decode($deal_cart['cart_item_option']));
		if (!empty($deal_options)) {
		
		$productAttributeAvailable = checkProductQuantityAvaiable($deal_id, $deal_options);
			if (!$productAttributeAvailable) {
				return false;
			}
		}
	}
	$subdeal_id=$deal_cart['cart_item_sub_deal_id'];	
	if(isset($deal_cart['cibdt_start_date']) && !empty($deal_cart['cibdt_end_date'])){
		$checkbookingQtyAvailable= getOnlineDealVoucher($deal_id,$subdeal_id,$deal_cart['cart_item_company_address_id'],$deal_cart['cibdt_start_date'],$deal_cart['cibdt_end_date']);
		
		if($checkbookingQtyAvailable < ($deal_cart['cart_item_qty'])){
		$msg->addError(t_lang('M_TXT_ONLY').' '.$checkbookingQtyAvailable.' '.t_lang('M_TXT_VOUCHER_AVAILABLE_FOR_HOTEL_BOOKING'));
		return false;
		}

	}
	if ($deal_id < 1) {
		$error= "Invalid deal Id";
		return false;
	}

	$for_single_address=false;
	if(isset($deal_cart['cart_item_company_address_id']))
	{
			$for_single_address=true;
	}
	$price = 0;
	$eligible_deal_data="";
	$eligible_company_address_for_user="";
	
	$prevQty = $deal_cart['cart_item_qty'];
       

        if (intval($new_qty) > 0)
            $deal_cart['cart_item_qty'] = intval($new_qty);
        if ($deal_cart['cart_item_qty'] < 1) {
            $deal_cart['cart_item_qty'] = 1;
        }
		
	
	$error1="";
	if ($chqQty > 0) {
		$eligible_deal_data = canBuyDeal($chqQty, $for_single_address, $price, $deal_id,$deal_cart['cart_item_company_address_id'],0,$subdeal_id, $error1);
	
	}else{
  $eligible_deal_data =canBuyDeal($deal_cart['cart_item_qty'], $for_single_address, $price, $deal_id,$deal_cart['cart_item_company_address_id'],0,$subdeal_id,$error1);

	}
	$eligible_company_address_for_user = $eligible_deal_data['address_id'];	
	if ($eligible_deal_data === false || sizeof($eligible_company_address_for_user) <= 0) {
		if ($chqQty > 0) {
			if (isset($deal_cart['cart_item_max_buy']) && $deal_cart['cart_item_max_buy'] > 0 && $deal_cart['cart_item_max_buy'] < $chqQty) {
				$error = $error1;
				$deal_cart['cart_item_qty']= $prevQty;
				return false;
			}
		}
		if (isset($deal_cart['cart_item_max_buy']) && $deal_cart['cart_item_max_buy'] > 0 && $deal_cart['cart_item_max_buy'] < $deal_cart['cart_item_qty']) {
				$error = $error1;
				$deal_cart['cart_item_qty']= $deal_cart['cart_item_max_buy'];
			
			}else{
			$this->removeItem($deal_cart['cart_item_id']);	
			}	
			return false;
	 }else {
            unset($error);
        }
	if (!isset($deal_cart['cart_item_company_address_id']) || intval($deal_cart['cart_item_company_address_id']) <= 0) {
		foreach ($eligible_company_address_for_user as $company_address_for_user) {
		  $deal_cart['cart_item_company_address_id'] = $company_address_for_user;
			break;
		}
	}
	if(isset($eligible_deal_data)){
	$deal_cart['cart_item_max_buy'] = $eligible_deal_data['max_buy'][array_search(intval($deal_cart['cart_item_company_address_id']), $eligible_deal_data['address_id'], true)];
	}
	if(is_array($deal_cart)){

	$deal_cart= filter_array($deal_cart);
	$error = "";
		if(!addUpdateCartItem($deal_cart, $error)){
			$error= $error;
			  return false;
		}	
		return true;
	}
	return true;
}

function filter_array(&$deal_cart){
	$Cart_items= array('cart_item_id','cart_item_deal_id','cart_item_sub_deal_id','cart_item_qty','cart_item_user_id','cart_item_company_address_id','cart_item_max_buy','cart_item_option');
	foreach($deal_cart as $key => $var){
		if(!in_array($key,$Cart_items)){
			unset($deal_cart[$key]);
		}	
	}
	return $deal_cart;
}




function fetchloadAllDBProducts($user_id){
	global $db;
	$srch = new SearchBase('tbl_cart_items', 'ct');
	$srch->joinTable('tbl_cart_item_booking_dates','LEFT JOIN','cibd.cibdt_item_id=ct.cart_item_id','cibd');
	$srch->joinTable('tbl_cart_item_gift_details','LEFT JOIN','cigdt.cigdt_item_id=ct.cart_item_id','cigdt');
	$srch->addCondition('ct.cart_item_user_id', '=', $user_id);
    $srch->addOrder('cart_item_id','desc');
	$rs = $srch->getResultSet();
	if($srch->recordCount()>0){
		return $db->fetch_all($rs);
	}
	else{
		return false;
	}

	
}

function getCharityInfo($deal_id, $charity_id) {

    $deal = new DealInfo($deal_id,false);
    global $db;
    $str = "";
    $rs = $db->query("select * from tbl_company_charity where charity_status=1 and charity_id=" . intval($charity_id));
    $rowCharity = $db->fetch($rs);
	$charityInfo= array();
    if ($rowCharity) {

        $charityInfo['charity_id'] = $rowCharity['charity_id'];
        $charityInfo['charity_image'] = CONF_WEBROOT_URL . "deal-image.php?charity=" . $rowCharity['charity_id'] . "&mode=charitythumbImages";
      
        $charityInfo['charity_discount']=  (($deal->getFldValue('deal_charity_discount_is_percent') == 1) ? '' : CONF_CURRENCY) . $deal->getFldValue('deal_charity_discount') . (($deal->getFldValue('deal_charity_discount_is_percent') == 1) ? '%' : '') ;
		$charityInfo['charity_name']	=	 $rowCharity['charity_name' . $_SESSION['lang_fld_prefix']] ;
		$charityInfo['charity_address']	=	 $rowCharity['charity_address1' . $_SESSION['lang_fld_prefix']] . $rowCharity['charity_address2' . $_SESSION['lang_fld_prefix']] . $rowCharity['charity_address3' . $_SESSION['lang_fld_prefix']] . $rowCharity['charity_city' . $_SESSION['lang_fld_prefix']] . "," . $rowCharity['charity_zip'] ;
    }
    return $charityInfo;
}	

function validateProductItem($data ,&$error){
	global $db;
	if(empty($data['cart_item_option'])){
			return true;
	}	
	$options = unserialize(base64_decode($data['cart_item_option']));
	$option_price = 0;
	$option_data = array();
	$optionSize = count(array_filter($options));
	if (is_array($options) && count($options)) {
		$srch = new SearchBase('tbl_deal_option', 'd_op');
		$srch->joinTable('tbl_options', 'Inner JOIN', 'o.option_id = d_op.option_id and o.is_deleted=0', 'o');
		$srch->addCondition('d_op.required', '=', 1);
		$srch->addCondition('d_op.deal_id', '=', $data['cart_item_deal_id']);
		$rs = $srch->getResultSet();
		$required_option_count = $srch->recordCount();
		if ($optionSize < $required_option_count) {
			$error= t_lang('M_TXT_YOU_HAVE_NOT_SELECTED_ALL_ATTRIBUTE');
			return false;
		}
		foreach($options as $deal_option_id => $option_value) {
		$srch = new SearchBase('tbl_deal_option', 'd_op');
		$select[] = array('op.option_id', 'op.option_name', 'op.option_name_lang1', 'op.option_type', 'd_op.deal_option_id', 'd_op.deal_id', 'd_op.required');
		$srch->joinTable('tbl_options', 'LEFT JOIN', 'op.option_id = d_op.option_id', 'op');
		$srch->joinTable('tbl_deal_option_value', 'LEFT JOIN', 'd_op.deal_option_id = dov.deal_option_id', 'dov');
		$select[] = array('dov.deal_option_value_id', 'dov.option_value_id', 'dov.quantity', 'dov.price', 'dov.price_prefix');
		$srch->addCondition('dov.deal_option_value_id', '=', $option_value);
		$srch->addCondition('dov.deal_option_id', '=', $deal_option_id);
		$srch->addCondition('dov.deal_id', '=', $data['cart_item_deal_id']);
		$srch->joinTable('tbl_option_values', 'LEFT JOIN', 'dov.option_value_id = ov.option_value_id', 'ov');
		$select[] = array('ov.name');
		$srch->addMultipleFields($select);
		$srch->addCondition('d_op.deal_option_id', '=', $deal_option_id);
		$srch->addCondition('d_op.deal_id', '=', $data['cart_item_deal_id']);
		$rs = $srch->getResultSet();
		//echo $srch->getQuery();
		$count = $srch->recordCount();
		if ($count == 0) {
			$error = t_lang('M_TXT_SOMETHING_WENT_WRONG_WITH_YOUR_PRODUCT_PLEASE_REMOVE_IT');
			return false;
		}

		 if ($deal_option = $db->fetch($rs)) {

			if ($deal_option['option_type'] == 'select' || $deal_option['option_type'] == 'radio') {

				if ($deal_option['price_prefix'] == '+') {
					$option_price += $deal_option['price'];
				} elseif ($deal_option['price_prefix'] == '-') {
					$option_price -= $deal_option['price'];
				}
				$option_data['option_price'] =$option_price;
				if ((!$deal_option['quantity'] || ($deal_option['quantity'] < $data['cart_item_qty']))) {
					$stock = false;
				}

				$option_data['data'][] = array(
					'deal_option_id' => $deal_option_id,
					'deal_option_value_id' => $option_value,
					'option_id' => $deal_option['option_id'],
					'option_value_id' => $deal_option['option_value_id'],
					'option_name' => $deal_option['option_name'],
					'option_value' => $deal_option['name'],
					'option_type' => $deal_option['option_type'],
					'quantity' => $deal_option['quantity'],
					'price' => $deal_option['price'],
					'price_prefix' => $deal_option['price_prefix'],
				);
			}//option_type select & radio ends
		} 
	}// end foreach of $options
	}
	return $option_data; 
}