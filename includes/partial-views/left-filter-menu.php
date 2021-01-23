<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
global $db;
$companyList = getCompaniesHavingDeals();
$subQuery = ' AND deal_deleted = 0 AND deal_complete = 1 AND deal_status = 1';
$rs = $db->query('SELECT max( ( CASE
        WHEN deal_discount_is_percent = 1 THEN ( `deal_original_price` - ( ( `deal_original_price` * `deal_discount` ) / 100 ) )
        ELSE  ( `deal_original_price` - `deal_discount` )
    END ) ) AS max, min( ( CASE
        WHEN deal_discount_is_percent = 1 THEN ( `deal_original_price` - ( ( `deal_original_price` * `deal_discount` ) / 100 ) )
        ELSE  ( `deal_original_price` - `deal_discount` )
    END ) ) AS min FROM `tbl_deals` where deal_original_price > 0' . $subQuery);
$range = $db->fetch($rs);
$min = intval($range['min']);
$max = intval($range['max']);
$avg = ($min + $max) / 2;
$type = "deal";
if ($pagename == "merchant-favorite") {
    $type = "both";
}
if ($pagename == "products") {
    $type = "both";
} if ($category['type'] == "product") {
    $type = "product";
}
if ($category['type'] == "both") {
    $type = "both";
}
if ($pagename == "category-deal" && (isset($category['type']) && ($category['type'] == "product"))) {
    $pagename = "products";
}
if ($pagename == "category-deal" && (isset($category['type']) && ($category['type'] == "deal"))) {
    $pagename = "all-deals";
}
?>
<aside class="col-md-3 fixed__panel fixed__panel-left">
    <div class="filter__overlay"></div>
    <div class="section__filter section__bordered" id="fixed__panel" >
        <h5 class="hide__mobile hide__tab"><?php echo t_lang('M_TXT_FILTER_BY'); ?></h5>
        <form name="page_search" id="page_search" method='post'>
            <input type="hidden" name="pagename" value="<?php echo $pagename; ?>">
            <input type="hidden" name="order" id="order" value="" >
            <?php
            $categoryId = isset($get['cat']) ? $get['cat'] : 0;
            if (isset($get['cat'])) {
                echo '<input type="radio" value="' . $categoryId . '" checked=true id="parentCategory" name="category" style="display:none;"> ';
            }
            $rs = fetchCategories($type, $categoryId);
            if ($rs->num_rows > 0) {
                ?>
                <div class="block">
                    <div class="block__head toggle__head"><?php echo t_lang('M_TXT_CATEGORIES'); ?></div>
                    <div class="block__body block__scroll toggle__body">
                        <ul class="links__vertical" >
                            <?php
                            if ($rs) {
                                while ($row = $db->fetch($rs)) {
                                    echo'<li class="category"> <a onClick="addRemoveClass(this);" href="javascript:void(0);" class="radio1">' . $row['cat_name' . $_SESSION['lang_fld_prefix']] . '';
                                    echo '<input type="radio" value="' . $row['cat_id'] . '"  name="category" style="display:none;"> </a>';
                                    if ($row['cat_id']) {
                                        echo "<ul>";
                                        if ($res = fetchCategories($type, $row['cat_id'])) {
                                            if (!$res) {
                                                continue;
                                            }
                                            $count = 0;
                                            while ($row1 = $db->fetch($res)) {
                                                echo '<li class="category"> <a onClick="addRemoveClass(this);" href="javascript:void(0);" class="radio1" >' . $row1['cat_name' . $_SESSION['lang_fld_prefix']] . '';
                                                echo '<input type="radio" value="' . $row1['cat_id'] . '" name="category" style="display:none;"> </a>';
                                                echo'</li>';
                                            }
                                        }
                                        echo "</ul>";
                                    }
                                    echo "</li>";
                                }
                            } else {
                                echo t_lang('M_TXT_NO_SUB_CATEGORY_EXISTS');
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>
            <?php ?>
            <?php if ($pagename == "merchant-favorite") { ?>
                <input type="radio" value="<?php echo isset($get['company']) ? $get['company'] : 0 ?>" name="company" checked=true  style="display:none;">
            <?php } else { ?>
                <div class="block">
                    <div class="block__head toggle__head"><?php echo t_lang('M_TXT_VENDORS'); ?></div>
                    <div class="block__body block__scroll toggle__body">
                        <ul class="links__vertical">
                            <?php foreach ($companyList as $company_id => $company) { ?>
                                <li class="company" ><a id="company_<?php echo $company['company_id']; ?>" onClick="addRemoveClass(this);" href="javascript:void(0);" class="radio1"><?php echo $company['company_name' . $_SESSION['lang_fld_prefix']] . ' ' . $company['company_lname' . $_SESSION['lang_fld_prefix']]; ?>
                                        <input type="radio" value="<?php echo $company['company_id']; ?>" name="company" style="display:none;"> </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?> 
            <div class="block">
                <div class="block__head toggle__head"><?php echo t_lang('M_TXT_FILTER_BY_PRICE'); ?></div>
                <div class="block__body toggle__body">
                    <div class="slide__range">
                        <div class="control-full" id="price"></div>
                       <!-- <div style="width:20%;" class="leftslide"><span class="handle"></span></div>
                        <div style="width:30%;" class="rightslide"><span class="handle"></span></div>-->
                    </div>
                    <ul class="slide__fields siteForm ">
                        <li><input type ="text" name="price[]" id="min" ></li>
                        <li>-</li>
                        <li><input type="text" name="price[]" id='max'></li>
                        <li><a class="themebtn themebtn--small themebtn--org searchPrice" href="javascript:void(0)" onclick="addfilter(this);"><?php echo t_lang('M_TXT_GO'); ?></a></li>
                    </ul>
                </div>
            </div>
            <?php
            if ($pagename == 'product' || (isset($category['type']) && ($category['type'] == "product" || $category['type'] == "both"))) {
                $attribute = fetchfilterCriteriaofProduct($categoryId, 'color');
                if (!empty($attribute[10])) {
                    ?>
                    <div class="block">
                        <div class="block__head toggle__head"><?php echo t_lang('M_TXT_FILTER_BY_SIZE'); ?></div>
                        <div class="block__body toggle__body">
                            <ul class="links__vertical">
                                <?php
                                foreach ($attribute[10] as $key => $value) {
                                    $val = explode('_', $value);
                                    ?>
                                    <li  id="size_<?php echo $key; ?>" onClick="addRemoveClass(this);" ><label class="checkbox"><input type="checkbox" value="<?php echo $key; ?>" name="size[]"  ><i class="input-helper"></i><?php echo $val[0]; ?></label></li>
                                <?php }
                                ?>
                            </ul> 
                        </div>
                    </div>
                <?php } ?>
                <?php if (!empty($attribute[1])) { ?>
                    <div class="block">
                        <div class="block__head toggle__head"><?php echo t_lang('M_TXT_FILTER_BY_COLOR'); ?></div>
                        <div class="block__body toggle__body">
                            <ul class="links__vertical" id='color' >
                                <?php
                                foreach ($attribute[1] as $key => $value) {
                                    $val = explode('_', $value);
                                    ?>
                                    <li id="color_<?php echo $key; ?>" onClick="addRemoveClass(this);">
                                        <label class="checkbox">
                                            <input type="checkbox" value="<?php echo $key; ?>" name="color[]"><i class="input-helper"></i>
                                            <span class="option__color" style="background:<?php echo $val[0]; ?>;"></span><?php echo $val[0]; ?>
                                        </label>
                                    </li>
                                <?php } ?>
                            </ul>  
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </form>
    </div>
</aside>
<?php ?>
<script>
    function selectSort(obj) {
        var value = $(obj).val();
        $('#order').val(value);
        pageSearch(obj);
    }
    function addfilter(obj) {
        $('#filterPrice').remove();
        $('#filter').append('<li id="filterPrice"><a href="javascript:void(0);" onclick="removeFilter(this);" >' + amount($('#min').val()) + ' - ' + amount($('#max').val()) + '</a></li>');
        pageSearch(obj);
    }
    function removeFilter(obj)
    {
        id = $(obj).parent().attr('id');
        if (id == "filterPrice" || id == "allfilter") {
            $('#filterPrice').remove();
            $('#min').val('<?php echo $min; ?>');
            $('#max').val('<?php echo ($max + 1); ?>');
        }
        if (id == "filterCategory" || id == "allfilter") {
            $('#filterCategory').remove();
            $(".category .radio1").each(function (index) {
                $(this).removeClass('current');
                $(this).find('input[type=\'radio\']').attr('checked', false);
            });
        }
        if (id == "filterCompany" || id == "allfilter") {
            $('#filterCompany').remove();
            $(".company .radio1").each(function (index) {
                $(this).removeClass('current');
                $(this).find('input[type=\'radio\']').attr('checked', false);
            });
        }
        pageSearch(obj);
    }
    function addRemoveClass(obj) {
        var parent = $(obj).parent().attr('class');
        if (parent == "category") {
            $(".category .radio1").each(function (index) {
                $(this).removeClass('current');
                $(this).find('input[type=\'radio\']').attr('checked', false);
            });
            $('#filterCategory').remove();
            $(obj).addClass('current');
            $(obj).find('input[type=\'radio\']').attr('checked', true);
            $('#filter').append('<li id="filterCategory"><a href="javascript:void(0);" onclick="removeFilter(this);" >' + $(obj).text() + '</a></li>');
        } else if (parent == "company") {
            $(".company .radio1 ").each(function (index) {
                $(this).removeClass('current');
            });
            $('#filterCompany').remove();
            $(obj).addClass('current');
            $(obj).find('input[type=\'radio\']').attr('checked', true);
            $('#filter').append('<li id="filterCompany"><a href="javascript:void(0);" onclick="removeFilter(this);" >' + $(obj).text() + '</a></li>');
        } else {
            if ($(obj).hasClass("current")) {
                $(obj).removeClass('current');
                $(obj).find('input[type=\'checkbox\']').attr('checked', false);
            } else {
                $(obj).addClass('current');
                $(obj).find('input[type=\'checkbox\']').attr('checked', true);
            }
        }
        pageSearch(obj);
    }
    function amount(amount) {
        return "<?php echo CONF_CURRENCY; ?>" + amount + "<?php echo CONF_CURRENCY_RIGHT; ?>";
        //  return amount;
    }
    $(function () {
        $("#price").slider({
            range: true,
            min: <?php echo $min; ?>,
            max: <?php echo ($max + 1); ?>,
            values: [1,<?php echo ($max + 1); ?>],
            slide: function (event, ui) {
                $('#min').val(ui.values[0]);
                $('#max').val(ui.values[1]);
            }
        });
        $('#min').val(($("#price").slider("values", 0)));
        $('#max').val(($("#price").slider("values", 1)));
    });
    var dealIds = [];
    function pageSearch(obj) {
        var length = $('#filter >li').length;
        if (length > 1) {
            $('.right_bar').css('display', 'block');
        } else {
            $('.right_bar').css('display', 'none');
        }
        data = $('#page_search').serialize();
        data += '&mode=pageSearch';
        var pageName = $('input[name^="pagename"]').val();
        var parentId = $(obj).parent().attr('id');
        if ((parentId == "filterCategory" || parentId == "allfilter" || parentId == "filterCompany" || parentId == "filterPrice")) {
            if ((pageName == "category-deal") || (pageName == "products")) {
                var parentCategory = $("#parentCategory").val();
                data += '&category=' + parentCategory;
            }
        }
        callAjax(webroot + 'common-ajax.php', data, function (t) {
            var ans = parseJsonData(t);
            $('.paginglink').remove();
            // $('.dealsContainer').append(ans.msg);
            $('.dealsContainer').html(ans.msg['html']);
            dealIds = dealIds.concat(ans.msg['dealIds']);
        })
    }
    /* for left filters  */
    $('.link__filter').live('click', function () {
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
    $(window).bind('resize orientationchange', function () {
        ww = document.body.clientWidth;
    });
    /* for left collapseable links */
    $(".toggle__body").show();
    $(".toggle__head").toggle(function () {
        $(this).addClass("active");
    }, function () {
        $(this).removeClass("active");
    });
    $(".toggle__head").click(function () {
        $(this).siblings(".toggle__body").slideToggle("slow");
    });
    var ww = document.body.clientWidth;
    if (ww <= 990) {
        $(".toggle__body").hide();
        $(".toggle__body:first").show();
    } else {
        $(".toggle__body").show();
    }
    $(".scroll").click(function (event) {
        event.preventDefault();
        var full_url = this.href;
        var parts = full_url.split("#");
        var trgt = parts[1];
        var target_offset = $("#" + trgt).offset();
        var target_top = target_offset.top - 54;
        $('html, body').animate({scrollTop: target_top}, 800);
    });
</script>