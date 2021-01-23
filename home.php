<?php
require_once './application-top.php';
$arr_common_js[] = 'js/slick.js';
require_once './includes/navigation-functions.php';
$verification_status = (int) isset($_GET['s']) ? $_GET['s'] : '';
$current_top_tab_url = 'deal.php';
require_once './header.php';
$pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4);
if (isset($verification_status) && $verification_status > 0) {
    if ($verification_status == 2) {
        $eMsg = t_lang('M_MSG_VERIFICATION_FAILED');
    }
    if ($verification_status == 1) {
        $eMsg = t_lang('M_MSG_ALREADY_VERIFIED');
    }
    if ($verification_status == 3) {
        $eMsg = t_lang('M_MSG_TOKKEN_EXPIRED');
    }
}
?>
<script type="text/javascript">
    var txtreload = "Please reload the page and try again.";
    var txtoops = "Oops! There was some internal error.";
    var txtemailfail = "Email sending failed. Please try again.";
    var txtemailsent = "Mail Sent Successfully.";
    var image_not_loaded_msg = '<?php echo addslashes(t_lang('M_TXT_IMAGE_CANNOT_LOADED')); ?>';
</script>
<!--main start here-->
<section class="section_listing">
    <div class="fixed_container">
        <div class="row">
            <div class="col-sm-12">
                <?php

                function getPopularCategories()
                {
                    global $db;
                    if ($rs = fetchTopCategories(15)) {
                        $data = $db->fetch_all($rs);
                        $catArray = [];
                        $count = 0;
                        foreach ($data as $key => $value) {
                            if ($value['cat_parent_id'] > 0) {
                                $row = fetchCategorydetail($value['cat_parent_id']);
                                $data = $row;
                                $catArray[$row['cat_id']]['cat_id'] = $row['cat_id'];
                                $catArray[$row['cat_id']]['cat_name' . $_SESSION['lang_fld_prefix']] = $row['cat_name' . $_SESSION['lang_fld_prefix']];
                            } else {
                                $catArray[$value['cat_id']]['cat_id'] = $value['cat_id'];
                                $catArray[$value['cat_id']]['cat_name' . $_SESSION['lang_fld_prefix']] = $value['cat_name' . $_SESSION['lang_fld_prefix']];
                            }
                            $count++;
                        }
                        return ($catArray);
                    }
                }

                function fetchCategorydetail($categoryId)
                {
                    global $db;
                    $srch = new SearchBase('tbl_deal_categories', 'dc');
                    $srch->addCondition('dc.cat_id', '=', $categoryId);
                    $rs = $srch->getResultSet();
                    $row = $db->fetch($rs);
                    if (!$row) {
                        return false;
                    }
                    return $row;
                }
                ?>
                <?php
                $data = getPopularCategories();
                if (count($data) > 0) {
                    ?>
                    <ul class="horizontal_links">
                        <li><?php echo t_lang('M_TXT_POPULAR_CATEGORY'); ?> <i class="icon ion-arrow-right-c"></i></li>  
                        <?php
                        foreach ($data as $key => $value) {
                            echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $value['cat_id'] . '&type=side') . '" >' . $value['cat_name' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                        }
                        ?>
                        <li class="last"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'categories.php'); ?>"><?php echo t_lang('M_TXT_MORE'); ?></a></li>  
                    </ul>
                <?php } ?>
            </div>   
        </div>  
    </div>
</section>
<section id="banner" class="sectionbanners">
    <div class="fixed_container">
        <div class="row">
            <div class="col-lg-9 col-sm-12">
                <div class="bannerslider">
                    <ul class="slides">
                        <?php
                        $rows = fetchBannerDetail(4, 5);
                        foreach ($rows as $key => $value) {
                            $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $value['banner_id'] . '&type=' . $value['banner_type'];
                            ?>
                            <li><a href="<?php echo addhttp($value['banner_url']); ?>" target="<?php echo $value['banner_target']; ?>" class="slide--main"><img src="<?php echo $src; ?>" alt=""></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-12">
                <?php
                $regSceheme = new SearchBase('tbl_registration_credit_schemes', 'regSceheme');
                $regSceheme->addCondition('regscheme_valid_from', '<=', date('Y-m-d'));
                $regSceheme->addCondition('regscheme_valid_till', '>=', date('Y-m-d'));
                $regSceheme->addCondition('regscheme_active', '=', 1);
                $rs_listing = $regSceheme->getResultSet();
                $sch_records = $regSceheme->recordCount();
                $row = $db->fetch($rs_listing);
                //	print_r($row);
                ?>
                <?php if (!isUserLogged() && ($sch_records > 0)) { ?>
                    <div class="registerbanner">
                        <div>
                            <img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIgNTEyOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjY0cHgiIGhlaWdodD0iNjRweCI+CjxnPgoJPGc+CgkJPHBhdGggZD0iTTUxMiwyNTQuODQ3YzAtMTIuMTMtMy41MDEtMjMuODg2LTEwLjEyNi0zMy45OThjLTUuNjQ2LTguNjE4LTcuNzY0LTE5LjE1My01Ljk1OC0yOS42NjQgICAgYzEuOTgtMTEuNTMzLDAuNjUtMjMuMzY1LTMuODQ2LTM0LjIxN2MtNC42NDItMTEuMjA2LTEyLjM3NC0yMC43MjctMjIuMzYxLTI3LjUzM2MtOC40MDEtNS43MjUtMTQuNDMzLTE0LjgzOC0xNi45ODMtMjUuNjYgICAgYy0yLjY0LTExLjIwMS04LjM1My0yMS40MzktMTYuNTI0LTI5LjYwOWMtOC40MzktOC40MzktMTkuMDM4LTE0LjIzNi0zMC42NTQtMTYuNzY0Yy0xMC41MjEtMi4yOTEtMTkuNjYtOC4yODQtMjUuNzMzLTE2Ljg3NSAgICBjLTYuNzUzLTkuNTU3LTE2LjA2MS0xNi45ODUtMjYuOTE2LTIxLjQ4MWMtMTEuMjA5LTQuNjQyLTIzLjQwNy01LjkwNi0zNS4yODUtMy42NTVjLTEwLjEyMiwxLjkxNy0yMC42NjYtMC4xNTgtMjkuNjg2LTUuODQ3ICAgIEMyNzguMDMyLDMuMjk5LDI2Ni41OTMsMCwyNTQuODQ1LDBjLTEyLjEzLDAtMjMuODg3LDMuNTAxLTMzLjk5OCwxMC4xMjVjLTguNjE4LDUuNjQ3LTE5LjE1NCw3Ljc2NC0yOS42NjMsNS45NTggICAgYy0xMS41MzEtMS45NzktMjMuMzY0LTAuNjUtMzQuMjE4LDMuODQ1Yy0xMS4yMDYsNC42NDMtMjAuNzI2LDEyLjM3NS0yNy41MzIsMjIuMzYyYy01LjcyNSw4LjQwMS0xNC44MzksMTQuNDMzLTI1LjY2LDE2Ljk4NCAgICBjLTExLjE5OSwyLjYzOS0yMS40MzgsOC4zNTItMjkuNjA5LDE2LjUyM0M2NS43MjcsODQuMjMzLDU5LjkzLDk0LjgzMyw1Ny40LDEwNi40NTFjLTIuMjksMTAuNTE5LTguMjgzLDE5LjY1OC0xNi44NzYsMjUuNzMyICAgIGMtOS41NTgsNi43NTYtMTYuOTg1LDE2LjA2My0yMS40ODEsMjYuOTE3Yy00LjY0MiwxMS4yMDgtNS45MDUsMjMuNDA4LTMuNjU1LDM1LjI4NGMxLjkxOCwxMC4xMjQtMC4xNTcsMjAuNjY3LTUuODQ2LDI5LjY4NiAgICBDMy4yOTksMjMzLjk2NSwwLDI0NS40MDUsMCwyNTcuMTU0YzAsMTIuMTMsMy41MDEsMjMuODg2LDEwLjEyNiwzMy45OThjNS42NDYsOC42MTgsNy43NjQsMTkuMTUzLDUuOTU4LDI5LjY2NCAgICBjLTEuOTgsMTEuNTMzLTAuNjUsMjMuMzY1LDMuODQ2LDM0LjIxN2M0LjY0MiwxMS4yMDYsMTIuMzc0LDIwLjcyNywyMi4zNjEsMjcuNTMzYzguNDAxLDUuNzI1LDE0LjQzMywxNC44MzgsMTYuOTgzLDI1LjY2ICAgIGMyLjY0LDExLjIwMSw4LjM1MywyMS40MzksMTYuNTI0LDI5LjYwOWM4LjQzOSw4LjQzOSwxOS4wMzgsMTQuMjM2LDMwLjY1NCwxNi43NjRjMTAuNTIyLDIuMjkxLDE5LjY2LDguMjg0LDI1LjczMywxNi44NzUgICAgYzYuNzUzLDkuNTU3LDE2LjA2MSwxNi45ODUsMjYuOTE2LDIxLjQ4MWMxMS4yMDgsNC42NDMsMjMuNDA4LDUuOTA2LDM1LjI4NiwzLjY1NWMxMC4xMjEtMS45MTksMjAuNjY2LDAuMTU4LDI5LjY4NSw1Ljg0NyAgICBjOS44OTcsNi4yNDMsMjEuMzM3LDkuNTQyLDMzLjA4NCw5LjU0MmMxMi4xMywwLDIzLjg4Ny0zLjUwMSwzMy45OTgtMTAuMTI1YzguNjE4LTUuNjQ3LDE5LjE1Mi03Ljc2NSwyOS42NjMtNS45NTggICAgYzExLjUzMywxLjk3OSwyMy4zNjQsMC42NTEsMzQuMjE4LTMuODQ1YzExLjIwNi00LjY0MywyMC43MjYtMTIuMzc1LDI3LjUzMi0yMi4zNjJjNS43MjUtOC40MDEsMTQuODM5LTE0LjQzMywyNS42Ni0xNi45ODQgICAgYzExLjE5OS0yLjYzOSwyMS40MzgtOC4zNTIsMjkuNjA5LTE2LjUyM2M4LjQzOC04LjQzNywxNC4yMzUtMTkuMDM3LDE2Ljc2NC0zMC42NTVjMi4yOS0xMC41MTksOC4yODMtMTkuNjU4LDE2Ljg3Ni0yNS43MzIgICAgYzkuNTU4LTYuNzU2LDE2Ljk4NS0xNi4wNjMsMjEuNDgxLTI2LjkxN2M0LjY0Mi0xMS4yMDgsNS45MDUtMjMuNDA4LDMuNjU1LTM1LjI4NGMtMS45MTgtMTAuMTI0LDAuMTU3LTIwLjY2Nyw1Ljg0Ni0yOS42ODYgICAgQzUwOC43MDEsMjc4LjAzNSw1MTIsMjY2LjU5NSw1MTIsMjU0Ljg0N3ogTTQ4NS4yMDcsMjc3LjA0OGMtOC40NTEsMTMuNC0xMS41MTgsMjkuMTU1LTguNjM2LDQ0LjM2NyAgICBjMS41MSw3Ljk2NCwwLjY1OSwxNi4xNTItMi40NiwyMy42OGMtMy4wMTksNy4yODktOC4wMDEsMTMuNTM2LTE0LjQwOCwxOC4wNjVjLTEyLjc2NSw5LjAyNC0yMS42NTUsMjIuNTM3LTI1LjAzMywzOC4wNSAgICBjLTEuNjk2LDcuNzktNS41ODcsMTQuOTAyLTExLjI1NiwyMC41N2MtNS40ODgsNS40ODgtMTIuMzU3LDkuMzI0LTE5Ljg2NSwxMS4wOTNjLTE1Ljc5NywzLjcyMy0yOS4yMzQsMTIuNzI3LTM3LjgzOCwyNS4zNTEgICAgYy00LjU2Myw2LjY5Ny0xMC45NTQsMTEuODg1LTE4LjQ4MSwxNS4wMDNjLTcuMjksMy4wMTktMTUuMjI3LDMuOTE0LTIyLjk2LDIuNTg2Yy0xNS42MTUtMi42OC0zMS4zNDUsMC41MTUtNDQuMjk1LDkgICAgYy02Ljc3OSw0LjQ0Mi0xNC42NzEsNi43OS0yMi44MTksNi43OWMtNy44OSwwLTE1LjU2OC0yLjIxMi0yMi4yMDMtNi4zOTdjLTEwLjExNi02LjM4LTIxLjU3Mi05LjY5MS0zMy4xMjYtOS42OTEgICAgYy0zLjc1LDAtNy41MTMsMC4zNDktMTEuMjQxLDEuMDU2Yy03Ljk2OSwxLjUwOC0xNi4xNTQsMC42NTgtMjMuNjgzLTIuNDZjLTcuMjktMy4wMi0xMy41MzYtOC4wMDEtMTguMDY0LTE0LjQwOCAgICBjLTkuMDIyLTEyLjc2NC0yMi41MzUtMjEuNjU1LTM4LjA1MS0yNS4wMzNjLTcuNzg4LTEuNjk2LTE0LjkwMS01LjU4OC0yMC41NjktMTEuMjU3Yy01LjQ4OC01LjQ4Ny05LjMyNS0xMi4zNTctMTEuMDk0LTE5Ljg2NCAgICBjLTMuNzIyLTE1Ljc5Ny0xMi43MjQtMjkuMjM0LTI1LjM0OS0zNy44MzhjLTYuNjk3LTQuNTY0LTExLjg4NS0xMC45NTUtMTUuMDA0LTE4LjQ4M2MtMy4wMTktNy4yODgtMy45MTMtMTUuMjI3LTIuNTg3LTIyLjk1OCAgICBjMi42ODEtMTUuNjE0LTAuNTE1LTMxLjM0NS04Ljk5OS00NC4yOTRjLTQuNDQyLTYuNzgtNi43OS0xNC42NzItNi43OS0yMi44MmMwLTcuODksMi4yMTItMTUuNTY3LDYuMzk2LTIyLjIwMiAgICBjOC40NTEtMTMuNCwxMS41MTgtMjkuMTU1LDguNjM2LTQ0LjM2N2MtMS41MDktNy45NjQtMC42NTktMTYuMTUyLDIuNDYtMjMuNjgxYzMuMDE5LTcuMjg5LDguMDAxLTEzLjUzNiwxNC40MDgtMTguMDY1ICAgIGMxMi43NjUtOS4wMjQsMjEuNjU1LTIyLjUzNywyNS4wMzMtMzguMDVjMS42OTYtNy43OSw1LjU4Ny0xNC45MDIsMTEuMjU2LTIwLjU3YzUuNDg4LTUuNDg4LDEyLjM1Ny05LjMyNCwxOS44NjUtMTEuMDkzICAgIGMxNS43OTgtMy43MjMsMjkuMjM0LTEyLjcyNywzNy44MzgtMjUuMzUxYzQuNTYzLTYuNjk3LDEwLjk1NC0xMS44ODUsMTguNDgxLTE1LjAwM2M3LjI5MS0zLjAxOCwxNS4yMjgtMy45MTMsMjIuOTYtMi41ODYgICAgYzE1LjYxMywyLjY4LDMxLjM0NS0wLjUxNSw0NC4yOTUtOWM2Ljc3OS00LjQ0MiwxNC42NzEtNi43OSwyMi44MTktNi43OWM3Ljg5LDAsMTUuNTY4LDIuMjEyLDIyLjIwNSw2LjM5NiAgICBjMTMuNCw4LjQ1MSwyOS4xNTYsMTEuNTE3LDQ0LjM2OCw4LjYzNmM3Ljk2NS0xLjUxLDE2LjE1Mi0wLjY1OCwyMy42ODEsMi40NmM3LjI5LDMuMDIsMTMuNTM2LDguMDAxLDE4LjA2NCwxNC40MDggICAgYzkuMDIyLDEyLjc2NCwyMi41MzUsMjEuNjU1LDM4LjA1MSwyNS4wMzNjNy43ODgsMS42OTYsMTQuOTAxLDUuNTg4LDIwLjU2OSwxMS4yNTdjNS40ODgsNS40ODcsOS4zMjUsMTIuMzU3LDExLjA5NCwxOS44NjQgICAgYzMuNzIyLDE1Ljc5NywxMi43MjUsMjkuMjM0LDI1LjM0OSwzNy44MzhjNi42OTcsNC41NjQsMTEuODg1LDEwLjk1NSwxNS4wMDQsMTguNDgzYzMuMDE5LDcuMjg4LDMuOTEzLDE1LjIyNywyLjU4NywyMi45NTggICAgYy0yLjY4MSwxNS42MTQsMC41MTUsMzEuMzQ1LDguOTk5LDQ0LjI5NGM0LjQ0Miw2Ljc4LDYuNzksMTQuNjcyLDYuNzksMjIuODJDNDkxLjYwNCwyNjIuNzM3LDQ4OS4zOTIsMjcwLjQxNCw0ODUuMjA3LDI3Ny4wNDh6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8L2c+CjwvZz4KPGc+Cgk8Zz4KCQk8Zz4KCQkJPHBhdGggZD0iTTIyNS40NjIsMjI2LjAxMnYtNDcuMzgzYzAtMjcuMTY3LTE4LjAwNi0zNy4yNzUtNDAuNzUtMzcuMjc1Yy0yMy4wNiwwLTQwLjc1LDEwLjEwOC00MC43NSwzNy4yNzV2NDcuMzgzICAgICBjMCwyNy4xNjcsMTcuNjksMzcuMjc2LDQwLjc1LDM3LjI3NUMyMDcuNDU2LDI2My4yODcsMjI1LjQ2MiwyNTMuMTc5LDIyNS40NjIsMjI2LjAxMnogTTIwNi41MDksMjI2LjAxMiAgICAgYzAsMTMuODk5LTguMjE0LDIwLjUzMy0yMS43OTcsMjAuNTMzYy0xMy41ODMsMC0yMS43OTctNi42MzQtMjEuNzk3LTIwLjUzM3YtNDcuMzgzYzAtMTMuODk5LDguMjEzLTIwLjUzMywyMS43OTctMjAuNTMzICAgICBjMTMuNTgyLDAsMjEuNzk3LDYuNjM1LDIxLjc5NywyMC41MzNWMjI2LjAxMnoiIGZpbGw9IiMwMDAwMDAiLz4KCQkJPHBhdGggZD0iTTMyMi4xMjYsMjUxLjI4NWMtMjMuMDYsMC00MC43NSwxMC4xMDgtNDAuNzUsMzcuMjc1djQ3LjM4M2MwLDI3LjE2NywxNy42OSwzNy4yNzUsNDAuNzUsMzcuMjc1ICAgICBjMjIuNzQ0LDAsNDAuNzUtMTAuMTA4LDQwLjc1LTM3LjI3NVYyODguNTZDMzYyLjg3NSwyNjEuMzkzLDM0NC44NywyNTEuMjg1LDMyMi4xMjYsMjUxLjI4NXogTTM0My45MjIsMzM1Ljk0MiAgICAgYzAsMTMuODk5LTguMjE0LDIwLjUzMy0yMS43OTcsMjAuNTMzYy0xMy41ODMsMC0yMS43OTctNi42MzQtMjEuNzk3LTIwLjUzM3YtNDcuMzgzYzAtMTMuODk5LDguMjEzLTIwLjUzMywyMS43OTctMjAuNTMzICAgICBjMTMuNTgyLDAsMjEuNzk3LDYuNjM0LDIxLjc5NywyMC41MzNWMzM1Ljk0MnoiIGZpbGw9IiMwMDAwMDAiLz4KCQkJPHBhdGggZD0iTTMyMy4zODgsMTM5LjQ1OGMwLTYuMDAxLTUuNjg1LTEwLjQyNS0xMC43MzktMTAuNDI0Yy0zLjE1OSwwLTYuMzE3LDEuNTgtNy44OTcsNC43MzlsLTExOC40NiwyMzQuMzkyICAgICBjLTAuNjMyLDEuMjYzLTAuOTQ5LDIuODQyLTAuOTQ5LDQuMTA2YzAsNS4wNTUsNC40MjIsOS40NzcsMTAuMTA4LDkuNDc3YzMuNzkxLDAsNy4yNjYtMS41OCw4Ljg0NS01LjA1NUwzMjIuNDQxLDE0My44OCAgICAgQzMyMy4wNzIsMTQyLjMwMSwzMjMuMzg4LDE0MS4wMzcsMzIzLjM4OCwxMzkuNDU4eiIgZmlsbD0iIzAwMDAwMCIvPgoJCTwvZz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K">
                            <h5 class="registerbanner__title"><?php echo subStringByWords($row['regscheme_name'], 35); ?></h5>
                            <p><?php echo subStringByWords($row['regscheme_description'], 60); ?></p>
                            <h6 class="registerbanner__offer"><span><?php echo CONF_CURRENCY . number_format(($row['regscheme_credit_amount']), 2) . CONF_CURRENCY_RIGHT; ?> <?php echo t_lang('M_TXT_FREE'); ?></span></h6>
                            <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'login.php?type="register"'); ?>" class="themebtn themebtn--small"><?php echo t_lang('M_TXT_REGISTER'); ?></a>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="groupbanners">
                        <ul>
                            <?php
                            $rows = fetchBannerDetail(2, 3);
                            foreach ($rows as $key => $value) {
                                if (!empty($value)) {
                                    echo '<li>';
                                    $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $value['banner_id'] . '&type=' . $value['banner_type'];
                                    $target = isset($value['[banner_target']) ? $value['[banner_target'] : '_blank';
                                    echo '<a href="' . $value['banner_url'] . '"  target="' . $target . '" class="banner__277" ><img src="' . $src . '" alt="image" ></a> ';
                                    echo '</li>';
                                }
                            }
                            ?>
                        </ul>    
                    </div> 
                <?php } ?>
            </div>
            <aside class="col-sm-12 hide__mobile">
                <?php
                $rows = fetchBannerDetail(1, 1);
                foreach ($rows as $key => $value) {
                    if (!empty($value)) {
                        $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $value['banner_id'] . '&type=' . $value['banner_type'];
                        $target = isset($value['[banner_target']) ? $value['[banner_target'] : '_blank';
                        echo '<a href="' . $value['banner_url'] . '"  target="' . $target . '" class="banner__1200" ><img src="' . $src . '" alt="image" ></a> ';
                    }
                }
                ?>
            </aside>
        </div>    
    </div>    
</section> 
<section class="section deals--featured">
    <div class="fixed_container">
        <?php
        echo featuredDeal(0);
        ?>
    </div>
</section> 
<?php if ($rs = fetchTopProducts(4)) { ?>
    <section class="section section--sectionslide">
        <div class="fixed_container">
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="section__content">
                        <h2 class="section__title"><?php echo t_lang('M_TXT_HOME_PAGE_LEFT_SLIDER_HEADING'); ?></h2>
                        <p><?php echo t_lang('M_TXT_HOME_PAGE_LEFT_SLIDER_CONTENT'); ?></p>
                        <a class="themebtn themebtn--large" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'products.php'); ?>"><?php echo t_lang('M_TXT_BROWSE_PRODUCTS'); ?></a>
                    </div>    
                </div>
                <div class="col-md-8 col-sm-12">
                    <ul class="best_deals">
                        <?php
                        while ($rowDealCat = $db->fetch($rs)) {
                            $deal_id = $rowDealCat["deal_id"];
                            $objDeal = new DealInfo($deal_id, false);
                            $deal = $objDeal->getFields();
                            ?>
                            <li>
                                <div class="itemcover">
                                    <?php
                                    $array = array('deal' => $deal, 'searchtype' => 'topSelling');
                                    echo renderDealView('deal.php', $array, false);
                                    ?>
                                </div>    
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>    
    </section>
<?php } ?>		
<?php
if ($rs = fetchTopVendors($_SESSION['city'], 5)) {
    global $db;
    $data = $db->fetch_all($rs);
    $companyrow = (!empty($data)) ? array_shift($data) : '';
    if (!empty($companyrow)) {
        ?>
        <section class="section">
            <div class="fixed_container">
                <div class="row">
                    <h2 class="section__title"><?php echo t_lang('M_TXT_TOP_VENDORS'); ?></h2>
                    <div class="col-sm-4">
                        <?php require_once CONF_VIEW_PATH . 'merchant.php'; ?>
                    </div>
                    <?php
                    $count = 0;
                    foreach ($data as $key => $companyrow) {
                        if ($count % 2 == 0) {
                            ?>
                            <div class="col-sm-4">
                                <div class="boxgroup">
                                <?php } ?>
                                <?php
                                $merchantUrl = CONF_WEBROOT_URL . 'merchant-favorite.php?company=' . $companyrow['company_id'] . '&page=1';
                                if ($companyrow['company_logo' . $_SESSION['lang_fld_prefix']] == "") {
                                    $imgsrc = '<img alt="" class="vendorlogo" src="' . CONF_WEBROOT_URL . 'images/defaultLogo.jpg" >';
                                } else {
                                    $imgsrc = '<img alt=""  class="vendorlogo" src="' . CONF_WEBROOT_URL . 'deal-image.php?company=' . $companyrow['company_id'] . '&mode=companyImages" >';
                                }
                                ?>
                                <div class="box">
                                    <div class="box__head">
                                        <a href="<?php echo friendlyUrl($merchantUrl); ?>"><?php echo $imgsrc; ?></a>
                                        <?php
                                        $user_id = $_SESSION['logged_user']['user_id'];
                                        if (($_SESSION['logged_user']['user_id'] > 0) && ($companyrow['company_id'] > 0)) {
                                            $totalRow = likeMerchant($companyrow['company_id']);
                                            if ($totalRow == 0) {
                                                ?>
                                                <span id="likeMerchant_<?php echo $companyrow['company_id']; ?>" class="heart"><a href="javascript:void(0);" onclick="likeMerchant('<?php echo $companyrow['company_id']; ?>', 'like', 'company-detail')" class="heart__link" title="<?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?>"> </a><span class="heart__txt"><?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?></span></span>
                                            <?php } else { ?>
                                                <span id="likeMerchant_<?php echo $companyrow['company_id']; ?>" class="heart active"> <a href="javascript:void(0);" onclick="likeMerchant('<?php echo $companyrow['company_id']; ?>', 'unlike', 'company-detail')" class="heart__link " title="<?php echo t_lang('M_TXT_REMOVE_FROM_FAVOURITES'); ?>">  </a><span class="heart__txt"><?php echo t_lang('M_TXT_REMOVE_FROM_FAVOURITES'); ?></span></span>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <span id="likeMerchant_<?php echo $companyrow['company_id']; ?>" class="heart"> <a href="javascript:void(0);" onclick="likeMerchant('<?php echo $companyrow['company_id']; ?>', 'like', 'company-detail')" class="heart__link" title="<?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?>"> </a><span class="heart__txt"><?php echo t_lang('M_TXT_ADD_TO_FAVOURITES'); ?></span></span>
                                        <?php } ?>
                                    </div>
                                    <div class="box__body">
                                        <span class="box__title"><a href="<?php echo friendlyUrl($merchantUrl); ?> "><?php echo $companyrow['company_name' . $_SESSION['lang_fld_prefix']]; ?></a></span>
                                        <?php if (CONF_REVIEW_RATING_MERCHANT == 1) { ?>
                                            <div class="ratings">
                                                <?php
                                                echo "<ul>";
                                                $reviewsRow = fetchCompanyRating($companyrow['company_id']);
                                                for ($i = 0; $i < $reviewsRow['rating']; $i++) {
                                                    echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-full.png" alt=""></li>';
                                                }
                                                for ($j = $reviewsRow['rating']; $j < 5; $j++) {
                                                    echo'<li><img src="' . CONF_WEBROOT_URL . 'images/rating-zero.png" alt=""></li>';
                                                }
                                                echo "</ul>";
                                                ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php if ($count % 2 != 0) { ?>
                                </div>    
                            </div>
                            <?php
                        }
                        $count++;
                    }
                    ?>
                </div>
            </div>    
        </section>
        <?php
    }
}
?>
<!--body end here-->
<?php
/* code start for pop up city selector */
if (CONF_SUBSCRIPTION_STEP == 1) {
    if (!isset($_COOKIE['city_subscriber'])) {
        $frm = getMBSFormByIdentifier('frmCitySelector');
        $frm->setAction(CONF_WEBROOT_URL . 'pre_subscription.php');
        $fld = $frm->getField('loginlink');
        $frm->removeField($fld);
        $fld = $frm->getField('city');
        $cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0  order by city_name" . $_SESSION['lang_fld_prefix']);
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
        <div class="popup">
            <div class="popup__content">
                <div class="sectionfull__centered">
                    <div class="sectiontable">
                        <!--  <a class="link__close" href="javascript:void(0);"></a> -->
                        <aside class="sectiontable__leftcell">
                            <h2><?php echo t_lang('M_TXT_CHOOSE_YOUR_CITY'); ?></h2>
                            <p><?php echo t_lang('M_TXT_GET_OFF_NEAR_YOU'); ?></p>
                            <div class="formwrap">
                                <?php $msg->display(); ?>
                                <?php echo $frm->getFormTag(); ?>
                                <table class="formwrap__table">
                                    <tbody><tr>
                                            <td><?php echo $frm->getFieldHTML('city'); ?> </td>
                                        </tr>
                                        <tr>
                                            <td><?php
                                                $fld = $frm->addEmailField('sub_email', 'sub_email', '');
                                                $fld->setRequiredStarWith('None');
                                                $fld->requirements()->setRequired();
                                                $name = t_lang('M_FRM_ENTER_YOUR_EMAIL_ADDRESS');
                                                $blur = "if (value == '') { value ='{$name}'}";
                                                $focus = "if (value == '{$name}') {value = ''}";
                                                $fld->extra = "onblur=\"$blur\" onfocus=\"$focus\" ";
                                                $fld->value = $name;
                                                echo $frm->getFieldHTML('sub_email');
                                                ?> </td>
                                        </tr>
                                        <tr>
                                            <td><?php
                                                $frm->addHiddenField('', 'subs_tick', '1', 'subs_tick');
                                                echo $frm->getFieldHTML('subs_tick');
                                                echo $frm->getFieldHTML('btn_submit');
                                                ?></td>
                                        </tr>
                                    </tbody></table>
                                </form><?php echo $frm->getExternalJS(); ?>
                            </div>
                            <span class="gap"></span>
                            <p class="nomargin__bottom"><a href="<?php echo CONF_WEBROOT_URL; ?>" onclick="return selectSessionCityRedirect();"><?php echo t_lang('M_TXT_ALREADY_SUBSCRIBER_SKIP'); ?></a> | <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'how-it-works.php'); ?>" ><?php echo t_lang('M_TXT_HOW_IT_WORKS'); ?></a></p>
                        </aside>
                        <?php
                        $rows = fetchBannerDetail(5, 1);
                        $src = "";
                        if (!empty($rows[0])) {
                            $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $rows[0]['banner_id'] . '&type=' . $rows[0]['banner_type'];
                        }
                        ?>
                        <aside style="background-image:url(<?php echo $src; ?>); background-repeat:no-repeat;" class="sectiontable__rightcell"></aside>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
<!-- home page sliders -->  
<script type="text/javascript">
    /* for Home page banner slides */
    $('.slides').slick({
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1, adaptiveHeight: true,
        dots: true,
        autoplay: true, autoplaySpeed: 5000,
        pauseOnHover: false,
        arrows: true,
        prevArrow: '<a data-role="none" class="slick-prev" aria-label="previous"></a>',
        nextArrow: '<a data-role="none" class="slick-next" aria-label="next"></a>',
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    dots: false,
                }
            }
        ]
    });
    $('.best_deals').slick({
        infinite: true,
        slidesToShow: 2, slidesToScroll: 1,
        adaptiveHeight: true,
        dots: false,
        autoplay: true, autoplaySpeed: 5000,
        pauseOnHover: true,
        arrows: true,
        centerMode: true,
        centerPadding: '50px',
        prevArrow: '<a data-role="none" class="slick-prev" aria-label="previous"></a>',
        nextArrow: '<a data-role="none" class="slick-next" aria-label="next"></a>',
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    arrows: true,
                    slidesToShow: 2,
                    autoplay: true,
                    infinite: false,
                    centerMode: false,
                }
            }
            ,
            {
                breakpoint: 400,
                settings: {
                    arrows: true,
                    slidesToShow: 1,
                    autoplay: true,
                    infinite: false,
                    centerPadding: '20px 0',
                }
            }
        ]
    });
    $(document).ready(function () {
        $(".first").trigger('click');
    });
    var dealIds = [];
    function getFeaturedDeals(catId) {
        data = 'category=' + catId;
        data += '&mode=pageSearch&pagesize=4&pagename=home';
        callAjax(webroot + 'common-ajax.php', data, function (t) {
            var ans = parseJsonData(t);
            $('.paginglink').remove();
            dealIds = [];
            $('.dealsContainer').html(ans.msg['html']);
            dealIds = dealIds.concat(ans.msg['dealIds']);
            $('.paginglink').remove();
        });
    }
</script>    				
<?php require_once './footer.php'; ?>
                