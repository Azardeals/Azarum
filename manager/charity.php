<?php
require_once './application-top.php';
checkAdminPermission(8);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$post = getPostedData();
$get = getQueryStringData();
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="charity.php"');
$fld = $Src_frm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
if (is_numeric($_GET['request'])) {
    if (!$db->update_from_array('tbl_company_charity', ['charity_status' => 1], 'charity_id=' . $_GET['request'])) {
        $msg->addError($db->getError());
    } else {
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
        redirectUser('?page=' . $page);
    }
}
if (is_numeric($_GET['inactive'])) {
    if (!$db->update_from_array('tbl_company_charity', ['charity_status' => 0], 'charity_id=' . $_GET['inactive'])) {
        $msg->addError($db->getError());
    } else {
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
        redirectUser('?page=' . $page);
    }
}
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    $rs = $db->query("select deal_charity from tbl_deals where deal_charity=" . $_GET['delete']);
    $rowCharity = $db->fetch($rs);
    $rs1 = $db->query("select ch_charity_id from tbl_charity_history where ch_charity_id=" . $_GET['delete']);
    $rowCharity1 = $db->fetch($rs1);
    if (($rowCharity['deal_charity'] != $_GET['delete']) && ($rowCharity1['ch_charity_id'] != $_GET['delete'])) {
        if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
            $charity_id = $_GET['delete'];
            $db->query("delete from tbl_company_charity  WHERE charity_id =$charity_id ");
            $msg->addMsg(t_lang("M_TXT_RECORD_DELETED"));
            redirectUser('?page=' . $page);
        } else {
            die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
        }
    } else {
        die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}
$frm = getMBSFormByIdentifier('frmComapnyCharity');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setLeftColumnProperties('class=""');
$frm->setAction('?page=' . $page);
$fld = $frm->getField('charity_contact_person');
$frm->removeField($fld);
$fld = $frm->getField('charity_logo');
$fld->extra = 'onchange="readURL(this);"';
$src = CHARITY_IMAGES_URL . 'no-image.jpg';
if (is_numeric($_GET['edit'])) {
    $src = CONF_WEBROOT_URL . 'deal-image.php?charity=' . $_GET['edit'] . '&mode=charitythumbImages';
}
$fld->html_after_field = '  <img class="deal_image" alt="" src="' . $src . '" >';
$rscountry = $db->query("select country_id, country_name" . $_SESSION['lang_fld_prefix'] . "  from tbl_countries where country_status='A' order by country_name");
$countryArray = [];
while ($arrs = $db->fetch($rscountry)) {
    $countryArray[$arrs['country_id']] = $arrs['country_name' . $_SESSION['lang_fld_prefix']];
}
$fld = $frm->getField('charity_percentage');
$frm->removeField($fld);
$fld = $frm->getField('charity_country');
$fld->selectCaption = t_lang('M_TXT_SELECT');
$fld->options = $countryArray;
$fld1 = $frm->getField('submit');
$fld1->value = t_lang('M_TXT_SUBMIT');
updateFormLang($frm);
$selected_state = 0;
if (is_numeric($_GET['edit'])) {
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        $record = new TableRecord('tbl_company_charity');
        if (!$record->loadFromDb('charity_id=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $rs = $db->query("select state_country from tbl_states where state_id=" . $arr['charity_state']);
            $row = $db->fetch($rs);
            $arr['charity_country'] = $row['state_country'];
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $selected_state = $arr['charity_state'];
            fillForm($frm, $arr);
            /*  $frm->fill($arr); */
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
    }
}
if (isset($_POST['submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $succeed = true;
        /* Image Validations if uploaded */
        if (is_uploaded_file($_FILES['charity_logo']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['charity_logo']['name'], '.'));
            if ((!in_array($ext, ['.gif', '.jpg', '.jpeg', '.png'])) || ($_FILES['charity_logo']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_CHARITY') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                fillForm($frm, $post);
                $succeed = false;
            }
        }
        if (true === $succeed) {
            $record = new TableRecord('tbl_company_charity');
            $arr_lang_independent_flds = ['charity_id', 'charity_company_id', 'charity_user_id', 'charity_state', 'charity_country', 'charity_zip', 'charity_status', 'charity_added_on', 'charity_approved_by', 'charity_phone', 'charity_email_address', 'charity_percentage', 'mode', 'btn_submit'];
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            if ((checkAdminAddEditDeletePermission(8, '', 'edit'))) {
                if ($post['charity_id'] > 0)
                    $success = $record->update('charity_id' . '=' . $post['charity_id']);
            }
            if ((checkAdminAddEditDeletePermission(8, '', 'add'))) {
                $record->setFldValue('charity_status', 1, '');
                $record->setFldValue('charity_user_id', $_SESSION['logged_user']['user_id'], '');
                $record->setFldValue('charity_added_on', date('Y-m-d H:i:s'), true);
                if ($post['charity_id'] == '')
                    $success = $record->addNew();
            }
            $charity_id = ($post['charity_id'] > 0) ? $post['charity_id'] : $record->getId();
            if (is_uploaded_file($_FILES['charity_logo']['tmp_name'])) {
                $flname = time() . '_' . $_FILES['charity_logo']['name'];
                if (!move_uploaded_file($_FILES['charity_logo']['tmp_name'], CHARITY_IMAGES_PATH . $flname)) {
                    $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                } else {
                    $getImg = $db->query("select * from tbl_company_charity where charity_id='" . $charity_id . "'");
                    $imgRow = $db->fetch($getImg);
                    unlink(CHARITY_IMAGES_PATH . $imgRow['charity_logo' . $_SESSION['lang_fld_prefix']]);
                    $db->update_from_array('tbl_company_charity', ['charity_logo' => $flname], 'charity_id=' . $charity_id);
                }
            }
            if ($success) {
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                fillForm($frm, $post);
            }
        }
    }
}
$srch = new SearchBase('tbl_company_charity', 'c');
if ($_REQUEST['status'] == 'active') {
    $srch->addCondition('charity_status', '=', 1);
} else if ($_REQUEST['status'] == 'deactive') {
    $srch->addCondition('charity_status', '=', 0);
} else if ($_REQUEST['status'] == 'un-approved') {
    $srch->addCondition('charity_status', '=', 2);
} else {
    $srch->addCondition('charity_status', '=', 1);
}
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('c.charity_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.charity_city' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.charity_email_address', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.charity_address1' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.charity_address2' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('c.charity_address3' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $Src_frm->fill($post);
}
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
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_SR_NO'),
    'charity_logo' => t_lang('M_TXT_ORGANIZATION'),
    'charity_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'charity_added_on' => t_lang('M_TXT_ADDED_ON'),
    'charity_total_donations' => t_lang('M_TXT_TOTAL_DONATIONS'),
    'charity_total_payout' => t_lang('M_TXT_TOTAL_PAYOUTS'),
    'charity_balance' => t_lang('M_TXT_BALANCE'),
    /*  'charity_approved_by' => t_lang('M_TXT_APPROVED_BY'), */
    'charity_company_id' => t_lang('M_TXT_SUGGESTED_BY'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_USERS'),
    '' => t_lang('M_TXT_CHARITY')
];
echo '<script language="javascript">
	selectedState=' . $selected_state . '
	</script>';
?>
<script type ="text/javascript">
    var selectCountryFirst = "<?php echo addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')); ?>";
    var txtloading = "<?php echo addslashes(t_lang('M_TXT_LOADING')); ?>";
</script>
<ul class="nav-left-ul">
    <li><a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="charity.php?status=active"><?php echo t_lang('M_TXT_ACTIVE_CHARITY_LISTING'); ?></a></li>
    <li><a <?php if ($_REQUEST['status'] == 'deactive') echo 'class="selected"'; ?> href="charity.php?status=deactive"><?php echo t_lang('M_TXT_INACTIVE_CHARITY_LISTING'); ?></a></li>
    <li><a <?php if ($_REQUEST['status'] == 'requested') echo 'class="selected"'; ?> href="charity.php?status=un-approved"><?php echo t_lang('M_TXT_UNAPPROVED_CHARITY_LISTING'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_CHARITY'); ?> 
            <?php if (checkAdminAddEditDeletePermission(8, '', 'add')) { ?> 
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
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                updateStates(document.frmComapnyCharity.charity_country.value);
            });</script>
        <?php if ((checkAdminAddEditDeletePermission(8, '', 'add')) || (checkAdminAddEditDeletePermission(8, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_CHARITY'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang("M_TXT_UNAUTHORIZED_ACCESS"));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_CHARITY'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $Src_frm->getFormHtml(); ?></div></div>	
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
            $balance = 0;
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                $charity_id = $row['charity_id'];
                echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['cat_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'charity_logo':
                            if ($row['charity_logo'] == '') {
                                echo '<img src="' . CONF_WEBROOT_URL . 'deal-image.php?charity=' . $row['charity_id'] . '&mode=charitythumbImages' . '"   />';
                            } else {
                                echo '<img src="' . CONF_WEBROOT_URL . 'deal-image.php?charity=' . $row['charity_id'] . '&mode=charitythumbImages' . '"   />';
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
                            echo CONF_CURRENCY . number_format(($rowCharity2['totalamount']), 2) . CONF_CURRENCY_RIGHT . '<br/><br/><ul class="actions"><li><a href="charity-history.php?charity=' . $row['charity_id'] . '" title="' . t_lang('M_TXT_DETAILS') . '"><i class="ion-eye icon"></i></a></li></ul>';
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
                            if (checkAdminAddEditDeletePermission(8, '', 'add')) {
                                if ($rowCharity2['totalamount'] > 0)
                                    echo '<br/><br/><ul class="actions"><li><a href="charity-history.php?charity=' . $row['charity_id'] . '&mode=pay" title="' . t_lang('M_TXT_PAY_NOW') . '"><i class="ion-social-usd icon"></i></a></li></ul>';
                            }
                            break;
                        case 'charity_approved_by':
                            if ($row['charity_approved_by'] == 0) {
                                echo 'Administrator';
                            } else {
                                $rs = $db->query("select user_name from tbl_users where user_id=" . $row['charity_approved_by']);
                                $row = $db->fetch($rs);
                                echo $row['user_name'];
                            }
                            break;
                        case 'charity_company_id':
                            if ($row['charity_company_id'] == 0 && $row['charity_user_id'] == 0) {
                                echo 'Administrator';
                            } else if ($row['charity_company_id'] == 0) {
                                $rs = $db->query("select user_name from tbl_users where user_id=" . $row['charity_approved_by']);
                                $row = $db->fetch($rs);
                                echo $row['user_name'];
                            } else {
                                $rs = $db->query("select company_name from tbl_companies where company_id=" . $row['charity_company_id']);
                                $row = $db->fetch($rs);
                                echo $row['company_name'];
                            }
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            $rs = $db->query("select deal_charity from tbl_deals where deal_charity=" . $charity_id);
                            $rowCharity = $db->fetch($rs);
                            $rs1 = $db->query("select ch_charity_id from tbl_charity_history where ch_charity_id=" . $charity_id);
                            $rowCharity1 = $db->fetch($rs1);
                            if (($rowCharity['deal_charity'] != $charity_id) && ($rowCharity1['ch_charity_id'] != $charity_id)) {
                                $deleteRow = '<li><a href="?status=' . $_REQUEST['status'] . '&delete=' . $charity_id . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                            } else {
                                $deleteRow = '';
                            }
                            if ($_REQUEST['status'] == 'active') {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $charity_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&inactive=' . $charity_id . '&page=' . $page . '" title="' . t_lang('M_TXT_INACTIVE') . '"><i class="ion-android-checkbox-blank icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
                                }
                            } else if ($_REQUEST['status'] == 'deactive') {
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&request=' . $charity_id . '&page=' . $page . '" title="' . t_lang('M_TXT_ACTIVE') . '"><i class="ion-android-checkbox icon"></i></a></li>';
                                    echo $deleteRow;
                                }
                            } else if ($_REQUEST['status'] == 'un-approved') {
                                echo '<li><a href="?status=' . $_REQUEST['status'] . '&request=' . $charity_id . '&page=' . $page . '" title="' . t_lang('M_TXT_APPROVE_REQUEST') . '"><i class="ion-checkmark-circled icon"></i></a></li>';
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $charity_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $charity_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
                                }
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
