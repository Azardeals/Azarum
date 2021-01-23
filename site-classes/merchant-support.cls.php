<?php

class merchantSupport
{

    private $pagesize;
    private $user_id;

    function __construct()
    {
        $this->pagesize = 10;
        $this->user_id = isCompanyUserLogged() == true ? $_SESSION['logged_user']['company_id'] : $_SESSION['admin_logged']['admin_id'];
    }

    public function getTickets($status = 0, $page, $keyword = "")
    {
        global $db;
        $status = intval($status);
        $page = intval($page);
        $page = $page < 1 ? 1 : $page;
        $srch = new SearchBase('tbl_support_tickets', 't');
        $srch->joinTable('tbl_companies', 'LEFT OUTER JOIN', 't.ticket_created_by=c.company_id', 'c');
        $srch->joinTable('tbl_support_ticket_messages', 'LEFT OUTER JOIN', 't.ticket_id=m.msg_ticket_id', 'm');
        if (isCompanyUserLogged())
            $srch->addCondition('ticket_created_by', '=', $this->user_id);
        switch ($status) {
            case 0: //all messages
                if (isCompanyUserLogged()) {
                    /* $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '0') THEN 1 ELSE 0 END) AS unread_messages");
                      //$cnd = $srch->addHaving('unread_messages', '=', 0);
                      //$cnd->attachCondition('ticket_archived_by_merchant', '=', '0', 'AND');
                      $srch->addHaving('unread_messages', '=', '0');
                      $srch->addHaving('ticket_archived_by_merchant', '=', '0'); */
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '0') THEN 1 ELSE 0 END) AS unread_messages");
                    //$srch->addHaving('unread_messages', '=', '0');
                    $srch->addHaving('ticket_archived_by_merchant', '=', '0');
                } else {
                    /* $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '1') THEN 1 ELSE 0 END) AS unread_messages");
                      $srch->addHaving('unread_messages', '=', 0);
                      $cnd = $srch->addHaving('ticket_viewed', '=', '1', 'AND');
                      $cnd->attachCondition('ticket_archived_by_admin', '=', '0', 'AND'); */
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '1') THEN 1 ELSE 0 END) AS unread_messages");
                    //$srch->addHaving('unread_messages', '=', 0);
                    //$cnd = $srch->addHaving('ticket_viewed', '=', '1', 'AND');
                    $srch->addHaving('ticket_archived_by_admin', '=', '0', 'AND');
                }
                break;
            case 1: //unread messages
                if (isCompanyUserLogged()) {
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '0') THEN 1 ELSE 0 END) AS unread_messages");
                    $srch->addHaving('unread_messages', '>', 0);
                } else {
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '1') THEN 1 ELSE 0 END) AS unread_messages");
                    $cnd = $srch->addHaving('ticket_viewed', '=', '0');
                    $cnd->attachCondition('unread_messages', '>', 0);
                }
                break;
            case 2: //archived messages
                if (isCompanyUserLogged()) {
                    /* $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '0') THEN 1 ELSE 0 END) AS unread_messages");
                      $srch->addHaving('unread_messages', '=', 0);
                      $srch->addHaving('ticket_archived_by_merchant', '=', '1'); */
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '0') THEN 1 ELSE 0 END) AS unread_messages");
                    //$srch->addHaving('unread_messages', '=', 0);
                    $srch->addHaving('ticket_archived_by_merchant', '=', '1');
                } else {
                    /* $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '1') THEN 1 ELSE 0 END) AS unread_messages");
                      $srch->addHaving('unread_messages', '=', 0);
                      $cnd = $srch->addHaving('ticket_viewed', '=', '1', 'AND');
                      $cnd->attachCondition('ticket_archived_by_admin', '=', '1', 'AND'); */
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '1') THEN 1 ELSE 0 END) AS unread_messages");
                    //$srch->addHaving('unread_messages', '=', 0);
                    //$cnd = $srch->addHaving('ticket_viewed', '=', '1', 'AND');
                    $srch->addHaving('ticket_archived_by_admin', '=', '1', 'AND');
                }
                break;
            case 3: //read messages
                if (isCompanyUserLogged()) {
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '0') THEN 1 ELSE 0 END) AS unread_messages");
                    $cnd = $srch->addHaving('ticket_viewed', '=', '1');
                    $cnd->attachCondition('unread_messages', '=', 0);
                } else {
                    $srch->addFld("SUM(CASE WHEN (msg_viewed = '0' AND msg_sender_is_merchant = '1') THEN 1 ELSE 0 END) AS unread_messages");
                    $cnd = $srch->addHaving('ticket_viewed', '=', '1', 'AND');
                    $cnd->attachCondition('unread_messages', '=', 0, 'AND');
                    $srch->addCondition('ticket_archived_by_admin', '=', 0);
                }
                break;
        }
        //$srch->addCondition('company_active', '=', 1);
        //$srch->addCondition('company_deleted', '=', 0);
        if (!empty($keyword)) {
            $srch->addCondition('t.ticket_title', 'LIKE', '%' . $keyword . '%');
            $srch->addCondition('t.ticket_description', 'LIKE', '%' . $keyword . '%', 'OR');
        }
        $srch->addOrder('ticket_created_on', 'desc');
        $srch->addGroupBy('ticket_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($this->pagesize);
        $srch->addMultipleFields(array('t.*', 'c.company_name', 'c.company_email', 'c.company_phone', 'c.company_address1', 'c.company_address2', 'c.company_address3', 'c.company_city', 'c.company_state', 'c.company_zip'));
        $rs = $srch->getResultSet();
        $data = [];
        $data['tickets'] = $db->fetch_all($rs);
        foreach ($data['tickets'] as $key => $arr) {
            $data['tickets'][$key]['files'] = $this->getFiles($arr['ticket_id']);
        }
        $data['total_pages'] = $srch->pages();
        $data['total_records'] = $srch->recordCount();
        $data['page_size'] = $this->pagesize;
        return $data;
    }

    public function getTicketById($id)
    {
        global $db;
        $srch = new SearchBase('tbl_support_tickets', 't');
        $srch->joinTable('tbl_companies', 'LEFT OUTER JOIN', 't.ticket_created_by=c.company_id', 'c');
        $srch->joinTable('tbl_support_ticket_files', 'LEFT OUTER JOIN', 't.ticket_id=f.file_ticket_id', 'f');
        $srch->addCondition('ticket_id', '=', $id);
        //$srch->addCondition('ticket_archived_by_merchant', '=', 0);
        $srch->addCondition('company_active', '=', 1);
        $srch->addCondition('company_deleted', '=', 0);
        $srch->addMultipleFields(array('t.*', 'f.*', 'c.company_name', 'c.company_email', 'c.company_phone', 'c.company_address1', 'c.company_address2', 'c.company_address3', 'c.company_city', 'c.company_state', 'c.company_zip'));
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        $row['files'] = $this->getFiles($id);
        return $row;
    }

    public function getMessagesByTicketId($id = 0, $page)
    {
        global $db;
        $id = intval($id);
        if ($id < 1)
            die('Invalid request!');
        $page = intval($page);
        $page = $page < 1 ? 1 : $page;
        $srch = new SearchBase('tbl_support_ticket_messages', 'm');
        $srch->joinTable('tbl_companies', 'LEFT OUTER JOIN', 'm.msg_sender=c.company_id', 'c');
        $srch->joinTable('tbl_admin', 'LEFT OUTER JOIN', 'm.msg_sender=a.admin_id', 'a');
        $srch->addCondition('msg_ticket_id', '=', $id);
        $srch->addOrder('msg_sent_on', 'desc');
        $srch->setPageNumber($page);
        $srch->setPageSize($this->pagesize);
        $srch->addMultipleFields(array('m.*'));
        $srch->addFld("CASE m.msg_sender_is_merchant WHEN '1' THEN c.company_name ELSE a.admin_name END AS msg_sent_by");
        $rs = $srch->getResultSet();
        $data = [];
        $data['messages'] = $db->fetch_all($rs);
        foreach ($data['messages'] as $key => $arr) {
            $data['messages'][$key]['files'] = $this->getFiles($id, $arr['msg_id']);
        }
        $data['total_pages'] = $srch->pages();
        $data['total_records'] = $srch->recordCount();
        $data['page_size'] = $this->pagesize;
        return $data;
    }

    public function getFiles($tid, $mid = 0)
    {
        global $db;
        $sql = $db->query("SELECT file_id,file_name FROM tbl_support_ticket_files WHERE file_ticket_id = $tid AND file_message_id = $mid");
        return $db->fetch_all_assoc($sql);
    }

    public function createTicket($data)
    {
        global $msg;
        $frm = $this->getMerchantSupportForm($post['ticket_id']);
        if (!$frm->validate($data)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error)
                $msg->addError($error);
            return false;
        }
        /** upload files * */
        $uploaded_files = [];
        for ($i = 0; $i < count($data['files']['name']); $i++) {
            if (is_uploaded_file($data['files']['tmp_name'][$i])) {
                $fname = $data['files']['name'][$i];
                while (file_exists(SUPPORT_FILES_PATH . $fname)) {
                    $path_parts = pathinfo(SUPPORT_FILES_PATH . $fname);
                    $fname = $path_parts['filename'] . '_' . rand(10, 99) . '.' . $path_parts['extension'];
                }
                $ext = strtolower(strrchr($data['files']['name'][$i], '.'));
                if (!in_array($ext, array('.doc', '.docx', '.odt', '.pdf', '.xls', '.xlsx', '.ppt', '.pptx', '.txt', 'ods'))) {
                    $msg->addError(t_lang('M_TXT_FILE_NOT_SUPPORTED'));
                    return false;
                }
                if (!move_uploaded_file($data['files']['tmp_name'][$i], SUPPORT_FILES_PATH . $fname)) {
                    $msg->addError($data['files']['name'][$i] . ' could not be uploaded.');
                } else {
                    $uploaded_files[] = $fname;
                }
            }
        }
        /*         * ****###***** */
        $record = new TableRecord('tbl_support_tickets');
        $record->setFldValue('ticket_title', $data['title']);
        $record->setFldValue('ticket_description', htmlentities($data['description'], ENT_QUOTES, 'UTF-8'));
        $record->setFldValue('ticket_created_by', $this->user_id);
        $record->setFldValue('ticket_created_on', date("Y-m-d H:i"));
        $record->setFldValue('ticket_archived_by_merchant', 0);
        if (!$record->addNew()) {
            $msg->addError($record->getError());
            return false;
        } else {
            $last_inserted_id = $record->getId();
            $msg_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'manager/message-details.php?tid=' . $last_inserted_id;
            $email_msg = nl2br($data['description']) . '<br /><br />' . $msg_url;
            if (count($uploaded_files) > 0) {
                $files_list = '<b>Attached Files:</b>';
                foreach ($uploaded_files as $file_name) {
                    $record = new TableRecord(' tbl_support_ticket_files');
                    $record->setFldValue('file_ticket_id', $last_inserted_id);
                    $record->setFldValue('file_message_id', 0);
                    $record->setFldValue('file_name', $file_name);
                    if ($record->addNew()) {
                        $files_list .= '<br /><a href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'download.php?fname=' . $file_name . '">' . $file_name . '</a>';
                    }
                }
                $email_msg .= '<br /><br />' . $files_list;
            }
            if (CONF_SEND_MERCHANT_SUPPORT_ALERTS == 1 && isCompanyUserLogged())
                $this->sendNotification($this->user_id, $email_msg);
            $msg->addMsg(t_lang('M_TXT_MESSAGE_SENT'));
            redirectUser('message-listing.php');
            return true;
        }
    }

    public function sendMessage($data, $status, $isAdmin = false)
    {
        global $msg;
        $frm = $this->getMerchantSupportForm($data['ticket_id']);
        $fld = $frm->getField('title');
        $frm->removeField($fld);
        if (!$frm->validate($data)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error)
                $msg->addError($error);
            return false;
        }
        /** upload files * */
        $uploaded_files = [];
        for ($i = 0; $i < count($data['files']['name']); $i++) {
            if (is_uploaded_file($data['files']['tmp_name'][$i])) {
                $fname = $data['files']['name'][$i];
                while (file_exists(SUPPORT_FILES_PATH . $fname)) {
                    $path_parts = pathinfo(SUPPORT_FILES_PATH . $fname);
                    $fname = $path_parts['filename'] . '_' . rand(10, 99) . '.' . $path_parts['extension'];
                }
                $ext = strtolower(strrchr($data['files']['name'][$i], '.'));
                if (!in_array($ext, array('.doc', '.docx', '.odt', '.pdf', '.xls', '.xlsx', '.ppt', '.pptx', '.txt', 'ods'))) {
                    $msg->addError(t_lang('M_TXT_FILE_NOT_SUPPORTED'));
                    return false;
                }
                if (!move_uploaded_file($data['files']['tmp_name'][$i], SUPPORT_FILES_PATH . $fname)) {
                    $msg->addError($data['files']['name'][$i] . ' could not be uploaded.');
                    //exit;
                } else {
                    $uploaded_files[] = $fname;
                }
            }
        }
        /*         * ****###***** */
        $record = new TableRecord('tbl_support_ticket_messages');
        $ticket_id = $data['ticket_id'];
        $record->setFldValue('msg_ticket_id', $ticket_id);
        $record->setFldValue('msg_description', htmlentities($data['description'], ENT_QUOTES, 'UTF-8'));
        $record->setFldValue('msg_sender', $this->user_id);
        $record->setFldValue('msg_recipient', $data['ticket_created_by']);
        $record->setFldValue('msg_sent_on', date("Y-m-d H:i"));
        if (isCompanyUserLogged())
            $record->setFldValue('msg_sender_is_merchant', '1');
        if (!$record->addNew()) {
            $msg->addError($record->getError());
            return false;
        } else {
            $last_inserted_id = $record->getId();
            if ($isAdmin == true) {
                $msg_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant/message-details.php?tid=' . $ticket_id;
            } else {
                $msg_url = 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'manager/message-details.php?tid=' . $ticket_id;
            }
            $email_msg = nl2br($data['description']) . '<br /><br />' . $msg_url;
            if (count($uploaded_files) > 0) {
                $files_list = '<b>Attached Files:</b>';
                foreach ($uploaded_files as $file_name) {
                    $record = new TableRecord(' tbl_support_ticket_files');
                    $record->setFldValue('file_ticket_id', $ticket_id);
                    $record->setFldValue('file_message_id', $last_inserted_id);
                    $record->setFldValue('file_name', $file_name);
                    if ($record->addNew()) {
                        $files_list .= '<br /><img src="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant/images/zip_icon.gif" /><a href="http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'download.php?fname=' . $file_name . '">' . $file_name . '</a>';
                    }
                }
                $email_msg .= '<br /><br />' . $files_list;
            }
            if ($isAdmin == true) {
                $this->sendNotiToMerchant($this->user_id, $email_msg);
            }
            if (CONF_SEND_MERCHANT_SUPPORT_ALERTS == 1 && isCompanyUserLogged() && $isAdmin != true) {
                $this->sendNotification($this->user_id, $email_msg);
            }
            $msg->addMsg(t_lang('M_TXT_MESSAGE_SENT'));
            redirectUser('message-details.php?status=' . $status . '&tid=' . $ticket_id);
            return true;
        }
    }

    private function sendNotiToMerchant($from, $msg)
    {
        global $db;
        $sql = $db->query("SELECT company_email,company_name FROM tbl_companies WHERE company_id = $from");
        $comp = $db->fetch($sql);
        $recipients = $comp['company_email'];
        $rs = $db->query("SELECT * FROM tbl_email_templates WHERE tpl_id = 51");
        $row_tpl = $db->fetch($rs);
        $subject = $row_tpl['tpl_subject'];
        $body = $row_tpl['tpl_message'];
        $arr_replacements = array(
            '{site_name}' => CONF_SITE_NAME,
            '{company}' => ucfirst($comp['company_name']),
            '{admin_name}' => $_SESSION['admin_logged']['admin_name'],
            '{message_description}' => $msg,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxsite_namexx' => CONF_SITE_NAME,
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $body = str_replace($key, $val, $body);
        }
        sendMail($recipients, $subject, $body, $headers);
    }

    private function sendNotification($from, $msg)
    {
        global $db;
        $recipients_arr = explode(',', CONF_MERCHANT_SUPPORT_NOTIFICATION_RECIPIENTS);
        $recipients = '';
        foreach ($recipients_arr as $val) {
            $email_id = $this->getAdminEmailById($val);
            $recipients .= $email_id . ',';
        }
        $recipients = rtrim($recipients, ',');
        $rs = $db->query("SELECT * FROM tbl_email_templates WHERE tpl_id = 41");
        $row_tpl = $db->fetch($rs);
        $subject = $row_tpl['tpl_subject'];
        $body = $row_tpl['tpl_message'];
        $arr_replacements = array(
            '{site_name}' => CONF_SITE_NAME,
            '{sender}' => $this->getMerchantNameById($from),
            '{message_description}' => $msg,
            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
            'xxsite_namexx' => CONF_SITE_NAME,
        );
        foreach ($arr_replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $body = str_replace($key, $val, $body);
        }
        /* $headers  = 'MIME-Version: 1.0' . "\r\n";
          $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
          $headers .= 'From: ' . CONF_EMAILS_FROM . "\r\n"; */
        sendMail($recipients, $subject, $body, $headers);
    }

    private function getMerchantNameById($id)
    {
        global $db;
        if (!isCompanyUserLogged())
            return false;
        $sql = $db->query("SELECT company_name FROM tbl_companies WHERE company_id = $id");
        $rs = $db->fetch($sql);
        return $rs['company_name'];
    }

    private function getAdminEmailById($id)
    {
        global $db;
        $sql = $db->query("SELECT admin_email FROM tbl_admin WHERE admin_id = $id");
        $rs = $db->fetch($sql);
        return $rs['admin_email'];
    }

    public function getMerchantSupportForm($ticket_id, $ticket_created_by = 0)
    {
        $frm = new Form('frmMerchantSupport', 'frmMerchantSupport');
        $frm->setTableProperties('class="tbl_form" width="100%"');
        $frm->setJsErrorDisplay('afterfield');
        $fld1 = $frm->addTextBox(t_lang('M_FRM_TITLE'), 'title', '', 'title', 'class="big" maxlength="50"');
        $fld1->requirements()->setRequired();
        $fld2 = $frm->addTextArea(t_lang('M_TXT_MESSAGE'), 'description', '', 'description', 'class="bodytxtarea" cols="100%" rows="7" placeholder="' . t_lang('M_TXT_MESSAGE') . '*"');
        $fld2->requirements()->setRequired();
        $fld2->setRequiredStarPosition('none');
        $frm->addFileUpload(t_lang('M_TXT_ATTACH_FILES'), 'files[]', 'files', 'multiple="multiple" onchange=getFilename()');
        $frm->addHiddenField('', 'ticket_id', $ticket_id, 'ticket_id');
        $frm->addHiddenField('', 'ticket_created_by', $ticket_created_by, 'ticket_created_by');
        $frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SEND'));
        return $frm;
    }

    public function markTicketAsViewed($ticket_id)
    {
        global $db;
        $db->query("UPDATE tbl_support_tickets SET ticket_viewed = '1' WHERE ticket_id = $ticket_id");
    }

    public function markTicketAsUnViewed($ticket_id)
    {
        global $db;
        $db->query("UPDATE tbl_support_tickets SET ticket_viewed = '0' WHERE ticket_id = $ticket_id");
    }

    public function markMessageAsViewed($ticket_id)
    {
        global $db;
        if (isCompanyUserLogged()) {
            $query = "UPDATE tbl_support_ticket_messages SET msg_viewed = '1' WHERE msg_ticket_id = " . $ticket_id . " AND msg_recipient = " . $_SESSION['logged_user']['company_id'] . " AND msg_sender_is_merchant = '0'";
            $db->query($query);
        } else {
            $db->query("UPDATE tbl_support_ticket_messages SET msg_viewed = '1' WHERE msg_ticket_id = " . $ticket_id . " AND msg_sender_is_merchant = '1'");
        }
    }

    public function markMessageAsUnViewed($ticket_id)
    {
        global $db;
        if (isCompanyUserLogged()) {
            $db->query("UPDATE tbl_support_ticket_messages SET msg_viewed = '0' WHERE msg_ticket_id = " . $ticket_id . " AND msg_recipient = " . $_SESSION['logged_user']['company_id'] . " AND msg_sender_is_merchant = '0'");
        } else {
            $db->query("UPDATE tbl_support_ticket_messages SET msg_viewed = '0' WHERE msg_ticket_id = " . $ticket_id . " AND msg_sender_is_merchant = '1'");
        }
    }

    public function archiveTicket($ticket_id)
    {
        global $db;
        $fld = isCompanyUserLogged() == true ? 'ticket_archived_by_merchant' : 'ticket_archived_by_admin';
        $db->query("UPDATE tbl_support_tickets SET " . $fld . " = '1' WHERE ticket_id = " . $ticket_id);
    }

    public function unarchiveTicket($ticket_id)
    {
        global $db;
        $fld = isCompanyUserLogged() == true ? 'ticket_archived_by_merchant' : 'ticket_archived_by_admin';
        $db->query("UPDATE tbl_support_tickets SET " . $fld . " = '0' WHERE ticket_id = " . $ticket_id);
    }

    /* private function getMerchantInfo() {
      $srch = new SearchBase('tbl_companies');
      $srch->addCondition('company_id', '=', $this->user_id);
      $srch->addCondition('company_active', '=', 1);
      $srch->addCondition('company_deleted', '=', 0);
      $srch->addMultipleFields(array('company_name', 'company_email', 'company_phone', 'company_address1', 'company_address2', 'company_address3', 'ompany_city', 'company_state', 'company_zip'));
      $rs = $srch->getResultSet();
      $row = $db->fetch_all($rs);
      return $row;
      } */
}

?>
