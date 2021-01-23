<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/site-functions-extended.php';
require_once './includes/page-functions/user-functions.php';
require_once './header.php';
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
$frm = getMBSFormByIdentifier('frmMyAccount');
$frm->captionInSameCell(true);
$frm->setRequiredStarWith('none');
$frm->setRequiredStarPosition('none');
$frm->setFieldsPerRow(2);
$frm->setExtra = 'class="siteForm"';
$frm->setAction($_SERVER['PHP_SELF']);
$frm->setTableProperties('class="formwrap__table"');
$fld = $frm->getField('user_name');
$fld->requirements()->setRequired(true);
$fld->value = $_SESSION['logged_user']['user_name'];
$fld = $frm->getField('user_lname');
$fld->changeCaption('Last Name');
$fld->value = $_SESSION['logged_user']['user_lname'];
$fld = $frm->getField('user_email');
$fld->value = $_SESSION['logged_user']['user_email'];
$fld = $frm->getField('user_city');
$cityList = $db->query("select city_id, IF(CHAR_LENGTH(city_name" . $_SESSION['lang_fld_prefix'] . "),city_name" . $_SESSION['lang_fld_prefix'] . ",city_name) as city_name from tbl_cities where city_active=1 and city_request=0 and city_deleted=0");
$fld->changeCaption('City of Interest');
$fld->options = $db->fetch_all_assoc($cityList);
$fld->value = $_SESSION['logged_user']['user_city'];
$fld = $frm->getField('btn_submit');
$fld->value = t_lang('M_TXT_UPDATE');
$fld = $frm->getField('email');
$frm->removeField($fld);
$fld = $frm->getField('user_avatar');
$frm->removeField($fld);
$arr_timezones = DateTimeZone::listIdentifiers();
$arr_timezones = array_combine($arr_timezones, $arr_timezones);
$fld = $frm->getField('user_timezone');
$fld->options = $arr_timezones;
$fld->value = $_SESSION['logged_user']['user_timezone'];
$fld = $frm->getField('user_newsletter');
$frm->removeField($fld);
$fld = $frm->getField('password');
$fld->html_after_field = '<br/>' . t_lang('M_TXT_LEAVE_BLANK_TO_KEEP_SAME');
$frm->setValidatorJsObjectName('frmValidatoraccount');
$frm->setOnSubmit('submitAccountInfo(this, frmValidatoraccount);');
$frm->addHiddenField('', 'user_id', $_SESSION['logged_user']['user_id'], 'user_id', '');
updateFormLang($frm);
$fld1 = $frm->addHTML('', '', '<input type="button" value="Cancel" class="link__edit">');
$fld = $frm->getField('btn_submit');
$fld->attachField($fld1);
$fld->value = t_lang('M_TXT_SUBMIT');
$i = 0;
while ($fld = $frm->getFieldByNumber($i)) {
    $star = false;
    if ($i <= 5) {
        $star = true;
    }
    if ($fld->fldType != "select") {
        setRequirementFieldPlaceholder($fld, $star);
    }
    $i++;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['mode'])) {
    $post = getPostedData();
    if ($post['user_name'] != "") {
        $success = true;
        $arr_updates = [
            'user_name' => ($post['user_name']),
            'user_timezone' => $post['user_timezone'],
            'user_lname' => $post['user_lname'],
            'user_city' => $post['user_city'],
            'user_email' => $post['user_email'],
        ];
        if ($post['password'] != '') {
            if ($post['password'] != $post['password_confirm']) {
                $msg->addError(t_lang('M_TXT_PASSWORD_AND_CONFIRM_PASSWORD_DID_NOT_MATCH'));
                $frm->fill($post);
                $success = false;
            } else {
                $arr_updates['user_password'] = md5($post['password']);
            }
        }
        if ($success == true) {
            $record = new TableRecord('tbl_users');
            $record->assignValues($arr_updates);
            if (!$record->update('user_id=' . $_SESSION['logged_user']['user_id'])) {
                $msg->addError($record->getError());
                $frm->fill($post);
            } else {
                $msg->addMsg(t_lang('M_TXT_INFO_UPDATED'));
                $_SESSION['logged_user']['user_name'] = ($post['user_name']);
                $_SESSION['logged_user']['user_timezone'] = $post['user_timezone'];
                $_SESSION['logged_user']['user_email'] = $post['user_email'];
                $_SESSION['logged_user']['user_lname'] = $post['user_lname'];
                $_SESSION['logged_user']['user_city'] = $post['user_city'];
                redirectUser();
            }
        }
    }
}
$frm1 = addCardDetailForm();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['mode'] == "updateCardInfo") {
    $post = getPostedData();
    $customerShippingAddressId = NULL;
    if (((int) $post["customerProfileId"]) <= 0) {
        $msg->addError(t_lang('M_ERROR_INVALID_REQUEST'));
    }
    if ($pay_profile_id = createCIMCustomerPaymentProfile($post)) {
        if (isset($pay_profile_id['error'])) {
            $frm1 = addCardDetailForm($post);
            $msg->addErrorMessage($pay_profile_id['error']);
        } else {
            if (!$db->insert_from_array('tbl_users_card_detail', array('ucd_user_id' => $_SESSION['logged_user']['user_id'], 'ucd_customer_payment_profile_id' => htmlspecialchars($pay_profile_id), 'ucd_card' => substr($post['cardNumber'], -4), 'ucd_street_address' => $post["address1"], 'ucd_street_address2' => $post["address2"], 'ucd_city' => $post["city"], 'ucd_state' => $post["state"], 'ucd_zip' => $post["zip"], 'ucd_state_id' => $post["state_id"], 'ucd_country_id' => $post["country_id"]), false)) {
                $msg->addError($db->getError());
            }
            $msg->addMessage(t_lang('M_TXT_UPDATED_CARD_INFORMATION'));
        }
    }
}
if ($_SESSION['logged_user']['user_id'] > 0) {
    $rowCheck = fetchEmailNotifications($_SESSION['logged_user']['user_id']);
}
?>
<script type="text/javascript">
    /* for select city form */
    $(document).ready(function () {
        /* for edit form */
        $('#editProfile').click(function () {
            $(this).toggleClass("active");
            $('.info__edit').slideToggle("600");
        });
        /* for add card form */
        $('.link__addcard').click(function () {
            $(this).toggleClass("active");
            $('.add__card').slideToggle("600");
        });
        /* for my account links */
        $('.links__account-link').click(function () {
            $(this).toggleClass("active");
            $('.links__account-drop').slideToggle("600");
        });
        $(".scroll").click(function (event) {
            event.preventDefault();
            var full_url = this.href;
            console.log(full_url);
            var parts = full_url.split("#");
            var trgt = parts[1];
            var target_offset = $("#" + trgt).offset();
            console.log(target_offset);
            if (typeof (target_offset) != "undefined" && target_offset !== null) {
                var target_top = target_offset.top - 54;
                $('html, body').animate({scrollTop: target_top}, 800);
            }
        });
    });
</script>
<script language="text/javascript">
    var txtreload = "<?php echo addslashes(t_lang('M_TXT_PLEASE_RELOAD_PAGE_AND_TRY_AGAIN')); ?>";
</script>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_MY_ACCOUNT') ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section>
<?php require_once './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="section__row">
                    <h2 class="section__subtitle"><?php echo t_lang('M_FRM_PERSONAL_INFORMATION'); ?></h2>
                    <a class="themebtn themebtn--small themebtn--positioned right link__edit scroll"  href="javascript:void(0);" id="editProfile" ><?php echo t_lang('M_TXT_EDIT'); ?></a>
                    <div class="section__row-border">
                        <div  class="table__info myaccountTable">
                            <table>
                                <tr>
                                    <th> <?php echo t_lang('M_FRM_NAME'); ?></th>
                                    <td><?php echo htmlentities($_SESSION['logged_user']['user_name']); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo t_lang('M_FRM_EMAIL'); ?></th>
                                    <td><?php echo $_SESSION['logged_user']['user_email']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo t_lang('M_FRM_TIMEZONE'); ?></th>
                                    <td><?php echo $_SESSION['logged_user']['user_timezone']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="container__form info__edit" id="info__edit" style="display:none;">
                    <div class="formwrap">
                        <?php echo $frm->getFormTag(); ?>   
                        <table class="formwrap__table">
                            <tr>
                                <td><?php echo $frm->getFieldHtml('user_name'); ?></td>
                                <td><?php echo $frm->getFieldHtml('user_lname'); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo $frm->getFieldHtml('password'); ?></td>
                                <td><?php echo $frm->getFieldHtml('password_confirm'); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo $frm->getFieldHtml('user_email'); ?></td>
                                <td><?php echo $frm->getFieldHtml('user_city'); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo $frm->getFieldHtml('user_timezone'); ?></td>
                                <td><?php echo $frm->getFieldHtml('btn_submit'); ?></td>
                            </tr>
                        </table>
                        </form>    
                    </div>
                </div>
                <div class="container__form add__card" id="add__card" style="display:none;">
                    <div class="formwrap">
                        <?php
                        if (((int) $_SESSION['logged_user']['user_customer_profile_id']) == 0) {
                            if (!createCIMCustomerProfile()) { /* To create logged in user's CIM Customer profileId */
                                die($msg->display());
                            }
                        }
                        echo $frm1;
                        ?>
                    </div>
                </div>
                <div class="section__row">
                    <?php
                    /* Display Credit card if CIM is active from Manager Section */
                    $rs = $db->query("select * from tbl_payment_options where po_name='CIM'");
                    $row = $db->fetch($rs);
                    if ($row['po_active'] == 1) {
                        //onclick="addCardDetail();"
                        ?>
                        <aside class="grid_1">
                            <h2 class="section__subtitle"><?php echo t_lang('M_TXT_CREDITCARD'); ?></h2>
                            <a class="themebtn themebtn--small right scroll link__addcard"  href="#add__card"><?php echo t_lang('M_TXT_ADD_CARD'); ?></a>    
                            <div class="section__row-border section--space">
                                <ul class="listing__vertical">
                                    <?php
                                    $rs = getUserCardDetail($_SESSION['logged_user']['user_id']);
                                    while ($row = $db->fetch($rs)) {
                                        echo '<li><div class="txt__wrap">
                        <span >xxxx-xxxx-xxxx-' . $row['ucd_card'] . '</span>
                        <a onclick="deleteCardDetail(\'' . $row['ucd_customer_payment_profile_id'] . '\');" href="javascript:void(0);" class="themebtn themebtn--small">' . t_lang('M_TXT_REMOVE') . '</a></div></li>';
                                    }
                                    if ($db->total_records($rs) == 0) {
                                        echo '<li>
                                <span class="">' . t_lang('M_TXT_YOU_DONT_HAVE_CREDIT_CARD_ON_FILE') . '</span>
                              </li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </aside>  
                        <?php
                    }
                    /* 	Display Credit card if CIM is active from Manager Section */
                    ?>      
                    <aside class="grid_2">
                        <h2 class="section__subtitle"><?php echo t_lang('M_TXT_EMAIL_NOTIFICATIONS'); ?></h2>
                        <div class="section__row-border section--space">
                            <ul class="listing__vertical morepadding">
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox" value="<?php echo $rowCheck['en_city_subscriber']; ?>"  onchange="updateCitySubscriber(this);" <?php echo ($rowCheck['en_city_subscriber']) ? 'checked' : ''; ?> ><i class="input-helper"></i> <?php echo t_lang('M_TXT_NEW_DEAL_FOR_CITY_SUBSCRIBERS'); ?>
                                    </label>
                                </li>
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox"  value="<?php echo $rowCheck['en_favourite_merchant']; ?>" onchange="updateFavouriteMerchants(this);" <?php echo ($rowCheck['en_favourite_merchant']) ? 'checked' : ''; ?> ><i class="input-helper"></i> <?php echo t_lang('M_TXT_NEW_DEAL_FROM_FAVOURITE_MERCHANTS'); ?> 
                                    </label>
                                </li>
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox" value="<?php echo $rowCheck['en_near_to_expired']; ?>" onchange="updateExpire(this);" <?php echo ($rowCheck['en_near_to_expired']) ? 'checked' : ''; ?> ><i class="input-helper"></i> <?php echo t_lang('M_TXT_DEAL_ABOUT_TO_EXPIRE'); ?>
                                    </label>
                                </li>
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox" value="<?php echo $rowCheck['en_friend_buy_deal']; ?>" <?php echo ($rowCheck['en_friend_buy_deal']) ? 'checked' : ''; ?> onchange="updatefriendBuy(this);" ><i class="input-helper"></i>  <?php echo t_lang('M_TXT_FRIEND_BUY_DEAL'); ?>
                                    </label>
                                </li>
                                <li>
                                    <label class="checkbox">
                                        <input type="checkbox" value="<?php echo $rowCheck['en_earned_deal_buck']; ?>" onchange="updatedealBuck(this);" <?php echo ($rowCheck['en_earned_deal_buck']) ? 'checked' : ''; ?> ><i class="input-helper"></i> <?php echo t_lang('M_TXT_EARNED_DEAL_BUCKS'); ?> 
                                    </label>
                                </li>                                
                            </ul>                           
                        </div>
                    </aside>
                </div>
            </div>    
        </div>
    </div>
</section>
<!--bodyContainer end here-->
<script language="javascript" type="text/javascript">
    var confirmMsg = "<?php echo t_lang('M_MSG_REALLY_WANT_TO_DELETE_THIS_RECORD') ?>";
</script>
<?php require_once './footer.php'; ?>