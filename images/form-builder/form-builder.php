<?php

require_once '../application-top.php';

$arr_page_js = array();
$arr_page_css = array();

$arr_page_js[] = CONF_WEBROOT_URL . 'js/jquery-1.4.2.js';
$arr_page_js[] = CONF_WEBROOT_URL . 'facebox/facebox.js';
$arr_page_js[] = CONF_WEBROOT_URL . 'js/mbsmessage.js';
$arr_page_js[] = CONF_WEBROOT_URL . 'js/jquery.tablednd_0_5.js';
$arr_page_js[] = CONF_WEBROOT_URL . 'js/jquery.autocomplete.js';

/* calendar js */
$arr_page_js[] = CONF_WEBROOT_URL . 'js/calendar.js';
$arr_page_js[] = CONF_WEBROOT_URL . 'js/calendar-en.js';
$arr_page_js[] = CONF_WEBROOT_URL . 'js/calendar-setup.js';
/* end calendar js */

$arr_page_js[] = CONF_WEBROOT_URL . 'includes/form-validation.js.php';
$arr_page_js[] = CONF_WEBROOT_URL . 'includes/functions.js.php';

$arr_page_css[] = CONF_WEBROOT_URL . 'css/mbs-styles.css';

$arr_page_css[] = CONF_WEBROOT_URL . 'facebox/facebox.css';

/* calendar css */
$arr_page_css[] = CONF_WEBROOT_URL . 'css/cal-css/calendar-win2k-cold-1.css';
/* end calendar css */

$arr_page_css[] = CONF_WEBROOT_URL . 'css/cal/border-radius.css';
$arr_page_css[] = CONF_WEBROOT_URL . 'css/cal/steel/steel.css';
$arr_page_css[] = CONF_WEBROOT_URL . 'css/mbsmessage.css';

$arr_page_css[] = CONF_WEBROOT_URL . 'css/jquery.autocomplete.css';

include './includes/form-builder.php';
