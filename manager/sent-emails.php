<?php
require_once './application-top.php';
checkAdminPermission(1);
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
$pagesize = 10;
$post = getPostedData();
$srcFrm = new Form('srcFrm', 'Src_frm');
$srcFrm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$srcFrm->setFieldsPerRow(2);
$srcFrm->setLeftColumnProperties('width="40%"');
$srcFrm->captionInSameCell(true);
$srcFrm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$srcFrm->addHiddenField('', 'mode', 'search');
$fld1 = $srcFrm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="medium" onclick=location.href="sent-emails.php"');
$fld = $srcFrm->addSubmitButton('', 'search', t_lang('M_TXT_SEARCH'), '', ' class="medium"')->attachField($fld1);
$sentEmail = new SearchBase('tbl_email_archives');
if ($post['mode'] == 'search') {
    if ($post['keyword'] != '') {
        $cnd = $sentEmail->addDirectCondition('0');
        $cnd->attachCondition('emailarchive_subject' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('emailarchive_to_email', 'like', '%' . $post['keyword'] . '%', 'OR');
        $cnd->attachCondition('emailarchive_body' . $_SESSION['lang_fld_prefix'], 'like', '%' . $post['keyword'] . '%', 'OR');
    }
    $srcFrm->fill($post);
}
if (is_numeric($_REQUEST['view'])) {
    $sentEmail->addCondition('emailarchive_id', '=', $_REQUEST['view']);
}
$sentEmail->setPageNumber($page);
$sentEmail->setPageSize($pagesize);
$sentEmail->addOrder('emailarchive_id', 'desc');
$page_listing = $sentEmail->getResultSet();
$pagestring = '';
$pages = $sentEmail->pages();
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page', 'keyword'], ['page' => '', 'keyword' => $_REQUEST['keyword']]);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $sentEmail->recordCount()) ? $sentEmail->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $sentEmail->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $sentEmail->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'sent-emails.php' => t_lang('M_SENT_EMAILS')
];
require_once './header.php';
$arr_listing_fields = [
    'emailarchive_subject' . $_SESSION['lang_fld_prefix'] => t_lang('M_TXT_SUBJECT'),
    'emailarchive_to_email' => t_lang('M_TXT_SENT_TO'),
    'emailarchive_headers' => t_lang('M_TXT_EMAIL_HEADERS'),
    'emailarchive_sent_on' => t_lang('M_TXT_SENT_ON'),
    'action' => t_lang('M_TXT_ACTION')
];
?>	</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="clear"></div>
    <div class="div-inline">
        <div class="page-name"><?php
            if (is_numeric($_GET['view'])) {
                echo t_lang('M_TXT_Sent_Email_Detail');
            } else {
                echo t_lang('M_TXT_LIST_OF_SENT_EMAILS');
            }
            ?> 
        </div>
    </div>				
    <?php
    if (is_numeric($_GET['view'])) {
        $data = $db->fetch($page_listing);
        ?>
        <section class="section">
            <div class="sectionhead">
                <h4></h4>
            </div>
            <div class="sectionbody space">
                <div class="border-box border-box--space">
                    <div class="repeatedrow">
                        <div class="rowbody">
                            <div class="listview">
                                <dl class="list">
                                    <dt><?php echo t_lang('M_TXT_Template_Name'); ?></dt>
                                    <dd><?php echo $data['emailarchive_tpl_name']; ?></dd>
                                </dl>
                                <dl class="list">
                                    <dt><?php echo t_lang('M_TXT_Subject'); ?></dt>
                                    <dd><?php echo $data['emailarchive_subject']; ?></dd>
                                </dl>			
                                <dl class="list">
                                    <dt><?php echo t_lang('M_TXT_Sent_On'); ?></dt>
                                    <dd><?php echo $data['emailarchive_sent_on']; ?></dd>
                                </dl>						
                                <dl class="list">
                                    <dt><?php echo t_lang('M_TXT_Sent_To'); ?></dt>
                                    <dd><?php echo $data['emailarchive_to_email']; ?></dd>
                                </dl>						
                                <dl class="list">
                                    <dt><?php echo t_lang('M_TXT_Headers'); ?></dt>
                                    <dd><?php echo $data['emailarchive_headers']; ?></dd>
                                </dl>						
                                <dl class="list">
                                    <dt><?php echo t_lang('M_TXT_Content'); ?></dt>
                                    <dd></dd>
                                </dl>
                                <?php echo $data['emailarchive_body']; ?>	
                            </div>	
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php } else { ?>
        <div class="box searchform_filter"><div class="title"> <?php echo t_lang('M_TXT_LIST_OF_SENT_EMAILS'); ?> </div><div class="content togglewrap" style="display:none;"><?php echo $srcFrm->getFormHtml(); ?>
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
                            case 'action':
                                echo '<ul class="actions">';
                                echo '<li><a href="sent-emails.php?view=' . $row['emailarchive_id'] . '" title="' . t_lang('M_TXT_View') . '" title="' . t_lang('M_TXT_View') . '"><i class="ion-eye icon"></i></a></li>';
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
    <?php } if ($sentEmail->pages() > 1) { ?>
        <div class="footinfo">
            <aside class="grid_1">
                <?php echo $pagestring; ?>	 
            </aside>  
            <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
        </div>
    <?php } ?>
</td>
<?php require_once './footer.php'; ?>
