<?php
require_once './application-top.php';
checkAdminPermission(8);
require_once './header.php';
if (isset($_POST['edit_amt_btn'])) {
    if (is_numeric($_POST['wallet_amt_txt'])) {
        if (!$db->query("UPDATE tbl_users SET user_wallet_amount = user_wallet_amount + " . round($_POST['wallet_amt_txt'], 2) . "  WHERE user_id = " . intval($_GET['user_id']))) {
            echo "Error In Update" . $db->getError();
        } else {
            if (!$db->query("INSERT INTO tbl_user_wallet_history VALUES (" . $_GET['user_id'] . "," . 0 . ",'" . "Updated By Admin" . "'," . $_POST['wallet_amt_txt'] . ",CURRENT_TIMESTAMP " . ")")) {
                echo "Error In Wallet Logs :" . $db->getError();
            } else {
                header("location:./registered-members.php?page=1");
            }
        }
    } else {
        echo "Data Is Not Numeric!";
    }
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
                    <td><input type="text" readonly="readonly" value="<?php echo $user_data['user_email']; ?>" ></td>
                </tr>
                <tr>
                    <td>Current Wallet Amount:</td>
                    <td><input type="text" readonly="readonly" name="current_wallet_amt_txt" value="<?php echo $user_data['user_wallet_amount']; ?>" ></td>
                </tr>
                <tr>
                    <td>New Credit/Debit Amount:</td>
                    <td><input type="text" name="wallet_amt_txt" ></td>
                </tr>
                <tr>
                    <td></td>	
                    <td><input type="submit" name="edit_amt_btn" value="Edit Amount" ></td>
                </tr>
            </table>
        </form>
        <?php require_once './footer.php'; ?>			
    </body>
</html>
