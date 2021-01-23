<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(1);
$mainTableName = 'tbl_banner';
$primaryKey = 'banner_id';
$colPrefix = 'banner_';
if (is_numeric($_REQUEST['delete'])) {
    $banner_id = $_REQUEST['delete'];
    $db->query("DELETE FROM tbl_banner WHERE banner_id =$banner_id");
    $msg->addMsg(t_lang("M_TXT_RECORD_DELETED"));
    redirectUser('?');
}
$banner_type_id = (int) $_REQUEST['banner_type'];
$banner_type = [4 => t_lang('M_TXT_HOME'), 6 => t_lang('M_TXT_PRODUCT_MAIN'), 1 => t_lang('M_TXT_HOME_MIDDLE'), 2 => t_lang('M_TXT_PRODUCT_RIGHT'), 3 => t_lang('M_TXT_OFFERS'), 5 => t_lang('M_TXT_REGISTERATION')];
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addSelectBox(t_lang('M_FRM_BANNER_TYPE'), 'banner_type', $banner_type, $banner_type_id, '', t_lang('M_TXT_SELECT'), 'banner_type');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="banner.php"');
$fld = $Src_frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons" ');
$fld->attachField($fld1);
$frm = getMBSFormByIdentifier('frmBanner');
$frm->setAction('?page=' . $page);
$fld = $frm->getField('banner_type');
$fld->requirements()->setRequired(true);
$fld->extra = 'onchange=changeBannerSizeValue();';
$fld->options = $banner_type;
$fld = $frm->getField('banner_size');
$fld->options = [6 => '1000x450', 1 => '1200x100', 2 => '277x120', 3 => '120x120', 4 => '1000x450', 5 => '360*590', 0 => '1000x450'];
$frm->getField('banner_url')->requirements()->setRequired(false);
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_SUBMIT');
$frm->addHiddenField('', 'status', $_REQUEST['status']);
$status = [0 => 'Inactive', 1 => 'Active'];
$fld1 = $frm->addSelectBox(t_lang('M_TXT_STATUS'), 'banner_active', $status);
$fld1->requirements()->setRequired(true);
$frm->changeFieldPosition($fld1->getFormIndex(), $fld->getFormIndex() - 1);
$fld = $frm->getField('banner_image');
//$fld->requirements()->setRequired();
$fld->extra = 'onchange="readURL(this);"';
$fld->html_after_field = ' <span class="spn_must_field">*</span><img alt="" src="' . CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $_REQUEST['edit'] . '&type=ADMINBANNERPAGE" class="deal_image">';
updateFormLang($frm);
if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
        $record = new TableRecord('tbl_banner');
        if (!$record->loadFromDb('banner_id=' . $_REQUEST['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if (isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if ($_FILES['banner_image']['tmp_name'] == "" && $post['banner_id'] == "") {
        $msg->addError(t_lang('M_TXT_BANNER_IMAGE_MISSING'));
        fillForm($frm, $arr);
        redirectUser(CONF_WEBROOT_URL . 'manager/banner.php?add=new');
    } else {
        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error)
                $msg->addError($error);
        } else {
            $succeed = true;
            /* Image Validations if uploaded */
            if (is_uploaded_file($_FILES['banner_image']['tmp_name'])) {
                $ext = strtolower(strrchr($_FILES['banner_image']['name'], '.'));
                if ((!in_array($ext, ['.gif', '.jpg', '.jpeg', '.png'])) || ($_FILES['banner_image']['size'] > CONF_IMAGE_MAX_SIZE)) {
                    $msg->addError(t_lang('M_TXT_BANNER') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                    fillForm($frm, $post);
                    $succeed = false;
                }
            }
            if (true === $succeed) {
                $record = new TableRecord('tbl_banner');
                $record->assignValues($post);
                $success = ($post['banner_id'] > 0) ? $record->update('banner_id' . '=' . $post['banner_id']) : $record->addNew();
                if ($success) {
                    $banner_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
                    if (is_uploaded_file($_FILES['banner_image']['tmp_name'])) {
                        $flname = time() . '_' . $_FILES['banner_image']['name'];
                        if (!move_uploaded_file($_FILES['banner_image']['tmp_name'], BANNER_IMAGES_PATH . $flname)) {
                            $msg->addError('File could not be saved.');
                        } else {
                            $db->update_from_array('tbl_banner', ['banner_image' => $flname], 'banner_id=' . $banner_id);
                            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                        }
                    }
                    redirectUser('?');
                } else {
                    $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                    fillForm($frm, $arr);
                }
            }
        }
    }
}
$srch = new SearchBase('tbl_banner', 'c');
if (isset($banner_type_id) && ($banner_type_id > 0)) {
    $srch->addCondition('banner_type', '=', $banner_type_id);
}
$srch->addOrder('banner_display_order', 'asc');
$rs_listing = $srch->getResultSet();
$pages = $srch->pages();
$arr_listing_fields = [
    'banner_image' => t_lang('M_TXT_IMAGE'),
    'banner_type' => t_lang('M_TXT_TYPE'),
    'banner_url' => t_lang('M_TXT_LINK'),
    'banner_active' => t_lang('M_TXT_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'banner.php' => t_lang('M_TXT_BANNER_MANAGEMENT')
];
?>
<script type="text/javascript">
    $(document).ready(function () {
<?php if (checkAdminAddEditDeletePermission(1, '', 'edit')) { ?>
            //Table DND call
            $('#banner-listing').tableDnD({
                onDrop: function (table, row) {
                    var order = $.tableDnD.serialize('id');
                    /*$('#msgbox').load("cms-ajax.php?" + order+"&mode=REORDER_NAVIGATION");
                     $.mbsmessage('Reordering Update!',true);*/
                    // $.mbsmessage('Updating display order....');
                    callAjax('cms-ajax.php', order + '&mode=REORDER_BANNER', function (t) {
                        /* $.mbsmessage(t,true); */
                    });
                }
            });
<?php } ?>
    });
    function changeBannerSizeValue() {
        $("#banner_size option").each(function (index) {
            $(this).css('display', 'block');
        })
        id = $('#banner_type').val();
        html = $("#banner_size").val(id).attr("selected");
        $("#banner_size option").each(function (index) {
            if ($(this).val() != id) {
                $(this).css('display', 'none');
            }
        })
    }
    $(window).load(function () {
        changeBannerSizeValue();
    })
</script>
</div>
</td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_BANNER_MANAGEMENT'); ?>
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="?add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            <?php } ?>
        </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                                return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        ?>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_BANNER_MANAGEMENT'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
    <?php } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_BANNER'); ?>  <?php echo t_lang('M_TXT_SEARCH'); ?>  </div><div class="content togglewrap" style="display:none;">	<?php echo $Src_frm->getFormHtml(); ?></div>	 </div>
        <table class="tbl_data" width="100%" id="banner-listing">
            <thead>
                <tr>
                    <?php
                    foreach ($arr_listing_fields as $val)
                        echo '<th>' . $val . '</th>';
                    ?>
                </tr>
            </thead>
            <?php
            while ($row = $db->fetch($rs_listing)) {
                echo '<tr id = ' . $row['banner_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'banner_type':
                            echo $banner_type[$row['banner_type']];
                            break;
                        case 'banner_image':
                            echo '<img src="' . BANNER_IMAGES_URL . $row['banner_image'] . '" width="50" height="50">';
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                                echo '<li><a href="?edit=' . $row['banner_id'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            } if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
                                echo '<li><a href="?delete=' . $row['banner_id'] . '" title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li>';
                            }
                            echo '</ul>';
                            break;
                        case 'banner_type':
                            echo $row['banner_type'];
                            break;
                        case 'banner_active':
                            echo $status[$row['banner_active']];
                            break;
                        default:
                            echo $row[$key];
                            break;
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            if ($db->total_records($rs_listing) == 0)
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            ?>
        </table>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>
