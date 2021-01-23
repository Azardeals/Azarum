<?php
require_once 'application-top.php';
require_once '../includes/navigation-functions.php';
loadModels(array('TaxRateModel'));
checkAdminPermission(4);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
/**
 * TAX RATE SEARCH FORM
 * */
$taxObj = new TaxRate();
$srchForm = TaxRate::getSearchForm();
/**
 * TAX RATE UPDATE FORM FILL UP
 * */
$frm = TaxRate::getForm();
updateFormLang($frm);
/**
 * TAX RATE DELETE CONDITION
 * */
if (is_numeric($_REQUEST['delete'])) {
    if (checkAdminAddEditDeletePermission(4, '', 'delete')) {
        if (true == $taxObj->canTaxRateDelete($_REQUEST['delete'])) {
            $taxObj->deleteTaxRate($_REQUEST['delete']);
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        }
        $msg->addError(t_lang('M_TXT_RECORD_CAN_NOT_DELETE') . '. ' . t_lang('M_TXT_MULTIPLE_TAX_CLASSES_ATTACH_WITH_THIS_TAX_RATE.'));
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * TAX RATE EDIT CONDITION
 * */
if (is_numeric($_REQUEST['edit'])) {
    if (checkAdminAddEditDeletePermission(4, '', 'edit')) {
        $srch = TaxRate::getSearchObject();
        $srch->addCondition(TaxRate::DB_TBL_PREFIX . 'id', '=', $_REQUEST['edit']);
        $rs = $srch->getResultSet();
        $record = $db->fetch($rs);
        if (!$record) {
            $msg->addError(t_lang('M_TXT_INVALID_REQUEST'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            $frm->addHiddenField('', 'taxrate_id', $_REQUEST['edit']);
            fillForm($frm, $record);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * TAX RATE POSTED FORM
 * */
if (isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $arrLangIndependentFlds = ['taxrate_id', 'taxrate_name', 'taxrate_tax_rate', 'taxrate_geozone_id', 'taxrate_active', 'btn_submit'];
        $success = $taxObj->addUpdateRecord($arrLangIndependentFlds, $post);
        if ($success) {
            $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE'));
            fillForm($frm, $arr);
        }
    }
}
/**
 * TAX RATES LISTING
 * */
$srch = TaxRate::getSearchObject();
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('taxrate_active', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('taxrate_active', '=', 0);
} else {
    $srch->addCondition('taxrate_active', '=', 1);
}
if ($_POST['zone']) {
    $srch->addCondition('taxrate_name', 'LIKE', '%' . $_POST['zone'] . '%');
}
$srch->joinTable('tbl_tax_geo_zones', 'INNER JOIN', 'gz.geozone_id=tr.taxrate_geozone_id', 'gz');
$srch->addMultipleFields(['gz.*', 'tr.*']);
$srch->addOrder('taxrate_name');
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 30;
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
/**
 * TAX RATES PAGINATION
 * */
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'zone'], ['page' => '', 'status' => $_REQUEST['status'], 'zone' => $_REQUEST['zone']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'taxrate_name' => t_lang('M_FRM_NAME'),
    'taxrate_tax_rate' => t_lang('M_TXT_TAX_RATE'),
    'taxrate_geozone_id' => t_lang('M_FRM_ZONE'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEALS') . '/' . t_lang('M_TXT_PRODUCTS'),
    '' => t_lang('M_TXT_TAX_RATE')
];
?>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="tax-rate.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_TAX_RATE_LISTING'); ?></a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="tax-rate.php?status=deactive"><?php echo t_lang('M_TXT_INACTIVE_TAX_RATE_LISTING'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_TAX_RATE'); ?>
            <?php if (checkAdminAddEditDeletePermission(4, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="?page=<?php echo $page; ?>&add=new" ><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') { ?>
        <?php if ((checkAdminAddEditDeletePermission(4, '', 'add')) || (checkAdminAddEditDeletePermission(4, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_TAX_RATE'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter">
            <div class="title"> <?php echo t_lang('M_TXT_TAX_RATE'); ?> </div>
            <div class="content togglewrap" style="display:none;">
                <?php echo $srchForm->getFormHtml(); ?>
            </div>
        </div>
        <table class="tbl_data" width="100%">
            <thead>
                <tr>
                    <?php
                    foreach ($arr_listing_fields as $val) {
                        echo '<th>' . $val . '</th>';
                    }
                    ?>
                </tr>
            </thead>
            <?php
            while ($row = $db->fetch($rs_listing)) {
                echo '<tr' . (($row['zone_active'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'taxrate_name':
                            echo $row['taxrate_name'] . '<br/>';
                            break;
                        case 'taxrate_tax_rate':
                            echo $row['taxrate_tax_rate'] . ' % <br/>';
                            break;
                        case 'taxrate_geozone_id':
                            echo $row['geozone_name'] . '<br/>';
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if ($_REQUEST['status'] == 'active') {
                                if (checkAdminAddEditDeletePermission(4, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['taxrate_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li> ';
                                }
                                if (checkAdminAddEditDeletePermission(4, '', 'delete')) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteZone(' . $row['taxrate_id'] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            } else if ($_REQUEST['status'] == 'deleted') {
                                if (checkAdminAddEditDeletePermission(4, '', 'delete')) {
                                    echo '<li><a href="?restore=' . $row['taxrate_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_RESTORE') . '"><i class="ion-archive icon"></i></a></li>';
                                    echo '<li><a href="?deletePer=' . $row['taxrate_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE_PERMANENTLY') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-ios-trash icon"></i></a></li>';
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(4, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row['taxrate_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(4, '', 'delete')) {
                                    echo '<li><a href="?delete=' . $row['taxrate_id'] . '&page=' . $page . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                                }
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
            if ($db->total_records($rs_listing) == 0) {
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </table>
    <?php } ?>
    <?php if (!isset($_GET['edit']) && $_GET['add'] != 'new' && ($srch->pages() > 1)) { ?>
        <div class="footinfo">
            <aside class="grid_1"><?php echo $pagestring; ?></aside>
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>
