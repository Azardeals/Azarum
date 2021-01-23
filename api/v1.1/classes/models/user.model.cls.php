<?php

require_once CONF_INSTALLATION_PATH . 'includes/page-functions/user-functions.php';

class UserClass extends ModelClass
{

    const CLIENT_TOKEN_LENGTH = 32;

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function resendVerificationEmail()
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Resend Verification Email!');
        }
        $request_data = $this->Api->getRequestData();
        $email = array_key_exists('email', $request_data) ? $request_data['email'] : '';

        if (strlen($email) < 5 || !$this->validateEmail($email)) {
            return $this->prepareErrorResponse('Invalid Email Address!');
        }

        $error = '';
        if (sendUserEmailVerificationEmail(array('user_email' => $email), $error)) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_MAIL_SENT'));
        }
        return $this->prepareErrorResponse($error);
    }

    public function forgotPassword()
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Forgot Password!');
        }
        $request_data = $this->Api->getRequestData();
        $email = array_key_exists('email', $request_data) ? $request_data['email'] : '';

        if (strlen($email) < 5 || !$this->validateEmail($email)) {
            return $this->prepareErrorResponse('Invalid Email Address!');
        }
        $error = '';
        if (processForgetPasswordRequest($email, $error)) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_PASSWORD_SENT'));
        }
        return $this->prepareErrorResponse($error);
    }

    public function register()
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Registration!');
        }
        $request_data = $this->Api->getRequestData();
        $frm = getUserRegisterationForm();
        $error = '';
        if (array_key_exists('agree_terms', $request_data) && intval($request_data['agree_terms']) != 1) {
            unset($request_data['agree_terms']);
        }
        $request_data = array_merge($request_data, array('user_id' => 0,));
        if (registerNewUser($frm, $request_data, $error)) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_EMAIL_VERIFICATION'));
        }
        //print_r($error); die();
        return $this->prepareErrorResponse($error);
    }

    public function fbLogin()
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For FB Login!');
        }

        $request_data = $this->Api->getRequestData();
        $fb_token = array_key_exists('fb_token', $request_data) ? $request_data['fb_token'] : '';
        if (strlen($fb_token) < 5) {
            return $this->prepareErrorResponse('Invalid Facebook Token!');
        }
        require_once './site-classes/facebook.php';
        $fb = new Facebook();
        $error = '';
        $user = $fb->getFBUserInfo($fb_token, $error);
        if ($user === false) {
            return $this->prepareErrorResponse($error);
        }

        $error = '';
        if ($fb->saveUserData($user, $error)) {
            return $this->getLoginSuccessContent($user->email);
        }
        return $this->prepareErrorResponse($error);
    }

    public function appleLogin()
    {
        $error = '';
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For FB Login!');
        }
        $appleResponse = $this->Api->getRequestData();
        if (empty($appleResponse['id_token'])) {
            return $this->prepareErrorResponse('Invalid Apple Token!');
        }
        if (isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS']) != 'off') {
            $path_url = 'https://';
        } else {
            $path_url = 'http://';
        }

        if (isset($appleResponse['id_token'])) {

            $claims = explode('.', $appleResponse['id_token'])[1];
            $claims = json_decode(base64_decode($claims), true);
            $appleUserInfo = isset($appleResponse['user']) ? json_decode($appleResponse['user'], true) : false;

            $isPrivateEmailId = false;
            if (isset($claims['is_private_email']) && $claims['is_private_email'] == true) {
                $isPrivateEmailId = true;
            }

            $userAppleId = isset($claims['sub']) ? $claims['sub'] : '';

            if (false === $appleUserInfo) {
                if (!isset($claims['email'])) {
                    $message = 'MSG_UNABLE_TO_FETCH_USER_INFO';
                    return $this->prepareErrorResponse($message);
                }
                $appleEmailId = $claims['email'];
            } else {
                $appleEmailId = $appleUserInfo['email'];
            }


            $error = '';

            $user = array('user_apple_id' => $userAppleId, 'user_apple_email' => $appleEmailId);
            require_once './site-classes/apple.php';
            $apple = new Apple();
            if ($apple->saveUserData($user, $error)) {
                return $this->getLoginSuccessContent($user['user_apple_email']);
            }
            return $this->prepareErrorResponse($error);
        }
        return $this->prepareErrorResponse($error);
    }

    public function login()
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $email = array_key_exists('email', $request_data) ? $request_data['email'] : '';
        if (strlen($email) < 5 || !$this->validateEmail($email)) {
            return $this->prepareErrorResponse('Invalid Email Address!');
        }
        $password = array_key_exists('password', $request_data) ? $request_data['password'] : '';
        if (strlen($password) < 1) {
            return $this->prepareErrorResponse('Invalid Password String!');
        }
        $error = '';
        if (loginUser($email, md5($password), $error)) {
            return $this->getLoginSuccessContent($email);
        }

        return $this->prepareErrorResponse($error);
    }

    public function logout()
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User logout!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }

        $user = $this->getUserDataFromSession();
        $name = $this->getLoggedUserName();
        $user_id = intval($user['user_id']);
        $this->deletePreviousTokensByUserId($user_id);
        session_destroy();

        $response_data = array(
            'message' => 'Logout successfully.',
            'name' => $name
        );
        return $this->prepareSuccessResponse($response_data);
    }

    private function getLoginSuccessContent($email)
    {
        $token = $this->getClientToken($email);
        if (strlen($token) != self::CLIENT_TOKEN_LENGTH) {
            return $this->prepareErrorResponse('Login Failed! Please try again.');
        }
        $name = $this->getLoggedUserName();
        $response_data = array(
            'message' => 'Logged in successfully.',
            'token' => $token,
            'name' => $name
        );
        return $this->prepareSuccessResponse($response_data);
    }

    private function getLoggedUserName()
    {
        $user_name = '';
        $user = &$this->getUserDataFromSession();
        if (!array_key_exists('user_name', $user) || !array_key_exists('user_lname', $user)) {
            return $user_name;
        }
        $user_name = $user['user_name'] . (strlen($user['user_lname']) ? ' ' . $user['user_lname'] : '');
        return $user_name;
    }

    private function getClientToken($email)
    {
        $token = '';
        $user = $this->getUserDataFromSession();
        if (!array_key_exists('user_email', $user) || $user['user_email'] != $email) {
            return $token;
        }
        $user_id = intval($user['user_id']);
        if ($user_id < 1) {
            return $token;
        }
        $this->deletePreviousTokensByUserId($user_id);
        $token = $this->generateAPIToken();
        $expiry = strtotime('+7 day');
        $values = array(
            'uapitoken_user_id' => $user_id,
            'uapitoken_token' => $token,
            'uapitoken_expiry' => date('Y-m-d H:i:s', $expiry),
        );
        if ($this->saveAPIToken($values)) {
            return $token;
        }
        $token = '';
        return $token;
    }

    private function deletePreviousTokensByUserId($user_id)
    {
        $user_id = intval($user_id);
        if ($user_id < 1) {
            return false;
        }
        if ($this->db->deleteRecords('tbl_user_api_token', array(
                    'smt' => '`uapitoken_user_id` = ?',
                    'vals' => array($user_id),
                    'execute_mysql_functions' => false
                ))) {
            return true;
        }
        return false;
    }

    public function checkAPITokenInDB($token)
    {
        $srch = new SearchBase('tbl_user_api_token');
        $srch->addCondition('uapitoken_token', '=', $token);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return $this->db->fetch($rs);
    }

    private function saveAPIToken(&$values)
    {
        if ($this->db->insert_from_array('tbl_user_api_token', $values)) {
            return true;
        }
        return false;
    }

    private function generateAPIToken()
    {
        do {
            $salt = substr(md5(microtime()), 7, 17);
            $token = md5($salt . microtime() . substr($salt, 7));
        } while ($this->checkAPITokenInDB($token));
        return $token;
    }

    private function getUserDataFromSession()
    {
        if (!array_key_exists('logged_user', $_SESSION) || !is_array($_SESSION['logged_user']) || sizeof($_SESSION['logged_user']) < 1) {
            return [];
        }
        return $_SESSION['logged_user'];
    }

    public function accountInformation($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $condition = [];
        $condition['user_id'] = $_SESSION['logged_user']['user_id'];
        $user_info = getRecords('tbl_users', $condition, 'first');
        if (!$user_info) {
            return $this->prepareSuccessResponse('Invalid User');
        }
        $userData = [];
        $userData['user_info']['user_info'] = $user_info;
        $arr_timezones = DateTimeZone::listIdentifiers();
        $userData['user_info']['timezone_array'] = $arr_timezones;
        $cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0");
        $cityLists = $db->fetch_all_assoc($cityList);
        $userData['user_info']['user_info']['City_of_Interest'] = $cityLists[$user_info['user_city']];
        $rs = getUserCardDetail($_SESSION['logged_user']['user_id']);
        $cardDetails = [];
        while ($row = $db->fetch($rs)) {
            $cardDetail['cardNumber'] = $row['ucd_card'];
            $cardDetail['profileId'] = $row['ucd_customer_payment_profile_id'];
            $cardDetails[] = $cardDetail;
        }
        $userData['card_detail'] = $cardDetails;

        $userData['email_notifications'] = fetchEmailNotifications($_SESSION['logged_user']['user_id']);
        return $this->prepareSuccessResponse($userData);
    }

    public function addUpdateEmailNotification($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $array_update = [];
        $notification = array_key_exists('notification', $request_data) ? $request_data['notification'] : '';
        $array_update['en_user_id'] = intval($_SESSION['logged_user']['user_id']);
        $data = array_merge($array_update, $notification);
        $error = "";
        if (addUpdateEmailNotification($data, $error)) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_RECORD_UPDATED'));
        } else {
            return $this->prepareErrorResponse($error);
        }
    }

    public function editAccountInformation($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';

        $user_name = array_key_exists('user_name', $request_data) ? $request_data['user_name'] : '';

        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if ($user_name == "") {
            return $this->prepareErrorResponse(t_lang('M_ERROR_NAME_IS_BLANK'));
        }
        unset($request_data['token']);
        if (updateUserInfo($request_data, $error)) {
            return $this->prepareSuccessResponse("Information Updated");
        }
        return $this->prepareErrorResponse($error);
    }

    public function addCreditCardInfo($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        require_once CONF_INSTALLATION_PATH . 'includes/site-functions-extended.php';
        $cardNumber = array_key_exists('cardNumber', $request_data) ? $request_data['cardNumber'] : '';
        if ($cardNumber == "") {
            return $this->prepareErrorResponse(t_lang('M_ERROR_CARDNUMBER_IS_BLANK'));
        }
        unset($request_data['token']);
        $$request_data['city'] = array_key_exists('cityName', $request_data) ? $request_data['cityName'] : '';
        $customerProfileId = 0;
        if (((int) $_SESSION['logged_user']['user_customer_profile_id']) == 0) {
            if (!createCIMCustomerProfile()) { /* To create logged in user's CIM Customer profileId */
                return $this->prepareErrorResponse($msg->display());
            }
        } else {
            $customerProfileId = (int) $_SESSION['logged_user']['user_customer_profile_id'];
        }
        $request_data['customerProfileId'] = $customerProfileId;
        if ($customerProfileId <= 0) {
            $error = t_lang('M_ERROR_INVALID_REQUEST');
            return $this->prepareErrorResponse($error);
        }
        if (addcreditcard($request_data, $error)) {
            return $this->prepareSuccessResponse("Card added successfully");
        }
        return $this->prepareErrorResponse($error);
    }

    public function deleteCreditCardInfo($args)
    {
        if ($this->Api->getRequestMethod() != 'DELETE') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $profileId = array_key_exists('profileId', $request_data) ? $request_data['profileId'] : '';
        if (strlen($profileId) < 1) {
            return $this->prepareErrorResponse('Invalid profileId!');
        }
        if (deleteCreditCardInfo($profileId, $error)) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_DELETED_CARD_INFORMATION'));
        }
        return $this->prepareErrorResponse($error);
    }

    public function getDealBuck($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }

        $referalUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/?refid=' . $_SESSION['logged_user']['user_id'];
        $wallet_amount = getWalletAmount($_SESSION['logged_user']['user_id']);
        $data = array('wallet_amount' => CONF_CURRENCY . number_format($wallet_amount, 2) . CONF_CURRENCY_RIGHT, 'referalUrl' => $referalUrl);
        return $this->prepareSuccessResponse($data);
    }

    public function getSubscribeCities($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }

        $rs = getSubscribedCities();
        $data = $db->fetch_all($rs);
        return $this->prepareSuccessResponse($data);
    }

    public function fetchSubscribedCategoryList($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $city_id = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        $categoryId = array_key_exists('categoryId', $request_data) ? $request_data['categoryId'] : '0';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if ($city_id == "") {
            return $this->prepareErrorResponse('Invalid CityId!');
        }
        $srch = fetchParentCategories($categoryId);
        $rs = $srch->getResultSet();
        $selected_categories = fetchSubscribedCategories($city_id, $categoryId);
        $catIdlist = array_column($selected_categories, 'nc_cat_id');
        while ($row = $db->fetch($rs)) {
            $data['category_name'] = $row['cat_name' . $_SESSION['lang_fld_prefix']];
            $data['is_selected'] = in_array($row['cat_id'], $catIdlist) ? 1 : 0;
            $catobj = fetchParentCategories($row['cat_id']);
            $rs1 = $catobj->getResultSet();
            $data['category_id'] = $row['cat_id'];
            $data['has_subcategory'] = ($catobj->recordCount() ? 1 : 0);
            $array[] = $data;
        }
        return $this->prepareSuccessResponse($array);
    }

    public function addsubscribedCity($args)
    {
        global $db, $msg;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $citylist = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if (empty($citylist)) {
            return $this->prepareErrorResponse('Invalid City Array');
        }
        $data['logged_email'] = $_SESSION['logged_user']['user_email'];
        $addCity = true;
        $city = [];
        foreach ($citylist as $key => $city_id) {
            if (addSubscribedCity($data['logged_email'], $city_id, $data)) {
                continue;
            } else {
                $city[] = $city_id;
                $addCity = false;
            }
        }
        if ($addCity) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_CITY_SUCCESSFULLY_ADDED.'));
        } else {
            return $this->prepareErrorResponse('City Id ' . print_r($city) . ' Not Found');
        }
    }

    public function removeSubscribedCity($args)
    {
        global $db, $msg;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $citylist = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if (empty($citylist)) {
            return $this->prepareErrorResponse('Invalid City Array');
        }
        $deleteCity = true;
        $city = [];
        foreach ($citylist as $key => $city_id) {
            if (removeSubscribedCity($city_id)) {
                continue;
            } else {
                $city[] = $city_id;
                $deleteCity = false;
            }
        }
        if ($deleteCity) {
            return $this->prepareSuccessResponse(t_lang('M_TXT_CITY_SUCCESSFULLY_REMOVED.'));
        } else {
            return $this->prepareErrorResponse('City Id ' . print_r($city) . ' Not Found');
        }
    }

    public function removedSubscribedCategoryByCityId($args)
    {
        global $msg;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $city_id = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        $categoryId = array_key_exists('categoryId', $request_data) ? $request_data['categoryId'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if ($city_id == "") {
            return $this->prepareErrorResponse('Invalid CityId!');
        }
        if ($categoryId < 1) {
            return $this->prepareErrorResponse('Invalid Category Id!');
        }
        if ($city_name = deleteCategoriesByCityId($categoryId, $city_id)) {
            $msg = t_lang('M_TXT_YOUR_SUBSCRIPTION') . ' ' . $city_name . ' ' . t_lang('M_TXT_CITYWIDE_UPDATED');
            return $this->prepareSuccessResponse($msg);
        }
        return $this->prepareErrorResponse('Category Not Found');
    }

    public function subscribeNewCategoryByCityId($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $city_id = array_key_exists('cityId', $request_data) ? $request_data['cityId'] : '';
        $categoryId = array_key_exists('categoryId', $request_data) ? $request_data['categoryId'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if ($city_id == "") {
            return $this->prepareErrorResponse('Invalid CityId!');
        }
        if ($categoryId < 1) {
            return $this->prepareErrorResponse('Invalid Category Id!');
        }
        $catIdArrays = [];
        $city_name = "";
        if (addCategoriesByCityId($categoryId, $city_id, $catIdArrays, $city_name)) {
            $msg = t_lang('M_TXT_YOUR_SUBSCRIPTION') . ' ' . $city_name . ' ' . t_lang('M_TXT_CITYWIDE_UPDATED');
            return $this->prepareSuccessResponse($msg);
        }
        return $this->prepareErrorResponse('Category Not Found');
    }

    public function fetchFavoriteDeals($args)
    {
        global $db;

        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For User Login!');
        }

        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $page = array_key_exists('page', $request_data) ? $request_data['page'] : '1';

        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }

        require_once CONF_INSTALLATION_PATH . 'includes/page-functions/deal-functions.php';

        $srch = fetchFavoriteDealList($page, 20);
        $rs = $srch->getResultSet();
        $countRecords = $srch->recordCount();
        $deal_info = [];
        if ($countRecords > 0) {
            $deal_info['deal_list'] = [];
            while ($row = $db->fetch($rs)) {
                $error = "";
                if ($info = getDealShortInfo($row['deal_id'], false, $error)) {
                    $deal_info['deal_list'][] = $info;
                } else {
                    return $this->prepareErrorResponse($error);
                }
            }
            return $this->prepareSuccessResponse($deal_info);
        }
        $deal_info['deal_list'] = [];
        //$msg= t_lang('M_TXT_SORRY_NO_FAVORITE_DEALS_AVAILABLE');
        return $this->prepareSuccessResponse($deal_info);
    }

}
