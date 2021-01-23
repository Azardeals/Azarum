<?php
require_once '../application-top.php';
require_once '../site-classes/merchant-support.cls.php';
include('admin-info.cls.php');
if (isset($_POST['ImageSubmit'])) {
    $admin_info = new adminInfo();
    $post = getPostedData();
    $admin_info->SaveImage($post);
}
checkAdminPermission(13);
$status = (int) $_REQUEST['status'];
$ticket_id = (int) $_GET['tid'];
$ticket_created_by = (int) $_GET['mid'];
$merchant_support = new merchantSupport();
$frm = $merchant_support->getMerchantSupportForm($ticket_id, $ticket_created_by);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    if (!checkAdminAddEditDeletePermission(13, '', 'add'))
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    $fld2 = $frm->getField('title');
    $frm->removeField($fld2);
    $post = getPostedData();
    $post['files'] = $_FILES['files'];
    $action = $merchant_support->sendMessage($post, $status, true);
    if (!$action)
        $frm->fill($post);
}
$status = (int) $_REQUEST['status'];
$ticket_id = (int) $_REQUEST['tid'];
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
/** mark unread messages as viewed * */
$merchant_support->markTicketAsViewed($ticket_id);
$merchant_support->markMessageAsViewed($ticket_id);
/* * **** */
/** get ticket details * */
$ticket_data = $merchant_support->getTicketById($ticket_id);
//print_r($ticket_data);
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
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $total_records) ? $total_records : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $total_records . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $total_pages, $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
require_once './header.php';
?>
<ul class="nav-left-ul">
    <li><a href="message-listing.php?status=0" <?php if ($status == 0) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ALL_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=1" <?php if ($status == 1) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_UNREAD_MESSAGES'); ?></a></li>
    <li><a href="message-listing.php?status=2" <?php if ($status == 2) echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ARCHIVED'); ?></a></li>
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
                    <div class="greentext"><?php echo $msg->display(); ?></div>
                <?php } ?>
            </div>
        </div> 
    <?php } ?>
    <?php if ($total_records > $pagesize) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
        <?php
    }
    ?>
    <div class="row">
        <div class="col-sm-12">  
            <h1><?php echo t_lang('M_TXT_MY_MESSAGES'); ?></h1> 
            <div class="containerwhite">
                <?php
                $admin_info = new adminInfo();
                echo $admin_info->leftPanel();
                ?>  
                <aside class="grid_2">
                    <div class="toptitle">
                        <ul class="actions">
                            <li><a href="javascript:void(0)" title="Expand All" class="expandlink"><i class="ion-arrow-expand icon"></i></a></li>
                            <li><a href="message-listing.php" title="Back"><i class="ion-android-arrow-back icon"></i></a></li>
                        </ul>
                        <h4><?php echo $ticket_data['ticket_title']; ?></h4>
                    </div>
                    <div class="bodyarea"> 
                        <ul class="medialist">
                            <li>
                                <span class="grid first"><figure class="avtar bgm-<?php echo $admin_info->backgroundColor(ucfirst(substr($ticket_data['company_name'], 0, 1))); ?>"><?php echo substr($ticket_data['company_name'], 0, 1) ?></figure></span>    
                                <div class="grid second">
                                    <div class="desc"><span class="name">
                                            <?php
                                            $company_address = $ticket_data['company_address1'];
                                            if ($ticket_data['company_address2'] != '') {
                                                $company_address .= ', ' . $ticket_data['company_address2'];
                                            }
                                            if ($ticket_data['company_address3'] != '') {
                                                $company_address .= ', ' . $ticket_data['company_address3'];
                                            }
                                            $company_address .= '<br />' . $ticket_data['company_city'];
                                            $company_address .= ', ' . $ticket_data['company_state'];
                                            $company_address .= ' - ' . $ticket_data['company_zip'];
                                            echo $ticket_data['company_name'] . '<br />';
                                            echo $ticket_data['company_email'] . '<br />';
                                            echo $ticket_data['company_phone'] . '<br />';
                                            echo $company_address;
                                            ?>
                                            <span class="lightxt"><span><</span><?php echo $ticket_data['company_email']; ?><span>></span></span></span>
                                        <div class="descbody">
                                            <?php echo (nl2br($ticket_data['ticket_description'])); ?>
                                        </div>    
                                    </div> 
                                </div>    
                                <span class="grid third">
                                    <?php
                                    if (count($ticket_data['files']) > 0) {
                                        foreach ($ticket_data['files'] as $filename) {
                                            echo '<a href="' . CONF_WEBROOT_URL . 'download.php?fname=' . $filename . '" class="attachFile"></a>';
                                        }
                                    }
                                    ?>
                                    <span class="date"><i class="icon ion-ios-clock-outline"></i> <?php echo date('M d, Y', strtotime($ticket_data['ticket_created_on'])); ?></span>
                                </span>
                            </li>
                            <?php foreach ($messages as $ele) { ?>
                                <li>
                                    <span class="grid first"><figure class="avtar bgm-<?php echo $admin_info->backgroundColor(ucfirst(substr($ele['msg_sent_by'], 0, 1))); ?>"><?php echo ucfirst(substr($ele['msg_sent_by'], 0, 1)); ?></figure></span>    
                                    <div class="grid second">
                                        <div class="desc"><span class="name"><?php echo ucwords($ele['msg_sent_by']); ?> <span class="lightxt"><span></span></span></span></span>
                                            <div class="descbody">
                                                <?php echo (nl2br($ele['msg_description'])) . '<br />';
                                                ?>
                                            </div>    
                                        </div> 
                                    </div>    
                                    <span class="grid third">
                                        <?php
                                        if (count($ele['files']) > 0) {
                                            foreach ($ele['files'] as $filename) {
                                                echo '<a href="' . CONF_WEBROOT_URL . 'download.php?fname=' . $filename . '" class="attachFile"></a>';
                                            }
                                        }
                                        ?>
                                        <span class="date"><i class="icon ion-ios-clock-outline"></i><?php echo date('M d, Y', strtotime($ele['msg_sent_on'])); ?></span>
                                    </span>
                                </li>
                            <?php } ?>
                        </ul> 
                    </div>
                    <?php if (checkAdminAddEditDeletePermission(13, '', 'add') || checkAdminAddEditDeletePermission(13, '', 'edit')) { ?>    
                        <div class="areareply">
                            <aside class="grid_1"><figure class="avtar bgm-<?php echo $admin_info->backgroundColor(ucfirst(substr($ele['msg_sent_by'], 0, 1))); ?>">A</figure></aside>    
                            <aside class="grid_2">
                                <p class="txtlink"><?php echo t_lang('M_TXT_CLICK_HERE_TO'); ?> <a class="openreply" href="javascript:void(0)"><?php echo t_lang('M_TXT_REPLY'); ?></a> </p>
                                <div style="display: none;" class="boxcontainer">
                                    <?php echo $frm->getFormTag(); ?>
                                    <div class="middle">
                                        <?php echo $frm->getFieldHTML('description'); ?>
                                        <?php echo $frm->getFieldHTML('ticket_id'); ?>
                                        <?php echo $frm->getFieldHTML('ticket_created_by'); ?>
                                    </div>
                                    <div class="bottom">
                                        <ul class="actions">
                                            <li><div id="attachfile"></div></li>
                                            <li class="attachment">
                                                <a href="javascript:void(0)" title="Attachment"><i class="ion-android-attach icon"></i>
                                                    <?php echo $frm->getFieldHTML('files[]'); ?></a>
                                            </li>
                                            <li><a href="javascript:void(0)" class="openreply" title="Discard Draft"><i class="ion-android-delete icon"></i></a></li>
                                        </ul>
                                                                                <!-- <input type="submit" class="themebtn btn-danger" value="Send">or <a class="openreply" href="javascript:void(0)">Forward</a> -->
                                        <?php echo $frm->getFieldHTML('btn_submit'); ?>
                                        <?php echo $frm->getExternalJS(); ?>
                                    </div>
                                    </form>
                                </div>
                            </aside>    
                        </div> 
                    <?php } ?>
                </aside>  
            </div>
        </div> 
    </div>		
</td>
<script type="text/javascript">
    function getFilename() {
        var files = $("#files")[0].files;
        $('#attachfile').html(files[0].name);
    }
</script>
<?php require_once './footer.php'; ?>