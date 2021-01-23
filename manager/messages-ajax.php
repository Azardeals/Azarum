<?php

require_once './application-top.php';
require_once '../site-classes/merchant-support.cls.php';
require_once './admin-info.cls.php';

function fetchMessageHtml($status, $page, $keyword = "")
{
    $admin_info = new adminInfo();
    $merchant_support = new merchantSupport();
    $tickets_arr = $merchant_support->getTickets($status, $page, $keyword);
    $tickets = $tickets_arr['tickets'];
    $total_pages = $tickets_arr['total_pages'];
    $total_records = $tickets_arr['total_records'];
    $pagesize = $tickets_arr['page_size'];
    $pagestring = '';
    $pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'keyword'], ['page' => '', 'status' => $status, 'keyword' => $keyword]);
    $pagestring .= '<div class="pagination "><ul>';
    $pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
            ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $total_records) ? $total_records : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $total_records . '</a>';
    $pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                    , $total_pages, $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
    $pagestring .= '</div>';
    if ($total_records > $pagesize) {
        $str = '	<div class="footinfo">
		<aside class="grid_1">' . $pagestring . '</aside>  
		<aside class="grid_2"><span class="info">' . $pageStringContent . '</span></aside>
	</div>';
    }
    $msgList = '<ul class="medialist">';
    foreach ($tickets as $ele) {
        $bgcolor = $admin_info->backgroundColor(ucfirst(substr($ele['company_name'], 0, 1)));
        $com_ltr = ucfirst(substr($ele['company_name'], 0, 1));
        $cls1 = "";
        if ($status == 3 || $ele['ticket_viewed'] == '1') {
            $cls1 = 'class="read"';
        }
        $msgList .= '<li ' . $cls1 . '><span class="grid first"><figure class="avtar bgm-' . $bgcolor . '">' . $com_ltr . '</figure>';
        if (checkAdminAddEditDeletePermission(13, '', 'edit')) {
            $msgList .= '<label class="checkbox"><input type="checkbox" value="' . $ele['ticket_id'] . '" name="message_ids[]"><i class="input-helper"></i></label>';
        }
        $msgList .= '</span>';
        $msgList .= '<div class="grid second">
			<div class="desc">
				<span class="name">
				<a href="message-details.php?status=' . $status . '&tid=' . $ele['ticket_id'] . '&mid=' . $ele['ticket_created_by'] . '">';
        $cls = '';
        if ($ele['ticket_viewed'] == 0) {
            $cls = 'class="unread_message"';
        }
        $msgList .= '<span ' . $cls . '>' . htmlentities($ele['ticket_title'], ENT_QUOTES, 'UTF-8') . '</span>';
        if ($ele['unread_messages'] > 0) {
            $msgList .= ' <span class="msg_flag">' . $ele['unread_messages'] . '</span>';
        }
        $msgList .= '</a> <span class="lightxt"><span><</span>' . $ele['company_email'] . '<span>></span></span></span>';
        $msgList .= '<div class="descbody">' . (nl2br($ele['ticket_description']));
        $msgList .= '</br>';
        $company_address = $ele['company_address1'];
        if ($ele['company_address2'] != '')
            $company_address .= ', ' . $ele['company_address2'];
        if ($ele['company_address3'] != '')
            $company_address .= ', ' . $ele['company_address3'];
        $company_address .= '<br />' . $ele['company_city'];
        $company_address .= ', ' . $ele['company_state'];
        $company_address .= ' - ' . $ele['company_zip'];
        $msgList .= $ele['company_name'] . ' , ';
        $msgList .= $ele['company_email'];
        //	$msgList .=$ele['company_phone'].'<br />';
        //$msgList .= $company_address;
        $msgList .= '</div>';
        $msgList .= '</div></div>';
        $msgList .= '<span class="grid third">';
        if (count($ele['files']) > 0) {
            foreach ($ele['files'] as $filename) {
                $msgList .= '<a href="' . CONF_WEBROOT_URL . 'download.php?fname=' . $filename . '" class="attachFile"></a>';
            }
        }
        $msgList .= '<span class="date"><i class="icon ion-ios-clock-outline"></i>' . date('M d, Y', strtotime($ele['ticket_created_on'])) . '';
        $msgList .= '</span></span></li>';
    }
    if ($total_records == 0) {
        $msgList .= '<li class="">
		<span class="grid first">
		
		</span>    
		<div class="grid second">
			<div class="">												
				<div class="">No Record found</div>
			</div>
		</div>    
		
	</li>';
    }
    $msgList .= '</ul>';
    $msgList .= $str;
    return $msgList;
}

$post = getPostedData();
$merchant_support = new merchantSupport();
switch (strtoupper($post['mode'])) {
    case 'ARCHIVE':
        $ticket_id = $post['ticket'];
        $merchant_support->archiveTicket($ticket_id);
        break;
    case 'UNARCHIVE':
        $ticket_id = $post['ticket'];
        $merchant_support->unarchiveTicket($ticket_id);
        break;
    case 'MARKASREAD':
        foreach ($post['message_ids'] as $ticket_id) {
            $merchant_support->markTicketAsViewed($ticket_id);
            $merchant_support->markMessageAsViewed($ticket_id);
        }
        $msg_count = getNewMessagesCount();
        dieJsonSuccess($msg_count);
        break;
    case 'MARKASUNREAD':
        foreach ($post['message_ids'] as $key => $value) {
            $merchant_support->markTicketAsUnViewed($value);
            $merchant_support->markMessageAsUnViewed($value);
        }
        $msg_count = getNewMessagesCount();
        dieJsonSuccess($msg_count);
        break;
    case 'MARKASARCHIVE':
        foreach ($post['message_ids'] as $key => $ticket_id) {
            $merchant_support->archiveTicket($ticket_id);
        }
        dieJsonSuccess(t_lang('M_TXT_TICKET_ARCHIEVED_SUCCESSFULLY'));
        break;
    case 'MARKASUNARCHIVE':
        foreach ($post['message_ids'] as $key => $ticket_id) {
            $merchant_support->unarchiveTicket($ticket_id);
        }
        dieJsonSuccess(t_lang('M_TXT_TICKET_UNARCHIEVED_SUCCESSFULLY'));
        break;
    case 'GETMESSAGEHTML':
        $html = fetchMessageHtml($post['status'], $post['page'], $post['keyword']);
        dieJsonSuccess($html);
        break;
    case 'SENDMESSAGE':
        $status = (int) $post['status'];
        $ticket_created_by = (int) $post['created_by'];
        $frm = $merchant_support->getMerchantSupportForm($ticket_id, $ticket_created_by);
        $fld2 = $frm->getField('title');
        $frm->removeField($fld2);
        $fld = $frm->getField('description');
        $fld->setRequiredStarWith('caption');
        dieJsonSuccess($frm->getFormHtml());
        break;
}
