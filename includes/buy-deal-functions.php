<?php

include_once CONF_INSTALLATION_PATH . 'application-top.php';
require_once CONF_INSTALLATION_PATH . "qrcode/qrlib.php";

function getDealDataToBuy($deal_id = 0, $flds = [], $for_single_address = false, $company_address_id = 0, $subdeal_id = 0)
{
    if (intval($deal_id) <= 0) {
        return false;
    }
    global $db;
    $deal_id = intval($deal_id);
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_id', '=', $deal_id);
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'ca.company_id=d.deal_company', 'ca');
    if ($for_single_address && intval($company_address_id) > 0) {
        $srch->addCondition('company_address_id', '=', intval($company_address_id));
    }
    if ($subdeal_id > 0) {
        $srch->joinTable('tbl_sub_deals', 'INNER JOIN', 'sd.sdeal_deal_id=d.deal_id AND sd.sdeal_id=' . $subdeal_id, 'sd');
        $srch->joinTable('tbl_deal_address_capacity', 'INNER JOIN', 'dac.dac_sub_deal_id=sd.sdeal_id AND dac.dac_address_id=ca.company_address_id', 'dac');
        $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'od.od_subdeal_id=sd.sdeal_id  AND od.od_company_address_id=dac.dac_address_id', 'od');
        $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    } else {
        $srch->joinTable('tbl_deal_address_capacity', 'INNER JOIN', 'dac.dac_deal_id=d.deal_id AND dac.dac_address_id=ca.company_address_id', 'dac');
        $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'od.od_deal_id=d.deal_id AND od.od_company_address_id=dac.dac_address_id', 'od');
        $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    }
    $srch->addMultipleFields(array('d.deal_id', 'dac.dac_address_capacity', 'ca.company_address_id', 'd.deal_city'));
    if (count($flds) > 0) {
        $srch->addMultipleFields($flds);
    }
    $probation_time = date('Y-m-d H:i:s', strtotime("-30 MINUTE"));
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=2 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS refund");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date > '" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
    $srch->addGroupBy('company_address_id');
    $rs = $srch->getResultSet();
    if ($rows = $db->fetch_all($rs)) {
        return $rows;
    }
    return false;
}

function getDealOrderDataForUser($for_single_address = false, $deal_id = 0, $company_address_id = 0, $user_id = 0, $subdeal_id = 0)
{
    if (intval($user_id) <= 0) {
        $user_id = (int) $_SESSION['logged_user']['user_id'];
    }
    if ($user_id > 0 && $deal_id > 0) {
        global $db;
        $srch = new SearchBase('tbl_deal_address_capacity', 'dac');
        $srch->addCondition('dac.dac_deal_id', '=', $deal_id);
        $srch->addCondition('dac.dac_sub_deal_id', '=', 0);
        $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'od.od_deal_id=dac.dac_deal_id AND od.od_company_address_id=dac.dac_address_id AND od.od_deal_id=' . $deal_id, 'od');
        $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'o.order_id=od.od_order_id AND o.order_user_id=' . $user_id, 'o');
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=1  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold_to_user");
        $probation_time = date('Y-m-d H:i:s', strtotime("-30 MINUTE"));
        $srch->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date >'" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending_user");
        $srch->addFld('dac_address_id as address_id');
        $srch->addGroupBy('dac_address_id');
        $rs = $srch->getResultSet();
        if ($rows = $db->fetch_all($rs)) {
            return $rows;
        }
    }
    return false;
}

function canBuyDeal($qty, $for_single_address = false, &$price = null, $deal_id = 0, $company_address_id_single = 0, $buyer_user_id = 0, $subdeal_id = 0, &$error = "", $markPaidByAdmin = false)
{
    global $msg;
    $qty = intval($qty);
    if ($qty < 1) {
        $error = t_lang('M_ERROR_YOU_HAVE_NOT_SELECTED_ANY_QUANTITY');
        return false;
    }
    $flds = array(
        'deal_start_time', 'deal_end_time', 'deal_status',
        'deal_min_buy', 'deal_max_buy', 'deal_original_price',
        'deal_discount_is_percent', 'deal_discount'
    );
    if (intval($deal_id) <= 0) {
        $error = t_lang('M_ERROR_INVALID_DEAL_ID');
        return false;
    }
    $data = getDealDataToBuy($deal_id, $flds, $for_single_address, $company_address_id_single, $subdeal_id);
    if (count($data) <= 0 || $data === false) {
        $error = t_lang('M_ERROR_THIS_DEAL_IS_NOT_AVAILABLE');
        return false;
    }
    $currenttime = strtotime(dateForTimeZone(CONF_TIMEZONE));
    $startTime = strtotime($data[0]['deal_start_time']);
    if (($currenttime < $startTime) || $data[0]['deal_status'] > 2) {
        $error = t_lang('M_ERROR_DEAL_IS_NOT_OPEN');
        return false;
    }
    if ($currenttime > strtotime($data[0]['deal_end_time'])) {
        $error = t_lang('M_ERROR_SORRY_DEAL_EXPIRED');
        return false;
    }
    $company_address_id = [];
    $dealData = [];
    $getQty = false;
    $i = 0;
    $count_payment_status_pending = 0;
    foreach ($data as $dealData) {
        $total_sold = intval($dealData['sold']) + intval($dealData['payment_pending']);
        $count_payment_status_pending += intval($dealData['payment_pending']);
        $requiredQty = $qty + $total_sold;
        if ($dealData['dac_address_capacity'] >= $requiredQty) {
            $company_address_id[intval($dealData['company_address_id'])] = intval($dealData['dac_address_capacity']) - $total_sold;
        } else if (count($data) > ($i + 1)) {
            continue;
        } else if (count($company_address_id) > 0) {
            break;
        } else {
            if ($count_payment_status_pending > 0) {
                $error = t_lang('M_ERROR_ALL_VOUCHER_OF_THIS_DEAL_IS_SOLD_AND_NUMBER_OF_PENDING_VOUCHERS_IS') . ' ' . $count_payment_status_pending . '. ' . t_lang('M_PLEASE_TRY_AFTER_SOMETIME');
                return false;
            } else {
                $error = t_lang('M_ERROR_ALL_VOUCHER_OF_THIS_DEAL_IS_SOLD');
                return false;
            }
        }
        $i++;
    }
    if ((!$getQty) && empty($company_address_id)) {
        $error = t_lang('M_TXT_NO_MORE_QUANTITY_AVAILABLE');

        return false;
    }
    $userOrderData = getDealOrderDataForUser($for_single_address, $deal_id, $company_address_id_single, $buyer_user_id, $subdeal_id);

    $eligible_address_id = [];
    $i = 0;
    $total_sold_to_user = 0;
    $total_payment_pending_for_user = 0;
    foreach ($userOrderData as $uod) {
        $total_sold_to_user += intval($uod['sold_to_user']);
        $total_payment_pending_for_user += intval($uod['payment_pending_user']);
    }

    //to enable the provision to mark a order as paid within the 30 mins of timeframe. We are skipping the sum of  $total_sold_to_user + $total_payment_pending_for_user
    if ($markPaidByAdmin) {
        $gt_sold_to_user = $total_sold_to_user;
    } else {
        $gt_sold_to_user = $total_sold_to_user + $total_payment_pending_for_user;
    }
    foreach ($userOrderData as $uod) {
        $i++;
        $requiredQtyForUser = $qty + $gt_sold_to_user;
        if (array_key_exists(intval($uod['address_id']), $company_address_id) && $requiredQtyForUser <= intval($dealData['deal_max_buy'])) {
            $eligible_address_id['address_id'][intval($uod['address_id'])] = intval($uod['address_id']);
            $eligible_address_id['min_buy'][intval($uod['address_id'])] = intval($dealData['deal_min_buy']);
            $leftQtyThatUserCanBuy = intval($dealData['deal_max_buy']) - $gt_sold_to_user;
            $max_left_for_loc = intval($company_address_id[$uod['address_id']]);
            if ($leftQtyThatUserCanBuy > $max_left_for_loc)
                $leftQtyThatUserCanBuy = $max_left_for_loc;
            $eligible_address_id['max_buy'][intval($uod['address_id'])] = $leftQtyThatUserCanBuy;
        } else if (count($userOrderData) > ($i)) {
            continue;
        } else if (count($eligible_address_id['address_id']) > 0) {
            break;
        } else {
            if ($total_payment_pending_for_user > 0) {
                $error1 = t_lang('M_TXT_NOT_ALLOWED_BUY_MORE_PAYMENT_PENDING');
            } else {
                $error1 = t_lang('M_TXT_NOT_ALLOWED_BUY_MORE_VOUCHER');
            }
            $error = sprintf($error1, $qty, intval($dealData['deal_max_buy']), $total_sold_to_user, $total_payment_pending_for_user);
            return false;
        }
    }
    if (count($eligible_address_id['address_id']) > 0) {
        return $eligible_address_id;
    }
    return false;
}

function getFormAuthorize()
{
    $frm = getMBSFormByIdentifier('frmAuthorize');
    $frm->setRequiredStarWith('none');
    if (CONF_PAYMENT_PRODUCTION == 0) {
        $fld = $frm->getField('card_name');
        $fld->value = 'Demo Card';
        $fld = $frm->getField('billing_address');
        $fld->value = 'Demo Billing Address';
        $fld = $frm->getField('last_name');
        $fld->value = 'Demo Last Name';
        $fld = $frm->getField('city');
        $fld->value = 'Demo City';
        $fld = $frm->getField('card_number');
        $fld->value = '370000000000002';
        $fld = $frm->getField('state');
        $fld->value = 'Demo State';
        $fld = $frm->getField('postal_code');
        $fld->value = '176202';
    }
    $fld = $frm->getField('expire_month');
    $arr = [];
    for ($i = 1; $i <= 12; $i++) {
        $v = str_pad($i, 2, '0', STR_PAD_LEFT);
        $arr[$v] = $v;
    }
    $fld->options = $arr;
    $fld->extra = "class='month'";
    $fld = $frm->getField('expire_year');
    $arr = [];
    for ($i = date('Y'); $i <= date('Y') + 10; $i++) {
        $arr[$i] = $i;
    }
    $fld->options = $arr;
    $fld->extra = "class='year'";
    $fld = $frm->getField('btn_submit');
    $fld->value = t_lang('M_TXT_PURCHASE_MY_DEAL');
    $fld = $frm->getField('terms');
    $fld->extra = 'title="' . t_lang('M_TXT_TERMS_OF_USE') . ' ' . t_lang('M_TXT_AND') . ' ' . t_lang('M_TXT_PRIVACY_POLICY') . '"';
    $fld->html_after_field = '<i class="input-helper"></i>' . t_lang(' M_TXT_I_AGREE_TO_THE') . ' ' . t_lang('M_TXT_TERMS_OF_USE') . ' ' . t_lang('M_TXT_AND') . ' ' . t_lang('M_TXT_PRIVACY_POLICY');
    return $frm;
}

function chargeCard()
{
    global $db, $msg;
    if (CONF_PAYMENT_PRODUCTION == 0) {
        $payMode = 'testMode';
    } else {
        $payMode = 'liveMode';
    }
    if (isset($_SESSION['token']) && $_SESSION['token'] != "") {
        $url = CONF_WEBROOT_URL . 'api/error.php';
    } else {
        $url = friendlyUrl(CONF_WEBROOT_URL . 'cart-checkout.php');
    }
    $cart = new Cart();
    if ($cart->isEmpty() == true)
        redirectUser($url);
    if (!$cart->validateShippingCharges()) {
        $msg->addError('Shipping details are not saved!!');
        redirectUser($url);
    }
    $price = 0;
    $total_qty_to_buy = 0;
    $dealIdArray = [];
    $fatCart = $cart->getProducts();
    foreach ($fatCart as $dealdate => $value) {
        $dealIdArray[] = intval($value['deal_id']);
        $total_qty_to_buy += $value['qty'];
        $price += $value['price'];
    }

    /* CODE FOR AUTHORIZED.NET START HERE */
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_id', 'IN', $dealIdArray);
    $srch->addFld('deal_instant_deal');
    $rs = $srch->getResultSet();
    $row_deal = $db->fetch_all($rs);
    $normaldeal = false;
    $instantdeal = false;
    foreach ($row_deal as $keys => $values) {
        if ($values['deal_instant_deal'] == 1) {
            $isInstantDeal = 1;
            $instantdeal = true;
        } else {
            $normaldeal = true;
        }
    }
    if ($isInstantDeal == 1) {
        $tagStart = '<profileTransAuthOnly>';
        $tagEnd = '</profileTransAuthOnly>';
    } else {
        $tagStart = '<profileTransAuthCapture>';
        $tagEnd = '</profileTransAuthCapture>';
    }

    $post = getPostedData();
    $rs = $db->query("select * from tbl_payment_options where po_id=3");
    $row = $db->fetch($rs);
    if ($row['po_active'] == 0) {
        die(t_lang('M_TXT_AUTHORIZE_PAYMENT_NOT_ACTIVE'));
    }
    if (!isset($_POST['card'])) {
        $post = getPostedData();
        $customerShippingAddressId = NULL;
        if (((int) $_POST["customerProfileId"]) <= 0) {
            $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
            redirectUser($url);
        }
        //build xml to post
        $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                "<createCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                MerchantAuthenticationBlock() .
                "<customerProfileId>" . $_POST["customerProfileId"] . "</customerProfileId>" .
                "<paymentProfile>" .
                "<billTo>" .
                "<firstName>" . $_POST["firstName"] . "</firstName>" .
                "<lastName>" . $_POST["lastName"] . "</lastName>" .
                "<address>" . $_POST["address1"] . " " . $_POST["address2"] . "</address>" .
                "<city>" . $_POST["city"] . "</city>" .
                "<state>" . $_POST["state"] . "</state>" .
                "<zip>" . $_POST["zip"] . "</zip>" .
                "<phoneNumber>000-000-0000</phoneNumber>" .
                "</billTo>" .
                "<payment>" .
                "<creditCard>" .
                "<cardNumber>" . $_POST["cardNumber"] . "</cardNumber>" .
                "<expirationDate>" . $_POST["expirationDateYear"] . "-" . $_POST["expirationDate"] . "</expirationDate>" . // required format for API is YYYY-MM
                "</creditCard>" .
                "</payment>" .
                "</paymentProfile>" .
                "<validationMode>" . $payMode . "</validationMode>" . // or testMode liveMode
                "</createCustomerPaymentProfileRequest>";
        $response = send_xml_request($content);
        $parsedresponse = parse_api_response($response);
        if ("Ok" == $parsedresponse->messages->resultCode) {
            if (!$db->insert_from_array('tbl_users_card_detail', array('ucd_user_id' => $_SESSION['logged_user']['user_id'], 'ucd_customer_payment_profile_id' => htmlspecialchars($parsedresponse->customerPaymentProfileId), 'ucd_card' => substr($_POST['cardNumber'], -4), 'ucd_street_address' => $_POST["address1"], 'ucd_street_address2' => $_POST["address2"], 'ucd_city' => $_POST["city"], 'ucd_state' => $_POST["state"], 'ucd_zip' => $_POST["zip"]), false)) {
                $msg->addError($db->getError());
            }
            $customerPaymentProfileId = htmlspecialchars($parsedresponse->customerPaymentProfileId);
        } else {
            $msg->addError($parsedresponse->messages->message->text . '&nbsp;');
            redirectUser($url);
        }
    } else {
        $customerPaymentProfileId = $_POST['card'];
    }
    if (!$orderId = $cart->processOrder(4, $post['charge_from_wallet'], true)) {
        $msg->addMsg(t_lang('M_ERROR_ORDER_EXECUTION_ERROR') . $cart->getError());
        require_once dirname(__FILE__) . '/msgdie.php';
    }

    $db->query("update tbl_orders set order_payment_profile_id=" . intval($customerPaymentProfileId) . " where order_id='" . $orderId . "'");
    //build xml to post
    $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            MerchantAuthenticationBlock() .
            "<transaction>" .
            "$tagStart" .
            "<amount>" . round($cart->getCartTotal(true), 2) . "</amount>" . // should include tax, shipping, and everything.
            "<lineItems>" .
            "<itemId>" . $orderId . "</itemId>" .
            "<name>name of item sold</name>" .
            "<description>Description of item sold</description>" .
            "<quantity>" . ($total_qty_to_buy) . "</quantity>" .
            "<unitPrice>" . number_format($price, 2, '.', '') . "</unitPrice>" .
            "<taxable>false</taxable>" .
            "</lineItems>" .
            "<customerProfileId>" . $_SESSION['logged_user']['user_customer_profile_id'] . "</customerProfileId>" .
            "<customerPaymentProfileId>" . intval($customerPaymentProfileId) . "</customerPaymentProfileId>" .
            "<order>" .
            "<invoiceNumber>" . $orderId . "</invoiceNumber>" .
            "</order>" .
            "$tagEnd" .
            "</transaction>" .
            "</createCustomerProfileTransactionRequest>";
    $response = send_xml_request($content);
    $parsedresponse = parse_api_response($response);
    if ("Ok" == $parsedresponse->messages->resultCode) {
        if (isset($parsedresponse->directResponse)) {
            $directResponseFields = explode(",", $parsedresponse->directResponse);
            $responseCode = $directResponseFields[0]; // 1 = Approved 2 = Declined 3 = Error
            $responseReasonCode = $directResponseFields[2]; // See http://www.authorize.net/support/AIM_guide.pdf
            $responseReasonText = $directResponseFields[3];
            $approvalCode = $directResponseFields[4]; // Authorization code
            $transId = $directResponseFields[6];
        }
        $cart->clearCart();
        if (strlen($orderId) < 4) {
            $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
            redirectUser($url);
        }
        if (isset($approvalCode)) {
            $db->query("update tbl_orders set order_approval_code = '" . $approvalCode . "' where order_id='" . $orderId . "'");
        }
        ################ EMAIL TO USERS#################
        notifyAboutPurchase($orderId);
        ################ EMAIL TO USERS#################
        $arr = array(
            'ot_order_id' => $orderId,
            'ot_transaction_id' => $transId,
            'ot_transaction_status' => 1,
            'ot_gateway_response' => var_export($response, true)
        );
        if (!$db->insert_from_array('tbl_order_transactions', $arr)) {
            $msg->addMsg(t_lang('M_ERROR_TRANSACTION_NOT_UPDATED') . $transId);
        }
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'success.php?dp_id=' . $orderId));
    } else {
        $db->query("DELETE FROM tbl_users_card_detail WHERE ucd_customer_payment_profile_id=" . intval($customerPaymentProfileId));
        $msg->addError($parsedresponse->messages->message->text . '&nbsp;');
        redirectUser($url);
    }
    /* CODE FOR AUTHORIZED.NET END HERE */
}

function notifyAboutPurchase($orderId)
{
    global $msg;
    global $db;
    if (strlen($orderId) < 13) {
        $msg->addError(t_lang('M_ERROR_ORDER_NOT_FOUND_FOR_PURCHASE_NOTIFICATION'));
        return false;
    }
    $cart = new Cart();
    $cart->getError();
    $srch = new SearchBase('tbl_order_deals', 'od');
    $srch->addCondition('od_order_id', '=', $orderId);
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
    $srch->joinTable('tbl_digital_product_extras', 'LEFT JOIN', 'od.od_deal_id=dpe.dpe_deal_id', 'dpe');
    $srch->joinTable('tbl_countries', 'INNER JOIN', 'count.country_id=c.company_country', 'count');
    $srch->joinTable('tbl_states', 'LEFT JOIN', 'state.state_id=c.company_state', 'state');
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'od.od_company_address_id =ca.company_address_id', 'ca');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    $srch->joinTable('tbl_order_shipping_details', 'LEFT JOIN', 'od.od_order_id=osd.osd_order_id', 'osd');
    $srch->joinTable('tbl_countries', 'LEFT JOIN', 'country.country_id=osd.osd_country_id', 'country');
    $srch->joinTable('tbl_states', 'LEFT JOIN', 'st.state_id=osd.osd_state_id', 'st');
    $srch->joinTable('tbl_order_bookings', 'LEFT JOIN', 'od.od_id=ob.obooking_od_id', 'ob');
    $srch->addMultipleFields(array('d.deal_min_coupons', 'o.order_shipping_charges', 'd.deal_type', 'd.deal_tipped_at', 'd.deal_id', 'od.od_deal_name as deal_name' . $_SESSION['lang_fld_prefix'], 'd.deal_status', 'd.deal_type', 'd.deal_sub_type',
        'd.deal_highlights' . $_SESSION['lang_fld_prefix'], 'd.voucher_valid_till', 'd.voucher_valid_from', 'd.deal_desc' . $_SESSION['lang_fld_prefix'], 'd.deal_redeeming_instructions' . $_SESSION['lang_fld_prefix'], 'c.company_name' . $_SESSION['lang_fld_prefix'], 'c.company_email', 'c.company_phone', 'c.company_address1' . $_SESSION['lang_fld_prefix'], 'c.company_address2' . $_SESSION['lang_fld_prefix'],
        'c.company_address3' . $_SESSION['lang_fld_prefix'], 'c.company_city' . $_SESSION['lang_fld_prefix'], 'c.company_state', 'c.company_zip', 'c.company_country', 'count.country_name' . $_SESSION['lang_fld_prefix'], 'ca.company_address_line1' . $_SESSION['lang_fld_prefix'],
        'ca.company_address_line2' . $_SESSION['lang_fld_prefix'], 'ca.company_address_line3' . $_SESSION['lang_fld_prefix'], 'ca.company_address_zip',
        'od.od_to_email', 'od.od_to_name', 'od.od_email_msg', 'u.user_name', 'o.order_id', 'o.order_date', 'o.order_payment_mode',
        'od_deal_price', 'od_qty ', 'od_gift_qty', 'od_voucher_suffixes', 'u.user_id', 'u.user_email', 'country.country_name as shipping_count_name', 'st.state_name' . $_SESSION['lang_fld_prefix'], 'ob.obooking_booking_from', 'ob.obooking_booking_till',
        "GROUP_CONCAT( distinct(CONCAT_WS(',',osd_address_line1,osd_address_line2,osd_city_name,st.state_name" . $_SESSION['lang_fld_prefix'] . ",country.country_name,osd_zip_code))SEPARATOR ' ') as shippingAddress", 'od.od_id', 'dpe.dpe_product_external_url', 'dpe.dpe_product_file',
        'state.state_name as company_state', 'od_sub_deal_name', 'od_mark_as_used_code'
    ));
//
    $srch->addGroupBy('od.od_id');
    $productInfoForUser = [];
    $rs = $srch->getResultSet();
    if ($db->total_records($rs) > 0) {
        $count = 0;
        $result = $db->fetch_all($rs);
        $resultSet = $result;
        foreach ($result as $key => $row_deal) {
            $tax = $cart->getDealTaxDetail($row_deal['deal_id'], $row_deal['od_deal_price']);
            $row_deal['tax_amount'] = $tax['taxAmount'];
            $count++;
            if ($count == 1 && in_array(intval($row_deal['order_payment_mode']), array(1, 2, 4), true)) {
                if ($row_deal['deal_status'] == 3) { /* Handle the case that user had gone for payment but the deal was cancelled in between */
                    notifyDealCancelation(intval($row_deal['deal_id']), $orderId);
                    return false;
                }
            }
            if (in_array(CONF_DEAL_PURCHASE_NOTIFICATION, array('4', '5', '6', '7'), true)) { /* if notification to user is enabled */
                if ($row_deal['deal_type'] == 0) {
                    sendPurchaseNotificationToUser($row_deal);
                } elseif ($row_deal['deal_type'] == 1 && $row_deal['deal_sub_type'] == 1 && ($row_deal['dpe_product_external_url'] != "" && $row_deal['dpe_product_file'] == "")) {
                    $productInfoForUser[] = $row_deal;
                    sendDownloadableLinkNotificationToUser($row_deal);
                } else {
                    if ($row_deal['deal_status'] != 3) {
                        $productInfoForUser[] = $row_deal;
                        //sendProductPurchaseNotificationToUser($row_deal);
                    }
                }
            }
        }
        if (in_array(CONF_DEAL_PURCHASE_NOTIFICATION, array('1', '3', '5', '7'), true)) {
            /* 	email template start for sending deal purchased notification to admin	 */
            $rs1 = $db->query("select * from tbl_email_templates where tpl_id=44");
            $row_tpl = $db->fetch($rs1);
            send_deal_purchased_email_to_admin($result, $row_tpl);
            /* 	email template end for sending deal purchased notification to admin		 */
        }
        if (strlen(CONF_DEAL_PURCHASE_NOTIFY_EMAIL_OTHERS) > 1) {
            /* 	email template start for sending deal purchased notification to other email addressesadded by admin	 */
            $rs1 = $db->query("select * from tbl_email_templates where tpl_id=44");
            $row_tpl = $db->fetch($rs1);
            send_deal_purchased_email_to_admin($result, $row_tpl, true);
            /* 	email template start for sending deal purchased notification to other email addressesadded by admin	 */
        }
        if (in_array(CONF_DEAL_PURCHASE_NOTIFICATION, array('2', '3', '6', '7'), true)) {
            /* 	email template start for sending deal purchased notification to merchant */
            $rs1 = $db->query("select * from tbl_email_templates where tpl_id=45");
            $row_tpl = $db->fetch($rs1);
            send_deal_purchased_email_to_merchant($result, $row_tpl);
            /* 	email template end for sending deal purchased notification to merchant	 */
        }
        /* 	email template  for sending product purchased notification to user	 */
        if (!empty($productInfoForUser)) {
            sendProductPurchaseNotificationToUser($productInfoForUser);
        }
    } else {
        $msg->addError(t_lang('M_ERROR_ORDER_NOT_FOUND_FOR_PURCHASE_NOTIFICATION'));
        return false;
    }
    return true;
}

function sendDownloadableLinkNotificationToUser($row_deal)
{
    global $db;
    $order_id = $row_deal['order_id'];
    $rs1 = $db->query("select * from tbl_email_templates where tpl_id=48");
    $row_tpl = $db->fetch($rs1);
    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
    $arr_replacements = array(
        'xxuser_namexx' => $row_deal['user_name'],
        'xxdeal_namexx' => $row_deal['deal_name' . $_SESSION['lang_fld_prefix']],
        'xxdownloadablelinkxx' => $row_deal['dpe_product_external_url'],
        'xxcompany_phonexx' => $row_deal['company_phone'],
        'xxcompany_emailxx' => $row_deal['company_email'],
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
    sendMail($row_deal['user_email'], $subject . ' ' . $order_id, emailTemplateSuccess($message));
}

function sendProductPurchaseNotificationToUser($productInfo)
{
    global $db;
    if (count($productInfo) <= 0 || !$productInfo) {
        return false;
    }
    /* 	email template start for sending deal purchased notification to merchant */
    $rs1 = $db->query("select * from tbl_email_templates where tpl_id=47");
    $row_tpl = $db->fetch($rs1);
    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
    $str = '';
    $price = 0;
    $tax_amount = 0;
    foreach ($productInfo as $key => $row_deal) {
        $option = "";
        $order_id = $row_deal['order_id'];
        if (($row_deal['od_qty'] + $row_deal['od_gift_qty']) > 0) {
            $order_options = get_order_option(array('od_id' => $row_deal['od_id']));
            if (is_array($order_options) && count($order_options) && $order_options != false) {
                $option .= '<div style="font-size:12px;">';
                foreach ($order_options as $op) {
                    $option .= '- ' . $op['oo_option_name'] . ': ' . $op['oo_option_value'] . '<br/>';
                }
                $option .= '</div>';
            }
            $qty = $row_deal['od_qty'] + $row_deal['od_gift_qty'];
            $tax = $row_deal['tax_amount'] * $qty;
            $tax_amount = $tax_amount + $tax;
            $price = $price + ($row_deal['od_deal_price'] * $qty);
            $grand_total = $price + $row_deal['order_shipping_charges'] + $tax_amount;
            $str .= '<table cellspacing="0" cellpadding="0" style="width: 100%; border-top: 1px solid rgb(221, 221, 221); background: none repeat scroll 0% 0% rgb(245, 245, 245); border-collapse: collapse; border-bottom: 1px solid rgb(221, 221, 221);">
                	<tbody>
                            <tr>
                                <td style="padding: 10px; vertical-align: top; width: 52%;">
                                    <table width="100%" cellspacing="0" cellpadding="2" border="0">
                                	<tbody>
                                        <tr>
                                          <td style="vertical-align: top; color: rgb(0, 171, 201); font-size: 16px; width: 40%;">' . t_lang("M_TXT_DEAL_NAME") . ':</td>
                                          <td style="font-weight:bold;font-size:15px;color:#3c3d3d">' . $row_deal["deal_name" . $_SESSION["lang_fld_prefix"]] . '<br/>' . $option . '</td>
                                        </tr>
                                         <tr>
                                         <td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_QUANTITY") . ':</td>
                                          <td style=" font-size:15px;">' . $qty . '</td>
                                        </tr>
                                        <tr>
                                         <td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_DEAL_PRODUCT") . ' ' . t_lang("M_TXT_PRICE") . ':</td>
                                            <td style=" font-size:15px;">' . CONF_CURRENCY . number_format($row_deal['od_deal_price'], 2) . '</td>
                                        </tr>
                                         <tr>
                                            <td style="color: rgb(0, 171, 201); font-size: 16px; width: 32%;">' . t_lang("M_TXT_EMAIL") . ':</td>
                                            <td style=" font-size:15px;">' . $row_deal['user_email'] . '</td>
                                        </tr>
                                          <tr>
                                            <td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_PURCHASED") . ':</td>
                                            <td style=" font-size:15px;">' . displayDate($row_deal['order_date'], true) . '</td>
                                        </tr>
                                	</tbody>
                                </table>
                                </td>
                         	<td style="line-height: 20px; font-size: 14px; padding: 10px; vertical-align: top; width: 48%;">
                             <table width="100%" cellspacing="5" cellpadding="2" border="0" bgcolor="#fff" style="padding: 0px 5px;">
                                                    <tbody><tr>
                                	<td><b style="color: rgb(0, 171, 201); font-size: 16px; margin: 0px 0px 0px -2px;">' . $row_deal['company_name' . $_SESSION['lang_fld_prefix']] . '</b><br/>' . $row_deal['company_name' . $_SESSION['lang_fld_prefix']] . '<br/>
                                                            ' . $row_deal['company_address_line1' . $_SESSION['lang_fld_prefix']] . ',<br/>
                                                            ' . $row_deal['company_address_line2' . $_SESSION['lang_fld_prefix']] . '<br/>
                                                            ' . $row_deal['company_address_line3' . $_SESSION['lang_fld_prefix']] . ' ' . $row_deal['company_city' . $_SESSION['lang_fld_prefix']] . ' <br/>
                                                                  ' . $row_deal['company_state'] . ' ' . $row_deal['country_name' . $_SESSION['lang_fld_prefix']] . '<br/>' . '</td>

                                                    </tr>

                                                    <tr>

                                                        <td>' . $row_deal['company_address_zip'] . '</td>

                                                    </tr>

                                                      <tr>

                                                        <td><a style="text-decoration: none; color: rgb(207, 30, 54);" href="mailto:' . $row_deal['company_email'] . '">' . $row_deal['company_email'] . '</a></td>

                                                    </tr>
                                                </tbody></table>
			 					</td>
              			</tr>
                        </tbody>
                        </table>';
        }
    }
    $str .= '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #ddd;border-collapse:collapse;background: none repeat scroll 0 0 rgb(245, 245, 245);">';
    $str .= '<tr>
      <td width="30%" style="font-size:14px;font-weight:bold;padding:10px;border:1px solid #ddd;">' . t_lang('M_TXT_SUB_TOTAL') . '</td>
      <td width="70%"  style="font-size:14px;border:1px solid #ddd;padding:10px;">' . CONF_CURRENCY . round($price, 2) . CONF_CURRENCY_RIGHT . '</td>
      </tr>
      <tr>
      <td style="font-size:14px;font-weight:bold;padding:10px;border:1px solid #ddd;">' . t_lang('M_TXT_SHIPPING_CHARGES') . '</td>
      <td style="font-size:14px;border:1px solid #ddd;padding:10px;">' . CONF_CURRENCY . round($row_deal['order_shipping_charges'], 2) . CONF_CURRENCY_RIGHT . '</td>
      </tr>
      <tr>
      <td style="font-size:14px;font-weight:bold;padding:10px;border:1px solid #ddd;" >' . t_lang('M_TXT_TAX_CHARGES') . '</td>
      <td style="font-size:14px;border:1px solid #ddd;padding:10px;">' . CONF_CURRENCY . round($tax_amount, 2) . CONF_CURRENCY_RIGHT . '</td>
      </tr>
      <tr>
      <td style="font-size:14px;font-weight:bold;padding:10px;border:1px solid #ddd;">' . t_lang('M_TXT_GRAND_TOTAL') . '</td>
      <td style="font-size:14px;border:1px solid #ddd;padding:10px;">' . CONF_CURRENCY . round($grand_total, 2) . CONF_CURRENCY_RIGHT . '</td>
      </tr>';
    if ($row_deal['deal_type'] == 1 && $row_deal['deal_sub_type'] == 0) {
        $str .= '<tr>
      <td style="font-size:14px;font-weight:bold;padding:10px;border:1px solid #ddd;">' . t_lang('M_TXT_SHIPPING_ADDRESS') . '</td>
      <td style="font-size:14px;border:1px solid #ddd;padding:10px;">' . $row_deal['shippingAddress'] . '</td>
      </tr>';
    }
    $str .= '</table>';
    $arr_replacements = array(
        'xxuser_namexx' => $row_deal['user_name'],
        'xxdeal_namexx' => $row_deal['deal_name' . $_SESSION['lang_fld_prefix']],
        'xxamountxx' => CONF_CURRENCY . round($grand_total, 2) . CONF_CURRENCY_RIGHT,
        'xxcompany_zipxx' => $row_deal['company_address_zip'],
        'xxcompany_phonexx' => $row_deal['company_phone'],
        'xxcompany_emailxx' => $row_deal['company_email'],
        'xxrecipientxx' => $row_deal['user_name'],
        'xxemail_addressxx' => $row_deal['user_email'],
        'xxpurchase_datexx' => displayDate($row_deal['order_date'], true),
        'xxsite_namexx' => CONF_SITE_NAME,
        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
        'xxwebrooturlxx' => CONF_WEBROOT_URL,
        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
        'xxshipping_addressxx' => $row_deal['shippingAddress']
    );
    foreach ($arr_replacements as $key => $val) {
        $subject = str_replace($key, $val, $subject);
        $message = str_replace($key, $val, $message);
    }
    $message = str_replace('xxorderdetailxx', $str, $message);
    sendMail($row_deal['user_email'], $subject . ' ' . $order_id, emailTemplateSuccess($message));
}

function sendPurchaseNotificationToUser(&$row_deal)
{
    global $db;
    if (count($row_deal) <= 0 || !$row_deal || intval($row_deal['od_qty'] + $row_deal['od_gift_qty']) <= 0) {
        return false;
    }
    if (intval($row_deal['od_qty'] + $row_deal['od_gift_qty']) > 0) {
        $tpl_id = 7;
        if (intval($row_deal['order_payment_mode']) === 3) {
            $tpl_id = 9;
        }
        $rs1 = $db->query("select tpl_status,tpl_message" . $_SESSION['lang_fld_prefix'] . " as tpl_message,tpl_subject" . $_SESSION['lang_fld_prefix'] . " as tpl_subject from tbl_email_templates where tpl_id=" . intval($tpl_id));
        $row_tpl = $db->fetch($rs1); /* email notification for main user */
    }
    if (intval($row_deal['od_gift_qty']) > 0 && displayDate($row_deal['deal_tipped_at']) != '') {
        $tpl_id = 6;
        if (intval($row_deal['order_payment_mode']) === 3) {
            $tpl_id = 10;
        }
        $rs1 = $db->query("select tpl_status,tpl_message" . $_SESSION['lang_fld_prefix'] . " as tpl_message,tpl_subject" . $_SESSION['lang_fld_prefix'] . " as tpl_subject from tbl_email_templates where tpl_id=" . intval($tpl_id));
        $row_tpl_gift = $db->fetch($rs1); /* email notification for gift */
    }
    $tipped_deal_text = t_lang('M_TXT_EMAIL_DEAL_TIPPED');
    if (displayDate($row_deal['deal_tipped_at']) == '') {
        $tipped_deal_text = t_lang('M_TXT_DEAL_HAS_NOT_TIPPED');
    }
    $instruction = '';
    $instruction = ($row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] ? $row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] : 'N/A');

    $dealPrice = $row_deal['od_deal_price'] + $row_deal['tax_amount'];
    $date = "";
    if ($row_deal['obooking_booking_from'] != "" && $row_deal['obooking_booking_till'] != "") {
        $checkoutDate = date('Y-m-d', strtotime($row_deal['obooking_booking_till'] . ' +1 day'));
        $date = date("D M j Y", strtotime($row_deal['obooking_booking_from'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D M j Y", strtotime($checkoutDate));
        $date1 = strtotime($row_deal['obooking_booking_from']);
        $date2 = strtotime($checkoutDate);
        $diff = $date2 - $date1;
        $date .= " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
    }

    $subdealname = "";
    $deal_desc = '';
    $deal_name = '';
    $style = 'style="color:#000; padding:3px 0;"';
    if ($row_deal['od_sub_deal_name'] != "") {
        $sub_deal_name = "(" . $row_deal['od_sub_deal_name'] . ")";
    }

    $deal_name = html_entity_decode(appendPlainText($row_deal['deal_name' . $_SESSION['lang_fld_prefix']])) . ' ' . $sub_deal_name;
    $deal_desc = '<li ' . $style . '><strong>' . $deal_name . '</strong></li>';
    if ($date != '') {
        $deal_desc .= '<li ' . $style . '><strong>' . $date . '</strong></li>';
    }
    if ($row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] != '') {
        $deal_desc .= '<li ' . $style . '><strong>' . $row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] . '</strong></li>';
    }

    $arr_replacements = array(
        'xxis_giftedxx' => '',
        'xxtippedxx' => '',
        'xxuser_namexx' => $row_deal['user_name'],
        'xxdeal_namexx' => appendPlainText($deal_name),
        'xxbookingdatesxx' => $date,
        'xxamountxx' => CONF_CURRENCY . number_format($dealPrice, 2) . CONF_CURRENCY_RIGHT,
        'xxtaxamountxx' => CONF_CURRENCY . number_format($row_deal['tax_amount'], 2) . CONF_CURRENCY_RIGHT,
        'xxordered_coupon_qtyxx' => '1',
        'xxdeal_highlightsxx' => $row_deal['deal_highlights' . $_SESSION['lang_fld_prefix']],
        'xxdeal_descriptionxx' => $deal_desc,
        'xxcompany_namexx' => $row_deal['company_name' . $_SESSION['lang_fld_prefix']],
        'xxcompany_addressxx' => $row_deal['company_name' . $_SESSION['lang_fld_prefix']] . '<br/>
		  ' . $row_deal['company_address_line1' . $_SESSION['lang_fld_prefix']] . ',<br/>
		  ' . $row_deal['company_address_line2' . $_SESSION['lang_fld_prefix']] . '<br/>
		  ' . $row_deal['company_address_line3' . $_SESSION['lang_fld_prefix']] . ' ' . $row_deal['company_city' . $_SESSION['lang_fld_prefix']] . ' <br/>
		  ' . $row_deal['company_state'] . ' ' . $row_deal['country_name' . $_SESSION['lang_fld_prefix']] . '<br/>',
        'xxcompany_zipxx' => $row_deal['company_address_zip'],
        'xxcompany_phonexx' => $row_deal['company_phone'],
        'xxcompany_emailxx' => $row_deal['company_email'],
        'xxpurchase_datexx' => displayDate($row_deal['order_date'], true),
        'xxinstructionsxx' => $instruction,
        'xxvalidtillxx' => displayDate($row_deal['voucher_valid_till']),
        'xxvalidfromxx' => displayDate($row_deal['voucher_valid_from']),
        'xxsite_namexx' => CONF_SITE_NAME,
        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
        'xxwebrooturlxx' => CONF_WEBROOT_URL,
        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
        'xxmark_as_used_code_xx' => $row_deal['od_mark_as_used_code'],
    );
    $od_voucher_suffixes = explode(', ', $row_deal['od_voucher_suffixes']);

    foreach ($od_voucher_suffixes as $voucher) {

        $imgSrc = fetchQRImageSrc($row_deal['order_id'] . $voucher);
        //echo 'tempp'; exit;
        $arr_replacements['xxorderidxx'] = $row_deal['order_id'] . $voucher;
        $arr_replacements['xxqrcodexx'] = '<img src="' . $imgSrc . '" />';
        $arr_replacements['xxofficeusexx'] = $officeUse;
        $order_id = $row_deal['order_id'] . $voucher;
        if (intval($row_deal['od_gift_qty']) > 0 && intval($voucher) >= 5556 && intval($voucher) <= 9999) { /* As voucher codes are created for gifted vouchers between 5556 and 9999 both values inclusive, while adding an order to db */
            if (displayDate($row_deal['deal_tipped_at']) != '' && $row_tpl_gift['tpl_status'] == 1) {
                /* email notification for gift */
                $message = $row_tpl_gift['tpl_message'];
                $subject = $row_tpl_gift['tpl_subject'];
                $arr_replacements['xxfriendxx'] = $row_deal['od_to_name'];
                $arr_replacements['xxmessagexx'] = $row_deal['od_email_msg'];
                $arr_replacements['xxrecipientxx'] = $row_deal['od_to_name'];
                $arr_replacements['xxemail_addressxx'] = $row_deal['od_to_email'];
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }

                sendMail($row_deal['od_to_email'], $subject . ' ' . $order_id, emailTemplateSuccess($message));
                $arr_replacements['xxis_giftedxx'] = sprintf(unescape_attr(t_lang('M_TXT_VOUCHER_IS_GIFTED_AND_NOTIFICATION_SENT')), $row_deal['od_to_name'], $row_deal['od_to_email']);
                /* Notification to gifted user end here */
            } else {
                $arr_replacements['xxis_giftedxx'] = t_lang('M_TXT_VOUCHER_IS_GIFTED') . ' <strong>' . $row_deal['od_to_name'] . '</strong>';
            }
        }
        if ($row_tpl['tpl_status'] == 1) {
            $arr_replacements['xxtippedxx'] = $tipped_deal_text;
            $arr_replacements['xxrecipientxx'] = $row_deal['user_name'];
            $arr_replacements['xxemail_addressxx'] = $row_deal['user_email'];
            $message = $row_tpl['tpl_message'];
            $subject = $row_tpl['tpl_subject'];
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            sendMail($row_deal['user_email'], $subject . ' ' . $order_id, emailTemplateSuccess($message));
        }
    }
    return true;
}

function getCouponCodeForm()
{
    $frm = new Form('frmCouponCode', 'frmCouponCode');
    $frm->setExtra('class="siteform"');
    $frm->setTableProperties('class="formtable"');
    $frm->captionInSameCell(true);
    $frm->setJsErrorDisplay('afterfield');
    $frm->setFieldsPerRow(3);
    $fld = $frm->addRequiredField('', 'coupon_code', '', 'coupon_code', 'placeholder="Apply Discount Code" title="Coupon Code"');
    $fld->setRequiredStarWith('none');
    $fld_html = $frm->addHTML('', 'coupon_value', '-' . CONF_CURRENCY . 0 . CONF_CURRENCY_RIGHT);
    $fld_html->fldCellExtra = 'class="org-txt" id="coupon_value_box" align="left"';
    $frm->addSubmitButton('', 'btn_apply_coupon', 'Apply', 'btn_apply_coupon', 'class="org-btn"');
    $frm->addHiddenField('', 'mode', 'applyCouponCode', 'mode');
    $frm->setValidatorJsObjectName('frm_apply_code_validator');
    $frm->setOnSubmit('updateCouponCode(this, frm_apply_code_validator); return false;');
    return $frm;
}

function getCharityDetail($rowCharity)
{
    $str = "";
    if (is_array($rowCharity)) {
        $id = $rowCharity['charity_id'];

        $src = CONF_WEBROOT_URL . "deal-image.php?charity=" . $id . "&mode=charitythumbImages";
        $str = "<img src='" . $src . "'>
        <span>" . t_lang('M_TXT_THANK_YOU') . " " . CONF_SITE_NAME . "</span>,<p>" . t_lang('M_TXT_WE_WILL_DONATE') . " " . $rowCharity['charity_discount'] . " " . t_lang('M_TXT_TO_THIS_CHARITY') . " </p><p><span>" . t_lang('M_TXT_ORGANISATION') . " : </span>" . $rowCharity['charity_name'] . "</p><p><span>" . t_lang('M_TXT_ADDRESS') . " : </span>" . trim($rowCharity['charity_address'], ',') . "</p></address>";
    }
    return $str;
}

function getShippingAddressForm()
{
    global $db;
    $frm = new Form('frmShipping', 'frmShipping');
    $frm->setExtra('class="siteForm"');
    $frm->setTableProperties('class="formtable"');
    $frm->setJsErrorDisplay('afterfield');
    $fld = $frm->addRequiredField('Name', 'ship_name', '', 'ship_name');
    $fld->setRequiredStarWith('none');
    $fld = $frm->addRequiredField('Address Line1', 'ship_address_line1', '', 'ship_address_line1');
    $fld->setRequiredStarWith('none');
    $fld = $frm->addTextBox('Address Line2', 'ship_address_line2', '', 'ship_address_line2');
    $fld->setRequiredStarWith('none');
    $fld = $frm->addSelectBox('Country', 'ship_country', getCountryAssociativeList(), '', 'onchange="loadStates(this)"', 'Select', 'ship_country');
    $fld->requirements()->setRequired(true);
    $fld->setRequiredStarWith('none');
    $fld = $frm->addSelectBox('State', 'ship_state', '', '', 'onchange="loadCities(this)"', 'Select', 'ship_state');
    $fld->requirements()->setRequired(true);
    $fld->setRequiredStarWith('none');
    $fld = $frm->addTextBox('City', 'ship_city', '', 'ship_city');
    $fld->requirements()->setRequired(true);
    $fld->setRequiredStarWith('none');
    $fld = $frm->addTextBox('Zip Code', 'zip_code', '', 'zip_code');
    $fld->requirements()->setRequired(true);
    $fld->setRequiredStarWith('none');
    $frm->addSubmitButton('', 'btn_save_shipadr', 'Proceed to Payment', 'btn_save_shipadr');
    $frm->addHiddenField('', 'mode', 'updateShippingDetails', 'mode');
    $frm->addHiddenField('', 'uaddr_id', '', 'uaddr_id');
    $frm->setValidatorJsObjectName('frm_shipiing_validator');
    $frm->setOnSubmit('updateShippingDetails(this, frm_shipiing_validator); return false;');
    return $frm;
}

function getDealReviews($dealId)
{
    global $db;
    $search = new SearchBase('tbl_reviews', 'r');
    $search->joinTable('tbl_users', 'Inner JOIN', 'u.user_id = r.reviews_user_id and reviews_type=1 AND reviews_approval=1', 'u');
    $search->addCondition('r.reviews_deal_id', '=', $dealId);
    $search->addFld('count(r.reviews_deal_id) as dealReview');
    $rs = $search->getResultSet();
    $countReview = $db->fetch($rs);
    return $countReview['dealReview'];
}

function getSubdealVoucher($subdeal_id, $company_address_id)
{
    if (intval($subdeal_id) <= 0) {
        return false;
    }
    global $db;
    $subdeal_id = intval($subdeal_id);
    $srch = new SearchBase('tbl_sub_deals', 'sd');
    $srch->addCondition('sdeal_id', '=', $subdeal_id);
    $srch->addCondition('sdeal_active', '=', 1);
    $srch->joinTable('tbl_deal_address_capacity', 'INNER JOIN', 'dac.dac_sub_deal_id=' . $subdeal_id . ' AND dac.dac_address_id=' . $company_address_id, 'dac');
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'od.od_subdeal_id=dac.dac_sub_deal_id AND od.od_company_address_id=dac.dac_address_id AND od.od_subdeal_id=' . $subdeal_id, 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->addMultipleFields(array('sd.sdeal_id', 'dac.dac_address_capacity'));
    $probation_time = date('Y-m-d H:i:s', strtotime("-30 MINUTE"));
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=2 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS refund");
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date > '" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
    $srch->addGroupBy('sdeal_id');
    $rs = $srch->getResultSet();
    if ($rows = $db->fetch($rs)) {
        $total_sold = intval($rows['sold']) + intval($rows['payment_pending']);
        $left_voucher = $rows['dac_address_capacity'] - $total_sold;
        return $left_voucher;
    }
    return false;
}

function getOnlineDealVoucher($deal_id, $subdeal_id = 0, $company_address_id, $startDate, $endDate)
{
    global $db;
    $endDate = date('Y-m-d', strtotime($endDate . ' -1 day'));
    $startDate_timestamp = strtotime($startDate);
    $endDate_timestamp = strtotime($endDate);
    if ($endDate_timestamp < $startDate_timestamp) {
        $temp = $startDate;
        $startDate = $endDate;
        $endDate = $temp;
    }
    $probation_time = date('Y-m-d H:i:s', strtotime("-30 MINUTE"));
    $srch = new SearchBase('tbl_deal_booking_dates', 'dbd');
    $srch->addCondition('dbd.dbdate_deal_id', '=', $deal_id);
    $srch->addCondition('dbd.dbdate_sub_deal_id', '=', $subdeal_id);
    $srch->addCondition('dbd.dbdate_date', 'BETWEEN', array($startDate, $endDate));
    $srch->addCondition('dbd.dbdate_company_location_id', '=', $company_address_id);
    $srch->addMultipleFields(array('min(`dbdate_stock`)as voucher_available'));
    $condition = 'dbd.dbdate_stock > (SELECT count( * )
		FROM `tbl_order_bookings` ob
		INNER JOIN tbl_order_deals AS od ON obooking_od_id = od.od_id
		INNER JOIN tbl_orders AS o ON od.od_order_id = o.order_id
		WHERE dbdate_date
		BETWEEN `obooking_booking_from`
		AND `obooking_booking_till`
		AND od.od_deal_id =' . $deal_id . '
		AND od.od_subdeal_id =' . $subdeal_id . '
		AND (o.order_payment_status=1 OR( o.order_payment_status=0 AND o.order_date > "' . $probation_time . '")) )';
    $srch->addDirectCondition($condition);
    $rs = $srch->getResultSet();
    $result = $db->fetch($rs);
    return $result['voucher_available'];
}

function checkProductQuantityAvaiable($productId, $options)
{
    if (intval($productId) <= 0) {
        return false;
    }
    global $db;
    global $msg;
    if (!empty($options)) {
        $productId = intval($productId);
        $deal_option_ids = array_keys($options);
        $deal_option_value_ids = array_values($options);
        $srch = new SearchBase('tbl_deal_option_value', 'dov');
        $srch->addCondition('deal_option_value_id', 'IN', $deal_option_value_ids, 'AND');
        $srch->addCondition('deal_option_id', 'IN', $deal_option_ids, 'AND');
        $srch->addCondition('deal_id', '=', $productId);
        $srch->addMultipleFields(array('GROUP_CONCAT(option_value_id)as id,min(quantity)as qty'));
        $rs = $srch->getResultSet();
        $count = $srch->recordCount();
        if ($count == 0) {
            //   return false;
        }
        $option_val_info = $db->fetch($rs);
        $option_val_id = $option_val_info['id'];
        $total_quantity = $option_val_info['qty'];
        if ($total_quantity < 0) {
            return false;
        }
    }
    $srch1 = new SearchBase('tbl_order_deals', 'od');
    $srch1->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $probation_time = date('Y-m-d H:i:s', strtotime("-30 MINUTE"));
    $srch1->addFld("SUM(CASE WHEN o.order_payment_status=1 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch1->addFld("SUM(CASE WHEN o.order_payment_status=2 THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS refund");
    $srch1->addFld("SUM(CASE WHEN o.order_payment_status=0 AND o.order_date > '" . $probation_time . "'  THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS payment_pending");
    $srch1->addCondition('od_deal_id', '=', $productId, 'AND');
    $srch1->addMultipleFields(array('od_id,od_id'));
    $srch1->addGroupBy('od_id');
    $rs1 = $srch1->getResultSet();
    $od_ids_info = $db->fetch_all($rs1);
    if (empty($od_ids_info)) {
        return true;
    }
    $total_sold_voucher = "";
    foreach ($od_ids_info as $key => $value) {
        $srch2 = new SearchBase('tbl_order_option', 'oo');
        $srch2->addCondition('oo.oo_od_id', '=', $value['od_id'], 'AND');
        $srch2->addFld('GROUP_CONCAT(oo_deal_option_value_id) as ID');
        $rs2 = $srch2->getResultSet();
        $data = $db->fetch($rs2);
        if ($data['ID'] == $option_val_id) {
            $total_sold_voucher += $value['sold'] + $value['payment_pending'];
        }
    }
    $total_quantity = $total_quantity - $total_sold_voucher;
    if ($total_quantity <= 0) {
        return false;
    } else {
        return $total_quantity;
    }

    return $total_quantity;
}

function emptyCartPageDiv()
{
    $str = '<section class="page__container">
        <div class="fixed_container">
            <div class="row">
                <div class="col-md-12">
                    <div class="block__empty">
                        <h6>' . unescape_attr(t_lang('M_TXT_EMPTY_CART_MSG')) . '</h6>
                        <p>' . unescape_attr(t_lang('M_TXT_ERROR_CONTENT')) . ' <a class="linknormal " href="' . friendlyUrl(CONF_WEBROOT_URL . 'all-deals.php') . '">' . t_lang('M_TXT_DEALS') . '</a>, <a class="linknormal " href="' . friendlyUrl(CONF_WEBROOT_URL . 'products.php') . '">' . t_lang('M_TXT_PRODUCTS') . '</a> and <a class="linknormal " href="' . friendlyUrl(CONF_WEBROOT_URL . 'getaways.php') . '">' . t_lang('M_TXT_GETAWAYS') . '</a></p>

                    </div>
                </div>
            </div>
       </div>
    </section> ';
    return $str;
}

function fetchUserAddress()
{
    $srch = new SearchBase('tbl_user_addresses', 'ua');
    $srch->joinTable('tbl_countries', 'INNER JOIN', 'country_id = uaddr_country_id');
    $srch->joinTable('tbl_states', 'INNER JOIN', 'state_id = uaddr_state_id');
    $srch->joinTable('tbl_cities', 'INNER JOIN', 'city_id = uaddr_city_id');
    $srch->addMultipleFields(array('ua.*', 'country_name', 'state_name', 'city_name'));
    $srch->addCondition('uaddr_user_id', '=', intval($_SESSION['logged_user']['user_id']));
    $srch->addCondition('uaddr_is_active', '=', 1);
    $srch->addCondition('uaddr_type', '=', 2);
    $srch->addOrder('uaddr_id', 'DESC');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $rs = $srch->getResultSet();
    return $rs;
}

function updateshippingAdress($post)
{
    global $db;
    global $msg;
    $frm = getShippingAddressForm();
    if (!$frm->validate($post)) {
        $errors = getValidationErrMsg($frm);
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        if (!$db->update_from_array('tbl_user_addresses', array('uaddr_is_dafault' => 0), array('smt' => 'uaddr_user_id=? AND uaddr_is_dafault = 1 AND uaddr_type = 2', 'vals' => array(intval($_SESSION['logged_user']['user_id']))))) {
            dieJsonError($db->getError());
        }
        $values = array(
            'uaddr_user_id' => intval($_SESSION['logged_user']['user_id']),
            'uaddr_name' => ($post['ship_name']),
            'uaddr_address_line1' => ($post['ship_address_line1']),
            'uaddr_address_line2' => $post['ship_address_line2'],
            'uaddr_country_id' => $post['ship_country'],
            'uaddr_state_id' => $post['ship_state'],
            //  'uaddr_city_id' => $post['ship_city'],
            'uaddr_city_name' => ($post['ship_city']),
            'uaddr_zip_code' => $post['zip_code'],
            'uaddr_is_active' => 1,
            'uaddr_type' => 2,
            'uaddr_is_dafault' => 1
        );
        $row = false;
        if (isset($post['uaddr_id']) && intval($post['uaddr_id']) > 0) {
            $srch = new SearchBase('tbl_user_addresses');
            $srch->addCondition('uaddr_id', '=', intval($post['uaddr_id']));
            $srch->addFld('uaddr_id');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getresultSet();
            $row = $db->fetch($rs);
        }
        $success = false;
        if ($row && $row['uaddr_id'] == intval($post['uaddr_id']) && intval($row['uaddr_id']) > 0) {
            if ($db->update_from_array('tbl_user_addresses', $values, array('smt' => 'uaddr_id=?', 'vals' => array(intval($row['uaddr_id']))))) {
                $success = true;
            }
        } else {
            if ($db->insert_from_array('tbl_user_addresses', $values)) {
                $success = true;
            }
        }
        return $success;
    }
}

/* @DD Paypal ipn response validate [ */

function validatePaypalIpn($req)
{

    $paypalVerifyUrl = (CONF_PAYMENT_PRODUCTION == 0) ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';

    // Step 2: POST IPN data back to PayPal to validate
    $ch = curl_init($paypalVerifyUrl);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

    // In wamp-like environments that do not come bundled with root authority certificates,
    // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
    // the directory path of the certificate as shown below:
    // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

    if (!($res = curl_exec($ch))) {
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return $res;
}

/* @DD ] */
