<?php

require_once './application-top.php';
require_once './site-classes/user-info.cls.php';
require_once './includes/site-functions-extended.php';
require_once './includes/page-functions/user-functions.php';

function insertCategory($parent_id, $arr_subscribed = [], $cityId = '', $code = '')
{
    $str = '';
    global $db;
    $categoryArray = $db->query("select cat_id,cat_name" . $_SESSION['lang_fld_prefix'] . " from tbl_deal_categories 
	where cat_parent_id = {$parent_id} order by cat_display_order ");
    $rows = $db->fetch_all_assoc($categoryArray);
    $str .= "<ul class='list__vertical'>";
    foreach ($rows as $key1 => $val1) {
        $subCat = fetchCategory($key1, $arr_subscribed, $cityId);
        $str .= '<li><label class="checkbox"><input type="checkbox" value="1"  onClick="if(this.checked){ return updateCatsubs(' . $cityId . ',' . $key1 . ')}else{ return insertCatsubs(' . $cityId . ',' . $key1 . ')}" name="subscitycat_' . $cityId . '_' . $key1 . '"' . (($arr_subscribed) ? ' checked="checked"' : '') . '> <i class="input-helper"></i>' . $val1 . '</label>';
        if (strlen($subCat) > 0) {
            $str .= $subCat;
        }
        $str .= '</li>';
    }
    $str .= "</ul>";
    return $str;
}

if (!isUserLogged()) {
    $_SESSION['login_page'] = 'buy-deal.php';
    $msg->display();
    die(t_lang('M_TXT_SESSION_EXPIRES'));
    require_once './msgdie.php';
    printf(t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN'), '<a href="' . CONF_WEBROOT_URL . 'login.php">' . t_lang('M_TXT_HERE') . '</a>');
    die();
}
$post = getPostedData();
if ($_GET['mode'] == 'editEmail') {
    $post['mode'] = $_GET['mode'];
}
switch (strtoupper($post['mode'])) {
    case 'EDITEMAIL':
        $frm = getMBSFormByIdentifier('frmMyAccount');
        $frm->setExtra = 'class="siteForm"';
        $frm->setAction($_SERVER['PHP_SELF']);
        $frm->setTableProperties('class="formwrap__table"');
        $fld = $frm->getField('user_name');
        $fld->requirements()->setRequired(true);
        $fld->value = $_SESSION['logged_user']['user_name'];
        $fld = $frm->getField('user_lname');
        $fld->changeCaption('Last Name');
        $fld->value = $_SESSION['logged_user']['user_lname'];
        $fld = $frm->getField('user_email');
        $fld->value = $_SESSION['logged_user']['user_email'];
        $fld = $frm->getField('user_city');
        $cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0");
        $fld->changeCaption('City of Interest');
        $fld->options = $db->fetch_all_assoc($cityList);
        $fld->value = $_SESSION['logged_user']['user_city'];
        $fld = $frm->getField('btn_submit');
        $fld->value = t_lang('M_TXT_UPDATE');
        $fld = $frm->getField('email');
        $frm->removeField($fld);
        $fld = $frm->getField('user_avatar');
        $frm->removeField($fld);
        $arr_timezones = DateTimeZone::listIdentifiers();
        $arr_timezones = array_combine($arr_timezones, $arr_timezones);
        $fld = $frm->getField('user_timezone');
        $fld->options = $arr_timezones;
        $fld->value = $_SESSION['logged_user']['user_timezone'];
        $fld = $frm->getField('user_newsletter');
        $frm->removeField($fld);
        $fld = $frm->getField('password');
        //$fld->html_after_field = '<br/>' . t_lang('M_TXT_LEAVE_BLANK_TO_KEEP_SAME');
        $frm->setValidatorJsObjectName('frmValidatoraccount');
        $frm->setOnSubmit('submitAccountInfo(this, frmValidatoraccount); return(false);');
        $frm->addHiddenField('', 'mode', 'updateaccountinfo', '', '');
        $frm->addHiddenField('', 'user_id', $_SESSION['logged_user']['user_id'], 'user_id', '');
        updateFormLang($frm);
        $fld = $frm->getField('btn_submit');
//		$fld->extra='onclick="return doSubmitFormAjax()"';
        $fld->value = t_lang('M_TXT_SUBMIT');
        echo $frm->getFormHtml();
        break;
    case 'UPDATEACCOUNTINFO':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = getPostedData();
            $post['user_name'] = htmlentities($post['user_name'], ENT_QUOTES, 'UTF-8');
            if (isset($_FILES) && $_FILES['user_avatar']['error'] == "0") {
                $ext = strtolower(strrchr($_FILES['user_avatar']['name'], '.'));
                if (!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png', '.bmp'))) {
                    echo 'ext panga ' . $post['user_avatar'] = '-1';
                } else {
                    $img = time() . $_FILES['user_avatar']['name'];
                    $img_dst = USER_IMAGES_PATH . $img;
                    $img_src = $_FILES['user_avatar']['tmp_name'];
                    if (move_uploaded_file($img_src, $img_dst)) {
                        $post['user_avatar'] = $img;
                    } else {
                        $post['user_avatar'] = '-1';
                    }
                }
            } else {
                $post['user_avatar'] = '';
            }
            $user = new userInfo();
            $user->updateUserAccount($_SESSION['logged_user']['user_id'], $post);
        }
        break;
    case 'ADDCARDDETAIL':
        if (((int) $_SESSION['logged_user']['user_customer_profile_id']) == 0) {
            if (!createCIMCustomerProfile()) { /* To create logged in user's CIM Customer profileId */
                die($msg->display());
            }
        }
        echo addCardDetailForm();
        break;
    case 'UPDATECARDINFO':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = getPostedData();
            $customerShippingAddressId = NULL;
            if (((int) $post["customerProfileId"]) <= 0) {
                $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
            }
            if ($pay_profile_id = createCIMCustomerPaymentProfile($post)) {
                if (isset($pay_profile_id['error'])) {
                    echo $pay_profile_id['error'];
                    echo $frm1 = addCardDetailForm($post);
                    exit();
                } else {
                    if (!$db->insert_from_array('tbl_users_card_detail', array('ucd_user_id' => $_SESSION['logged_user']['user_id'], 'ucd_customer_payment_profile_id' => htmlspecialchars($pay_profile_id), 'ucd_card' => substr($post['cardNumber'], -4), 'ucd_street_address' => $post["address1"], 'ucd_street_address2' => $post["address2"], 'ucd_city' => $post["city"], 'ucd_state' => $post["state"], 'ucd_zip' => $post["zip"], 'ucd_state_id' => $post["state_id"], 'ucd_country_id' => $post["country_id"]), false)) {
                        echo $db->getError();
                    }
                    echo t_lang('M_TXT_UPDATED_CARD_INFORMATION');
                }
            }
        }
        break;
    case 'DELETECARDINFO':
        if (CONF_PAYMENT_PRODUCTION == 0) {
            $payMode = 'testMode';
        } else {
            $payMode = 'liveMode';
        }
        if (((int) $_POST["profileId"]) > 0) {
            $rs_capture = $db->query("select count(*) as pay_pending from tbl_orders where order_payment_capture=0 and order_payment_status=3 and order_payment_profile_id=" . intval($_POST["profileId"]));
            $order_pending = $db->fetch($rs_capture);
            if (((int) $order_pending['pay_pending']) > 0) {
                die(t_lang('M_TXT_PAYMENT_PROFILE_CANT_DELETED'));
            }
            if (deleteCIMCustomerPaymentProfile(intval($_POST["profileId"]))) {
                if ($db->query("DELETE FROM tbl_users_card_detail WHERE ucd_customer_payment_profile_id=" . intval($_POST["profileId"]))) {
                    die(t_lang('M_TXT_DELETED_CARD_INFORMATION'));
                }
            } else {
                //die($msg->display());
                if ($db->query("DELETE FROM tbl_users_card_detail WHERE ucd_customer_payment_profile_id=" . intval($_POST["profileId"]))) {
                    die(t_lang('M_TXT_DELETED_CARD_INFORMATION'));
                }
            }
        }
        die(t_lang('M_ERROR_INVALID_REQUEST'));
        break;
    case 'UPDATEEXPIRE':
        $post = getPostedData();
        $record = new TableRecord('tbl_email_notification');
        $arr_updates = array('en_near_to_expired' => $post['value']);
        $rs = $db->query('select * from tbl_email_notification where  en_user_id = ' . intval($_SESSION['logged_user']['user_id']));
        if ($db->total_records($rs) > 0) {
            $record->assignValues($arr_updates);
            if (!$record->update('en_user_id=' . $_SESSION['logged_user']['user_id'])) {
                $msg->addError($record->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        } else {
            if (!$db->insert_from_array('tbl_email_notification', array('en_user_id' => $_SESSION['logged_user']['user_id'], 'en_favourite_merchant' => 0, 'en_city_subscriber' => 0, 'en_near_to_expired' => 1, 'en_earned_deal_buck' => 0, 'en_friend_buy_deal' => 0), false)) {
                $msg->addError($db->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        }
        $arr = array('status' => 1, 'msg' => t_lang('M_TXT_RECORD_UPDATED'));
        die(convertToJson($arr));
        break;
    case 'UPDATECITYSUBSCRIBER':
        $post = getPostedData();
        $record = new TableRecord('tbl_email_notification');
        $arr_updates = array('en_city_subscriber' => $post['value']);
        $rs = $db->query('select * from tbl_email_notification where  en_user_id = ' . intval($_SESSION['logged_user']['user_id']));
        if ($db->total_records($rs) > 0) {
            $record->assignValues($arr_updates);
            if (!$record->update('en_user_id=' . $_SESSION['logged_user']['user_id'])) {
                $msg->addError($record->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        } else {
            if (!$db->insert_from_array('tbl_email_notification', array('en_user_id' => $_SESSION['logged_user']['user_id'], 'en_favourite_merchant' => 0, 'en_city_subscriber' => 1, 'en_near_to_expired' => 0, 'en_earned_deal_buck' => 0, 'en_friend_buy_deal' => 0), false)) {
                $msg->addError($db->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        }
        $arr = array('status' => 1, 'msg' => t_lang('M_TXT_RECORD_UPDATED'));
        die(convertToJson($arr));
        break;
    case 'UPDATEFAVOURITEMERCHANTS':
        $post = getPostedData();
        $record = new TableRecord('tbl_email_notification');
        $arr_updates = array(
            'en_favourite_merchant' => $post['value']
        );
        $rs = $db->query('select * from tbl_email_notification where  en_user_id = ' . intval($_SESSION['logged_user']['user_id']));
        if ($db->total_records($rs) > 0) {
            $record->assignValues($arr_updates);
            if (!$record->update('en_user_id=' . intval($_SESSION['logged_user']['user_id']))) {
                $msg->addError($record->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        } else {
            if (!$db->insert_from_array('tbl_email_notification', array('en_user_id' => intval($_SESSION['logged_user']['user_id']), 'en_favourite_merchant' => 1, 'en_city_subscriber' => 0, 'en_near_to_expired' => 0, 'en_earned_deal_buck' => 0, 'en_friend_buy_deal' => 0), false)) {
                $msg->addError($db->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        }
        $arr = array('status' => 1, 'msg' => t_lang('M_TXT_RECORD_UPDATED'));
        die(convertToJson($arr));
        break;
    case 'UPDATEDEALBUCK':
        $post = getPostedData();
        $record = new TableRecord('tbl_email_notification');
        $arr_updates = array('en_earned_deal_buck' => $post['value']);
        $rs = $db->query('select * from tbl_email_notification where  en_user_id = ' . intval($_SESSION['logged_user']['user_id']));
        if ($db->total_records($rs) > 0) {
            $record->assignValues($arr_updates);
            if (!$record->update('en_user_id=' . intval($_SESSION['logged_user']['user_id']))) {
                $msg->addError($record->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        } else {
            if (!$db->insert_from_array('tbl_email_notification', array('en_user_id' => intval($_SESSION['logged_user']['user_id']), 'en_favourite_merchant' => 0, 'en_city_subscriber' => 0, 'en_near_to_expired' => 0, 'en_earned_deal_buck' => 1, 'en_friend_buy_deal' => 0), false)) {
                $msg->addError($db->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        }
        $arr = array('status' => 1, 'msg' => t_lang('M_TXT_RECORD_UPDATED'));
        die(convertToJson($arr));
        break;
    case 'UPDATEFRIENDBUY':
        $post = getPostedData();
        $record = new TableRecord('tbl_email_notification');
        $arr_updates = array('en_friend_buy_deal' => $post['value']);
        $rs = $db->query('select * from tbl_email_notification where en_user_id = ' . intval($_SESSION['logged_user']['user_id']));
        if ($db->total_records($rs) > 0) {
            $record->assignValues($arr_updates);
            if (!$record->update('en_user_id=' . $_SESSION['logged_user']['user_id'])) {
                $msg->addError($record->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        } else {
            if (!$db->insert_from_array('tbl_email_notification', array('en_user_id' => $_SESSION['logged_user']['user_id'], 'en_favourite_merchant' => 0, 'en_city_subscriber' => 0, 'en_near_to_expired' => 0, 'en_earned_deal_buck' => 0, 'en_friend_buy_deal' => 1), false)) {
                $msg->addError($db->getError());
                $arr = array('status' => 0, 'msg' => $msg);
                die(convertToJson($arr));
            }
        }
        $arr = array('status' => 1, 'msg' => t_lang('M_TXT_RECORD_UPDATED'));
        die(convertToJson($arr));
        break;
    case 'UPDATECATSUBS':
        $post = getPostedData();
        $catIdArrays = [];
        $city_name = "";
        addCategoriesByCityId($post['cat_id'], $post['city'], $catIdArrays, $city_name);
        $msg->addMsg(t_lang('M_TXT_YOUR_SUBSCRIPTION') . ' ' . $city_name . ' ' . t_lang('M_TXT_CITYWIDE_UPDATED'));
        $arr = array('status' => 1, 'msg' => $msg->display());
        die(convertToJson($arr));
        break;
    case 'INSERTPARENTCHILDCAT':
        $post = getPostedData();
        $catIdArrays = [];
        $city_name = "";
        addCategoriesByCityId($post['cat_id'], $post['city'], $catIdArrays, $city_name);
        $str = insertCategory($post['cat_id'], $catIdArrays, $post['city']);
        $msg->addMsg(t_lang('M_TXT_YOUR_SUBSCRIPTION') . ' ' . $city_name . ' ' . t_lang('M_TXT_CITYWIDE_UPDATED'));
        $arr = array('status' => 1, 'msg' => $msg->display(), 'str' => $str);
        die(convertToJson($arr));
        break;
    case 'DELETEPARENTCHILDCAT':
        $post = getPostedData();
        $city_name = deleteCategoriesByCityId($post['cat_id'], $post['city']);
        $str = insertCategory($post['cat_id'], '', $post['city']);
        $msg->addMsg(t_lang('M_TXT_YOUR_SUBSCRIPTION') . ' ' . $city_name . ' ' . t_lang('M_TXT_CITYWIDE_UPDATED'));
        $arr = array('status' => 1, 'msg' => $msg->display(), 'str' => $str);
        die(convertToJson($arr));
        break;
    case 'DELETECATSUBS':
        $post = getPostedData();
        $nc_cat_id = intval($post['cat_id']);
        $post = getPostedData();
        //removedSubscribedCategoryCity($post['city'],$nc_cat_id);
        $city_name = deleteCategoriesByCityId($post['cat_id'], $post['city']);
        $msg->addMsg(t_lang('M_TXT_YOUR_SUBSCRIPTION') . ' ' . $city_name . ' ' . t_lang('M_TXT_CITYWIDE_UPDATED'));
        $arr = array('status' => 1, 'msg' => $msg->display());
        die(convertToJson($arr));
        break;
    case 'SUBSCRIPTIONCATEGORY':
        $post = getPostedData();
        $srch = new SearchBase('tbl_deal_categories');
        $srch->addOrder('cat_display_order');
        $srch->addMultipleFields(array('cat_id', 'cat_name' . $_SESSION['lang_fld_prefix']));
        $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
        $pagesize = 5;
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $arr_cats = $db->fetch_all_assoc($rs);
        $pages = $srch->pages();
        if ($pages > 1) {
            $pagestring .= createHiddenFormFromPost('frmPaging', '', array('page', 'mode'), array('page' => '', 'mode' => 'subscriptionCategory'));
            $pagestring .= '<ul class="paging"><li class="space">' . t_lang('M_TXT_DISPLAYING_PAGE') . ' ' . $page . ' ' . t_lang('M_TXT_OF') . ' ' . $pages . ' ';
        }
        if ($pages > 1) {
            $pagestring .= t_lang('M_TXT_GOTO') . '  </li>' .
                    getPageString('<li><a onclick="document.frmPaging.page.value=xxpagexx; subscriptionCategory(document.frmPaging);" 
			href="javascript:void(0);">xxpagexx</a></li> ', $pages, $page, '<li><a class="pagingActive" href="javascript:void(0);">xxpagexx</a></li> ', '...') . '</ul>';
            echo ' <div class="tableWrapper_top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td width="75%">
                            	' . $pagestring . '
                            </td>
                            <td width="30%">&nbsp;</td>
                           </tr>
                      </table>
                    </div> ';
        }
        $srch = new SearchBase('tbl_newsletter_subscription', 's');
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('s.subs_email', '=', $_SESSION['logged_user']['user_email'], 'OR');
        $cnd->attachCondition('s.subs_user_id', '=', $_SESSION['logged_user']['user_id']);
        $srch->joinTable('tbl_cities', 'INNER JOIN', 's.subs_city = c.city_id', 'c');
        $srch->addMultipleFields('s.subs_id', 's.subs_city', 'c.city_name');
        $rs = $srch->getResultSet();
        $html .= '<div class="tableWrapper_mid">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="emails_table">
			<tr>
                    	<th >' . t_lang('M_FRM_CITY') . '</th>';
        foreach ($arr_cats as $cat) {
            $html .= '<th  align="center">' . $cat . '</th>';
        }
        $html .= '</tr> ';
        $totalRec = $db->total_records($rs);
        $count = 0;
        while ($row = $db->fetch($rs)) {
            $count++;
            if ($count == $totalRec) {
                $classl = 'last-row';
            } else {
                $classl = '';
            }
            $rs_subscribed = $db->query("SELECT nc_cat_id, nc_cat_id as catid FROM tbl_newsletter_category WHERE nc_subs_id = " . intval($row['subs_id']));
            $arr_subscribed = $db->fetch_all_assoc($rs_subscribed);
            $html .= '<tr >
			 <td width="30%">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td><span>' . $row['city_name' . $_SESSION['lang_fld_prefix']] . '</span></td>
                            <td><a href="?remove-link=' . $row['subs_city'] . '" class="delete" title="' . t_lang('M_TXT_DELETE') . '"><img src="' . CONF_WEBROOT_URL . 'images/delete.png" alt="" /></a></td>
                          </tr>
                        </table>
                    </td>';
            foreach ($arr_cats as $cat_id => $cat_name) {
                $html .= '<td><input type="checkbox" value="1"  onClick="if(this.checked){ return updateCatsubs(' . $row['city_id'] . ',' . $cat_id . ')}else{ return insertCatsubs(' . $row['city_id'] . ',' . $cat_id . ')}" name="subscitycat_' . $row['city_id'] . '_' . $cat_id . '"' . ((in_array($cat_id, $arr_subscribed)) ? ' checked="checked"' : '') . '></td>';
            }
            $html .= '</tr>';
        }
        if ($totalRec == 0) {
            $html .= '
		<tr><td colspan="' . (count($arr_cats) + 1) . '">' . t_lang('M_MSG_NO_CITY_SUBSCRIBED') . '</td></tr>';
        }
        $html .= '</table></div>';
        echo $html;
        break;
}