<?php
require_once '../application-top.php';
require_once '../site-classes/merchant-support.cls.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$status = (int) $_GET['status'];
$ticket_id = (int) $_GET['tid'];
$merchant_support = new merchantSupport();
$frm = $merchant_support->getMerchantSupportForm($ticket_id);
if ($ticket_id > 0) {
    $fld = $frm->getField('title');
    $frm->removeField($fld);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    $post['files'] = $_FILES['files'];
    if ($post['ticket_id'] > 0) {
        $action = $merchant_support->sendMessage($post, $status);
    } else {
        $action = $merchant_support->createTicket($post);
    }
    if (!$action)
        $frm->fill($post);
}
require_once './header.php';
?>
<ul class="nav-left-ul">
    <li><a href="message-listing.php?status=0"><?php echo t_lang('M_TXT_ALL_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=1"><?php echo t_lang('M_TXT_UNREAD_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=2"><?php echo t_lang('M_TXT_ARCHIVED'); ?></a></li>
    <li><a href="message.php" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message.php')) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_NEW_MESSAGE'); ?></a></li>
</ul>
</div>
</td>
<td class="right-portion">
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_MESSAGES'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg">
                <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a>
            </div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?></div>
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"><?php echo $msg->display(); ?></div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="box">
        <div class="title"><?php echo t_lang('M_TXT_MESSAGES'); ?></div>
        <div class="content">
            <?php echo $frm->getFormHtml(); ?>
        </div>
    </div>
</td>
<?php
require_once './footer.php';
