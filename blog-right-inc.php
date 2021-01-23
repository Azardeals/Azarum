<?php
if ($pagename != 'blog') {
    /** get blog categories * */
    $srch = new SearchBase('tbl_blog_categories', 'c');
    $srch->addCondition('cat_status', '=', true);
    $result = $srch->getResultSet();
    $category_listing = $db->fetch_all($result);
    /*     * ******* */
}
/** get archive index * */
$srch_archive = new SearchBase('tbl_blogs');
$srch_archive->addGroupBy('Month(blog_added_on)');
$srch_archive->addGroupBy('Year(blog_added_on)');
$srch_archive->addMultipleFields(array('Month(blog_added_on) as themonth', 'Year(blog_added_on) as theyear', 'COUNT(blog_id) AS blogs'));
$srch_archive->addOrder('theyear', 'DESC');
$srch_archive->addOrder('themonth', 'DESC');
$srch_archive->addCondition('mysql_func_MONTH(blog_added_on)', '<', 'mysql_func_MONTH(CURRENT_DATE)', 'AND', true);  /* Show Archives */
$srch_archive->addCondition('blog_approved_by_admin', '=', 1);
$srch_archive->addCondition('blog_status', '=', 1);
$rs = $srch_archive->getResultSet();
$archive_listing = $db->fetch_all($rs);
/* * ****** */
?>
<div class="col-md-3 right m__clear">
    <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog.php'); ?>" class="themebtn themebtn--org themebtn--large themebtn--block hide__tab hide__mobile"><?php echo t_lang('M_TXT_ADD_BLOG'); ?></a>
    <div class="search__blog">
        <h5><?php echo t_lang('M_TXT_QUICK_SEARCH'); ?></h5>
        <div class="searchbar siteForm">
            <form method="post" action="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-listing.php'); ?>">
                <input type="text" autocomplete="off" value="<?php echo (!empty($search_val)) ? $search_val : t_lang('M_TXT_SEARCH_HERE'); ?>" name="search_blog" onfocus="if (this.value == '<?php echo t_lang('M_TXT_SEARCH_HERE'); ?>')
                            this.value = '';" onblur="if (this.value == '')
                                        this.value = '<?php echo t_lang('M_TXT_SEARCH_HERE'); ?>';">
                <button><i class="icon ion-android-search"></i></button>
            </form>
        </div>
    </div>
    <div class="filter__overlay"></div>
    <div class="fixed__panel section__filter">
        <div id="fixed__panel">
            <div class="block__bordered">
                <h5><?php echo t_lang('M_TXT_QUICK_LINK'); ?></h5>
                <div class="block">
                    <div class="block__head box__head-link"><?php echo t_lang('M_FRM_CATEGORIES'); ?></div>
                    <div class="block__body box__head-body" >
                        <ul class="links__vertical">
                            <?php
                            if ($pagename == 'blog') {
                                foreach ($category_listing as $key => $val) {
                                    ?>
                                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-listing.php?cat_id=' . $key . '&y=0&m=0'); ?>"><?php echo $val; ?></a></li>
                                    <?php
                                }
                            } else {
                                foreach ($category_listing as $ele) {
                                    ?>
                                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-listing.php?cat_id=' . $ele['cat_id'] . '&y=0&m=0'); ?>"><?php echo $ele['cat_name']; ?></a></li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php if (sizeof($archive_listing) > 0) { ?>
                    <div class="block">
                        <div class="block__head box__head-link"><?php echo t_lang('M_TXT_ARCHIVES'); ?></div>
                        <div class="block__body box__head-body" >
                            <ul class="links__vertical">
                                <?php
                                foreach ($archive_listing as $row) {
                                    ?>
                                    <li>
                                        <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-listing.php?cat_id=0&y=' . $row['theyear'] . '&m=' . $row['themonth']); ?>"><?php echo date('F Y', strtotime($row['theyear'] . '-' . $row['themonth'] . '-01')); ?></a>&nbsp;(<?php echo $row['blogs'] ?>)
                                    </li>        
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>  
    </div>    
</div>
