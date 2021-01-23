<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
?>
<?php
if (isset($_SESSION['errs']) && is_array($_SESSION['errs']))
    $_SESSION['errs'] = array_unique($_SESSION['errs']);
if (isset($_SESSION['msgs']) && is_array($_SESSION['msgs']))
    $_SESSION['msgs'] = array_unique($_SESSION['msgs']);
$pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4);
if ($pagename != 'buy-deal-ajax') {
    ?>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?>
        <div id="msg">
            <?php
            if (isset($_SESSION['errs'][0]))
                $classMsg = 'div_error';
            else if (isset($_SESSION['msgs'][0]))
                $classMsg = 'div_msg';
            ?>
            <div class="system-notice animated fadeInDown">
                <a class="close" href="javascript:void(0);" onclick="closeMsgDiv(this);"> </a>
                <?php echo $msg->display(); ?>
            </div>
        </div>
        <?php
    }
}
?>
</div>
<!--closed body div-->
<!--footer start here-->
<footer id="footer" class="footer">
    <section class="footer__upper">
        <div class="fixed_container">
            <div class="gridspanel">
                <div class="gridspanel__grid">
                    <h4 class="gridspanel__title"><?php echo CONF_SITE_NAME; ?></h4>
                    <div class="gridspanel__content">
                        <ul class="footerlinks">
                            <?php echo printNav(0, 1); ?>
                        </ul>
                    </div>
                </div>
                <div class="gridspanel__grid">
                    <h4 class="gridspanel__title"><?php echo t_lang('M_TXT_OUR_POLICY'); ?></h4>
                    <div class="gridspanel__content">
                        <ul class="footerlinks">
                            <?php echo printNav(0, 8); ?>
                        </ul>
                    </div>
                </div>
                <div class="gridspanel__grid">
                    <h4 class="gridspanel__title"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h4>
                    <div class="gridspanel__content">
                        <ul class="footerlinks">
                            <?php echo printNav(0, 3); ?>
                        </ul>
                    </div>
                </div>
                <div class="gridspanel__grid">
                    <h4 class="gridspanel__title"><?php echo t_lang('M_TXT_SUBSCRIBE_NOW_FOR_DAILY_DEAL_EMAILS'); ?></h4>
                    <div class="gridspanel__content">
                        <div class="subscribeform">
                            <form action="?" id="newsletter_subscription" name="newsletter_subscription" method="POST" onSubmit="return checkValidEmailAddress();" >
                                <span class="selectedtxt"><?php echo strtoupper($_SESSION['cityname']); ?></span>
                                <input type="hidden" name="city" value="<?php echo $_SESSION['city']; ?>" />
                                <input type="text" id="sub_email" name="sub_email" placeholder= "<?php echo t_lang('M_FRM_ENTER_YOUR_EMAIL_ADDRESS'); ?>" />
                                <p>*<?php echo t_lang('M_TXT_CHOOSE_YOUR_CITY_TO_GET_GREAT_DEALS_VIA_MAILS'); ?></p>
                                <input id="subscribe_newsletter"  <?php if ($pagename == "preview-deal") { ?>onclick="$.facebox('<?php echo t_lang('M_MSG_THIS_IS_DEAL_PREVIEW'); ?>');" <?php } else { ?> type="submit" <?php } ?> class="themebtn themebtn--large" name="subscribe_newsletter" value="<?php echo t_lang('M_BTN_SUBSCRIBE'); ?>" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="iconinfos">
                <ul>
                    <?php if (!isCompanyUserLogged()) { ?>
                        <li>
                            <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'merchant-sign-up.php'); ?>">
                                <figure class="icon">
                                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                         viewBox="0 0 495.622 495.622" style="enable-background:new 0 0 495.622 495.622;" xml:space="preserve">
                                    <g>
                                    <path style="fill:#cccccc;" d="M495.622,113.089v150.03c0,0-32.11,6.326-38.725,7.158c-6.594,0.83-27.316,7.521-42.334-6.914
                                          c-23.16-22.197-105.447-104.03-105.447-104.03s-14.188-13.922-36.969-1.89c-20.912,11.022-51.911,27.175-64.859,33.465
                                          c-24.477,13.028-44.764-7.642-44.764-23.387c0-12.213,7.621-20.502,18.515-26.598c29.524-17.898,91.752-52.827,117.67-66.598
                                          c15.754-8.379,27.105-9.097,48.734,9.124c26.638,22.403,50.344,42.824,50.344,42.824s7.732,6.453,20.063,3.854
                                          C448.13,123.725,495.622,113.089,495.622,113.089z M168.098,367.3c3.985-10.238,2.653-21.689-4.987-29.545
                                          c-6.865-7.027-16.888-8.879-26.445-6.689c2.673-9.479,1.197-19.568-5.705-26.688c-6.886-7.009-16.89-8.898-26.446-6.688
                                          c2.653-9.465,1.181-19.553-5.725-26.652c-10.814-11.092-29.519-10.616-41.807,1.097c-12.223,11.729-20.053,32.979-9.144,45.487
                                          c10.891,12.445,23.405,4.873,32.945,2.699c-2.654,9.465-10.606,18.269-0.813,30.658c9.784,12.395,23.404,4.875,32.954,2.721
                                          c-2.663,9.429-10.268,19.117-0.851,30.604c9.502,11.522,25.065,5.383,35.344,2.19c-3.967,10.199-12.458,21.193-1.549,33.513
                                          c10.892,12.409,36.063,6.668,48.358-5.063c12.262-11.729,13.439-30.318,2.654-41.445
                                          C189.435,365.865,178.335,364.089,168.098,367.3z M392.442,289.246c-88.88-88.881-47.075-47.058-94.906-94.992
                                          c0,0-14.375-14.311-33.321-5.998c-13.3,5.828-30.423,13.771-43.307,19.835c-14.158,7.424-24.347,9.722-29.131,9.69
                                          c-27.37-0.179-49.576-22.178-49.576-49.521c0-17.738,9.417-33.181,23.462-41.947c19.75-13.667,65.21-37.847,65.21-37.847
                                          s-13.849-17.549-44.187-17.549c-30.329,0-93.695,41.512-93.695,41.512s-17.976,11.514-43.601,1.143L0,96.373V268.05
                                          c0,0,14.103,4.082,26.775,9.258c2.862-8.162,7.48-15.699,13.886-21.924c21.023-20.024,55.869-20.232,74.996-0.537
                                          c5.762,5.987,9.783,13.129,11.835,21.024c7.707,2.379,14.688,6.593,20.298,12.373c5.779,5.947,9.785,13.129,11.854,20.984
                                          c7.698,2.381,14.669,6.611,20.298,12.395c6.339,6.537,10.562,14.433,12.534,22.988c8.047,2.344,15.319,6.705,21.176,12.693
                                          c11.495,11.807,15.575,27.826,13.103,43.278c0.02,0,0.058,0,0.076,0.035c0.188,0.246,7.122,7.976,11.446,12.336
                                          c8.474,8.482,22.311,8.482,30.811,0c8.444-8.479,8.481-22.289,0-30.811c-0.304-0.303-30.572-31.963-28.136-34.418
                                          c2.418-2.438,40.981,37.688,41.699,38.422c8.463,8.465,22.291,8.465,30.792,0c8.481-8.479,8.463-22.289,0-30.791
                                          c-0.416-0.396-2.152-2.059-2.796-2.721c0,0-38.234-34.06-35.324-36.97c2.946-2.928,50.438,41.392,50.515,41.392
                                          c8.537,7.688,21.687,7.631,29.9-0.586c7.991-7.99,8.162-20.629,1.078-29.146c-0.15-0.453-36.194-38.121-33.381-40.955
                                          c2.854-2.871,38.519,33.853,38.594,33.929c8.444,8.463,22.291,8.463,30.792,0c8.463-8.464,8.463-22.291,0-30.83
                                          C392.706,289.396,392.555,289.32,392.442,289.246z"/>
                                    </g>
                                    </svg>
                                </figure>
                                <h5><?php echo t_lang('M_TXT_JOIN_OUR_BUSINESS_BANNER'); ?></h5>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if (!isAffiliateUserLogged() && !isUserLogged()) { ?>
                        <li>
                            <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'login.php'); ?>">
                                <figure class="icon">
                                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                         viewBox="0 0 30 30" style="enable-background:new 0 0 30 30;" xml:space="preserve">
                                    <g>
                                    <g>
                                    <path style="fill:#ccc;" d="M20.381,5.52c0.064,0.034,0.133,0.067,0.193,0.105C21,5.856,21.48,6,22,6c1.658,0,3-1.343,3-3
                                          s-1.342-3-3-3c-1.656,0-3,1.343-3,3C19,4.06,19.553,4.985,20.381,5.52z"/>
                                    <path style="fill:#ccc;" d="M6,18.001c0-2,0-10,0-10S6.005,7.551,6.238,7C4.159,7,2,7,2,7S0,7,0,9.001v7c0,2,2,2,2,2v10h6v-8
                                          C8,20.001,6,20.001,6,18.001z"/>
                                    <circle style="fill:#ccc;" cx="14" cy="3" r="3"/>
                                    <path style="fill:#ccc;" d="M6,6c0.52,0,1.001-0.144,1.427-0.376c0.059-0.036,0.125-0.068,0.188-0.103C8.446,4.988,9,4.062,9,3
                                          c0-1.657-1.343-3-3-3S3,1.343,3,3S4.343,6,6,6z"/>
                                    <path style="fill:#ccc;" d="M28,15.352c0-2.262,0-6.351,0-6.351S28,7,26,7h-4.238C21.996,7.551,22,8.001,22,8.001
                                          s0,1.888,0,4.059c-0.328-0.036-0.662-0.059-1-0.059s-0.67,0.022-1,0.059c0-1.632,0-3.059,0-3.059S20,7,18,7c-0.5,0-8,0-8,0
                                          S8,7,8,9.001v7c0,2,2,2,2,2v10h5.35C16.895,29.249,18.859,30,21,30c4.971,0,9-4.027,9-8.999C30,18.859,29.248,16.896,28,15.352z
                                          M21,28c-3.865-0.008-6.994-3.135-7-6.999c0.006-3.865,3.135-6.994,7-7c3.865,0.006,6.992,3.135,7,7
                                          C27.992,24.865,24.865,27.992,21,28z"/>
                                    <polygon style="fill:#ccc;" points="22.002,16 20,16 20,20 16,20 16,22 20,22 20,26 22.002,26 22.002,22 26,22 26,20 22.002,20"/>
                                    </g>
                                    </g>
                                    </svg>
                                </figure>
                                <h5><?php echo t_lang('M_TXT_BECOME_A_MEMBER_BANNER'); ?></h5>
                            </a>
                        </li>
                    <?php } ?>
                    <li>
                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'how-it-works.php');
                    ?>">
                            <figure class="icon">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     viewBox="0 0 268.765 268.765" style="enable-background:new 0 0 268.765 268.765;" xml:space="preserve">
                                <g id="Settings">
                                <path style="fill:#ccc;" d="M267.92,119.461c-0.425-3.778-4.83-6.617-8.639-6.617
                                      c-12.315,0-23.243-7.231-27.826-18.414c-4.682-11.454-1.663-24.812,7.515-33.231c2.889-2.641,3.24-7.062,0.817-10.133
                                      c-6.303-8.004-13.467-15.234-21.289-21.5c-3.063-2.458-7.557-2.116-10.213,0.825c-8.01,8.871-22.398,12.168-33.516,7.529
                                      c-11.57-4.867-18.866-16.591-18.152-29.176c0.235-3.953-2.654-7.39-6.595-7.849c-10.038-1.161-20.164-1.197-30.232-0.08
                                      c-3.896,0.43-6.785,3.786-6.654,7.689c0.438,12.461-6.946,23.98-18.401,28.672c-10.985,4.487-25.272,1.218-33.266-7.574
                                      c-2.642-2.896-7.063-3.252-10.141-0.853c-8.054,6.319-15.379,13.555-21.74,21.493c-2.481,3.086-2.116,7.559,0.802,10.214
                                      c9.353,8.47,12.373,21.944,7.514,33.53c-4.639,11.046-16.109,18.165-29.24,18.165c-4.261-0.137-7.296,2.723-7.762,6.597
                                      c-1.182,10.096-1.196,20.383-0.058,30.561c0.422,3.794,4.961,6.608,8.812,6.608c11.702-0.299,22.937,6.946,27.65,18.415
                                      c4.698,11.454,1.678,24.804-7.514,33.23c-2.875,2.641-3.24,7.055-0.817,10.126c6.244,7.953,13.409,15.19,21.259,21.508
                                      c3.079,2.481,7.559,2.131,10.228-0.81c8.04-8.893,22.427-12.184,33.501-7.536c11.599,4.852,18.895,16.575,18.181,29.167
                                      c-0.233,3.955,2.67,7.398,6.595,7.85c5.135,0.599,10.301,0.898,15.481,0.898c4.917,0,9.835-0.27,14.752-0.817
                                      c3.897-0.43,6.784-3.786,6.653-7.696c-0.451-12.454,6.946-23.973,18.386-28.657c11.059-4.517,25.286-1.211,33.281,7.572
                                      c2.657,2.89,7.047,3.239,10.142,0.848c8.039-6.304,15.349-13.534,21.74-21.494c2.48-3.079,2.13-7.559-0.803-10.213
                                      c-9.353-8.47-12.388-21.946-7.529-33.524c4.568-10.899,15.612-18.217,27.491-18.217l1.662,0.043
                                      c3.853,0.313,7.398-2.655,7.865-6.588C269.044,139.917,269.058,129.639,267.92,119.461z M134.595,179.491
                                      c-24.718,0-44.824-20.106-44.824-44.824c0-24.717,20.106-44.824,44.824-44.824c24.717,0,44.823,20.107,44.823,44.824
                                      C179.418,159.385,159.312,179.491,134.595,179.491z"/>
                                </g>
                                </svg>
                            </figure>
                            <h5><?php echo t_lang('M_TXT_MORE_INFO'); ?></h5>
                        </a>
                    </li>
                </ul>
            </div>
            <?php if ($rs = fetchTopCategories(20)) { ?>
                <div class="inlinelisting">
                    <h6><?php echo t_lang('M_TXT_TOP_CATEGORIES'); ?></h6>
                    <ul>
                        <?php
                        while ($row = $db->fetch($rs)) {
                            echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $row['cat_id'] . '&type=side') . '" >' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            <?php } ?>
        </div>
    </section>
    <section class="footer__lower">
        <div class="fixed_container">
            <div class="row">
                <aside class="col-sm-4">
                    <ul class="list__socials">
                        <li><a href="<?php echo CONF_FACEBOOK_URL; ?>"><i class="icon ion-social-facebook"></i></a></li>
                        <li><a href="<?php echo CONF_TWITTER_USER; ?>"><i class="icon ion-social-twitter"></i></a></li>
                        <li><a href="<?php echo CONF_YOUTUBE_URL; ?>"><i class="icon ion-social-pinterest"></i></a></li>
                    </ul>
                </aside>
                <aside class="col-sm-8">
                    <img  alt="" src="<?php echo LOGO_URL . CONF_FRONT_END_FOOTER_LOGO; ?>" class="footerlogo">
                </aside>
            </div>
        </div>
    </section>
    <section class="footer__endrow">
        <div class="fixed_container">
            <div class="row">
                <aside class="col-sm-4">
                    <p><?php echo t_lang('M_TXT_PAYMENT_OPTIONS'); ?></p>
                    <img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/payment_cards.png">
                </aside>
                <aside class="col-sm-8">
                    <ul class="footer__whitelinks">
                        <?php echo printNav(0, 8); ?>
                    </ul>
                    <p class="copyright">© <?php echo date("Y"); ?>  <?php echo t_lang('M_TXT_RIGHT_RESERVE'); ?></p>
                </aside>
            </div>
        </div>
    </section>
</footer>
<?php echo CONF_GOOGLE_ANALYTIC_CODE; ?>
<!--footer start here-->
<script type="text/javascript">
    var placehoder_name = '<?php echo addslashes(t_lang('M_FRM_ENTER_YOUR_EMAIL_ADDRESS')) ?>';
</script>
</div>
<div id="getaway-calender" class="popup" style="display:none">
    <div class="popup__content-wrap">
        <div class="popup__content">
            <section class="getaways_selection">
                <div class="fixed_container">
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>
