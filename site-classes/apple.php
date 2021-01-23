<?php

class Apple
{

    public function login()
    {
        global $msg;
        $json = [];
        $appleResponse = $_REQUEST;
        if (isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS']) != 'off') {
            $path_url = 'https://';
        } else {
            $path_url = 'http://';
        }
        $redirecUri = $path_url . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'apple-callback.php';
        if (isset($appleResponse['id_token'])) {
            if ($_SESSION['appleSignIn']['state'] != $appleResponse['state']) {
                $message = 'Authorization server returned an invalid state parameter';
                $msg->addError($message);
                redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
            }
            if (isset($_REQUEST['error'])) {
                $message = 'Authorization server returned an error: ' . htmlspecialchars($_REQUEST['error']);
                $msg->addError($message);
                redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
            }
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
                    $msg->addError($message);
                    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
                }
                $appleEmailId = $claims['email'];
            } else {
                $appleEmailId = $appleUserInfo['email'];
            }
            $error = '';
            $otherUrl = friendlyUrl(CONF_WEBROOT_URL . 'home.php');
            if (isset($_SESSION['login_other_page'])) {
                $otherUrl = $_SESSION['login_other_page'];
                unset($_SESSION['login_other_page']);
            }
            if (isset($_SESSION['login_page'])) {
                $cart = new Cart();
                if ($cart->isEmpty() == false) {
                    $url = $_SESSION['login_page'];
                    unset($_SESSION['login_page']);
                    $otherUrl = $url;
                }
            }
            $user = array('user_apple_id' => $userAppleId, 'user_apple_email' => $appleEmailId);
            if ($this->saveUserData($user, $error)) {
                /* echo 'Logged in as ' . $user->email; die; */
                redirectUser($otherUrl);
            }
        }
        /* for first time to redirect to apple login page */
        $_SESSION['appleSignIn']['state'] = bin2hex(random_bytes(5));
        $url = 'https://appleid.apple.com/auth/authorize?' . http_build_query([
                    'response_type' => 'code id_token',
                    'response_mode' => 'form_post',
                    'client_id' => CONF_APPLE_SERVICE_KEY,
                    'redirect_uri' => $redirecUri,
                    'state' => $_SESSION['appleSignIn']['state'],
                    'scope' => 'name email',
        ]);
        return $url;
    }

    function saveUserData($user, &$error)
    {
        global $db;
        global $msg;
        $user_city = intval($_SESSION['city']);
        $city_to_show = '';
        if ($_SESSION['lang_fld_prefix'] == '_lang1')
            $city_to_show = ',city_name_lang1';
        $query = "select * from tbl_users where user_email='" . $user['user_apple_email'] . "' OR apple_user_id = '" . $user['user_apple_id'] . "'";
        $rs = $db->query($query);
        $user_db = $db->fetch($rs);
        $password = '';
        $do_login = false;
        if (!$user_db) {
            $exp = explode("@", $user['user_apple_email']);
            $name = substr($exp[0], 0, 80);
            $password = md5(genRandomString());
            $record = new TableRecord('tbl_users');
            $record->setFldValue('apple_user_id', $user['user_apple_id']);
            $record->setFldValue('user_name', $name);
            //$record->setFldValue('user_lname', $name);
            $record->setFldValue('user_email', $user['user_apple_email']);
            $record->setFldValue('user_password', $password);
            $record->setFldValue('user_regdate', 'mysql_func_now()', true);
            $record->setFldValue('user_city', $user_city);
            $record->setFldValue('user_active', 1);
            $record->setFldValue('user_email_verified', 1);
            $record->setFldValue('user_timezone', CONF_TIMEZONE);
            $user_code = mt_rand();
            $record->setFldValue('reg_code', $user_code, '');
            if (isset($_COOKIE['affid'])) {
                $record->setFldValue('user_affiliate_id', $_COOKIE['affid'] + 0);
            }
            if ($record->addNew()) {
                $do_login = true;
            } else {
                $error = 'Login failed!';
                return false;
            }
        } else {
            /*             * **
              If user is deleted by Admin then need to display a message and do not allow user to login into the system
             */
            if (1 == $user_db['user_deleted']) {
                $error = t_lang('M_TXT_PLEASE_CONTACT_ADMINISTRATOR');
                return false;
            }
            $user['user_apple_email'] = $user_db['user_email'];
            $password = $user_db['user_password'];
            if ($user_db['apple_user_id'] <= 0) {
                $db->query("UPDATE tbl_users set apple_user_id='" . $user['apple_user_id'] . "' where user_id=" . $user_db['user_id']);
            }
            if ($user_db['user_email'] == "") {
                $db->query("UPDATE tbl_users set user_email='" . $user['user_apple_email'] . "' where user_id=" . $user_db['user_id']);
            }
            if ($user_db['user_name'] == "") {
                $db->query("UPDATE tbl_users set user_name='" . $user['first_name'] . "' where user_id=" . $user_db['user_id']);
            }
            $do_login = true;
        }
        if ($do_login) {
            selectCity(intval($user_city));
            if (loginUser($user['user_apple_email'], $password, $error)) {
                return true;
            }
        }
        $error = 'Login With Apple failed!';
        return false;
    }

}
