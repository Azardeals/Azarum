<?php
require_once './application-top.php';
checkAdminPermission(1);
$srch = new SearchBase('tbl_navigations', 'nav');
$srch->addCondition('nav_active', '=', 1);
$srch->addFld('nav.*');
$navigation_listing = $srch->getResultSet();
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_LIST_OF_NAVIGATIONS')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_LIST_OF_NAVIGATIONS'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <table class="tbl_data" width="100%">
        <thead>
            <tr>
                <th ><?php echo t_lang('M_TXT_NAVIGATION'); ?></th>
                <th ><?php echo t_lang('M_TXT_ACTION'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $db->fetch($navigation_listing)) {
                ?>
                <tr>	
                    <td><?php echo $row['nav_name'] ?></td>
                    <td>
                        <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                            <ul class="actions">
                                <li><a href="navigation.php?nav_id=<?php echo $row['nav_id'] ?>" title="<?php echo t_lang('M_TXT_EDIT') ?>"><i class="ion-edit icon"></i></a></li>
                            </ul>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</td>
<?php require_once './footer.php'; ?>
