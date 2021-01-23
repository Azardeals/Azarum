<?php
require_once './application-top.php';
if (isset($_GET['code']) && isset($_GET['email'])) {
    if (!empty($_GET['code'])) {
        $check_unique = $db->query("select * from tbl_newsletter_subscription where subs_email=" . $db->quoteVariable($_GET['email']) . " and  subs_code=" . $db->quoteVariable($_GET['code']));
        $result = $db->fetch($check_unique);
        if ($result['subs_email_verified'] == 1) {
            if ($db->total_records($check_unique) > 0) {
                $db->query("delete from   tbl_newsletter_subscription where subs_email=" . $db->quoteVariable($_GET['email']) . " and  subs_code=" . $db->quoteVariable($_GET['code']));
                $msg->addMsg(t_lang('M_TXT_NEWSLETTER_SUBSCRIBED'));
            } else {
                $msg->addError(t_lang('M_TXT_EMAIL_NOT_VERIFIED'));
            }
        } else {
            $msg->addError(t_lang('M_TXT_UNSUBSCRIPTION_CODE_EXPIRED'));
        }
    } else {
        $msg->addError(t_lang('M_TXT_UNSUBSCRIPTION_FROM_MERCHANT'));
    }
    redirectUser(CONF_WEBROOT_URL);
}
require_once './header.php';
if (strpos($_SERVER['REQUEST_URI'], 'newsletter-subscription.php') == true) {
    echo '<link rel="stylesheet" type="text/css" href="' . CONF_WEBROOT_URL . 'css/mbs-styles.css?sid=1293283535" /> ';
}
$mainTableName = 'tbl_newsletter_subscription';
$primaryKey = 'subs_id';
$colPrefix = 'subs_';
$unsubscribe = $db->query("delete FROM tbl_newsletter_subscription WHERE DATEDIFF(CURRENT_DATE(),subs_addedon)>=2 and subs_email_verified = 0");
$url = $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL;
if (isset($_POST['subs_email']) && $_POST['subs_email'] != "") {
    if (isset($_SESSION['city_to_show'])) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = getPostedData();
            $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email='" . $post['subs_email'] . "' and  subs_city='" . $post['subs_city'] . "'");
            $result = $db->fetch($check_unique);
            if ($db->total_records($check_unique) == 0) {
                $record = new TableRecord($mainTableName);
                $record->assignValues($post);
                $code = mt_rand(0, 999999999999999);
                $record->setFldValue('subs_addedon', date('Y-m-d H:i:s'), true);
                $record->setFldValue('subs_code', $code, '');
                if (isset($_COOKIE['affid']))
                    $record->setFldValue('subs_affiliate_id', $_COOKIE['affid'] + 0);
                $email = $post['subs_email'];
                $success = $record->addNew();
                if ($success) {
                    $rs = $db->query("select * from tbl_email_templates where tpl_id=5");
                    $row_tpl = $db->fetch($rs);
                    $messageAdmin = 'Dear Admin,
				' . $email . ' is subscribing your newsletter.';
                    $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                    $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                    $arr_replacements = array(
                        'xxemailxx' => $email,
                        'xxverificationcodexx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'newsletter-subscription.php?code=' . $code . '&mail=' . $email,
                        'xxsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                        'xxcityxx' => $_SESSION['city_to_show'],
                        'xxsite_namexx' => CONF_SITE_NAME,
                        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                        'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
                        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                        'xxwebrooturlxx' => CONF_WEBROOT_URL
                    );
                    foreach ($arr_replacements as $key => $val) {
                        $subject = str_replace($key, $val, $subject);
                        $message = str_replace($key, $val, $message);
                    }
                    if ($_SESSION['city_to_show'] != "") {
                        if ($row_tpl['tpl_status'] == 1) {
                            sendMail($email, $subject . ' - ' . time(), emailTemplate($message), $headers);
                        }
                    }
                    ##############################################	
                    ?>
                    <script >
                        setTimeout("faceboxDisp()", 2000);
                        function faceboxDisp() {
                            $.facebox('<div class="div_msg"><ul><li><?php echo addslashes(t_lang('M_TXT_SUBSCRIBED_SUCCESSFULLY')); ?></li></ul>	</div>');
                        }
                    </script>
                    <?php
                    //redirectUser();
                }
            } else {
                //$msg->addError('Email address for this city already exist!.');
                ?>
                <script >
                    setTimeout("faceboxDisp()", 2000);
                    function faceboxDisp() {
                        $.facebox('<div class="div_error"><?php echo addslashes(t_lang('M_TXT_THE_FOLLOWING_ERROR_OCCURED')); ?><ul><li><?php echo addslashes(t_lang('M_TXT_EMAIL_ADDRESS_ALREADY_EXISTS_FOR_THIS_CITY')); ?></li></ul></div>');
                    }
                </script>
                <?php
            }
        }
    } else {
        ?>
        <script type="text/javascript">
            setTimeout("faceboxDisp()", 2000);
            function faceboxDisp() {
                $.facebox('<div class="div_error"><?php echo addslashes(t_lang('M_TXT_THE_FOLLOWING_ERROR_OCCURED')); ?><ul><li><?php echo addslashes(t_lang('M_TXT_SESSION_EXPIRES')); ?></li></ul></div>');
            }
        </script>
        <?php
    }
}
if (isset($_GET['code']) && isset($_GET['mail'])) {
    $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email=" . $db->quoteVariable($_GET['mail']) . " and  subs_code=" . $db->quoteVariable($_GET['code']) . "");
    $result = $db->fetch($check_unique);
    if ($result['subs_email_verified'] == 0) {
        if ($db->total_records($check_unique) > 0) {
            $db->query("update  tbl_newsletter_subscription set subs_email_verified = 1 where subs_email=" . $db->quoteVariable($_GET['mail']) . " and  subs_code=" . $db->quoteVariable($_GET['code']) . "");
            $msg->addMsg(t_lang('M_TXT_EMAIL_VERIFIED'));
        } else {
            $msg->addError(t_lang('M_TXT_EMAIL_NOT_VERIFIED'));
        }
    } else {
        $msg->addMsg(t_lang('M_TXT_EMAIL_ALREADY_VERIFIED'));
    }
    require_once './msgdie.php';
} else {
    if (strpos($_SERVER['REQUEST_URI'], 'newsletter-subscription.php') == false) {
        $frmNewsletter = getMBSFormByIdentifier('frmNewsletter');
        $fld = $frmNewsletter->getField('subs_email');
        $fld->extra = 'class="subField" title="' . t_lang('M_TXT_EMAIL_ADDRESS') . '"';
        $fld->value = '';
        $fld = $frmNewsletter->getField('submit');
        $fld->extra = 'class="signMe" ';
        $fld->value = t_lang('M_TXT_SIGN_ME_UP');
        $fld = $frmNewsletter->getField('subs_city');
        $fld->value = $_SESSION['city'];
        echo '<div id="subscribe_openMain">
                            <div id="subscribe_openWrapper" style="display: none; ">
                                    <span>
                                    <div class="subscribeHead">';
        if (isset($_SESSION['city_to_show'])) {
            echo '<h3>' . t_lang('M_TXT_SUBSCRIBE_TO') . ' ' . $_SESSION['city_to_show'] . '</h3> ';
        } else {
            echo '<h3>' . t_lang('M_TXT_CITY_NOT_SELECTED') . '</h3>';
        }
        echo '<p>' . t_lang('M_TXT_GET_WEEKLY_EMAILS') . ' ' . $_SESSION['city_to_show'] . '</p>
                                    </div>
                                    <div class="subscribeWrap">
                                            <div class="subscribeWrap_right">
                                            <h5>' . t_lang('M_TXT_WHATS_YOUR_EMAIL_ADDRESS') . '</h5>
                                            <p>' . t_lang('M_TXT_AFTER_SUBSCRIBING') . '</p> 
                                                            ' . $frmNewsletter->getFormTag() . '
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="subscribeTable">
                                              <tbody><tr>
                                                <td>' . $frmNewsletter->getFieldHTML('subs_city') . '</td>
                                                <td>&nbsp;</td>
                                              </tr>
                                              <tr>
                                                <td>' . $frmNewsletter->getFieldHTML('subs_email') . '</td>
                                                <td>' . $frmNewsletter->getFieldHTML('submit') . '</td>
                                              </tr>
                                              <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                              </tr>
                                            </tbody></table></form>' . $frmNewsletter->getExternalJS() . '
                                        </div>
                                    </div>
                                </span>
                            </div>
                            </div>';
    }
}
            