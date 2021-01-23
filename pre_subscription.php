<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isset($_POST['city']) || !is_numeric($_POST['city'])) {
    $msg->addError(t_lang('M_TXT_INVALID_REQUEST'));
    redirectUser(CONF_WEBROOT_URL);
}
$post = getPostedData();
$mainTableName = 'tbl_newsletter_subscription';
$primaryKey = 'subs_id';
$colPrefix = 'subs_';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $post['subs_tick'] == 1) {
    if (!filter_var($_POST['sub_email'], FILTER_VALIDATE_EMAIL)) {
        $msg->addError(t_lang('M_TXT_INVALID_EMAIL_ADDRESS'));
        redirectUser(CONF_WEBROOT_URL);
    } else {
        $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email='" . $post['sub_email'] . "' and  subs_city='" . $post['city'] . "'");
        $result = $db->fetch($check_unique);
        if ($db->total_records($check_unique) == 0) {
            $record = new TableRecord($mainTableName);
            $record->assignValues($post);
            $code = mt_rand(0, 999999999999999);
            $record->setFldValue('subs_addedon', date('Y-m-d H:i:s'), true);
            $record->setFldValue('subs_code', $code, '');
            $record->setFldValue('subs_email', $post['sub_email'], '');
            $record->setFldValue('subs_email_verified', 1, '');
            $record->setFldValue('subs_city', $post['city'], '');
            $email = $post['sub_email'];
            $success = $record->addNew();
            if ($success) {
                $nc_subs_id = $record->getId();
                insertsubscatCity($nc_subs_id);
                if (is_numeric($post['city'])) {
                    selectCity(intval($post['city']));
                }
                subscribeToMailChimp($post); //function defined in site-functions.php
                $rs = $db->query("select * from tbl_email_templates where tpl_id=5");
                $row_tpl = $db->fetch($rs);
                if (is_numeric($post['city'])) {
                    selectCity(intval($post['city']));
                }
                $messageAdmin = 'Dear ' . CONF_EMAILS_FROM_NAME . ',' . $email . ' is subscribing your newsletter.';
                $message = $row_tpl['tpl_message'];
                $subject = $row_tpl['tpl_subject'];
                $arr_replacements = [
                    'xxemailxx' => $email,
                    'xxsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxcityxx' => $_SESSION['city_to_show'],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL
                ];
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($_SESSION['city_to_show'] != "") {
                    if ($row_tpl['tpl_status'] == 1) {
                        sendMail($email, $subject . ' - ' . time(), emailTemplate($message));
                    }
                }
                $msg->addMsg(t_lang('M_TXT_THANKYOU_FOR_SUBSCRIBING_WITH_US'));
                setcookie('city_subscriber', true, time() + 30 * 24 * 3600, CONF_WEBROOT_URL);
                redirectUser(CONF_WEBROOT_URL);
            }
        } else {
            if (is_numeric($post['city'])) {
                selectCity(intval($post['city']));
                setcookie('city_subscriber', true, time() + 30 * 24 * 3600, CONF_WEBROOT_URL);
            }
            $msg->addMsg(t_lang('M_TXT_YOU_HAVE_ALREADY_SUBSCRIBED_WITH_US'));
            redirectUser(CONF_WEBROOT_URL);
        }
    }
}
$msg->addError(t_lang('M_TXT_INVALID_REQUEST'));
redirectUser(CONF_WEBROOT_URL);
