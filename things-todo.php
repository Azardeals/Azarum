<?php
require_once './application-top.php';
require_once './header.php';
?>
<!--bodyContainer start here-->
<div class="bodyContainer">
    <div class="containerTop">
        <h2><?php echo t_lang('M_TXT_THINGS_TO_DO'); ?></h2>
    </div>
    <div class="clear"></div>
    <!--body start here-->
    <div class="terms-area">
        <div class="jobs-area">
            <?php
            $srch = new SearchBase('tbl_things_todo', 'ttd');
            $srch->addCondition('ttd.things_status', '=', 1);
            $srch->addOrder('ttd.things_display_id', 'desc');
            $srch->addCondition('ttd.things_city_id', '=', $_SESSION['city']);
            $page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
            $pagesize = 5;
            $srch->setPageNumber($page);
            $srch->setPageSize($pagesize);
            $rs = $srch->getResultSet();
            $pagestring = '';
            $pages = $srch->pages();
            if ($pages > 1) {
                $pagestring .= '<ul class="listing_paging">';
                $pagestring .= '<li>' . t_lang('M_TXT_GOTO_PAGE') . ' </li>' . getPageString('<li><a href="?page=xxpagexx">xxpagexx</a> </li> ', $srch->pages(), $page, '<li class="selected"><a class="pagingActive" href="javascript:void(0);">xxpagexx</a></li>');
                $pagestring .= '</ul>';
            }
            if ($db->total_records($rs) > 0) {
                $countThings = $db->total_records($rs);
                while ($row = $db->fetch($rs)) {
                    $thingsDate = displayDate($row['things_date'], false, true, '');
                    $str .= '<li>
        	<div class="detailPic_wrap">
            	<img rel="" src="' . CONF_WEBROOT_URL . 'thing-image.php?id=' . $row['things_id'] . '&type=list" alt="' . $row['things_title'] . '" >
                <div class="detail_YellowBox">#' . $row['things_display_id'] . '</div>
                <div class="byBox">' . $row['things_image_by'] . '</div>
            </div>
            <a class="detailHeading" href="' . friendlyUrl(CONF_WEBROOT_URL . 'things-detail.php?id=' . $row['things_id']) . '">' . $row['things_title'] . '</a>        </li>';
                    $countThings--;
                }
            }
            ?>
            <!--thingsWrap start here-->
            <div class="thingsWrap">
                <ul class="listing_things">
                    <?php echo $str; ?>
                </ul>
                <?php echo $pagestring; ?>
            </div>
            <!--thingsWrap end here-->
        </div>
    </div>
</div>  <div class="deal-cont-btm2"></div>
</section>
</div>
<?php require_once './footer.php'; ?>
