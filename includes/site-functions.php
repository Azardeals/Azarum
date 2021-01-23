<?php

require_once realpath(dirname(__FILE__) . '/deal_attributes_functions.php');
require_once realpath(dirname(__FILE__) . '/deal_functions.php');

function redirectUser($url = '')
{
    if ($url == '') {
        $url = $_SERVER['REQUEST_URI'];
    }
    header("Location: " . $url);
    exit;
}

function sendMandrillMail($to, $subject, $body, $from = CONF_EMAILS_FROM, $attachments = [])
{
    require_once realpath(dirname(__FILE__) . '/mandrill/Mandrill.php');
    $mandrill = new Mandrill(CONF_MANDRILL_API_KEY);
    $message = array(
        'subject' => $subject,
        'from_email' => CONF_EMAILS_FROM,
        'from_name' => CONF_EMAILS_FROM_NAME,
        'html' => $body,
        'to' => array(array('email' => $to))
    );
    if (!empty($attachments)) {
        $attachment = array('attachments' =>
            array(
                array(
                    'content' => base64_encode(file_get_contents($attachments['file'])),
                    'type' => 'application/octet-stream',
                    'name' => $attachments['filename'],
                )
            )
        );
        $message = array_merge($message, $attachment);
    }
    try {
        $response = $mandrill->messages->send($message);
        foreach ($response as $res) {
            if (($res['status'] != 'sent' && $res['status'] != 'queued') || $res['reject_reason'] != '') {
                if ($res['status'] != 'sent' && $res['status'] != 'queued') {
                    sendMail($to, $subject, $body, '', false);
                }
                $message = "Hello Fatbit Administrator,<br/><br/>";
                $message .= "Current status to send email for " . $subject . " to " . $res['email'] . " is " . $res['status'];
                if ($res['reject_reason'] == 'hard-bounce') {
                    $message .= ", due to following reasons:<br/><br/>";
                    $message .= "1) Recipient email address does not exist.<br/>";
                    $message .= "2) Domain name does not exist. <br/>";
                    $message .= "3) Recipient email server has completely blocked delivery.<br/>";
                } elseif ($res['reject_reason'] == 'soft-bounce') {
                    $message .= ", due to following reasons:<br/><br/>";
                    $message .= "1) Mailbox is full(over quota).<br/>";
                    $message .= "2) Recipient email server is down or offline. <br/>";
                    $message .= "3) Email message is too large. <br/>";
                }
                $message .= "<br/><br/>";
                $message .= "Thanks,<br/><br/>";
                $message .= "FATBIT Team";
                $sub = 'Mail could not sent via mandrill mail on bitfat.com';
                sendMail(CONF_ADMIN_EMAIL_ID, $sub, $message, '', false);
                // mail(CONF_ADMIN_EMAIL_ID, $sub, $message);
            }
        }
        return true;
    } catch (Mandrill_Error $e) {
        // Mandrill errors are thrown as exceptions
        echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
        // A mandrill error occurred: Mandrill_Invalid_Key - Invalid API key
        throw $e;
    }
}

function sendMail($to, $subject, $body, $extra_headers = '', $extraParam = true)
{
    //$extraParam is used for sendMandrillMail
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: ' . CONF_EMAILS_FROM . "\r\n";
    if ($extra_headers != '') {
        $headers .= $extra_headers;
    }
    /** emailarchive_tpl_name is subject in Yodeal just because 'sendMail' not getting the template name from anywhere  * */
    $archive = array(
        'emailarchive_to_email' => $to,
        'emailarchive_tpl_name' => $subject,
        'emailarchive_subject' => $subject,
        'emailarchive_body' => $body,
        'emailarchive_headers' => $headers
    );
    $record = new TableRecord('tbl_email_archives');
    $record->assignValues($archive);
    $record->setFldValue('emailarchive_sent_on', date('Y-m-d H:i:s'), false);
    $success = $record->addNew();
    if (!$success) {
        return false;
    }
    //if setting is set to not send email
    if (CONF_SEND_EMAIL == 0) {
        return true;
    }
    if ($extraParam && CONF_EMAIL_SENDING_METHOD == 3) {
        if (sendMandrillMail($to, $subject, $body)) {
            //echo "Mail to " . $to . "<br/>" . $body . "<br/><hr>";
            return true;
        }
    } else {
        if ((CONF_EMAIL_SENDING_METHOD == 2 || $extraParam === false) && strlen(CONF_SMTP_HOST) > 0 && strlen(CONF_SMTP_USERNAME) > 0 && strlen(CONF_SMTP_PASSWORD) > 0) {
            /* SMTP mail */
            require_once realpath(dirname(__FILE__) . '/fat.smtp.mailer.php');
            $error = '';
            $sent = sendSmtpMail($to, $subject, $body, array(
                'sender' => CONF_EMAILS_FROM_NAME,
                'from' => CONF_EMAILS_FROM,
                'use_ssl' => CONF_SMTP_USE_SSL,
                'host' => CONF_SMTP_HOST,
                'port' => CONF_SMTP_PORT,
                'username' => CONF_SMTP_USERNAME,
                'password' => CONF_SMTP_PASSWORD,
                    ), $error
            );
            if (!$sent) {
                echo $error;
                return false;
            } else {
                return true;
            }
        } else {
            /* Simple mail */
            if (mail($to, $subject, $body, $headers)) {
                return true;
            }
        }
    }
    return false;
}

function checkPearMailExt()
{
    /* disabled this check with phpmailer integration */
    return true;
    $path_arr = explode(":", ini_get('include_path'));
    foreach ($path_arr as $path) {
        if ($path == ".")
            continue;
        if (file_exists($path . '/Mail.php'))
            return true;
    }
    return false;
}

function getCalToSQLDate($date)
{
    /* function workes to convert calender date format='dd-mm-yyyy' to Sql date format yyyy-mm-dd */
    if (strlen(trim($date)) != 10)
        return '0000-00-00';
    $temp_arr = explode('-', $date);
    return $temp_arr[2] . '-' . $temp_arr[1] . '-' . $temp_arr[0];
}

function loginAdministrator($username, $password)
{
    global $db;
    $rs = $db->query("select * from tbl_admin where admin_username = " . $db->quoteVariable($username));
    $row = $db->fetch($rs);
    if ($row['admin_username'] != $username || $row['admin_password'] != $password)
        return false;
    unset($row['admin_password']);
    setAdminLoginSession($row);
    return true;
}

function setAdminLoginSession($data)
{
    $_SESSION['admin_logged'] = $data;
}

function loginAdministratorById($id, $password)
{
    global $db;
    $id = intval($id);
    if ($id < 1) {
        return false;
    }
    $rs = $db->query("select * from `tbl_admin` where `admin_id` = " . $id);
    $row = $db->fetch($rs);
    if ($row['admin_password'] !== $password) {
        return false;
    }
    unset($row['admin_password']);
    setAdminLoginSession($row);
    return true;
}

function checkAdminPermission($permssion_id, $return_value = false)
{
    checkAdminSession();
    if (!is_numeric($permssion_id))
        return false;
    if ($_SESSION['admin_logged']['admin_id'] == 1)
        return true; // This is the super user
    global $db;
    $rs = $db->query("select * from tbl_admin_permissions where ap_admin_id = " . $_SESSION['admin_logged']['admin_id'] . " and ap_permission_id = " . $permssion_id);
    if ($row = $db->fetch($rs))
        return true;
    if ($return_value)
        return false;
    die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
}

function checkAdminAddEditDeletePermission($permssion_id, $return_value = false, $permission)
{
    checkAdminSession();
    if ($_SESSION['admin_logged']['admin_id'] == 1)
        return true; // This is the super user
    if (!is_numeric($permssion_id))
        return false;
    global $db;
    if ($permission == 'add') {
        $rs = $db->query("select * from tbl_admin_permissions where ap_permission_add = 1 and ap_admin_id = " . $_SESSION['admin_logged']['admin_id'] . " and ap_permission_id = " . $permssion_id);
    } else if ($permission == 'edit') {
        $rs = $db->query("select * from tbl_admin_permissions where ap_permission_edit = 1 and ap_admin_id = " . $_SESSION['admin_logged']['admin_id'] . " and ap_permission_id = " . $permssion_id);
    } else if ($permission == 'delete') {
        $rs = $db->query("select * from tbl_admin_permissions where ap_permission_delete = 1 and ap_admin_id = " . $_SESSION['admin_logged']['admin_id'] . " and ap_permission_id = " . $permssion_id);
    } else {
        return false;
    }
    if ($row = $db->fetch($rs))
        return true;
    if ($return_value)
        return false;
    //die('Unauthorized Access.');
}

function checkAdminSession($redirect = true)
{
    //if($_SESSION['admin_logged']===1) return true;
    if (is_array($_SESSION['admin_logged']))
        return true;
    if ($redirect) {
        session_destroy();
        if (substr($_SERVER['SCRIPT_NAME'], -9) == '-ajax.php')
            die(t_lang('M_TXT_SESSION_EXPIRES')); //parsejsondata js function handles this string.
        redirectUser('login.php');
    }
    return false;
}

function loginUser($username, $password, & $error)
{
    global $db;
    $srch = new SearchBase('tbl_users');
    $srch->addCondition('user_email', '=', $username);
    $srch->addCondition('user_password', '=', $password);
    $rs = $srch->getResultSet();
    if ($db->total_records($rs) == 0) {
        $error = t_lang('M_TXT_INVALID_EMAIL_PASSWORD');
        return false;
    }
    $row = $db->fetch($rs);
    if (!strtoupper($row['user_email']) == strtoupper($username) && strtoupper($row['user_password']) == strtoupper($password)) {
        $error = t_lang('M_MEG_INVALID_USERNAME_PASSWORD_CASE_SENSITIVE');
        return false;
    }
    if ($row['user_email_verified'] != 1) {
        $jsFunction = 'verifyUserEmail("' . $row['user_name'] . '","' . $row['user_email'] . '","' . $row['user_member_id'] . '",' . $row['reg_code'] . ',' . $row['user_city'] . ');';
        $error = t_lang('M_TXT_VERIFICATION_PENDING') . ' <br/><a class="send_verification_email" href="javascript:void(0);" onclick=' . $jsFunction . ' >' . t_lang('M_TXT_SEND_VERIFICATION_EMAIL') . '</a>';
        return false;
    }
    if ($row['user_active'] != 1) {
        $error = t_lang('M_TXT_ACCOUNT_NOT_ACTIVE');
        return false;
    }
    if ($row['user_deleted'] != 0) {
        $error = t_lang('M_TXT_ACCOUNT_DELETED');
        return false;
    }
    unset($row['password']);
    $_SESSION['logged_user'] = $row;
    return true;
}

function isUserLogged()
{
    if (isset($_SESSION['logged_user']['user_id']) && $_SESSION['logged_user']['user_id'] > 0) {
        global $db;
        $srch = new SearchBase('tbl_users');
        $srch->addCondition('user_id', '=', $_SESSION['logged_user']['user_id']);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (($row['user_email_verified'] != 1) || ($row['user_active'] != 1) || ($row['user_deleted'] != 0)) {
            return false;
        }
        return true;
    }
    if (isset($_COOKIE['u']) && isset($_COOKIE['p'])) {
        $cookie = stripMagicSlashes($_COOKIE);
        $user = $cookie['u'];
        $pass = $cookie['p'];
        global $db;
        $rs = $db->query("select user_password from tbl_users where user_email=" . $db->quoteVariable($user));
        if ($row = $db->fetch($rs)) {
            if (crypt($row['user_password'], 'JAO8maIFyojQvvDbrnHP9iEHQAhK4Cjl7mr33CYxf2CVo6JpNxhPwrR07zYiMW1E') === $pass) {
                $error = '';
                if (loginUser($user, $row['user_password'], $error))
                    return true;
            }
        }
    }
    return false;
}

function loginAffiliateUser($username, $password, & $error)
{
    global $db;
    $srch = new SearchBase('tbl_affiliate');
    $srch->addCondition('affiliate_email_address', '=', $username);
    $srch->addCondition('affiliate_password', '=', $password);
    $rs = $srch->getResultSet();
    if ($db->total_records($rs) == 0) {
        $error = t_lang('M_TXT_INVALID_EMAIL_PASSWORD');
        return false;
    }
    $row = $db->fetch($rs);
    if (!(strtolower($row['affiliate_email_address']) == strtolower($username) && $row['affiliate_password'] == $password)) {
        $error = t_lang('M_MEG_INVALID_USERNAME_PASSWORD_CASE_SENSITIVE'); #$error='Invalid username or password. Please note that the password is case sensitive.';
        return false;
    }
    if ($row['affiliate_status'] != 1) {
        $error = t_lang('M_TXT_ACCOUNT_NOT_ACTIVE');
        return false;
    }
    unset($row['password']);
    $_SESSION['logged_user'] = $row;
    return true;
}

function isAffiliateUserLogged()
{
    if (isset($_SESSION['logged_user']['affiliate_id']) && ($_SESSION['logged_user']['affiliate_id'] > 0)) {
        global $db;
        $srch = new SearchBase('tbl_affiliate');
        $srch->addCondition('affiliate_id', '=', $_SESSION['logged_user']['affiliate_id']);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if ($row['affiliate_status'] != 1) {
            return false;
        }
        return true;
    }
    if (isset($_COOKIE['au']) && isset($_COOKIE['ap'])) {
        $cookie = stripMagicSlashes($_COOKIE);
        $user = $cookie['au'];
        $pass = $cookie['ap'];
        global $db;
        $rs = $db->query("select affiliate_password from tbl_affiliate where affiliate_email_address=" . $db->quoteVariable($user));
        if ($row = $db->fetch($rs)) {
            if (crypt($row['affiliate_password'], 'JAO8maIFyojQvvDbrnHP9iEHQAhK4Cjl7mr33CYxf2CVo6JpNxhPwrR07zYiMW1E') === $pass) {
                $error = '';
                if (loginAffiliateUser($user, $row['affiliate_password'], $error))
                    return true;
            }
        }
    }
    return false;
}

function loginCompanyUser($username, $password, & $error)
{
    global $db;
    $srch = new SearchBase('tbl_companies');
    $srch->addCondition('company_email', '=', $username);
    $srch->addCondition('company_password', '=', $password);
    $rs = $srch->getResultSet();
    if ($db->total_records($rs) == 0) {
        $error = t_lang('M_TXT_INVALID_EMAIL_PASSWORD');
        return false;
    }
    $row = $db->fetch($rs);
    if (!($row['company_email'] == $username && $row['company_password'] == $password)) {
        $error = t_lang('M_MEG_INVALID_USERNAME_PASSWORD_CASE_SENSITIVE'); #$error='Invalid username or password. Please note that both are case sensitive.';
        return false;
    }
    if ($row['company_active'] != 1) {
        $error = t_lang('M_TXT_ACCOUNT_NOT_ACTIVE');
        return false;
    }
    if ($row['company_deleted'] != 0) {
        $error = t_lang('M_TXT_ACCOUNT_DELETED');
        return false;
    }
    unset($row['company_password']);
    $_SESSION['logged_user'] = $row;
    return true;
}

function isCompanyUserLogged()
{
    if (isset($_SESSION['logged_user']['company_id']) > 0) {
        global $db;
        $srch = new SearchBase('tbl_companies');
        $srch->addCondition('company_id', '=', $_SESSION['logged_user']['company_id']);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (($row['company_active'] != 1) || ($row['company_deleted'] != 0)) {
            return false;
        }
        return true;
    }
    if (isset($_COOKIE['mu']) && isset($_COOKIE['mp'])) {
        $cookie = stripMagicSlashes($_COOKIE);
        $user = $cookie['mu'];
        $pass = $cookie['mp'];
        global $db;
        $rs = $db->query("select company_password from tbl_companies where company_email=" . $db->quoteVariable($user));
        if ($row = $db->fetch($rs)) {
            if (crypt($row['company_password'], 'JAO8maIFyojQvvDbrnHP9iEHQAhK4Cjl7mr33CYxf2CVo6JpNxhPwrR07zYiMW1E') === $pass) {
                $error = '';
                if (loginCompanyUser($user, $row['company_password'], $error))
                    return true;
            }
        }
    }
    return false;
}

function setMerchantLoginSession($data)
{
    $_SESSION['logged_user'] = $data;
}

function loginMerchantById($id, $password)
{
    global $db;
    $id = intval($id);
    if ($id < 1) {
        return false;
    }
    $rs = $db->query("select * from `tbl_companies` where `company_id` = " . $id);
    $row = $db->fetch($rs);
    if ($row['company_password'] !== $password) {
        return false;
    }
    unset($row['company_password']);
    setMerchantLoginSession($row);
    return true;
}

function loginRepresentativeUser($username, $password, & $error)
{
    global $db;
    $srch = new SearchBase('tbl_representative');
    $srch->addCondition('rep_email_address', '=', $username);
    $srch->addCondition('rep_password', '=', $password);
    $rs = $srch->getResultSet();
    if ($db->total_records($rs) == 0) {
        $error = t_lang('M_TXT_INVALID_EMAIL_PASSWORD');
        return false;
    }
    $row = $db->fetch($rs);
    if (!($row['rep_email_address'] == $username && $row['rep_password'] == $password)) {
        $error = t_lang('M_MEG_INVALID_USERNAME_PASSWORD_CASE_SENSITIVE'); #$error='Invalid username or password. Please note that both are case sensitive.';
        return false;
    }
    if ($row['rep_status'] != 1) {
        $error = t_lang('M_TXT_ACCOUNT_NOT_ACTIVE');
        return false;
    }
    if ($row['rep_deleted'] != 0) {
        $error = t_lang('M_TXT_ACCOUNT_DELETED');
        return false;
    }
    unset($row['rep_password']);
    $_SESSION['logged_user'] = $row;
    return true;
}

function isRepresentativeUserLogged()
{
    global $db;
    if ($_SESSION['logged_user']['rep_id'] > 0) {
        $srch = new SearchBase('tbl_representative');
        $srch->addCondition('rep_id', '=', $_SESSION['logged_user']['rep_id']);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (($row['rep_status'] != 1) || ($row['rep_deleted'] != 0)) {
            return false;
        }
        return true;
    }
    if (isset($_COOKIE['ru']) && isset($_COOKIE['rp'])) {
        $cookie = stripMagicSlashes($_COOKIE);
        $user = $cookie['ru'];
        $pass = $cookie['rp'];
        global $db;
        $rs = $db->query("select rep_password from tbl_representative where rep_email_address=" . $db->quoteVariable($user));
        if ($row = $db->fetch($rs)) {
            if (crypt($row['rep_password'], 'JAO8maIFyojQvvDbrnHP9iEHQAhK4Cjl7mr33CYxf2CVo6JpNxhPwrR07zYiMW1E') === $pass) {
                $error = '';
                if (loginRepresentativeUser($user, $row['rep_password'], $error))
                    return true;
            }
        }
    }
    return false;
}

function setRepresentativeLoginSession($data)
{
    $_SESSION['logged_user'] = $data;
}

function loginRepresentativeById($id, $password)
{
    global $db;
    $id = intval($id);
    if ($id < 1) {
        return false;
    }
    $rs = $db->query("select * from `tbl_representative` where `rep_id` = " . $id);
    $row = $db->fetch($rs);
    if ($row['rep_password'] !== $password) {
        return false;
    }
    unset($row['rep_password']);
    setRepresentativeLoginSession($row);
    return true;
}

function getDealSaleProgress($sold, $min)
{
    return ($min - $sold) . ' more needed to get the deal.';
}

function linkURLS($text)
{
    //$text = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $text);
    $expr = '/(ftp|http):\/\/([_a-z\d\-]+(\.[_a-z\d\-]+)+)(([_a-z\d\-\\\.\/]+[_a-z\d\-\\\/])+)/';
    $text = preg_quote($text);
    $text = str_replace('/', '\/', $text);
    $text = preg_replace($expr, "<a href=\"\\0\" target=\"_blank\">\\0</a>", $text);
    return $text;
}

/**
 * Generates Navigation code
 *
 * @param number $nav_id Id of navigation whose nav code is needed
 * @param number $parent_id ID of parent fo the nav Id
 * @return string code of the navigation
 */
function getNavCode($nav_id, $parent_id)
{
    global $db;
    $code = str_pad($nav_id, 5, '0', STR_PAD_LEFT);
    // prepend code of parent naviation
    if ($parent_id > 0) {
        $rs = $db->query("select nl_code from tbl_nav_links where nl_id=" . $parent_id);
        if ($row = $db->fetch($rs)) {
            $code = $row['nl_code'] . $code;
        }
    }
    // prepend code of parent naviation ends
    return $code;
}

/**
 * Generates Category code
 *
 * @param number $category_id Id of Category whose nav code is needed
 * @param number $parent_id ID of parent fo the nav Id
 * @return string code of the Category
 */
function getCategoryCode($category_id, $parent_id)
{
    global $db;
    $code = str_pad($category_id, 5, '0', STR_PAD_LEFT);
    // prepend code of parent category
    if ($parent_id > 0) {
        $rs = $db->query("select category_code from tbl_cms_faq_categories where category_id=" . $parent_id);
        if ($row = $db->fetch($rs)) {
            $code = $row['category_code'] . $code;
        }
    }
    // prepend code of parent category ends
    return $code;
}

/**
 * Generates Category code
 *
 * @param number $cat_id Id of category whose cat code is needed
 * @param number $cat_parent_id ID of parent fo the cat Id
 * @return string code of the category
 */
function getDealCategoryCode($cat_id, $cat_parent_id)
{
    global $db;
    $code = str_pad($cat_id, 5, '0', STR_PAD_LEFT);
    // prepend code of parent naviation
    if ($cat_parent_id > 0) {
        $rs = $db->query("select cat_code from tbl_deal_categories where cat_id=" . $cat_parent_id);
        if ($row = $db->fetch($rs)) {
            $code = $row['cat_code'] . $code;
        }
    }
    // prepend code of parent naviation ends
    return $code;
}

function checkImageTypes($t)
{
    switch ($t) {
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
        case "image/pjpg":
        case "image/png":
        case "image/x-png":
        case "image/gif":
        case "image/giff":
            // case "application/pdf";
            return true;
        default:
            return false;
    }
}

function checkImageFavIconType($t)
{
    switch ($t) {
        case "image/x-icon":
		case "image/png":
        case "image/vnd.microsoft.icon":
            return true;
        default:
            return false;
    }
}

function getAdminBreadCrumb($arr)
{
    $str = '<div class="breadcrumb">
                    <ul>';
    $total = count($arr);
    $count = 0;
    foreach ($arr as $key => $val) {
        $count++;
        if ($count == 1) {
            $class = 'class="home"';
        } else {
            $class = '';
        }
        $str .= '<li>';
        if ($key != '')
            $str .= '<a href="' . $key . '" ' . $class . '>';
        $str .= $val;
        if ($key != '')
            $str .= '</a>';
        $str .= '</li>';
    }
    $str .= '</ul><div class="gap"></div>
                  </div>';
    return $str;
}

function getMerchantBreadCrumb($arr)
{
    $str = '<div class="breadcrumb"><ul>';
    $str .= '<li><a href="merchant-account.php" class="home"><img alt="Home" src="images/home-icon.png"></a></li>';
    $total = count($arr);
    $count = 0;
    $class = '';
    foreach ($arr as $key => $val) {
        $str .= '<li>';
        if ($key != '') {
            $str .= '<a href="' . $key . '" ' . $class . '>';
        }
        $str .= $val;
        if ($key != '') {
            $str .= '</a>';
        }
    }
    $str .= '</li>';
    $str .= '</ul><div class="gap"></div>
                  </div>';
    return $str;
}

function post($name, $else = "")
{
    return str_replace("'", "''", (isset($_POST[$name]) ? $_POST[$name] : $else));
}

//function is used to download the file
function dl_file($file)
{
    $main = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
    //First, see if the file exists
    if (!is_file($main . "/" . $file)) {
        die("<b>404 File not found!</b>");
    }
    //Gather relevent info about file
    $len = filesize($main . "/" . $file);
    $filename = basename($main . "/" . $file);
    // $ctype="application/octet-stream"; break;
    $ctype = "application/force-download";
    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    //Use the switch-generated Content-Type
    header("Content-Type: $ctype");
    //Force the download
    $header = "Content-Disposition: attachment; filename=" . $filename . ";";
    header($header);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . $len);
    @readfile($file);
    die();
}

function dl_file_p($file)
{
    $download_dir = CONF_DB_BACKUP_DIRECTORY_FULL_PATH; // the folder where the files are stored ('.' if this script is in the same folder)
    $path = $download_dir . "/" . $file;
    if (file_exists($path)) {
        $filename = $download_dir . "/" . $file;
        header('Content-Description: File Transfer');
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
        header('Content-Length: ' . filesize($filename));
        readfile("$filename");
    } else {
        echo "<center>The file [$file] is not available for download.</center>";
    }
}

function getMySQLVariable($varname, $scope = "session")
{
    global $db;
    $gv = $db->query("show $scope variables");
    $counter = 0;
    $val = false;
    while ($grow = $db->fetch($gv)) {
        if ($grow[0] == $varname) {
            $val = $grow[1];
            break;
        }
    }
    return $val;
}

function restoreDatabase($backupFile)
{
    $db_server = CONF_DB_SERVER;
    $db_user = CONF_DB_USER;
    $db_password = CONF_DB_PASS;
    $db_databasename = CONF_DB_NAME;
    $conf_db_path = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
    $varbsedir = getMySQLVariable("basedir");
    if ($varbsedir == "/") {
        $varbsedir = $varbsedir . "usr/";
    } else {
        $varbsedir = $varbsedir;
    }
    $backupFile = $conf_db_path . "/" . $backupFile;
    $data_str = $varbsedir . 'bin\mysqlimport -u ' . $db_user . ' -p\'' . $db_password . '\' ' . $db_databasename . ' > ' . $backupFile;
    $restore_backup = system($data_str);
    if ($restore_backup) {
        return true;
    }
    return false;
}

function backupDatabase($name, $attachtime = true, $download = false)
{
    $db_server = CONF_DB_SERVER;
    $db_user = CONF_DB_USER;
    $db_password = CONF_DB_PASS;
    $db_databasename = CONF_DB_NAME;
    $conf_db_path = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
    if ($attachtime) {
        $backupFile = $conf_db_path . "/" . $name . "_" . date("Y-m-d-H-i-s") . '.sql';
        $fileToDownload = $name . "_" . date("Y-m-d-H-i-s") . '.sql';
    } else {
        $backupFile = $conf_db_path . "/" . $name . '.sql';
        $fileToDownload = $name . '.sql';
    }
    $data_str = 'mysqldump --opt -h ' . $db_server . ' -u ' . $db_user . ' -p\'' . $db_password . '\' ' . $db_databasename . ' > ' . $backupFile;
    $create_backup = system($data_str);
    if ($download)
        dl_file_p($fileToDownload);
    return true;
}

/* * ----- sign-up emails -------* */

function signUpEmailTemplate($inner_body, $user_code, $user_email)
{
    return emailTemplate($inner_body);
}

/* ------- */

function emailTemplate($emailMsg)
{
    global $db;
    /*  $rs = $db->query("select * from tbl_configurations");
      while ($row = $db->fetch($rs)) {
      define(strtoupper($row['conf_name']), $row['conf_val']);
      } */
    if (CONF_EMAIL_LOGO == '') {
        $image = '<img style="display:block; margin:0 auto;"  title="logo"  width="200"  alt="" src="http://' . CONF_SERVER_NAME . 'images/logo.png">';
    } else {
        $image = '<img style="display:block; margin:0 auto;"  title="logo"  width="200" alt="" src="http://' . LOGO_URL . CONF_EMAIL_LOGO . '">';
    }
    $common_footer = html_entity_decode(t_lang('M_EMAIL_NEED_HELP')) . '<br/><br/> ' . t_lang('M_TXT_ALL_THE_BEST') . '<br/>' . t_lang('M_TXT_TEAM') . '<br/> <a style="color:#d71732; text-decoration:none;" href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'contact-us/">  ' . t_lang('M_TXT_SEND_US_MESSAGE') . '</a>';
    $message = '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f2f2f2; font-family:Arial; line-height:24px; font-size:16px; color:#a1a1a1;">
    <tr>
        <td style="padding:60px;">
            <table width="600" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                         ' . replace_img_src($emailMsg) . '
                    </td>
                </tr>
		       <!--body footer start here-->
                <tr>
                    <td style="padding:20px 0;font-size:12px; line-height:16px; color:#999;">
                        <table>
                            <tr>
                                <td width="65%" valign="top">
                                    ' . sprintf(unescape_attr(t_lang('M_TXT_FOOTER_TEXT')), CONF_SITE_OWNER_EMAIL) . '<br/><br/>
                                   ' . t_lang('M_EMAIL_COPYRIGHT') . '
                                </td>
                                <td width="35%" align="right" valign="top">
                                    <a href="' . CONF_TWITTER_USER . '" style="margin:0 1px;"><img src="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'images/icon_social_1.png" alt="twitter"></a>
                                    <a href="' . CONF_FACEBOOK_URL . '"  style="margin:0 1px;"><img src="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'images/icon_social_2.png" alt="facebook"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!--body footer end here-->
            </table>
        </td>
    </tr>
</table>';
    $message = str_replace('xxcommonfooterxx', $common_footer, $message);
    $message = str_replace('xxlogoxx', $image, $message);
    return $message;
}

function emailTemplateSuccess($emailMsg)
{
    return emailTemplate($emailMsg);
}

function replace_img_src($img_tag)
{
    $base_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL;
    $doc = new DOMDocument();
    $doc->loadHTML(mb_convert_encoding($img_tag, 'HTML-ENTITIES', 'UTF-8'));
    $tags = $doc->getElementsByTagName('img');
    foreach ($tags as $tag) {
        $old_src = $tag->getAttribute('src');
        $old_src = str_replace($base_url, "", $old_src);
        $old_src = str_replace("/images", "images", $old_src);
        $new_src_url = $base_url . $old_src;
        $tag->setAttribute('src', $new_src_url);
    }
    return $doc->saveHTML();
}

function getCityListing()
{
    global $db;
    $srch = new SearchBase('tbl_cities');
    $srch->addCondition('city_active', '=', 1);
    $srch->addCondition('city_deleted', '=', 0);
    $srch->addCondition('city_request', '=', 0);
    if (is_numeric($_SESSION['city'])) {
        $srch->addCondition('city_id', '!=', $_SESSION['city']);
    }
    $srch->addOrder('city_name');
    $srch->addMultipleFields(array('city_id', 'city_name'));
    $srch->doNotLimitRecords();
    $srch->doNotCalculateRecords();
    $rs = $srch->getResultSet();
    $str .= '<ul>';
    while ($row = $db->fetch($rs)) {
        $str .= '<li><a href="javascript:void(0);" onclick="selectCity(' . $row['city_id'] . ',' . CONF_FRIENDLY_URL . ');">' . $row['city_name'] . '</a></li>';
    }
    $str .= '</ul>';
    return($str);
}

function t_lang($key)
{
    global $arr_lang_vals;
    global $db;
    if (isset($arr_lang_vals[$key])) {
        return $arr_lang_vals[$key];
    }
    /* Get the language which is being used */
    $lang_id = 0;
    if (isset($_SESSION['language'])) {
        $lang_id = $_SESSION['language'];
    } else {
        $lang_id = CONF_DEFAULT_LANGUAGE;
    }
    if (intval($lang_id) == 0) {
        $lang_id = 1;
    }
    $arr_lang_fld = array(1 => 'lang_english', 2 => 'lang_spanish');
    $lang_fld = $arr_lang_fld[$lang_id];
    /* Get the language which is being used ends */
    /* Get the language string */
    $rs = $db->query("SELECT " . $lang_fld . " as lang_val FROM tbl_lang WHERE lang_key = " . $db->quoteVariable($key));
    if ($row = $db->fetch($rs)) {
        $row['lang_val'] = escape_attr($row['lang_val']);
        $arr_lang_vals[$key] = $row['lang_val'];
        return $row['lang_val'];
    }
    /* Handle the case if it was not found in db */
    $arr = explode('_', $key);
    array_shift($arr);
    array_shift($arr);
    $str = ucwords(strtolower(implode(' ', $arr)));
    $db->insert_from_array('tbl_lang', array('lang_key' => $key, 'lang_english' => $str));
    $arr_lang_vals[$key] = $str;
    return ($str);
}

function updateFormLang($frm)
{
    $n = $frm->getFieldCount();
    for ($i = 0; $i < $n; $i++) {
        $fld = $frm->getFieldByNumber($i);
        $cap = $fld->field_caption;
        if (strpos($cap, 'M_') !== false) {
            $fld->field_caption = t_lang($cap);
        }
        $custom_msg = $fld->requirements()->getCustomErrorMessage();
        if (strpos($custom_msg, 'M_') !== false) {
            $fld->requirements()->setCustomErrorMessage(t_lang($custom_msg));
        }
        if (strlen(trim($custom_msg)) < 1) {
            setRequirementFieldCaption($fld);
        }
    }
}

function getValidationErrMsg($frm)
{
    $msgs = [];
    $n = $frm->getFieldCount();
    for ($i = 0; $i < $n; $i++) {
        $fld = $frm->getFieldByNumber($i);
        $err = $fld->getValidationError();
        $custom = $fld->requirements()->getCustomErrorMessage();
        if ($err != '') {
            if (!empty($custom)) {
                if (strpos($custom, 'M_') === false) {
                    $msgs[] = $custom; /* added by Lakhvir */
                } else {
                    $msgs[] = t_lang($custom);
                }
            } else {
                while (strpos($err, 'M_') !== false) /* added by Lakhvir */ {
                    $start_pos = strpos($err, 'M_');
                    if (strpos($err, ' ', $start_pos) !== false) {
                        $end_pos = strpos($err, ' ', $start_pos);
                    } else {
                        $end_pos = strlen($err) - 1;
                    }
                    $lang_str = substr($err, $start_pos, ($end_pos - $start_pos) + 1);
                    $err = str_replace($lang_str, t_lang($lang_str), $err);
                }
                $err = replaceValidationErrMsgLang($err);
                $msgs[] = $err;
            }
        }
    }
    return $msgs;
}

/* functions added by Lakhvir start here */

function selectCity($city)
{
    global $db;
    if (!is_numeric($city))
        return false;
    $city = intval($city);
    $srch = new SearchBase('tbl_cities');
    $srch->addCondition('city_id', '=', $city);
    $srch->addCondition('city_active', '=', 1);
    $srch->addCondition('city_deleted', '=', 0);
    $city_to_show = '';
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $city_to_show = ',city_name_lang1';
    }
    $srch->addMultipleFields(array('city_id', 'city_name' . $city_to_show));
    $rs = $srch->getResultSet();
    if (!$row = $db->fetch($rs)) {
        return false;
    }
    $_SESSION['city'] = $row['city_id'];
    $_SESSION['cityname'] = $row['city_name'];
    $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
    return true;
}

function getFieldCaptionFromTitle(&$fld)
{
    $fld_title = '';
    if (strpos($fld->extra, 'title') !== false) {
        $pt = strpos($fld->extra, '=', strpos($fld->extra, 'title'));
        $i = 0;
        $not_found = false;
        while ($quote = substr($fld->extra, ($pt + $i), 1)) {
            if (in_array(htmlentities($quote), array('&#39;', '&#34;', '&quot;'), true)) {
                break;
            }
            if ($i > 3) {
                $not_found = true;
                break;
            }
            $i++;
        }
        if (!$not_found) {
            $start_pt = $pt + $i + 1;
            $end_pt = strpos($fld->extra, $quote, $start_pt);
            $fld_title = substr($fld->extra, $start_pt, ($end_pt - $start_pt));
        }
    }
    return $fld_title;
}

function setRequirementFieldCaption(&$fld)
{
    if ($fld->field_caption == null || strlen($fld->field_caption) <= 0) {
        $fld->requirements()->fldCaption = getFieldCaptionFromTitle($fld);
    } else {
        $fld->requirements()->fldCaption = $fld->field_caption;
    }
}

function replaceValidationErrMsgLang($err)
{
    $array_old_msgs = array('is mandatory.', 'Please enter valid email ID for', 'not unique');
    $array_replacements = array('M_JS_IS_MANDATORY', 'M_JS_EMAIL_VALIDATION_MESSAGE', 'M_ERROR_ALREADY_EXIST');
    for ($i = 0; $i <= count($array_old_msgs); $i++) {
        $err = str_replace($array_old_msgs[$i], t_lang($array_replacements[$i]), $err);
    }
    return $err;
}

function checkDealSoldForCompanyLoc($deal, $company_loc)
{
    global $db;
    if (intval($deal) <= 0 || intval($company_loc) <= 0) {
        return 0;
    }
    $sold = 0;
    $srch = new SearchBase('tbl_orders', 'o');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'od.od_order_id=o.order_id', 'od');
    $srch->addCondition('od_deal_id', '=', intval($deal));
    $srch->addCondition('od_company_address_id', '=', intval($company_loc));
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $rs = $srch->getResultSet();
    if ($row = $db->fetch($rs)) {
        $sold = $row['sold'];
    }
    if ($sold === null || $sold == '') {
        $sold = 0;
    }
    return $sold;
}

/* functions added by Lakhvir end */

function genRandomString()
{
    $length = 8;
    $characters = '23456789abcdefghijkmnpqrstuvwxyz';
    $string = '';
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}

function genRandomNumber($n)
{
    $length = $n;
    $characters = '23456789';
    $string = '';
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, $n)];
    }
    return $string;
}

function setLangSessionVals()
{
    if (!isset($_SESSION['language']) || !in_array($_SESSION['language'], array(1, 2))) {
        $_SESSION['language'] = CONF_DEFAULT_LANGUAGE;
    }
    if ($_SESSION['language'] == 1) {
        $_SESSION['lang_fld_prefix'] = '';
    }
    if ($_SESSION['language'] == 2) {
        $_SESSION['lang_fld_prefix'] = '_lang1';
    }
}

function checkCity(&$maintenance = false)
{
    global $db;
    if (isset($_SESSION['city']) && isset($_SESSION['cityname']) && isset($_SESSION['city_to_show'])) {
        if (strlen($_SESSION['city_to_show']) <= 1) {
            $_SESSION['city_to_show'] = $_SESSION['cityname'];
        }
        return;
    }
    $script = $_SERVER['SCRIPT_FILENAME'];
    $info = pathinfo($script);
    $str = $_SERVER['REQUEST_URI'];
    if (strpos($str, CONF_WEBROOT_URL) === 0) {
        $str = substr($str, strlen(CONF_WEBROOT_URL));
    }
    $str = ltrim($str, '/');
    $arr = explode('/', $str);
    $city_to_show = '';
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $city_to_show = ', city_name_lang1';
    }
    $rs = $db->query("SELECT city_id, city_name" . $city_to_show . " FROM tbl_cities WHERE city_active = 1 AND city_deleted = 0 and city_request=0");
    if ($db->total_records($rs) == 1) {
        $row = $db->fetch($rs);
        $_SESSION['city'] = $row['city_id'];
        $_SESSION['cityname'] = $row['city_name'];
        $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
        return;
    }
    while ($row = $db->fetch($rs)) {
        if (!is_array($first_city)) {
            $first_city = $row;
        }
        if (strtolower(convertStringToUrlForDirectBrowsing($row['city_name'])) == strtolower($arr[0])) {
            $_SESSION['city'] = $row['city_id'];
            $_SESSION['cityname'] = $row['city_name'];
            $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
            return;
        }
    }
    if (is_numeric($_SESSION['city']) && $_SESSION['city'] > 0) {
        return;
    }
    if (!isset($_SESSION['city']) || !is_numeric($_SESSION['city'])) {
        $dealList = $db->query("select count(*) as total,deal_city from tbl_deals as d inner join tbl_cities as c  where d.deal_city=c.city_id and c.city_active=1 and c.city_deleted=0 and c.city_request=0 and d.deal_status=1 and d.deal_deleted=0 and d.deal_complete=1 group by deal_city order by total desc limit 0,1");
        $dealrow = $db->fetch($dealList);
        if (intval($dealrow['deal_city']) > 0) {
            selectCity(intval($dealrow['deal_city']));
            return;
        } else {
            $row = checkForActiveCity($city_to_show);
            if ($row === false) {
                $maintenance = true;
                return false;
            }
            $_SESSION['cityname'] = $row['city_name'];
            $_SESSION['city'] = $row['city_id'];
            $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
            return;
        }
    }
    if (!is_array($first_city)) {
        die(t_lang('M_TXT_NO_CITIES'));
    }
    $_SESSION['city'] = $first_city['city_id'];
    $_SESSION['cityname'] = $first_city['city_name'];
    $_SESSION['city_to_show'] = $row['city_name' . $_SESSION['lang_fld_prefix']];
    return;
}

function checkForActiveCity(&$city_to_show = '')
{
    global $db;
    $rs = $db->query("select city_id, city_name" . $city_to_show . " from tbl_cities where city_active=1 and city_deleted=0 and city_request=0");
    $row = $db->fetch($rs);
    if ($db->total_records($rs) == 0) {
        return false;
    }
    return $row;
}

function convertStringToUrlForDirectBrowsing($strRecord)
{
    $strRecord = str_replace('.', ' ', $strRecord);
    $strRecord = trim($strRecord);
    $strRecord = strtolower(preg_replace('/ +(?=)/', '-', $strRecord));
    $strRecord = strtolower(preg_replace('/"/', '-', $strRecord));
    $strRecord = preg_replace('/[^A-Za-z0-9_\.-]+/', '', $strRecord);
    $strdisplay = '';
    $myStr_array = explode("-", $strRecord);
    for ($jVal = 0; $jVal < count($myStr_array); $jVal++) {
        if ($jVal < count($myStr_array) - 1) {
            $strdisplay = $strdisplay . $myStr_array[$jVal] . "-";
        } else {
            if (($jVal == count($myStr_array) - 1) && (!is_numeric($myStr_array[$jVal]) == false)) {
                $strdisplay = substr($strdisplay, 0, strlen($strdisplay) - 1);
            }
            $strdisplay = $strdisplay . $myStr_array[$jVal];
        }
    }
    return $strdisplay;
}

function notifyDealCancelation($deal_id, $order_id = null)
{
    global $db;
    if (intval($deal_id) <= 0) {
        return false;
    }
    $srch = new SearchBase('tbl_order_deals', 'od');
    $srch->addCondition('od_deal_id', '=', intval($deal_id));
    if ($order_id != null && strlen($order_id) == 13) {
        $srch->addCondition('od_order_id', '=', intval($order_id));
    }
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'd');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status=1', 'o');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    $srch->addMultipleFields(array('o.order_id', 'od.od_deal_price', 'od.od_deal_tax_amount', 'u.user_id', 'u.user_name', 'u.user_email', 'od.od_to_name', 'od.od_to_email', 'd.deal_name'));
    $srch->addFld('IF(deal_tipped_at, 1, 0) as is_deal_tipped');
    $srch->addFld('(select sum(od_qty+od_gift_qty) from tbl_order_deals odi where odi.od_order_id=od.od_order_id) as total_bought');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $rs = $srch->getResultSet();
    if ($db->total_records($rs) > 0) {
        $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=2");
        $row_tpl = $db->fetch($rs_tpl);
        $count = 0;
        $arr_mail_sent_to = [];
        while ($row = $db->fetch($rs)) {
            $count++;
            $arr_replacements = array(
                'xxuser_namexx' => $row['user_name'],
                'xxdeal_namexx' => $row['deal_name'],
                'xxordered_coupon_qtyxx' => $row['total_bought'],
                'xxorderidxx' => $row['order_id'],
                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                'xxsite_namexx' => CONF_SITE_NAME,
                'xxwebrooturlxx' => CONF_WEBROOT_URL
            );
            if ($row['od_to_email'] != '' && intval($row['is_deal_tipped']) === 1 && $order_id == null && $row_tpl['tpl_status'] == 1) {
                $arr_replacements['xxuser_namexx'] = $row['od_to_name'];
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                sendMail($row['od_to_email'], $subject . ' - ' . time() . $count, emailTemplate($message));
            }
            /* Notify User */
            if (!isset($arr_mail_sent_to[$row['order_id']])) {
                if ($row_tpl['tpl_status'] == 1) {
                    $arr_replacements['xxuser_namexx'] = $row['user_name'];
                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                    foreach ($arr_replacements as $key => $val) {
                        $subject = str_replace($key, $val, $subject);
                        $message = str_replace($key, $val, $message);
                    }
                    sendMail($row['user_email'], $subject . ' - ' . time() . $count, emailTemplate($message), $headers);
                }
                $arr_mail_sent_to[$row['order_id']] = $row['user_email'];
                /* Add amount to user wallet */
                $price = $row['od_deal_price'] + $row['od_deal_tax_amount'];
                if (!$db->query("update tbl_users set user_wallet_amount = user_wallet_amount + " . ($price * intval($row['total_bought'])) . " where user_id=" . $row['user_id'])) {
                    dieJsonError($db->getError());
                }
                /* Add amount to user wallet ends */
                /* mark deal as refund */
                $order_id = $row['order_id'];
                if (!$db->update_from_array('tbl_orders', array('order_payment_status' => 2), "order_id='$order_id'")) {
                    die($db->getError());
                }
                /* mark deal as refund */
                $txt1 = 'M_TXT_DEAL';
                $txt2 = 'M_TXT_CANCELLED';
                $txt3 = 'M_TXT_QTY';
                /* Update User Wallet History */
                if (!$db->insert_from_array('tbl_user_wallet_history', array(
                            'wh_user_id' => $row['user_id'],
                            'wh_untipped_deal_id' => $post['id'],
                            'wh_particulars' => $txt1 . ' ' . $row_deal['deal_name' . $_SESSION['lang_fld_prefix']] . $txt2 . '. ' . $txt3 . ' ' . intval($row['total_bought']) . '@' . $price,
                            'wh_amount' => $price * intval($row['total_bought']),
                            'wh_time' => 'mysql_func_now()'
                                ), true)) {
                    dieJsonError($db->getError());
                }
                /* Update User Wallet History Ends */
            }
        }
    }
    return true;
}

function voucherRefund($order_id)
{
    global $db;
    global $msg;
    $length = strlen($order_id);
    if ($length > 13) {
        $order_no = substr($order_id, 0, 13);
        $LastVouvherNo = ($length - 13);
        $voucher_no = substr($order_id, 13, $LastVouvherNo);
    } else if ($length == 13) {
        $order_no = $order_id;
    } else {
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
        return false;
    }
    $srch = new SearchBase('tbl_order_deals', 'od');
    $srch->addCondition('od_order_id', '=', $order_no);
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=c.company_id', 'c');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status < 2', 'o');
    $q_patch = '';
    if ($length == 13) {
        $q_patch = ' AND (od_voucher_suffixes like cm_counpon_no OR od_voucher_suffixes like concat("%, ",cm_counpon_no) OR od_voucher_suffixes like concat(cm_counpon_no,",%") OR od_voucher_suffixes like concat("%, ",cm_counpon_no,",%"))';
    }
    $srch->joinTable('tbl_coupon_mark', 'INNER JOIN', 'cm.cm_order_id =o.order_id' . $q_patch, 'cm');
    if ($length > 13) {
        $srch->addCondition('cm.cm_counpon_no', '=', intval($voucher_no));
        $srch->addDirectCondition("(od_voucher_suffixes like '" . intval($voucher_no) . "' OR od_voucher_suffixes like '%, " . intval($voucher_no) . "' OR od_voucher_suffixes like '" . intval($voucher_no) . ",%' OR od_voucher_suffixes like '%, " . intval($voucher_no) . ",%')");
    }
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    $srch->addMultipleFields(array('d.deal_min_coupons', 'd.deal_tipped_at', 'd.deal_id', 'd.deal_name' . $_SESSION['lang_fld_prefix'], 'd.deal_status', 'od.od_to_email', 'od.od_to_name', 'od.od_email_msg', 'u.user_name', 'o.order_id', 'o.order_date', 'od_deal_price', 'od_deal_tax_amount', 'od_qty', 'od_gift_qty', 'od_voucher_suffixes', 'od_cancelled_voucher_suffixes', 'u.user_id', 'u.user_email', 'u.user_name', 'c.company_name', 'c.company_email', 'cm.cm_counpon_no', 'cm.cm_status', 'od_deal_charity_id', 'd.deal_charity_discount_is_percent', 'd.deal_charity_discount', 'd.deal_type', 'd.deal_sub_type', 'od_id'));
    //$srch->getQuery();
    $rs = $srch->getResultSet();
    $user_id = 0;
    $deal_id = 0;
    $deal_name = '';
    $price = 0;
    $total_qty = 0;
    $amount = 0;
    $set_values = [];
    while ($row_deal = $db->fetch($rs)) {
        if ($row_deal['cm_status'] == 1 || $row_deal['cm_status'] == 2 || $row_deal['cm_status'] == 3 || ($row_deal['deal_type'] == 1 && $row_deal['deal_sub_type'] == 1)) {
            continue;
        }
        if (!isset($set_values[$row_deal['od_id']])) {
            $set_values[$row_deal['od_id']]['od_voucher_suffixes'] = $row_deal['od_voucher_suffixes'];
            $set_values[$row_deal['od_id']]['od_cancelled_voucher_suffixes'] = $row_deal['od_cancelled_voucher_suffixes'];
            $set_values[$row_deal['od_id']]['od_qty'] = $row_deal['od_qty'];
            $set_values[$row_deal['od_id']]['od_gift_qty'] = $row_deal['od_gift_qty'];
        }
        $voucher_suffixes = explode(', ', $set_values[$row_deal['od_id']]['od_voucher_suffixes']);
        $voucher_suffixes = array_map('trim', $voucher_suffixes);
        unset($voucher_suffixes[array_search($row_deal['cm_counpon_no'], $voucher_suffixes, true)]);
        $vouchers_left = implode(', ', $voucher_suffixes);
        $set_values[$row_deal['od_id']]['od_voucher_suffixes'] = $vouchers_left;
        if (strlen($row_deal['od_cancelled_voucher_suffixes']) > 1) {
            $cancelled_suffixes = explode(', ', $set_values[$row_deal['od_id']]['od_cancelled_voucher_suffixes']);
            $cancelled_suffixes = array_map('trim', $cancelled_suffixes);
            $cancelled_suffixes[] = $row_deal['cm_counpon_no'];
            $set_values[$row_deal['od_id']]['od_cancelled_voucher_suffixes'] = implode(', ', $cancelled_suffixes);
        } else {
            $set_values[$row_deal['od_id']]['od_cancelled_voucher_suffixes'] = $row_deal['cm_counpon_no'];
        }
        if (intval($row_deal['cm_counpon_no']) >= 1111 && intval($row_deal['cm_counpon_no']) <= 5555) {
            if (count($voucher_suffixes) == intval($set_values[$row_deal['od_id']]['od_qty']) - 1) {
                $set_values[$row_deal['od_id']]['od_qty'] = intval($set_values[$row_deal['od_id']]['od_qty']) - 1;
            } else {
                $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
                return false;
            }
        } else {
            if (count($voucher_suffixes) == intval($set_values[$row_deal['od_id']]['od_gift_qty']) - 1) {
                $set_values[$row_deal['od_id']]['od_gift_qty'] = intval($set_values[$row_deal['od_id']]['od_gift_qty']) - 1;
            } else {
                $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
                return false;
            }
        }
        if (!$db->update_from_array('tbl_order_deals', $set_values[$row_deal['od_id']], "od_order_id='$order_no' AND (od_voucher_suffixes like '" . $row_deal['cm_counpon_no'] . "' OR od_voucher_suffixes like '%, " . $row_deal['cm_counpon_no'] . "' OR od_voucher_suffixes like '" . $row_deal['cm_counpon_no'] . ",%' OR od_voucher_suffixes like '%, " . $row_deal['cm_counpon_no'] . ",%')")) {
            die($db->getError());
        }
        $db->update_from_array('tbl_coupon_mark', array('cm_status' => 3), "cm_counpon_no=" . intval($row_deal['cm_counpon_no']));
        if ($total_qty == 0) {
            $deal_id = $row_deal['deal_id'];
            $user_id = $row_deal['user_id'];
            $deal_name = $row_deal['deal_name' . $_SESSION['lang_fld_prefix']];
            $company_name = $row_deal['company_name'];
            $company_email = $row_deal['company_email'];
            $user_name = $row_deal['user_name'];
            $user_email = $row_deal['user_email'];
            $order_id = $order_id;
            $price = $row_deal['od_deal_price'] + $row_deal['od_deal_tax_amount'];
        }
        /* charityRefund($row_deal); */
        $amount += $row_deal['od_deal_price'] + $row_deal['od_deal_tax_amount'];
        $total_qty++;
    }
    if (!isset($total_qty) || intval($total_qty) <= 0) {
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
        return false;
    }
    $srch = new SearchBase('tbl_order_deals');
    $srch->addCondition('od_order_id', '=', $order_no);
    $srch->addFld('SUM(od_qty+od_gift_qty) as total_qty');
    $rs_qty_left = $srch->getResultSet();
    $row_qty = $db->fetch($rs_qty_left);
    if (intval($row_qty['total_qty']) <= 0) {
        if (!$db->update_from_array('tbl_orders', array('order_payment_status' => 2), "order_id='$order_no'")) {
            die($db->getError());
        }
    }
    if ($length == 13) {
        $txt = 'M_TXT_ADMIN_REFUND_THE_ORDER_FOR_ORDER_ID';
        $particulars_text = $txt . ' : ' . $order_id;
    } else if ($length > 13) {
        $txt = 'M_TXT_ADMIN_REFUND_THE_ORDER_FOR_DEAL';
        $txt1 = 'M_TXT_AND_FOR_VOUCHER_CODE';
        $txt2 = 'M_TXT_QTY';
        $particulars_text = $txt . ' : ' . $deal_name . ' ' . $txt1 . ' : ' . $order_id . '. ' . $txt2 . ' ' . ($total_qty) . ' @' . $price;
    }
    /* Update User Wallet History */
    if (!$db->insert_from_array('tbl_user_wallet_history', array(
                'wh_user_id' => $user_id,
                'wh_untipped_deal_id' => $deal_id,
                'wh_particulars' => $particulars_text,
                'wh_amount' => $amount,
                'wh_time' => 'mysql_func_now()'
                    ), true))
        dieJsonError($db->getError());
    /* Update User Wallet History Ends */
    if ($total_qty > 0)
        $db->query("update tbl_users set user_wallet_amount = user_wallet_amount + " . ($amount) . " where user_id=" . intval($user_id));
    ############ EMAIL NOTIFICATION TO USERS ##############
    $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=17");
    $row_tpl = $db->fetch($rs_tpl);
    /* Notify User for wallet credit after voucher refund */
    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
    $arr_replacements = array(
        'xxdeal_namexx' => $deal_name,
        'xxcompany_namexx' => $company_name,
        'xxcompany_emailxx' => $company_email,
        'xxuser_namexx' => $user_name,
        'xxuser_emailxx' => $user_email,
        'xxorder_idxx' => $order_id,
        'xxpricexx' => CONF_CURRENCY . number_format($price, 2) . CONF_CURRENCY_RIGHT,
        'xxtotal_pricexx' => CONF_CURRENCY . number_format(($amount), 2) . CONF_CURRENCY_RIGHT,
        'xxquantityxx' => intval($total_qty),
        'xxsite_namexx' => CONF_SITE_NAME,
        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
        'xxwebrooturlxx' => CONF_WEBROOT_URL,
        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
    );
    foreach ($arr_replacements as $key => $val) {
        $subject = str_replace($key, $val, $subject);
        $message = str_replace($key, $val, $message);
    }
    $MAILTO = $user_email . ',' . CONF_SITE_OWNER_EMAIL;
    $messageRefund = '<p style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; line-height: 20px; color: rgb(75, 75, 74); min-height: 200px; padding-left: 10px; float:left;">' . nl2br($message) . '</p>';
    sendMail("$MAILTO", $subject . ' ( ' . $order_id . ' )', emailTemplateSuccess($message), $headers);
    /* Notify User for wallet credit after voucher refund */
    ############ EMAIL NOTIFICATION TO USERS ##############
    return true;
}

function charityRefund($row_deal)
{
    global $db;
    /* Update charity  History */
    if ($row_deal['od_deal_charity_id'] > 0) {
        if ($row_deal['deal_charity_discount_is_percent'] == 1) {
            $charityAmount = ((($row_deal['od_deal_price'] + $row_deal['od_deal_tax_amount']) / 100) * $row_deal['deal_charity_discount']);
        } else {
            $charityAmount = $row_deal['deal_charity_discount'];
        }
        $db->insert_from_array('tbl_charity_history', array(
            'ch_user_id' => $row_deal['user_id'],
            'ch_order_id' => $row_deal['order_id'],
            'ch_charity_id' => $row_deal['od_deal_charity_id'],
            'ch_deal_id' => $row_deal['deal_id'],
            'ch_particulars' => 'Charity debited on deal ' . $row_deal['deal_name'] . ' having quantity ' . (1) . '@' . $charityAmount,
            'ch_debit' => $charityAmount,
            'ch_time' => 'mysql_func_now()'
                ), true);
    }
    return true;
    /* Update charity  History */
}

function insertVouchers($orderId)
{
    global $db;
    $srchVoucher = new SearchBase('tbl_order_deals', 'od');
    $srchVoucher->addCondition('od_order_id', '=', $orderId);
    $srchVoucher->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srchVoucher->addMultipleFields(array('o.order_id', 'o.order_date',
        'od_deal_price', 'od_qty', 'od_gift_qty', 'od_voucher_suffixes', 'od_deal_id'));
    $rsVoucher = $srchVoucher->getResultSet();
    while ($row_voucher = $db->fetch($rsVoucher)) {
        $od_voucher_suffixes = explode(', ', $row_voucher['od_voucher_suffixes']);
        foreach ($od_voucher_suffixes as $voucher) {
            $voucher_id = $row_voucher['order_id'];
            $deal_id = $row_voucher['od_deal_id'];
            $db->query("insert IGNORE into tbl_coupon_mark(cm_order_id,cm_counpon_no,cm_status,cm_deal_id,cm_redeem_date) values('$voucher_id','$voucher','0','$deal_id',NOW())");
        }
    }
}

function voucherUsed($cm_id)
{
    global $db, $msg;
    $db->query("update tbl_coupon_mark set cm_status=1, cm_redeem_date=NOW() where cm_id=" . $cm_id);
}

function voucherMarkUsed($id, $is_cm_id = false, $mark_for_merchant = false, $tipping_point_check = false, $markAsUsedCode = '')
/* $id is 17 digit voucher ID and if "$is_cm_id" is true then $id is a numeric coupon code from tbl_coupon_mark table. */
{
    global $db, $msg;
    if ($is_cm_id && intval($id) > 0) {
        $cm_id = intval($id);
    } else {
        $length = strlen($id);
        if ($length > 13) {
            $order_id = substr($id, 0, 13);
            $LastVouvherNo = ($length - 13);
            $voucher_no = substr($id, 13, $LastVouvherNo);
        } else {
            $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
            return false;
        }
    }
    /* get records from db */
    $srch = new SearchBase('tbl_coupon_mark', 'cm');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
    if ($is_cm_id && intval($id) > 0) {
        $srch->addCondition('cm_id', '=', $cm_id);
    } else {
        $srch->addCondition('cm_order_id', '=', $order_id);
        $srch->addCondition('cm_counpon_no', '=', $voucher_no);
    }
    $srch->addCondition('order_payment_status', '>', 0);
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id', 'd');
    if ($mark_for_merchant) {
        $srch->addCondition('d.deal_company', '=', intval($_SESSION['logged_user']['company_id']));
    }
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    //AND IF( deal_tipped_at, 1, 0 )
    if ($tipping_point_check == 1) {
        $srch->addFld('CASE WHEN d.voucher_valid_from <= now()  THEN 1 ELSE 0 END as canUse');
    } else {
        $srch->addFld('CASE WHEN d.voucher_valid_from <= now() AND IF( deal_tipped_at, 1, 0 ) THEN 1 ELSE 0 END as canUse');
    }
    $srch->addFld('CASE WHEN  d.voucher_valid_till >= now() and cm.cm_status=0 THEN 1 ELSE 0 END as active');
    $srch->addFld('CASE WHEN  cm.cm_status=1 THEN 1 ELSE 0 END as used');
    $srch->addFld('CASE WHEN  (d.voucher_valid_till < now()  and cm.cm_status=0) || cm.cm_status=2 THEN 1 ELSE 0 END as expired');
    $srch->addMultipleFields(array('od.od_order_id', 'od.od_to_name', 'u.user_name', 'u.user_email', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status', 'o.order_payment_capture', 'cm.cm_counpon_no', 'cm.cm_status', 'cm.cm_id', 'd.deal_id', 'd.deal_instant_deal', 'd.voucher_valid_from', 'd.voucher_valid_till', 'od_mark_as_used_code'));
    $srch->addOrder('o.order_date', 'desc');
    $result = $srch->getResultSet();
    $row = $db->fetch($result);
    if ($row['active'] == 1) {
        if(strtolower($row['od_mark_as_used_code']) != strtolower($markAsUsedCode)) {
            $msg->addError(t_lang('M_MSG_VIRTUAL_CODE_IS_INVALID'));
            return false;
        }

        if ($row['canUse'] == 1) {
            voucherUsed($row['cm_id']);
            return true;
        } else {
            $msg->addError(t_lang('M_MSG_VOUCHER_IS_NOT_ACTIVE_TO_USE'));
            return false;
        }
    }
    return false;
}

function paidReferralCommission($id, $is_cm_id = false)
{
    global $db, $msg;
    if ($is_cm_id && intval($id) > 0) {
        $cm_id = intval($id);
    }
    $srch = new SearchBase('tbl_coupon_mark', 'cm');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
    if ($is_cm_id && intval($id) > 0) {
        $srch->addCondition('cm_id', '=', $cm_id);
    }
    $srch->addCondition('order_payment_status', '>', 0);
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id', 'd');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    $srch->addFld('CASE WHEN  d.voucher_valid_till >= now() and cm.cm_status=1 THEN 1 ELSE 0 END as active');
    $srch->addMultipleFields(array('od.od_order_id', 'd.deal_id', 'u.user_id', 'o.order_date', 'o.order_payment_mode', 'o.order_payment_status', 'o.order_payment_capture', 'cm.cm_counpon_no', 'cm.cm_status', 'u.user_referral_id'));
    $srch->addOrder('o.order_date', 'desc');
    $result = $srch->getResultSet();
    $row = $db->fetch($result);
    if ($row['active'] == 1 && $row['user_referral_id'] > 0) {
        $referAmount = (float) CONF_REFERRER_COMMISSION_PERCENT;
        $rs_first_rf_com = $db->query("select count(*) as total from tbl_referral_history where rh_credited_to = " . intval($row['user_referral_id']) . " and rh_referral_user_id = " . intval($row['user_id']));
        $rs_first_rf_com = $db->fetch($rs_first_rf_com);
        if ($rs_first_rf_com['total'] == 0) {
            $db->insert_from_array('tbl_referral_history', array(
                'rh_amount' => $referAmount,
                'rh_credited_to' => $row['user_referral_id'],
                'rh_referral_user_id' => $row['user_id'],
                'rh_transaction_date' => date('Y-m-d H:i:s')
            ));
            if (!$db->insert_id()) {
                $msg->addMsg($db->getError());
            }
            $commission_percent = CONF_REFERRER_COMMISSION_PERCENT;
            $db->query("update tbl_users set user_wallet_amount = user_wallet_amount + " . $commission_percent . " where user_id=" . intval($row['user_referral_id']));
            $db->insert_from_array('tbl_user_wallet_history', array(
                'wh_user_id' => $row['user_referral_id'],
                'wh_untipped_deal_id' => $row['deal_id'],
                'wh_particulars' => 'M_TXT_COMMISSION_FOR_ORDERID' . ' ' . $row['od_order_id'],
                'wh_amount' => $commission_percent,
                'wh_time' => date('Y-m-d H:i:s')
            ));
        }
    }
}

/* While doing any updates in this function, don't forget to update cron_commissions.php file */

function paidAffiliateCommission($id, $is_cm_id = false)
{
    global $db, $msg;
    if ($is_cm_id && intval($id) > 0) {
        $cm_id = intval($id);
    }
    $srch = new SearchBase('tbl_coupon_mark', 'cm');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
    if ($is_cm_id && intval($id) > 0) {
        $srch->addCondition('cm_id', '=', $cm_id);
    }
    $srch->addCondition('order_payment_status', '>', 0);
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id', 'd');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    $srch->addFld('CASE WHEN  d.voucher_valid_till >= "' . date('Y-m-d H:i:s') . '" and cm.cm_status=1 THEN 1 ELSE 0 END as active');
    $srch->addMultipleFields(array('od.od_order_id', 'od.od_gift_qty', 'od.od_qty', 'd.deal_id', 'd.deal_name', 'u.user_id', 'u.user_affiliate_id', 'o.order_date', 'o.order_user_id', 'o.order_payment_status', 'o.order_payment_capture', 'cm.cm_counpon_no', 'od.od_deal_price', 'od.od_deal_tax_amount'));
    $srch->addOrder('o.order_date', 'desc');
    $result = $srch->getResultSet();
    $row = $db->fetch($result);
    if ($row['active'] == 1 && $row['user_affiliate_id'] > 0) {
        // $totalQuantity = $row['od_qty'] + $row['od_gift_qty'];
        $commission_to = (int) $row['user_affiliate_id'];
        $rsComm = $db->query("select affiliate_fname,affiliate_lname,affiliate_commission from tbl_affiliate where affiliate_status =1 AND affiliate_id=" . intval($commission_to));
        $rowComm = $db->fetch($rsComm);
        $commission_percent = (float) $rowComm['affiliate_commission'];
        $voucher_code = $row['od_order_id'] . $row['cm_counpon_no'];
        if ($commission_percent > 0) {
            $arr = array(
                'wh_affiliate_id' => $commission_to,
                'wh_untipped_deal_id' => $row['deal_id'],
                'wh_particulars' => 'M_TXT_AFFILIATE_COMMISSION_FOR' . ' : ' . $row['deal_name'] . ' [To: ' . $rowComm['affiliate_fname'] . ' ' . $rowComm['affiliate_lname'] . ']',
                'wh_amount' => ($row['od_deal_price'] * $commission_percent / 100),
                'wh_time' => 'mysql_func_now()',
                'wh_trans_type' => 'A',
                'wh_buyer_id' => $row['order_user_id'],
                'wh_counpon_no' => $voucher_code
            );
            $db->insert_from_array('tbl_affiliate_wallet_history', $arr, true);
        }
    }
}

function paidCharityCommission($id, $is_cm_id = false)
{
    global $db, $msg;
    if ($is_cm_id && intval($id) > 0) {
        $cm_id = intval($id);
    }
    $srch = new SearchBase('tbl_coupon_mark', 'cm');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', "cm.cm_order_id=od.od_order_id AND od.od_voucher_suffixes LIKE CONCAT('%', cm.cm_counpon_no, '%')", 'od');
    if ($is_cm_id && intval($id) > 0) {
        $srch->addCondition('cm_id', '=', $cm_id);
    }
    $srch->addCondition('order_payment_status', '>', 0);
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id = d.deal_id', 'd');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id = o.order_id', 'o');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'o.order_user_id=u.user_id', 'u');
    $srch->addFld('CASE WHEN  d.voucher_valid_till >= "' . date('Y-m-d H:i:s') . '" and cm.cm_status=1 THEN 1 ELSE 0 END as active');
    $srch->addMultipleFields(array('od.od_order_id', 'od_deal_charity_id', 'od.od_gift_qty', 'od.od_qty', 'd.deal_name', 'd.deal_id', 'u.user_id', 'd.deal_charity_discount_is_percent', 'o.order_date', 'd.deal_charity_discount', 'o.order_payment_status', 'o.order_id', 'cm.cm_counpon_no', 'od.od_deal_price', 'od.od_deal_tax_amount'));
    $srch->addOrder('o.order_date', 'desc');
    $result = $srch->getResultSet(); //echo '<pre>'.$srch->getQuery();
    $row_deal = $db->fetch($result); //echo 'arrayaaa<pre>';print_r($row_deal); exit;
    $qty = $row_deal['od_qty'] + $row_deal['od_gift_qty'];
    $qty = 1; //charity will be given one by one because voucher mark used one by one
    if ($row_deal['active'] == 1 && $row_deal['od_deal_charity_id'] > 0) {
        if ($row_deal['deal_charity_discount_is_percent'] == 1) {
            $charityAmount = (((($row_deal['od_deal_price'] + $row_deal['od_deal_tax_amount']) * ($qty)) / 100) * $row_deal['deal_charity_discount']);
        } else {
            $charityAmount = $row_deal['deal_charity_discount'];
        }
        if ($row_deal['od_deal_charity_id'] > 0) {
            $db->insert_from_array('tbl_charity_history', array(
                'ch_user_id' => $row_deal['user_id'],
                'ch_order_id' => $row_deal['order_id'],
                'ch_charity_id' => $row_deal['od_deal_charity_id'],
                'ch_deal_id' => $row_deal['deal_id'],
                'ch_particulars' => 'Charity on deal ' . $row_deal['deal_name'] . ' having quantity ' . ($qty) . '@' . $charityAmount,
                'ch_amount' => $charityAmount,
                'ch_time' => date("Y-m-d H:i:s")
                    ), true);
        }
    }
}

function fetchQRImageSrc($voucherId, &$officeUse = '')
{
    /* QR CODE */
    $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . '../qrcode/temp' . DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'qrcode/temp/';
    if (!file_exists($PNG_TEMP_DIR)) {
        mkdir($PNG_TEMP_DIR);
    }
    $errorCorrectionLevel = 'L';
    $matrixPointSize = 4;
    $filename = $PNG_TEMP_DIR . 'qr_' . $voucherId . '.png';
    if (CONF_QR_CODE == 1) {
        QRcode::png($voucherId, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        $officeUse = '';
    }
    if (CONF_QR_CODE == 2) {
        QRcode::png('http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant/voucher-detail.php?id=' . $voucherId, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        $officeUse = 'For office use only';
    }
    /* QR CODE */
    return $PNG_WEB_DIR . basename($filename);
}

function printVoucherDetail($id, &$row_deal, &$message, $show_for = '')
{
    global $db, $msg;
    $cart = new cart();
    $cart->getError();
    $length = strlen($id);
    if ($length > 13) {
        $order_id = substr($id, 0, 13);
        $LastVouvherNo = ($length - 13);
        $voucher_no = substr($id, 13, $LastVouvherNo);
        if (!is_numeric($voucher_no)) {
            return false;
        }
        $voucher_no = intval($voucher_no);
    } else {
        return false;
    }
    $srch = new SearchBase('tbl_orders', 'o');
    $srch->addCondition('o.order_id', '=', $order_id);
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', "o.order_id=od.od_order_id and (od.od_voucher_suffixes LIKE CONCAT('%', " . $voucher_no . ", '%')  OR od.od_cancelled_voucher_suffixes LIKE CONCAT('%', " . $voucher_no . ", '%'))", 'od');
    $srch->joinTable('tbl_users', 'INNER JOIN', "u.user_id=o.order_user_id ", 'u');
    if ($show_for == 'user') {
        $srch->addCondition('u.user_id', '=', intval($_SESSION['logged_user']['user_id']));
    }
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id', 'd');
    if ($show_for == 'merchant') {
        $srch->addCondition('d.deal_company', '=', intval($_SESSION['logged_user']['company_id']));
    }
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'c.company_id=d.deal_company', 'c');
    $srch->joinTable('tbl_countries', 'INNER JOIN', 'ct.country_id=c.company_country', 'ct');
    $srch->joinTable('tbl_states', 'LEFT JOIN', 'state.state_id=c.company_state', 'state');
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'ca.company_address_id=od.od_company_address_id ', 'ca');
    $srch->joinTable('tbl_coupon_mark', 'LEFT OUTER JOIN', 'cm.cm_order_id=o.order_id AND cm.cm_counpon_no="' . $voucher_no . '"', 'cm');
    $srch->addFld('CASE WHEN d.voucher_valid_from <= now() AND IF(deal_tipped_at, 1, 0) THEN 1 ELSE 0 END as canUse');
    $srch->addFld('CASE WHEN d.voucher_valid_till >= now() AND cm.cm_status=0 THEN 1 ELSE 0 END as active');
    $srch->joinTable('tbl_order_bookings', 'LEFT JOIN', 'od.od_id=ob.obooking_od_id', 'ob');
    $srch->addOrder('order_date', 'desc');
    $srch->addMultipleFields(array('o.order_id', 'o.order_date', 'od.od_deal_price', 'od.od_to_name', 'od.od_gift_qty', 'd.deal_name' . $_SESSION['lang_fld_prefix'], 'd.deal_redeeming_instructions' . $_SESSION['lang_fld_prefix'], 'd.deal_highlights' . $_SESSION['lang_fld_prefix'], 'd.deal_desc' . $_SESSION['lang_fld_prefix'], 'd.deal_tipped_at', 'd.voucher_valid_till', 'd.voucher_valid_from', 'c.company_name' . $_SESSION['lang_fld_prefix'], 'ca.company_address_line1' . $_SESSION['lang_fld_prefix'], 'ca.company_address_line2' . $_SESSION['lang_fld_prefix'], 'ca.company_address_line3' . $_SESSION['lang_fld_prefix'], 'c.company_city' . $_SESSION['lang_fld_prefix'], 'c.company_state', 'ca.company_address_zip', 'c.company_phone', 'c.company_email', 'state.state_name' . $_SESSION['lang_fld_prefix'], 'ct.country_name' . $_SESSION['lang_fld_prefix'], 'u.user_name', 'u.user_email', 'd.deal_id',
        'od.od_sub_deal_name', 'ob.obooking_booking_from', 'ob.obooking_booking_till', 'od_mark_as_used_code'
    ));
    $rs_listing = $srch->getResultSet();
    $sub_deal_name = "";
    $deal_desc = '';
    while ($row = $db->fetch($rs_listing)) {
        $row_deal = $row;
        $voucher_code = $row['order_id'] . $voucher_no;
        $tax = $cart->getDealTaxDetail($row['deal_id'], $row['od_deal_price']);
        //echo '<pre>';print_r($tax); exit;
        $officeUse = "";
        $imgSrc = fetchQRImageSrc($id, $officeUse);
        if ($row['od_sub_deal_name'] != "") {
            $sub_deal_name = "(" . $row['od_sub_deal_name'] . ")";
        }
        $date = "";
        if ($row['obooking_booking_from'] != "" && $row['obooking_booking_till'] != "") {
            $checkoutDate = date('Y-m-d', strtotime($row['obooking_booking_till'] . ' +1 day'));
            $date = date("D M j Y", strtotime($row['obooking_booking_from'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D M j Y", strtotime($checkoutDate));
            $date1 = strtotime($row['obooking_booking_from']);
            $date2 = strtotime($checkoutDate);
            $diff = $date2 - $date1;
            $date .= " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
        }
        $style = 'style="color:#000; padding:3px 0;"';
        $deal_name = html_entity_decode(appendPlainText($row_deal['deal_name' . $_SESSION['lang_fld_prefix']])) . ' ' . $sub_deal_name;
        $deal_desc = '<li ' . $style . '><strong>' . $deal_name . '</strong></li>';
        if ($date != '') {
            $deal_desc .= '<li ' . $style . '><strong>' . $date . '</strong></li>';
        }
        if ($row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] != '') {
            $deal_desc .= '<li ' . $style . '><strong>' . $row_deal['deal_desc' . $_SESSION['lang_fld_prefix']] . '</strong></li>';
        }
        $arr_replacements = array(
            'xxuser_namexx' => $row['user_name'],
            'xxdeal_namexx' => appendPlainText($deal_name),
            'xxdeal_descriptionxx' => $deal_desc,
            'xxis_giftedxx' => '',
            'xxamountxx' => CONF_CURRENCY . number_format(($row['od_deal_price'] + $tax['taxAmount']), 2) . CONF_CURRENCY_RIGHT,
            // 'xxtaxamountxx' => CONF_CURRENCY . number_format($tax['taxAmount']) . CONF_CURRENCY_RIGHT,
            'xxtaxamountxx' => CONF_CURRENCY . number_format($tax['taxAmount'], 2) . CONF_CURRENCY_RIGHT,
            'xxordered_coupon_qtyxx' => '1',
            'xxinstructionsxx' => ($row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] ? $row_deal['deal_redeeming_instructions' . $_SESSION['lang_fld_prefix']] : 'N/A'),
            'xxdeal_highlightsxx' => $row['deal_highlights' . $_SESSION['lang_fld_prefix']],
            'xxcompany_namexx' => $row['company_name' . $_SESSION['lang_fld_prefix']],
            'xxcompany_addressxx' => $row['company_name' . $_SESSION['lang_fld_prefix']] . '<br/>
				  ' . $row['company_address_line1' . $_SESSION['lang_fld_prefix']] . ',<br/>
				  ' . $row['company_address_line2' . $_SESSION['lang_fld_prefix']] . '<br/>
				  ' . $row['company_address_line3' . $_SESSION['lang_fld_prefix']] . ' ' . $row['company_city' . $_SESSION['lang_fld_prefix']] . ' <br/>
				  ' . $row['state_name' . $_SESSION['lang_fld_prefix']] . ' ' . $row['country_name' . $_SESSION['lang_fld_prefix']] . '<br/>',
            'xxcompany_zipxx' => $row['company_address_zip'],
            'xxcompany_phonexx' => $row['company_phone'],
            'xxcompany_emailxx' => $row['company_email'],
            'xxrecipientxx' => $row['user_name'],
            'xxemail_addressxx' => $row['user_email'],
            'xxpurchase_datexx' => displayDate($row['order_date'], true),
            'xxvalidfromxx' => displayDate($row['voucher_valid_from']),
            'xxvalidtillxx' => displayDate($row['voucher_valid_till']),
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxwebsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxordered_coupon_qtyxx' => '1',
            'xxorderidxx' => $voucher_code, //$row['order_id'],
            'xxqrcodexx' => '<img src="' . $imgSrc . '" />',
            'xxofficeusexx' => $officeUse,
            'xxvouchercodexx' => $voucher_code,
            'xxmark_as_used_code_xx' => (isUserLogged()) ? $row['od_mark_as_used_code'] : 'N/A',
        );

        if (displayDate($row['deal_tipped_at']) != '') {
            $rs = $db->query("select * from tbl_email_templates where tpl_id=1");
            $row_tpl = $db->fetch($rs);
        } else {
            $rs = $db->query("select * from tbl_email_templates where tpl_id=7");
            $row_tpl = $db->fetch($rs);
            $arr_replacements['xxtippedxx'] = t_lang('M_TXT_DEAL_HAS_NOT_TIPPED');
        }
        if ($row['od_to_name'] != '' && intval($row['od_gift_qty']) > 0) {
            $arr_replacements['xxis_giftedxx'] = t_lang('M_TXT_VOUCHER_IS_GIFTED') . ' <strong>' . $row['od_to_name'] . '</strong>';
        }
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
    }
    return false;
}

function deleteSubscriber($subs_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
        $db->query("DELETE FROM tbl_newsletter_subscription WHERE subs_id =$subs_id");
        $msg->addMsg(t_lang("M_TXT_RECORD_DELETED"));
        return true;
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
    return false;
}

function deleteMember($user_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
        $db->query("UPDATE tbl_users set user_deleted = 1 WHERE user_id =$user_id");
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
    } else {
        $msg->addError(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

function restoreMember($user_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        $db->query("UPDATE tbl_users set user_deleted = 0 WHERE user_id =$user_id");
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
    } else {
        $msg->addError(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

function restoreCompanyMember($company_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
        $db->query("UPDATE tbl_companies set company_deleted = 0 WHERE company_id =$company_id");
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
    } else {
        $msg->addError(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

function deleteCompanyMemberPermanent($company_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(3, '', 'delete')) {
        $db->query("delete from tbl_companies  WHERE company_id =$company_id and company_deleted = 1 ");
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
    } else {
        $msg->addError(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

function deleteMemberPermanent($user_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
        $db->query("delete from tbl_users  WHERE user_id = " . intval($user_id) . " and user_deleted = 1 ");
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
    } else {
        $msg->addError(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}

function canDeleteCity($city_id)
{
    global $srch, $db, $msg;
    $srch = new SearchBase('tbl_deals');
    $srch->addCondition('deal_city', '=', $city_id);
    $srch->addCondition('deal_deleted', '=', 0);
    $rs = $srch->getResultSet();
    $total_count = $srch->recordCount($rs);
    //	echo $total_count;
    return $total_count;
    /* if($total_count == 0 ) return true; else return false; */
}

function deleteCity($city_id)
{
    global $db, $msg, $srch;
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        if (canDeleteCity($city_id) == 0) {
            if (!$db->update_from_array('tbl_cities', array('city_deleted' => 1), 'city_id=' . $city_id)) {
                $msg->addError($db->getError());
            } else {
                $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            }
        } else {
            $msg->addError(t_lang('M_MSG_CITY_DELETION_NOT_ALLOWED'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function deleteCityPermanent($city_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $db->query("delete from tbl_cities  WHERE city_id =$city_id and city_deleted = 1 ");
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function deleteZonePermanent($zone_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        $db->query("delete from tbl_tax_geo_zones  WHERE geozone_id =$zone_id and geozone_deleted = 1 ");
        $db->query("delete from tbl_geo_zone_location  WHERE zoneloc_geozone_id =$zone_id ");
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function restoreCity($city_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        if (!$db->update_from_array('tbl_cities', array('city_deleted' => 0), 'city_id=' . $city_id)) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_CITY_RESTORE'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function restoreZone($zone_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(7, '', 'edit')) {
        if (!$db->update_from_array('tbl_tax_geo_zones', array('geozone_deleted' => 0), 'geozone_id=' . $zone_id)) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_CITY_RESTORE'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function canDeleteCategory($category_id)
{
    global $srch, $db, $msg;
    $srch = new SearchBase('tbl_deal_to_category', 'dtc');
    $srch->addCondition('dc_cat_id', '=', $category_id);
    $rs = $srch->getResultSet();
    $total_count = $srch->recordCount($rs);
    return $total_count;
    /* if($total_count == 0 ) return true; else return false; */
}

function isParentCategory($category_id)
{
    global $db, $msg, $srchCat;
    $srchCat = new SearchBase('tbl_deal_categories');
    $srchCat->addCondition('cat_parent_id', '=', $category_id);
    $srchCat->addGroupBy('cat_parent_id');
    $rsCat = $srchCat->getResultSet();
    $total_count = $srchCat->recordCount($rsCat);
    return $total_count;
}

function deleteCategory($category_id)
{
    global $db, $msg, $srch;
    if (checkAdminAddEditDeletePermission(5, '', 'delete')) {
        if (isParentCategory($category_id) == 0) {
            if (canDeleteCategory($category_id) == 0) {
                if (!$db->query("delete c, dc, udc from 	tbl_deal_categories c
						left outer join tbl_deal_to_category dc on c.cat_id=dc.dc_cat_id
						left outer join tbl_user_to_deal_cat udc on c.cat_id=udc.udc_cat_id
						where c.cat_id=" . $category_id)) {
                    $msg->addError($db->getError());
                } else {
                    $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
                }
            } else {
                $msg->addError(t_lang('M_TXT_CATEGORY_DELETION_NOT_ALLOWED'));
            }
        } else {
            $msg->addError(t_lang('M_TXT_To_delete_this_category_you_must_first_remove_the_association'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function canDeleteCompany($company_id)
{
    global $srch, $db, $msg;
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_company', '=', $company_id);
    $srch->addCondition('deal_deleted', '=', 0);
    $srch->addCondition('deal_status', 'IN', array(0, 1, 2, 4, 5));
    $rs = $srch->getResultSet();
    $total_count = $srch->recordCount($rs);
    return $total_count;
    /* if($total_count == 0 ) return true; else return false; */
}

function getCompaniesHavingDeals()
{
    global $srch, $db, $msg;
    $srch = fetchDealObj();
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'c.company_id = deal_company', 'c');
    $srch->addCondition('company_deleted', '=', 0);
    $srch->addCondition('company_active', '=', 1);
    $srch->addMultipleFields(array('company_id', 'company_name', 'company_name_lang1', 'company_lname', 'company_lname_lang1'));
    $srch->doNotCalculateRecords();
    $srch->addGroupBy('company_id');
    $result = $srch->getResultSet();
    $rows = $db->fetch_all($result);
    return $rows;
}

function deleteCompany($company_id)
{
    global $db, $msg, $srch;
    if (checkAdminAddEditDeletePermission(5, '', 'delete')) {
        if (canDeleteCompany($company_id) == 0) {
            if (!$db->update_from_array('tbl_companies', array('company_deleted' => 1), 'company_id' . '=' . $company_id)) {
                $msg->addError($db->getError());
            } else {
                $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            }
        } else {
            $msg->addError(t_lang('M_TXT_COMPANY_DELETION_NOT_ALLOWED'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function deleteTrainingVideo($id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
        if (!$db->query("delete from tbl_training_video where tv_id = " . $id)) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function deleteAdminUser($admin_id)
{
    global $db, $msg;
    if (checkAdminAddEditDeletePermission(9, '', 'delete')) {
        if (!$db->query("delete from tbl_admin where admin_id = " . $admin_id)) {
            $msg->addError($db->getError());
        } else {
            $db->query("delete from tbl_admin_permissions where ap_admin_id  = " . $admin_id);
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
        }
    } else {
        $msg->addError(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}

function addAdminUser($post)
{
    global $db, $msg, $frm;
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $record = new TableRecord('tbl_admin');
        $record->assignValues($post);
        if ($post['admin_password'] != "") {
            $record->setFldValue('admin_password', md5($post['admin_password']));
        }
        if ($post['admin_id'] == '') {
            $record->setFldValue('admin_password', md5($post['admin_password']));
        }
        $success = ($post['admin_id'] > 1 && $post['admin_id'] != $_SESSION['admin_logged']['admin_id']) ? $record->update('admin_id' . '=' . $post['admin_id']) : $record->addNew(); // can not edit 1 which is superadmin
        if ($success) {
            if ($post['admin_id'] == '') {
                sendNotificationtoSubAdmin($post);
            }
            $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            //redirectUser();
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            $frm->fill($post);
        }
    }
}

function addAdminUserPermission($post)
{
    global $db, $msg, $frm;
    $admin_id = ($post['admin_id'] > 1 && $post['admin_id'] != $_SESSION['admin_logged']['admin_id']) ? $post['admin_id'] : $post['admin_id'];
    $db->query("delete from tbl_admin_permissions where ap_admin_id = " . $admin_id);
    if (is_array($post['ap_permission_view'])) {
        foreach ($post['ap_permission_view'] as $key => $val) {
            $db->insert_from_array('tbl_admin_permissions', array('ap_admin_id' => $admin_id, 'ap_permission_id' => $key));
        }
    }
    if (is_array($post['ap_permission_add'])) {
        foreach ($post['ap_permission_add'] as $key => $val) {
            $db->update_from_array('tbl_admin_permissions', array('ap_permission_add' => $val), array('smt' => 'ap_admin_id = ?', 'smt' => 'ap_permission_id = ?', 'vals' => array($admin_id), 'vals' => array($key), 'execute_mysql_functions' => false));
        }
    }
    if (is_array($post['ap_permission_edit'])) {
        foreach ($post['ap_permission_edit'] as $key => $val) {
            $db->update_from_array('tbl_admin_permissions', array('ap_permission_edit' => $val), array('smt' => 'ap_admin_id = ?', 'smt' => 'ap_permission_id = ?', 'vals' => array($admin_id), 'vals' => array($key), 'execute_mysql_functions' => false));
        }
    }
    if (is_array($post['ap_permission_delete'])) {
        foreach ($post['ap_permission_delete'] as $key => $val) {
            $db->update_from_array('tbl_admin_permissions', array('ap_permission_delete' => $val), array('smt' => 'ap_admin_id = ?', 'smt' => 'ap_permission_id = ?', 'vals' => array($admin_id), 'vals' => array($key), 'execute_mysql_functions' => false));
        }
    }
    $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
}

function sendNotificationtoSubAdmin($data)
{
    global $db;
    $rs = $db->query("select * from tbl_email_templates where tpl_id=21");
    $row_tpl = $db->fetch($rs);
    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
    $arr_replacements = array(
        'xxcompany_namexx' => $data['admin_name'],
        'xxuser_namexx' => $data['admin_username'],
        'xxemail_addressxx' => $data['admin_email'],
        'xxpasswordxx' => $data['admin_password'],
        'xxloginurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'manager/',
        'xxsite_namexx' => CONF_SITE_NAME,
        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
        'xxwebrooturlxx' => CONF_WEBROOT_URL,
        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
    );
    foreach ($arr_replacements as $key => $val) {
        $subject = str_replace($key, $val, $subject);
        $message = str_replace($key, $val, $message);
    }
    if ($row_tpl['tpl_status'] == 1) {
        sendMail($data['admin_email'], $subject, emailTemplateSuccess($message), $headers);
    }
    return true;
}

function editAdminUser($admin_id)
{
    global $db, $msg, $frm;
    $record = new TableRecord('tbl_admin');
    if (!$record->loadFromDb('admin_id' . '=' . $admin_id, true)) {
        $msg->addError($record->getError());
    } else {
        $arr = $record->getFlds();
        $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
        $admin_password = $arr['admin_password'];
        $arr['admin_password'] = '';
        $srch = new SearchBase('tbl_admin_permissions');
        $srch->addCondition('ap_admin_id', '=', $admin_id);
        $srch->addFld('ap_permission_id');
        $rs = $srch->getResultSet();
        $arr['ap_permission_view'] = [];
        while ($row = $db->fetch($rs)) {
            $arr['ap_permission_view'][$row['ap_permission_id']] = 1;
        }
        $cnd = $srch->addCondition('ap_permission_add', '=', 1);
        $rs = $srch->getResultSet();
        $arr['ap_permission_add'] = [];
        while ($row = $db->fetch($rs)) {
            $arr['ap_permission_add'][$row['ap_permission_id']] = 1;
        }
        $cnd->remove();
        $cnd = $srch->addCondition('ap_permission_edit', '=', 1);
        $rs = $srch->getResultSet();
        $arr['ap_permission_edit'] = [];
        while ($row = $db->fetch($rs)) {
            $arr['ap_permission_edit'][$row['ap_permission_id']] = 1;
        }
        $cnd->remove();
        $cnd = $srch->addCondition('ap_permission_delete', '=', 1);
        $rs = $srch->getResultSet();
        $arr['ap_permission_delete'] = [];
        while ($row = $db->fetch($rs)) {
            $arr['ap_permission_delete'][$row['ap_permission_id']] = 1;
        }
        $frm->fill($arr);
    }
}

function payToCharityByAdmin($post)
{
    global $db, $msg, $frm;
    $record = new TableRecord('tbl_charity_history');
    $record->assignValues($post);
    $record->setFldValue('ch_time', date('Y-m-d H:i:s'), false);
    $success = $record->addNew();
    if ($success) {
        $rs = $db->query("select * from tbl_company_charity where charity_id=" . $post['ch_charity_id']);
        $row = $db->fetch($rs);
        $rs1 = $db->query("select (sum(ch_amount)-sum(ch_debit)) as balance from tbl_charity_history where ch_charity_id=" . $post['ch_charity_id']);
        $row1 = $db->fetch($rs1);
        $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=30");
        $row_tpl = $db->fetch($rs_tpl);
        /* Notify User */
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = array(
            'xxcharity_namexx' => $row['charity_name'],
            'xxcharity_email_addressxx' => $row['charity_email_address'],
            'xxparticularsxx' => $post['ch_particulars'],
            'xxamountxx' => CONF_CURRENCY . number_format(($post['ch_debit']), 2) . CONF_CURRENCY_RIGHT,
            'xxbalancexx' => $row1['balance'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        if ($row_tpl['tpl_status'] == 1) {
            sendMail($row['charity_email_address'], $subject, emailTemplateSuccess($message), $headers);
        }
        /* Notify User Ends */
        $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
        redirectUser('charity.php');
    } else {
        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
        $frm->fill($post);
    }
}

function payToAffiliateByAdmin($post)
{
    global $db, $msg, $frm;
    $record = new TableRecord('tbl_affiliate_wallet_history');
    $record->assignValues($post);
    $record->setFldValue('wh_amount', '-' . $post['wh_amount'], true);
    $record->setFldValue('wh_trans_type', 'A', true);
    $record->setFldValue('wh_time', date('Y-m-d H:i:s'), false);
    $success = $record->addNew();
    if ($success) {
        $rs = $db->query("select * from tbl_affiliate where affiliate_id=" . $post['wh_affiliate_id']);
        $row = $db->fetch($rs);
        $rs1 = $db->query("select (sum(wh_amount)) as balance from tbl_affiliate_wallet_history where wh_affiliate_id=" . $post['wh_affiliate_id']);
        $row1 = $db->fetch($rs1);
        $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=30");
        $row_tpl = $db->fetch($rs_tpl);
        /* Notify User */
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = array(
            'xxcharity_namexx' => $row['affiliate_fname'] . ' ' . $row['affiliate_lname'],
            'xxcharity_email_addressxx' => $row['affiliate_email_address'],
            'xxparticularsxx' => $post['wh_particulars'],
            'xxamountxx' => CONF_CURRENCY . number_format(($post['wh_amount']), 2) . CONF_CURRENCY_RIGHT,
            'xxbalancexx' => $row1['balance'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        if ($row_tpl['tpl_status'] == 1) {
            sendMail($row['affiliate_email_address'], $subject, emailTemplateSuccess($message), $headers);
        }
        /* Notify User Ends */
        $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
        redirectUser('affiliate_list.php?uid=' . $post['wh_affiliate_id']);
    } else {
        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
        $frm->fill($post);
    }
}

function payToRepresentativeByAdmin($post)
{
    global $db, $msg, $frm;
    $record = new TableRecord('tbl_representative_wallet_history');
    $record->assignValues($post);
    if ($post['entry_type'] == 2) {
        $amount = $post['rwh_amount'];
    }
    if ($post['entry_type'] == 1) {
        $amount = (-1) * ($post['rwh_amount']);
    }
    $record->setFldValue('rwh_amount', $amount, true);
    $record->setFldValue('rwh_trans_type', 'A', true);
    $record->setFldValue('rwh_time', 'mysql_func_NOW()', true);
    $success = $record->addNew();
    if ($success) {
        $rs = $db->query("select * from tbl_representative where rep_id=" . $post['rwh_rep_id']);
        $row = $db->fetch($rs);
        $rs1 = $db->query("select (sum(rwh_amount)) as balance from tbl_representative_wallet_history where rwh_rep_id=" . $post['rwh_rep_id']);
        $row1 = $db->fetch($rs1);
        $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=30");
        $row_tpl = $db->fetch($rs_tpl);
        /* Notify User */
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        if ($post['entry_type'] == 2) {
            $amount = (-1) * ($post['rwh_amount']);
        }
        if ($post['entry_type'] == 1) {
            $amount = $post['rwh_amount'];
        }
        $arr_replacements = array(
            'xxcharity_namexx' => $row['rep_fname'] . ' ' . $row['rep_lname'],
            'xxcharity_email_addressxx' => $row['rep_email_address'],
            'xxparticularsxx' => $post['rwh_particulars'],
            'xxamountxx' => CONF_CURRENCY . number_format(($amount), 2) . CONF_CURRENCY_RIGHT,
            'xxbalancexx' => $row1['balance'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        if ($row_tpl['tpl_status'] == 1) {
            sendMail($row['rep_email_address'], $subject, emailTemplateSuccess($message), $headers);
        }
        /* Notify User Ends */
        $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
        /*  redirectUser('rep-report.php?uid='.$post['rwh_rep_id']); */
    } else {
        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
        $frm->fill($post);
    }
}

function payToMerchantByAdmin($post)
{
    global $db, $msg, $frm;
    /* If amount is to be Debited then it should not be more that payable amount */
    if ($post['entry_type'] == 1 && $post['cwh_amount'] > $post['payable_amount']) {
        die(t_lang('M_TXT_AMOUNT_NEEDS_TO_DEBITED_CANNOT_BE_GREATER_THAN_PAYABLE_AMOUNT'));
    }
    /* If amount is to be Debited then it should not be more that payable amount */
    $record = new TableRecord('tbl_company_wallet_history');
    $record->assignValues($post);
    if ($post['entry_type'] == 2) {
        $amount = $post['cwh_amount'];
    }
    if ($post['entry_type'] == 1) {
        $amount = (-1) * ($post['cwh_amount']);
    }
    $record->setFldValue('cwh_amount', $amount, true);
    if (isset($post['cwh_untipped_deal_id'])) {
        $record->setFldValue('cwh_untipped_deal_id', $post['cwh_untipped_deal_id'], true);
    }
    if (isset($post['deal']) && $post['deal'] > 0) {
        $record->setFldValue('cwh_untipped_deal_id', $post['deal'], true);
    }
    $record->setFldValue('cwh_time', 'mysql_func_NOW()', true);
    $success = $record->addNew();
    if ($success) {
        $rs = $db->query("select * from tbl_companies where company_id=" . $post['cwh_company_id']);
        $row = $db->fetch($rs);
        $rs1 = $db->query("select (sum(cwh_amount)) as balance from tbl_company_wallet_history where cwh_company_id=" . $post['cwh_company_id']);
        $row1 = $db->fetch($rs1);
        $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=30");
        $row_tpl = $db->fetch($rs_tpl);
        /* Notify User */
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        if ($post['entry_type'] == 2) {
            $amount = (-1) * ($post['cwh_amount']);
        }
        if ($post['entry_type'] == 1) {
            $amount = $post['cwh_amount'];
        }
        $arr_replacements = array(
            'xxcharity_namexx' => $row['company_name'],
            'xxcharity_email_addressxx' => $row['company_email'],
            'xxparticularsxx' => $post['cwh_particulars'],
            'xxamountxx' => CONF_CURRENCY . number_format(($amount), 2) . CONF_CURRENCY_RIGHT,
            'xxbalancexx' => $row1['balance'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        if ($row_tpl['tpl_status'] == 1) {
            sendMail($row['company_email'], $subject, emailTemplateSuccess($message), $headers);
        }
        /* Notify User Ends */
        $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
        /*  redirectUser('rep-report.php?uid='.$post['rwh_rep_id']); */
    } else {
        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
        $frm->fill($post);
    }
}

function repPermission($rep_id, $deal_id)
{
    global $db, $msg;
    $company = [];
    $companyRep = $db->query("SELECT company_id FROM tbl_companies WHERE company_rep_id=" . $rep_id);
    while ($company_data = $db->fetch($companyRep)) {
        $company[] = $company_data['company_id'];
    }
    if ($deal_id > 0) {
        $repDeal = $db->query("SELECT * FROM tbl_deals WHERE deal_id = " . $deal_id);
    }
    $dealData = $db->fetch($repDeal);
    if (in_array($dealData['deal_company'], $company)) {
        return true;
    } else {
        return false;
    }
}

function getNewMessagesCount()
{
    global $db;
    $unread_tickets = 0;
    if (!isCompanyUserLogged()) {
        $sql1 = $db->query("SELECT COUNT(*) AS unread_messages FROM tbl_support_tickets WHERE ticket_viewed = '0'");
        $rs1 = $db->fetch($sql1);
        $unread_tickets = $rs1['unread_messages'];
    }
    if (isCompanyUserLogged()) {
        $sql2 = $db->query("SELECT COUNT(*) AS unread_messages FROM tbl_support_ticket_messages AS m INNER JOIN tbl_support_tickets AS t ON m.msg_ticket_id=t.ticket_id WHERE msg_viewed = '0' AND msg_sender_is_merchant = '0' AND ticket_created_by = " . $_SESSION['logged_user']['company_id']);
    } else {
        $sql2 = $db->query("SELECT COUNT(*) AS unread_messages FROM tbl_support_ticket_messages WHERE msg_viewed = '0' AND 	msg_sender_is_merchant = '1'");
    }
    $rs2 = $db->fetch($sql2);
    $unread_messages = $rs2['unread_messages'];
    $total_unread_messages = intval($unread_tickets + $unread_messages);
    return $total_unread_messages;
}

/* 	email template start for sending deal purchased notification to merchant	 */

function send_deal_purchased_email_to_merchant($result, $row_tpl)
{
    $cart = new Cart();
    $cart->getError();
    foreach ($result as $key => $value) {
        $merchantresult[$value['company_name']][] = $value;
    }
    foreach ($merchantresult as $detail) {
        $product = false;
        $str = '';
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        foreach ($detail as $row_deal) {
            $tax = $cart->getDealTaxDetail($row_deal['deal_id'], $row_deal['od_deal_price']);
            $row_deal['tax_amount'] = $tax['taxAmount'];
            $option = "";
            $order_id = $row_deal['order_id'];
            $coupnCode = '';
            if (($row_deal['od_qty'] + $row_deal['od_gift_qty']) > 0) {
                $qty = $row_deal['od_qty'] + $row_deal['od_gift_qty'];
                $tax = $row_deal['tax_amount'] * $qty;
                $od_voucher_suffixes = explode(', ', $row_deal['od_voucher_suffixes']);
                foreach ($od_voucher_suffixes as $voucher) {
                    $coupnCode .= $row_deal['order_id'] . $voucher . ',';
                }
                $order_options = get_order_option(array('od_id' => $row_deal['od_id']));
                if (is_array($order_options) && count($order_options) && $order_options != false) {
                    foreach ($order_options as $op) {
                        $option .= ' ' . $op['oo_option_name'] . ': ' . $op['oo_option_value'] . ' ,';
                    }
                }
                $date = '';
                if ($row_deal['obooking_booking_from'] != "" && $row_deal['obooking_booking_till'] != "") {
                    $checkoutDate = date('Y-m-d', strtotime($row_deal['obooking_booking_till'] . ' +1 day'));
                    $date .= '<div style="font-size:12px;">';
                    $date .= date("D, M j Y", strtotime($row_deal['obooking_booking_from'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D ,M j Y", strtotime($checkoutDate));
                    $date1 = strtotime($row_deal['obooking_booking_from']);
                    $date2 = strtotime($checkoutDate);
                    $diff = $date2 - $date1;
                    $date .= " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
                    $date .= '</div>';
                }
                if ($row_deal['deal_type'] == 1) {
                    $product = true;
                }
                if ($row_deal['od_sub_deal_name'] != "") {
                    $subdealname = "(" . $row_deal['od_sub_deal_name'] . ")";
                }
                $shipping_charges = $row_deal['od_shipping_charges'];
                $str .= '<span class="im" style="">
				<table width="100%" cellspacing="0" cellpadding="2" border="0" style="background: none repeat scroll 0% 0% rgb(249, 249, 249); border-top: 1px solid rgb(237, 237, 237); border-bottom: 1px solid rgb(237, 237, 237); font-size: 15px; padding: 0px 0px 10px;">
					<tbody>
                        <tr>
                        	<td colspan="2" style="font-weight: 700; font-size: 20px; padding: 4px 20px; background: none repeat scroll 0px 0px rgb(225, 225, 225); color: rgb(0, 0, 0);">' . $row_deal["deal_name" . $_SESSION["lang_fld_prefix"]] . $subdealname . '</td>
                         </tr><tr>
                            <td colspan="2" style=""></td>
                         </tr>';
                if ($date) {
                    $str .= '<tr>
                        	<td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_BOOKING_DATE") . ':</td>
                            <td>' . $date . '</td>
                         </tr>';
                }
                if ($row_deal['deal_type'] == 0) {
                    $str .= '  <tr>
                        	<td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_COUPON_CODE") . ':</td>
                            <td>' . trim($coupnCode, ',') . '</td>
                         </tr>';
                } else {
                    if (strlen($option) > 2) {
                        $str .= '  <tr>
                        	<td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_ATTRIBUTE") . ':</td>
                            <td>' . trim($option, ",") . '</td>
                         </tr>';
                    }
                }
                $str .= '<tr>
                        	<td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_QUANTITY") . ':</td>
                            <td>' . $qty . '</td>
                         </tr>
                         <tr>
                        	<td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_DEAL_PRODUCT") . ' ' . t_lang("M_TXT_PRICE") . ':</td>
                            <td>' . CONF_CURRENCY . number_format($row_deal['od_deal_price'], 2) . CONF_CURRENCY_RIGHT . '</td>
                         </tr>
                           <tr>
                        	<td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_EMAIL") . ':</td>
                            <td><a href="mailto:' . $row_deal['user_email'] . '" style="color:#cf1e36;">' . $row_deal['user_email'] . '</a></td>
                         </tr>
                          <tr>
						   <td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_TAX_CHARGES") . ':</td>
								<td style=" font-size:15px;">' . CONF_CURRENCY . number_format($tax, 2) . CONF_CURRENCY_RIGHT . '</td>
						</tr>';
                if ($shipping_charges > 0 && ($row_deal['deal_type'] == 1) && ($row_deal['deal_sub_type'] == 0)) {
                    $str .= '  <tr>
                            <td style="font-size:18px;vertical-align:top;padding-left:20px;color:#009eba;width:30%;">' . t_lang("M_TXT_SHIPPING_CHARGES") . ':</td>
							<td style=" font-size:15px;">' . CONF_CURRENCY . number_format($shipping_charges, 2) . '</td>
                           </tr>';
                }
                $str .= ' </tbody>
				</table></span>';
            }
        }
        if ($product && $row_deal['deal_sub_type'] == 0) {
            $str .= '<div style="background:#f5f5f5">  <p style="font-family:Arial;font-size:14px;border:1px solid #ddd;padding:10px;margin:0"><span style="font-size:18px;font-weight:bold">' . t_lang("M_TXT_SHIPPING_ADDRESS") . ': </span>' . $row_deal['shippingAddress'] . '</p></div>';
        }
        $arr_replacements = array(
            'xxuser_namexx' => $row_deal['user_name'],
            'xxcompany_namexx' => $row_deal['company_name' . $_SESSION['lang_fld_prefix']],
            'xxrecipientxx' => $row_deal['user_name'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxwebrooturlxx' => CONF_WEBROOT_URL,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
        );
        foreach ($arr_replacements as $key => $val) {
            $message = str_replace($key, $val, $message);
        }
        $message = str_replace('xxorderdetailxx', $str, $message);
        if ($row_tpl['tpl_status'] == 1) {
            $headers = "";
            sendMail($row_deal['company_email'], $subject . ' ' . $order_id, emailTemplateSuccess($message), $headers);
        }
    }
}

/* 	email template end for sending deal purchased notification to merchant	 */
/* 		email template start for sending deal purchased notification to admin	 */

function send_deal_purchased_email_to_admin($result, $row_tpl, $mail_to_others = false)
{
    $cart = new Cart();
    $cart->getError();
    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
    $str = '';
    $product = false;
    foreach ($result as $key => $row_deal) {
        $option = "";
        $tax = $cart->getDealTaxDetail($row_deal['deal_id'], $row_deal['od_deal_price']);
        $row_deal['tax_amount'] = $tax['taxAmount'];
        if (($row_deal['od_qty'] + $row_deal['od_gift_qty']) > 0) {
            $qty = $row_deal['od_qty'] + $row_deal['od_gift_qty'];
            $od_voucher_suffixes = explode(', ', $row_deal['od_voucher_suffixes']);
            $coupnCode = '';
            foreach ($od_voucher_suffixes as $voucher) {
                $coupnCode .= $row_deal['order_id'] . $voucher . ',';
            }
            $order_id = $row_deal['order_id'];
            $order_options = get_order_option(array('od_id' => $row_deal['od_id']));
            if (is_array($order_options) && count($order_options) && $order_options != false) {
                $option .= '<div style="font-size:12px;">';
                foreach ($order_options as $op) {
                    $option .= '- ' . $op['oo_option_name'] . ': ' . $op['oo_option_value'] . '<br/>';
                }
                $option .= '</div>';
            }
            if ($row_deal['deal_type'] == 1) {
                $product = true;
            }
            if ($row_deal['obooking_booking_from'] != "" && $row_deal['obooking_booking_till'] != "") {
                $checkoutDate = date('Y-m-d', strtotime($row_deal['obooking_booking_till'] . ' +1 day'));
                $option .= '<div style="font-size:12px;">';
                $option .= date("D, M j Y", strtotime($row_deal['obooking_booking_from'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D, M j Y", strtotime($checkoutDate));
                $date1 = strtotime($row_deal['obooking_booking_from']);
                $date2 = strtotime($checkoutDate);
                $diff = $date2 - $date1;
                $option .= " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
                $option .= '</div>';
            }
            $tax = $row_deal['tax_amount'] * $qty;
            $shipping_charges = $row_deal['od_shipping_charges'];
            $subdealname = "";
            if ($row_deal['od_sub_deal_name'] != "") {
                $subdealname = "(" . $row_deal['od_sub_deal_name'] . ")";
            }
            $str .= '<table cellspacing="0" cellpadding="0" style="width: 100%; border-top: 1px solid rgb(221, 221, 221); background: none repeat scroll 0% 0% rgb(245, 245, 245); border-collapse: collapse; border-bottom: 1px solid rgb(221, 221, 221);">
			<tbody>
			<tr>
				<td style="padding: 10px; vertical-align: top; width: 52%;">
					<table width="100%" cellspacing="0" cellpadding="2" border="0">
																		<tbody>
																			<tr>
																		<td style="vertical-align: top; color: rgb(0, 171, 201); font-size: 16px; width: 40%;">' . t_lang("M_TXT_DEAL_NAME") . ':</td><td style="font-weight:bold;font-size:15px;color:#3c3d3d">' . $row_deal["deal_name" . $_SESSION["lang_fld_prefix"]] . ' ' . $subdealname . '<br/>' . $option . '</td>
                                                        </tr>';
            if ($row_deal['deal_type'] == 0) {
                $str .= '   <tr>
                                                            <td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_COUPON_CODE") . ':</td>
                                                                <td style=" font-size:15px;">' . trim($coupnCode, ',') . '</td>
                                                        </tr>';
            }
            $str .= '    <tr>
                                                          <td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_QUANTITY") . ':</td>
                                                                <td style=" font-size:15px;">' . $qty . '</td>
                                                        </tr>
                                                        <tr>
                        	<td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_DEAL_PRODUCT") . ' ' . t_lang("M_TXT_PRICE") . ':</td>
                            <td style=" font-size:15px;">' . CONF_CURRENCY . number_format($row_deal['od_deal_price'], 2) . CONF_CURRENCY_RIGHT . '</td>
                         </tr>
                                                        <tr><td style="color: rgb(0, 171, 201); font-size: 16px; width: 32%;">' . t_lang("M_TXT_EMAIL") . ':</td>
                                                                <td style=" font-size:15px;">' . $row_deal['user_email'] . '</td>
                                                        </tr>
                                                         <tr>
						   <td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_TAX_CHARGES") . ':</td>
								<td style=" font-size:15px;">' . CONF_CURRENCY . number_format($tax, 2) . '</td>
						</tr>';
            if ($shipping_charges > 0 && ($row_deal['deal_type'] == 1) && ($row_deal['deal_sub_type'] == 0)) {
                $str .= '  <tr>
                            <td style="color: rgb(0, 171, 201); width: 30%; font-size: 16px;">' . t_lang("M_TXT_SHIPPING_CHARGES") . ':</td>
							<td style=" font-size:15px;">' . CONF_CURRENCY . number_format($shipping_charges, 2) . '</td>
                           </tr>';
            }
            $str .= ' </tbody>
                                        </table>
                            </td>
               <td style="line-height: 20px; font-size: 14px; padding: 10px; vertical-align: top; width: 48%;">
				<table width="100%" cellspacing="5" cellpadding="2" border="0" bgcolor="#fff" style="padding: 0px 5px;">
                                            <tbody>
                                                    <tr>
                                              <td><b style="color: rgb(0, 171, 201); font-size: 16px; margin: 0px 0px 0px -2px;">' . $row_deal['company_name' . $_SESSION['lang_fld_prefix']] . '</b><br/>' . $row_deal['company_name' . $_SESSION['lang_fld_prefix']] . '<br/>
                                                            ' . $row_deal['company_address_line1' . $_SESSION['lang_fld_prefix']] . ',<br/>
                                                            ' . $row_deal['company_address_line2' . $_SESSION['lang_fld_prefix']] . '<br/>
                                                            ' . $row_deal['company_address_line3' . $_SESSION['lang_fld_prefix']] . ' ' . $row_deal['company_city' . $_SESSION['lang_fld_prefix']] . ' <br/>
                                                                  ' . $row_deal['company_state' . $_SESSION['lang_fld_prefix']] . ' ' . $row_deal['country_name' . $_SESSION['lang_fld_prefix']] . '<br/>' . '</td>
                                                                                                          </tr>
                                                    <tr>
                                                                                                                   <td>' . $row_deal['company_address_zip'] . '</td>
                                                    </tr>
                                                    <tr>
                                                            <td>
                                                                    <a href="mailto:' . $row_deal['company_email'] . '" style="text-decoration: none; color: rgb(207, 30, 54);">' . $row_deal['company_email'] . '</a></td>
                                                    </tr>
                                            </tbody>
                                    </table>
                            </td>
                    </tr>
            </tbody>
    </table>';
        }
    }
    if ($product && $row_deal['deal_sub_type'] == 0) {
        $str .= '<div style="background:#f5f5f5;">
					<p style="font-family:Arial; font-size:14px; border:1px solid #ddd; padding:10px; margin:0;">
						<span style="font-size:18px; font-weight:bold; margin-right: 15px; ">' . t_lang("M_TXT_SHIPPING_ADDRESS") . ': </span>' . $row_deal['shippingAddress'] . '</p>
				</div>';
    }
    $arr_replacements = array(
        'xxuser_namexx' => $row_deal['user_name'],
        'xxdeal_namexx' => $row_deal['deal_name' . $_SESSION['lang_fld_prefix']],
        'xxorderidxx' => trim($coupnCode, ','),
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
    if ($row_tpl['tpl_status'] == 1) {
        $headers = "";
        if ($mail_to_others) {
            $emails_to_notify = explode(',', CONF_DEAL_PURCHASE_NOTIFY_EMAIL_OTHERS);
            foreach ($emails_to_notify as $etn) {
                if (validateOtEmail($etn)) {
                    sendMail($etn, $subject . ' ' . $order_id, emailTemplateSuccess($message), $headers);
                }
            }
        } else {
            sendMail(CONF_SITE_OWNER_EMAIL, $subject . ' ' . $order_id, emailTemplateSuccess($message), $headers);
        }
    }
}

/* 	email template end for sending deal purchased notification to admin		 */

function validateOtEmail($email)
{
    return preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email);
}

function get_page_url_without_parameters()
{
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER['SCRIPT_NAME'];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER['SCRIPT_NAME'];
    }
    return $pageURL;
}

function calculateDealAmountPaidPayableToMerchant($company_id = 0, $deal_id = 0)
{
    define('CONF_CALC_TIP_DEAL_PAYABLE_TO_MERCHANT', 1);
    $srch_amt = new SearchBase('tbl_coupon_mark', 'cm');
    $srch_amt->addDirectCondition('cm.cm_status IN(' . CONF_MERCHANT_VOUCHER . ')');
    $srch_amt->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=cm.cm_deal_id', 'd');
    if (intval($company_id) > 0) {
        $srch_amt->addCondition('d.deal_company', '=', intval($company_id));
    }
    if (intval($deal_id) > 0) {
        $srch_amt->addCondition('d.deal_id', '=', intval($deal_id));
    }
    if (intval(CONF_CALC_TIP_DEAL_PAYABLE_TO_MERCHANT) === 1) {
        $srch_amt->addFld('IF(deal_tipped_at,1,0) as is_tipped');
        $srch_amt->addHaving('is_tipped', '=', 1);
    }
    $srch_amt->joinTable('tbl_orders', 'INNER JOIN', 'o.order_id=cm.cm_order_id AND order_payment_status=1', 'o');
    $srch_amt->joinTable('tbl_order_deals', 'INNER JOIN', 'od.od_order_id=o.order_id AND od.od_deal_id=cm.cm_deal_id', 'od');
    $srch_amt->joinTable('tbl_charity_history', 'LEFT OUTER JOIN', 'ch.ch_deal_id=cm.cm_deal_id', 'ch');
    $srch_amt->addFld('(od_deal_price - IFNULL(deal_bonus,0) - (IFNULL(deal_commission_percent,0)/100*od_deal_price) - IFNULL(ch_amount,0)) as calculated_deal_amount');
    $srch_amt->addMultipleFields(array('cm_counpon_no', 'deal_paid', 'deal_company', 'deal_id'));
    $srch_amt->doNotCalculateRecords();
    $srch_amt->addGroupBy('cm_counpon_no');
    echo $srch_amt->getQuery();
    exit; //Query ok
}

/* ------ Insert voucher number starts here -------- */

function insertVoucherNumbers()
{
    global $db;
    $srchVoucher = new SearchBase('tbl_order_deals', 'od');
    $srchVoucher->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id and o.order_payment_status=1', 'o');
    $srchVoucher->addMultipleFields(array('o.order_id', 'od_deal_id', 'o.order_date',
        'od_deal_price', 'od_qty', 'od_gift_qty', 'od_voucher_suffixes', 'od_cancelled_voucher_suffixes'));
    $rsVoucher = $srchVoucher->getResultSet();
    while ($row_voucher = $db->fetch($rsVoucher)) {
        $voucher_suffixes_from_od = $row_voucher['od_voucher_suffixes'];
        if (strlen($row_voucher['od_cancelled_voucher_suffixes']) > 3) {
            $voucher_suffixes_from_od .= strlen($row_voucher['od_voucher_suffixes'] > 1) ? ', ' : '';
            $voucher_suffixes_from_od .= $row_voucher['od_cancelled_voucher_suffixes'];
        }
        $od_voucher_suffixes = explode(', ', $voucher_suffixes_from_od);
        foreach ($od_voucher_suffixes as $voucher) {
            $voucher_id = $row_voucher['order_id'];
            $deal_id = $row_voucher['od_deal_id'];
            $db->query("insert IGNORE into tbl_coupon_mark(cm_order_id,cm_counpon_no,cm_status,cm_deal_id) values('$voucher_id','$voucher','0','$deal_id')");
        }
    }
}

/*   ------ Insert voucher number End Here -------- */
/* ------ Check favorite deal exists or not  -------- */

function IslikeDeal($deal_id)
{
    $logged_user_id = isset($_SESSION['logged_user']['user_id']) ? $_SESSION['logged_user']['user_id'] : 0;
    if ($logged_user_id == 0) {
        return false;
    }
    $srchRcd = new SearchBase('tbl_users_favorite_deals');
    //	$srchRcd->addCondition('company_id','=',$company_id);
    $srchRcd->addCondition('deal_id', '=', $deal_id);
    if ($logged_user_id) {
        $srchRcd->addCondition('user_id', '=', $_SESSION['logged_user']['user_id']);
    }
    $rs = $srchRcd->getResultSet();
    return $rs->num_rows;
}

function fetchParentCategories($cat_parent_id = 0)
{
    $srch = new SearchBase('tbl_deal_categories', 'm');
    $srch->joinTable('tbl_deal_categories', 'LEFT OUTER JOIN', 'm.cat_parent_id = p.cat_id', 'p');
    $srch->addMultipleFields(array('m.cat_id', 'm.cat_parent_id', 'm.cat_code', 'm.cat_name' . $_SESSION['lang_fld_prefix'], "CONCAT(CASE WHEN m.cat_parent_id = 0 THEN '' ELSE LPAD(p.cat_display_order, 7, '0') END, LPAD(m.cat_display_order, 7, '0')) AS display_order"));
    $srch->addCondition('m.cat_parent_id', '=', $cat_parent_id);
    $srch->addOrder('display_order');
    return $srch;
}

/* ------ For fetching sub category deal used in backend also -------- */

function fetchsubCategory($parent_id, $selCategory = [], $frontend = true, $pagename = '', $limit = 5)
{
    $str = '';
    global $db;
    $categoryArray = $db->query("select cat_id,cat_name" . $_SESSION['lang_fld_prefix'] . " from tbl_deal_categories
	where cat_parent_id = {$parent_id} order by cat_display_order ");
    $rows = $db->fetch_all_assoc($categoryArray);
    if (!empty($rows)) {
        if ($frontend) {
            $str .= "<ul class=''>";
            $count = 1;
            foreach ($rows as $key1 => $val1) {
                if ($count > $limit) {
                    if ($pagename != "") {
                        $url = friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $parent_id . '&type=side');
                        $str .= '<li class="parent_id_' . $parent_id . ' seemore" ><a href="' . $url . '">' . t_lang("M_TXT_SEE_MORE") . '</a>';
                    } else {
                        $str .= '<li class="parent_id_' . $parent_id . ' seemore" ><a href="?cat=' . $parent_id . '" >' . t_lang("M_TXT_SEE_MORE") . '</a>';
                    }
                    $str .= '</li>';
                    $str .= "</ul>";
                    return $str;
                }
                if ($pagename != "") {
                    $url = friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $key1 . '&type=side');
                    $str .= '<li class="parent_id_' . $key1 . '" ><a href="' . $url . '">' . $val1 . '</a>';
                } else {
                    $str .= '<li class="parent_id_' . $key1 . '" ><a href="?cat=' . $key1 . '" >' . $val1 . '</a>';
                }
                $str .= '</li>';
                $count++;
            }
            $str .= "</ul>";
        }
        if (!$frontend) {
            $str .= "<ul>";
            foreach ($rows as $key1 => $val1) {
                $subCat = fetchsubCategory($key1, $selCategory, false);
                if (strlen($subCat) > 0) {
                    $str .= '<li class="parent_id_' . $key1 . ' subParent" >' . $val1;
                    $str .= $subCat;
                } else {
                    $selected = "";
                    if (in_array($key1, $selCategory)) {
                        $selected = 'checked="checked"';
                        unset($selCategory[$key1]);
                    }
                    $str .= '<li class="parent_id_' . $key1 . '" ><input type="checkbox" id="deal_categories" ' . $selected . ' name="deal_categories[]" value="' . $key1 . '"/><label>' . $val1 . '</label>';
                }
                $str .= '</li>';
            }
            $str .= "</ul>";
        }
        return $str;
    } else {
        return false;
    }
}

/*   ------ For fetching sub category deal End Here -------- */

function fetchCategory($parent_id, $arr_subscribed = [], $cityId = '', $code = '')
{
    $str = '';
    global $db;
    $categoryArray = $db->query("select cat_id,cat_name" . $_SESSION['lang_fld_prefix'] . " from tbl_deal_categories
	where cat_parent_id = {$parent_id} order by cat_display_order ");
    $rows = $db->fetch_all_assoc($categoryArray);
    if (!empty($rows)) {
        $str .= "<ul class='list__vertical'>";
        foreach ($rows as $key1 => $val1) {
            $subCat = fetchCategory($key1, $arr_subscribed, $cityId);
            //   $str.= '<li class="parent_id_' . $key1 . '" ><input type="checkbox" id="deal_categories" ' . $selected . ' name="deal_categories[]" value="' . $key1 . '"/><label>' . $val1 . '</label>';
            $str .= '<li><label class="checkbox"><input type="checkbox" value="1"  onClick="if(this.checked){ return updateCatsubs(' . $cityId . ',' . $key1 . ')}else{ return insertCatsubs(' . $cityId . ',' . $key1 . ')}" name="subscitycat_' . $cityId . '_' . $key1 . '"' . ((in_array($key1, $arr_subscribed)) ? ' checked="checked"' : '') . '> <i class="input-helper"></i>' . $val1 . '</label>';
            if (strlen($subCat) > 0) {
                $str .= $subCat;
            }
            $str .= '</li>';
        }
        $str .= "</ul>";
    }
    return $str;
}

function fetchCompanyRepIds()
{
    global $db;
    $repIds = [];
    $srch = new SearchBase('tbl_companies', 'c');
    $srch->addMultipleFields(array('c.company_rep_id'));
    $srch->addCondition('c.company_rep_id', '!=', 0);
    $srch->addGroupBy('c.company_rep_id');
    $rep_ids = $srch->getResultSet();
    if (!empty($rep_ids)) {
        foreach ($rep_ids as $key => $value) {
            $repIds[] = $value['company_rep_id'];
        }
    }
    return $repIds;
}

function fetchTotalRepIds()
{
    global $db;
    $repIds = [];
    $srch = new SearchBase('tbl_representative', 'tr');
    $srch->addMultipleFields(array('tr.rep_id'));
    $srch->addCondition('tr.rep_id', '!=', 0);
    $srch->addGroupBy('tr.rep_id');
    $rep_ids = $srch->getResultSet();
    if (!empty($rep_ids)) {
        foreach ($rep_ids as $key => $value) {
            $repIds[] = $value['rep_id'];
        }
    }
    return $repIds;
}

function fetchAffiliatedByUsersIds()
{
    global $db;
    $affIds = [];
    $srch = new SearchBase('tbl_users', 'u');
    $srch->addMultipleFields(array('u.user_affiliate_id'));
    $srch->addCondition('u.user_affiliate_id', '!=', 0);
    $srch->addGroupBy('u.user_affiliate_id');
    $rep_ids = $srch->getResultSet();
    if (!empty($rep_ids)) {
        foreach ($rep_ids as $key => $value) {
            $affIds[] = $value['user_affiliate_id'];
        }
    }
    return $affIds;
}

function fetchAffiliatedUsersIds()
{
    global $db;
    $affIds = [];
    $srch = new SearchBase('tbl_affiliate', 'a');
    $srch->addMultipleFields(array('a.affiliate_id'));
    $srch->addGroupBy('a.affiliate_id');
    $rep_ids = $srch->getResultSet();
    if (!empty($rep_ids)) {
        foreach ($rep_ids as $key => $value) {
            $affIds[] = $value['affiliate_id'];
        }
    }
    return $affIds;
}

function insertsubscatCity($sub_id, &$error = '')
{
    global $db;
    $srch1 = new SearchBase('tbl_deal_categories');
    $srch1->addOrder('cat_display_order');
    $srch1->addMultipleFields(array('cat_id'));
    $srch1->addCondition('cat_active', '=', 1);
    $srch1->doNotLimitRecords();
    $rs1 = $srch1->getResultSet();
    $arr_cats = $db->fetch_all($rs1);
    if (!empty($arr_cats)) {
        foreach ($arr_cats as $key => $val) {
            if (!$db->insert_from_array('tbl_newsletter_category', array('nc_subs_id' => $sub_id, 'nc_cat_id' => $val['cat_id']))) {
                $error = $db->getError();
                return false;
            }
        }
    }
    return true;
}

/* -------MAILCHIMP< API FUNCTIONS START FROM HERE --------- */
if (!defined('CONF_EMAIL_SENDING_METHOD_PROMOTIONAL') || CONF_EMAIL_SENDING_METHOD_PROMOTIONAL != 1) {
    if (!defined('CONF_MAILCHIMP_LIST_ID') || strlen(trim(CONF_MAILCHIMP_LIST_ID)) < 2) {
        require_once realpath(dirname(__FILE__) . '/mailchimp/Mailchimp.php');
    }
}

function subscribeToMailChimp($data = [])
{
    global $db;
    global $msg;
    if (!filter_var($data['sub_email'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (!defined('CONF_EMAIL_SENDING_METHOD_PROMOTIONAL') || CONF_EMAIL_SENDING_METHOD_PROMOTIONAL != 1) {
        return false;
    }
    if (!defined('CONF_MAILCHIMP_LIST_ID') || strlen(trim(CONF_MAILCHIMP_LIST_ID)) < 2) {
        return false;
    }
    $merge_vars = "";
    $chimp = new Mailchimp(CONF_MAILCHIMP_API_KEY);
    try {
        $user_info = $chimp->lists->subscribe(trim(CONF_MAILCHIMP_LIST_ID), array('email' => $data['sub_email']), $merge_vars, 'html', false, true);
    } catch (Exception $e) {
        $msg->addError($e->getMessage());
        return false;
    }
    $row = getRecords('tbl_mailchimp_user_desc', array('mc_sub_email' => $user_info['email']), 'first');
    if ($row) {
        return true;
    }
    if (!$db->insert_from_array('tbl_mailchimp_user_desc', array('mc_sub_email' => $user_info['email'], 'mc_euid' => $user_info['euid'], 'mc_leid' => $user_info['leid']))) {
        $msg->addError($db->getError());
        return false;
    }
    return true;
}

function fetchCityname($city)
{
    global $db;
    if (!is_numeric($city)) {
        return false;
    }
    $city = intval($city);
    $srch = new SearchBase('tbl_cities');
    $srch->addCondition('city_id', '=', $city);
    $srch->addCondition('city_active', '=', 1);
    $srch->addCondition('city_deleted', '=', 0);
    $srch->addMultipleFields(array('city_id', 'city_name'));
    $rs = $srch->getResultSet();
    if (!$row = $db->fetch($rs)) {
        return false;
    }
    return $row['city_name'];
}

function fetchCatCode($categoryId)
{
    global $db;
    $srch = new SearchBase('tbl_deal_categories', 'dc');
    $srch->addCondition('dc.cat_id', '=', $categoryId);
    $srch->addMultipleFields(array('dc.cat_code'));
    $rs = $srch->getResultSet();
    $code = $db->fetch($rs);
    return $code['cat_code'];
}

function getTotalProductsInCart($cart_items)
{
    global $db;
    if (!is_array($cart_items)) {
        return 0;
    }
    $srch = new SearchBase('tbl_deals');
    $srch->addCondition('deal_id', 'IN', $cart_items);
    $srch->addCondition('deal_type', '=', 1);
    $srch->addCondition('deal_sub_type', '=', 0);
    $srch->addFld('COUNT(`deal_id`) as products');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitrecords();
    $rs = $srch->getResultSet();
    if (!$row = $db->fetch($rs)) {
        return 0;
    }
    return (intval($row['products']));
}

function getStateAssociativeList()
{
    global $db;
    $stateList = $db->query("select state_id, state_name from tbl_states where state_status = 'A'");
    return ($db->fetch_all_assoc($stateList));
}

function getCountryAssociativeList()
{
    global $db;
    $stateList = $db->query("select country_id, country_name from tbl_countries where country_status = 'A'");
    return ($db->fetch_all_assoc($stateList));
}

function companyReviewObj($page, $comapnyId, $pagesize)
{
    global $db;
    $srch = new SearchBase('tbl_reviews', 'r');
    $srch->joinTable('tbl_users', 'INNER JOIN', 'u.user_id = r.reviews_user_id', 'u');
    $srch->addCondition('reviews_type', '=', 2);
    $srch->addCondition('reviews_approval', '=', 1);
    $srch->addCondition('reviews_company_id', '=', $comapnyId);
    $srch->addOrder('reviews_added_on', 'desc');
    $page = is_numeric($page) ? $page : 1;
    $pagesize = $pagesize;
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    return $srch;
}

function getcompanyReviewReply($reviewsId)
{
    global $db;
    $srch = new SearchBase('tbl_reviews', 'r');
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'c.company_id = r.reviews_company_id', 'c');
    $srch->addMultipleFields(array('reviews_id', 'reviews_reviews', 'reviews_rating', 'reviews_company_id', 'reviews_user_id', 'reviews_added_on', 'company_name as user_name'));
    $srch->addCondition('reviews_type', '=', 2);
    $srch->addCondition('reviews_approval', '=', 1);
    $srch->addCondition('reviews_parent_id', '=', $reviewsId);
    $rs_listing = $srch->getResultSet();
    $reviewsRow = $db->fetch($rs_listing);
    return $reviewsRow;
}

function getDealReviewReply($reviewsId)
{
    global $db;
    $srch = new SearchBase('tbl_reviews', 'r');
    $srch->joinTable('tbl_companies', 'INNER JOIN', 'c.company_id = r.reviews_deal_company_id', 'c');
    $srch->addMultipleFields(array('reviews_id', 'reviews_reviews', 'reviews_rating', 'reviews_company_id', 'reviews_user_id', 'reviews_added_on', 'company_name as user_name'));
    $srch->addCondition('reviews_type', '=', 1);
    $srch->addCondition('reviews_approval', '=', 1);
    $srch->addCondition('reviews_parent_id', '=', $reviewsId);
    $rs_listing = $srch->getResultSet();
    $reviewsRow = $db->fetch($rs_listing);
    return $reviewsRow;
}

function showReviews($page, $comapnyId, $pagination = true)
{
    $str = "";
    global $db;
    $pagesize = 3;
    if ($pagination == 'true') {
        $pagesize = 9;
    }
    $srch = companyReviewObj($page, $comapnyId, $pagesize);
    $rs_listing = $srch->getResultSet();
    $pagestring = '';
    $pages = $srch->pages();
    $pageno = $page + 1;
    $click = "onclick=showReviews($pageno,$comapnyId);";
    if ($pages > $page) {
        $pagestring .= '<div class="aligncenter loadmore">
                <a class="themebtn themebtn--large themebtn--grey" href="javascript:void(0);" ' . $click . '>' . t_lang('M_TXT_LOAD_MORE') . '</a>
            </div>';
    }
    while ($reviewsRow = $db->fetch($rs_listing)) {
        $reviewsRow['reviews_reviews'] = htmlentities($reviewsRow['reviews_reviews'], ENT_QUOTES, 'UTF-8');
        $avatar = '';
        if ($reviewsRow['user_avatar'] == "") {
            $avatar = CONF_WEBROOT_URL . 'images/defaultLogo.jpg';
        } else {
            $avatar = CONF_WEBROOT_URL . 'images-crop.php?id=' . $reviewsRow['user_id'] . '&mode=userImages';
        }
        $str .= '<div class="listrepeated">';
        $str .= '<aside class="grid_1">
            <figure class="avtar">' . substr($reviewsRow['user_name'], 0, 1) . '</figure>
        </aside>';
        $str .= '<aside class="grid_2">';
        $str .= '<div class="ratings star-ratings"> <ul>';
        for ($i = 0; $i < $reviewsRow['reviews_rating']; $i++) {
            $str .= '<li><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
        }
        for ($j = 0; $j < 5 - $reviewsRow['reviews_rating']; $j++) {
            $str .= '<li><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
        }
        $str .= '</ul></div><h3 class="name">' . $reviewsRow['user_name'] . ' ' . htmlentities($reviewsRow['user_lname']) . ' <span class="datetxt">' . date("F j, Y  g:i a", strToTime($reviewsRow['reviews_added_on'])) . '</span></h3>';
        $str .= ' <div class="reviewsdescription"><p>' . ($reviewsRow['reviews_reviews']) . '</p></div>';
        $str .= '</aside></div> ';
        $replyRs = $db->query("select * from tbl_reviews as r INNER JOIN tbl_companies as c where c.company_id = r.reviews_company_id and reviews_type=2 AND reviews_approval=1 AND reviews_parent_id=" . $reviewsRow['reviews_id']);
        $replyRow = $db->fetch($replyRs);
        $replyRow['reviews_reviews'] = htmlentities($replyRow['reviews_reviews'], ENT_QUOTES, 'UTF-8');
        if ($db->total_records($replyRs) > 0) {
            $company_logo = '';
            if ($replyRow['company_logo'] == "") {
                $company_logo = CONF_WEBROOT_URL . 'images/defaultLogo.jpg';
            } else {
                $company_logo = CONF_WEBROOT_URL . 'images-crop.php?id=' . $replyRow['company_id'] . '&mode=companyLogo';
            }
            $str .= '<div class="listrepeated replied">';
            $str .= '<div class="grid_1">
            <figure class="avtar">' . substr($replyRow['company_name'], 0, 1) . '</figure>
            </div>';
            $str .= '<div class="grid_2">';
            $str .= '<h3 class="name"> ' . $replyRow['company_name '] . '<span class="datetxt">"' . date("F j, Y g:i a", strToTime($replyRow['reviews_added_on'])) . '"  </span></h3>';
            $str .= '<div class="reviewsdescription"><p> ' . htmlentities($replyRow['reviews_reviews'], ENT_QUOTES, 'UTF-8') . '</p></div></div></div>';
        }
    }
    if ($pagination == 'true') {
        $str .= $pagestring;
    }
    return $str;
}

function merchantDealsObj($page, $comapnyId, $pagesize)
{
    $srch = fetchDealSearchObj();
    $srch->addCondition('deal_company', '=', $comapnyId);
    $srch->addCondition('d.deal_instant_deal', '!=', 1);
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addHaving('mysql_func_sold', '<', 'mysql_func_(deal_max_coupons - deal_min_buy)', 'AND', true);
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    $srch->addMultipleFields(array('d.deal_id', 'deal_max_coupons', 'deal_min_buy'));
    return $srch;
}

function featuredDeal($deal_id = 0)
{
    global $db;
    global $msg;
    $categoryList = $db->query("select * from tbl_deal_categories where cat_is_featured =1 order by cat_parent_id ");
    $cat_tab_html = '<h2 class="section__title">' . t_lang('M_TXT_FEATURED_DEALS_CATEGORIES') . '</h2><div class="tabspanel"><ul class="tabs__flat normaltabs center">';
    $cat_deal_box_html = '<div class="tabspanel__container listing__items">';
    $count = 0;
    $catCount = 0;
    $show_tabs = false;
    while ($row = $db->fetch($categoryList)) {
        if ($row['cat_is_featured'] == 1) {
            $catCode = fetchCatCode(intval($row['cat_id']));
            $srch = new SearchBase('tbl_deal_to_category', 'dtc');
            $srch->joinTable('tbl_deal_categories', 'INNER JOIN', 'dtc.dc_cat_id=c.cat_id ', 'c');
            $srch->joinTable('tbl_deals', 'INNER JOIN', 'd.deal_id=dtc.dc_deal_id and d.deal_status<2 and d.deal_deleted=0 and d.deal_complete=1 ', 'd');
            $srch->joinTable('tbl_cities', 'INNER JOIN', 'tc.city_id=d.deal_city', 'tc');
            $srch->addCondition('d.deal_id', '!=', $deal_id);
            $srch->addCondition('c.cat_code', ' LIKE ', $catCode . '%');
            applycityBasedDealCondition($srch);
            $srch->addGroupBy('d.deal_id');
            $srch->addOrder('d.deal_id', 'desc');
            $srch->setPageSize(4);
            $rs = $srch->getResultSet();
            $countDeal = 0;
            if ($srch->recordCount() <= 0) {
                continue;
            }
            $count++;
            if ($catCount == 5) {
                break;
            }
            $catCount++;
            $show_tabs = true;
            if ($count == 1) {
                $class = "active first";
            } else {
                $class = "";
            }
            $cat_tab_html .= '<li><a href="javascript:void(0);" onclick="getFeaturedDeals(' . $row['cat_id'] . ')" class= "' . $class . '" rel="tab' . $count . '"> ' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . '</a></li>';
            if ($count == 1) {
                $style = 'display:block';
            } else {
                $style = 'display:none';
            }
            $cat_deal_box_html .= '<span class="togglehead" rel="tab' . $count . '" onclick="getFeaturedDeals(' . $row['cat_id'] . ')" >' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . '</span><div id="tab' . $count . '" class="tabspanel__content dealsContainer" style="' . $style . '">';
            $cat_deal_box_html .= '</div>';
        }
    }
    $cat_deal_box_html .= '</div>';
    $cat_tab_html .= '</ul>';
    $cat_deal_box_html .= '</div>';
    if ($show_tabs) {
        return $cat_tab_html . $cat_deal_box_html;
    }
}

function renderDealView($fname, $deal = [], $return = true)
{
    ob_start();
    extract($deal);
    include CONF_VIEW_PATH . $fname;
    $contents = ob_get_clean();
    if ($return == true) {
        return $contents;
    } else {
        echo $contents;
    }
}

function fetchTopProducts($limit, $city = 0)
{
    global $db;
    $srch = fetchDealSearchObj();
    applycityBasedDealCondition($srch, $city);
    $srch->addCondition('d.deal_status', '=', 1);
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->joinTable('tbl_order_deals', 'inner JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_cities', 'LEFT OUTER JOIN', 'd.deal_city=c.city_id', 'c');
    $srch->addGroupBy('d.deal_id');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addOrder('sold', 'desc');
    $srch->addOrder('deal_city', 'desc');
    $srch->setPageSize($limit);
    $srch->addMultipleFields(array('d.*'));
    $rs = $srch->getResultSet();
    if ($srch->recordCount() == 0) {
        return false;
    } else {
        return $rs;
    }
}

function fetchTopCategories($pagesize = 10)
{
    global $db;
    $srch = fetchDealSearchObj();
    $srch->joinTable('tbl_deal_to_category', 'LEFT JOIN', 'dtc.dc_deal_id=d.deal_id ', 'dtc');
    $srch->joinTable('tbl_deal_categories', 'LEFT JOIN', "dtc.dc_cat_id=c.cat_id ", 'c');
    $srch->joinTable('tbl_deal_categories', 'LEFT JOIN', " c.cat_parent_id =dc.cat_id", 'dc');
    $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'INNER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold ");
    $srch->addMultipleFields(array("dc.cat_id,  dc.`cat_parent_id`"));
    $srch->addFld('dc.cat_name' . $_SESSION['lang_fld_prefix']);
    $srch->addCondition('dc.cat_id', '!=', '');
    applycityBasedDealCondition($srch);
    $srch->addOrder('sold', 'desc');
    $srch->addGroupBy('dc.cat_id');
    $srch->removGroupBy('d.deal_id');
    $srch->setPageSize($pagesize);
    $rs = $srch->getResultSet();
    #echo $srch->getQuery();die;
    if ($srch->recordCount() == 0) {
        return false;
    } else {
        return $rs;
    }
}

function fetchTopVendors($city, $pagesize = 4)
{
    global $db;
    $srch = fetchDealSearchObj();
    applycityBasedDealCondition($srch, $city);
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_companies', 'LEFT OUTER JOIN', 'd.deal_company=c.company_id', 'c');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_countries', 'INNER JOIN', 'country.country_id=c.company_country', 'country');
    $srch->joinTable('tbl_states', 'LEFT JOIN', 'c.company_state=st.state_id', 'st');
    $srch->joinTable('tbl_company_addresses', 'INNER JOIN', 'c.company_id=ca.company_id', 'ca');
    $srch->joinTable('tbl_reviews', 'LEFT JOIN', 'c.company_id=r.reviews_company_id and r.reviews_type=2 AND r.reviews_approval=1 and reviews_user_id !=0', 'r');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addOrder('sold', 'desc');
    $srch->setPageSize($pagesize);
    $srch->addMultipleFields(array('count(DISTINCT(r.reviews_id))as reviews', 'c.company_name', 'c.company_city' . $_SESSION['lang_fld_prefix'], 'ca.*', 'country.country_name' . $_SESSION['lang_fld_prefix']));
    $srch->addMultipleFields(array('c.*', 'st.state_name' . $_SESSION['lang_fld_prefix']));
    $srch->removGroupBy('d.deal_id');
    $srch->addGroupBy('c.company_id');
    $rs = $srch->getResultSet();  //echo 'hello<pre>';print_r($srch->getQuery()); exit;
    return $rs;
}

function applycityBasedDealCondition($srch, $cityId = 0)
{
    if (empty($cityId)) {
        $cityId = $_SESSION['city'];
    }
    if ($cityId != 0) {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('deal_city', '=', $cityId, 'OR');
        $cnd->attachCondition('deal_city', '=', 0);
    }
}

function fetchCategories($type = "both", $catId = 0, $display_order = false)
{
    $srch = new SearchBase('tbl_deal_categories', 'c');
    $srch->joinTable('tbl_deal_categories', 'INNER JOIN', "dc.cat_code LIKE CONCAT(c.cat_code, '%')  AND c.cat_parent_id =" . $catId, 'dc');
    $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'dtc.dc_cat_id=dc.cat_id ', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
    $srch->addCondition('c.cat_parent_id', '=', $catId);
    if ($type == "deal") {
        $srch->addCondition('d.deal_type', '=', 0);
    } elseif ($type == "product") {
        $srch->addCondition('d.deal_type', '=', 1);
    }
    applycityBasedDealCondition($srch);
    if (true === $display_order) {
        $srch->addOrder('c.cat_display_order', 'asc');
    } else {
        $srch->addOrder('c.cat_name', 'asc');
    }
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_status', '=', 1);
    $srch->addCondition('d.deal_complete', '=', 1);
    $srch->addFld('c.cat_id');
    $srch->addFld('c.cat_name' . $_SESSION['lang_fld_prefix']);
    $srch->addGroupBy('c.cat_id');
    $rs = $srch->getResultSet();
    return $rs;
}

function favoriteDealCount()
{
    $logged_user_id = isset($_SESSION['logged_user']['user_id']) ? $_SESSION['logged_user']['user_id'] : 0;
    if ($logged_user_id == 0) {
        return false;
    }
    $srch = new SearchBase('tbl_users_favorite_deals', 'uf');
    $srch->addCondition('uf.user_id', '=', $_SESSION['logged_user']['user_id']);
    $srch->joinTable('tbl_deals', 'LEFT OUTER JOIN', 'd.deal_id=uf.deal_id', 'd');
    //$srch->addCondition('deal_status', '=', 1);
    $srch->addCondition('deal_complete', '=', 1);
    $srch->addCondition('deal_deleted', '=', 0);
    $rs = $srch->getResultSet();
    return $rs->num_rows;
}

function fetchfavUnfavIconHtml($deal_id)
{
    $icon_html = '';
    $icon_html .= '<span class="likeDeal_' . $deal_id . '"  style="float:right;" class="deal_favorite">';
    $result = IslikeDeal($deal_id);
    if ($result) {
        $icon_html .= '<span class="heart active">
            <a title="' . t_lang("M_TXT_REMOVE_FROM_FAVOURITES") . '" class="heart__link" onclick="likeDeal(' . $deal_id . ' , \'unlike\')"  href="javascript:void(0);"></a>
            <span class="heart__txt">"' . t_lang("M_TXT_REMOVE_FROM_FAVOURITES") . '"</span>
        </span>';
    } if (!($result) || $result == 0) {
        $icon_html .= '<span class="heart ">
            <a  class="heart__link"  onclick="likeDeal(' . $deal_id . ' , \'like\')"  href="javascript:void(0);"></a>
            <span class="heart__txt">"' . t_lang("M_TXT_ADD_TO_FAVOURITES") . '"</span>
        </span>';
    }
    $icon_html .= '</span>';
    return $icon_html;
}

function fetchDealObj()
{
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('d.deal_status', '<', 2);
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_complete', '=', 1);
    return $srch;
}

function fetchDealSearchObj()
{
    $srch = fetchDealObj();
    $srch->addGroupBy('d.deal_id');
    return $srch;
}

function fetchTopRecentProducts($limit)
{
    global $db;
    $srch = fetchDealSearchObj();
    applycityBasedDealCondition($srch);
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->addOrder('deal_city', 'desc');
    $srch->addOrder('d.deal_addedon', 'desc');
    $srch->setPageSize($limit);
    $srch->addMultipleFields(array('d.*'));
    $rs = $srch->getResultSet();
    return $db->fetch_all($rs);
}

function getPrevNextElemForTopSellingProducts($deal_id, $limit)
{
    global $db;
    $srch = fetchDealSearchObj();
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_cities', 'LEFT OUTER JOIN', 'd.deal_city=c.city_id', 'c');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addOrder('sold', 'desc');
    $srch->setPageSize($limit);
    $srch->addMultipleFields(array('d.*'));
    $result = $db->fetch_all($db->query($srch->getQuery()));
    foreach ($result as $val) {
        $dealarray[] = $val['deal_id'];
    }
    $key = array_search($deal_id, $dealarray); // $key = 2;
    $array['prev'] = $dealarray[$key - 1];
    $array['next'] = $dealarray[$key + 1];
    return $array;
}

function getprevNextRecentProduct($deal_id, $limit)
{
    global $db;
    $srch = fetchDealSearchObj();
    $srch->addCondition('d.deal_status', '=', 1);
    $srch->addCondition('d.deal_type', '=', 1);
    /* $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty ELSE 0 END) AS sold"); */
    $srch->addOrder('d.deal_addedon', 'desc');
    $srch->setPageSize($limit);
    $srch->addMultipleFields(array('d.*'));
    $rs = $srch->getResultSet();
    $result = $db->fetch_all($rs);
    foreach ($result as $val) {
        $dealarray[] = $val['deal_id'];
    }
    $key = array_search($deal_id, $dealarray); // $key = 2;
    $array['prev'] = $dealarray[$key - 1];
    $array['next'] = $dealarray[$key + 1];
    return $array;
}

function fetchQuickViewHtml($deal_id, $type = "normal", $limit = 2000)
{
    global $db;
    if ($type == "topSelling") {
        $preNext = getPrevNextElemForTopSellingProducts($deal_id, $limit);
    } else if ($type == "topRecentProduct") {
        $preNext = getprevNextRecentProduct($deal_id, $limit);
    }
    /** used for Carousel * */
    $prevclick = "fetchQuickViewHtmlJS(" . $preNext['prev'] . ",'$type ','$limit')";
    $nextclick = "fetchQuickViewHtmlJS(" . $preNext['next'] . ",'$type','$limit')";
    if ($preNext['prev'] != "") {
        $str .= '<div class="ctrl-btn next" onclick="' . $prevclick . '" ><img alt="" src="' . CONF_WEBROOT_URL . 'images/arrow-before.png"></div>';
        $prevSearch = "fetchQuickViewHtmlJS(" . $preNext['prev'] . ",'$type ','$limit')";
    }
    if ($preNext['next'] != "") {
        $str .= '<div class="ctrl-btn before" onclick="' . $nextclick . '" ><img alt="" src="' . CONF_WEBROOT_URL . 'images/arrow-nxt.png"></div>';
        $nextSearch = "fetchQuickViewHtmlJS(" . $preNext['next'] . ",'$type','$limit')";
    }
    /** end for Carousel * */
    if ($type == 'search' || $type == 'deal') {
        $prevSearch = "fetchPrevious('$deal_id')";
        $nextSearch = "fetchNext('$deal_id')";
    }
    $str = "";
    $objDeal = new DealInfo($deal_id, false);
    $deal = $objDeal->getFields();
    $deal_id = $deal_id;
    $array = array('deal' => $deal, 'deal_id' => $deal_id);
    /** popup section start here  <span class="items__count"> 15 out of 10737</span> * */
    $str .= '<div class="popup hide__mobile hide__tab">
            <div class="popup__content" id="' . $deal_id . '">';
    /** item slide start here * */
    $str .= '<section class="item__details">
                <a class="link__close" href="javascript:void(0)" onclick="closeDiv();"></a>';
    $str .= renderDealView('deal-quick-view.php', $array);
    $str .= '</section>';
    /** item slide end here * */
    $str .= '</div>
    </div>';
    /** popup section end here * */
    return $str;
}

function getFeaturedDeal($catId, $page, $pagesize)
{
    global $db;
    $srch = new SearchBase('tbl_deal_categories', 'c');
    $srch->joinTable('tbl_deal_categories', 'INNER JOIN', "c1.cat_code LIKE CONCAT(c.cat_code, '%')", 'c1');
    $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'dtc.dc_cat_id=c1.cat_id ', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
    $srch->addCondition('c.cat_id', '=', $catId);
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_status', '<', 2);
    if ($is_featured) {
        $srch->addCondition('d.deal_featured', '=', 1);
        $srch->addFld('d.deal_id');
    }
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    $srch->addGroupBy('d.deal_id');
    $rs = $srch->getResultSet();
    return $db->fetch($rs);
}

function fetchfilterCriteriaofProduct($catId = 0, $type = 'CAT')
{
    global $db;
    $srch = new SearchBase('tbl_deal_categories', 'c');
    $srch->joinTable('tbl_deal_categories', 'INNER JOIN', "dc.cat_code LIKE CONCAT(c.cat_code, '%') ", 'dc');
    $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'dtc.dc_cat_id=dc.cat_id ', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
    $srch->addCondition('c.cat_id', '=', $catId);
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->addOrder('c.cat_name', 'asc');
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_status', '=', 1);
    $srch->addCondition('d.deal_complete', '=', 1);
    if ($type == 'color') {
        $srch->joinTable('tbl_deal_option', 'LEFT JOIN', 'tdo.deal_id=d.deal_id ', 'tdo');
        $srch->joinTable('tbl_deal_option_value', 'LEFT JOIN', 'dov.deal_option_id=tdo.deal_option_id ', 'dov');
        $srch->joinTable('tbl_options', 'INNER JOIN', 'tdo.option_id=o.option_id and o.is_deleted =0 ', 'o');
        $srch->joinTable('tbl_option_values', 'INNER JOIN', 'dov.option_value_id=ov.option_value_id', 'ov');
    }
    if ($type == 'Category') {
        $groupBy = 'dc.cat_id';
        $findFld = array('dc.cat_id,dc.cat_name');
    } else {
        $groupBy = 'dov.option_value_id';
        $findFld = array('o.option_id, o.option_name, ov.option_value_id,ov.name,count( distinct(dov.deal_id))as total');
    }
    $srch->addMultipleFields($findFld);
    $srch->addGroupBy($groupBy);
    $rs = $srch->getResultSet();
    $total_count = $srch->recordCount();
    $i = 0;
    if ($type == 'Category') {
        $rest_count = 0;
        $str .= '<ul id="category">';
        while ($row = $db->fetch($rs)) {
            $url = friendlyUrl(CONF_WEBROOT_URL . 'products-featured.php?productcat=' . $row['cat_id'] . '&type=side');
            $str .= '<li onClick="addRemoveClass(this);" class="" id="category_' . $row['cat_id'] . '" > <a href="javascript:void(0);">' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . '';
            $str .= '<input type="radio" value="' . $row['cat_id'] . '" name="category" style="display:none;"> </a></li>';
            $i++;
            if ($i == 10) {
                $rest_count = $total_count - 10;
                break;
            }
        }
        $str .= '</ul>';
        if ($rest_count > 0) {
            $str .= '<div class="more-cat-links"><a href="#"><span class="more-products">' . $rest_count . 'More Products</span> <span class="more-add"> + </span></a></div> ';
        }
        return $str;
    } else {
        while ($row = $db->fetch($rs)) {
            $attribute[$row['option_id']][$row['option_value_id']] = $row['name'] . '_' . $row['total'];
        }
        return $attribute;
    }
}

function productSearch($condition = [], $page = 1, $pagesize = 9)
{
    global $msg;
    global $db;
    $catId = $condition['category'];
    $srch = new SearchBase('tbl_deal_categories', 'c');
    $srch->joinTable('tbl_deal_categories', 'INNER JOIN', "dc.cat_code LIKE CONCAT(c.cat_code, '%')  AND c.cat_id =$catId", 'dc');
    $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'dtc.dc_cat_id=dc.cat_id ', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
    $srch->addCondition('c.cat_id', '=', $condition['category']);
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_status', '=', 1);
    $srch->addCondition('d.deal_complete', '=', 1);
    if (!empty($condition['price'])) {
        $srch->addCondition('d.deal_original_price', 'BETWEEN', $condition['price']);
    }
    if (!empty($condition['color']) || (!empty($condition['size']))) {
        $srch->joinTable('tbl_deal_option', 'LEFT JOIN', 'tdo.deal_id=d.deal_id ', 'tdo');
        $srch->joinTable('tbl_deal_option_value', 'LEFT JOIN', 'dov.deal_option_id=tdo.deal_option_id ', 'dov');
        $srch->joinTable('tbl_options', 'INNER JOIN', 'tdo.option_id=o.option_id ', 'o');
        $srch->joinTable('tbl_option_values', 'INNER JOIN', 'dov.option_value_id=ov.option_value_id', 'ov');
        if (!empty($condition['color'])) {
            $srch->addCondition('tdo.option_id', '=', 1);
            $srch->addCondition('dov.option_value_id', 'IN', $condition['color']);
        }
        if (!empty($condition['size'])) {
            $srch->addCondition('tdo.option_id', '=', 2);
            $srch->addCondition('dov.option_value_id', 'IN', $condition['size']);
        }
    }
    $srch->addGroupBy('d.deal_id');
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    $rs = $srch->getResultSet();
    if ($srch->recordCount() < 1) {
        $array['html'] = '<div class="error">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</div>';
        $array['dealIds'] = $deal_id;
        return $array;
    }
    $pagestring = '';
    $pages = $srch->pages();
    $pageno = $page + 1;
    if ($pages > 1) {
        $rescount = ((($pageno - 1) * $pagesize < $srch->recordCount()) ? $srch->recordCount() - (($pageno - 1) * $pagesize) : 0);
        $pagestring .= '<div class="paginglink"><h3 class="textcenter"><span> Showing ' . ((($pageno - 1) * $pagesize > $srch->recordCount()) ? $srch->recordCount() : (($pageno - 1) * $pagesize)) . ' of ' . $srch->recordCount() . '</span></h3>';
        if ($rescount > 0) {
            $pagestring .= '<div class="aligncenter ">';
            $json_con = json_encode($condition);
            $click = 'productSearchwithPagination("' . addslashes($json_con) . '","' . $pageno . '", "' . $pagesize . '")';
            $pagestring .= "<a href='javascript:void(0);' class='button searchPagination red' onclick='" . $click . "'> See " . $rescount . " More</a>";
            $pagestring .= '</div></div><div class="gap"></div>';
        }
    }
    $dealIdArrays = [];
    while ($rowDealCat = $db->fetch($rs)) {
        $deal_id = $rowDealCat['deal_id'];
        $dealIdArrays[] = $deal_id;
        $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $rowDealCat['deal_id'] . '&type=main';
        $cat_deal_box_html .= '<div class="dealBox" id="main_' . $deal_id . '">';
        $cat_deal_box_html .= '<div class="hidden-link">';
        $cat_deal_box_html .= ' <ul>';
        $cat_deal_box_html .= '<li><a href="' . friendlyUrl($dealUrl) . '">' . t_lang("M_TXT_DETAILS") . '</a></li>';
        $click = "fetchQuickViewHtmlJS(" . $rowDealCat['deal_id'] . ",'search'," . $pagesize . ")";
        $cat_deal_box_html .= '<li class="topRecentProduct_' . $deal_id . '"><a href="javascript:void(0);" onclick="' . $click . '">' . t_lang("M_TXT_QUICK_VIEW") . ' </a></li>';
        $cat_deal_box_html .= ' </ul>';
        $cat_deal_box_html .= '</div>';
        $cat_deal_box_html .= '<div class="pic"><a href="' . friendlyUrl($dealUrl) . '"><img src="' . CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $rowDealCat['deal_id'] . '&type=category' . '" alt=""></a></div>
                                    <a  href="' . friendlyUrl($dealUrl) . '" class="dealname"><h1>' . substr($rowDealCat['deal_name' . $_SESSION['lang_fld_prefix']], 0, 90) . '</h1></a>
                                    <div class="dealinfo">
                                        <h2>' . substr($rowDealCat['deal_subtitle' . $_SESSION['lang_fld_prefix']], 0, 230) . '</h2>
                                    </div><h4 class="price"><del>' . CONF_CURRENCY . number_format($rowDealCat["deal_original_price"], 2) . CONF_CURRENCY_RIGHT . '</del> ' . fetchProductSalePrice($rowDealCat["deal_id"]) . '</h4>';
        $cat_deal_box_html .= fetchfavUnfavIconHtml($rowDealCat['deal_id']);
        $cat_deal_box_html .= '</div>';
    }
    $cat_deal_box_html .= $pagestring;
    $array['dealIds'] = $dealIdArrays;
    $array['html'] = $cat_deal_box_html;
    return $array;
}

function fetchProductSalePrice($deal_id)
{
    global $db;
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('d.deal_id', '=', $deal_id);
    $srch->addMultipleFields(array('d.*'));
    $rs = $srch->getResultSet();
    $rowDealCat = $db->fetch($rs);
    $saleprice = CONF_CURRENCY . number_format($rowDealCat['deal_original_price'] - (($rowDealCat['deal_discount_is_percent'] == 1) ? ($rowDealCat['deal_original_price'] * $rowDealCat['deal_discount'] / 100) : $rowDealCat['deal_discount']), 2) . CONF_CURRENCY_RIGHT;
    return $saleprice;
}

function fetchBannerDetail($bannertype = 0, $limit = 1)
{
    //used in multiple files
    global $db;
    $srch = new SearchBase('tbl_banner', 'b');
    $srch->addCondition('b.banner_type', '=', $bannertype);
    $srch->addCondition('b.banner_active', '=', 1);
    $srch->setPageSize($limit);
    $srch->addOrder('RAND()');
    $rs = $srch->getResultSet();
    $row = $db->fetch_all($rs);
    return $row;
}

function fetchCompanyRating($companyId = 0)
{
    global $db;
    $srch1 = new SearchBase('tbl_reviews', 'r');
    $srch1->addCondition('reviews_type', '=', 2);
    $srch1->addCondition('r.reviews_approval', '=', 1);
    $srch1->addCondition('r.reviews_company_id', '=', $companyId);
    $srch1->addFld("ROUND(SUM(r.reviews_rating)/count(DISTINCT(r.reviews_id)))as rating");
    $reviewsRs = $srch1->getResultSet();
    $reviewsRow = $db->fetch($reviewsRs);
    return $reviewsRow;
}

function fetchDealRating($dealId = 0)
{
    global $db;
    $srch1 = new SearchBase('tbl_reviews', 'r');
    $srch1->addCondition('reviews_type', '=', 1);
    $srch1->addCondition('r.reviews_approval', '=', 1);
    $srch1->addCondition('r.reviews_deal_id', '=', $dealId);
    $srch1->addCondition('r.reviews_user_id', '!=', 0);
    $srch1->addFld("ROUND(SUM(r.reviews_rating)/count(DISTINCT(r.reviews_id)))as rating");
    $reviewsRs = $srch1->getResultSet();
    $reviewsRow = $db->fetch($reviewsRs);
    return $reviewsRow;
}

function showImage($img_obj, $img_path)
{
    global $do_not_compress;
    if (!is_object($img_obj)) {
        return false;
    }
    $img_found = false;
    if (is_file($img_path) && file_exists($img_path)) {
        $img_found = true;
    }
    if ($img_found) {
        $headers = apache_request_headers();
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($img_path))) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($img_path)) . ' GMT', true, 304);
            exit(0);
        }
    }
    ob_end_clean();
    header("Content-type: image/jpeg");
    header('Cache-Control: public');
    header("Pragma: public");
    header("Expires: " . date('r', strtotime("+10 Day")));
    if ($img_found) {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($img_path)) . ' GMT', true, 200);
    }
    $img_obj->displayImage();
    return true;
}

function addBonusAmountToRegisteredUser($userId)
{
    if ($userId <= 0) {
        return false;
    }
    global $db;
    $srch = new SearchBase('tbl_registration_credit_schemes', 'rcs');
    $srch->addCondition('rcs.regscheme_active', '=', 1);
    $srch->addCondition('rcs.regscheme_valid_from', '<', date("Y-m-d H:i:s"));
    $srch->addCondition('rcs.regscheme_valid_till', '>', date("Y-m-d H:i:s"));
    $rs = $srch->getResultSet();
    while ($row = $db->fetch($rs)) {
        $srch1 = new SearchBase('tbl_regscheme_offer_log', 'rol');
        $srch1->addCondition('rol.rofferlog_scheme_id', '=', $row['regscheme_id']);
        $srch1->addCondition('mysql_func_date(rol.rofferlog_datetime)', '=', date("Y-m-d"), 'AND', true);
        $rs1 = $srch1->getResultSet();
        $total_count = $srch1->recordCount($rs1);
        if ($total_count < $row['regscheme_to_users_per_day']) {
            $db->query("update tbl_users set user_wallet_amount = user_wallet_amount + " . $row['regscheme_credit_amount'] . " where user_id=" . intval($userId));
            $db->insert_from_array('tbl_user_wallet_history', array(
                'wh_user_id' => $userId,
                'wh_untipped_deal_id' => 0,
                'wh_particulars' => $row['regscheme_name'],
                'wh_amount' => $row['regscheme_credit_amount'],
                'wh_time' => date('Y-m-d H:i:s')
            ));
            $db->insert_from_array('tbl_regscheme_offer_log', array(
                'rofferlog_scheme_id' => $row['regscheme_id'],
                'rofferlog_user_id' => $userId,
                'rofferlog_amount' => $row['regscheme_credit_amount'],
                'rofferlog_datetime' => date('Y-m-d H:i:s')
            ));
            sendUserNotificationsForBonusAmount($userId, $row['regscheme_credit_amount']);
        }
    }
}

function sendUserNotificationsForBonusAmount($userId, $amount)
{
    global $db;
    $rs1 = $db->query("select * from tbl_users where user_id=" . $userId);
    $row = $db->fetch($rs1);
    $rs = $db->query("select * from tbl_email_templates where tpl_id=49");
    $row_tpl = $db->fetch($rs);
    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
    $arr_replacements = array(
        'xxuser_namexx' => $row['user_name'],
        'xxuser_emailxx' => $row['user_email'],
        'xxwallet_amountxx' => amount($amount, 2),
        'xxsite_namexx' => CONF_SITE_NAME,
        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
        'xxwebrooturlxx' => CONF_WEBROOT_URL,
        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
    );
    foreach ($arr_replacements as $key => $val) {
        $subject = str_replace($key, $val, $subject);
        $message = str_replace($key, $val, $message);
    }
    if ($row_tpl['tpl_status'] == 1) {
        sendMail($row['user_email'], $subject, emailTemplate(($message)));
    }
    return true;
}

function getRecords($tablename, $condition, $fetchtype)
{
    global $db;
    $srch = new SearchBase($tablename);
    if (is_array($condition)) {
        foreach ($condition as $key => $value) {
            $srch->addCondition($key, '=', $value);
        }
    }
    $rs = $srch->getResultSet();
    switch (strtoupper($fetchtype)) {
        case 'FIRST':
            $data = $db->fetch($rs);
            break;
        case 'ALL':
            $data = $db->fetch_all($rs);
            break;
        case 'LIST':
            $data = $db->fetch_all_assoc($rs);
            break;
        default:
            $data = $srch;
            break;
    }
    return $data;
}

function escape_attr($val)
{
    return htmlentities($val, ENT_QUOTES, 'UTF-8');
}

function unescape_attr($val)
{
    return html_entity_decode($val, ENT_QUOTES, 'UTF-8');
}

function setRequirementFieldPlaceholder(&$fld, $star = false, $notRequired = "")
{
    if ($fld->field_caption == null || strlen($fld->field_caption) <= 0) {
        $placeholder = getFieldCaptionFromTitle($fld);
    } else {
        $placeholder = $fld->field_caption;
    }
    if ($star) {
        $star = "*";
    }
    if ($notRequired != "") {
        if ($placeholder == $notRequired) {
            $star = "";
        }
    }
    $fld->extra = 'placeholder="' . $placeholder . $star . '"';
    //  $fld->field_caption="";
}

function getUserRegisterationForm()
{
    global $db;
    $frm = getMBSFormByIdentifier('frmRegistration');
    $frm->setValidatorJsObjectName('signupFormValidator');
    $frm->captionInSameCell(true);
    $fld = $frm->getField('user_zip_code');
    $frm->removeField($fld);
    $fld = $frm->getField('user_dob');
    $frm->removeField($fld);
    $fld = $frm->getField('user_gender');
    $fld->Caption = t_lang('M_FRM_GENDER');
    $frm->removeField($fld);
    $fld = $frm->getField('udc_cat_id');
    $fld->selectCaption = t_lang('M_TXT_SELECT');
    $frm->removeField($fld);
    $arr = [];
    $rs = $db->query('select cat_id, IF(CHAR_LENGTH(cat_name' . $_SESSION['lang_fld_prefix'] . '), cat_name' . $_SESSION['lang_fld_prefix'] . ',cat_name) as cat_name from tbl_deal_categories');
    $fld->options = $db->fetch_all_assoc($rs);
    $fld = $frm->getField('user_newsletter');
    $frm->removeField($fld);
    $fld = $frm->getField('user_city');
    $cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0");
    $fld->options = $db->fetch_all_assoc($cityList);
    $fld = $frm->getField('btn_submit');
    $fld->value = t_lang('M_TXT_SIGN_UP');
    $fld = $frm->getField('user_name');
    $fld->extra = "placeholder='" . t_lang('M_TXT_FIRST_NAME') . "'";
    $fld = $frm->getField('user_lname');
    $fld->extra = "placeholder=" . t_lang('M_TXT_LAST_NAME');
    $fld = $frm->getField('user_email');
    $fld->extra = "placeholder=" . t_lang('M_TXT_EMAIL');
    ;
    $fld = $frm->getField('user_password');
    $fld->extra = "placeholder='" . t_lang('M_TXT_PASSWORD') . "'";
    $fld = $frm->getField('password1');
    $fld->extra = "placeholder='" . t_lang('M_TXT_CONFIRM_PASSWORD') . "'";
    //$frm->setRequiredStarPosition('ss');
    $frm->setOnSubmit('return singupFormSubmit(this,signupFormValidator); ');
    updateFormLang($frm);
    return $frm;
}

function sendUserEmailVerificationEmail($data, &$error)
{
    if (!class_exists('userInfo')) {
        require_once realpath(dirname(__FILE__) . '/../site-classes/user-info.cls.php');
    }
    $userObj = new userInfo();
    $user = getUserByEmail($data['user_email'], false);
    if (!$user) {
        $error = t_lang('M_TXT_EMAIL_NOT_FOUND');
        return false;
    }
    if ($user['user_email_verified'] == 1) {
        $error = 'Email Already Verified!';
        return false;
    }
    if ($userObj->sendVerificationEmail($user['user_id'], $user['user_name'], $user['user_email'], $user['user_member_id'], $user['reg_code'], $user['user_city'], 0)) {
        return true;
    }
    $error = t_lang('M_MSG_EMAIL_SENDING_FAILED');
    return false;
}

function registerNewUser($frm, $data, &$error)
{
    if (!$frm->validate($data)) {
        $error = getValidationErrMsg($frm);
        return false;
    } //echo '<pre>';print_r($data); exit;
    if (!class_exists('userInfo')) {
        require_once dirname(__FILE__) . '/../site-classes/user-info.cls.php';
    }
    $user = new userInfo();
    $user->addUser($data['user_email'], mt_rand(), ($data['user_name']), ($data['user_lname']), $data['user_gender'], $data['user_dob'], md5($data['user_password']), $data['user_city'], CONF_TIMEZONE);
    $user->setFldValue('user_regdate', date('Y-m-d H:i:s'), false);
    /* Set affiliate id for order */
    if (isset($_COOKIE['affid']))
        $user->setFldValue('user_affiliate_id', $_COOKIE['affid'] + 0);
    /* Set affiliate id for order ends */
    if (!$user->addNew()) {
        $error = 'User execution error! ' . $user->getError();
        return false;
    }
    return true;
}

function getUserByEmail($email, $check_active = true, $password = false)
{
    global $db;
    $srch = new SearchBase('tbl_users', 'user');
    $srch->addCondition('user_deleted', '=', 0);
    if ($check_active === true) {
        $srch->addCondition('user_active', '=', 1);
    }
    $srch->addCondition('user_email', '=', $email);
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $rs = $srch->getResultSet();
    $row = $db->fetch($rs);
    if ($row && $password == false) {
        unset($row['user_password']);
    }
    return $row;
}

function checkForgotPasswordRequest($user_id)
{
    $user_id = intval($user_id);
    if ($user_id < 1) {
        return true; //to stop sending password reset email.
    }
    global $db;
    $srch = new SearchBase('tbl_user_password_resets_requests');
    $srch->addCondition('uprr_company_id', '=', 0);
    $srch->addCondition('uprr_affiliate_id', '=', 0);
    $srch->addCondition('uprr_expiry', '>', 'mysql_func_(NOW() - INTERVAL 1 DAY)', 'AND', true);
    $srch->addCondition('uprr_user_id', '=', $user_id);
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $rs = $srch->getResultSet();
    return $db->fetch($rs);
}

function processForgetPasswordRequest($email, &$error)
{
    if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID');
        return false;
    }
    $row = getUserByEmail($email);
    $user_id = intval($row['user_id']);
    if ($row && $email == $row['user_email'] && $user_id > 0) {
        if (checkForgotPasswordRequest($user_id)) {
            $error = t_lang('M_TXT_FORGOT_PASSWORD_ERROR_MESSAGE');
            return false;
        }
        $affiliate_id = 0;
        $company_id = 0;
        $rep_id = 0;
        $code = mt_rand(0, 9999999999);
        global $db;
        if ($db->query("INSERT INTO tbl_user_password_resets_requests VALUES ($user_id, " . $db->quoteVariable($code) . ", now(),0,0,0);")) {
            $rs = $db->query("select * from tbl_email_templates where tpl_id=4");
            $row_tpl = $db->fetch($rs);
            $verification_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'reset-password.php?code=' . $user_id . '_' . $company_id . '_' . $affiliate_id . '_' . $rep_id . '_' . $code;
            $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
            $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
            $arr_replacements = array(
                'xxuser_namexx' => $row['user_name'],
                'xxuser_emailxx' => $row['user_email'],
                'xxuser_passwordxx' => '<a style="text-decoration:none;font-weight:bold;color:#0066cc;" href="' . $verification_url . '">' . t_lang('M_TXT_CLICK_HERE') . '</a>',
                'xxsite_namexx' => CONF_SITE_NAME,
                'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                'xxwebrooturlxx' => CONF_WEBROOT_URL,
                'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
            );
            foreach ($arr_replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $message = str_replace($key, $val, $message);
            }
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($email, $subject, emailTemplate(($message)));
            }
            return true;
        } else {
            $error = 'Error: ' . $db->getError();
        }
    } else {
        $error = t_lang('M_TXT_EMAIL_NOT_FOUND');
    }
    return false;
}

/*
  Note :- IF city id(selected city) is exists then seletced city and 'all cities' deal will be show in system.If 'all cities' selected and 'allcities' + other cities deal will be list in system.
 */

function alldealPageHtml($page = 1, $pagename = 'all-deals', $showMore = '', $cityId = '', $start_date = '', $end_date = '', $pagesize = 12, $showFor = "pagelist")
{
    global $db;
    $pagesize = $pagesize;
    if (empty($cityId)) {
        $cityId = $_SESSION['city'];
    }
    // $srch = new SearchBase('tbl_deals', 'd');
    $srch = new SearchBase('tbl_deal_categories', 'c');
    $srch->joinTable('tbl_deal_categories', 'INNER JOIN', "dc.cat_code LIKE CONCAT(c.cat_code, '%')", 'dc');
    $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'dtc.dc_cat_id=dc.cat_id ', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
    if ($pagename == 'expired-deal') {
        $srch->addCondition('deal_status', '=', 2);
    } else {
        $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
        $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
        $srch->addCondition('deal_status', '=', 1);
    }
    $srch->addCondition('deal_complete', '=', 1);
    $srch->addCondition('deal_deleted', '=', 0);
    if ($pagename == 'instant-deal') {
        $srch->addCondition('d.deal_instant_deal', '=', 1);
    } else {
        // $srch->addCondition('d.deal_instant_deal', '!=', 1);
    }
    if ($pagename == 'main-deal') {
        $srch->addCondition('d.deal_main_deal', '=', 1);
        $srch->addCondition('deal_city', '=', $cityId);
    }
    if ($pagename == 'city-deals' || $pagename == "home" || $pagename == 'instant-deal' || $showFor == "app" || $pagename == "category-deal" || $pagename == "products" || $pagename == "getaways" || $pagename == "expired-deal" || $pagename == "merchant-favorite") {
        if ($cityId != 0) {
            $cnd = $srch->addDirectCondition('0');
            $cnd->attachCondition('deal_city', '=', $cityId, 'OR');
            $cnd->attachCondition('deal_city', '=', 0);
        }
    }
    if ($pagename == 'more-cities') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('deal_city', '!=', $cityId, 'OR');
    }
    if ($pagename == 'getaways') {
        $srch->joinTable('tbl_cities', 'LEFT JOIN', 'city.city_id=d.deal_city ', 'city');
        $srch->addCondition('deal_sub_type', '>', 0);
        if (($cityId != "") || ($showMore != "")) {
            if ($showMore) {
                $cnd->attachCondition('city_name' . $_SESSION['lang_fld_prefix'], 'LIKE', '%' . $showMore . '%', 'AND');
            }
        }
        if ($start_date != "") {
            $srch->joinTable('tbl_deal_booking_dates', 'INNER JOIN', 'd.deal_id=dbd.dbdate_deal_id', 'dbd');
            if ($end_date != "") {
                $srch->addDirectCondition('dbd.dbdate_date BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
            } else {
                $srch->addCondition('dbd.dbdate_date', '=', $start_date);
            }
        }
    } else {
        //	$srch->addCondition('deal_sub_type', '=', 0);
    }
    if ($pagename == 'products') {
        $srch->addCondition('deal_type', '=', 1);
    } else if ($pagename == "home" || $pagename == "category-deal" || $pagename == "merchant-favorite") {
        $srch->addCondition('deal_type', 'IN', array(0, 1));
    } else {
        $srch->addCondition('deal_type', '=', 0);
    } //echo $srch->getQuery(); //exit;
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_sub_deals', 'LEFT OUTER JOIN', 'd.deal_id=sd.sdeal_deal_id', 'sd');
    $srch->addGroupBy('d.deal_id');
    $srch->addOrder('deal_city', 'desc');
    // $srch->addHaving('mysql_func_sold', '<', 'mysql_func_(deal_max_coupons)', 'AND', true);
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 || o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od_gift_qty ELSE 0 END) AS sold");
    $fld = "(CASE WHEN (d.deal_is_subdeal = 1)
        THEN
            CASE
            WHEN (sd.`sdeal_discount_is_percentage` = 0)
                THEN (sd.`sdeal_original_price` - sd.`sdeal_discount`)
                ELSE (sd.`sdeal_original_price` -(sd.`sdeal_original_price` * sd.`sdeal_discount` / 100))
            END
        ELSE
            CASE
            WHEN (d.`deal_discount_is_percent` = 0)
                THEN (d.`deal_original_price` - d.`deal_discount`)
                ELSE (d.`deal_original_price` -(d.`deal_original_price` * d.`deal_discount` / 100 ))
            END
        END )AS sellPrice";
    $srch->addMultipleFields(array('d.deal_id', 'deal_max_coupons', 'deal_min_buy', $fld));
//echo $srch->getQuery(); //exit;
    if ($showFor != "pagelist") {
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs_deal_list = $srch->getResultSet();
        return $db->fetch_all($rs_deal_list);
    } else {
        return $srch;
    }
}

/* Last parameter "order_on_subdeals" gets used only for All Deals page - Searching */

function pageSearch($condition = [], $page = 1, $pagesize = 9, $order_on_subdeals = false)
{
    if ($condition['pagename'] == "city-deals") {
        $cityId = $_SESSION['city'];
    }
    if (isset($condition['cityId'])) {
        $cityId = $condition['cityId'];
    }
    $srch = alldealPageHtml($page, $condition['pagename'], $condition['city_search'], $cityId, $condition['start_date'], $condition['end_date']);
    if (!empty($condition['category'])) {
        $srch->addCondition('c.cat_id', '=', $condition['category']);
        $srch->addCondition('d.deal_status', '=', 1);
    }
    if (!empty($condition['company'])) {
        $srch->addCondition('d.deal_company', '=', $condition['company']);
    }
    if (!empty($condition['price'])) {
        // $srch->addCondition('mysql_func_sellPrice', 'BETWEEN', $condition['price'], 'AND', true);
        $srch->addHaving('sellPrice', 'BETWEEN', $condition["price"]);
    }
    if (!empty($condition['color']) || (!empty($condition['size']))) {
        $srch->joinTable('tbl_deal_option', 'LEFT JOIN', 'tdo.deal_id=d.deal_id ', 'tdo');
        $srch->joinTable('tbl_deal_option_value', 'LEFT JOIN', 'dov.deal_option_id=tdo.deal_option_id', 'dov');
        $srch->joinTable('tbl_options', 'INNER JOIN', 'tdo.option_id=to1.option_id', 'to1');
        $srch->joinTable('tbl_option_values', 'INNER JOIN', 'dov.option_value_id=ov.option_value_id', 'ov');
        if (!empty($condition['color'])) {
            $srch->addCondition('tdo.option_id', '=', 1);
            $srch->addCondition('dov.option_value_id', 'IN', $condition['color']);
        }
        if (!empty($condition['size'])) {
            $srch->addCondition('tdo.option_id', '=', 10);
            $srch->addCondition('dov.option_value_id', 'IN', $condition['size']);
        }
    }
    if ($order_on_subdeals) {
        if (!empty($condition['order_type'])) {
            $srch->addFld('(CASE WHEN sdeal_voucher_price is NULL THEN deal_voucher_price ELSE sdeal_voucher_price END) as sdeal_voucher_price');
            $srch->addOrder($condition['order_type'][2], $condition['order']);
            //$srch->addOrder($condition['order_type'][1],$condition['order']);
        } else {
            $srch->addOrder('d.deal_id', 'desc');
        }
    } else {
        if (!empty($condition['order_type'])) {
            $srch->addOrder($condition['order_type'], $condition['order']);
        } else {
            $srch->addOrder('d.deal_id', 'desc');
        }
    }
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    //echo $srch->getQuery();
    return $srch;
}

function addFavouriteDeal($user_id, $deal_id)
{
    global $db;
    removeFavouriteDeal($user_id, $deal_id);
    if (!$db->query("insert into tbl_users_favorite_deals (user_id,deal_id) values ('$user_id','$deal_id')")) {
        return false;
    }
    return true;
}

function removeFavouriteDeal($user_id, $deal_id)
{
    global $db;
    if (!$db->query("delete from tbl_users_favorite_deals where user_id=" . $user_id . "  and deal_id=" . $deal_id)) {
        return false;
    }
    return true;
}

function addFavouriteMerchant($user_id, $company_id)
{
    global $db;
    removeFavouriteMerchant($user_id, $company_id);
    if (!$db->query("insert into tbl_users_favorite (user_id,company_id) values ('$user_id','$company_id')")) {
        return false;
    }
    return true;
}

function removeFavouriteMerchant($user_id, $company_id)
{
    global $db;
    if (!$db->query("delete from tbl_users_favorite where user_id=" . $user_id . " and company_id=" . $company_id)) {
        return false;
    }
    return true;
}

function currency_number_format($val, $decimal = 2)
{
    return number_format($val, $decimal);
}

function currency_round($val, $decimal = 2)
{
    return number_format(round($val, $decimal), $decimal);
}

function amount($val, $decimal = 2)
{
    return CONF_CURRENCY . number_format($val, $decimal) . CONF_CURRENCY_RIGHT;
}

function dealsearchListHtml($name, $cat, $page, $session = true, $type = "deal", $cityId, $fordevice = "web")
{
    $str = "";
    $name = urldecode($name);
    global $db;
    global $msg;
    $dealArrCat = [];
    if ($type == 'deal') {
        $srch = new SearchBase('tbl_deal_to_category', 'dtc');
        $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
        $srch->addCondition('d.deal_name' . $_SESSION["lang_fld_prefix"], 'like', '%' . $name . '%');
        $rs = $srch->getResultSet();
        if ($srch->recordCount() > 0) {
            while ($row = $db->fetch($rs)) {
                $dealArrCat[] = $row['dc_deal_id'];
            }
        } else {
            if ($fordevice == "app") {
                return false;
            }
            $str = '<span class="noresultsfound">' . t_lang('M_TXT_SORRY_NO_MATCHING_DEALS_AVAILABLE') . '</span>';
            //   return $str;
        }
    }if ($type == 'cat') {
        $srch = new SearchBase('tbl_deal_categories', 'dc');
        $srch->addCondition('dc.cat_name' . $_SESSION["lang_fld_prefix"], 'like', $cat . '%');
        $rs = $srch->getResultSet();
        if ($srch->recordCount() > 0) {
            $catrow = $db->fetch($rs);
            $srch1 = new SearchBase('tbl_deal_to_category', 'dtc');
            //$srch->addCondition('dtc.dc_cat_id', '=', $get['cat']);
            $srch1->joinTable('tbl_deal_categories', 'INNER JOIN', 'dtc.dc_cat_id=c.cat_id ', 'c');
            $srch1->addCondition('c.cat_code', 'like', $catrow['cat_code'] . '%');
            $rs = $srch1->getResultSet();
            while ($row = $db->fetch($rs)) {
                $dealArrCat[] = $row['dc_deal_id'];
            }
        } else {
            $str = '<span class="noresultsfound">' . t_lang('M_TXT_SORRY_NO_MATCHING_DEALS_AVAILABLE') . '</span>';
            return $str;
        }
    }
    $srch = new SearchBase('tbl_deals', 'd');
    if ($session == true) {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('deal_city', '=', $cityId, 'OR');
        $cnd->attachCondition('deal_city', '=', 0);
    } else {
        $srch->addCondition('deal_city', '!=', $cityId);
        $srch->addCondition('deal_city', '!=', 0);
    }
    $srch->addCondition('d.deal_name' . $_SESSION["lang_fld_prefix"], 'like', '%' . $name . '%');
    $srch->addCondition('d.deal_status', '=', 1);
    $srch->addCondition('deal_complete', '=', 1);
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->addCondition('d.deal_id', 'IN', $dealArrCat);
    $srch->addGroupBy('d.deal_id');
    $srch->addOrder('deal_id', 'desc');
    $page = is_numeric($page) ? $page : 1;
    $pagesize = 9;
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    if ($fordevice == "app") {
        $rs_deal_list = $srch->getResultSet();
        return $rs_deal_list;
    }
    return $srch;
}

function setCartValuesForResponse(&$cart)
{
    $discount = array('code' => '', 'value' => 0);
    if ($dd = $cart->getDiscountDetail()) {
        $discount = array(
            'code' => $dd['coupon_code'],
            'value' => $cart->getDiscountValue()
        );
    }
    $shipping_charges = $cart->getShippingCharges();
    $cart_options = displayCartOptions($cart->getProducts());
    return array(
        'status' => 1,
        'cart_vals' => array(
            'cart' => $cart->getProducts(),
            'discount' => $discount,
            'shipping' => $shipping_charges,
            'cart_options' => $cart_options,
            'tax' => $cart->getTaxAmount(),
            'count' => $cart->getItemCount()
        )
    );
}

function convertLangTextToProperText($str)
{
    if (strlen($str) == 0)
        return false;
    $str = str_ireplace('M_TXT_ADDED_MONEY_IN_WALLET', t_lang('M_TXT_ADDED_MONEY_IN_WALLET'), $str);
    $str = str_ireplace('M_TXT_TRANSACTION_ID', t_lang('M_TXT_TRANSACTION_ID'), $str);
    $str = str_ireplace('M_TXT_PAYPAL', t_lang('M_TXT_PAYPAL'), $str);
    $str = str_ireplace('M_TXT_ORDER_ID', t_lang('M_TXT_ORDER_ID'), $str);
    $str = str_ireplace('M_TXT_AMOUNT_DEPOSITED', t_lang('M_TXT_AMOUNT_DEPOSITED'), $str);
    $str = str_ireplace('M_TXT_ORDER', t_lang('M_TXT_ORDER'), $str);
    $str = str_ireplace('M_TXT_PLACED_WITH_WALLET_AND', t_lang('M_TXT_PLACED_WITH_WALLET_AND'), $str);
    $str = str_ireplace('M_TXT_WALLET_AND', t_lang('M_TXT_WALLET_AND'), $str);
    $str = str_ireplace('M_TXT_ITEM_PURCHASED', t_lang('M_TXT_ITEM_PURCHASED'), $str);
    $str = str_ireplace('M_TXT_UPDATED_BY_ADMIN', t_lang('M_TXT_UPDATED_BY_ADMIN'), $str);
    $str = str_ireplace('M_TXT_ADMIN_REFUND_THE_ORDER_FOR_DEAL', t_lang('M_TXT_ADMIN_REFUND_THE_ORDER_FOR_DEAL'), $str);
    $str = str_ireplace('M_TXT_AND_FOR_VOUCHER_CODE', t_lang('M_TXT_AND_FOR_VOUCHER_CODE'), $str);
    $str = str_ireplace('M_TXT_QTY', t_lang('M_TXT_QTY'), $str);
    $str = str_ireplace('M_TXT_ADMIN_REFUND_THE_ORDER_FOR_ORDER_ID', t_lang('M_TXT_ADMIN_REFUND_THE_ORDER_FOR_ORDER_ID'), $str);
    $str = str_ireplace('M_TXT_COMMISSION_FOR_ORDERID', t_lang('M_TXT_COMMISSION_FOR_ORDERID'), $str);
    $str = str_ireplace('M_TXT_DEAL', t_lang('M_TXT_DEAL'), $str);
    $str = str_ireplace('M_TXT_CANCELLED', t_lang('M_TXT_CANCELLED'), $str);
    $str = str_ireplace('M_TXT_AFFILIATE_COMMISSION_FOR', t_lang('M_TXT_AFFILIATE_COMMISSION_FOR'), $str);
    $str = str_ireplace('M_TXT_FROM_WALLET', t_lang('M_TXT_FROM_WALLET'), $str);
    $str = str_ireplace('M_TXT_CREDIT_CARD', t_lang('M_TXT_CREDIT_CARD'), $str);
    return $str;
}

function getTotalDebitsAmountForMerchant($company_id, $deal_id = 0)
{
    if (intval($company_id) < 1)
        return 0;
    global $db;
    $srch = new SearchBase('tbl_company_wallet_history');
    $srch->addCondition('cwh_company_id', '=', $company_id);
    $srch->addCondition('cwh_amount', '<', '0');
    $srch->addFld('sum(cwh_amount) as totalDebits');
    $srch->addGroupBy('cwh_company_id');
    if (intval($deal_id) > 0) {
        $srch->addCondition('cwh_untipped_deal_id', '=', $deal_id);
    }
    $rs = $srch->getResultSet();
    $rowtotalpaid = $db->fetch($rs);
    return abs($rowtotalpaid['totalDebits']);
}

function getTotalCreditsAmountForMerchant($company_id, $deal_id = 0)
{
    if (intval($company_id) < 1)
        return 0;
    global $db;
    $srch = new SearchBase('tbl_company_wallet_history');
    $srch->addCondition('cwh_company_id', '=', $company_id);
    $srch->addCondition('cwh_amount', '>', '0');
    $srch->addFld('sum(cwh_amount) as totalCredits');
    $srch->addGroupBy('cwh_company_id');
    if (intval($deal_id) > 0) {
        $srch->addCondition('cwh_untipped_deal_id', '=', $deal_id);
    }
    $rs = $srch->getResultSet();
    $rowtotalCredits = $db->fetch($rs);
    return abs($rowtotalCredits['totalCredits']);
}

if (!function_exists('apache_request_headers')) {

    function apache_request_headers()
    {
        $arh = [];
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $arh_key = ucfirst(strtolower($arh_key));
                $rx_matches = [];
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return( $arh );
    }

}
if (!function_exists('addhttp')) {

    function addhttp($url)
    {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }

}
if (!function_exists('detectDevice')) {

    function detectDevice()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
            return 1; //for mobile
        } else {
            return 2; ////for web
        }
    }

}
if (!function_exists('appendPlainText')) {

    function appendPlainText($text)
    {
        return htmlentities($text, ENT_QUOTES, 'UTF-8');
    }

}
if (!function_exists('loadModels')) {

    function loadModels(array $modelNames)
    {
        foreach ($modelNames as $model) {
            require_once $_SERVER['DOCUMENT_ROOT'] . "/site-classes/models/$model.php";
        }
    }

}
