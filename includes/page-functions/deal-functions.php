<?php 

require_once CONF_INSTALLATION_PATH.'includes/deal_attributes_functions.php';

function updateMainDealRequest($city_id, &$error) {
	global $db;
    $srchDeal = new SearchBase('tbl_deals');
    $srchDeal->addCondition('deal_city', '=', $_SESSION['city']);
    $srchDeal->addCondition('deal_status', '=', 1);
    $srchDeal->addCondition('deal_complete', '=', 1);
    $srchDeal->doNotLimitRecords();
    $srchDeal->doNotCalculateRecords();
    $rs1 = $srchDeal->getResultSet();
    $count1 = $db->total_records($rs1);
    $row1 = $db->fetch($rs1);
    if ($count1 == 1) {
        if ($row1['deal_main_deal'] == 0) {
            if (!$db->update_from_array('tbl_deals', array('deal_main_deal' => 1), 'deal_id=' . $row1['deal_id'])){
                $error = $db->getError();
				return false;
            }
        }
    }
	return true;
}

function fetchMainDealId($city_id){
	global $db;
	$srch = new SearchBase('tbl_deals', 'd');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('deal_city', '=', $city_id, 'OR');
    $cnd->attachCondition('deal_city', '=', 0);
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_status', '<', 3);
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->addCondition('deal_complete', '=', 1);
    $deal_sold = new SearchBase('tbl_order_deals', 'od');
    $deal_sold->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $deal_sold->addDirectCondition('o.order_payment_status = 1 or o.order_date > "' . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . '"');
    $deal_sold->addMultipleFields(array('od.od_deal_id', 'SUM(od.od_qty+od.od_gift_qty) AS sold'));
    $deal_sold->addGroupBy('od_deal_id');
    $deal_sold->doNotCalculateRecords();
    $deal_sold->doNotLimitRecords();
    $srch->joinTable('(' . $deal_sold->getQuery() . ')', 'LEFT OUTER JOIN', 'd.deal_id = deal_sold.od_deal_id AND sold < d.deal_max_coupons', 'deal_sold');
    $srch->addGroupBy('d.deal_id');
    $srch->addOrder('deal_city', 'desc');
    $srch->addOrder('deal_main_deal', 'desc');
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id and c.company_active=1 and c.company_deleted=0', 'c');
    /* $srch->addOrder('RAND()'); */
    $srch->doNotCalculateRecords();
    $srch->addMultipleFields(array('d.deal_id', 'deal_sold.sold', 'deal_max_coupons', 'deal_min_buy', 'deal_city', 'deal_main_deal'));
    $srch->setPageSize(10);
    $rs = $srch->getResultSet();
    $total_main_deals = $db->total_records($rs);
	if (!$row = $db->fetch($rs)) {
		return false;
	}else{
		$deal = $row['deal_id'];
		if ($total_main_deals > 1) {
        while($row = $db->fetch($rs))
        {
			if (intval($row['deal_city']) === intval($city_id) && intval($row['deal_main_deal']) === 1) {
				$deal = $row['deal_id'];
                return $deal;
			}
        }   
		}
		return $deal;
	}
		
	
}

function getDealShortInfo($deal_id, $session=true, &$error){
	if($deal_id < 1){
	return false;
	}	
	global $db;
	$deal_info= array();
	$objDeal = new DealInfo($deal_id,$session);	
	 if ($objDeal->getError() != '') {		
	      $error= t_lang('M_ERROR_INVALID_REQUEST');		
	      return false;		
	    }  
	$deal_info['deal_id'] = $objDeal->getFldValue('deal_id');
	$deal_info['deal_name'] = $objDeal->getFldValue('deal_name'. $_SESSION['lang_fld_prefix']);
	$deal_info['deal_img_name']= CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $objDeal->getFldValue('deal_id') . '&type=categorylist&time='.time();
	$deal_info['price']=amount($objDeal->getFldValue('price'));
	$deal_info['deal_original_price']=amount($objDeal->getFldValue('deal_original_price'));
	$deal_info['deal_discount']=$objDeal->getFldValue('deal_discount');
	//$deal_info['deal_discount_is_percent']=$objDeal->getFldValue('deal_discount_is_percent');
	if($objDeal->getFldValue('deal_discount_is_percent') == 1) {
		$discountPrice = $objDeal->getFldValue('deal_discount') .'%'; }
	else{ 
		$discountPrice= amount($objDeal->getFldValue('deal_discount')); 
	}
	
	$deal_info['deal_discounted_value']= $discountPrice;
	
	$deal_info['deal_end_time_timestamp']= strtotime($objDeal->getFldValue('deal_end_time'));
	$deal_info['deal_end_time']= $objDeal->getFldValue('deal_end_time');
	$deal_info['IslikeDeal']=(IslikeDeal($deal_id)) ? 1: 0; 
	return $deal_info;
}

function getDealInfo($deal_id ,$session){
	if($deal_id < 1){
	return false;
	}	
	require_once CONF_INSTALLATION_PATH. 'includes/navigation-functions.php';
	global $db;
	$deal_info= array();
	$objDeal = new DealInfo($deal_id,$session);	

	$deal_info['deal_id'] = $objDeal->getFldValue('deal_id');
	$deal_info['deal_name'] = $objDeal->getFldValue('deal_name'. $_SESSION['lang_fld_prefix']);
	$deal_info['deal_type']=$objDeal->getFldValue('deal_type');
	$deal_info['deal_sub_type']=$objDeal->getFldValue('deal_sub_type');

	$rs = $db->query("select * from tbl_deals_images where dimg_deal_id=" . $deal_id);
	$rows= array();
	while ($row = $db->fetch($rs)) {
		
	$rows[]= CONF_WEBROOT_URL . 'deal-image-crop.php?id='.$row['dimg_id'].'&type=categorylist&galleryImgId='.$row['dimg_id'].'&time='.time();
	}
	$deal_info['deal_other_images']=$rows;
	$deal_info['deal_img_name']= CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $objDeal->getFldValue('deal_id') . '&type=categorylist&time='.time();
	$deal_info['deal_end_time_timestamp']= strtotime($objDeal->getFldValue('deal_end_time'));
	$deal_info['deal_end_time']= $objDeal->getFldValue('deal_end_time');
	$deal_info['deal_status']=$objDeal->getFldValue('deal_status');
	$deal_info['deal_subtitle']=$objDeal->getFldValue('deal_subtitle');
	$deal_info['deal_original_price']=amount($objDeal->getFldValue('deal_original_price'));
	$deal_info['deal_discount_is_percent']=$objDeal->getFldValue('deal_discount_is_percent');
	$deal_info['deal_fine_print']=htmlentities($objDeal->getFldValue('deal_fine_print'. $_SESSION['lang_fld_prefix']), ENT_QUOTES, "UTF-8");
	$deal_info['deal_desc']=htmlentities($objDeal->getFldValue('deal_desc'. $_SESSION['lang_fld_prefix']), ENT_QUOTES, "UTF-8");
	$deal_info['deal_total_coupon']=$objDeal->getFldValue('deal_max_coupons');
	$deal_info['sold']=$objDeal->getFldValue('sold');
    
	$srch1 = getCompanyDetail($deal_id);
	 $rs1 = $srch1->getResultSet();
	$company_Info= $db->fetch($rs1);
	$company_rating= fetchCompanyRating($objDeal->getFldValue('deal_company'));
	$deal_info['company_id']=	$company_Info['company_id'];
	$deal_info['company_name']=	$company_Info['company_name'];
	$deal_info['company_reviews']=	$company_Info['reviews'];
	$deal_info['company_rating']=	$company_rating['rating'];
	$deal_info['sold']=$objDeal->getFldValue('sold');
	$deal_info['price']=amount($objDeal->getFldValue('price'));
	$dealReview= fetchDealRating($deal_id);
	
	$deal_info['CONF_REVIEW_RATING_DEALS']=CONF_REVIEW_RATING_DEALS;		
	$deal_info['CONF_POST_REVIEW_RATING_DEALS']=CONF_POST_REVIEW_RATING_DEALS;		
    $user_id= isset($_SESSION['logged_user']) ? $_SESSION['logged_user']['user_id']: ''; 
      if(isset($user_id) && ($user_id>0)){
            if(CONF_POST_REVIEW_RATING_DEALS == 1){	
                $canReview=canPostDealReview($deal_id, $user_id);		
                if ($db->total_records($canReview) > 0) {		
                    $deal_info['canPostReview']=1;		
                }else{		
                    $deal_info['canPostReview']=0;	
					$deal_info['postReviewMsg']=t_lang('M_TXT_SORRY_NOT_ALLOWED_TO_POST_REVIEWS_OF_MERCHANT');					
                }		
            }else{		
			$deal_info['canPostReview']=0;	
			$deal_info['postReviewMsg']=t_lang('M_TXT_SORRY_NOT_ALLOWED_TO_POST_REVIEWS_OF_MERCHANT');			
		}
        }
        else{		
			$deal_info['canPostReview']=0;	
			$deal_info['postReviewMsg']=t_lang('M_TXT_SORRY_NOT_ALLOWED_TO_POST_REVIEWS_OF_MERCHANT');			
		}
	
	$reviewsRs = getReviews($deal_id); 
	$deal_info['deal_review']=($reviewsRs->num_rows >0 )? $reviewsRs->num_rows : "";
	$deal_info['deal_rating']=$dealReview['rating'];
	$deal_info['deal_is_subdeal']=$objDeal->getFldValue('deal_is_subdeal');	
	$deal_info['deal_highlights']=$objDeal->getFldValue('deal_highlights' . $_SESSION['lang_fld_prefix']);
	// used preg_replace to remove p tag from description start and end 
	$data= preg_replace("/^<p.*?>/", "",$objDeal->getFldValue('deal_desc' . $_SESSION['lang_fld_prefix']));
	//$deal_info['deal_desc']=preg_replace("|</p>$|", "",$data);
	//$deal_info['deal_fine_print']=$objDeal->getFldValue('deal_fine_print'. $_SESSION['lang_fld_prefix']);
	$dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $deal_id . '&type=main';
	$deal_info['share_url']= friendlyUrl($dealUrl);
	/* Options functions starts here 
	function getDealOptions() is used to fecth all the options/attributes associated with deal/product by passing integer type deal_id
	*/
	if($objDeal->getFldValue('deal_type')==1){
	$deal_info['deal_option']=getDealOptions($deal_id);
	}
	if($objDeal->getFldValue('deal_type')==0 && $objDeal->getFldValue('deal_is_subdeal') == 0){
		$locations= fetchcompanyAddressListForPopup($deal_id);
			$data=array();
			foreach($locations as $location=>$company_address){
			$li['location_id']= $location;	
			$li['company_address']= $company_address;	
			$data[]=$li;
			}
			$deal_info['locations']=$data;
	}
	if($objDeal->getFldValue('deal_type')==0 && $objDeal->getFldValue('deal_is_subdeal') == 1){
		$subdeal= getSubOption($deal_id);
		$result = $subdeal->getResultSet();
		$subdealcount = $subdeal->recordCount();
		if($subdealcount == 0){
			$deal_info['sub_deal_data']="No Data Found";
			
		}else{
		$data=	$db->fetch_all($result);
		
		foreach($data as $key=> $info){
			$data[$key]['sdeal_original_price']= amount($info['sdeal_original_price']);
			$data[$key]['sdeal_price']= amount($info['sdeal_original_price'] - (($info['sdeal_discount_is_percentage'] == 1) ? $info['sdeal_original_price'] * $info['sdeal_discount'] / 100 : $info['sdeal_discount']));
			$locations= fetchcompanyAddressListForPopup($deal_id, $info['sdeal_id']);
			foreach($locations as $key1=>$val){
			$li['location_id']= $key1;	
			$li['company_address']= $val;	
			$data[$key]['locations'][]=$li;
			}
			
		}	
		$deal_info['sub_deal_data']=$data;

		}	
	}
		$deal_info['IslikeDeal']=(IslikeDeal($deal_id)) ? 1: 0; 
		
	return $deal_info;

}

function getReviews($deal_id){
	global $db;
	$query="select * from tbl_reviews as r INNER JOIN tbl_users as u where u.user_id = r.reviews_user_id and reviews_type=1 AND reviews_approval=1 AND reviews_deal_id=" . $deal_id . " order by reviews_added_on desc";
    $reviewsRs = $db->query($query);
	return $reviewsRs;
}

function getReviewsWithPagination($deal_id){
	global $db;
	$query="select * from tbl_reviews as r INNER JOIN tbl_users as u where u.user_id = r.reviews_user_id and reviews_type=1 AND reviews_approval=1 AND reviews_deal_id=" . $deal_id . " order by reviews_added_on desc";
      $reviewsRs = $db->query($query);
	return $reviewsRs;
}

function getCompanyDetail($deal_id){
	$srch = new SearchBase('tbl_deals', 'd');
	$srch->addCondition('deal_id', '=', $deal_id);
	$srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
	$srch->joinTable('tbl_countries', 'INNER JOIN', 'country.country_id=c.company_country', 'country');
	$srch->joinTable('tbl_states', 'LEFT JOIN', 'c.company_state=st.state_id', 'st');
	$srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'c.company_id=ca.company_id', 'ca');
	$srch->joinTable('tbl_reviews', 'LEFT JOIN', 'c.company_id=r.reviews_company_id and r.reviews_type=2 AND r.reviews_approval=1 and reviews_user_id !=0', 'r');
	$srch->setPageNumber(1);
	$srch->setPageSize(1);
	$srch->addMultipleFields(array('count(DISTINCT(r.reviews_id))as reviews', 'c.company_name'. $_SESSION['lang_fld_prefix'], 'c.company_city' . $_SESSION['lang_fld_prefix'], 'st.state_name', 'ca.*', 'country.country_name' . $_SESSION['lang_fld_prefix']));

	return $srch;
}



function getExpiredDealIds($city_id, $page, $pagesize=12){
	global $db;
	$srch = new SearchBase('tbl_deals', 'd');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('deal_city', '=', $city_id, 'OR');
    $cnd->attachCondition('deal_city', '=', 0);
    /* $srch->addCondition('deal_start_time', '<=', addTimezone(date('Y-m-d H:i:s'), CONF_TIMEZONE), 'AND', true);
      $srch->addCondition('deal_end_time', '>', addTimezone(date('Y-m-d H:i:s'), CONF_TIMEZONE), 'AND', true); */
    $srch->addCondition('deal_status', '=', 2);
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->addCondition('deal_complete', '=', 1);

    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->addGroupBy('d.deal_id');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty ELSE 0 END) AS sold");
    /*  $srch->addHaving('mysql_func_sold', '<', 'mysql_func_(deal_max_coupons - deal_min_buy)', 'AND', true); */

    /* Consider user preferences */
    if (isUserLogged()) {
        $srch->joinTable('tbl_deal_to_category', 'LEFT OUTER JOIN', 'd.deal_id=dc.dc_deal_id', 'dc');
        $srch->joinTable('tbl_user_to_deal_cat', 'LEFT OUTER JOIN', 'dc.dc_cat_id=udc.udc_cat_id and udc.udc_user_id=' . $_SESSION['logged_user']['user_id'], 'udc');
        $srch->addFld("SUM(CASE WHEN udc_user_id IS NULL THEN 0 ELSE 1 END) as cat_weight");
        $srch->addOrder('cat_weight', 'desc');
    }
    /* Consider user preferences ends */

    // $srch->addOrder('RAND()');

    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    $srch->addMultipleFields(array('d.deal_id', 'deal_max_coupons', 'deal_min_buy'));
	return $srch;
	
}


function fetchCategoryDealList($cat_id, $page, $pagesize){
	$catCode = fetchCatCode(intval($cat_id));
	$srch = new SearchBase('tbl_deal_to_category', 'dtc');
	//and (d.deal_city=' . $_SESSION['city'] . ' or d.deal_city=0)
	$srch->joinTable('tbl_deal_categories', 'INNER JOIN', 'dtc.dc_cat_id=c.cat_id ', 'c');
	$srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=dtc.dc_deal_id and d.deal_status=1 and d.deal_deleted=0 and d.deal_complete=1 ', 'd');
	$srch->addCondition('c.cat_code', ' LIKE ', $catCode . '%');
	$srch->addGroupBy('d.deal_id');
	$srch->addOrder('d.deal_id', 'desc');
	$srch->setPageSize($pagesize);
	$srch->setPageNumber($page);

	return $srch;
}	

function canPostDealReview($deal_id,$user_id){		
		global $db;		
		$rs= $db->query("select * from tbl_orders as o INNER JOIN tbl_order_deals as od where o.order_id=od.od_order_id and od.od_deal_id=" . $deal_id . " AND o.order_user_id=" . $user_id . " AND o.order_payment_status=1 ");		
		return $rs;		
	}

function dealReviewForm($user_id,$deal_id,$company_id){
	$frm = new Form('frmReview', 'frmReview');
    $frm->setExtra('class="siteForm" enctype="multipart/form-data"');
    $frm->setAction($_SERVER['REQUEST_URI']);
    $frm->setTableProperties(' class="formwrap__table"');
  //  $frm->setFieldsPerRow(2);
    $frm->captionInSameCell(true);
    $frm->setRequiredStarWith('caption');
    $frm->setJsErrorDisplay('afterfield');
    /* $frm->addRadioButtons( t_lang('M_FRM_RATINGS'), 'reviews_rating', array('1'=>'','2'=>'','3'=>'','4'=>'','5'=>''),'', 5, 'width="17%"',''); */
    $fld = $frm->addRadioButtons(t_lang('M_FRM_RATINGS'), 'rating', array('1' => '', '2' => '', '3' => '', '4' => '', '5' => ''), '', 5, 'width="" class="ratingtable" ', 'class="star"');
	
	$fld->requirements()->setRequired();
	
    $frm->addTextArea(t_lang('M_FRM_REVIEWS'), 'reviews_reviews', '', '', '')->requirements()->setRequired();
    $frm->addHiddenField('', 'reviews_deal_id', $deal_id, 'reviews_deal_id');
    $frm->addHiddenField('', 'reviews_deal_company_id', $company_id , 'reviews_deal_company_id');
    $frm->addHiddenField('', 'reviews_id', '', 'reviews_id');
    $frm->addHiddenField('', 'reviews_type', '1', 'reviews_type');
    $frm->addHiddenField('', 'reviews_user_id', $user_id, 'reviews_user_id');
    $frm->setValidatorJsObjectName('frmReviewValidator');
    $frm->setOnSubmit("return setDisable(frmReviewValidator)");
    $frm->addSubmitButton('', 'btn_submit_review', t_lang('M_TXT_POST_REVIEW'), '', ' class="inputbuttons" ');
	return $frm;
}	

function dealSaveReview($frm, $data, &$error){

	global $db;
	if(!$frm->validate($data)){
		$error = getValidationErrMsg($frm);
		return false;
	} else {
            $record = new TableRecord('tbl_reviews');
            $arr_lang_independent_flds = array('reviews_deal_id', 'reviews_deal_company_id', 'reviews_user_id', 'reviews_company_id', 'reviews_approval', 'reviews_type', 'reviews_id', 'mode', 'btn_submit');
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $data);
            //$record->setFldValue('reviews_reviews', nl2br($_POST['reviews_reviews']));
			$record->setFldValue('reviews_reviews', $data['reviews_reviews']);
            $record->setFldValue('reviews_deal_id', $data['reviews_deal_id']);
            $record->setFldValue('reviews_type', $data['reviews_type']);
            $record->setFldValue('reviews_rating', $data['rating']);
            $record->setFldValue('reviews_added_on', date('Y-m-d H:i:s'), false);
            $success = $record->addNew();
            $rating='<div class="ratings">
                <ul style="margin:0; padding:0;list-style:none;">';
                
                    for ($i = 0; $i < $_POST['rating']; $i++) {
                        $rating.='<li style="float:left; padding:0 5px 0 0; margin:0;"><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
                    }
                    for ($j = $_POST['rating']; $j < 5; $j++) {
                        $rating.='<li  style="float:left; padding:0 5px 0 0; margin:0;"><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
                    }
               
                $rating.='</ul>
            </div>';
			
            if ($success) {
                $companyName = $db->query("select company_name,company_email from tbl_companies where company_id=" . $data['reviews_deal_company_id']);
                $row_company = $db->fetch($companyName);
                $rs = $db->query("select * from tbl_email_templates where tpl_id=18");
                $row_tpl = $db->fetch($rs);
				$record= getRecords('tbl_deals', array('deal_id'=>$data['reviews_deal_id']), 'first');
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $message1 = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $src=CONF_WEBROOT_URL .'deal-image-crop.php?id='.$data['reviews_deal_id'] . '&type=instant';
                $arr_replacements = array(
                    'xxuser_namexx' => htmlentities($_SESSION['logged_user']['user_name']) . ' ' . htmlentities($_SESSION['logged_user']['user_lname']),
                    'xxuser_f_namexx' => substr(htmlentities($_SESSION['logged_user']['user_name']),0,1),
                    'xxdeal_namexx' => $record['deal_name'. $_SESSION['lang_fld_prefix']],
                    'xxreviewsxx' => nl2br($_POST['reviews_reviews']),
                    'xxratingxx' => $rating,
                    'xxsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxcompany_namexx' => $row_company['company_name'. $_SESSION['lang_fld_prefix']],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxdeal_imgxx' => '<img alt="" src="'.$src.'" style="margin:0 auto; display:block; border:1px solid #ddd; padding:5px;" width="100px" />',
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxbackgroundleftquotexx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL. 'images/emails/quote_left.png',
                    'xxbackgroundrightquotexx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL. 'images/emails/quote_right.png',
                );
                

                $arr_replacements['xxnamexx'] = $row_company['company_name'. $_SESSION['lang_fld_prefix']];
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                
                if ($row_tpl['tpl_status'] == 1) {
                 
                    sendMail($row_company['company_email'], $subject, emailTemplate($message));

                    $arr_replacements['xxnamexx'] = 'Admin';
                    foreach ($arr_replacements as $key => $val) {
                        $subject = str_replace($key, $val, $subject);
                        $message1 = str_replace($key, $val, $message1);
                    }
                    sendMail(CONF_SITE_OWNER_EMAIL, $subject, emailTemplate($message1));
                }
				return true;
            } else {
                $error= $record->getError();
                return false;
            }
        }	
}


function fetchFavoriteDealList($page,$pagesize){
	$srch= new SearchBase('tbl_users_favorite_deals', 'uf');
	$srch->addCondition('uf.user_id', '=',$_SESSION['logged_user']['user_id']);
	$srch->joinTable('tbl_deals', 'LEFT OUTER JOIN', 'd.deal_id=uf.deal_id', 'd');
	//$srch->addCondition('deal_status', '=', 1);
	$srch->addCondition('deal_complete', '=', 1);
   $srch->addCondition('deal_deleted', '=', 0);
	$srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    $srch->addMultipleFields(array('d.deal_id', 'deal_max_coupons'));
	return $srch;
}




function favoriteMerchantCount() {
	$logged_user_id = isset($_SESSION['logged_user']['user_id'])?$_SESSION['logged_user']['user_id']:0;
	if($logged_user_id==0){
		return false;
	}
    $srchRcd = new SearchBase('tbl_users_favorite');
	if($logged_user_id){
		$srchRcd->addCondition('user_id', '=', $_SESSION['logged_user']['user_id']);
	}
	$rs = $srchRcd->getResultSet();
    return $rs->num_rows;
}

function fetchSimilarProducts($deal_id=0,$limit=6) {
	$cityId = $_SESSION['city']; 
    $query= "Select * from tbl_deal_to_category where dtc.dc_deal_id =".$deal_id ;
    $srch = new SearchBase('tbl_deal_to_category', 'dtc');
    $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'dtc.dc_cat_id=dtc1.dc_cat_id ', 'dtc1');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=dtc1.dc_deal_id and d.deal_status<2 and d.deal_deleted=0 and d.deal_complete=1 ', 'd');
// $srch->joinTable('tbl_deal_categories', 'INNER JOIN', 'dtc.dc_cat_id=c.cat_id ', 'c');
    $srch->addCondition('d.deal_id', '!=', $deal_id);
    $srch->addCondition('dtc.dc_deal_id', '=', $deal_id);
	if($cityId !=0) {
		$cnd = $srch->addDirectCondition('0');
		$cnd->attachCondition('deal_city', '=', $cityId, 'OR');
		$cnd->attachCondition('deal_city', '=', 0);
	}
    $srch->addFld('d.deal_id');
    $srch->addGroupBy('d.deal_id');
    $srch->addOrder('d.deal_id', 'desc');
    $srch->setPageSize($limit);
 
    $rs = $srch->getResultSet();
  
    return $rs;
}	

?>