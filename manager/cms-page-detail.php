<?php
require_once './application-top.php';
checkAdminPermission(1);
if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
    
} else {
    die('Unauthorized Access.');
}
require_once './header.php';
if ($_REQUEST['mode1'] != "" && isset($_REQUEST['mode1'])) {
    $mode1 = $_REQUEST['mode1'];
} else if ($_REQUEST['edit'] != "" && isset($_REQUEST['edit'])) {
    $edit = $_REQUEST['edit'];
} else if ($_REQUEST['edit1'] != "" && isset($_REQUEST['edit1'])) {
    $edit1 = $_REQUEST['edit1'];
} else if ($_REQUEST['editcontent'] != "" && isset($_REQUEST['editcontent'])) {
    $editcontent = $_REQUEST['editcontent'];
} else if ($_REQUEST['mode1'] == "" && $_REQUEST['editcontent'] == "" && $_REQUEST['edit1'] == "" && $_REQUEST['edit'] == "") {
    redirectUser('cms-page-listing.php');
}

function trim_text($text, $count)
{
    return subStringByWords(strip_tags($text), $count);
    $text = str_replace("  ", " ", $text);
    $string = explode(" ", $text);
    for ($wordCounter = 0; $wordCounter <= $count; $wordCounter++) {
        $trimed .= $string[$wordCounter];
        if ($wordCounter < $count) {
            $trimed .= " ";
        } else {
            $trimed .= "...";
        }
    }
    $trimed = trim($trimed);
    return $trimed;
}
?>
<?php
$basic_frm = new Form('basic_page_info', 'basic_page_info');
$basic_frm->addHiddenField('', 'mode', 'basic_setup');
$basic_frm->setAction('?');
if ($_GET['hide'] != '000' and $_GET['hide'] != '001') {
    $basic_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
    $basic_frm->setFieldsPerRow(1);
    $basic_frm->captionInSameCell(false);
    $basic_frm->setJsErrorDisplay('afterfield');
    $basic_frm->addRequiredField('M_TXT_PAGE_NAME', 'page_name', '', '', '');
    $basic_frm->addTextBox('M_TXT_PAGE_URL', 'page_url', '', 'page_url', '')->setUnique('tbl_cms_pages', 'page_url', 'page_id', 'page_url', 'page_url');
    if ($_GET['edit1'] != '') {
        $edit1 = $_GET['edit1'];
        $basic_frm->addHiddenField('', 'edit1', $edit1, 'hide_basic', 'readonly="readonly"');
        //              $basic_frm->addHiddenField('', 'page_url', '', 'page_url');
    }
    $basic_frm->addTextArea('M_FRM_PAGE_SEARCH_KEYWORDS', 'page_search_keywords', '', 'page_search_keywords', 'cols="45" rows="5"');
    $basic_frm->addSelectBox('M_FRM_STATUS', 'page_active', array('1' => 'Active', '0' => 'Inactive'), '', '', '');
    $basic_frm->addHiddenField('', 'hide_basic', '000', 'hide_basic', 'readonly="readonly"');
    $basic_frm->addHiddenField('', 'mode1', 'Add', 'mode1', 'readonly="readonly"');
    $basic_frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_ADD'), '', ' class="inputbuttons"');
}
$basic_frm->addHiddenField('', 'page_id', '', '', 'readonly="readonly"');
?>	
<?php
if ($_GET['edit'] > 0) {
    $basic_frm->setAction('?');
    $basic_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
    $basic_frm->setFieldsPerRow(1);
    $basic_frm->captionInSameCell(false);
    $basic_frm->addTextBox('M_TXT_PAGE_META_TITLE', 'page_meta_title', '', 'page_meta_title', '');
    $basic_frm->addTextArea('M_FRM_PAGE_META_KEYWORDS', 'page_meta_keywords', '', 'page_meta_keywords', 'cols="45" rows="5"');
    $basic_frm->addTextArea('M_FRM_PAGE_META_DESCRIPTION', 'page_meta_description', '', 'page_meta_description', 'cols="45" rows="5"');
    $basic_frm->addHiddenField('', 'hide_basic', '001', 'hide_basic', 'readonly="readonly"');
    $basic_frm->addHiddenField('', 'editcontent', $_GET['edit'], 'editcontent', 'readonly="readonly"');
    $basic_frm->addSubmitButton('', 'btn_submit', 'Add', '', ' class="inputbuttons" ');
}
updateFormLang($basic_frm);
$page_id = $_GET['editcontent'];
$post = getPostedData();
$edit1 = $post['edit1'];
$hide = $_POST['hide_basic'];
if ($post['mode'] == 'basic_setup') {
    if ($post['page_url'] != "") {
        echo $url = $post['page_url'];
        if (preg_match("/^[^0-9][A-z0-9_A-Z-]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/", $url)) {
            //if (preg_match("/^[^0-9][A-z0-9_A-Z-]+$/", $url )) {
            //echo "Match was found <br />";
            //echo $matches[0];
        } else {
            $msg->addError(t_lang("M_MSG_URL_IS_INVALID"));
            header("Location:cms-page-detail.php?edit1=$edit1&hide=$hide");
            exit;
        }
    }
    $record = new TableRecord('tbl_cms_pages');
    /* $record->assignValues($post); */
    $arr_lang_independent_flds = array('page_id', 'page_url', 'page_active', 'hide_basic', 'edit1', 'mode1', 'mode', 'btn_submit');
    assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
    if ($post['page_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($record->update('page_id=' . $post['page_id'])) {
                $page_id = $post['page_id'];
                if ($post['edit1'] != "") {
                    $msg->addMsg(t_lang("M_MSG_BASIC_INFORMATION_UPDATED_SUCCESSFULLY"));
                    header("Location:cms-page-detail.php?edit=$page_id&hide=$hide");
                    exit;
                } else {
                    $msg->addMsg(t_lang("M_MSG_SEO_INFORMATION_UPDATED_SUCCESSFULLY"));
                    header("Location:cms-page-detail.php?editcontent=$page_id&hide=$hide");
                    exit;
                }
            } else {
                $msg->addError('Could not update. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    } else {
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($record->addNew()) {
                $msg->addMsg(t_lang("M_MSG_NEW_PAGE_ADDED_SUCCESSFULLY"));
                $page_id = $record->getId();
                header("Location:cms-page-detail.php?edit=$page_id&hide=$hide");
                exit;
            } else {
                $msg->addError('Could not add. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    }
    header("Location:cms-page-detail.php?edit=$page_id");
    exit;
}
if ($_GET['edit'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $record = new TableRecord('tbl_cms_pages');
        $record->loadFromDb('page_id=' . $_GET['edit'], true);
        $row = $record->getFlds();
        $row['btn_submit'] = t_lang('M_TXT_UPDATE');
        /* $basic_frm->fill($row); */
        fillForm($basic_frm, $row);
        //$msg->addMsg('Update Seo information values and submit.');
    } else {
        die('Unauthorized Access.');
    }
}
if ($_GET['edit1'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $record = new TableRecord('tbl_cms_pages');
        $record->loadFromDb('page_id=' . $_GET['edit1'], true);
        $row = $record->getFlds();
        $row['btn_submit'] = t_lang('M_TXT_UPDATE');
        /* $basic_frm->fill($row); */
        fillForm($basic_frm, $row);
        $msg->addMsg(t_lang('M_MSG_UPDATE_BASIC_INFORMATION'));
    } else {
        die('Unauthorized Access.');
    }
}
####################For content of the page tab3###########################################
$post = getPostedData();
if ($post['mode'] == 'page_content_setup') {
    $record = new TableRecord('tbl_cms_contents');
    $record->assignValues($post);
    if ($post['cmsc_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($record->update('cmsc_page_id=' . $post['cmsc_page_id'])) {
                $msg->addMsg(t_lang("M_MSG_RECORD_UPDATED_SUCCESSFULLY"));
                header("Location:cms-page-listing.php");
                exit;
            } else {
                $msg->addError('Could not update. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    }
}
?>	
<script type="text/javascript">
    $(document).ready(function () {
        //Table DND call
        $('#cms-listing').tableDnD({
            onDrop: function (table, row) {
                var order = $.tableDnD.serialize('id');
                callAjax('cms-ajax.php', order + '&mode=REORDER_CMS_CONTENT', function (t) {
                    $.facebox(t);
                });
            }
        });
    });
</script>
<div id="msgbox"></div>
<?php
$arr_bread = array(
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'cms-page-listing.php' => t_lang('M_TXT_PAGES'),
    '' => t_lang('M_TXT_PAGE_DETAIL'),
);
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <?php
    if (isset($_GET['edit1'])) {
        $edit = $_GET['edit1'];
    }
    if (isset($_GET['edit'])) {
        $edit = $_GET['edit'];
    }
    if (isset($_GET['editcontent'])) {
        $edit = $_GET['editcontent'];
    }
    if (isset($_POST['cmsc_page_id'])) {
        $edit = $_POST['cmsc_page_id'];
    }
    ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_PAGE_DETAIL'); ?> 
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?> 
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>  
                                    <a href="cms-add-content-page.php?add=<?php echo $edit ?>"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
                                </li>
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
    <div class="box">
        <div class="title"> <?php echo t_lang('M_TXT_PAGE_DETAIL'); ?></div>
        <div class="content">
            <div class="tabsholder">
                <ul class="tabs">
                    <?php
                    if (isset($_GET['edit1']) || isset($_GET['editcontent']) || isset($_GET['edit'])) {
                        $check_for_content_tab = $db->query("select * from tbl_cms_pages where page_id=$edit");
                        $result = $db->fetch($check_for_content_tab);
                        $page_meta_title = $result['page_meta_title'];
                        $page_meta_keywords = $result['page_meta_keywords'];
                        ?>					
                        <li>    <a  <?php
                            if (isset($_GET['edit1'])) {
                                echo 'class="current"';
                            }
                            ?> href="cms-page-detail.php?edit1=<?php echo $edit ?>" ><?php echo t_lang('M_TXT_BASIC_DETAILS'); ?></a></li>
                        <li><a  <?php
                            if (isset($_GET['edit'])) {
                                echo 'class="current"';
                            }
                            ?> href="cms-page-detail.php?edit=<?php echo $edit ?>&hide=000"  ><?php echo t_lang('M_TXT_SEO'); ?></a></li>
                            <?php if (1 || /* $db->total_records($check_val1) > 0 || isset($_GET['editcontent'] )&& */($page_meta_title != "" && $page_meta_keywords != "")) { ?>
                            <li ><a  <?php
                                if (isset($_GET['editcontent'])) {
                                    echo 'class="current"';
                                }
                                ?> href="cms-page-detail.php?editcontent=<?php echo $edit ?>&hide=001" ><?php echo t_lang('M_TXT_PAGE_CONTENT'); ?> </a></li>
                            <li ><a href="cms-page-listing.php" ><?php echo t_lang('M_TXT_BACK_TO_PAGE_LISTING'); ?></a></li>
                        <?php } else { ?>
                            <li><a href="javascript:void(0);" > <?php echo t_lang('M_TXT_PAGE_CONTENT'); ?> </a></li>
                            <li ><a href="cms-page-listing.php" ><?php echo t_lang('M_TXT_BACK_TO_PAGE_LISTING'); ?></a></li>
                            <?php
                        }
                    }
                    if ($_GET['mode1'] == 'Add') {
                        ?>
                        <li >    <a class="current" href="cms-page-detail.php?mode1=Add" ><?php echo t_lang('M_TXT_BASIC_DETAILS'); ?></a></li>
                        <li><a href="javascript:void(0);" > <?php echo t_lang('M_TXT_SEO'); ?> </a></li>
                        <li><a href="javascript:void(0);" ><?php echo t_lang('M_TXT_PAGE_CONTENT'); ?></a></li>
                        <li ><a href="cms-page-listing.php" ><?php echo t_lang('M_TXT_BACK_TO_PAGE_LISTING'); ?></a></li>
                    <?php } ?>			
                </ul> 
                <div class="contents">	  
                    <div id="1" <?php echo ($_GET['hide'] == '001') ? 'style="display:none;"' : ''; ?> class="tabscontent">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"  class="tbl_form">
                            <tr>
                                <td><?php
                                    if ($_GET['mode1'] == 'Add' || (isset($_GET['edit1'])) || (isset($_GET['edit']))) {
                                        echo $msg->display();
                                    }
                                    echo $basic_frm->getFormHtml();
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div id="2"  <?php echo ($_GET['hide'] != '001') ? ' style="display:none;"' : ''; ?> class="tabscontent">
                        <table class="tbl_data" id="cms-listing" width="100%">
                            <thead>
                                <tr>                      
                                    <th width="60%"><?php echo t_lang('M_TXT_CONTENT'); ?></th>
                                    <th width="20%"><?php echo t_lang('M_TXT_CONTENT_TYPE'); ?></th>
                                    <th width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($_GET['deletecontent'] > 0) {
                                    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
                                        $db->query("update tbl_cms_contents set cmsc_content_delete=1 where 
							cmsc_id=" . $_GET['deletecontent']);
                                        $msg->addMsg(t_lang("M_TXT_PAGE_DELETED_SUCCESSFULLY"));
                                        $url = 'editcontent=' . $_GET['editcontent'] . '&hide=' . $_GET['hide'];
                                        header("Location:cms-page-detail.php?$url");
                                        exit;
                                    } else {
                                        die('Unauthorized Access.');
                                    }
                                }
                                $page_content_listing = new SearchBase('tbl_cms_contents');
                                $page_content_listing->addCondition('cmsc_page_id', '=', $edit);
                                $page_content_listing->addCondition('cmsc_content_delete', '!=', '1');
                                $page_content_listing->addOrder('cmsc_display_order', 'asc');
                                $page_content_listing->getQuery();
                                $page_listing = $page_content_listing->getResultSet();
                                while ($row = $db->fetch($page_listing)) {
                                    $count = str_word_count($row['cmsc_content' . $_SESSION['lang_fld_prefix']]);
                                    ?>
                                    <tr id="<?php echo $row['cmsc_id']; ?>">
                                        <td width="60%">
                                            <?php
                                            if ($count > 30) {
                                                echo trim_text($row['cmsc_content' . $_SESSION['lang_fld_prefix']], 30);
                                            } else {
                                                echo $row['cmsc_content' . $_SESSION['lang_fld_prefix']];
                                            }
                                            ?>
                                        </td>
                                        <td width="20%"> 
                                            <ul class="actions"> 
                                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                                                    <li><a href="cms-add-content-page.php?content=<?php echo $row['cmsc_id']; ?>" title="<?php echo t_lang('M_TXT_EDIT'); ?>"><i class="ion-edit icon"></i></a></li>
                                                <?php } ?>
                                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) { ?>
                                                    <li><a href="cms-page-detail.php?editcontent=<?php echo $_GET['editcontent'] ?>&hide=<?php echo $_GET['hide'] ?>&deletecontent=<?php echo $row['cmsc_id']; ?>" alt="<?php echo t_lang('M_TXT_DELETE'); ?>"  title="<?php echo t_lang('M_TXT_DELETE'); ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a></li>
                                                        <?php } ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php
                                if ($db->total_records($page_listing) == 0) {
                                    echo '<tr><td colspan="4">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</td>
<?php require_once './footer.php'; ?>
