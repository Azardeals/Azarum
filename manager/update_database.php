<?php
require_once './application-top.php';
checkAdminPermission(6);
$file = $_GET["file_name"];
$db_server = CONF_DB_SERVER;
$db_user = CONF_DB_USER;
$db_password = CONF_DB_PASS;
$db_databasename = CONF_DB_NAME;
//print "file name= ".$filel." database   ".$database."<br/>";
$conf_db_path = "database.bak";
$BackupFile = $conf_db_path . "/" . $file;
$cmd = "mysql --user=" . $db_user . " --password=" . $db_password . " " . $db_databasename . " < " . $_SERVER['DOCUMENT_ROOT'] . CONF_WEBROOT_URL . 'manager/' . $BackupFile;
system($cmd);
$msg->addMsg("Database restored to '$file'!");
if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) {
    ?> 
    <div class="box" id="messages">
        <div class="title-msg"> System messages <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;">Hide</a></div>
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
