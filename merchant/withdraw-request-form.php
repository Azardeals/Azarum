<?php
require_once '../application-top.php';
$frm = getMBSFormByIdentifier('frmCompanyWithdraw');
?>
<div class="box">
    <div class="title"><?php echo t_lang('M_TXT_WITHDRAW_REQUEST'); ?></div>
    <div class="content"><?php echo $frm->getFormHtml(); ?></div>
</div>