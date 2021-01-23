

function referFriendInfo() {
    jQuery.facebox(function () {

        callAjax(webroot + 'buy-deal-ajax.php', 'mode=referfriends', function (t) {
            $.facebox(t);
        });
    });
}

function referFriendInfoSubmit(email_subject, recipients, email_message) {
//	$.facebox('<img src="'+webroot+'facebox/loading.gif" alt="Loading...">');
    jQuery.facebox(function () {

        callAjax(webroot + 'buy-deal-ajax.php', 'mode=referfriendssubmit&email_subject=' + email_subject + '&recipients=' + recipients + '&email_message=' + email_message, function (t) {
            $.facebox(t);
        });
    });
}