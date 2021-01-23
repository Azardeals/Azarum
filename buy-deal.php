<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
$arr_page_js[] = 'js/mbsmessage.js';
$arr_page_css[] = 'css/mbsmessage.css';
if (!isUserLogged()) {
    $_SESSION['login_page'] = $_SERVER['REQUEST_URI'];
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
$cart = new Cart();
require_once './includes/buy-deal-functions.php';
require_once './AuthorizeNet.php';
require_once './site-classes/order.cls.php';
require_once './site-classes/deal-info.cls.php';
require_once './cim-xml/util.php';
if (CONF_PAYMENT_PRODUCTION == 0) {
    //  $system_alerts[] = 'Payment mode is set to test mode. Set it to production mode for real use.';
}
if (!$cart->validateCartItems()) {
    $msg->addError($cart->getError());
}
require_once './header.php';
$rs = $db->query("select user_wallet_amount from tbl_users where user_id=" . intval($_SESSION['logged_user']['user_id']));
$row = $db->fetch($rs);
$wallet_amount = $row['user_wallet_amount'];

function showGiftPopup($deal_id)
{
    ?><script type="text/javascript">$(document).ready(function () {
            loadGiftPopUp(<?php echo intval($deal_id); ?>);
        });</script><?php
}
?>
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
    session_expired = "<?php echo addslashes(t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN')); ?>";
    txtgiftinfo = "<?php echo addslashes(t_lang('M_TXT_EDIT_GIFT_INFO')); ?>";
    txtcancelgift = "<?php echo addslashes(t_lang('M_TXT_CANCEL_GIFT')); ?>";
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
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_CART'); ?></h3>
                <h6><?php echo $cart->getItemCount(); ?> <?php echo t_lang('M_TXT_ITEMS'); ?></h6>
            </aside>
        </div>
    </div>
</section> 
<?php
if ($cart->getItemCount() < 1) {
    echo emptyCartPageDiv();
} else {
    ?>
    <section class="page__container">
        <div class="fixed_container">
            <div class="row">
                <div class="col-md-12">
                    <div class="cart__table">
                        <div class="cart__head">
                            <div class="grid_1"><?php echo t_lang('M_TXT_ITEM_DESCRIPTION'); ?></div>
                            <div class="grid_2"><?php echo t_lang('M_TXT_OPTIONS'); ?></div>
                            <div class="grid_3"><?php echo t_lang('M_TXT_SUBTOTAL'); ?></div>
                        </div>
                        <div class="cart__body">
                            <?php
                            $cart_data_arr = $cart->getProducts();
                            if (!is_array($cart_data_arr) || sizeof($cart_data_arr) < 1) {
                                ?>
                                <div class="emptyarea">
                                    <?php echo emptyCartPageDiv(); ?>
                                    <h5><?php echo unescape_attr(t_lang('M_TXT_EMPTY_CART_MSG')); ?></h5>
                                    <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'all-deals.php'); ?>" class="button blue"><?php echo t_lang('M_TXT_CONTINUE_SHOPPING') ?></a>
                                </div>
                            <?php } else { ?>
                                <?php
                                foreach ($cart_data_arr as $cart_data) {
                                    $deal_can_be_gifted = false;
                                    if ($cart_data['deal_type'] == 0 /* && $cart_data['deal_sub_type'] == 0 */) {
                                        $deal_can_be_gifted = true;
                                    }
                                    if (isset($cart_data['error_msg']) || isset($cart_data['error'])) {
                                        ?>
                                        <tr class="errorTr">
                                            <td colspan="2" id="error_msg_<?php echo $cart_data['key']; ?>" <?php echo ((!isset($cart_data['error_msg'])) ? 'style="display:none;"' : ''); ?>>
                                                <?php
                                                if (isset($cart_data['error'])) {
                                                    echo $cart_data['error'];
                                                    $cart->clearDealErrorMsg($cart_data['key']);
                                                }
                                                if (isset($cart_data['error_msg'])) {
                                                    echo $cart_data['error_msg'];
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <div class="cart__row">
                                        <div class="grid_1">
                                            <div class="item">
                                                <div class="item__head">
                                                    <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'deal.php?deal=' . $cart_data['deal_id'] . '&type=main'); ?>"><img alt="" src="<?php echo CONF_WEBROOT_URL . 'deal-image-crop.php?id=' . $cart_data['deal_id'] . '&type=carttable'; ?>"></a> 
                                                </div>
                                                <div class="item__body">
                                                    <div class="top">
                                                        <span class="item__title"> <?php echo appendPlainText($cart_data['deal_name']); ?></span>
                                                        <?php if ($deal_can_be_gifted) { ?>
                                                            <div class="info__gift" id="gift_wrap_<?php echo $cart_data['key']; ?>">
                                                                <?php if (isset($cart_data['to_email']) && filter_var($cart_data['to_email'], FILTER_VALIDATE_EMAIL)) { ?>
                                                                    <a class="gift themebtn link__gift" href="javascript:void(0)" onclick="return loadGiftPopUp('<?php echo $cart_data['key']; ?>');">
                                                                        <?php include CONF_VIEW_PATH . 'gift-svg.php'; ?>
                                                                        <?php echo t_lang('M_TXT_GIFT_FOR'); ?> : <?php echo $cart_data['to_name']; ?></a>
                                                                    <a class="hidelink" href="javascritp:void(0)" onclick="return cancelGift('<?php echo $cart_data['key']; ?>');"></a>
                                                                <?php } else { ?>
                                                                    <a class="gift themebtn link__gift" href="javascript:void(0)" onclick="return loadGiftPopUp('<?php echo $cart_data['key']; ?>');">
                                                                        <?php include CONF_VIEW_PATH . 'gift-svg.php'; ?>
                                                                        <?php echo t_lang('M_TXT_GIVE_AS_A_GIFT') ?></a>
                                                                <?php } ?>
                                                            </div> 
                                                        <?php } ?>  
                                                    </div>
                                                    <div class="btngroup">
                                                        <!-- <a href="#" class="themebtn themebtn--small themebtn--org">Edit</a>-->
                                                        <a href="javascript:void(0)" id="remove_<?php echo $cart_data['key']; ?>" class="themebtn themebtn--small" onclick="removeItem(this, '<?php echo $cart_data['key']; ?>');"><?php echo t_lang('M_TXT_REMOVE'); ?></a>   
                                                    </div>
                                                </div>    
                                            </div>
                                        </div>
                                        <div class="grid_2">
                                            <ul class="list__options siteForm">
                                                <?php
                                                if ($cart_data['deal_type'] == 1) {
                                                    if (isset($cart_data['option']) && is_array($cart_data['option']) && count($cart_data['option'])) {
                                                        echo '<li>';
                                                        echo '<span class="lefts">' . t_lang('M_TXT_OPTIONS') . ':</span>';
                                                        echo '<span class="rights">';
                                                        echo $p_option = "";
                                                        foreach ($cart_data['option'] as $op) {
                                                            $p_option .= $op['option_name'] . ': <strong>' . $op['option_value'] . ' (Price: ' . $op['price_prefix'] . ' ' . CONF_CURRENCY . round($op['price'], 2) . CONF_CURRENCY_RIGHT . ')</strong>';
                                                            $p_option .= '|';
                                                        }
                                                        echo rtrim($p_option, '|');
                                                        echo '</span>';
                                                        echo '</li>';
                                                    }
                                                }
                                                ?>
                                                <?php
                                                if (($cart_data['startDate'] != "") && is_array($cart_data) && ($cart_data['endDate'] != "")) {
                                                    echo '<li>';
                                                    if ($cart_data['deal_sub_type'] == 2) {
                                                        echo date("D, M j Y", strtotime($cart_data['startDate'])) . ' ' . t_lang('M_TXT_TO') . ' ' . date("D, M j Y", strtotime($cart_data['endDate']));
                                                        $date1 = strtotime($cart_data['startDate']);
                                                        $date2 = strtotime($cart_data['endDate']);
                                                        $diff = $date2 - $date1;
                                                        echo " ( " . floor($diff / 3600 / 24) . ' ' . t_lang('M_TXT_NIGHTS') . " )";
                                                    }
                                                    echo '</li>';
                                                }
                                                ?>
                                                <li>
                                                    <?php
                                                    if ($cart_data['deal_type'] == 0 && ($cart_data['deal_sub_type'] < 2)) {
                                                        if (!empty($cart_data['company_locations'])) {
                                                            $key1 = array_search($cart_data['company_address_id'], array_column($cart_data['company_locations'], 'company_address_id'));
                                                            ?>
                                                            <span class="lefts"><?php echo t_lang('M_TXT_VENDOR'); ?>:</span>
                                                            <span class="rights">
                                                                <div class="selection_area" >
                                                                    <a href="javascript:void(0)" class="seleclink" onclick="showAddress('<?php echo $cart_data['key'] ?>');"><?php echo $cart_data['company_locations'][$key1]['company_address']; ?></a>
                                                                    <div class="section_droparea" id="section_droparea<?php echo $cart_data['key'] ?>" style="display:none;">
                                                                        <?php
                                                                        $deal_key = "'" . $cart_data["key"] . "'";
                                                                        echo '<ul  class="verticaldots_list" id="' . $deal_key . 'company_address_id" name="' . $deal_key . 'company_address_id" >';
                                                                        foreach ($cart_data['company_locations'] as $row_address) {
                                                                            if ($cart_data['company_address_id'] == $row_address['company_address_id']) {
                                                                                $checked = 'selected = "selected"';
                                                                            } else {
                                                                                $checked = '';
                                                                            }
                                                                            echo '<li ' . $checked . ' ><a  href="javascript:void(0);" onClick="return updateDropDown(' . $deal_key . ',' . $row_address['company_address_id'] . ',this);"   > ' . $row_address['company_address'];
                                                                            echo '</a></li>';
                                                                        }
                                                                        echo '</ul>';
                                                                        echo'</div></div></span>';
                                                                    }
                                                                }
                                                                ?>
                                                                </li>
                                                                <li>
                                                                    <?php
                                                                    //$eligible_deal_data = canBuyDeal($cart_data['qty'], false, $cart_data['price'], $cart_data['deal_id'],0,$_SESSION['logged_user']['user_id'],$cart_data['subdeal_id'],$error);
                                                                    $maxBuy = $cart_data['maxBuy'];
                                                                    if ($maxBuy <= 0) {
                                                                        if ($cart->removeItem($cart_data['key'])) {
                                                                            redirectUser();
                                                                        }
                                                                    }
                                                                    if ($maxBuy > 1) {
                                                                        ?>
                                                                        <span class="lefts"><?php echo t_lang('M_TXT_QUANTITY'); ?>:</span>
                                                                        <span class="rights">
                                                                            <select name="quantity_<?php echo $cart_data['key']; ?>" class="qtyfield qty_select_box "  id="<?php echo $cart_data['key']; ?>">
                                                                                <?php for ($i = 1; $i <= $maxBuy; $i++) { ?>
                                                                                    <option value="<?php echo $i; ?>" <?php if ($cart_data['qty'] == $i) echo 'selected="selected"'; ?> >
                                                                                        <?php echo $i; ?>
                                                                                    </option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </span>
                                                                    <?php } ?>
                                                                </li>
                                                                <li>
                                                                    <span class="lefts"><?php echo t_lang('M_TXT_PRICE'); ?>:</span>
                                                                    <span class="rights item__price_standard"><?php echo amount($cart_data['price'], 2); ?></span>
                                                                </li>
                                                                </ul>
                                                                <?php if (!empty($cart_data['charity'])) { ?>
                                                                    <span class="info__small"><?php echo t_lang('M_TXT_DONATE'); ?> <?php echo $cart_data['charity']['charity_discount']; ?> <?php echo t_lang('M_TXT_TO'); ?>  <?php echo $cart_data['charity']['charity_name']; ?>   <a href="javascript:void(0)" class="link__donation" onclick="displayCharityInfo(<?php echo $cart_data['key']; ?>);"><?php echo t_lang('M_TXT_VIEW_MORE'); ?></a></span>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="grid_3">
                                                                <div class="item__price">
                                                                    <span class="item__price_standard" id="ptotal_<?php echo $cart_data['key']; ?>"><?php echo amount($cart_data['total'], 2); ?></span>
                                                                </div>
                                                            </div>
                                                        </div> 
                                                        <div class="row__information info__donation_<?php echo $cart_data['key']; ?>" style="display:none;">
                                                            <a href="javascript:void(0)" class="link__close link__donation"></a>
                                                            <?php
                                                            if (is_array($cart_data['charity'])) {
                                                                echo getCharityDetail($cart_data['charity']);
                                                            }
                                                            ?>
                                                        </div>
                                                        <?php
                                                        $sub_total += $cart_data['total'];
                                                    }
                                                }
                                                ?>                  
                                                <?php if ($cart->isEmpty() == false) { ?>
                                                    <div class="row row--total">
                                                        <div class="col-md-12">
                                                            <h5 class="right">
                                                                <?php
                                                                $grand_total = ($sub_total - $discount);
                                                                if ($grand_total < 0)
                                                                    $grand_total = 0;
                                                                echo '<span class="totl-prc">' . t_lang('M_TXT_GRAND_TOTAL') . ' = <span class="cart_sub_total" >' . amount($grand_total, 2) . '</span>';
                                                                ?>
                                                            </h5>  
                                                            <p class="text__normal"><?php echo t_lang('M_TXT_PURCHASE_AGREE_DEAL_CHECKOUT'); ?> <?php echo'<a href="' . friendlyUrl(CONF_WEBROOT_URL . 'terms.php') . '" target="_blank">' . t_lang('M_TXT_TERMS_AND_CONDITIONS') . '</a>'; ?> </p>
                                                        </div>    
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row__buttons clearfix">
                                                                <a class="themebtn  left" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'all-deals.php'); ?>"><?php echo t_lang('M_TXT_CONTINUE_SHOPPING'); ?></a>
                                                                <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'cart-checkout.php'); ?>" class="themebtn right themebtn--org "><?php echo t_lang('M_TXT_PROCEED_TO_CHECKOUT'); ?></a>
                                                            </div>
                                                        </div>    
                                                    </div>
                                                <?php } ?>
                                                </div>  
                                                </section>           
                                            <?php } ?>
                                            <?php
                                            if (isset($_SESSION['gift'])) {
                                                if ($_SESSION['errs'][0] == '') {
                                                    ?><script type="text/javascript">$(document).ready(function () {
                                                            loadGiftPopUp(<?php echo $_SESSION['gift']; ?>);
                                                        });</script><?php
                                                }
                                                unset($_SESSION['gift']);
                                            }
                                            ?>
                                            <?php
                                            require_once './footer.php';
                                            ?>