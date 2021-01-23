<?php

require_once './application-top.php';
checkAdminPermission(1);
$post = getPostedData();
$get = getQueryStringData();
$mode = (isset($post['mode'])) ? $post['mode'] : $get['mode'];
switch (strtoupper($mode)) {
    case 'REORDER_CMS_CONTENT' :
        $i = 1;
        $record = new TableRecord('tbl_cms_contents');
        foreach ($post['cms-listing'] as $key => $value) {
            $record->setFldValue('cmsc_display_order', $i);
            $record->update('cmsc_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['cms-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_FAQ_CATEGORY' :
        $i = 1;
        $record = new TableRecord('tbl_cms_faq_categories');
        foreach ($post['category-listing'] as $key => $value) {
            $record->setFldValue('category_display_order', $i);
            $record->update('category_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['category-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_CMS_FAQ_GALLERY' :
        $i = 1;
        $record = new TableRecord('tbl_cms_faq_gallery');
        foreach ($post['cms-listing'] as $key => $value) {
            $record->setFldValue('cmsfg_display_order', $i);
            $record->update('cmsfg_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['cms-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_CMS_FAQ_IMAGES' :
        $i = 1;
        $record = new TableRecord('tbl_cms_faq_gallery_items');
        foreach ($post['FaqimageGalery-listing'] as $key => $value) {
            $record->setFldValue('cmsfgi_display_order', $i);
            $record->update('cmsfgi_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['FaqimageGalery-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_CMS_IMAGES' :
        $i = 1;
        $record = new TableRecord('tbl_cms_gallery_items');
        foreach ($post['imageGalery-listing'] as $key => $value) {
            $record->setFldValue('cmsgi_display_order', $i);
            $record->update('cmsgi_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['imageGalery-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_FAQ_QUES' :
        $i = 1;
        $record = new TableRecord('tbl_cms_faq');
        foreach ($post['cms-faq-listing'] as $key => $value) {
            $record->setFldValue('faq_display_order', $i);
            $record->update('faq_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['cms-faq-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_NAVIGATION' :
        $i = 1;
        $record = new TableRecord('tbl_nav_links');
        foreach ($post['nav-listing'] as $key => $value) {
            $record->setFldValue('nl_display_order', $i);
            $record->update('nl_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['nav-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_CATEGORY' :
        $i = 1;
        $record = new TableRecord('tbl_deal_categories');
        foreach ($post['category_listing'] as $key => $value) {
            $record->setFldValue('cat_display_order', $i);
            $record->update('cat_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['category_listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_ADV_CHILD_CATEGORIES':
        $i = 1;
        $record = new TableRecord('tbl_advertisement_child_categories');
        foreach ($post['category_listing'] as $key => $value) {
            $record->setFldValue('cat_display_order', $i);
            $record->update('cat_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['category_listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_ADV_PARENT_CATEGORIES':
        $i = 1;
        $record = new TableRecord('tbl_advertisement_parent_categories');
        foreach ($post['category_listing'] as $key => $value) {
            $record->setFldValue('pcat_display_order', $i);
            $record->update('pcat_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['category_listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_TRAINING' :
        $i = 1;
        $record = new TableRecord('tbl_training_video');
        foreach ($post['nav-listing'] as $key => $value) {
            $record->setFldValue('tv_display_order', $i);
            $record->update('tv_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['nav-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'REORDER_BANNER' :
        $i = 1;
        $record = new TableRecord('tbl_banner');
        foreach ($post['banner-listing'] as $key => $value) {
            $record->setFldValue('banner_display_order', $i);
            $record->update('banner_id=' . intval($value));
            $i++;
            if ($db->rows_affected() > 0) {
                $Count_rows = $db->rows_affected();
                $Count_rows++;
            }
        }
        $check_blank = [];
        $check_blank = $post['banner-listing'];
        if ($check_blank['1'] != "") {
            if ($i < 4) {
                echo t_lang('M_MSG_ONLY_ONE_RECORD_EXIST');
            } else if ($Count_rows == 0) {
                echo t_lang('M_TXT_PLEASE_MOVE_RECORD_FOR_REORDER');
            } else {
                echo t_lang('M_MSG_DISPLAY_ORDER_UPDATED');
            }
        } else {
            echo t_lang('M_TXT_NO_RECORD_FOUND');
        }
        break;
    case 'DISAPPROVECOMMENT' :
        $comment_id = $post['comment_id'];
        $db->query("UPDATE tbl_deal_discussions set comment_approved = 0 WHERE comment_id =$comment_id");
        break;
    case 'APPROVECOMMENT' :
        $comment_id = $post['comment_id'];
        $db->query("UPDATE tbl_deal_discussions set comment_approved = 1 WHERE comment_id =$comment_id");
        break;
    case 'PENDING' :
        $comment_id = $post['comment_id'];
        $db->query("UPDATE tbl_deal_discussions set comment_approved = 2 WHERE comment_id =$comment_id");
        break;
    case 'DISAPPROVEUSER' :
        $user_id = $post['user_id'];
        $db->query("UPDATE tbl_users set user_active = 0 WHERE user_id =$user_id");
        break;
    case 'APPROVEUSER' :
        $user_id = intval($post['user_id']);
        $db->query("UPDATE tbl_users set user_active = 1, user_email_verified = 1 WHERE user_id =$user_id");
        $default_value = (in_array(intval(CONF_DEFAULT_NOTIFICATION_STATUS), [0, 1], true)) ? CONF_DEFAULT_NOTIFICATION_STATUS : 1;
        $db->query("REPLACE INTO tbl_email_notification set 
                    en_user_id = $user_id, 
                    en_city_subscriber = $default_value,
                    en_favourite_merchant = $default_value,
                    en_near_to_expired = $default_value,
                    en_earned_deal_buck = $default_value,
                    en_friend_buy_deal = $default_value
                ");
        break;
}
