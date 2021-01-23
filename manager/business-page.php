<?php
require_once './application-top.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_business_page");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['business_conf_name']), $row1['business_conf_value']);
}
/* end configuration variables */
checkAdminPermission(1);
require_once './header.php';
$frm = new Form('business_page', 'business_page');
$frm->setAction('?');
$frm->setTableProperties(' width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_form" ');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
$frm->setJsErrorDisplay('afterfield');
if ($_SESSION['lang_fld_prefix'] == "") {
    $frm->addTextarea('M_FRM_BANNER_HEADER_TEXT', 'business_header_text', BUSINESS_HEADER_TEXT);
    $frm->addTextarea('M_FRM_BANNER_QUOTE_TEXT', 'business_quote_text', BUSINESS_QUOTE_TEXT);
    $frm->addTextarea('M_FRM_BANNER_QUOTE_NAME', 'business_quote_name', BUSINESS_QUOTE_NAME);
    $frm->addTextarea('M_FRM_HEADING_1', 'business_why_heading', BUSINESS_WHY_HEADING);
    $frm->addTextarea('M_FRM_TEXT_UNDER_HEADING_1', 'business_why_heading_text', BUSINESS_WHY_HEADING_TEXT);
    $frm->addTextarea('M_FRM_BUILD_YOUR_PLAN', 'business_build_your_plan', BUSINESS_BUILD_YOUR_PLAN);
    $frm->addTextarea('M_FRM_MORE_CUSTOMER', 'business_find_more_customer', BUSINESS_FIND_MORE_CUSTOMER);
    $frm->addTextarea('M_FRM_BOOK_MORE_REVENUE', 'business_book_more_revenue', BUSINESS_BOOK_MORE_REVENUE);
    $frm->addTextarea('M_FRM_BUILD_YOUR_BRAND_HEADING', 'business_build_your_plan1', BUSINESS_BUILD_YOUR_PLAN1);
    $frm->addTextarea('M_FRM_BUILD_YOUR_BRAND_TEXT', 'business_build_your_brand_text', BUSINESS_BUILD_YOUR_BRAND_TEXT);
    $frm->addTextarea('M_FRM_PERCENTAGE_AMOUNT', 'business_percent_amount', BUSINESS_PERCENT_AMOUNT);
    $frm->addTextarea('M_FRM_PERCENTAGE_TEXT', 'business_percent_text', BUSINESS_PERCENT_TEXT);
    $frm->addTextarea('M_FRM_CUSTOMER_QUOTE_TEXT_BLUE', 'business_customer_quote_text', BUSINESS_CUSTOMER_QUOTE_TEXT);
    $frm->addTextarea('M_FRM_CUSTOMER_QUOTE_TEXT_BY', 'business_customer_quote_text_by', BUSINESS_CUSTOMER_QUOTE_TEXT_BY);
    $frm->addTextarea('M_FRM_MORE_CUSTOMER_HEADING', 'business_find_more_customer1', BUSINESS_FIND_MORE_CUSTOMER1);
    $frm->addTextarea('M_FRM_MORE_CUSTOMER_TEXT', 'business_find_more_customer_text', BUSINESS_FIND_MORE_CUSTOMER_TEXT);
    $frm->addTextarea('M_FRM_BOOK_MORE_REVENUE_HEADING', 'business_book_more_revenue1', BUSINESS_BOOK_MORE_REVENUE1);
    $frm->addTextarea('M_FRM_BOOK_MORE_REVENUE_TEXT', 'business_book_more_revenue_text', BUSINESS_BOOK_MORE_REVENUE_TEXT);
    $frm->addTextarea('M_FRM_PAST_SUCCESSFUL_DEALS', 'business_past_deals_text', BUSINESS_PAST_DEALS_TEXT);
} else {
    $frm->addTextarea('M_FRM_BANNER_HEADER_TEXT', 'business_header_text_lang1', BUSINESS_HEADER_TEXT_LANG1);
    $frm->addTextarea('M_FRM_BANNER_QUOTE_TEXT', 'business_quote_text_lang1', BUSINESS_QUOTE_TEXT_LANG1);
    $frm->addTextarea('M_FRM_BANNER_QUOTE_NAME', 'business_quote_name_lang1', BUSINESS_QUOTE_NAME_LANG1);
    $frm->addTextarea('M_FRM_HEADING_1', 'business_why_heading_lang1', BUSINESS_WHY_HEADING_LANG1);
    $frm->addTextarea('M_FRM_TEXT_UNDER_HEADING_1', 'business_why_heading_text_lang1', BUSINESS_WHY_HEADING_TEXT_LANG1);
    $frm->addTextarea('M_FRM_BUILD_YOUR_PLAN', 'business_build_your_plan_lang1', BUSINESS_BUILD_YOUR_PLAN_LANG1);
    $frm->addTextarea('M_FRM_MORE_CUSTOMER', 'business_find_more_customer_lang1', BUSINESS_FIND_MORE_CUSTOMER_LANG1);
    $frm->addTextarea('M_FRM_BOOK_MORE_REVENUE', 'business_book_more_revenue_lang1', BUSINESS_BOOK_MORE_REVENUE_LANG1);
    $frm->addTextarea('M_FRM_BUILD_YOUR_BRAND_HEADING', 'business_build_your_plan1_lang1', BUSINESS_BUILD_YOUR_PLAN1_LANG1);
    $frm->addTextarea('M_FRM_BUILD_YOUR_BRAND_TEXT', 'business_build_your_brand_text_lang1', BUSINESS_BUILD_YOUR_BRAND_TEXT_LANG1);
    $frm->addTextarea('M_FRM_PERCENTAGE_AMOUNT', 'business_percent_amount_lang1', BUSINESS_PERCENT_AMOUNT_LANG1);
    $frm->addTextarea('M_FRM_PERCENTAGE_TEXT', 'business_percent_text_lang1', BUSINESS_PERCENT_TEXT_LANG1);
    $frm->addTextarea('M_FRM_CUSTOMER_QUOTE_TEXT_BLUE', 'business_customer_quote_text_lang1', BUSINESS_CUSTOMER_QUOTE_TEXT_LANG1);
    $frm->addTextarea('M_FRM_CUSTOMER_QUOTE_TEXT_BY', 'business_customer_quote_text_by_lang1', BUSINESS_CUSTOMER_QUOTE_TEXT_BY_LANG1);
    $frm->addTextarea('M_FRM_MORE_CUSTOMER_HEADING', 'business_find_more_customer1_lang1', BUSINESS_FIND_MORE_CUSTOMER1_LANG1);
    $frm->addTextarea('M_FRM_MORE_CUSTOMER_TEXT', 'business_find_more_customer_text_lang1', BUSINESS_FIND_MORE_CUSTOMER_TEXT_LANG1);
    $frm->addTextarea('M_FRM_BOOK_MORE_REVENUE_HEADING', 'business_book_more_revenue1_lang1', BUSINESS_BOOK_MORE_REVENUE1_LANG1);
    $frm->addTextarea('M_FRM_BOOK_MORE_REVENUE_TEXT', 'business_book_more_revenue_text_lang1', BUSINESS_BOOK_MORE_REVENUE_TEXT_LANG1);
    $frm->addTextarea('M_FRM_PAST_SUCCESSFUL_DEALS', 'business_past_deals_text_lang1', BUSINESS_PAST_DEALS_TEXT_LANG1);
}
$frm->addHiddenField('', 'mode', 'extra');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_UPDATE'), '', ' class="inputbuttons"');
updateFormLang($frm);
if ($_POST['mode'] == 'extra') {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $post = getPostedData();
        $record = new TableRecord('tbl_business_page');
        $record->assignValues($post);
        foreach ($post as $key => $val) {
            $qry = "update tbl_business_page set business_conf_value=" . $db->quoteVariable($val) . " where business_conf_name=" . $db->quoteVariable($key);
            $db->query($qry);
        }
        $msg->addMsg(t_lang('M_MSG_RECORD_UPDATED_SUCCESSFULLY'));
        redirectUser('?');
        /* //	header("Location:home-page-banner.php");	exit; */
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
}
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_BUSINESS_PAGE')
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
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
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        echo '<div class="box"><div class="title"> ' . t_lang('M_TXT_UPDATE_BUSINESS_PAGE_CONTENT') . ' </div><div class="content">' . $frm->getFormHtml() . '</div></div>';
    } else {
        die(t_lang('M_TXT_UNAUTHORIZED_ACCESS'));
    }
    ?>
</td>
<?php
require_once './footer.php';
