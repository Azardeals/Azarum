<?php
require_once '../application-top.php';
include './update-deal-status.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$post = getPostedData();
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 10;
$mainTableName = 'tbl_reviews';
$primaryKey = 'reviews_id';
$arr_rating = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(4);
$Src_frm->captionInSameCell(true);
$Src_frm->addSelectBox(t_lang('M_FRM_RATING'), 'rating', $arr_rating, '', '', t_lang('M_TXT_SELECT'));
$Src_frm->addHiddenField('', 'mode', 'search');
$fld1 = $Src_frm->addButton('', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="company-review.php"');
$fld = $Src_frm->addSubmitButton('', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"');
$fld->attachField($fld1);
$frm = new Form('frmReview', 'frmReview');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->addTextarea(t_lang('M_FRM_REVIEWS'), 'reviews_reviews', '', '', 'cols="30" rows="5"')->requirements()->setRequired();
$frm->setJsErrorDisplay('afterfield');
$frm->addHiddenField('', 'reviews_parent_id', $_GET['reply'], 'reviews_parent_id');
$frm->addHiddenField('', 'reviews_type', 2, 'reviews_type');
$frm->addHiddenField('', 'reviews_company_id', $_SESSION['logged_user']['company_id'], 'reviews_company_id');
$frm->addHiddenField('', 'reviews_id', '', 'reviews_id');
$frm->addHiddenField('', 'reviews_user_id', '', 'reviews_user_id');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($post['btn_search']) && isset($post['btn_submit'])) {
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $record = new TableRecord($mainTableName);
        $arr_lang_independent_flds = array('reviews_deal_id', 'reviews_deal_company_id', 'reviews_user_id', 'reviews_company_id', 'reviews_approval', 'reviews_type', 'reviews_id', 'mode', 'reviews_added_on', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        $record->setFldValue('reviews_reviews', nl2br($post['reviews_reviews']));
        $record->setFldValue('reviews_company_id', $_SESSION['logged_user']['company_id']);
        $record->setFldValue('reviews_type', 2);
        $record->setFldValue('reviews_approval', 1);
        $record->setFldValue('reviews_deal_company_id', $_SESSION['logged_user']['company_id']);
        $record->setFldValue('reviews_added_on', date('Y-m-d H:i:s'));
        $success = $record->addNew();
        if ($success) {
            $review_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
            redirectUser('company-review.php');
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            $frm->fill($post);
        }
    }
}
$srch = new SearchBase('tbl_reviews', 'd');
$srch->addCondition('reviews_company_id', '=', $_SESSION['logged_user']['company_id']);
$srch->addCondition('reviews_type', '=', 2);
$srch->addCondition('reviews_approval', '=', 1);
$srch->addCondition('reviews_parent_id', '=', 0);
$srch->addOrder('reviews_added_on', 'desc');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
if ($post['mode'] == 'search') {
    if ($post['rating'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('d.reviews_rating', '=', $post['rating'], 'OR');
    }
    $Src_frm->fill($post);
}
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
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
$arr_bread = ['company-deals.php' => t_lang('M_TXT_DEALS_PRODUCTS'), '' => t_lang('M_TXT_COMPANY_REVIEW')];
if ($_GET['status'] == "") {
    $class = 'class="active"';
} else {
    $tabStatus = $_GET['status'];
    $tabClass = 'class="active"';
}
?>
</div></td>
<td class="right-portion"><?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_COMPANY_REVIEW'); ?> </div>
    </div>
    <div class="clear"></div>
    <?php if (!isset($_GET['reply'])) { ?> 
        <div class="box searchform_filter">
            <div class="title"><?php echo t_lang('M_TXT_COMPANY_REVIEW'); ?> </div>
            <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
        </div>
        <?php
    }
    if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
        ?> 
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
    if ($_GET['reply'] > 0) {
        $dealReplyValid = $db->query("select * from tbl_reviews where reviews_parent_id=" . $_GET['reply']);
        $rowReplyValid = $db->fetch($dealReplyValid);
        $totalReplyValid = $db->total_records($dealReplyValid);
    }
    if ($_REQUEST['reply'] > 0 && $totalReplyValid == 0) {
        ?>
        <div class="box">
            <div class="title"> <?php echo t_lang('M_TXT_COMPANY_REVIEW'); ?> </div>
            <div class="content"><?php echo $frm->getFormHtml(); ?></div>
        </div>
    <?php } else { ?>
        <table class="tbl_data" width="100%" style="border: 1px solid rgb(222, 222, 222);">
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
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['reviews_reviews'] . '<br/>';
                            //echo '<strong>'.$arr_lang_name[1].'</strong>'. ' ' .$row['reviews_reviews_lang1'];
                            $dealReply = $db->query("select * from tbl_reviews where reviews_parent_id=" . $row['reviews_id']);
                            $rowReply = $db->fetch($dealReply);
                            $totalReply = $db->total_records($dealReply);
                            if ($db->total_records($dealReply) > 0) {
                                echo '<br/><strong>' . t_lang('M_TXT_REPLY') . '</strong>: ';
                                echo $rowReply['reviews_reviews'];
                            }
                            break;
                        case 'reviews_rating':
                            echo '<div class="rating" style="float:none!important;margin:0px!important;"><span class="rate_' . $row['reviews_rating'] . '"> </span></div><br/>';
                            break;
                        case 'review_given_by':
                            $user_detail = $db->query("select user_name, user_email from tbl_users where user_id=" . $row['reviews_user_id']);
                            $rowUser = $db->fetch($user_detail);
                            echo '<strong>' . $rowUser['user_name'] . '</strong><br/>' . $rowUser['user_email'];
                            break;
                        case 'action':
                            if ($totalReply == 0) {
                                echo '<ul class="actions"><li>  <a href="?reply=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_REPLY') . '" class="btn "><i class="ion-reply icon"></i></a></li></ul>';
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
            if ($db->total_records($rs_listing) == 0) {
                echo '<tr><td colspan="' . count($arr_listing_fields) . '">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
            }
            ?>
        </table>
        <?php if ($srch->pages() > 1) { ?>
            <div class="footinfo">
                <aside class="grid_1"><?php echo $pagestring; ?></aside>  
                <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
            </div>
        <?php } ?>
    <?php } ?>
</td>  
<?php
require_once './footer.php';
