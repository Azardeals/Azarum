<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isAffiliateUserLogged()) {
    $msg->display();
    die(t_lang('M_TXT_SESSION_EXPIRES'));
    require_once './msgdie.php';
    sprintf(t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN'), '<a href="' . CONF_WEBROOT_URL . 'login.php">' . t_lang('M_TXT_HERE') . '</a>');
    die();
}
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'REFERFRIENDS':
        $sql = $db->query("select * from tbl_email_templates where tpl_id=22");
        $email_data = $db->fetch($sql);
        $subject = $email_data['tpl_subject'];
        echo '<form name="user_msg_form" id="user_msg_form" action="?" method="POST" class="siteForm">
                <table class="formwrap__table">
                <tr>
                    <td colspan=2><textarea class="textBox_area" rows="" cols="" name="recipients" placeholder="' . t_lang('M_FRM_FRIENDS_EMAIL_ADDRESS_SUCCESS_PAGE') . '">' . $_POST['recipients'] . '</textarea>
                   <input type="hidden" name="email_subject" id="email_subject" value="' . $subject . '"/>
                   <input type="hidden" name="mode" id="mode" value="referfriendsubmit"/></td></tr>

                   <tr>
                <td colspan=2><textarea class="textBox_area" rows="" cols="" name="email_message" placeholder="' . t_lang('M_FRM_YOUR_MESSAGE_SUCCESS_PAGE') . '">http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?code=' . $_SESSION['logged_user']['affiliate_code'] . '&affid=' . $_SESSION['logged_user']['affiliate_id'] . '</textarea></td></tr>

               <tr><td>&nbsp;</td>
                <td><input type="submit" value="' . t_lang('M_TXT_SEND') . '"   name="submit_button"  ></td></tr>
           </table>
           </form>';
        break;
    case 'REFERFRIENDSSUBMIT':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['recipients'] != '' && $_POST['email_message']) {
                $recipients = $_POST['recipients'];
                $recipients = str_replace(' ', '', $recipients);
                $recipients_arr = explode(',', $recipients);
                $error = 0;
                foreach ($recipients_arr as $val) {
                    if (!filter_var($val, FILTER_VALIDATE_EMAIL) === false) {
                        //do nothing
                    } else {
                        $error = 1;
                    }
                }
                $sql = $db->query("select * from tbl_email_templates where tpl_id=22");
                $email_data = $db->fetch($sql);
                $subject = $_POST['email_subject'];
                $email_msg1 = $email_data['tpl_message'];
                $arr_replacements = array(
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxmessagexx' => t_lang('M_TXT_YOUR_FRIEND_HAS_REFERRED_LINK') . '<br/> ' . $_POST['email_message']
                );
                foreach ($arr_replacements as $key => $val) {
                    $email_msg1 = str_replace($key, $val, $email_msg1);
                }
                if ($error != 1) {
                    foreach ($recipients_arr as $val) {
                        sendMail($val, $subject, emailTemplate($email_msg1), $headers);
                    }
                    die(t_lang('M_TXT_MAIL_SENT'));
                } else {
                    die(t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID'));
                }
            } else {
                die(t_lang('M_ERROR_ENTER_EMAIL_ADDRESS_AND_MESSAGE'));
            }
        }
        break;
}