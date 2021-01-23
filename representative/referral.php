<?php
require_once '../application-top.php';
require_once '../includes/navigation-functions.php';
if (!isRepresentativeUserLogged())
    redirectUser(CONF_WEBROOT_URL . 'representative/login.php');
$rep_id = $_SESSION['logged_user']['rep_id'];
$dealList = $db->query("select count(*) as total,deal_city from tbl_deals as d inner join tbl_cities as c  where d.deal_city=c.city_id and c.city_active=1 and c.city_deleted=0 and c.city_request=0 and d.deal_status=1 and d.deal_deleted=0 and d.deal_complete=1 group by deal_city order by total desc limit 0,1");

$dealrow = $db->fetch($dealList);

if ($dealrow['deal_city'] > 0) {
    $cityList = $db->query("select city_id, city_name from tbl_cities where city_id=" . $dealrow['deal_city']);
    $Cityrow = $db->fetch($cityList);


    $_SESSION['city'] = $Cityrow['city_id'];
    $_SESSION['cityname'] = $Cityrow['city_name'];
} else {
    $rs = $db->query("select city_id, city_name from tbl_cities where city_active=1 and city_deleted=0 and city_request=0");
    $row = $db->fetch($rs);
    $_SESSION['cityname'] = $row['city_name'];
    $_SESSION['city'] = $row['city_id'];
}
require_once './header.php';
$arr_bread = array(
    'my-account.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    '' => t_lang('M_TXT_REFERRAL_URL')
);
?>

</div>
</td>

<td class="right-portion"> <?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_REFERRAL_URL'); ?> </div>
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


    <script type="text/javascript">
            $(document).ready(function () {

			var copyBtn = document.getElementById('copy');
			copyBtn.onclick = function(){
				var myCode = document.getElementById('box-content').innerHTML;
				var fullLink = document.createElement('input');
				document.body.appendChild(fullLink);
				fullLink.value = myCode;
				fullLink.select();
				document.execCommand("copy", false);
				fullLink.remove();
				//alert("Copied: " + fullLink.value);
			}

            });


    </script>

    <table class="tbl_data" width="100%">
        <thead>
            <tr>
            <tr>

                <td>
                    <div class="message info" id="box-content"><?php
                        if (CONF_FRIENDLY_URL == 0) {
                            ?>http://<?php echo $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant-sign-up.php?rep=' . $rep_id; ?></div></td>
                             <?php }if (CONF_FRIENDLY_URL == 1) {
                            ?>http://<?php echo $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'merchant-sign-up/' . $rep_id . '/' . strtolower($_SESSION['logged_user']['rep_fname'] . '-' . $_SESSION['logged_user']['rep_lname']); ?></div></td>
                        <?php } ?>
                <td><input type="button" id="copy" name="copy" onclick="Copy();" value="<?php echo t_lang('M_TXT_COPY_URL'); ?>" class="btn green"/> </td>

            </tr>

    </table>



</td>
<?php
require_once './footer.php';
?>
