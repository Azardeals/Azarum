<?php
require_once './application-top.php';
require_once './cim-xml/util.php';

function createCIMCustomerProfile()
{
    global $msg;
    global $db;
    $email = $_SESSION['logged_user']['user_email'];
    $uniqueCustomerId = rand(1, 9999999999);
    if (!validateOtEmail($email)) {
        return false;
    }
    $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            MerchantAuthenticationBlock() .
            "<profile>" .
            "<merchantCustomerId>" . $uniqueCustomerId . "</merchantCustomerId>" . // Your own identifier for the customer.
            "<description></description>" .
            "<email>" . $email . "</email>" .
            "</profile>" .
            "</createCustomerProfileRequest>";
    $response = send_xml_request($content);
    $parsedresponse = parse_api_response($response);
    if ("Ok" == $parsedresponse->messages->resultCode) {
        $customerProfileId = htmlspecialchars($parsedresponse->customerProfileId);
        if (!$db->query("UPDATE  tbl_users SET user_customer_profile_id=" . $customerProfileId . " WHERE user_id=" . intval($_SESSION['logged_user']['user_id']))) {
            $msg->addError($db->getError());
        }
        $_SESSION['logged_user']['user_customer_profile_id'] = $customerProfileId;
        return true;
    } else {
        $msg->addError($parsedresponse->messages->message->text . '&nbsp;');
    }
    return false;
}

function createCIMCustomerPaymentProfile($data = [])
{
    global $msg;
    if (CONF_PAYMENT_PRODUCTION == 0) {
        $payMode = 'testMode';
    } else {
        $payMode = 'liveMode';
    }
    //build xml to post
    $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            MerchantAuthenticationBlock() .
            "<customerProfileId>" . intval($data["customerProfileId"]) . "</customerProfileId>" .
            "<paymentProfile>" .
            "<billTo>" .
            "<firstName>" . $data["firstName"] . "</firstName>" .
            "<lastName>" . $data["lastName"] . "</lastName>" .
            "<address>" . $data["address1"] . " </address>" .
            "<city>" . $data["city"] . "</city>" .
            "<state>" . $data["state"] . "</state>" .
            "<zip>" . $data["zip"] . "</zip>" .
            "<phoneNumber>000-000-0000</phoneNumber>" .
            "</billTo>" .
            "<payment>" .
            "<creditCard>" .
            "<cardNumber>" . $data["cardNumber"] . "</cardNumber>" .
            "<expirationDate>" . $data["expirationDateYear"] . "-" . $data["expirationDate"] . "</expirationDate>" . // required format for API is YYYY-MM
            "</creditCard>" .
            "</payment>" .
            "</paymentProfile>" .
            "<validationMode>" . $payMode . "</validationMode>" . // or testMode liveMode
            "</createCustomerPaymentProfileRequest>";
    $response = send_xml_request($content);
    $parsedresponse = parse_api_response($response);
    if ("Ok" == $parsedresponse->messages->resultCode) {
        return $parsedresponse->customerPaymentProfileId;
    } else {
        $error = $parsedresponse->messages->message->text . '&nbsp;';
        // $msg->addError($parsedresponse->messages->message->text . '&nbsp;');
        return array('error' => $error);
    }
}

function getCIMCustomerPaymentProfile($customer_profile, $customer_payment_profile)
{
    global $msg;
    //build xml to post
    $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<getCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            MerchantAuthenticationBlock() .
            "<customerProfileId>" . intval($customer_profile) . "</customerProfileId>" .
            "<customerPaymentProfileId>" . intval($customer_payment_profile) . "</customerPaymentProfileId>" .
            "</getCustomerPaymentProfileRequest>";
    $response = send_xml_request($content);
    $parsedresponse = parse_api_response($response);
    if ("Ok" == $parsedresponse->messages->resultCode) {
        return $parsedresponse->paymentProfile->billTo;
    } else {
        $msg->addError($parsedresponse->messages->message->text);
    }
    return false;
}

function deleteCIMCustomerPaymentProfile($payment_profile_id)
{
    global $msg;
    if (intval($payment_profile_id) <= 0)
        return false;
    //build xml to post
    $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
			<deleteCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            MerchantAuthenticationBlock() .
            "<customerProfileId>" . $_SESSION['logged_user']['user_customer_profile_id'] . "</customerProfileId>" .
            "<customerPaymentProfileId>" . $payment_profile_id . "</customerPaymentProfileId>" .
            "</deleteCustomerPaymentProfileRequest>";
    $response = send_xml_request($content);
    $parsedresponse = parse_api_response($response);
    if ("Ok" == $parsedresponse->messages->resultCode) {
        return true;
    } else {
        // $msg->addError($parsedresponse->messages->message->text);
        return false;
    }
    return false;
}

function addCardDetailForm($post = '')
{
    error_reporting(0);
    global $msg;
    global $db;
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
    $frm->setAction($_SERVER['PHP_SELF']);
    $frm->setTableProperties(' width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable"');
    $frm->setFieldsPerRow(1);
    $frm->setExtra('class="siteForm"');
    $frm->setJsErrorDisplay('afterfield');
    $frm->setRequiredStarWith('none');
    $frm->setRequiredStarPosition('none');
    $frm->setValidatorJsObjectName('frmValidatorCardDetail');
    $frm->setOnSubmit('updateCardInfo(frmAddCardDetail, frmValidatorCardDetail); ');
    $frm->captionInSameCell(true);
    $fld = $frm->addTextBox(t_lang('M_FRM_CARD_HOLDER_FIRST_NAME'), 'firstName', $firstName, '', '');
    $fld->requirements()->setRequired(true);
    $fld->setRequiredStarPosition('none');
    $fld = $frm->addTextBox(t_lang('M_FRM_CARD_HOLDER_LAST_NAME'), 'lastName', $lastName, '', '');
    $fld->requirements()->setRequired(true);
    $fld->setRequiredStarPosition('none');
    $fld_card_num = $frm->addIntegerField(t_lang('M_FRM_CARD_NUMBER'), 'cardNumber', '', '', ' maxlength=16 class="fl"');
    $fld_card_num->requirements()->setRequired(true);
    //  $fld_card_num->extra='placeholder='. t_lang('M_FRM_CARD_NUMBER') . '*';
    $fld_card_num->setRequiredStarWith('none');
    $fld_card_num->requirements()->setLength(13, 16);
    $fld_exp_date = $frm->addSelectBox(t_lang('M_FRM_EXPIRY_DATE'), 'expirationDate', $arrMonth, '', 'class="fieldLeft" title="Month"', 'Month', '');
    $fld_exp_date->requirements()->setRequired();
    $fld_exp_date->setRequiredStarWith('none');
    //    $fld_exp_date->extra='placeholder='. t_lang('M_FRM_EXPIRY_MONTH') ;
    $fld_exp = $frm->addSelectBox(t_lang('M_FRM_EXPIRY_DATE'), 'expirationDateYear', $arrYear, '', 'class="fieldRight" title="Year"', 'Year', '');
    $fld_exp->requirements()->setRequired();
    $fld_exp->setRequiredStarWith('none');
    //  $fld_exp->extra='placeholder="'. t_lang('M_FRM_EXPIRY_DATE') . '*"';
    $srch = new SearchBase('tbl_countries', 'c');
    $srch->addCondition('c.country_status', '=', 'A');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addFld('country_id');
    $srch->addFld('country_name' . $_SESSION['lang_fld_prefix']);
    $srch->addOrder('country_name');
    $rs = $srch->getResultSet();
    $arr_options = $db->fetch_all_assoc($rs);
    $frm->addSelectBox(t_lang('M_FRM_COUNTRY'), 'country_id', $arr_options, $_POST['country_id'], 'onchange="updateStates(this.value);"', t_lang('M_TXT_SELECT'), 'country_id');
    $frm->addSelectBox(t_lang('M_FRM_STATE'), 'state_id', '', $_POST['state_id'], 'onchange="updateStateName();"', t_lang('M_TXT_SELECT'), 'state_id');
//	$frm->addTextBox(t_lang('M_FRM_STATE'), 'state', $state, '', '');
    $frm->addHiddenField('', 'state', $state, 'state', '');
    $frm->addTextBox(t_lang('M_FRM_CITY'), 'city', $city, '', '');
    $frm->addTextBox('Street Address', 'address1', $address, '', '');
    $frm->addTextBox('Street Address2', 'address2', '', '', '');
    $frm->addTextBox(t_lang('M_FRM_ZIP_CODE'), 'zip', $zip, '', '');
    $frm->addHiddenField('', 'customerProfileId', $_SESSION['logged_user']['user_customer_profile_id']);
    $frm->addHiddenField('', 'paymentProfile', $_POST['profileId']);
    $frm->addHiddenField('', 'mode', 'updateCardInfo');
    $frm->addHiddenField('', 'status', $_REQUEST['status']);
    $frm->addSubmitButton('', 'btn_submit', '', 'btn_submit', '');
    $fld = $frm->getField('btn_submit');
    //$fld->extra='onclick="return doSubmitFormAjax()"';
    $fld1 = $frm->addHtml('', '', '<input type="button" class="link__addcard" value="Cancel">');
    $fld->attachField($fld1);
    $fld->value = t_lang('M_TXT_SUBMIT');
    $i = 0;
    while ($fld = $frm->getFieldByNumber($i)) {
        $star = false;
        if ($i <= 5) {
            $star = true;
        }
        if ($fld->fldType != "select") {
            setRequirementFieldPlaceholder($fld, $star);
        }
        $i++;
    }
    if ($post != "") {
        $frm->fill($post);
    }
    ?>
    <!--<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>page-js/jquery.form.js"></script>-->
    <script type="text/javascript">
        var selectedState = 0;
        var countryId = "<?php echo $_POST['country_id'] ?>";
        if (countryId != 0 && countryId != 'undefined' && countryId !== '') {
            selectedState = "<?php echo $_POST['state_id'] ?>";
            $('#country_id').trigger('change');
        }
        function doSubmitFormAjax()
        {
            $('#frmAddCardDetail').ajaxForm({
                success: function (data, status, xhr) {
                    location.href = webroot + 'my-account.php';
                }
                /* target: ''*/
            }).submit();
            //$.facebox('<img src="'+webroot+'facebox/loading.gif">');
            return false;
        }
        function updateStateName() {
            $('#state').val($('#state_id option:selected').text());
        }
        var value = '<?php echo t_lang("M_TXT_SELECT_COUNTRY_FIRST"); ?>';
        var selectCountryFirst = '<option value="">' + value + '</option>';
    </script>
    <?php
    $str = '<div class="coverwrap">';
    // $str.= $msg->display();
    $str .= $frm->getFormTag();
    $str .= '<div class="panel__onehalf"><div class="grid_1">     <h6>' . t_lang('M_FRM_PERSONAL_INFORMATION') . '</h6><div class="formwrap"><table  class="formwrap__table">
					  <tbody>
							<tr><td colspan=2>' . $frm->getFieldHTML('firstName') . '</td></tr>
							<tr><td colspan=2>' . $frm->getFieldHTML('lastName') . '</td></tr>
							<tr><td colspan=2>' . $frm->getFieldHTML('cardNumber') . '</td></tr>
                          
							<tr><td>' . $frm->getFieldHTML('expirationDate') . '</td><td>' . $frm->getFieldHTML('expirationDateYear') . '</td></tr>
							<tr><td colspan=2>' . $frm->getFieldHTML('customerProfileId') . $frm->getFieldHTML('paymentProfile') . '</td></tr>
							</tbody>
						</table></div></div>	
						<div class="grid_2">
							<h6>' . t_lang('M_FRM_BILLING_INFORMATION') . '</h6><div class="formwrap">	
							<table class="formwrap__table"> 
								<tbody>	
									<tr> <td>' . $frm->getFieldHTML('country_id') . '</td><td >' . $frm->getFieldHTML('state_id') . $frm->getFieldHTML('state') . '</td></tr>
								
									<tr><td colspan=2>' . $frm->getFieldHTML('city') . '</td></tr>
									<tr><td colspan=2>' . $frm->getFieldHTML('address1') . '</td></tr>
									<tr><td colspan=2>' . $frm->getFieldHTML('zip') . '</td></tr>
							
                            </tbody></table></div></div><span class="gap"></span>
							' . $frm->getFieldHTML('btn_submit') . $frm->getFieldHTML('mode') . '
								
						</form>' . $frm->getExternalJS() . '
					  <span class="gap"></span><span class="gap"></span>
                    	<h6>' . t_lang('M_TXT_IS_MY_PERSONAL_INFORMATION_SAFE') . '</h6>
                        <p>' . t_lang('M_TXT_YES') . ' ' . t_lang('M_TXT_CREDITCARD_INFORMATION_IS_SECURE') . '</p>
                    </div>
                </div>';
    return $str;
}
