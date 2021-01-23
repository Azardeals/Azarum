<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/mailchimp-function.php';
checkAdminPermission(0);
require_once './header.php';
$setting = mailchimpSetting();
if (!$setting) {
    $msg->addMsg(t_lang('M_TXT_PLEASE_SET_MAILCHIMP_IN_YOUR_EMAIL_SENDING_METHOD_PROMOTIONAL'));
    redirectUser('configurations.php');
}
$Src_frm = new Form('campaign', 'campaign');
$Src_frm->setTableProperties('border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$segment_types = fetchallSegment();
$Src_frm->setFieldsPerRow(1);
$Src_frm->captionInSameCell(false);
$fld = $Src_frm->addRequiredField(t_lang('M_TXT_CAMPAIGN_NAME'), 'campaign_name', '', 'campaign_name_id', '');
//$fld->setUnique('tbl_mc_segments', 'segment_name','id','segment_name_id','segment_name');
$Src_frm->addRequiredField(t_lang('M_TXT_SUBJECT'), 'subject_name', '', 'subject_id', '');
$Src_frm->addRequiredField(t_lang('M_TXT_FROM_NAME'), 'from_name', '', 'from_name_id', '');
$Src_frm->addRequiredField(t_lang('M_TXT_TO_NAME'), 'to_name', '', 'to_name_id', '');
$cityList = getCityList();
$categoryList = getCategoryList();
$groups = getGroups($list_id);
$fld = $Src_frm->addSelectBox(t_lang('M_TXT_SELECT_CITY'), 'city_id', $cityList, '', 'onchange="fetchDeal(this.value)"', 'Select Your City', 'city_id')->requirements()->setRequired();
$Src_frm->addSelectBox(t_lang('M_TXT_SELECT_CATEGORY'), 'category_id', $categoryList, '', '', 'Select Your Category');
$new_deals = fetchNewDeals();
$fld = $Src_frm->addSelectBox(t_lang('M_TXT_SELECT_MAIN_DEAL'), 'main_deal_id', $new_deals, '', 'maindeal', 'Select Your Main Deal');
$fld->html_before_field = "<div id='main_deal_id'>";
$fld->html_after_field = "</div>";
$fld = $Src_frm->addCheckBoxes(t_lang('M_TXT_SELECT_OTHER_DEAL'), 'other_deal_id', $new_deals, '', '', '', '');
$fld->html_before_field = "<div id='other_deal_id'>";
$fld->html_after_field = "</div>";
$editor = $Src_frm->addHtmlEditor(t_lang('M_TXT_ADD_TEMPLATE'), 'template_description', '');
$editor->requirements()->setRequired();
$editor->attachField($Src_frm->addHTML(t_lang('M_TXT_ADD'), '', "<div style='color:red;'>Instruction: Please Add xxmaindealxx for maindeal and xxotherdealxx for otherdeal</div>"));
$Src_frm->addHTML(t_lang('M_TXT_ADD_PREVIEW'), 'template_description', "<input type='button' value='Preview' onclick='showPreview();'>");
$timefield = $Src_frm->addDateTimeField(t_lang('M_TXT_SCHEDULE'), 'time', '', '', '');
$timefield->html_before_field = '<div id="datepicker" style="display:none;">';
$timefield->html_after_field = '</div>';
$timaArray = array('send' => 'Send Now', 'schedule' => 'Schedule');
$fld = $Src_frm->addRadioButtons(t_lang('M_TXT_SCHEDULE'), 'schedule', $timaArray, '', '', 'id="schedule_id"');
$fld->attachField($timefield);
$Src_frm->setJsErrorDisplay('afterfield');
$fld = $Src_frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SUBMIT'), '', ' class="medium"');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    $cityName = getCityName($post['city_id']);
    $categoryName = fetchCategoryListName($post['category_id']);
    //	fetchMaindealInfo($post['maindeal']);	
    $segment_opts = array('match' => "all", 'conditions' => array(
            0 => array('field' => 'interests-17097', 'op' => 'one', 'value' => $categoryName),
            1 => array('field' => 'interests-17089', 'op' => 'one', 'value' => $cityName)
    ));
    $array = array('main_deal_id' => $post['main_deal_id'], 'other_deal_id' => $post['other_deal_id'], 'template' => $post['template_description']);
    $array['template'] = html_entity_decode($post['template_description']);
    $template_content = fetchMaindealInfo($array);
    if ($template_content == "") {
        $error = "Template should not be empty";
        $msg->addError(strtoupper($error));
        redirectUser();
    }
    $time = date('Y-m-d H:i:s', strtotime($post['time']));
    $arr_replacements = array(
        'xxuser_namexx' => '*|MERGE1|**|MERGE2|*',
        'xxuser_emailxx' => '*|EMAIL|*',
        'xxemailxx' => '*|EMAIL|*',
        'xxdeal_cityxx' => '*|MERGE4|*',
        'xxcityxx' => '*|MERGE4|*',
        'xxverification_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'verify-user.php?code=' . $user_code . '&mail=' . $user_email,
        'xxsite_namexx' => CONF_SITE_NAME,
        'xxuser_member_idxx' => $member_id,
        'xxclick_buttonxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/click' . $_SESSION['lang_fld_prefix'] . '.png',
        'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
        'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
        'xxserver_namexx' => $_SERVER['SERVER_NAME'],
        'xxwebrooturlxx' => CONF_WEBROOT_URL
    );
    foreach ($arr_replacements as $key => $val) {
        $post['template_description'] = str_replace($key, $val, $post['template_description']);
    }
    $total_user = segmentTest($list_id, $segment_opts);
    if ($total_user['total'] == 0) {
        $error = "There is no user in the list";
        $msg->addError(strtoupper($error));
        redirectUser('campaign.php');
    }
    if ($post['campaign_name'] != '') {
        $type = 'regular';
        $options = array('list_id' => $list_id,
            'subject' => $post['subject_name'],
            'from_email' => 'fatbittest@gmail.com',
            'from_name' => $post['from_name'],
            'to_name' => $post['to_name'],
            'title' => $post['campaign_name'],
            'auto_footer' => false);
        $content = array(
            'html' => $template_content,
            //   'sections'=>'',
            'text' => 'static_content',
                //'url'=>'',
                //  'archive'=>''
        );
        $segment_opts = $segment_opts;
        $type_opts = [];
        $campaign = createCampaign($type, $options, $content, $segment_opts, $type_opts);
        $campaign_id = $campaign['id'];
        if ($post['schedule'] == 'send') {
            $send = sendCampaign($campaign_id);
            if ($send['complete'] == 1) {
                $msg->addMsg(t_lang('M_TXT_SEND_SUCCESSFULL'));
                redirectUser('campaign.php');
            } else {
                $error = $send;
                //$error.="No user is exists in campaign";
                $msg->addError(strtoupper($error));
            }
        }
        if ($post['schedule'] == 'schedule') {
            echo "schedule";
            $set = scheduleCampaign($campaign_id, $time);
            if ($set['complete'] == 1) {
                $msg->addMsg(t_lang('M_TXT_SCHEDULE_SUCCESSFULL'));
                redirectUser('campaign.php');
            } else {
                $msg->addError(strtoupper($set));
            }
        }
    }
}
?>
</div></td>
<td class="right-portion">
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
    <?php ?>
    <div class="box"><div class="title"><?php echo t_lang('M_TXT_CREATE_CAMPAIGN'); ?></div><div class="content">		
            <?php
            echo $Src_frm->getFormHtml();
            ?>
            <div class="gap">&nbsp;</div>	
        </div></div>
</td>
<?php
require_once './footer.php';
?>