<?php
require_once './application-top.php';
checkAdminPermission(8);
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$pagesize = CONF_ADMIN_PAGE_SIZE;
$post = getPostedData();
/*
 * BUSINESS REFERRAL SERACH FORM
 */
$srchForm = new Form('Src_frm', 'Src_frm');
$srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$srchForm->setFieldsPerRow(2);
$srchForm->setLeftColumnProperties('width="40%"');
$srchForm->captionInSameCell(true);
$srchForm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$srchForm->addHiddenField('', 'mode', 'search');
$fld1 = $srchForm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="business-referral.php"');
$fld = $srchForm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
/*
 * DOWNLOAD XLS FOR THE Business Referral
 */
###### DOWNLOAD XLS FOR THE Business Referral ##########
$arr_listing = ['br_id' => t_lang('M_TXT_BUSINESS_ID'),
    'br_person_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_CLIENT_NAME'),
    'br_email' => t_lang('M_TXT_CLIENT_EMAIL'),
    'br_phone' => t_lang('M_TXT_CLIENT_PHONE'),
    'br_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_BUSINESS_NAME'),
    'br_website' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_BUSINESS_WEBSITE'),
    'br_zip' => t_lang('M_TXT_BUSINESS_ZIP'),
    'br_country' => t_lang('M_TXT_BUSINESS_COUNTRY'),
    'br_category_type' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_BUSINESS_TYPE'),
    'br_address' . $_SESSION['lang_fld_prefix'] => t_lang('M_FRM_ADDRESS_OF_THE_BUSINESS'),
    'br_review' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_ABOUT_BUSINESS'),
];
if ($_GET['mode'] == 'downloadcsv') {
    if ((checkAdminAddEditDeletePermission(8, '', 'add')) || (checkAdminAddEditDeletePermission(8, '', 'edit'))) {
        global $db;
        $srch = new SearchBase('tbl_business_referral', 'br');
        $rs_listing = $srch->getResultSet();
        $fname = time() . '_business_referral.CSV';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"" . $fname . "\";");
        header("Content-Transfer-Encoding: binary");
        $fp = fopen(TEMP_XLS_PATH . $fname, 'w+');
        if (!$fp) {
            die(t_lang('M_TXT_FILE_NOT_CREATED'));
        }
        fputcsv($fp, $arr_listing);
        while ($row = $db->fetch($rs_listing)) {
            $arr = [];
            foreach ($arr_listing as $key => $val) {
                switch ($key) {
                    case 'br_id':
                        $arr[] = $row[$key];
                        break;
                    case 'br_name':
                        $arr[] = $row[$key];
                        break;
                    case 'br_email':
                        $arr[] = $row[$key];
                        break;
                    case 'br_address':
                        $arr[] = $row[$key];
                        break;
                    case 'br_review':
                        $arr[] = $row[$key];
                        break;
                    default:
                        $arr[] = $row[$key];
                        break;
                }
            }
            if (count($arr) > 0) {
                fputcsv($fp, $arr);
            }
        }
        fclose($fp);
        header("Content-Length: " . filesize(TEMP_XLS_PATH . $fname));
        readfile(TEMP_XLS_PATH . $fname);
        exit;
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
###### DOWNLOAD XLS FOR THE Business Referral ##########
/**
 * BUSINESS REFERRAL SEARCH LISTING
 * */
$srch = new SearchBase('tbl_business_referral', 'br');
$srch->addOrder('br_id', 'desc');
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('br.br_person_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('br.br_person_lname', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('br.br_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('br.br_phone', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('br.br_name', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('br.br_category_type', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('br.br_country', 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $srchForm->fill($post);
}
/**
 * BUSINESS REFERRAL PAGINATION
 * */
$srch->setPageSize($pagesize);
$srch->setPageNumber($page);
$srch->addFld('br.*');
$business_listing = $srch->getResultSet();
$pagestring = '';
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'keyword'], ['page' => '', 'keyword' => $_REQUEST['keyword']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
		' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
/**
 * BUSINESS REFERRAL DELETE MODE
 * */
if (isset($_GET['delete']) && $_GET['delete'] != "") {
    if ((checkAdminAddEditDeletePermission(8, '', 'delete'))) {
        $br_id = $_GET['delete'];
        $db->query("DELETE FROM tbl_business_referral WHERE br_id =$br_id");
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED_SUCCESSFULLY'));
        redirectUser('?page=' . $page);
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_USERS'),
    '' => t_lang('M_TXT_BUSINESS_REFERRAL')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_BUSINESS_REFERRAL'); ?> <?php echo t_lang('M_TXT_LISTING'); ?> </div>
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
    <div class="box searchform_filter"><div class="title"><?php echo t_lang('M_TXT_BUSINESS_REFERRAL'); ?> <?php echo t_lang('M_TXT_LISTING'); ?></div><div class="content togglewrap" style="display:none;">		<?php echo $srchForm->getFormHtml(); ?></div></div>	
    <table class="tbl_data" width="100%">
        <thead>
            <tr>
                <th ><?php echo t_lang('M_TXT_BUSINESS_NAME'); ?></th>
                <th><?php echo t_lang('M_TXT_BUSINESS_PHONE'); ?></th>
                <th><?php echo t_lang('M_TXT_BUSINESS_EMAIL'); ?></th>
                <th><?php echo t_lang('M_FRM_BUSINESS_NAME'); ?></th>
                <th><?php echo t_lang('M_TXT_BUSINESS_TYPE'); ?></th>
                <th><?php echo t_lang('M_FRM_COUNTRY'); ?></th>
                <th><?php echo t_lang('M_TXT_ACTION'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $db->fetch($business_listing)) {
                ?>
                <tr>	
                    <td  ><?php
                        echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['br_person_name'] . ' ' . $row['br_person_lname'] . '<br/>';
                        echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['br_person_name_lang1'];
                        ?></td>
                    <td  ><?php echo $row['br_phone']; ?></td>
                    <td  id="comment<?php echo $row['br_id'] ?>"><?php echo $row['br_email']; ?></td>
                    <td  ><?php echo $row['br_name' . $_SESSION['lang_fld_prefix']]; ?></td>
                    <td  ><?php echo $row['br_category_type' . $_SESSION['lang_fld_prefix']]; ?></td>
                    <td  ><?php echo $row['br_country']; ?></td>
                    <td width="10%"><ul class="listing_option actions" id="comment-status<?php echo $row['br_id'] ?>">
                            <?php if ((checkAdminAddEditDeletePermission(8, '', 'delete'))) { ?>
                                <li><a href="business-referral.php?delete=<?php echo $row['br_id'] ?>" title="<?php echo t_lang('M_TXT_DELETE') ?>" onclick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);"><i class="ion-android-delete icon"></i></a></li>
                            <?php } ?>
                        </ul></td>
                </tr>
                <?php
            }
            if ($db->total_records($business_listing) == 0) {
                echo '<tr><td colspan="7">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </tbody>
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
<?php
require_once './footer.php';
