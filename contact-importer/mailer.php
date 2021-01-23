<?php
if (count($contact) <= 0) {
    echo 'Invalid request';
    exit();
}
?>
<div style="border:none;overflow:auto;height:590px;width:590px;">
    <form name="frmContacts" method="post" action="<?php echo $act; ?>social_refer_friends.php" enctype="multipart/form-data" accept-charset="utf-8">
        <table width="80%" border="0" style="border:none;overflow:auto;" cellspacing="5" cellpadding="5" align="center">
            <tr>
                <td colspan="3">
                    Type your msg here: <br />
                    <textarea rows="5" cols="50" name="mail_msg"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <input type="submit" name="btn_submit" id="btn_submit" value="Send Invites" />
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="send_all" id="send_all" value="1" onclick="
                            if (this.checked)
                            {
                                for (i = 0; i < document.frmContacts.length; i++)
                                {
                                    if (document.frmContacts.elements[i].type == 'checkbox')
                                    {
                                        document.frmContacts.elements[i].checked = true;
                                    }
                                }
                            } else
                            {
                                for (i = 0; i < document.frmContacts.length; i++)
                                {
                                    if (document.frmContacts.elements[i].type == 'checkbox')
                                    {
                                        document.frmContacts.elements[i].checked = false;
                                    }
                                }
                            }" />
                </td>
                <td>Contact Name
                </td>
                <td>Contact Email
                </td>
            </tr>
            <?php
            foreach ($contact as $k => $v) {
                if (trim($v['name']) == '') {
                    $v['name'] = substr($v['email'], 0, strpos($v['email'], '@', 0));
                }
                ?>
                <tr>
                    <td>
                        <input type="checkbox" name="send_to_<?php echo $v['name']; ?>" id="send_to_<?php echo $v['name']; ?>" value="<?php echo $v['email']; ?>" />
                    </td>
                    <td>
                        <?php echo $v['name']; ?>
                    </td>
                    <td>
                        <?php echo $v['email']; ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </form></div>