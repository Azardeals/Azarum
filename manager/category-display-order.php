<?php
require_once './header.php';
checkAdminPermission(5);
$srch = new SearchBase('tbl_deal_categories', 'cat');
$parent = "";
$qrystr = "";
$cat_id = $_GET['cat_id'];
if (isset($cat_id)) {
    $GetCode = $db->query("select * from tbl_deal_categories where cat_id = " . $cat_id);
    $CodeResult = $db->fetch($GetCode);
    $CatCode = $CodeResult['cat_code'];
    $parent = $CatCode;
    $qrystr = "?cat=$parent";
}
$cnd = $srch->addCondition('cat_code', 'LIKE', '' . $parent . '_____');
$srch->addGroupBy('cat_code');
$srch->addOrder('cat_display_order', 'asc');
$rs = $srch->getResultSet();
$count = $srch->recordCount($rs);
$arr_bread = [
    'index.php' => '<img alt="Home" src="images/home-icon.png">',
    'deal-categories.php' => t_lang('M_FRM_DEAL_CATEGORIES')
];
?>
<script type="text/javascript">
    $(document).ready(function () {
        //Table DND call
        $('#category_listing').tableDnD({
            onDrop: function (table, row) {
                var order = $.tableDnD.serialize('id');
                callAjax('cms-ajax.php', order + '&mode=REORDER_CATEGORY', function (t) {
                    $.facebox(t);
                });
            }
        });
    });
</script>
</div></td>
<div id="msgbox"></div>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_MANAGE_DISPLAY_ORDER_FOR_DEAL_CATEGORIES'); ?>
            <?php if ((checkAdminAddEditDeletePermission(5, '', 'add'))) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li><a href="deal-categories.php?page=<?php echo $page; ?>&add=new"><?php echo t_lang('M_TXT_ADD_NEW_CATEGORY'); ?></a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            <?php } ?>	
        </div>
    </div>
    <div class="clear"></div>
    <?php if ((isset($_SESSION['errs'][0])) || (isset($_SESSION['msgs'][0]))) { ?> 
        <div class="box" id="messages">
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide();
                        return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
            <div class="content">
                <?php if (isset($_SESSION['errs'][0])) { ?>
                    <div class="redtext"><?php echo $msg->display(); ?> </div>
                    <br/>
                    <br/>
                <?php } if (isset($_SESSION['msgs'][0])) { ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?> 
    <table class="tbl_data" width="100%" id="category_listing">
        <thead>
            <tr>                      
                <th>Caption</th>
                <th>Manage Display Order</th>			
            </tr>
        </thead>
        <?php
        while ($row = $db->fetch($rs)) {
            echo '<tr id = ' . $row['cat_id'] . '>';
            $flag = 0;
            $subCatQuery = $db->query("select cat_name,cat_code from tbl_deal_categories where  cat_code like '" . $row['cat_code'] . "_____%' group by cat_code order by cat_code ");
            if ($db->total_records($subCatQuery) > 0) {
                $flag = $db->total_records($subCatQuery);
            }
            if ($flag === 0) {
                echo '<td>' . $row['cat_name'] . '</td><td>&nbsp;</td>';
            } else {
                echo '<td>' . $row['cat_name'] . '</td>';
                ?>
                <td>
                    <ul class="actions">
                        <li><a href="category-display-order.php?cat_id=<?php echo $row['cat_id'] ?>" title="Manage Child Display Order"><i class="ion-drag icon"></i></a></li>
                    </ul>
                </td>  
                <?php
            }
            echo '</tr>';
        }
        if ($db->total_records($rs) == 0) {
            echo '<tr><td colspan="4">No Records Found.</td></tr>';
        }
        ?>
    </table>
</td>
<?php
require_once './footer.php';
