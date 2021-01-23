<?php
die('Invalid Access!');
require_once './application-top.php';
checkAdminPermission(6);
if (isset($_GET["mode"]) and $_GET["mode"] == "download") {
    if (isset($_GET["file"]) and trim($_GET["file"]) != "") {
        $file = $_GET["file"];
        dl_file_p($file);
    }
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST["name"];
    $name = trim($name, " ");
    if ($_POST['Submit'] != "") {
        backupDatabase($name);
        $msg->addMsg("Database Backup on Server Successfully!! ");
    }
}
// Backup and download on server
$frm = new Form('database');
$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0"  class="tbl_form"');
$frm->setFieldsPerRow(1);
$frm->setJsErrorDisplay('afterfield');
$frm->captionInSameCell(false);
$frm->setLeftColumnProperties('width="30%"');
$fld = $frm->addRequiredField(t_lang('M_FRM_FILE_NAME'), 'name', '', '', 'class="input" autocomplete=off ');
$frm->addSubmitButton('', 'Submit', t_lang('M_BTN_BACKUP_ON_SERVER'), 'Backup on Server', ' class="inputbuttons"');
// upload on server
$Uploadfrm = new Form('upload');
$Uploadfrm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0"  class="tbl_form"');
$Uploadfrm->setFieldsPerRow(1);
$Uploadfrm->setJsErrorDisplay('afterfield');
$Uploadfrm->captionInSameCell(false);
$Uploadfrm->setLeftColumnProperties('width="30%"');
$caption = t_lang('M_FRM_DB_UPLOAD') . '<br/>' . t_lang('M_FRM_FILE_EXTENSION') . ' - ' . '.sql';
$Uploadfrm->addFileUpload($caption, 'file', '', '', 'class="input" autocomplete=off ')->requirements()->setRequired();
$Uploadfrm->addSubmitButton('', 'Submit3', t_lang('M_BTN_UPLOAD_ON_SERVER'), 'Upload on Server', 'class="inputbuttons"');
$conf_db_path = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
#dELETE
if (isset($_GET['delete'])) {
    if (checkAdminAddEditDeletePermission(7, '', 'delete')) {
        unlink($conf_db_path . '/' . $_GET['delete']);
        $msg->addMsg(t_lang('M_TXT_RECORD_DELETED'));
        header("Location:" . $_SERVER['PHP_SELF']);
        exit;
    } else {
        die('Unauthorized Access.');
    }
}
if ($_POST['Submit3'] != "") {
    $db_server = CONF_DB_SERVER;
    $db_user = CONF_DB_USER;
    $db_password = CONF_DB_PASS;
    $db_databasename = CONF_DB_NAME;
    $ext = strrchr($_FILES['file']['name'], '.');
    if (strtolower($ext) != '.sql') {
        die("File type unsupported. Please upload Sql file.");
    }
    $target = UPLOADS_PATH . "/sql/" . str_replace(' ', '-', $_FILES['file']['name']);
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        die('could not copy file!! ');
    }
    $cmd = "mysql --user=" . $db_user . " --password=" . $db_password . " " . $db_databasename . " < " . $_SERVER['DOCUMENT_ROOT'] . CONF_WEBROOT_URL . 'manager/' . $target;
    system($cmd);
    $msg->addMsg("Upload Data on Server Successfully!!");
}
require_once './header.php';
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'configurations.php' => t_lang('M_TXT_SETTINGS'),
    '' => t_lang('M_TXT_DATABASE_BACKUP_RESTORE')
];
$strheadline = "Add New Database";
$p = 1;
if (isset($_GET["page"])) {
    $p = $_GET["page"];
}
if (isset($_POST["page"])) {
    $p = $_POST["page"];
}
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_DATABASE_BACKUP_RESTORE'); ?> </div>
    </div>
    <div class="clear"></div>
    <div id="infoBox"><?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
            <div class="box" id="messages">
                <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
                <div class="content">
                    <?php if (isset($_SESSION['errs'][0])) { ?>
                        <div class="message error"><?php echo $msg->display(); ?> </div>
                        <br/>
                        <br/>
                    <?php } if (isset($_SESSION['msgs'][0])) { ?>
                        <div class="greentext"> <?php echo $msg->display(); ?> </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?> </div>
    <?php if (checkAdminAddEditDeletePermission(7, '', 'add')) { ?>  
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_DATABASE_BACKUP_RESTORE'); ?> </div><div class="content"><?php echo $frm->getFormHtml(); ?></div></div>
        <div class="box"><div class="title"> <?php echo t_lang('M_TXT_DATABASE_UPLOAD'); ?> </div><div class="content"><?php echo $Uploadfrm->getFormHtml(); ?></div></div>
    <?php } ?>  
    <div class="box"><div class="content"> <table width="100%"  border="0" cellpadding="0" cellspacing="0" class="tbl_data">
                <thead>
                    <tr>
                        <th width="285"><?php echo t_lang('M_TXT_BACKUP_FILE_NAME'); ?></th>
                        <th width="323" >&nbsp;</th>
                        <th width="144" height="25" ></th>
                    </tr>
                </thead>
                <?php
                $dir = dir($conf_db_path);
                $count = 0;
                while (($file = $dir->read()) !== false) {
                    if ($file == "." || $file == ".." || $file == ".htaccess") {
                        
                    } else {
                        ?>
                        <tr>
                            <td   height="25" style="color:#1a91f7" >
                                <?php echo $file; ?>
                            </td>
                            <td height="25" ><?php echo date("d/m/Y H:i:s.", filectime($conf_db_path . "/" . $file)); ?></td>
                            <td height="25" align="center" nowrap  width="30%">
                                <ul class="actions">
                                    <?php if (checkAdminAddEditDeletePermission(7, '', 'add')) { ?>
                                        <li><a href="javascript:void(0);" onclick="window.open('database-backup.php?mode=download&file=<?php echo $file; ?>');" title="<?php echo t_lang('M_TXT_DOWNLOAD_DATABASE'); ?>"><i class="ion-ios-download icon"></i></a></li>
                                        <li><a href="javascript:void(0);" onclick="doRestore('<?php echo $file; ?>');" title="<?php echo t_lang('M_TXT_RESTORE_DATABASE'); ?>"><i class="ion-archive icon"></i></a></li>
                                    <?php } ?>
                                    <?php if (checkAdminAddEditDeletePermission(7, '', 'delete')) { ?>
                                        <li><a href="<?php $_SERVER['PHP_SELF'] ?>?delete=<?php echo $file ?>" alt="<?php echo t_lang('M_TXT_DELETE'); ?>"  title="<?php echo t_lang('M_TXT_DELETE'); ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a></li>
                                            <?php } ?>
                                </ul>
                            </td>
                        </tr>                           
                        <?php
                    }
                }
                ?>
            </table>
        </div>
    </div>
</td>
<?php require_once './footer.php'; ?>
<script type="text/javascript">
    function DatabaseName11() {
        var str = document.getElementById('name').value;
        if (str == "") {
            requestPopup(this, "Please Fill Database Name", 0);
            document.getElementById('name').focus();
            return false;
        } else {
            return true;
        }
    }
</script> 
<script language="javascript" src="includes/ajax.js"></script>
<script language="javascript">
    function doRestore(f)
    {
        msg = 'Preparing for restore...<img src="images/ajax2.gif">';
        requestPopupAjax(this, 'Are you sure you want to restore the database to this instance?', 1);
    }
    function doRequiredAction() {
        //updateMessage('infoBox',msg);
        msg = '<div class="box" id="messages"><div class="title-msg"> System messages <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest(#messages).hide(); return false;">Hide</a></div><div class="content"><div class="greentext">Restoring database to: <b>' + f + '</b> ...<img src="images/ajax2.gif"> </div></div></div>';
        setTimeout("doAjax('update_database.php?file_name=" + f + "','','infoBox','POST','" + msg + "')", 20)
        //doAjax('update_database.php?file_name='+f,'','infoBox','POST',msg);
    }
</script>
<script type="text/javascript">
    function xmlhttpPost(strURL, divName) {
        var xmlHttpReq = false;
        var self = this;
        document.getElementById(divName).innerHTML = "<img align=top src='images/ajax2.gif' title='Do not Click any where During Process...'>";
        // Mozilla/Safari
        if (window.XMLHttpRequest) {
            self.xmlHttpReq = new XMLHttpRequest();
        }
        // IE
        else if (window.ActiveXObject) {
            self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
        }
        self.xmlHttpReq.open('POST', strURL, true);
        self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        self.xmlHttpReq.onreadystatechange = function () {
            if (self.xmlHttpReq.readyState == 4)
            {
                document.getElementById(divName).innerHTML = self.xmlHttpReq.responseText;
            }
        }
        self.xmlHttpReq.send('');
    }
</script>
