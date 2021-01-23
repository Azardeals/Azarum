<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
$post = getPostedData();
if (isset($_REQUEST['code'])) {
    $userArr = explode('_', $_REQUEST['code']);
    if ($userArr[0] > 0) {
        $query1 = $db->query("select * from  tbl_user_password_resets_requests where uprr_company_id= 0 and uprr_affiliate_id =0 and uprr_rep_id =0 and  uprr_user_id=" . $userArr[0]);
        $result1 = $db->fetch($query1);
    }
    if ($userArr[1] > 0) {
        $query1 = $db->query("select * from  tbl_user_password_resets_requests where uprr_user_id= 0 and uprr_affiliate_id =0 and uprr_rep_id =0 and  uprr_company_id=" . $userArr[1]);
        $result1 = $db->fetch($query1);
    }
    if ($userArr[2] > 0) {
        $query1 = $db->query("select * from  tbl_user_password_resets_requests where uprr_company_id= 0 and uprr_user_id =0 and uprr_rep_id =0 and  uprr_affiliate_id=" . $userArr[2]);
        $result1 = $db->fetch($query1);
    }
    if ($userArr[3] > 0) {
        $query1 = $db->query("select * from  tbl_user_password_resets_requests where uprr_company_id= 0 and uprr_user_id =0 and uprr_affiliate_id =0 and  uprr_rep_id=" . $userArr[3]);
        $result1 = $db->fetch($query1);
    }
    if ($result1) {
        if ($userArr[0] > 0) {
            $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($userArr[4]) . " and uprr_user_id=" . $userArr[0]);
            $result2 = $db->fetch($query2);
        }
        if ($userArr[1] > 0) {
            $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($userArr[4]) . " and uprr_company_id=" . $userArr[1]);
            $result2 = $db->fetch($query2);
        }
        if ($userArr[2] > 0) {
            $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($userArr[4]) . " and uprr_affiliate_id=" . $userArr[2]);
            $result2 = $db->fetch($query2);
        }
        if ($userArr[3] > 0) {
            $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($userArr[4]) . " and uprr_rep_id=" . $userArr[3]);
            $result2 = $db->fetch($query2);
        }
        if ($db->total_records($query2) == 1) {
            $frmForgot = getMBSFormByIdentifier('frmResetPassword');
            $frmForgot->setTableProperties('class="formwrap__table" ');
            $frmForgot->setFieldsPerRow(1);
            $frmForgot->captionInSameCell(true);
            $frmForgot->setAction($_SERVER['REQUEST_URI']);
            $fld = $frmForgot->getField('submit');
            $fld->value = t_lang('M_TXT_UPDATE');
            $fld = $frmForgot->getField('user_id');
            $fld->value = $userArr[0];
            $fld = $frmForgot->getField('company_id');
            $fld->value = $userArr[1];
            $fld = $frmForgot->getField('affiliate_id');
            $fld->value = $userArr[2];
            $fld = $frmForgot->addHiddenField('', 'rep_id', '', 'rep_id');
            $fld->value = $userArr[3];
            $fld = $frmForgot->getField('tocken');
            $fld->value = $userArr[4];
            $fld = $frmForgot->getField('code');
            $fld->value = $_REQUEST['code'];
            updateFormLang($frmForgot);
            /* $frmForgot=new Form('frmResetPassword', 'frmResetPassword');
              $frmForgot->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="formwrap__table" width="100%"');
              $frmForgot->setFieldsPerRow(1);
              $frmForgot->captionInSameCell(false);
              $frmForgot->addPasswordField( 'New Password', 'password', '', 'password', '')->requirements()->setRequired();
              $frmForgot->addPasswordField( 'Confirm Password', 'confirm_password', '', 'confirm_password', '')->requirements()->setRequired();
              $frmForgot->addHiddenField('','user_id',$userArr[0]);
              $frmForgot->addHiddenField('','company_id',$userArr[1]);
              $frmForgot->addHiddenField('','affiliate_id', $userArr[2]);
              $frmForgot->addHiddenField('','code',$_REQUEST['code']);
              $frmForgot->addHiddenField('','tocken', $userArr[4]);
              $frmForgot->addHiddenField('','rep_id',$userArr[3]);
              $fld=$frmForgot->addSubmitButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), '', ' class="inputbuttons"'); */
        } else {
            die(t_lang('M_TXT_RESET_ERROR_MESSAGE'));
        }
    } else {
        die(t_lang('M_TXT_RESET_ERROR_MESSAGE'));
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['user_id'] > 0 && $_POST['tocken'] != "" && $_POST['company_id'] == 0 && $_POST['affiliate_id'] == 0) {
        $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($_POST['tocken']) . " and uprr_company_id=0 and uprr_affiliate_id=0 and uprr_user_id=" . intval($_POST['user_id']));
        $result2 = $db->fetch($query2);
        if ($db->total_records($query2) == 1) {
            if ($frmForgot->validate($post)) {
                $query3 = $db->query("select * from  tbl_users where user_id =" . $result2['uprr_user_id']);
                $result3 = $db->fetch($query3);
                if ($db->total_records($query3) == 1) {
                    if ($post['password'] != '')
                        $user_password = md5($post['password']);
                    $arr_pass = array('user_password' => $user_password);
                    $db->update_from_array('tbl_users', $arr_pass, array('smt' => 'user_id = ?', 'vals' => array($result3['user_id']), 'execute_mysql_functions' => false));
                    //$db->query("UPDATE tbl_users SET user_password = ".$db->quoteVariable($user_password)."'  WHERE  user_id=". $result3['user_id']);
                    $db->query("DELETE FROM tbl_user_password_resets_requests WHERE  uprr_user_id=" . $result3['user_id']);
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
                    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
                }
            }
        }
    }
    if ($_POST['company_id'] > 0 && $_POST['tocken'] != "" && $_POST['user_id'] == 0 && $_POST['affiliate_id'] == 0) {
        $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($_POST['tocken']) . " and uprr_user_id=0 and uprr_affiliate_id=0 and  uprr_company_id=" . $_POST['company_id']);
        $result2 = $db->fetch($query2);
        if ($db->total_records($query2) == 1) {
            if ($frmForgot->validate($post)) {
                $query3 = $db->query("select * from  tbl_companies where  company_id=" . $result2['uprr_company_id']);
                $result3 = $db->fetch($query3);
                if ($db->total_records($query3) == 1) {
                    if ($post['password'] != '')
                        $company_password = md5($post['password']);
                    $db->query("UPDATE tbl_companies SET company_password = " . $db->quoteVariable($company_password) . "  WHERE  company_id=" . $result3['company_id']);
                    $db->query("DELETE FROM   tbl_user_password_resets_requests   WHERE  uprr_company_id=" . $result3['company_id']);
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
                    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
                }
            }
        }
    }
    if ($_POST['affiliate_id'] > 0 && $_POST['tocken'] != "" && $_POST['company_id'] == 0 && $_POST['user_id'] == 0) {
        $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($_POST['tocken']) . " and uprr_user_id=0 and uprr_company_id=0 and  uprr_affiliate_id=" . $_POST['affiliate_id']);
        $result2 = $db->fetch($query2);
        if ($db->total_records($query2) == 1) {
            if ($frmForgot->validate($post)) {
                $query3 = $db->query("select * from  tbl_affiliate where  affiliate_id=" . $result2['uprr_affiliate_id']);
                $result3 = $db->fetch($query3);
                if ($db->total_records($query3) == 1) {
                    if ($post['password'] != '')
                        $affiliate_password = md5($post['password']);
                    $db->query("UPDATE tbl_affiliate SET affiliate_password =  '$affiliate_password'  WHERE  affiliate_id=" . $result3['affiliate_id']);
                    $db->query("DELETE FROM   tbl_user_password_resets_requests   WHERE  uprr_affiliate_id=" . $result3['affiliate_id']);
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
                    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-login.php'));
                }
            }
        }
    }
    if ($_POST['rep_id'] > 0 && $_POST['tocken'] != "" && $_POST['company_id'] == 0 && $_POST['user_id'] == 0 && $_POST['affiliate_id'] == 0) {
        $query2 = $db->query("select * from  tbl_user_password_resets_requests where  uprr_expiry > (NOW() - INTERVAL 1 DAY) and uprr_tocken = " . $db->quoteVariable($_POST['tocken']) . " and uprr_user_id=0 and uprr_company_id=0 and uprr_affiliate_id=0 and  uprr_rep_id=" . $_POST['rep_id']);
        $result2 = $db->fetch($query2);
        if ($db->total_records($query2) == 1) {
            if ($frmForgot->validate($post)) {
                $query3 = $db->query("select * from  tbl_representative where  rep_id=" . $result2['uprr_rep_id']);
                $result3 = $db->fetch($query3);
                if ($db->total_records($query3) == 1) {
                    if ($post['password'] != '')
                        $rep_password = md5($post['password']);
                    $db->query("UPDATE tbl_representative SET rep_password = " . $db->quoteVariable($rep_password) . " WHERE  rep_id=" . $result3['rep_id']);
                    $db->query("DELETE FROM   tbl_user_password_resets_requests   WHERE  uprr_rep_id=" . $result3['rep_id']);
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
                    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');
                }
            }
        }
    }
}
require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="sectionfull">
    <div class="sectionfull__centered">
        <div class="sectiontable">
            <aside class="sectiontable__leftcell">
                <ul class="tabs__dual clearfix">
                    <li class="current"><?php echo t_lang('M_TXT_RESET_PASSWORD'); ?></li>
                </ul>
                <div class="formwrap">
                    <?php
                    if ($result1) {
                        echo $msg->display();
                        echo $frmForgot->getFormHtml();
                    }
                    ?>
                </div>
            </aside>
            <?php
            $rows = fetchBannerDetail(5, 1);
            if (!empty($rows[0])) {
                $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $rows[0]['banner_id'] . '&type=' . $rows[0]['banner_type'];
            }
            ?>
            <aside class="sectiontable__rightcell" style="background-image:url(<?php echo $src; ?>); background-repeat:no-repeat;"></aside>
        </div>
    </div>
</section>   
<!--bodyContainer end here-->
<?php
require_once './footer.php';
