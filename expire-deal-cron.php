<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
$day = "INTERVAL " . CONF_DEALS_EXPIRATION_NOTICE . " DAY";
$query = "Select o.order_id,od.od_deal_id,u.user_id,d.deal_name,u.user_name,u.user_email from  tbl_orders AS o INNER JOIN  tbl_order_deals as od INNER JOIN tbl_users as u INNER JOIN tbl_deals as d where o.order_id=od.od_order_id and o.order_user_id = u.user_id and od.od_deal_id=d.deal_id and deal_status=1 and DATE_SUB(deal_end_time,$day) = '" . date("Y-m-d H:i:s") . "'";
$rs = $db->query($query);
while ($row = $db->fetch($rs)) {
    $user_id = intval($row['user_id']);
    $deal_id = intval($row['od_deal_id']);
    $checkPer = $db->query("select * from tbl_email_notification where en_user_id=" . $user_id);
    $row_per = $db->fetch($checkPer);
    if ($row_per['en_near_to_expired'] == 1) {
        $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=46");
        $row_tpl = $db->fetch($rs_tpl);
        $days = 'day';
        if (CONF_DEALS_EXPIRATION_NOTICE > 1) {
            $days = CONF_DEALS_EXPIRATION_NOTICE . " days";
        } elseif (CONF_DEALS_EXPIRATION_NOTICE == 1) {
            $days = CONF_DEALS_EXPIRATION_NOTICE . " day";
        }
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = [
            'xxuser_namexx' => $row['user_name'],
            'xxsomedaysxx' => $days,
            'xxdeal_namexx' => $row['deal_name'],
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxwebrooturlxx' => CONF_WEBROOT_URL
        ];
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        $checkRow = $db->query("select * from tbl_deal_expire_notification where den_deal_id=$deal_id and den_user_id=$user_id");
        if ($db->total_records($checkRow) == 0) {
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($row['user_email'], $subject, emailTemplate($message), $headers);
            }
            echo $row['user_email'];
            echo emailTemplate($message);
        }
    }
}