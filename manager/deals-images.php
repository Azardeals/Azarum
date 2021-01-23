<?php
require_once './application-top.php';
checkAdminPermission(5);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 30;
$mainTableName = 'tbl_deals_images';
$primaryKey = 'dimg_id';
$colPrefix = 'dimg_';
$frm = getMBSFormByIdentifier('frmDealImage');
$fld = $frm->getField('dimg_deal_id');
$fld->value = $_GET['deal_id'];
$fld = $frm->getField('dimg_name');
$fld->requirements()->setRequired(true);
$fld = $frm->getField('dimg_thumb_name');
$frm->removeField($fld);
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
        $record = new TableRecord($mainTableName);
        if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            fillForm($frm, $arr);
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(5, '', 'delete')) {
        $imgName = $db->query('select * from ' . $mainTableName . ' where dimg_id=' . $primaryKey);
        $img = $db->fetch($imgName);
        unlink(DEAL_IMAGES_PATH . $img['dimg_name']);
        $db->query('delete from ' . $mainTableName . ' where dimg_id=' . $_GET['delete']);
        $msg->addMsg(t_lang('M_TXT_IMAGE_DELETED'));
        redirectUser('deals-images.php?deal_id=' . $_GET['deal_id']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (isset($_FILES['dimg_name']['name']) && $_FILES['dimg_name']['error'] == 0) {
        $post['dimg_name'] = $_FILES['dimg_name']['name'];
    }
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord($mainTableName);
        if (is_uploaded_file($_FILES['dimg_name']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['dimg_name']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['dimg_name']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_DEAL') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                $success = false;
            } else {
                $arr_lang_independent_flds = array('dimg_id', 'dimg_deal_id', 'mode', 'btn_submit');
                assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
                $record->setFldValue('dimg_deal_id', $_GET['deal_id']);
                $success = ($post[$primaryKey] > 0) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew();
            }
        }
        if (true === $success) {
            $dimg_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            if (is_uploaded_file($_FILES['dimg_name']['tmp_name'])) {
                $flname = time() . '_' . $_FILES['dimg_name']['name'];
                if (!move_uploaded_file($_FILES['dimg_name']['tmp_name'], DEAL_IMAGES_PATH . $flname)) {
                    $msg->addError('File could not be saved.');
                } else {
                    $db->update_from_array('tbl_deals_images', array('dimg_name' => $flname), 'dimg_id=' . $dimg_id);
                    $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                }
            } else {
                $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            }
            redirectUser('deals-images.php?deal_id=' . $post['dimg_deal_id']);
        } else {
            $msg->addError(t_lang('M_TXT_FAILED_TO_PERFORM_ACTION') . ' ' . $record->getError());
            $frm->fill($post);
        }
    }
}
$srch = new SearchBase('tbl_deals_images', 'd');
$srch->addCondition('dimg_deal_id', '=', $_GET['deal_id']);
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="?deal_id=' . $_REQUEST['deal_id'] . '&page=xxpagexx"  >xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SN_NO'),
    'image' => t_lang('M_TXT_IMAGE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEALS'),
    'deals.php' => t_lang('M_TXT_DEAL_IMAGES')
);
if ($_REQUEST['status'] == "") {
    $class = 'class="active"';
} else {
    $tabStatus = $_REQUEST['status'];
    $tabClass = 'class="active"';
}
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_DEAL_IMAGES'); ?>
            <?php if (checkAdminAddEditDeletePermission(5, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li><a href="?deal_id=<?php echo $_GET['deal_id']; ?>&page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a></li>
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
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
        if ((checkAdminAddEditDeletePermission(5, '', 'add')) || (checkAdminAddEditDeletePermission(5, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_DEAL_IMAGES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
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
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'image':
                            if ($row['dimg_name'] != "") {
                                echo '<img src="' . DEAL_IMAGES_URL . $row['dimg_name'] . '" width="50" height="50" border="0">';
                            } else {
                                echo 'Image is not uploded.';
                            }
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                echo ' <li><a href="?deal_id=' . $row['dimg_deal_id'] . '&edit=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if (checkAdminAddEditDeletePermission(5, '', 'delete')) {
                                echo '<li><a href="?deal_id=' . $row['dimg_deal_id'] . '&delete=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_DELETE') . '"><i class="ion-android-delete icon"></i></a></li>';
                            }
                            echo '</ul>';
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
        <?php if ($srch->pages() > 1) { ?>
            <div class="footinfo">
                <aside class="grid_1">
                    <?php echo $pagestring; ?>
                </aside>
                <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
            </div>
            <?php
        }
    }
    ?>
</td>
<?php
require_once './footer.php';
?>
