<?php
require_once './application-top.php';
checkAdminSession();
include('admin-info.cls.php');
$frm = getMBSFormByIdentifier('frmChangePassword');
$fld = $frm->getField('confirm_password');
$fld->setRequiredStarWith('caption');
$fld = $frm->getField('new_password');
$fld->setRequiredStarWith('caption');
$fld = $frm->getField('password');
$fld->setRequiredStarWith('caption');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['ImageSubmit']))) {
    $post = getPostedData();
    $srch = new SearchBase('tbl_admin');
    $srch->addCondition('admin_id', '=', $_SESSION['admin_logged']['admin_id']);
    $srch_listing = $srch->getResultSet();
    $row = $db->fetch($srch_listing);
    $admin_password = $row['admin_password'];
    if ($admin_password == md5($post['password'])) {
        if ($post['new_password'] == $post['confirm_password']) {
            $db->update_from_array('tbl_admin', ['admin_password' => md5($post['new_password'])], 'admin_id = ' . $_SESSION['admin_logged']['admin_id']);
            $msg1 = t_lang('M_TXT_SUCESS_PASSWORD');
            $msg->addMsg($msg1);
            redirectUser();
        } else {
            $msg1 = t_lang('M_TXT_SUCESS_PASSWORD');
            $msg->addError($msg1);
        }
    } else {
        $msg1 = t_lang('M_TXT_OLD_PASSWORD_NOT_CORRECT');
        $msg->addError($msg1);
    }
}
require_once './header.php';
if (isset($_POST['ImageSubmit'])) {
    $admin_info = new adminInfo();
    $post = getPostedData();
    if (!$admin_info->SaveImage($post)) {
        
    }
}
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => 'Change Password'
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"> </div>       
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"><?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?><a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <div class="row">
        <div class="col-sm-12">  
            <h1><?php echo t_lang('M_TXT_CHANGE_PASSWORD'); ?></h1> 
            <div class="containerwhite">
                <?php
                $admin_info = new adminInfo();
                echo $admin_info->leftPanel();
                ?> 
                <aside class="grid_2">
                    <?php echo $admin_info->navigationLink('changepassword'); ?>
                    <div class="areabody">
                        <div class="repeatedrow">
                            <h3><i class="ion-ios-unlocked icon"></i><?php echo t_lang('M_TXT_CHANGE_PASSWORD'); ?></h3>
                            <div class="rowbody">
                                <div class="listview">
                                    <?php echo $frm->getFormHtml(); ?>
                                </div>
                            </div>    
                        </div>
                    </div>
                </aside>  
            </div>
        </div> 
</td>
<?php require_once './footer.php';
?>