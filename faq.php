<?php
$array = explode('-', $_GET['cat']);
$_GET['cat'] = array_pop($array);
require_once './application-top.php';
require_once './includes/navigation-functions.php';

function fetchCategoryData($category_id = 0, $parentId = 0)
{
    $srch = new SearchBase('tbl_cms_faq_categories', 'fc');
    $srch->joinTable('tbl_cms_faq', 'LEFT JOIN', 'faq.faq_category_id=fc.category_id and faq_deleted =0 and faq_active=1', 'faq');
    $srch->addCondition('fc.category_deleted', '=', '0');
    $srch->addCondition('fc.category_active', '=', '1');
    $srch->addCondition('fc.category_parent_id', '=', $parentId);
    $srch->addGroupBy('fc.category_id');
    $srch->addOrder('fc.category_display_order', 'asc');
    $srch->addMultipleFields(array('category_id', 'category_name' . $_SESSION['lang_fld_prefix']));
    $rs_cat = $srch->getResultSet();
    return $rs_cat;
}

function GetFaqs($cat_data)
{
    global $db;
    $str = '';
    $cat = intval($cat_data['category_id']);
    $faq_content_listing = new SearchBase('tbl_cms_faq', 'cmspage');
    $faq_content_listing->addCondition('faq_deleted', '=', 0);
    $faq_content_listing->addCondition('faq_active', '=', 1);
    $faq_content_listing->addCondition('faq_category_id', '=', $cat);
    $faq_content_listing->addOrder('faq_display_order', 'asc');
    $faq_listing = $faq_content_listing->getResultSet();
    $RowCheck = $faq_content_listing->recordCount($faq_listing);
    $count = 0;
    $str .= '<ul class="links__vertical">';
    if ($RowCheck < 1) {
        $str .= '<li><h4>' . t_lang('M_TXT_NO_RECORD_FOUND') . '</h4></li>';
        return $str;
    }
    while ($row = $db->fetch($faq_listing)) {
        $count++;
        $faq_question_title = $row['faq_question_title' . $_SESSION['lang_fld_prefix']];
        $faq_answer_brief = $row['faq_answer_brief' . $_SESSION['lang_fld_prefix']];
        $faq_meta_title = $row['faq_meta_title' . $_SESSION['lang_fld_prefix']];
        $faq_answer_detailed = $row['faq_answer_detailed' . $_SESSION['lang_fld_prefix']];
        $faq_id = $row['faq_id'];
        $CatArray = array('cat' => $cat, 'ques' => $faq_id);
        $url = getPageUrl('faq-detail.php', $CatArray);
        $viewMore = '';
        if ($faq_answer_detailed != "") {
            $viewMore = '<a class="more" href="' . friendlyUrl($url) . '"  >' . t_lang('M_LINK_VIEW_MORE') . '</a>';
        }
        if (isset($faq_question_title) && $faq_question_title != "") {
            //   $str .= '<div class="box__repeated"><h4>' . $faq_question_title . '</h4></div>';
            $str .= '<li> <a href= ' . $url . '>' . nl2br($faq_question_title) . '</a> </li> ';
        }
    }
    $str .= '</ul>';
    return $str;
}

require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_FAQS'); ?></h3>
            </aside>
        </div>
    </div>
</section>
<section class="tabs__inline tabs__centered tabs-view">
    <?php require_once CONF_VIEW_PATH . 'center-navigation.php' ?>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3 hide__tab hide_mobile">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h5>
                            <div class="block">
                                <div class="block__head"><?php echo t_lang('M_TXT_CATEGORIES'); ?></div>
                                <?php require_once './left-panel-links.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <?php
                global $db;
                $rs_cat = fetchCategoryData(0, 0);
                while ($row = $db->fetch($rs_cat)) {
                    echo '<div class="boxflat">';
                    echo '<div class="boxflat__head box__head-link view__arrow " id="' . $row['category_id'] . '">' . $row['category_name' . $_SESSION['lang_fld_prefix']] . '</div>';
                    echo '<div class="boxflat__body box__head-body" style="display:none;">';
                    $rs_cat1 = fetchCategoryData(0, $row['category_id']);
                    if ($db->total_records($rs_cat1) > 0) {
                        while ($record = $db->fetch($rs_cat1)) {
                            echo'<div class="box__repeated"><h4>' . $record['category_name' . $_SESSION['lang_fld_prefix']] . '</h4>';
                            echo $faqs = GetFaqs($record);
                            echo'</div>';
                        }
                    } else {
                        echo'<div class="box__repeated">';
                        echo $faqs = GetFaqs($row);
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</section>
<!--bodyContainer end here-->
<!--faq links-->
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
    $(document).ready(function () {
        var id = '<?php echo isset($_GET['cat']) ? $_GET['cat'] : 0 ?>';
        console.log(id);
        if (id > 0) {
            $('#' + id).trigger('click');
        } else {
            $('.boxflat:eq(0)').find('.box__head-link:eq(0)').trigger('click');
        }
    })
</script>
<?php require_once './footer.php'; ?>
