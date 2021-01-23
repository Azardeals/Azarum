<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './header.php';
$rs = fetchCategories('both', 0);
$deal_cat_arr = $db->fetch_all($rs);
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_CATEGORIES'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_CATEGORIES'); ?></li>
                </ul>
            </aside>           
        </div>
    </div>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="cell" >
                    <?php
                    $deal_sub_cat_arr = [];
                    foreach ($deal_cat_arr as $key => $value) {
                        ?>
                        <div class="cell__item" >
                            <div class="cell__list">
                                <div class="cell__head"><a <?php if ($_GET['cat'] == $value['cat_id']) { ?> class="active"<?php } else { ?> class="" <?php } ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $value['cat_id'] . '&type=both') ?>"><?php echo $value['cat_name' . $_SESSION['lang_fld_prefix']]; ?></a></div> 
                                <div class="cell__body">
                                    <?php
                                    if ($value['cat_id']) {
                                        $str = fetchSubCategories($value['cat_id']);
                                        echo $str;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div> 
        </div>
    </div>     
</section>
<?php

function fetchSubCategories($category)
{
    global $db;
    $res = fetchCategories('both', $category);
    $str = '';
    $str .= '<ul class="list__vertical links">';
    while ($row1 = $db->fetch($res)) {
        $str .= '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $row1['cat_id'] . '&type=both') . '" >' . $row1['cat_name' . $_SESSION['lang_fld_prefix']] . '</a></li>';
        $str .= '</li>';
    }
    $str .= '</ul>';
    return $str;
}
?>
<script src="<?php echo CONF_WEBROOT_URL; ?>js/masonry.pkgd.js"></script> 
<script type="text/javascript">
    $('.cell').masonry({
        itemSelector: '.cell__item',
    });
    /* for select city form */
    $('.add__newcity-link').click(function () {
        $(this).toggleClass("active");
        $('.citysearch__form').slideToggle("600");
    });
</script>
<?php
require_once './footer.php';
?>