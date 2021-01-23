<?php
require_once './application-top.php';
require_once './header.php';

function isBlogExists($id)
{
    $srch = new SearchBase('tbl_blogs', 'b');
    $srch->joinTable('tbl_blog_categories', 'LEFT OUTER JOIN', 'b.blog_cat_id=bc.cat_id', 'bc');
    $srch->addCondition('blog_id', '=', $id);
    $srch->addCondition('b.blog_approved_by_admin', '=', 1);
    $srch->addOrder('blog_added_on', 'desc');
    $rs_listing = $srch->getResultSet();
    if ($srch->recordCount() > 0) {
        return true;
    }
    return false;
}

$blog_id = (int) $_GET['id'];
$next_blog_id = $blog_id + 1;
$previous_blog_id = $blog_id - 1;
$page = (is_numeric($_REQUEST['page']) ? $_REQUEST['page'] : 1);
$pagesize = 10;
/** Get blog details * */
$srch = new SearchBase('tbl_blogs', 'b');
$srch->joinTable('tbl_blog_categories', 'LEFT OUTER JOIN', 'b.blog_cat_id=bc.cat_id', 'bc');
$srch->addCondition('blog_id', '=', $blog_id);
$srch->addMultipleFields(array('b.*', 'bc.*'));
$srch->addFld('IF(blog_admin_id, (SELECT admin_name FROM tbl_admin a WHERE a.admin_id = b.blog_admin_id), (SELECT user_name FROM tbl_users u WHERE u.user_id = b.blog_user_id)) AS blogger_name');
$srch->addOrder('blog_added_on', 'desc');
$rs_listing = $srch->getResultSet();
$data = $db->fetch($rs_listing);
$data['blog_title'] = htmlentities($data['blog_title'], ENT_QUOTES, 'UTF-8');
//  $data['blog_description']= htmlentities($data['blog_description'], ENT_QUOTES, 'UTF-8');
/* * ------* */
/** Get blog comments * */
$srch = new SearchBase('tbl_blog_comments', 'c');
$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'c.comment_user_id=u.user_id', 'u');
$srch->joinTable('tbl_admin', 'LEFT OUTER JOIN', 'c.comment_admin_id=a.admin_id', 'a');
$srch->addCondition('comment_blog_id', '=', $blog_id);
$srch->addCondition('comment_approved_by_admin', '=', 1);
$srch->addMultipleFields(array('c.*', 'u.user_gender', 'u.user_avatar'));
$srch->addFld('IF(c.comment_admin_id, a.admin_name, u.user_name) AS comment_posted_by');
$srch->addOrder('comment_posted_on', 'desc');
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$result = $srch->getResultSet();
$comment_listing = $db->fetch_all($result);
/* * ------* */
$pagestring = '';
$pages = $srch->pages();
$total_comments = $srch->recordCount();
/** Comment form * */
$frm = new Form('frmComment');
$frm->setExtra('class="siteForm"');
$frm->setTableProperties('class="formwrap__table"');
$frm->captionInSameCell(true);
$frm->setJsErrorDisplay('afterfield');
$frm->setAction('?');
$frm->addHiddenField('', 'comment_blog_id', $blog_id, 'comment_blog_id');
$frm->addHiddenField('', 'form_type', 'comment', 'form_type');
$fld = $frm->addTextArea('', 'comment_description', '', 'comment_description', 'placeholder="' . t_lang('M_TXT_COMMENT') . '*" title="' . t_lang('M_TXT_COMMENT') . '"');
$fld->setRequiredStarPosition('none');
$fld->requirements()->setRequired();
$frm->setValidatorJsObjectName('frmblogCommentValidator');
$frm->setOnSubmit("return setDisable(frmblogCommentValidator)");
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_SEND'), 'btn_submit', 'class="themebtn themebtn--large"');
/* * ***** */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form_type'] == 'comment') {
    if (isUserLogged()) {
        $post = getPostedData();
        if (!$frm->validate($post)) {
            $errors = $frm->getValidationErrors();
            foreach ($errors as $error) {
                $msg->addError($error);
            }
        } else {
            $record = new TableRecord('tbl_blog_comments');
            $record->setFldValue('comment_user_id', $_SESSION['logged_user']['user_id']);
            $record->setFldValue('comment_admin_id', 0);
            $record->setFldValue('comment_posted_on', date("Y-m-d H:i"));
            $arr_lang_independent_flds = array('comment_id', 'comment_blog_id', 'comment_posted_on', 'btn_submit');
            assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
            $success = $record->addNew();
            if ($success) {
                $msg->addMsg(t_lang('M_TXT_COMMENT_POSTED_FOR_APPROVAL'));
                redirectUser();
            } else {
                $msg->addError(t_lang('M_TXT_COULD_NOT_POST_THE_COMMENT') . '&nbsp;' . $record->getError());
                fillForm($frm, $post);
            }
        }
    } else {
        redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
    }
}
?>
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_BLOG'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-listing.php'); ?>"><?php echo t_lang('M_TXT_BLOG'); ?></a></li>
                    <li><?php echo $data['cat_name']; ?></li>
                </ul>
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
                <div class="post__details">
                    <div class="post">
                        <div class="post__head">
                            <span class="post__title"><?php echo $data['blog_title']; ?></span>
                            <div class="post__by">
                                <span class="name"><?php echo t_lang('M_TXT_BY'); ?> : <?php echo $data['blogger_name']; ?> </span>
                                <!--<a href="#" class="txt__caps">Design</a>-->
                                <span class="post__date"><?php echo date(CONF_DATE_FORMAT_PHP, strtotime($data['blog_added_on'])); ?></span>
                            </div>
                        </div>
                        <div class="post__body container__cms">
                            <div class="post__img"><img alt="" src="<?php echo CONF_WEBROOT_URL . 'blog-image.php?id=' . $data['blog_id']; ?>&w=800&h=550"></div>
                            <div class="post__description">
                                <p><?php echo nl2br($data['blog_description']); ?></p>
                            </div>
                        </div>
                        <div class="post__footer">
                            <?php if (isBlogExists($previous_blog_id)) { ?>
                                <a class="themebtn themebtn--grey themebtn--small " href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-details.php?id=' . $previous_blog_id); ?>"><?php echo t_lang('M_TXT_PREVIOUS_POST') ?></a>
                            <?php } ?>
                            <?php if (isBlogExists($next_blog_id)) { ?>
                                <a class="themebtn themebtn--grey themebtn--small " href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'blog-details.php?id=' . $next_blog_id); ?>"><?php echo t_lang('M_TXT_NEXT_POST') ?></a>
                            <?php } ?>
                            <div class="page__container socialShareBlogs">
                                <span class='st_sharethis_large' displayText='ShareThis'></span>
                                <span class='st_facebook_large' displayText='Facebook'></span>
                                <span class='st_twitter_large' displayText='Tweet'></span>
                                <span class='st_linkedin_large' displayText='LinkedIn'></span>
                                <span class='st_pinterest_large' displayText='Pinterest'></span>
                                <span class='st_email_large' displayText='Email'></span>
                                <?php if (CONF_SSL_ACTIVE == 1) { ?>
                                    <script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
                                <?php } else { ?>
                                    <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="sectiontop__row">
                            <h4><?php echo t_lang('M_TXT_COMMENTS') . '&nbsp'; ?> (<?php echo $total_comments; ?>)</h4>
                            <?php $url = isUserLogged() ? "#comments" : friendlyUrl(CONF_WEBROOT_URL . 'login.php'); ?>
                            <a href="<?php echo $url; ?>" class="themebtn themebtn--small right scroll"><?php echo unescape_attr(t_lang('M_TXT_POST_COMMENT')); ?></a>
                        </div>
                        <div class="allreviews">
                            <?php
                            if (count($comment_listing) > 0) {
                                foreach ($comment_listing as $ele) {
                                    $ele['comment_description'] = htmlentities($ele['comment_description'], ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <div class="listrepeated">
                                        <aside class="grid_1">
                                            <figure class="avtar"><?php echo substr($ele['comment_posted_by'], 0, 1); ?></figure>
                                        </aside>
                                        <aside class="grid_2">
                                            <h3 class="name"> <?php echo $ele['comment_posted_by']; ?> </h3>
                                            <span class="datetxt"> <?php echo date('M d, Y', strtotime($ele['comment_posted_on'])); ?></span>
                                            <div class="reviewsdescription">
                                                <p><?php echo nl2br($ele['comment_description']); ?> </p>
                                            </div>
                                        </aside>
                                    </div>
                                    <?php
                                    if ($pages > 1) {
                                        echo createHiddenFormFromPost('frmPaging', '?#comments_block', array('page', 'id'), array('page' => '', 'id' => $_REQUEST['id']));
                                    }
                                    if ($pages > 1) {
                                        $vars = array('page' => $page, 'pages' => $pages, 'total_records' => $total_comments, 'pagesize' => $pagesize);
                                        require_once CONF_VIEW_PATH . 'pagination.php';
                                    }
                                    ?>
                                    <?php
                                }
                            } else {
                                echo '<div><p style="margin:0px;">' . t_lang('M_TXT_NO_COMMENTS') . '</p></div>';
                            }
                            ?>
                        </div>
                        <?php if (isUserLogged()) { ?>
                            <div id="comments">&nbsp;</div>
                            <div class="cover__grey" id="comment__form">
                                <h4><?php echo unescape_attr(t_lang('M_TXT_LEAVE_COMMENT')); ?></h4>
                                <div class="formwrap">
                                    <?php echo $frm->getFormHtml(); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
</section>
<!--containerWhite end here-->
<script type="text/javascript">
    $(document).ready(function () {
        /* $('#comment_description').on('input propertychange', function() {
         CharLimit(this, 500);
         }); */
    });
    function CharLimit(input, maxChar) {
        var len = $(input).val().length;
        if (len > maxChar) {
            $(input).val($(input).val().substring(0, maxChar));
        }
    }
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
        if (el.hasClass('filter__show')) {
            el.removeClass("filter__show");
        } else {
            el.addClass('filter__show');
        }
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
