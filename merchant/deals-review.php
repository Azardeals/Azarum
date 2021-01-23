<?php
require_once '../application-top.php';
include './update-deal-status.php';
if (!isCompanyUserLogged()) {
    redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
}
$post = getPostedData();
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 10;
$mainTableName = 'tbl_reviews';
$primaryKey = 'reviews_id';
$frm = new Form('frmReview', 'frmReview');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$fld = $frm->addTextArea(t_lang('M_FRM_REVIEWS'), 'reviews_reviews', '', 'reviews_reviews', '');
$fld->requirements()->setRequired(true);
$frm->setJsErrorDisplay('afterfield');
$frm->addHiddenField('', 'reviews_parent_id', $_GET['reply'], 'reviews_parent_id');
$frm->addHiddenField('', 'reviews_type', 1, 'reviews_type');
$frm->addHiddenField('', 'reviews_deal_id', $_GET['deal_id'], 'reviews_deal_id');
$frm->addHiddenField('', 'reviews_id', '', 'reviews_id');
$frm->addHiddenField('', 'reviews_user_id', '', 'reviews_user_id');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error) {
            $msg->addError($error);
        }
    } else {
        $record = new TableRecord($mainTableName);
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = array('reviews_deal_id', 'reviews_deal_company_id', 'reviews_user_id', 'reviews_company_id', 'reviews_approval', 'reviews_type', 'reviews_id', 'mode', 'reviews_added_on', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        $record->setFldValue('reviews_reviews', nl2br($_POST['reviews_reviews']));
        $record->setFldValue('reviews_deal_id', $_GET['deal_id']);
        $record->setFldValue('reviews_type', 1);
        $record->setFldValue('reviews_approval', 1);
        $record->setFldValue('reviews_deal_company_id', $_SESSION['logged_user']['company_id']);
        $record->setFldValue('reviews_added_on', date('Y-m-d H:i:s'));
        $success = $record->addNew();
        if ($success) {
            $review_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('deals-review.php?deal_id=' . $post['reviews_deal_id']);
        } else {
            $msg->addError(t_lang('M_TXT_COULD_NOT_ADD_UPDATE') . $record->getError());
            $frm->fill($post);
        }
    }
}
$srch = new SearchBase('tbl_reviews', 'd');
if ($_GET['deal_id'] > 0) {
    $srch->addCondition('reviews_deal_id', '=', $_GET['deal_id']);
}
$srch->addCondition('reviews_deal_company_id', '=', $_SESSION['logged_user']['company_id']);
$srch->addCondition('reviews_type', '=', 1);
$srch->addCondition('reviews_parent_id', '=', 0);
$srch->addOrder('reviews_added_on', 'desc');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="?deal_id=' . $_REQUEST['deal_id'] . '&page=xxpagexx"  >xxpagexx</a> </li>'
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SR_NO'),
    'reviews_reviews' => t_lang('M_TXT_DESCRIPTION'),
    'review_given_by' => t_lang('M_TXT_REVIEW_GIVEN_BY'),
    'reviews_rating' => t_lang('M_TXT_RATING'),
    'review_approval_status' => t_lang('M_TXT_REVIEW_APPROVAL_STATUS'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    'company-deals.php' => t_lang('M_TXT_DEALS_PRODUCTS'),
    '' => t_lang('M_TXT_DEAL_PRODUCT_REVIEW')
);
if ($_GET['status'] == "") {
    $class = 'class="active"';
} else {
    $tabStatus = $_GET['status'];
    $tabClass = 'class="active"';
}
?>
</div></td>
<td class="right-portion">
    <?php echo getMerchantBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_DEAL_PRODUCT_REVIEW'); ?> </div>
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
    <?php
    if ($_REQUEST['reply'] > 0 && $_REQUEST['deal_id'] > 0) {
        ?>
        <div class="box">
            <div class="title"> <?php echo t_lang('M_TXT_DEAL_REVIEW'); ?> </div>
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
                echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'reviews_reviews':
                            $dealName = $db->query("select deal_name from tbl_deals where deal_id=" . $row['reviews_deal_id']);
                            $rowDeal = $db->fetch($dealName);
                            echo '<strong>' . $rowDeal['deal_name'] . '</strong><br/>';
                            echo htmlentities($row['reviews_reviews']);
                            $dealReply = $db->query("select * from tbl_reviews where reviews_parent_id=" . $row['reviews_id']);
                            $rowReply = $db->fetch($dealReply);
                            $totalReply = $db->total_records($dealReply);
                            if ($db->total_records($dealReply) > 0) {
                                echo '<br/><strong>' . t_lang('M_TXT_REPLY') . '</strong>: ';
                                echo $rowReply['reviews_reviews'];
                            }
                            break;
                        case 'reviews_reviews_lang1':
                            $dealName = $db->query("select deal_name from tbl_deals where deal_id=" . $row['reviews_deal_id']);
                            $rowDeal = $db->fetch($dealName);
                            echo '<strong>' . $rowDeal['deal_name'] . '</strong><br/>';
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
                        case 'review_approval_status':
                            echo ($row['reviews_approval'] ? t_lang('M_TXT_APPROVED') : t_lang('M_TXT_UNAPPROVED'));
                            break;
                        case 'action':
                            if ($totalReply == 0) {
                                echo '<ul class="actions"><li > <a href="?deal_id=' . $row['reviews_deal_id'] . '&reply=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_REPLY') . '" class="btn gray"><i class="ion-reply icon"></i></a></li></ul>';
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
                <aside class="grid_1">
                    <?php echo $pagestring; ?>
                </aside>
                <aside class="grid_2"><span class="info"><?php echo $pageStringContent; ?></span></aside>
            </div>
        <?php } ?>
    <?php } ?>
</td>
<?php
require_once './footer.php';
