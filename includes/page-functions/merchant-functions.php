<?php 

function merchantSearchObj(){
	$srch = new SearchBase('tbl_companies', 'c');
    $srch->joinTable('tbl_countries', 'LEFT JOIN', 'ct.country_id=c.company_country', 'ct');
    $srch->joinTable('tbl_states', 'LEFT JOIN', 'st.state_id=c.company_state', 'st');
	$srch->joinTable('tbl_reviews', 'LEFT JOIN', 'c.company_id=r.reviews_company_id and r.reviews_type=2 AND r.reviews_approval=1 and reviews_user_id !=0', 'r');
    $srch->addCondition('company_active', '=', 1);
    $srch->addCondition('company_deleted', '=', 0);
    $srch->addMultipleFields(array('count(DISTINCT(r.reviews_id))as reviews','c.*', 'st.*','ct.country_name' . $_SESSION['lang_fld_prefix']));
	$srch->addGroupBy('c.company_id');
	return $srch;
}

function getCompanyLocations($company_id){
	$srch = new SearchBase('tbl_companies', 'c');
	$srch->addCondition('c.company_id', '=', $company_id);
	$srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'c.company_id=ca.company_id', 'ca');
	$srch->joinTable('tbl_countries', 'INNER JOIN', 'ct.country_id=c.company_country', 'ct');
	$srch->joinTable('tbl_states', 'LEFT JOIN', 'st.state_id=c.company_state', 'st');
	$srch->addMultipleFields(array('c.company_city','c.company_id', 'ca.*','st.*', 'ct.country_name' . $_SESSION['lang_fld_prefix']));
	$totalcityAddress = $srch->getResultSet();	
	return $totalcityAddress; 
}	

function getReviewForm($companyId){
	$frm = new Form('frmReview', 'frmReview');
	$frm->setExtra('class="siteForm"');
	$frm->setTableProperties(' class="formwrap__table" ');
	//$frm->setFieldsPerRow(2);
	$frm->captionInSameCell(true);
	$frm->setRequiredStarWith('caption');
	$frm->setJsErrorDisplay('afterfield');
	$fld = $frm->addRadioButtons(t_lang('M_FRM_RATINGS'), 'rating', array('1' => '', '2' => '', '3' => '', '4' => '', '5' => ''), '', 5, 'width="100%" class="ratingtable" ', 'class="star"');
	
	$fld->requirements()->setRequired();
	
	$fld = $frm->addTextArea(t_lang('M_FRM_REVIEWS'), 'reviews_reviews', '', '', '');
	$fld->fldCellExtra = "class='review'";
	$fld->requirements()->setRequired();
	$frm->addHiddenField('', 'reviews_company_id', $companyId, 'reviews_company_id');
	$frm->addHiddenField('', 'reviews_id', '', 'reviews_id');
	$frm->addHiddenField('', 'reviews_type', '2', 'reviews_type');
	$frm->addHiddenField('', 'reviews_user_id', $_SESSION['logged_user']['user_id'], 'reviews_user_id');
//	$frm->addHTML('', '', '&nbsp;');
	$frm->setValidatorJsObjectName('frmReviewValidator');
	$frm->setOnSubmit("return setDisable(frmReviewValidator)");
	$frm->addSubmitButton('', 'btn_submit_review', t_lang('M_TXT_POST_REVIEW'), '', ' class="inputbuttons"');
	return $frm;	
}

function saveReview($frm, $data, &$message){
	global $db;
	if(!$frm->validate($data)){
		$message = getValidationErrMsg($frm);
		return false;
	}
	 else {

        if (isUserLogged()) { 
            $record = new TableRecord('tbl_reviews');

            $arr_lang_independent_flds = array('reviews_deal_id', 'reviews_user_id', 'reviews_company_id', 'reviews_approval', 'reviews_type', 'reviews_id', 'mode', 'btn_submit');
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $data);
            $record->setFldValue('reviews_reviews', $data['reviews_reviews']);
            $record->setFldValue('reviews_company_id', $data['reviews_company_id']);
            $record->setFldValue('reviews_type', $data['reviews_type']);
            $record->setFldValue('reviews_rating', $data['rating']);
            $record->setFldValue('reviews_added_on', date('Y-m-d H:i:s'), false);
            $success = $record->addNew();
            if ($data['rating'] == "") {
                    $data['rating'] = "0";
                }
             $rating='<div class="ratings">
                <ul style="margin:0; padding:0;list-style:none;">';
                
                    for ($i = 0; $i < $data['rating']; $i++) {
                        $rating.='<li style="float:left; padding:0 5px 0 0; margin:0;"><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
                    }
                    for ($j = $data['rating']; $j < 5; $j++) {
                        $rating.='<li  style="float:left; padding:0 5px 0 0; margin:0;"><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
                    }
               
                $rating.='</ul>
            </div>';
			if($success) {
                $companyName = $db->query("select company_name,company_email from tbl_companies where company_id=" . $data['reviews_company_id']);
                $row_company = $db->fetch($companyName);
                $rs = $db->query("select * from tbl_email_templates where tpl_id=18");
                $row_tpl = $db->fetch($rs);
				$message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                 $src=CONF_WEBROOT_URL.'deal-image.php?company=' . $data['reviews_company_id'] . '&mode=companyImages';
                $arr_replacements = array(
                    'xxnamexx' => $row_company['company_name'],
                    'xxuser_namexx' => htmlentities($_SESSION['logged_user']['user_name']) . ' ' . htmlentities($_SESSION['logged_user']['user_lname']),
                    'xxdeal_namexx' => $row_company['company_name'],
                    'xxreviewsxx' => htmlentities($_POST['reviews_reviews'], ENT_QUOTES, 'UTF-8'),
                    'xxratingxx' => $rating,
                    'xxsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxcompany_namexx' => $row_company['company_name'],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxuser_f_namexx' => substr($_SESSION['logged_user']['user_name'],0,1),
                    'xxsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxdeal_imgxx' => '<img alt="" src="'.$src.'" style="margin:0 auto; display:block; border:1px solid #ddd; padding:5px;" width="100px" />',
                    'xxbackgroundleftquotexx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL. 'images/emails/quote_left.png',
                    'xxbackgroundrightquotexx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL. 'images/emails/quote_right.png',
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                
                if ($row_tpl['tpl_status'] == 1) {
                    sendMail($row_company['company_email'], $subject, emailTemplate($message));

                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $arr_replacements['xxnamexx'] = 'Admin';
                    foreach ($arr_replacements as $key => $val) {
                        $subject = str_replace($key, $val, $subject);
                        $message = str_replace($key, $val, $message);
                    }
                    sendMail(CONF_SITE_OWNER_EMAIL, $subject, emailTemplate($message));
                }

                $message = t_lang('M_TXT_REVIEW_POSTED');
                return true;
            } else {
               $message = $record->getError();
			   return false;
            }
        }
    }
}

function canPostReview($companyId,$user_id){
	if(!$user_id){
			return false;
	}	
	global $db;
	$query="select * from tbl_orders as o INNER JOIN tbl_order_deals as od INNER JOIN tbl_deals as d INNER JOIN tbl_companies as c where o.order_id=od.od_order_id and d.deal_company=c.company_id AND od.od_deal_id=d.deal_id AND c.company_id=" . $companyId . " AND o.order_user_id=" . $user_id . " AND o.order_payment_status=1 ";
	 return $canReview = $db->query($query);
}

function fetchMerchantDeals($page,$companyId,$pagsize){
	global $db;
	return merchantDealsObj($page,$companyId,$pagsize);
	
}


function likeMerchant($company_id){
	global $db; 
	 $user_id = isset($_SESSION['logged_user']['user_id']) ?$_SESSION['logged_user']['user_id'] :0;
	 if($user_id==0){
		 return false;
	 } 
	$favouriteQuery = $db->query("select * from tbl_users_favorite where  user_id =" . intval($user_id) . " and company_id=" . intval($company_id));
    $totalRow = $db->total_records($favouriteQuery);
	return $totalRow;
	
}

function fetchMerchantFavoriteList($page=1, $pagesize=12){
	$srch=new SearchBase('tbl_companies', 'c');
	$srch->joinTable('tbl_users_favorite','INNER JOIN','uf.company_id=c.company_id AND uf.user_id='.intval($_SESSION['logged_user']['user_id']),'uf');
	$srch->joinTable('tbl_countries','INNER JOIN','ct.country_id=c.company_country','ct');
	$srch->joinTable('tbl_states','INNER JOIN','st.state_id=c.company_state','st');
	$srch->joinTable('tbl_reviews', 'LEFT JOIN', 'c.company_id=r.reviews_company_id and r.reviews_type=2 AND r.reviews_approval=1 and reviews_user_id !=0', 'r');
	$srch->addMultipleFields(array('count(DISTINCT(r.reviews_id))as reviews'));
    // $srch->addFld(" (SUM(r.reviews_rating)/count(DISTINCT(r.reviews_id)))as rating");
    $srch->addMultipleFields(array('c.*', 'st.state_name' . $_SESSION['lang_fld_prefix']));
	$srch->addCondition('company_active', '=',1);
	$srch->addCondition('company_deleted', '=',0);
	$srch->addGroupBy('c.company_id');
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
	return $srch;
}





?>