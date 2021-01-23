<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/voucher-functions.php';
$page = is_numeric($_POST['page']) ? $_POST['page'] : 1;
$pagesize = 10;
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
require_once './header.php';
if (isset($_POST['purchase'])) {
    $purchase = $_POST['purchase'];
} else {
    $purchase = 'desc';
}
if (isset($_POST['type'])) {
    $type = $_POST['type'];
} else {
    $type = '-1';
}
$srch = fetchVoucherObj($type, $purchase, $page, $pagesize);
$rs_listing = $srch->getResultSet();
$total_records = $srch->recordCount();
$pages = $srch->pages();
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php require_once './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_MY_VOUCHERS'); ?></h2>
                <ul class="grids__half list__inline right even">
                    <li>
                        <div class="sort siteForm">
                            <select onchange="getValue('purchase', this);">
                                <option value="asc" <?php echo (($purchase == 'asc') ? ' selected="selected"' : '') ?> ><?php echo t_lang('M_TXT_PURCHASE_DATE') . ' ' . t_lang('M_TXT_ASC'); ?> </option>
                                <option   value="desc" <?php echo (($purchase == 'desc' ) ? 'selected="selected"' : '') ?> onchange= ><?php echo t_lang('M_TXT_PURCHASE_DATE') . ' ' . t_lang('M_TXT_DESC'); ?> </option>
                            </select>
                        </div>
                    </li>
                    <li>
                        <div class="sort siteForm hightlight">
                            <select onchange="getValue('type', this);" >
                                <option <?php echo (($type == '-1') ? 'selected' : '' ) ?> value="-1"><?php echo t_lang('M_TXT_SELECT'); ?></option>
                                <option <?php echo (($type == 0) ? 'selected' : '' ) ?> value="0" ><?php echo t_lang('M_TXT_DEAL'); ?> </option>
                                <option <?php echo (($type == 1) ? 'selected' : '') ?> value="1" ><?php echo t_lang('M_TXT_PRODUCTS'); ?></option>
                            </select>
                        </div>
                    </li>
                </ul>
                <div class="allblock">
                    <?php
                    while ($row = $db->fetch($rs_listing)) {
                        $voucher = $row['cm_counpon_no'];
                        ?>
                        <div class="orderblock">      
                            <div class="orderblock__head"><h5><?php echo t_lang('M_TXT_ORDER_ID') . ' : ' . $row['order_id']; ?> </h5>
                                <?php
                                $show_order_payment_instruction = '';
                                if (($row['cm_status']) == 3) {
                                    echo '<span class="label label-primary">' . t_lang('M_TXT_REFUNDED') . '</span>';
                                } else if ($row['order_payment_status'] == '1') {
                                    echo '<span class="label label-success">' . t_lang('M_TXT_PAID') . '</span>';
                                    $show_order_payment_instruction = t_lang('M_TXT_SHOW_ORDER_PAYMENT_SUCCESS_INSTRUCTION');
                                } else if ($row['order_payment_status'] == '0') {
                                    echo '<span class="label label-info">' . t_lang('M_TXT_PENDING') . '</span>';
                                    $show_order_payment_instruction = t_lang('M_TXT_SHOW_ORDER_PAYMENT_PENDING_INSTRUCTION');
                                } else if ($row['order_payment_status'] == '-1') {
                                    echo '<span class="label label-danger">' . t_lang('M_TXT_ORDER_CANCELLED') . '</span>';
                                    $show_order_payment_instruction = t_lang('M_TXT_ORDER_CANCELLED_PAYMENT_FAILURE');
                                } else if ($row['order_payment_status'] == '2') {
                                    echo '<span class="label label-primary">' . t_lang('M_TXT_REFUNDED') . '</span>';
                                }
                                ?> 
                                <?php
                                if ($row['order_payment_status'] == '1') {
                                    if ($row['deal_type'] == 0) {
                                        if (($row['od_qty'] + $row['od_gift_qty']) > 1) {
                                            echo '<a class="linknormal right hide__mobile" href="' . CONF_WEBROOT_URL . 'print-voucher.php?id=' . $row['order_id'] . $voucher . '" target="_blank">' . t_lang('M_TXT_PRINT') . '</a>';
                                        } else {
                                            echo '<a class="linknormal right hide__mobile" href="' . CONF_WEBROOT_URL . 'print-voucher.php?id=' . $row['order_id'] . $voucher . '" target="_blank">' . t_lang('M_TXT_PRINT') . '</a>';
                                        }
                                    }
                                }
                                ?>    
                            </div>
                            <div class="orderblock__body">
                                <table>
                                    <tr>
                                        <td class="first">
                                            <div class="item">
                                                <div class="item__head">
                                                    <?php
                                                    $dealUrl = friendlyUrl(CONF_WEBROOT_URL . 'deal.php?deal=' . $row['od_deal_id'] . '&type=main');
                                                    echo '<a href="' . $dealUrl . '"><img  alt="" src="' . CONF_WEBROOT_URL . 'deal-image.php?id=' . $row['od_deal_id'] . '&type=voucherImages"></a>';
                                                    ?> 
                                                </div>
                                                <div class="item__body">
                                                    <span class="item__title">
                                                        <?php
                                                        $dot = "..";
                                                        $sub_deal_name = '';
                                                        if ($row['od_sub_deal_name'] != "") {
                                                            $sub_deal_name = $row['od_sub_deal_name'];
                                                        }
                                                        $name = (strlen($row['od_deal_name']) > 30) ? substr($row['od_deal_name'], 0, 30) . $dot : $row['od_deal_name'];
                                                        echo '<a href="' . $dealUrl . '" title="' . appendPlainText($row['od_deal_name']) . '">' . appendPlainText($name) . ' </a>';
                                                        ?>
                                                    </span>
                                                    <?php
                                                    if ($row['od_sub_deal_name'] != "") {
                                                        echo '<div class="item__title">';
                                                        echo t_lang('M_TXT_SUBDEAL_NAME') . ' : ' . substr($sub_deal_name, 0, 40);
                                                        echo '</div>';
                                                    }
                                                    if ($row['order_payment_status'] == '1') {
                                                        if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 1 && $row['dpe_product_file_name'] != "") {
                                                            echo '<a href="' . CONF_WEBROOT_URL . 'download-digital-product.php?product_id=' . $row['deal_id'] . ' & id=' . $row['order_id'] . $voucher . '" target="_blank" class=" themebtn themebtn--xsmall themebtn--org">' . t_lang('M_TXT_DOWNLOAD') . '</a>';
                                                        }
                                                        if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 1 && $row['dpe_product_external_url'] != "") {
                                                            echo '&nbsp;<a href="' . addhttp($row['dpe_product_external_url']) . '" target="_blank" class=" themebtn themebtn--xsmall themebtn--org">' . t_lang('M_TXT_EXTERNAL_DOWNLOAD') . '</a>';
                                                        }
                                                    }
                                                    ?>
                                                    <p><strong>
                                                            <?php echo t_lang('M_TXT_PAYMENT'); ?>:</strong> <?php
                                                        if ($row['order_payment_mode'] == '1') {
                                                            echo t_lang('M_TXT_PAYPAL');
                                                        } else if ($row['order_payment_mode'] == '2') {
                                                            echo t_lang('M_TXT_CREDITCARD');
                                                        } else if ($row['order_payment_mode'] == '3') {
                                                            echo t_lang('M_TXT_WALLET');
                                                        } else if ($row['order_payment_mode'] == '4') {
                                                            echo t_lang('M_TXT_CIM');
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?></p>
                                                    <p><strong><?php echo t_lang('M_TXT_ORDER_DATE'); ?>: </strong><?php echo $row['order_date'] ?></p>
                                                    <?php
                                                    if ($row['deal_type'] == 0 && $row['order_payment_status'] != '-1') {
                                                        $date = displayDate($row['deal_tipped_at'], true, true, $_SESSION['logged_user']['user_timezone']);
                                                        if ($date == '') {
                                                            echo '<p><strong>' . t_lang('M_TXT_TIPPED') . ': </strong>' . t_lang('M_TXT_DEAL_IS_NOT_TIPPED_YET') . '</p>';
                                                        } else {
                                                            echo '<p><strong>' . t_lang('M_TXT_TIPPED_AT') . ': </strong>' . $date . '</p>';
                                                        }
                                                        ?>
                                                    <?php } ?>
                                                    <?php
                                                    $order_options = get_order_option(array('od_id' => $row['od_id']));
                                                    if (is_array($order_options) && count($order_options) && $order_options != false) {
                                                        ?>
                                                        <p><strong><?php echo t_lang('M_TXT_OPTIONS'); ?>:</strong> 
                                                            <?php
                                                            $str = "";
                                                            echo '<div style="font-size:14px;">';
                                                            foreach ($order_options as $op) {
                                                                $str .= '- ' . $op['oo_option_name'] . ': ' . $op['oo_option_value'] . '|';
                                                            }
                                                            echo rtrim($str, '|');
                                                            echo '</div>';
                                                            echo '</p>';
                                                        }
                                                        ?>
                                                    <p><strong><?php echo t_lang('M_TXT_QUANTITY'); ?>:</strong> 1 </p>
                                                    <?php //if($row['order_payment_status'] == '-1'){  ?>
                                                    <p><?php echo $show_order_payment_instruction; ?> </p>
                                                    <?php //}  ?>
                                                </div>    
                                            </div>
                                        </td>
                                        <td>
                                            <div class="item__price">
                                                <span class="item__price_standard"><?php echo amount($row['od_deal_price']); ?></span>
                                            </div>
                                            <span class="gap"></span>
                                            <?php
                                            if ($row['order_payment_status'] == '1') {
                                                if ($row['deal_type'] == 0) {
                                                    if (($row['od_qty'] + $row['od_gift_qty']) > 1) {
                                                        echo '<a class="linknormal desktop__hide" href="' . CONF_WEBROOT_URL . 'print-voucher.php?id=' . $row['order_id'] . $voucher . '" target="_blank">' . t_lang('M_TXT_PRINT') . '</a>';
                                                    } else {
                                                        echo '<a class="linknormal desktop__hide" href="' . CONF_WEBROOT_URL . 'print-voucher.php?id=' . $row['order_id'] . $voucher . '" target="_blank">' . t_lang('M_TXT_PRINT') . '</a>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btngroup">
                                                <?php if (strlen($row['shipping_details']) > 5 && $row['deal_type'] == 1 && $row['deal_sub_type'] == 0) { ?>
                                                    <a class="themebtn themebtn--small themebtn--org info__addreses-link<?php echo $row['order_id'] . $voucher; ?>" onclick= "showShipping('<?php echo $row['order_id'] . $voucher; ?>');" href="javascript:void(0)"><?php echo t_lang('M_TXT_VIEW_SHIPPING_ADDRESS'); ?></a>
                                                <?php } if ($row['deal_type'] == 1 && $row['deal_sub_type'] == 0) { ?>
                                                    <a class="themebtn themebtn--small info__addreses-link_<?php echo $row['order_id'] . $voucher; ?>" onclick= "deliveryDetail('<?php echo $row['order_id'] . $voucher; ?>');" href="javascript:void(0)"> <?php echo t_lang('M_TXT_VIEW_DELIVERY_DETAILS'); ?></a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div style="display:none;" class="row__information info__addreses<?php echo $row['order_id'] . $voucher; ?>">
                            <a class="link__close info__addreses-link" href="javascript:void(0)" onclick= "hideShipping('<?php echo $row['order_id'] . $voucher; ?>');" ></a>
                            <?php echo nl2br($row['shipping_details']); ?>
                        </div>
                        <div style="display:none;" class="row__information info__addreses1_<?php echo $row['order_id'] . $voucher; ?>">
                            <?php
                            $status = '<b>' . t_lang('M_TXT_SHIPPING_STATUS') . ':</b> ' . $row['shipping_status'] . '<br/>';
                            if ($row['cm_shipping_date'] != '0000-00-00' && $row['cm_shipping_date'] != '1970-01-01') {
                                $status .= '<b>' . t_lang('M_TXT_SHIPPED_DATE') . ':</b> ' . date('d-M-Y', strtotime($row['cm_shipping_date'])) . '<br/>';
                            }
                            if ($row['cm_shipping_details'] != '') {
                                $status .= '<b>' . t_lang('M_TXT_SHIPPING_INFO') . ':</b> <br/>' . nl2br($row['cm_shipping_details']);
                            }
                            echo $status;
                            ?>
                        </div>
                        <?php
                    } if ($total_records == 0) {
                        /* $msg->addError(t_lang("M_TXT_NO_RECORD_FOUND")); */
                        ?>
                        <div class="col-md-12">
                            <div class="block__empty">
                                <h6><?php echo t_lang("M_TXT_NO_RECORD_FOUND"); ?> </h6>
                            </div>
                        </div>
                    <?php } ?>
                </div>    
                <?php
                echo createHiddenFormFromPost('frmPaging', '', array('page', 'purchase', 'type'), array('page' => '', 'purchase' => $purchase, 'type' => $type));
                if ($total_records > 0) {
                    $pages = $srch->pages();
                    $total_records = $srch->recordCount();
                    require_once CONF_VIEW_PATH . 'pagination.php';
                }
                ?>
            </div>
        </div>
    </div>
</section>
<!--bodyContainer end here-->		  
<script type="text/javascript">
    $(document).ready(function () {
        $('.sortlink').click(function () {
            $('.sortdrop').slideToggle();
            return false;
        });
        $('html').click(function () {
            $('.sortdrop').slideUp('slow');
        });
    });
    function getValue(type, obj) {
        val = $(obj).find("option:selected").val();
        console.log(type);
        console.log(val);
        $('input[name=' + type + ']').val(val);
        $('#frmPaging').submit();
    }
    /* for address information */
    $('.info__addreses-link').click(function () {
        $(this).toggleClass("active");
        $('.info__addreses').slideToggle("600");
    });
    $('.info__addreses-link1').click(function () {
        $(this).toggleClass("active");
        $('.info__addreses1').slideToggle("600");
    });
    function showShipping(id)
    {
        $('.info__addreses-link' + id).toggleClass("active");
        $('.info__addreses' + id).slideToggle("600");
    }
    function hideShipping(id)
    {
        $('.info__addreses-link' + id).toggleClass("active");
        $('.info__addreses' + id).slideToggle("600");
    }
    function deliveryDetail(id)
    {
        $('.info__addreses-link_' + id).toggleClass("active");
        $('.info__addreses1_' + id).slideToggle("600");
    }
</script>
<?php require_once './footer.php'; ?>