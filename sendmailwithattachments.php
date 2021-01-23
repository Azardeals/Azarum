<?php
// The Code
require_once './class.Email.php';
if (isset($_POST['submit'])) {
    foreach ($_POST as $key => $val) {
        $$key = $val;
    }
    $to = "email@dummyid.com";
    $subject = "Mail with attachment";
    $from = "test@dummyid.com";
    $msg = new Email($to, $from, $subject);
    $msg->TextOnly = false;
    $msg->Content = nl2br($message);
    if (!$_FILES['attachmentfile']['name'] == "") {
        $filehere = $_FILES['attachmentfile']['name'];
        move_uploaded_file($_FILES['attachmentfile']['tmp_name'], $filehere);
        $msg->Attach($filehere);
    }
    $SendSuccess = $msg->Send();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Untitled Document</title>
    </head>
    <body>
        <form name="attachmentwithfile" method="post" enctype="multipart/form-data">
            Content:<br />
            <textarea name="message" cols="" rows=""></textarea><br />
            <br />
            <br />
            File to be attached:<br />
            <input type="file" name="attachmentfile" /><br />
            <br />
            <br />
            <input name="submit" type="submit" value="send mail" />
        </form>
    </body>
</html>
