<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(8);
loadModels(array('RepresentativeModel'));
$pagesize = CONF_ADMIN_PAGE_SIZE;
/*
 * REPRESENTATIVE USERS AUTOLOGIN CODE START HERE.
 */
if (is_numeric($_REQUEST['autoLogin'])) {
    $srch = Representative::getSearchObject();
    $srch->addCondition("rep_id", "=", $_REQUEST['autoLogin']);
    $srch->addMultipleFields(['rep_email_address', 'rep_password']);
    $result = $srch->getResultSet();
    $rep = $db->fetch($result);
    $error = '';
    if (loginRepresentativeUser($rep['rep_email_address'], $rep['rep_password'], $error)) {
        if (isset($_SESSION['rep_login_page'])) {
            $url = $_SESSION['rep_login_page'];
            unset($_SESSION['rep_login_page']);
            redirectUser($url);
        }
        redirectUser(CONF_WEBROOT_URL . 'representative/my-account.php');
    } else {
        $msg->addError($error);
        unset($_SESSION["logged_user"]);
        redirectUser(CONF_WEBROOT_URL . 'manager/representative.php');
    }
}
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$_REQUEST['status'] = (isset($_REQUEST['status']) ? $_REQUEST['status'] : 'approved');
$post = getPostedData();
/*
 * REPRESENTATIVE SERACH FORM
 */
$srchForm = Representative::getSearchForm($arr_user_status, $arr_sale_earning);
/**
 * REPRESENTATIVE DELETE MODE
 * */
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    $isExist = Representative::recordExists(REPRESENTATIVE::DB_TBL, REPRESENTATIVE::DB_TBL_PRIMARY_KEY, $_GET['delete']);
    if ($isExist == true) {
        if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
            $repObj = new Representative();
            $repObj->deleteRepresentativeHistory($_GET['delete']);
            $msg->addMsg(t_lang("M_TXT_RECORD_DELETED"));
            redirectUser('?page=' . $page);
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * REPRESENTATIVE FORM
 * */
$frm = Representative::getForm();
$selected_state = 0;
updateFormLang($frm);
/**
 * REPRESENTATIVE EDIT MODE
 * */
if (is_numeric($_GET['edit'])) {
    $fld = $frm->getField('rep_password');
    $frm->removeField($fld);
    $frm->addHiddenField('', 'rep_password', '');
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        $record = new TableRecord(Representative::DB_TBL);
        if (!$record->loadFromDb(Representative::DB_TBL_PRIMARY_KEY, $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $rs = $db->query("select state_country from tbl_states where state_id=" . $arr['rep_state']);
            $row = $db->fetch($rs);
            $arr['rep_country'] = $row['state_country'];
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $selected_state = $arr['rep_state'];
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * REPRESENTATIVE SUBMIT FORM
 * */
if (isset($_POST['btn_submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $record = new TableRecord(Representative::DB_TBL);
        $arr_lang_independent_flds = array('rep_id', 'rep_city', 'rep_state', 'rep_country', 'rep_zipcode', 'rep_email_address', 'rep_password', 'rep_phone', 'rep_code', 'rep_status', 'rep_payment_mode', 'mode', 'rep_paypal_id', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(8, '', 'edit'))) {
            if ($post['rep_id'] > 0) {
                $success = $record->update('rep_id' . '=' . $post['rep_id']);
            }
        }
        if ((checkAdminAddEditDeletePermission(8, '', 'add'))) {
            $code = mt_rand(0, 9999999999);
            $record->setFldValue('rep_code', $code);
            if ($post['rep_password'] == "") {
                $record->setFldValue('rep_password', md5($code));
            } else {
                $record->setFldValue('rep_password', md5($post['rep_password']));
                $code = $post['rep_password'];
            }
            /* $record->setFldValue('rep_status',1); */
            if ($post['rep_id'] == '')
                $success = $record->addNew();
        }
        $rep_id = ($post['rep_id'] > 0) ? $post['rep_id'] : $record->getId();
        #$success=($post['rep_id']>0)?$record->update('rep_id' . '=' . $post['rep_id']):$record->addNew();
        if ($success) {
            if ($post['rep_id'] == "") {
                ########## Email #####################
                /* $headers  = 'MIME-Version: 1.0' . "\r\n";
                  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                  $headers .= 'From: ' . CONF_SITE_NAME . ' <' . CONF_EMAILS_FROM . '>' . "\r\n"; */
                $rs = $db->query("select * from tbl_email_templates where tpl_id=38");
                $row_tpl = $db->fetch($rs);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxcompany_namexx' => $post['rep_fname'] . ' ' . $post['rep_lname'],
                    'xxuser_namexx' => $post['rep_email_address'],
                    'xxemail_addressxx' => $post['rep_email_address'],
                    'xxpasswordxx' => $code,
                    'xxloginurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'representative/login.php',
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
                    sendMail($post['rep_email_address'], $subject, emailTemplateSuccess($message), $headers);
                }
                ##############################################
            }
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('?');
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            $frm->fill($post);
        }
    }
}
/**
 * REPRESENTATIVE SEARCH
 * */
$srch = Representative::getSearchObject();
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('a.rep_fname', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.rep_lname', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.rep_bussiness_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.rep_address_line1', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.rep_address_line2', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.rep_address_line3', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.rep_email_address', 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    if ($post['rep_status'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('rep_status', '=', $post['rep_status'], 'OR');
    }
    if ($post['sales_earning'] != '') {
        $srch1 = new SearchBase('tbl_companies', 'c');
        $srch1->joinTable('tbl_deals', 'INNER JOIN', 'c.company_id=d.deal_company', 'd');
        $srch1->addMultipleFields(['c.company_rep_id']);
        $srch1->joinTable('tbl_order_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'od');
        $srch1->joinTable('tbl_orders', 'INNER JOIN', 'o.order_id=od.od_order_id ', 'o');
        $srch1->addCondition('o.order_payment_status', '!=', 0);
        $srch1->addCondition('c.company_rep_id', '!=', 0);
        $srch1->addMultipleFields(["SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"]);
        $srch1->addGroupBy('c.company_rep_id');
        $sales_earning = $post['sales_earning'];
        $company_rep_id = [0];
        switch ($sales_earning) {
            case 1:
                /* $free_rep who don not belong to any company */
                $co_rep_ids = fetchCompanyRepIds();
                $total_rep_ids = fetchTotalRepIds();
                $free_rep = array_diff($total_rep_ids, $co_rep_ids);
                $company_rep_id = $free_rep;
                $srch1->addHaving('totalAmount', '<=', 1000, 'AND', true);
                break;
            case 5:
                $srch1->addHaving('totalAmount', '>', 1000, 'AND', true);
                $srch1->addHaving('totalAmount', '<=', 5000, 'AND', true);
                break;
            case 25:
                $srch1->addHaving('totalAmount', '>', 5000, 'AND', true);
                $srch1->addHaving('totalAmount', '<=', 25000, 'AND', true);
                break;
            case 50:
                $srch1->addHaving('totalAmount', '>', 25000, 'AND', true);
                $srch1->addHaving('totalAmount', '<=', 50000, 'AND', true);
                break;
            case 100:
                $srch1->addHaving('totalAmount', '>', 50000, 'AND', true);
                $srch1->addHaving('totalAmount', '<=', 100000, 'AND', true);
                break;
            case 1000:
                $srch1->addHaving('totalAmount', '>', 100000, 'AND', true);
                //$srch1->addHaving('totalAmount', '<=',50000,'AND',true); 
                break;
        }
        $rep_data = $srch1->getResultSet();
        $row1 = $db->fetch_all($rep_data);
        foreach ($row1 as $key => $val) {
            $company_rep_id[] = $val['company_rep_id'];
        }
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('rep_id', 'IN', $company_rep_id, 'OR');
    }
    $srchForm->fill($post);
}
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
/**
 * AFFILIATE PAGINATION
 * */
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'status', 'keyword'], ['page' => '', 'status' => $_REQUEST['status'], 'keyword' => $_REQUEST['keyword']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'listserial' => 'S.N.',
    'rep_fname' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_FIRST_NAME'),
    'rep_lname' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_LAST_NAME'),
    'rep_bussiness_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_BUSINESS_NAME'),
    'rep_address_line1' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_ADDRESS'),
    'rep_email_address' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_EMAIL_ADDRESS'),
    'total_signup' => t_lang('M_TXT_TOTAL_MERCHANT_SIGNUPS'),
    /* 'total_newsletter_signup'=>t_lang('M_TXT_NEWSLETTER_SIGN_UP'), */
    'total_sale' => t_lang('M_TXT_TOTAL_SALES'),
    'rep_status' => t_lang('M_FRM_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
echo '<script language="javascript">
	selectCountryFirst="' . addslashes(t_lang('M_TXT_SELECT_COUNTRY_FIRST')) . '"
	</script>';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_USERS'),
    '' => t_lang('M_TXT_REPRESENTATIVE')
];
echo '<script language="javascript">
selectedState=' . $selected_state . '
</script>';
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_REPRESENTATIVE'); ?> 
            <?php if (checkAdminAddEditDeletePermission(8, '', 'add')) { ?> 
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li> 
                                    <a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
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
                updateStates(document.frmRepresentative.rep_country.value);
            });</script>
        <?php if ((checkAdminAddEditDeletePermission(8, '', 'add')) || (checkAdminAddEditDeletePermission(8, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_REPRESENTATIVE'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_REPRESENTATIVE'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $srchForm->getFormHtml(); ?>
            </div></div>	
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
            $balance = 0;
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                $rep_id = $row['rep_id'];
                echo '<tr' . (($row[$colPrefix . 'approved'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['cat_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'rep_fname' . $_SESSION['lang_fld_prefix']:
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['rep_fname'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['rep_fname_lang1'];
                            break;
                        case 'rep_address_line1' . $_SESSION['lang_fld_prefix']:
                            echo $row['rep_address_line1' . $_SESSION['lang_fld_prefix']] . ' <br/>' . $row['rep_address_line2' . $_SESSION['lang_fld_prefix']] . ' <br/>' . $row['rep_address_line3' . $_SESSION['lang_fld_prefix']] . '<br/>' . $row['rep_city'];
                            break;
                        case 'total_signup':
                            /** get number of signups * */
                            $srch = new SearchBase('tbl_companies', 'c');
                            $srch->addCondition('company_deleted', '=', 0);
                            $srch->addCondition('c.company_rep_id', '=', $row['rep_id']);
                            $srch->addMultipleFields(['COUNT(*) as total']);
                            $registration_data = $srch->getResultSet();
                            $row1 = $db->fetch($registration_data);
                            if ($row1['total'] > 0) {
                                echo '<a href="companies.php?rep=' . $row['rep_id'] . '" title="' . t_lang('M_TXT_VIEW_MERCHANTS') . '">' . $row1['total'] . '</a>';
                            }
                            break;
                        case 'total_sale':
                            $srch = new SearchBase('tbl_companies', 'c');
                            $srch->addCondition('c.company_rep_id', '=', $row['rep_id'], 'OR');
                            $srch->joinTable('tbl_deals', 'INNER JOIN', 'c.company_id=d.deal_company', 'd');
                            $srch->addMultipleFields(array('c.company_id', 'd.deal_id'));
                            $wallet_data = $srch->getResultSet();
                            $company = [];
                            $deal = [];
                            while ($row1 = $db->fetch($wallet_data)) {
                                $company[] = $row1['company_id'];
                                $deal[] = $row1['deal_id'];
                            }
                            if ($db->total_records($wallet_data) > 0) {
                                $srch = new SearchBase('tbl_orders', 'o');
                                $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id ', 'od');
                                $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
                                $srch->addCondition('o.order_payment_status', '!=', 0);
                                $srch->addCondition('od.od_deal_id', 'IN', $deal);
                                $srch->addMultipleFields(['od.*', 'o.*', "SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"]);
                                $data = $srch->getResultSet();
                                $amountRow = $db->fetch($data);
                                if ($db->total_records($data) > 0) {
                                    echo '<a href="rep_list.php?uid=' . $rep_id . '" title="' . t_lang('M_TXT_CLICK_TO_VIEW_SALES_DATA') . '">' . CONF_CURRENCY . number_format($amountRow['totalAmount'], 2) . CONF_CURRENCY_RIGHT . '  </a>
                                        <br/><ul class="actions"><li><a href="javascript:void(0);" onClick="return payNow(' . $rep_id . ');" title="' . t_lang('M_TXT_ADD_TRANSACTION') . '"> <i class="ion-social-usd icon"></i> </a></li></ul>';
                                }
                            }
                            break;
                        case 'rep_status':
                            if ($row['rep_status'] == 1) {
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<span class="statustab" id="comment-status' . $row['rep_id'] . '" onclick="activeRepresentative(' . $row['rep_id'] . ',0);">
                                                <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                                <span class="switch-handles"></span>
                                        </span>';
                                }
                            }
                            if ($row['rep_status'] == 0) {
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<span class="statustab active" id="comment-status' . $row['rep_id'] . '" onclick="activeRepresentative(' . $row['rep_id'] . ',1);">
                                                <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                                <span class="switch-handles"></span>
                                        </span>';
                                }
                            }
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            $deleteRow = '<li><a href="?status=' . $_REQUEST['status'] . '&delete=' . $rep_id . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                            if ($_REQUEST['status'] == 'approved') {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $rep_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                    echo '<li><a href="rep-history.php?rep_id=' . $rep_id . '" title="' . t_lang('M_TXT_TRANSACTION_HISTORY') . '"><i class="ion-alert icon"></i></a></li>';
                                    echo '<li><a href="?autoLogin=' . $rep_id . '" target="_blank" title="' . t_lang('M_TXT_Login_To_Profile') . '"><i class="ion-log-in icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
                                }
                            } else if ($_REQUEST['status'] == 'un-approved') {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $rep_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $rep_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                    /* echo '&nbsp;<a href="rep_summary.php?uid=' . $rep_id .'">Commission Earnings</a>';  */
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
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
<?php require_once './footer.php'; ?>
