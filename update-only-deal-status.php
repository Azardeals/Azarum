<?php

require_once './application-top.php';
$whr = ['smt' => 'deal_status<3 AND deal_start_time > ?', 'vals' => [date("Y-m-d H:i:s")], 'execute_mysql_functions' => false];
$db->update_from_array('tbl_deals', ['deal_status' => 0], $whr);
$qry2 = "update tbl_deals set deal_status=1 where deal_status<3 and deal_start_time <= '" . date("Y-m-d H:i:s") . "' and deal_end_time > '" . date("Y-m-d H:i:s") . "'";
$db->query($qry2);
$whr = ['smt' => 'deal_status<3 AND deal_end_time <= ?', 'vals' => [date("Y-m-d H:i:s")], 'execute_mysql_functions' => false];
$db->update_from_array('tbl_deals', ['deal_status' => 2], $whr);
