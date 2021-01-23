<?php
require_once './application-top.php';
checkAdminPermission(8);
require_once './header.php';
if (isset($_POST['edit_password_btn'])) {
    if (!$db->query("UPDATE tbl_users SET user_password =" . $db->quoteVariable($_POST['password_txt']) . " WHERE user_id = " . intval($_GET['user_id']))) {
        echo "Error In Update" . $db->getError();
    } else {
        $body = "A New Password Has Been Generated For You On ValleyBestDeals.com. It Is " . $_POST['password_txt'] . " .\n\n Regards - ValleyBestDeals Team";
        send_email($_POST['user_email_txt'], "New Password Generated", $body);
        header("location:./registered-members.php?page=1");
    }
}

//generic function for password emails
function send_email($user_email, $subject, $body)
{
    require_once './mailer/swift_required.php';
    require_once './email_config.php';
    //Create the Transport
    try {
        $transport = Swift_SmtpTransport::newInstance($smtp, $port)
                ->setUsername($username)
                ->setPassword($password);
    } catch (Exception $e) {
        echo "exception" . $e;
        return FALSE;
    }
    //echo "transport completed";
    $mailer = Swift_Mailer::newInstance($transport);
    //Create a message
    $message = Swift_Message::newInstance($subject)
            ->setFrom(array($from_address => $website_name))
            ->setTo($user_email)
            ->setBody($body)
    ;
    //Send the message
    try {
        $numSent = $mailer->batchSend($message);
    } catch (Exception $e) {
        echo "exception" . $e;
        return FALSE;
    }
//End of code by Softronikx Technologies
}
?>
<html>
    <body>
        <form method="post">
            <?php
            $user_query = $db->query("SELECT * FROM tbl_users WHERE user_id =" . intval($_GET['user_id']));
            $user_data = $db->fetch($user_query);
            ?>
            <table>
                <tr>
                    <td>UserName:</td>
                    <td><input type="text" readonly="readonly" value="<?php echo $user_data['user_name']; ?>" ></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><input type="text" readonly="readonly" name="user_email_txt"  value="<?php echo $user_data['user_email']; ?>" ></td>
                </tr>
                <tr>
                    <td>New Password:</td>
                    <td><input type="text" name="password_txt" value="<?php echo $user_data['user_password']; ?>" ></td>
                </tr>
                <tr>
                    <td></td>	
                    <td><input type="submit" name="edit_password_btn" value="Change Password" ></td>
                </tr>
            </table>
        </form>
        <?php require_once './footer.php'; ?>			
    </body>
</html>
