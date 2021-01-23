<!-- 365 Starts here-->
<?php if ($_SESSION['city'] > 0) { ?>
    <?php
    $srch = new SearchBase('tbl_things_todo', 'ttd');
    $srch->addCondition('ttd.things_status', '=', 1);
    $srch->addOrder('ttd.things_display_id', 'desc');
    $srch->addCondition('ttd.things_city_id', '=', $_SESSION['city']);
    $rs = $srch->getResultSet();
    $count = $db->total_records($rs);
    if ($count > 0) {
        echo '<div class="slide-products ">';
        if ($count > 3) {
            echo '<div class="slide-head">
					<h3>' . t_lang('M_TXT_THINGS_TO_DO') . '</h3>
				</div>';
        }
        echo '<div id="owl-demo" >';
        $countThings = $db->total_records($rs);
        while ($row = $db->fetch($rs)) {
            echo '<div class="item">
                <div class="blueWrap_right">
                       <p>' . substr($row['things_subtitle'], 0, 400) . '</p>
                        <div class="byBox">' . $row['things_image_by'] . '</div>
                       </div>
                       <div class="things-to-do ">
                  <div class="pic"><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'things-todo.php') . '"><img rel="" src="' . CONF_WEBROOT_URL . 'thing-image.php?id=' . $row['things_id'] . '&type=main" alt="' . $row['things_title'] . '" ></a></div>
                  <a href="' . friendlyUrl(CONF_WEBROOT_URL . 'things-detail.php?id=' . $row['things_id']) . '"> ' . substr($row['things_title'], 0, 30) . '</a>
                  <h4 class="things_sub_title">' . substr($row['things_subtitle'], 0, 100) . '</h4>
                   </div>
            	</div>';
            ?> <?php
            $countThings--;
        }
        ?>
        </div>
        </div>
    <?php } ?>		
<?php } ?>
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL ?>js/owl.carousel.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo CONF_WEBROOT_URL ?>css/owl.carousel.css" />
<script type="text/javascript">
    $(document).ready(function () {
        $("#owl-demo").owlCarousel({
            items: 3,
            itemsDesktop: [1199, 3],
            itemsDesktopSmall: [979, 2],
            pagination: false,
            navigation: true,
            itemsMobile: [500, 1]
        });
    });
</script>	
<!-- 365 Ends here -->