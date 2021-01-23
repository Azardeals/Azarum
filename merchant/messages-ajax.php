<?php

require_once '../application-top.php';
require_once '../site-classes/merchant-support.cls.php';
$post = getPostedData();
$ticket_id = $post['ticket'];
$merchant_support = new merchantSupport();
switch (strtoupper($post['mode'])) {
    case 'ARCHIVE':
        $merchant_support->archiveTicket($ticket_id);
        break;
    case 'UNARCHIVE':
        $merchant_support->unarchiveTicket($ticket_id);
        break;
    case 'MARKASREAD':
        $merchant_support->markMessageAsViewed($ticket_id);
        $msg_count = getNewMessagesCount();
        break;
}
