function checkShippingDetails(v) {
    if (v == '' || v == 'undefined') {
        $.facebox('Invalid Request!');
        return false;
    }
    $.facebox('<img src="' + webroot + 'facebox/loading.gif">');

    callAjax('tipped-members-ajax.php', 'mode=ShippingDetails&v=' + v, function (t) {
        $.facebox(t);
    });
}

function shippingSubmit(frm, v) {
    v.validate();
    if (!v.isValid())
        return false;

    $(".error_msg").remove();

    /*if (!$('input[name=shipping_effect_to]:checked').val() ) {
     $('input[name=shipping_effect_to]:first').closest('table').after('<span style="color:#ff181c" class="error_msg">"Effect to" is Required!</span>');
     return false;
     }*/

    var data = getFrmData(frm);
    callAjax('tipped-members-ajax.php', data, function (t) {

        var ans = parseJsonData(t);
        if (ans === false) {
            $.facebox('Some Error occur, Please try loading Page.');
            return false;
        }
        if (parseInt(ans.status) === 0) {
            $.facebox(ans.msg);
        }

        $.facebox(ans.msg);
        if (ans.page_reload) {
            setTimeout(function () {
                location.reload();
            }, 1000);
        } else {
            $(".ship_status_" + frm.cm_order_id.value + frm.cm_counpon_no.value).html(ans.shipping_status_text);
        }


    });
    return false;
}

function digitalProductSendLink(email) {

    jQuery.facebox(function () {
        callAjax('tipped-members-ajax.php', 'mode=sendlinkForm&useremail=' + email, function (t) {
            $.facebox(t);
        });
    });

}
function sendLinkInfoSubmit(email_subject, recipients, email_message, v) {
    if (v == '' || v == 'undefined') {
        $.facebox('Invalid Request!');
        return false;
    }
    v.validate();
    if (!v.isValid()) {
        return false;
    }
//	$.facebox('<img src="'+webroot+'facebox/loading.gif" alt="Loading...">');
    jQuery.facebox(function () {

        callAjax('tipped-members-ajax.php', 'mode=sendLinkInfoSubmit&email_subject=' + email_subject + '&recipients=' + recipients + '&email_message=' + email_message, function (t) {
            $.facebox(t);
        });
    });
}
