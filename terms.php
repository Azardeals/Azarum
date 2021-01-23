<?php
require_once './application-top.php';
require_once './header.php';
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
?>
<!--bodyContainer start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo $page_name; ?></h3>
                <ul class="grids__half list__inline positioned__right">  
                    <li><a href="javascript:void(0)" class="themebtn link__filter"><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></a></li>
                </ul>
            </aside>
        </div>
    </div>
</section>
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-3">
                <div class="filter__overlay"></div>
                <div class="fixed__panel section__filter">
                    <div id="fixed__panel">
                        <div class="block__bordered">
                            <h5><?php echo t_lang('M_TXT_QUICK_LINKS'); ?></h5>
                            <ul class="links__vertical uppercase">
                                <?php echo printNav(0, 8); ?>
                            </ul>
                        </div>
                    </div>    
                </div>    
            </div>
            <div class="col-md-9">
                <div class="container__cms">    
                    <?php echo EXTRA_TERMS_CONDITION; ?>
                </div>
            </div>
            <!--bodyContainer end here-->
            </section>
            <?php require_once './footer.php'; ?>
            <script  type="text/javascript" src= "<?php echo CONF_WEBROOT_URL; ?>page-js/cms-page.js" /> 