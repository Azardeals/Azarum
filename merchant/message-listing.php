<?php
require_once '../application-top.php';
require_once '../site-classes/merchant-support.cls.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$status = (int) $_REQUEST['status'];
$status = $status > 2 ? 0 : $status;
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$merchant_support = new merchantSupport();
/** get merchant tickets * */
$tickets_arr = $merchant_support->getTickets($status, $page);
//print_r($tickets_arr);
$tickets = $tickets_arr['tickets'];
$total_pages = $tickets_arr['total_pages'];
$total_records = $tickets_arr['total_records'];
$pagesize = $tickets_arr['page_size'];
/* * **** */
$pagestring = '';
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status'), array('page' => '', 'status' => $status));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $total_records) ? $total_records : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $total_records . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $total_pages, $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
require_once './header.php';
$arr_bread = array('' => t_lang('M_TXT_MESSAGES'));
?>
<script type="text/javascript">
    var status = <?php echo intval($status); ?>;
    var txtnomsg = "<?php echo addslashes(t_lang('M_TXT_NO_RECORD_FOUND')); ?>";
</script>
<ul class="nav-left-ul">
    <li><a href="message-listing.php?status=0" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message-listing.php') && $status == 0) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ALL_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=1" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message-listing.php') && $status == 1) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_UNREAD_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=2" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message-listing.php') && $status == 2) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ARCHIVED'); ?></a></li>
    <li><a href="message.php" <?php if (strpos($_SERVER['SCRIPT_NAME'], 'message.php')) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_NEW_MESSAGE'); ?></a></li>
</ul>
</div>
</td>
<td class="right-portion">
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
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
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?></div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php if ($total_records == 0) { ?>
        <table class="tbl_messages" width="100%">
            <tr class="header">
                <td colspan="2">
                    <span id="no_messages">
                        <?php echo t_lang('M_TXT_NO_RECORD_FOUND'); ?>
                    </span>
                </td>
            </tr>
        </table>
    <?php } ?>
    <?php foreach ($tickets as $ele) { ?>
        <table class="tbl_messages" width="100%">
            <tr class="header">
                <td width="74%">
                    <a href="message-details.php?status=<?php echo $status; ?>&tid=<?php echo $ele['ticket_id']; ?>">
                        <?php echo '<span>' . htmlentities($ele['ticket_title'], ENT_QUOTES, 'UTF-8') . '</span>'; ?>
                        <?php if ($ele['unread_messages'] > 0) { ?>
                            <span class="msg_flag"><?php echo htmlentities($ele['unread_messages'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php } ?>
                    </a>
                </td>
                <td width="*" class="date"><?php echo t_lang('M_TXT_POSTED_ON') . ':&nbsp;' . date('M d, Y', strtotime($ele['ticket_created_on'])); ?></td>
                <td width="5%" class="date">
                    <?php if ($ele['unread_messages'] > 0 || $status == 1) { ?>
                        <a class="mark_as_read" id="mark_as_read_<?php echo $ele['ticket_id']; ?>" alt="<?php echo $ele['ticket_id']; ?>" title="<?php echo t_lang('M_TXT_MARK_AS_READ'); ?>" href="javascript:void(0);">&#10003;</a>&nbsp;&nbsp;
                    <?php } ?>
                    <?php if ($status == 0) { ?>
                        <a class="archive" id="arch_<?php echo $ele['ticket_id']; ?>" alt="<?php echo $ele['ticket_id']; ?>" title="<?php echo t_lang('M_TXT_ARCHIVE'); ?>" href="javascript:void(0);">&times;</a>
                    <?php } else if ($status == 2) { ?>
                        <a class="unarchive" id="unarch_<?php echo $ele['ticket_id']; ?>" alt="<?php echo $ele['ticket_id']; ?>" title="<?php echo t_lang('M_TXT_UNARCHIVE'); ?>" href="javascript:void(0);">&larr;</a>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <?php
                    echo (nl2br($ele['ticket_description'])) . '<br /><div class="gap"></div>';
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
?>
