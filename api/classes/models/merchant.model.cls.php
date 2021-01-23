<?php

require_once CONF_INSTALLATION_PATH . 'includes/page-functions/merchant-functions.php';
require_once CONF_INSTALLATION_PATH . 'includes/page-functions/deal-functions.php';

class MerchantClass extends ModelClass
{

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function fetchCompanyList($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Merchant Class');
        }
        $request_data = $this->Api->getRequestData();
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        $srch = merchantSearchObj();
        $srch->setPageNumber($page);
        $srch->setPageSize(20);
        $rs = $srch->getResultSet();
        if ($srch->recordCount() > 0) {
            $vendors = [];
            while ($companyrow = $db->fetch($rs)) {
                $companydata['company_id'] = $companyrow['company_id'];
                $companydata['company_phone'] = ($companyrow['company_phone'] != "") ? $companyrow['company_phone'] : "----";
                $companydata['company_image'] = CONF_WEBROOT_URL . 'deal-image.php?company=' . $companyrow['company_id'] . '&mode=companyImages';
                $reviewsRow = fetchCompanyRating($companyrow['company_id']);
                $companydata['company_rating'] = $reviewsRow['rating'];
                $companydata['company_reviews'] = $companyrow['reviews'];
                $companydata['company_url'] = $companyrow['company_url'];
                $companydata['company_name'] = $companyrow['company_name' . $_SESSION['lang_fld_prefix']];
                $companydata['likeMerchant'] = likeMerchant($companyrow['company_id']);
                $vendors[] = $companydata;
            }
            if (is_array($vendors)) {
                return $this->prepareSuccessResponse($vendors);
            }
        }
        $msg = "No Content Found";
        return $this->prepareSuccessResponse($msg);
    }

    public function getDetail($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Merchant Class');
        }
        $request_data = $this->Api->getRequestData();
        $companyId = array_key_exists('companyId', $request_data) ? $request_data['companyId'] : '';
        if ($companyId == "") {
            return $this->prepareErrorResponse('Invalid Request');
        }
        $srch = merchantSearchObj();
        $srch->addCondition('c.company_id', '=', $companyId);
        $rs_listing = $srch->getResultSet();
        $companyrow = $db->fetch($rs_listing);
        if ($db->total_records($rs_listing) == 0) {
            return $this->prepareErrorResponse('Invalid Company Id selected!');
        }
        $resultset = getCompanyLocations($companyrow['company_id']);
        $companyrow['company_locations'] = $db->fetch_all($resultset);
        $companyrow['company_logo'] = CONF_WEBROOT_URL . 'deal-image.php?company=' . $companyrow['company_id'] . '&mode=companyImages';
        $companyrow['background_image'] = CONF_WEBROOT_URL . 'images/merchant-pic.jpg';
        $companyrow['company_name'] = $companyrow['company_name' . $_SESSION['lang_fld_prefix']];
        $reviewsRow = fetchCompanyRating($companyrow['company_id']);
        $companyrow['company_rating'] = $reviewsRow['rating'];
        $companyrow['CONF_REVIEW_RATING_MERCHANT'] = CONF_REVIEW_RATING_MERCHANT;
        $companyrow['CONF_POST_REVIEW_RATING_MERCHANT'] = CONF_POST_REVIEW_RATING_MERCHANT;
        $companyrow['likeMerchant'] = likeMerchant($companyId);
        if (CONF_POST_REVIEW_RATING_MERCHANT == 1) {
            $user_id = isset($_SESSION['logged_user']) ? $_SESSION['logged_user']['user_id'] : '';
            $canReview = canPostReview($companyId, $user_id);
            if (!$canReview) {
                $companyrow['canPostReview'] = 0;
                $companyrow['postReviewMsg'] = t_lang('M_TXT_SORRY_NOT_ALLOWED_TO_POST_REVIEWS_OF_MERCHANT');
            }
            if ($db->total_records($canReview) > 0) {
                $companyrow['canPostReview'] = 1;
            } else {
                $companyrow['canPostReview'] = 0;
                $companyrow['postReviewMsg'] = t_lang('M_TXT_SORRY_NOT_ALLOWED_TO_POST_REVIEWS_OF_MERCHANT');
            }
        } else {
            $companyrow['canPostReview'] = 1;
        }
        /* check user is logged in or not if yes check he can post review ? */
        return $this->prepareSuccessResponse($companyrow);
    }

    public function getReviews($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Merchant Class!');
        }
        $request_data = $this->Api->getRequestData();
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        $companyId = array_key_exists('companyId', $request_data) ? $request_data['companyId'] : '';
        $srch = companyReviewObj($page, $companyId, 20);
        $rs = $srch->getResultSet();
        while ($row = $db->fetch($rs)) {
            if ($row['reviews_approval'] == 1) {
                $record['reviews_id'] = $row['reviews_id'];
                $record['reviews_reviews'] = $row['reviews_reviews'];
                $record['reviews_rating'] = $row['reviews_rating'];
                $record['reviews_user_id'] = $row['reviews_user_id'];
                $record['user_name'] = $row['user_name'];
                $record['user_lname'] = $row['user_lname'];
                $record['reviews_added_on'] = $row['reviews_added_on'];
                $data[] = $record;
            }
        }
        return $this->prepareSuccessResponse($data);
        //return $this->prepareErrorResponse($error);
    }

    public function saveReviews($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Merchant Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $request_data['reviews_user_id'] = $_SESSION['logged_user']['user_id'];
        $frm = getReviewForm();
        $error = '';
        if (saveReview($frm, $request_data, $error)) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_REVIEW_POSTED'));
        }
        return $this->prepareErrorResponse($error);
    }

    public function getMerchantDeals($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Merchant Class');
        }
        require_once CONF_INSTALLATION_PATH . 'includes/page-functions/deal-functions.php';
        $request_data = $this->Api->getRequestData();
        $companyId = array_key_exists('companyId', $request_data) ? $request_data['companyId'] : 0;
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';
        if ($companyId < 1) {
            return $this->prepareErrorResponse('Invalid Company Id');
        }
        $srch = fetchMerchantDeals($page, $companyId, 20);
        $rs_deal_list = $srch->getResultSet();
        $deal_info = [];
        $deal_info['fav_deal_count'] = favoriteDealCount();
        $deal_info['fav_merchant_count'] = favoriteMerchantCount();
        $count = 0;
        while ($row = $db->fetch($rs_deal_list)) {
            $error = "";
            $count++;
            if ($info = getDealShortInfo($row['deal_id'], false, $error)) {
                $deal_info['deal_list'][] = $info;
            } else {
                $deal_info['deal_list'] = $error;
                return $this->prepareErrorResponse($deal_info);
            }
        }
        if ($count > 0) {
            return $this->prepareSuccessResponse($deal_info);
        }
        $deal_info['deal_list'] = t_lang('M_TXT_NO_RECORD_FOUND');
        return $this->prepareSuccessResponse($deal_info);
    }

    public function likeMerchant($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $companyId = array_key_exists('companyId', $request_data) ? $request_data['companyId'] : '0';
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '0';
        $favourite = array_key_exists('favourite', $request_data) ? $request_data['favourite'] : '';
        $user_id = isset($_SESSION['logged_user']) ? $_SESSION['logged_user']['user_id'] : 0;
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if ($user_id < 1) {
            return $this->prepareSuccessResponse("Invalid User Id");
        }
        if ($favourite == "") {
            return $this->prepareSuccessResponse("Invalid Action");
        }
        if ($favourite == 1) {
            if (addFavouriteMerchant($user_id, $companyId)) {
                return $this->prepareSuccessResponse("like Successfully");
            }
        }
        if ($favourite == 0) {
            if (removeFavouriteMerchant($user_id, $companyId)) {
                return $this->prepareSuccessResponse("Unlike Successfully");
            }
        }
        return $this->prepareSuccessResponse("Invalid Action");
    }

    public function fetchFavoriteMerchantList($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Deal Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '0';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $srch = fetchMerchantFavoriteList(1, 200);
        $rs_listing = $srch->getResultSet();
        while ($companyrow = $db->fetch($rs_listing)) {
            $companyrow['company_id'] = $companyrow['company_id'];
            $companyrow['company_phone'] = ($companyrow['company_phone'] != "") ? $companyrow['company_phone'] : "----";
            $companyrow['company_image'] = CONF_WEBROOT_URL . 'deal-image.php?company=' . $companyrow['company_id'] . '&mode=companyImages';
            $companyrow['company_url'] = $companyrow['company_url'];
            $companyrow['company_name'] = $companyrow['company_name' . $_SESSION['lang_fld_prefix']];
            $companyrow['likeMerchant'] = likeMerchant($companyrow['company_id']);
            $vendors[] = $companyrow;
        }
        if (is_array($vendors)) {
            return $this->prepareSuccessResponse($vendors);
        } else {
            return $this->prepareSuccessResponse(t_lang('M_TXT_NO_RECORD_FOUND'));
        }
    }

}
