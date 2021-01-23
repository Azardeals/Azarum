<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
$arr_common_css[] = 'css/prettyPhoto.css';
$arr_common_js[] = 'js/jquery.prettyPhoto.js';
if ($_SESSION['cityname'] != "") {
    $cityname = convertStringToFriendlyUrl($_SESSION['cityname']);
} else {
    $cityname = 1;
}
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0");
$cityArray = $db->fetch_all_assoc($cityList);
$catList = $db->query("select cat_id, IF(CHAR_LENGTH(cat_name" . $_SESSION['lang_fld_prefix'] . "),cat_name" . $_SESSION['lang_fld_prefix'] . ",cat_name) as cat_name from tbl_deal_categories where cat_active=1  order by cat_name");
$catArray = $db->fetch_all_assoc($catList);
$typeArray = array('0-0' => t_lang('M_TXT_DEAL'), '0-1' => t_lang('M_TXT_BOOKING_REQUEST'), '0-2' => t_lang('M_TXT_ONLINE_BOOKING'), '1-0' => t_lang('M_TXT_PRODUCT'), '1-1' => t_lang('M_TXT_DIGITAL_PRODUCT'));
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(4);
$Src_frm->captionInSameCell(true);
//$Src_frm->captionInSameCell(false);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$Src_frm->addSelectBox(t_lang('M_TXT_CITY_NAME'), 'deal_city', $cityArray, $value, '', t_lang('M_TXT_SELECT'), 'deal_city');
$Src_frm->addSelectBox(t_lang('M_TXT_CATEGORY_NAME'), 'deal_cat', $catArray, $value, '', t_lang('M_TXT_SELECT'), 'deal_cat');
$Src_frm->addSelectBox(t_lang('M_TXT_TYPE'), 'deal_type', $typeArray, '', '');
$Src_frm->addDateField(t_lang('M_FRM_DEAL_STARTS_ON'), 'deal_start_time', '', 'deal_start_time', '');
$Src_frm->addDateField(t_lang('M_FRM_DEAL_ENDS_ON'), 'deal_end_time', '', 'deal_end_time', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$Src_frm->addHiddenField('', 'status', $_REQUEST['status']);
$tipping_point = array(0 => t_lang('M_TXT_ALL'), 1 => t_lang('M_TXT_TIPPED'), 2 => t_lang('M_TXT_NOT_TIPPED'));
$Src_frm->addSelectBox(t_lang('M_TXT_TIPPING_POINT'), 'deal_tipped_at', $tipping_point, '', '');
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="company-deals.php"');
$fld = $Src_frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
$fld->merge_cells = 4;
$fld->fldCellExtra = 'style="text-align:center;"';
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 15;
$post = getPostedData();
$mainTableName = 'tbl_deals';
$primaryKey = 'deal_id';
$colPrefix = 'deal_';
if (is_numeric($_GET['cancel'])) {
    if (!$db->update_from_array($mainTableName, [$colPrefix . 'status' => 3], $primaryKey . '=' . $_GET['cancel'])) {
        $msg->addError($db->getError());
    } else {
        $msg->addMsg(t_lang('M_TXT_SUCCESSFULLY_CANCELLED'));
        redirectUser(CONF_WEBROOT_URL . 'merchant/company-deals.php?status=cancelled&page=1');
    }
}
$srch = new SearchBase('tbl_deals', 'd');
$srch->addCondition('deal_deleted', '=', 0);
$srch->addCondition('deal_company', '=', $_SESSION['logged_user']['company_id']);
if ($_GET['status'] != 'incomplete') {
    $srch->joinTable('tbl_cities', 'INNER JOIN', 'd.deal_city=c.city_id', 'c');
}
$srch->joinTable('tbl_companies', 'INNER JOIN', 'd.deal_company=company.company_id', 'company');
if ($_GET['status'] == 'incomplete') {
    $srch->addMultipleFields(['d.*', 'company.*']);
    $srch->addCondition('deal_status', '=', 5);
} else {
    $srch->addMultipleFields(['d.*', 'c.*', 'company.*']);
}
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    if ($post['deal_city'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.deal_city', '=', $post['deal_city'], 'OR');
    }
    if ($post['deal_start_time'] != '') {
        $start_time = date('Y-m-d', strtotime($post['deal_start_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(d.`deal_start_time`)", '>=', $start_time, 'OR', true);
    }
    if ($post['deal_end_time'] != '') {
        $end_time = date('Y-m-d', strtotime($post['deal_end_time']));
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("mysql_func_date(d.`deal_end_time`)", '<=', $end_time, 'OR', true);
    }
    if ($post['deal_tipped_at'] != '') {
        if ($post['deal_tipped_at'] == 1) {
            $cnd = $srch->addDirectCondition('0');
            $cnd->attachCondition("d.`deal_tipped_at`", '!=', "0000-00-00 00:00:00", 'OR', true);
        }
        if ($post['deal_tipped_at'] == 2) {
            $cnd = $srch->addDirectCondition('0');
            $cnd->attachCondition("d.`deal_tipped_at`", '=', "0000-00-00 00:00:00", 'OR', true);
        }
    }
    if ($post['deal_cat'] != '') {
        $catCode = fetchCatCode($post['deal_cat']);
        $srch->joinTable('tbl_deal_to_category', 'INNER JOIN', 'd.deal_id=doc.dc_deal_id ', 'doc');
        $srch->joinTable('tbl_deal_categories', 'INNER JOIN', 'doc.dc_cat_id=dc.cat_id ', 'dc');
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('dc.cat_code', 'LIKE', $catCode . "%", 'OR');
        $srch->addGroupBy('d.deal_id');
    }
    if ($post['deal_type'] != '') {
        $type = explode('-', $post['deal_type']);
        $deal_type = $type[0];
        $deal_sub_type = $type[1];
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition("d.`deal_type`", '=', $deal_type, 'OR', true);
        $cnd->attachCondition("d.`deal_sub_type`", '=', $deal_sub_type, 'AND', true);
    }
    $Src_frm->fill($post);
}
$status = $_REQUEST['status'];
/* * **	Reposting a new deal	*** */
$get = getQueryStringData();
if ($status == 'expired') {
    date_default_timezone_set(CONF_TIMEZONE);
    $current_date_format = CONF_DATE_FORMAT_PHP . " H:i:s";
    if (isset($get['old_deal_id']) && $get['old_deal_id'] != "") {
        //get old deal id
        $old_deal_id = $get['old_deal_id'];
        /*         * ********		Start Adding deal data into tbl_deals		*************** */
        $srchDeal = new SearchBase('tbl_deals');
        $srchDeal->addCondition('deal_id', '=', $old_deal_id);
        $rs1 = $srchDeal->getResultSet();
        $row1 = $db->fetch($rs1);
        if (isset($row1['deal_id']) && isset($row1['deal_is_duplicate']) && $row1['deal_is_duplicate'] == 0) {
            //remove old_deal_id
            unset($row1['deal_id']);
            $row1['deal_id'] = '';
            $row1['deal_start_time'] = displayDate(date($current_date_format), true, false);
            $row1['deal_end_time'] = displayDate(date($current_date_format, strtotime('+1 week')), true, false);
            if (CONF_VOUCHER_START_DATE == 0) {
                $row1['voucher_valid_from'] = $row1['deal_start_time'];
            }
            if (CONF_VOUCHER_START_DATE == 1) {
                $row1['voucher_valid_from'] = $row1['deal_end_time'];
            }
            $old_deal_end_time = $row1['deal_end_time'];
            $days = CONF_VOUCHER_END_DATE;
            $row1['voucher_valid_till'] = date($current_date_format, strtotime("+$days day", strtotime($old_deal_end_time)));
            $row1['deal_addedon'] = displayDate(date($current_date_format), true, false);
            $row1['deal_status'] = 5;
            $row1['deal_main_deal'] = 0;
            $row1['deal_tipped_at'] = '';
            $row1['deal_paid_date'] = '';
            $data = [];
            foreach ($row1 as $key => $val) {
                $data[$key] = $val;
            }
            /** 		inserting prepared data into tbl_deals		* */
            $record = new TableRecord('tbl_deals');
            $record->assignValues($data);
            $record->addNew();
            $new_deal_id = $record->getId();
            /*             * ********		End Adding deal data into tbl_deals		*************** */
            /*             * ********		Start Adding deal data into tbl_deal_to_category		*************** */
            /** 	getting data from tbl_deal_to_category	* */
            $srchDeal = new SearchBase('tbl_deal_to_category');
            $srchDeal->addCondition('dc_deal_id', '=', $old_deal_id);
            $rs1 = $srchDeal->getResultSet();
            while ($rowAddress = $db->fetch($rs1)) {
                $data = [];
                foreach ($rowAddress as $key => $val) {
                    if ($key == 'dc_deal_id' && $val == $old_deal_id) {
                        $val = $new_deal_id;
                    }
                    $data[$key] = $val;
                }
                /** 		inserting prepared data into tbl_deal_to_category		* */
                $record = new TableRecord('tbl_deal_to_category');
                $record->assignValues($data);
                $record->addNew();
            }
            /*             * ********		End Adding deal data into tbl_deal_to_category		*************** */
            /*             * ********		Adding deal data into tbl_deal_address_capacity		*************** */
            /** 		getting data from tbl_deal_address_capacity		* */
            $srchDeal = new SearchBase('tbl_deal_address_capacity');
            $srchDeal->addCondition('dac_deal_id', '=', $old_deal_id);
            $rs1 = $srchDeal->getResultSet();
            while ($rowAddress = $db->fetch($rs1)) {
                $data = [];
                foreach ($rowAddress as $key => $val) {
                    if ($key == 'dac_deal_id' && $val == $old_deal_id)
                        $val = $new_deal_id;
                    if ($key == 'dac_id') {
                        //remove dac_id				
                        unset($rowAddress['dac_id']);
                    } else {
                        $data[$key] = $val;
                    }
                }
                /** 		inserting prepared data into tbl_deal_address_capacity		* */
                $record = new TableRecord('tbl_deal_address_capacity');
                $record->assignValues($data);
                $record->addNew();
            }
            /*             * ********		Adding deal data into tbl_deal_address_capacity		*************** */
            $record = new TableRecord('tbl_deals');
            $record->setFldValue('deal_is_duplicate', 1);
            $record->update('deal_id' . '=' . $old_deal_id);
            $msg->addMsg(t_lang('M_TXT_REPOST_DEAL_UPDATE_SUCCESSFUL'));
            redirectUser(CONF_WEBROOT_URL . 'merchant/company-deals.php?status=approval');
        }
        $msg->addError(t_lang('M_TXT_REPOST_DEAL_ALREADY_REPOSTED_OR_DOESNT_EXIST'));
        redirectUser(CONF_WEBROOT_URL . 'merchant/company-deals.php');
    }
}
/* * **	Reposting a new deal	*** */
if ($status == 'upcoming') {
    $srch->addCondition('deal_status', '=', 0);
    $srch->addCondition('deal_complete', '=', 1);
} else if ($status == 'active') {
    $srch->addCondition('deal_status', '=', 1);
    $srch->addCondition('deal_complete', '=', 1);
} else if ($status == 'expired') {
    $srch->addCondition('deal_status', '=', 2);
} else if ($status == 'approval') {
    $srch->addCondition('deal_status', '=', 5);
    $srch->addCondition('deal_complete', '=', 1);
} else if ($status == 'incomplete') {
    $srch->addCondition('deal_complete', '=', 0);
    $srch->addCondition('deal_status', '=', 5);
} else if ($status == 'cancelled') {
    $srch->addCondition('deal_status', '=', 3);
} else if ($status == 'rejected') {
    $srch->addCondition('deal_status', '=', 6);
} else if ($status == 'unsettled') {
    $srch->addCondition('deal_paid', '=', 0);
    $srch->addCondition('deal_status', '=', 2);
    if ($_SESSION['logged_user']['company_id'] > 0)
        $srch->addCondition('deal_company', '=', $_SESSION['logged_user']['company_id']);
} else if ($status == 'new') {
    //$srch->addCondition('deal_status', '=', 6);
} else {
    $srch->addCondition('deal_status', '=', 1);
}
$srch->addOrder('d.deal_start_time', 'desc');
$srch->addOrder('d.deal_status');
$srch->addOrder('d.deal_name');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'status'), array('page' => '', 'status' => $_REQUEST['status']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'deal_img_name' => t_lang('M_FRM_DEAL_IMAGE'),
    'deal_name' => t_lang('M_TXT_DEAL') . ' ' . t_lang('M_FRM_TITLE'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
if ($_REQUEST['status'] == "") {
    $class = 'class="selected"';
} else {
    $tabStatus = $_REQUEST['status'];
    $class = '';
    $tabClass = 'class="selected"';
}
$company_id = $_SESSION['logged_user']['company_id'];
if (is_numeric($_REQUEST['status']) > 0) {
    $deal_id = $_REQUEST['status'];
} else {
    $deal_id = 0;
}
$arr_bread = array(
    '' => t_lang('M_TXT_DEALS_PRODUCTS'),
);
?>
<script type="text/javascript" charset="utf-8">
    var txtload = "<?php echo addslashes(t_lang('M_TXT_LOADING')); ?>";
    var txtreload = "<?php echo addslashes(t_lang('M_TXT_PLEASE_RELOAD_PAGE_AND_TRY_AGAIN')); ?>";
    var txtoops = "<?php echo addslashes(t_lang('M_TXT_INTERNAL_ERROR')); ?>";
    var txtselectadd = "<?php echo addslashes(t_lang('M_TXT_PLEASE_CHECK_ATLEAST_ONE_ADDRESS')); ?>";
</script>
<link href="<?php echo CONF_WEBROOT_URL; ?>css/prettyPhoto.css" rel="stylesheet" type="text/css" />
<script src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $(" a[rel^='prettyPhoto']").prettyPhoto({theme: 'facebook', social_tools: false});
    });
</script>
<ul class="nav-left-ul">
    <li ><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=active&page=' . $page); ?>" <?php echo ($tabStatus == 'active') ? $tabClass : $class; ?>><?php echo t_lang('M_TXT_ACTIVE'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=expired&page=1'); ?>" <?php echo ($tabStatus == 'expired') ? $tabClass : ''; ?>><?php echo t_lang('M_TXT_EXPIRED'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=upcoming&page=1'); ?>" <?php echo ($tabStatus == 'upcoming') ? $tabClass : ''; ?>><?php echo t_lang('M_TXT_UPCOMING'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=approval&page=1'); ?>" <?php echo ($tabStatus == 'approval') ? $tabClass : ''; ?>><?php echo t_lang('M_TXT_UNAPPROVED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=rejected&page=1'); ?>" <?php echo ($tabStatus == 'rejected') ? $tabClass : ''; ?>><?php echo t_lang('M_TXT_REJECTED'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=cancelled&page=1'); ?>" <?php echo ($tabStatus == 'cancelled') ? $tabClass : ''; ?>><?php echo t_lang('M_TXT_CANCELLED'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=incomplete&page=1'); ?>" <?php echo ($tabStatus == 'incomplete') ? $tabClass : ''; ?>><?php echo t_lang('M_TXT_INCOMPLETE'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/company-deals.php?status=unsettled&page=1'); ?>" title="<?php echo t_lang('M_TXT_UNSETTLED_DEALS_TOOL_TIP'); ?>" <?php echo ($tabStatus == 'unsettled') ? $tabClass : ''; ?>><?php echo t_lang('M_TXT_UNSETTLED'); ?>  <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
    <li><a href="<?php echo (CONF_WEBROOT_URL . 'merchant/add-deals.php?add=new&page=1'); ?>" <?php echo ($_GET['status'] == 'new') ? 'class="active"' : ''; ?>><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
</ul>
</div></td>
<td class="right-portion"><?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php
            if ($_REQUEST['status'] == 'approval') {
                echo t_lang('M_TXT_UNAPPROVED');
            } else {
                echo ucfirst($_REQUEST['status']);
            }
            ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?> </div>
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
    <div class="box searchform_filter"><div class="title"><?php
            if ($_REQUEST['status'] == 'approval') {
                echo t_lang('M_TXT_UNAPPROVED');
            } else {
                echo ucfirst($_REQUEST['status']);
            }
            ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?>  </div><div class="content togglewrap" style="display:none;">	<?php echo $Src_frm->getFormHtml(); ?></div>	 </div>	 
    <div class="contentgroup">	
        <?php require_once('./inc.deal-list.php'); ?> 
    </div>
    <?php if ($srch->pages() > 1) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
</td>
<script>
    var deletemsg = '<?php echo addslashes(t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE')); ?>';
</script>
<?php
require_once './footer.php';
