<?php
require_once './application-top.php';
require_once './header.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
/* end configuration variables */
$srch = new SearchBase('tbl_jobs', 'j');
$srch->addCondition('j.jobs_status', '=', 1);
$srch->addCondition('j.jobs_id', '=', $_GET['id']);
$srch->joinTable('tbl_cities', 'INNER JOIN', 'j.jobs_city_id=c.city_id ', 'c');
$srch->joinTable('tbl_states', 'INNER JOIN', 'c.city_state = s.state_id ', 's');
$srch->joinTable('tbl_countries', 'INNER JOIN', 's.state_country=co.country_id', 'co');
$srch->joinTable('tbl_job_catagory', 'INNER JOIN', 'j.jobs_category=jc.job_category_id', 'jc');
$srch->addOrder('c.city_name');
$srch->addGroupBy('jc.job_category_name');
$srch->addOrder('jc.job_category_name');
$srch->addOrder('j.jobs_title');
$rs = $srch->getResultSet();
$row = $db->fetch($rs);
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_JOBS_AT'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'jobs.php'); ?>"><?php echo t_lang('M_TXT_JOBS'); ?></a></li>
                    <li><?php echo $row['job_category_name']; ?> </li>
                </ul>
                <ul class="grids__half list__inline positioned__right">  
                    <li><a href="javascript:void(0)" class="themebtn  link__filter"><?php echo t_lang('M_TXT_SIMILAR_JOBS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container ">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3 right m__clear">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_SIMILAR_JOBS'); ?></h5>
                            <?php
                            $srch = new SearchBase('tbl_jobs', 'j');
                            $srch->addCondition('j.jobs_status', '=', 1);
                            $srch->addCondition('j.jobs_id', '!=', $_GET['id']);
                            $srch->addCondition('j.jobs_category', '=', $row['jobs_category']);
                            $srch->joinTable('tbl_cities', 'INNER JOIN', 'j.jobs_city_id=c.city_id', 'c');
                            $srch->addOrder('c.city_name' . $_SESSION['lang_fld_prefix']);
                            $srch->addMultipleFields(array('c.city_id', 'c.city_name' . $_SESSION['lang_fld_prefix'] . ''));
                            $srch->addGroupBy('c.city_id');
                            $rs = $srch->getResultSet();
                            while ($row2 = $db->fetch($rs)) {
                                echo '<div class="block">';
                                echo ' <div class="block__head box__head-link active">' . $row2['city_name' . $_SESSION['lang_fld_prefix']] . '</div>';
                                $srch = new SearchBase('tbl_jobs', 'j');
                                $srch->addCondition('j.jobs_status', '=', 1);
                                $srch->addCondition('j.jobs_city_id', '=', $row2['city_id']);
                                $srch->addCondition('j.jobs_category', '=', $row['jobs_category']);
                                $srch->joinTable('tbl_cities', 'INNER JOIN', 'j.jobs_city_id=c.city_id ', 'c');
                                $srch->joinTable('tbl_job_catagory', 'INNER JOIN', 'j.jobs_category=jc.job_category_id', 'jc');
                                $srch->addOrder('c.city_name');
                                $srch->addGroupBy('jc.job_category_name');
                                $srch->addOrder('jc.job_category_name');
                                $srch->addOrder('j.jobs_title');
                                $rs1 = $srch->getResultSet();
                                while ($row1 = $db->fetch($rs1)) {
                                    $srch = new SearchBase('tbl_jobs', 'j');
                                    $srch->addCondition('j.jobs_status', '=', 1);
                                    $srch->addCondition('j.jobs_city_id', '=', $row['city_id']);
                                    $srch->addCondition('j.jobs_category', '=', $row1['jobs_category']);
                                    $rs2 = $srch->getResultSet();
                                    $totalJob = $db->total_records($rs2);
                                    //echo ($totalJob > 1) ? $totalJob." jobs" : $totalJob." job";
                                    echo '<div class="block__body box__head-body">
                                        <strong>' . $row1['job_category_name' . $_SESSION['lang_fld_prefix']] . ' (' . (($totalJob > 1) ? $totalJob . " jobs" : $totalJob . " job") . ')</strong>
                                        <ul class="links__vertical">';
                                    while ($row2 = $db->fetch($rs2)) {
                                        echo '<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'jobs-detail.php?id=' . $row2['jobs_id']) . '">' . $row2['jobs_title' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                                    }
                                    echo '</ul></div>';
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                            ?>
                        </div>
                    </div>
                </div>    
                <div class="col-md-9">
                    <div class="container__cms">
                        <h3><?php echo $row['jobs_title' . $_SESSION['lang_fld_prefix']]; ?></h3>
                        <h6 class="txt__uppercase"><?php echo $row['job_category_name']; ?> | <?php echo $row['city_name' . $_SESSION['lang_fld_prefix']]; ?> , <?php echo $row['country_name']; ?></h6><span class="gap"></span>
                        <p><?php echo $row['jobs_description' . $_SESSION['lang_fld_prefix']]; ?></p>
                        <ul class="btns__inline">
                            <li class="first"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'job-apply.php?jobs_id=' . $row['jobs_id']); ?>" class="themebtn themebtn--large"><?php echo t_lang('M_TXT_APPLY_NOW'); ?></a></li>
                            <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'jobs.php'); ?>" class="themebtn themebtn--large themebtn--block"><?php echo t_lang('M_TXT_BACK_TO_JOBS'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>    
        </div>    
</section>   	
<!--bodyContainer end here-->
<script type="text/javascript">
    /* for sticky right panel */
    if ($(window).width() > 1050) {
        function sticky_relocate() {
            var window_top = $(window).scrollTop();
            var div_top = $('.fixed__panel').offset().top - 110;
            var sticky_left = $('#fixed__panel');
            if ((window_top + sticky_left.height()) >= ($('#footer').offset().top - 100)) {
                var to_reduce = ((window_top + sticky_left.height()) - ($('#footer').offset().top - 100));
                var set_stick_top = -100 - to_reduce;
                sticky_left.css('top', set_stick_top + 'px');
            } else {
                sticky_left.css('top', '110px');
                if (window_top > div_top) {
                    $('#fixed__panel').addClass('stick');
                } else {
                    $('#fixed__panel').removeClass('stick');
                }
            }
        }
        $(function () {
            $(window).scroll(sticky_relocate);
            sticky_relocate();
        });
    }
    /* for right filters  */
    $('.link__filter').click(function () {
        $(this).toggleClass("active");
        var el = $("body");
        if (el.hasClass('filter__show'))
            el.removeClass("filter__show");
        else
            el.addClass('filter__show');
        return false;
    });
    $('body').click(function () {
        if ($('body').hasClass('filter__show')) {
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    });
    $('.filter__overlay').click(function () {
        if ($('body').hasClass('filter__show')) {
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    });
    $('.section__filter').click(function (e) {
        e.stopPropagation();
    });
    /* for right categories  */
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
    $(window).load(function () {
        $('.block:eq(0)').find('.box__head-link:eq(0)').trigger('click');
    });
</script>	        
<?php require_once './footer.php'; ?>
