<?php
require_once '../application-top.php';
?>
<!Doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=768, initial-scale=0">
		<link rel="shortcut icon" type="image/ico" href="<?php echo LOGO_URL . CONF_FAV_LOGO ?>">
        <?php
        include 'js-and-css.inc.php';
        include 'meta.inc.php';
        ?>
        <script type="text/javascript">
            image_not_loaded_msg = '<?php echo addslashes(t_lang('M_TXT_IMAGE_CANNOT_LOADED')); ?>';
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
                                <div class="logo"> <a href="my-account.php"><?php
                                        $rep_id = $_SESSION['logged_user']['rep_id'];
                                        $image = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM tbl_representative where rep_id=$rep_id");
                                        $row = $db->fetch($image);
                                        if (CONF_ADMIN_PANEL_LOGO == "") {
                                            ?>
                                            <img alt="Logo" src="images/logo.jpg" alt="Logo" />
                                        <?php } else { ?>
                                            <img alt="Logo" border="0" src="<?php echo LOGO_URL . CONF_ADMIN_PANEL_LOGO; ?>">
                                        <?php } ?>
                                    </a>  </div>
                                <!-- End of Logo -->
                                <!-- Meta information -->
                                <div class="meta">
                                              <p class="infol"><strong><?php echo t_lang('M_TXT_WELCOME'); ?>, <?php echo $_SESSION['logged_user']['rep_fname']; ?></strong> <a class="tooltip"   href="javascript:void(0);"><!--  <span>1</span> --> <?php echo date("l M d, Y, H:i"); ?></a></p>
                                    <a class="logout" title="<?php echo t_lang('M_TXT_END_ADMIN_SESSION'); ?>" href="<?php echo CONF_WEBROOT_URL; ?>representative/logout.php"></a>
                                    <ul class="iconmenus">
                                        <li class="viewstore">
                                            <a title="View Store" href="/" target="_blank"><i class="icon ion-home"></i></a>
                                        </li>
                                        <li class="droplink" >
                                            <a href="javascript:void(0)" title="Language"><i class="icon ion-android-globe"></i></a>
                                            <div class="dropwrap">
                                                <div class="body">
                                                    <ul class="linksvertical">
                                                        <li><a href="javascript:void(0)" onclick="updateLanguageRepresentative('1', '<?php echo $pagename . '?' . $_SERVER['QUERY_STRING']; ?>');">English</a></li>
                                                        <li><a href="javascript:void(0)" onclick="updateLanguageRepresentative('2', '<?php echo $pagename . '?' . $_SERVER['QUERY_STRING']; ?>');"><?php echo CONF_SECONDARY_LANGUAGE; ?></a></li>
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
                                <?php $pagename = strrchr($_SERVER['SCRIPT_NAME'], '/'); ?>
                                <ul id="topnav">
                                    <li><a href="my-account.php" <?php if ($pagename == '/my-account.php') echo 'class="selected"'; ?> ><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
                                    <li><a href="businesses.php" <?php if ($pagename == '/businesses.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_MY_BUSINESSES'); ?></a>
                                    <!-- <li><a href="deals.php" <?php //if($pagename == '/deals.php' || $pagename == '/add-deals.php')echo 'class="selected"';       ?>><?php //echo t_lang('M_TXT_MY_DEALS');       ?></a> -->
                                        <!-- <div class="sub" >
                                                <ul>
                                                        <li><a href="deals.php" ><?php //echo t_lang('M_TXT_MY_DEALS');       ?></a></li>
                                                        <li><a href="tipped-members.php"  ><?php //echo t_lang('M_TXT_VOUCHERS');       ?></a></li>
                                                </ul>
                                                <ul>
                                                        <li><a href="add-deals.php?add=new&page=1" ><?php //echo t_lang('M_TXT_ADD_NEW');       ?> <?php //echo t_lang('M_TXT_DEAL');       ?></a></li>



                                                </ul>
                                         </div> -->
                                    </li>
                                    <li><a href="rep-report.php" <?php if ($pagename == '/rep-report.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_REPORTS'); ?> </a></li>
                                    <li><a href="rep-list.php" <?php if ($pagename == '/rep-list.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_MY_TRANSACTIONS_HISTORY'); ?> </a></li>
                                    <li><a href="referral.php" <?php if ($pagename == '/referral.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_REFERRAL_URL'); ?> </a></li>
                                    <li><a href="training.php" <?php if ($pagename == '/training.php') echo 'class="selected"'; ?>><?php echo t_lang('M_TXT_TRAINING'); ?></a></li>
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
                                                    <figure class="profilepic"><img src="images/default.png" alt=""></figure>
                                                    <span class="profileinfo"><?php echo t_lang('M_TXT_WELCOME'); ?>, <?php echo $_SESSION['logged_user']['rep_fname']; ?></span>
                                                </div>
                                                <div class="profilelinkswrap">
                                                    <ul class="leftlinks">
                                                        <li><a href="my-account.php">View / Edit Profile</a></li>
                                                        <li><a href="<?php echo CONF_WEBROOT_URL; ?>representative/logout.php"><?php echo t_lang('M_TXT_LOGOUT'); ?></a></li>
                                                    </ul>
                                                </div>
                                            </div>
