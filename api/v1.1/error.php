<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
?>
<link rel="stylesheet" href="<?php echo CONF_WEBROOT_URL . 'page-css/msgdie.css'; ?>" type="text/css">
<?php
global $msg;
if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
    ?> 
    <div id="body">
        <section class="pagebar center">
            <div class="fixed_container">
                <div class="row">
                    <aside class="col-md-12">
                        <h3><?php t_lang('M_TXT_SYSTEM_MESSAGES'); ?></h3>
                    </aside>
                </div>
            </div>
        </section> 
        <section class="page__container">
            <div class="fixed_container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="layout__centered">
                            <div class="content__centered">
                                <h3><?php echo unescape_attr(t_lang('M_TXT_NOW_SHARE_IT')); ?></h3>
                                <p><?php
                                    if (isset($s_odr_row) && $s_odr_row['order_payment_mode'] == 3 && $s_odr_row['order_payment_status'] == 1) {
                                        /* The message is for paid orders, payment through wallet */
                                        echo t_lang('M_TXT_SUCCESS_PAID_DEAL');
                                    } else {
                                        if ($s_odr_row['deal_type'] == 0) {
                                            echo t_lang('M_TXT_SUCCESSFULLY_BOUGHT_DEAL');
                                        }
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="content__centered bg__grey">
                                <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
                                    <div  id="msg">
                                        <?php
                                        if (isset($_SESSION['errs'][0]))
                                            $classMsg = 'error';
                                        if (isset($_SESSION['msgs'][0]))
                                            $classMsg = 'success';
                                        ?>
                                        <div class="system-notice <?php echo $classMsg; ?>">
                                            <?php /* if(isset($_SESSION['msgs'][0])) echo 'Success Message'; */ ?>
                                            <p id="message"><?php echo $msg1->display(); ?> </p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div> 
                        </div>
                    </div>
                </div>    
            </div>    
        </section>   
    </div>
    <?php
} else {
    die('Problem with cart');
}
?>
