<?php
require_once './application-top.php';
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
?>
<!--bodyContainer start here-->
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="container__cms">    
                    <?php echo EXTRA_TERMS_CONDITION; ?>
                </div>
            </div>
        </div>
    </div>
    <!--bodyContainer end here-->
</section>
