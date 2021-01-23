<?php
require_once './application-top.php';
require_once './includes/navigation-functions.php';
require_once './includes/page-functions/user-functions.php';
if (!isUserLogged()) {
    redirectUser(friendlyUrl(CONF_WEBROOT_URL . 'login.php'));
}
require_once './header.php';
$wallet_amount = getWalletAmount($_SESSION['logged_user']['user_id']);
?>
<!--bodyContainer start here-->
<section class="pagebar">
    <div class="fixed_container">
        <div class="row">
            <aside class="col-md-7 col-sm-7">
                <h3><?php echo t_lang('M_TXT_DEAL_BUCKS'); ?></h3>
                <ul class="breadcrumb">
                    <li><a href="<?php echo friendlyUrl(CONF_WEBROOT_URL); ?>"><?php echo t_lang('M_TXT_HOME'); ?></a></li>
                    <li><?php echo t_lang('M_TXT_DEAL_BUCKS') ?></li>
                </ul>
            </aside>
        </div>
    </div>
</section> 
<?php include './left-panel-links.php'; ?> 
<section class="page__container">
    <div class="fixed_container">
        <div class="row">
            <div class="col-md-12">
                <div class="section__row">
                    <h2 class="section__subtitle hide__mobile hide__tab hide__ipad"><?php echo t_lang('M_TXT_DEAL_BUCKS') ?></h2>
                    <div class="section__row-border">
                        <div class="info__amount">
                            <span class="info__largetxt"><?php echo amount(($wallet_amount == '') ? '0.00' : $wallet_amount); ?></span>
                            <p><?php echo t_lang('M_TXT_MY_DEAL_BUCKS') ?></p>
                        </div>
                        <div class="table__info">
                            <table>
                                <tr>
                                    <td>
                                        <h6><?php echo t_lang('M_TXT_WHAT_DEAL_BUCKS_HEADING'); ?></h6>
                                        <p><?php echo t_lang('M_TXT_WHAT_DEAL_BUCKS_ANSWER'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6><?php echo t_lang('M_TXT_WANT_EARN_MORE_DEAL_BUCKS_HEADING'); ?></h6>
                                        <p> <a href="javascript:void(0);" onclick="referFriendInfo();"><?php echo t_lang('M_TXT_INVITE_YOUR_FRINDS'); ?></a>&nbsp;<?php echo t_lang('M_TXT_WANT_EARN_MORE_DEAL_BUCKS_ANSWER'); ?></br> </br><strong> <?php echo t_lang('M_TXT_NOTE'); ?></strong> : <?php echo t_lang('M_TXT_GET_REFERAL_COMISSION_NOTE'); ?> 
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>   
                    </div>
                </div> 
                <div class="section__row">
                    <h2 class="section__subtitle"><?php echo t_lang('M_TXT_REFERRER_HEAD'); ?></h2>
                    <div class="cover__grey">  
                        <div class="form__small siteForm">
                            <ul>
                                <li><input type="text" id="copyTarget" value="http://<?php echo $_SERVER['SERVER_NAME'] . CONF_WEBROOT_URL . '?refid=' . $_SESSION['logged_user']['user_id']; ?>"></li>
                                <li><input type="button" id="copyButton" class="btn" value="Copy Link"  ></li>
                            </ul>
                        </div>
                        <span id="msg1" class="gap"></span>
                    </div>      
                </div>    
            </div>    
        </div>
    </div>    
</section>
<script type="text/javascript">
    $("#copyButton").live("click", function () {
        copyToClipboardMsg(document.getElementById("copyTarget"), "msg1");
    });
    function copyToClipboardMsg(elem, msgElem) {
        var succeed = copyToClipboard(elem);
        var msg;
        if (!succeed) {
            msg = "Copy not supported or blocked.  Press Ctrl+c to copy."
        } else {
            msg = "Text copied to the clipboard."
        }
        if (typeof msgElem === "string") {
            msgElem = document.getElementById(msgElem);
        }
        msgElem.innerHTML = msg;
        setTimeout(function () {
            msgElem.innerHTML = "";
        }, 2000);
    }
    function copyToClipboard(elem) {
        // create hidden text element, if it doesn't already exist
        var targetId = "_hiddenCopyText_";
        var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
        var origSelectionStart, origSelectionEnd;
        if (isInput) {
            // can just use the original source element for the selection and copy
            target = elem;
            origSelectionStart = elem.selectionStart;
            origSelectionEnd = elem.selectionEnd;
        } else {
            // must use a temporary form element for the selection and copy
            target = document.getElementById(targetId);
            if (!target) {
                var target = document.createElement("textarea");
                target.style.position = "absolute";
                target.style.left = "-9999px";
                target.style.top = "0";
                target.id = targetId;
                document.body.appendChild(target);
            }
            target.textContent = elem.textContent;
        }
        // select the content
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);
        // copy the selection
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch (e) {
            succeed = false;
        }
        // restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }
        if (isInput) {
            // restore prior selection
            elem.setSelectionRange(origSelectionStart, origSelectionEnd);
        } else {
            // clear temporary content
            target.textContent = "";
        }
        return succeed;
    }
</script>   
<?php require_once './footer.php'; ?>
