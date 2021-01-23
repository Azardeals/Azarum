<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
if (!isCompanyUserLogged())
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'merchant-login.php'));
$page = (is_numeric($_GET['page']) ? $_GET['page'] : 1);
$pagesize = 10;
$mainTableName = 'tbl_deals_images';
$primaryKey = 'dimg_id';
$colPrefix = 'dimg_';
$frm = getMBSFormByIdentifier('frmDealImage');
//$frm->setAction(friendlyUrl(CONF_WEBROOT_URL.'deals-images.php?deal_id='.$post['dimg_deal_id'].'&'));
$fld = $frm->getField('submit');
$frm->addSubmitButton('', 'submit', 'Submit', 'submit', 'class="button_small"');
$frm->removeField($fld);
$frm->setTableProperties('style="width:935px;" class="account_table" ');
$fld = $frm->getField('dimg_deal_id');
$fld->value = $_GET['deal_id'];
if (is_numeric($_GET['edit'])) {
    $record = new TableRecord($mainTableName);
    if (!$record->loadFromDb($primaryKey . '=' . $_GET['edit'], true)) {
        $msg->addError($record->getError());
    } else {
        $arr = $record->getFlds();
        $arr['btn_submit'] = 'Update';
        $frm->fill($arr);
        $msg->addMsg('Change the values and submit.');
    }
}
if (is_numeric($_GET['delete'])) {
    $imgName = $db->query('select * from ' . $mainTableName . ' where dimg_id=' . $_GET['delete']);
    $img = $db->fetch($imgName);
    unlink(DEAL_IMAGES_PATH . $img['dimg_name']);
    $db->query('delete from tbl_deals_images where dimg_id=' . $_GET['delete']);
    $msg->addMsg('Image Deleted Successful!');
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'deals-images.php?deal_id=' . $_GET['deal_id'] . '&page=' . $page . '&'));
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    if (!$frm->validate($post)) {
        $errors = $frm->getValidationErrors();
        foreach ($errors as $error)
            $msg->addError($error);
    } else {
        $record = new TableRecord($mainTableName);
        $record->assignValues($post);
        $record->setFldValue('dimg_deal_id', $_GET['deal_id']);
        $ext = strtolower(strrchr($_FILES['dimg_name']['name'], '.'));
        if (!in_array($ext, array('.gif', '.jpg', '.jpeg'))) {
            
        } else {
            $success = ($post[$primaryKey] > 0) ? $record->update($primaryKey . '=' . $post[$primaryKey]) : $record->addNew();
        }
        if ($success) {
            $dimg_id = ($post[$primaryKey] > 0) ? $post[$primaryKey] : $record->getId();
            if (is_uploaded_file($_FILES['dimg_name']['tmp_name'])) {
                $ext = strtolower(strrchr($_FILES['dimg_name']['name'], '.'));
                if (!in_array($ext, array('.gif', '.jpg', '.jpeg'))) {
                    $msg->addError('Deal image could not be saved. Only gif, jpg and jpeg images are supported.');
                } else {
                    $flname = time() . '_' . $_FILES['dimg_name']['name'];
                    if (!move_uploaded_file($_FILES['dimg_name']['tmp_name'], DEAL_IMAGES_PATH . $flname)) {
                        $msg->addError('File could not be saved.');
                    } else {
                        $db->update_from_array('tbl_deals_images', array('dimg_name' => $flname), 'dimg_id=' . $dimg_id);
                    }
                }
            }
            $msg->addMsg('Add/Update Successful!');
            redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'deals-images.php?deal_id=' . $post['dimg_deal_id'] . '&page=' . $page . '&'));
        } else {
            $msg->addError('Could not add/update! Error: ' . $record->getError());
            redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'deals-images.php?deal_id=' . $post['dimg_deal_id'] . '&page=' . $page . '&'));
        }
    }
}
$srch = new SearchBase('tbl_deals_images', 'd');
$srch->addCondition('dimg_deal_id', '=', $_GET['deal_id']);
$srch->setPageNumber($page);
$srch->setPageSize($pagesize);
$rs_listing = $srch->getResultSet();
$pagestring = '';
$pages = $srch->pages();
if ($pages > 1) {
    $pagestring .= '<ul class="paging"><li class="space">';
    $pagestring .= 'Displaying Page ' . $page . ' of ' . $pages . ' Go to:</li> <ul class="paging">';
    $pagestring .= getPageString('<li><a href="' . friendlyUrl(CONF_WEBROOT_URL . 'deals-images.php?deal_id=' . $_GET['deal_id'] . '&page=xxpagexx&') . '">xxpagexx</a></li> ', $pages, $page, '<li><a class="still" href="javascript:void(0);">xxpagexx</a></li>  ', '....');
    $pagestring .= '</ul>';
}
$arr_listing_fields = array(
    'listserial' => 'S.N.',
    'image' => 'Image',
    'dimg_name' => 'Name',
    'action' => 'Action'
);
require_once './header.php';
?>
<!--body start here-->
<div id="body">
    <div id="center_Wrapper">
        <div class="center_intro_Wrap">
            <ul class="intro_navs">
                <li >    <a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'deals-images.php?deal_id=' . $_GET['deal_id'] . '&page=' . $_GET['page'] . '&'); ?>" <?php if ($_GET['deal_id'] > 0 && ($_GET['add'] != 'new')) echo 'class="current"'; ?>><span>Images</span></a></li>
                <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'deals-images.php?deal_id=' . $_GET['deal_id'] . '&page=' . $_GET['page'] . '&add=new'); ?>" <?php if ($_GET['add'] == 'new') echo 'class="current"'; ?>><span>Add New Image</span></a></li>
                <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'company-deals.php'); ?>" ><span>Deals</span></a></li>
                <li ><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL . 'merchant-account.php'); ?>" ><span>My Account</span></a></li>
            </ul>
        </div>
        <div class="center_Wrap">
            <!--account_area start here-->
            <div class="account_area">
                <?php
                if (is_numeric($_GET['edit']) || $_GET['add'] == 'new') {
                    echo '<div class="intro_head_wrap">
           <h3>Add Deal Images</h3>
		    </div>
					 <div class="account_wrapper">
                        	<div class="account_wrap">
                            	<div class="account_tablewrap">';
                    echo $frm->getFormHtml();
                } else {
                    echo $pagestring;
                    ?>
                    <div class="account_wrapper">
                        <div class="account_wrap" style="width:935px;">
                            <div class="account_tablewrap" style="width:935px;">		 
                                <?php echo $msg->display(); ?>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="data_table" style="width:935px;">
                                    <thead>
                                        <tr>
                                            <?php
                                            foreach ($arr_listing_fields as $val)
                                                echo '<th>' . $val . '</th>';
                                            ?>
                                        </tr>
                                    </thead>
                                    <?php
                                    for ($listserial = ($page - 1) * $pagesize + 1; $row = $db->fetch($rs_listing); $listserial++) {
                                        echo '<tr' . (($row[$colPrefix . 'active'] == '0') ? ' class="inactive"' : '') . '>';
                                        foreach ($arr_listing_fields as $key => $val) {
                                            echo '<td>';
                                            switch ($key) {
                                                case 'listserial':
                                                    echo $listserial;
                                                    break;
                                                case 'image':
                                                    echo '<img src="' . DEAL_IMAGES_URL . $row['dimg_name'] . '" width="50" height="50" border="0">';
                                                    break;
                                                case 'action':
                                                    echo'<a href="' . CONF_WEBROOT_URL . 'deals-images.php?deal_id=' . $row['dimg_deal_id'] . '&delete=' . $row['dimg_id'] . '&' . '"  title="Delete"><img src="' . CONF_WEBROOT_URL . 'manager/images/mail-delete.png"></a> ';
                                                    break;
                                                default:
                                                    echo $row[$key];
                                                    break;
                                            }
                                            echo '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                    if ($db->total_records($rs_listing) == 0)
                                        echo '<tr><td colspan="' . count($arr_listing_fields) . '">No records found.</td></tr>';
                                    ?>
                                </table>  
                            <?php } ?>
                        </div> 
                    </div>
                </div>
            </div>
            <!--account_area end here-->
        </div>
        <img src="<?php echo CONF_WEBROOT_URL; ?>images/center_main_bottom.png" alt="" />
    </div>
    <div class="clear"></div>    
</div> 
<!--body end here-->      
<div class="clear"></div>
<?php require_once './footer.php'; ?>
