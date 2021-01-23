<?php
require_once './application-top.php';
$arr_page_css[] = './page-css/msgdie.css';
require_once './header.php';
?>
<div class="bodyContainer">
    <div class="containerTop">
        <h2><?php t_lang('M_TXT_SYSTEM_MESSAGES'); ?></h2>
    </div>
    <div class="clear"></div>
    <div class="bodyWrapper">
        <div class="gap"></div>
        <div class="clear"></div>
        <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
            <div  id="msg">
                <?php
                if (isset($_SESSION['errs'][0])) {
                    $classMsg = 'error';
                }
                if (isset($_SESSION['msgs'][0])) {
                    $classMsg = 'success';
                }
                ?>
                <div class="system-notice <?php echo $classMsg; ?>">
                    <?php /* if(isset($_SESSION['msgs'][0])) echo 'Success Message'; */ ?>
                    <a class="closeMsg" href="javascript:void(0);" onclick="$(this).closest('#msg').hide(); return false;"> <img src="<?php echo CONF_WEBROOT_URL; ?>images/cross.png"></a>
                    <p id="message"><?php echo $msg->display(); ?> </p>
                </div>
            </div>
        <?php } ?>
        <div class="gap"></div>
    </div>
</div> 
<!--bodyContainer end here-->
</section>
</div>
<?php require_once './footer.php'; ?>