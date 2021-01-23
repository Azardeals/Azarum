<?php require_once '../application-top.php'; ?>
<!Doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=768, initial-scale=0">
		<link rel="shortcut icon" type="image/ico" href="<?php echo LOGO_URL . CONF_FAV_LOGO ?>">
        <?php
        include 'js-and-css.inc.php';
        include 'meta.inc.php';
        if (!isCompanyUserLogged()) {
            redirectUser(CONF_WEBROOT_URL . 'merchant/login.php');
        }
        ?>
        <script type="text/javascript">
            image_not_loaded_msg = '<?php echo addslashes(t_lang('M_TXT_IMAGE_CANNOT_LOADED')); ?>';
            cancel = '<?php echo t_lang('M_MSG_Cancel'); ?>';
            yes = '<?php echo t_lang('M_MSG_Yes'); ?>';
        </script>
        <script src="<?php echo CONF_WEBROOT_URL; ?>js/languageswitcher.js"></script>
        <script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.hoverIntent.minified.js"></script>
        <script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.naviDropDown.1.0.js"></script>
        <script type="text/javascript">
            $(function () {
                $('.navigation_vert').naviDropDown({
                    dropDownWidth: '350px',
                    orientation: 'vertical'
                });
            });
        </script>
        <?php $pagename = strrchr($_SERVER['SCRIPT_NAME'], '/'); ?>
        <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
        <script>
            $(document).ready(function () {
                $("#dialog").dialog();
            });
        </script>
    </head>
    <body>
        <div id="wrapper">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td><div id="header">
                            <!-- Top -->
                            <div id="top">
                                <a class="menutrigger" href="javascript:void(0);"></a>
                                <!-- Logo -->
                                <div class="logo"> <a href="merchant-account.php"><?php
                                        $company_id = $_SESSION['logged_user']['company_id'];
                                        $image = $db->query("SELECT company_logo" . $_SESSION['lang_fld_prefix'] . " FROM tbl_companies where company_id=$company_id");
                                        $row = $db->fetch($image);
                                        if ($row['company_logo' . $_SESSION['lang_fld_prefix']] == "") {
                                            ?>
                                            <?php if (CONF_ADMIN_PANEL_LOGO == "") { ?>
                                                <img alt="Logo" src="images/logo.jpg" alt="Logo" />
                                            <?php } else { ?>
                                                <img alt="Logo" border="0" src="<?php echo LOGO_URL . CONF_ADMIN_PANEL_LOGO; ?>">
                                            <?php } ?>
                                        <?php } else { ?>
                                            <img alt="Logo" border="0" src="<?php echo COMPANY_LOGO_URL . $row['company_logo' . $_SESSION['lang_fld_prefix']]; ?>">
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
                                        <li class="droplink" >
                                            <a href="javascript:void(0)" title="Language"><i class="icon ion-android-globe"></i></a>
                                            <div class="dropwrap">
                                                <div class="body">
                                                    <ul class="linksvertical">
                                                        <li><a href="javascript:void(0)" onclick="updateLanguageMerchant('1', '<?php echo $pagename . '?' . $_SERVER['QUERY_STRING']; ?>');">English</a></li>
                                                        <li><a href="javascript:void(0)" onclick="updateLanguageMerchant('2', '<?php echo $pagename . '?' . $_SERVER['QUERY_STRING']; ?>');"><?php echo CONF_SECONDARY_LANGUAGE; ?></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <!-- End of Meta information -->
                            </div>
                            <!-- End of Top-->
                            <!-- The navigation bar -->
                            <div id="navbar">
                                <?php $pagename = strrchr($_SERVER['SCRIPT_NAME'], '/'); ?>
                                <ul id="topnav">
                                    <li><a href="javascript:void(0);" <?php if ($pagename == '/merchant-account.php' || $pagename == '/company-addresses.php') echo 'class="selected"'; ?> ><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a>
                                        <div class="sub" >
                                            <ul>
                                                <li><a href="merchant-account.php" ><?php echo t_lang('M_TXT_ACCOUNT_INFO'); ?></a></li>
                                                <li><a href="company-addresses.php?company_id=<?php echo $_SESSION['logged_user']['company_id']; ?>&page=<?php echo $page; ?>&add=new"  class="nobg"><?php echo t_lang('M_TXT_ADD'); ?> <?php echo t_lang('M_TXT_LOCATION'); ?></a></li>
                                            </ul>
                                            <ul>
                                                <li><a href="company-addresses.php"><?php echo t_lang('M_TXT_MANAGE_LOCATIONS'); ?></a></li>
                                                <li><a href="logout.php"  class="nobg"><?php echo t_lang('M_TXT_LOGOUT'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li><a href="javascript:void(0);" <?php if ($pagename == '/company-deals.php' || $pagename == '/add-deals.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a>
                                        <div class="sub" >
                                            <ul>
                                                <li><a href="company-deals.php" ><?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
                                                <li><a href="tipped-members.php"  ><?php echo t_lang('M_TXT_VOUCHERS'); ?></a></li>
                                                <li><a href="company-review.php" class="nobg" ><?php echo t_lang('M_TXT_COMPANY_REVIEWS_AND_RATINGS'); ?></a></li>
                                            </ul>
                                            <ul>
                                                <li><a href="add-deals.php?add=new&page=1" ><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_DEALS_PRODUCTS'); ?></a></li>
                                                <li><a href="deals-review.php" class="nobg" ><?php echo t_lang('M_TXT_REVIEWS_AND_RATINGS'); ?></a></li>
                                                <li><a href="options.php" class="nobg" ><?php echo t_lang('M_TXT_OPTIONS/ATTRIBUTES'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li><a href="javascript:void(0);" <?php if ($pagename == '/company-transactions.php' || $pagename == '/company-withdrawals.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_TRANSACTIONS'); ?></a>
                                        <div class="sub">
                                            <ul>
                                                <li><a href="company-transactions.php" <?php if ($pagename == '/company-transactions.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_TRANSACTION_HISTORY'); ?></a></li>
                                                <li><a href="company-withdrawals.php" <?php if ($pagename == '/company-withdrawals.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_WITHDRAW_REQUESTS'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li><a href="javascript:void(0);" <?php if ($pagename == '/merchant-report.php' || $pagename == '/tax-report.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_REPORTS'); ?></a>
                                        <div class="sub" >
                                            <ul>
                                                <li><a href="merchant-report.php" ><?php echo t_lang('M_TXT_MERCHANT_REPORT'); ?></a></li>
                                            </ul>
                                            <ul>
                                                <li><a href="tax-report.php" ><?php echo t_lang('M_TXT_TAX_REPORT'); ?></a></li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li><a href="charity.php" <?php if ($pagename == '/charity.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_CHARITY'); ?></a></li>
                                    <li><a href="request-city.php" <?php if ($pagename == '/request-city.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_ADD_CITY_REQUEST'); ?></a></li>
                                    <li>
                                        <a href="message-listing.php?status=<?php echo getNewMessagesCount() > 0 ? 1 : 0; ?>" <?php if ($pagename == '/message-listing.php') echo 'class="selected"'; ?>>
                                            <?php echo t_lang('M_TXT_MESSAGES'); ?>
                                            <?php if (getNewMessagesCount() > 0) { ?>
                                                <span class="msg_flag" id="msg_counter"><?php echo getNewMessagesCount(); ?></span>
                                            <?php } ?>
                                        </a>
                                    </li>
                                    <li><a href="facebook-update.php" <?php if ($pagename == '/facebook-update.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_INTEGRATION_WITH_FACEBOOK_BUSINESS_PAGE'); ?></a></li>
                                    <li><a href="training.php" <?php if ($pagename == '/training.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_TRAINING'); ?></a></li>
                                </ul>
                            </div>
                            <!-- End of navigation bar -->
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
                                                    <figure class="profilepic"><img src="images/default.png" alt=""></figure>
                                                    <span class="profileinfo"><?php echo t_lang('M_TXT_WELCOME'); ?>, <?php echo $_SESSION['logged_user']['company_name']; ?></span>
                                                </div>
                                                <div class="profilelinkswrap">
                                                    <ul class="leftlinks">
                                                        <li><a href="merchant-account.php">View / Edit Profile</a></li>
                                                        <li><a href="message-listing.php?status=0">My Messages</a></li>
                                                        <li><a href="logout.php">Logout</a></li>
                                                    </ul>
                                                </div>
                                            </div>
