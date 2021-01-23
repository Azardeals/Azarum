<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
if ($_SESSION['cityname'] != "") {
    $cityname = convertStringToFriendlyUrl($_SESSION['cityname']);
} else {
    $cityname = 1;
}
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
if (is_numeric($_GET['inactive'])) {
    if (!$db->update_from_array('tbl_company_charity', array('charity_status' => 0), 'charity_id=' . $_GET['inactive'])) {
        $msg->addError($db->getError());
    } else {
        $msg->addMsg(t_lang('M_TXT_CHARITY_UPDATED'));
        redirectUser('?page=' . $page);
    }
}
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    $charity_id = $_GET['delete'];
    $rs = $db->query("select deal_charity from tbl_deals where deal_charity=" . $_GET['delete']);
    $rowCharity = $db->fetch($rs);
    $rs1 = $db->query("select ch_charity_id from tbl_charity_history where ch_charity_id=" . $_GET['delete']);
    $rowCharity1 = $db->fetch($rs1);
    if (($rowCharity['deal_charity'] != $_GET['delete']) && ($rowCharity1['ch_charity_id'] != $_GET['delete'])) {
        $db->query("delete from tbl_company_charity  WHERE charity_id =$charity_id and charity_status=2");
        $msg->addMsg("Record deleted successfully.");
        redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$frm = getMBSFormByIdentifier('frmComapnyCharity');
$frm->setLeftColumnProperties('class=""');
$frm->setAction('?page=' . $page);
$fld = $frm->getField('charity_contact_person');
$frm->removeField($fld);
$fld = $frm->getField('charity_percentage');
$frm->removeField($fld);
$frm->addHiddenField('', 'page', $_GET['page']);
$frm->addHiddenField('', 'status', $_GET['status']);
$fld1 = $frm->getField('submit');
$fld1->value = t_lang('M_TXT_SUBMIT');
updateFormLang($frm);
$selected_state = 0;
if (is_numeric($_GET['edit'])) {
    $record = new TableRecord('tbl_company_charity');
    if (!$record->loadFromDb('charity_status = 2 and charity_id=' . $_GET['edit'], true)) {
        $msg->addError($record->getError());
    } else {
        $arr = $record->getFlds();
        $rs = $db->query("select state_country from tbl_states where state_id=" . $arr['charity_state']);
        $row = $db->fetch($rs);
        $arr['charity_country'] = $row['state_country'];
        $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
        $selected_state = $arr['charity_state'];
        fillForm($frm, $arr);
        $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
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
        if (is_uploaded_file($_FILES['charity_logo']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['charity_logo']['name'], '.'));
            if ((!in_array($ext, array('.gif', '.jpg', '.jpeg', '.png'))) || ($_FILES['charity_logo']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_CHARITY_LOGO') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        if (true === $succeed) {
            $record = new TableRecord('tbl_company_charity');
            $arr_lang_independent_flds = array('charity_id', 'charity_company_id', 'charity_user_id', 'charity_state', 'charity_country', 'charity_zip', 'charity_status', 'charity_added_on', 'charity_approved_by', 'charity_phone', 'charity_email_address', 'charity_percentage', 'mode', 'btn_submit');
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            $record->setFldValue('charity_status', 2, '');
            $record->setFldValue('charity_company_id', $_SESSION['logged_user']['company_id'], '');
            $record->setFldValue('charity_added_on', date('Y-m-d H:i:s'), true);
            $success = ($post['charity_id'] > 0) ? $record->update('charity_id=' . $post['charity_id']) : $record->addNew();
            $charity_id = ($post['charity_id'] > 0) ? $post['charity_id'] : $record->getId();
            if (is_uploaded_file($_FILES['charity_logo']['tmp_name'])) {
                $flname = time() . '_' . $_FILES['charity_logo']['name'];
                if (!move_uploaded_file($_FILES['charity_logo']['tmp_name'], CHARITY_IMAGES_PATH . $flname)) {
                    $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                } else {
                    $getImg = $db->query("select * from tbl_company_charity where charity_id='" . $charity_id . "'");
                    $imgRow = $db->fetch($getImg);
                    unlink(CHARITY_IMAGES_PATH . $imgRow['charity_logo' . $_SESSION['lang_fld_prefix']]);
                    $db->update_from_array('tbl_company_charity', array('charity_logo' . $_SESSION['lang_fld_prefix'] => $flname), 'charity_id=' . $charity_id);
                }
            }
            if ($success) {
                $succ_message = T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL');
                if ($post['charity_id'] <= 0) {
                    $succ_message .= ' ' . T_lang('M_TXT_Admin_approval_pending');
                }
                $msg->addMsg($succ_message);
                /* Notify Admin  */
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
                $fromemail = $_SESSION['logged_user']['company_email'];
                $fromname = $_SESSION['logged_user']['company_name'];
                $headers .= "From: " . $fromname . " <" . $fromemail . ">\r\n";
                $rs = $db->query("select * from tbl_email_templates where tpl_id=31");
                $row_tpl = $db->fetch($rs);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxname_of_companyxx' => $fromname,
                    'xxcharity_namexx' => $post['charity_name' . $_SESSION['lang_fld_prefix']],
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
                redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($frm, $post);
            }
        }
    }
}
$srch = new SearchBase('tbl_company_charity', 'c');
if ($_GET['status'] == 'active') {
    $srch->addCondition('charity_status', '=', 1);
} else if ($_GET['status'] == 'deactive') {
    $srch->addCondition('charity_status', '=', 0);
} else if ($_GET['status'] == 'un-approved') {
    $srch->addCondition('charity_status', '=', 2);
} else {
    $srch->addCondition('charity_status', '=', 1);
}
$srch->addCondition('charity_company_id', '=', $_SESSION['logged_user']['company_id']);
$srch->addOrder('charity_name');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="?status=' . $_REQUEST['status'] . '&page=xxpagexx" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'charity_logo' => t_lang('M_TXT_ORGANIZATION'),
    'charity_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'charity_added_on' => t_lang('M_TXT_ADDED_ON'),
    'charity_total_donations' => t_lang('M_TXT_TOTAL_DONATIONS'),
    'charity_total_payout' => t_lang('M_TXT_TOTAL_PAYOUTS'),
    'charity_balance' => t_lang('M_TXT_BALANCE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
echo '<script language="javascript">
selectedState=' . $selected_state . '
</script>';
$arr_bread = array('' => t_lang('M_TXT_CHARITY'));
?>
<script type="text/javascript">
    var txtselectcountry = "<?php echo addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')); ?>";
    var txtload = "<?php echo addslashes(t_lang('M_TXT_LOADING')); ?>";
</script>
<ul class="nav-left-ul">
    <li><a <?php if ($_GET['status'] == 'active') echo 'class="selected"'; ?> href="charity.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_CHARITY_LISTING'); ?></a></li>
    <li><a <?php if ($_GET['status'] == 'deactive') echo 'class="selected"'; ?> href="charity.php?status=deactive"><?php echo t_lang('M_TXT_INACTIVE_CHARITY_LISTING'); ?></a></li>
    <li><a <?php if ($_GET['status'] == 'requested') echo 'class="selected"'; ?> href="charity.php?status=un-approved"><?php echo t_lang('M_TXT_UNAPPROVED_CHARITY_LISTING'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_CHARITY'); ?>
            <ul class="actions right">
                <li class="droplink">
                    <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                    <div class="dropwrap">
                        <ul class="linksvertical">
                            <li> 
                                <a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_CHARITY'); ?></a>
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
    <?php if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') { ?>
        <script type="text/javascript">
            $(document).ready(function () {
                updateStates(document.frmComapnyCharity.charity_country.value);
            });</script>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_CHARITY'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
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
            while ($row = $db->fetch($rs_listing)) {
                $charity_id = $row['charity_id'];
                echo '<tr' . (($row['charity_status'] == 0) ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'charity_logo':
                            if ($row['charity_logo' . $_SESSION['lang_fld_prefix']] == '') {
                                echo '<img src="' . CONF_WEBROOT_URL . 'deal-image.php?charity=' . $row['charity_id'] . '&mode=charitythumbImages' . '"  />';
                            } else {
                                echo '<img src="' . CONF_WEBROOT_URL . 'deal-image.php?charity=' . $row['charity_id'] . '&mode=charitythumbImages' . '"  />';
                            }
                            break;
                        case 'charity_name_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['charity_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['charity_name_lang1'];
                            break;
                        case 'charity_added_on':
                            echo displayDate($row[$key], true);
                            break;
                        case 'charity_total_donations':
                            $rs2 = $db->query("select sum(ch_amount) as totalamount from tbl_charity_history where ch_charity_id=" . $row['charity_id']);
                            $rowCharity2 = $db->fetch($rs2);
                            echo CONF_CURRENCY . number_format(($rowCharity2['totalamount']), 2) . CONF_CURRENCY_RIGHT . '<br/><br/><a href="charity-history.php?charity=' . $row['charity_id'] . '">' . t_lang('M_TXT_DETAILS') . '</a>';
                            break;
                        case 'charity_total_payout':
                            $rs2 = $db->query("select sum(ch_debit) as totalamount from tbl_charity_history where ch_charity_id=" . $row['charity_id']);
                            $rowCharity2 = $db->fetch($rs2);
                            echo CONF_CURRENCY . number_format(($rowCharity2['totalamount']), 2) . CONF_CURRENCY_RIGHT;
                            break;
                        case 'charity_balance':
                            $rs2 = $db->query("select (sum(ch_amount)-sum(ch_debit)) as totalamount from tbl_charity_history where ch_charity_id=" . $row['charity_id']);
                            $rowCharity2 = $db->fetch($rs2);
                            echo CONF_CURRENCY . number_format(($rowCharity2['totalamount']), 2) . CONF_CURRENCY_RIGHT;
                            break;
                        case 'action':
                            $rs = $db->query("select deal_charity from tbl_deals where deal_charity=" . $charity_id);
                            $rowCharity = $db->fetch($rs);
                            $rs1 = $db->query("select ch_charity_id from tbl_charity_history where ch_charity_id=" . $charity_id);
                            $rowCharity1 = $db->fetch($rs1);
                            if (($rowCharity['deal_charity'] != $charity_id) && ($rowCharity1['ch_charity_id'] != $charity_id)) {
                                $deleteRow = '<a href="?status=' . $_GET['status'] . '&delete=' . $charity_id . '&page=' . $page . '" title="Delete" onclick="requestPopup(this,\'' . t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE') . '\',1);" class="btn delete">' . t_lang('M_TXT_DELETE') . '</a> ';
                            } else {
                                $deleteRow = '';
                            }
                            if ($_GET['status'] == 'un-approved') {
                                echo '<a href="?status=' . $_GET['status'] . '&edit=' . $charity_id . '&page=' . $page . '" title="Edit" class="btn gray">' . t_lang('M_TXT_EDIT') . '</a> ';
                                echo $deleteRow;
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
    ?>
</td>
<script type="text/javascript">
    $("document").ready(function () {
        if ($('.btn').length <= 0) {
            $('.tbl_data').find('th:last').remove();
            $('.tbl_data').find('tr').each(function () {
                $(this).find('td:last').remove();
            });
        }
    });
</script>
<?php
require_once './footer.php';
