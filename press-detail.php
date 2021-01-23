<?php
require_once './application-top.php';
require_once './header.php';
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_PRESS_RELEASE') . " " . t_lang('M_TXT_AT'); ?>  <?php echo CONF_SITE_NAME; ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'press.php'); ?>"><?php echo t_lang('M_TXT_PRESS'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_PRESS_RELEASE') . " " . t_lang('M_TXT_AT'); ?>  <?php echo CONF_SITE_NAME; ?></li>
                </ul>
                <ul class="grids__half list__inline positioned__right">  
                    <li><a href="javascript:void(0)" class="themebtn  link__filter"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<section class="page__container ">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3 right m__clear">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h5>
                            <div class="block">
                                <?php echo EXTRA_PRESS_CONTENT; ?>
                            </div>
                        </div>
                    </div>    
                </div>    
            </div>
            <div class="col-md-9">
                <div class="container__cms">
                    <?php
                    $srch = new SearchBase('tbl_press_release', 'pr');
                    $srch->addCondition('pr.pr_id', '=', $_GET['id']);
                    $srch->addCondition('pr.pr_status', '=', 1);
                    $rs = $srch->getResultSet();
                    $row = $db->fetch($rs);
                    $pressDate = date('F jS, Y', strtotime($row['pr_date']));
                    ?>
                    <h3><?php echo $row['pr_title' . $_SESSION['lang_fld_prefix']]; ?></h3>
                    <h6 class="txt__uppercase"><?php echo $row['pr_subtitle' . $_SESSION['lang_fld_prefix']]; ?></h6>
                    <span class="gap"></span>
                    <span class="post__date"><?php echo $pressDate; ?></span>
                    <p><?php echo ($row['pr_description' . $_SESSION['lang_fld_prefix']]); ?></p>
                    <ul class="btns__inline">
                        <li class="first"><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'press.php'); ?>" class="themebtn themebtn--large themebtn--block"><?php echo t_lang('M_TXT_BACK_TO_PRESS') ?></a></li>
                    </ul>
                </div>
            </div>
        </div>    
    </div>    
</section>
<div class="containerLeft">
    <div class="cms-container">
        <div class="listRepeat details">
            <div class="upperwrap clearfix">
                <div class="grid2">
                </div>
            </div>
        </div>
    </div>   
</div>
<!--bodyContainer end here-->
<?php
require_once './footer.php';

