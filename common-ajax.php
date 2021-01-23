<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/buy-deal-functions.php';
$post = getPostedData();

function getGiveGiftForm()
{
    $frm = new Form('frmGiftDeal');
    $frm->setExtra('class="siteForm"');
    $frm->setJsErrorDisplay('afterfield');
    $frm->setTableProperties('class="formtable" width="100%"');
    $frm->setRequiredStarWith('caption');
    $frm->captionInSameCell(true);
    $frm->setFieldsPerRow(2);
    $name = t_lang('M_TXT_RECIPIENT') . ' \'s ' . t_lang('M_FRM_NAME');
    $frm->addRequiredField($name, 'to_name', '', 'to_name');
    $frm->addEmailField(t_lang('M_FRM_ENTER_YOUR_EMAIL_ADDRESS'), 'to_email', '', 'to_email')->requirements()->setRequired(true);
    $fld = $frm->addTextArea(t_lang('M_FRM_YOUR_MESSAGE_SUCCESS_PAGE') . ' <span style="font-size:.7em;">(' . t_lang('M_FRM_RECIPIENT_MESSAGE_IN_TAG') . ')</span>', 'to_msg', '', 'to_msg');
    $fld->merge_cells = 2;
    $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), 'btn_submit');
    $frm->addHiddenField('', 'deal_id', '', 'deal_id');
    $frm->addHiddenField('', 'key', '', 'key');
    $frm->addHiddenField('', 'mode', 'saveGiftDetails');
    $frm->setValidatorJsObjectName('frmGiftDetails_validator');
    $frm->setOnSubmit('return saveGiftDetails(this, frmGiftDetails_validator);');
    return $frm;
}

switch (strtoupper($post['mode'])) {
    case 'LISTCITIES':
        $srch = new SearchBase('tbl_cities', 'c');
        $srch->addCondition('c.city_active', '=', 1);
        $srch->addCondition('c.city_deleted', '=', 0);
        $srch->addCondition('c.city_request', '=', 0);
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'c.city_id = d.deal_city and d.deal_status = 1 and d.deal_deleted = 0 ', 'd');
        $srch->addFld("DISTINCT UCASE(SUBSTRING(city_name, 1, 1)) AS ch");
        $srch->addOrder('ch');
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $chars = [];
        while ($row = $db->fetch($rs)) {
            $chars[] = $row['ch'];
        }
        $str = '<ul class="listing_cities_character"><li class="first">' . t_lang('M_TXT_SEARCH_BY_CHARACTER') . '</li>';
        foreach ($chars as $char) {
            $str .= '<li><a href="javascript:void(0);" onclick="listCitiesForSelection(\'' . $char . '\');">' . $char . '</li>';
        }
        $str .= '</ul><span ><a class="closeCity"  href="javascript:void(0);" onclick="hideCitySelector();">&nbsp;</a></span> <div class="clear"></div>';
        if (strlen($post['c']) == 1) {
            $char = $post['c'];
        } else {
            if (isset($_SESSION['cityname'])) {
                $char = strtoupper(substr($_SESSION['cityname'], 0, 1));
            } else {
                $char = $chars[0];
            }
        }
        $srch = new SearchBase('tbl_cities');
        $srch->addCondition('city_active', '=', 1);
        $srch->addCondition('city_deleted', '=', 0);
        $srch->addCondition('city_request', '=', 0);
        $srch->addCondition('city_name', 'LIKE', $char . '%');
        $srch->addOrder('city_name');
        $srch->addMultipleFields(array('city_id', 'city_name'));
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $count = $db->total_records($rs);
        $str .= '<ul class="listing_cities">';
        $countCity = 0;
        while ($row = $db->fetch($rs)) {
            $countCity++;
            $srchDeal = new SearchBase('tbl_deals', 'd');
            $srchDeal->addCondition('deal_city', '=', $row['city_id']);
            $srchDeal->addCondition('deal_status', '=', 1);
            $srchDeal->doNotLimitRecords();
            $srchDeal->doNotCalculateRecords();
            $rs1 = $srchDeal->getResultSet();
            $count1 = $db->total_records($rs1);
            if ($countCity == 1) {
                $text = '<li>' . t_lang('M_TXT_WE_FOUND_FOLLOWING_CITIES') . '</li> <div class="clear"></div>';
            } else {
                $text = '';
            }
            if ($count1 > 0) {
                $str .= $text . '<li><a href="javascript:void(0);" onclick="selectCity(' . $row['city_id'] . ', ' . CONF_FRIENDLY_URL . ');">' . $row['city_name'] . ' ( ' . $count1 . ' )</a></li>';
            } else {
                $str .= $text;
            }
        }
        $str .= '</ul> ';
        die($str);
        break;
    case 'GETADDRESS':
        if (!is_numeric($post['company'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $rs = $db->query("select * from tbl_company_addresses where company_id=" . $post['company']);
        if (!$row_deal = $db->fetch($rs)) {
            dieJsonError(t_lang('M_MSG_NO_ADDRESS_IS_ADDED_PLEASE_ADD_ADDRESS_FIRST'));
        }
        if (!is_numeric($post['deal_id'])) {
            $post['deal_id'] = 0;
        }
        $deal_data = $db->fetch($db->query("select deal_sub_type from tbl_deals where deal_id=" . intval($post['deal_id'])));
        if ($deal_data['deal_sub_type'] == 2) {
            $caption = t_lang('M_TXT_CAPACITY_PER_DAY');
        } else {
            $caption = t_lang('M_TXT_CAPACITY');
        }
        $srch = new SearchBase('tbl_company_addresses', 'ca');
        $srch->addCondition('ca.company_id', '=', $post['company']);
        $srch->joinTable('tbl_deal_address_capacity', 'LEFT OUTER JOIN', 'ca.company_address_id = dac.dac_address_id and dac.dac_deal_id = ' . intval($post['deal_id']) . ' and dac.dac_sub_deal_id = 0 ', 'dac');
        if ($_SESSION['lang_fld_prefix'] == '_lang1') {
            $srch->addFld("CONCAT(company_address_line1_lang1, '<br/>', company_address_line2_lang1, '<br/>', company_address_line3_lang1,  '<br/>', company_address_zip, ' ') AS address");
        } else {
            $srch->addFld("CONCAT(company_address_line1, '<br/>', company_address_line2, '<br/>', company_address_line3,  '<br/>', company_address_zip, ' ') AS address");
        }
        $srch->addFld("CASE WHEN dac.dac_id IS NULL THEN 0 ELSE dac.dac_address_capacity END AS capacity");
        $srch->addMultipleFields(array('ca.*', 'dac.*'));
        $rs = $srch->getResultSet();
        if ($db->total_records($rs) > 0) {
            $frm = new Form('frmDealAddress', 'frmDealAddress');
            $frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form"  width="100%"');
            $frm->setFieldsPerRow(2);
            $fld = $frm->addHtml('', '', '<div class="tblheading">' . t_lang('M_TXT_CHECK_ATLEAST_ONE_ADDRESS') . '</div>');
            $fld->merge_cells = 2;
            $fld->merge_caption = true;
            $frm->captionInSameCell(false);
            $count = 0;
            while ($row = $db->fetch($rs)) {
                $count++;
                $dac_address_capacity = $row['capacity'];
                $company_address_id[] = $row['company_address_id'];
                $str .= $row['address'];
                if ($dac_address_capacity > 0) {
                    $fld1 = $frm->addCheckBox($row['address'], 'dac_address_id[]', ($_POST['company_address_id'] == '' ? $row['company_address_id'] : $_POST['company_address_id']), 'dac_address_id' . $count, 'onclick="return updateMaxCoupons(this.value);"', true);
                } else {
                    $fld1 = $frm->addCheckBox($row['address'], 'dac_address_id[]', $row['company_address_id'], 'dac_address_id' . $count, 'onclick="return updateMaxCoupons(this.value);"', false);
                }
                $fld = $frm->addTextBox($caption, 'dac_address_capacity[]', $dac_address_capacity, 'dac_address_capacity' . $count, 'onKeyUp="return updateMaxCoupons(this.value);" maxLength="15"');
                $dac_address_capacity = '';
            }
        }
        $str = $frm->getFormHtml(false);
        dieJsonSuccess($str);
        break;
    case 'CITYSEARCH':
        $srch = new SearchBase('tbl_cities', 'c');
        $srch->addCondition('city_active', '=', 1);
        $srch->addCondition('city_deleted', '=', 0);
        $srch->addCondition('city_request', '=', 0);
        $srch->addCondition('city_name' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $post['city'] . '%');
        $srch->joinTable('tbl_states', 'INNER JOIN', 'c.city_state = s.state_id', 's');
        $srch->addOrder('s.state_name');
        $srch->addOrder('c.city_name' . $_SESSION['lang_fld_prefix']);
        $srch->addGroupBy('s.state_id');
        $srch->addMultipleFields(array('city_id', 'state_id', 'city_name' . $_SESSION['lang_fld_prefix'], 's.state_name' . $_SESSION['lang_fld_prefix']));
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $count = 0;
        if ($db->total_records($rs) > 0) {
            while ($row = $db->fetch($rs)) {
                $str .= '<div class="grids__item"><div class="grids__list"><div class="grids__head">' . $row['state_name' . $_SESSION['lang_fld_prefix']] . '</div><div class="grids__body"><ul class="list__vertical links">';
                $srch = new SearchBase('tbl_deals', 'd');
                $srch->addCondition('deal_status', '=', 1);
                $srch->addCondition('deal_deleted', '=', 0);
                $srch->addCondition('deal_complete', '=', 1);
                $srch->addMultipleFields(array('deal_city', 'count(deal_id) as total'));
                $srch->addGroupBy('deal_city');
                $srch->doNotLimitRecords();
                $srch->doNotCalculateRecords();
                $qry_num_deals = $srch->getQuery();
                $srch = new SearchBase('tbl_cities', 'c');
                $srch->addCondition('city_active', '=', 1);
                $srch->addCondition('city_deleted', '=', 0);
                $srch->addCondition('city_request', '=', 0);
                $srch->addCondition('city_name' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $post['city'] . '%');
                $srch->addCondition('city_state', '=', $row['state_id']);
                $srch->addOrder('c.city_name' . $_SESSION['lang_fld_prefix']);
                $srch->addMultipleFields(array('city_id', 'city_name' . $_SESSION['lang_fld_prefix'], 'IF(qd.total >0, qd.total, "0" ) as total'));
                $srch->joinTable('(' . $qry_num_deals . ')', 'LEFT JOIN', 'qd.deal_city = c.city_id', 'qd');
                $srch->doNotLimitRecords();
                $srch->doNotCalculateRecords();
                $rs1 = $srch->getResultSet();
                $countCity = 0;
                while ($row1 = $db->fetch($rs1)) {
                    $countCity++;
                    $total = '';
                    if ($row1['total'] > 0) {
                        $total = "(" . $row1['total'] . ")";
                    }
                    $str .= '<li ' . $classCity . '><a href="javascript:void(0);" onclick="selectCity(' . $row1['city_id'] . ',' . CONF_FRIENDLY_URL . ');">' . $row1['city_name' . $_SESSION['lang_fld_prefix']] . " " . $total . '</a></li>';
                }
                $str .= '</ul></div></div></div>';
            }
        } else {
            $str .= '<div class="col-md-12 "><div class="alert alert_info">' . t_lang('M_TXT_SEARCH_CITY_NOT_FOUND') . '</div>';
        }
        dieJsonSuccess($str);
        break;
    case 'SELECTCITY':
        if (!is_numeric($post['id']))
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        if (!selectCity(intval($post['id'])))
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        //city-deals.php
        $arr = array('status' => 1, 'msg' => 'City Selected.', 'link' => friendlyUrl(CONF_WEBROOT_URL . 'city-deals.php'));
        die(convertToJson($arr));
        break;
    case 'SELECTSESSIONCITY':
        if (!is_numeric($post['id']))
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        if (!selectCity(intval($post['id'])))
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        setcookie('city_subscriber', true, time() + 30 * 24 * 3600, CONF_WEBROOT_URL);
        $arr = array('status' => 1, 'msg' => 'City Selected.', 'link' => friendlyUrl(CONF_WEBROOT_URL . 'home.php'));
        die(convertToJson($arr));
        break;
    case 'SELECTDEALTOCART':
        global $db;
        if (intval($post['id']) <= 0)
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        $city_to_show = '';
        if ($_SESSION['lang_fld_prefix'] == '_lang1')
            $city_to_show = ',city_name_lang1';
        $query = "select deal_id,deal_type,deal_city,city_name" . $city_to_show . " from tbl_deals d,tbl_cities c where c.city_id=d.deal_city AND deal_id=" . intval($post['id']);
        $rs = $db->query($query);
        if (!$row = $db->fetch($rs))
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        if ($post['forFriend'] == 1 && $row['deal_type'] == 1)
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        /* Validation to check that required Options/Attributes of deal/product are selected or not, script starts here */
        if (isset($post['option'])) {
            $option = $post['option'];
        } else {
            $option = [];
        }
        $deal_options = getDealOptions(intval($post['id']));
        if (is_array($deal_options) && count($deal_options)) {
            foreach ($deal_options as $deal_option) {
                if ($deal_option['required'] && empty($option[$deal_option['deal_option_id']])) {
                    $arr['error']['option'][$deal_option['deal_option_id']] = ($deal_option['option_name'] . ' is required!');
                }
            }
        }
        if ($arr['error']) {
            $arr['status'] = 0;
            die(convertToJson($arr));
        }
        /* Validation to check that required Options/Attributes of deal/product are selected or not, script ends here */
        $cart = new Cart();
        $message = 'Deal added.';
        if ($post['forFriend'] == 1) {
            $_SESSION['type'] = 'gift';
            $forFriend = true;
        } else {
            $forFriend = false;
        }
        $company_loaction_id = isset($post['company_loaction_id']) ? $post['company_loaction_id'] : 0;
        $startDate = isset($post['startDate']) ? $post['startDate'] : '';
        $endDate = isset($post['endDate']) ? $post['endDate'] : '';
        if (!$cart->add(intval($row['deal_id']), $quantity = 1, $option, $forFriend, $post['sub_deal_id'], $company_loaction_id, $startDate, $endDate)) {
            global $msg;
            $msg->addError($cart->getError());
        }
        $_SESSION['city'] = $row['deal_city'];
        $_SESSION['cityname'] = $row['city_name'];
        $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
        $arr = array('status' => 1, 'msg' => $message, 'url' => friendlyUrl(CONF_WEBROOT_URL . 'buy-deal.php'));
        die(convertToJson($arr));
        break;
    case 'SELECTDEALFORADDTOCART':
        global $db;
        global $msg;
        if (intval($post['id']) <= 0)
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        $city_to_show = '';
        if ($_SESSION['lang_fld_prefix'] == '_lang1')
            $city_to_show = ',city_name_lang1';
        $query = "select deal_id,deal_type,deal_city,city_name" . $city_to_show . " from tbl_deals d,tbl_cities c where c.city_id=d.deal_city AND deal_id=" . intval($post['id']);
        $rs = $db->query($query);
        if (!$row = $db->fetch($rs))
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        if ($post['forFriend'] == 1 && $row['deal_type'] == 1)
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        /* Validation to check that required Options/Attributes of deal/product are selected or not, script starts here */
        if (isset($post['option'])) {
            $option = $post['option'];
        } else {
            $option = [];
        }
        $deal_options = getDealOptions(intval($post['id']));
        if (is_array($deal_options) && count($deal_options)) {
            foreach ($deal_options as $deal_option) {
                if ($deal_option['required'] && empty($option[$deal_option['deal_option_id']])) {
                    $arr['error']['option'][$deal_option['deal_option_id']] = ($deal_option['option_name'] . ' is required!');
                }
            }
        }
        if (isset($arr['error'])) {
            $arr['status'] = 0;
            die(convertToJson($arr));
        }
        /* Validation to check that required Options/Attributes of deal/product are selected or not, script ends here */
        $cart = new Cart();
        if ($post['forFriend'] == 1) {
            $_SESSION['type'] = 'gift';
            $for_friend = true;
        } else {
            $for_friend = false;
        }
        if (!$cart->add(intval($row['deal_id']), 1, $option, $for_friend)) {
            $arr = array('status' => 0, 'msg' => $cart->getError(), 'url' => 0);
            die(convertToJson($arr));
        }
        $_SESSION['city'] = $row['deal_city'];
        $_SESSION['cityname'] = $row['city_name'];
        $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
        if (!isUserLogged()) {
            $arr = array('status' => 0, 'msg' => 'User Not LOGGEDIN.', 'url' => friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
            die(convertToJson($arr));
        }
        $arr = array('status' => 1, 'msg' => 'Deal added.', 'url' => friendlyUrl(CONF_WEBROOT_URL . 'buy-deal.php'));
        die(convertToJson($arr));
        //   print_r($arr);
        break;
    case 'UPDATEDROPDOWN':
        require_once './includes/page-functions/cart-functions.php';
        if (intval($post['company_address_id']) <= 0) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $subdeals = "";
        $condition = array('cart_item_id' => $post['dealkey']);
        $data = getRecords('tbl_cart_items', $condition, 'first');
        $post['company_address_id'] = intval($post['company_address_id']);
        $price = "";
        $error = "";
        $eligible_deal_data = canBuyDeal(1, true, $price, $data['cart_item_deal_id'], $post['company_address_id'], 0, $data['cart_item_sub_deal_id'], $error);
        if ($eligible_deal_data === false || count($eligible_deal_data['address_id']) <= 0) {
            dieJsonError($error);
        }
        $maxBuy = $eligible_deal_data['max_buy'][intval($post['company_address_id'])];
        if ($data['cart_item_sub_deal_id'] > 0) {
            $subdeal_id = $data['cart_item_sub_deal_id'];
            $sub_voucherleft = getSubdealVoucher($subdeal_id, $post['company_address_id']);
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
        $data['cart_item_company_address_id'] = $post['company_address_id'];
        $error = "";
        if (addUpdateCartItem($data, $error)) {
            dieJsonSuccess($dropdown);
        } else {
            dieJsonError($error);
        }
        break;
    case UPDATECART:
        $cart = new cart();
        $cart_vals = setCartValuesForResponse($cart);
        die(convertToJson($cart_vals));
        break;
    case 'VERIFYUSEREMAIL':
        $values = array(
            'user_name' => $post['user_name'],
            'user_email' => $post['email'],
            'member_id' => $post['member_id'],
            'code' => $post['code'],
            'city' => $post['city']
        );
        $error = '';
        if (sendUserEmailVerificationEmail($values, $error)) {
            $arr = array('status' => 1, 'msg' => t_lang('M_TXT_MAIL_SENT'));
        } else {
            $arr = array('status' => 0, 'msg' => $error);
        }
        echo convertToJson($arr);
        break;
    case 'UPDATELANGUAGE':
        if ($post['val'] == 2) {
            $_SESSION['lang_fld_prefix'] = '_lang1';
            $_SESSION['language'] = 2;
            echo t_lang('M_TXT_LANGUAGE_UPDATED');
        } elseif ($post['val'] == 1) {
            $_SESSION['lang_fld_prefix'] = '';
            $_SESSION['language'] = 1;
            echo t_lang('M_TXT_LANGUAGE_UPDATED');
        } else {
            echo t_lang('M_TXT_INVALID_INPUT');
        }
        break;
    case 'LIKEMERCHANT':
        $user_id = $_SESSION['logged_user']['user_id'];
        $company_id = intval($post['id']);
        $page = $post['pagename'];
        if ($_SESSION['logged_user']['user_id'] > 0) {
            if (intval($post['id']) > 0 && $post['txt'] == 'like') {
                removeFavouriteMerchant($user_id, $company_id);
                addFavouriteMerchant($user_id, $company_id);
                $merchantText = '<span id="likeMerchant_' . $company_id . '" class="heart active"> <a href="javascript:void(0);" onclick="likeMerchant(' . $company_id . ', \'unlike\',\'company-detail\')" class="heart__link " title="' . t_lang('M_TXT_REMOVE_FROM_FAVOURITES') . '">  </a><span class="heart__txt">' . t_lang('M_TXT_REMOVE_FROM_FAVOURITES') . '</span></span>';
                $msg = t_lang('M_TXT_REMOVE_FROM_FAVOURITES');
            }
            if (intval($post['id']) > 0 && $post['txt'] == 'unlike') {
                removeFavouriteMerchant($user_id, $company_id);
                $msg = t_lang('M_TXT_ADDED_TO_FAVOURITES');
                $merchantText = '<span id="likeMerchant_' . $company_id . '" class="heart"> <a href="javascript:void(0);" onclick="likeMerchant(' . $company_id . ', \'like\',\'company-detail\')" class="heart__link" title="' . t_lang('M_TXT_ADD_TO_FAVOURITES') . '"> </a><span class="heart__txt">' . t_lang('M_TXT_ADD_TO_FAVOURITES') . '</span></span>';
            }
        } else {
            $msg = 'login';
            $merchantUrl = CONF_WEBROOT_URL . 'merchant-favorite.php?company=' . $company_id . '&page=1';
            $_SESSION['login_page'] = friendlyUrl($merchantUrl);
            $merchantText = '<span id="likeMerchant_' . $company_id . '" class="heart"> <a href="javascript:void(0);" onclick="likeMerchant(' . $company_id . ', , \'like\',\'company-detail\')" class="heart__link" title="' . t_lang('M_TXT_ADD_TO_FAVOURITES') . '"> </a><span class="heart__txt">' . t_lang('M_TXT_ADD_TO_FAVOURITES') . '</span></span>';
        }
        $arr = array('status' => 1, 'msg' => $msg, 'merchantText' => $merchantText);
        die(convertToJson($arr));
        break;
    case 'LIKEDEAL':
        $user_id = $_SESSION['logged_user']['user_id'];
        $deal_id = intval($post['deal_id']);
        if (isset($_SESSION['logged_user']['affiliate_id']) && $_SESSION['logged_user']['affiliate_id'] > 0) {
            $msg = t_lang('M_ERROR_AFFILIATE_HAVE_NO_PERMISSSION');
            $arr = array('status' => 0, 'msg' => $msg, 'merchantText' => '');
            die(convertToJson($arr));
        }
        if ($_SESSION['logged_user']['user_id'] > 0) {
            if (intval($post['deal_id']) > 0 && $post['txt'] == 'like') {
                addFavouriteDeal($user_id, $deal_id);
                $merchantText = '<span class="heart active">
                            <a title="' . t_lang("M_TXT_REMOVE_FROM_FAVOURITES") . '" class="heart__link" onclick="likeDeal(' . $deal_id . ' , \'unlike\')"  href="javascript:void(0);"></a>
                            <span class="heart__txt">"' . t_lang("M_TXT_REMOVE_FROM_FAVOURITES") . '"</span>
                        </span>';
                $msg = t_lang('M_TXT_ADDED_TO_FAVOURITES');
            }
            if (intval($post['deal_id']) > 0 && $post['txt'] == 'unlike') {
                removeFavouriteDeal($user_id, $deal_id);
                $msg = t_lang('M_TXT_ADDED_TO_FAVOURITES');
                $merchantText = '<span class="heart ">
                            <a  class="heart__link" onclick="likeDeal(' . $deal_id . ' , \'like\')"  href="javascript:void(0);"></a>
                            <span class="heart__txt">"' . t_lang("M_TXT_ADD_TO_FAVOURITES") . '"</span>
                        </span>';
            }
        } else {
            $msg = 'login';
            $loginUrl = CONF_WEBROOT_URL . 'login.php';
            $_SESSION['login_page'] = friendlyUrl($loginUrl);
            $merchantText = '<span class="heart ">
                            <a  class="heart__link" onclick="likeDeal(' . $deal_id . ' , \'like\')"  href="javascript:void(0);"></a>
                            <span class="heart__txt">"' . t_lang("M_TXT_ADD_TO_FAVOURITES") . '"</span>
                        </span>';
        }
        $arr = array('status' => 1, 'msg' => $msg, 'merchantText' => $merchantText);
        die(convertToJson($arr));
        break;
    case 'FETCHSUBCATEGORY':
        $deal_id = intval($post['id']);
        $str = '';
        $categoryArray = $db->query("select cat_id,cat_name" . $_SESSION['lang_fld_prefix'] . " from tbl_deal_categories 
		where cat_parent_id = {$deal_id} order by cat_display_order");
        $rows = $db->fetch_all_assoc($categoryArray);
        if (!empty($rows)) {
            $str .= "<ul>";
            foreach ($rows as $key1 => $val1) {
                $str .= '<li class="parent_id_' . $key1 . '" ><a onmouseover="fetchsubCategory(' . $key1 . ',this)" href="?cat=' . $key1 . '">' . $val1 . '</a>';
                $str .= '</li>';
            }
            $str .= "</ul>";
        }
        $arr = array('status' => 1, 'msg' => $msg, 'str' => stripslashes($str));
        die(convertToJson($arr));
        break;
    case 'CITYSTATESEARCH':
        $srch = new SearchBase('tbl_cities', 'c');
        $srch->addCondition('city_active', '=', 1);
        $srch->addCondition('city_deleted', '=', 0);
        $srch->addCondition('city_request', '=', 0);
        $srch->addCondition('city_name' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $post['city'] . '%');
        $srch->joinTable('tbl_states', 'INNER JOIN', 'c.city_state = s.state_id', 's');
        $srch->addOrder('s.state_name');
        $srch->addOrder('c.city_name');
        $srch->addGroupBy('s.state_id');
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $count = 0;
        while ($row = $db->fetch($rs)) {
            $count++;
            $str .= '<div class="col3_list"><h2>' . $row['state_name' . $_SESSION['lang_fld_prefix']] . '</h2><ul class="col3_checklist">';
            $srch = new SearchBase('tbl_cities', 'c');
            $srch->addCondition('city_active', '=', 1);
            $srch->addCondition('city_deleted', '=', 0);
            $srch->addCondition('city_request', '=', 0);
            $srch->addCondition('city_state', '=', $row['state_id']);
            $srch->addOrder('c.city_name');
            $srch->doNotLimitRecords();
            $srch->doNotCalculateRecords();
            $rs1 = $srch->getResultSet();
            $tcity = $db->total_records($rs1);
            $ccity = 0;
            $flag = 0;
            while ($row1 = $db->fetch($rs1)) {
                $ccity++;
                $srch = new SearchBase('tbl_newsletter_subscription', 'ns');
                $cnd = $srch->addDirectCondition('0');
                $cnd->attachCondition('ns.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
                $cnd->attachCondition('ns.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
                $srch->addCondition('subs_city', '=', $row1['city_id']);
                $rs2 = $srch->getResultSet();
                if ($db->total_records($rs2) == 0) {
                    $str .= ($flag % 3 == 0) ? '' : '';
                    $str .= '<li><label><input id="city_' . $row1['city_id'] . '" name="city_id[]" value="' . $row1['city_id'] . '" type="checkbox"  /> ' . $row1['city_name' . $_SESSION['lang_fld_prefix']] . '</label></li>';
                    $flag++;
                } else {
                    $str .= ($flag == 0 && $ccity == $tcity) ? ' <li>' . t_lang('M_TXT_ALL_CITIES_SELECTED') . '</li>' : '';
                }
            }
            $str .= '</ul></div>';
        }
        dieJsonSuccess($str);
        break;
    case 'GETFRIENDLYURL':
        $str = friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $post['cat_id'] . '&type=side');
        dieJsonSuccess($str);
        break;
    case 'SEARCHLIST':
        $name = $post['deal'];
        $cat = $post['cat'];
        $page = $post['page'];
        $type = $post['type'];
        $cityId = $_SESSION['city'];
        $srch = dealsearchListHtml($name, $cat, $page, true, $type, $cityId);
        $rs_deal_list = $srch->getResultSet();
        $countRecords = $srch->recordCount();
        $pages = $srch->pages();
        if ($pages > 1) {
            $pagestring .= '<div class="footinfo"> ';
            $pagestring .= '<ul class="pagination">';
            $pagestring .= getPageString('<li><a href="javascript:void(0)" onclick=showDiv(xxpagexx,"' . $name . '","' . $cat . '","' . $type . '");>xxpagexx</a> </li> ', $srch->pages(), $page, '<li class="selected"><a  href="javascript:void(0);">xxpagexx</a></li>', '<li class="more disabled "><a href="javascript:void(0);"></a></li>');
            $pagestring .= '</ul></div>';
        }
        $str .= '<div class="listing__items">';
        $str .= '<div class="row">';
        if ($countRecords > 0) {
            while ($row = $db->fetch($rs_deal_list)) {
                $deal_id_arr[] = $row['deal_id'];
                $deal = $row['deal_id'];
                $objDeal = new DealInfo($deal);
                if ($objDeal->getError() != '') {
                    continue;
                }
                $deal = $objDeal->getFields();
                $str .= '<div class="col-lg-3 col-xs-4 col-xs-6">';
                $str .= renderDealView('deal.php', $deal);
                $str .= '</div>';
            }
        } else {
            $str .= '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show'])) . '</div>';
        }
        $str .= '</div>';
        $str .= '</div>';
        $str .= $pagestring;
        $data = array('html' => $str, 'dealIds' => $deal_id_arr);
        dieJsonSuccess($data);
        break;
    case 'SEARCHLISTFOROTHER':
        $name = $post['deal'];
        $cat = $post['cat'];
        $page = $post['page'];
        $type = $post['type'];
        $cityId = $_SESSION['city'];
        $srch = dealsearchListHtml($name, $cat, $page, false, $type, $cityId);
        $rs_deal_list = $srch->getResultSet();
        $countRecords = $srch->recordCount();
        $pages = $srch->pages();
        if ($pages > 1) {
            $pagestring .= '<div class="footinfo"> ';
            $pagestring .= '<ul class="pagination">';
            $pagestring .= getPageString('<li><a href="javascript:void(0)" onclick=showForOther(xxpagexx,"' . $name . '","' . $cat . '","' . $type . '");>xxpagexx</a> </li> ', $srch->pages(), $page, '<li class="selected"><a  href="javascript:void(0);">xxpagexx</a></li>');
            $pagestring .= '</ul>';
            $pagestring .= '</div>';
        }
        $str .= '<div class="listing__items">';
        $str .= '<div class="row">';
        if ($countRecords > 0) {
            while ($row = $db->fetch($rs_deal_list)) {
                $deal_id_arr[] = $row['deal_id'];
                $deal = $row['deal_id'];
                $objDeal = new DealInfo($deal);
                if ($objDeal->getError() != '') {
                    continue;
                }
                $deal = $objDeal->getFields();
                $str .= '<div class="col-lg-3 col-xs-4 col-xs-6">';
                $str .= renderDealView('deal.php', $deal);
                $str .= '</div>';
            }
        } else {
            $str .= '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', 'Other City')) . '</div>';
        }
        $str .= '</div>';
        $str .= '</div>';
        $str .= $pagestring;
        $data = array('html' => $str, 'dealIds' => $deal_id_arr);
        dieJsonSuccess($data);
        break;
    case 'REMOVEITEM':
        if (!isUserLogged()) {
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        if ($post['item'] == '' || !isset($post['el'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $item = $post['item'];
        if ($item < 1) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $cart = new Cart();
        $cart->removeItem($post['item']);
        dieJsonSuccess('');
        break;
    case 'UPDATECARTQTY':
        if (!isUserLogged()) {
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        if (intval($post['qty']) < 0 || !isset($post['el'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST_QUANTITY_NUMERIC_ONLY'));
        }
        if ((int) $post['qty'] != $post['qty']) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST_QUANTITY_NUMERIC_NONDEC'));
        }
        if (!isset($post['key'])) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $key = str_replace('quantity_', '', $post['key']);
        $cart_item_id = filter_var($key, FILTER_SANITIZE_NUMBER_INT);
        if ($cart_item_id < 1) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $cart = new Cart();
        if (!$cart->updateQuantity($key, intval($post['qty']), $error)) {
            dieJsonError($error);
        }
        $cart_vals = setCartValuesForResponse($cart);
        die(convertToJson($cart_vals));
        break;
    case 'LOADGIFTFORM':
        if (!isUserLogged()) {
            die(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        $key = $post['d'];
        if ($key < 0 || $key == '') {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $frm = getGiveGiftForm();
        $cart = new Cart();
        if ($gift_data = $cart->getCartItem($key)) {
            $frm->fill($gift_data);
        }
        $frm_html = '<div class="cartarea"><h2 class="marg_bt">' . t_lang('M_TXT_GIFT_DETAILS') . '</h2>' . $frm->getFormHtml() . '</div>';
        die($frm_html);
        break;
    case 'SAVEGIFTDETAILS':
        if (!isUserLogged()) {
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        $deal_id = intval($post['deal_id']);
        $key = $post['key'];
        if ($deal_id < 0) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        if ($key == '') {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $frm = getGiveGiftForm();
        if (!$frm->validate($post)) {
            $msg->addError($frm->getValidationErrors());
            dieJsonError($msg->display());
        }
        $cart = new Cart();
        if (!$cart->updateDealGiftDetails($post)) {
            dieJsonError('Gift details not saved!!');
        }
        $resp = array('status' => 1, 'msg' => t_lang('M_TXT_GIFT_DETAILS_SAVED'));
        ob_start();
        include CONF_VIEW_PATH . 'gift-svg.php';
        $svg = ob_get_clean();
        $resp['html'] = '<a title="Cancel Gift" class="gift themebtn link__gift" href="javascript:void(0)" onclick="return loadGiftPopUp(\'' . $key . '\');">' . t_lang('M_TXT_GIFT_FOR') . $svg . ': ' . htmlentities($post['to_name']) . '</a><a class="hidelink" href="javascritp:void(0)" onclick="return cancelGift(\'' . $key . '\');"></a>';
        die(convertToJson($resp));
        break;
    case 'CLEARGIFTDETAILS':
        if (!isUserLogged()) {
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        $key = $post['d'];
        if ($key <= 0) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $cart = new Cart();
        if (!$cart->clearGiftDetails($key)) {
            dieJsonError('Gift has not cancelled!!');
        }
        $resp = array('status' => 1, 'msg' => 'Gift cancelled successfully.');
        $resp['html'] = '<a class="gift" href="javascript:void(0)" onclick="return loadGiftPopUp(\'' . $key . '\');">Give as a Gift?</a>';
        die(convertToJson($resp));
        break;
    case 'UPDATESHIPPINGDETAILS':
        if (!isUserLogged()) {
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        global $db;
        $success = updateshippingAdress($post);
        if ($success) {
            $cart = new Cart();
            if (!$cart->updateShippingCharges(intval($post['ship_country']), $error)) {
                dieJsonError($error);
            }
            $cart_vals = setCartValuesForResponse($cart);
            $cart_vals['msg'] = 'Address Updated.';
            die(convertToJson($cart_vals));
        } else {
            dieJsonError($db->getError());
        }
        dieJsonError($msg->display());
        break;
    case 'UPDATESHIPPINGCHARGES':
        if (!isUserLogged()) {
            dieJsonError(t_lang('M_TXT_SESSION_EXPIRES'));
        }
        if (intval($post['cid']) < 0 || intval($post['cid']) != $post['cid']) {
            dieJsonError(t_lang('M_ERROR_INVALID_REQUEST'));
        }
        $cart = new Cart();
        $error = "not updated";
        if (!$cart->updateShippingCharges(intval($post['cid']), $error)) {
            dieJsonError($error);
        }
        $cart_vals = setCartValuesForResponse($cart);
        die(convertToJson($cart_vals));
        break;
    case 'SHOWREVIEWS':

        $page = $post['page'];
        $comapnyId = $post['comapnyId'];
        $pagination = isset($post['pagination']) ? $post['pagination'] : true;
        $str = showReviews($page, $comapnyId, $pagination);
        dieJsonSuccess($str);
        break;
    case 'GETDEALSPAGEHTML':
        $page = $post['page'];
        $pagename = $post['pagename'];
        $showMore = $post['seemore'];
        $city_id = 0;
        $start_date = 0;
        if (isset($post['city_id'])) {
            $city_id = $post['city_id'];
        }
        $start_date = $post['start_date'];
        $end_date = $post['end_date'];
        $pagesize = 12;
        if ($pagename == "citydeal") {
            $city_id = $_SESSION['city'];
        }
        $srch = alldealPageHtml($page, $pagename, $showMore, $city_id, $start_date, $end_date, $pagesize);
        $rs_deal_list = $srch->getResultSet();
        $countRecords = $srch->recordCount();
        if ($countRecords == 0) {
            echo sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $_SESSION['city_to_show']);
            die();
        }
        $str = "";
        while ($row = $db->fetch($rs_deal_list)) {
            $deal_id_arr[] = $row['deal_id'];
            $deal = $row['deal_id'];
            $objDeal = new DealInfo($deal);
            if ($objDeal->getError() != '') {
                continue;
            }
            $deal = $objDeal->getFields();
            $str .= '<div class="col-sm-4 col-xs-6">';
            $str .= renderDealView('deal.php', $deal);
            $str .= '</div>';
        }
        dieJsonSuccess($str);
        break;
    case 'GETINSTANTDEALSPAGEHTML':
        $page = $post['page'];
        $cat = $post['cat'];
        $pagesize = $post['pagesize'];
        $dealArrCat = $post['dealArrCat'];
        $showMore = $post['seemore'];
        $str = fetchInstantDeal($page, $pagesize, $cat, $dealArrCat, $showMore);
        dieJsonSuccess($str);
        break;
    case 'LOADMORESUBDEALS':
        $subdeal = new SearchBase('tbl_sub_deals');
        $deal_id = $post['deal_id'];
        $subdeal->addCondition('sdeal_deal_id', '=', $deal_id);
        $subdeal->addCondition('sdeal_active', '=', 1);
        $result = $subdeal->getResultSet();
        $subdealcount = $db->total_records();
        $rs = $db->fetch_all($result);
        $seemoresubdeal_html .= '<div class="popup-more-deals">';
        $seemoresubdeal_html .= '<h2 class="dealtitle">Choose your deal:</h2>';
        $seemoresubdeal_html .= '<ul>';
        foreach ($rs as $key => $data) {
            $seemoresubdeal_html .= '<li>';
            $seemoresubdeal_html .= '<label>';
            $seemoresubdeal_html .= '<div class="deals-cnt">';
            $seemoresubdeal_html .= '<h3>' . $data['sdeal_name'] . '</h3>';
            $sdeal_price = (($data['sdeal_discount_is_percentage'] == 1) ? $data['sdeal_original_price'] - ($data['sdeal_discount_is_percentage'] * $data['sdeal_discount'] / 100) : $data['sdeal_original_price'] - $data['sdeal_discount']);
            $seemoresubdeal_html .= '<div class="deal-cnt">';
            $seemoresubdeal_html .= '<p class="breakout-pricing"> <span class="breakout-option-value">' . $data['sdeal_original_price'] . '</span> <span class="breakout-option-price">' . $sdeal_price . '</span> </p>';
            $seemoresubdeal_html .= '</div>';
            $seemoresubdeal_html .= ' <a class="btn-buy" href="#">Buy</a>';
            $seemoresubdeal_html .= '</div>';
            $seemoresubdeal_html .= '</label>';
            $seemoresubdeal_html .= '</li>';
        }
        $seemoresubdeal_html .= '</ul>';
        $seemoresubdeal_html .= '</div>';
        die($seemoresubdeal_html);
        break;
    case 'GETPRODUCTATTRIBUTEVALUE':
        $deal_id = $post['deal_id'];
        $deal_option_id = $post['deal_option_id'];
        $deal_option_value_id = $post['deal_option_value_id'];
        if ($deal_id == "" || $deal_option_id == "" || $deal_option_value_id == "") {
            return false;
        }
        $srch = new SearchBase('tbl_deal_option_value', 'dov');
        $srch->addCondition('dov.deal_option_value_id', '=', $deal_option_value_id, 'AND');
        $srch->addCondition('dov.deal_option_id', '=', $deal_option_id, 'AND');
        $srch->addCondition('dov.deal_id', '=', $deal_id);
        $srch->joinTable('tbl_deal_option_value', 'INNER JOIN', "dov.option_value_id = dov1.parent_option_value_id AND dov.deal_id= $deal_id AND dov1.deal_id= $deal_id", 'dov1');
        $srch->joinTable('tbl_option_values', 'INNER JOIN', 'dov1.option_value_id=ov.option_value_id', 'ov');
        $srch->joinTable('tbl_options', 'INNER JOIN', 'op.option_id=ov.option_id AND op.is_deleted=0', 'op');
        $srch->addMultipleFields(array('dov1.deal_option_value_id,ov.name,dov1.price,dov1.price_prefix,dov1.option_value_id'));
        $rs = $srch->getResultSet();
        $option_val_info = $db->fetch_all($rs);
        $option .= '<option value="">--Please Select--</option>';
        foreach ($option_val_info as $option_value) {
            $option .= '<option value="' . $option_value['deal_option_value_id'] . '">' . $option_value["name"];
            if ($option_value["price"]) {
                $option .= '(' . $option_value["price_prefix"] . CONF_CURRENCY . $option_value["price"] . CONF_CURRENCY_RIGHT . ')';
            }
        }
        dieJsonSuccess($option);
        break;
    case'GETOPTIONVALUESFORMERCHANT' :
        $option_id = intval($post['id']);
        $d_op_src = new SearchBase('tbl_option_values', 'd_op');
        $d_op_src->addCondition('d_op.option_id', '=', $option_id);
        $deal_op = $d_op_src->getResultSet();
        $deal_op = $db->fetch_all($deal_op);
        foreach ($deal_op as $option_value) {
            $options .= '<option value="' . $option_value['option_value_id'] . '">' . $option_value['name'] . '</option>';
        }
        dieJsonSuccess($options);
        break;
    case 'FETCHQUICKVIEWHTML':
        $deal_id = intval($post['dealId']);
        $type = $post['type'];
        $limit = $post['limit'];
        $str = fetchQuickViewHtml($deal_id, $type, $limit);
        die($str);
        break;
    case 'PRODUCTSEARCH':
        $arr['category'] = $post['category'];
        if (empty($post['category'])) {
            $arr['category'] = $post['parent_cat_id'];
        }
        $arr['price'] = $post['price'];
        $arr['color'] = $post['color'];
        $arr['size'] = $post['size'];
        $page = $post['page'];
        $pagesize = $post['pagesize'];
        $str = productSearch($arr, $page, $pagesize);
        dieJsonSuccess($str);
        break;
    case 'PAGESEARCH' :
        if (!empty($post['category'])) {
            $arr['category'] = $post['category'];
        }
        if (!empty($post['company'])) {
            $arr['company'] = $post['company'];
        }
        if (!empty($post['city_search'])) {
            $arr['city_search'] = $post['city_search'];
        }
        if (!empty($post['price'])) {
            $temp = $post['price'];
            if ($temp[0] != '' && $temp[1] != '') {
                $arr['price'] = $temp;
            }
        }
        if (!empty($post['color'])) {
            $arr['color'] = $post['color'];
        }
        if (!empty($post['size'])) {
            $arr['size'] = $post['size'];
        }
        $arr['pagename'] = $post['pagename'];
        if (!empty($post['order'])) {
            $order = explode('||', $post['order']);
            $arr['order_type'] = ($order[0] == 'price') ? 'sellPrice' : $order[0];
            $arr['order'] = $order[1];
        }
        if (isset($post['city_id'])) {
            $arr['cityId'] = $post['city_id'];
        }
        $arr['start_date'] = $post['start_date'];
        $arr['end_date'] = $post['end_date'];
        $page = isset($post['page']) ? intval($post['page']) : 1;
        $pagesize = isset($post['pagesize']) ? intval($post['pagesize']) : 12;
        $srch = pageSearch($arr, $page, $pagesize);
        $rs_deal_list = $srch->getResultSet();
        $pagestring = '';
        $pages = $srch->pages();
        $pageno = $page + 1;
        if ($pages > 1) {
            $rescount = ((($pageno - 1) * $pagesize < $srch->recordCount()) ? $srch->recordCount() - (($pageno - 1) * $pagesize) : 0);
            if ($rescount > 0) {
                $pagestring .= '<div class="aligncenter loadmore">';
                $pagestring .= '<div class="paginglink">';
                $click = 'getalldeals("' . $pageno . '", "' . $pagesize . '")';
                $pagestring .= "<a href='javascript:void(0);' class='themebtn themebtn--large themebtn--grey pagination' onclick='" . $click . "'>" . t_lang('M_TXT_LOAD_MORE') . " </a>";
                $pagestring .= '</div></div>';
            }
        }
        $countRecords = $srch->recordCount();
        $str .= '<div class="listing__items">';
        $str .= '<div class="row">';
        $deal_id_arr = [];
        if ($countRecords > 0) {
            while ($row = $db->fetch($rs_deal_list)) {
                $deal_id_arr[] = $row['deal_id'];
                $deal = $row['deal_id'];
                $objDeal = new DealInfo($deal);
                if ($objDeal->getError() != '') {
                    continue;
                }
                $deal = $objDeal->getFields();
                if ($post['pagename'] == "getaways" || $post['pagename'] == "more-cities") {
                    $class = "col-lg-3 col-xs-4 col-xs-6";
                } else if ($post['pagename'] == "home") {
                    $class = "col-md-3 col-sm-6 col-xs-6";
                } else {
                    $class = "col-sm-4 col-xs-6";
                }
                $str .= '<div class="' . $class . '">';
                $str .= renderDealView('deal.php', $deal);
                $str .= '</div>';
            }
        } else {
            if (empty($arr['city_search'])) {
                $cityName = $_SESSION['city_to_show'];
            } else {
                $cityName = $arr['city_search'];
            }
            $str .= '<div class="col-md-12 "><div class="alert alert_info">' . unescape_attr(sprintf(t_lang(M_TXT_SORRY_NO_DEAL), '', $cityName)) . '</div></div>';
        }
        $str .= '</div>';
        $str .= '</div>';
        $str .= $pagestring;
        $data = array('html' => $str, 'dealIds' => $deal_id_arr);
        dieJsonSuccess($data);
        break;
    case 'PRODUCTSEARCHWITHPAGINATION':
        $data = $post['data'];
        $page = $post['page'];
        $pagesize = $post['pagesize'];
        $arr = (array) json_decode($data);
        $str = productSearch($arr, $page, $pagesize);
        dieJsonSuccess($str);
        break;
    case 'GETSTATES':
        global $db;
        if (!is_numeric($post['country']))
            die(t_lang('M_TXT_SELECT') . ' ' . t_lang('M_FRM_COUNTRY'));
        $selected = $post['selected'];
        $srch = new SearchBase('tbl_states');
        $srch->addCondition('state_status', '=', 'A');
        $srch->addCondition('state_country', '=', $post['country']);
        $srch->addOrder('state_name', 'asc');
        $srch->addMultipleFields(array('state_id', 'state_name' . $_SESSION['lang_fld_prefix']));
        $rs = $srch->getResultSet();
        $arr_states = $db->fetch_all_assoc($rs);
        if (count($arr_states) > 0) {
            foreach ($arr_states as $key => $val) {
                $selectedval = "";
                if ($key == $selected) {
                    $selectedval = "selected";
                }
                $str .= '<option value="' . $key . '" ' . $selectedval . '>' . $val . '</option>';
            }
        } else {
            $str = '<option value="">' . t_lang('M_TXT_NO_STATE_EXISTS') . '</option>';
        }
        dieJsonSuccess($str);
        break;
    case 'FETCHCALENDERLIST':
        global $db;
        if (!is_numeric($post['deal_id']))
            die(t_lang('M_TXT_INVALID_INPUT'));
        $month = 0;
        $year = 0;
        if (isset($post['month'])) {
            $month = $post['month'];
        }
        if (isset($post['year'])) {
            $year = $post['year'];
        }
        $html = getCalenderHtml($post['deal_id'], $post['subdeal_id'], $post['location'], $month, $year);
        dieJsonSuccess($html);
        break;
    case 'FETCHCALENDERHTML' :
        $month = 0;
        $year = 0;
        if (isset($post['month'])) {
            $month = $post['month'];
        }
        if (isset($post['year'])) {
            $year = $post['year'];
        }
        $html = getCalenderHtml(0, 0, 0, $month, $year);
        dieJsonSuccess($html);
        break;
    case 'CALCULATESUBDEALPRICE' :
        $data = getSubTotalOfOnlineDeal($post['deal_id'], $post['subdeal_id'], $post['location'], $post['startDate'], $post['endDate'], true);
        dieJsonSuccess($data);
        break;
    case 'FETCHSUBDEALLOCATIONLIST':
        $company_List = fetchcompanyAddressListForPopup($post['deal_id'], $post['subdeal_id']);
        $first_key = key($company_List); // First Element's Key
        $first_value = reset($company_List); // First Element's Va
        $str = '<a class="seleclink active" href="javascript:void(0)" >' . $first_value . '</a>
			<input type="hidden" name="company_location_id" id="company_location_id" value="' . $first_key . '">
			<div style="display:none" class="section_droparea">
			<ul class="verticaldots_list">';
        foreach ($company_List as $key => $compLoc) {
            $str .= '<li><a href="javascript:void(0)" onclick="fetchCalenderlist(' . $post['deal_id'] . ',' . $key . ',this);" >' . $compLoc . '</a></li>';
        }
        $str .= '</ul>
			</div>';
        dieJsonSuccess($str);
        break;
    case 'FETCHSUBDEALPOPUP':
        $deal_id = $post['deal_id'];
        $objDeal = new DealInfo($deal_id);
        $str = getSubdealDataHtml($objDeal);
        die($str);
        break;
}
