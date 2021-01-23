<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/user-functions.php';
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
require_once './header.php';
$post = getPostedData();
$mainTableName = 'tbl_newsletter_subscription';
$primaryKey = 'subs_id';
$colPrefix = 'subs_';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_new_subscription'])) {
    $check_user = $db->query("select * from  tbl_newsletter_subscription where subs_email='" . $post['logged_email'] . "'");
    $result = $db->fetch($check_user);
    if ($db->total_records($check_user) == 0) {
        $data = [];
        $data['sub_email'] = $post['logged_email'];
        if (is_numeric($post['city_id'][0])) {
            selectCity(intval($post['city'][0]));
        }
    }
    if (!empty($post['city_id'])) {
        foreach ($post['city_id'] as $key => $val) {
            if ($val != "") {
                if (!addSubscribedCity($post['logged_email'], $val, $post)) {
                    redirectUser();
                }
            }
        }
        if (!empty($post['city_id'][0])) {
            Message::addMessage(t_lang('M_TXT_CITY_SUCCESSFULLY_ADDED.'));
        }
    }
    if (!intval($post['city_id'][0])) {
        Message::addErrorMessage(t_lang('M_TXT_SELECT_CITY_VALUE_FROM_SUGESSTION_SEARCH.'));
    }
    redirectUser();
}
if (((int) $_GET['remove-link'] > 0) || $_GET['remove-link'] == "0") {
    $city = intval($_GET['remove-link']);
    if (removeSubscribedCity($city)) {
        $msg->addMsg(t_lang('M_TXT_CITY_SUCCESSFULLY_REMOVED.'));
    } else {
        $msg->addMsg(t_lang('M_TXT_CITY_NOT_FOUND.'));
    }
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'my-subscriptions.php'));
}
?> 
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_MY_SUBSCRIPTIONS'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_MY_SUBSCRIPTIONS'); ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php include './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_MY_SUBSCRIPTIONS'); ?></h2>
                <div class="container__bordered">
                    <?php
                    $srch = fetchParentCategories(0);
                    $srch->doNotLimitRecords();
                    $rs = $srch->getResultSet();
                    $arr_cats = $db->fetch_all($rs);
                    $pages = $srch->pages();
                    $rs = getSubscribedCities();
                    echo '<ul class="listing__tabs">';
                    $c = 0;
                    while ($row = $db->fetch($rs)) {
                        $cl = '';
                        $c++;
                        if ($c == 1) {
                            $cl = 'selected';
                        }
                        echo '<li  id="li_' . $row['subs_city'] . '"><a class="' . $cl . '" href="javascript:void(0)" onclick=showDiv(' . $row['subs_city'] . '); id="row" >' . $row['city_name' . $_SESSION['lang_fld_prefix']] . '</a></li>';
                    }
                    echo ' <li class="last"><a href="javascript:void(0)" class="add__newcity-link">Add New City</a></li>';
                    echo "</ul>";
                    ?>
                    <div class="container__search citysearch__form" style="display:none;">
                        <div class="cover__grey">
                            <div class="formwrap">
                                <form id="frm_mbs_id_frmSubscriptionSubmit" name="frmSubscriptionSubmit" action="" method="post" class="siteForm" onSubmit="return checkValidCityName();">
                                    <table class="formwrap__table">
                                        <tr>
                                            <td colspan="2">
                                                <div class="cover__search">
                                                    <input type="text" name="citySearch" id="citySearch" placeholder="<?php echo t_lang('M_TXT_SEARCH_BY_CITY'); ?>" >
                                                    <input type="hidden" name="city_id[]" value="" id="cityid" />
                                                </div>    
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <input type="hidden" name="logged_email" value="<?php echo $_SESSION['logged_user']['user_email']; ?>" />
                                                <?php
                                                foreach ($arr_cat as $key => $val) {
                                                    echo '<input type="hidden" name="cat_id[]" value="' . $key . '" />';
                                                }
                                                ?>
                                                <input type="submit" value="Add New City" class="themebtn themebtn--large" name="btn_new_subscription">
                                                <input type="button" value="Cancel" class="themebtn themebtn--large add__newcity-link" >
                                            </td>
                                        </tr>
                                    </table>
                                </form> 
                            </div>
                        </div>
                    </div>
                    <?php
                    $rs = getSubscribedCities();
                    $totalRec = $db->total_records($rs);
                    $count = 0;
                    while ($row = $db->fetch($rs)) {
                        $count++;
                        if ($count == $totalRec) {
                            $classl = 'last-row';
                        } else {
                            $classl = '';
                        }
                        $rs_subscribed = $db->query("SELECT nc_cat_id, nc_cat_id as catid FROM tbl_newsletter_category WHERE nc_subs_id = " . $row['subs_id']);
                        $arr_subscribed = $db->fetch_all_assoc($rs_subscribed);
                        $style = "";
                        if ($count > 1) {
                            $style = "display:none";
                        }
                        ?>
                        <div class="space" id="city_<?php echo $row['subs_city']; ?>" style="<?php echo $style; ?>" >
                            <h2  class="section__subtitle"> <?php echo $row['city_name' . $_SESSION['lang_fld_prefix']]; ?> </h2>
                            <a href="?remove-link=<?php echo $row['subs_city']; ?>" class="sqaureredlink"><img alt="" src="/images/x_white.png"></a>
                            <div class="grids" >
                                <?php
                                /* echo "<pre>";
                                  print_r(fetchParentChildCategories(0)); */
                                foreach ($arr_cats as $key => $value) {
                                    $code = "'" . $value['cat_code'] . "'";
                                    echo '<div class="grids__item">';
                                    echo '<div class="grids__list">';
                                    echo '<div class="grids__head"><label class="checkbox"><input type="checkbox" value="1"  onClick="if(this.checked){ return insertParentChildCat(' . $row['city_id'] . ',' . $value['cat_id'] . ',' . $code . ')}else{ return deleteParentChildCat(' . $row['city_id'] . ',' . $value['cat_id'] . ',' . $code . ')}" name="subscitycat_' . $row['city_id'] . '_' . $value['cat_id'] . '"' . ((in_array($value['cat_id'], $arr_subscribed)) ? ' checked="checked"' : '') . '><i class="input-helper"></i>' . $value['cat_name' . $_SESSION['lang_fld_prefix']] . '</label></div>';
                                    echo '<div class="grids__body" id="' . $value['cat_code'] . "_" . $row['city_id'] . '">';
                                    echo fetchCategory($value['cat_id'], $arr_subscribed, $row['city_id'], $value['cat_code']);
                                    echo '</div>';
                                    echo '</div></div>';
                                }
                                ?>
                            </div></div>
                        <?php
                    }
                    if ($totalRec == 0) {
                        $msg->addMessage(t_lang('M_MSG_NO_CITY_SUBSCRIBED'));
                    }
                    ?>
                </div>
            </div>    
        </div>
    </div>    
</section>
<!--bodyContainer end here-->
<script src="<?php echo CONF_WEBROOT_URL; ?>js/masonry.pkgd.js"></script>  
<script type="text/javascript" charset="utf-8">

                                    $(document).ready(function () {
                                        /* for select city form */
                                        $('.add__newcity-link').click(function () {
                                            $(this).toggleClass("active");
                                            $('.citysearch__form').slideToggle("600");
                                        });
                                        $("#citySearch").autocomplete({
                                            source: "<?php echo CONF_WEBROOT_URL; ?>city-search.php",
                                            focus: function (event, ui) {
                                                $("#citySearch").val(ui.item.label);
                                                return false;
                                            },
                                            appendTo: ".cover__search",
                                            select: function (event, ui) {
                                                $("#cityid").val(ui.item.value);
                                                //     $("#frm_mbs_id_frmSubscriptionSubmit").submit();
                                                return false;
                                            }
                                        });
                                        $('.selected').trigger('click');
                                    });
                                    function showDiv(id) {
                                        $('.space').css('display', 'none');
                                        $('#city_' + id).css('display', 'block');
                                        $('#row').parent().parent().find('li >a').removeClass('selected');
                                        $('#li_' + id).find('a').addClass('selected');
                                        $('.grids').masonry({
                                            itemSelector: '.grids__item',
                                        });
                                    }
                                    function checkValidCityName() {
                                        var city = $("#citySearch").val();
                                        var placehoder_name = '<?php echo t_lang('M_TXT_SEARCH_BY_CITY') ?>';
                                        if (city == "" || city == placehoder_name) {
                                            alert("<?php echo t_lang('M_TXT_ENTER_CITY_NAME') ?>");
                                            return false;
                                        } else
                                        {
                                            return true;
                                        }
                                    }
</script>
<?php require_once './footer.php'; ?>