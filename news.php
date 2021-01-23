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
                <h3><?php echo t_lang('M_TXT_NEWS_AT'); ?>&nbsp;<?php echo CONF_SITE_NAME; ?></h3>
            </aside>
        </div>
    </div>
</section> 
<section class="tabs__inline tabs__centered tabs-view">
    <div class="fixed_container">
        <?php require_once CONF_VIEW_PATH . 'center-navigation.php' ?>
    </div>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <div class="panel__centered">
                    <!--jobs_section start here-->
                    <?php
                    global $msg;
                    $srch = new SearchBase('tbl_news', 'n');
                    $srch->addCondition('n.news_status', '=', 1);
                    $srch->addCondition('n.news_date', '<=', date('Y-m-d H:i:s'));
                    $srch->addOrder('n.news_date', 'desc');
                    $rs = $srch->getResultSet();
                    if ($srch->recordCount() == 0) {
                        echo'<div class="block__empty">' . t_lang('M_TXT_NO_NEWS_AVAILABLE_AT_THIS_MOMENT') . '</div>';
                    }
                    while ($row = $db->fetch($rs)) {
                        echo '<div class="boxflat">';
                        echo '<div class="boxflat__head box__head-link view__arrow"><h4>' . $row['news_title' . $_SESSION['lang_fld_prefix']] . '</h4></div>';
                        echo '<div class="boxflat__body box__head-body" style="display:none;">';
                        echo '<div class="boxflat__box">
                                        <h5>' . t_lang('M_TXT_DATE') . ' : <a href="' . friendlyUrl(CONF_WEBROOT_URL . 'news-detail.php?id=' . $row['news_id']) . '">' . displayDate($row['news_date']) . '</a></h5>
                                        <ul class="list__uppertxt">';
                        echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'news-detail.php?id=' . $row['news_id']) . '">' . $row['news_sub_title' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                        echo '</ul></div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                    <!--jobs_section end here-->
                </div>
            </aside>
        </div>    
    </div>    
</section>       
<!--bodyContainer end here-->
<script>
    $('.box__head-link').click(function () {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).siblings('.box__head-body').slideUp();
            return false;
        }
        $('.box__head-link').removeClass('active');
        $(this).addClass("active");
        $('.box__head-body').slideUp();
        $(this).siblings('.box__head-body').slideDown();
        return;
    });
    $(document).ready(function () {
        $('.boxflat:eq(0)').find('.box__head-link:eq(0)').trigger('click');
    });
</script>   		
<?php require_once './footer.php'; ?>
