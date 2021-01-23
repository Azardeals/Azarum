<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
$srch = new SearchBase('tbl_cms_faq_categories', 'nl');
$parent = "";
$qrystr = "";
$category_id = $_GET['id'];
if (isset($category_id) && $category_id != "") {
    $GetCode = $db->query("select * from tbl_cms_faq_categories where category_id = " . $category_id);
    $CodeResult = $db->fetch($GetCode);
    $CatCode = $CodeResult['category_code'];
    $parent = $CatCode;
    $qrystr = "?cat=$parent";
}
$cnd = $srch->addCondition('category_code', 'LIKE', '' . $parent . '_____');
$srch->addCondition('category_deleted', '=', 0);
$srch->addGroupBy('category_code');
$srch->addOrder('category_display_order', 'asc');
$rs = $srch->getResultSet();
//echo $srch->getQuery();
$count = $srch->recordCount($rs);
?>
<script type="text/javascript">
    $(document).ready(function () {
        //Table DND call
        $('#category-listing').tableDnD({
            onDrop: function (table, row) {
                var order = $.tableDnD.serialize('id');
                /* $.mbsmessage('Updating display order....'); */
                callAjax('cms-ajax.php', order + '&mode=REORDER_FAQ_CATEGORY', function (t) {
                    $.facebox(t);
                });
            }
        });
    });
</script>
<!--<div id="msgbox"></div>-->
<?php
if ($category_id != "") {
    $category_name = $CodeResult['category_name'];
} else {
    $category_name = 'Category';
}
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => 'CMS',
    'faq-categories.php' => 'FAQ',
    '' => 'Manage ' . $category_name . ' Display Order',
);
?>
</div>
</td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name">List of  Faq Categories</div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> System messages <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;">Hide</a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <table class="tbl_data" id="category-listing" width="100%">
        <thead>
            <tr >                      
                <th>Caption</th>
                <th>Manage Display Order</th>			
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $db->fetch($rs)) {
                echo '<tr id = ' . $row['category_id'] . '>';
                $flag = 0;
                $subCatQuery = $db->query("select * from tbl_cms_faq_categories where  category_deleted=0  and category_code like '" . $row['category_code'] . "_____%' group by category_code order by category_code ");
                if ($db->total_records($subCatQuery) > 0) {
                    $flag = $db->total_records($subCatQuery);
                }
                if ($flag == "") {
                    echo '<td>' . $row['category_name'] . '</td><td>&nbsp;</td>';
                } else {
                    echo '<td>' . $row['category_name'] . '</td>';
                    ?>
                <td>
                    <ul class="actions"><li><a href="faq-display-order.php?id=<?php echo $row['category_id'] ?>" title="Manage Child Display Order"><i class="ion-drag icon"></i></a></li></ul>
                </td>
                <?php
            }
            echo '</tr>';
        }
        if ($db->total_records($rs) == 0) {
            echo '<tr><td colspan="4">No Records Found.</td></tr>';
        }
        ?>
    </tbody>
</table> 
</td>
<?php
require_once './footer.php';
