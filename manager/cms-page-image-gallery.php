<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ($_REQUEST['img_gal'] == "" && $_REQUEST['video_gal'] == "") {
    redirectUser('cms-page-detail.php');
} else {
    $editcontent = $_REQUEST['editcontent'];
    $edit = $_REQUEST['edit'];
    $hide = $_REQUEST['hide'];
}
if ($_REQUEST['img_gal'] != "") {
    $img_gal = $_REQUEST['img_gal'];
    $gal_id = $img_gal;
    $gal_name = t_lang('M_TXT_IMAGE_GALLERY');
    $gal_name1 = 'M_FRM_SELECT_IMAGE_THUMB_IMAGE';
}
$cmsgi_gallery_id = $_REQUEST['cmsgi_gallery_id'];
if ($_REQUEST['video_gal'] != "") {
    $video_gal = $_REQUEST['video_gal'];
    $gal_id = $video_gal;
    $gal_name = t_lang('M_TXT_VIDEO_GALLERY');
    $gal_name1 = 'M_FRM_SELECT_VIDEO_THUMB_IMAGE';
}
$frm = new Form('cms_page_img_galery', 'cms_page_img_galery');
$frm->setAction('?');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%" ');
$frm->setJsErrorDisplay('afterfield');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
if ($_REQUEST['img_gal'] != "") {
    $frm->addRequiredField('M_FRM_IMAGE_TITLE', 'cmsgi_title', '', 'cmsgi_title', 'class="input"');
    $frm->addTextArea('M_FRM_IMAGE_DESCRIPTION', 'cmsgi_desc', '', 'cmsgi_desc', 'class="input"');
    if ($edit != "") {
        $frm->addFileUpload('M_FRM_SELECT_IMAGE_FILE', 'cmsgi_file_path', 'cmsgi_file_path', '');
    } else {
        $fld = $frm->addFileUpload('M_FRM_SELECT_IMAGE_FILE', 'cmsgi_file_path', 'cmsgi_file_path', '');
        $fld->html_after_field = '<span style="color: #f00;">File Format: jpg, jpeg, pjpg, gif, png</span>';
        $fld->requirements()->setRequired();
    }
}
if ($_REQUEST['video_gal'] != "") {
    $frm->addRequiredField('M_FRM_VIDEO_TITLE', 'cmsgi_title', '', 'cmsgi_title', 'class="input"');
    $frm->addTextArea('M_FRM_VIDEO_DESCRIPTION', 'cmsgi_desc', '', 'cmsgi_desc', 'class="input"');
    if ($edit != "") {
        $frm->addFileUpload('M_FRM_SELECT_VIDEO_FILE', 'cmsgi_file_path', 'cmsgi_file_path', '');
    } else {
        $frm->addFileUpload('M_FRM_SELECT_VIDEO_FILE', 'cmsgi_file_path', 'cmsgi_file_path', '')->requirements()->setRequired();
    }
}
if ($edit != "") {
    $getImg = $db->query("select * from tbl_cms_gallery_items where cmsgi_id='" . $edit . "'");
    $imgRow = $db->fetch($getImg);
    if ($imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']] != "" && $_REQUEST['img_gal'] != "") {
        $frm->addHTML('', '', '<img src="' . IMAGE_GALLERY_URL . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']] . '" width="75" height="75">', false);
    }
    if ($imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']] != "" && $_REQUEST['video_gal'] != "") {
        $frm->addHTML('', '', 'Video Exist ' . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']], false);
    }
}
if ($edit != "") {
    $frm->addFileUpload($gal_name1, 'cmsgi_thumb_path', 'cmsgi_thumb_path', '');
} else {
    $fld = $frm->addFileUpload($gal_name1, 'cmsgi_thumb_path', 'cmsgi_thumb_path', '');
    $fld->requirements()->setRequired();
    $fld->html_after_field = '<span style="color: #f00;">File Format: jpg, jpeg, pjpg, gif, png</span>';
}
if ($edit != "") {
    if ($imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']] != "") {
        $frm->addHTML('', '', '<img src="' . IMAGE_GALLERY_URL . $imgRow['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']] . '" width="50" height="50">', false);
    }
}
if ($_REQUEST['img_gal'] != "") {
    $frm->addTextBox('M_FRM_LINK_URL', 'cmsgi_url', '', 'cmsgi_url', 'class="input"');
}
$frm->addHiddenField('', 'cmsgi_id', '', '', 'readonly="readonly"');
$frm->addHiddenField('', 'cmsgi_gallery_id', $gal_id, '', 'readonly="readonly"');
$frm->addHiddenField('', 'img_gal', $img_gal, '', 'readonly="readonly"');
$frm->addHiddenField('', 'video_gal', $video_gal, '', 'readonly="readonly"');
$frm->addHiddenField('', 'editcontent', $editcontent, '', 'readonly="readonly"');
$frm->addHiddenField('', 'hide', $hide, '', 'readonly="readonly"');
$frm->addSubmitButton('', 'btn_submit', t_lang('M_TXT_ADD'), '', ' class="inputbuttons"');
updateFormLang($frm);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    $record = new TableRecord('tbl_cms_gallery_items');
    $arr_lang_independent_flds = ['cmsgi_url', 'cmsgi_gallery_id', 'cmsgi_id', 'img_gal', 'video_gal', 'editcontent', 'hide', 'btn_submit'];
    assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
    ///////////////////image///////////////////////
    if (!$_FILES['cmsgi_file_path']['name'] == "") {
        if ($_REQUEST['img_gal'] != "") {
            $check = checkImageTypes($_FILES['cmsgi_file_path']['type']);
        }
        if ($_REQUEST['video_gal'] != "") {
            $accepted_files = ['.mov', '.flv', '.FLV'];
            $ext = strtolower(strrchr($_FILES['cmsgi_file_path']['name'], '.'));
            $check = in_array($ext, $accepted_files);
        }
        if ($check) {
            $item_path = time() . "_" . $_FILES['cmsgi_file_path']['name'];
            if ($_REQUEST['img_gal'] != "") {
                if (!move_uploaded_file($_FILES['cmsgi_file_path']['tmp_name'], IMAGE_GALLERY_PATH . $item_path)) {
                    die('Could not save file.');
                }
                $img = new ImageResize(IMAGE_GALLERY_PATH . $item_path);
                $img->setMaxDimensions(200, 200);
                $img->saveImage(IMAGE_GALLERY_PATH . "big/" . $item_path);
            }
            if ($_REQUEST['video_gal'] != "") {
                move_uploaded_file($_FILES['cmsgi_file_path']['tmp_name'], IMAGE_GALLERY_PATH . "video/" . $item_path) or $error = "Not A File";
            }
            $record->setFldValue('cmsgi_file_path' . $_SESSION['lang_fld_prefix'], $item_path);
            if ($post['cmsgi_id'] > 0) {
                $getImg = $db->query("select * from tbl_cms_gallery_items where cmsgi_id='" . $post['cmsgi_id'] . "'");
                $imgRow = $db->fetch($getImg);
                if ($_REQUEST['img_gal'] != "") {
                    unlink(IMAGE_GALLERY_PATH . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']]);
                    unlink(IMAGE_GALLERY_PATH . 'big/' . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']]);
                }
                if ($_REQUEST['video_gal'] != "") {
                    unlink(IMAGE_GALLERY_PATH . 'video/' . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']]);
                }
            }
        } else {
            if ($_REQUEST['img_gal'] != "") {
                $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&img_gal=' . $post['cmsgi_gallery_id'];
            }
            if ($_REQUEST['video_gal'] != "") {
                $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&video_gal=' . $post['cmsgi_gallery_id'];
            }
            $msg->addError("could not update video file extension is wrong .");
            redirectUser($url);
        }
    }
    ///////////////////////////////////////////////////////
    ///////////////////image-thumb///////////////////////
    if ($_FILES['cmsgi_thumb_path']['name'] != "") {
        if (checkImageTypes($_FILES['cmsgi_thumb_path']['type'])) {
            $item_path = time() . "_thumb_" . $_FILES['cmsgi_thumb_path']['name'];
            if (!move_uploaded_file($_FILES['cmsgi_thumb_path']['tmp_name'], IMAGE_GALLERY_PATH . $item_path)) {
                die('Could not save file.');
            }
            $img = new ImageResize(IMAGE_GALLERY_PATH . $item_path);
            ImageResize::IMG_RESIZE_EXTRA_ADDSPACE;
            $img->setMaxDimensions(90, 67);
            $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_ADDSPACE);
            $img->saveImage(IMAGE_GALLERY_PATH . "thumb/" . $item_path);
            $record->setFldValue('cmsgi_thumb_path' . $_SESSION['lang_fld_prefix'], $item_path);
            if ($post['cmsgi_id'] > 0) {
                $getImg = $db->query("select * from tbl_cms_gallery_items where cmsgi_id='" . $post['cmsgi_id'] . "'");
                $imgRow = $db->fetch($getImg);
                unlink(IMAGE_GALLERY_PATH . $imgRow['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']]);
                unlink(IMAGE_GALLERY_PATH . 'thumb/' . $imgRow['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']]);
            }
        } else {
            if ($_REQUEST['img_gal'] != "") {
                $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&img_gal=' . $post['cmsgi_gallery_id'];
            }
            if ($_REQUEST['video_gal'] != "") {
                $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&video_gal=' . $post['cmsgi_gallery_id'];
            }
            $msg->addError("could not update image file ext is wrong .");
            redirectUser($url);
        }
    }
    ///////////////////////////////////////////////////////
    if ($post['cmsgi_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($record->update('cmsgi_id=' . $post['cmsgi_id'])) {
                $msg->addMsg($gal_name . " updated successfully.");
            } else {
                $msg->addError('Could not update. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    } else {
        if ((checkAdminAddEditDeletePermission(1, '', 'add'))) {
            if ($record->addNew()) {
                $msg->addMsg("New " . $gal_name . " added successfully.");
            } else {
                $msg->addError('Could not add. Error! ' . $record->getError());
            }
        } else {
            die('Unauthorized Access.');
        }
    }
    if ($_REQUEST['img_gal'] != "") {
        $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&img_gal=' . $post['cmsgi_gallery_id'];
    }
    if ($_REQUEST['video_gal'] != "") {
        $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&video_gal=' . $post['cmsgi_gallery_id'];
    }
    redirectUser($url);
}
if ($_GET['edit'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $record = new TableRecord('tbl_cms_gallery_items');
        $record->loadFromDb('cmsgi_id=' . $_GET['edit'], true);
        $row = $record->getFlds();
        $row['btn_submit'] = 'Update';
        fillForm($frm, $row);
    } else {
        die('Unauthorized Access.');
    }
}
if ($_GET['delete'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $getImg = $db->query("select * from tbl_cms_gallery_items where  cmsgi_id='" . $_GET['delete'] . "'");
        $imgRow = $db->fetch($getImg);
        if ($_REQUEST['img_gal'] != "") {
            unlink(IMAGE_GALLERY_PATH . $imgRow['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']]);
            unlink(IMAGE_GALLERY_PATH . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']]);
            unlink(IMAGE_GALLERY_PATH . 'thumb/' . $imgRow['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']]);
            unlink(IMAGE_GALLERY_PATH . 'big/' . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']]);
            $db->query("delete from tbl_cms_gallery_items where cmsgi_id=" . $_GET['delete']);
            $msg->addMsg("Image Deleted Successfully.");
            $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&img_gal=' . $img_gal;
        } else {
            unlink(IMAGE_GALLERY_PATH . 'video/' . $imgRow['cmsgi_file_path' . $_SESSION['lang_fld_prefix']]);
            unlink(IMAGE_GALLERY_PATH . 'thumb/' . $imgRow['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']]);
            $db->query("delete from tbl_cms_gallery_items where cmsgi_id=" . $_GET['delete']);
            $msg->addMsg("Video Deleted Successfully.");
            $url = 'cms-page-image-gallery.php?editcontent=' . $editcontent . '&hide=' . $hide . '&video_gal=' . $video_gal;
        }
        redirectUser($url);
    } else {
        die('Unauthorized Access.');
    }
}
$imageGalery = new SearchBase('tbl_cms_gallery_items', 'cmsgi_id');
$imageGalery->addCondition('cmsgi_gallery_id', '=', $gal_id);
$imageGalery->addOrder('cmsgi_display_order', 'asc');
$img_gal_listing = $imageGalery->getResultSet();
?>
<?php
if ($_REQUEST['img_gal'] != "") {
    $imgGal = t_lang('M_TXT_IMAGE_GALLERY');
} else {
    $imgGal = t_lang('M_TXT_VIDEO_GALLERY');
}
$arr_bread = [
    'index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'cms-page-listing.php' => t_lang('M_TXT_PAGES'),
    'cms-page-detail.php?editcontent=' . $editcontent . '&hide=' . $hide => t_lang('M_TXT_PAGE_DETAIL'),
    '' => $imgGal
];
?>
</div></td>
<td class="right-portion"><?php echo getAdminBreadCrumb($arr_bread); ?>
    <div class="div-inline">
        <div class="page-name"><?php
            if ($_REQUEST['img_gal'] != "") {
                echo t_lang('M_TXT_IMAGE_GALLERY');
            } else {
                echo t_lang('M_TXT_VIDEO_GALLERY');
            }
            ?> 
            <?php if (checkAdminAddEditDeletePermission(1, '', 'add')) { ?> 
                <ul class="actions right">
                    <li class="droplink">
                        <a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
                        <div class="dropwrap">
                            <ul class="linksvertical">
                                <?php if ($_REQUEST['img_gal'] != "") { ?> 
                                    <li>
                                        <a href="cms-page-image-gallery.php?editcontent=<?php echo $editcontent; ?>&hide=<?php echo $hide; ?>&add=new<?php echo '&img_gal=' . $gal_id; ?>"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_IMAGE'); ?> </a>
                                    </li>
                                <?php } if ($_REQUEST['video_gal'] != "") { ?> 
                                    <li>
                                        <a href="cms-page-image-gallery.php?editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&add=new<?php echo '&video_gal=' . $gal_id; ?>"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_VIDEO'); ?></a>
                                    </li>
                                <?php } ?>
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
    <?php if (isset($_GET['edit']) || isset($_GET['add'])) { ?>
        <div class="box">
            <?php if (isset($_GET['edit'])) { ?>
                <div class="title"><?php
                    if ($_REQUEST['img_gal'] != "") {
                        echo t_lang('M_TXT_EDIT') . ' ' . t_lang('M_TXT_IMAGE_GALLERY');
                    } else {
                        echo t_lang('M_TXT_EDIT') . ' ' . t_lang('M_TXT_VIDEO_GALLERY');
                    }
                    ?> </div>
            <?php } elseif (isset($_GET['add'])) { ?>
                <div class="title"><?php
                    if ($_REQUEST['img_gal'] != "") {
                        echo t_lang('M_TXT_ADD_NEW') . ' ' . t_lang('M_TXT_IMAGE_GALLERY');
                    } else {
                        echo t_lang('M_TXT_ADD_NEW') . ' ' . t_lang('M_TXT_VIDEO_GALLERY');
                    }
                    ?></div>
            <?php } ?>
            <?php
            if ((checkAdminAddEditDeletePermission(1, '', 'add')) || (checkAdminAddEditDeletePermission(1, '', 'edit'))) {
                echo '<div class="content">' . $frm->getFormHtml() . '</div>';
            } else {
                die('Unauthorized Access.');
            }
            echo '</div>';
        } else {
            ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    //Table DND call
                    $('#imageGalery-listing').tableDnD({
                        onDrop: function (table, row) {
                            var order = $.tableDnD.serialize('id');
                            callAjax('cms-ajax.php', order + '&mode=REORDER_CMS_IMAGES', function (t) {
                                $.facebox(t);
                            });
                        }
                    });
                });
            </script>
            <div id="msgbox"></div>
            <table id="imageGalery-listing" class="tbl_data" width="100%">
                <thead>
                    <tr>      
                        <th><?php echo t_lang('M_TXT_THUMB'); ?> <?php echo t_lang('M_TXT_IMAGE'); ?></th>
                        <th><?php
                            if ($_REQUEST['img_gal'] != "") {
                                echo t_lang('M_TXT_IMAGE');
                            } else {
                                echo t_lang('M_TXT_VIDEO');
                            }
                            ?> <?php echo t_lang('M_FRM_TITLE'); ?></th>
                        <th><?php
                            if ($_REQUEST['img_gal'] != "") {
                                echo t_lang('M_TXT_IMAGE');
                            } else {
                                echo t_lang('M_TXT_VIDEO');
                            }
                            ?> <?php echo t_lang('M_TXT_DESCRIPTION'); ?></th>
                        <th><?php echo t_lang('M_TXT_ACTION'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $db->fetch($img_gal_listing)) {
                        ?>
                        <tr id="<?php echo $row['cmsgi_id'] ?>">
                            <td>
                                <?php if ($row['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']] != "") { ?>
                                    <img src="<?php echo IMAGE_GALLERY_PATH . "thumb/" . $row['cmsgi_thumb_path' . $_SESSION['lang_fld_prefix']]; ?>" width="30" height="30">
                                <?php } else { ?>
                                    <img src="../images/no_img.jpg" width="30" height="30">
                                <?php } ?>
                            </td>
                            <td><?php
                                echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['cmsgi_title'] . '<br/>';
                                echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['cmsgi_title_lang1'];
                                ?> </td>
                            <td><?php echo subStringByWords(strip_tags($row['cmsgi_desc' . $_SESSION['lang_fld_prefix']]), 30); ?></td>                        
                            <td> 
                                <ul class="actions">
                                    <?php if ($_REQUEST['img_gal'] != "") { ?>
                                        <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                                            <li><a href="cms-page-image-gallery.php?editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&img_gal=<?php echo $gal_id ?>&edit=<?php echo $row['cmsgi_id']; ?>" title="<?php echo t_lang('M_TXT_EDIT') . ' ' . t_lang('M_TXT_IMAGE'); ?>"><i class="ion-edit icon"></i></a></li>
                                        <?php } ?>
                                        <?php if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) { ?>
                                            <li><a href="cms-page-image-gallery.php?editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&img_gal=<?php echo $gal_id; ?>&delete=<?php echo $row['cmsgi_id']; ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a>
                                            <?php } ?>
                                        <?php } if ($_REQUEST['video_gal'] != "") { ?>	
                                            <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>		
                                            <li><a href="cms-page-image-gallery.php?editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide ?>&video_gal=<?php echo $gal_id ?>&edit=<?php echo $row['cmsgi_id']; ?>" title="<?php echo t_lang('M_TXT_EDIT') . ' ' . t_lang('M_TXT_VIDEO'); ?>"><i class="ion-edit icon"></i></a></li>
                                        <?php } ?>
                                        <?php if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) { ?>
                                            <li><a href="cms-page-image-gallery.php?editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&video_gal=<?php echo $gal_id ?>&delete=<?php echo $row['cmsgi_id']; ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a>
                                                <?php } ?>
                                            <?php } ?>
                                </ul>
                            </td>
                        </tr>
                        <?php
                    }
                    if ($db->total_records($img_gal_listing) == 0) {
                        echo '<tr ><td colspan="4">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        <?php } ?>
</td>
<?php
require_once './footer.php';
