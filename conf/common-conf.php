<?php

/**
 * General configurations
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
define('CONF_DEVELOPMENT_MODE', true);
define('CONF_LIB_HALDLE_ERROR_IN_PRODUCTION', true);
define('CONF_INSTALLATION_PATH', $_SERVER['DOCUMENT_ROOT'] . CONF_WEBROOT_URL);
$_SESSION['WYSIWYGFileManagerRequirements'] = realpath(dirname(__FILE__) . '/WYSIWYGFileManagerRequirements.php');
define('CONF_DATE_FIELD_TRIGGER_IMG', CONF_WEBROOT_URL . 'images/calender.png');
define('CONF_HTML_EDITOR', 'innova');
define('CONF_CKEDITOR_PATH', CONF_WEBROOT_URL . 'innova-lnk');
$innova_settings = [
    'width' => '"100%"',
    'height' => '500',
    'css' => '"' . CONF_WEBROOT_URL . 'innova-lnk/styles/default.css"',
    'groups' => '[
        ["group1", "", ["Bold", "Italic" , "Underline", "FontDialog", "ForeColor", "TextDialog", "RemoveFormat", "Paragraph"]],
        ["group2", "", ["Bullets", "Numbering", "JustifyLeft", "JustifyCenter", "JustifyRight"]],
        ["group5", "", ["Undo", "Redo", "SourceDialog"]]
    ]',
];
define('CONF_VIEW_PATH', CONF_INSTALLATION_PATH . 'includes/partial-views/');
define('CONF_FREE_WALLET_CREDIT_SCHEME', 1);
define('CONF_FREE_WALLET_CREDIT_AMOUNT', 100);
define('CONF_FREE_WALLET_CREDIT_SCHEME_ACTIVE_TILL', '');
define('CONF_IMAGE_MAX_SIZE', '5242880'); /* 5 MB = 5242880 BYTES */
define('CONF_WEB_APP_VERSION', 'V4.2.1');
define('CONF_ADMIN_PAGE_SIZE', '10');

$arr_deal_status = ['Upcoming', 'Open', 'Ended', 'Cancelled', 'Paid to Company', 'Pending Approval', 'Rejected'];
$arr_nav_type = [0 => 'CMS page', 1 => 'Custom HTML', 2 => 'External URL'];
$arr_email_notification = [0 => 'Near to Expire', 1 => 'Earned Deal Buck', 2 => 'Friend Buy Deal'];
$arr_user_status = ['1' => 'Active', '0' => 'Inactive'];
$arr_sale_earning = ['1' => 'Upto 1,000', '5' => '1,000 to 5,000', '25' => '5,001 to 25,000', '50' => '25,001 to 50,000', '100' => '50,001 to 100,000', '1000' => 'Above 100,000'];
define($name, $arr_sale_earning);

define('CONF_DIRECTORY_SEPARATOR', '/');
define('CONF_USER_UPLOADS', CONF_INSTALLATION_PATH . 'user-uploads' . CONF_DIRECTORY_SEPARATOR);
define('BACKGROUND_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/background-images' . CONF_DIRECTORY_SEPARATOR);
define('BACKGROUND_IMAGES_PATH', CONF_USER_UPLOADS . 'background-images' . CONF_DIRECTORY_SEPARATOR);
define('BANNER_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/banner-images' . CONF_DIRECTORY_SEPARATOR);
define('BANNER_IMAGES_PATH', CONF_USER_UPLOADS . 'banner-images' . CONF_DIRECTORY_SEPARATOR);
define('BLOG_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/blog-images' . CONF_DIRECTORY_SEPARATOR);
define('BLOG_IMAGES_PATH', CONF_USER_UPLOADS . 'blog-images' . CONF_DIRECTORY_SEPARATOR);
define('CATEGORY_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/category-images' . CONF_DIRECTORY_SEPARATOR);
define('CATEGORY_IMAGES_PATH', CONF_USER_UPLOADS . 'category-images' . CONF_DIRECTORY_SEPARATOR);
define('CHARITY_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/charity-images' . CONF_DIRECTORY_SEPARATOR);
define('CHARITY_IMAGES_PATH', CONF_USER_UPLOADS . 'charity-images' . CONF_DIRECTORY_SEPARATOR);
define('COMPANY_LOGO_URL', CONF_WEBROOT_URL . 'user-uploads/company-logo' . CONF_DIRECTORY_SEPARATOR);
define('COMPANY_LOGO_PATH', CONF_USER_UPLOADS . 'company-logo' . CONF_DIRECTORY_SEPARATOR);
define('DEAL_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/deal-images' . CONF_DIRECTORY_SEPARATOR);
define('DEAL_IMAGES_PATH', CONF_USER_UPLOADS . 'deal-images' . CONF_DIRECTORY_SEPARATOR);
define('DIGITAL_UPLOADS_URL', CONF_WEBROOT_URL . 'user-uploads/digital-uploads' . CONF_DIRECTORY_SEPARATOR);
define('DIGITAL_UPLOADS_PATH', CONF_USER_UPLOADS . 'digital-uploads' . CONF_DIRECTORY_SEPARATOR);
define('LOGO_URL', CONF_WEBROOT_URL . 'user-uploads/logo' . CONF_DIRECTORY_SEPARATOR);
define('LOGO_PATH', CONF_USER_UPLOADS . 'logo' . CONF_DIRECTORY_SEPARATOR);
define('SUPPORT_FILES_URL', CONF_WEBROOT_URL . 'user-uploads/merchant-support-attached-files' . CONF_DIRECTORY_SEPARATOR);
define('SUPPORT_FILES_PATH', CONF_USER_UPLOADS . 'merchant-support-attached-files' . CONF_DIRECTORY_SEPARATOR);
define('NAVIGATION_BULLETS_URL', CONF_WEBROOT_URL . 'user-uploads/navigation-bullets' . CONF_DIRECTORY_SEPARATOR);
define('NAVIGATION_BULLETS_PATH', CONF_USER_UPLOADS . 'navigation-bullets' . CONF_DIRECTORY_SEPARATOR);
define('NEWS_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/news-images' . CONF_DIRECTORY_SEPARATOR);
define('NEWS_IMAGES_PATH', CONF_USER_UPLOADS . 'news-images' . CONF_DIRECTORY_SEPARATOR);
define('TEMP_XLS_URL', CONF_WEBROOT_URL . 'user-uploads/temp-xls' . CONF_DIRECTORY_SEPARATOR);
define('TEMP_XLS_PATH', CONF_USER_UPLOADS . 'temp-xls' . CONF_DIRECTORY_SEPARATOR);
define('THINGS_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/things-images' . CONF_DIRECTORY_SEPARATOR);
define('THINGS_IMAGES_PATH', CONF_USER_UPLOADS . 'things-images' . CONF_DIRECTORY_SEPARATOR);
define('FAQ_GALLERY_URL', CONF_WEBROOT_URL . 'user-uploads/upload-faq-image-gallery' . CONF_DIRECTORY_SEPARATOR);
define('FAQ_GALLERY_PATH', CONF_USER_UPLOADS . 'upload-faq-image-gallery' . CONF_DIRECTORY_SEPARATOR);
define('IMAGE_GALLERY_URL', CONF_WEBROOT_URL . 'user-uploads/upload-image-gallery' . CONF_DIRECTORY_SEPARATOR);
define('IMAGE_GALLERY_PATH', CONF_USER_UPLOADS . 'upload-image-gallery' . CONF_DIRECTORY_SEPARATOR);
define('UPLOADS_URL', CONF_WEBROOT_URL . 'user-uploads/uploads' . CONF_DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', CONF_USER_UPLOADS . 'uploads' . CONF_DIRECTORY_SEPARATOR);
define('USER_IMAGES_URL', CONF_WEBROOT_URL . 'user-uploads/user-images' . CONF_DIRECTORY_SEPARATOR);
define('USER_IMAGES_PATH', CONF_USER_UPLOADS . 'user-images' . CONF_DIRECTORY_SEPARATOR);
