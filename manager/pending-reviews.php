<?php
require_once './application-top.php';
checkAdminPermission(5);
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 10;
$mainTableName = 'tbl_reviews';
$primaryKey = 'reviews_id';
if (is_numeric($_GET['reviews'])) {
    if (checkAdminAddEditDeletePermission(8, '', 'edit')) {
        if (!$db->update_from_array('tbl_reviews', ['reviews_approval' => 1], 'reviews_id=' . $_GET['reviews'])) {
            $msg->addError($db->getError());
        } else {
            $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
            redirectUser('?');
        }
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$srch = new SearchBase('tbl_reviews', 'c');
$srch->addCondition('reviews_approval', '=', 0);
$srch->addOrder('reviews_id', 'desc');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
$pagestring .= '<div class="pagination "><ul>';
$pageStringContent = '<a href="javascript:void(0);">' . t_lang('M_TXT_DISPLAYING_RECORDS') . ' ' . (($page - 1) * $pagesize + 1) .
        ' ' . t_lang('M_TXT_TO') . ' ' . (($page * $pagesize > $srch->recordCount()) ? $srch->recordCount() : ($page * $pagesize)) . ' ' . t_lang('M_TXT_OF') . ' ' . $srch->recordCount() . '</a>';
$pagestring .= '<li><a href="javascript:void(0);">' . t_lang('M_TXT_GOTO') . ': </a></li>
	' . getPageString('<li><a href="?page=xxpagexx" onclick="setPage(xxpagexx,document.frmPaging);">xxpagexx</a> </li> '
                , $srch->pages(), $page, '<li class="selected"><a class="active" href="javascript:void(0);">xxpagexx</a></li>');
$pagestring .= '</div>';
$arr_listing_fields = [
    'listserial' => t_lang('M_TXT_SR_NO'),
    'reviews_company_id' => t_lang('M_FRM_COMPNAY_NAME'),
    'reviews_deal_id' => t_lang('M_TXT_DEAL_NAME'),
    'reviews_reviews' => t_lang('M_FRM_REVIEWS'),
    'action' => t_lang('M_TXT_ACTION')
];
require_once './header.php';
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'deals.php' => t_lang('M_TXT_DEAL_PRODUCT'),
    '' => t_lang('M_TXT_PENDING_REVIEWS')
];
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
                    <div class="redtext"><?php echo $msg->display(); ?> </div><br/><br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
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
                    $listserial = '';
                    foreach ($arr_listing_fields as $key => $val) {
                        echo '<th ' . (( $key == 'listserial') ? ' width="4%" ' : 'width="24%"') . '>' . $val . '</th>';
                    }
                    ?>
                </tr>
            </thead>
            <?php
            $listserial = '';
            for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                $row['reviews_reviews'] = htmlentities($row['reviews_reviews'], ENT_QUOTES, 'UTF-8');
                echo '<tr>';
                foreach ($arr_listing_fields as $key => $val) {
                    echo '<td>';
                    switch ($key) {
                        case 'listserial':
                            echo $listserial;
                            break;
                        case 'reviews_deal_id':
                            $dealRs = $db->query("Select * from tbl_deals where deal_id=" . $row['reviews_deal_id']);
                            $rowDeal = $db->fetch($dealRs);
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $rowDeal['deal_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $rowDeal['deal_name_lang1'];
                            break;
                        case 'reviews_company_id':
                            if ($row['reviews_company_id'] > 0) {
                                $companyRs = $db->query("Select * from tbl_companies where company_id=" . $row['reviews_company_id']);
                            } else {
                                $companyRs = $db->query("Select * from tbl_companies where company_id=" . $row['reviews_deal_company_id']);
                            }
                            $rowCompany = $db->fetch($companyRs);
                            echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $rowCompany['company_name'] . '<br/>';
                            echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $rowCompany['company_name_lang1'];
                            break;
                        case 'action':
                            //if(checkAdminAddEditDeletePermission(2,'','edit')){
                            echo '<ul class="actions"><li><a href="?reviews=' . $row['reviews_id'] . '" title="' . t_lang('M_TXT_APPROVE_REQUEST') . '"><i class="ion-ios-timer-outline icon"></i></a></li></ul>';
                            //}
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
            <?php
        }
    }
    ?>
</td>  
<?php require_once './footer.php'; ?>
