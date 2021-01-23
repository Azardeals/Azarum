<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (isset($_SESSION['city']) || is_numeric($_SESSION['city'])) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL));
}
$frm = getMBSFormByIdentifier('frmCitySelector');
$frm->setAction((CONF_WEBROOT_URL . 'pre_subscription.php'));
$fld = $frm->getField('loginlink');
$frm->removeField($fld);
$fld = $frm->getField('city');
$cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0");
$city_opts = $db->fetch_all_assoc($cityList);
$fld->options = $city_opts;
$fld->id = 'city';
$fld->extra = 'class="inputSelect fl"';
$fld = $frm->getField('btn_submit');
$fld->extra = "class='inputSubmit'";
$fld->value = t_lang('M_TXT_CONTINUE');
$urlPrivacy = CONF_WEBROOT_URL . 'privacy.php';
$cityList = $db->query("select * from tbl_cities where city_active=1 and city_request=0 and city_deleted=0");
while ($Cityrow = $db->fetch($cityList)) {
    $cityOption .= '<option value="' . $Cityrow['city_id'] . '" >' . $Cityrow['city_name'] . '</option>';
}
?>
<!Doctype html>
<html>
    <head>
        <?php
        require_once './meta.inc.php';
        require_once './js-and-css.inc.php';
        ?>
        <!--[if IE 6]>
        <script src="js/DD_belatedPNG_0.0.8a-min.js" type="text/javascript"></script>
        <script type="text/javascript">
        DD_belatedPNG.fix('#outer, #container, #header, a, img');
        </script>
        <![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <style type="text/css">
            @media screen and (min-width:1000px){
                .landingBanner .bannerImage{background-size:100%!important;}
                .landingBanner .content {padding:30px;}
            }
        </style>
    </head>
    <body>
        <script type="text/javascript">
            function changeCity(val) {
                val = $("#city option:selected").html();
                $("#cityname").html(val);
            }
            function selectCityRedirect()
            {
                var city = parseInt($('#city').val());
                selectCity(city, 1);
                return false;
            }
        </script>
        <div id="wrapper" class="landingBanner">
            <div class="bannerImage" style="background:url(images/landing-bg.jpg) top center no-repeat;"></div>
            <div class="overlay"></div>
            <div class="landingWrapper">
                <section class="content">
                    <div class="citySelector">
                        <div class="logo"><?php if (CONF_FRONT_END_LOGO == "") { ?>
                                <a href="<?php echo CONF_WEBROOT_URL; ?>" onclick="return selectCityRedirect();"><img src="<?php echo CONF_WEBROOT_URL; ?>images/company_logo.png" border="0" alt="<?php echo CONF_SITE_NAME; ?>" title="<?php echo CONF_SITE_NAME; ?>" /></a>
                            <?php } else { ?>
                                <a href="<?php echo CONF_WEBROOT_URL; ?>" onclick="return selectCityRedirect();"><img border="0" src="<?php echo CONF_WEBROOT_URL . 'images/company_logo.png'; ?>" alt="<?php echo CONF_SITE_NAME; ?>"></a>
                            <?php } ?></div>
                        <div class="pagetitle">
                            <h2 style="border-bottom-width:0;"><?php echo t_lang('M_TXT_BITFAT_DEALS'); ?></h2>
                            <p><?php echo t_lang('M_TXT_GET_OFF_NEAR_YOU'); ?></p>
                        </div>
                        <?php $msg->display(); ?>
                        <?php echo $frm->getFormTag(); ?>
                        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="formTable">
                            <tbody>
                                <tr>
                                    <td width="100%">
                                        <?php echo $frm->getFieldHTML('city'); ?> </td>
                                    <td>
                                        <?php echo $frm->getFieldHTML('btn_submit'); ?></td>
                                </tr>
                            </tbody></table>
                        </form><?php echo $frm->getExternalJS(); ?>
                    </div>
                </section>
            </div>
            <!--change-->
            <div class="bottom-bar">
                <p> <a href="<?php echo CONF_WEBROOT_URL; ?>" onclick="return selectCityRedirect();"><?php echo t_lang('M_TXT_ALREADY_SUBSCRIBER_SKIP'); ?></a> | 
                    <a href="<?php
                    $CatArray = array('id' => 17);
                    $url = getPageUrl('cms-page.php', $CatArray);
                    echo friendlyUrl($url);
                    ?>" ><?php echo t_lang('M_TXT_HOW_IT_WORKS'); ?></a></p>
            </div>
        </div>
        <script type="text/javascript">
            $(function () {
                var pull = $('.quickLinks');
                menu = $('.topLinks');
                menuHeight = menu.height();
                $(pull).on('click', function (e) {
                    e.preventDefault();
                    menu.slideToggle();
                });
                $(window).resize(function () {
                    var w = $(window).width();
                    if (w > 320 && menu.is(':hidden')) {
                        menu.removeAttr('style');
                    }
                });
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.cityselector').click(function () {
                    $('.citiesWrap').slideToggle();
                    return false;
                });
                $('html').click(function () {
                    $('.citiesWrap').slideUp('slow');
                });
            });
        </script> 
        <script src="js/jquery.flexnav.js" type="text/javascript"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $(".flexnav").flexNav();
            });
        </script>  
        <?php echo CONF_GOOGLE_ANALYTIC_CODE; ?>
    </body>
</html>