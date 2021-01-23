<?php
require_once './application-top.php';
error_reporting(0);
$rs1 = $db->query("select * from tbl_extra_values");
require_once './header.php';
?>
</div></td>		
<td class="right-portion">
    <div class="clear"></div>
    <div class="box"><div class="title"><?php echo t_lang('M_TXT_CAMPAIGN_LIST'); ?></div><div class="content">		
            <div class="gap">&nbsp;</div>	
            <table class="table table-striped tbl_form" border="0" cellspacing="0" cellpadding="0"  width="100%">
                <thead>
                    <tr>
                        <th>S.NO</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    echo 'sd';
                    $i = 1;
                    $row1 = $db->fetch_all($rs1);
                    foreach ($row1 as $key => $value) {
                        ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo strtoupper($value['extra_conf_name']); ?></td>
                            <td><?php echo strip_tags($value['extra_conf_val' . $_SESSION['lang_fld_prefix']]); ?></td>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    require_once './footer.php';

    