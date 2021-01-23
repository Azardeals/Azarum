<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './header.php';
$catId = $_GET['productcat'];

function fetchFeaturedViewHtml($deal_id, $type = "normal", $limit = 2000)
{
    global $db;
    $rs = $db->query("select * from tbl_deals_images where dimg_deal_id=" . $deal_id);
    while ($row = $db->fetch($rs)) {
        $imageSlider .= '<li><img rel="" src="' . CONF_WEBROOT_URL . 'deal-image.php?id=' . $row['dimg_id'] . '&mode=homeSliderImages"  alt="' . $deal_id . '"></li>';
    }
    if ($db->total_records($rs) < 1) {
        $imageSlider = '';
        $galDiv = '';
    } else {
        $galDiv = 'id="gallery"';
    }
    ?>
    <?php
    $srch = new SearchBase('tbl_deals', 'd');
    $srch->addCondition('deal_start_time', '<=', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('deal_end_time', '>', date('Y-m-d H:i:s'), 'AND', true);
    $srch->addCondition('d.deal_status', '=', 1);
    $srch->addCondition('d.deal_complete', '=', 1);
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->addCondition('d.deal_id', '=', $deal_id);
    $srch->addCondition('d.deal_deleted', '=', 0);
    $srch->joinTable('tbl_order_deals', 'LEFT OUTER JOIN', 'd.deal_id=od.od_deal_id', 'od');
    $srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'od.od_order_id=o.order_id', 'o');
    $srch->joinTable('tbl_cities', 'LEFT OUTER JOIN', 'd.deal_city=c.city_id', 'c');
    $srch->addGroupBy('d.deal_id');
    $srch->addFld("SUM(CASE WHEN o.order_payment_status=1 or o.order_date>'" . date('Y-m-d H:i:s', strtotime('-30 MINUTE')) . "' THEN od.od_qty+od.od_gift_qty ELSE 0 END) AS sold");
    $srch->addOrder('sold', 'desc');
    //$srch->setPageSize($limit);
    $srch->addMultipleFields(array('d.*,od.*'));
    //  style="display:none";
    $rs = $srch->getResultSet();
    $row = $db->fetch($rs);
    $dealUrl = CONF_WEBROOT_URL . 'deal.php?deal=' . $deal_id . '&type=main';
    $str .= '<h1 class="dealtitle topTitle">' . substr($row['deal_subtitle' . $_SESSION['lang_fld_prefix']], 0, 100) . '</h1>';
    $str .= '<div class="mainDeal">';
    $str .= '<div class="grid_1">';
    $str .= '<div class="dealwrap"><div class="featture-ribbon"> <span class="ribbon-cnt"><img src="' . CONF_WEBROOT_URL . 'images/star-icon.png"><span class="fet-txt">Featured</span><b></b></span> </div>';
    $str .= '<div class="image-left"> <div class="flexslider">';
    $str .= '<ul class="slides">' . $imageSlider;
    $deal_name = $row['deal_name' . $_SESSION['lang_fld_prefix']];
    $src = CONF_WEBROOT_URL . 'deal-image.php?id=' . $deal_id . '&mode=homeSliderMainImage';
    $str .= '<li> <img rel="" src="' . $src . '" alt="' . $deal_name . '"/></li>';
    $str .= ' </ul>';
    $str .= ' </div></div>';
    // $str.=' <div class="image-left"> <img src="images/maindeal.jpg"> </div>';
    $str .= '</div>';
    $str .= '</div>';
    $str .= '<div class="grid_2">';
    $price = $row['deal_original_price'] - (($row['deal_discount_is_percent'] == 1) ? $row['deal_original_price'] * $row['deal_discount'] / 100 : $row['deal_discount']);
    $str .= '<div class="prce"> <span class="price"> ' . CONF_CURRENCY . '' . number_format(($price > 0) ? $price : 0, 2) . CONF_CURRENCY_RIGHT . '</span> <span class="fst-price">' . t_lang('M_TXT_LIST_PRICE') . ': <del>' . CONF_CURRENCY . '' . number_format($row['deal_original_price'], 2) . CONF_CURRENCY_RIGHT . ' </del> </span> </div>';
    $str .= '<ul class="borderblocks">';
    $str .= '<li>';
    $str .= '<h5>' . (($row['deal_discount_is_percent'] == 1) ? '' : CONF_CURRENCY) . $row['deal_discount'] . (($row['deal_discount_is_percent'] == 1) ? '%' : '') . '<span> ' . t_lang('M_TXT_SAVINGS') . ' </span> </h5>';
    $str .= '</li>';
    $str .= '<li>';
    $str .= '<h5>' . $row['sold'] . ' <span>' . t_lang('M_TXT_PURCHASED') . ' </span> </h5>';
    $str .= '</li>';
    $str .= '<li>';
    $str .= ' <h5>' . ($row['deal_max_coupons'] - $row['sold']) . ' <span>' . t_lang('M_TXT_REMAINING') . '</span> </h5>';
    $str .= '</li>';
    $str .= '</ul>';
    $str .= '<div class="wrapTimer">';
    $str .= '<h4>' . t_lang('M_TXT_TIME_LEFT_TO_BUY') . '</h4>';
    $str .= '<div class="timer">';
    $str .= '<div class="timebox"> <span class="digit spndaysleft" id="spndaysleft">00</span> <span class="caption">' . t_lang('M_TXT_DAYS') . '</span> </div>
                <div class="timebox"> <span class="digit spnhrsleft" id="spnhrsleft">00</span> <span class="caption">' . t_lang('M_TXT_HOURS') . '</span> </div>
                <div class="timebox"> <span class="digit spnmtsleft" id="spnmtsleft">00</span> <span class="caption">' . t_lang('M_TXT_MINUTES') . '</span> </div>
                <div class="timebox"> <span class="digit spnscsleft" id="spnscsleft">00</span> <span class="caption">' . t_lang('M_TXT_SECONDS') . '</span> </div>';
    $str .= '</div>';
    $str .= '</div>';
    /* Code to Show attributes/options of Products starts here */
    $options = '';
    $options = getDealOptions($deal_id);
    if (!empty($options)) {
        $str .= '<div class="filtr-cart attributes siteform deal-info">';
        $str .= '<table width="100%" cellspacing="0" cellpadding="0" border="0" class="filtr-tbl">
									<tbody><tr>';
        $count = 1;
        if (is_array($options) && count($options)) {
            foreach ($options as $key => $option) {
                $str .= '<td>';
                if ($option['option_type'] == 'select') {
                    $str .= '<div id="option-' . $option['deal_option_id'] . '" class="option">';
                    $str .= $option['option_name'] . ':';
                    if ($option['required']) {
                        $str .= '<span class="mandatory">*</span>';
                    }
                    $str .= '<br/>';
                    $change = "getproductAttributeValue(" . $deal_id . " ," . $option['deal_option_id'] . " ," . $key . ", this)";
                    $name = 'option[' . $option['deal_option_id'] . ']';
                    $str .= '<select name="' . $name . '" onchange="' . $change . '" class="level_' . $key . '">';
                    $str .= '<option value="">--Please Select--</option>';
                    if ($count == 1) {
                        foreach ($option['option_value'] as $option_value) {
                            $str .= '<option value="' . $option_value['deal_option_value_id'] . '">' . $option_value['name'];
                            if ($option_value['price']) {
                                $str .= ' (' . $option_value['price_prefix'] . CONF_CURRENCY . $option_value['price'] . CONF_CURRENCY_RIGHT . ')';
                            }
                            $str .= '</option>';
                        }
                    }
                    $str .= '</select>';
                    $str .= '</div>';
                }
                $str .= '</td>';
                $count++;
            }
        }
        $str .= '</tr></tbody></table></div>';
    }
    $str .= '<div class="clearfix"></div>';
    /* Code to Show attributes/options of Products ends here */
    /* -Added for new deals */
    $str .= '<div class="deal-area"> </div>';
    /* Added for new deals */
    ?><script>
            var frm_data;
            frm_data = $('.deal-info input[type=\'text\'], .deal-info input[type=\'hidden\'], .deal-info input[type=\'radio\']:checked, .deal-info input[type=\'checkbox\']:checked, .deal-info select, .deal-info textarea');
    </script>
    <?php
    $str .= '<div class="more-links clearfix"> ';
    $str .= '<span id="likeDeal_' . $deal_id . '"   class=" likeDeal_' . $deal_id . '">';
    $result = IslikeDeal($deal_id);
    if ($result == 1) {
        $str .= ' <a title="Remove From Favourites" class="linkGreen" onclick="likeDeal(' . $deal_id . ' , \'unlike\')"  href="javascript:void(0);"><span class="iconheart"></span><span class="sign"></span></a>';
    } if (empty($result)) {
        $str .= '  <a title="Add To Favourites" class="linkGreen" onclick="likeDeal(' . $deal_id . ' , \'like\')" href="javascript:void(0);"><span class="iconheart"></span><span class="sign"></span> </a>';
    }
    $str .= '   </span>';
    $str .= '<a href="' . friendlyUrl($dealUrl) . '"  class="button" >' . t_lang("M_TXT_VIEW_DETAIL") . '</a>';
    $str .= '<a href="javascript:void(0);"  onclick="addToCart(' . $deal_id . ', false, ' . CONF_FRIENDLY_URL . ', $(\'.deal-info select\'), 0);" class="button blue" >' . t_lang("M_TXT_ADD_TO_CART") . '</a> </div>';
    $str .= '</div>';
    $str .= '</div>';
    $timenow = strtotime(date('Y-m-d H:i:s'));
    ?>
    <script language="javascript">
        var d = new Date();
        var end_time = d.valueOf() +<?php echo((strtotime($row['deal_end_time']) - $timenow) * 1000); ?>;
        function updateSecsLeft() {
            var d = new Date();
            var days;
            var hours;
            var mins;
            var secs;
            var remaining = (end_time - d.valueOf()) / 1000;
            if (remaining < 0) {
                $('#timedisp').html('<li id="spnmtsleft">EXPIRED</li>');
                return;
            }
            days = Math.floor(remaining / (3600 * 24));
            $('.spndaysleft').html(days);
            remaining = remaining % (3600 * 24);
            hours = Math.floor(remaining / 3600);
            $('.spnhrsleft').html(hours);
            remaining = remaining % 3600;
            mins = Math.floor(remaining / 60);
            $('.spnmtsleft').html(mins);
            remaining = remaining % 60;
            secs = Math.floor(remaining);
            $('.spnscsleft').html(secs);
            setTimeout('updateSecsLeft();', 1000);
        }
        updateSecsLeft();
    </script>
    <?php
    return $str;
}
?>
<?php

function fetchsubProductCategory($parent_id)
{
    global $db;
    $srch = new SearchBase('tbl_deal_categories', 'c');
    $srch->joinTable('tbl_deal_to_category', 'LEFT JOIN', 'dtc.dc_cat_id=c.cat_id ', 'dtc');
    $srch->joinTable('tbl_deals', 'INNER JOIN', 'dtc.dc_deal_id=d.deal_id ', 'd');
    $srch->addCondition('c.cat_parent_id', '=', $parent_id);
    $srch->addCondition('d.deal_type', '=', 1);
    $srch->addOrder('c.cat_name', 'asc');
    $groupBy = 'c.cat_id';
    $findFld = array('c.cat_id,c.cat_name' . $_SESSION["lang_fld_prefix"]);
    $srch->addMultipleFields($findFld);
    $srch->addGroupBy($groupBy);
    $rs = $srch->getResultSet();
    $rows = $db->fetch_all_assoc($rs);
    $srch->getQuery();
    $str = '';
    $str .= '<ul  id="category1" >';
    foreach ($rows as $key1 => $val1) {
        $subCat = fetchsubProductCategory($key1);
        if (strlen($subCat) > 23) {
            $str .= '<li class="category"  > <a onClick="addRemoveClass(this);"  href="javascript:void(0);">' . $val1 . '';
            $str .= '<input type="radio" value="' . $key1 . '" name="category" style="display:none;"> </a>';
            $str .= $subCat;
        } else {
            $str .= '<li class="category"  > <a onClick="addRemoveClass(this);" href="javascript:void(0);">' . $val1 . '';
            $str .= '<input type="radio" value="' . $key1 . '" name="category" style="display:none;"> </a>';
        }
        $str .= '</li>';
    }
    $str .= '</ul>';
    if ($rest_count > 0) {
        $str .= '<div class="more-cat-links"><a href="#"><span class="more-products">' . $rest_count . 'More Products</span> <span class="more-add"> + </span></a></div> ';
    }
    return $str;
}
?>
<div class="product-page">
    <div class="left-section">
        <form name="product_search" id="product_search" method='post'>
            <input type='hidden' value='<?php echo $catId ?>' name='parent_cat_id'>
            <input type='hidden' value='9' name='pagesize'>
            <input type='hidden' value='1' name='page'>
            <?php
            $data = fetchsubProductCategory($catId);

            if (strlen($data) >= 30) {
                ?>
                <div class="filter categories-list">
                    <h2 class="dealtitle"><?php echo t_lang('M_TXT_BROWSE_BY_CATEGORIES'); ?>
                        <a class="linetoggle" href="javascript:void(0)"><span></span></a></h2>
                    <div class="filter-inner">
                        <div class="cat-list">
                            <?php echo $data; ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="filter price-filter">
                <h2 class="dealtitle"><?php echo t_lang('M_TXT_FILTER_BY_PRICE'); ?>
                    <a class="linetoggle" href="javascript:void(0)"><span></span></a></h2>
                <div class="filter-inner">
                    <div class="price-wrap">
                        <div class="control-full" id="price">
                        <!--  <div style="width:15%;" class="leftslide"><span class="circle-controls"></span></div>
                          <div style="width:20%;" class="rightslide"><span class="circle-controls"></span></div>-->
                        </div>
                        <span class="price-cnt"> <?php echo t_lang('M_TXT_PRICE'); ?>: <input type="text" id="amount" readonly style="border:0; color:#f6931f; font-weight:bold;"> </span>
                        <a class="btn-theme filter-btn searchPrice" href="javascript:void(0)" onclick="productSearch(this);">Filter</a> </div>
                    <input type ="hidden" name="price[]" id="min" >
                    <input type="hidden" name="price[]" id='max'>
                </div>
            </div>
            <?php
            $attribute = fetchfilterCriteriaofProduct($catId, 'color');
            if (!empty($attribute[1])) {
                ?>
                <div class="filter color-filter">
                    <h2 class="dealtitle"><?php echo t_lang('M_TXT_FILTER_BY_COLOR'); ?>
                        <a class="linetoggle" href="javascript:void(0)"><span></span></a></h2>
                    <div class="filter-inner">
                        <div class="color-wrap clearfix">
                            <ul id='color'>
                                <?php
                                foreach ($attribute[1] as $key => $value) {
                                    $val = explode('_', $value);
                                    echo '<li id="color_' . $key . '" onClick="addRemoveClass(this);"><a href="javascript:void(0);"  ><span style="background:' . $val[0] . ';" class="color"></span><span class="color-name">' . $val[0] . '</span> <span class="color-value">(' . $val[1] . ')</span>';
                                    echo'<input type="checkbox" value="' . $key . '" name="color[]" style="display:none;"></a></li>';
                                }
                                ?>
                            </ul>
                           <!--  <div class="more-cat-links"><a href="#"><span class="more-products">14 More Color</span> <span class="more-add"> + </span></a></div>-->
                        </div>
                    </div>
                </div>
            <?php } if (!empty($attribute[2])) { ?>
                <div class="filter size-filter">
                    <h2 class="dealtitle"><?php echo t_lang('M_TXT_FILTER_BY_SIZE'); ?>
                        <a class="linetoggle" href="javascript:void(0)"><span></span></a></h2>
                    <div class="filter-inner">
                        <div class="size-wrap">
                            <ul id='size'>
                                <?php
                                foreach ($attribute[2] as $key => $value) {
                                    $val = explode('_', $value);
                                    echo '<li id="size_' . $key . '" onClick="addRemoveClass(this);" ><a href="javascript:void(0);" >' . $val[0] . '<input type="checkbox" value="' . $val[0] . '" name="size[]" style="display:none;" ></a> ';
                                    echo'</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo "<div class='bannersGroup'>";
            $rows = fetchBannerDetail(1, 2);
            foreach ($rows as $key => $value) {
                echo '<div class="small-baner">';
                $src = CONF_WEBROOT_URL . 'banner-image-crop.php?banner=' . $value['banner_id'] . '&type=' . $value['banner_type'];
                echo '<a href="' . $value['banner_url'] . '"  target="' . $value['[banner_target'] . '" ><img src="' . $src . '" alt="'
                . $value['banner_name'] . '" ></a> ';
                echo '</div>';
            }
            echo "</div>";
            ?>
        </form>
    </div>
    <div class="rt-section">
        <?php
        $featured = getFeaturedDeal($_GET['productcat'], 1, 1);
        if (!empty($featured['deal_id'])) {
            echo '<div class="featured-deal clearfix">';
            echo fetchFeaturedViewHtml($featured['deal_id']);
            echo '</div>';
        }
        ?>
        <div  class="featured-all-products clearfix ">
            <?php
            if ($_GET['productcat']) {
                $condition['category'] = $_GET['productcat'];
            }
            $arry = productSearch($condition, 1, 9);
            echo $arry['html'];
            ?>
        </div>
    </div>
    <div class="popup product_popup"></div>
</div>
</section>
</div>
<?php
$rs = $db->query('SELECT min(`deal_original_price`) as min , max(`deal_original_price`) as max FROM `tbl_deals` where deal_original_price >0');
$range = $db->fetch($rs);
$min = intval($range['min']);
$max = intval($range['max']);
$avg = ($min + $max) / 2;
?>
<script type="text/javascript">
    function addRemoveClass(obj) {
        var className = $(obj).parent().attr('class');
        if (className == "category") {
            if ($(obj).hasClass("selected")) {
                $(obj).removeClass('selected');
                $(obj).find('input[type=\'radio\']').attr('checked', false);
            } else {
                $("#category1 a").each(function (index) {
                    $(this).removeClass('selected');
                });
                $(obj).addClass('selected');
                $(obj).find('input[type=\'radio\']').attr('checked', true);
            }
        } else {
            if ($(obj).hasClass("selected")) {
                $(obj).removeClass('selected');
                $(obj).find('input[type=\'checkbox\']').attr('checked', false);
            } else {
                $(obj).addClass('selected');
                $(obj).find('input[type=\'checkbox\']').attr('checked', true);
            }
        }
        productSearch(obj);
    }
    function amount(amount) {
        return "<?php echo CONF_CURRENCY; ?>" + amount + "<?php echo CONF_CURRENCY_RIGHT; ?>";
    }

    $(function () {
        $("#price").slider({
            range: true,
            min: <?php echo intval($range['min']); ?>,
            max: <?php echo intval($range['max']); ?>,
            values: [50,<?php echo $avg; ?>],
            slide: function (event, ui) {
                $("#amount").val(amount(ui.values[ 0 ]) + " - " + amount(ui.values[ 1 ]));
                $('#min').val(ui.values[ 0 ]);
                $('#max').val(ui.values[ 1 ]);
            }
        });
        $("#amount").val(amount($("#price").slider("values", 0)) +
                " - " + amount($("#price").slider("values", 1)));
        $('#min').val(amount($("#price").slider("values", 0)));
        $('#max').val(amount($("#price").slider("values", 1)));
    });
    var dealIds = [];
    function productSearch(obj) {
        data = $('#product_search').serialize();
        data += '&mode=productSearch';
        //var myaudiotracks = jQuery('#category li').serialize();
        callAjax(webroot + 'common-ajax.php', data, function (t) {
            $(".paginglink").remove();
            var ans = parseJsonData(t);
            $('.featured-all-products').html(ans.msg['html']);
            dealIds = ans.msg['dealIds'];
            fetchdealids(dealIds);
        })
    }
    function productSearchwithPagination(json_con, pageno, pagesize) {
        callAjax(webroot + 'common-ajax.php', 'mode=productSearchwithPagination&data=' + json_con + '&page=' + pageno + '&pagesize=' + pagesize, function (t) {
            $(".paginglink").remove();
            var ans = parseJsonData(t);
            $('.featured-all-products').append(ans.msg['html']);
            dealIds = dealIds.concat(ans.msg['dealIds']);
            fetchdealids(dealIds);
        });
    }
    function fetchdealids(dealIds) {
        dealIds = dealIds;
    }
    function fetchNext(id) {
        last = id;
        id = "" + id;
        // alert(id);
        var current = dealIds.indexOf(id);
        var next = current + 1;
        dealId = dealIds[next];
        type = 'search';
        limit = 1;
        if (dealId > 0) {
            fetchQuickViewHtmlJS(dealId, type, limit);
        } else {
            $('.searchPagination').trigger('click');
            //  fetchNext(last);
        }
    }
    function fetchPrevious(id) {
        id = "" + id;
        var current = dealIds.indexOf(id);
        var prev = current - 1;
        dealId = dealIds[prev];
        type = 'search';
        limit = 1;
        if (dealId > 0) {
            fetchQuickViewHtmlJS(dealId, type, limit);
        } else {
            $.facebox('No Product has been found');
        }
    }
    $(document).ready(function () {
        $('.searchPrice').trigger('click');
        $('.linetoggle').click(function () {
            $(this).toggleClass("active");
            if ($(window).width() < 1000)
                $(this).parent().next('.filter-inner').slideToggle();
        });
    })
</script>
<?php require_once './footer.php'; ?>
