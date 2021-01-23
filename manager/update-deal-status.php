<?php

require_once '../application-top.php';
$qry1 = "update tbl_deals set deal_status=0 where deal_status<3 and deal_start_time > '" . date("Y-m-d H:i") . "'";
$db->query($qry1);
$qry2 = "update tbl_deals set deal_status=1 where deal_status<3 and deal_start_time <= '" . date("Y-m-d H:i") . "' and deal_end_time > '" . date("Y-m-d H:i") . "'";
$db->query($qry2);
/* echo $qry2;  */
$qry3 = "update tbl_deals set deal_status=2 where deal_status<3 and deal_end_time <= '" . date("Y-m-d H:i") . "'";
$db->query($qry3);
