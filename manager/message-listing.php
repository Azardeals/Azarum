<?php
require_once '../application-top.php';
require_once '../site-classes/merchant-support.cls.php';
require_once './admin-info.cls.php';
checkAdminPermission(13);
$status = (int) $_REQUEST['status'];
$status = $status > 3 ? 0 : $status;
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$keyword = $_REQUEST['keyword'];
require_once './header.php';
if (isset($_POST['ImageSubmit'])) {
    $admin_info = new adminInfo();
    $post = getPostedData();
    if (!$admin_info->SaveImage($post)) {

    }
}
?>
<script type="text/javascript">
    var status = "<?php echo $status; ?>";
    var txtnomsg = "<?php echo addslashes(t_lang('M_TXT_NO_MESSAGES')); ?>";
</script>
<ul class="nav-left-ul">
    <li><a href="message-listing.php?status=0" <?php echo ($status == 0) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_ALL_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=3" <?php echo ($status == 3) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_READ_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=1" <?php echo ($status == 1) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_UNREAD_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=2" <?php echo ($status == 2) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_ARCHIVED'); ?></a></li>
</ul>
</div>
</td>
<td class="right-portion">
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
    <div class="row">
        <div class="col-sm-12">
            <h1><?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?></h1>
            <div class="containerwhite">
                <?php
                $admin_info = new adminInfo();
                echo $admin_info->leftPanel();
                ?>
                <aside class="grid_2">
                    <?php echo $admin_info->navigationLink('message'); ?>
                    <div class="sortbar">
                        <aside class="grid_1">
                            <ul class="actions rights">
                                <li class="droplink">
                                    <a title="Sort By" href="javascript:void(0)"><i class="icon sorticon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="message-listing.php?status=0" <?php echo ($status == 0) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_ALL'); ?></a></li>
                                            <li><a href="message-listing.php?status=3" <?php echo ($status == 3) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_READ'); ?></a></li>
                                            <li><a href="message-listing.php?status=1" <?php echo ($status == 1) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_UNREAD'); ?></a></li>
                                            <li><a href="message-listing.php?status=2" <?php echo ($status == 2) ? 'class="selected"' : ''; ?>><?php echo t_lang('M_TXT_ARCHIVED'); ?></a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            <span class="txtnormal"><?php echo t_lang('M_TXT_INBOX') ?></span>
                        </aside>
                        <aside class="grid_2">
                            <input type= "hidden" name="status" value="<?php echo $_REQUEST['status']; ?>">
                            <div class="searchbar"><input type="text" placeholder="Search" name="keyword" onchange="pageMsgHtml()" value="<?php echo $_REQUEST['keyword']; ?>" id="search"></div>
                        </aside>
                    </div>
                    <span id="msg_html">
                    </span>
                </aside>
            </div>
        </div>
    </div>
</td>
<script type="text/javascript">
    var status = '<?php echo $status; ?>';
    var page = '<?php echo $page; ?>';
    var keyword = '<?php echo $keyword; ?>';
</script>
<div class="topwrap">
    <div class="one_third_grid">
        <a href="javascript:void(0);" class="backarrow"></a><span class="txtwhite"><?php echo t_lang('M_TXT_BACK') ?></span>
    </div>
    <div class="one_third_grid">
        <span class="selectedt_txt"><span class="messagecount">1</span> <?php echo t_lang('M_TXT_SELECTED') ?></span>
    </div>
    <div class="one_third_grid">
        <ul class="actions">
            <li><a title="Mark Read" href="javascript:void(0)" onclick="markasRead();"><i class="ion-android-done icon"></i></a></li>
            <li><a title="Select All" href="javascript:void(0)" onclick="selectAll();"><i class="ion-android-done-all icon"></i></a></li>
            <li><a title="Mark Unread" href="javascript:void(0)" onclick="markAsUnRead();"><i class="ion-email-unread icon"></i></a></li>
            <?php if ($status != 2 && (checkAdminAddEditDeletePermission(13, '', 'delete'))) { ?>
                <li><a title="Delete" href="javascript:void(0)" onclick="markAsArchive();"><i class="ion-android-delete icon"></i></a></li>
            <?php } else { ?>
                <li><a title="Mark Unarchieve" href="javascript:void(0)" onclick="markAsUnArchive();"><i class="ion-backspace-outline icon"></i></a></li>
                    <?php } ?>
        </ul>
    </div>
</div>
<?php
require_once './footer.php';
