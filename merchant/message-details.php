<?php
require_once '../application-top.php';
require_once '../site-classes/merchant-support.cls.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$status = (int) $_REQUEST['status'];
$ticket_id = (int) $_REQUEST['tid'];
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$merchant_support = new merchantSupport();
/** mark unread messages as viewed * */
$merchant_support->markMessageAsViewed($ticket_id);
/* * **** */
/** get ticket details * */
$ticket_data = $merchant_support->getTicketById($ticket_id);
/* * ***** */
/** get messages by ticket id * */
$messages_arr = $merchant_support->getMessagesByTicketId($ticket_id, $page);
$messages = $messages_arr['messages'];
$total_pages = $messages_arr['total_pages'];
$total_records = $tickets_arr['total_records'];
$pagesize = $tickets_arr['page_size'];
/* * **** */
$pagestring = '';
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status', 'tid'), array('page' => '', 'status' => $status, 'tid' => $ticket_id));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent .= '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $total_records) ? $total_records : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $total_records . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $total_pages, $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
require_once './header.php';
$arr_bread = array('message-listing.php' => t_lang('M_TXT_MESSAGES'));
?>
<ul class="nav-left-ul">
    <li><a href="message-listing.php?status=0" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message-details.php') && $status == 0) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ALL_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=1" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message-details.php') && $status == 1) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_UNREAD_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=2" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message-details.php') && $status == 2) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ARCHIVED'); ?></a></li>
    <li><a href="message.php" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message.php')) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_NEW_MESSAGE'); ?></a></li>
</ul>
</div>
</td>
<td class="right-portion">
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_MESSAGES'); ?></div>
        <ul class="actions right">
            <li class="droplink">
                <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                <div class="dropwrap">
                    <ul class="linksvertical">
                        <li>
                            <a href="message.php?status=<?php echo $status; ?>&tid=<?php echo $ticket_id; ?>" ><?php echo t_lang('M_TXT_REPLY'); ?></a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg">
                <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a>
            </div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?></div>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"><?php echo $msg->display(); ?></div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <table class="tbl_messages" width="100%">
        <tr class="header">
            <td width="80%"><?php echo htmlentities($ticket_data['ticket_title'], ENT_QUOTES); ?></td>
            <td width="*" class="date"><?php echo t_lang('M_TXT_POSTED_ON') . ':&nbsp;' . date('M d, Y', strtotime($ticket_data['ticket_created_on'])); ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <?php
                htmlentities($ticket_data['ticket_description'], ENT_QUOTES) . '<br /><div class="gap"></div>';
                if (count($ticket_data['files']) > 0) {
                    foreach ($ticket_data['files'] as $filename) {
                        echo '<img src="images/zip_icon.gif" /><a href="' . CONF_WEBROOT_URL . 'download.php?fname=' . $filename . '">' . $filename . '</a><br />';
                    }
                }
                ?>
            </td>
        </tr>
    </table>
    <?php foreach ($messages as $ele) { ?>
        <table class="tbl_messages" width="100%">
            <tr>
                <td width="20%">
                    <?php
                    echo t_lang('M_TXT_POSTED_BY') . ':&nbsp;' . ucwords($ele['msg_sent_by']) . '<br />';
                    echo t_lang('M_TXT_POSTED_ON') . ':&nbsp;' . date('M d, Y', strtotime($ele['msg_sent_on']));
                    ?>
                </td>
                <td width="*">
                    <?php
                    echo nl2br(($ele['msg_description'])) . '<br /><div class="gap"></div>';
                    if (count($ele['files']) > 0) {
                        foreach ($ele['files'] as $filename) {
                            echo '<img src="images/zip_icon.gif" /><a href="' . CONF_WEBROOT_URL . 'download.php?fname=' . $filename . '">' . $filename . '</a><br />';
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
    <?php } ?>
    <?php if ($total_records > $pagesize) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>
            </aside>
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php }
    ?>
</td>
<?php
require_once './footer.php';
