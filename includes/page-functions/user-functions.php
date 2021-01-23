<?php

function getUserCardDetail($user_id)
{
    $srch = new SearchBase('tbl_users_card_detail', 'ucd');
    $srch->addCondition('ucd.ucd_user_id', '=', $user_id);
    $rs = $srch->getResultSet();
    return $rs;
}

function fetchEmailNotifications($user_id)
{
    global $db;
    $srch = new SearchBase('tbl_email_notification', 'en');
    $srch->addCondition('en.en_user_id', '=', $user_id);
    $rs = $srch->getResultSet();
    if ($srch->recordCount() > 0) {
        return $db->fetch($rs);
    } else {
        return array('en_city_subscriber' => 0, 'en_favourite_merchant' => 0, 'en_near_to_expired' => 0, 'en_earned_deal_buck' => 0, 'en_friend_buy_deal' => 0);
    }
}

function addUpdateEmailNotification($data, &$error)
{
    global $db;
    $record = new TableRecord('tbl_email_notification');
    $rs = $db->query('select * from tbl_email_notification where  en_user_id = ' . intval($_SESSION['logged_user']['user_id']));
    if ($db->total_records($rs) > 0) {
        $record->assignValues($data);
        if (!$record->update('en_user_id=' . intval($_SESSION['logged_user']['user_id']))) {
            $error = $record->getError();
            return false;
        }
        return true;
    } else {
        if (!$db->insert_from_array('tbl_email_notification', $data, false)) {
            $error = $record->getError();
            return false;
        }
    }
    return true;
}

function updateUserInfo($request_data, &$error)
{
    require_once './site-classes/user-info.cls.php';
    $user = new userInfo();

    if (!$user->updateUserAccount($_SESSION['logged_user']['user_id'], $request_data)) {
        $error = Message::getHtml();
        return false;
    }
    return true;
}

function addcreditcard($request_data, &$error)
{
    error_reporting(0);
    global $db;
    require_once './includes/site-functions-extended.php';
    $customerShippingAddressId = NULL;

    if (((int) $request_data["customerProfileId"]) <= 0) {
        $error = t_lang('M_ERROR_INVALID_REQUEST');
        return false;
    }
    if ($pay_profile_id = createCIMCustomerPaymentProfile($request_data)) {
        if (isset($pay_profile_id['error'])) {
            $error = $pay_profile_id['error'];
            return false;
        } else {
            if (!$db->insert_from_array('tbl_users_card_detail', array('ucd_user_id' => $_SESSION['logged_user']['user_id'], 'ucd_customer_payment_profile_id' => htmlspecialchars($pay_profile_id), 'ucd_card' => substr($request_data['cardNumber'], -4), 'ucd_street_address' => $request_data["address1"], 'ucd_street_address2' => $request_data["address2"], 'ucd_city' => $request_data["city"], 'ucd_state' => $request_data["state"], 'ucd_zip' => $request_data["zip"], 'ucd_state_id' => $request_data["state_id"], 'ucd_country_id' => $request_data["country_id"]), false)) {
                $error = $db->getError();
                return false;
            }
            return true;
        }
    }
}

function deleteCreditCardInfo($profileId, &$error)
{
    require_once './includes/site-functions-extended.php';
    global $db;
    if (CONF_PAYMENT_PRODUCTION == 0) {
        $payMode = 'testMode';
    } else {
        $payMode = 'liveMode';
    }
    if (((int) $profileId) > 0) {
        $rs_capture = $db->query("select count(*) as pay_pending from tbl_orders where order_payment_capture=0 and order_payment_status=3 and order_payment_profile_id=" . intval($profileId));
        $order_pending = $db->fetch($rs_capture);
        if (((int) $order_pending['pay_pending']) > 0) {
            $error = t_lang('M_TXT_PAYMENT_PROFILE_CANT_DELETED');
            return false;
        }

        if (deleteCIMCustomerPaymentProfile(intval($profileId))) {
            if ($db->query("DELETE FROM tbl_users_card_detail WHERE ucd_customer_payment_profile_id=" . intval($profileId))) {
                return true;
            }
        } else {


            if ($db->query("DELETE FROM tbl_users_card_detail WHERE ucd_customer_payment_profile_id=" . intval($profileId))) {
                //  die(t_lang('M_TXT_DELETED_CARD_INFORMATION'));
                return true;
            }
        }
    }
    $error = t_lang('M_ERROR_INVALID_REQUEST');
    return false;
}

function getWalletAmount($user_id)
{
    global $db;
    $rs = $db->query("select user_wallet_amount from tbl_users where user_id=" . $user_id);
    $row = $db->fetch($rs);
    return $row['user_wallet_amount'];
}

function getSubscribedCities()
{
    $srch = new SearchBase('tbl_newsletter_subscription', 's');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('s.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
    $cnd->attachCondition('s.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->joinTable('tbl_cities', 'INNER JOIN', 's.subs_city = c.city_id', 'c');
    $srch->addMultipleFields(array('s.subs_id', 's.subs_city', 'c.city_name', 'c.city_id'));
    $srch->addOrder('c.city_name');
    $rs = $srch->getResultSet();
    return $rs;
}

function fetchSubscribedCategories($city_id, $category_id)
{
    global $db;
    $catobj = fetchParentCategories($category_id);
    $rs1 = $catobj->getResultSet();
    $catlist = $db->fetch_all($rs1);
    $catIdlist = array_column($catlist, 'cat_id');
    $srch = new SearchBase('tbl_newsletter_subscription', 's');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('s.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
    $cnd->attachCondition('s.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->joinTable('tbl_cities', 'INNER JOIN', 's.subs_city = c.city_id and c.city_id=' . $city_id, 'c');
    $srch->joinTable('tbl_newsletter_category', 'INNER JOIN', 's.subs_id = nc.nc_subs_id', 'nc');
    $srch->addCondition('c.city_id', '=', $city_id);
    $srch->addCondition('nc.nc_cat_id', 'IN', $catIdlist);
    $srch->addMultipleFields(array('s.subs_id', 's.subs_city', 'nc.nc_cat_id'));
    $srch->addFld('nc.nc_cat_id');
    $rs = $srch->getResultSet();
    $rslt = $db->fetch_all($rs);

    return $rslt;
}

function fetchCategoriesRecursevly($cat_parent_id = 0)
{
    global $db;
    $srch = new SearchBase('tbl_deal_categories', 'm');
    $srch->joinTable('tbl_deal_categories', 'LEFT OUTER JOIN', 'm.cat_parent_id = p.cat_id', 'p');
    $srch->addMultipleFields(array('m.cat_id', 'm.cat_parent_id', 'm.cat_code', 'm.cat_name' . $_SESSION['lang_fld_prefix'], "CONCAT(CASE WHEN m.cat_parent_id = 0 THEN '' ELSE LPAD(p.cat_display_order, 7, '0') END, LPAD(m.cat_display_order, 7, '0')) AS display_order"));
    $srch->addCondition('m.cat_parent_id', '=', $cat_parent_id);
    $srch->addOrder('display_order');
    $categoryList = $srch->getResultSet();
    $deal_cat_arr = $db->fetch_all($categoryList);
    foreach ($deal_cat_arr as $key => $value) {
        if ($value['cat_id']) {
            $deal_cat_arr[$key][] = fetchCategoriesRecursevly($value['cat_id']);
        }
    }

    return $deal_cat_arr;
}

function addSubscribedCity($email, $cityId, $data)
{
    global $db;
    global $msg;
    $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email='" . $email . "' and subs_city='" . $cityId . "'");
    $result = $db->fetch($check_unique);
    if ($db->total_records($check_unique) == 0) {
        $record = new TableRecord('tbl_newsletter_subscription');
        $record->assignValues($data);
        $code = mt_rand(0, 999999999999999);
        $record->setFldValue('subs_addedon', date('Y-m-d H:i:s'), true);
        $record->setFldValue('subs_code', $code, '');
        $record->setFldValue('subs_email', $data['logged_email'], '');
        $record->setFldValue('subs_city', $cityId, '');
        $record->setFldValue('subs_email_verified', 1, '');
        $record->setFldValue('subs_user_id', $_SESSION['logged_user']['user_id'], '');
        $success = $record->addNew();
        if ($success) {
            $nc_subs_id = $record->getId();
            if (!insertsubscatCity($nc_subs_id, $error)) {
                $msg->addError($error);
                return false;
            }
            return true;
        } else {
            return false;
        }
    } else {
        $db->query("UPDATE tbl_newsletter_subscription set subs_user_id='" . $_SESSION['logged_user']['user_id'] . "' where subs_email='" . $data['logged_email'] . "' and  subs_city='" . $cityId . "'");
    }
    return true;
}

function removeSubscribedCity($cityId)
{
    global $db;
    $srch = new SearchBase('tbl_newsletter_subscription', 's');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('s.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
    $cnd->attachCondition('s.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->addCondition('s.subs_city', '=', $cityId);
    $srch->addMultipleFields('s.subs_id');
    $rs = $srch->getResultSet();
    $row = $db->fetch($rs);
    if ($row) {
        $nc_subs_id = $row['subs_id'];
        $db->query("DELETE FROM tbl_newsletter_category WHERE nc_subs_id =$nc_subs_id ");
        $db->query("DELETE FROM tbl_newsletter_subscription WHERE  subs_city=$cityId and subs_email ='" . $_SESSION['logged_user']['user_email'] . "'");
        return true;
    }
    return false;
}

function removedSubscribedCategoryCity($city_id, $nc_cat_id)
{
    global $msg;
    global $db;
    $srch = new SearchBase('tbl_newsletter_subscription', 's');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('s.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
    $cnd->attachCondition('s.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->addCondition('s.subs_city', '=', $city_id);
    $srch->joinTable('tbl_cities', 'INNER JOIN', 's.subs_city = c.city_id', 'c');
    $srch->addMultipleFields('s.subs_id', 'c.city_name');
    $rs = $srch->getResultSet();
    while ($row = $db->fetch($rs)) {
        $city_name = $row['city_name'];
        $nc_subs_id = intval($row['subs_id']);
        $db->query("DELETE FROM tbl_newsletter_category WHERE nc_subs_id =$nc_subs_id and nc_cat_id=$nc_cat_id");
    }
    $msg->addMsg(t_lang('M_TXT_YOUR_SUBSCRIPTION') . ' ' . $city_name . ' ' . t_lang('M_TXT_CITYWIDE_UPDATED'));
    return true;
}

function deleteCategoriesByCityId($catId, $cityId)
{
    global $db;
    $catCode = fetchCatCode($catId);
    $srch1 = new SearchBase('tbl_deal_categories', 'dc');
    $srch1->addCondition('dc.cat_code', 'LIKE', $catCode . '%');
    $srch1->addMultipleFields(array('dc.cat_id'));
    $rs1 = $srch1->getResultSet();
    $catIdArray = $db->fetch_all($rs1);
    $nc_cat_id = intval($post['cat_id']);
    $post = getPostedData();
    $srch = new SearchBase('tbl_newsletter_subscription', 's');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('s.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
    $cnd->attachCondition('s.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->addCondition('s.subs_city', '=', $cityId);
    $srch->joinTable('tbl_cities', 'INNER JOIN', 's.subs_city = c.city_id', 'c');
    $srch->addMultipleFields('s.subs_id', 'c.city_name');
    $rs = $srch->getResultSet();
    while ($row = $db->fetch($rs)) {
        $city_name = $row['city_name'];
        $nc_subs_id = intval($row['subs_id']);
        //$db->query("DELETE FROM tbl_newsletter_category WHERE nc_subs_id =$nc_subs_id and nc_cat_id=$nc_cat_id");
    }
    foreach ($catIdArray as $key => $value) {
        $db->query("DELETE FROM tbl_newsletter_category WHERE nc_subs_id =$nc_subs_id and nc_cat_id={$value['cat_id']}");
    }
    return $city_name;
}

function addCategoriesByCityId($catId, $cityId, &$catIdArrays = array(), &$city_name = '')
{
    global $db;
    $catCode = fetchCatCode($catId);
    $srch1 = new SearchBase('tbl_deal_categories', 'dc');
    $srch1->addCondition('dc.cat_code', 'LIKE', $catCode . '%');
    $srch1->addMultipleFields(array('dc.cat_id'));
    $rs1 = $srch1->getResultSet();
    $catIdArray = $db->fetch_all($rs1);
    $srch = new SearchBase('tbl_newsletter_subscription', 's');
    $cnd = $srch->addDirectCondition('0');
    $cnd->attachCondition('s.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
    $cnd->attachCondition('s.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->addCondition('s.subs_city', '=', $cityId);
    $srch->joinTable('tbl_cities', 'INNER JOIN', 's.subs_city = c.city_id', 'c');
    $srch->addMultipleFields('s.subs_id', 'c.city_name');
    //  echo $srch->getQuery();
    $rs = $srch->getResultSet();
    $catIdArrays = array();
    while ($row = $db->fetch($rs)) {
        $city_name = $row['city_name'];
        $record = new TableRecord('tbl_newsletter_category');
        foreach ($catIdArray as $key => $value) {
            $arr_insert = array(
                'nc_subs_id' => $row['subs_id'],
                'nc_cat_id' => $value['cat_id']
            );
            $catIdArrays[] = $value['cat_id'];
            $record->assignValues($arr_insert);
            $success = $record->addNew();
            if (!$success) {
                $msg->addError($record->getError());
            }
        }
    }
    return true;
}

?>