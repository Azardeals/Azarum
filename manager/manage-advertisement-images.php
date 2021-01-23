<?php
require_once './application-top.php';
checkAdminPermission(1);
$adv_id = (int) $_GET['aid'];
$img_id = (int) $_GET['edit'];
/** Get advertisement images list * */
$srch = new SearchBase('tbl_advertisement_images', 'ai');
$srch->addCondition('adimg_advertisement_id', '=', $adv_id);
$srch->addOrder('adimg_id');
$srch->addMultipleFields(array('ai.*'));
$rs_listing = $srch->getResultSet();
/* * ------* */
/** upload image form * */
$frm = new Form('frmAdvertisementImage');
$frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->addHiddenField('', 'adv_id', $adv_id);
$frm->addHiddenField('', 'img_id', $img_id);
$frm->addFileUpload(t_lang('M_TXT_IMAGE'), 'adv_image', 'adv_image');
$frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit');
/* * * */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if ($db->total_records($rs_listing) == 5) {
        $msg->addMsg(t_lang('M_TXT_MAX_ADVERTISEMENT_IMAGES'));
        redirectUser('?aid=' . $post['adv_id']);
    }
    if (is_uploaded_file($_FILES['adv_image']['tmp_name'])) {
        $ext = strtolower(strrchr($_FILES['adv_image']['name'], '.'));
        if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['adv_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
            $msg->addError(t_lang('M_TXT_ADVERTISEMENT') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
            $success = false;
        } else {
            $flname = time() . '_' . $_FILES['adv_image']['name'];
            if (!move_uploaded_file($_FILES['adv_image']['tmp_name'], '../advertisement-images/' . $flname)) {
                $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                $success = false;
            } else {
                if ($post['img_id'] > 0) {
                    if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                        $srch = new SearchBase('tbl_advertisement_images', 'ai');
                        $srch->addCondition('adimg_id', '=', $post['img_id']);
                        $srch->addMultipleFields(array('ai.*'));
                        $rs = $srch->getResultSet();
                        $row = $db->fetch($rs);
                        unlink('../advertisement-images/' . $row['adimg_name']);
                        $success = $db->update_from_array('tbl_advertisement_images', array('adimg_name' => $flname), array('smt' => 'adimg_id = ?', 'vals' => array($post['img_id'])));
                    } else {
                        $msg->addError(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
                    }
                } else {
                    $success = $db->insert_from_array('tbl_advertisement_images', array('adimg_advertisement_id' => $post['adv_id'], 'adimg_name' => $flname));
                }
            }
        }
    }
    if ($success) {
        $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
        redirectUser('?aid=' . $post['adv_id']);
    } else {
        $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $db->getError());
        fillForm($frm, $post);
    }
}
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
        $srch = new SearchBase('tbl_advertisement_images', 'ai');
        $srch->addCondition('adimg_id', '=', $_GET['delete']);
        $srch->addMultipleFields(array('ai.*'));
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!$db->query('delete from tbl_advertisement_images where adimg_id=' . $_GET['delete'])) {
            $msg->addError($db->getError());
        } else {
            unlink('../advertisement-images/' . $row['adimg_name']);
            $msg->addMsg(t_lang('M_TXT_ADVERTISEMENT_DELETED'));
            redirectUser('?aid=' . $adv_id);
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SR_NO'),
    'adv_image' => t_lang('M_TXT_PREVIEW'),
    'action' => t_lang('M_TXT_ACTION')
);
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'advertisements.php' => t_lang('M_TXT_ADVERTISEMENTS'),
    '' => t_lang('M_TXT_MANAGE_IMAGES')
];
require_once './header.php';
?>
<ul class="nav-left-ul">
    <li>
        <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="advertisements.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_ADVERTISEMENT_LISTING') ?></a>
    </li>
    <li>
        <a <?php if ($_REQUEST['status'] == 'inactive') echo 'class="selected"'; ?> href="advertisements.php?status=inactive"><?php echo t_lang('M_TXT_INACTIVE_ADVERTISEMENT_LISTING') ?></a>
    </li>
</ul>
</div>
</td>					
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>                
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> 
                <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?>
                <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a>
            </div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="greentext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(1, '', 'add')) || (checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            ?>
            <div class="box">
                <div class="title"><?php echo t_lang('M_TXT_MANAGE_IMAGES'); ?> </div>
                <div class="content"><?php echo $frm->getFormHtml(); ?></div>
            </div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box">
            <div class="title"><?php echo t_lang('M_TXT_MANAGE_IMAGES'); ?></div>
            <div class="content">		
                <div class="gap">&nbsp;</div>					 
                <table class="tbl_data" width="100%">
                    <thead>
                        <tr>
                            <?php
                            foreach ($arr_listing_fields as $val)
                                echo '<th>' . $val . '</th>';
                            ?>
                        </tr>
                    </thead>
                    <?php
                    for ($listserial = 1; $row = $db->fetch($rs_listing); $listserial++) {
                        if ($listserial % 2 == 0) {
                            $even = 'even';
                        } else {
                            $even = '';
                        }
                        echo '<tr class=" ' . $even . ' " ' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['adimg_id'] . '>';
                        foreach ($arr_listing_fields as $key => $val) {
                            echo '<td>';
                            switch ($key) {
                                case 'listserial':
                                    echo $listserial;
                                    break;
                                case 'adv_image':
                                    echo '<img src="' . CONF_WEBROOT_URL . 'advertisement-image.php?img_id=' . $row['adimg_id'] . '&w=100&h=75" alt="No Preview"/>';
                                    break;
                                case 'action':
                                    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
                                        echo '<a href="?aid=' . $adv_id . '&edit=' . $row['adimg_id'] . '" title="' . t_lang('M_TXT_EDIT') . '" class="btn gray">' . t_lang('M_TXT_EDIT') . '</a> ';
                                    }
                                    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
                                        echo '<a href="?aid=' . $adv_id . '&delete=' . $row['adimg_id'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);" class="btn delete">' . t_lang('M_TXT_DELETE') . '</a> ';
                                    }
                                    break;
                                default:
                                    echo $row[$key];
                                    break;
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                    if ($db->total_records($rs_listing) == 0) {
                        echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                    }
                    ?>
                </table>
                <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                    <div style="padding-top:5px;" >
                        <?php if ($db->total_records($rs_listing) < 5) { ?>
                            <a href="?aid=<?php echo $adv_id; ?>&add=new" class="btn green fr"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_IMAGE'); ?></a>
                        <?php } ?>
                    </div>
                    <div class="clear"></div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>