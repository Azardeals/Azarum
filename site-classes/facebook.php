<?php
require_once realpath(__DIR__ . '/../facebook-php-sdk/autoload.php');

class Facebook {
    protected $fb;

    public function __construct() {
        $this->fb = new Facebook\Facebook([
            'app_id' => CONF_FACEBOOK_API_KEY,
            'app_secret' => CONF_FACEBOOK_SECRET_KEY,
            'default_graph_version' => 'v2.5',
        ]);
    }

    function index() {
        $helper = $this->fb->getRedirectLoginHelper();
        
        if(isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') {
			$path_url = 'https://';
		} else {
			$path_url = 'http://';
		}
        
        $permissions = ['email']; // optional
        
        $loginUrl = $helper->getLoginUrl($path_url . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'fb-callback.php', $permissions);
		
        return $loginUrl;
    }

    function callback() {
        global $db;
        global $msg;
        $helper = $this->fb->getRedirectLoginHelper();
        try {
            //$accessToken = $helper->getAccessToken();
            
            if(isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') {
                $path_url = 'https://';
            } else {
                $path_url = 'http://';
            }
            
            $accessToken = $helper->getAccessToken($path_url . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'fb-callback.php');
            
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $msg->addError($e->getMessage());
            redirectUser('home.php');
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            $msg->addError($e->getMessage());
            redirectUser('home.php');
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        if (isset($accessToken)) {
            $this->getInfo($accessToken);
        }
    }
	
	private function setTokenInSession($accessToken){
		$_SESSION['facebook_access_token'] = $accessToken;
	}
	
	public function getFBUserInfo($accessToken, &$rmsg){
		$accessToken = (string) $accessToken;
		if(strlen($accessToken) < 2){
			return false;
		}
		$this->setTokenInSession($accessToken);
		
		$this->fb->setDefaultAccessToken($accessToken);
		try {
			$response = $this->fb->get('/me?fields=name,email,first_name,last_name,gender');
			$user = $response->getDecodedBody();
            
			return ((object)$user);
		} catch (Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$rmsg = $e->getMessage();
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$rmsg = $e->getMessage();
		}
		return false;
	}

    function getInfo($accessToken) {
        global $db;
        global $msg;
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
        
        $error = '';
		$user = $this->getFBUserInfo($accessToken, $error);
        
        if($user === false){
			$msg->addError($error);
			redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
		}
		$error = '';
		if($this->saveUserData($user, $error)){
			/* echo 'Logged in as ' . $user->email; die; */
            redirectUser($otherUrl);
		}
        $msg->addError($error);
		redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
    }

    function saveUserData($user, &$error) {
        global $db;
        global $msg;
        $user_city = intval($_SESSION['city']);
        $city_to_show = '';
        if ($_SESSION['lang_fld_prefix'] == '_lang1')
            $city_to_show = ',city_name_lang1';
        $query = "select * from tbl_users where user_email='" . $user->email . "' OR fb_user_id = " . $user->id;
        $rs = $db->query($query);
        $user_db = $db->fetch($rs);
		$password = '';
		$do_login = false;
        if (!$user_db) {
            $password = md5(genRandomString());
            $record = new TableRecord('tbl_users');
            $record->setFldValue('fb_user_id', $user->id);
            $record->setFldValue('user_name', $user->first_name);
            $record->setFldValue('user_lname', $user->last_name);
            $record->setFldValue('user_email', $user->email);
            if ($user->gender == 'male'){
                $record->setFldValue('user_gender', 'M');
			}
            if ($user->gender == 'female'){
                $record->setFldValue('user_gender', 'F');
			}
            $record->setFldValue('user_password', $password);
            $record->setFldValue('user_regdate', 'mysql_func_now()', true);
            $record->setFldValue('user_city', $user_city);
            $record->setFldValue('user_active', 1);
            $record->setFldValue('user_email_verified', 1);
            $record->setFldValue('user_timezone', CONF_TIMEZONE);
            $user_code = mt_rand();
            $record->setFldValue('reg_code', $user_code, '');
            if (isset($_COOKIE['affid'])){
                $record->setFldValue('user_affiliate_id', $_COOKIE['affid'] + 0);
			}

            if($record->addNew()){
				$do_login = true;
			}else{
               $error = 'Login failed!';
               return false; 
            }
        } else {
			
            /****
                If user is deleted by Admin then need to display a message and do not allow user to login into the system
            */
            if(1 == $user_db['user_deleted']){
                $error = t_lang('M_TXT_PLEASE_CONTACT_ADMINISTRATOR');
                return false;
            }
            
            $user->email = $user_db['user_email'];
            
            $password = $user_db['user_password'];
            
            if ($user_db['fb_user_id'] <= 0) {
                $db->query("UPDATE tbl_users set fb_user_id='" . $user->id . "' where user_id=" . $user_db['user_id']);
            }
            
            if ($user_db['user_email'] == "") {
                $db->query("UPDATE tbl_users set user_email='" . $user->email . "' where user_id=" . $user_db['user_id']);
            }
            
            if ($user_db['user_name'] == "") {
                $db->query("UPDATE tbl_users set user_name='" . $user->first_name . "' where user_id=" . $user_db['user_id']);
            }
            
			$do_login = true;
        }
		
		if ($do_login) {
			selectCity(intval($user_city));
			if (loginUser($user->email, $password, $error)) {
				return true;
			}
		}
        
		$error = 'Login With Facebook failed!';
		return false;
    }

}