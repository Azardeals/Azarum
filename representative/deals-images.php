<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isRepresentativeUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');
}
$rep_id = $_SESSION['logged_user']['rep_id'];
$rsc = $db->query("SELECT * FROM `tbl_companies` WHERE company_rep_id=$rep_id");
$companyArray = array();
while ($arrs = $db->fetch($rsc)) {
    $companyArray[$arrs['company_id']] = $arrs['company_id'];
}
$check = $db->query('select * from tbl_deals where deal_status = 5 and deal_company IN ' . $companyArray . ' and  deal_id=' . $_GET['deal_id']);
$rowCheck = $db->fetch($check);
if ($db->total_records($check) == 0) {
    die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
}
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 10;
$mainTableName = 'tbl_deals_images';
$primaryKey = 'dimg_id';
$colPrefix = 'dimg_';
$frm = getMBSFormByIdentifier('frmDealImage');
//$frm->setAction(friendlyUrl(CONF_WEBROOT_URL.'deals-images.php?deal_id='.$post['dimg_deal_id'].'&'));
$fld = $frm->getField('submit');
$frm->addSubmitButton('', 'submit', t_lang('M_TXT_SUBMIT'), 'submit', 'class="inputbuttons"');
$frm->removeField($fld);
$fld = $frm->getField('dimg_deal_id');
$fld->value = $_GET['deal_id'];
if (is_numeric($_GET['delete'])) {
    $record = new TableRecord('tbl_deals');
    if (!$record->loadFromDb('deal_id' . '=' . $_GET['deal_id'] . '&& deal_company IN ' . $companyArray . ' && deal_status = 5 ', true)) {
        $msg->addError($record->getError());
    } else {
        $imgName = $db->query('select * from ' . $mainTableName . ' where dimg_id=' . $_GET['delete']);
        $img = $db->fetch($imgName);
        unlink(DEAL_IMAGES_PATH . $img['dimg_name']);
        $db->query('delete from tbl_deals_images where dimg_id=' . $_GET['delete']);
        $msg->addMsg(t_lang('M_TXT_IMAGE_DELETED'));
        redirectUser(CONF_WEBROOT_URL . 'merchant/deals-images.php?deal_id=' . $_GET['deal_id'] . '&page=' . $page . '&');
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $succeed = true;
        /* Image Validations if uploaded */
        if (is_uploaded_file($_FILES['dimg_name']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['dimg_name']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['dimg_name']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_DEAL') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                $succeed = false;
            }
        }
        if (true === $succeed) {
            if (is_uploaded_file($_FILES['dimg_thumb_name']['tmp_name'])) {
                $ext = strtolower(strrchr($_FILES['dimg_thumb_name']['name'], '.'));
                if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['dimg_thumb_name']['size'] > CONF_IMAGE_MAX_SIZE)) {
                    $msg->addError(t_lang('M_TXT_DEAL_THUMB') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                    $succeed = false;
                }
            }
        }
        if (true === $succeed) {
            $record = new TableRecord($mainTableName);
            $record->assignValues($post);
            $record->setFldValue('dimg_deal_id', $_GET['deal_id']);
            $success = ($post[$primaryKey] > 0) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew();
            if ($success) {
                $dimg_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
                if (is_uploaded_file($_FILES['dimg_name']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['dimg_name']['name'];
                    if (!move_uploaded_file($_FILES['dimg_name']['tmp_name'], DEAL_IMAGES_PATH . $flname)) {
                        $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                    } else {
                        $db->update_from_array('tbl_deals_images', array('dimg_name' => $flname), 'dimg_id=' . $dimg_id);
                    }
                }
                if (is_uploaded_file($_FILES['dimg_thumb_name']['tmp_name'])) {
                    $flname = time() . '_' . $_FILES['dimg_thumb_name']['name'];
                    if (!move_uploaded_file($_FILES['dimg_thumb_name']['tmp_name'], DEAL_IMAGES_PATH . $flname)) {
                        $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                    } else {
                        $db->update_from_array('tbl_deals_images', array('dimg_thumb_name' => $flname), 'dimg_id=' . $dimg_id);
                    }
                }
                $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser(CONF_WEBROOT_URL . 'merchant/deals-images.php?deal_id=' . $post['dimg_deal_id'] . '&page=' . $page . '&');
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                redirectUser(CONF_WEBROOT_URL . 'merchant/deals-images.php?deal_id=' . $post['dimg_deal_id'] . '&page=' . $page . '&');
            }
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
if ($pages > 1) {
    $pagestring .= '<div class="pagination fr"><ul><li><a href="javascript:void(0);">Displaying records ' . (($page - 1) * $pagesize + 1) .
            ' to ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' of ' . $srch->recordCount() . '</a></li>';
    $pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO_PAGE') . '</a></li>
	' . getPageString('<li><a href="?deal_id=' . $_REQUEST['deal_id'] . '&page=xxpagexx"  >xxpagexx</a> </li> '
                    , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
    $pagestring .= '</div>';
}
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SN_NO'),
    'image' => t_lang('M_TXT_IMAGE'),
    'thumb' => t_lang('M_FRM_THUMB_IMAGE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
?>
<!--body start here-->
<td class="right-portion"><?php //echo getAdminBreadCrumb($arr_bread);            ?>
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
        ?>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_DEAL_IMAGES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
        <?php
    } else {
        ?>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_DEAL_IMAGES'); ?> </div><div class="content">		<?php //echo $Src_frm->getFormHtml();         ?>
        <?php echo $pagestring; ?>		
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
                        echo t_lang('M_TXT_IMAGE') . t_lang('M_TXT_NOT_UPLODED');
                    }
                    break;
                case 'thumb':
                    if ($row['dimg_thumb_name'] != "") {
                        echo '<img src="' . DEAL_IMAGES_URL . $row['dimg_thumb_name'] . '" width="50" height="50" border="0">';
                    } else {
                        echo t_lang('M_FRM_THUMB_IMAGE') . t_lang('M_TXT_NOT_UPLODED');
                    }
                    break;
                case 'action':
                    echo'<a href="' . CONF_WEBROOT_URL . 'merchant/deals-images.php?deal_id=' . $row['dimg_deal_id'] . '&delete=' . $row['dimg_id'] . '&' . '"  title="' . t_lang('M_TXT_DELETE') . '" class="btn delete">' . t_lang('M_TXT_DELETE') . '</a> ';
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
                <div style="padding-top:5px;" ><a href="?deal_id=<?php echo $_GET['deal_id']; ?>&page=<?php echo $page; ?>&add=new" class="btn green fr"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a></div>
                <div class="clear"></div>
            </div></div>
<?php } ?>
</td> <?php
    require_once './footer.php';
    