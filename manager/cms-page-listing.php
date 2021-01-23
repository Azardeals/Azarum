<?php
require_once './application-top.php';
checkAdminPermission(1);
$page = $_REQUEST['page'] ?? 1;
$pagesize = CONF_ADMIN_PAGE_SIZE;
$post = getPostedData();
/*
 * CMS SERACH FORM
 */
$srchForm = new Form('Src_frm', 'Src_frm');
$srchForm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$srchForm->setFieldsPerRow(2);
$srchForm->setLeftColumnProperties('width="40%"');
$srchForm->captionInSameCell(true);
$srchForm->addTextBox('M_FRM_KEYWORD', 'keyword', '', '', '');
$srchForm->addHiddenField('', 'mode', 'search');
$fld1 = $srchForm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="cms-page-listing.php"');
$fld = $srchForm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
updateFormLang($srchForm);
/*
 * CMS DELETE MODE
 */
if ($_GET['delete'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $db->query("update tbl_cms_pages set page_deleted=1 where page_id=" . $_GET['delete']);
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/*
 * CMS PAGE LISTIIG
 */
$pageContentList = new SearchBase('tbl_cms_pages', 'cmspage');
$pageContentList->addCondition('page_deleted', '=', 0);
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $pageContentList->addDirectCondition('0');
        $cnd->attachCondition('cmspage.page_name' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('cmspage.page_url', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('cmspage.page_meta_title' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $srchForm->fill($post);
}
$pageName = 'page_name' . $_SESSION['lang_fld_prefix'];
$pageContentList->setPageNumber($page);
$pageContentList->setPageSize($pagesize);
$pageContentList->addOrder($pageName, 'asc');
$page_listing = $pageContentList->getResultSet();
$pagestring = '';
$pages = $pageContentList->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'keyword'], ['page' => '', 'keyword' => $_REQUEST['keyword']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $pageContentList->recordCount()) ? $pageContentList->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $pageContentList->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $pageContentList->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    '' => t_lang('M_TXT_PAGES')
];
require_once './header.php';
$arr_listing_fields = [
    'page_name' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_PAGE_NAME'),
    'page_url' => t_lang('M_TXT_PAGE_URL'),
    'page_meta_title' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_PAGE_META_TITLE'),
    'page_active' => t_lang('M_FRM_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
];
?>	</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="clear"></div>
    <?php if (!isset($_GET['edit']) && $_GET['add'] != 'new') { ?>
        <div class="div-inline">
            <div class="page-name"><?php echo t_lang('M_TXT_LIST_OF'); ?> <?php echo t_lang('M_TXT_CONTENT'); ?> <?php echo t_lang('M_TXT_PAGES'); ?> 
                <ul class="actions right">
                    <?php if (checkAdminAddEditDeletePermission(9, '', 'add')) { ?> 
                        <li class="droplink">
                            <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                            <div class="dropwrap">
                                <ul class="linksvertical">
                                    <li> 
                                        <a href="cms-page-detail.php?mode1=Add"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
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
    <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_LIST_OF'); ?> <?php echo t_lang('M_TXT_CONTENT'); ?> <?php echo t_lang('M_TXT_PAGES'); ?>  </div><div class="content togglewrap" style="display:none;"><?php echo $srchForm->getFormHtml(); ?>
        </div></div>
    <table class="tbl_data" id="cms-listing" width="100%">
        <thead>
            <tr>                      
                <?php
                foreach ($arr_listing_fields as $val) {
                    echo '<th>' . $val . '</th>';
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $db->fetch($page_listing)) {
                echo '<tr>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td ' . (($key == action) ? 'width="20%"' : '') . '>';
                    switch ($key) {
                        case 'page_name_lang1':
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['page_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['page_name_lang1'];
                            break;
                        case 'page_meta_title_lang1':
                            echo $row['page_meta_title_lang1'];
                            break;
                        case 'page_active':
                            echo $row['page_active'] == '1' ? '<span class="label label-primary">' . t_lang('M_TXT_ACTIVE') . '</span>' : '<span class="label label-danger">' . t_lang('M_TXT_INACTIVE') . '</span>';
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(1, '', 'edit')) {
                                echo '<li><a href="cms-page-detail.php?edit1=' . $row['page_id'] . '" title="' . t_lang('M_TXT_EDIT') . '" title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if (checkAdminAddEditDeletePermission(1, '', 'delete')) {
                                echo '<li><a href="?delete=' . $row['page_id'] . '" title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
            if ($db->total_records($page_listing) == 0) {
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
    </table>
    <?php if ($pageContentList->pages() > 1) { ?>
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
