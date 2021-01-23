<?php
require_once './application-top.php';
checkAdminPermission(3);
require_once '../includes/navigation-functions.php';
require_once '../includes/deal_functions.php';
loadModels(['CompanyModel']);
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$status = ($_REQUEST['status']) ? $_REQUEST['status'] : 'active';
$pagesize = CONF_ADMIN_PAGE_SIZE;
/**
 * COMPANY DELETE 
 * */
if (is_numeric($_REQUEST['delete'])) {
    if (checkAdminAddEditDeletePermission(3, '', 'delete')) {
        deleteCompany($_REQUEST['delete']);
        redirectUser('?page=' . $page);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/*
 * COMPANIES AUTOLOGIN CODE START HERE.
 */
if (is_numeric($_REQUEST['autoLogin'])) {
    $srch = Company::getSearchObject();
    $srch->addCondition("company_id", "=", $_REQUEST['autoLogin']);
    $srch->addMultipleFields(['company_email', 'company_password']);
    $result = $srch->getResultSet();
    $comp = $db->fetch($result);
    $error = '';
    if (loginCompanyUser($comp['company_email'], $comp['company_password'], $error)) {
        if (isset($_SESSION['merchant_login_page'])) {
            $url = $_SESSION['merchant_login_page'];
            unset($_SESSION['merchant_login_page']);
            redirectUser($url);
        }
        redirectUser(CONF_WEBROOT_URL . 'merchant/company-deals.php');
    } else {
        $msg->addError($error);
        unset($_SESSION["logged_user"]);
        redirectUser(CONF_WEBROOT_URL . 'manager/companies.php');
    }
}
/**
 * COMPANY ADD/EDIT FORM 
 * */
if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new' || ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && !isset($_POST['page']) )) {
    /** GET COMPANY FORM * */
    $frm = Company::getForm();
    if ((isset($_POST['company_id']) && intval($_POST['company_id']) > 0) || (isset($_REQUEST['edit']) && intval($_REQUEST['edit']) > 0)) {
        $fld_pwd = $frm->getField('company_password');
        $fld_pwd->requirements()->setRequired(false);
    }
    /** FILL LANGUAGE COMPANY FORM * */
    updateFormLang($frm);
    /** EDIT MODE COMPANY FORM FILLUP * */
    if (is_numeric($_GET['edit'])) {
        $fld = $frm->getField('company_password');
        $frm->removeField($fld);
        $record = new TableRecord(Company::DB_TBL);
        if (!$record->loadFromDb(Company::DB_TBL_PRIMARY_KEY . '=' . $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $arr['company_password'] = '';
            $selectedState = $arr['company_state'];
            $frm->addHiddenField('', 'old_company_name', $arr['company_name']);
            if (!empty($arr['company_logo' . $_SESSION['lang_fld_prefix']])) {
                $fld = $frm->getField('company_logo');
                $fld->extra = 'onchange="readURL(this);"';
                $src = COMPANY_LOGO_URL . $arr['company_logo' . $_SESSION['lang_fld_prefix']];
                $fld->html_after_field = '<div class="CompanyImage_show"><img class="deal_image" src="' . $src . '" ></div>';
            }
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    }
    /**
     * POST MODE COMPANY FORM 
     * */
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
        $post = getPostedData();
        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                $msg->addError($error);
            }
        } else {
            $succeed = true;
            /** CHECK IF STATUS CHANGE AND ANY DEAL IS ONGOING * */
            if (($post['company_active'] != 1) && ($post['company_id'] > 0)) {
                $canUpdate = canDeleteCompany($post['company_id']);
                if ($canUpdate > 0) {
                    fillForm($frm, $post);
                    $msg->addError(addslashes(t_lang('M_TXT_COMPANY_CANNOT_BE_INACTIVE')));
                    redirectUser('/manager/companies.php?edit=' . $post['company_id']);
                }
            }
            /* IMAGE VALIDATIONS IF UPLOADED */
            if (is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
                $ext = strtolower(strrchr($_FILES['company_logo']['name'], '.'));
                if ((!in_array($ext, ['.gif', '.jpg', '.jpeg', '.png'])) || ($_FILES['company_logo']['size'] > CONF_IMAGE_MAX_SIZE)) {
                    $msg->addError(t_lang('M_TXT_COMPANY') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
                    fillForm($frm, $post);
                    $succeed = false;
                }
            }
            if (true === $succeed) {
                $record = new TableRecord(Company::DB_TBL);
                $post['company_rep_id'] = intval($post['company_rep_id']);
                $arr_lang_independent_flds = ['company_id', 'company_email', 'company_phone', 'company_url', 'company_zip', 'company_rep_id', 'company_country', 'company_profile_enabled', 'company_tin', 'company_state', 'company_paypal_account'/* , 'company_google_map' */, 'company_active', 'company_deleted', 'mode', 'btn_submit', 'company_facebook_url', 'company_twitter', 'company_linkedin'];
                if ($post['company_password'] != '') {
                    $code = $post['company_password'];
                    $post['company_password'] = md5($post['company_password']);
                } else {
                    unset($post['company_password']);
                }
                if (!isset($post['old_company_name'])) {
                    $record->setFldValue('company_name', $post['company_name']);
                }
                assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
                $success = ($post[Company::DB_TBL_PRIMARY_KEY] > 0) ? $record->update(Company::DB_TBL_PRIMARY_KEY . '=' . $post[Company::DB_TBL_PRIMARY_KEY]) : $record->addNew();
                if ($success) {
                    $company_id = ($post['company_id'] > 0) ? $post['company_id'] : $record->getId();
                    if ($post['company_id'] == "") {
                        $rs = $db->query("select * from tbl_email_templates where tpl_id=8");
                        $row_tpl = $db->fetch($rs);
                        $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                        $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                        $arr_replacements = [
                            'xxcompany_namexx' => $post['company_name'],
                            'xxuser_namexx' => $post['company_email'],
                            'xxemail_addressxx' => $post['company_email'],
                            'xxpasswordxx' => $code,
                            'xxloginurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant/',
                            'xxsite_namexx' => CONF_SITE_NAME,
                            'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                            'xxwebrooturlxx' => CONF_WEBROOT_URL,
                            'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL
                        ];
                        foreach ($arr_replacements as $key => $val) {
                            $subject = str_replace($key, $val, $subject);
                            $message = str_replace($key, $val, $message);
                        }
                        if ($row_tpl['tpl_status'] == 1) {
                            sendMail($post['company_email'], $subject, emailTemplateSuccess($message));
                        }
                        ##############################################
                    }
                    ################### COMPANY LOGO ###################
                    if (is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
                        $flname = time() . '_' . $_FILES['company_logo']['name'];
                        if (!move_uploaded_file($_FILES['company_logo']['tmp_name'], COMPANY_LOGO_PATH . $flname)) {
                            $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                        } else {
                            $getImg = $db->query("select * from tbl_companies where company_id='" . $company_id . "'");
                            $imgRow = $db->fetch($getImg);
                            unlink(COMPANY_LOGO_PATH . $imgRow['company_logo' . $_SESSION['lang_fld_prefix']]);
                            $db->update_from_array('tbl_companies', ['company_logo' . $_SESSION['lang_fld_prefix'] => $flname], 'company_id=' . $company_id);
                        }
                    }
                    ################### COMPANY LOGO END ###################
                    ################### CHECK REDIRECTION IF THE MULTIPLE ADDRESSES ARE NULL###################
                    $srchAdd = new SearchBase('tbl_company_addresses', 'ca');
                    $srchAdd->addCondition('company_id', '=', $company_id);
                    $rs_listingAdd = $srchAdd->getResultSet();
                    if ($db->total_records($rs_listingAdd) == 0) {
                        redirectUser();
                        //$msg->addMsg(unescape_attr(t_lang('M_MSG_ADD_ATLEAST_ONE_ADDRESS')));
                        //redirectUser('company-addresses.php?company_id=' . $company_id . '&page=1&add=new');
                    }
                    #########################CHECK REDIRECTION IF THE MULTIPLE ADDRESSES ARE NULL##############
                    $msg->addMsg(t_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                    redirectUser();
                } else {
                    $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
                    /* $frm->fill($post); */
                    $selectedState = $post['company_state'];
                    $company_country = $post['company_country'];
                    fillForm($frm, $post);
                }
            }
            redirectUser();
        }
    }
} else {
    $post = getPostedData();
    /**
     * COMPANY SEARCH FORM 
     * */
    $srcFrm = Company::getSearchForm();
    $srcFrm->addHiddenField('', 'status', $_REQUEST['status']);
    /**
     * SEARCH COMPANY LISTING 
     * */
    $srch = Company::getSearchObject();
    if ($_REQUEST['rep'] > 0) {
        $srch->addCondition('company_rep_id', '=', $_REQUEST['rep']);
    }
    if ($_REQUEST['status'] == 'inactive') {
        $srch->addCondition('company_active', '=', 0);
        $srch->addCondition('company_deleted', '=', 0);
    } else if ($_REQUEST['status'] == 'deleted') {
        $srch->addCondition('company_deleted', '=', 1);
    } else if ($_REQUEST['status'] == 'active') {
        $srch->addCondition('company_active', '=', 1);
        $srch->addCondition('company_deleted', '=', 0);
    } else {
        $srch->addCondition('company_deleted', '=', 0);
    }
    $srch->joinTable('tbl_countries', 'INNER JOIN', 'c.company_country=country.country_id', 'country');
    $srch->joinTable('tbl_states', 'LEFT JOIN', 'st.state_id=c.company_state', 'st');
    $srch->joinTable('tbl_deals', 'LEFT OUTER JOIN', 'd.deal_company=c.company_id AND deal_paid=0 AND deal_status=2 AND deal_deleted=0', 'd');
    $srch->joinTable('tbl_representative', 'LEFT OUTER JOIN', 'r.rep_id=c.company_rep_id AND r.rep_id > 0', 'r');
    $srch->joinTable('tbl_company_addresses', 'LEFT OUTER JOIN', 'ca.company_id=c.company_id', 'ca');
    $srch->addFld('COUNT(DISTINCT d.deal_id) AS total_unsetteled_deals');/** expired unsettled deals * */
    $srch->addMultipleFields(['c.company_id', 'c.company_name' . $_SESSION['lang_fld_prefix'] . ' as company_name', 'c.company_email', 'c.company_active', 'c.company_rep_id', 'country.country_name', 'r.rep_fname', 'r.rep_lname', 'COUNT(DISTINCT ca.company_address_id) as total_company_address', 'st.*']);
    if ($_SESSION['lang_fld_prefix'] == '_lang1') {
        $srch->addFld("CONCAT(company_address1_lang1, '<br/>', company_address2_lang1, '<br/>', company_address3_lang1, ' ', company_city_lang1, ' ', state_name_lang1, '-', company_zip, ' ', country_name_lang1) AS address");
    } else {
        $srch->addFld("CONCAT(company_address1, '<br/>', company_address2, '<br/>', company_address3, ' ', company_city, ' ', state_name, '-', company_zip, ' ', country_name) AS address");
    }
    $srch->addOrder('company_name' . $_SESSION['lang_fld_prefix']);
    if ($post['mode'] == 'search') {
        if ($post['keyword'] != '') {
            $cnd = $srch->addDirectCondition('0');
            $cnd->attachCondition('c.company_email', 'like', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('c.company_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('c.company_address1' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('c.company_address2' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('c.company_address3' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        }
        $srcFrm->fill($post);
    }
    $srch->addGroupBy('company_id');
    $srch->setPageNumber($page);
    $srch->setPageSize($pagesize);
    $rs_listing = $srch->getResultSet();
    /* PAGINATION */
    $pagestring = '';
    $pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => $page, 'status' => $_REQUEST['status']]);
    $pagestring .= '<div class="pagination "><ul>';
    $pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
            ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
    $pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
		' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                    , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
    $pagestring .= '</ul></div>';
    /**
     * COMPANY DELETE MODE
     * */
    if (isset($_GET['deletePer']) && $_GET['deletePer'] != "") {
        if (checkAdminAddEditDeletePermission(3, '', 'delete')) {
            $user_id = $_GET['deletePer'];
            deleteCompanyMemberPermanent($user_id);
            /* function write in the site-function.php */
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    }
    /**
     * COMPANY RESTORE MODE
     * */
    if (isset($_GET['restore']) && $_GET['restore'] != "") {
        if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
            $user_id = $_GET['restore'];
            restoreCompanyMember($user_id);
            /* function write in the site-function.php */
            redirectUser('?page=' . $page . '&status=' . $_REQUEST['status']);
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    }
    $arr_listing_fields = [
        'listserial' => '',
        'company_name' => '',
        'total_address' => '',
        'unsettled_deals' => '',
        'payout' => '',
        'company_active' => '',
        'action' => ''
    ];
}
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_COMPANIES')
];
?>
<script type = "text/javascript">
    var txtdelcomp = "<?php echo addslashes(t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE_THIS_COMPANY')); ?>";
    var txtnotallowed = "<?php echo addslashes(t_lang('M_TXT_COMPANY_DELETION_NOT_ALLOWED')); ?>";
    var txtinactive = "<?php echo addslashes(t_lang('M_TXT_COMPANY_CANNOT_BE_INACTIVE')); ?>";
    var txtstatusup = "<?php echo addslashes(t_lang('M_TXT_STATUS_UPDATED')); ?>";
    var txtCompActive = "<?php echo addslashes(t_lang('M_TXT_You_have_to_entered_commission,_Would_you_like_to_continue?')); ?>";
</script>
<ul class="nav-left-ul">
    <li>    <a <?php if ($_REQUEST['status'] == 'active') echo 'class="selected"'; ?> href="companies.php?status=active"><?php echo t_lang('M_TXT_ACTIVE'); ?> <?php echo t_lang('M_TXT_COMPANIES'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'inactive') echo 'class="selected"'; ?> href="companies.php?status=inactive"><?php echo t_lang('M_TXT_INACTIVE'); ?> <?php echo t_lang('M_TXT_COMPANIES'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
    <li>    <a <?php if ($_REQUEST['status'] == 'deleted') echo 'class="selected"'; ?> href="companies.php?status=deleted"><?php echo t_lang('M_TXT_DELETED'); ?> <?php echo t_lang('M_TXT_COMPANIES'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_COMPANIES'); ?> 
            <?php if (checkAdminAddEditDeletePermission(3, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li><a href="?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> </a></li>
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
        <?php
    }
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(3, '', 'add')) || (checkAdminAddEditDeletePermission(3, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_COMPANIES'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter">
            <div class="title"> <?php echo t_lang('M_TXT_COMPANIES'); ?> </div>
            <div class="content togglewrap" style="display:none;"><?php echo $srcFrm->getFormHtml(); ?></div>
        </div>
        <table class="tbl_data table companieslist table-striped">
            <thead>
                <tr>
                    <th><?php echo t_lang('M_TXT_SR_NO'); ?></th>
                    <th><?php echo t_lang('M_TXT_COMPANY_INFO'); ?></th>
                    <th><?php echo t_lang('M_TXT_TOTAL_LOCATIONS'); ?></th>
                    <th><?php echo t_lang('M_TXT_SALES_DATA'); ?></th>
                    <th><?php echo t_lang('M_TXT_TOTAL_AMOUNT_PAID'); ?></th>
                    <th><?php echo t_lang('M_FRM_STATUS'); ?></th>
                    <th><?php echo t_lang('M_TXT_ACTION'); ?></th>
                </tr>
            </thead>
            <?php
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                /** SALES OF unsettled deals * */
                $totalUnsettledPrice = 0;
                $totalSettledPrice = 0;
                //calculateDealAmountPaidPayableToMerchant($row['company_id']);exit;
                $arrs = getSettledUnsettledDealData($row['company_id']);
                $totalUnsettledPrice = $arrs['totalUnsettledPrice'];
                $totalSettledPrice = $arrs['totalSettledPrice'];
                $totalDebits = getTotalDebitsAmountForMerchant($row['company_id']);
                $totalcredits = getTotalCreditsAmountForMerchant($row['company_id']);
                $payable_amount = (($totalSettledPrice + $totalcredits) - $totalDebits);
                /** SALES OF  settled deals * */
                /*                 * ****** */
                if ($listserial % 2 != 0) {
                    $even = 'even';
                } else {
                    $even = '';
                }
                echo '<tr class=" ' . $even . ' ">';
                $i = 0;
                foreach ($arr_listing_fields as $key => $val) {
                    $td_even = '';
                    if ($i % 2 == 0) {
                        $td_even = 'center';
                    }
                    echo '<td class=" ' . $td_even . ' ">';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'company_name':
                            echo '<strong>' . t_lang('M_TXT_COMPANY_NAME') . '</strong><br/>' . htmlentities($row['company_name']) . '<br/><br/>';
                            echo '<strong>' . t_lang('M_TXT_EMAIL_ADDRESS') . '</strong> <br/>' . $row['company_email'] . '<br/><br/>';
                            if ($row['rep_fname'] != '') {
                                echo '<strong>' . t_lang('M_TXT_REP_NAME') . '</strong><br/>' . htmlentities($row['rep_fname']) . ' ' . $row['rep_lname'] . '<br/><br/>';
                            }
                            echo '<strong>' . t_lang('M_TXT_ADDRESS') . '</strong><br/>' . $row['address'];
                            break;
                        case 'total_address':
                            if ($row['total_company_address'] == 0) {
                                echo '<span class="label label-danger">' . t_lang('M_TXT_COMPANY_ADDRESS_PAGE') . '</span>';
                            } else {
                                echo '<strong>' . $row['total_company_address'] . '</strong>';
                            }
                            echo '<br/><ul class="actions center"><li><a href="javascript:void(0);" onclick="companyLocation(' . $row[Company::DB_TBL_PRIMARY_KEY] . ')" title=" ' . t_lang('M_TXT_VIEW_LOCATIONS') . '"  > <i class="ion-eye icon"></i></a></li>';
                            echo '<li><a href="company-addresses.php?company_id=' . $row[Company::DB_TBL_PRIMARY_KEY] . '" title=" ' . t_lang('M_TXT_ADD_LOCATIONS') . '"  > <i class="ion-ios-plus-empty icon"></i> </a></li></ul>';
                            break;
                        case 'unsettled_deals':
                            if (intval($row['total_unsetteled_deals']) > 0) {
                                echo '<strong>' . t_lang('M_TXT_UNSETTLED_DEALS_COUNT') . ': </strong> <br/><a href="deals.php?status=unsettled&cid=' . intval($row['company_id']) . '" title="' . t_lang('M_TXT_UNSETTLED_DEAL_COUNT_DESC') . '">' . intval($row['total_unsetteled_deals']) . '</a>';
                            } else {
                                echo '<strong>' . t_lang('M_TXT_UNSETTLED_DEALS_COUNT') . ':</strong> <br/><span title="' . t_lang('M_TXT_UNSETTLED_DEAL_COUNT_DESC') . '">' . intval($row['total_unsetteled_deals']) . '</span>';
                            }
                            echo '<br/><br/><strong>' . t_lang('M_TXT_UNSETTLED_DEALS') . ':</strong> <br/><a href="deal-unsettled-reports.php?company_id=' . intval($row['company_id']) . '" title="' . t_lang('M_TXT_UNSETTLED_DEAL_AMOUNT_DESC') . '">' . CONF_CURRENCY . number_format($totalUnsettledPrice, 2) . CONF_CURRENCY_RIGHT . '</a>';
                            echo '<br/><br/><strong>' . t_lang('M_TXT_SETTLED_DEAL_AMOUNT') . ':</strong> <br/><a href="deal-settled-reports.php?company_id=' . intval($row['company_id']) . '" title="' . t_lang('M_TXT_SETTLED_DEAL_AMOUNT_DESC') . '">' . CONF_CURRENCY . number_format($totalSettledPrice, 2) . CONF_CURRENCY_RIGHT . '</a>';
                            echo '<br/><br/><strong>' . t_lang('M_TXT_PAYABLE_AMOUNT') . ':</strong> <br/><span title="' . t_lang('M_TXT_PAYABLE_AMOUNT') . '">' . CONF_CURRENCY . number_format($payable_amount, 2) . CONF_CURRENCY_RIGHT . '</span>';
                            break;
                        case 'payout':
                            if ($totalDebits > 0) {
                                echo '<a href="company-transactions.php?company=' . $row[Company::DB_TBL_PRIMARY_KEY] . '" title="' . t_lang('M_TXT_CLICK_TO_VIEW_TRANSACTION_LIST') . '"><strong>' . CONF_CURRENCY . number_format($totalDebits, 2) . CONF_CURRENCY_RIGHT . '</strong></a>';
                            } else {
                                echo '<a href="company-transactions.php?company=' . $row[Company::DB_TBL_PRIMARY_KEY] . '" ><strong>' . CONF_CURRENCY . '0.00' . CONF_CURRENCY_RIGHT . '</strong></a>';
                            }
                            if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                echo '<br/><ul class="actions center"><li><a href="javascript:void(0);" alt="When you want to give credit to the merchant for their sales." onclick="addTransaction(' . $row[Company::DB_TBL_PRIMARY_KEY] . ',' . abs($payable_amount) . ')" title="' . t_lang('M_TXT_ADD_TRANSACTION') . '" ><i class="ion-social-usd icon"></i></a></li></ul>';
                            }
                            break;
                        case 'company_active':
                            echo '<br/><span id="original_span' . $row[Company::DB_TBL_PRIMARY_KEY] . '">';
                            if ($row['company_active'] == 1) {
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<span class="statustab addmarg" id="comment-status' . $row[Company::DB_TBL_PRIMARY_KEY] . '" onclick="activeCompany(' . $row[Company::DB_TBL_PRIMARY_KEY] . ',0);">
													<span class="switch-labels" data-off="Active" data-on="Inactive"></span>
													<span class="switch-handles"></span>
												</span>';
                                }
                            }
                            if ($row['company_active'] == 0) {
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<span class="statustab addmarg active" id="comment-status' . $row[Company::DB_TBL_PRIMARY_KEY] . '" onclick="activeCompany(' . $row[Company::DB_TBL_PRIMARY_KEY] . ',1);">
													<span class="switch-labels" data-off="Active" data-on="Inactive"></span>
													<span class="switch-handles"></span>
												</span>';
                                }
                            }
                            echo '</span>';
                            break;
                        case 'action':
                            /* if($row['company_fb_access_token'] !=''){
                              echo '<a href="facebook-update.php?id=' . $row[Company::DB_TBL_PRIMARY_KEY] . '&api='.$row['company_fb_apikey'].'&secret='.$row['company_fb_secret'].'" class="btn gray">' . t_lang('M_TXT_UPDATE_FACEBOOK_SESSION') . '</a> ';
                              } */
                            echo '<br/><ul class="actions center">';
                            if ($_REQUEST['status'] != 'deleted') {
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<li><a href="?edit=' . $row[Company::DB_TBL_PRIMARY_KEY] . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li> ';
                                    echo '<li><a href="javascript:void(0);" onClick="return companyChangePassword(' . $row[Company::DB_TBL_PRIMARY_KEY] . ');" title="' . t_lang('M_TXT_CHANGE_PASSWORD') . '"><i class="ion-unlocked icon"></i></a></li>';
                                    echo '<li><a href="?autoLogin=' . $row[Company::DB_TBL_PRIMARY_KEY] . '" target="_blank" title="' . t_lang('M_TXT_Login_To_Profile') . '"><i class="ion-log-in icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(3, '', 'delete')) {
                                    echo '<li><a href="javascript:void(0);" title="' . t_lang('M_TXT_DELETE') . '" onclick="deleteCompany(' . $row[Company::DB_TBL_PRIMARY_KEY] . ',' . $page . ');"><i class="ion-android-delete icon"></i></a></li>';
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(3, '', 'delete')) {
                                    echo '<li><a href="companies.php?deletePer=' . $row[Company::DB_TBL_PRIMARY_KEY] . '&status=' . $_REQUEST['status'] . '" title="' . t_lang('M_TXT_DELETE_PERMANENTLY') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-ios-trash icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<li><a href="companies.php?restore=' . $row[Company::DB_TBL_PRIMARY_KEY] . '&status=' . $_REQUEST['status'] . '"  title="' . t_lang('M_TXT_RESTORE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_RESTORE_THIS_RECORD') . '\',1);"><i class="ion-archive icon"></i></a></li>';
                                }
                            }
                            echo '</ul>';
                            break;
                        default:
                            echo $row[$key];
                            break;
                    }
                    echo '</td>';
                    $i++;
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
        <?php } ?>
    </td>
<?php } ?>
<script>
    var selectedState = '<?php echo ($selectedState > 0 ? $selectedState : 0); ?>';
    var company_country = '<?php echo ($company_country > 0 ? $company_country : 0); ?>';
    var value = '<?php echo t_lang("M_TXT_SELECT_COUNTRY_FIRST"); ?>';
    var selectCountryFirst = '<option value="">' + value + '</option>';
    window.onload = updateStates(<?php echo $arr['company_country']; ?>);
</script>
<?php
//If set commission request is coming	
if (is_numeric($_REQUEST['commssion']) || $_REQUEST['commssion'] == 1) {
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name^="company_deal_commission_percent"]').focus();
        });
    </script>
<?php }
?>
<?php
require_once './footer.php';
?> 