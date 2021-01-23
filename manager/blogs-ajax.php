<?php

require_once './application-top.php';
checkAdminPermission(1);
$post = getPostedData();
$get = getQueryStringData();
$mode = (isset($post['mode'])) ? $post['mode'] : $get['mode'];
switch (strtoupper($mode)) {
    case APPROVE_BLOG:
        if (!$db->update_from_array('tbl_blogs', ['blog_approved_by_admin' => 1], ['smt' => 'blog_id = ?', 'vals' => [$post['blog_id']]])) {
            $status = 0;
            $msg_class = 'redtext';
            $msg->addError('Could not approve the blog.');
        } else {
            $status = 1;
            $msg_class = 'greentext';
            $msg->addMsg('Blog Approved.');
        }
        $Usermsg = '<div class="box" id="messages">
                        <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                        <div class="content"><div class="' . $msg_class . '">' . $msg->display() . '</div></div>
                    </div>';
        $arr = ['status' => $status, 'msg' => $Usermsg];
        die(convertToJson($arr));
        return true;
    case APPROVE_COMMENT:
        if (!$db->update_from_array('tbl_blog_comments', ['comment_approved_by_admin' => 1], ['smt' => 'comment_id = ?', 'vals' => [$post['comment_id']]])) {
            $status = 0;
            $msg_class = 'redtext';
            $msg->addError('Could not approve the comment.');
        } else {
            $status = 1;
            $msg_class = 'greentext';
            $msg->addMsg('Comment Approved.');
        }
        $Usermsg = '<div class="box" id="messages">
                        <div class="title-msg"> ' . t_lang('M_TXT_SYSTEM_MESSAGES') . '</div>
                        <div class="content"><div class="' . $msg_class . '">' . $msg->display() . '</div></div>
                    </div>';
        $arr = ['status' => $status, 'msg' => $Usermsg];
        die(convertToJson($arr));
        return true;
}

