<?php
require_once './application-top.php';
checkAdminPermission(5);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 10;
$mainTableName = 'tbl_reviews';
$primaryKey = 'reviews_id';

if (is_numeric($_GET['deal_id'])) {
    $imgName = $db->query('select * from tbl_deals where deal_id=' . $_GET['deal_id']);
    $deal = $db->fetch($imgName);
    $deal_name = $deal['deal_name' . $_SESSION['lang_fld_prefix']];
}

$frm = new Form('frmReview', 'frmReview');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->addTextArea(t_lang('M_FRM_REVIEWS'), 'reviews_reviews', '', '', 'class="field--large"')->requirements()->setRequired();
$frm->setJsErrorDisplay('afterfield');

$frm->addHiddenField('', 'reviews_deal_id', '', 'reviews_deal_id');
$frm->addHiddenField('', 'reviews_id', '', 'reviews_id');
$frm->addHiddenField('', 'reviews_user_id', '', 'reviews_user_id');
$frm->addHiddenField('', 'reviews_id', '', 'reviews_id');

$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="inputbuttons"');
if (is_numeric($_GET['edit'])) {
    $record = new TableRecord($mainTableName);
    $record->setFldValue('review_reviews', nl2br($_POST['review_reviews']));

    if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
        $msg->addError($record->getError());
    } else {
        $arr = $record->getFlds();
        $arr['btn_submit'] = t_lang('M_TXT_UPDATE');
        if ($row['review_title' . $_SESSION['lang_fld_prefix']] == '') {
            $arr['review_title'] = $deal_name;
        } else {
            $arr['review_title'] = $row['review_title' . $_SESSION['lang_fld_prefix']];
        }

        /* $frm->fill($arr); */
        fillForm($frm, $arr);
        $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
    }
}
if (is_numeric($_GET['delete'])) {
    $db->query('delete from ' . $mainTableName . ' where reviews_id=' . $_GET['delete']);
    $msg->addMsg('Review Deleted Successfully!');
    redirectUser('deals-review.php?deal_id=' . $_GET['deal_id']);
}
if (is_numeric($_GET['approve'])) {
    $db->query('update  ' . $mainTableName . ' set reviews_approval=0  where reviews_id=' . $_GET['approve']);
    $db->query('update  ' . $mainTableName . ' set reviews_approval=0  where reviews_parent_id=' . $_GET['approve']);
    $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
    redirectUser('deals-review.php?deal_id=' . $_GET['deal_id']);
}
if (is_numeric($_GET['pending'])) {
    $db->query('update  ' . $mainTableName . ' set reviews_approval=1 where reviews_id=' . $_GET['pending']);
    $db->query('update  ' . $mainTableName . ' set reviews_approval=1 where reviews_parent_id=' . $_GET['pending']);
    $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
    redirectUser('deals-review.php?deal_id=' . $_GET['deal_id']);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord($mainTableName);
        /* $record->assignValues($post); */
        $arr_lang_independent_flds = array('reviews_deal_id', 'reviews_deal_company_id', 'reviews_user_id', 'reviews_company_id', 'reviews_approval', 'reviews_type', 'reviews_id', 'mode', 'reviews_added_on', 'btn_submit');
        assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
        $record->setFldValue('reviews_reviews', nl2br($_POST['reviews_reviews']));
        $record->setFldValue('reviews_deal_id', $_GET['deal_id']);
        $record->setFldValue('reviews_deal_company_id', $post['reviews_deal_company_id']);
        $record->setFldValue('reviews_added_on', date('Y-m-d H:i:s'));

        $success = ($post[$primaryKey] > 0) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : ''; //$record->addNew();

        if ($success) {
            $review_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();

            $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
            redirectUser('deals-review.php?deal_id=' . $post['reviews_deal_id']);
        } else {
            $msg->addError('Could not add/update! Error: ' . $record->getError());
            $frm->fill($post);
        }
    }
}
$srch = new SearchBase('tbl_reviews', 'd');
if ($_GET['deal_id'] > 0) {
    $srch->addCondition('reviews_deal_id', '=', $_GET['deal_id']);
}
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
	' . getPageString('<li><a href="?deal_id=' . $_REQUEST['deal_id'] . '&page=xxpagexx" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = array(
    'listserial' => t_lang('M_TXT_SR_NO'),
    'reviews_reviews' => t_lang('M_TXT_DESCRIPTION'),
    'review_given_by' => t_lang('M_TXT_REVIEW_GIVEN_BY'),
    'reviews_rating' => t_lang('M_TXT_RATING'),
    'action' => t_lang('M_TXT_ACTION')
);
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEAL_PRODUCT'),
    '' => t_lang('M_TXT_DEAL_PRODUCT_REVIEW')
);


if ($_REQUEST['status'] == "") {
    $class = 'class="active"';
} else {
    $tabStatus = $_REQUEST['status'];
    $tabClass = 'class="active"';
}
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>

    <div class="clear"></div>

    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_DEAL_PRODUCT_REVIEW'); ?> 
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
    if (is_numeric($_REQUEST['edit']) || $_REQUEST['add'] == 'new') {

        if ((checkAdminAddEditDeletePermission(5, '', 'add')) || (checkAdminAddEditDeletePermission(5, '', 'edit'))) {
            ?>
            <div class="box"><div class="title"> <?php echo t_lang('M_TXT_DEAL_REVIEW'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
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
                            $dealName = $db->query("select deal_name from tbl_deals where deal_id=" . $row['reviews_deal_id']);
                            $rowDeal = $db->fetch($dealName);
                            echo '<strong>' . $rowDeal['deal_name'] . '</strong><br/>';
                            echo $row['reviews_reviews'];
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
                            echo '<div class="rating" style="float:none!important;margin:0px!important;"><span class="rate_' . $row['reviews_rating'] . '"> </span></div>';
                            break;

                        case 'review_given_by':
                            $user_detail = $db->query("select user_name, user_email from tbl_users where user_id=" . $row['reviews_user_id']);
                            $rowUser = $db->fetch($user_detail);
                            echo '<strong>' . $rowUser['user_name'] . '</strong><br/>' . $rowUser['user_email'];
                            break;

                        case 'action':
                            echo '<ul class="actions">';
                            if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                if ($row['reviews_approval'] == 1) {
                                    echo '<li><a href="?deal_id=' . $row['reviews_deal_id'] . '&approve=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_APPROVE') . '"><i class="ion-checkmark-circled icon"></i></a></li>';
                                }
                                if ($row['reviews_approval'] == 0) {
                                    echo '<li><a class="linkpending" href="?deal_id=' . $row['reviews_deal_id'] . '&pending=' . $row[$primaryKey] . '" title="' . t_lang('M_TXT_PENDING') . '"><i class="ion-ios-timer-outline icon"></i></a></li>';
                                }
                            }
                            if (checkAdminAddEditDeletePermission(5, '', 'edit')) {
                                echo ' <li><a href="?deal_id=' . $row['reviews_deal_id'] . '&edit=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_EDIT') . '"><i class="ion-edit icon"></i></a></li>';
                            }
                            if (checkAdminAddEditDeletePermission(5, '', 'delete')) {
                                echo '<li><a href="?deal_id=' . $row['reviews_deal_id'] . '&delete=' . $row[$primaryKey] . '"  title="' . t_lang('M_TXT_DELETE') . '" onclick="requestPopup(this,\'' . t_lang('M_TXT_ARE_YOU_SURE_TO_DELETE') . '\',1);"><i class="ion-android-delete icon"></i></a></li>';
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
?>
