<?php
require_once './application-top.php';
checkAdminPermission(7);
$post = getPostedData();
//Search Form
$Src_frm = new Form('Src_frm', 'Src_frm');
$Src_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$Src_frm->setFieldsPerRow(2);
$Src_frm->captionInSameCell(true);
$Src_frm->addTextBox(t_lang('M_FRM_KEYWORD'), 'keyword', '', '', '');
$Src_frm->addHiddenField('', 'mode', 'search');
$fld1 = $Src_frm->addButton('&nbsp;', 'btn_cancel', t_lang('M_TXT_CLEAR_SEARCH'), '', ' class="inputbuttons" onclick=location.href="language-managment.php"');
$fld = $Src_frm->addSubmitButton('&nbsp;', 'btn_search', t_lang('M_TXT_SEARCH'), '', ' class="inputbuttons"')->attachField($fld1);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['btn_submit'] == t_lang('M_TXT_UPDATE')) {
    $configurable_fields = [];
    $rsc1 = $db->query("select * from  tbl_lang order by lang_id desc");
    while ($row = $db->fetch($rsc1)) {
        $english_lang_key = strtolower($row['lang_id']);
        $french_lang_key = strtolower($row['lang_id']) . "_2";
        $configurable_fields[] = $english_lang_key;
        $configurable_fields2[] = $french_lang_key;
    }
    foreach ($configurable_fields as $fld) {
        if (!isset($post[$fld]))
            continue;
        $qry = "UPDATE tbl_lang SET lang_english=" . $db->quoteVariable($post[$fld]) . " where lang_id=" . $db->quoteVariable(strtoupper($fld));
        $db->query($qry);
    }
    foreach ($configurable_fields2 as $fld2) {
        if (!isset($post[$fld2]))
            continue;
        $qry = "UPDATE tbl_lang SET lang_spanish=" . $db->quoteVariable($post[$fld2]) . " where lang_id=" . $db->quoteVariable(strtoupper(substr($fld2, 0, -2)));
        $db->query($qry);
    }
    $msg->addMsg(t_lang('M_TXT_SETTINGS_UPDATED'));
    redirectUser();
    exit;
}
$frm = new Form('frmLamguage', 'frmLamguage');
$frm->setTableProperties('width="100%" class="tbl_form tbl_data  table-striped"');
$frm->setFieldsPerRow(3);
$frm->setJsErrorDisplay('afterfield');
$frm->captionInSameCell(true);
$frm->setAction('?');
$fld = $frm->addHTML('<div class="tblheading">Sr No.</div>', '', '', true);
$fld = $frm->addHTML('<div class="tblheading">English</div>', '', '', true);
$fld = $frm->addHTML('<div class="tblheading">' . CONF_SECONDARY_LANGUAGE . '</div>', '', '', true);
$totalRs = $db->query("select * from  tbl_lang ");
$total = $db->total_records($totalRs);
if (isset($_REQUEST['limit'])) {
    $limit = $_REQUEST['limit'];
    if ($limit != 'all') {
        $limitPara = explode('-', $limit);
        if ($limitPara[0] > 0) {
            $limitFrom = 'limit ' . $limitPara[0] . ' , ' . 25;
        } else {
            $limitFrom = 'limit ' . $limitPara[0] . ' , ' . 25;
        }
    } else {
        $limitFrom = '';
    }
} else {
    $limitFrom = 'limit 0 , 25';
}
if ($post['mode'] == 'search') {
    $srch = new SearchBase('tbl_lang');
    if ($post['keyword'] != '') {
        $cnd = $srch->addDirectCondition('0');
        $cnd->attachCondition('lang_english', 'like', '%' . trim($post['keyword']) . '%', 'OR');
        $cnd->attachCondition('lang_spanish', 'like', '%' . trim($post['keyword']) . '%', 'OR');
        $cnd->attachCondition('lang_key', '=', trim($post['keyword']), 'OR');
    }
    $Src_frm->fill($post);
    $rsc = $srch->getResultSet();
} else {
    $rsc = $db->query("select * from  tbl_lang  $limitFrom");
}
$count = 0;
while ($row = $db->fetch($rsc)) {
    $count++;
    $lower_lang_key = strtolower($row['lang_id']);
    $french_lang_key = strtolower($row['lang_id']) . "_2";
    $arr = explode('_', $row['lang_key']);
    array_shift($arr);
    array_shift($arr);
    $lang_value = $row['lang_english'];
    $caption = (ucwords(strtolower(implode(' ', $arr))));
    $fld = $frm->addHTML($count, '', '', true);
    $frm->addTextarea($caption, $lower_lang_key, $row['lang_english'], '', ' ');
    $frm->addTextarea($caption, $french_lang_key, $row['lang_spanish']);
}
$fld = $frm->addSubmitButton('&nbsp;', 'btn_submit', t_lang('M_TXT_UPDATE'), '', 'class="inputbuttons"  ');
$fld->merge_cells = 3;
$fld->merge_caption = true;
require_once './header.php';
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_LANGUAGE_MANAGEMENT')
);
?>
<ul class="nav-left-ul">
    <li><a href="configurations.php" ><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></a></li>
    <li><a href="payment-settings.php"><?php echo t_lang('M_TXT_PAYMENT_GATEWAY_SETTINGS'); ?></a></li>
    <li><a href="email-templates.php"><?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?></a></li>
    <li><a href="language-managment.php" class="selected"><?php echo t_lang('M_TXT_LANGUAGE_MANAGEMENT'); ?></a></li>
    <li><a href="cities.php" ><?php echo t_lang('M_TXT_CITIES_MANAGEMENT'); ?></a></li>
    <!--li><a href="database-backup.php" ><?php echo t_lang('M_TXT_DATABASE_BACKUP_RESTORE'); ?></a></li-->
</ul>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_LANGUAGE_SETTINGS'); ?></div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="message error"><?php echo $msg->display(); ?> </div>
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
    <?php if ((checkAdminAddEditDeletePermission(7, '', 'add')) && (checkAdminAddEditDeletePermission(7, '', 'edit'))) { ?>
        <div class="box searchform_filter">
            <div class="title"> <?php echo t_lang('M_TXT_SEARCH'); ?> </div>
            <div class="content togglewrap" style="display:none;"><?php echo $Src_frm->getFormHtml(); ?></div>
        </div>
        <div class="box">
            <div class="title"> <?php echo t_lang('M_TXT_LANGUAGE_SETTINGS'); ?> </div>
            <div class="content">
                <form method="post" name="language">
                    <table width="100%" class="tbl_form">
                        <tr>
                            <td><?php echo t_lang('M_TXT_SELECT_DEFAULT_LANGUAGE'); ?></td>
                            <td>English <input type="radio" name="conf_default_language[]" onclick="changeLanguage(1)" value="1" <?php if (CONF_DEFAULT_LANGUAGE == 1) echo 'checked="checked"'; ?> />
                                <?php echo CONF_SECONDARY_LANGUAGE; ?>  <input type="radio" name="conf_default_language[]" onclick="changeLanguage(2)" value="2" <?php if (CONF_DEFAULT_LANGUAGE == 2) echo 'checked="checked"'; ?>/>
                            </td>
                        </tr>
                    </table>
                </form>
                <form method="post" name="pagesize">
                    <table width="100%" class="tbl_form">
                        <tr>
                            <td><?php echo t_lang('M_TXT_SELECT_DEFAULT_SIZE'); ?></td>
                            <td>
                                <select name="limit" onchange="this.form.submit();">
                                    <?php
                                    for ($i = 1; $i < $total; $i++) {
                                        if ($_REQUEST['limit'] == $i . '-' . ($i + 24)) {
                                            $selected = 'selected';
                                        } else {
                                            $selected = '';
                                        }
                                        echo '<option value="' . $i . '-' . ($i + 24) . '" ' . $selected . '>' . $i . '-' . ($i + 24) . '</option>';
                                        $i = $i + 24;
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
        <?php } ?>
</td>
<?php
require_once './footer.php';

