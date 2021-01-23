<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
$order_id = $_GET['order_id'];
$srch = new SearchBase('tbl_order_deals', 'od');
$srch->joinTable('tbl_orders', 'INNER JOIN', 'o.`order_id` = od.`od_order_id`', 'o');
$srch->joinTable('tbl_deals', 'INNER JOIN', 'd.`deal_id` = od.`od_deal_id`', 'd');
$srch->addCondition('od.od_order_id', '=', $order_id);
$srch->doNotCalculateRecords();
$srch->setPageSize(1);
$srch->setPageNumber(1);
$srch->addMultipleFields(array('order_id', 'order_payment_mode', 'order_payment_status', 'od_deal_name', 'd.deal_type', 'd.deal_sub_type', 'd.deal_id'));
$order_details_rs = $srch->getResultSet();
/* $vouchers = []; */
$s_odr_row = [];
while ($row = $db->fetch($order_details_rs)) {
    $deal_name = $row['od_deal_name'];
    $s_odr_row = $row;
}
if (empty($s_odr_row)) {
    redirectUser((CONF_WEBROOT_URL . 'api/cart-payment.php?show=payment'));
}
$sql = $db->query("select * from tbl_email_templates where tpl_id=29");
$email_data = $db->fetch($sql);
$subject = $email_data['tpl_subject'];
$email_msg = t_lang('M_TXT_HELLO_FRIENDS') . ",\n\n " . t_lang('M_TXT_BOUGHT_DEAL_FROM') . " " . CONF_SITE_NAME . " " . t_lang('M_TXT_YOU_MAY_BE_INTERESTED') . " \n\n" . t_lang('M_TXT_ENJOY_THE_DEAL') . "\n\n" . 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'];
$email_msg1 = ' 
      <tr>
        
        <td align="left" style="background:#fff; padding:15px; font-size:13px; color:#616161; line-height:18px; font-family:Arial;">
            <table>
                <tr>
                    <td><tr>
        
        <td align="left" style="font-size:13px; color:#616161; line-height:18px; font-family:Arial; padding:0 0 0 0;">' . t_lang('M_TXT_YOUR_FRIEND_HAS_REFERRED_LINK') . '</td>
        
      </tr>
	  
	  <tr>
        
        <td align="left" style="font-size:13px; color:#616161; line-height:18px; font-family:Arial; padding:10px 0 10px 0;">
        ' . $_POST['email_message'] . '</td>
       
      </tr>
      
      
      <tr>
       
        <td align="left" style="font-size:13px; color:#616161; line-height:18px; font-family:Arial; padding:0 0 0 0;"> &nbsp;</td>
       
      </tr> <tr>
       
        <td align="left" style="font-size:13px; color:#616161; line-height:18px; font-family:Arial; padding:0 0 0 0;"> ' . t_lang('M_TXT_THANKYOU_FOR_SUPPORT') . '</td>
        
      </tr></td>
                </tr>
            </table>
        </td>
        
      </tr>
     <tr>
                      <td style="padding:10px; background:#fff; border-top:1px solid #ddd;">
                      		<table width="100%" border="0">
                              
                                <tbody><tr>
                                  <td valign="top" style="font-size:14px; font-weight:bold; color:#313131; font-family:Arial; padding:0 0 5px 0;">Thanks<br>
          The xxsite_namexx Team<br>
  <a href="xxsite_urlxx" style="color:#d71732; text-decoration:none;">xxsite_urlxx</a></td>
                                </tr>
                                
                            </tbody></table>
						</td>
                    </tr>';
$arr_replacements = array(
    'xxsite_namexx' => CONF_SITE_NAME,
    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
    'xxwebrooturlxx' => CONF_WEBROOT_URL,
    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
);
foreach ($arr_replacements as $key => $val) {
    $email_msg1 = str_replace($key, $val, $email_msg1);
}
$disable_button = '';
/* CODE FOR SENDING THE EMAILS TO THE USERS START HERE */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['deal_name'] != "") {
    if ($_POST['recipients'] != '' && $_POST['email_message']) {
        $recipients = $_POST['recipients'];
        $recipients = str_replace(' ', '', $recipients);
        $recipients_arr = explode(',', $recipients);
        $error = 0;
        foreach ($recipients_arr as $val) {
            if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $val)) {
                $error = 1;
            }
        }
        $subject = $_POST['email_subject'];
        $email_msg1 = emailTemplate(($email_msg1));
        if ($error != 1) {
            foreach ($recipients_arr as $val) {
                sendMail($val, $subject, $email_msg1, $headers);
            }
            $disable_button = "disabled";
        } else {
            $msg->addError(t_lang('M_ERROR_EMAIL_ADDRESSES_NOT_VALID'));
        }
    } else {
        #$msg->addError(t_lang('M_ERROR_ENTER_EMAIL_ADDRESS_AND_MESSAGE'));
    }
}
/* CODE FOR SENDING THE EMAILS TO THE USERS END HERE */
require_once './js-and-css.inc.php';
if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
    ?> 
    <div  id="msg">
        <div class="system-notice notice"><a class="close" href="javascript:void(0);" onclick="$(this).closest('#msg').hide();
                    return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a><p id="message"><?php echo $msg->display(); ?> </p></div>
    </div>
<?php } ?>
<!--bodyContainer start here-->
<div id="body">
    <section class="pagebar center">
        <div class="fixed_container">
            <div class="row">
                <aside class="col-md-12">
                    <h3><?php echo t_lang('M_TXT_CONGRATULATIONS'); ?></h3>
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
                           <!-- <h3><?php echo unescape_attr(t_lang('M_TXT_NOW_SHARE_IT')); ?></h3>-->
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
                            <h3><?php echo t_lang('M_TXT_ORDER_ID'); ?>: <strong><?php echo $order_id; ?> </strong></h3>
                            <p><strong><?php echo CONF_SITE_NAME; ?></strong>
                                <?php echo ' ' . t_lang('M_TXT_OFFER_REFERAL_COMMISSION_SUCCESS_PAGE'); ?></p>
                            <?php
                            if ($s_odr_row['deal_type'] == 1 && $s_odr_row['deal_sub_type'] == 1) {
                                require_once './site-classes/digital-product.cls.php';
                                $dg = new DigitalProduct();
                                $dgProduct_data = $dg->getDigitalProductRecord($s_odr_row['deal_id']);
                                if ($dgProduct_data && (($dgProduct_data['dpe_product_file'] != "" && $dgProduct_data['dpe_product_external_url'] != ""))) {
                                    echo "<h4><a href='" . CONF_WEBROOT_URL . "my-deals'>" . t_lang('M_TXT_SEND_DIGITAL_MSG_FOR_VOUCHER_PAGE') . "</a></h4>";
                                } else if ($dgProduct_data && ($dgProduct_data['dpe_product_file'] == "" && $dgProduct_data['dpe_product_external_url'] != "")) {
                                    echo "<h4>" . t_lang('M_TXT_SEND_DIGITAL_MSG') . "</h4>";
                                } else if ($dgProduct_data && ($dgProduct_data['dpe_product_file'] != "" && $dgProduct_data['dpe_product_external_url'] == "")) {
                                    echo "<h4><a href='" . CONF_WEBROOT_URL . "my-deals'>" . t_lang('M_TXT_SEND_DIGITAL_MSG_FOR_VOUCHER_PAGE') . "</a></h4>";
                                } else if (!$dgProduct_data || ($dgProduct_data['dpe_product_file'] == "" && $dgProduct_data['dpe_product_external_url'] == "")) {
                                    echo "<h4>" . t_lang('M_TXT_SEND_DIGITAL_MSG') . "</h4>";
                                }
                            }
                            ?>
                        </div> 
                    </div>
                </div>
            </div>    
        </div>    
    </section>   
</div>

