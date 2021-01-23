<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!(isCompanyUserLogged())) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$company_id = intval($_SESSION['logged_user']['company_id']);
if (isset($_GET['company_id']) && ($company_id != $_GET['company_id'])) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/logout.php');
}
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 10;
$mainTableName = 'tbl_company_addresses';
$primaryKey = 'company_address_id';
$colPrefix = 'company_address_';
if (is_numeric($_GET['delete'])) {
    $merchant_company_id = $company_id;
    $srch_address = new SearchBase('tbl_deal_address_capacity', 'dac');
    $srch_address->addCondition('dac_address_id', '=', $_GET['delete']);
    $rs_listing_address = $srch_address->getResultSet();
    $row_address = $db->fetch_all($rs_listing_address);
    $count_row_address = count($row_address);
    if ($count_row_address == 0) {
        if (!$db->deleteRecords($mainTableName, array('smt' => 'company_address_id = ? AND company_id = ?', 'vals' => array(intval($_GET['delete']), $company_id), 'execute_mysql_functions' => false))) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
            redirectUser('?company_id=' . $merchant_company_id . '&page=' . $page);
        }
    }
}
$frm = getMBSFormByIdentifier('frmCompanyAddresses');
$fld = $frm->getField('company_id');
$fld->value = $company_id;
$frm->setAction('?company_id=' . $company_id . '&page=' . $page);
$fld = $frm->getField('company_address_google_map');
$frm->removeField($fld);
updateFormLang($frm);
$fld = $frm->getField('submit');
$fld->value = t_lang('M_TXT_SUBMIT');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord($mainTableName);
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = array('company_id', 'company_address_id', 'company_address_zip', 'company_address_google_map', 'mode', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        $record->setFldValue('company_id', $company_id);
        $success = ($post[$primaryKey] > 0) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew();
        if ($success) {
            $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser();
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            /* $frm->fill($post); */
            fillForm($frm, $post);
        }
    }
}
if (is_numeric($_GET['edit'])) {
    $record = new TableRecord($mainTableName);
    if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
        $msg->addError($record->getError());
    } else {
        $arr = $record->getFlds();
        $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
        fillForm($frm, $arr);
        /*  $frm->fill($arr); */
        $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
    }
}
$srch = new SearchBase('tbl_company_addresses', 'ca');
$srch->addCondition('company_id', '=', intval($company_id));
$srch->addMultipleFields(array('ca.*'));
$srch->addFld("CONCAT(company_address_line1, '<br/>', company_address_line2, '<br/>', company_address_line3,  '-', company_address_zip, ' ') AS address");
$srch->addFld("CONCAT(company_address_line1_lang1, '<br/>', company_address_line2_lang1, '<br/>', company_address_line3_lang1,  '-', company_address_zip, ' ') AS address_lang1");
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="?company_id=' . $company_id . '&page=xxpagexx">xxpagexx</a> </li>'
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SR_NO'),
    'address' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_ADDRESS'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    '' => t_lang('M_TXT_COMPANY_ADDRESSES'),
);
?>
<td class="right-portion"> 
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_COMPANY_ADDRESSES'); ?>
            <ul class="actions right">
                <li class="droplink">
                    <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                    <div class="dropwrap">
                        <ul class="linksvertical">
                            <li>
                                <a href="?company_id=<?php echo $company_id ?>&page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div>
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
    <?php if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') { ?>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_COMPANY_ADDRESSES'); ?> </div><div class="content">	<?php echo $frm->getFormHtml(); ?></div>	 </div>	 
    <?php } else { ?>
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
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                $srch_addresses = new SearchBase('tbl_deal_address_capacity', 'dac');
                $srch_addresses->addCondition('dac_address_id', '=', $row['company_address_id']);
                $rs_listing_addresses = $srch_addresses->getResultSet();
                $row_addresses = $db->fetch_all($rs_listing_addresses);
                $count_row_addresses = count($row_addresses);
                $even = ($listserial % 2 == 0) ? 'even' : '';
                echo '<tr class=" ' . $even . ' ">';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'address_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['address'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['address_lang1'];
                            break;
                        case 'action':
                            echo '<a href="?company_id=' . $company_id . '&edit=' . $row[$primaryKey] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '" class="btn gray">' . t_lang('M_TXT_EDIT') . '</a> ';
                            if ($count_row_addresses == 0) {
                                echo '<a href="?company_id=' . $company_id . '&delete=' . $row[$primaryKey] . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);" class="btn delete">' . t_lang('M_TXT_DELETE') . '</a> ';
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

    require_once './footer.php';

    