<?php
require_once './application-top.php';
checkAdminPermission(8);
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$pagesize = CONF_ADMIN_PAGE_SIZE;
/*
 * Registered Users autoLogin code start here.
 */
if (is_numeric($_REQUEST['autoLogin'])) {
    $reg = $db->query("select user_email,user_password from tbl_users where user_id='" . $_REQUEST['autoLogin'] . "'");
    $reg = $db->fetch($reg);
    $error = '';
    if (loginUser($reg['user_email'], $reg['user_password'], $error)) {
        if (isset($_SESSION['login_page'])) {
            $url = $_SESSION['login_page'];
            unset($_SESSION['login_page']);
            redirectUser($url);
        }
        redirectUser(CONF_WEBROOT_URL);
    } else {
        $msg->addError($error);
        unset($_SESSION["logged_user"]);
        redirectUser(CONF_WEBROOT_URL . 'manager/registered-members.php');
    }
}
$post = getPostedData();
/*
 * USER SERACH FORM
 */
$srchForm = new Form('Src_frm', 'Src_frm');
$srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$srchForm->setFieldsPerRow(3);
$srchForm->captionInSameCell(true);
$srchForm->setLeftColumnProperties('width="30%"');
$srchForm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$srchForm->addSelectBox(t_lang('M_FRM_STATUS'), 'user_active', $arr_user_status, '', 'style="width: 160px;"', '--Select--', '');
$srchForm->addHiddenField('', 'mode', 'search');
if ($_REQUEST['affiliate'] > 0) {
    $srchForm->addHiddenField('', 'affiliate', $_REQUEST['affiliate']);
}
$fld1 = $srchForm->addButton('', 'btn_search', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="registered-members.php"');
$fld = $srchForm->addSubmitButton('', 'btn_cancel', t_lang('M_TXT_SEARCH'), '', 'class="inputbuttons"')->attachField($fld1);
/*
 * USER SERACH LISTING
 */
$srch = new SearchBase('tbl_users', 'dd');
$srch->addOrder('user_id', 'desc');
if ($_REQUEST['status'] == 'inactive') {
    $srch->addCondition('user_active', '=', 0);
    $srch->addCondition('user_deleted', '=', 0);
} else if ($_REQUEST['status'] == 'deleted') {
    $srch->addCondition('user_deleted', '=', 1);
} else if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('user_active', '=', 1);
    $srch->addCondition('user_deleted', '=', 0);
} else {
    $srch->addCondition('user_deleted', '=', 0);
}
if ($_REQUEST['affiliate'] > 0) {
    $srch->addCondition('user_affiliate_id', '=', $_REQUEST['affiliate']);
}
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('user_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('user_name', 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    if ($post['user_active'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('user_active', '=', $post['user_active'], 'OR');
    }
    $srchForm->fill($post);
}
/*
 * USER SERACH PAGINATION
 */
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$navigation_listing = $srch->getResultSet();
$pagestring = '';
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'affiliate'), array('page' => $_REQUEST['page'], 'affiliate' => $_REQUEST['affiliate']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
/*
 * USER PERMANENT DELETE MODE
 */
if (isset($_GET['deletePer']) && $_GET['deletePer'] != "") {
    if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
        $user_id = $_GET['deletePer'];
        deleteMemberPermanent($user_id);
        /* function write in the site-function.php */
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/*
 * USER DELETE MODE
 */
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
        $user_id = $_GET['delete'];
        deleteMember($user_id);
        /* function write in the site-function.php */
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/*
 * USER RESTORE MODE
 */
if (isset($_GET['restore']) && $_GET['restore'] != "") {
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        $user_id = $_GET['restore'];
        restoreMember($user_id);
        /* function write in the site-function.php */
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if ($_GET['affiliate'] > 0) {
    $arr_bread = array(
        'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
        'affiliate.php' => t_lang('M_TXT_AFFILIATE'),
        '' => t_lang('M_TXT_REGISTERED_USERS')
    );
} else {
    $arr_bread = array(
        'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
        'javascript:void(0)' => t_lang('M_TXT_USERS'),
        '' => t_lang('M_TXT_REGISTERED_USERS')
    );
}
require_once './header.php';
$start = ($page - 1) * $pagesize + 1;
$limit = $pagesize;
if (isset($_POST['mass_update_btn'])) {
    if (is_numeric($_POST['wallet_txt'])) {
        $j = 0;
        for ($i = $start; $i < ($start + $limit); $i++) {
            $var = "id" . $i;
            if (isset($_POST[$var])) {
                $id[$j] = $_POST[$var];
                $j++;
            }
        }
        $ids = join(',', $id);
        if ($ids != "") {
            if (!$db->query("UPDATE tbl_users SET user_wallet_amount = user_wallet_amount + " . $_POST['wallet_txt'] . " WHERE user_id IN(" . $ids . ")")) {
                echo "Error " . $db->getError();
            } else {
                echo "Id:" . $ids . "<br/>";
                $position = strpos($ids, ",");
                if ($position === false) {
                    if (!$db->query("INSERT INTO tbl_user_wallet_history VALUES (" . $ids . "," . 0 . ",'" . "Mass Updated By Admin" . "'," . $_POST['wallet_txt'] . ",CURRENT_TIMESTAMP " . ")")) {
                        echo "Error In Wallet Logs :" . $db->getError();
                    } else {
                        header("location:./registered-members.php?page=1");
                    }
                } else {
                    echo "Match found at location $position";
                    $id_array = explode(",", $ids);
                    foreach ($id_array as $user_id_value) {
                        if (!$db->query("INSERT INTO tbl_user_wallet_history VALUES (" . $user_id_value . "," . 0 . ",'" . "Mass Updated By Admin" . "'," . $_POST['wallet_txt'] . ",CURRENT_TIMESTAMP " . ")")) {
                            echo "Error In Wallet Logs :" . $db->getError();
                        } else {
                            header("location:./registered-members.php?page=1");
                        }
                    }
                }
            }
        }
    }
}
//end of code
?>
<script language="javascript">
    var txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
    var txtreload = "<?php echo addslashes(t_lang('M_TXT_PLEASE_RELOAD_PAGE_AND_TRY_AGAIN')); ?>";
    var txtload = "<?php echo addslashes(t_lang('M_TXT_LOADING')); ?>";
    var txtsuredel = "<?php echo addslashes(t_lang('M_TXT_ARE_ YOU_SURE_TO_DELETE')); ?>";
    var txtstatusup = "<?php echo addslashes(t_lang('M_TXT_STATUS_UPDATED')); ?>";
</script>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="registered-members.php?status=active"><?php echo t_lang('M_TXT_ACTIVE'); ?> <?php echo t_lang('M_TXT_USERS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'inactive') echo 'class="selected"'; ?> href="registered-members.php?status=inactive"><?php echo t_lang('M_TXT_INACTIVE'); ?> <?php echo t_lang('M_TXT_USERS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deleted') echo 'class="selected"'; ?> href="registered-members.php?status=deleted"><?php echo t_lang('M_TXT_DELETED'); ?> <?php echo t_lang('M_TXT_USERS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_REGISTERED_USERS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
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
    <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_REGISTERED_USERS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $srchForm->getFormHtml(); ?></div></div>	
    <div class="clear">&nbsp;</div>

    <table class="tbl_data" width="100%"> 
        <thead>
            <tr>
             <!--  <th>&nbsp;</th> -->
                <th width="10%"><?php echo t_lang('M_TXT_FIRST_NAME'); ?></th>
                <th width="10%"><?php echo t_lang('M_FRM_EMAIL_ADDRESS'); ?></th>
                <th width="10%"><?php echo t_lang('M_TXT_DATE'); ?></th>
                <th width="10%"><?php echo t_lang('M_TXT_WALLET_AMOUNT'); ?></th>
                <th width="10%"><?php echo t_lang('M_FRM_REFERRED_BY'); ?></th>
                <th width="10%"><?php echo t_lang('M_FRM_AFFLIATED_BY'); ?></th>
                <th width="5%"><?php echo t_lang('M_FRM_STATUS'); ?></th>
                <th width="10%"><?php echo t_lang('M_TXT_EMAIL_VERIFIED'); ?></th>
                <th width="25%"><?php echo t_lang('M_TXT_ACTION'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $id = $start;
            while ($row = $db->fetch($navigation_listing)) {
                $row['user_name'] = htmlentities($row['user_name'], ENT_QUOTES, 'UTF-8');
                ?>
                <tr>	
                  <!--  <td ><input type="checkbox" name="id<?php echo $id ?>" id="id<?php echo $id ?>"  value="<?php echo $row['user_id']; ?>"   >
                    <?php $id++; ?></td> -->
                    <td width="10%"><?php echo $row['user_name']; ?></td>
                    <td width="10%"><?php echo $row['user_email']; ?></td>
                    <td width="10%"><?php echo displayDate($row['user_regdate'], true, false, ''); ?></td>
                    <td width="15%">
                        <span  id="wallet_<?php echo $row['user_id']; ?>"><?php echo CONF_CURRENCY . $row['user_wallet_amount'] . CONF_CURRENCY_RIGHT; ?></span><br/>
                        <br/><ul class="actions"><li><a href="registered-user-wallet.php?user=<?php echo $row['user_id']; ?>" title="<?php echo t_lang('M_TXT_VIEW_WALLET_HISTORY'); ?>"><i class="ion-eye icon"></i></a></li>
                    <!--comment by softronikx , code to edit wallet amount href="edit_wallet_amt.php?user_id=<?php echo $row['user_id']; ?>"  -->
                            <li><a href="javascript:void(0);" onClick="return userUpdateWallet('<?php echo $row['user_id'] ?>');" title="<?php echo t_lang('M_TXT_ADD_TRANSACTION'); ?>"  > <i class="ion-social-usd icon"></i> </a></li></ul>
                        <!-- end comment -->	
                    </td>
                    <td width="10%"><?php
                        if ($row['user_referral_id'] > 0) {
                            $refRS = $db->query("select * from tbl_users where user_id=" . $row['user_referral_id']);
                            $refRow = $db->fetch($refRS);
                            echo $refRow['user_name'];
                        } else {
                            echo '---';
                        }
                        ?></td>
                    <td width="10%"><?php
                        if ($row['user_affiliate_id'] > 0) {
                            $refRS = $db->query("select * from tbl_affiliate where affiliate_id=" . $row['user_affiliate_id']);
                            $refRow = $db->fetch($refRS);
                            echo $refRow['affiliate_fname' . $_SESSION['lang_fld_prefix']] . ' ' . $refRow['affiliate_lname' . $_SESSION['lang_fld_prefix']];
                        } else {
                            echo '---';
                        }
                        ?></td>
                    <td width="5%" id="">
                        <span id="original_span<?php echo $row['user_id'] ?>">
                            <?php if ($row['user_active'] == 0) { ?>
                                <?php if (checkAdminAddEditDeletePermission(8, '', 'edit')) { ?>
                                    <span class="statustab active" id="comment<?php echo $row['user_id'] ?>" onclick="activeUser(<?php echo $row['user_id'] ?>, 1);">
                                        <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                        <span class="switch-handles"></span>
                                    </span>
                                <?php } ?>
                            <?php } else { ?>	
                                <?php if (checkAdminAddEditDeletePermission(8, '', 'edit')) { ?>
                                    <span class="statustab" id="comment<?php echo $row['user_id'] ?>" onclick="activeUser(<?php echo $row['user_id'] ?>, 0);">
                                        <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                        <span class="switch-handles"></span>
                                    </span>
                                <?php } ?>
                            <?php } ?>
                        </span> 
                        <?php //echo ($row['user_active']==1)?'Active':'Inactive'; ?></td>
                    <td width="10%" ><?php echo ($row['user_email_verified'] == 1) ? t_lang('M_TXT_YES') : t_lang('M_TXT_NO'); ?></td>
                    <td width="20%" id="comment-status<?php echo $row['user_id'] ?>"> 
                        <ul class="actions">
                            <?php if ($_REQUEST['status'] != 'deleted') { ?>
                                <?php if (checkAdminAddEditDeletePermission(8, '', 'delete')) { ?>
                                    <li><a href="registered-members.php?delete=<?php echo $row['user_id'] ?>&status=<?php echo $_REQUEST['status']; ?>" onclick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?> <?php echo t_lang('M_TXT_USER'); ?>"><i class="ion-android-delete icon"></i></a></li>
                                <?php } ?>
                            <?php } else { ?>
                                <?php if (checkAdminAddEditDeletePermission(8, '', 'delete')) { ?>
                                    <li><a href="registered-members.php?deletePer=<?php echo $row['user_id'] ?>&status=<?php echo $_REQUEST['status']; ?>" onclick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE_PERMANENTLY'); ?>"><i class="ion-ios-trash icon"></i></a></li>
                                <?php } ?>
                                <?php if (checkAdminAddEditDeletePermission(8, '', 'edit')) { ?>
                                    <li><a href="registered-members.php?restore=<?php echo $row['user_id'] ?>&status=<?php echo $_REQUEST['status']; ?>" title="<?php echo t_lang('M_TXT_RESTORE'); ?>  <?php echo t_lang('M_TXT_USER'); ?>"><i class="ion-archive icon"></i></a></li>
                                <?php } ?>
                            <?php } ?>
                            <?php if (checkAdminAddEditDeletePermission(8, '', 'edit')) { ?>
                                <li><a href="javascript:void(0);" onClick="return userChangePassword('<?php echo $row['user_id'] ?>');" title="<?php echo t_lang('M_TXT_CHANGE_PASSWORD'); ?>"><i class="ion-unlocked icon"></i></a></li>
                                <?php
                                echo '<li><a href="?autoLogin=' . $row['user_id'] . '" target="_blank" title="' . t_lang('M_TXT_Login_To_Profile') . '"><i class="ion-log-in icon"></i></a></li>';
                            }
                            ?>
                        </ul>
                    </td>
                </tr>
                <?php
            }
            if ($db->total_records($navigation_listing) == 0) {
                echo '<tr><td colspan="9">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
    <?php if (!isset($_GET['edit']) && $_GET['add'] != 'new' && ($srch->pages() > 1)) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
    <!-- </form> -->
    <?php require_once './footer.php'; ?>
