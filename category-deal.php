<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';

function fetchCatname($catId)
{
    global $db;
    $srch = new SearchBase('tbl_deal_categories');
    $srch->addCondition('cat_id', '=', $catId);
    $srch->addMultipleFields(array('cat_id', 'cat_name' . $_SESSION['lang_fld_prefix']));
    $rs = $srch->getResultSet();
    if (!$row = $db->fetch($rs))
        return false;
    return $row;
}

$get = getQueryStringData();
$category['type'] = $get['type'];
require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'categories.php'); ?>"><?php echo t_lang('M_TXT_CATEGORIES'); ?></a></li>
                    <?php if ($parentcat = fetchCatname($cat_parent_id)) { ?>
                        <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'category-deal.php?cat=' . $parentcat['cat_id'] . '&type=' . $category['type']); ?>"><?php echo $parentcat['cat_name' . $_SESSION['lang_fld_prefix']]; ?></a></li>
                    <?php } ?>
                    <li><?php
                        $cat = fetchCatname($get['cat']);
                        echo $cat['cat_name' . $_SESSION['lang_fld_prefix']];
                        ?></li>
                </ul>
            </aside>
            <aside class="col-md-5 col-sm-5 side">
                <?php require_once CONF_VIEW_PATH . 'sort-filter-menu.php'; ?>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <?php require_once CONF_VIEW_PATH . 'left-filter-menu.php'; ?>
            <aside class="col-md-9">
                <div class="row__filter right_bar" style="display:none;">
                    <div class="row " >
                        <aside class="col-md-9 col-sm-9">
                            <ul class="tags__filter" id="filter" >
                                <li><?php echo t_lang('M_TXT_SHOW'); ?></li>
                            </ul>
                        </aside>
                        <aside class="col-md-3 col-sm-3 alignright">
                            <ul class="tags__filter">
                                <li class="clear" id="allfilter"><a href="javascript:void(0);" onclick="removeFilter(this)" ><?php echo t_lang('M_TXT_CLEAR_ALL'); ?></a></li>
                            </ul>
                        </aside>
                    </div>
                </div>
                <!--items list start here-->
                <div class="dealsContainer">
                </div>
                <!--items list end here-->
            </aside>
        </div>
    </div>    
</section>
<!--bodyContainer end here-->
<script>
    $(document).ready(function () {
        getalldeals(1);
        $('#topcontrol').fadeOut();
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.tabLink').click(function () {
            $(this).toggleClass("active");
            $('.sectiondowncontainer').slideToggle("600");
            return false;
        });
        $('html').click(function () {
            $('.sectiondowncontainer').slideUp('slow');
            if ($('.tabLink').hasClass('active')) {
                $('.tabLink').removeClass('active');
            }
        });
    });
</script>
<?php require_once './footer.php'; ?>
     
