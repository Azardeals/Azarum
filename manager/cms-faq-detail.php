<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ($_REQUEST['faq_category_id'] != "" && isset($_REQUEST['faq_category_id'])) {
    $faq_category_id = $_REQUEST['faq_category_id'];
}
if ($_REQUEST['mode1'] != "" && isset($_REQUEST['mode1'])) {
    $mode1 = $_REQUEST['mode1'];
} else if ($_REQUEST['edit'] != "" && isset($_REQUEST['edit'])) {
    $edit = $_REQUEST['edit'];
} else if ($_REQUEST['edit1'] != "" && isset($_REQUEST['edit1'])) {
    $edit1 = $_REQUEST['edit1'];
} else if ($_REQUEST['editcontent'] != "" && isset($_REQUEST['editcontent'])) {
    $editcontent = $_REQUEST['editcontent'];
} else if ($_REQUEST['mode1'] == "" && $_REQUEST['editcontent'] == "" && $_REQUEST['edit1'] == "" && $_REQUEST['edit'] == "") {
    redirectUser('faq-categories.php');
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
$basic_frm = new Form('basic_faq_info', 'basic_faq_info');
$basic_frm->addHiddenField('', 'mode', 'basic_setup');
$basic_frm->setAction('?');
if ($_GET['hide'] != '000' and $_GET['hide'] != '001') {
    $basic_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
    $basic_frm->setFieldsPerRow(1);
    $basic_frm->setJsErrorDisplay('afterfield');
    $basic_frm->captionInSameCell(false);
    $basic_frm->addRequiredField('Question Title', 'faq_question_title', '', '', '');
    $fld = $basic_frm->addHtmlEditor('Answer Detailed Description', 'faq_answer_detailed', '');
    $fld->html_before_field = '<div class="frm-editor">';
    $fld->html_after_field = '</div>';
    if ($_GET['edit1'] != '') {
        $edit1 = $_GET['edit1'];
        $basic_frm->addHiddenField('', 'edit1', $edit1, 'hide_basic', 'readonly="readonly"');
    }
    $basic_frm->addSelectBox('Status', 'faq_active', ['1' => 'Active', '0' => 'Inactive'], '', '', '');
    $basic_frm->addHiddenField('', 'hide_basic', '000', 'hide_basic', 'readonly="readonly"');
    $basic_frm->addHiddenField('', 'mode1', 'Add', 'mode1', 'readonly="readonly"');
    $basic_frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_ADD'), '', ' class="inputbuttons"');
}
$basic_frm->addHiddenField('', 'faq_id', '', '', 'readonly="readonly"');
$basic_frm->addHiddenField('', 'faq_category_id', $faq_category_id, '', 'readonly="readonly"');

if ($_GET['edit'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $basic_frm->setAction('?');
        $basic_frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%" ');
        $basic_frm->setFieldsPerRow(1);
        $basic_frm->captionInSameCell(false);
        $basic_frm->addTextBox('Faq Meta Title', 'faq_meta_title', '', 'faq_meta_title', '');
        $basic_frm->addTextArea('Faq Meta Keywords', 'faq_meta_keywords', '', 'faq_meta_keywords', 'cols="45" rows="5"');
        $basic_frm->addTextArea('Faq Meta Description', 'faq_meta_discription', '', 'faq_meta_discription', 'cols="45" rows="5"');
        $basic_frm->addHiddenField('', 'hide_basic', '001', 'hide_basic', 'readonly="readonly"');
        $basic_frm->addHiddenField('', 'editcontent', $_GET['edit'], 'editcontent', 'readonly="readonly"');
        $basic_frm->addSubmitButton('', 'btn_submit', 'Add', '', ' class="inputbuttons" onclick="toggle2();"');
    } else {
        die('Unauthorized Access.');
    }
}

$faq_id = $_GET['editcontent'];

$post = getPostedData();
$hide = $_POST['hide_basic'];
if ($post['mode'] == 'basic_setup') {
    $record = new TableRecord('tbl_cms_faq');
    $arr_lang_independent_flds = ['faq_id', 'faq_category_id', 'editcontent', 'faq_active', 'hide_basic', 'edit1', 'mode1', 'mode', 'btn_submit'];
    assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
    if ($post['faq_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($record->update('faq_id=' . $post['faq_id'])) {
                $faq_id = $post['faq_id'];
                if ($post['edit1'] != "") {
                    $msg->addMsg(t_lang("M_MSG_BASIC_INFORMATION_UPDATED_SUCCESSFULLY"));
                    header("Location:cms-faq-detail.php?faq_category_id=$faq_category_id&edit=$faq_id&hide=$hide");
                    exit;
                } else {
                    $msg->addMsg(t_lang("M_MSG_SEO_INFORMATION_UPDATED_SUCCESSFULLY"));
                    header("Location:cms-faq-detail.php?faq_category_id=$faq_category_id&editcontent=$faq_id&hide=$hide");
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
                $msg->addMsg(T_lang('M_TXT_ADDED_UPDATED_SUCCESSFULL'));
                $faq_id = $record->getId();
                header("Location:cms-faq-detail.php?faq_category_id=$faq_category_id&edit=$faq_id&hide=$hide");
                exit;
            } else {
                $msg->addError('Could not add. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    }
    header("Location:cms-faq-detail.php?faq_category_id=$faq_category_id&edit=$faq_id");
    exit;
}
if ($_GET['edit'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $record = new TableRecord('tbl_cms_faq');
        $record->loadFromDb('faq_id=' . $_GET['edit'], true);
        $row = $record->getFlds();
        $row['btn_submit'] = t_lang('M_TXT_UPDATE');
        fillForm($basic_frm, $row);
    } else {
        die('Unauthorized Access.');
    }
}
if ($_GET['edit1'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $record = new TableRecord('tbl_cms_faq');
        $record->loadFromDb('faq_id=' . $_GET['edit1'], true);
        $row = $record->getFlds();
        $row['btn_submit'] = t_lang('M_TXT_UPDATE');
        fillForm($basic_frm, $row);
        $msg->addMsg(t_lang('M_MSG_UPDATE_BASIC_INFORMATION'));
    } else {
        die('Unauthorized Access.');
    }
}
####################For content of the page tab3###########################################
$post = getPostedData();
if ($post['mode'] == 'page_content_setup') {
    $record = new TableRecord('tbl_cms_faq_gallery');
    $record->assignValues($post);
    if ($post['cmsc_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($record->update('cmsc_faq_id=' . $post['cmsc_faq_id'])) {
                $msg->addMsg(t_lang("M_MSG_RECORD_UPDATED_SUCCESSFULLY"));
                header("Location:cms-faq-listing.php");
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
                callAjax('cms-ajax.php', order + '&mode=REORDER_CMS_FAQ_GALLERY', function (t) {
                    $.facebox(t);
                });
            }
        });
    });
</script>
<div id="msgbox"></div>
<?php
$breadQry = $db->query("select * from tbl_cms_faq_categories where category_id=$faq_category_id");
$breadrow = $db->fetch($breadQry);
$arr_bread = ['index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'faq-categories.php' => t_lang('M_TXT_FAQ'),
    'cms-faq-listing.php?faq_category_id=' . $faq_category_id => $breadrow['category_name'],
    '' => $breadrow['category_name'] . ' Detail Page'
];
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
    if (isset($_POST['cmsc_faq_id'])) {
        $edit = $_POST['cmsc_faq_id'];
    }
    ?>
    <div class="div-inline">
        <div class="page-name"><?php echo t_lang('M_TXT_FAQ_DETAIL') ?>
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?>
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <li>
                                    <a href="cms-add-faq-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $edit ?>&hide=001&mode1=add"><?php echo t_lang('M_TXT_ADD_NEW'); ?></a>
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
                    <?php
                }
                if (isset($_SESSION['msgs'][0])) {
                    ?>
                    <div class="greentext"> <?php echo $msg->display(); ?> </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <div class="box">
        <div class="title"> <?php echo t_lang('M_TXT_FAQ_DETAIL'); ?></div>
        <div class="content">
            <div class="tabsholder">
                <ul class="tabs">
                    <?php
                    if (isset($_GET['edit1']) || isset($_GET['editcontent']) || isset($_GET['edit'])) {
                        $check_for_content_tab = $db->query("select * from tbl_cms_faq where faq_id=$edit");
                        $result = $db->fetch($check_for_content_tab);
                        $faq_meta_title = $result['faq_meta_title'];
                        $faq_meta_keywords = $result['faq_meta_keywords'];
                        ?>					
                        <li>    <a <?php
                            if (isset($_GET['edit1'])) {
                                echo 'class="current"';
                            }
                            ?> href="cms-faq-detail.php?faq_category_id=<?php echo $faq_category_id; ?>&edit1=<?php echo $edit; ?>" ><?php echo t_lang('M_TXT_BASIC_DETAILS'); ?></a></li>
                        <li><a <?php
                            if (isset($_GET['edit'])) {
                                echo 'class="current"';
                            }
                            ?> href="cms-faq-detail.php?faq_category_id=<?php echo $faq_category_id; ?>&edit=<?php echo $edit ?>&hide=000"  ><?php echo t_lang('M_TXT_SEO'); ?></a></li>
                            <?php if (/* $db->total_records($check_val1) > 0 || isset($_GET['editcontent'] )&& */($faq_meta_title != "" && $faq_meta_keywords != "")) { ?>
                            <li ><a <?php
                                if (isset($_GET['editcontent'])) {
                                    echo 'class="current"';
                                }
                                ?> href="cms-faq-detail.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $edit ?>&hide=001" ><?php echo t_lang('M_TXT_FAQ_GALLERY'); ?></a></li>
                            <?php } else { ?>
                            <li><a href="javascript:void(0);" ><?php echo t_lang('M_TXT_FAQ_GALLERY'); ?></a></li>
                            <?php
                        }
                    }
                    if ($_GET['mode1'] == 'Add') {
                        ?>
                        <li>    <a class="current" href="cms-faq-detail.php?faq_category_id=<?php echo $faq_category_id; ?>&mode1=Add" onclick="toggle();"><?php echo t_lang('M_TXT_BASIC_DETAILS'); ?></a></li>
                        <li><a href="javascript:void(0);" ><?php echo t_lang('M_TXT_SEO'); ?></a></li>
                        <li><a href="javascript:void(0);" ><?php echo t_lang('M_TXT_FAQ_GALLERY'); ?></a></li>
                    <?php }
                    ?>			
                    <li><a href="cms-faq-listing.php?faq_category_id=<?php echo $faq_category_id; ?>"><?php echo t_lang('M_TXT_BACK_TO_FAQ_LISTING'); ?></a></li>
                </ul> 
                <div class="contents">	  	  
                    <div id="1" <?php if ($_GET['hide'] == '001') echo' style="display:none;"'; ?> class="tabscontent">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"  class="tbl_forms" style="border: 1px solid #DEDEDE;">
                            <tr>
                                <td ><?php
                                    if ($_GET['mode1'] == 'Add' || (isset($_GET['edit1'])) || (isset($_GET['edit']))) {
                                        echo $msg->display();
                                    } echo $basic_frm->getFormHtml();
                                    ?></td>
                            </tr>
                        </table>
                    </div>
                    <div id="2"  <?php if ($_GET['hide'] != '001') echo' style="display:none;"'; ?> class="tabscontent">
                        <table class="tbl_data" id="cms-listing" width="100%">
                            <thead>
                                <tr>                      
                                    <th width="20%"><?php echo t_lang('M_TXT_GALLERY_TYPE'); ?></th>
                                    <th width="20%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($_GET['deletecontent'] > 0) {
                                    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
                                        $db->query("update tbl_cms_faq_gallery set cmsfg_deleted=1 where cmsfg_id=" . $_GET['deletecontent']);
                                        $msg->addMsg("Faq deleted.");
                                        $url = 'faq_category_id=' . $faq_category_id . '&editcontent=' . $_GET['editcontent'] . '&hide=' . $_GET['hide'];
                                        header("Location:cms-faq-detail.php?$url");
                                        exit;
                                    } else {
                                        die('Unauthorized Access.');
                                    }
                                }
                                $faq_content_listing = new SearchBase('tbl_cms_faq_gallery');
                                $faq_content_listing->addCondition('cmsfg_faq_id', '=', $edit);
                                $faq_content_listing->addCondition('cmsfg_deleted', '!=', '1');
                                $faq_content_listing->addOrder('cmsfg_display_order', 'asc');
                                $faq_content_listing->getQuery();
                                $faq_listing = $faq_content_listing->getResultSet();
                                while ($row = $db->fetch($faq_listing)) { //echo $row['cmsc_id'];
                                    ?>
                                    <tr id="<?php echo $row['cmsfg_id'] ?>">
                                        <?php if ($row['cmsfg_type'] == 0) { ?>
                                            <td width="20%"><?php echo t_lang('M_TXT_IMAGE_GALLERY'); ?></td>
                                        <?php } if ($row['cmsfg_type'] == 1) { ?>
                                            <td width="20%"><?php echo t_lang('M_TXT_VIDEO_GALLERY'); ?></td>
                                        <?php } ?>
                                        <td width="20%"> 
                                            <ul class="actions">
                                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                                                    <li><a href="cms-add-faq-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $edit ?>&hide=001&editgal=<?php echo $row['cmsfg_id']; ?>" title="<?php echo t_lang('M_TXT_EDIT'); ?>"><i class="ion-edit icon"></i></a></li>
                                                <?php } ?>
                                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) { ?>
                                                    <li><a href="cms-faq-detail.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $edit ?>&hide=001&deletecontent=<?php echo $row['cmsfg_id']; ?>" alt="Delete"  title="Delete"onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a></li>
                                                <?php } ?>
                                                <?php if ($row['cmsfg_type'] == 0) { ?>
                                                    <?php if ((checkAdminAddEditDeletePermission(1, '', 'add'))) { ?>
                                                        <li><a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $_GET['editcontent'] ?>&hide=<?php echo $_GET['hide'] ?>&img_gal=<?php echo $row['cmsfg_id']; ?>" title="<?php echo t_lang('M_TXT_IMAGE_GALLERY'); ?>"><i class="ion-android-person icon"></i></a></li>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                <?php if ($row['cmsfg_type'] == 1) { ?>
                                                    <?php if ((checkAdminAddEditDeletePermission(1, '', 'add'))) { ?>
                                                        <li><a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $_GET['editcontent'] ?>&hide=<?php echo $_GET['hide'] ?>&video_gal=<?php echo $row['cmsfg_id']; ?>" title="<?php echo t_lang('M_TXT_VIDEO_GALLERY'); ?>"><i class="ion-play icon"></i></a></li>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php
                                if ($db->total_records($faq_listing) == 0) {
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
<?php
require_once './footer.php';

