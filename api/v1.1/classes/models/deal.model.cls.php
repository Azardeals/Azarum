<?php

require_once CONF_INSTALLATION_PATH . 'includes/page-functions/deal-functions.php';

class DealClass extends ModelClass
{

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function getMainDeal($args)
    {

        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $cityId = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';

        if (!updateMainDealRequest($cityId, $error)) {
            return $this->prepareErrorResponse($error);
        }
        if (!$deal = fetchMainDealId($cityId)) {
            $error = t_lang(M_TXT_SORRY_NO_DEAL_AVAILABLE);
            return $this->prepareSuccessResponse($error);
        }
        if ($data = getDealInfo($deal)) {
            $cart = new Cart();
            $data['cart_count'] = $cart->getItemCount();
            ;
            return $this->prepareSuccessResponse($data);
        }
        return $this->prepareErrorResponse('Invalid Deal Request!');
    }

    public function getAllDeals($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';

        $data = alldealPageHtml($page, 'all-deals', '', 0, '', '', 20, 'app');
        $deal_info = [];
        $deal_info['fav_deal_count'] = favoriteDealCount();
        ;
        $deal_info['fav_merchant_count'] = favoriteMerchantCount();
        $cart = new Cart();
        $deal_info['cart_count'] = $cart->getItemCount();
        if ($data) {

            foreach ($data as $row) {
                $error = "";
                if ($info = getDealShortInfo($row['deal_id'], false, $error)) {

                    $deal_info['deal_list'][] = $info;
                } else {
                    $deal_info['deal_list'][] = $error;
                    return $this->prepareErrorResponse($deal_info);
                }
            }
            return $this->prepareSuccessResponse($deal_info);
        }
        $deal_info['deal_list'] = t_lang('M_TXT_SORRY_NO_MATCHING_DEALS_AVAILABLE');
        return $this->prepareSuccessResponse($deal_info);
    }

    public function getCityDeals($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $cityId = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';

        $pageName = array_key_exists('pageName', $request_data) ? $request_data['pageName'] : 'city-deals';

        if ($cityId == "") {
            return $this->prepareErrorResponse('Invalid City Id selected!');
        }
        $data = alldealPageHtml($page, $pageName, '', $cityId, '', '', 20, 'app');
        $deal_info = [];
        $deal_info['fav_deal_count'] = favoriteDealCount();
        ;
        $deal_info['fav_merchant_count'] = favoriteMerchantCount();
        $cart = new Cart();
        $deal_info['cart_count'] = $cart->getItemCount();
        if ($data) {
            //echo '<pre>';print_r($data); exit;
            foreach ($data as $row) {
                $error = "";
                if ($info = getDealShortInfo($row['deal_id'], false, $error)) {

                    $deal_info['deal_list'][] = $info;
                } else {
                    $deal_info['deal_list'][] = $error;
                    return $this->prepareErrorResponse($deal_info);
                }
            }
            return $this->prepareSuccessResponse($deal_info);
        }
        $deal_info['deal_list'] = []; //t_lang('M_TXT_SORRY_NO_MATCHING_DEALS_AVAILABLE');
        return $this->prepareSuccessResponse($deal_info);
    }

    public function getHotelDeals($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $cityId = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        if ($cityId == "") {
            return $this->prepareErrorResponse('Invalid City Id selected!');
        }
        $data = alldealPageHtml($page, 'getaways', '', $cityId, '', '', 20, 'app');
        $deal_info = [];
        $deal_info['fav_deal_count'] = favoriteDealCount();
        ;
        $deal_info['fav_merchant_count'] = favoriteMerchantCount();
        $cart = new Cart();
        $deal_info['cart_count'] = $cart->getItemCount();
        if ($data) {

            foreach ($data as $row) {
                $error = "";
                if ($info = getDealShortInfo($row['deal_id'], true, $error)) {
                    $deal_info['deal_list'][] = $info;
                } else {
                    return $this->prepareErrorResponse($error);
                }
            }
            return $this->prepareSuccessResponse($deal_info);
        }
        $deal_info['deal_list'] = []; //t_lang('M_TXT_SORRY_NO_MATCHING_DEALS_AVAILABLE');
        return $this->prepareSuccessResponse($deal_info);
    }

    public function getAllProducts($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        $city = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : 0;

        $data = alldealPageHtml($page, 'products', '', $city, '', '', 20, 'app');
        $cart = new Cart();
        $deal_info['cart_count'] = $cart->getItemCount();
        $deal_info = [];
        $deal_info['fav_deal_count'] = favoriteDealCount();
        ;
        $deal_info['fav_merchant_count'] = favoriteMerchantCount();
        if ($data) {
            foreach ($data as $row) {
                $error = "";
                if ($info = getDealShortInfo($row['deal_id'], false, $error)) {
                    $deal_info['deal_list'][] = $info;
                } else {
                    return $this->prepareErrorResponse($error);
                }
            }
            return $this->prepareSuccessResponse($deal_info);
        }
        $deal_info['deal_list'] = []; //t_lang('M_TXT_SORRY_NO_MATCHING_DEALS_AVAILABLE');
        return $this->prepareSuccessResponse($deal_info);
    }

    public function getExpiredDeals($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $cityId = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        if ($cityId == "") {
            return $this->prepareErrorResponse('Invalid City Id selected!');
        }
        $srch = getExpiredDealIds($cityId, $page, 20);
        $rs = $srch->addCondition('deal_type', '=', 0);
        $rs = $srch->getResultSet();
        $deal_info = [];
        $deal_info['fav_deal_count'] = favoriteDealCount();
        ;
        $deal_info['fav_merchant_count'] = favoriteMerchantCount();
        $cart = new Cart();
        $deal_info['cart_count'] = $cart->getItemCount();
        $countRecords = 0;
        while ($row = $db->fetch($rs)) {
            $countRecords++;
            $error = "";
            if ($info = getDealShortInfo($row['deal_id'], false, $error)) {
                $deal_info['deal_list'][] = $info;
            } else {
                $deal_info['deal_list'] = $error;
                return $this->prepareErrorResponse($deal_info);
            }
        }
        if ($countRecords > 0) {
            return $this->prepareSuccessResponse($deal_info);
        }
        $deal_info['deal_list'] = [];

        /* $deal_info['deal_list']= t_lang('M_TXT_SORRY_NO_EXPIRED_DEALS_AVAILABLE'); */

        return $this->prepareSuccessResponse($deal_info);
    }

    public function getDetailPage($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();

        $dealId = array_key_exists('dealId', $request_data) ? (int) $request_data['dealId'] : '0';
        /* $token = array_key_exists('token', $request_data)?$request_data['token']:'';

          if(isset($token)){
          if(strlen($token) < 32){
          return $this->prepareErrorResponse('Invalid token!');
          }
          } */
        if ($dealId < 1) {
            return $this->prepareErrorResponse('Invalid deal Id selected!');
        }

        $deal_info = getDealInfo($dealId, false);
        $cart = new Cart();
        $deal_info['cart_count'] = $cart->getItemCount();

        $rs = $db->query("select * from tbl_deals_images where dimg_deal_id=" . $dealId);
        $rows = [];
        while ($row = $db->fetch($rs)) {
            $rows[] = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['dimg_id'] . '&type=ACTUAL&galleryImgId=' . $row['dimg_id'] . '&time=' . time();
        }
        $deal_info['deal_other_images'] = $rows;
        $deal_info['deal_img_name'] = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $dealId . '&type=ACTUAL&time=' . time();

        if ($deal_info) {

            $deal_fine_prints = filter_var(html_entity_decode($deal_info['deal_fine_print'], ENT_COMPAT, 'UTF-8'), FILTER_SANITIZE_STRING);
            $deal_fine_prints = html_entity_decode($deal_fine_prints, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $deal_info['deal_fine_prints'] = str_replace(["&nbsp;"], "", $deal_fine_prints);
            $deal_descs = filter_var(html_entity_decode($deal_info['deal_desc'], ENT_COMPAT, 'UTF-8'), FILTER_SANITIZE_STRING);
            $deal_descs = html_entity_decode($deal_descs, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $deal_info['deal_descs'] = str_replace(["&nbsp;"], "", $deal_descs);
            $deal_info['CONF_REVIEW_RATING_MERCHANT'] = CONF_REVIEW_RATING_MERCHANT;
            $deal_info['CONF_POST_REVIEW_RATING_MERCHANT'] = CONF_POST_REVIEW_RATING_MERCHANT;

            return $this->prepareSuccessResponse($deal_info);
        }
        $error = t_lang('M_TXT_INVALID_REQUEST');
        return $this->prepareErrorResponse($error);
    }

    public function getProductAttributeValue($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $dealId = array_key_exists('dealId', $request_data) ? $request_data['dealId'] : '0';
        $dealOptionId = array_key_exists('deal_option_id', $request_data) ? $request_data['deal_option_id'] : '0';
        $dealOptionValueId = array_key_exists('deal_option_value_id', $request_data) ? $request_data['deal_option_value_id'] : '0';
        if ($dealId < 1 || $dealOptionId < 1 || $dealOptionValueId < 1) {
            return $this->prepareErrorResponse('Invalid Keys selected!');
        }

        $srch = new SearchBase('tbl_deal_option_value', 'dov');
        $srch->addCondition('dov.deal_option_value_id', '=', $dealOptionValueId, 'AND');
        $srch->addCondition('dov.deal_option_id', '=', $dealOptionId, 'AND');
        $srch->addCondition('dov.deal_id', '=', $dealId);
        $srch->joinTable('tbl_deal_option_value', 'INNER JOIN', "dov.option_value_id = dov1.parent_option_value_id AND dov.deal_id= $dealId AND dov1.deal_id= $dealId", 'dov1');
        $srch->joinTable('tbl_option_values', 'INNER JOIN', 'dov1.option_value_id=ov.option_value_id', 'ov');
        $srch->joinTable('tbl_options', 'INNER JOIN', 'op.option_id=ov.option_id AND op.is_deleted=0', 'op');
        $srch->addMultipleFields(array('dov1.deal_option_value_id,ov.name,dov1.price,dov1.price_prefix,dov1.option_value_id,dov1.deal_option_id'));
        $rs = $srch->getResultSet();
        $option_val_info = $db->fetch_all($rs);
        $optionVals = [];
        foreach ($option_val_info as $option_value) {
            $data['deal_option_id'] = $option_value['deal_option_id'];
            $data['deal_option_value_id'] = $option_value['deal_option_value_id'];
            $data['name'] = $option_value["name"];
            $data['price'] = $option_value["price"];
            $optionVals[] = $data;
        }

        return $this->prepareSuccessResponse($optionVals);
    }

    public function getRatingReview($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $dealId = array_key_exists('dealId', $request_data) ? $request_data['dealId'] : '0';
        if ($dealId < 1) {
            return $this->prepareErrorResponse('Invalid deal Id selected!');
        }
        global $db;
        $dealReviews = [];
        $reviewsRs = getReviews($dealId);
        $reviewsRs1 = $db->fetch_all($reviewsRs);
        foreach ($reviewsRs1 as $key => $row) {
            $deal_info = $row;
            $reply = getDealReviewReply($row['reviews_id']);
            $deal_info['reviews_is_replied'] = !($reply) ? 0 : 1;
            $deal_info['reviews_reply'] = !($reply) ? [] : array($reply);
            $dealReviews[] = $deal_info;
        }

        if ($dealReviews) {
            return $this->prepareSuccessResponse($dealReviews);
        }

        $msg = t_lang('M_TXT_NO_RECORD_FOUND');
        return $this->prepareSuccessResponse($dealReviews);
    }

    public function searchDealCityWise($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $keyword = array_key_exists('keyword', $request_data) ? $request_data['keyword'] : '0';
        $cityId = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        if ($keyword == "") {
            return $this->prepareErrorResponse('Invalid keyword selected!');
        }
        $deal_info = [];
        $deal_info['fav_deal_count'] = favoriteDealCount();
        ;
        $deal_info['fav_merchant_count'] = favoriteMerchantCount();
        $cart = new Cart();
        $deal_info['cart_count'] = $cart->getItemCount();

        global $db;

        $rs['rs_deal_list'] = dealsearchListHtml($keyword, '', $page, true, 'deal', $cityId, "app");
        $rs['rs_deal_list1'] = dealsearchListHtml($keyword, '', $page, false, 'deal', $cityId, "app");
        if (!$rs['rs_deal_list'] && !$rs['rs_deal_list1']) {
            //$deal_info['deal_list']= t_lang('M_TXT_NO_RECORD_FOUND');
            $deal_info['deal_list'] = [];
            return $this->prepareSuccessResponse($deal_info);
        }

        $countRecords = 0;

        foreach ($rs as $key => $value) {
            while ($row = $db->fetch($value)) {
                if (empty($row)) {
                    continue;
                }
                $error = "";
                $countRecords++;
                if ($info = getDealShortInfo($row['deal_id'], false, $error)) {
                    $deal_info['deal_list'][] = $info;
                } else {
                    return $this->prepareErrorResponse($error);
                }
            }
        }

        if ($countRecords > 0) {
            return $this->prepareSuccessResponse($deal_info);
        }

        //$deal_info['deal_list']= t_lang('M_TXT_NO_RECORD_FOUND');
        $deal_info['deal_list'] = [];

        return $this->prepareSuccessResponse($deal_info);
    }

    function likeDeal($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $dealId = array_key_exists('dealId', $request_data) ? $request_data['dealId'] : '0';
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '0';
        $favourite = array_key_exists('favourite', $request_data) ? $request_data['favourite'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }

        $user_id = $_SESSION['logged_user']['user_id'];
        if ($user_id < 1) {
            return $this->prepareErrorResponse("Invalid User Id");
        }

        if ($favourite == "") {
            return $this->prepareSuccessResponse("Invalid Action");
        }
        if ($favourite == 1) {
            if (addFavouriteDeal($user_id, $dealId)) {
                $data['fav_deal_count'] = favoriteDealCount();
                $data['message'] = t_lang('M_TXT_ADDED_TO_WISHLIST');
                return $this->prepareSuccessResponse($data);
            }
        }
        if ($favourite == 0) {
            if (removeFavouriteDeal($user_id, $dealId)) {
                $data['fav_deal_count'] = favoriteDealCount();
                $data['message'] = t_lang('M_TXT_REMOVED_TO_WISHLIST');
                return $this->prepareSuccessResponse($data);
            }
        }
        return $this->prepareSuccessResponse("Invalid Action");
    }

    /* 	public function fetchCategories($args){
      global $db;
      if($this->Api->getRequestMethod() != 'POST'){
      return $this->prepareErrorResponse('Invalid Method For Deal Class!');
      }
      $srch = fetchParentCategories(0);
      $categoryList = $srch->getResultSet();
      $deal_cat_arr = $db->fetch_all($categoryList);
      $cart= new Cart();
      $data['cart_count']= $cart->getItemCount();
      foreach($deal_cat_arr as $key => $value) {
      if ($value['cat_id']) {
      $srch1 = fetchParentCategories($value['cat_id']);
      $subcategoryList = $srch1->getResultSet();
      $subcatData= $db->fetch_all($subcategoryList);
      foreach($subcatData as $s_key => $s_value){
      $srch_n = fetchParentCategories($s_value['cat_id']);
      $subcategoryList = $srch_n->getResultSet();
      $subcatData[$s_key]['has_subcategory']= ($srch_n->recordCount() ? 1 : 0);
      }

      $deal_cat_arr[$key]['subCategory'] = $subcatData;
      }
      }
      $data['data']=$deal_cat_arr;
      return $this->prepareSuccessResponse($data);
      } */

    public function fetchCategories($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $cart = new Cart();
        $data['cart_count'] = $cart->getItemCount();
        $rs = fetchCategories('both', 0);
        $deal_cat_arr = $db->fetch_all($rs);
        foreach ($deal_cat_arr as $key => $value) {
            if ($value['cat_id']) {
                $subcategoryList = fetchCategories('both', $value['cat_id']);
                $subcatData = $db->fetch_all($subcategoryList);
                foreach ($subcatData as $s_key => $s_value) {
                    $srch_n = fetchCategories('both', $s_value['cat_id']);
                    $hasSubcategoryList = $db->fetch_all($srch_n);
                    $subcatData[$s_key]['has_subcategory'] = ($hasSubcategoryList ? 1 : 0);
                }
                $deal_cat_arr[$key]['subCategory'] = $subcatData;
            }
        }
        $data['data'] = $deal_cat_arr;
        return $this->prepareSuccessResponse($data);
    }

    public function fetchCategoryDetail($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $has_subcategory = array_key_exists('has_subcategory', $request_data) ? $request_data['has_subcategory'] : '0';
        $categoryId = array_key_exists('categoryId', $request_data) ? $request_data['categoryId'] : '0';
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        if ($categoryId == 0) {
            return $this->prepareErrorResponse('Invalid category Id selected!');
        }
        if ($has_subcategory == 0) {
            $catDealData = [];
            $catDealData['fav_deal_count'] = favoriteDealCount();
            ;
            $catDealData['fav_merchant_count'] = favoriteMerchantCount();
            $srch = fetchCategoryDealList($categoryId, $page, 8);
            $rs = $srch->getResultSet();
            $count = 0;
            while ($row = $db->fetch($rs)) {
                $count++;
                $catDeal['deal_id'] = $row['deal_id'];
                $catDeal['deal_name'] = $row['deal_name' . $_SESSION['lang_fld_prefix']];
                $catDeal['deal_original_price'] = amount($row['deal_original_price']);
                $catDeal['price'] = (fetchProductSalePrice($row['deal_id']));
                $catDeal['deal_end_time'] = $row['deal_end_time'];
                $catDeal['deal_end_time_timestamp'] = strtotime($row['deal_end_time']);
                $catDeal['deal_img_url'] = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=main';
                $catDeal['IslikeDeal'] = (IslikeDeal($row['deal_id'])) ? 1 : 0;
                $catDealData['deal_list'][] = $catDeal;
            }
            if ($count > 0) {
                return $this->prepareSuccessResponse($catDealData);
            }
            $catDealData['deal_list'] = []; //"No deal Found";
            return $this->prepareSuccessResponse($catDealData);
        } else {
            $srch1 = fetchParentCategories($categoryId);
            $subcategoryList = $srch1->getResultSet();
            if ($srch1->recordCount() < 1) {
                $subcatData = "No Sub-category Found";
                return $this->prepareSuccessResponse($subcatData);
            }
            $subcatData = $db->fetch_all($subcategoryList);
            foreach ($subcatData as $s_key => $s_value) {
                $srch_n = fetchParentCategories($s_value['cat_id']);
                $subcategoryList = $srch_n->getResultSet();
                $subcatData[$s_key]['has_subcategory'] = ($srch_n->recordCount() ? 1 : 0);
            }
            return $this->prepareSuccessResponse($subcatData);
        }
    }

    public function saveDealReviews($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $frm = dealReviewForm();
        $error = '';
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $request_data['reviews_user_id'] = $_SESSION['logged_user']['user_id'];
        if (dealSaveReview($frm, $request_data, $error)) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_REVIEW_POSTED'));
        }
        return $this->prepareErrorResponse($error);
    }

    public function getCalenderDetail()
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $dealId = array_key_exists('dealId', $request_data) ? $request_data['dealId'] : '';
        $subdealId = array_key_exists('subdealId', $request_data) ? $request_data['subdealId'] : 0;
        $locationId = array_key_exists('locationId', $request_data) ? $request_data['locationId'] : '';
        if (($dealId) < 1) {
            return $this->prepareErrorResponse('Invalid dealId!');
        }
        if (($locationId) < 1) {
            return $this->prepareErrorResponse('Invalid locationId!');
        }
        require_once CONF_INSTALLATION_PATH . 'includes/subdeals-functions.php';
        $dealData = fetchDealInfo($dealId);
        $start_date = date("Y-m-d", strtotime($dealData['deal_start_time']));
        $end_date = date("Y-m-d", strtotime($dealData['deal_end_time']));
        $showprice = false;
        if ($dealData['deal_sub_type'] == 2) {
            $showprice = true;
        }
        $data = fetchRequestBookingblockUnblockDate($dealData['deal_id'], $subdealId, $locationId, $start_date, $dealData['deal_end_time'], $showprice, 'api');

        return $this->prepareSuccessResponse($data);
    }

}
