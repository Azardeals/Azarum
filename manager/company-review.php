<?php
require_once './application-top.php';
checkAdminPermission(3);
loadModels(['CompanyReviewModel']);
$post = getPostedData();
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = CONF_ADMIN_PAGE_SIZE;
/**
 * COMPANY REVIEW SEARCH FORM 
 * */
$srcFrm = CompanyReview::getSearchForm();
/**
 * COMPANY REVIEW FORM 
 * */
$frm = CompanyReview::getForm();
/**
 * COMPANY REVIEW EDIT MODE
 * */
if (is_numeric($_GET['edit'])) {
    $record = new TableRecord(CompanyReview::DB_TBL);
    $record->setFldValue('review_reviews', nl2br($_POST['review_reviews']));
    if (!$record->loadFromDb(CompanyReview::DB_TBL_PRIMARY_KEY . '=' . $_GET['edit'], true)) {
        $msg->addError($record->getError());
    } else {
        $arr = $record->getFlds();
        $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
        if ($row['review_title' . $_SESSION['lang_fld_prefix']] == '') {
            $arr['review_title'] = $deal_name;
        } else {
            $arr['review_title'] = $row['review_title' . $_SESSION['lang_fld_prefix']];
        }
        fillForm($frm, $arr);
        $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
    }
}
/**
 * COMPANY REVIEW DELETE MODE
 * */
if (is_numeric($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(3, '', 'delete')) {
        $db->query('delete from ' . CompanyReview::DB_TBL . ' where reviews_id=' . $_GET['delete']);
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
        redirectUser('company-review.php');
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * COMPANY REVIEW APPROVE MODE
 * */
if (is_numeric($_GET['approve'])) {
    if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
        $compRevwObj = new CompanyReview();
        $compRevwObj->updateStatus($_GET['approve'], 0);
        $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
        redirectUser('company-review.php');
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * COMPANY REVIEW PENDING MODE
 * */
if (is_numeric($_GET['pending'])) {
    if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
        $compRevwObj = new CompanyReview();
        $compRevwObj->updateStatus($_GET['pending'], 1);
        $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
        redirectUser('company-review.php');
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * COMPANY REVIEW POST FORM MODE
 * */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['btn_search']) && isset($_POST['btn_submit'])) {
    if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error)
                $msg->addError($error);
        } else {
            $record = new TableRecord(CompanyReview::DB_TBL);
            $arr_lang_independent_flds = ['reviews_deal_id', 'reviews_deal_company_id', 'reviews_user_id', 'reviews_company_id', 'reviews_approval', 'reviews_type', 'reviews_id', 'mode', 'reviews_added_on', 'btn_submit'];
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            $record->setFldValue('reviews_reviews', nl2br($_POST['reviews_reviews']));
            $record->setFldValue('reviews_deal_id', $_GET['deal_id']);
            $record->setFldValue('reviews_added_on', date('Y-m-d H:i:s'), false);
            $success = ($post[CompanyReview::DB_TBL_PRIMARY_KEY] > 0) ? $record->update(CompanyReview::DB_TBL_PRIMARY_KEY . '=' . $post[CompanyReview::DB_TBL_PRIMARY_KEY]) : ''; //$record->addNew();
            if ($success) {
                $review_id = ($post[CompanyReview::DB_TBL_PRIMARY_KEY] > 0) ? $post[CompanyReview::DB_TBL_PRIMARY_KEY] : $record->getId();
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                redirectUser('company-review.php?company=' . $post['reviews_company_id']);
            } else {
                $msg->addError('Could not add/update! Error: ' . $record->getError());
                $frm->fill($post);
            }
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
/**
 * COMPANY REVIEW SEARCH MODE
 * */
$srch = CompanyReview::getSearchObject();
$srch->joinTable('tbl_companies', 'LEFT JOIN', 'd.reviews_company_id=c.company_id ', 'c');
$srch->addCondition('reviews_type', '=', 2);
$srch->addCondition('reviews_parent_id', '=', 0);
$srch->addOrder('reviews_added_on', 'desc');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
if ($post['mode'] == 'search') {
    if ($post['deal_company'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('c.company_id', '=', $post['deal_company'], 'OR');
    }
    if ($post['rating'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.reviews_rating', '=', $post['rating'], 'OR');
    }
    $srcFrm->fill($post);
}
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
/**
 * COMPANY REVIEW PAGINATION MODE
 * */
$pagestring .= createHiddenFormFromPost('frmPaging', '?', ['page'], ['page' => '']);
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
    ' . getPageString('<li><a href="javascript:void(0);" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_SR_NO'),
    'reviews_reviews' => t_lang('M_TXT_DESCRIPTION'),
    'review_given_by' => t_lang('M_TXT_REVIEW_GIVEN_BY'),
    'reviews_rating' => t_lang('M_TXT_RATING'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'companies.php' => t_lang('M_TXT_COMPANY'),
    '' => t_lang('M_TXT_COMPANY_REVIEW')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_COMPANY_REVIEW'); ?> </div>
    </div>
    <div class="clear"></div>
    <div class="box searchform_filter">
        <div class="title"><?php echo t_lang('M_TXT_COMPANY_REVIEW'); ?> </div>
        <div class="content togglewrap" style="display:none;"><?php echo $srcFrm->getFormHtml(); ?></div>
    </div>
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
    <?php } ?> 
    <?php
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {
        if ((checkAdminAddEditDeletePermission(3, '', 'add')) || (checkAdminAddEditDeletePermission(3, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_COMPANY_REVIEW'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
            <?php
        } else {
            die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
        }
    } else {
        ?>
        <table class="tbl_data" width="100%" style="border: 1px solid rgb(222, 222, 222);">
            <thead>
                <tr>
                    <?php
                    foreach ($arr_listing_fields as $val)
                        echo '<th>' . $val . '</th>';
                    ?>
                </tr>
            </thead>
            <?php
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                $row['reviews_reviews'] = htmlentities($row['reviews_reviews'], ENT_QUOTES, 'UTF-8');
                echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'reviews_reviews':
                            $companyName = $db->query("select company_name" . $_SESSION['lang_fld_prefix'] . " from tbl_companies where company_id=" . $row['reviews_company_id']);
                            $rowCompany = $db->fetch($companyName);
                            echo '<strong>' . t_lang('M_TXT_COMPANY_NAME') . ' : ' . $rowCompany['company_name' . $_SESSION['lang_fld_prefix']] . '</strong><br/>';
                            echo '<strong>' . t_lang('M_TXT_USER_REVIEW') . '</strong> : ' . $row['reviews_reviews'];
                            $dealReply = $db->query("select * from tbl_reviews where reviews_parent_id=" . $row['reviews_id']);
                            $rowReply = $db->fetch($dealReply);
                            $totalReply = $db->total_records($dealReply);
                            if ($db->total_records($dealReply) > 0) {
                                echo '<br/><strong>' . t_lang('M_TXT_COMPANY_REPLY') . '</strong>: ';
                                echo $rowReply['reviews_reviews'];
                            }
                            break;
                        case 'reviews_reviews_lang1':
                            $companyName = $db->query("select company_name" . $_SESSION['lang_fld_prefix'] . " from tbl_companies where company_id=" . $row['reviews_company_id']);
                            $rowCompany = $db->fetch($companyName);
                            echo '<strong>' . $rowCompany['company_name' . $_SESSION['lang_fld_prefix']] . '</strong><br/>';
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['reviews_reviews'] . '<br/>';
                            //echo '<strong>'.$arr_lang_name[1].'</strong>'. ' ' .$row['reviews_reviews_lang1'];
                            break;
                        case 'reviews_rating':
                            echo '<div class="rating" style="float:none!important;margin:0px!important;"><span class="rate_' . $row['reviews_rating'] . '"> </span></div>';
                            break;
                        case 'review_given_by':
                            $user_detail = $db->query("select user_name, user_email from tbl_users where user_id=" . $row['reviews_user_id']);
                            $rowUser = $db->fetch($user_detail);
                            echo '<strong>' . $rowUser['user_name'] . '</strong><br/>' . $rowUser['user_email'];
                            break;
                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(3, '', 'edit')) {
                                if ($row['reviews_approval'] == 1) {
                                    echo '<li><a href="?company=' . $row['reviews_company_id'] . '&approve=' . $row[CompanyReview::DB_TBL_PRIMARY_KEY] . '"  title="' . t_lang('M_TXT_APPROVE') . '"><i class="ion-checkmark-circled icon"></i></a></li>';
                                }
                                if ($row['reviews_approval'] == 0) {
                                    echo '<li><a href="?company=' . $row['reviews_company_id'] . '&pending=' . $row[CompanyReview::DB_TBL_PRIMARY_KEY] . '"  title="' . t_lang('M_TXT_PENDING') . '"><i class="ion-ios-timer-outline icon"></i></a></li>';
                                }
                                echo '<li><a href="?deal_id=' . $row['reviews_company_id'] . '&edit=' . $row[CompanyReview::DB_TBL_PRIMARY_KEY] . '"  title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li><li><a href="?company=' . $row['reviews_company_id'] . '&delete=' . $row[CompanyReview::DB_TBL_PRIMARY_KEY] . '"  title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
<?php
require_once './footer.php';
