<?php
require_once './application-top.php';
require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo $page_name; ?></h3>
                <ul class="grids__half list__inline positioned__right">  
                    <li><a href="javascript:void(0)" class="themebtn link__filter"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h5>
                            <ul class="links__vertical uppercase">
                                <?php echo printNav(0, 8); ?>
                            </ul>
                        </div>
                    </div>    
                </div>    
            </div>
            <div class="col-md-9">
                <div class="container__cms">
                    <?php
                    $rs1 = $db->query("select * from tbl_extra_values");
                    while ($row1 = $db->fetch($rs1)) {
                        define(strtoupper($row1['extra_conf_name']), parseSpecialStrings($row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]));
                    }
                    $cms_page = $db->query("Select  nl_id,nl_cms_page_id from tbl_nav_links where nl_deleted=0 ");
                    $row = $db->fetch_all_assoc($cms_page);
                    if (in_array($_GET['id'], $row)) {
                        if ($_GET['id'] != "" || isset($_GET['id'])) {
                            $srch = new SearchBase('tbl_cms_contents', 'cmsc');
                            $srch->joinTable('tbl_cms_pages', ' JOIN', "page.page_id = cmsc.cmsc_page_id ", 'page');
                            $srch->addCondition('page.page_deleted', '=', 0);
                            $srch->addCondition('cmsc.cmsc_page_id', '=', $_GET['id']);
                            $srch->addCondition('cmsc.cmsc_content_delete', '=', 0);
                            $srch->addOrder('cmsc.cmsc_display_order', 'asc');
                            $rs = $srch->getResultSet();
                            while ($row = $db->fetch($rs)) {
                                $cms_galley_id = $row['cmsc_id'];
                                $cms_content = $row['cmsc_content' . $_SESSION['lang_fld_prefix']];
                                $cmsc_type = $row['cmsc_type'];
                                $srch1 = new SearchBase('tbl_cms_gallery_items', 'cmsgi');
                                $srch1->addCondition('cmsgi.cmsgi_gallery_id', '=', $cms_galley_id);
                                $srch1->addOrder('cmsgi.cmsgi_display_order', 'asc');
                                $rs1 = $srch1->getResultSet();
                                if ($cmsc_type == 0) {
                                    ?>
                                    <?php
                                    if ($cms_content != "") {
                                        echo $cms_content;
                                    }
                                }
                                if ($db->total_records($rs) == 0) {
                                    echo '<h2>' . t_lang('M_TXT_CONTENT_COMING_SOON') . '</h2>';
                                }
                            }
                        }
                    } else {
                        redirectUser(friendlyUrl(CONF_WEBROOT_URL . '404.php'));
                        exit;
                    }
                    ?>     	 
                </div>
            </div>
        </div>    
    </div>    
</section>
<!--bodyContainer end here-->
<?php require_once './footer.php'; ?>