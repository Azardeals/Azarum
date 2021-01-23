<?php
require_once './application-top.php';
checkAdminPermission(1);
require_once './header.php';
if ($_REQUEST['faq_category_id'] != "" && $_REQUEST['editcontent'] != "" && $_REQUEST['hide'] != "") {
    $editcontent = $_REQUEST['editcontent'];
    $hide = $_REQUEST['hide'];
    $faq_category_id = $_REQUEST['faq_category_id'];
}
if ($_REQUEST['img_gal'] != "") {
    $img_gal = $_REQUEST['img_gal'];
    $gal_id = $img_gal;
    $gal_name = t_lang('M_TXT_IMAGE_GALLERY');
    $gal_name1 = 'M_FRM_SELECT_IMAGE_THUMB_IMAGE';
}
if ($_REQUEST['video_gal'] != "") {
    $video_gal = $_REQUEST['video_gal'];
    $gal_id = $video_gal;
    $gal_name = t_lang('M_TXT_VIDEO_GALLERY');
    $gal_name1 = 'M_FRM_SELECT_VIDEO_THUMB_IMAGE';
}
$edit = $_REQUEST['edit'];
$frm = new Form('cms_faq_img_galery', 'cms_faq_img_galery');
$frm->setAction('?');
$frm->setTableProperties(' border="0" cellspacing="0" cellpadding="0" class="tbl_form" width="100%"');
$frm->setJsErrorDisplay('afterfield');
$frm->setFieldsPerRow(1);
$frm->captionInSameCell(false);
if ($_REQUEST['img_gal'] != "") {
    $frm->addRequiredField('M_FRM_IMAGE_TITLE', 'cmsfgi_title', '', 'cmsfgi_title', 'class="input"');
    $frm->addTextArea('M_FRM_IMAGE_DESCRIPTION', 'cmsfgi_desc', '', 'cmsfgi_desc', 'class="input"');
    if ($edit != "") {
        $frm->addFileUpload('M_FRM_SELECT_IMAGE_FILE', 'cmsfgi_file_path', 'cmsfgi_file_path', '');
    } else {
        $frm->addFileUpload('M_FRM_SELECT_IMAGE_FILE', 'cmsfgi_file_path', 'cmsfgi_file_path', '')->requirements()->setRequired();
    }
}
if ($_REQUEST['video_gal'] != "") {
    $frm->addRequiredField('M_FRM_VIDEO_TITLE', 'cmsfgi_title', '', 'cmsfgi_title', 'class="input"');
    $frm->addTextArea('M_FRM_VIDEO_DESCRIPTION', 'cmsfgi_desc', '', 'cmsfgi_desc', 'class="input"');
    if ($edit != "") {
        $frm->addFileUpload('M_FRM_SELECT_VIDEO_FILE', 'cmsfgi_file_path', 'cmsfgi_file_path', '');
    } else {
        $frm->addFileUpload('M_FRM_SELECT_VIDEO_FILE', 'cmsfgi_file_path', 'cmsfgi_file_path', '')->requirements()->setRequired();
    }
}
if ($edit != "") {
    $getImg = $db->query("select * from tbl_cms_faq_gallery_items where cmsfgi_id='" . $edit . "'");
    $imgRow = $db->fetch($getImg);
    if ($imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']] != "" && $_REQUEST['img_gal'] != "") {
        $frm->addHTML('', '', '<img src="' . FAQ_GALLERY_URL . $imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']] . '" width="75" height="75">', false);
    }
    if ($imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']] != "" && $_REQUEST['video_gal'] != "") {
        $frm->addHTML('', '', 'Video Exist ' . $imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']], false);
    }
}
if ($edit != "") {
    $frm->addFileUpload($gal_name1, 'cmsfgi_thumb_path', 'cmsfgi_thumb_path', '');
} else {
    $frm->addFileUpload($gal_name1, 'cmsfgi_thumb_path', 'cmsfgi_thumb_path', '')->requirements()->setRequired();
}
if ($edit != "") {
    if ($imgRow['cmsfgi_thumb_path' . $_SESSION['lang_fld_prefix']] != "") {
        $frm->addHTML('', '', '<img src="' . FAQ_GALLERY_URL . 'thumb/' . $imgRow['cmsfgi_thumb_path' . $_SESSION['lang_fld_prefix']] . '" width="50" height="50">', false);
    }
}
if ($_REQUEST['img_gal'] != "") {
    $frm->addTextBox('M_FRM_LINK_URL', 'cmsfgi_url', '', 'cmsfgi_url', 'class="input"');
    $frm->addSelectBox('M_FRM_LINK_TARGET', 'cmsfgi_link_target', ['_self' => 'Current Window', '_blank' => 'New Window'], '', '', 'Select');
}
$frm->addHiddenField('', 'cmsfgi_id', '', '', 'readonly="readonly"');
$frm->addHiddenField('', 'cmsfgi_gallery_id', $gal_id, '', 'readonly="readonly"');
$frm->addHiddenField('', 'img_gal', $img_gal, '', 'readonly="readonly"');
$frm->addHiddenField('', 'video_gal', $video_gal, '', 'readonly="readonly"');
$frm->addHiddenField('', 'editcontent', $editcontent, '', 'readonly="readonly"');
$frm->addHiddenField('', 'faq_category_id', $faq_category_id, '', 'readonly="readonly"');
$frm->addHiddenField('', 'hide', $hide, '', 'readonly="readonly"');
$frm->addSubmitButton('', 'btn_submit', 'Add', '', ' class="inputbuttons"');
updateFormLang($frm);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post = getPostedData();
    $record = new TableRecord('tbl_cms_faq_gallery_items');
    $arr_lang_independent_flds = ['cmsfgi_url', 'cmsfgi_gallery_id', 'cmsfgi_id', 'img_gal', 'video_gal', 'editcontent', 'hide', 'btn_submit'];
    assignValuesToTableRecord($record, $arr_lang_independent_flds, $post);
    ///////////////////image///////////////////////
    if (!$_FILES['cmsfgi_file_path']['name'] == "") {
        if ($_REQUEST['img_gal'] != "") {
            $check = checkImageTypes($_FILES['cmsfgi_file_path']['type']);
        }
        if ($_REQUEST['video_gal'] != "") {
            $accepted_files = ['.mov', '.flv', '.FLV'];
            $ext = strtolower(strrchr($_FILES['cmsfgi_file_path']['name'], '.'));
            $check = in_array($ext, $accepted_files);
        }
        if ($check) {
            $item_path = time() . "_" . $_FILES['cmsfgi_file_path']['name'];
            if ($_REQUEST['img_gal'] != "") {
                if (!move_uploaded_file($_FILES['cmsfgi_file_path']['tmp_name'], FAQ_GALLERY_PATH . $item_path)) {
                    die('Could not save file.');
                }
                $img = new ImageResize(FAQ_GALLERY_PATH . $item_path);
                $img->setMaxDimensions(200, 200);
                $img->saveImage(FAQ_GALLERY_PATH . "big/" . $item_path);
            }
            if ($_REQUEST['video_gal'] != "") {
                move_uploaded_file($_FILES['cmsfgi_file_path']['tmp_name'], FAQ_GALLERY_PATH . "video/" . $item_path) or $error = "Not A ";
            }
            $record->setFldValue('cmsfgi_file_path' . $_SESSION['lang_fld_prefix'], $item_path);
            if ($post['cmsfgi_id'] > 0) {
                $getImg = $db->query("select * from tbl_cms_faq_gallery_items where 
					cmsfgi_id='" . $post['cmsfgi_id'] . "'");
                $imgRow = $db->fetch($getImg);
                if ($_REQUEST['img_gal'] != "") {
                    unlink(FAQ_GALLERY_PATH . $imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']]);
                    unlink(FAQ_GALLERY_PATH . 'big/' . $imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']]);
                }
                if ($_REQUEST['video_gal'] != "") {
                    unlink(FAQ_GALLERY_PATH . 'video/' . $imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']]);
                }
            }
        } else {
            if ($_REQUEST['img_gal'] != "") {
                $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' . $editcontent . '&hide=' . $hide . '&img_gal=' . $post['cmsfgi_gallery_id'];
            }
            if ($_REQUEST['video_gal'] != "") {
                $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' . $editcontent . '&hide=' . $hide . '&video_gal=' . $post['cmsfgi_gallery_id'];
            }
            $msg->addError("could not update video file extension is wrong .");
            header("Location:$url");
            exit;
        }
    }
///////////////////////////////////////////////////////
    ///////////////////image-thumb///////////////////////
    if ($_FILES['cmsfgi_thumb_path']['name'] != "") {
        if (checkImageTypes($_FILES['cmsfgi_thumb_path']['type'])) {
            $item_path = time() . "_thumb_" . $_FILES['cmsfgi_thumb_path']['name'];
            if (!move_uploaded_file($_FILES['cmsfgi_thumb_path']['tmp_name'], FAQ_GALLERY_PATH . "thumb/" . $item_path)) {
                die('Could not save file.');
            }
            $img = new ImageResize(FAQ_GALLERY_PATH . "thumb/" . $item_path);
            ImageResize::IMG_RESIZE_EXTRA_ADDSPACE;
            $img->setMaxDimensions(70, 70);
            $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_ADDSPACE);
            $img->saveImage(FAQ_GALLERY_PATH . "thumb/" . $item_path);
            $record->setFldValue('cmsfgi_thumb_path' . $_SESSION['lang_fld_prefix'], $item_path);
            if ($post['cmsfgi_id'] > 0) {
                $getImg = $db->query("select * from tbl_cms_faq_gallery_items  where cmsfgi_id='" . $post['cmsfgi_id'] . "'");
                $imgRow = $db->fetch($getImg);
                unlink(FAQ_GALLERY_PATH . 'thumb/' . $imgRow['cmsfgi_thumb_path' . $_SESSION['lang_fld_prefix']]);
            }
        } else {
            if ($_REQUEST['img_gal'] != "") {
                $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' . $editcontent . '&hide=' . $hide . '&img_gal=' . $post['cmsfgi_gallery_id'];
            }
            if ($_REQUEST['video_gal'] != "") {
                $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' . $editcontent . '&hide=' . $hide . '&video_gal=' . $post['cmsfgi_gallery_id'];
            }
            $msg->addError("could not update image file ext is wrong .");
            header("Location:$url");
            exit;
        }
    }
    ///////////////////////////////////////////////////////
    if ($post['cmsfgi_id'] > 0) {
        if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
            if ($record->update('cmsfgi_id=' . $post['cmsfgi_id'])) {
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
        $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' .
                $editcontent . '&hide=' . $hide . '&img_gal=' . $post['cmsfgi_gallery_id'];
    }
    if ($_REQUEST['video_gal'] != "") {
        $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' .
                $editcontent . '&hide=' . $hide . '&video_gal=' . $post['cmsfgi_gallery_id'];
    }
    header("Location:$url");
    exit;
}
if ($_GET['edit'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $record = new TableRecord('tbl_cms_faq_gallery_items');
        $record->loadFromDb('cmsfgi_id=' . $_GET['edit'], true);
        $row = $record->getFlds();
        $row['btn_submit'] = t_lang('M_TXT_UPDATE');
        fillForm($frm, $row);
        $msg->addMsg(t_lang('M_TXT_Edit_THE_VALUES_AND_UPDATE'));
    } else {
        die('Unauthorized Access.');
    }
}
if (isset($_GET['crnt']) && $_GET['crnt'] != "") {
    if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) {
        $updateCurrentValue = $db->query("update tbl_cms_faq_gallery_items set cmsgi_default=0  where 
		cmsfgi_gallery_id=" . $gal_id);
        $updateCurrentValue = $db->query("update tbl_cms_faq_gallery_items set cmsgi_default=1  where 
		cmsfgi_id=" . $_GET['crnt'] . " AND cmsfgi_gallery_id=" . $gal_id);
        $msg->addMsg('Default value is set.');
    } else {
        die('Unauthorized Access.');
    }
}
if ($_GET['delete'] > 0) {
    if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) {
        $getImg = $db->query("select * from tbl_cms_faq_gallery_items where  cmsfgi_id='" . $_GET['delete'] . "'");
        $imgRow = $db->fetch($getImg);
        if ($_REQUEST['img_gal'] != "") {
            unlink(FAQ_GALLERY_PATH . $imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']]);
            unlink(FAQ_GALLERY_PATH . 'thumb/' . $imgRow['cmsfgi_thumb_path' . $_SESSION['lang_fld_prefix']]);
            $db->query("delete from tbl_cms_faq_gallery_items where cmsfgi_id=" . $_GET['delete']);
            $msg->addMsg("Image Deleted Successfully.");
            $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' .
                    $editcontent . '&hide=' . $hide . '&img_gal=' . $img_gal;
        } else {
            unlink(FAQ_GALLERY_PATH . 'video/' . $imgRow['cmsfgi_file_path' . $_SESSION['lang_fld_prefix']]);
            $db->query("delete from tbl_cms_faq_gallery_items where cmsfgi_id=" . $_GET['delete']);
            $msg->addMsg("Video Deleted Successfully.");
            $url = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id . '&editcontent=' . $editcontent . '&hide=' . $hide . '&video_gal=' . $video_gal;
        }
        header("Location:$url");
        exit;
    } else {
        die('Unauthorized Access.');
    }
}
$imageGalery = new SearchBase('tbl_cms_faq_gallery_items', 'cmsfgi_id');
$imageGalery->addCondition('cmsfgi_gallery_id', '=', $gal_id);
$imageGalery->addOrder('cmsfgi_display_order', 'asc');
$img_gal_listing = $imageGalery->getResultSet();
if ($_REQUEST['img_gal'] != "") {
    $imgGal = t_lang('M_TXT_IMAGE_GALLERY');
} else {
    $imgGal = t_lang('M_TXT_VIDEO_GALLERY');
}
$breadQry = $db->query("select * from tbl_cms_faq_categories where category_id=$faq_category_id");
$breadrow = $db->fetch($breadQry);
$arr_bread = ['index.php' => '<img class="home" alt="Home" src="images/home-icon.png">',
    'javascript:void(0);' => t_lang('M_TXT_CMS'),
    'faq-categories.php' => t_lang('M_TXT_FAQ'),
    'cms-faq-listing.php?faq_category_id=' . $faq_category_id => $breadrow['category_name'],
    'cms-faq-detail.php?faq_category_id=' . $faq_category_id . '&editcontent=' . $editcontent . '&hide=' . $hide => $breadrow['category_name'] . ' Detail Page',
    '' => $breadrow['category_name'] . ' ' . $imgGal
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
                                        <a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&add=new<?php echo '&img_gal=' . $gal_id; ?>"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_IMAGE'); ?> </a>
                                    </li>
                                    <?php
                                }
                                if ($_REQUEST['video_gal'] != "") {
                                    ?>
                                    <li>
                                        <a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&add=new<?php echo '&video_gal=' . $gal_id; ?>"><?php echo t_lang('M_TXT_ADD_NEW'); ?> <?php echo t_lang('M_TXT_VIDEO'); ?></a>
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
            <div class="title-msg"> <?php echo t_lang('M_TXT_SYSTEM_MESSAGES'); ?> <a class="btn gray fr" href="javascript:void(0);" onclick="$(this).closest('#messages').hide(); return false;"><?php echo t_lang('M_TXT_HIDE'); ?></a></div>
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
            <div class="box"><div class="title"> <?php
                    if ($_REQUEST['img_gal'] != "") {
                        echo t_lang('M_TXT_IMAGE_GALLERY');
                    } else {
                        echo t_lang('M_TXT_VIDEO_GALLERY');
                    }
                    ?> </div><div class="content">		
                    <div class="gap">&nbsp;</div>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Table DND call
                            $('#FaqimageGalery-listing').tableDnD({
                                onDrop: function (table, row) {
                                    var order = $.tableDnD.serialize('id');
                                    callAjax('cms-ajax.php', order + '&mode=REORDER_CMS_FAQ_IMAGES', function (t) {
                                        $.facebox(t);
                                    });
                                }
                            });
                        });
                    </script>
                    <div id="msgbox"></div>
                    <table id="FaqimageGalery-listing" class="tbl_data" width="100%">
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
                                <th><?php echo t_lang('M_TXT_SET_DEFAULT'); ?></th>
                                <th><?php echo t_lang('M_TXT_ACTION'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $db->fetch($img_gal_listing)) {
                                $Count_rows = mysqli_num_rows($img_gal_listing);
                                if ($Count_rows == 1) {
                                    $db->query("update tbl_cms_faq_gallery_items set cmsgi_default=1  where cmsfgi_id=" . $row['cmsfgi_id']);
                                }
                                ?>
                                <tr id="<?php echo $row['cmsfgi_id'] ?>">
                                    <td><img src="<?php echo FAQ_GALLERY_URL . "thumb/" . $row['cmsfgi_thumb_path' . $_SESSION['lang_fld_prefix']]; ?>" width="30" height="30"></td>
                                    <td><?php
                                        echo '<strong>' . $arr_lang_name[0] . '</strong>' . ' ' . $row['cmsfgi_title'] . '<br/>';
                                        echo '<strong>' . $arr_lang_name[1] . '</strong>' . ' ' . $row['cmsfgi_title_lang1'];
                                        ?></td>
                                    <td><?php echo subStringByWords(strip_tags($row['cmsfgi_desc' . $_SESSION['lang_fld_prefix']]), 30); ?></td>                        
                                    <td><?php
                                        if ($row['cmsgi_default'] == 1) {
                                            echo t_lang("M_TXT_DEFAULT");
                                        }
                                        if ($row['cmsgi_default'] == 0) {
                                            $cmsfgi_id = $row['cmsfgi_id'];
                                            $link = 'cms-faq-image-gallery.php?faq_category_id=' . $faq_category_id .
                                                    '&editcontent=' . $editcontent . '&hide=' . $hide . '&img_gal=' . $gal_id . '&crnt='
                                                    . $cmsfgi_id;
                                            echo '<a href="' . $link . '" >' . t_lang('M_TXT_MAKE_DEFAULT') . '</a>';
                                        }
                                        ?></td>  
                                    <td><ul class="listing_option actions">
                                            <?php if ($_REQUEST['img_gal'] != "") { ?>
                                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>
                                                    <li><a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&img_gal=<?php echo $gal_id; ?>&edit=<?php echo $row['cmsfgi_id']; ?>" title="<?php echo t_lang('M_TXT_EDIT') . ' ' . t_lang('M_TXT_IMAGE'); ?>"><i class="ion-edit icon"></i></a></li>
                                                <?php } ?>
                                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'delete'))) { ?>
                                                    <li>
                                                        <a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?echo $editcontent; ?>&hide=<?php echo $hide; ?>&img_gal=<?php echo $gal_id ?>&delete=<?php echo $row['cmsfgi_id']; ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            if ($_REQUEST['video_gal'] != "") {
                                                ?>
                                                <?php if ((checkAdminAddEditDeletePermission(1, '', 'edit'))) { ?>							
                                                    <li><a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&video_gal=<?php echo $gal_id ?>&edit=<?php echo $row['cmsfgi_id']; ?>" title="<?php echo t_lang('M_TXT_EDIT') . ' ' . t_lang('M_TXT_VIDEO'); ?>"><i class="ion-edit icon"></i></a></li>
                                                    <li>
                                                        <a href="cms-faq-image-gallery.php?faq_category_id=<?php echo $faq_category_id; ?>&editcontent=<?php echo $editcontent ?>&hide=<?php echo $hide; ?>&video_gal=<?php echo $gal_id; ?>&delete=<?php echo $row['cmsfgi_id']; ?>" onClick="requestPopup(this, '<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD'); ?>', 1);" title="<?php echo t_lang('M_TXT_DELETE'); ?>"><i class="ion-android-delete icon"></i></a></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                        </ul></td>
                                </tr>
                                <?php
                            }
                            if ($db->total_records($img_gal_listing) == 0) {
                                echo '<tr ><td colspan="5">' . t_lang('M_TXT_NO_RECORD_FOUND') . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
</td> 
<?php
require_once './footer.php';

