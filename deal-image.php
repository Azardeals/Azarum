<?php

require_once './application-top.php';
$get = getQueryStringData();
$actual_image_path = '';
if ($_GET['mode'] == 'userImages') {
    $rs = $db->query("select user_image from tbl_users where user_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        $name = $row['user_image'];
        $actual_image_path = USER_IMAGES_PATH . $name;
        $img = new imageResize(USER_IMAGES_PATH . $name);
        $img->setMaxDimensions(53, 53);
        $img->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'companyLogo') {
    $rs = $db->query("select company_logo" . $_SESSION['lang_fld_prefix'] . " from tbl_companies where company_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        $name = $row['company_logo' . $_SESSION['lang_fld_prefix']];
        if ($name == "") {
            $name = 'defaultLogo.jpg';
        }
        $actual_image_path = COMPANY_LOGO_PATH . $name;
        $img = new imageResize(COMPANY_LOGO_PATH . $name);
        $img->setMaxDimensions(500, 500);
        $img->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'thumbImages') {
    $rs = $db->query("select dimg_name from tbl_deals_images where dimg_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        if ($row['dimg_name'] == "") {
            $name = 'no-image.jpg';
        } else {
            $name = $row['dimg_name'];
            $actual_image_path = DEAL_IMAGES_PATH . $name;
            if (!file_exists(DEAL_IMAGES_PATH . $name)) {
                $name = 'no-image.jpg';
            }
        }
        $img = new imageResize(DEAL_IMAGES_PATH . $name);
        $img->setMaxDimensions(600, 450);
        $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'mainthumbImages') {
    $rs = $db->query("select deal_img_name from tbl_deals where deal_id=" . $get['id']);
    if ($row = $db->fetch($rs)) {
        if ($row['deal_img_name'] == "") {
            $name = 'no-image.jpg';
        } else {
            $name = $row['deal_img_name'];
            $actual_image_path = DEAL_IMAGES_PATH . $name;
            if (!file_exists(DEAL_IMAGES_PATH . $name)) {
                $name = 'no-image.jpg';
            }
        }
        $img = new imageResize(DEAL_IMAGES_PATH . $name);
        $img->setMaxDimensions(600, 450);
        $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'homeSliderImages') {
    $rs = $db->query("select dimg_name from tbl_deals_images where dimg_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        if ($row['dimg_name'] == "") {
            $name = 'no-image.jpg';
        } else {
            $name = $row['dimg_name'];
            $actual_image_path = DEAL_IMAGES_PATH . $name;
            if (!file_exists(DEAL_IMAGES_PATH . $name)) {
                $name = 'no-image.jpg';
            }
        }
        $img = new imageResize(DEAL_IMAGES_PATH . $name);
        $img->setMaxDimensions(700, 525);
        $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'homeSliderMainImage') {
    $rs = $db->query("select deal_img_name from tbl_deals where deal_id=" . $get['id']);
    if ($row = $db->fetch($rs)) {
        if ($row['deal_img_name'] == "") {
            $name = 'no-image.jpg';
        } else {
            $name = $row['deal_img_name'];
            $actual_image_path = DEAL_IMAGES_PATH . $name;
            if (!file_exists(DEAL_IMAGES_PATH . $name)) {
                $name = 'no-image.jpg';
            }
        }
        $img = new imageResize(DEAL_IMAGES_PATH . $name);
        $img->setMaxDimensions(700, 525);
        $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'charitythumbImages') {
    $rs = $db->query("select charity_logo from tbl_company_charity where charity_id 	=" . $_GET['charity']);
    if ($row = $db->fetch($rs)) {
        $name = $row['charity_logo'];
        $actual_image_path = DEAL_IMAGES_PATH . $name;
        if ($row['charity_logo'] == "") {
            $img = new imageResize('images/no-image.jpg');
            $img->setMaxDimensions(100, 100);
            $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_ADDSPACE);
            $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        } else {
            $img = new imageResize(CHARITY_IMAGES_PATH . $name);
            $img->setMaxDimensions(100, 100);
            $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_ADDSPACE);
            $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        }
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'newsthumbImages') {
    $rs = $db->query("select news_image from tbl_news where news_id 	=" . $_GET['news']);
    if ($row = $db->fetch($rs)) {
        $name = $row['news_image'];
        $actual_image_path = DEAL_IMAGES_PATH . $name;
        if ($row['news_image'] == "") {
            $img = new imageResize('images/no-image.jpg');
            $img->setMaxDimensions(100, 100);
            $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_ADDSPACE);
            $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        } else {
            $img = new imageResize(NEWS_IMAGES_PATH . $name);
            $img->setMaxDimensions(100, 100);
            $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_ADDSPACE);
            $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        }
        showImage($img, $actual_image_path);
        exit;
    }
}
if ($_GET['mode'] == 'companyImages') {
    $rs = $db->query("select company_logo" . $_SESSION['lang_fld_prefix'] . " from tbl_companies where company_id =" . $_GET['company']);
    if ($row = $db->fetch($rs)) {
        $name = $row['company_logo' . $_SESSION['lang_fld_prefix']];
        $actual_image_path = COMPANY_LOGO_PATH . $name;
        $img = new imageResize(COMPANY_LOGO_PATH . $name);
        $img->setMaxDimensions(250, 90);
        $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_ADDSPACE);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);

        exit;
    }
}
if ($_GET['mode'] == 'categoryImages') {
    $rs = $db->query("select * from tbl_deal_categories where cat_id=" . $_GET['cat']);
    if ($row = $db->fetch($rs)) {
        if ($row['cat_image'] == "") {
            $name = 'no-image.jpg';
        } else {
            $name = $row['cat_image'];
            $actual_image_path = CATEGORY_IMAGES_PATH . $name;
        }
        $img = new imageResize(CATEGORY_IMAGES_PATH . $name);
        $img->setMaxDimensions(229, 105);
        $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);

        exit;
    }
}
if ($_GET['mode'] == 'dealcatimages') {
    $rs = $db->query("select * from tbl_deal_categories where cat_id=" . $_GET['cat']);
    if ($row = $db->fetch($rs)) {
        if ($row['cat_image'] == "") {
            $name = 'no-image.jpg';
        } else {
            $name = $row['cat_image'];
            $actual_image_path = CATEGORY_IMAGES_PATH . $name;
        }
        $img = new imageResize(CATEGORY_IMAGES_PATH . $name);
        $img->setMaxDimensions(85, 101);
        $img->setResizeMethod(ImageResize::IMG_RESIZE_EXTRA_CROP);
        $img->setCropMethod(imageResize::IMG_CROP_BOTH);
        showImage($img, $actual_image_path);

        exit;
    }
}
if ($_GET['mode'] == 'images') {
    $rs = $db->query("select dimg_name from tbl_deals_images where dimg_id=" . $_GET['id']);
    if ($row = $db->fetch($rs)) {
        $name = $row['dimg_name'];
        $actual_image_path = DEAL_IMAGES_PATH . $name;
        $img = new imageResize(DEAL_IMAGES_PATH . $name);
        $img->setMaxDimensions(420, 280);
        $img->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);
        showImage($img, $actual_image_path);

        exit;
    }
} else {
    if (!is_numeric($get['id'])) {
        $name = 'no-image.jpg';
    } else {
        $rs = $db->query("select deal_img_name from tbl_deals where deal_id=" . $get['id']);
        if (!$row = $db->fetch($rs)) {
            $name = 'no-image.jpg';
        } else {
            $name = $row['deal_img_name'];
            $actual_image_path = DEAL_IMAGES_PATH . $name;
        }
    }
    if (!file_exists(DEAL_IMAGES_PATH . $name) || $name == '') {
        $name = 'no-image.jpg';
    }
    $img = new imageResize(DEAL_IMAGES_PATH . $name);
    switch (strtoupper($get['type'])) {
        case 'SIDE':
            $img->setMaxDimensions(204, 137);
            break;
        case 'OTHER':
            $img->setMaxDimensions(215, 137);
            break;
        case 'LIST':
            $img->setMaxDimensions(232, 209);
            break;
        case 'CATEGORY':
            $img->setMaxDimensions(79, 107);
            break;
        case 'BUYDEAL':
            $img->setMaxDimensions(175, 86);
            break;
        case 'THUMB':
            $img->setMaxDimensions(134, 79);
            break;
        case 'EMAILMAIN':
            $img->setMaxDimensions(398, 300);
            break;
        case 'EMAILUPCOMING':
            $img->setMaxDimensions(270, 150);
            break;
        case 'VOUCHERIMAGES':
            $img->setMaxDimensions(600, 450);
            break;
        default:
            $img->setMaxDimensions(329, 422);
            break;
    }
    $img->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);
    showImage($img, $actual_image_path);
    exit;
}