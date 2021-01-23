<?php require_once './application-top.php'; ?>
<script type="text/javascript">
    function loginCheck() {
        $("#loggedin").show();
        $("#loggedin").html("<?php echo addslashes(t_lang('M_TXT_THE_FOLLOWING_ERROR_OCCURED')) . '<ul><li>' . addslashes(t_lang('M_ERROR_PLEASE_LOGIN_FOR_DISCUSSION')) . '</li></ul>'; ?>");
    }
</script>
<?php
$deal_name = $objDeal->getFldValue('deal_name');
$frm = getMBSFormByIdentifier('frmDealDiscussion');
$fld1 = $frm->getField('comment_user_id');
$fld1->value = $_SESSION['logged_user']['user_id'];
$fld = $frm->getField('comment_deal_id');
$fld->value = $objDeal->getFldValue('deal_id');
$fld = $frm->getField('comment_comments');
$fld5 = $frm->getField('comment_title');
$frm->removeField($fld5);
$frm->addHiddenField('', 'comment_title', $deal_name, 'comment_title', '');
$fld2 = $frm->getField('submit');
if ($_SESSION['logged_user']['user_id'] == "") {
    $frm->removeField($fld2);
    $frm->addButton('', 'submit', 'Post Your Comment', 'submit', 'onclick= "return loginCheck();" class="button_large"');
}
$urlDeal = "'" . friendlyUrl(CONF_WEBROOT_URL . 'deal.php') . "'";
#$fld2->html_after_field='<a href ="javascript:void(0);" onclick="javascript:window.location.href ='.$urlDeal.'">Or Cancel</a>';
if (CONF_COMMENTS_NEED_APPROVAL == 1) {
    $fldComment = $frm->getField('comment_approved');
    $fldComment->value = 0;
} else {
    $fldComment = $frm->getField('comment_approved');
    $fldComment->value = 1;
}
if ($_POST['comment_comments'] != "") {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (is_numeric($_SESSION['logged_user']['user_id']) > 0) {
            if (isset($_POST['comment_comments']) && trim($_POST['comment_comments']) != "") {
                $post = getPostedData();
                $record = new TableRecord('tbl_deal_discussions');
                $record->assignValues($post);
                $record->setFldValue('comment_posted_on', date('Y-m-d H:i:s'), true);
                $record->setFldValue('comment_title', nl2br($_POST['comment_comments']));
                $record->setFldValue('comment_comments', '');
                $success = $record->addNew();
                if ($success) {
                    if ($_POST['comment_approved'] == 1) {
                        $messageAdmin = 'Hello ' . CONF_EMAILS_FROM_NAME . ',
				There has been submission of Discussion  form on you site. Need approval for displaying in the front end.Details are given below:
				<b>Title: </b>' . $post['comment_title'] . '
				
				<b>Comment: </b>' . $post['comment_comments'] . '
				
				<b>By: </b>' . htmlentities($_SESSION['logged_user']['user_name']);
                        /*  $headers  = 'MIME-Version: 1.0' . "\r\n";
                          $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                          $fromemail = CONF_EMAILS_FROM ;
                          $fromname = CONF_EMAILS_FROM_NAME ;
                          $headers .= "From: ".$fromname." <".$fromemail.">\r\n"; */
                        sendMail(CONF_SITE_OWNER_EMAIL, 'Discussion Form Submission', emailTemplate(nl2br($messageAdmin)), $headers);
                    }
                    $msg->addMsg('Your comment has been successfully posted.');
                    $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $_POST['comment_deal_id'] . '&mode=main';
                    //redirectUser(friendlyUrl($dealUrl));
                    //redirectUser();
                } else {
                    $msg->addError('Could not post! Error: ' . $record->getError());
                    $frm->fill($post);
                }
            } else {
                $msg->addError('Comment is mandatory.');
            }
        } else {
            $msg->addError('Please login first for discussion.');
        }
    }
}
?>
<div class="colum_head_wrap">
    <h3><?php echo t_lang('M_TXT_POST_NEW_COMMENT'); ?></h3>
</div>  
<div class="reviews_wrapper"><div  id="loggedin" class="div_error" style="display:none;"></div>
    <?php
    echo $msg->display();
    echo $frm->getFormHtml();
    ?>
</div>  
