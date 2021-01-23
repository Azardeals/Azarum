<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/buy-deal-functions.php';
if (isset($_SESSION['token']) && $_SESSION['token'] != "") {
    $url = CONF_WEBROOT_URL . 'api/error.php';
} else {
    $url = friendlyUrl(CONF_WEBROOT_URL . 'buy-deal.php');
}
if (!isUserLogged()) {
    $_SESSION['login_page'] = $_SERVER['REQUEST_URI'];
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
require_once './site-classes/order.cls.php';
require_once './site-classes/user-info.cls.php';
$cart = new Cart();
$user = new userInfo();
if ($cart->isEmpty()) {
    redirectUser($url);
}
if (!$cart->validateCartItems()) {
    $msg->addMsg(t_lang('M_ERROR_YOU_HAVE_NOT_SELECTED_ANY_QUANTITY'));
    redirectUser($url);
}
if (!$cart->validateShippingCharges()) {
    $msg->addError('Shipping details are not saved!!');
    redirectUser($url);
}
$total_payable = $cart->getCartTotal(true);
$rs = $db->query("select * from tbl_payment_options where po_id=1");
$row = $db->fetch($rs);
if ($row['po_active'] == 0) {
    $msg->addMsg(t_lang('M_TXT_PAYPAL_PAYMENT_NOT_ACTIVE'));
    redirectUser($url);
}
$walletAmount = 0;
if ($_GET['wallet'] > 0) {
    $walletAmount = $user->getUserWalletAmount(intval($_SESSION['logged_user']['user_id']));
    $total_payable = $total_payable - $walletAmount;
}
if (!$orderId = $cart->processOrder(1, $walletAmount, false)) {
    $msg->addMsg(t_lang('M_ERROR_ORDER_EXECUTION_ERROR') . $cart->getError());
    require_once './msgdie.php';
}
$ps_paypal_url = (CONF_PAYMENT_PRODUCTION == 1) ? $row['po_address'] : $row['po_test_address'];
$ps_paypal_merchant = (CONF_PAYMENT_PRODUCTION == 1) ? $row['po_account_id'] : $row['po_test_account_id'];
if (strlen($orderId) < 13) {
    $msg->addMsg(t_lang('M_ERROR_ORDER_EXECUTION_ERROR'));
    require_once './msgdie.php';
}
require_once './application-top.php';
require_once './header.php';
?>
<form action="<?php echo $ps_paypal_url; ?>" method="post" id="myForm">
    <input type="hidden" name="business" value="<?php echo $ps_paypal_merchant; ?>">
    <input type="hidden" name="notify_url" value="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'paypal-ipn.php'; ?>">
    <input type="hidden" name="item_name" value="<?php echo 'For Order ' . $orderId; ?>">
    <input type="hidden" name="quantity" value="1">

    <input TYPE="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="amount" value="<?php echo round($total_payable, 2); ?>">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="custom" value="<?php echo $orderId; ?>">
    <?php
    if (CONF_FRIENDLY_URL == 1) {
        $success = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'success/' . $orderId;
    } else {
        $success = ('http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'success.php?dp_id=' . $orderId);
    }
    ?>	
    <input type="hidden" name="return" value="<?php echo $success; ?>">
    <input type="hidden" name="currency_code" value="<?php echo CONF_CURRENCY_CODE; ?>">
</form>


<section class="page__container" style="Margin-top:10%;">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-4">
            </div>			
            <div class="col-md-4">
                <div id="paypalText"><p><?php echo addslashes(t_lang('M_TXT_PAYPAL_REDIRECT_MSG')); ?></p></div>
                <div id="loader"><img src="/images/loader.gif"     style="text-align: center;  margin: auto;" ></div>
            </div>
            <div class="col-md-4">
            </div>
        </div>
    </div>
</section>

<script language="javascript">
    //document.write("<?php echo addslashes(t_lang('M_TXT_PAYPAL_REDIRECT_MSG')); ?>");
    //document.forms[0].submit(); 
    document.getElementById("myForm").submit();
</script>
<?php
?>
<?php
//require_once './footer.php';
?>
