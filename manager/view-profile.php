<?php
require_once './application-top.php';
checkAdminSession();
include('./admin-info.cls.php');
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 50;
$mainTableName = 'tbl_admin';
$primaryKey = 'admin_id';
$colPrefix = 'admin_';
$srch = new SearchBase('tbl_admin', 'a');
$srch->joinTable('tbl_admin_addresses', 'LEFT JOIN', 'a.admin_id = aa.admaddress_admin_id', 'aa');
$srch->joinTable('tbl_states', 'LEFT JOIN', 'aa.admaddress_state = s.state_id', 's');
$srch->joinTable('tbl_countries', 'LEFT JOIN', 's.state_country = c.country_id', 'c');
$srch->addCondition('a.admin_id', '=', $_SESSION['admin_logged']['admin_id']);
$srch->addCondition('a.admin_id', '=', $_SESSION['admin_logged']['admin_id']);
$rs_listing = $srch->getResultSet();
$data = $db->fetch($rs_listing);
require_once './header.php';
if (isset($_POST['ImageSubmit'])) {
    $admin_info = new adminInfo();
    $post = getPostedData();
    if (!$admin_info->SaveImage($post)) {
        $msg->display();
    }
}
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_ADMIN_USERS')
);
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <div class="row">
        <div class="col-sm-12">  
            <h1><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></h1> 
            <div class="containerwhite">
                <?php
                $admin_info = new adminInfo();
                echo $admin_info->leftPanel();
                ?> 
                <aside class="grid_2">
                    <?php echo $admin_info->navigationLink('view'); ?>
                    <div class="areabody">
                        <div class="repeatedrow">
                            <h3><i class="ion-person icon"></i> <?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></h3>
                            <div class="rowbody">
                                <div class="listview">
                                    <dl class="list">
                                        <dt><?php echo t_lang('M_TXT_FULL_NAME'); ?></dt>
                                        <dd><?php echo $data['admin_name']; ?></dd>
                                    </dl>
                                </div>
                            </div>    
                        </div>
                        <div class="repeatedrow">
                            <h3><i class="icon ion-android-call"></i><?php echo t_lang('M_TXT_CONTACT_INFORMATION'); ?></h3>
                            <div class="rowbody">
                                <div class="listview">
                                    <?php if (!empty($data['admin_skype'])) { ?>
                                        <dl class="list">
                                            <dt><?php echo t_lang('M_TXT_MOBILE_PHONE'); ?></dt>
                                            <dd><?php echo $data['admin_phone']; ?></dd>
                                        </dl>
                                    <?php } ?>
                                    <dl class="list">
                                        <dt><?php echo t_lang('M_TXT_EMAIL_ADDRESS'); ?></dt>
                                        <dd><?php echo $data['admin_email']; ?></dd>
                                    </dl>
                                    <?php if (!empty($data['admin_twitter'])) { ?>
                                        <dl class="list">
                                            <dt><?php echo t_lang('M_TXT_TWITTER'); ?></dt>
                                            <dd><?php echo $data['admin_twitter']; ?></dd>
                                        </dl>
                                    <?php } ?>
                                    <?php if (!empty($data['admin_skype'])) { ?>
                                        <dl class="list">
                                            <dt><?php echo t_lang('M_TXT_SKYPE'); ?></dt>
                                            <dd><?php echo $data['admin_skype']; ?></dd>
                                        </dl>
                                    <?php } ?>
                                </div>
                            </div>  
                        </div>
                    </div>
                </aside>  
            </div>
        </div> 
    </div>
</td>
<?php require_once './footer.php'; ?>
