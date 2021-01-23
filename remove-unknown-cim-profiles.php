<?php
require_once './application-top.php';
require_once './cim-xml/vars.php';
require_once './cim-xml/util.php';
$request_content = '<?xml version="1.0" encoding="utf-8"?>
<getCustomerProfileIdsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">' . MerchantAuthenticationBlock() . '</getCustomerProfileIdsRequest>';
$response = send_xml_request($request_content);
$parsedresponse = parse_api_response($response);
echo "<pre>";
print_r($parsedresponse);
if (!$parsedresponse->messages->resultCode == 'Ok') {
    echo $parsedresponse->messages->message->code . ' : ' . $parsedresponse->messages->message->text;
    exit();
}
$profile_ids = (array) $parsedresponse->ids;
$profile_ids = $profile_ids['numericString'];
//print_r($profile_ids);
$rs = $db->query('select user_customer_profile_id, user_email, user_id from tbl_users where user_customer_profile_id > 0');
$total = $db->total_records($rs);
$i = 0;
$details = '<table width="80%" align="center" cellspacing="0" cellpadding="0">
			<tr><td> User ID </td><td> User Email </td><td> User CIM profile ID </td></tr>';
$forLog = "User Id \t\t\t User Email \t\t\t User CIM profile ID \n\r";
if ($total <= 0) {
    echo 'No profile found in local system.';
} else {
    while ($row = $db->fetch($rs)) {
        if (!in_array($row['user_customer_profile_id'], $profile_ids, true)) {
            if ($db->query('update tbl_users set user_customer_profile_id = 0 where user_customer_profile_id=' . $row['user_customer_profile_id'])) {
                $db->query("DELETE FROM tbl_users_card_detail WHERE ucd_user_id=" . intval($row['user_id']));
                $forLog .= $row['user_id'] . " \t\t\t " . $row['user_email'] . " \t\t\t " . $row['user_customer_profile_id'] . " \n\r";
                $details .= '<tr><td>' . $row['user_id'] . '</td><td>' . $row['user_email'] . '</td><td>' . $row['user_customer_profile_id'] . '</td></tr>';
            }
            $i++;
        }
    }
}
$details .= '</table>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <?php
        $log = '';
        $log .= 'Operation Time: ' . date('Y-M-d H:i:s e T : O U') . "\n\r";
        $log .= "Total CIM profiles associated with system before operation: " . $total . "\n\r";
        $log .= "Total CIM profiles associated with merchant account: " . count($profile_ids) . "\n\r";
        $log .= "Deleted CIM profiles associated with system count: " . $i . "\n\r";
        $log .= "Total CIM profiles associated with system after operation: " . ($total - $i) . "\n\r";
        if ($i > 0) {
            $log .= "Deleted CIM profiles:\n\r";
            echo $details = nl2br($log) . $details;
            $log = $log . $forLog;
        } else {
            echo nl2br($log);
        }
        $log_f = fopen('logs/logCIMDeletedProfileIDs.txt', 'a');
        fwrite($log_f, $log);
        fclose($log_f);
        ?>
    </body>
</html>