<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isUserLogged()) {
    header('Location: ' . friendlyUrl(CONF_WEBROOT_URL . "start_refer_friends.php"));
    exit();
}

function close_popup()
{
    ?>
    <script type="text/javascript"> window.close();</script>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    $post = getPostedData($_POST);
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $fromemail = CONF_EMAILS_FROM;
    $fromname = CONF_EMAILS_FROM_NAME;
    $headers .= "From: " . $fromname . " <" . $fromemail . ">\r\n";
    $subject = htmlentities($_SESSION['logged_user']['user_name']) . " " . htmlentities($_SESSION['logged_user']['user_lname']) . t_lang("M_TXT_IS_INVITING_YOU_TO") . CONF_SITE_NAME;
    $mesg = sprintf(unescape_attr(t_lang('M_TXT_CONTACT_INVITATION_MAIL_MSG')), CONF_SITE_NAME, "http://" . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . "?refid=" . $_SESSION['logged_user']['user_id'], CONF_SITE_NAME, $post['mail_msg']);
    $i = 0;
    foreach ($post as $keyname => $value) {
        if (substr($keyname, 0, 8) == 'send_to_') {
            $name = ucwords(str_replace('_', ' ', substr($keyname, 7)));
            $email = $value;
            if (trim($name) == '') {
                $name = substr($email, 0, (strpos($email, '@', 0) + 1));
            }
            $message = "Dear " . $name . ",<br /><br />" . $mesg;
            if (mail($email, $subject, $message, $headers)) {
                $i++;
            }
        } else {
            continue;
        }
    }
    $msg->addMsg($i . " " . t_lang('M_TXT_CONTACTS_INVITED'));
    close_popup();
    exit();
}
require_once './header.php';
if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
    ?> 
    <div  id="msg">
        <div class="system-notice error"><a class="closeMsg" href="javascript:void(0);" onclick="$(this).closest('#msg').hide(); return false;"><img src="<?php echo CONF_WEBROOT_URL; ?>images/cross.png"></a><p id="message"><?php echo $msg->display(); ?> </p></div>
    </div>
<?php } ?>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>contact-importer/yahoo/popupmanager.js"></script>
<script type="text/javascript">
        function popupcenter(pageURL)
        {
            PopupManager.open(pageURL, 600, 600);
        }
</script>
<!--bodyContainer start here-->
<?php require_once './left-panel-links.php'; ?>
<div class="sectionRight">
    <div class="pagetitle">
        <?php if (isset($_GET['provider_box'])) { ?> 
            <a href="<?php echo CONF_WEBROOT_URL; ?>social_refer_friends.php" class="backbtn"><?php echo t_lang('M_TXT_BACK'); ?></a>
        <?php } ?> 
        <h2><?php echo t_lang('M_TXT_REFER_FRIENDS'); ?></h2>
    </div>
    <ul class="whitetabLinks">	
        <li><a href="javascript:void(0);" onclick="return popupcenter('contact-importer/?provider=gmail')">
                <span class="spantxt"><?php echo t_lang('M_TXT_INVITE_YOUR_FRINDS'); ?> <?php echo t_lang('M_TXT_FROM'); ?></span>
                <img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/gmail.png" class="iconGm"></a></li>
        <li><a href="javascript:void(0);" onclick="return popupcenter('contact-importer/yahoo/index.php')">
                <span class="spantxt"><?php echo t_lang('M_TXT_INVITE_YOUR_FRINDS'); ?> <?php echo t_lang('M_TXT_FROM'); ?> </span>
                <img class="iconYahoo" alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/yahoo.png">
            </a></li>
    </ul>
</div> 
</section> 
<!--bodyContainer end here-->
</div> 
<?php require_once './footer.php'; ?>