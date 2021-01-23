<?php
require_once './application-top.php';
require_once './header.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
/* end configuration variables */
?>
<!--bodyContainer start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_PRESS'); ?></h3>
                <ul class="grids__half list__inline positioned__right">  
                    <li><a href="javascript:void(0)" class="themebtn link__filter"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section>
<section class="tabs__inline tabs__centered tabs-view">
    <?php require_once CONF_VIEW_PATH . 'center-navigation.php' ?>
</section>
<section class="page__container ">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h5>
                            <div class="block">
                                <!--<div class="block__head active">Archive</div>-->
                                <div class="block__body">
                                    <?php echo EXTRA_PRESS_CONTENT; ?>
                                </div>
                            </div>
                        </div>
                    </div>    
                </div>    
            </div>
            <div class="col-md-9">
                <div class="all__posts">
                    <?php
                    $pressList = $db->query("select * from tbl_press_release where pr_status = 1 order by pr_date desc");
                    while ($row = $db->fetch($pressList)) {
                        $pressDate = date("F jS ,Y", strtotime($row['pr_date']));
                        ?>
                        <div class="post">
                            <div class="post__head">
                                <span class="post__title"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'press-detail.php?id=' . $row['pr_id']); ?>"><?php echo $row['pr_title' . $_SESSION['lang_fld_prefix']]; ?></a></span>
                                <span class="post__date"><?php echo $pressDate; ?></span>
                            </div>
                            <div class="post__body">
                                <div class="post__description">
                                    <p>
                                        <?php echo $row['pr_subtitle' . $_SESSION['lang_fld_prefix']]; ?>	
                                    </p>
                                </div>
                            </div>
                            <div class="post__footer">
                                <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'press-detail.php?id=' . $row['pr_id']); ?>" class="themebtn themebtn--grey themebtn--small"><?php echo t_lang('M_TXT_READ_MORE'); ?></a>
                            </div>
                        </div>
                    <?php } ?>
                </div> 
            </div>
        </div>    
    </div>    
</section>
<!--bodyContainer end here-->
<?php require_once './footer.php'; ?>
