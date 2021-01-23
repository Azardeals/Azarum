<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './header.php';
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo $page_name; ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'faq.php'); ?>"><?php echo t_lang('M_TXT_FAQS'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_FAQ_DETAILS'); ?></li>
                </ul>
                <ul class="grids__half list__inline positioned__right">  
                    <li><a href="javascript:void(0)" class="themebtn  link__filter"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3 right m__clear">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h5>
                            <div class="block">
                                <div class="block__head"><?php echo t_lang('M_TXT_CATEGORIES'); ?></div>
                                <?php require_once './left-panel-links.php'; ?> 
                            </div>
                        </div>
                    </div>    
                </div>    
            </div>
            <div class="col-md-9">
                <div class="container__cms">
                    <?php
                    $cat = $_GET['cat'];
                    //echo '<div class="gap"></div>';
                    //echo GetFaqParentListing($cat);
                    $ques = $_GET['ques'];
                    if (isset($_GET['ques']) && $_GET['ques'] > 0) {
                        $faq_content_listing = new SearchBase('tbl_cms_faq', 'cmspage');
                        $faq_content_listing->addCondition('faq_deleted', '=', 0);
                        $faq_content_listing->addCondition('faq_id', '=', $ques);
                        $faq_listing = $faq_content_listing->getResultSet();
                        $RowCheck = $faq_content_listing->recordCount($faq_listing);
                        while ($row = $db->fetch($faq_listing)) { //echo $row['cmsc_id'];
                            $faq_question_title = $row['faq_question_title' . $_SESSION['lang_fld_prefix']];
                            $faq_answer_brief = $row['faq_answer_brief' . $_SESSION['lang_fld_prefix']];
                            $faq_answer_detailed = $row['faq_answer_detailed' . $_SESSION['lang_fld_prefix']];
                            $faq_meta_title = $row['faq_meta_title' . $_SESSION['lang_fld_prefix']];
                            $faq_id = $row['faq_id'];
                            echo '<h3>' . $faq_question_title . '</h3>';
                            if ($faq_answer_detailed != "") {
                                $content = '<p>' . $faq_answer_detailed . '</p>';
                            } else {
                                $content = '<p>' . $faq_answer_brief . '</p>';
                            }
                            if (isset($faq_question_title) && $faq_question_title != "") {
                                //echo nl2br($content);
                                echo $content;
                            }
                        }
                        //echo '</div> ';
                    }
                    ?>
                </div>
                <div class="gallery--inlinethumbs">
                    <?php
                    $srch1 = new SearchBase('tbl_cms_faq_gallery', 'cmsfg');
                    $srch1->addCondition('cmsfg.cmsfg_faq_id', '=', $faq_id);
                    $srch1->addOrder('cmsfg.cmsfg_display_order', 'asc');
                    $rs1 = $srch1->getResultSet();
                    if ($srch1->recordCount() > 0) {
                        echo '<ul id="commentary_users">';
                    }
                    while ($row = $db->fetch($rs1)) {
                        $cmsfg_type = $row['cmsfg_type'];
                        $cmsfgi_gallery_id = $row['cmsfg_id'];
                        $hide_Border = $db->query("Select * from tbl_cms_faq_gallery_items where    cmsfgi_gallery_id = " . $cmsfgi_gallery_id . " order by cmsgi_default desc");
                        $Count_rows = mysqli_num_rows($hide_Border);
                        if ($cmsfg_type == 0) {
                            while ($row = $db->fetch($hide_Border)) {
                                $count++;
                                ?>
                                <li><div class="thumb"><a rel="facebox" href="<?php echo FAQ_GALLERY_URL . $row['cmsfgi_file_path'] ?>" class="client-image"><img   src="<?php echo FAQ_GALLERY_URL; ?>thumb/<?php echo $row['cmsfgi_thumb_path']; ?>" alt="" class="imagedisplay"></a>
                                        <?php
                                        if ($row['cmsfgi_title'] != "") {
                                            echo '<h3>' . $row['cmsfgi_title'] . '</h3>';
                                        }
                                        ?>
                                        <?php
                                        echo '</div></li>';
                                    }
                                }
                            }
                            echo '</ul>';
                            ?>
                        </div>
                        <ul class="btns__inline">
                            <li class="first"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'faq.php'); ?>" class="themebtn themebtn--large themebtn--block"><?php echo t_lang('M_TXT_BACK_TO_FAQ') ?></a></li>
                        </ul>
                </div>
            </div>    
        </div>    
</section>
<!--bodyContainer end here-->
<?php
require_once './footer.php';
?>