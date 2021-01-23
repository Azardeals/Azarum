<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/site-functions-extended.php';
require_once './includes/buy-deal-functions.php';
/* * * Code for newsletter subscription starts here *** */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subscribe_newsletter'])) {
    $post = getPostedData();
    if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['sub_email'])) {
        $msg->addError(t_lang('M_TXT_INVALID_EMAIL_ADDRESS'));
        redirectUser(CONF_WEBROOT_URL);
    } else {
        $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email==" . $db->quoteVariable($post['sub_email']) . " and subs_city=" . $db->quoteVariable($post['city']) . "");
        $result = $db->fetch($check_unique);
        if ($db->total_records($check_unique) == 0) {
            $record = new TableRecord('tbl_newsletter_subscription');
            $record->assignValues($post);
            $code = mt_rand(0, 999999999999999);
            $record->setFldValue('subs_addedon', date('Y-m-d H:i:s'), true);
            $record->setFldValue('subs_code', $code, '');
            $record->setFldValue('subs_email', $post['sub_email'], '');
            $record->setFldValue('subs_email_verified', 1, '');
            $record->setFldValue('subs_city', $post['city'], '');
            $email = $post['sub_email'];
            $success = $record->addNew();
            if ($success) {
                $rs = $db->query("select * from tbl_email_templates where tpl_id=5");
                $row_tpl = $db->fetch($rs);
                if (is_numeric($post['city'])) {
                    selectCity(intval($post['city']));
                }
                $messageAdmin = 'Dear ' . CONF_EMAILS_FROM_NAME . ',
				' . $email . ' is subscribing your newsletter.';
                $message = $row_tpl['tpl_message'];
                $subject = $row_tpl['tpl_subject'];
                $arr_replacements = array(
                    'xxemailxx' => $email,
                    'xxsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxcityxx' => $_SESSION['city_to_show'],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($_SESSION['city_to_show'] != "") {
                    if ($row_tpl['tpl_status'] == 1) {
                        sendMail($email, $subject . ' - ' . time(), emailTemplate($message));
                    }
                }
                $msg->addMsg(t_lang('M_TXT_THANKYOU_FOR_SUBSCRIBING_WITH_US'));
                redirectUser(CONF_WEBROOT_URL);
                ##############################################	
            }
        } else {
            $msg->addMsg(t_lang('M_TXT_YOU_HAVE_ALREADY_SUBSCRIBED'));
            redirectUser(CONF_WEBROOT_URL);
        }
    }
}
/* * * Code for newsletter subscription ends here *** */
if (!isUserLogged()) {
    $_SESSION['login_page'] = 'buy-deal.php';
    $msg->display();
    $login = '<a href="' . CONF_WEBROOT_URL . 'login.php">' . t_lang('M_TXT_HERE') . '</a>';
    if ($_GET['mode'] == 'chargewallet') {
        $msg->adderror(sprintf(t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN'), $login));
        require_once './msgdie.php';
    } else {
        die(t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN'));
    }
}
$post = getPostedData();
if ($_GET['mode'] == 'chargewallet')
    $post['mode'] = $_GET['mode'];
switch (strtoupper($post['mode'])) {
    case 'AUTHFORM':
        if (!isUserLogged())
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        $cart = new Cart();
        if ($cart->isEmpty()) {
            dieJsonError(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateCartItems()) {
            dieJsonError(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        $frmAuthorize = getFormAuthorize();
        $i = 0;
        $array = array(2, 8, 3, 5, 6, 7, 9);
        if ((float) $post['wallet'] > 0) {
            $frmAuthorize->addHiddenField('', 'charge_from_wallet', number_format($post['wallet'], 2));
        } else {
            $frmAuthorize->addHiddenField('', 'charge_from_wallet', '');
        }
        updateFormLang($frmAuthorize);
        while ($fld = $frmAuthorize->getFieldByNumber($i)) {
            $star = false;
            if (in_array($i, $array)) {
                $star = true;
            }
            if ($fld->fldType != "select") {
                setRequirementFieldPlaceholder($fld, $star);
            }
            $i++;
        }
        $frmAuthorize->setValidatorJsObjectName('frmValidatorCreditCardPage');
        $frmAuthorize->setOnSubmit('return setDisableCreditCardButton(frmValidatorCreditCardPage); ');
        echo '<h5>' . t_lang('M_TXT_CREDITCARD') . '</h5>
                                <div class="panel__onehalf">';
        echo $frmAuthorize->getFormTag();
        echo '<div class="grid_1"><h6>' . t_lang('M_TXT_CREDIT_CARD_HEADING') . '</h6><div class="formwrap"><table  class="formwrap__table">
                                              <tbody>
                                                <tr><td >' . $frmAuthorize->getFieldHTML('card_name') . '</td></tr><tr><td >' . $frmAuthorize->getFieldHTML('last_name') . '</td></tr><tr><td>' . $frmAuthorize->getFieldHTML('card_number') . '</td></tr><tr><td>' . $frmAuthorize->getFieldHTML('security_code') . '</td></tr><tr><td>' . $frmAuthorize->getFieldHTML('expire_month') . '</td></tr></tbody></table></div></div>
			<div class="grid_2"><h6>' . t_lang('M_TXT_BILLING_DETAIL_HEADING') . '</h6><div class="formwrap"><table class="formwrap__table">
                                <tbody>
                          <tr><td>' . $frmAuthorize->getFieldHTML('billing_address') . '</td></tr>
                          <tr><td>' . $frmAuthorize->getFieldHTML('city') . '</td></tr>
                          <tr><td>' . $frmAuthorize->getFieldHTML('state') . '</td></tr>
                          <tr><td>' . $frmAuthorize->getFieldHTML('postal_code') . '</td></tr>
                          <tr><td>' . $frmAuthorize->getFieldHTML('country') . '</td></tr>
                          </tbody></table></div></div><label class="checkbox" >' . $frmAuthorize->getFieldHTML('terms') . ' ' . $frmAuthorize->getFieldHTML('charge_from_wallet') . '</label><span class="gap"></span>' . $frmAuthorize->getFieldHTML('btn_submit') . '</form>';
        echo $frmAuthorize->getExternalJS();
        break;
    case 'REFERFRIENDS':
        $sql = $db->query("select * from tbl_email_templates where tpl_id=29");
        $email_data = $db->fetch($sql);
        $subject = $email_data['tpl_subject' . $_SESSION['lang_fld_prefix']];
        echo '<form name="user_msg_form" id="user_msg_form" action="?" method="POST" class="siteForm"  onsubmit="referFriendInfoSubmit(this.email_subject.value, this.recipients.value, this.email_message.value); return(false);">
			 <table width="100%" border="0" cellspacing="0" cellpadding="0" class="formwrap__table">
			 <tr>
			 <td colspan=2><textarea class="textBox_area" rows="" cols="" name="recipients" placeholder="' . t_lang('M_FRM_FRIENDS_EMAIL_ADDRESS_SUCCESS_PAGE') . '">' . $_POST['recipients'] . '</textarea>
			<input type="hidden" name="email_subject" id="email_subject" value="' . $subject . '"/>
			<input type="hidden" name="mode" id="mode" value="referfriendsubmit"/></td></tr>
			
			<tr>
			 <td colspan=2><textarea class="textBox_area" rows="" cols="" name="email_message" placeholder="' . t_lang('M_FRM_YOUR_MESSAGE_SUCCESS_PAGE') . '">http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] . '</textarea></td></tr>
            
			<tr><td>&nbsp;</td>
			 <td ><input type="submit" value="' . t_lang('M_TXT_SEND') . '"   name="submit_button"  class="themebtn themebtn--large"></td></tr>
            </table>
           </form>';
        break;
    case 'REFERFRIENDSSUBMIT':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['recipients'] != '' && $_POST['email_message']) {
                $recipients = $_POST['recipients'];
                $recipients = str_replace(' ', '', $recipients);
                $recipients_arr = explode(',', $recipients);
                $error = 0;
                foreach ($recipients_arr as $key => $val) {
                    $recipients_arr[$key] = trim($val, ',');
                }
                foreach ($recipients_arr as $val) {
                    if (!empty($val)) {
                        if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $val)) {
                            $error = 1;
                        }
                    }
                }
                /* $email_msg = emailTemplate(nl2br($_POST['email_message'])); */
                $sql = $db->query("select * from tbl_email_templates where tpl_id=22");
                $email_data = $db->fetch($sql);
                $subject = $_POST['email_subject'];
                $email_msg1 = $email_data['tpl_message'];
                $arr_replacements = array(
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxmessagexx' => t_lang('M_TXT_YOUR_FRIEND_HAS_REFERRED_LINK') . '<br/> ' . $_POST['email_message']
                );
                foreach ($arr_replacements as $key => $val) {
                    $email_msg1 = str_replace($key, $val, $email_msg1);
                }
                if ($error != 1) {
                    foreach ($recipients_arr as $val) {
                        sendMail($val, $subject, emailTemplate($email_msg1));
                    }
                    die(t_lang('M_TXT_MAIL_SENT'));
                } else {
                    die(t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID'));
                }
            } else {
                die(t_lang('M_ERROR_ENTER_EMAIL_ADDRESS_AND_MESSAGE'));
            }
        }
        break;
    case 'PAYPAL':
        header('Content-Type: text/plain; charset=ISO-8859-1');
        $cart = new Cart();
        if ($cart->isEmpty()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateCartItems()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateShippingCharges()) {
            die('Shipping details are not saved!!');
        }
        $showPaypalMsg = '<h5>' . t_lang('M_TXT_PROCEED_WITH_PAYPAL') . '</h5><img src="' . CONF_WEBROOT_URL . 'images/cards_payment.png" alt="" class="cards_payment"/><p>' . t_lang('M_TXT_PAYPAL_SECURELY_PAYMENTS_TEXT') . '</br>' . t_lang('M_TXT_PAY_USING_CREDIT_OR_DEBIT_CARD') . '</p>
				<a href ="javascript:void(0)" onclick="javascript:window.location.href=\'' . friendlyUrl(CONF_WEBROOT_URL . 'pay-via-paypal.php') . '\'" class="themebtn themebtn--large themebtn--org">' . t_lang('M_TXT_CLICK_TO_PAY_VIA_PAYPAL') . ' </a></div>';
        if (CONF_PAYMENT_PRODUCTION == 0) {
            $showPaypalMsg .= '<div class="dummypaypal alert alert_warning">' . t_lang('M_TXT_Please_find_below_the_dummy_credentials_for_paypal_account') . '<br/><span>' . t_lang('M_TXT_USERNAME') . ': testuser@dummyid.com </span>';
            $showPaypalMsg .= '<span>' . t_lang('M_TXT_PASSWORD') . ': AblySoft!@34</span></div>';
        }
        die($showPaypalMsg);
        break;
    case 'WALLETCHARGECONFIRMATION':
        if (!isUserLogged()) {
            die(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        $cart = new Cart();
        if ($cart->isEmpty()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateCartItems()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateShippingCharges()) {
            die('Shipping details are not saved!!');
        }
        $total_payable = $cart->getCartTotal(true);
        $rs = $db->query("select user_wallet_amount from tbl_users where user_id=" . intval($_SESSION['logged_user']['user_id']));
        $row = $db->fetch($rs);
        $wallet_amount = (float) $row['user_wallet_amount'];
        $html = '<h5>' . t_lang("M_TXT_WALLET_DETAIL") . '</h5><div class="table__whitebox" >';
        if ($wallet_amount == 0) {
            if ($total_payable == 0) {
                $html .= '<div><a href="' . CONF_WEBROOT_URL . 'buy-deal-ajax.php?mode=chargewallet" class="button red">' . t_lang('M_TXT_PAY_FROM_WALLET') . '</a></div></div></form>';
            } else {
                $html .= '' . t_lang('M_TXT_INSUFFICIENT_AMOUNT') . ' ' . CONF_CURRENCY . (($wallet_amount == '') ? '0' : $wallet_amount) . CONF_CURRENCY_RIGHT . '.</div>';
            }
            die($html);
        }
        if ($wallet_amount < $total_payable) {
            $rs = $db->query("select po_name from tbl_payment_options where po_active=1");
            while ($row = $db->fetch($rs)) {
                switch ($row['po_name']) {
                    case 'PayPal':
                        $paypal = '<a class="button red" href="' . CONF_WEBROOT_URL . 'pay-via-paypal.php?wallet=' . $wallet_amount . '">' . t_lang('M_TXT_PROCESS_ORDER_BY_PAYPAL') . '</a>';
                        break;
                    case 'Authorize.net':
                        $authorized = '<a class="button red" href="javascript:void(0);" onclick="showInfodiv(' . $wallet_amount . ');">' . t_lang('M_TXT_PROCESS_ORDER_BY_CREDITCARD') . '</a>';
                        break;
                }
            }
            $html .= '' . t_lang('M_TXT_INSUFFICIENT_AMOUNT') . ' ' . CONF_CURRENCY . round($wallet_amount, 2) . CONF_CURRENCY_RIGHT . '<br/><br>Want to be charged from wallet the please choose payment method for rest of the payment ' . CONF_CURRENCY . round(($total_payable - $wallet_amount), 2) . CONF_CURRENCY_RIGHT . ' . <span class="gap"></span>' . $paypal . '  &nbsp;&nbsp;&nbsp;&nbsp;  ' . $authorized . '</div>';
            die($html);
        }

        $html .= '<div>' . t_lang('M_TXT_AMOUNT_IN_WALLET') . '<strong>' . amount($wallet_amount, 2) . '</strong></div><div>' . t_lang('M_TXT_THIS_PURCHASE') . '<strong>' . amount($total_payable, 2) . '</strong></div><div>' . t_lang('M_TXT_BALANCE_AFTER_PURCHASE') . '<strong>' . amount(($wallet_amount - $total_payable), 2) . '</strong></div>';
        $html .= '<span class="gap"></span><a href="' . CONF_WEBROOT_URL . 'buy-deal-ajax.php?mode=chargewallet" class="themebtn themebtn--large themebtn--org"  >' . t_lang('M_TXT_PAY_FROM_WALLET') . '</a></div>';
        die($html);
        break;
    case 'CHARGEWALLET':
        require_once './site-classes/order.cls.php';
        $cart = new Cart();
        if ($cart->isEmpty()) {
            $msg->addError(t_lang('M_TXT_CART_IS_EMPTY'));
            require_once './msgdie.php';
        }
        if (!$cart->validateCartItems()) {
            $msg->addError(t_lang('M_TXT_CART_IS_EMPTY'));
            require_once './msgdie.php';
        }
        if (!$cart->validateShippingCharges()) {
            $msg->addError('Shipping details are not saved!!');
            require_once './msgdie.php';
        }
        $total_payable = $cart->getCartTotal(true);
        $rs = $db->query("select user_wallet_amount from tbl_users where user_id=" . intval($_SESSION['logged_user']['user_id']));
        $row = $db->fetch($rs);
        $wallet_amount = (float) $row['user_wallet_amount'];
        if ($wallet_amount < $total_payable) {
            $msg->addError(t_lang('M_TXT_INSUFFICIENT_AMOUNT') . ' ' . CONF_CURRENCY . round($wallet_amount, 2) . CONF_CURRENCY_RIGHT . '.');
            require_once './msgdie.php';
        }
        if (!$orderId = $cart->processOrder(3, 0, true)) {
            $msg->addMsg(t_lang('M_ERROR_ORDER_EXECUTION_ERROR') . $cart->getError());
            redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'buy-deal.php'));
        }
        $arr = array(
            'ot_order_id' => $orderId,
            'ot_transaction_id' => time(),
            'ot_transaction_status' => 1,
            'ot_gateway_response' => 'Charged from Wallet'
        );
        $db->insert_from_array('tbl_order_transactions', $arr);
        // echo "insert";
        /* Deduct from user wallet */
        $db->query("update tbl_users set user_wallet_amount = user_wallet_amount - " . round($total_payable, 2) . " where user_id=" . intval($_SESSION['logged_user']['user_id']));
        /* Deduct from user wallet ends */
        /* Update User Wallet History */
        $rs2 = $db->query("select od_qty,od_gift_qty,od_deal_price,od_deal_tax_amount,od_deal_id,od_deal_name from tbl_order_deals where od_order_id=" . $db->quoteVariable($orderId));
        while ($row2 = $db->fetch($rs2)) {
            $rsdealData = $db->query("select deal_type,deal_sub_type from tbl_deals where deal_id=" . $row2['od_deal_id']);
            $dealData = $db->fetch($rsdealData);
            $shipingCharging = 0;
            if (($dealData['deal_type'] == 1) & ($dealData['deal_sub_type'] == 0)) {
                $rsoData = $db->query('select order_shipping_charges from tbl_orders where order_id =' . "'$orderId'");
                $oData = $db->fetch($rsoData);
                $shipingCharging = $oData['order_shipping_charges'];
            }
            $totalQuantity = ($row2['od_qty'] + $row2['od_gift_qty']);
            $priceQty = $row2['od_deal_price'] + $row2['od_deal_tax_amount'] + $shipingCharging; //$total_payable; //$row2['od_deal_price'] + $row2['od_deal_tax_amount'];
            //$deal_id = $row2['od_deal_id'];
            $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $row2['od_deal_id'] . '&type=main';
            $db->insert_from_array('tbl_user_wallet_history', array(
                'wh_user_id' => $_SESSION['logged_user']['user_id'],
                'wh_particulars' => 'M_TXT_ITEM_PURCHASED' . ' ' . 'M_TXT_FROM_WALLET' . ': <a href="' . friendlyUrl($dealUrl) . '">' . $row2['od_deal_name'] . '</a>',
                /* 'wh_amount' => (- round($total_payable, 2)), */
                'wh_amount' => 0 - ($priceQty * ($totalQuantity)),
                'wh_time' => 'mysql_func_now()'
                    ), true);
        }
        /* Update User Wallet History Ends */
        /* Notify the user about the transaction for security sake */
        notifyAboutPurchase($orderId);
        $cart->clearCart();
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'success.php?dp_id=' . $orderId));
        break;
    case 'CLEARCART':
        /* we hardly care if this statement was exactly executed. So no proper response etc. As there is no full cart management. */
        unset($_SESSION['cart']);
        break;
    case 'ADDBUYCARD':
        if (((int) $_SESSION['logged_user']['user_id']) <= 0) {
            die(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if (!isUserLogged()) {
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        $cart = new Cart();
        if ($cart->isEmpty()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateCartItems()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        $srch = new SearchBase('tbl_users_card_detail', 'ucd');
        $srch->addCondition('ucd.ucd_user_id', '=', $_SESSION['logged_user']['user_id']);
        $rs = $srch->getResultSet();
        $option .= '<h5>' . strtoupper(t_lang('M_TXT_CIM')) . '</h5><form class="siteForm"  id="frmAddCardDetail" name="frmAddCardDetail" action="?" method="post"><input type="hidden" value="chargeCard" name="mode"><ul class="checkList">';
        while ($row = $db->fetch($rs)) {
            $option .= '<li><label class="radio"><input type="radio" name="card" id="card" checked="checked"  value="' . $row['ucd_customer_payment_profile_id'] . '"><i class="input-helper"></i>xxxx-xxxx-xxxx-' . $row['ucd_card'] . '</label></li> ';
        }
        $option .= '<li><label class="radio"><input type="radio" name="card" id="card" onclick="showCimInfo(\'new\');"  value="new"><i class="input-helper"></i>' . t_lang('M_TXT_ADD_NEW') . '</label></li></ul> <div style="height:15px"></div>';
        if ($db->total_records($rs) > 0 && $_POST['newCard'] != 'new') {
            $showPaypalMsg = '' . $option . '<input type="submit"  class="button_large" value="' . t_lang('M_TXT_CLICK_TO_PAY_VIA_CARD') . '" /></from>';
            die($showPaypalMsg);
        }
        if (((int) $_SESSION['logged_user']['user_customer_profile_id']) == 0) {
            if (!createCIMCustomerProfile()) { /* To create logged in user's CIM Customer profileId */
                die($msg->display());
            }
        }
        if (intval($_POST['profileId']) > 0) {
            $customerShippingAddressId = NULL;
            if ($billTo = getCIMCustomerPaymentProfile($_SESSION['logged_user']['user_customer_profile_id'], $_POST['profileId'])) {
                $firstName = $billTo->firstName;
                $lastName = $billTo->lastName;
                $address = $billTo->address;
                $city = $billTo->city;
                $state = $billTo->state;
                $zip = $billTo->zip;
            }
        }
        $arrYear = [];
        $year = date("Y");
        for ($i = $year; $i < ($year + 15); $i++) {
            $arrYear[$i] = $i;
        }
        for ($j = 1; $j <= 12; $j++) {
            if ($j < 10) {
                $arrMonth['0' . $j] = '0' . $j;
            } else {
                $arrMonth[$j] = $j;
            }
        }
        $frm = new Form('frmAddCardDetail', 'frmAddCardDetail');
        $frm->setTableProperties(' width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
        $frm->setFieldsPerRow(1);
        $frm->setExtra('class="siteForm"');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setRequiredStarWith('caption');
        $frm->captionInSameCell(true);
        $fld = $frm->addTextBox(t_lang('M_FRM_CARD_HOLDER_FIRST_NAME'), 'firstName', $firstName, '', '');
        $fld->requirements()->setRequired(true);
        $fld->setRequiredStarWith('none');
        $frm->addTextBox(t_lang('M_FRM_CARD_HOLDER_LAST_NAME'), 'lastName', $lastName, '', '');
        $fld_card_num = $frm->addIntegerField(t_lang('M_FRM_CARD_NUMBER'), 'cardNumber', '', '', ' maxlength=16 class="fl"');
        $fld_card_num->requirements()->setRequired(true);
        $fld_card_num->setRequiredStarWith('none');
        $fld_card_num->requirements()->setLength(13, 16);
        $month = t_lang('M_FRM_MONTH');
        $fld_exp_date = $frm->addSelectBox(t_lang('M_FRM_EXPIRY_DATE'), 'expirationDate', $arrMonth, '', "class='month' title=$month", 'Month', '');
        $fld_exp_date->requirements()->setRequired();
        $fld_exp_date->setRequiredStarWith('none');
        $year = t_lang('M_FRM_YEAR');
        $fld_exp = $frm->addSelectBox(t_lang('M_FRM_EXPIRY_DATE'), 'expirationDateYear', $arrYear, '', "class='year' title=$year", 'Year', '');
        $fld_exp->requirements()->setRequired();
        $fld_exp->setRequiredStarWith('none');
        $frm->addTextBox('Street Address', 'address1', $address, '', '');
        $frm->addTextBox('Street Address2', 'address2', '', '', '');
        $frm->addTextBox(t_lang('M_FRM_CITY'), 'city', $city, '', '');
        $frm->addTextBox(t_lang('M_FRM_STATE'), 'state', $state, '', '');
        $frm->addTextBox(t_lang('M_FRM_ZIP_CODE'), 'zip', $zip, '', '');
        $frm->addHiddenField('', 'customerProfileId', $_SESSION['logged_user']['user_customer_profile_id']);
        $frm->addHiddenField('', 'paymentProfile', $_POST['profileId']);
        $frm->addHiddenField('', 'mode', 'chargeCard');
        $frm->addHiddenField('', 'status', $_REQUEST['status']);
        $frm->addSubmitButton('', 'submit', t_lang('M_TXT_SUBMIT'), 'submit', '');
        $i = 0;
        $array = array(0, 2, 6);
        while ($fld = $frm->getFieldByNumber($i)) {
            $star = false;
            if (in_array($i, $array)) {
                $star = true;
            }
            if ($fld->fldType != "select") {
                setRequirementFieldPlaceholder($fld, $star);
            }
            $i++;
        }
        echo '<div class="panel__onehalf">';
        echo $msg->display();
        echo $frm->getFormTag();
        echo '<div class="grid_1">     <h6>Personal Information</h6><div class="formwrap"><table class="formwrap__table">
                          <tbody>
                          <tr><td>' . $frm->getFieldHTML('firstName') . '</td></tr>
                          <tr><td>' . $frm->getFieldHTML('lastName') . '</td></tr>
                          <tr><td>' . $frm->getFieldHTML('cardNumber') . '</td></tr>
                          <tr><td>' . $frm->getFieldHTML('expirationDate') . '' . $frm->getFieldHTML('expirationDateYear') . '</td></tr>
			  <tr><td>' . $frm->getFieldHTML('paymentProfile') . '</td></tr>
                       </tbody>
			</table></div></div>	<div class="grid_2">
                         <h6>Billing Information</h6><div class="formwrap">	<table class="formwrap__table"> 
                         <tbody>			 
                         <tr>
                            <td>' . $frm->getFieldHTML('address1') . '</td>
                          </tr>
                          <tr>
                            <td>
                               ' . $frm->getFieldHTML('city') . '</td></tr>
                               <tr> <td>' . $frm->getFieldHTML('state') . '</td></tr>
                             <tr><td>' . $frm->getFieldHTML('zip') . '</td></tr>
							</tbody></table></div></div><span class="gap"></span>' . $frm->getFieldHTML('submit') . $frm->getFieldHTML('mode') . $frm->getFieldHTML('customerProfileId') . $frm->getFieldHTML('status') . '
								
						</form>' . $frm->getExternalJS() . '
					  <span class="gap"></span>
                    	<h6>' . t_lang('M_TXT_IS_MY_PERSONAL_INFORMATION_SAFE') . '</h6>
                        <p>' . t_lang('M_TXT_YES') . ' ' . t_lang('M_TXT_CREDITCARD_INFORMATION_IS_SECURE') . '</p>
                    </div>
                </div>';
        break;
    case 'LOADSHIPPINGFORM':
        if (!isUserLogged()) {
            die(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        $cart = new Cart();
        if ($cart->isEmpty()) {
            die('emptyCart');
        }
        if (!$cart->validateCartItems()) {
            die('emptyCart');
        }
        $cart_deal_ids = array_column($cart->getCart(), 'cart_item_deal_id');
        if (!is_array($cart_deal_ids) || count($cart_deal_ids) <= 0) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        $products_in_cart = getTotalProductsInCart($cart_deal_ids);
        if ($products_in_cart <= 0) {
            die('1');
        }
        ?>
        <!-- <h2>Choose Shipping Address</h2>-->
        <?php
        $rs = fetchUserAddress();
        $default_addr = false;
        $addresses = [];
        if ($db->total_records($rs) > 0) {
            ?>
            <div class="tabs__bordered address clearfix">   
                <ul class="add_list">
                    <?php
                    while ($row = $db->fetch($rs)) {
                        if ($row['uaddr_is_dafault'] == 1) {
                            $addresses[$row['uaddr_id']] = $default_addr = array(
                                'ship_name' => $row['uaddr_name'],
                                'ship_address_line1' => $row['uaddr_address_line1'],
                                'ship_address_line2' => $row['uaddr_address_line2'],
                                'ship_country' => $row['uaddr_country_id'],
                                'ship_state' => $row['uaddr_state_id'],
                                'ship_city' => $row['uaddr_city_name'],
                                'ship_city_name' => $row['uaddr_city_name'],
                                'zip_code' => $row['uaddr_zip_code'],
                                'uaddr_id' => $row['uaddr_id'],
                                'deafult' => $row['uaddr_is_dafault']
                            );
                        } else {
                            $addresses[$row['uaddr_id']] = array(
                                'ship_name' => $row['uaddr_name'],
                                'ship_address_line1' => $row['uaddr_address_line1'],
                                'ship_address_line2' => $row['uaddr_address_line2'],
                                'ship_country' => $row['uaddr_country_id'],
                                'ship_state' => $row['uaddr_state_id'],
                                'ship_city' => $row['uaddr_city_name'],
                                'ship_city_name' => $row['uaddr_city_name'],
                                'zip_code' => $row['uaddr_zip_code'],
                                'uaddr_id' => $row['uaddr_id'],
                                'deafult' => $row['uaddr_is_dafault']
                            );
                        }
                        ?>
                        <li <?php
                        echo ($row['uaddr_is_dafault'] == 1 ) ? 'class="active"' : '';
                        $address2 = '';
                        if ($row['uaddr_address_line2'] != "") {
                            $address2 = $row['uaddr_address_line2'] . ',';
                        }
                        ?>>
                            <a href="javascript:void(0)" <?php echo ($row['uaddr_is_dafault'] == 1 ) ? 'class="active"' : ''; ?> onclick="setAddress(this, <?php echo intval($row['uaddr_id']); ?>);"><?php echo htmlentities($row['uaddr_name']) . ', ' . $row['uaddr_address_line1'] . ', ' . $address2 . ' ' . $row['uaddr_city_name'] . ', ' . $row['state_name'] . ', ' . $row['country_name'] . ', ' . $row['uaddr_zip_code']; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>   
            <?php
        }
        $frmShipping = getShippingAddressForm();
        if ($default_addr)
            $frmShipping->fill($default_addr);
        $i = 0;
        $array = array(0, 1, 5, 6);
        while ($fld = $frmShipping->getFieldByNumber($i)) {
            $star = false;
            if (in_array($i, $array)) {
                $star = true;
            }
            if ($fld->fldType != "select") {
                setRequirementFieldPlaceholder($fld, $star);
            }
            $i++;
        }
        ?>
        <div class="form__cover">
            <div class="formwrap">
                <?php echo $frmShipping->getFormTag(); ?>
                <table class="formwrap__table" width="100%" cellspacing="0" cellpadding="0" border="0" >
                    <tr>
                        <td colspan="2" ><?php echo $frmShipping->getFieldHTML('ship_name'); ?></td>
                    </tr>
                    <tr>
                        <td>	<?php echo $frmShipping->getFieldHTML('ship_address_line1'); ?></td>
                        <td class=""> <?php echo $frmShipping->getFieldHTML('ship_address_line2'); ?></td>	
                    </tr>
                    <tr>
                        <td><?php echo $frmShipping->getFieldHTML('ship_country'); ?></td>
                        <td ><?php echo $frmShipping->getFieldHTML('ship_state'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $frmShipping->getFieldHTML('ship_city'); ?></td>
                        <td>
                            <?php echo $frmShipping->getFieldHTML('zip_code'); ?> </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="btn-list">
                                <?php
                                $frmShipping->setValidatorJsObjectName('frm_shipiing_validator');
                                $fld = $frmShipping->getField('btn_save_shipadr');
                                $fld->value = t_lang('M_TXT_PROCEED_TO_CHECKOUT');
                                echo $frmShipping->getFieldHTML('btn_save_shipadr');
                                ?>
                                <?php echo $frmShipping->getFieldHTML('mode'); ?>
                                <?php echo $frmShipping->getFieldHTML('uaddr_id'); ?>
                            </div>  </td>
                    </tr>
                </table>
                </form>
                <?php echo $frmShipping->getExternalJs(); ?>
            </div>
            <script type="text/javascript">
                var addresses = <?php echo json_encode($addresses); ?>;
            </script>
        </div>
        <?php
        break;
    case 'LOADPAYMENTMETHODS':
        if (!isUserLogged())
            die(t_lang('M_TXT_SESSION_EXPIRES'));
        $cart = new Cart();
        if ($cart->isEmpty()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateCartItems()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        $cart_total = $cart->getCartTotal();
        ?>
        <div class="payments tabs__bordered normaltabs clearfix">
            <ul class="linkslarge clearfix">
                <?php
                echo '<li><a class="active" href="javascript:void(0);" onclick="walletChargeConfirmation()" rel="tabs1"><strong>' . t_lang('M_TXT_MY_WALLET') . '</strong></a></li>';
                $mobile_tabs .= '<span rel="tabs1" class="togglehead" onclick="walletChargeConfirmation()" >' . strtoupper(t_lang('M_TXT_MY_WALLET')) . '</span><div id="tabs1" class="tabs_content"></div>';
                $rs = $db->query("select * from tbl_payment_options where po_active=1");
                while ($row = $db->fetch($rs)) {
                    switch ($row['po_name']) {
                        case 'PayPal':
                            echo '<li><a href="javascript:void(0);" onclick="redirectPaypal();" rel="tabs2"><strong>' . t_lang('M_TXT_PAYPAL') . '</strong></a></li>';
                            $mobile_tabs .= '<span rel="tabs2" class="togglehead" onclick="redirectPaypal();">' . strtoupper(t_lang('M_TXT_PAYPAL')) . '</span><div id="tabs2" class="tabs_content"></div>';
                            break;
                        case 'Authorize.net':
                            echo '<li><a href="javascript:void(0);" onclick="showInfodiv();" rel="tabs3"><strong>' . t_lang('M_TXT_CREDITCARD') . '</strong>
                        </a></li>';
                            $mobile_tabs .= '<span rel="tabs3" class="togglehead" onclick="showInfodiv();">' . strtoupper(t_lang('M_TXT_CREDITCARD')) . '</span><div id="tabs3" class="tabs_content"></div>';
                            break;
                        case 'CIM':
                            echo '<li><a href="javascript:void(0);" onclick="showCimInfo();" rel="tabs4"><strong>' . strtoupper(t_lang('M_TXT_CIM')) . '</strong></a></li>';
                            $mobile_tabs .= '<span rel="tabs4" class="togglehead" onclick="showCimInfo();">' . strtoupper(t_lang('M_TXT_CIM')) . '</span><div id="tabs4" class="tabs_content"></div>';
                            break;
                    }
                }
                ?>
            </ul>
        </div>
        <div class="tabspanel__container wrap__grey "><?php echo $mobile_tabs; ?></div>
        <?php
        break;
    case 'LOADSTATES':
        if (!isset($post['country_id']) || intval($post['country_id']) < 1) {
            die(convertToJson(array('status' => 1, 'states' => '')));
            // dieJsonError('Invalid Request!!');
        }
        $sList = $db->query("select state_id, state_name from `tbl_states` where state_status='A' AND state_country = '" . intval($post['country_id']) . "'");
        $s_opts = $db->fetch_all_assoc($sList);
        if (is_array($s_opts)) {
            die(convertToJson(array('status' => 1, 'states' => $s_opts)));
        }
        dieJsonError('No States Found!!');
        break;
    case 'LOADCITIES':
        if (!isset($post['state_id']) || intval($post['state_id']) < 1) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $cList = $db->query("select city_id, city_name from `tbl_cities` where city_active='1' AND city_state = '" . intval($post['state_id']) . "' AND city_id>0");
        $c_opts = $db->fetch_all_assoc($cList);
        if (is_array($c_opts)) {
            die(convertToJson(array('status' => 1, 'cities' => $c_opts)));
        }
        dieJsonError('No Cities Found!!');
        break;
    case 'LOADREVIEWDATA':
        if (!isUserLogged()) {
            die(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        $cart = new Cart();
        if ($cart->isEmpty()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        if (!$cart->validateCartItems()) {
            die(t_lang('M_TXT_CART_IS_EMPTY'));
        }
        ?>
        <div class="cart__table">
            <div class="cart__body">
                <?php
                $cart_data_arr = $cart->getProducts();
                if (is_array($cart_data_arr) || sizeof($cart_data_arr) > 0) {
                    foreach ($cart_data_arr as $cart_data) {
                        //   print_r($cart_data);
                        ?>
                        <div class="cart__row">
                            <div class="grid_1">
                                <div class="item">
                                    <div class="item__head">
                                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'deal.php?deal=' . $cart_data['deal_id'] . '&type=main'); ?>"><img alt="" src="<?php echo CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $cart_data['deal_id'] . '&type=carttable'; ?>"></a>  
                                    </div>
                                    <div class="item__body">
                                        <div class="top">
                                            <span class="item__title"> <?php echo appendPlainText($cart_data['deal_name']); ?></span>
                                            <p><?php echo t_lang('M_TXT_SOLD_BY'); ?> : <strong><?php echo $cart_data['company_name']; ?></strong></p>
                                            <?php
                                            if ($cart_data['deal_type'] == 1) {
                                                if (isset($cart_data['option']) && is_array($cart_data['option']) && count($cart_data['option'])) {
                                                    echo '<p>';
                                                    echo t_lang('M_TXT_OPTIONS') . ':';
                                                    $str = "";
                                                    foreach ($cart_data['option'] as $op) {
                                                        $str .= $op['option_name'] . ': <strong>' . $op['option_value'] . ' (Price: ' . $op['price_prefix'] . ' ' . CONF_CURRENCY . round($op['price'], 2) . CONF_CURRENCY_RIGHT . ')</strong>';
                                                        $str .= '|';
                                                    }
                                                    echo rtrim($str, '|');
                                                    echo '</p>';
                                                }
                                            }
                                            ?>
                                            <p><?php echo t_lang('M_TXT_QUANTITY'); ?> : <strong><?php echo $cart_data['qty']; ?></strong></p>
                                        </div>    
                                    </div>    
                                </div>
                            </div>
                            <div class="grid_3">
                                <div class="item__price">
                                    <span class="item__price_standard"><?php echo amount($cart_data['price'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <input type="submit" onclick="loadPage('payment')"value="<?php echo t_lang('M_TXT_PROCEED_TO_CHECKOUT'); ?>">
        <a class="linknormal linkedit" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'buy-deal.php'); ?>"><?php echo t_lang('M_TXT_EDIT_BAG'); ?></a>
        <span class="gap"></span>
        <?php
        break;
}