<?php

function getCleanMetaString($str, $length = 50)
{
    $str = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', strip_tags($str));
    $length = intval($length);
    if ($length < 1) {
        $length = 50;
    }
    $str = subStringByWords($str, $length);
    return trim($str);
}

$page_keywords = "";
$page_name = "";
$page_description = "";
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'index.php') {
    $srch = new SearchBase('tbl_extra_values');
    $srch->doNotCalculateRecords();
    $srch->doNotLimitRecords();
    $srch->addCondition('extra_conf_name', 'IN', array(
        'extra_home_page_meta_title',
        'extra_home_page_meta_description',
        'extra_home_page_meta_keywords'
    ));
    $srch->addMultipleFields(array(
        'extra_conf_name',
        'extra_conf_val' . $_SESSION['lang_fld_prefix']
    ));
    $rs = $srch->getResultSet();
    $rows = $db->fetch_all_assoc($rs);
    $page_name = $rows['extra_home_page_meta_title'];
    $page_description = $rows['extra_home_page_meta_description'];
    $page_keywords = $rows['extra_home_page_meta_keywords'];
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'cms-page.php') {
    //echo $_SERVER['SCRIPT_FILENAME'];
    $cms_page = $db->query("Select  nl_id,nl_cms_page_id from tbl_nav_links where nl_deleted=0 ");
    $row = $db->fetch_all_assoc($cms_page);
    //print_r($row)	;
    $page_name = t_lang("M_TXT_CONTENT_COMING_SOON");
    if (in_array($_GET['id'], $row)) {
        if ($_GET['id'] != "" || isset($_GET['id'])) {
            $srch = new SearchBase('tbl_cms_pages', 'pag');
            $srch->addCondition('pag.page_deleted', '=', '0');
            $srch->addCondition('pag.page_active', '=', '1');
            $srch->addCondition('pag.page_id', '=', $_GET['id']);
            $rs = $srch->getResultSet();
            $srch->getQuery();
            $row = $db->fetch($rs);
            $page_name = $row['page_name' . $_SESSION['lang_fld_prefix']];
            $page_description = $row['page_meta_description' . $_SESSION['lang_fld_prefix']];
            $page_keywords = $row['page_meta_keywords' . $_SESSION['lang_fld_prefix']];
            $page_active = $row['page_active'];
            $page_id = $row['page_id'];
            if ($page_name == "") {
                $page_name = t_lang("M_TXT_CONTENT_COMING_SOON");
            }
            #####Server Side check for the cms Inactive Parent's childeren check 
            $PageInactive = $db->query("SELECT SQL_CALC_FOUND_ROWS * FROM `tbl_cms_pages` page INNER JOIN `tbl_nav_links` nl on nl.nl_cms_page_id=page.page_id AND page.page_active=0");
            while ($PageResult = $db->fetch($PageInactive)) {
                $nl_code = $PageResult['nl_code'];
                $Inactive_page_id = $PageResult['page_id'];
                $ParentChild = $db->query("SELECT SQL_CALC_FOUND_ROWS nl.* FROM `tbl_nav_links` nl INNER JOIN `tbl_navigations` nav on nav.nav_id=1 AND nav.nav_active=1 WHERE nl.`nl_nav_id` = '1' AND nl.`nl_deleted` = '0' AND nl.`nl_code` LIKE '$nl_code%' ORDER BY nl.nl_code asc, nl.nl_display_order asc");
                while ($ParentResult = $db->fetch($ParentChild)) {
                    $childInactive = $ParentResult['nl_cms_page_id'];
                    if ($childInactive == $_GET['id']) {
                        header("Location:cms-page.php");
                        exit;
                    }
                }
            }
            #####Server Side check for the cms Inactive Parent's childeren check end 	
        }
    } else {
        $page_name = t_lang("M_TXT_CONTENT_COMING_SOON");
    }
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'faq-detail.php') {
    if ($_GET['ques'] != "" || isset($_GET['ques'])) {
        $srch = new SearchBase('tbl_cms_faq', 'faq');
        $srch->addCondition('faq.faq_deleted', '=', '0');
        $srch->addCondition('faq.faq_active', '=', '1');
        $srch->addCondition('faq.faq_id', '=', $_GET['ques']);
        $rs = $srch->getResultSet();
        $srch->getQuery();
        $row = $db->fetch($rs);
        if ($row['faq_meta_title'] != "") {
            $page_name = $row['faq_meta_title' . $_SESSION['lang_fld_prefix']];
        } else {
            $page_name = getCleanMetaString($row['faq_question_title' . $_SESSION['lang_fld_prefix']], 50);
        }
        if ($row['faq_meta_discription' . $_SESSION['lang_fld_prefix']] != "") {
            $page_description = $row['faq_meta_discription' . $_SESSION['lang_fld_prefix']];
        } else {
            $page_description = getCleanMetaString($row['faq_answer_brief' . $_SESSION['lang_fld_prefix']], 100);
        }
        if ($row['faq_meta_keywords' . $_SESSION['lang_fld_prefix']] != "") {
            $page_keywords = $row['faq_meta_keywords' . $_SESSION['lang_fld_prefix']];
        } else {
            $page_keywords = getCleanMetaString($row['faq_answer_brief' . $_SESSION['lang_fld_prefix']], 100);
        }
        $page_active = $row['faq_active'];
        $page_id = $row['faq_id'];
    }
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'press-detail.php') {
    if ($_GET['id'] != "" || isset($_GET['id'])) {
        $srch = new SearchBase('tbl_press_release', 'pr');
        $srch->addCondition('pr.pr_id', '=', $_GET['id']);
        $srch->addCondition('pr.pr_status', '=', 1);
        $rs = $srch->getResultSet();
        $srch->getQuery();
        $row = $db->fetch($rs);
        $page_name = $row['pr_title' . $_SESSION['lang_fld_prefix']];
        $page_description = $row['pr_title' . $_SESSION['lang_fld_prefix']];
        $page_keywords = $row['pr_title' . $_SESSION['lang_fld_prefix']];
    }
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'deal.php' || $_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'city-deals.php' || $_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'instant-deal.php') {
    if (isset($_GET['deal']) && $_GET['deal'] != "") {
        $srch = new SearchBase('tbl_deals', 'd');
        $srch->addCondition('d.deal_deleted', '=', '0');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('d.deal_id', '=', $_GET['deal']);
        $rs = $srch->getResultSet();
        $srch->getQuery();
        $row = $db->fetch($rs);
        if (trim($row['deal_meta_title' . $_SESSION['lang_fld_prefix']]) != "") {
            $page_name = $row['deal_meta_title' . $_SESSION['lang_fld_prefix']];
        } else {
            $page_name = getCleanMetaString($row['deal_name' . $_SESSION['lang_fld_prefix']], 50);
        }
        if (trim($row['deal_meta_description' . $_SESSION['lang_fld_prefix']]) != "") {
            $page_description = $row['deal_meta_description' . $_SESSION['lang_fld_prefix']];
        } else {
            $page_description = getCleanMetaString($row['deal_desc' . $_SESSION['lang_fld_prefix']], 160);
        }
        if (trim($row['deal_meta_keywords' . $_SESSION['lang_fld_prefix']]) != "") {
            $page_keywords = $row['deal_meta_keywords' . $_SESSION['lang_fld_prefix']];
        }
    } else {
        if ($_SESSION['city'] > 0) {
            $srch = new SearchBase('tbl_cities', 'c');
            $srch->addCondition('c.city_deleted', '=', '0');
            $srch->addCondition('c.city_active', '=', '1');
            $srch->addCondition('c.city_request', '=', '0');
            $srch->addCondition('c.city_id', '=', $_SESSION['city']);
            $rs = $srch->getResultSet();
            $srch->getQuery();
            $row = $db->fetch($rs);
            if ($row['city_meta_title' . $_SESSION['lang_fld_prefix']] != "") {
                $page_name = $row['city_meta_title' . $_SESSION['lang_fld_prefix']];
            }
            if ($row['city_meta_description'] != "") {
                $page_description = $row['city_meta_description' . $_SESSION['lang_fld_prefix']];
            }
            if ($row['city_meta_keywords'] != "") {
                $page_keywords = $row['city_meta_keywords' . $_SESSION['lang_fld_prefix']];
            }
        }
    }
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'merchant-favorite.php') {
    if (isset($companyrow) && isset($companyrow['company_name' . $_SESSION['lang_fld_prefix']])) {
        $page_name = $companyrow['company_name' . $_SESSION['lang_fld_prefix']];
        if (isset($companyrow['company_profile' . $_SESSION['lang_fld_prefix']])) {
            $page_description = getCleanMetaString($companyrow['company_profile' . $_SESSION['lang_fld_prefix']], 160);
        }
    }
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'contact-us.php') {
    $page_name = t_lang('M_TXT_CONTACT_US');
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'privacy.php') {
    $page_name = t_lang('M_TXT_PRIVACY_POLICY');
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'terms.php') {
    $page_name = t_lang('M_TXT_TERMS_OF_USE');
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'login.php') {
    $page_name = t_lang('M_TXT_USERS_LOGIN');
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'my-account.php') {
    $page_name = t_lang('M_TXT_USERS_ACCOUNT');
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'forgot-password.php') {
    $page_name = t_lang('M_TXT_FORGOT_PASSWORD');
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'get-featured.php') {
    $page_name = t_lang('M_TXT_GET_FEATURED');
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'suggest-a-business.php') {
    $page_name = t_lang('M_TXT_SUGGEST_BUSINESS');
}
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php
    if ($page_name != "") {
        echo $page_name;
    } else {
        echo CONF_META_TITLE;
    }
    ?></title>
<meta name="description"  content="<?php
if ($page_description != "") {
    echo $page_description;
} else {
    echo CONF_META_DESCRIPTION;
}
?>" />
<meta name="keywords" content="<?php
if ($page_keywords != "") {
    echo $page_keywords;
} else {
    echo CONF_META_KEYWORDS;
}
?>" />
      <?php
      if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'deal.php') {
          if (isset($_GET['deal']) && $_GET['deal'] != "") {
              /* Here is the facebook OG for this product  */
              ?>
              <?php
              if (CONF_SSL_ACTIVE == 1) {
                  $ssl = 'https://';
              } else {
                  $ssl = 'http://';
              }
              if (empty($page_description)) {
                  $page_description = CONF_META_DESCRIPTION;
              }
              $blog_description = html_entity_decode($page_description);
              $cleanString = filter_var($blog_description, FILTER_SANITIZE_STRING);
              ?>			   
        <!-- OG Product Facebook Meta [ -->
        <meta property="og:type" content="product" />
        <meta property="og:title" content="<?php echo $page_name; ?>" />
        <meta property="og:site_name" content="<?php echo CONF_SITE_NAME; ?>" />
        <meta property="og:image" content="<?php echo $ssl . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'deal-image.php?id=' . $_GET['deal']; ?>&mode=homeSliderMainImage&time=<?php echo time(); ?>" /> 
        <meta property="og:url" content="<?php echo $ssl . $_SERVER['HTTP_HOST'] . friendlyUrl(CONF_WEBROOT_URL . 'deal.php?deal=' . $_GET['deal'] . '&type=main'); ?>" />
        <meta property="og:description" content="<?php echo $cleanString; ?>" />
        <!-- ]   -->        
        <!--Here is the Twitter Card code for this product  -->
        <?php if (!empty(CONF_TWITTER_USER)) { ?>
            <meta name="twitter:card" content="product">
            <meta name="twitter:site" content="@<?php echo CONF_TWITTER_USER; ?>">
            <meta name="twitter:title" content="<?php echo $page_name; ?>">
            <meta name="twitter:description" content="<?php echo $cleanString; ?>">
            <meta name="twitter:image:src" content="<?php echo $ssl . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'deal-image.php?id=' . $_GET['deal']; ?>&<?php echo time(); ?>" />
        <?php }; ?>
        <!-- End Here is the Twitter Card code for this product  -->        
        <?php
    }
}
if ($_SERVER['SCRIPT_NAME'] == CONF_WEBROOT_URL . 'success.php') {
    ?>
    <meta property="og:url"   content="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>" />
    <meta property="og:type"  content="referal link" />
    <meta property="og:title" content="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?> ">
    <meta property="og:site_name" content="<?php echo 'http://' . $_SERVER['SERVER_NAME']; ?>">
    <meta property="og:description" content="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>"/>
    <!-- <meta property="og:image" content="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id'] ?>" />	-->
<?php } ?>       