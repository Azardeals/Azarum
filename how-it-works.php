<?php
require_once './application-top.php';
require_once './header.php';
/* define configuration variables */
$rs1 = $db->query("select * from tbl_extra_values");
while ($row1 = $db->fetch($rs1)) {
    define(strtoupper($row1['extra_conf_name']), $row1['extra_conf_val' . $_SESSION['lang_fld_prefix']]);
}
/* end configuration variables */
?>
<!--bodyContainer start here-->
<section class="pagebar center">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-12">
                <h3><?php echo t_lang('M_TXT_HOW_IT_WORKS'); ?></h3>
            </aside>
        </div>
    </div>
</section> 
<?php echo EXTRA_HOW_HEADING1; ?>
<!--bodyContainer end here-->
<?php require_once './footer.php'; ?>
