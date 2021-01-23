<?php
require_once './application-top.php';
checkAdminPermission(8);
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$pagesize = CONF_ADMIN_PAGE_SIZE;
$post = getPostedData();
/*
 * GET CITIES FROM DB
 */
$cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0 order by city_name asc");
$cities_arr = $db->fetch_all_assoc($cityList);
/*
 * NEWSLETTER-SUBSCRIBERS SERACH FORM
 */
$srchForm = new Form('Src_frm', 'Src_frm');
$srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$srchForm->setFieldsPerRow(3);
$srchForm->setLeftColumnProperties('');
$srchForm->captionInSameCell(true);
$srchForm->addTextBox(t_lang('M_FRM_EMAIL_ADDRESS'), 'subs_email', '', '', '');
$srchForm->addHiddenField('', 'mode', 'search');
$srchForm->addSelectBox(t_lang('M_TXT_SELECT_YOUR_CITY'), 'city', $cities_arr, $_REQUEST['city'], ' ', t_lang('M_TXT_SELECT'), 'city_selector');
$fld1 = $srchForm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="newsletter-subscribers.php?city=' . $_REQUEST['city'] . '"');
$fld = $srchForm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
/*
 * DOWNLOAD XLS FOR THE NEWSLETTER SUBSCRIBERS
 */
###### DOWNLOAD XLS FOR THE NEWSLETTER SUBSCRIBERS ##########
$arr_listing = array('subs_id' => t_lang('M_TXT_SUBSCRIBERS_ID'),
    'subs_email' => t_lang('M_FRM_EMAIL_ADDRESS'),
    'subs_city' => t_lang('M_FRM_CITY'),
    'subs_addedon' => t_lang('M_TXT_DATE_OF_SUBSCRIPTION'),
    'subs_email_verified' => t_lang('M_TXT_IS_VERIFIED'),
);
$frm_csv = new Form('frmSubscribersCSV');
$frm_csv->setAction($_SERVER['REQUEST_URI']);
$frm_csv->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
$frm_csv->setLeftColumnProperties('width="40%"');
$frm_csv->captionInSameCell(false);
$frm_csv->setFieldsPerRow(1);
$frm_csv->addFileUpload('Subscribers CSV File', 'subscribers_csv');
$frm_csv->addSubmitButton('&nbsp;', 'submit', 'Submit', '', 'class="inputbuttons" title="Submit"');
###################IMPORT FEATURE START HERE ##############################
if (is_uploaded_file($_FILES['subscribers_csv']['tmp_name'])) {
    $accepted_files = array('.csv');
    $ext = strtolower(strrchr($_FILES['subscribers_csv']['name'], '.'));
    if (in_array($ext, $accepted_files)) {
        $fp = fopen($_FILES['subscribers_csv']['tmp_name'], 'r');
        $arr = fgetcsv($fp);
        if (count($arr) != 1) {
            $msg->addError('Number of columns in csv must be 1. Your file has ' . count($arr));
        } else {
            $arr_question = [];
            $countUser = 0;
            while ($arr = fgetcsv($fp)) {
                $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email='" . trim($arr[0]) . "' and  subs_city='" . trim($_REQUEST['city']) . "'");
                $result = $db->fetch($check_unique);
                if ($db->total_records($check_unique) == 0) {
                    $countUser++;
                    $record = new TableRecord('tbl_newsletter_subscription');
                    $record->setFldValue('subs_email', trim($arr[0]));
                    $record->setFldValue('subs_city', $_REQUEST['city']);
                    $code = mt_rand(0, 999999999999999);
                    $record->setFldValue('subs_addedon', date('Y-m-d H:i:s'), true);
                    $record->setFldValue('subs_code', $code, '');
                    $record->setFldValue('subs_email_verified', '1', '');
                    $record->addNew();
                }
            }
            $msg->addMsg('File Imported with ' . $countUser . ' subscribers.');
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $msg->addError('Please choose .csv file only.');
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}
###################IMPORT FEATURE END HERE ##############################
$frm = new Form('city_form');
$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form"');
$frm->captionInSameCell(false);
$frm->setLeftColumnProperties('width="40%"');
$frm->setFieldsPerRow(1);
$frm->addSelectBox(t_lang('M_TXT_SELECT_YOUR_CITY'), 'city_selector', $cities_arr, $_REQUEST['city'], ' ', '', 'city_selector');
/** ### * */
if ($_REQUEST['mode'] == 'downloadcsv') {
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        global $db;
        $srch = new SearchBase('tbl_newsletter_subscription', 'ns');
        if (isset($_POST['listing_id'])) {
            $srch->addCondition('subs_id', 'IN', $_POST['listing_id']);
        }
        if ($_REQUEST['city'] != "") {
            $srch->addCondition('subs_city', '=', $_REQUEST['city']);
        }
        if (isset($_REQUEST['affiliate'])) {
            $srch->addCondition('subs_affiliate_id', '=', intval($_REQUEST['affiliate']));
        }
        $srch->addOrder('subs_id', 'desc');
        $rs_listing = $srch->getResultSet();
        $fname = time() . '_newsletter_subscribers.csv';
        $fp = fopen(TEMP_XLS_PATH . $fname, 'w+');
        if (!$fp)
            die('Could not create file in temp-images directory. Please check permissions');
        fputcsv($fp, $arr_listing);
        while ($row = $db->fetch($rs_listing)) {
            $arr = [];
            foreach ($arr_listing as $key => $val) {
                switch ($key) {
                    case 'subs_id':
                        $arr[] = $row[$key];
                        break;
                    case 'subs_email':
                        $arr[] = $row[$key];
                        break;
                    case 'subs_city':
                        //$arr[]=$row[$key];
                        $citysrch = new SearchBase('tbl_cities', 'c');
                        $citysrch->addCondition('city_id', '=', $row[$key]);
                        $city_listing = $citysrch->getResultSet();
                        while ($row1 = $db->fetch($city_listing)) {
                            $arr[] = $row1['city_name'];
                        }
                        break;
                    case 'subs_addedon':
                        $arr[] = $row[$key];
                        break;
                    case 'subs_email_verified':
                        if ($row[$key] == 1) {
                            $arr[] = 'Verified';
                        } else {
                            $arr[] = 'Verification Pending';
                        }
                        break;
                    default:
                        $arr[] = $row[$key];
                        break;
                }
            }
            if (count($arr) > 0)
                fputcsv($fp, $arr);
        }
        fclose($fp);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Disposition: attachment; filename=\"" . $fname . "\";");
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize(TEMP_XLS_PATH . $fname));
        readfile(TEMP_XLS_PATH . $fname);
        exit;
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
###### DOWNLOAD XLS FOR THE NEWSLETTER SUBSCRIBERS ##########
$srch = new SearchBase('tbl_newsletter_subscription', 'dd');
$srch->addOrder('subs_id', 'desc');
if (isset($_REQUEST['affiliate'])) {
    $srch->addCondition('subs_affiliate_id', '=', intval($_REQUEST['affiliate']));
}
$srch->joinTable('tbl_cities', 'LEFT OUTER JOIN', 'dd.subs_city=c.city_id', 'c');
//paging
if ($_REQUEST['city'] != "") {
    $srch->addCondition('subs_city', '=', $_REQUEST['city']);
}
if ($post['mode'] == 'search') {
    if ($post['subs_email'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('subs_email', 'like', '%' . $post['subs_email'] . '%', 'OR');
    }
    $srchForm->fill($post);
}
$srch->setPageSize($pagesize);
$srch->setPageNumber($page);
//paging
$navigation_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', array('page', 'city', 'subs_email'), array('page' => '', 'city' => $_REQUEST['city'], 'subs_email' => $_REQUEST['subs_email']));
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
			' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    if (checkAdminAddEditDeletePermission(8, '', 'delete')) {
        $subs_id = intval($_GET['delete']);
        if (deleteSubscriber($subs_id)) {
            /* function write in the site-function.php */
            $msg->addMsg(t_lang('M_TXT_RECORD_DELETED_SUCCESSFULLY'));
            redirectUser('?city=' . $_GET['city'] . '&page=' . $page);
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'registered-members.php' => t_lang('M_TXT_USERS'),
    '' => t_lang('M_TXT_SUBSCRIBERS')
);
?>
<script type="text/javascript">
    txtselectfirst = '<?php echo addslashes(t_lang('M_TXT_PLZ_SELECT_RECORD')); ?>';
</script>
</div></td>
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="clear"></div>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_SUBSCRIBERS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?>
            <ul class="actions right">
                <li class="droplink">
                    <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                    <div class="dropwrap">
                        <ul class="linksvertical">
                            <li><a href="?mode=downloadcsv&affiliate=<?php echo $_REQUEST['affiliate']; ?>&city=<?php echo $_GET['city']; ?>"><?php echo t_lang('M_TXT_DOWNLOAD_COMPLETE_LIST'); ?></a></li>
                            <li><a href="javascript:void(0);" onclick="return downloadSelected();"><?php echo t_lang('M_TXT_DOWNLOAD_SELECTED_LIST'); ?></a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
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
    /* if ( $_REQUEST['city'] > 0 || $city > 0 ) {
      if(checkAdminAddEditDeletePermission(8,'','add')) {
      ?>
      <div class="box"><div class="title"> Import Subscribers </div><div class="content"><?php echo  $frm_csv->getFormHtml();?></div></div>
      <?php
      }
      } */
    ?>	
<!-- <div class="box"><div class="title"> Select City </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div> -->
    <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_SUBSCRIBERS'); ?> <?php echo t_lang('M_TXT_LISTING'); ?>  </div><div class="content togglewrap" style="display:none;"><?php echo $srchForm->getFormHtml(); ?>
        </div></div>
    <table class="tbl_data" width="100%"> 
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form_listing">
            <input type="hidden" name="mode" value="downloadcsv" />
            <?php if (isset($_REQUEST['affiliate'])) { ?>
                <input type="hidden" name="affiliate" value="<?php echo $_REQUEST['affiliate']; ?>" />
            <?php } ?>
            <input type="hidden" name="city" value="<?php echo $_REQUEST['city']; ?>" />
            <thead>
                <tr>
                    <th ><input type="checkbox" name="checkbox5" id="checkbox5" onClick="checkAllCheckBoxes(document.form_listing.elements['listing_id'], this.checked);" /><a class="selectAll" href="javascript:void(0);" title="<?php echo t_lang('M_TXT_DELETE') ?>" onclick="deleteMultipleRecords();"><i class="ion-android-delete icon"></i></a></th>
                    <th ><?php echo t_lang('M_FRM_EMAIL_ADDRESS'); ?></th>
                    <th ><?php echo t_lang('M_FRM_CITY'); ?> </th>
                    <th ><?php echo t_lang('M_TXT_ADDED_ON'); ?> </th>
                    <th><?php echo t_lang('M_TXT_ACTION'); ?> </th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $db->fetch($navigation_listing)) {
                    ?>
                    <tr>	
                        <td width="3%"><input type="checkbox" name="listing_id[]" value="<?php echo $row['subs_id']; ?>" id="listing_id" /></td>
                        <td width="20%"><?php echo $row['subs_email']; ?></td>
                        <td width="20%"><?php echo $row['city_name']; ?></td>
                        <td width="15%"><?php echo displayDate($row['subs_addedon'], true, '', ''); ?></td>
                        <td width="10%"><ul class="listing_option actions" id="comment-status<?php echo $row['subs_id'] ?>">
                                <?php if (checkAdminAddEditDeletePermission(8, '', 'delete')) { ?>
                                    <li><a href="newsletter-subscribers.php?city=<?php echo $_REQUEST['city'] ?>&delete=<?php echo $row['subs_id'] ?>" title="<?php echo t_lang('M_FRM_DELETE') . ' ' . t_lang('M_TXT_SUBSCRIBERS'); ?>" onclick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);"><i class="ion-android-delete icon"></i></a></li>
                                <?php } ?>
                            </ul></td>
                    </tr>
                    <?php
                }
                if ($db->total_records($navigation_listing) == 0)
                    echo '<tr><td colspan="5">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                ?>
            </tbody>
        </form>	
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
<script>
    function deleteMultipleRecords() {
        if ($('[name="listing_id[]"]:checked').length == 0) {
            requestPopup(this, '<?php echo (t_lang('M_MSG_please_select_at_least_one_Email_ID')); ?>', 0);
            return false;
        }
        requestPopupAjax(1, '<?php echo addslashes(t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD')); ?>', 1);
    }
    function doRequiredAction(t) {
        if ($('[name="listing_id[]"]:checked').length == 0) {
            requestPopup(this, '<?php echo (t_lang('M_MSG_please_select_at_least_one_Email_ID')); ?>', 0);
            return false;
        }
        zone_ids = $('.tbl_data input[type="checkbox"]').serialize();
        callAjax('cities-ajax.php', zone_ids + '&mode=deleteSubscribedUsers', function (t) {
            var ans = parseJsonData(t);
            if (ans) {
                jQuery.facebox(function () {
                    $.facebox(ans.msg)
                    setTimeout(function () {
                        location.reload()
                    }, 1500);
                });
            }
        });
    }
</script>				  
<?php require_once './footer.php'; ?>
