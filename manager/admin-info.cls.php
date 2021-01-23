<?php

class adminInfo
{

    function __construct()
    {
        require_once '../application-top.php';
        if (!is_numeric($_SESSION['admin_logged']['admin_id'])) {
            $this->error = 'Invalid Id';
            return false;
        }
    }

    public function leftPanel()
    {
        global $db;
        $srch = new SearchBase('tbl_admin', 'a');
        $srch->joinTable('tbl_admin_addresses', 'LEFT JOIN', 'a.admin_id = aa.admaddress_admin_id', 'aa');
        $srch->joinTable('tbl_states', 'LEFT JOIN', 'aa.admaddress_state = s.state_id', 's');
        $srch->joinTable('tbl_countries', 'LEFT JOIN', 's.state_country = c.country_id', 'c');
        $srch->addCondition('a.admin_id', '=', $_SESSION['admin_logged']['admin_id']);
        $rs_listing = $srch->getResultSet();
        $adminDetail = $db->fetch($rs_listing);
        $address = '';
        if ($adminDetail['admaddress_address1'] != "") {
            $address .= $adminDetail['admaddress_address1'] . ' , ';
        }
        if ($adminDetail['admaddress_address2'] != "") {
            $address .= $adminDetail['admaddress_address2'] . '<br/>';
        }
        if ($adminDetail['admaddress_zip'] != "") {
            $address .= $adminDetail['admaddress_zip'] . '<br/>';
        }
        if ($adminDetail['admaddress_city'] != "") {
            $address .= $adminDetail['admaddress_city'] . '<br/>';
        }
        if ($adminDetail['state_name'] != "") {
            $address .= $adminDetail['state_name'] . '<br/>';
        }
        if ($adminDetail['country_name'] != "") {
            $address .= $adminDetail['country_name'] . '<br/>';
        }
        if (strlen($address) > 1) {
            $address1 = '<li><i class="icon ion-ios-location"></i>' . $address . '</li>';
        }
        $src = CONF_WEBROOT_URL . 'user-image-crop.php?id=' . $adminDetail['admin_id'] . '&type=Admin&uniq=' . time();
        if (!empty($adminDetail['admin_phone'])) {
            $str .= '<li><i class="icon ion-android-call"></i>' . $adminDetail['admin_phone'] . '</li>';
        }
        if (!empty($adminDetail['admin_email'])) {
            $str .= '<li><i class="icon ion-android-mail"></i>' . $adminDetail['admin_email'] . '</li>';
        }
        if (!empty($adminDetail['admin_skype'])) {
            $str .= '<li><i class="icon ion-social-skype"></i>' . $adminDetail['admin_skype'] . '</li>';
        }
        if (!empty($adminDetail['admin_twitter'])) {
            $str .= '<li><i class="icon ion-social-twitter"></i>' . $adminDetail['admin_twitter'] . '</li>';
        }
        $html = '<aside class="grid_1 profile">
                    <div class="avtararea">
                        <figure class="pic">
                            <form   method="post" enctype="multipart/form-data" id="imageUpload" >
                                <img src="' . $src . '" alt="' . $adminDetail['admin_name'] . '">
                                  <span class="uploadavtar">
                                    <i class="icon ion-android-camera"></i> Update Profile Picture 
                                    <input type="file" name="admin_avtar" onchange="$(\'#imageUpload\').submit();">
                                    <input name="admin_id" type="hidden" value="' . $_SESSION['admin_logged']['admin_id'] . '">
                                    <input type="hidden" name="ImageSubmit">
                                </span>
                            </form>    
                        </figure>
                        <div class="picinfo">
                            <span class="name">' . $_SESSION['admin_logged']['admin_name'] . '</span>
                            <span class="mailinfo">' . $_SESSION['admin_logged']['admin_email'] . '</span>
                        </div>
                    </div>
              <div class="contactarea">
                    <h3>' . t_lang('M_TXT_CONTACT_INFO') . '</h3>
                    <ul class="contactlist">' . $str . $address1 . '</ul>
            </div>
            </aside>';
        return $html;
    }

    public function navigationLink($class)
    {
        $html = '<ul class="centered_nav">';
        $html .= '<li><a href="view-profile.php" class="' . ($class == 'view' ? 'active' : '') . '">' . t_lang('M_TXT_MY_ACCOUNT') . '</a></li>
                    <li><a href="message-listing.php" class="' . ($class == 'message' ? 'active' : '') . '">' . t_lang('M_TXT_MY_MESSAGES') . '</a></li>
                    <li><a href="my-account.php" class="' . ($class == 'edit' ? 'active' : '') . '">' . t_lang('M_TXT_EDIT_ACCOUNT') . '</a></li>
                    <li><a href="change-password.php" class="' . ($class == 'changepassword' ? 'active' : '') . ' ">' . t_lang('M_TXT_CHANGE_PASSWORD') . '</a></li>
                </ul>';
        return $html;
    }

    public function SaveImage($post)
    {
        global $db;
        global $msg;
        if (is_uploaded_file($_FILES['admin_avtar']['tmp_name'])) {
            $ext = strtolower(strrchr($_FILES['admin_avtar']['name'], '.'));
            if ((!in_array($ext, ['.gif', '.jpg', '.jpeg', '.png'])) || ($_FILES['admin_avtar']['size'] > CONF_IMAGE_MAX_SIZE)) {
                $msg->addError(t_lang('M_TXT_ADMIN') . ' ' . t_lang('M_TXT_IMAGE_NOT_SUPPORTED_OR_SIZE_EXCEEDED'));
            } else {
                $flname = time() . '_' . $_FILES['admin_avtar']['name'];
                if (!move_uploaded_file($_FILES['admin_avtar']['tmp_name'], UPLOADS_PATH . 'admin-images/' . $flname)) {
                    $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                    return false;
                } else {
                    $getImg = $db->query("select * from tbl_admin where admin_id='" . $_SESSION['admin_logged']['admin_id'] . "'");
                    $imgRow = $db->fetch($getImg);
                    unlink(UPLOADS_PATH . 'admin-images/' . $imgRow['admin_avtar']);
                    $db->update_from_array('tbl_admin', ['admin_avtar' => $flname], 'admin_id=' . $_SESSION['admin_logged']['admin_id']);
                }
            }
        }
        return true;
    }

    public function backgroundColor($comapnyNameLetter)
    {
        $range1 = ["A", "G", "M", "S"];
        $range2 = ["B", "H", "N", "T"];
        $range3 = ["C", "I", "O", "U"];
        $range4 = ["D", "J", "P", "V", "X"];
        $range5 = ["E", "K", "Q", "W", "Z"];
        $range6 = ["F", "L", "R", "Y"];
        $comapnyNameLetter . " ";
        if (in_array($comapnyNameLetter, $range1)) {
            return 'red';
        } else if (in_array($comapnyNameLetter, $range2)) {
            return 'purple';
        } else if (in_array($comapnyNameLetter, $range3)) {
            return 'green';
        } else if (in_array($comapnyNameLetter, $range4)) {
            return 'blue';
        } else if (in_array($comapnyNameLetter, $range5)) {
            return 'red';
        } else if (in_array($comapnyNameLetter, $range6)) {
            return 'yellow';
        }
    }

}
