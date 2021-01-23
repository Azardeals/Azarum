<?php
require_once './application-top.php';
require_once '../securimage/securimage.php';
require_once '../includes/navigation-functions.php';
$post = getPostedData();
if (isset($_REQUEST['code'])) {
    $userArr = explode('_', $_REQUEST['code']);
    if ($userArr[0] > 0) {
        $query1 = $db->query("select * from  tbl_admin_password_resets_requests where aprr_admin_id=" . $userArr[0]);
        $result1 = $db->fetch($query1);
    }
    if ($result1) {
        if ($userArr[0] > 0) {
            $query2 = $db->query("select * from  tbl_admin_password_resets_requests where  aprr_expiry > (NOW() - INTERVAL 1 DAY) and aprr_tocken = " . $db->quoteVariable($userArr[1]) . " and aprr_admin_id=" . intval($userArr[0]));
            $result2 = $db->fetch($query2);
        }
        if ($db->total_records($query2) == 1) {
            $frmForgot = getMBSFormByIdentifier('frmResetAdminPassword');
            //$frmForgot->setAction('?');
            $frmForgot->setExtra('class="web_form"');
            $frmForgot->setJsErrorDisplay('afterfield');
        } else {
            $msg->addError(t_lang('M_TXT_RESET_ERROR_MESSAGE'));
            redirectUser('login.php');
        }
    } else {
        $msg->addError(t_lang('M_TXT_RESET_ERROR_MESSAGE'));
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['admin_id'] > 0 && $_POST['tocken'] != "") {
        $query2 = $db->query("select * from  tbl_admin_password_resets_requests where  aprr_expiry > (NOW() - INTERVAL 1 DAY) and aprr_tocken = " . $db->quoteVariable($_POST['tocken']) . " and aprr_admin_id=" . intval($_POST['admin_id']));
        $result2 = $db->fetch($query2);
        if ($db->total_records($query2) == 1) {
            if ($frmForgot->validate($post)) {
                $query3 = $db->query("select * from  tbl_admin where  admin_id=" . intval($result2['aprr_admin_id']));
                $result3 = $db->fetch($query3);
                if ($db->total_records($query3) == 1) {
                    if ($post['password'] != '')
                        $admin_password = md5($post['password']);
                    $db->query("UPDATE tbl_admin SET admin_password = " . $db->quoteVariable($admin_password) . " WHERE  admin_id=" . intval($result3['admin_id']));
                    $db->query("DELETE FROM   tbl_admin_password_resets_requests   WHERE  aprr_admin_id=" . intval($result3['admin_id']));
                    $msg->addMsg(t_lang('M_TXT_PASSWORD_UPDATED'));
                    redirectUser('login.php');
                }
            }
        }
    }
}
?>
<!Doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <?php
        $arr_common_css[] = 'manager/page-css/login.css';
        $arr_common_css[] = 'manager/css/.css';
        include 'meta.inc.php';
        include 'js-and-css.inc.php';
        ?>
    </head>
    <body class="enterpage">
        <?php if ((isset($_SESSION['msgs'][0]))) { ?>
            <div class="system_message">
                <a class="closeMsg" href="javascript:void(0);" onclick="closediv()"></a>
                <?php echo $msg->display(); ?>
            </div>
        <?php } ?>
        <main id="wrapper">
            <div class="backlayer">
                <?php
                if (CONF_ADMIN_PANEL_LOGO == "") {
                    $src = CONF_WEBROOT_URL . 'images/login_screen_logo.png';
                } else {
                    $src = LOGO_URL . CONF_ADMIN_PANEL_LOGO;
                }
                ?>
                <div class="layerLeft" style="background-image:url(images/dealsbg.jpg); background-repeat:no-repeat;">
                    <figure class="logo"><img alt="" src="<?php echo $src; ?>"></figure>
                </div>
                <div class="layerRight" style="background-image:url(images/dealsbg.jpg); background-repeat:no-repeat;">
                    <figure class="logo"><img alt="" src="<?php echo $src; ?>"></figure>
                </div>
            </div>
            <div class="left">
                <div class="formcontainer">
                    <?php if ($result1) { ?>
                        <h5><?php echo t_lang('M_TXT_RESET_PASSWWORD'); ?> </h5>
                        <?php echo $frmForgot->getFormTag(); ?>
                        <div class="field_control fieldicon secure">
                            <label class="field_label"><?php echo t_lang('M_FRM_PASSWORD'); ?> <span class="mandatory">*</span></label>
                            <div class="field_cover">
                                <?php echo $frmForgot->getFieldHTML('password'); ?>
                            </div>
                        </div>
                        <div class="field_control fieldicon secure">
                            <label class="field_label"><?php echo t_lang('M_FRM_CONFIRM_PASSWORD'); ?> <span class="mandatory">*</span></label>
                            <div class="field_cover">
                                <?php echo $frmForgot->getFieldHTML('confirm_password'); ?>
                            </div>
                        </div>
                        <input type ="hidden" name="tocken" value="<?php echo $userArr[1]; ?>">
                        <input type ="hidden" name="code" value="<?php echo $_REQUEST['code']; ?>">
                        <input type ="hidden" name="admin_id" value="<?php echo $userArr[0]; ?>">
                        <span class="circlebutton"><?php echo $frmForgot->getFieldHTML('submit'); ?></span>
                        </form>
                        <?php echo $frmForgot->getExternaljs(); ?>
                    <?php } ?>
                    <?php if ((isset($_SESSION['errs'][0]))) { ?>
                        <a class="closeMsg" href="javascript:void(0);" onclick="closediv()"></a>
                        <?php echo $msg->display(); ?>
                    <?php } ?>
                </div>
            </div>
        </main>
    </body>
</html>
