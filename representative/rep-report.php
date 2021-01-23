<?php
require_once '../application-top.php';

if (!isRepresentativeUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');
}
$rep_id = $_SESSION['logged_user']['rep_id'];
$srch =new SearchBase('tbl_representative', 'r');
    $srch->addCondition('rep_id', '=', $rep_id);
    $srch->addFld(array('r.rep_commission'));
    $rs_listing=$srch->getResultSet();
    $rep=$db->fetch($rs_listing);


/** get total referral commission and total affiliate commission * */
$srch = new SearchBase('tbl_companies', 'c');
/* $srch->joinTable('tbl_deals', 'INNER JOIN', 'c.company_id=d.deal_company', 'd'); */
$srch->addCondition('c.company_rep_id', '=', $rep_id);
$srch->addCondition('c.company_active', '=', 1);
$srch->addCondition('c.company_deleted', '=', 0);
$srch->addGroupBy('c.company_id');
$wallet_data = $srch->getResultSet();
$total_records = $db->total_records($wallet_data);
require_once './header.php';
$arr_bread = array(
    'my-account.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'rep-report.php' => t_lang('M_TXT_REPRESENTATIVE'),
    'javascript:void(0)' => t_lang('M_TXT_COMMISSION_EARNINGS')
);
?>

</div></td>

<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_REPRESENTATIVE'); ?> </div>

    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
    ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                    return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) {
        ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                    <?php
    }
    if (isset($_SESSION['msgs'][0])) {
        ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php
    } ?>
            </div>
        </div>
    <?php
} ?>

    <table class="tbl_data" width="100%">
        <thead>
            <tr>
                <th width='25%'><?php echo t_lang('M_TXT_COMPANY'); ?></th>

                <th width='*'><?php echo t_lang('M_TXT_SALES'); ?></th>
					<th width='*'><?php echo t_lang('M_TXT_COMMISSION_EARNINGS');?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_records != 0) {
                $total_commission = 0;
                $totalAmount = 0;

                while ($row = $db->fetch($wallet_data)) {
                    echo '<tr>';
                    echo '<td><strong>' . $row['company_name'] . '</strong></td>';


                    $srch = new SearchBase('tbl_companies', 'c');
                    $srch->addCondition('c.company_id', '=', $row['company_id']);
                    $srch->joinTable('tbl_deals', 'INNER JOIN', 'c.company_id=d.deal_company', 'd');
                    $srch->addMultipleFields(array('c.company_id', 'd.deal_id'));
                    $repDeal = $srch->getResultSet();
                    $company = array();

                    $deal = array();
                    while ($row1 = $db->fetch($repDeal)) {
                        $company[] = $row1['company_id'];
                        $deal[] = $row1['deal_id'];
                    }
                    echo '<td>';

                    if ($db->total_records($repDeal) > 0) {
                        $srch = new SearchBase('tbl_orders', 'o');
                        $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id ', 'od');
                        $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
                        /* $srch->addCondition('d.deal_tipped_at', '!=','0000-00-00 00:00:00'); */
                        $srch->addCondition('o.order_payment_status', '!=', 0);
                        $srch->addCondition('od.od_deal_id', 'IN', $deal);
                        $srch->addMultipleFields(array('d.deal_company', 'od.*', 'o.*', "SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"));
                        //	echo $srch->getQuery();
                        $data = $srch->getResultSet();
                        $amountRow = $db->fetch($data);

                        if ($db->total_records($data) > 0) {
                            $totalAmount +=$amountRow['totalAmount'];
                            // echo '<a href="sales.php?deal_company=' . $amountRow['deal_company'] . '" >' . CONF_CURRENCY . number_format($amountRow['totalAmount'], 2) . CONF_CURRENCY_RIGHT . '</a>';
                            echo CONF_CURRENCY . number_format($amountRow['totalAmount'], 2) . CONF_CURRENCY_RIGHT;
                        }
                    }
                    echo '</td>';

                    echo '<td>&nbsp;';
                    if (!empty($deal)) {
                        $srch = new SearchBase('tbl_orders', 'o');
                        $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id ', 'od');

                        $srch->joinTable('tbl_coupon_mark', 'LEFT JOIN', 'cm.cm_order_id=o.order_id AND od.od_deal_id=cm.cm_deal_id', 'cm');
                        $srch->addCondition('o.order_payment_status', '!=', 0);
                        $srch->addCondition('od.od_deal_id', 'IN', $deal);
                        $srch->addCondition('cm.cm_deal_id', 'IN', $deal);
                        $srch->addFld("SUM(CASE WHEN o.order_payment_status!=0 AND cm.cm_status=1  THEN od_deal_price ELSE 0 END) AS redeemsaleAmount");

                        $data = $srch->getResultSet();
                        $amountRow = $db->fetch($data);
                        $redeemAmount=$amountRow['redeemsaleAmount'];
                        if ($redeemAmount>0) {
                            $rep_commission= $redeemAmount*$rep['rep_commission']/100;
                            echo CONF_CURRENCY . $rep_commission . CONF_CURRENCY_RIGHT;
                            $total_rep_commission+=$rep_commission;
                        } else {
                            echo CONF_CURRENCY . '0.00' . CONF_CURRENCY_RIGHT;
                        }
                    } else {
                        echo CONF_CURRENCY . '0.00' . CONF_CURRENCY_RIGHT;
                    }
                    echo '</td>';
                    echo '</tr>';
                }

                /*  echo '<tr>
            <td><strong>' . t_lang('M_TXT_TOTAL_SALES') . '</strong></td><td><a href="sales.php" >' . CONF_CURRENCY . number_format($totalAmount, 2) . CONF_CURRENCY_RIGHT . '</a></td><td>'.CONF_CURRENCY .$total_rep_commission.CONF_CURRENCY_RIGHT.'</td></tr>'; */

                echo '<tr>
			<td><strong>' . t_lang('M_TXT_TOTAL_SALES') . '</strong></td><td>' . CONF_CURRENCY . number_format($totalAmount, 2) . CONF_CURRENCY_RIGHT . '</td><td>'.CONF_CURRENCY .$total_rep_commission.CONF_CURRENCY_RIGHT.'</td></tr>';
            } else {
                echo '<tr><td colspan="2">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>

        </tbody>
    </table>

</td>
<?php require_once './footer.php'; ?>
