<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
require_once "../qrcode/qrlib.php";

checkAdminPermission(5);

if ($_GET['used'] != "" && $_GET['submit_form'] == 1) {

    if(empty($_POST['mark_as_used_code'])){
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
        if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == ""){
            redirectUser('tipped-members.php');
        }
        redirectUser($_SERVER['HTTP_REFERER']);
    }

    $markAsUsedCode = $_POST['mark_as_used_code'];

    if(!voucherMarkUsed($_GET['used'], false, false, false, $markAsUsedCode)) {
        redirectUser('tipped-members.php');
    }
    redirectUser('voucher-detail.php?id=' . $_GET['used']);
}

if (!empty($_GET['used'])) {
    $markAsUsedForm = new Form('frmMarkAsUsed');
    $markAsUsedForm->setAction('?used='.$_GET['used'].'&submit_form=1');
    $markAsUsedForm->setJsErrorDisplay('afterfield');
    $markAsUsedForm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
    $markAsUsedForm->addTextBox(t_lang('M_TXT_Virtual_Code'), 'mark_as_used_code');

    $markAsUsedForm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', 'class="inputbuttons"');
}

/* ------ Insert voucher number starts here -------- */
insertVoucherNumbers();
/*   ------ Insert voucher number End Here -------- */
$id = $_GET['id'];
$row_deal = [];
$message = '';

if(empty($_GET['used'])) {
    printVoucherDetail($id, $row_deal, $message);
    if (!isset($message) || $message === null || strlen($message) < 10) {
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
        if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == "") {
            redirectUser('tipped-members.php');
        }
        redirectUser($_SERVER['HTTP_REFERER']);
    }
}

if (!empty($_GET['used'])) {
    require_once './header.php';

    $arr_bread = array(
        'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
        'deals.php' => t_lang('M_TXT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS'),
        'tipped-members.php' => t_lang('M_TXT_TIPPED_MEMBERS_LISTING'),
        'voucher-detail.php?id='. $_GET['used'] => t_lang('M_TXT_VOUCHER_DETAIL'),
        '' => t_lang('M_TXT_MARK_USED'),
    );
    ?>
    <ul class="nav-left-ul">

    </ul>
    </div></td>
    <td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
        <div class="div-inline">
            <?php if(!empty($_GET['used'])) { ?>
                <div class="page-name"><?php echo t_lang('M_TXT_MARK_USED'); ?> </div>
            <?php } else { ?>
                <div class="page-name"><?php echo t_lang('M_TXT_TIPPED_MEMBERS_LISTING'); ?> </div>
            <?php } ?>
        </div>
        <div class="clear"></div>
        <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
            <div class="box" id="messages">
                <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
                <div class="content">
                    <?php if (isset($_SESSION['errs'][0])) { ?>
                        <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                    <?php } if (isset($_SESSION['msgs'][0])) { ?>
                        <div class="greentext"> <?php echo $msg->display(); ?> </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if(!empty($_GET['used'])) { ?>
            <div class="box">
                <div class="title">
                    <?php echo t_lang('M_TXT_MARK_USED'); ?>
                </div>
                <div class="content">
                    <?php echo $markAsUsedForm->getFormHtml(); ?>
                </div>
            </div>
        </td>
        <?php require_once('./footer.php'); ?>
<?php
    }
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="page-css/voucher-detail.css" />
        <script src="<?php echo CONF_WEBROOT_URL; ?>js/jquery-1.4.2.js" type="text/javascript"></script>
    </head>
    <body>
        <table cellspacing="0" cellpadding="0" border="0" bgcolor="#5894cd" align="center" width="900">
            <tbody>
                <tr>
                    <td>
        <?php

        if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
            ?>
            <div class="box" id="messages">
                <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
                <div class="content">
                    <?php
                        if (isset($_SESSION['errs'][0])) {
                    ?>
                            <div class="redtext"><?php echo $msg->display(); ?> </div>
                            <br/>
                            <br/>
                    <?php
                        }
                        if (isset($_SESSION['msgs'][0])) {
                    ?>
                            <div class="greentext"> <?php echo $msg->display(); ?> </div>
                    <?php
                        }
                    ?>
                </div>
            </div>
                <?php
                }
                if ($row_deal['active'] == 1 && $row_deal['canUse'] == 1) {
                    echo '<a href="?used=' . $_GET['id'] . '" class="btn gray">Mark Used</a>';
                } else {
                    //echo '<a   href="javascript:void(0);" onclick="alert(\'' . t_lang('M_MSG_VOUCHER_IS_NOT_ACTIVE_TO_USE') . '\')" class="btn gray">'.t_lang('M_TXT_MARK_USED').'</a> ';
                }


                ?>
                </td></tr></table>
                <?php
                    echo emailTemplate($message);
                 ?>
        </body>
</html>
<?php
}
?>
