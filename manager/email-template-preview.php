<?php

require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isset($_SESSION['admin_logged']['admin_id']) > 0) {
    die(t_lang('M_TXT_SESSION_EXPIRES'));
}
########## Email #####################
$tpl_id = intval($_GET['tpl_id']);
$rs = $db->query("select * from tbl_email_templates where tpl_id=$tpl_id");
$row_tpl = $db->fetch($rs);
$message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
$subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
$arr_replacements = array('xxserver_namexx' => $_SERVER['SERVER_NAME'], 'xxwebrooturlxx' => CONF_WEBROOT_URL);
foreach ($arr_replacements as $key => $val) {
    $subject = str_replace($key, $val, $subject);
    $message = str_replace($key, $val, $message);
}
?>
<style type="text/css">
    body{margin: 0;}
</style>
<?php

echo emailTemplate($message);
##############################################