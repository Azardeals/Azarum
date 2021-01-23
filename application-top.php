<?php

session_start();
if (!in_array('ob_gzhandler', ob_list_handlers())) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}
if (ini_get('date.timezone') == '') {
    date_default_timezone_set('America/Los_Angeles');
}
$path = dirname(__FILE__) . '/';
$server_time_zone = date_default_timezone_get();

require_once dirname(__FILE__) . '/conf/' . $_SERVER['SERVER_NAME'] . '.php';
require_once dirname(__FILE__) . '/conf/common-conf.php';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
set_include_path(get_include_path() . PATH_SEPARATOR . $path . 'lib/');
 
$arr_lang_vals = [];
require_once dirname(__FILE__) . '/includes/functions.php';
require_once dirname(__FILE__) . '/includes/site-functions.php';
require_once dirname(__FILE__) . '/_classes/message.cls.php';
require_once dirname(__FILE__) . '/site-classes/message-info.php';
require_once dirname(__FILE__) . '/site-classes/cart.class.php';

################################################################
 
spl_autoload_register(function($clname) {
    switch ($clname) {
        case 'Database':
            require_once dirname(__FILE__) . '/_classes/db.mysqli.php';
            break;
        case 'FormField':
            require_once dirname(__FILE__) . '/_classes/form-field.cls.php';
            break;
        case 'SearchBase':
            require_once dirname(__FILE__) . '/_classes/search-base.cls.php';
            break;
        case 'SearchCondition':
            require_once dirname(__FILE__) . '/_classes/search-condition.cls.php';
            break;
        case 'Form':
            require_once dirname(__FILE__) . '/_classes/form.cls.php';
            break;
        case 'FormFieldRequirement':
            require_once dirname(__FILE__) . '/_classes/form-field-requirement.cls.php';
            break;
        case 'TableRecord':
            require_once dirname(__FILE__) . '/_classes/table-record.cls.php';
            break;
        case 'Record':
            require_once dirname(__FILE__) . '/_classes/record-base.cls.php';
            break;
        case 'imageResize':
        case 'ImageResize':
            require_once dirname(__FILE__) . '/_classes/image-resize.cls.php';
            break;
        case 'DealInfo':
            require_once dirname(__FILE__) . '/site-classes/deal-info.cls.php';
            break;
    }
});
$db_config = array('server' => CONF_DB_SERVER, 'user' => CONF_DB_USER, 'pass' => CONF_DB_PASS, 'db' => CONF_DB_NAME);
$db = new Database(CONF_DB_SERVER, CONF_DB_USER, CONF_DB_PASS, CONF_DB_NAME);
 
$db->query("SET sql_mode = '' ");
$db->query("SET NAMES 'utf8'");
/* define configuration variables */
$rs = $db->query("select * from tbl_configurations");
while ($row = $db->fetch($rs)) {
    define(strtoupper($row['conf_name']), $row['conf_val']);
}
/* end configuration variables */
$arr_lang_name = array(0 => 'English', 1 => CONF_SECONDARY_LANGUAGE);
/* 	Timezone settings for php and mysql servers, please don't edit without knowing the logic of this code	 */
date_default_timezone_set(CONF_TIMEZONE);
// get local time on Web/PHP server
$localtime = strtotime(date('Y-m-d H:i:s'));
//get local time in GMT/UTC (i.e GMT/UTC is set as +0:00 on database and other timezones are set as +/- hours of this)
$gm_localtime = strtotime(gmdate('Y-m-d H:i:s'));
//find offset in hours (if any - which allows for Daylight Saving Time or British Summer Time (BST))
$diff_mins = ($localtime - $gm_localtime) / 60;
//Then the Database server needs to be set to this Offset to store/retrieve values as local ones
$adjust = "SET time_zone = '";
$diff_hrs = $diff_mins / 60;
$diff_mins = abs($diff_mins) % 60;
if ($diff_hrs > 0) {
    $adjust .= "+" . floor($diff_hrs) . ":" . $diff_mins;
} elseif ($diff_hrs < 0) {
    $adjust .= ceil($diff_hrs) . ":" . $diff_mins;
} else {
    $adjust .= "+0:00";
}
$adjust .= "'";
$db->query($adjust);
$db->query("SET sql_mode = '' ");
/* 	Timezone settings for php and mysql servers, please don't edit without knowing the logic of this code	 */
$db->query("SET NAMES utf8 ");
require_once dirname(__FILE__) . '/includes/lang-functions.php';
define('ORDER_CANCELLATION_TIME', 300); //in seconds
define('ORDER_PROBATION_TIME', '-5 MINUTE'); //must be same as that of ORDER_CANCELLATION_TIME
define('ORDER_AVAILABILITY_TEXT', t_lang('M_TXT_MINUTES'));
$arr_page_js = [];
$arr_common_js = [];
$arr_page_css = [];
$arr_common_css = [];
$arr_common_js[] = 'js/jquery-1.7.2.min.js';
$arr_common_js[] = 'functions.js.php';
$arr_common_js[] = 'js/site-functions.js';
$arr_common_js[] = 'form-validation.js.php';
$arr_common_js[] = 'form-validation-lang.php';
$arr_common_js[] = 'js/jquery-ui.min.js';
$arr_common_js[] = 'js/jquery.placeholder.js';
$arr_common_js[] = 'facebox/facebox' . $_SESSION['lang_fld_prefix'] . '.js';
$arr_common_js[] = 'js/mbsmessage.js';
$arr_common_js[] = 'js/modernizr-custom.js';
$arr_common_js[] = 'js/social_sharing.js';

$pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4);
if ($pagename == 'cms-page') {
    /* Image gallery for cms page js */
    $arr_page_js[] = 'js/jquery.ad-gallery.js';
    $arr_page_js[] = 'js/jquery.ad-gallery.pack.js';
    /* end image slide js */
}
if ($pagename == 'deal' || $pagename == 'products-featured' || $pagename == 'products' || $pagename == 'preview-deal' || $pagename == 'all-deals' || $pagename == 'city-deals' || $pagename == 'getaways') {
    /* Image gallery for front page js */
    $arr_page_js[] = 'js/jquery.flexslider.js';
    /* end image slide js */
}
$relativePathOfScript = substr($_SERVER['SCRIPT_NAME'], 0, -(strlen(strrchr($_SERVER['SCRIPT_NAME'], '/')))) . '/';
if (CONF_WEBROOT_URL == '/') {
    $relativePathOfScript = substr($relativePathOfScript, 1);
} else {
    $relativePathOfScript = str_replace(CONF_WEBROOT_URL, '', $relativePathOfScript);
}
if (file_exists('page-js/' . $pagename . '.js')) {
    $arr_page_js[] = $relativePathOfScript . 'page-js/' . $pagename . '.js';
}
if (file_exists('page-css/' . $pagename . '.css')) {
    $arr_page_css[] = $relativePathOfScript . 'page-css/' . $pagename . '.css';
}
if ($pagename == 'cms-page') {
    /* Gallery css */
    $arr_page_css[] = '/css/jquery.ad-gallery.css';
    /* Gallery css end */
}
$arr_common_css[] = dirname(__FILE__) . '/css/mbsmessage.css';
$arr_common_css[] = dirname(__FILE__) . '/facebox/facebox.css';
$arr_common_css[] = dirname(__FILE__) . '/css/jquery-ui.css';
if ($pagename == 'deal' || $pagename == 'getaways') {
    $arr_common_css[] = dirname(__FILE__) . '/css/front-calender.css';
}
//echo $_SERVER['DOCUMENT_ROOT'] .CONF_WEBROOT_URL.'footer.php';
/* $content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . CONF_WEBROOT_URL . 'footer.php'); */
//if(!stristr($content, '<a href="http://www.fatbit.com">Powered By: FATbit Technologies </a>') ) die('');
/* --------------------------------  SSL ACTIVATION CHECK ------------------ */
if (CONF_SSL_ACTIVE == 1) {
    $arr_secure_pages = array('buy-deal.php');
    $arr_non_secure_pages = array('index.php', 'deal.php', 'deal-list.php');
    if (in_array(substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, strlen($_SERVER['SCRIPT_NAME']) - 1), $arr_secure_pages)) {
        if ($_SERVER['SERVER_PORT'] == '80') {
            $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            redirectuser($redirect);
        }
    }/*  else if (in_array(substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, strlen($_SERVER['SCRIPT_NAME']) - 1), $arr_non_secure_pages)) {
      if ($_SERVER['SERVER_PORT'] != '80') {
      $redirect = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      redirectuser($redirect);
      }
      } */
}
/* --------------------------------  SSL ACTIVATION CHECK ------------------ */
$system_alerts = [];
if (true === CONF_DEVELOPMENT_MODE) {
    $system_alerts[] = 'system is in development mode';
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
    ini_set('display_errors', 1);
} else {
    $system_alerts[] = '';
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}
/* Track referrer and affiliate */
$date = substr(addTimezone(date('Y-m-d H:i:s'), CONF_TIMEZONE), 0, 10);
if (!isset($_COOKIE['affid']) && !isset($_COOKIE['refid']) && isset($_GET['affid']) && ((int) $_GET['affid']) > 0) {
    $_GET['affid'] = (int) $_GET['affid'];
    if (((int) $_GET['code']) > 0) {
        $_GET['code'] = (int) $_GET['code'];
        $is_aff_exist_rs = $db->query('select count(*) as aff_exist from tbl_affiliate where affiliate_status=1 and affiliate_id=' . $_GET['affid'] . ' and affiliate_code=' . $_GET['code']);
        $is_aff_exist = $db->fetch($is_aff_exist_rs);
        if (((int) $is_aff_exist['aff_exist']) === 1) {
            setcookie('affid', $_GET['affid'], time() + 30 * 24 * 3600, CONF_WEBROOT_URL);
            $srch = new SearchBase('tbl_referral_affiliate_clicks', 'c');
            $srch->addCondition('c.clicks_date', '=', $date);
            $srch->addCondition('c.clicks_affiliate_id', '=', $_GET['affid']);
            $result = $srch->getResultSet();
            $total_records = $srch->recordCount();
            if ($total_records == 0) {
                $record = new TableRecord('tbl_referral_affiliate_clicks');
                $record->setFldValue('clicks_date', $date);
                $record->setFldValue('clicks_affiliate_id', $_GET['affid']);
                if (!$record->addNew()) {
                    die($record->getError());
                }
            }
            $db->query("update tbl_referral_affiliate_clicks set clicks_affiliate = (clicks_affiliate + 1) where clicks_date = '" . $date . "' and clicks_affiliate_id = " . $_GET['affid']);
        }
    }
}
/* Track referrer and affiliate ends */
/* Track referrer and affiliate */
if (!isset($_COOKIE['affid']) && !isset($_COOKIE['refid']) && isset($_GET['refid']) && ((int) $_GET['refid']) > 0) {
    $_GET['refid'] = (int) $_GET['refid'];
    $is_ref_exist_rs = $db->query('select count(*) as ref_exist from tbl_users where user_deleted=0 and user_active=1 and user_email_verified=1 and user_id=' . $_GET['refid']);
    $is_ref_exist = $db->fetch($is_ref_exist_rs);
    if (((int) $is_ref_exist['ref_exist']) === 1) {
        setcookie('refid', $_GET['refid'], time() + 30 * 24 * 3600, CONF_WEBROOT_URL);
    }
}
/* Track referrer and affiliate ends */
setLangSessionVals(); // Added by Lakhvir
if ((strpos($_SERVER['SCRIPT_NAME'], 'manager/') != false) || (strpos($_SERVER['SCRIPT_NAME'], 'merchant/') !== false && strpos($_SERVER['SCRIPT_FILENAME'], '/merchant-favorite.php') === false) || (strpos($_SERVER['SCRIPT_NAME'], 'representative/') != false)) {
    $arr_page_css[] = 'manager/css/general.css';
    $arr_page_css[] = 'manager/css/navi.css';
    $arr_page_css[] = 'manager/css/style.css';
    $arr_page_css[] = 'manager/css/ionicons.css';
    $arr_page_css[] = 'manager/css/mbs-styles.css';
    $arr_common_js[] = 'js/jquery.tablednd_0_5.js';
    $arr_common_js[] = 'js/jquery-ui.js';
    $arr_common_js[] = 'manager/js/common_functions.js';
    $arr_page_css[] = 'css/system_messages.css';
} else {
    $arr_common_js[] = 'js/common_functions.js';
    $arr_common_js[] = 'js/slick.js';
    $arr_page_css[] = 'css/style.css';
    if ($pagename !== 'index') {
        $arr_page_css[] = 'css/inner.css';
    }
    $arr_page_css[] = 'css/mbs-styles.css';
    $arr_page_css[] = 'css/reset.css';
    $arr_page_css[] = 'css/mobile.css';
    $arr_page_css[] = 'css/ionicons.css';
    $arr_page_css[] = 'css/tablet.css';
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $arr_page_css[] = 'css/language.css';
    }
    $maintain = false;
    if (CONF_DIRECT_BROWSING_ALLOW == 1) {
        checkCity($maintain);
    } else {
        $maintain = (!checkForActiveCity()); /* If there is no city record then maintenance = true */
    }
    if ($maintain && !in_array($pagename, array('js', 'css', 'js-and-css.inc'))) {
        require_once './maintenance.php';
        exit();
    }
}
/* One time setup fields during installation */
$arr_tax_received = array(1 => t_lang('M_TXT_SELLER'), 2 => t_lang('M_TXT_ADMIN'));
$arr_tax_applicable_on = array(1 => t_lang('M_TXT_PRODUCT_PRICE_(EXCLUDING_COMMISSION)'), 2 => t_lang('M_TXT_PRODUCT_PRICE_(INCLUDING_COMMISSION)'), 3 => t_lang('M_TXT_PRODUCT_AND_SHIPPING_PRICE'));
/* One time setup fields during installation */