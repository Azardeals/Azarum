<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isRepresentativeUserLogged())
    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');
$rep_id = $_SESSION['logged_user']['rep_id'];
$arr_bread = array(
    'my-account.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_TRAINING_VIDEO')
);
require_once './header.php';
?>
    </div>
</td>
<td class="right-portion"> <?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_TRAINING_VIDEO'); ?> </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                    return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
    <script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/ZeroClipboard.js"></script>
    <?php
    $trainingRow = $db->query("select * from tbl_training_video where tv_status=1 and tv_user=1 order by tv_display_order asc");
    ?>
    <table class="tbl_data" width="100%">
        <thead>
            <?php while ($row = $db->fetch($trainingRow)) { ?>
                <tr>
                    <td>

                        <?php echo $row['tv_title']; ?><br/> <br/> <?php echo $row['tv_link']; ?><br/>
                    </td>


                </tr>
            <?php } ?>
    </table>
</td>
<?php
require_once './footer.php';
?>
