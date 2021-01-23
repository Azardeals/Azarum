<?php
if (strpos($_SERVER['SCRIPT_FILENAME'], '/faq.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/faq-detail.php') == true) {

    function getCatName($category_id)
    {
        global $db;
        $srch = new SearchBase('tbl_cms_faq_categories', 'fc');
        $srch->addCondition('fc.category_deleted', '=', '0');
        $srch->addCondition('fc.category_active', '=', '1');
        $srch->addCondition('fc.category_parent_id', '=', '0');
        $srch->addGroupBy('fc.category_id');
        $srch->addOrder('fc.category_display_order', 'asc');
        $srch->addMultipleFields(array('category_id', 'category_name' . $_SESSION['lang_fld_prefix']));
        $rs_cat = $srch->getResultSet();
        $catCount = 0;
        while ($catRow = $db->fetch($rs_cat)) {

            $catCount++;
            $cat = $catRow['category_id'];
            if ($_GET['cat'] == $cat) {
                $class = "selected current";
            } else {
                $class = '';
            }
            $catName = $catRow['category_name' . $_SESSION['lang_fld_prefix']];
            echo '<li ><a  class="' . $class . '" href="' . friendlyUrl(CONF_WEBROOT_URL . 'faq.php?cat=' . $catRow['category_id']) . '">' . $catRow['category_name' . $_SESSION['lang_fld_prefix']] . '</a>';
            echo '</li>';
        }
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $(".arrowDown").click(function () {
                    $(this).toggleClass("active");
                    $(this).next("ul").stop('true', 'true').slideToggle("slow");
                });
            });
        </script>			
        <?php
    }
    ?>
    <div class="block__body">
        <ul class="links__vertical">
            <?php echo getCatName(0); ?>
        </ul>
    </div>
<?php } ?>
<?php if (strpos($_SERVER['SCRIPT_FILENAME'], '/my-account.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/my-deals.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/my-wallet.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/refer-friends.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/social_refer_friends.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/my-subscriptions.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/my-favorites.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/my-favorites-deals.php') == true) { ?>
    <?php $pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4); ?>
    <section class="tabs__inline">
        <div class="fixed_container">
            <a href="javascript:void(0)" class="links__account-link"><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></a>
            <ul class="links__account-drop">
                <li> <a <?php echo ($pagename == 'my-account') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-account.php'); ?>"><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></a></li>
                <li><a <?php echo ($pagename == 'my-deals') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-deals.php'); ?>"><?php echo t_lang('M_TXT_VOUCHERS'); ?></a></li>
                <li> <a <?php echo ($pagename == 'my-wallet') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'purchase-history.php'); ?>"><?php echo t_lang('M_TXT_PURCHASE_HISTORY') ?></a></li>
                <li><a  <?php echo ($pagename == 'refer-friends') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'refer-friends.php'); ?>"><?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></a></li>
                <li><a <?php echo ($pagename == 'my-subscriptions') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-subscriptions.php'); ?>"><?php echo t_lang('M_TXT_SUBSCRIPTIONS'); ?></a></li>
                <li> <a <?php echo ($pagename == 'my-favorites') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-favorites.php'); ?>"><?php echo t_lang('M_TXT_FAVOURITE_MERCHANT'); ?></a></li>
                <li> 
                    <a class="<?php echo ($pagename == 'my-favorites-deals') ? 'active' : '' ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'my-favorites-deals.php'); ?>"><?php echo t_lang('M_TXT_FAVOURITE_DEALS_PRODUCTS'); ?></a>
                </li>
            </ul>
        </div>
    </section>    
<?php } ?>
<?php if (strpos($_SERVER['SCRIPT_FILENAME'], '/affiliate-account.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/affiliate-refer-friends.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/affiliate-report.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/affiliate-list.php') == true || strpos($_SERVER['SCRIPT_FILENAME'], '/affiliate-history.php') == true) { ?>
    <?php $pagename = substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1, -4); ?>
    <section class="tabs__inline">
        <div class="fixed_container">
            <a href="javascript:void(0)" class="links__account-link"><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></a>
            <ul class="leftLinks">
                <li ><a <?php echo($pagename == 'affiliate-account') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-account.php'); ?>" ><?php echo t_lang('M_TXT_MY_ACCOUNT'); ?></a></li>
                <li ><a <?php echo($pagename == 'affiliate-refer-friends') ? 'class="active"' : '' ?> title="<?php echo t_lang('M_TXT_DEAL_BUCKS'); ?>" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-refer-friends.php'); ?>" > <?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></a></li>
                <li ><a  <?php echo($pagename == 'affiliate-report') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-report.php'); ?>" ><?php echo t_lang('M_TXT_AFFILIATE_REPORTS'); ?></a></li>
                <li ><a <?php echo($pagename == 'affiliate-list') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-list.php'); ?>" ><?php echo t_lang('M_TXT_DEAL_PRODUCT_WISE_REPORTS'); ?></a></li>
                <li ><a <?php echo($pagename == 'affiliate-history') ? 'class="active"' : '' ?> href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'affiliate-history.php'); ?>" > <?php echo t_lang('M_TXT_AFFILIATE_BALANCE'); ?> </a></li>
            </ul>
        </div>
    </section>     
<?php } ?>
