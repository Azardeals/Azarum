<?php

require_once CONF_INSTALLATION_PATH . 'includes/page-functions/cart-functions.php';
include_once CONF_INSTALLATION_PATH . 'includes/buy-deal-functions.php';

class CartClass extends ModelClass
{

    const CLIENT_TOKEN_LENGTH = 32;

    public function __construct($api)
    {
        parent::__construct($api);
    }

    public function add($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $dealId = array_key_exists('dealId', $request_data) ? $request_data['dealId'] : '';
        $subdealId = array_key_exists('subdealId', $request_data) ? $request_data['subdealId'] : '0';
        $startDate = array_key_exists('startDate', $request_data) ? $request_data['startDate'] : '';
        $endDate = array_key_exists('endDate', $request_data) ? $request_data['endDate'] : '';
        $company_address_id = array_key_exists('location', $request_data) ? $request_data['location'] : '';
        $option = array_key_exists('option', $request_data) ? $request_data['option'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if ($dealId < 1) {
            return $this->prepareErrorResponse('Invalid deal Id');
        }
        $cart = new Cart();
        $price = 0;
        $error = "";
        if (!canBuyDeal(1, '', $price, $dealId, 0, 0, $subdealId, $error)) {
            return $this->prepareErrorResponse($error);
        }
        if (!$cart->add($dealId, 1, $option, false, $subdealId, $company_address_id, $startDate, $endDate)) {
            $msg = $cart->getError();
            return $this->prepareErrorResponse($msg);
        }
        $data['cart_count'] = $cart->getItemCount();
        $data['message'] = t_lang('M_TXT_ITEM_ADDED');
        return $this->prepareSuccessResponse($data);
    }

    public function getProducts()
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $user_id = $_SESSION['logged_user']['user_id'];
        $cart = new Cart();
        if (!$cart->validateCartItemsForAPI()) {
            $error = $cart->getError();
            if (is_array($cart->getError())) {
                //send success response in case of cart empty
                $data = [];
                return $this->prepareSuccessResponse($data);
            } else {
                return $this->prepareErrorResponse($error);
            }
        } else {

            $data = $cart->getProducts([], true);
            //changed for ios
            if (!empty($data))
                $data = array_values($data);
        }
        return $this->prepareSuccessResponse($data);
    }

    public function removeCartItem($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'DELETE') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();

        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $cartItemId = array_key_exists('cartItemId', $request_data) ? $request_data['cartItemId'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }

        $user_id = $_SESSION['logged_user']['user_id'];
        $cart = new Cart();


        if (!$cart->removeItem($cartItemId)) {
            return $this->prepareErrorResponse($cart->getError());
        } else {
            $data['cart_count'] = $cart->getItemCount();
            $data['message'] = t_lang('M_TXT_ITEM_DELETED');
            return $this->prepareSuccessResponse($data);
        }
    }

    public function updateCartQty($args)
    {
        global $db;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $cartItemId = array_key_exists('cartItemId', $request_data) ? $request_data['cartItemId'] : '';
        $qty = array_key_exists('qty', $request_data) ? $request_data['qty'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if (intval($qty) <= 0) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_REQUEST_QUANTITY_NUMERIC_ONLY'));
        }
        if (intval($cartItemId) <= 0) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_CARTITEMID'));
        }

        $user_id = $_SESSION['logged_user']['user_id'];

        $cart = new Cart();
        if (!$cart->updateQuantity($cartItemId, intval($qty), $error)) {
            return $this->prepareErrorResponse($error);
        }
        $cart_vals = setCartValuesForResponse($cart);
        return $this->prepareSuccessResponse($cart_vals);
    }

    public function updateMerchantLoction($args)
    {
        global $db;
        global $msg;
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $cartItemId = array_key_exists('cartItemId', $request_data) ? $request_data['cartItemId'] : '';
        $companyAddressId = array_key_exists('companyAddressId', $request_data) ? $request_data['companyAddressId'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if (intval($companyAddressId) <= 0) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_REQUEST_COMPANY_ID_NUMERIC_ONLY'));
        }
        if (intval($cartItemId) <= 0) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_CARTITEMID'));
        }
        $user_id = $_SESSION['logged_user']['user_id'];
        $cart = new Cart();
        $subdeals = "";
        $condition = array('cart_item_id' => $cartItemId);
        $data = getRecords('tbl_cart_items', $condition, 'first');
        if ($user_id != $data['cart_item_user_id']) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_USER_ID'));
        }
        $companyAddressId = intval($companyAddressId);
        $price = "";
        $eligible_deal_data = canBuyDeal(1, true, $price, $data['cart_item_deal_id'], $companyAddressId, 0, $data['cart_item_sub_deal_id']);
        if ($eligible_deal_data === false || count($eligible_deal_data['address_id']) <= 0) {
            return $this->prepareErrorResponse($msg->display());
        }

        $maxBuy = $eligible_deal_data['max_buy'][intval($companyAddressId)];
        if ($data['cart_item_sub_deal_id'] > 0) {
            $subdeal_id = $data['cart_item_sub_deal_id'];
            $sub_voucherleft = getSubdealVoucher($subdeal_id, $companyAddressId);
            if ($maxBuy > $sub_voucherleft) {
                $maxBuy = $sub_voucherleft;
            }
        }
        for ($i = 1; $i <= $maxBuy; $i++) {
            if ($data['cart_item_qty'] == $i) {
                $checked = 'selected="selected"';
            } else {
                $checked = '';
            }
            $dropdown .= '<option value="' . $i . '" >' . $i . '</option>';
        }
        $data['cart_item_qty'] = 1;
        $data['cart_item_company_address_id'] = $companyAddressId;

        $error = "";
        if (addUpdateCartItem($data, $error)) {
            $cart_vals = setCartValuesForResponse($cart);
        } else {
            return $this->prepareErrorResponse($error);
        }

        return $this->prepareSuccessResponse($cart_vals);
    }

    public function addUpdateGiftDetails($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $key = array_key_exists('key', $request_data) ? $request_data['key'] : '';
        $email = array_key_exists('to_email', $request_data) ? $request_data['to_email'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID');
            return false;
        }
        $user_id = $_SESSION['logged_user']['user_id'];
        $cart = new Cart();
        $condition = array('cart_item_id' => $key);
        $data = getRecords('tbl_cart_items', $condition, 'first');

        if ($user_id != $data['cart_item_user_id']) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_USER_ID'));
        }
        $cart = new Cart();
        if (!$cart->updateDealGiftDetails($request_data)) {
            return $this->prepareErrorResponse('Gift details not saved!!');
        }
        return $this->prepareSuccessResponse($request_data);
    }

    public function clearGiftDetails($args)
    {
        if ($this->Api->getRequestMethod() != 'DELETE') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $key = array_key_exists('key', $request_data) ? $request_data['key'] : '';
        $user_id = $_SESSION['logged_user']['user_id'];
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $cart = new Cart();
        $condition = array('cart_item_id' => $key);
        $data = getRecords('tbl_cart_items', $condition, 'first');
        if ($user_id != $data['cart_item_user_id']) {
            return $this->prepareErrorResponse(t_lang('M_ERROR_INVALID_USER_ID'));
        }
        if (!$cart->clearGiftDetails($key)) {
            return $this->prepareErrorResponse('Gift details not deleted!!');
        }
        return $this->prepareSuccessResponse('Gift details deleted');
    }

    public function fetchUserAddress($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        global $db;
        $rs = fetchUserAddress();
        if ($rs) {
            $data = $db->fetch_all($rs);
            return $this->prepareSuccessResponse($data);
        }
    }

    public function addUpdateShippingDetails($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        unset($request_data['token']);
        $success = updateshippingAdress($request_data);
        if ($success) {
            $cart = new Cart();
            if (!$cart->updateShippingCharges(intval($request_data['ship_country']), $error)) {
                return $this->prepareErrorResponse($error);
            }
            $cart_vals = setCartValuesForResponse($cart);
            $cart_vals['msg'] = 'Address Updated.';
            return $this->prepareSuccessResponse($cart_vals);
        } else {
            return $this->prepareErrorResponse('Address Not Updated!!');
        }
    }

    public function selectShippingAddress($args)
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        $uaddr_id = array_key_exists('addressId', $request_data) ? $request_data['addressId'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        unset($request_data['token']);
        $condition = array('uaddr_id' => $uaddr_id);
        $data = getRecords('tbl_user_addresses', $condition, 'first');
        if ($data['uaddr_country_id'] < 0) {
            return $this->prepareErrorResponse('Invalid Country Id');
        }
        if ($data['uaddr_country_id']) {
            $cart = new Cart();
            if (!$cart->updateShippingCharges(intval($data['uaddr_country_id']), $error)) {
                return $this->prepareErrorResponse($error);
            }

            $cart_vals = setCartValuesForResponse($cart);
            $cart_vals['msg'] = 'Address Updated.';
            return $this->prepareSuccessResponse($cart_vals);
        } else {
            return $this->prepareErrorResponse('Address Not Selected!!');
        }
    }

    public function cartStatus()
    {
        if ($this->Api->getRequestMethod() != 'POST') {
            return $this->prepareErrorResponse('Invalid Method For Cart Class!');
        }
        $request_data = $this->Api->getRequestData();
        $token = array_key_exists('token', $request_data) ? $request_data['token'] : '';
        if (strlen($token) < 32) {
            return $this->prepareErrorResponse('Invalid token!');
        }
        $cart = new Cart();
        $cart_vals = setCartValuesForResponse($cart);
        return $this->prepareSuccessResponse($cart_vals);
    }

}
