<?php
require_once dirname(__FILE__) . '/application-top.php';
require_once dirname(__FILE__) . '/../includes/navigation-functions.php';
?>
<!Doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=768, initial-scale=0">
		<link rel="shortcut icon" type="image/ico" href="<?php echo LOGO_URL . CONF_FAV_LOGO ?>">
        <?php
        require_once dirname(__FILE__) . '/../js-and-css.inc.php';
        require_once dirname(__FILE__) . '/../meta.inc.php';
        ?>
        <script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.hoverIntent.minified.js"></script>
        <script type="text/javascript">
            image_not_loaded_msg = '<?php echo addslashes(t_lang('M_TXT_IMAGE_CANNOT_LOADED')); ?>';
            cancel = '<?php echo t_lang('M_MSG_Cancel'); ?>';
            yes = '<?php echo t_lang('M_MSG_Yes'); ?>';
        </script>
        <script src="<?php echo CONF_WEBROOT_URL; ?>js/languageswitcher.js"></script>
        <!-- <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/> -->
        <?php $pagename = strrchr($_SERVER['SCRIPT_NAME'], '/'); ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#dialog").dialog();
                $('.navtoggle').click(function () {
                    $(this).toggleClass("active");
                    var el = $("body");
                    if (el.hasClass('toggled-left')) {
                        el.removeClass("toggled-left");
                    } else {
                        el.addClass('toggled-left');
                    }
                    return false;
                });
                $('.left_portion').click(function (e) {
                    e.stopPropagation();
                });
            });
        </script>
    </head>
    <?php
    $bodyclass = '';
    $pagename = strrchr($_SERVER['SCRIPT_NAME'], '/');
    if ($pagename == '/tipped-members.php') {
        $bodyclass = 'layout_full';
    }
    $left_nav_status = CONF_MANAGER_LEFT_NAV_DISPLAY_STATUS;
    if (CONF_MANAGER_LEFT_NAV_DISPLAY_STATUS == 1) {
        $bodyclass = 'layout_full';
        $left_nav_status = 0;
    } else {
        $bodyclass = 'toggled-left';
        $left_nav_status = 1;
    }
    ?>
    <body class="<?php echo $bodyclass; ?>">
        <div id="wrapper ">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td><div id="header">
                            <!-- Top -->
                            <div id="top">
                                <a class="menutrigger" href="javascript:void(0);" onclick="updateLeftNavDisplayStatus(this, <?php echo $left_nav_status ?>)"></a>
                                <!-- Logo -->
                                <div class="logo"> 
                                    <a href="index.php" class="tooltip" title="Administration Home">
                                        <?php if (CONF_ADMIN_PANEL_LOGO == "") { ?>
                                            <img alt="Logo" src="<?php echo CONF_WEBROOT_URL . 'images/logo.jpg' ?>" alt="Logo" />
                                        <?php } else { ?>
                                            <img alt="Logo" border="0" src="<?php echo LOGO_URL . CONF_ADMIN_PANEL_LOGO; ?>">
                                        <?php } ?></a>  </div>
                                <!-- End of Logo -->
                                <!-- Meta information -->
                                <div class="meta">
                                  <p class="infol"><strong><?php echo t_lang('M_TXT_WELCOME'); ?>, <?php echo $_SESSION['admin_logged']['admin_name']; ?></strong> <a class="tooltip"   href="javascript:void(0);"><!--  <span>1</span> --> <?php echo date("l M d, Y, H:i"); ?></a></p>
                                    <a class="logout" title="<?php echo t_lang('M_TXT_END_ADMIN_SESSION'); ?>" href="logout.php"></a>
                                    <ul class="iconmenus">
                                        <li class="viewstore">
                                            <a title="View Store" href="/" target="_blank"><i class="icon ion-home"></i></a>
                                        </li>										
                                        <?php if (checkAdminPermission(13, true)) { ?>
                                            <li class="togglemsg">
                                                <a href="message-listing.php?status=<?php echo getNewMessagesCount() > 0 ? 1 : 0; ?>" title="<?php echo t_lang('M_TXT_MESSAGES'); ?>"><i class="icon ion-android-mail"></i>
                                                    <?php if (getNewMessagesCount() > 0) { ?>
                                                        <span class="counts"><?php echo getNewMessagesCount(); ?></span>
                                                    <?php } ?>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <li class="droplink" >
                                            <a href="javascript:void(0)" title="Language"><i class="icon ion-android-globe"></i></a>
                                            <div class="dropwrap">
                                                <div class="body">
                                                    <ul class="linksvertical">
                                                        <li><a href="javascript:void(0)" onclick="updateLanguage('1', '<?php echo $pagename . '?' . $_SERVER['QUERY_STRING']; ?>');">English</a></li>
                                                        <li><a href="javascript:void(0)" onclick="updateLanguage('2', '<?php echo $pagename . '?' . $_SERVER['QUERY_STRING']; ?>');"><?php echo CONF_SECONDARY_LANGUAGE; ?></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div> 
                                <!-- End of Meta information -->
                                <!-- language -->
                                <!-- /language -->
                            </div>
                            <!-- End of Top-->
                            <!-- The navigation bar -->
                            <div id="navbar">
                                <ul id="topnav">
                                    <?php if (checkAdminPermission(16, true)) { ?>
                                        <li><a href="index.php" <?php echo ($pagename == '/index.php') ? 'class="selected"' : ''; ?>  ><?php echo t_lang('M_TXT_DASHBOARD'); ?></a></li>
                                    <?php } if (checkAdminPermission(5, true)) { ?>
                                        <li><a href="javascript:void(0);" <?php if ($pagename == '/deals.php' || $pagename == '/deal-categories.php' || $pagename == '/discussions.php' || $pagename == '/deals-review.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="deals.php?status=active"><?php echo t_lang('M_TXT_DEALS_PRODUCTS') . ' ' . t_lang('M_TXT_LISTS'); ?></a></li>
                                                    <li><a href="deal-categories.php" class="isParent"><?php echo t_lang('M_TXT_DEALS_PRODUCTS') . ' ' . t_lang('M_FRM_CATEGORIES'); ?> </a></li>
                                                    <?php if (checkAdminPermission(4, true)) { ?>
                                                        <li><a href="tax-rate.php"><?php echo t_lang('M_TXT_TAX_RATE'); ?></a></li>  
                                                        <li><a href="tax-class.php"><?php echo t_lang('M_TXT_TAX_CLASS'); ?></a></li>  
                                                    <?php } ?>
                                                    <li><a href="tipped-members.php" class="nobg" ><?php echo t_lang('M_TXT_ALL_VOUCHERS'); ?></a></li>
                                                    <li><a href="options.php" class="nobg" ><?php echo t_lang('M_TXT_OPTIONS/ATTRIBUTES'); ?></a></li>
                                                </ul>
                                                <ul>
                                                    <?php if (checkAdminAddEditDeletePermission(5, '', 'add')) { ?>
                                                        <li><a href="add-deals.php?page=1&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?> </a></li>
                                                        <li><a href="deal-categories.php?page=1&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS') . ' ' . t_lang('M_FRM_CATEGORIES'); ?></a></li>
                                                        <li><a href="tax-rate.php?page=1&add=new"  class="nobg" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_TAX_RATE'); ?></a>	</li>
                                                        <li><a href="tax-class.php?page=1&add=new"  class="nobg" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_TAX_CLASS'); ?></a>	</li>
                                                    <?php } ?>
                                                    <li><a href="deals-review.php" class="nobg" ><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></a></li>
                                                    <li><a href="import_export.php" class="nobg" ><?php echo t_lang('M_TXT_IMPORT_EXPORT_DEALS'); ?></a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if (checkAdminPermission(3, true)) { ?>
                                        <li><a href="javascript:void(0);" <?php if ($pagename == '/companies.php' || $pagename == '/company-withdraws.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_COMPANIES_MERCHANT'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="companies.php" ><?php echo t_lang('M_TXT_LIST_OF_COMPANIES'); ?></a></li>
                                                    <li><a href="company-review.php" class="nobg" ><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></a></li>
                                                </ul>
                                                <ul>
                                                    <?php if (checkAdminAddEditDeletePermission(3, '', 'add')) { ?>
                                                        <li><a href="companies.php?page=1&add=new" class="nobg" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_COMPANY'); ?></a></li>
                                                    <?php } ?>	
                                                    <li><a href="company-withdraws.php" class="nobg" ><?php echo t_lang('M_TXT_WITHDRAW_REQUESTS'); ?></a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if (checkAdminPermission(8, true)) { ?>
                                        <li><a href="javascript:void(0);" <?php if ($pagename == '/affiliate.php' || $pagename == '/charity.php' || $pagename == '/registered-members.php' || $pagename == '/newsletter-subscribers-import.php' || $pagename == '/newsletter-subscribers.php' || $pagename == '/business-referral.php' || $pagename == '/referral-history.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_USERS'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="affiliate.php"  class="isParent"><?php echo t_lang('M_TXT_AFFILIATE_USERS'); ?> </a></li>
                                                    <li><a href="representative.php"  class="isParent"><?php echo t_lang('M_TXT_REPRESENTATIVE_USERS'); ?> </a></li>
                                                    <li><a href="charity.php?status=active"  class="isParent"><?php echo t_lang('M_TXT_CHARITY_MANAGEMENT'); ?></a>
                                                    <li><a href="registered-members.php"><?php echo t_lang('M_TXT_REGISTERED_USERS'); ?></a></li>
                                                    <li><a href="business-referral.php" class="nobg" ><?php echo t_lang('M_TXT_BUSINESS_REFERRAL'); ?></a></li>
                                                </ul>
                                                <ul>
                                                    <?php if (checkAdminAddEditDeletePermission(8, '', 'add')) { ?>
                                                        <li><a href="affiliate.php?page=1&add=new" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_AFFILIATE'); ?></a></li>
                                                        <li><a href="representative.php?page=1&add=new" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_REPRESENTATIVE'); ?></a></li>
                                                        <li><a href="charity.php?page=1&add=new" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_CHARITY_ORG'); ?> </a></li>
                                                    <?php } ?>	
                                                    <li><a href="newsletter-subscribers.php"><?php echo t_lang('M_TXT_PROMOTION_NEWSLETTER_SUBSCRIBERS'); ?></a></li>
                                                    <li><a href="business-referral.php?mode=downloadcsv" class="nobg" ><?php echo t_lang('M_TXT_BUSINESS_REFERRAL_DOWNLOAD_CSV'); ?></a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if (checkAdminPermission(9, true)) { ?>
                                        <li><a href="javascript:void(0);" <?php if ($pagename == '/admin-users.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ADMIN_USERS'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="admin-users.php" class="nobg" ><?php echo t_lang('M_TXT_ADMIN_USERS_LISTING'); ?></a></li> 
                                                </ul>
                                                <ul>
                                                    <li><a href="admin-users.php?page=1&add=new" class="nobg"  ><?php echo t_lang('M_TXT_ADD_ADMIN_USER'); ?></a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if (checkAdminPermission(1, true)) { ?>
                                        <li><a href="javascript:void(0);" <?php if ($pagename == '/cms-page-listing.php' || $pagename == '/cms-faq-listing.php' || $pagename == '/news.php' || $pagename == '/press-release.php' || $pagename == '/jobs.php' || $pagename == '/business-page.php' || $pagename == '/members-page.php' || $pagename == '/navigation-management.php' || $pagename == '/extra-pages.php' || $pagename == '/gifts-page.php' || $pagename == '/blogs.php' || $pagename == '/blog-categories.php' || $pagename == '/training.php') echo 'class="selected"'; ?> ><?php echo t_lang('M_TXT_CMS'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="cms-page-listing.php"><?php echo t_lang('M_TXT_PAGES'); ?></a></li>
                                                    <li><a href="cms-faq-listing.php"><?php echo t_lang('M_TXT_FAQ_CATEGORY_MANAGEMENT'); ?></a></li>
                                                    <li><a href="news.php" class="isParent"><?php echo t_lang('M_TXT_NEWS'); ?></a></li>
                                                    <?php if (checkAdminPermission(12, true)) { ?>
                                                        <li><a href="press-release.php" class="isParent"><?php echo t_lang('M_TXT_PRESS_RELEASE'); ?></a></li>
                                                    <?php } ?>
                                                    <li><a href="jobs.php" class="isParent"><?php echo t_lang('M_TXT_JOBS'); ?></a></li>
                                                    <li><a href="blogs.php" class="isParent"><?php echo t_lang('M_TXT_BLOGS'); ?></a></li>
                                                    <?php /* <li><a href="business-page.php"><?php echo t_lang('M_TXT_BUSINESS_PAGE');?> <?php echo t_lang('M_TXT_CONTENT');?> </a></li> */ ?>	
                                                    <li><a href="banner.php" class="nobg" ><?php echo t_lang('M_TXT_BANNER_MANAGEMENT'); ?>  </a></li>
                                                </ul>
                                                <ul>
                                                    <li><a href="navigation-management.php"><?php echo t_lang('M_TXT_NAVIGATION_MANAGEMENT'); ?></a></li>
                                                    <li><a href="extra-pages.php">  <?php echo t_lang('M_TXT_EXTRA_PAGES'); ?> </a></li>
                                                    <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                                                        <li><a href="news.php?page=1&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_NEWS'); ?></a></li>
                                                        <li><a href="press-release.php?page=1&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_PRESS_RELEASE'); ?></a></li>
                                                        <li><a href="jobs.php?page=1&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_JOBS'); ?></a></li>
                                                    <?php } ?>
                                                    <?php /* <li><a href="gifts-page.php"   ><?php echo t_lang('M_TXT_GIFT_PAGE');?> <?php echo t_lang('M_TXT_CONTENT');?></a></li>
                                                      <li><a href="members-page.php"><?php echo t_lang('M_TXT_MEMBER_PAGE');?> <?php echo t_lang('M_TXT_CONTENT');?> </a></li> */ ?>
                                                    <li><a href="blog-categories.php" ><?php echo t_lang('M_TXT_BLOG_CATEGORIES'); ?></a></li>
                                                    <li><a href="training.php" class="nobg" ><?php echo t_lang('M_TXT_TRAINING_VIDEO'); ?>  </a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if (checkAdminPermission(7, true)) { ?>
                                        <li><a href="javascript:void(0);" <?php if ($pagename == '/configurations.php' || $pagename == '/email-templates.php' || $pagename == '/database-backup.php' || $pagename == '/cities.php' || $pagename == '/payment-settings.php' || $pagename == '/language-managment.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_SETTINGS'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="configurations.php"><?php echo t_lang('M_TXT_GENERAL_SETTINGS'); ?></a></li>
                                                    <?php if (CONF_PAYMENT_PRODUCTION == 0) { ?>
                                                        <li><a href="registration-offers.php"><?php echo t_lang('M_TXT_REGISTRATION_OFFERS'); ?></a></li>
                                                    <?php } ?>
                                                    <li><a href="email-templates.php"><?php echo t_lang('M_TXT_EMAIL_TEMPLATES'); ?></a></li>
                                                    <li><a href="cities.php?status=active" class="nobg" ><?php echo t_lang('M_TXT_CITIES_MANAGEMENT'); ?></a>	</li>
                                                    <li><a href="states.php" ><?php echo t_lang('M_TXT_STATES_MANAGEMENT'); ?></a>	</li>
                                                    <li><a href="countries.php" ><?php echo t_lang('M_TXT_COUNTRY_MANAGEMENT'); ?></a>	</li>
                                                    <li><a href="tax-zones.php" class="nobg"><?php echo t_lang('M_TXT_ZONE_MANAGEMENT'); ?></a></li>
                                                    <?php if (checkAdminPermission(6, true)) { ?>
                                                                                                                                                                                                        <!--li><a href="database-backup.php" class="nobg"><?php echo t_lang('M_TXT_DATABASE_BACKUP_RESTORE'); ?></a></li-->
                                                    <?php } ?>
                                                </ul>
                                                <ul>
                                                    <li><a href="payment-settings.php"><?php echo t_lang('M_TXT_PAYMENT_GATEWAY_SETTINGS'); ?></a></li>
                                                    <li><a href="language-managment.php"><?php echo t_lang('M_TXT_LANGUAGE_MANAGEMENT'); ?></a></li>
                                                    <?php if (checkAdminAddEditDeletePermission(7, '', 'add')) { ?>	
                                                        <li><a href="cities.php?page=1&add=new" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_FRM_CITY'); ?></a></li>
                                                        <li><a href="states.php?page=1&add=new"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_FRM_STATE'); ?></a></li>
                                                        <li><a href="countries.php?page=1&add=new"  class="nobg" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_FRM_COUNTRY'); ?></a>	</li>
                                                        <li><a href="tax-zones.php?page=1&add=new"  class="nobg" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_FRM_ZONE'); ?></a>	</li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if (checkAdminPermission(14, true)) { ?>
                                        <li><a href="javascript:void(0)"><?php echo t_lang('M_TXT_MANAGE_MAILCHIMP'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="campaign.php"><?php echo t_lang('M_TXT_CAMPAIGN'); ?></a></li> 
                                                </ul>
                                                <ul>
                                                    <li><a href="manage-campaign.php"  ><?php echo t_lang('M_TXT_MANAGE_CAMPAIGN'); ?></a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if (checkAdminPermission(15, true)) { ?>
                                        <li><a href="javascript:void(0);"><?php echo t_lang('M_TXT_REPORTS'); ?></a>
                                            <div class="sub" >
                                                <ul>
                                                    <li><a href="referral-history.php"><?php echo t_lang('M_TXT_REFERRAL_COMMISSION_TRANSACTION'); ?></a></li>
                                                </ul>
                                                <ul>
                                                    <li><a href="merchant-report.php"><?php echo t_lang('M_TXT_MERCHANT_REPORT'); ?></a></li>
                                                </ul>
                                                <ul>
                                                    <li><a href="companies-payment-report.php"><?php echo t_lang('M_TXT_PAYMENT_REPORT'); ?></a></li>
                                                </ul>
                                                <ul>
                                                    <li><a href="all-deals-purchased.php"><?php echo t_lang('M_TXT_SALES'); ?></a></li>
                                                </ul>                                                
                                                <ul>
                                                    <li><a href="tax-report.php"><?php echo t_lang('M_TXT_TAX_REPORT'); ?></a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    <?php } if ($_SESSION['admin_logged']['admin_id'] == 1 && CONF_PAYMENT_PRODUCTION == 0) { ?>
                                        <li><a href="clean-data.php"><?php echo t_lang('M_TXT_CLEAN_DATA'); ?></a></li>
                                        <li><a href="sent-emails.php"><?php echo t_lang('M_TXT_SENT_EMAILS'); ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <!-- End of navigation bar" -->
                        </div></td>
                </tr>
                <tr>
                    <td><div class="main_tbl">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                                <tr>
                                    <td class="left_portion" width="230">
                                        <div class="left_nav">
                                            <div class="profilewrap">
                                                <div class="profilecover">
                                                    <?php $src = CONF_WEBROOT_URL . 'user-image-crop.php?id=' . $_SESSION['admin_logged']['admin_id'] . '&type=Profile?time=' . time(); ?>
                                                    <figure class="profilepic"><img src="<?php echo $src; ?>" alt=""></figure>
                                                    <span class="profileinfo"><?php echo t_lang('M_TXT_WELCOME'); ?>, <?php echo $_SESSION['admin_logged']['admin_name']; ?> </span>
                                                </div>    
                                                <div class="profilelinkswrap">
                                                    <ul class="leftlinks">
                                                        <li><a href="view-profile.php"><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?> </a></li>
                                                        <li><a href="message-listing.php?status=0"><?php echo t_lang('M_TXT_MY'); ?> <?php echo t_lang('M_TXT_MESSAGES'); ?></a></li>
                                                        <li><a href="my-account.php"> <?php echo t_lang('M_TXT_EDIT_ACCOUNT'); ?></a></li>
                                                        <li><a href="change-password.php"><?php echo t_lang('M_TXT_CHANGE_PASSWORD'); ?> </a></li>
                                                        <li><a href="logout.php"><?php echo t_lang('M_TXT_END_ADMIN_SESSION'); ?></a></li>
                                                    </ul>   
                                                </div>    
                                            </div> 