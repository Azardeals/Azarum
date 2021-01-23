<?php
require_once './header.php';
if (!getManageDisplayOrderCMSPermission()) {
    die("Permission denied");
}
$srch = new SearchBase('tbl_nav_links', 'nl');
$parent = "";
$qrystr = "";
$nl_id = $_GET['id'];
if (isset($nl_id)) {
    $GetCode = $db->query("select * from tbl_nav_links where nl_id = " . $nl_id);
    $CodeResult = $db->fetch($GetCode);
    $CatCode = $CodeResult['nl_code'];
    $parent = $CatCode;
    $qrystr = "?cat=$parent";
}
if (isset($_GET['nav_id'])) {
    $nav_id = $_GET['nav_id'];
    $srch->addCondition('nl_nav_id', '=', $nav_id);
}
$cnd = $srch->addCondition('nl_code', 'LIKE', '' . $parent . '_____');
$srch->addCondition('nl_deleted', '=', 0);
$srch->addGroupBy('nl_code');
$srch->addOrder('nl_display_order', 'asc');
$rs = $srch->getResultSet();
//echo $srch->getQuery();
$count = $srch->recordCount($rs);
?>
<script type="text/javascript">
    $(document).ready(function () {
        //Table DND call
        $('#nav-listing').tableDnD({
            onDrop: function (table, row) {
                var order = $.tableDnD.serialize('id');
                callAjax('cms-ajax.php', order + '&mode=REORDER_NAVIGATION', function (t) {
                    $.facebox(t);
                });
            }
        });
    });
</script>
<div id="msgbox"></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="contentarea">
    <?php
    if (isset($_GET['nav_id'])) {
        $nav_id = $_GET['nav_id'];
        $cms_page = $db->query("Select  nav_name from tbl_navigations where nav_active=1 and nav_id=$nav_id");
        $cms_result = $db->fetch($cms_page);
        $nav_name = $cms_result['nav_name'];
    }
    ?>
    <tr>
        <td class="tblheading">List of <?php echo $nav_name; ?> Content Pages</td>
    </tr>
    <?php echo $msg->display(); ?>
    <tr>
        <td><table class="tbl_listing" id="nav-listing">
                <thead>
                    <tr >                      
                        <th>Caption</th>
                        <th>Manage Display Order</th>			
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $db->fetch($rs)) {
                        echo '<tr id = ' . $row['nl_id'] . '>';
                        $flag = 0;
                        $subCatQuery = $db->query("select nl_caption,nl_code from tbl_nav_links where  nl_deleted=0  and nl_code like '" . $row['nl_code'] . "_____%' group by nl_code order by nl_code ");
                        if ($db->total_records($subCatQuery) > 0) {
                            $flag = $db->total_records($subCatQuery);
                        }
                        if ($flag == "") {
                            echo '<td>' . $row['nl_caption'] . '</td><td>&nbsp;</td>';
                        } else {
                            echo '<td>' . $row['nl_caption'] . '</td>';
                            ?>
                        <td><ul class="actions"><li><a href="navigation-display-order.php?nav_id=<?php echo $nav_id; ?>&id=<?php echo $row['nl_id'] ?>" title="Manage Child Display Order"><i class="ion-drag icon"></i></a></li></ul></td>  
                        <?php
                    }
                    echo '</tr>';
                }
                ?>
                <?php
                if ($db->total_records($rs) == 0) {
                    echo '<tr><td colspan="4">No Records Found.</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr><td class="spacer"></td></tr>
    <tr><td>&nbsp;</td></tr>
</table>
<?php require_once './footer.php'; ?>