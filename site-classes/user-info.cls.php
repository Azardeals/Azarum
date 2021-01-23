<?php

require_once CONF_INSTALLATION_PATH . 'includes/navigation-functions.php';

class userInfo extends TableRecord
{

    private $user;

    function __construct()
    {
        if (!parent::__construct('tbl_users'))
            return false;
        $this->user = [];
    }

    function addUser($user_email, $user_code, $user_name, $user_lname, $user_gender, $user_dob, $user_password, $user_city, $user_timezone)
    {
        $this->user = array('user_email' => $user_email, 'reg_code' => $user_code, 'user_name' => $user_name, 'user_lname' => $user_lname, 'user_gender' => $user_gender, 'user_dob' => $user_dob,
            'user_password' => $user_password, 'user_city' => $user_city, 'user_timezone' => $user_timezone);
    }

    function addNew($x = '', $y = '')
    {
        if (count($this->user) == 0) {
            $this->error = t_lang('M_ERROR_NO_USER_ADDED');
            return false;
        }
        if (!isset($this->flds['user_regdate']))
            $this->setFldValue('user_regdate', date('Y-m-d H:i:s'), true);
        /* Set affiliate id for order */
        if (isset($_COOKIE['affid']))
            $this->setFldValue('user_affiliate_id', $_COOKIE['affid'] + 0);
        if (isset($_COOKIE['refid']))
            $this->setFldValue('user_referral_id', $_COOKIE['refid'] + 0);
        /* Set affiliate id for order ends */
        foreach ($this->user as $key => $arr) {
            if (!isset($this->flds[$key]))
                $this->setFldValue($key, $arr, true);
        }
        if (!parent::addNew()) {
            return false;
        }
        $user_code = $this->user['reg_code'];
        $user_email = $this->user['user_email'];
        $user_id = intval($this->getId());
        $this->sendVerificationEmail($user_id, $this->user['user_name'], $this->user['user_email'], $this->user['user_member_id'], $user_code, $this->user['user_city'], 1);
        return true;
    }

    function getUserId()
    {
        return $this->getUserId;
    }

    function sendVerificationEmail($user_id, $user_name, $user_email, $member_id, $user_code, $user_city, $show_msg = 0)
    {
        $user_id = intval($user_id);
        if ($user_id < 1) {
            return false;
        }
        global $db;
        if ($user_city != "") {
            $rs = $db->query("select city_name, city_id from tbl_cities where city_active=1 and city_deleted=0 and city_request=0 and city_id=" . $user_city);
            $row = $db->fetch($rs);
        }
        $rs = $db->query("select * from tbl_email_templates where tpl_id=3");
        $row_tpl = $db->fetch($rs);
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = array(
            'xxuser_namexx' => htmlentities($user_name),
            'xxverification_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'verify-user.php?code=' . $user_code . '&mail=' . urlencode($user_email),
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxuser_member_idxx' => $member_id,
            'xxclick_buttonxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/click' . $_SESSION['lang_fld_prefix'] . '.png',
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        $mail_sent = false;
        if ($row_tpl['tpl_status'] == 1) {
            $db->insert_from_array('tbl_user_email_verification', array(
                'uev_user_id' => $user_id
            ));
            if (sendMail($user_email, $subject, emailtemplate($message), $headers)) {
                $mail_sent = true;
                $arr = array('status' => 1, 'msg' => t_lang('M_TXT_MAIL_SENT'));
            } else {
                $arr = array('status' => 0, 'msg' => t_lang('M_MSG_EMAIL_SENDING_FAILED'));
            }
        }
        if ($show_msg == 1) {
            echo convertToJson($arr);
        }
        if ($mail_sent === true) {
            return true;
        }
        return false;
    }

    /* CODE FOR MY-PROFILE.PHP */

    function userToCategory($user_id, $categories)
    {
        if ($user_id != $_SESSION['logged_user']['user_id']) {
            $this->error = 'User id is not valid.';
            return false;
        }
        $this->db->query("delete from tbl_user_to_deal_cat where udc_user_id=" . $_SESSION['logged_user']['user_id']);
        if (is_array($categories)) {
            foreach ($categories as $cat)
                $this->db->insert_from_array('tbl_user_to_deal_cat', array('udc_user_id' => $_SESSION['logged_user']['user_id'],
                    'udc_cat_id' => $cat));
        }
        return true;
    }

    function updateUserAccount($user_id, $account_info)
    {
        global $msg;
        if ($account_info['user_name'] != "") {
            $arr_updates = array(
                'user_name' => $account_info['user_name'],
                'user_lname' => $account_info['user_lname'],
                'user_city' => $account_info['user_city'],
                'user_email' => $account_info['user_email'],
                'user_timezone' => $account_info['user_timezone']
            );
            if (isset($account_info['password'])) {
                if ($account_info['password'] != '') {
                    $arr_updates['user_password'] = md5($account_info['password']);
                }
            }
            $record = new TableRecord('tbl_users');
            $record->assignValues($arr_updates);
            if (!$record->update('user_id=' . $_SESSION['logged_user']['user_id'])) {
                $msg->addError($record->getError());
                return false;
            } else {
                $_SESSION['logged_user']['user_name'] = $account_info['user_name'];
                $_SESSION['logged_user']['user_email'] = $account_info['user_email'];
                $_SESSION['logged_user']['user_lname'] = $account_info['user_lname'];
                $_SESSION['logged_user']['user_city'] = $account_info['user_city'];
                $_SESSION['logged_user']['user_timezone'] = $account_info['user_timezone'];
                $laname = "";
                if ($_SESSION['logged_user']['user_lname']) {
                    $laname = ' ' . $_SESSION['logged_user']['user_lname'];
                }
                $name = $_SESSION['logged_user']['user_name'] . $laname;
                $arr = array('status' => 1, 'msg' => t_lang('M_TXT_INFO_UPDATED'), 'username' => htmlentities($name), 'useremail' => $_SESSION['logged_user']['user_email']);
                die(convertToJson($arr));
            }
        }
    }

    function updateUserWallet($entry_type, $wallet_amount, $wallet_new_amount, $uwh_particulars, $user_id)
    {
        global $msg;
        global $db;
        if (intval($user_id) <= 0 && intval($entry_type) <= 0) {
            $this->error = t_lang('M_TXT_INVALID_REQUEST');
            return false;
        }
        $wallet_amount = $this->getUserWalletAmount($user_id);
        if (( is_numeric($wallet_new_amount)) && ((float) $wallet_new_amount) > 0) {
            if (intval($entry_type) === 1) {
                $wallet = $wallet_amount - $wallet_new_amount;
                $wallet_new_amount = "-" . $wallet_new_amount;
            } elseif (intval($entry_type) === 2) {
                $wallet = $wallet_amount + $wallet_new_amount;
                $wallet_new_amount = "+" . $wallet_new_amount;
            } else {
                $this->error = t_lang('M_TXT_INVALID_ENTRY_TYPE');
                return false;
            }
            if (!$this->db->update_from_array('tbl_users', array('user_wallet_amount' => $wallet), 'user_id=' . $user_id)) {
                $this->error = $this->db->getError();
                return false;
            } else {
                if (!$db->insert_from_array('tbl_user_wallet_history', array('wh_user_id' => $user_id, 'wh_particulars' => 'Updated By Admin: ' . $uwh_particulars, 'wh_amount' => $wallet_new_amount, 'wh_time' => 'mysql_func_now()'), true)) {
                    $this->error = $this->db->getError();
                    return false;
                }
            }
            $msg->addMsg(t_lang('M_TXT_WALLET_UPDATED'));
            $Usermsg = '<div class="box" id="messages">
                    <div class="title-msg">' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="greentext">' . Message::getHtml() . '</div>

                    </div>
                  </div>';
            $wallet = CONF_CURRENCY . number_format($wallet, 2) . CONF_CURRENCY_RIGHT;
        } else {
            $msg->addError(t_lang('M_TXT_WALLET_NOT_UPDATED'));
            $Usermsg = '<div class="box" id="messages">
                    <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                    <div class="content">
                      <div class="redtext">' . Message::getHtml() . '</div>

                    </div>
                  </div>';
            $wallet = CONF_CURRENCY . number_format($wallet_amount, 2) . CONF_CURRENCY_RIGHT;
        }
        $arr = array('status' => 1, 'msg' => $Usermsg, 'wallet' => $wallet, 'id' => $user_id);
        die(convertToJson($arr));
    }

    function getError()
    {
        return $this->error;
    }

    function getUserWalletAmount($user_id)
    {
        $user_id = intval($user_id);
        if (0 === $user_id) {
            $this->error = t_lang('M_TXT_INVALID_USER');
            return false;
        }
        $srch_userwallet = new SearchBase('tbl_users');
        $srch_userwallet->addCondition('user_id', '=', $user_id);
        $srch_userwallet->addFld('user_wallet_amount');
        $suw_rs = $srch_userwallet->getResultSet();
        if (!($user_wallet_amt = $this->db->fetch($suw_rs))) {
            $this->error = t_lang('M_TXT_USER_WALLET_DETAILS_NOT_FOUND');
            return false;
        }
        return $user_wallet_amt['user_wallet_amount'];
    }

}
