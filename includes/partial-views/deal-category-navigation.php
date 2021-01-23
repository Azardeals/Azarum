<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
global $db;
$level = 5;
?>
<a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'categories.php'); ?>"><?php echo t_lang('M_TXT_CATEGORIES'); ?></a>
<span class="link__mobilenav"></span>
<div class="subnav">
    <span class="arrow"><span></span></span>
    <div class="subnav__wrapper addspace">
        <div class="fixed_container">
            <div class="row">
                <div class="col-lg-9 col-sm-12">
                    <ul class="sublinks">
                        <?php
                        $rs = fetchCategories('both', 0);
                        $catCount = 0;
                        while ($row = $db->fetch($rs)) {
                            if ($catCount >= 8) {
                                break;
                            }
                            echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $row['cat_id'] . '&type=both') . '" >' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . '</a>';
                            if ($row['cat_id']) {
                                echo "<ul>";
                                if ($res = fetchCategories('both', $row['cat_id'])) {
                                    if (!$res) {
                                        continue;
                                    }
                                    $catCount++;
                                    $count = 0;
                                    while ($row1 = $db->fetch($res)) {
                                        $count++;
                                        if ($count > 5) {
                                            echo '<li class="seemore"><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $row['cat_id'] . '&type=both') . '" >' . t_lang('M_TXT_SEE_MORE') . '</a></li>';
                                            break;
                                        } else {
                                            echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $row1['cat_id'] . '&type=both') . '" >' . $row1['cat_name' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                                        }
                                    }
                                }
                                echo "</ul>";
                            }
                            echo "</li>";
                        }
                        ?>
                    </ul>
                    <?php if ($catCount >= 8) { ?>
                        <a class="themebtn themebtn--small left" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'categories.php'); ?>"><?php
                            echo
                            t_lang('M_TXT_VIEW_ALL_CATEGORIES');
                            ?></a>
                    <?php } ?>
                </div>
                <div class="col-lg-3 col-sm-12 hide__mobile hide__tab hide__ipad">
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
                </div>
            </div>
        </div>
    </div>
</div>    
