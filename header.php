<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
date_default_timezone_set(CONF_TIMEZONE);
$current_top_tab_url = 'deal.php';
require_once './update-only-deal-status.php';
$pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4);
switch ($pagename) {
    case 'my-account':
    case 'my-deals':
    case 'my-wallet':
    case 'my-profile':
        $current_top_tab_url = 'my-account.php';
        break;
    case 'login':
        $current_top_tab_url = 'login.php';
        break;
    default:
        $current_top_tab_url = 'deal.php';
        break;
}
//echo '<pre>';print_r($_SESSION); exit;
if (strpos($_SERVER['SCRIPT_FILENAME'], '/terms.php') != true && strpos($_SERVER['SCRIPT_FILENAME'], '/privacy.php') != true && strpos($_SERVER['SCRIPT_FILENAME'], '/cms-page.php') != true && strpos($_SERVER['SCRIPT_FILENAME'], '/faq.php') != true && strpos($_SERVER['SCRIPT_FILENAME'], '/suggest-a-bussiness.php') != true && strpos($_SERVER['SCRIPT_FILENAME'], '/how-it-works.php') != true && strpos($_SERVER['SCRIPT_FILENAME'], '/login.php') != true && strpos($_SERVER['SCRIPT_FILENAME'], '/affiliate-login.php') != true) {
    if ($_SESSION['city'] == "") {
        $cityname = '1';
        $_SESSION['city'] = '0';
        if (detectDevice() == 2) {
            redirectUser(CONF_WEBROOT_URL);
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subscribe_newsletter'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    $post = getPostedData();
    if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $post['sub_email'])) {
        $msg->addError(t_lang('M_TXT_INVALID_EMAIL_ADDRESS'));
        redirectUser('');
    } else {
        $post['sub_email'] = trim($post['sub_email']);
        $check_unique = $db->query("select * from  tbl_newsletter_subscription where subs_email=" . $db->quoteVariable($post['sub_email']) . " and  subs_city=" . $db->quoteVariable($post['city']) . "");
        $result = $db->fetch($check_unique);
        if ($db->total_records($check_unique) == 0) {
            $record = new TableRecord('tbl_newsletter_subscription');
            $record->assignValues($post);
            $code = mt_rand(0, 999999999999999);
            $record->setFldValue('subs_addedon', date('Y-m-d H:i:s'), false);
            $record->setFldValue('subs_code', $code, '');
            $record->setFldValue('subs_email', $post['sub_email'], '');
            $record->setFldValue('subs_email_verified', 1, '');
            $record->setFldValue('subs_city', $post['city'], '');
            if (isset($_COOKIE['affid']))
                $record->setFldValue('subs_affiliate_id', $_COOKIE['affid'] + 0);
            $email = $post['sub_email'];
            $success = $record->addNew();
            if ($success) {
                $nc_subs_id = $record->getId();
                if (!subscribeToMailChimp($post)) {
                    //function defined in sit- function.php
                    redirectUser('');
                }
                insertsubscatCity($nc_subs_id);
                if (is_numeric($post['city'])) {
                    selectCity(intval($post['city']));
                }
                $rs = $db->query("select * from tbl_email_templates where tpl_id=5");
                $row_tpl = $db->fetch($rs);
                $messageAdmin = 'Dear ' . CONF_EMAILS_FROM_NAME . ',
				' . $email . ' is subscribing your newsletter.';
                $message = $row_tpl['tpl_message' . $_SESSION['lang_fld_prefix']];
                $subject = $row_tpl['tpl_subject' . $_SESSION['lang_fld_prefix']];
                $arr_replacements = array(
                    'xxemailxx' => $email,
                    'xxsiteurlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxverificationcodexx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'newsletter-subscription.php?code=' . $code . '&mail=' . $email,
                    'xxcityxx' => $_SESSION['city_to_show'],
                    'xxsite_namexx' => CONF_SITE_NAME,
                    'xxsite_urlxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL,
                    'xxshadow_imgxx' => 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '/images/shadow.jpg',
                    'xxserver_namexx' => $_SERVER['SERVER_NAME'],
                    'xxwebrooturlxx' => CONF_WEBROOT_URL
                );
                foreach ($arr_replacements as $key => $val) {
                    $subject = str_replace($key, $val, $subject);
                    $message = str_replace($key, $val, $message);
                }
                if ($_SESSION['city_to_show'] != "") {
                    if ($row_tpl['tpl_status'] == 1) {
                        sendMail($email, $subject . ' - ' . time(), emailTemplate($message), $headers);
                    }
                }
                $msg->addMsg(t_lang('M_TXT_THANKYOU_FOR_SUBSCRIBING_WITH_US'));
                redirectUser('');
                #########################################
            }
        } else {
            $msg->addMsg(t_lang('M_TXT_YOU_HAVE_ALREADY_SUBSCRIBED'));
            redirectUser('');
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        require_once './meta.inc.php';
        ?>
        <!-- Mobile Specific Metas ================================================== -->
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">  -->
        <link rel="shortcut icon" type="image/ico" href="<?php echo LOGO_URL . CONF_FAV_LOGO ?>">
        <link rel="alternate" type="application/rss+xml" title="<?php echo CONF_SITE_NAME; ?>" href="http://<?php echo $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'deal-rss.php?city=' . intval($_SESSION['city']); ?>" />
        <script>var CONF_GOOGLE_MAP_KEY = '<?php echo CONF_GOOGLE_MAP_KEY; ?>';</script>

        <?php
        require_once './js-and-css.inc.php';
        ?>
        <!-- favicon ================================================== -->
        <?php
        $pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4);
        if ($pagename == 'news-detail') {
            ?>
            <link href="<?php echo CONF_WEBROOT_URL; ?>css/prettyPhoto.css" rel="stylesheet" type="text/css" />
            <script src="<?php echo CONF_WEBROOT_URL; ?>js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
            <script type="text/javascript" charset="utf-8">
                $(document).ready(function () {
                    $(" a[rel^='prettyPhoto']").prettyPhoto({theme: 'facebook'});
                });
            </script>
        <?php }
        ?>
        <?php
        if ($pagename == 'blog-details') {
            $blog_id = (int) $_GET['id'];
            $srch = new SearchBase('tbl_blogs', 'b');
            $srch->joinTable('tbl_blog_categories', 'LEFT OUTER JOIN', 'b.blog_cat_id=bc.cat_id', 'bc');
            $srch->addCondition('blog_id', '=', $blog_id);
            $srch->addMultipleFields(array('b.*', 'bc.*'));
            $srch->addFld('IF(blog_admin_id, (SELECT admin_name FROM tbl_admin a WHERE a.admin_id = b.blog_admin_id), (SELECT user_name FROM tbl_users u WHERE u.user_id = b.blog_user_id)) AS blogger_name');
            $srch->addOrder('blog_added_on', 'desc');
            $rs_listing = $srch->getResultSet();
            $data = $db->fetch($rs_listing);
            ?>
            <?php
            if (CONF_SSL_ACTIVE == 1) {
                $ssl = 'https://';
            } else {
                $ssl = 'http://';
            }
            $blog_description = html_entity_decode($data['blog_description']);
            $cleanString = filter_var($blog_description, FILTER_SANITIZE_STRING);
            ?>		 

            <!-- OG Product Facebook Meta [ -->
            <meta property="og:type" content="product" />
            <meta property="og:title" content="<?php echo $data['blog_title']; ?>" />
            <meta property="og:site_name" content="<?php echo $ssl . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL; ?>" />
            <meta property="og:image" content="<?php echo $ssl . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-image.php?id=' . $data['blog_id']; ?>&w=800&h=550&<?php echo time(); ?>" /> 
            <meta property="og:url" content="<?php echo $ssl . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-details.php?id=' . $data['blog_id']; ?>" />
            <meta property="og:description" content="<?php echo $cleanString; ?>" />
            <!-- ]   -->
            <!--Here is the Twitter Card code for this product  -->
            <?php if (!empty(CONF_TWITTER_USER)) { ?>
                <meta name="twitter:card" content="product">
                <meta name="twitter:site" content="@<?php echo CONF_TWITTER_USER; ?>">
                <meta name="twitter:title" content="<?php echo $data['blog_title']; ?>">
                <meta name="twitter:description" content="<?php echo $cleanString; ?>">
                <meta name="twitter:image:src" content="<?php echo $ssl . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-image.php?id=' . $data['blog_id']; ?>&w=800&h=550&<?php echo time(); ?>" />
            <?php }; ?>
            <!-- End Here is the Twitter Card code for this product  -->
        <?php } ?>
        <script type="text/javascript">
            var txtsessionexpire = "<?php echo t_lang('M_MSG_SESSION_EXPIRE_PLEASE_LOGIN'); ?>";
            image_not_loaded_msg = '<?php echo addslashes(t_lang('M_TXT_IMAGE_CANNOT_LOADED')); ?>';
            function closeMsgDiv(obj) {
                $(obj).parent().addClass('fadeOutUp');
                $(obj).parent().removeClass('fadeInDown');
                setTimeout(function () {
                    $(obj).closest('#msg').remove();
                }, 1000);
                return false;
            }
            cancel = '<?php echo t_lang('M_MSG_Cancel'); ?>';
            yes = '<?php echo t_lang('M_MSG_Yes'); ?>';
        </script>
    </head>
    <body>
        <div id="wrapper">
            <!--header start here-->
            <header id="header">
                <div class="navigations__overlay"></div>
                <section class="section_secondary">
                    <div class="fixed_container">
                        <div class="row">
                            <div class="col-sm-5 right userinfo userinfo2">
                                <ul class="topnav">
                                    <?php include CONF_VIEW_PATH . 'login-navigation.php'; ?>
                                </ul>
                                <?php if (!defined('CONF_LANGUAGE_SWITCHER') || (CONF_LANGUAGE_SWITCHER == 1)) { ?>
                                    <div class="selector">
                                        <ul>
                                            <?php
                                            if (isset($_SESSION['language'])) {
                                                if ($_SESSION['language'] == 2) {
                                                    ?>
                                                    <li ><a href="javascript:void(0);" onclick="updateLanguageFront('1');"><?php echo t_lang('M_TXT_ENGLISH'); ?></a></li>
                                                    <li ><a href="javascript:void(0);" class="active"><?php echo CONF_SECONDARY_LANGUAGE; ?></a></li>
                                                    <?php
                                                }
                                                if ($_SESSION['language'] == 1) {
                                                    ?>
                                                    <li ><a href="javascript:void(0);" class="active"><?php echo t_lang('M_TXT_ENGLISH'); ?></a></li>
                                                    <li class=" "><a href="javascript:void(0);" onclick="updateLanguageFront('2');"><?php echo CONF_SECONDARY_LANGUAGE; ?></a></li>
                                                    <?php
                                                }
                                            } else {
                                                ?>      <li ><a href="javascript:void(0);" onclick="updateLanguageFront('1');"><?php echo t_lang('M_TXT_ENGLISH'); ?></a></li>
                                                <li ><a href="javascript:void(0);" onclick="updateLanguageFront('2');"><?php echo CONF_SECONDARY_LANGUAGE; ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-sm-3 city-selector">
                                <ul class="topnav">
                                    <?php include CONF_VIEW_PATH . 'cities-navigation.php'; ?>
                                </ul>
                            </div>
                            <?php if (count($system_alerts) > 0 && strlen(trim($system_alerts[0])) > 0) { ?>
                                <div class=" col-sm-4 aligncenter hide__mobile hide__tab">
                                    <?php
                                    foreach ($system_alerts as $val)
                                        echo '<div class="topadvertisement"><p>' . $val . '</p></a></div>';
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </section>
                <section class="section_primary">
                    <div class="fixed_container">
                        <div class="row">
                            <aside class="col-md-7 static">
                                <a href="javascript:void(0);" class="navs_toggle"></a>
                                <figure class="logo"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'home.php'); ?>"><img src="<?php echo LOGO_URL . CONF_FRONT_END_LOGO ?>" alt=""></a></figure>
                                <div class="mobile__overlay"></div>
                                <div class="navpanel">
                                    <ul class="navigations">
                                        <?php include CONF_VIEW_PATH . 'deals-navigation.php'; ?>
                                        <li class="navchild">
                                            <?php include CONF_VIEW_PATH . 'deal-category-navigation.php'; ?>
                                        </li>
                                        <li class="navchild">
                                            <?php include CONF_VIEW_PATH . 'product-category-navigation.php'; ?>
                                        </li>
                                        <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'getaways'); ?>"><?php echo t_lang('M_TXT_GETAWAYS'); ?>  </a></li>
                                    </ul>
                                </div>
                            </aside>
                            <aside class="col-md-5 static last">
                                <ul class="actions">
                                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-favorites-deals.php'); ?>"><i class="icon ion-ios-heart-outline"></i><?php if (favoriteDealCount()) { ?><span class="count"><?php echo intval(favoriteDealCount()); ?></span><?php } ?></a></li>
                                    <?php
                                    $cart = new Cart();
                                    $item = $cart->getItemCount();
                                    ?>
                                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'buy-deal.php'); ?>"><i class="icon ion-bag"></i>
                                            <?php echo ($item > 0) ? '<span class="count">' . $item . '</span>' : '' ?>
                                        </a></li>
                                    <li class="user__account desktop__hide"><a href="#"><i class="icon ion-ios-person-outline"></i></a></li>
                                </ul>
                                <div class="searchbar">
                                    <!-- <form>
                                        <input type="text" placeholder="Search here...">
                                        <button>
                                    </form>-->
                                    <form action="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'search.php'); ?>" method="get" class="subscribeForm" id="searchform">
                                        <input id="dealSearch" type="text" name="q" placeholder= "<?php echo t_lang('M_TXT_SEARCH_DEAL_CATEGORY'); ?>" value="<?php
                                        if (!empty($_REQUEST['q'])) {
                                            echo htmlentities(urldecode($_REQUEST['q']));
                                        };
                                        ?>" >
                                        <input type="hidden" name="cat" value="" id="catgory">
                                        <button><i class="icon ion-android-search"></i></button>
                                    </form>
                                </div>
                            </aside>
                        </div>
                    </div>
                    <?php
                    if ($pagename == 'index') {
                        $rows = fetchBannerDetail(3, 1);
                        if (!empty($rows)) {
                            echo '<div class="banner__sticky">';
                            echo ' <span class="circle">' . t_lang('M_TXT_HOME_BANNER_CONTENT') . '</span>';
                            foreach ($rows as $key => $value) {
                                $banner_name = isset($value['banner_name']) ? $value['banner_name'] : "";
                                $banner_target = isset($value['banner_target']) ? $value['banner_target'] : "";
                                $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $value['banner_id'] . '&type=' . $value['banner_type'];
                                echo '<div class="bannerimg"><a href="' . $value['banner_url'] . '"  target="' . $banner_target . '" ><img src="' . $src . '" alt="'
                                . $banner_name . '" ></a></div>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </section>
            </header>
            <!-- header end-->
            <?php
            $class = "";
            if ($pagename == "affiliate-login" || $pagename == "affiliate-forgot-password" || $pagename == "forgot-password" || $pagename == "reset-password") {
                $class = "affiliate-login";
            }
            if ($pagename == "how-it-works" || $pagename == "job-apply" || $pagename == "success.php") {
                $class = "page__blog";
            } elseif ($pagename == "merchant-sign-up" || $pagename == "suggest-a-business" || $pagename == "cms-page" || $pagename == "contact-us" || $pagename == "blog-listing" || $pagename == "faq" || $pagename == "faq-detail" || $pagename == "press" || $pagename == "press-detail" || $pagename == "job-detail") {
                $class = "page__panel-right";
            }
            ?>
            <!--main start here-->
            <div id="body" class="<?php echo $class; ?>">
                <script type="text/javascript" charset="utf-8">
                    $(document).ready(function () {
                        $("#dealSearch").autocomplete({
                            source: "<?php echo CONF_WEBROOT_URL; ?>autocomplete-deals.php",
                            select: function (event, ui) {
                                $("#dealSearch").val(ui.item.category);
                                return false;
                            },
                            appendTo: ".searchbar",
                        }).autocomplete("instance")._renderMenu = function (ul, items) {
                            var that = this,
                                    currentCategory = "";
                            $.each(items, function (index, item) {
                                var li;
                                if (item.category != currentCategory) {
                                    //   currentCategory = '"' + item.category + '"';
                                    var li_cat = $('<li />');
                                    li_cat.addClass("ui-autocomplete-category");
                                    li_cat.append("<a href='javascript:void(0);'  onclick='curr_cat(this)'>" + item.category);
                                    li_cat.attr("aria-label", item.category);
                                    ul.append(li_cat);
                                    currentCategory = item.category;
                                }
                                li = that._renderItemData(ul, item);
                                if (item.category) {
                                    li.attr("aria-label", item.category + " : " + item.label);
                                    li.html("<a href='javascript:void(0);' class='drop_subcategory' onclick=requestResult('" + escapeString(item.category) + "','" + escapeString(item.label) + "');>In <span >" + item.label + "</span></a>");
                                }
                            });
                        }
                    });
                    function requestResult(category, label) {
                        $('#dealSearch').val(category);
                        $('#catgory').val(label);
                        $('#searchform').submit();
                    }
                    function curr_cat(obj) {
                        var categoryValue = $(obj).text();
                        $('#dealSearch').val(categoryValue);
                        $('#searchform').submit();
                    }
                    function fetchurl(value) {
                        callAjax('/common-ajax.php', 'mode=getFriendlyUrl&cat_id=' + value, function (t) {
                            var obj = JSON.parse(t);
                            friendlyurl = obj.msg;
                            window.location.replace(friendlyurl);
                        });
                    }
                    function escapeString(text) {
                        text = text.replace("'", "&#39;");
                        text = text.replace('"', '&#34;');
                        return encodeURIComponent(text);
                    }
                </script>
