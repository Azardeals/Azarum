<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
require_once './header.php';
checkAdminPermission(5);
include './update-deal-status.php';
$getRes = $db->query("select * from tbl_order_transactions where ot_order_id='" . $_GET['order'] . "'");
$resRow = $db->fetch($getRes);
$ot_gateway_response = $resRow['ot_gateway_response'];
?> 
</div></td>
<td class="right-portion"> 
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_GATEWAY_RESPONSE'); ?></div>       
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <div class="box">
        <div class="content">
            <?php echo '<pre>' . $ot_gateway_response . '</pre>'; ?>
            <div class="gap">&nbsp;</div>	
        </div>
    </div>
</td>
<?php require_once './footer.php'; ?>
