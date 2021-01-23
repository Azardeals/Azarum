<?php

/**
 * Generates Top Navigation 
 * 
 * @param number $NavId Id of navigation whose nav code is needed
 *  @return  navigation
 */
//require_once 'application-top.php';
function getNavigationResultSet($parent_id, $NavId)
{
    $srch = new SearchBase('tbl_nav_links', 'nl');
    $cnd = $srch->addCondition('nl.nl_parent_id', '=', $parent_id);
    $srch->joinTable('tbl_navigations', 'INNER JOIN', 'nav.nav_id=' . $NavId . ' AND nav.nav_active=1', 'nav');
    $srch->addFld('nav.*');
    $srch->addCondition('nl.nl_deleted', '=', 0);
    $srch->addCondition('nl.nl_nav_id', '=', $NavId);
    $srch->addGroupBy('nl.nl_code');
    $srch->addOrder('nl.nl_display_order', 'asc');
    $srch->joinTable('tbl_nav_links', 'LEFT OUTER JOIN', "nl_tmp.nl_code like CONCAT(nl.nl_code, '%')  AND nl.nl_code != nl_tmp.nl_code and nl_tmp.nl_deleted=0 ", 'nl_tmp');
    $srch->addFld('nl.*');
    $srch->addFld('COUNT(nl_tmp.nl_id) as children'); //echo $srch->getQuery();
    return $srch;
    //$rs=$srch->getResultSet();
    //$rowNum =  $srch->recordCount($rs);
    //return ($rs);
}

function printNav($parent_id, $NavId)
{
    global $db;
    $currentPageLinkCodes = [];
    if (strrchr($_SERVER['SCRIPT_NAME'], '/') == '/cms-page.php' && is_numeric($_GET['id'])) {
        $rs = $db->query("select nl_code from tbl_nav_links where nl_deleted=0 and  nl_cms_page_id=" . $_GET['id']);
    } else {
        $qry = "select nl_code from tbl_nav_links where nl_deleted=0 and  REPLACE(nl_html, '{SITEROOT}', " . $db->quoteVariable(CONF_WEBROOT_URL) . ") like " . $db->quoteVariable("%" . $_SERVER['SCRIPT_NAME'] . "%");
        $rs = $db->query($qry);
    }
    while ($row = $db->fetch($rs))
        $currentPageLinkCodes[] = $row['nl_code'];
    $nav_id = $NavId;
    if (!isset($parent_id)) {
        $parent_id = 0;
    }
    if ($parent_id == 0) {
        //$li = 'class="heading"';
        $li = '';
    }
    if ($NavId == 5) {
        $spanStart = '<span>';
        $spanEnd = '</span>';
    } else {
        $spanStart = '';
        $spanEnd = '';
    }
    $srch = new SearchBase('tbl_nav_links', 'nl');
    $cnd = $srch->addCondition('nl.nl_parent_id', '=', $parent_id);
    $srch->joinTable('tbl_navigations', 'INNER JOIN', 'nav.nav_id=' . $nav_id . ' AND nav.nav_active=1', 'nav');
    $srch->addFld('nav.*');
    $srch->addCondition('nl.nl_deleted', '=', 0);
    $srch->addCondition('nl.nl_nav_id', '=', $nav_id);
    $srch->addGroupBy('nl.nl_code');
    $srch->addOrder('nl.nl_display_order', 'asc');
    $srch->joinTable('tbl_nav_links', 'LEFT OUTER JOIN', "nl_tmp.nl_code like CONCAT(nl.nl_code, '%')  AND nl.nl_code != nl_tmp.nl_code and nl_tmp.nl_deleted=0 ", 'nl_tmp');
    $srch->addFld('nl.*');
    $srch->addFld('COUNT(nl_tmp.nl_id) as children');
    $rs = getNavigationResultSet($parent_id, $NavId)->getResultSet();
    $rs = $srch->getResultSet();
    //echo $srch->getQuery();
    $rowNum = $srch->recordCount($rs);
    if ($parent_id == 0) {
        $rowParent = $srch->recordCount($rs);
    }
    $count = 0;
    $parentCount = 0;
    while ($row = $db->fetch($rs)) {
        $count++;
        $ismultilevel = $row['nav_ismultilevel'];
        $cms_page_id = $row['nl_cms_page_id'];
        $child = $row['children'];
        ####### Inactive child whose parent is not active code start here-----
        if ($cms_page_id > 0) {
            $ParentInactive = $db->query("Select * from tbl_cms_pages where page_id = $cms_page_id ");
            $ParentResult = $db->fetch($ParentInactive);
            $active = $ParentResult['page_active'];
            if ($active == 0) {
                $countChild = $child;
                continue;
            }
        }
        ##### Inactive child whose parent is not active code End here----
        $id = $row['nl_id'];
        $linkClass = '';
        if ($NavId == 7) {
            $parentCount++;
        }
        if ($parent_id == 0) {
            $linkClass .= getSelectedLinkClass($row['nl_code'], $currentPageLinkCodes);
        } else {
            $linkClass .= '';
        }
        $out = "";
        $liclass = 'vtabs_link';
        $id = str_replace('{SITEROOT}', '', $row['nl_html']);
        $id = str_replace('.php', '', $id);
        $out = "rel=tabs-" . $id;
        $hover = '';
        $imageThumb = '';
        $CatArray = array('id' => $row['nl_cms_page_id']);
        $url = getPageUrl('cms-page.php', $CatArray);
        $target = $row['nl_target'];
        if ($row['nl_type'] == 1) {
            $customLink = $row['nl_html' . $_SESSION['lang_fld_prefix']];
            echo friendlyUrl(parseSpecialStrings($customLink));
        } else if ($row['nl_type'] == 0) {
            $hover = isset($hover) ? $hover : "";
            $out = isset($out) ? $out : "";
            echo('<li class="' . $liclass . '"> <a  class="' . trim($linkClass) . '" href="' . friendlyUrl($url) . '" target="' . $target . '" title="' . $row['nl_caption' . $_SESSION['lang_fld_prefix']] . '"  ' . $hover . '  ' . $out . '>' . $imageThumb . $spanStart . $row['nl_caption' . $_SESSION['lang_fld_prefix']] . $spanEnd . '</a>');
        } else if ($row['nl_type'] == 2) {
            echo('<li class="' . $liclass . '"><a class="' . trim($linkClass) . '" href="' . friendlyUrl(parseSpecialStrings($row['nl_html'])) . '" title="' . $row['nl_caption' . $_SESSION['lang_fld_prefix']] . '" target="' . $target . '"  ' . $hover . ' ' . $out . '>' . $imageThumb . $spanStart . $row['nl_caption' . $_SESSION['lang_fld_prefix']] . $spanEnd . '</a>');
        }
        if ($row['children'] == 0 && $row['nl_type'] != 1) {
            echo '</li>';
        }
    }
}

/**
 * Generates CMS Page Url
 * 
 * @param numbe */

/**
 * Generates CMS Page Url
 * 
 * @param number $PageId Id of page whose url is detected
 *  @return  CMS PAGE URL
 */
function GetCmsPageUrl($PageId)
{
    global $db;
    $nav_id = $NavId;
    $srch = new SearchBase('tbl_cms_pages', 'pag');
    $srch->addCondition('pag.page_deleted', '=', '0');
    $srch->addCondition('pag.page_active', '=', '1');
    $srch->addCondition('pag.page_id', '=', $PageId);
    $rs = $srch->getResultSet();
    $row = $db->fetch($rs);
    return ($row['page_url']);
}

function parseSpecialStrings($str)
{
    $str = str_replace('{SITEROOT}', CONF_WEBROOT_URL, $str);
    $pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4);
    if ($str == strstr($str, '{BLOCK_FOLLOW_US}')) {
        $str = str_replace('{BLOCK_FOLLOW_US}', '', $str);
    }
    if ($pagename == 'my-account' || $pagename == 'my-deals' || $pagename == 'my-wallet' || $pagename == 'my-profile') {
        $current1 = 'class="current"';
    }
    if ($str == strstr($str, '{MY_ACCOUNT}')) {
        $li = '<li><a  title="My Account" href="' . friendlyUrl(CONF_WEBROOT_URL . 'my-account.php') . '" ' . $current1 . '><span>My Account</span></a></li>';
        $str = str_replace('{MY_ACCOUNT}', $li, $str);
    }
    if ($str == strstr($str, '{LOGIN}')) {
        if (isUserLogged()) {
            $userName = $_SESSION['logged_user']['user_name'];
            $name = explode(" ", $userName);
            $str = str_replace('{LOGIN}', '<li><a title="Logout" href="' . friendlyUrl(CONF_WEBROOT_URL . 'logout.php') . '"><span>' . $name[0] . ' | Logout</span></a></li>', $str);
        } else {
            if ($pagename == 'login')
                $current = 'class="current"';
            $li = '<li><a  title="Login" href="' . friendlyUrl(CONF_WEBROOT_URL . 'login.php') . '" ' . $current . '><span>Login</span></a></li>';
            $str = str_replace('{LOGIN}', $li, $str);
        }
    }
    return $str;
}

function getPageUrl($script_name, $querystring)
{
    $qry = '';
    if (count($querystring) > 0) {
        $qry .= '?';
        foreach ($querystring as $key => $val)
            $qry .= $key . '=' . $val . '&';
        $qry = rtrim($qry, '&');
    }
    return CONF_WEBROOT_URL . $script_name . $qry;
}

function getSelectedLinkClass($linkCode, $currentPageLinkCodes)
{
    foreach ($currentPageLinkCodes as $val) {
        if (strpos($val, $linkCode) !== false) {
            return 'current'; //selected link class name
        }
    }
    return '';
}

function GetFaqParentListing($category_id)
{
    global $db;
    $srch = new SearchBase('tbl_cms_faq_categories');
    $parent = "";
    $qrystr = "";
    if (isset($category_id)) {
        $GetCode = $db->query("select * from tbl_cms_faq_categories where category_id = " . $category_id);
        $CodeResult = $db->fetch($GetCode);
        $CatCode = $CodeResult['category_code'];
        $parent = $CatCode;
        $qrystr = "?cat=$parent";
    }
    $cnd = $srch->addCondition('category_code', 'LIKE', '' . $parent . '_____');
    $srch->addCondition('category_active', '=', 1);
    $srch->addCondition('category_deleted', '=', 0);
    $srch->addGroupBy('category_code');
    $srch->addOrder('category_display_order', 'asc');
    $rs = $srch->getResultSet();
    //echo $srch->getQuery();
    $count = $srch->recordCount($rs);
    if ($count > 0) {
        //	echo '<div class="border">';
        echo'<h3>Faq Categories</h3>';
        echo '<ul class="listing_release">';
    } else {
        //echo "<h3>Faqs Content Coming Soon.</h3>";
    }
    while ($row = $db->fetch($rs)) {
        $flag = 0;
        $subCatQuery = $db->query("select category_name,category_code from tbl_cms_faq_categories where  category_deleted=0 and 	category_active =1 and category_code like '" . $row['category_code'] . "_____%' group by category_code order by category_code asc , category_display_order asc ");
        if ($db->total_records($subCatQuery) > 0) {
            $flag = $db->total_records($subCatQuery);
        }
        $CatArray = array('cat' => $row['category_id']);
        $url = getPageUrl('faq.php', $CatArray);
        if ($flag == "" && $count == 1) {
            echo '<li >';
            echo '<a href="' . friendlyUrl($url) . '" >' . $row['category_name'] . '</a></li>';
        } else {
            echo '<li >';
            echo '<a href="' . friendlyUrl($url) . '" >' . $row['category_name'] . '</a></li>';
        }
    }
    echo '</ul>';
}

function convertStringToFriendlyUrl($strRecord)
{
    $strRecord = str_replace('.', ' ', $strRecord);
    $strRecord = trim($strRecord);
    $strRecord = strtolower(preg_replace('/ +(?=)/', '-', $strRecord));
    $strRecord = strtolower(preg_replace('/"/', '-', $strRecord));
    $strRecord = preg_replace('/[^A-Za-z0-9_\.-]+/', '', $strRecord);
    $strRecord = preg_replace('/-+/', '-', $strRecord);
    $strdisplay = '';
    $myStr_array = explode("-", $strRecord);
    for ($jVal = 0; $jVal < count($myStr_array); $jVal++) {
        if ($jVal < count($myStr_array) - 1) {
            $strdisplay = $strdisplay . $myStr_array[$jVal] . "-";
        } else {
            if (($jVal == count($myStr_array) - 1) && (!is_numeric($myStr_array[$jVal]) == false))
                $strdisplay = substr($strdisplay, 0, strlen($strdisplay) - 1);
            $strdisplay = $strdisplay . $myStr_array[$jVal];
        }
    }
    return $strdisplay;
}

if (CONF_FRIENDLY_URL == 1) {

    function friendlyUrl($str, $set_city_name = '')
    {
        global $db;
        if (strpos($str, 'affiliate-forgot-password.php') == true) {
            return CONF_WEBROOT_URL . "affiliate-forgot-password/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'affiliate-refer-friends.php') == true) {
            return CONF_WEBROOT_URL . "affiliate-refer-friends/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'list-categories.php') == true) {
            return CONF_WEBROOT_URL . "list-categories/" . UrlRewriteFormat($str);
        }
        if (strpos($str, '404.php') == true) {
            return CONF_WEBROOT_URL . "404" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'affiliate-list.php') == true) {
            return CONF_WEBROOT_URL . "affiliate-list/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'affiliate-report.php') == true) {
            return CONF_WEBROOT_URL . "affiliate-report/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'affiliate-login.php') == true) {
            return CONF_WEBROOT_URL . "affiliate-login/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'affiliate-account.php') == true) {
            return CONF_WEBROOT_URL . "affiliate-account/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'affiliate-history.php') == true) {
            return CONF_WEBROOT_URL . "affiliate-history/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'add-my-card.php') == true) {
            return CONF_WEBROOT_URL . "add-my-card/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'my-account-edit.php') == true) {
            return CONF_WEBROOT_URL . "my-account-edit/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'my-account.php') == true) {
            return CONF_WEBROOT_URL . "my-account/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'pay-via-paypal.php') == true) {
            return CONF_WEBROOT_URL . "pay-via-paypal/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'my-deals.php') == true) {
            return CONF_WEBROOT_URL . "my-deals/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'my-wallet.php') == true) {
            return CONF_WEBROOT_URL . "my-wallet/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'my-subscriptions.php') == true) {
            return CONF_WEBROOT_URL . "my-subscriptions/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'refer-friends.php') == true) {
            return CONF_WEBROOT_URL . "refer-friends/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'social_refer_friends.php') == true) {
            return CONF_WEBROOT_URL . "social_refer_friends/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'index.php') == true) {
            return CONF_WEBROOT_URL . UrlRewriteFormat($str);
        }
        if (strpos($str, "login.php?type=") == true) {
            $str1 = explode('?type=', $str);
            $name = $str1[1];
            return CONF_WEBROOT_URL . "login/register";
        }
        if (strpos($str, 'login.php') == true) {
            return CONF_WEBROOT_URL . "login/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'logout.php') == true) {
            return CONF_WEBROOT_URL . "logout/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'how-it-works.php') !== false) {
            return CONF_WEBROOT_URL . "how-it-works/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'contact-us.php') == true) {
            return CONF_WEBROOT_URL . "contact-us/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'suggest-a-business.php') == true) {
            return CONF_WEBROOT_URL . "suggest-a-business/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'terms.php') == true) {
            return CONF_WEBROOT_URL . "terms/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'privacy.php') == true) {
            return CONF_WEBROOT_URL . "privacy/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'forgot-password.php') == true) {
            return CONF_WEBROOT_URL . "forgot-password/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'all-deals.php?s=') == true) {
            return CONF_WEBROOT_URL . "all-deals/" . UrlRewriteFormat($str);
        }
        //if(strpos($str, 'faq.php')==true){
        //		return CONF_WEBROOT_URL . "faq/" . UrlRewriteFormat($str);
        //	}
        if (strpos($str, 'faq.php?cat=') == true) {
            $str1 = explode('?cat=', $str);
            $catId = $str1[1];
            $srch = new SearchBase('tbl_cms_faq_categories', 'fc');
            $srch->addCondition('fc.category_deleted', '=', '0');
            $srch->addCondition('category_id', '=', intval($catId));
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $category_name = $row['category_name'];
            $category_id = $row['category_id'];
            //$deal_name = str_replace(' ','-', strtolower($deal_name));
            $category_name = convertStringToFriendlyUrl($category_name);
            //echo CONF_WEBROOT_URL ."faq/".$category_name."-".$category_id;
            return CONF_WEBROOT_URL . "faq/" . $category_name . "-" . $category_id;
        }
        if (strpos($str, 'buy-deal.php') == true) {
            return CONF_WEBROOT_URL . "buy-deal/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'success.php?dp_id=') == true) {
            return CONF_WEBROOT_URL . "success/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'merchant-sign-up.php') == true) {
            return CONF_WEBROOT_URL . "merchant-sign-up/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'my-favorites.php') == true) {
            return CONF_WEBROOT_URL . "my-favorites/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'my-favorites-deals.php') == true) {
            return CONF_WEBROOT_URL . "my-favorites-deals/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'home.php') == true) {
            return CONF_WEBROOT_URL;
        }
        if (strpos($str, 'merchant-favorite.php?company=') == true) {
            $str_arr = explode('?', $str);
            $qry_str = str_replace('&amp;', '&', $str_arr[1]);
            $param_pairs = explode('&', $qry_str);
            $page = 1;
            $company_id = 0;
            foreach ($param_pairs as $pair) {
                $param = explode('=', $pair);
                if ($param[0] == 'page') {
                    if ('xxpagexx' == $param[1]) {
                        $page = $param[1];
                    } elseif (intval($param[1]) > 0) {
                        $page = intval($param[1]);
                    }
                } elseif ($param[0] == 'company' && intval($param[1]) > 0) {
                    $company_id = intval($param[1]);
                }
            }
            $company_name = '';
            if ($company_id > 0) {
                $srch = new SearchBase('tbl_companies', 'c');
                $srch->addCondition('company_id', '=', $company_id);
                $srch->doNotCalculateRecords();
                $srch->doNotLimitRecords();
                $srch->addFld('company_name');
                $rs = $srch->getResultSet();
                if ($row = $db->fetch($rs)) {
                    $company_name = $row['company_name'];
                }
            }
            $company_name = convertStringToFriendlyUrl($company_name);
            return CONF_WEBROOT_URL . "merchant/" . $company_name . '-' . $company_id . '-' . $page;
        }
        if (strpos($str, 'news-detail.php?id=') == true) {
            $str1 = explode('?id=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_news', 'n');
            $srch->addCondition('n.news_id', '=', $id);
            $srch->addCondition('n.news_status', '=', 1);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $news_title = $row['news_title'];
            $news_id = $row['news_id'];
            $news_title = convertStringToFriendlyUrl($news_title);
            return CONF_WEBROOT_URL . "news-detail" . "/" . $news_id . "/" . $news_title;
        }
        if (strpos($str, 'blog-details.php?id=') == true) {
            $str1 = explode('?id=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_blogs', 'b');
            $srch->addCondition('b.blog_id', '=', $id);
            $srch->addCondition('b.blog_status', '=', 1);
            $srch->addCondition('b.blog_approved_by_admin', '=', 1);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $blog_title = $row['blog_title'];
            $blog_id = $row['blog_id'];
            $blog_title = convertStringToFriendlyUrl($blog_title);
            return CONF_WEBROOT_URL . "blog-details" . "/" . $blog_id . "/" . $blog_title;
        }
        if (strpos($str, 'blog-listing.php') == true) {
            return CONF_WEBROOT_URL . "blog-listing/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'blog.php') == true) {
            return CONF_WEBROOT_URL . "blog/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'about.php') == true) {
            return CONF_WEBROOT_URL . "about/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'jobs.php') == true) {
            return CONF_WEBROOT_URL . "jobs/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'job-apply.php?jobs_id=') !== false) {
            return CONF_WEBROOT_URL . "job-apply/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'jobs-detail.php?id=') == true) {
            $str1 = explode('?id=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_jobs', 'j');
            $srch->addCondition('j.jobs_id', '=', $id);
            $srch->addCondition('j.jobs_status', '=', 1);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $jobs_title = $row['jobs_title'];
            $jobs_id = $row['jobs_id'];
            $jobs_title = convertStringToFriendlyUrl($jobs_title);
            return CONF_WEBROOT_URL . "jobs-detail" . "/" . $jobs_id . "/" . $jobs_title;
        }
        if (strpos($str, 'press-detail.php?id=') == true) {
            $str1 = explode('?id=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_press_release', 'pr');
            $srch->addCondition('pr.pr_id', '=', $id);
            $srch->addCondition('pr.pr_status', '=', 1);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $pr_name = $row['pr_title'];
            $pr_id = $row['pr_id'];
            $pr_name = convertStringToFriendlyUrl($pr_name);
            return CONF_WEBROOT_URL . "press-detail" . "/" . $pr_id . "/" . $pr_name;
        }
        if (strpos($str, 'press.php') == true) {
            return CONF_WEBROOT_URL . "press/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'cms-page.php?id=') == true) {
            $str1 = explode('?id=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_cms_pages', 'cp');
            $srch->addCondition('cp.page_id', '=', $id);
            $srch->addCondition('cp.page_active', '=', 1);
            $srch->addCondition('cp.page_deleted', '=', 0);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $page_url = $row['page_url'];
            $urlWithouExt = explode('.php', $page_url);
            $name = $urlWithouExt[0];
            return CONF_WEBROOT_URL . "cms/" . $name . "/" . UrlRewriteFormat($str);
        }
        /* ============ Urls with cityname below this line =========== */
        if (!isset($_SESSION['cityname']) || trim($_SESSION['cityname']) == "") {
            return $str;
        } else {
            $cityname = convertStringToFriendlyUrl($_SESSION['cityname']);
            if (trim($cityname) == '')
                return $str;
        }
        if (strpos($str, 'expired-deal.php') == true) {
            return CONF_WEBROOT_URL . $cityname . "/" . "expired-deal/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'more-cities.php') == true) {
            return CONF_WEBROOT_URL . $cityname . "/" . "more-cities/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'city-deals.php') !== false) {
            return CONF_WEBROOT_URL . $cityname . "/" . "city-deals/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'side-deals.php') == true) {
            return CONF_WEBROOT_URL . $cityname . "/" . "side-deals/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'upcoming-deals.php') == true) {
            return CONF_WEBROOT_URL . $cityname . "/" . "upcoming-deals/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'things-todo.php') == true) {
            return CONF_WEBROOT_URL . "things-todo/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'instant-deal.php') == true) {
            return CONF_WEBROOT_URL . $cityname . "/" . "instant-deal/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'search.php') == true) {
            return CONF_WEBROOT_URL . "search/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'things-detail.php?id=') == true) {
            $str1 = explode('?id=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_things_todo', 'ttd');
            $srch->addCondition('ttd.things_id', '=', $id);
            $srch->addCondition('ttd.things_status', '=', 1);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $things_title = $row['things_title'];
            $things_id = $row['things_id'];
            $things_title = convertStringToFriendlyUrl($things_title);
            return CONF_WEBROOT_URL . "things-detail" . "/" . $things_id . "/" . $things_title;
        }
        if (strpos($str, 'category-deal.php?cat=') == true) {
            $str1 = explode('?cat=', $str);
            $str2 = explode('&type=', $str1[1]);
            $id = $str2[0];
            $srch = new SearchBase('tbl_deal_categories', 'dc');
            $srch->addCondition('dc.cat_id', '=', $id);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $cat_name = $row['cat_name'];
            $cat_id = $row['cat_id'];
            $cat_name = convertStringToFriendlyUrl($cat_name);
            return CONF_WEBROOT_URL . $cityname . "/" . $cat_name . "-cat-" . $cat_id . "-" . $str2[1];
        }
        if (strpos($str, 'products-featured.php?productcat=') == true) {
            $str1 = explode('?productcat=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_deal_categories', 'dc');
            $srch->addCondition('dc.cat_id', '=', $id);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $cat_name = $row['cat_name'];
            $cat_id = $row['cat_id'];
            $cat_name = convertStringToFriendlyUrl($cat_name);
            return CONF_WEBROOT_URL . "products/" . $cat_name . "-productcat-" . $cat_id;
        }
        if (strpos($str, 'search.php?cat=') == true) {
            $str1 = explode('?cat=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_deal_categories', 'dc');
            $srch->addCondition('dc.cat_id', '=', $id);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $cat_name = $row['cat_name'];
            $cat_id = $row['cat_id'];
            $cat_name = convertStringToFriendlyUrl($cat_name);
            return CONF_WEBROOT_URL . $cityname . "/" . "search" . "/" . $cat_id . "/" . $cat_name;
        }
        if (strpos($str, 'deal-detail.php?deal=') == true) {
            $str1 = explode('?deal=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_deals', 'd');
            $srch->addCondition('d.deal_id', '=', $id);
            $srch->addCondition('d.deal_deleted', '=', 0);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $deal_name = $row['deal_name'];
            $deal_id = $row['deal_id'];
            $deal_name = convertStringToFriendlyUrl($deal_name);
            return CONF_WEBROOT_URL . $cityname . "/" . "deal-detail" . "/" . $deal_id . "/" . $deal_name;
        }
        if (strpos($str, 'deal.php?deal=') == true) {
            $str1 = explode('?deal=', $str);
            $id = $str1[1];
            $srch = new SearchBase('tbl_deals', 'd');
            $srch->addCondition('d.deal_id', '=', $id);
            $srch->addCondition('d.deal_deleted', '=', 0);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $deal_name = $row['deal_name'];
            $deal_id = $row['deal_id'];
            //$deal_name = str_replace(' ','-', strtolower($deal_name));
            $deal_name = convertStringToFriendlyUrl($deal_name);
            if ($set_city_name != '' && strlen(trim($set_city_name)) > 1) {
                $cityname = convertStringToFriendlyUrl($set_city_name);
            }
            return CONF_WEBROOT_URL . $cityname . "/" . $deal_name . "-dl-" . $deal_id;
        }
        if (strpos($str, 'deal.php') == true) {
            return CONF_WEBROOT_URL . $cityname . "/" . "deal/" . UrlRewriteFormat($str);
        }
        if (strpos($str, 'deal-rss.php?city=') == true) {
            $str2 = explode('?', $str);
            $str1 = explode('&', $str2[1]);
            $city = explode('city=', $str1[0]);
            $deal = explode('deal=', $str1[1]);
            $srch = new SearchBase('tbl_deals', 'd');
            $srch->addCondition('d.deal_city', '=', $city[1]);
            $srch->addCondition('d.deal_deleted', '=', 0);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $dealname = $row['deal_name'];
            $cityList = $db->query("select * from tbl_cities where city_active=1 and city_deleted=0 and city_id=" . $city[1]);
            $Cityrow = $db->fetch($cityList);
            $cityname = $Cityrow['city_name'];
            $cityname = convertStringToFriendlyUrl($cityname);
            return CONF_WEBROOT_URL . "dealrss/" . $city[1] . "/" . $cityname;
        }
        if (strpos($str, 'faq-detail.php?cat=') == true) {
            $str2 = explode('?', $str);
            $str1 = explode('&', $str2[1]);
            $cat = explode('cat=', $str1[0]);
            $ques = explode('ques=', $str1[1]);
            $srch = new SearchBase('tbl_cms_faq', 'cf');
            $srch->addCondition('cf.faq_category_id', '=', $cat[1]);
            $srch->addCondition('cf.faq_id', '=', $ques[1]);
            $srch->joinTable('tbl_cms_faq_categories', 'INNER JOIN', 'cf.faq_category_id=fc.category_id', 'fc');
            $srch->addCondition('cf.faq_active', '=', 1);
            $srch->addCondition('cf.faq_deleted', '=', 0);
            $rs = $srch->getResultSet();
            $row = $db->fetch($rs);
            $catName = str_replace(" ", "-", $row['category_name']);
            $quesName = str_replace(" ", "-", $row['faq_question_title']);
            $quesName = str_replace("?", "", $quesName);
            $quesName = trim(convertStringToFriendlyUrl($quesName), '-');
            return CONF_WEBROOT_URL . "faq-detail/" . $catName . "/" . $quesName . "-" . $cat[1] . "-" . $ques[1];
        } else {
            $urlWithouExt = explode('.php', $str);
            return $urlWithouExt[0];
        }
    }

}
if (CONF_FRIENDLY_URL == 0) {

    function friendlyUrl($str, $set_city_name = '')
    {
        return $str;
    }

}

function UrlRewriteFormat($str)
{
    $paramEx = explode('?', $str);
    if (count($paramEx) > 1) {
        foreach ($paramEx as $f) {
            $p = explode('&', $f);
        }
        $sid = "";
        $counter = 1;
        for ($i = 0; $i < count($p); $i++) {
            $sid .= substr($p[$i], -(strlen(strstr($p[$i], '=')) - 1));
            if (count($p) == $counter) {
                $counter = 0;
            } else {
                $sid .= "/";
            }
            $counter++;
        }
        return $sid;
    }
}
