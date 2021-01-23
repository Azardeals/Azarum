<?php

require_once '../application-top.php';

$arr_page_js = array();
$arr_page_css = array();

$arr_page_js[] = './js/jquery-1.4.2.js';
$arr_page_js[] = './facebox/facebox.js';
$arr_page_js[] = './js/mbsmessage.js';
$arr_page_js[] = './js/jquery.tablednd_0_5.js';
$arr_page_js[] = './js/jquery.autocomplete.js';

/* calendar js */
$arr_page_js[] = './js/calendar.js';
$arr_page_js[] = './js/calendar-en.js';
$arr_page_js[] = './js/calendar-setup.js';
/* end calendar js */

$arr_page_js[] = './includes/form-validation.js.php';
$arr_page_js[] = './includes/functions.js.php';

$arr_page_css[] = './css/mbs-styles.css';

$arr_page_css[] = './facebox/facebox.css';

/* calendar css */
$arr_page_css[] = './css/cal-css/calendar-win2k-cold-1.css';
/* end calendar css */

$arr_page_css[] = './css/cal/border-radius.css';
$arr_page_css[] = './css/cal/steel/steel.css';
$arr_page_css[] = './css/mbsmessage.css';

$arr_page_css[] = './css/jquery.autocomplete.css';

/* error_reporting(E_ALL);
  ini_set('display_errors', 1); */

include './includes/form-builder.php';
