<?php
require_once './application-top.php';
require_once '../includes/navigation-functions.php';
require_once '../includes/mailchimp-function.php';
checkAdminPermission(14);
require_once './header.php';
if (!defined('CONF_EMAIL_SENDING_METHOD_PROMOTIONAL') || CONF_EMAIL_SENDING_METHOD_PROMOTIONAL != 1) {
    $msg->addError(t_lang('M_TXT_PLEASE_SET_MAILCHIMP_AS_YOUR_PROMOTIONAL_SETTING'));
    redirectUser('configurations.php');
}
if (!defined('CONF_MAILCHIMP_LIST_ID') || strlen(trim(CONF_MAILCHIMP_LIST_ID)) < 2) {
    $msg->addError(t_lang('M_TXT_PLEASE_SET_MAILCHIMP_AS_YOUR_PROMOTIONAL_SETTING'));
    redirectUser('configurations.php');
}
$campaigns = getList($list_id);
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'javascript:void(0)' => t_lang('M_TXT_MAILCHIMP'),
    '' => t_lang('M_TXT_CAMPAIGN_LIST')
];
?>
</div></td>		
<td class="right-portion">
    <?php echo getAdminBreadCrumb($arr_bread); ?>  
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_CAMPAIGN_LIST'); ?></div>
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
    ?>
    <table class="tbl_data"   width="100%">
        <thead>
        <th width="5%">S.NO</th>
        <th ><?php echo t_lang('M_TXT_TITLE'); ?></th>
        <th><?php echo t_lang('M_TXT_SUBJECT'); ?> </th>
        <th><?php echo t_lang('M_TXT_DATE_CREATED'); ?></th>
        <th><?php echo t_lang('M_TXT_SEND_TIME'); ?></th>
        <th><?php echo t_lang('M_TXT_STATUS'); ?></th>
        <th><?php echo t_lang('M_TXT_ACTION'); ?></th>
    </thead>
    <tbody>
        <?php
        $i = 1;
        foreach ($campaigns['data'] as $key => $value) {
            ?>
            <?php if (!empty($value)) { ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $value['title'] ?></td>
                    <td><?php echo $value['subject']; ?></td>
                    <td><?php echo displayDate($value['create_time'], true); ?></td>
                    <td><?php echo displayDate($value['send_time'], true); ?></td>
                    <?php if ($value['status'] == "sent") { ?>
                        <td><?php echo $value['status']; ?></td>
                    <?php } else { ?>
                        <td><span class="label label-info"><?php echo $value['status']; ?></span></td>
                    <?php } ?>
                    <?php if (checkAdminAddEditDeletePermission(14, '', 'delete')) { ?>
                        <td><ul class="actions"><li><a href="javascript:void(0)" onclick="deleteCampaign('<?php echo $value['id'] ?>')" title="<?php echo t_lang('M_TXT_DELETE') ?>"><i class="ion-android-delete icon"></i></a></li></ul></td>
                    <?php } ?>
                </tr>
                <?php
                $i++;
            }
        }
        ?>
    </tbody>	
</table>
</td>
<div id='page_navigation'></div>  
<?php require_once './footer.php'; ?>
<script type="text/javascript">
    function deleteCampaign(id) {
        requestPopupAjax("'" + id + "'", "<?php echo t_lang("M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD") ?>", 1, 'DeleteCampaign');
    }
    function doRequiredActionDeleteCampaign(id) {
        callAjax('mailchimp-ajax.php', 'mode=deleteCampaign&id=' + id, function (t) {
            $.facebox(t);
            setTimeout(function () {
                window.location.reload(1);
            }, 1000);
        });
    }
</script>