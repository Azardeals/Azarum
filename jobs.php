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
                <h3><?php echo t_lang('M_TXT_JOBS_AT'); ?></h3>
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
                    $srch = new SearchBase('tbl_jobs', 'j');
                    $srch->addCondition('j.jobs_status', '=', 1);
                    $srch->joinTable('tbl_cities', 'INNER JOIN', 'j.jobs_city_id=c.city_id', 'c');
                    $srch->addOrder('c.city_name' . $_SESSION['lang_fld_prefix']);
                    $srch->addMultipleFields(array('c.city_id', 'c.city_name' . $_SESSION['lang_fld_prefix'] . ''));
                    $srch->addGroupBy('c.city_id');
                    $rs = $srch->getResultSet();
                    if ($srch->recordCount() == 0) {
                        echo'<div class="block__empty">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</div>';
                    }
                    while ($row = $db->fetch($rs)) {
                        echo '<div class="boxflat">';
                        echo '<div class="boxflat__head box__head-link view__arrow"><h4>' . $row['city_name' . $_SESSION['lang_fld_prefix']] . '</h4></div>';
                        $srch = new SearchBase('tbl_jobs', 'j');
                        $srch->addCondition('j.jobs_status', '=', 1);
                        $srch->addCondition('j.jobs_city_id', '=', $row['city_id']);
                        $srch->joinTable('tbl_cities', 'INNER JOIN', 'j.jobs_city_id=c.city_id ', 'c');
                        $srch->joinTable('tbl_job_catagory', 'INNER JOIN', 'j.jobs_category=jc.job_category_id', 'jc');
                        $srch->addOrder('c.city_name');
                        $srch->addGroupBy('jc.job_category_name');
                        $srch->addOrder('jc.job_category_name');
                        $srch->addOrder('j.jobs_title');
                        $rs1 = $srch->getResultSet();
                        echo '<div class="boxflat__body box__head-body" style="display:none;">';
                        while ($row1 = $db->fetch($rs1)) {
                            $srch = new SearchBase('tbl_jobs', 'j');
                            $srch->addCondition('j.jobs_status', '=', 1);
                            $srch->addCondition('j.jobs_city_id', '=', $row['city_id']);
                            $srch->addCondition('j.jobs_category', '=', $row1['jobs_category']);
                            $rs2 = $srch->getResultSet();
                            $totalJob = $db->total_records($rs2);
                            //echo ($totalJob > 1) ? $totalJob." jobs" : $totalJob." job";
                            echo '<div class="boxflat__box">
                                        <h5>' . $row1['job_category_name' . $_SESSION['lang_fld_prefix']] . ' (' . (($totalJob > 1) ? $totalJob . " jobs" : $totalJob . " job") . ')</h5>
                                        <ul class="list__uppertxt">';
                            while ($row2 = $db->fetch($rs2)) {
                                echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'jobs-detail.php?id=' . $row2['jobs_id']) . '">' . $row2['jobs_title' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                            }
                            echo '</ul></div>';
                        }
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
