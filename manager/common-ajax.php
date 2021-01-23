<?php

require_once './application-top.php';
checkAdminPermission(2);
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case 'ISPARENTCATEGORY':
        echo isParentCategory($post['category']);
        break;
    case 'DELETECATEGORY':
        echo canDeleteCategory($post['category']);
        break;
    case 'CHKPEARMAILEXT':
        if (((int) $_POST['val']) === 2) {
            if (!checkPearMailExt()) {
                die(t_lang('M_TXT_PEAR_MAIL_NOT_INSTALLED'));
            } else {
                die("1");
            }
        }
        break;
    case 'SAVEADMINIMAGES':
        print_r($_POST);
        break;
}
