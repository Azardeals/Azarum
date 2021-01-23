<?php
require_once './application-top.php';
require_once './header.php';
$srch = new SearchBase('tbl_news', 'n');
$srch->addCondition('n.news_id', '=', $_GET['id']);
$srch->addCondition('n.news_status', '=', 1);
$rs = $srch->getResultSet();
$row = $db->fetch($rs);
$pressDate = displayDate($row['news_date'], false, true, '');
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_NEWS_AT'); ?>&nbsp;<?php echo CONF_SITE_NAME; ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'news.php'); ?>"><?php echo t_lang('M_TXT_NEWS'); ?></a></li>
                    <li><?php echo $row['news_title' . $_SESSION['lang_fld_prefix']]; ?> </li>
                </ul>
            </aside>
        </div>
    </div>
</section>
<section class="page__container ">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="container--narrow">
                    <div class="container__cms">
                        <h3><?php echo $row['news_title' . $_SESSION['lang_fld_prefix']]; ?> ( <?php echo $pressDate; ?> )</h3>
                        <h6 class="txt__uppercanews_imagese"><?php echo $row['news_sub_title' . $_SESSION['lang_fld_prefix']]; ?></h6><span class="gap"></span>
                        <?php if ($row['news_image'] != "") { ?>
                            <div class="news_image">
                                <img src="<?php echo NEWS_IMAGES_URL . $row['news_image']; ?>" ></div>
                        <?php } ?>
                        <p><?php echo $row['news_desc' . $_SESSION['lang_fld_prefix']]; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once './footer.php'; ?>
