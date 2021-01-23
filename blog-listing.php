<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './header.php';
$cat_id = (int) $_REQUEST['cat_id'];
$y = (int) $_REQUEST['y'];
$m = (int) $_REQUEST['m'];
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 2;
$search_val = t_lang('M_TXT_SEARCH_HERE');
/** Get blogs list * */
$srch = new SearchBase('tbl_blogs', 'b');
$srch->joinTable('tbl_admin', 'LEFT OUTER JOIN', 'b.blog_admin_id=a.admin_id', 'a');
$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'b.blog_user_id=u.user_id', 'u');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_val = $_POST['search_blog'];
    if ($search_val != t_lang('M_TXT_SEARCH_HERE')) {
        $srch->addCondition('blog_title', 'LIKE', '%' . $search_val . '%');
    }
}
if ($search_val == '' || $search_val == t_lang('M_TXT_SEARCH_HERE')) {
    if ($y > 0 && $m > 0) {
        if ($m < 10) {
            $m = '0' . $m;
        }
        $search_date = $y . '-' . $m . '-%';
        $srch->addCondition('blog_added_on', 'LIKE', $search_date);
    }
    if ($cat_id > 0) {
        $srch->addCondition('blog_cat_id', '=', $cat_id);
    }
}
$srch->addCondition('blog_approved_by_admin', '=', 1);
$srch->addCondition('blog_status', '=', 1);
$srch->addMultipleFields(array('b.*'));
$srch->addFld('CASE b.blog_admin_id WHEN 0 THEN u.user_name ELSE a.admin_name END AS comment_posted_by');
$srch->addOrder('blog_added_on', 'desc');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
//echo $srch->getQuery();
$rs_listing = $srch->getResultSet();
/* * ------* */
$pagestring = '';
$pages = $srch->pages();
$total_records = $srch->recordCount();
if ($pages > 1) {
    echo createHiddenFormFromPost('frmPaging', '?', array('page', 'y', 'm'), array('page' => '', 'status' => $_REQUEST['y'], 'blogs' => $_REQUEST['m']));
}
?>
<!--containerWhite start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_BLOG'); ?></h3>
                <ul class="grids__half list__inline positioned__right">
                    <li> <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog.php'); ?>" class="themebtn themebtn--org"><?php echo t_lang('M_TXT_ADD_BLOG'); ?></a></li>
                    <li><a href="javascript:void(0)" class="themebtn  link__filter"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section>
<section class="page__container ">
    <div class="fixed_container">
        <div class="row">
            <?php include('blog-right-inc.php'); ?>
            <div class="col-md-9">
                <div class="all__posts">
                    <!--post one start here-->
                    <?php
                    while ($row = $db->fetch($rs_listing)) {
                        /*   echo "<pre>";
                          print_r($row);
                          echo "</pre>"; */
                        $row['blog_title'] = htmlentities($row['blog_title'], ENT_QUOTES, 'UTF-8');
                        $sql = $db->query('SELECT COUNT(*) AS total_comments FROM tbl_blog_comments WHERE comment_blog_id = ' . $row['blog_id'] . ' AND comment_approved_by_admin = 1');
                        $comments_rs = $db->fetch($sql);
                        ?>
                        <div class="post ">
                            <div class="post__head">
                                <span class="post__title"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']); ?>"><?php echo $row['blog_title']; ?></a></span>
                                <span class="post__date"><?php
                                    echo date(CONF_DATE_FORMAT_PHP, strtotime($row['blog_added_on']));
                                    ?></span>
                            </div>
                            <div class="post__body">
                                <div class="post__img"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']); ?>"><img alt="" src="<?php echo CONF_WEBROOT_URL . 'blog-image.php?id=' . $row['blog_id']; ?>&w=800&h=550"></a></div>
                                <div class="post__by">
                                    <figure class="avtar"><?php echo substr($row['comment_posted_by'], 0, 1); ?></figure>
                                    <span class="name"><?php echo t_lang('M_TXT_BY'); ?> :  <?php echo $row['comment_posted_by']; ?>  </span>
                                    <!-- <a href="#" class="txt__caps">Design</a>-->
                                </div>
                                <div class="post__description">
                                    <p><?php echo nl2br(strlen($row['blog_description']) > 500 ? substr($row['blog_description'], 0, 500) . '...' : $row['blog_description']); ?></p>
                                </div>
                            </div>
                            <div class="post__footer">
                                <a class="themebtn themebtn--grey themebtn--small" href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']); ?>"><?php echo t_lang('M_TXT_READ_MORE'); ?></a>
                                <a class="themebtn themebtn--grey themebtn--small " href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']); ?>"><?php echo t_lang('M_TXT_COMMENTS'); ?> [<?php echo $comments_rs['total_comments']; ?>]</a>
                                <div class="sharewraps list__socials right">
                                    <ul class="blogs-listing list__socials">	
                                        <li><span class='st_sharethis_large' displayText='ShareThis'></span></li>
                                        <li><a class='st_facebook_custom' st_url="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']; ?>" st_title="<?php echo $row['blog_title']; ?>" displayText='Facebook'><img src="<?php echo CONF_WEBROOT_URL . 'images/'; ?>facebook_custom.png"></a></li>
                                        <li><a class='st_twitter_custom' st_url="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']; ?>" st_title="<?php echo $row['blog_title']; ?>" displayText='Tweet'><img src="<?php echo CONF_WEBROOT_URL . 'images/'; ?>twitter_custom.png"></a></li>									
                                        <li><a class='st_linkedIn_custom' st_url="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']; ?>" st_title="<?php echo $row['blog_title']; ?>" displayText='LinkedIn'><img src="<?php echo CONF_WEBROOT_URL . 'images/'; ?>linkedin_custom.png"></a></li>
                                        <li><a class='st_pinterest_custom' st_url="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']; ?>" st_title="<?php echo $row['blog_title']; ?>" displayText='Pinterest'><img src="<?php echo CONF_WEBROOT_URL . 'images/'; ?>pinterest_custom.png"></a></li>
                                        <li><a class='st_email_custom' st_url="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . CONF_WEBROOT_URL . 'blog-details.php?id=' . $row['blog_id']; ?>" st_title="<?php echo $row['blog_title']; ?>" displayText='Email'><img src="<?php echo CONF_WEBROOT_URL . 'images/'; ?>email_custom.png"></a></li>
                                    </ul>	
                                </div>
                                <?php if (CONF_SSL_ACTIVE == 1) { ?>
                                    <script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
                                <?php } else { ?>
                                    <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($db->total_records($rs_listing) == 0) echo '<p>No blogs found.</p>'; ?>
                    <!--post one end here-->
                </div>
                <span class="gap"></span>
                <?php
                if ($pages > 1) {
                    $vars = array('page' => $page, 'pages' => $pages, 'total_records' => $total_records, 'pagesize' => $pagesize);
                    require_once CONF_VIEW_PATH . 'pagination.php';
                }
                ?>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    /* for sticky right panel */
    if ($(window).width() > 1050) {
        function sticky_relocate() {
            var window_top = $(window).scrollTop();
            var div_top = $('.fixed__panel').offset().top - 110;
            var sticky_left = $('#fixed__panel');
            if ((window_top + sticky_left.height()) >= ($('#footer').offset().top - 40)) {
                var to_reduce = ((window_top + sticky_left.height()) - ($('#footer').offset().top - 40));
                var set_stick_top = -40 - to_reduce;
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
        $('.box__head-link').removeClass('active');
        $(this).addClass("active");
        var $t = $(this).siblings('.box__head-body');
        if ($t.is(':visible')) {
            $t.slideUp();
        } else {
            $t.slideDown();
        }

        return;
    });
</script>
<?php
require_once './footer.php';
