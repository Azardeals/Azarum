<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/mailchimp-function.php';
$arr_common_js[] = 'js/calendar.js';
$arr_common_js[] = 'js/calendar-en.js';
$arr_common_js[] = 'js/calendar-setup.js';
$arr_common_css[] = 'css/cal-css/calendar-win2k-cold-1.css';
checkAdminPermission(14);
require_once './header.php';
?>
</div></td>
<?php
if (!defined('CONF_EMAIL_SENDING_METHOD_PROMOTIONAL') || CONF_EMAIL_SENDING_METHOD_PROMOTIONAL != 1) {
    $msg->addError(t_lang('M_TXT_PLEASE_SET_MAILCHIMP_AS_YOUR_PROMOTIONAL_SETTING'));
    redirectUser('configurations.php');
}
if (!defined('CONF_MAILCHIMP_LIST_ID') || strlen(trim(CONF_MAILCHIMP_LIST_ID)) < 2) {
    $msg->addError(t_lang('M_TXT_PLEASE_SET_MAILCHIMP_AS_YOUR_PROMOTIONAL_SETTING'));
    redirectUser('configurations.php');
}
$Src_frm = new Form('campaign', 'campaign');
$Src_frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setLeftColumnProperties('style="width:30%;"');
$Src_frm->setFieldsPerRow(1);
$Src_frm->captionInSameCell(false);
$fld = $Src_frm->addRequiredField(t_lang('M_TXT_CAMPAIGN_NAME'), 'campaign_name', '', 'campaign_name_id', '');
$Src_frm->addRequiredField(t_lang('M_TXT_SUBJECT'), 'subject_name', '', 'subject_id', '');
$cityList = getCityList();
$categoryList = getCategoryList();
$fld = $Src_frm->addSelectBox(t_lang('M_TXT_SELECT_CITY'), 'city_id', $cityList, '', 'onChange="fetchDeal(this.value)"', t_lang('M_TXT_SELECT_CITY'), 'city_id')->requirements()->setRequired();
$fld = $Src_frm->addSelectBox(t_lang('M_TXT_SELECT_CATEGORY'), 'category_id', $categoryList, '', 'onChange="fetchDealList(this.value)"', 'Select Category', 'category_id');
$fld->field_caption = t_lang('M_TXT_SELECT_CATEGORY') . " <small class='textsmall'>" . t_lang('M_TXT_NEWSLETTER_WILL_GO_FOR_SELECTED_CATEGORY') . "</small>";
$fld = $Src_frm->addSelectBox(t_lang('M_TXT_SELECT_MAIN_DEAL'), 'main_deal_id', $new_deals, '', 'maindeal', t_lang('M_TXT_SELECT_MAIN_DEAL'));
$fld->html_before_field = "<div id='main_deal_id'>";
$fld->html_after_field = "</div>";
$new_deals = '';
$fld = $Src_frm->addCheckBoxes(t_lang('M_TXT_SELECT_OTHER_DEAL'), 'other_deal_id', $new_deals, '', '', '', 'Select  Deal');
$fld->html_before_field = "<div id='other_deal_id'>";
$fld->html_after_field = "</div>";
$html = '<table width="900" cellspacing="0" cellpadding="0" border="0" align="center">
        <tbody>
            <tr>
                <td align="center" style="border:1px solid #ccc;">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="left">
                        <tbody>
                            <tr>
                                <td style="background:#f5f5f5;font-size:12px;color:#2f2f2f;font-weight:normal;text-align:left;font-family:Arial,Helvetica,sans-serif;vertical-align:top;border-top:0">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
                                        <tbody>
                                            <tr><td valign="top" colspan="2">' . CONF_EMAIL_HEADER_TEXT . '</td></tr>
                                            <tr><td colspan="2" style="padding:10px;background:#fff;">xxmaindealxx</td></tr>
                                            <tr><td colspan="2">xxotherdealxx</td></tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
	</table>';
$editor = $Src_frm->addHtmlEditor(t_lang('M_TXT_ADD_TEMPLATE'), 'template_description', $html);
$editor->html_before_field = '<div class="frm-editor">';
$editor->html_after_field = '</div>';
$editor->requirements()->setRequired();
$editor->requirements()->setCustomErrorMessage(t_lang('M_TXT_DESCRIPTION_IS_MANADATORY.'));
$editor->attachField($Src_frm->addHTML(t_lang('M_TXT_ADD'), '', "<div style='color:red;'><h4>" . unescape_attr(t_lang('M_TXT_CAMPAIGN_INSTRUCTION')) . "</h4></div>"));
$Src_frm->addHTML(t_lang('M_TXT_PREVIEW'), 'template_description', '<ul class="actions"><li><a href="javascript:void(0);" onClick="showPreview();" title="' . t_lang('M_TXT_PREVIEW') . '"><i class="ion-eye icon"></i></a></li></ul>');
$timefield = $Src_frm->addDateTimeField(t_lang('M_TXT_SCHEDULE'), 'time', '', '', '');
$timefield->html_before_field = '<div id="datepicker" style="display:none;">';
$timefield->html_after_field = '</div>';
;
$timaArray = ['send' => t_lang('M_TXT_SEND_NOW'), 'schedule' => t_lang('M_TXT_SCHEDULE')];
$fld = $Src_frm->addRadioButtons(t_lang('M_TXT_SCHEDULE'), 'schedule', $timaArray, '', '', 'id="schedule_id"');
$fld->attachField($timefield);
$Src_frm->setJsErrorDisplay('afterfield');
$fld = $Src_frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="medium"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    $array = ['main_deal_id' => $post['main_deal_id'], 'other_deal_id' => $post['other_deal_id'], 'template' => $post['template_description']];
    $array['template'] = html_entity_decode($post['template_description']);
    $template_content = fetchMaindealInfo($array);
    if ($template_content == "") {
        $error = t_lang("M_TXT_TEMPLATE_SHOULD_NOT_BE_EMPTY");
        $msg->addError(strtoupper($error));
        redirectUser();
    }
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $users = fetchUsers($post['category_id'], $post['city_id']);
    if (!empty($users)) {
        $segmentID = createSegment($list_id, $users);
    } else {
        $error = "There is no Subscriber for this campaign.";
        $msg->addError(strtoupper($error));
        redirectUser();
    }
    $time = date('Y-m-d H:i:s', strtotime($post['time']));
    if ($post['campaign_name'] != '') {
        $type = 'regular';
        $options = [
            'list_id' => $list_id,
            'subject' => $post['subject_name'],
            'from_email' => CONF_EMAILS_FROM,
            'from_name' => CONF_EMAILS_FROM_NAME,
            'title' => $post['campaign_name']];
        $content = [
            'html' => $template_content,
            'text' => 'static_content',
        ];
        $segment_opts = [];
        if ($segmentID != '') {
            $segment_opts['saved_segment_id'] = $segmentID;
        }
        $type_opts = [];
        $campaign = createCampaign($type, $options, $content, $segment_opts, $type_opts);
        if (!$campaign) {
            redirectUser('campaign.php');
        }
        $campaign_id = $campaign['id'];
        if ($post['schedule'] == 'send') {
            $send = sendCampaign($campaign_id);
            if ($send['complete'] == 1) {
                $msg->addMsg(t_lang('M_TXT_CAMPAIGN_IS_POSTED_SUCCESSFULLY'));
                redirectUser('campaign.php');
            } else {
                $error = $send;
                $msg->addError(strtoupper($error));
            }
        }
        if ($post['schedule'] == 'schedule') {
            $set = scheduleCampaign($campaign_id, $time);
            if ($set['complete'] == 1) {
                $msg->addMsg(t_lang('M_TXT_CAMPAIGN_IS_SCHEDULE_SUCCESSFULLY'));
                redirectUser('campaign.php');
            } else {
                $msg->addError(strtoupper($set));
            }
        }
    }
}
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_MAILCHIMP'),
    '' => t_lang('M_FRM_CAMPAIGN')
];
?>
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>    
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_CREATE_CAMPAIGN'); ?></div>
    </div>
    <?php if (checkAdminAddEditDeletePermission(14, '', 'add')) { ?>
        <div class="box">
            <div class="content">	
                <?php echo $Src_frm->getFormHtml(); ?>
            </div>
        </div>
    <?php } ?>
</td>
<?php
require_once './footer.php';
