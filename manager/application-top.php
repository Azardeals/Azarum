<?php

require_once dirname(__FILE__) . '/../application-top.php';
$login_not_required_pages = ['login', 'forgot-password', 'reset-password'];
$is_admin_logged = checkAdminSession(false);
if (!in_array($pagename, $login_not_required_pages) && !$is_admin_logged) {
    if (substr($_SERVER['SCRIPT_NAME'], -9) == '-ajax.php') {
        die(t_lang('M_TXT_SESSION_EXPIRES'));
    }
    redirectUser('login.php');
}
if (in_array($pagename, $login_not_required_pages) && $is_admin_logged) {
    redirectUser('index.php');
}