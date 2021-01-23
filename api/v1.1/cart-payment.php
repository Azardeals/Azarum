<?php
require_once realpath(dirname(__FILE__) . "/../../") . '/application-top.php';
require_once realpath(dirname(__FILE__) . "/../../") . '/includes/navigation-functions.php';
$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : $_SESSION['token'];
$_SESSION['token'] = $token;
$srch = new SearchBase('tbl_user_api_token');
$srch->addCondition('uapitoken_token', '=', $token);
$srch->doNotCalculateRecords();
$srch->doNotLimitRecords();
$rs = $srch->getResultSet();
$row = $db->fetch($rs);
if (!$row) {
    die('Invalid token');
}
if ($row) {
    $srch = new SearchBase('tbl_users');
    $srch->addCondition('user_id', '=', $row['uapitoken_user_id']);
    $rs = $srch->getResultSet();
    if ($db->total_records($rs) == 0) {
        die('Invalid User Id');
    }
    $rowdata = $db->fetch($rs);
    $_SESSION['logged_user'] = $rowdata;
}
$cart = new Cart();
//error_reporting(E_ALL);
//ini_set('display_errors',1);
require_once realpath(dirname(__FILE__) . "/../../") . '/includes/buy-deal-functions.php';
require_once realpath(dirname(__FILE__) . "/../../") . '/AuthorizeNet.php';
require_once realpath(dirname(__FILE__) . "/../../") . '/site-classes/order.cls.php';
require_once realpath(dirname(__FILE__) . "/../../") . '/site-classes/deal-info.cls.php';
require_once realpath(dirname(__FILE__) . "/../../") . '/cim-xml/util.php';
if (CONF_PAYMENT_PRODUCTION == 0) {
    $system_alerts[] = 'Payment mode is set to test mode. Set it to production mode for real use.';
}
if (!isset($_POST['mode'])) {
    $_POST['mode'] = "";
}
/* CODE FOR AUTHORIZED.NET START HERE */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && strtoupper($_POST['mode']) == 'CHARGECARD') {
    if (!$cart->validateCartItems()) {
        $msg->addError(t_lang('M_TXT_YOU_HAVE_SOMETHING_WRONG_WITH_YOUR_CART_ITEM'));
        redirectUser();
    }
    chargeCard();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$cart->validateCartItems()) {
        $msg->addError(t_lang('M_TXT_YOU_HAVE_SOMETHING_WRONG_WITH_YOUR_CART_ITEM'));
        redirectUser();
    }
    if (!$cart->validateShippingCharges()) {
        $msg->addError('Shipping details are not saved!!');
        redirectUser((CONF_WEBROOT_URL . 'api/error.php'));
    }
    $frm = getFormAuthorize();
    $rs = $db->query("select * from tbl_payment_options where po_id=2");
    $row = $db->fetch($rs);
    if ($row['po_active'] == 0) {
        $msg->addError(t_lang('M_TXT_AUTHORIZE_PAYMENT_NOT_ACTIVE'));
        redirectUser(CONF_WEBROOT_URL . 'api/error.php');
    }
    $qs = $db->query("select user_email,user_id from tbl_users where user_id=" . $_SESSION['logged_user']['user_id']);
    $rows = $db->fetch($qs);
    $login_id = (CONF_PAYMENT_PRODUCTION == 1) ? $row['po_account_id'] : $row['po_test_account_id'];
    $transaction_key = (CONF_PAYMENT_PRODUCTION == 1) ? $row['po_key'] : $row['po_test_key'];
    /** Start the upgraded authorized.net code * */
    $authAry = array('login_id' => $login_id, 'transaction_key' => $transaction_key);
    $authPay = new AuthorizeAimPayController();
    $total_amount = $cart->getCartTotal(true);
    $chargedAmount = $total_amount - $post['charge_from_wallet'];
    $authFields = array(
        'address' => $post['billing_address'],
        'amount' => round($chargedAmount, 2),
        'card_num' => $post['card_number'],
        'exp_date' => $post['expire_month'] . substr($post['expire_year'], -2),
        'card_code' => $post['security_code'],
        'city' => $post['city'],
        'first_name' => $post['card_name'],
        'last_name' => $post['last_name'],
        'state' => $post['state'],
        'zip' => $post['postal_code'],
        'expire_month' => $post['expire_month'],
        'expire_year' => $post['expire_year'],
        'country' => $post['country'],
        'email' => $rows['user_email'],
        'user_id' => $rows['user_id']
    );
    $response = $authPay->send($authAry, $authFields);
    if ($response != null) {
        $tresponse = $response->getTransactionResponse();
        if ($response->getMessages()->getResultCode() == "Ok") {
            if (!$orderId = $cart->processOrder(2, $post['charge_from_wallet'], true)) {
                $msg->addMsg(t_lang('M_ERROR_ORDER_EXECUTION_ERROR') . $cart->getError());
                redirectUser((CONF_WEBROOT_URL . 'api/error.php'));
            }
            $cart->clearCart();
            /*   ------ Insert voucher number -------- */
            insertVouchers($orderId);
            /*   ------ Insert voucher number End Here -------- */
            ############### EMAIL TO USERS#################
            notifyAboutPurchase($orderId); /* the function is written in buy-deal-functions.php by Lakhvir */
            ################ EMAIL TO USERS#################
            $arr = array(
                'ot_order_id' => $orderId,
                'ot_transaction_id' => $tresponse->getTransId(),
                'ot_transaction_status' => 1,
                'ot_gateway_response' => var_export($response, true)
            );
            if (!$db->insert_from_array('tbl_order_transactions', $arr)) {
                $msg->addMsg(t_lang('M_ERROR_TRANSACTION_NOT_UPDATED') . $tresponse->getTransId());
            }
            redirectUser((CONF_WEBROOT_URL . 'api/app-success.php?order_id=' . $orderId));
            require_once 'msgdie.php';
        } else {
            if ($tresponse != null && $tresponse->getErrors() != null) {
                $error = $tresponse->getErrors()[0]->getErrorText();
            } else {
                $error = $response->getMessages()->getMessage()[0]->getText();
            }
            $msg->addError($error);
            $frm->fill($post);
            redirectUser((CONF_WEBROOT_URL . 'api/error.php'));
        }
    } else {
        $msg->addError(t_lang('M_ERROR_No_response_returned'));
        $frm->fill($post);
        redirectUser((CONF_WEBROOT_URL . 'api/error.php'));
    }
}
/* CODE FOR AUTHORIZED.NET END HERE */
$rs = $db->query("select user_wallet_amount from tbl_users where user_id=" . intval($_SESSION['logged_user']['user_id']));
$row = $db->fetch($rs);
$wallet_amount = $row['user_wallet_amount'];
?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">    
        <?php require_once 'js-and-css.inc.php'; ?>
        <script type="text/javascript" src="<?php echo CONF_WEBROOT_URL . 'page-js/cart-payment.js' ?>"></script>
        <script type="text/javascript" >
            minBuy = 1;
<?php $currenttime = strtotime(dateForTimeZone(CONF_TIMEZONE)); ?>
            var txtsessionexpire = "<?php echo addslashes(t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN')); ?>";
            txtshippingAdd = "<?php echo addslashes(t_lang('M_TXT_SHIPPING_ADDRESS')) ?>";
            txtaddnew = "<?php echo addslashes(t_lang('M_TXT_ADD_NEW')) ?>";
            txtbackbutton = "<?php echo addslashes(t_lang('M_TXT_GO_BACK')) ?>";
            txtselectpaymthod = "<?php echo addslashes(t_lang('M_TXT_SELECT_PAYMENT_METHOD')) ?>";
            var txtusersessionexpire = "<?php echo t_lang('M_TXT_SESSION_EXPIRES'); ?>";
            txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
            txtreload = "<?php echo addslashes(t_lang('M_JS_PLEASE_RELOAD_AND_TRY')); ?>";
            txtgiftfor = "<?php echo addslashes(t_lang('M_TXT_GIFT_FOR')); ?>";
            currency_left = "<?php echo CONF_CURRENCY; ?>";
            currency_right = "<?php echo CONF_CURRENCY_RIGHT; ?>";
            var txtprocessing = "<?php echo addslashes(t_lang('M_JS_PROCESSING')); ?>";
            txtqtyupdated = "<?php echo addslashes(t_lang('M_JS_QUANTITY_TO_BUY_UPDATED')); ?>";
            txtaddressupdated = "<?php echo addslashes(t_lang('M_JS_ADDRESS_UPDATED')); ?>";
            txtcharityupdated = "<?php echo addslashes(t_lang('M_TXT_CHARITY_UPDATED')); ?>";
            cleft = "<?php echo addslashes(CONF_CURRENCY); ?>";
            cright = "<?php echo addslashes(CONF_CURRENCY_RIGHT); ?>";
        </script>
        <style>
            /*  .page--payment .tabs_content{padding:10px;}
            .page--payment .togglehead{background: #f2f2f2; color: #000;}
            .page--payment .togglehead:before,.page--payment .togglehead:after{background: #000;}
            
            .page--payment .table__white{table-layout: auto;}
            .page--payment .table__white td{display: block;text-align: left; padding: 10px 0; width:100%;}
             .page--payment .table__white tr{display: block;text-align: left; padding: 10px 0; width:100%;} */
        </style>
    </head>
    <body>
        <?php
        $sub_total = 0;
        $grand_total = 0;
        ?>
        <!-- strt new page from here -->
        <div id="body">
            <section class="pagebar">
                <div class="fixed_container">
                    <div class="row">
                        <aside class="col-md-6">
                            <h3><?php echo t_lang('M_TXT_CART'); ?></h3>
                        </aside>
                    </div>
                </div>
            </section> 
            <section class="page__container">
                <div class="fixed_container">
                    <div class="row">
                        <div class="col-md-4 section__right right">
                            <div id="stickyright">
                                <?php
                                $total = $cart->getCartTotal();
                                $tax_Amount = $cart->getTaxAmount();
                                $sub_total = $total - $tax_Amount;
                                ?>
                                <div class="table__total">
                                    <h5><?php echo t_lang('M_TXT_CART_SUMMARY'); ?></h5>
                                    <table>
                                        <tbody id="cart_summary" >
                                            <tr class=""  id="">
                                                <td><?php echo t_lang('M_TXT_SUB_TOTAL'); ?></td>
                                                <td id="cart_sub_total"><?php echo amount($sub_total, 2); ?></td>
                                            </tr>
                                            <tr class="cart_summary_options"  id="ship_sum_container">
                                                <td><?php echo t_lang('M_TXT_SHIPPING_CHARGES'); ?></td>
                                                <td id="cart_shipping_charges"><?php echo amount($cart->getShippingCharges(), 2); ?></td>
                                            </tr>
                                            <tr >
                                                <td><span><?php echo t_lang('M_TXT_TAX_CHARGES'); ?></span></td>
                                                <td class="tax"><?php echo amount($tax_Amount, 2); ?></td>
                                            </tr>
                                            <tr class="total last">
                                                <td><span><?php echo t_lang('M_TXT_GRAND_TOTAL'); ?></span></td>
                                                <td class="cart_grand_total">
                                                    <?php echo amount($total, 2); ?>
                                                </td>
                                            </tr>
                                        </tbody></table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="section__bordered stepscheckout">
                                <div class="allsteps siteForm">
                                    <?php
                                    $cart_deal_ids = array_column($cart->getCart(), 'cart_item_deal_id');
                                    if (!is_array($cart_deal_ids) || count($cart_deal_ids) <= 0) {
                                        die(t_lang('M_TXT_CART_IS_EMPTY'));
                                    }
                                    $products_in_cart = getTotalProductsInCart($cart_deal_ids);
                                    if ($products_in_cart > 0) {
                                        ?>
                                        <div class="step selected">
                                            <div class="step__head"><?php echo t_lang('M_TXT_CONFIRM_ADDRESS'); ?> <a href="javascript:void(0);" class="linknormal"onclick="loadPage('shipping')"><?php echo t_lang('M_TXT_EDIT'); ?></a></div>
                                            <div class="step__body cartboxes">
                                                <div class="step__top">
                                                </div>
                                                <div class="shiping-address" id="shipping"></div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="step">
                                        <div class="step__head"><?php echo t_lang('M_TXT_REVIEW_ORDER'); ?> <a href="javascript:void(0);" class="linknormal"onclick="loadPage('reviewOrder')"><?php echo t_lang('M_TXT_EDIT'); ?></a></div>
                                        <div class="step__body cartboxes">
                                            <div class="   " id="reviewOrder"></div>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <div class="step__head"><?php echo t_lang('M_TXT_MAKE_PAYMENT'); ?>  <a href="javascript:void(0);" class="linknormal"onclick="loadPage('payment')"><?php echo t_lang('M_TXT_EDIT'); ?></a></div>
                                        <div class="step__body cartboxes"  >
                                            <div class="  " id="payment"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </div>
                    </div>
                </div>    
            </section>
        </div>
        <!-- end new page from here -->
        <!--tabs for payment-->
        <script type="text/javascript">
            $(document).ready(function () {
                loadPage('shipping');
            })
            function paymenttabScript() {
                // tabbed content
                $(".tabs_content").hide();
                $(".togglehead:first").addClass('active');
                $(".tabs_content:first").show();
                $(".linkslarge li a").click(function () {
                    $(".tabs_content").hide();
                    var activeTab = $(this).attr("rel");
                    $("#" + activeTab).fadeIn();
                    $(".linkslarge li a").removeClass("active");
                    $(this).addClass("active");
                    $(".togglehead").removeClass("active");
                    $(".togglehead[rel^='" + activeTab + "']").addClass("active");
                });
                $(".togglehead").click(function () {
                    $(".tabs_content").hide();
                    var d_activeTab = $(this).attr("rel");
                    $("#" + d_activeTab).fadeIn();
                    $(".togglehead").removeClass("active");
                    $(this).addClass("active");
                    $(".linkslarge li a").removeClass("active");
                    $(".linkslarge li a[rel^='" + d_activeTab + "']").addClass("active");
                });
                $('.linkslarge li a').last().addClass("tab_last");
            }
            var cleft = '<?php echo CONF_CURRENCY; ?>';
            var currency_left = '<?php echo CONF_CURRENCY; ?>';
            var cright = '<?php echo CONF_CURRENCY_RIGHT; ?>';
        </script>  
        <?php if (isset($_REQUEST['show'])) { ?>
            <script>
                $(window).load(function () {
                    $(".chkout").trigger("click");
                    setTimeout(function () {
                        $("#btn_save_shipadr").trigger("click");
                    }, 2000);
                });
            </script>
        <?php } ?>
        <style type="text/css">
            #mbsmessage .content, #mbsmessage .tdclose {
                font-size: 12px;
                font-weight: normal;
            }	
            div#mbsmessage {
                text-align: center;
                width: 100%;
                left: 28%;
            }	
        </style>
    </body>
</html>