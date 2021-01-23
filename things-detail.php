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
    <div class="terms-area">
        <div class="jobs-area">
            <!--thingsWrap start here-->
            <div class="thingsWrap">
                <div class="thingsWrapper">
                    <div class="thingsWrap_left">
                        <?php
                        $srch = new SearchBase('tbl_things_todo', 'ttd');
                        $srch->addCondition('ttd.things_status', '=', 1);
                        $srch->addCondition('ttd.things_id', '=', $_GET['id']);
                        $srch->addCondition('ttd.things_city_id', '=', $_SESSION['city']);
                        $rs = $srch->getResultSet();
                        if ($db->total_records($rs) > 0) {
                            $countThings = $db->total_records($rs);
                            $row = $db->fetch($rs);
                            $thingsDate = displayDate($row['things_date'], false, true, '');
                            echo '<h4><a href="#">365 things to do in ' . $_SESSION['city_to_show'] . '</a></h4>
                            <div class="detailFull_wrap">
                                    <div class="detailPic_wrap">
                                        <img rel="" src="' . CONF_WEBROOT_URL . 'thing-image.php?id=' . $row['things_id'] . '&type=list" alt="' . $row['things_title'] . '" >
                                        <div class="detail_YellowBox">#' . $row['things_display_id'] . '</div>
                                        <div class="byBox">photo by ' . $row['things_image_by'] . '</div>
                                    </div>
                                    <div class="thingsDetails_wrap">
                                        <h3>
                                            <a href="javascript:void(0);">' . $row['things_title'] . '</a>
                                        </h3>
                                       ' . $row['things_description'] . '
                                    </div>
                                </div>
                            <h5><a href="#">' . $row['things_image_by'] . '</a><br>Neighborhood: ' . $row['things_neighbourhood'] . '</h5>';
                        }
                        ?>
                    </div>
                    <div class="thingsWrap_right">
                        <ul class="listingThumbs">
                            <?php
                            $srch = new SearchBase('tbl_things_todo', 'ttd');
                            $srch->addCondition('ttd.things_status', '=', 1);
                            $srch->addCondition('ttd.things_id', '!=', $_GET['id']);
                            $srch->addCondition('ttd.things_city_id', '=', $_SESSION['city']);
                            $rs = $srch->getResultSet();
                            if ($db->total_records($rs) > 0) {
                                $countThings = $db->total_records($rs);
                                $count = 0;
                                while ($rowThumb = $db->fetch($rs)) {
                                    $count++;
                                    $thingsDate = displayDate($row['things_date'], false, true, '');
                                    if ($count < 6) {
                                        echo '<li>
                                        <a href="' . friendlyUrl(CONF_WEBROOT_URL . 'things-detail.php?id=' . $rowThumb['things_id']) . '"">
                                          <div class="detailSide_wrap">
                                               <img rel="" src="' . CONF_WEBROOT_URL . 'thing-image.php?id=' . $rowThumb['things_id'] . '&type=thumb" alt="' . $rowThumb['things_title'] . '" >
                                                <div class="detail_Yellowsmall">#' . $rowThumb['things_display_id'] . '</div>
                                            </div>
                                        </a>
                                    </li>';
                                    }
                                }
                            }
                            ?>
                        </ul>
                        <?php if ($db->total_records($rs) > 5) { ?>
                            <a class="seeAll" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'things-todo.php'); ?>">See All <?php echo $countThings; ?></a>
                        <?php } ?>
                    </div>
                </div>
                <div id="fb-root"></div>
                <script type="text/javascript">
                    (function (d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id))
                            return;
                        js = d.createElement(s);
                        js.id = id;
                        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=APP_ID";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>
                <div class="fb-live-stream" data-event-app-id="171691556284395" data-width="400" data-height="500" data-xid="<?php echo $row['things_id']; ?>" data-via-url="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . 'things-detail/' . $row['things_id'] . '/' . $row['things_title']; ?>" data-always-post-to-friends="false"></div>
            </div>
            <!--thingsWrap end here-->
        </div>
    </div>
</div>
</div>
<div class="deal-cont-btm2"></div>
<?php require_once './footer.php'; ?>
