<?php

require_once CONF_INSTALLATION_PATH . 'includes/page-functions/merchant-functions.php';
require_once CONF_INSTALLATION_PATH . 'includes/page-functions/deal-functions.php';

class HomeClass extends ModelClass
{

    const CLIENT_TOKEN_LENGTH = 32;

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function homePageInfo($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Home Class!');
        }
        $request_data = $this->Api->getRequestData();
        $city = $request_data['city'];
        $homePage = [];
        $data = fetchBannerDetail(4, 3);
        foreach ($data as $key => $info) {
            $banner[]['url'] = BANNER_IMAGES_URL . $info['banner_image'];
        }
        $homePage['bannerInfo'] = $banner;
        if ($rs = fetchTopVendors($city)) {
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
                $homePage['vendors'][] = $companydata;
            }
        }
        if ($rsltset = fetchTopProducts(4)) {
            while ($row = $db->fetch($rsltset)) {
                $top_products['deal_id'] = $row['deal_id'];
                $top_products['deal_name'] = $row['deal_name' . $_SESSION['lang_fld_prefix']];
                $top_products['deal_original_price'] = amount($row['deal_original_price']);
                $top_products['price'] = (fetchProductSalePrice($row['deal_id']));
                $top_products['deal_discount'] = $row['deal_discount'];
                if ($row['deal_discount_is_percent'] == 1) {
                    $discountPrice = $row['deal_discount'] . '%';
                } else {
                    $discountPrice = amount($row['deal_discount']);
                }
                $top_products['deal_discounted_value'] = $discountPrice;
                $top_products['deal_end_time'] = $row['deal_end_time'];
                $top_products['deal_end_time_timestamp'] = strtotime($row['deal_end_time']);
                $top_products['deal_img_url'] = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=category';
                $top_products['IslikeDeal'] = (IslikeDeal($row['deal_id'])) ? 1 : 0;
                $homePage['top_products'][] = $top_products;
            }
        }
        $deal_info = [];
        $homePage['featured_deals'] = $this->getFeaturedDeals(1, 8);
        $homePage['fav_deal_count'] = favoriteDealCount();
        $homePage['fav_merchant_count'] = favoriteMerchantCount();
        $cart = new Cart();
        $homePage['cart_count'] = $cart->getItemCount();
        $homePage['conf_language_switcher'] = CONF_LANGUAGE_SWITCHER;
        $homePage['conf_currency_right'] = CONF_CURRENCY_RIGHT;
        $homePage['conf_currency'] = CONF_CURRENCY;
        if (is_array($homePage)) {
            return $this->prepareSuccessResponse($homePage);
        }
        $error = "No Content Found";
        return $this->prepareSuccessResponse($error);
    }

    private function getFeaturedDeals($page, $pagesize)
    {
        global $db;
        $srch = new SearchBase('tbl_deal_categories', 'c');
        $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'dtc.dc_cat_id=c.cat_id ', 'dtc');
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
        $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
        $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
        $srch->addCondition('d.deal_deleted', '=', 0);
        $srch->addCondition('d.deal_status', '<', 2);
        $srch->addCondition('c.cat_is_featured', '=', 1);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('RAND()');
        $srch->addGroupBy('d.deal_id');
        $rs = $srch->getResultSet();
        if ($db->total_records($rs)) {
            while ($row = $db->fetch($rs)) {
                $top_products['deal_id'] = $row['deal_id'];
                $top_products['deal_name'] = $row['deal_name' . $_SESSION['lang_fld_prefix']];
                $top_products['deal_original_price'] = amount($row['deal_original_price']);
                $top_products['price'] = (fetchProductSalePrice($row['deal_id']));
                $top_products['deal_end_time'] = $row['deal_end_time'];
                $top_products['deal_end_time_timestamp'] = strtotime($row['deal_end_time']);
                $top_products['deal_img_url'] = CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $row['deal_id'] . '&type=category';
                $top_products['IslikeDeal'] = (IslikeDeal($row['deal_id'])) ? 1 : 0;
                $homePage[] = $top_products;
            }
            return $homePage;
        }
        return false;
    }

    private function getUserDataFromSession()
    {
        if (!array_key_exists('logged_user', $_SESSION) || !is_array($_SESSION['logged_user']) || sizeof($_SESSION['logged_user']) < 1) {
            return [];
        }
        return $_SESSION['logged_user'];
    }

}
