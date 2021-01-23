<?php

require_once './application-top.php';
require_once './includes/navigation-functions.php';
$qry1 = "update tbl_deals set deal_status=0 where deal_status<3 and deal_start_time > '" . date("Y-m-d H:i:s") . "'";
$db->query($qry1);
$rsc = $db->query("SELECT d.deal_id, d.deal_name, d.deal_subtitle, d.deal_desc, c.company_fanpage_id, c.company_fb_access_token 
                    FROM `tbl_deals` d 
                    INNER JOIN `tbl_companies` c
                    ON c.company_id = d.deal_company
                    WHERE deal_status=1 AND deal_fb_post=0 AND company_fanpage_id != '' AND company_fb_access_token != '' AND DATE_ADD(company_fb_token_updated_on, INTERVAL 60 DAY) >= NOW()");
while ($deal_row = $db->fetch($rsc)) {
    $company_fanpage_id = $deal_row['company_fanpage_id'];
    if (strlen(CONF_FACEBOOK_API_KEY) > 1 && strlen(CONF_FACEBOOK_SECRET_KEY) > 1 && $company_fanpage_id != '' && strlen($deal_row['company_fb_access_token']) > 1) {
        require_once './facebook-php-sdk/autoload.php';
        echo "Post on wall";
        // instance
        $fb = new Facebook\Facebook([
            'app_id' => CONF_FACEBOOK_API_KEY,
            'app_secret' => CONF_FACEBOOK_SECRET_KEY,
            'default_graph_version' => 'v2.4',
        ]);
        try {
            $message = 'Check out great deal !!' . ' ' . $deal_row['deal_name'];
            $dealUrl = 'http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'deal.php?deal=' . $deal_row['deal_id'] . '&type=main';
            $imageUrl = 'http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'deal-image.php?id=' . $deal_row['deal_id'] . '&type=main';
            $access_token = $deal_row['company_fb_access_token'];
            $linkData = array(
                'message' => stripslashes($message),
                'name' => stripslashes($deal_row['deal_name']),
                'link' => $dealUrl,
                'picture' => $imageUrl,
                'caption' => stripslashes($deal_row['deal_subtitle']),
                'description' => stripslashes($deal_row['deal_desc']),
            );
            $response = $fb->post("/$company_fanpage_id/feed", $linkData, $access_token);
            $post = $response->getDecodedBody();
            if (isset($post['id'])) {
                echo '\"' . $deal_row['deal_name'] . '\" posted on FB fanpage<br />';
                $qry4 = "update tbl_deals set deal_fb_post=1 where deal_id=" . intval($deal_row['deal_id']);
                $db->query($qry4);
            }
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
}
$qry2 = "update tbl_deals set deal_status=1 where deal_status<3 and deal_start_time <= '" . date("Y-m-d H:i:s") . "' and deal_end_time > '" . date("Y-m-d H:i") . "'";
$db->query($qry2);
$qry3 = "update tbl_deals set deal_status=2 where deal_status<3 and deal_end_time <= '" . date("Y-m-d H:i:s") . "'";
$db->query($qry3);
$rs = $db->query("Select o.order_id,od.od_deal_id,u.user_id,d.deal_name,u.user_name,u.user_email from  tbl_orders AS o INNER JOIN  tbl_order_deals as od INNER JOIN tbl_users as u INNER JOIN tbl_deals as d where o.order_id=od.od_order_id and o.order_user_id = u.user_id and od.od_deal_id=d.deal_id and deal_status=2");
while ($row = $db->fetch($rs)) {
    $user_id = intval($row['user_id']);
    $deal_id = intval($row['od_deal_id']);
    $checkPer = $db->query("select * from tbl_email_notification where en_user_id=" . $user_id);
    $row_per = $db->fetch($checkPer);
    if ($row_per['en_near_to_expired'] == 1) {
        $rs_tpl = $db->query("select * from tbl_email_templates where tpl_id=42");
        $row_tpl = $db->fetch($rs_tpl);
        /* Notify User */
        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
        $arr_replacements = array(
            'xxuser_namexx' => $row['user_name'],
            'xxdeal_namexx' => $row['deal_name'],
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxsite_namexx' => CONF_SITE_NAME,
            'xxwebrooturlxx' => CONF_WEBROOT_URL
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $message = str_replace($key, $val, $message);
        }
        $checkRow = $db->query("select * from tbl_deal_expire_notification where den_deal_id=$deal_id and den_user_id=$user_id");
        if ($db->total_records($checkRow) == 0) {
            if ($row_tpl['tpl_status'] == 1) {
                sendMail($row['user_email'], $subject, emailTemplate($message), $headers);
            }
            echo emailTemplate($message);
        }
    }
    $db->query("INSERT IGNORE INTO tbl_deal_expire_notification (den_user_id,den_deal_id,den_status) VALUES ('$user_id','$deal_id', '1');");
}