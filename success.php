<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './site-classes/order.cls.php';
$cart = new Cart();
$cart->clearCart();
if (isset($_SESSION['token']) && $_SESSION['token'] != "") {
    redirectUser((CONF_WEBROOT_URL . 'api/app-success.php?order_id=' . $_GET['dp_id']));
} else {
    if (!isUserLogged()) {
        $_SESSION['login_page'] = $_SERVER['REQUEST_URI'];
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
    }
}
$order_id = $_GET['dp_id'];
$srch = new SearchBase('tbl_order_deals', 'od');
$srch->joinTable('tbl_orders', 'INNER JOIN', 'o.`order_id` = od.`od_order_id`', 'o');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'd.`deal_id` = od.`od_deal_id`', 'd');
$srch->addCondition('od.od_order_id', '=', $order_id);
$srch->doNotCalculateRecords();
$srch->setPageSize(1);
$srch->setPageNumber(1);
$srch->addMultipleFields(array('order_id', 'order_payment_mode', 'order_payment_status', 'od_deal_name', 'd.deal_type', 'd.deal_sub_type', 'd.deal_id'));
$order_details_rs = $srch->getResultSet();
$s_odr_row = [];
while ($row = $db->fetch($order_details_rs)) {
    $deal_name = $row['od_deal_name'];
    $s_odr_row = $row;
}
if (empty($s_odr_row)) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'buy-deal.php'));
}
$sql = $db->query("select * from tbl_email_templates where tpl_id=22");
$email_data = $db->fetch($sql);
$subject = $email_data['tpl_subject'];
$email_msg1 = $email_data['tpl_message'];
$arr_replacements = array(
    'xxsite_namexx' => CONF_SITE_NAME,
    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
    'xxwebrooturlxx' => CONF_WEBROOT_URL,
    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
    'xxmessagexx' => nl2br(htmlentities($_POST['email_message']))
);
foreach ($arr_replacements as $key => $val) {
    $email_msg1 = str_replace($key, $val, $email_msg1);
}
$disable_button = '';
/* CODE FOR SENDING THE EMAILS TO THE USERS START HERE */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['deal_name'] != "") {
    if ($_POST['recipients'] != '' && $_POST['email_message']) {
        $recipients = $_POST['recipients'];
        $recipients = nl2br($recipients);
        $recipients = str_replace('<br/>', '', $recipients);
        $recipients = str_replace(' ', '', $recipients);
        $recipients_arr = explode(',', $recipients);
        $error = 0;
        foreach ($recipients_arr as $val) {
            $val = strip_tags($val);
            $val = str_replace('<br/>', '', $val);
            if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $error = 1;
            }
        }
        $subject = $_POST['email_subject'];
        $email_msg1 = emailTemplate($email_msg1);
        if ($error != 1) {
            foreach ($recipients_arr as $val) {
                $val = strip_tags($val);
                $val = str_replace('<br/>', '', $val);
                sendMail(trim($val), $subject, $email_msg1, $headers);
            }
            $disable_button = "disabled";
        } else {
            $msg->addError(t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID'));
        }
    } else {
        #$msg->addError(t_lang('M_ERROR_ENTER_EMAIL_ADDRESS_AND_MESSAGE'));
    }
}
/* CODE FOR SENDING THE EMAILS TO THE USERS END HERE */
require_once './header.php';
?>
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_CONGRATULATIONS'); ?></h3>
            </aside>
        </div>
    </div>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="layout__centered">
                    <div class="content__centered">
                        <h3><?php echo unescape_attr(t_lang('M_TXT_NOW_SHARE_IT')); ?></h3>
                        <p><?php
                            if (isset($s_odr_row) && $s_odr_row['order_payment_mode'] == 3 && $s_odr_row['order_payment_status'] == 1) {
                                /* The message is for paid orders, payment through wallet */
                                echo t_lang('M_TXT_SUCCESS_PAID_DEAL');
                            } else {
                                if ($s_odr_row['deal_type'] == 0) {
                                    echo t_lang('M_TXT_SUCCESSFULLY_BOUGHT_DEAL');
                                }
                            }
                            ?>
                            &nbsp;&nbsp;<a href="<?php echo CONF_WEBROOT_URL; ?>"> <?php echo t_lang('M_TXT_Continue_browsing_other_deals...'); ?> </a>
                        </p>
                    </div>
                    <div class="content__centered bg__grey">
                        <h3><?php echo t_lang('M_TXT_ORDER_ID'); ?>: <strong><?php echo $order_id; ?> </strong></h3>
                        <p><strong><?php echo CONF_SITE_NAME; ?></strong>
                            <?php echo ' ' . t_lang('M_TXT_OFFER_REFERAL_COMMISSION_SUCCESS_PAGE'); ?></p>
                        <?php
                        if ($s_odr_row['deal_type'] == 1 && $s_odr_row['deal_sub_type'] == 1) {
                            require_once './site-classes/digital-product.cls.php';
                            $dg = new DigitalProduct();
                            $dgProduct_data = $dg->getDigitalProductRecord($s_odr_row['deal_id']);
                            if ($dgProduct_data && (($dgProduct_data['dpe_product_file'] != "" && $dgProduct_data['dpe_product_external_url'] != ""))) {
                                echo "<h4><a href='" . CONF_WEBROOT_URL . "my-deals'>" . t_lang('M_TXT_SEND_DIGITAL_MSG_FOR_VOUCHER_PAGE') . "</a></h4>";
                            } else if ($dgProduct_data && ($dgProduct_data['dpe_product_file'] == "" && $dgProduct_data['dpe_product_external_url'] != "")) {
                                echo "<h4>" . t_lang('M_TXT_SEND_DIGITAL_MSG') . "</h4>";
                            } else if ($dgProduct_data && ($dgProduct_data['dpe_product_file'] != "" && $dgProduct_data['dpe_product_external_url'] == "")) {
                                echo "<h4><a href='" . CONF_WEBROOT_URL . "my-deals'>" . t_lang('M_TXT_SEND_DIGITAL_MSG_FOR_VOUCHER_PAGE') . "</a></h4>";
                            } else if (!$dgProduct_data || ($dgProduct_data['dpe_product_file'] == "" && $dgProduct_data['dpe_product_external_url'] == "")) {
                                echo "<h4>" . t_lang('M_TXT_SEND_DIGITAL_MSG') . "</h4>";
                            }
                        }
                        ?>
                    </div>
                    <div class="box__bordered tabspanel">
                        <ul class="boxed__tabs normaltabs">
                            <li><a class="active" href="javascript:void(0)" rel="tab__1"><?php echo t_lang('M_TXT_SHARE_WITH_SOCIAL_NETWORKS'); ?> </a></li>
                            <li><a href="javascript:void(0)" rel="tab__2"><?php echo t_lang('M_TXT_SHARE_WITH_SOCIAL_EMAIL'); ?></a></li>
                        </ul>
                        <div class="tabspanel__container cover__grey clearfix">
                            <?php if (CONF_SSL_ACTIVE == 1) { ?>
                                <script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
                            <?php } else { ?>
                                <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
                            <?php } ?>									
                            <div id="tab__1" class="tabspanel__content">
                                <ul class="icons__socialmedia">
                                    <li class="fb"><a href="javascript:void(0);"  onclick="social_media_share('facebook_share', '<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>');" ><i class="icon ion-social-facebook"></i><span >Facebook</span></a></li>
                                    <li class="tw"><a href="javascript:void(0);" onclick="social_media_share('twitter_share', '<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>');" class='st_twitter_large' displayText='Tweet'><i class="icon ion-social-twitter"></i><span>Twitter</span></a></li>
                                    <li class="li"><a href="javascript:void(0);" onclick="social_media_share('linkedin_share', '<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>');" class='st_linkedin_large' displayText='LinkedIn'><i class="icon ion-social-linkedin"></i><span>Linkedin</span></a></li>
                                    <li class="pt"><a onclick="social_media_share('pinterest_share', '<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>');" href="javascript:void(0);" class='st_pinterest_large' displayText='Pinterest'><i class="icon ion-social-pinterest"></i><span>Pinterest</span></a></li>
                                </ul>
                                <span class="gap"></span><span class="gap"></span>
                                <h5><?php echo t_lang('M_TXT_SHARE_LINK'); ?></h5>
                                <div class="form__small siteForm">
                                    <ul>
                                        <li>
                                            <input type="text"  id="copyTarget" value="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>" >
                                        </li>
                                        <li><input type="button" id="copyButton" class="btn" value="Copy Link"  ></li>
                                        <li><span id="msg1"></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div id="tab__2" class="tabspanel__content">
                                <h5><?php echo t_lang('M_TXT_SHARE_WITH_SOCIAL_EMAIL'); ?></h5>
                                <div class="formwrap">
                                    <form name='user_msg_form' class="siteForm" id='user_msg_form' action='?' method='POST'>
                                        <table class="formwrap__table">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="deal_name" id="deal_name" value="<?php echo $deal_name; ?>"><input type="hidden" name="email_subject" id="email_subject" value="<?php echo $subject; ?>"/></td></tr>
                                                <tr><td><?php echo t_lang('M_FRM_FRIENDS_EMAIL_ADDRESS_SUCCESS_PAGE'); ?><textarea class="textBox_area" rows="" cols="" name="recipients"><?php echo $_POST['recipients']; ?></textarea></td>
                                                </tr>
                                                <tr><td ><?php echo t_lang('M_FRM_YOUR_MESSAGE_SUCCESS_PAGE'); ?><textarea class="textBox_area"  name="email_message"><?php
                                                            if ($_POST['email_message'] != '') {
                                                                echo $_POST['email_message'];
                                                            } else {
                                                                echo $email_msg;
                                                            }
                                                            ?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <?php
                                                    if ($disable_button != "") {
                                                        $msg->addMsg(t_lang('M_TXT_THANKS_MESSAGE_SUCCESS_PAGE'));
                                                        //  echo $msg->display();
                                                    }
                                                    ?>
                                                    <td>
                                                        <input type="submit" value="<?php echo t_lang('M_FRM_SEND_TO_FRIENDS'); ?>" class="" name="submit_button" <?php echo $disable_button; ?> >
                                                        <a class="linknormal" href="<?php echo CONF_WEBROOT_URL; ?>"><?php echo t_lang('M_TXT_NO_THANKS'); ?></a></td> </tr></tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $cart->clearCart(); ?>
<script type="text/javascript">
    $("#copyButton").live("click", function () {
        copyToClipboardMsg(document.getElementById("copyTarget"), "msg1");
    });
    function copyToClipboardMsg(elem, msgElem) {
        var succeed = copyToClipboard(elem);
        var msg;
        if (!succeed) {
            msg = "Copy not supported or blocked.  Press Ctrl+c to copy."
        } else {
            msg = "Text copied to the clipboard."
        }
        if (typeof msgElem === "string") {
            msgElem = document.getElementById(msgElem);
        }
        msgElem.innerHTML = msg;
        setTimeout(function () {
            msgElem.innerHTML = "";
        }, 2000);
    }
    function copyToClipboard(elem) {
        // create hidden text element, if it doesn't already exist
        var targetId = "_hiddenCopyText_";
        var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
        var origSelectionStart, origSelectionEnd;
        if (isInput) {
            // can just use the original source element for the selection and copy
            target = elem;
            origSelectionStart = elem.selectionStart;
            origSelectionEnd = elem.selectionEnd;
        } else {
            // must use a temporary form element for the selection and copy
            target = document.getElementById(targetId);
            if (!target) {
                var target = document.createElement("textarea");
                target.style.position = "absolute";
                target.style.left = "-9999px";
                target.style.top = "0";
                target.id = targetId;
                document.body.appendChild(target);
            }
            target.textContent = elem.textContent;
        }
        // select the content
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);
        // copy the selection
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch (e) {
            succeed = false;
        }
        // restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }
        if (isInput) {
            // restore prior selection
            elem.setSelectionRange(origSelectionStart, origSelectionEnd);
        } else {
            // clear temporary content
            target.textContent = "";
        }
        return succeed;
    }
</script>
<?php
require_once './footer.php';
exit;
