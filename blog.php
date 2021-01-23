<?php
require_once './application-top.php';
require_once './header.php';
if (!isUserLogged()) {
    $_SESSION['login_page'] = $_SERVER['REQUEST_URI'];
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
/** get blog categories * */
$srch = new SearchBase('tbl_blog_categories', 'c');
$result = $srch->getResultSet();
$category_listing = $db->fetch_all_assoc($result);
/* * ******* */
/** Blog form * */
$frm = new Form('frmBlog');
$frm->setExtra('class="siteForm"');
$frm->setTableProperties('class="formwrap__table"');
$frm->setJsErrorDisplay('afterfield');
$frm->setAction('?');
$frm->captionInSameCell(true);
$frm->setJsErrorDisplay('afterfield');
$fld = $frm->addRequiredField('', 'blog_title', '', 'blog_title', 'placeholder="' . t_lang('M_FRM_TITLE') . '*" title="' . t_lang('M_FRM_TITLE') . '"');
$fld->requirement->setLength(5, 200);
$frm->setRequiredStarPosition('none');
$fld = $frm->addTextArea('', 'blog_description', '', 'blog_description', 'placeholder="' . t_lang('M_TXT_DESCRIPTION') . '*" title="' . t_lang('M_TXT_DESCRIPTION') . '"');
$fld->requirement->setLength(20, 2000);
$fld->requirements()->setRequired();
$frm->addSelectBox('', 'blog_cat_id', $category_listing, '', '', 'Select', 'blog_cat_id');
$fld = $frm->addFileUpload('', 'blog_image', 'blog_image', 'onchange= getValue(this)');
$fld->html_before_field = "<div class='fieldcover'><span id='uploadFile' class='filename'>" . t_lang('M_TXT_IMAGE') . "</span>";
$fld->html_after_field = '<span class="filelabel">' . t_lang('M_TXT_BROWSE_FILE') . '</span></div>';
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), 'btn_submit');
/* * ***** */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //print_r($_POST);
    if (isUserLogged()) {
        $post = getPostedData();
        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                $msg->addError($error);
            }
        } else {
            $record = new TableRecord('tbl_blogs');
            $record->setFldValue('blog_user_id', $_SESSION['logged_user']['user_id']);
            $record->setFldValue('blog_admin_id', 0);
            $record->setFldValue('blog_added_on', date("Y-m-d H:i"));
            $arr_lang_independent_flds = array('blog_id', 'blog_cat_id', 'blog_added_on', 'blog_status', 'btn_submit');
            $post['blog_description'] = preg_replace('/<script>/', '', $post['blog_description']);
            $post['blog_description'] = preg_replace('/<SCRIPT>/', '', $post['blog_description']);
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            $success = $record->addNew();
            if ($success) {
                $blog_id = $record->getId();
                if (is_uploaded_file($_FILES['blog_image']['tmp_name'])) {
                    $ext = strtolower(strrchr($_FILES['blog_image']['name'], '.'));
                    if (!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) {
                        $msg->addError(t_lang('M_TXT_IMAGE_NOT_SUPPORTED'));
                    } else {
                        $flname = time() . '_' . $_FILES['blog_image']['name'];
                        if (!move_uploaded_file($_FILES['blog_image']['tmp_name'], BLOG_IMAGES_PATH . $flname)) {
                            $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                        } else {
                            $db->update_from_array('tbl_blogs', array('blog_image' => $flname), 'blog_id=' . $blog_id);
                        }
                    }
                }
                $msg->addMsg(t_lang('M_TXT_BLOG_POSTED'));
                /* Notify Admin  */
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
                $fromemail = $_SESSION['logged_user']['user_email'];
                $fromname = $_SESSION['logged_user']['user_name'];
                $headers .= "From: " . $fromname . " <" . $fromemail . ">\r\n";
                $rs = $db->query("select * from tbl_email_templates where tpl_id=50");
                $row_tpl = $db->fetch($rs);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxname_of_companyxx' => ucfirst($fromname),
                    'xxblog_namexx' => $post['blog_title' . $_SESSION['lang_fld_prefix']],
                    'xxwebsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($row_tpl['tpl_status'] == 1) {
                    mail(CONF_SITE_OWNER_EMAIL, $subject, emailTemplate($message), $headers);
                }
                /* Notify Admin */
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_POST_THE_BLOG') . '&nbsp;' . $record->getError());
                fillForm($frm, $post);
            }
        }
    } else {
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
    }
}
?>
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_BLOG'); ?></h3>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel__centered">
                    <div class="cover__grey">
                        <h4><?php echo t_lang('M_TXT_POST_A_BLOG'); ?></h4>
                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-listing.php') ?>" class="themebtn themebtn--xsmall right ">Back</a>
                        <?php echo $frm->getFormHtml(); ?>
                    </div>
                </div>
            </div>
        </div>    
    </div>    
</section>
<script type="text/javascript">
    var selectedState = 0;
    function getValue(obj) {
        var value = $("input[name=blog_image]").val();
        $('.filename').text(value);
    }
</script>	
<?php
require_once './footer.php';
