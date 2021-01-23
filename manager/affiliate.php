<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
checkAdminPermission(8);
loadModels(array('AffiliateModel'));
$pagesize = CONF_ADMIN_PAGE_SIZE;
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
/*
 * AFFILIATE USERS AUTOLOGIN CODE START HERE.
 */
if (is_numeric($_REQUEST['autoLogin'])) {
    $srch = Affiliate::getSearchObject();
    $srch->addCondition("affiliate_id", "=", $_REQUEST['autoLogin']);
    $srch->addMultipleFields(array('affiliate_email_address', 'affiliate_password'));
    $result = $srch->getResultSet();
    $aff = $db->fetch($result);
    $error = '';
    if (loginAffiliateUser($aff['affiliate_email_address'], $aff['affiliate_password'], $error)) {
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'));
    } else {
        $msg->addError($error);
        unset($_SESSION["logged_user"]);
        redirectUser(CONF_WEBROOT_URL . 'manager/affiliate.php');
    }
}
$_REQUEST['status'] = (isset($_REQUEST['status']) ? $_REQUEST['status'] : 'approved');
$post = getPostedData();
/**
 * AFFILIATE SEARCH FORM 
 * */
$srchForm = Affiliate::getSearchForm($arr_user_status, $arr_sale_earning);
/**
 * AFFILIATE DELETE MODE
 * */
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    $isExist = Affiliate::recordExists(Affiliate::DB_TBL, Affiliate::DB_TBL_PRIMARY_KEY, $_GET['delete']);
    if ($isExist == true) {
        if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
            $affObj = new Affiliate();
            $affObj->deleteAffilateHistory($_GET['delete']);
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
 * AFFILIATE FORM
 * */
$frm = Affiliate::getForm($page);
updateFormLang($frm);
$selected_state = 0;
/**
 * AFFILIATE EDIT MODE
 * */
if (is_numeric($_GET['edit'])) {
    $fld = $frm->getField('affiliate_password');
    $frm->removeField($fld);
    $frm->addHiddenField('', 'affiliate_password', '');
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        $record = new TableRecord(Affiliate::DB_TBL);
        if (!$record->loadFromDb(Affiliate::DB_TBL_PRIMARY_KEY, $_GET['edit'], true)) {
            $msg->addError($record->getError());
        } else {
            $arr = $record->getFlds();
            $rs = $db->query("select state_country from tbl_states where state_id=" . $arr['affiliate_state']);
            $row = $db->fetch($rs);
            $arr['affiliate_country'] = $row['state_country'];
            $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
            $selected_state = $arr['affiliate_state'];
            fillForm($frm, $arr);
            $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * AFFILIATE SUBMIT FORM
 * */
if (isset($_POST['submit'])) {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $record = new TableRecord(Affiliate::DB_TBL);
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = array('affiliate_id', 'affiliate_city', 'affiliate_state', 'affiliate_country', 'affiliate_zipcode', 'affiliate_email_address', 'affiliate_password', 'affiliate_phone', 'affiliate_code', 'affiliate_status', 'affiliate_payment_mode', 'mode', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        if ((checkAdminAddEditDeletePermission(8, '', 'edit'))) {
            if ($post['affiliate_id'] > 0) {
                $success = $record->update('affiliate_id' . '=' . $post['affiliate_id']);
            }
        }
        if ((checkAdminAddEditDeletePermission(8, '', 'add'))) {
            $code = mt_rand(0, 9999999999);
            $record->setFldValue('affiliate_code', $code);
            if ($post['affiliate_password'] == "") {
                $record->setFldValue('affiliate_password', md5($code));
            } else {
                $record->setFldValue('affiliate_password', md5($post['affiliate_password']));
                $code = $post['affiliate_password'];
            }
            if ($post['affiliate_id'] == '') {
                $success = $record->addNew();
            }
        }
        $affiliate_id = ($post['affiliate_id'] > 0) ? $post['affiliate_id'] : $record->getId();
        if ($success) {
            if ($post['affiliate_id'] == "") {
                ########## Email #####################
                $rs = $db->query("select * from tbl_email_templates where tpl_id=8");
                $row_tpl = $db->fetch($rs);
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxcompany_namexx' => $post['affiliate_fname'] . ' ' . $post['affiliate_lname'],
                    'xxuser_namexx' => $post['affiliate_email_address'],
                    'xxemail_addressxx' => $post['affiliate_email_address'],
                    'xxpasswordxx' => $code,
                    'xxloginurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'affiliate-login.php',
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
                    sendMail($post['affiliate_email_address'], $subject, emailTemplateSuccess($message), $headers);
                }
                ##############################################
            }
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser();
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            $frm->fill($post);
        }
    }
}
/**
 * AFFILIATE SEARCH
 * */
$srch = Affiliate::getSearchObject();
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('a.affiliate_fname', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.affiliate_lname', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.affiliate_bussiness_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.affiliate_address_line1', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.affiliate_address_line2', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.affiliate_address_line3', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('a.affiliate_email_address', 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    if ($post['affiliate_status'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('affiliate_status', '=', $post['affiliate_status'], 'OR');
    }
    if ($post['sales_earning'] != '') {
        $srch1 = new SearchBase('tbl_users', 'u');
        //$srch->addCondition('u.user_affiliate_id', '=', 7, 'OR');
        $srch1->addMultipleFields(array('u.user_affiliate_id'));
        $srch1->joinTable('tbl_orders', 'INNER JOIN', 'o.order_user_id=u.user_id ', 'o');
        $srch1->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id ', 'od');
        $srch1->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
        $srch1->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
        $srch1->addCondition('o.order_payment_status', '!=', 0);
        $srch1->addCondition('u.user_affiliate_id', '!=', 0);
        $srch1->addMultipleFields(array("SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"));
        $srch1->addGroupBy('u.user_affiliate_id');
        $sales_earning = $post['sales_earning'];
        $user_affiliate_id = array(0);
        switch ($sales_earning) {
            case 1:
                /* $free_rep who don not belong to any company */
                $usr_aff_ids = fetchAffiliatedByUsersIds();
                $total_aff_ids = fetchAffiliatedUsersIds();
                $free_aff = array_diff($total_aff_ids, $usr_aff_ids);
                $user_affiliate_id = $free_aff;
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
            $user_affiliate_id[] = $val['user_affiliate_id'];
        }
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('affiliate_id', 'IN', $user_affiliate_id, 'OR');
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
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status', 'keyword'), array('page' => '', 'status' => $_REQUEST['status'], 'keyword' => $_REQUEST['keyword']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => 'S.N.',
    'affiliate_fname' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_NAME'),
    'affiliate_bussiness_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_BUSINESS_NAME'),
    'affiliate_address_line1' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_ADDRESS'),
    'affiliate_email_address' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_EMAIL'),
    /* 'affiliate_password'=>'Password', */
    'total_signup' => t_lang('M_TXT_MEMBER_SIGNUPS'),
    'total_newsletter_signup' => t_lang('M_TXT_NEWSLETTER_SIGNUPS'),
    'total_sale' => t_lang('M_TXT_SALES'),
    'affiliate_status' => t_lang('M_FRM_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'affiliate.php' => t_lang('M_TXT_DASHBOARD'),
    '' => t_lang('M_TXT_AFFILIATES')
);
echo '<script language="javascript">
	selectCountryFirst="' . t_lang('M_TXT_SELECT_COUNTRY_FIRST') . '";
	
selectedState=' . $selected_state . '
</script>';
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_AFFILIATES'); ?> 
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
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') { ?>
        <script type="text/javascript">
            $(document).ready(function () {
                updateStates(document.frmAffiliate.affiliate_country.value);
            });</script>
        <?php if ((checkAdminAddEditDeletePermission(8, '', 'add')) || (checkAdminAddEditDeletePermission(8, '', 'edit'))) { ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_NEW_AFFILIATE'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_SEARCH_AFFILIATE'); ?> </div><div class="content togglewrap" style="display:none;">		<?php echo $srchForm->getFormHtml(); ?>	
            </div></div>
        <table class="tbl_data affiliateslist" width="100%">
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
                $affiliate_id = $row['affiliate_id'];
                echo '<tr' . (($row[$colPrefix . 'approved'] == '0') ? ' class="inactive"' : '') . ' id = ' . $row['cat_id'] . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'affiliate_fname' . $_SESSION['lang_fld_prefix']:
                            echo $row['affiliate_fname'] . ' ' . $row['affiliate_lname'] . '<br/>';
                            //echo '<strong>'.$arr_lang_name[0].'</strong>'. ' ' .$row['affiliate_fname'].'<br/>';
                            //echo '<strong>'.$arr_lang_name[1].'</strong>'. ' ' .$row['affiliate_fname_lang1'];
                            break;
                        case 'affiliate_address_line1' . $_SESSION['lang_fld_prefix']:
                            echo $row['affiliate_address_line1' . $_SESSION['lang_fld_prefix']] . ' <br/>' . $row['affiliate_address_line2' . $_SESSION['lang_fld_prefix']] . ' <br/>' . $row['affiliate_address_line3' . $_SESSION['lang_fld_prefix']] . '<br/>' . $row['affiliate_city'];
                            break;
                        case 'total_signup':
                            /** get number of signups * */
                            $srch = new SearchBase('tbl_users', 'u');
                            $srch->addCondition('u.user_affiliate_id', '=', $row['affiliate_id'], 'OR');
                            $srch->addMultipleFields(array('COUNT(*) as total'));
                            //echo $srch->getQuery();
                            $registration_data = $srch->getResultSet();
                            $row1 = $db->fetch($registration_data);
                            if ($row1['total'] > 0) {
                                echo '<a href="registered-members.php?affiliate=' . $row['affiliate_id'] . '" title="' . t_lang('M_TXT_CLICK_TO_VIEW_DETAILS') . '">' . $row1['total'] . '</a>';
                            }
                            break;
                        case 'total_newsletter_signup':
                            /** get number of signups * */
                            $srch = new SearchBase('tbl_newsletter_subscription', 'ns');
                            $srch->addCondition('ns.subs_affiliate_id', '=', $affiliate_id, 'AND');
                            $srch->addMultipleFields(array('COUNT(*) as total'));
                            //echo $srch->getQuery();
                            $newsletter_data = $srch->getResultSet();
                            $row1 = $db->fetch($newsletter_data);
                            if ($row1['total'] > 0) {
                                //echo '<a href="newsletter-subscribers.php?affiliate='.$row['affiliate_id'].'">' . $row1['total'].'</a>';
                            }
                            break;
                        case 'total_sale':
                            /** get total referral commission and total affiliate commission * */
                            $srch = new SearchBase('tbl_users', 'u');
                            $srch->addCondition('u.user_affiliate_id', '=', $row['affiliate_id'], 'OR');
                            $srch->addMultipleFields(array('u.user_id'));
                            $wallet_data = $srch->getResultSet();
                            $users = [];
                            while ($row1 = $db->fetch($wallet_data)) {
                                $users[] = $row1['user_id'];
                            }
                            if ($db->total_records($wallet_data) > 0) {
                                $srch = new SearchBase('tbl_orders', 'o');
                                $srch->joinTable('tbl_order_deals', 'INNER JOIN', 'o.order_id=od.od_order_id ', 'od');
                                $srch->joinTable('tbl_deals', 'INNER JOIN', 'od.od_deal_id=d.deal_id ', 'd');
                                $srch->addCondition('d.deal_tipped_at', '!=', '0000-00-00 00:00:00');
                                $srch->addCondition('o.order_payment_status', '!=', 0);
                                $srch->addCondition('o.order_user_id', 'IN', $users);
                                $srch->addMultipleFields(array('od.*', 'o.*', "SUM((od.od_qty+od.od_gift_qty)*od.od_deal_price) as totalAmount"));
                                //echo $srch->getQuery();
                                $data = $srch->getResultSet();
                                $amountRow = $db->fetch($data);
                                if ($db->total_records($data) > 0) {
                                    echo '<a title="' . t_lang('M_TXT_CLICK_TO_VIEW_DETAILS') . '" href="affiliate_list.php?uid=' . $affiliate_id . '">' . CONF_CURRENCY . number_format($amountRow['totalAmount'], 2) . CONF_CURRENCY_RIGHT . '</a>';
                                    echo '<br/><br/><ul class="actions"><li><a href="affiliate-history.php?affiliate=' . $affiliate_id . '" title="' . t_lang('M_TXT_VIEW_TRANSACTIONS') . '"><i class="ion-social-usd icon"></i></a></li></ul>';
                                }
                            }
                            break;
                        case 'affiliate_status':
                            echo '<span id="original_span' . $row['affiliate_id'] . '">';
                            if ($row['affiliate_status'] == 1) {
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<span class="statustab" id="comment-status' . $row['affiliate_id'] . '" onclick="activeAffiliate(' . $row['affiliate_id'] . ',0);">
                                            <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                            <span class="switch-handles"></span>
                                        </span>';
                                }
                            }
                            if ($row['affiliate_status'] == 0) {
                                if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                    echo '<span class="statustab active" id="comment-status' . $row['affiliate_id'] . '" onclick="activeAffiliate(' . $row['affiliate_id'] . ',1);">
                                                <span class="switch-labels" data-off="Active" data-on="Inactive"></span>
                                                <span class="switch-handles"></span>
                                            </span>';
                                }
                            }
                            echo '</span>';
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            $deleteRow = '<li><a href="?status=' . $_REQUEST['status'] . '&delete=' . $affiliate_id . '&page=' . $page . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
                            if ($_REQUEST['status'] == 'approved') {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $affiliate_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                    echo '&nbsp;<li><a href="affiliate_summary.php?uid=' . $affiliate_id . '" title="' . t_lang('M_TXT_VIEW_AFFILIATE_PERFORMANCE_REPORT') . '"><i class="ion-stats-bars icon"></i></a></li>';
                                    echo '<li><a href="?autoLogin=' . $affiliate_id . '" target="_blank" title="' . t_lang('M_TXT_Login_To_Profile') . '"><i class="ion-log-in icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
                                }
                            } else if ($_REQUEST['status'] == 'un-approved') {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $affiliate_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                                }
                                if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
                                    echo $deleteRow;
                                }
                            } else {
                                if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
                                    echo '<li><a href="?status=' . $_REQUEST['status'] . '&edit=' . $affiliate_id . '&page=' . $page . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
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
            if ($db->total_records($rs_listing) == 0) {
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </table>
        <?php if ($pages > 1) { ?>
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
