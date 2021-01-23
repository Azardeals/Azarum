/**
 * 
 */


var txtreload;
var val;
function updateCitySubscriber(obj) {

val =$(obj).val();
    if (val == 1) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updateCitySubscriber&value=0', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
        $("obj").attr("checked",false);

    }

    if (val == 0) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updateCitySubscriber&value=1', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
        $("obj").attr("checked",true);
    }

}

function updateFavouriteMerchants(obj) {

val =$(obj).val();
    if (val == 1) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updateFavouriteMerchants&value=0', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
         $("obj").attr("checked",false);
    }

    if (val == 0) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updateFavouriteMerchants&value=1', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
         $("obj").attr("checked",true);
    }

}


function updateExpire(obj) {
val =$(obj).val();

    if (val == 1) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updateExpire&value=0', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
       $("obj").attr("checked",false);
    }

    if (val == 0) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updateExpire&value=1', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
           $("obj").attr("checked",true);
    }

}

function updatedealBuck(obj) {
val =$(obj).val();

    if (val == 1) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updatedealBuck&value=0', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
        $("obj").attr("checked",false);
    }

    if (val == 0) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updatedealBuck&value=1', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
        $("obj").attr("checked",true);
    }

}

function updatefriendBuy(obj) {
val =$(obj).val();

    if (val == 1) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updatefriendBuy&value=0', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
        $("obj").attr("checked",false);
    }

    if (val == 0) {
        callAjax(webroot + 'my-account-ajax.php', 'mode=updatefriendBuy&value=1', function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
          $("obj").attr("checked",true);
    }

}

function submitAccountInfo(frm, v) {
    v.validate();
    if (!v.isValid()){
        return false;
    }else
        return true;
}

function cancelFriendGift() {
    document.getElementById("gift").value = 0;
    document.getElementById("giftCheck").checked = false;
    $('#giftImageRow').show();
    askFriendInfoGift();

}

function editEmail() {
    //$.facebox('<img src="'+webroot+'facebox/loading.gif">');
    $.facebox(function () {
        callAjax(webroot + 'my-account-ajax.php', 'mode=editEmail', function (t) {
            $.facebox(t);
        });
    });
}

function addCardDetail() {
    //$.facebox('<img src="'+webroot+'facebox/loading.gif">');
    $.facebox(function () {
        callAjax(webroot + 'my-account-ajax.php', 'mode=addCardDetail', function (t) {
            $.facebox(t);
        });
    })

}

function deleteCardDetail(profileId) {
    //$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	requestPopupAjax(profileId,confirmMsg,1,'DeleteCard');
}
 function doRequiredActionDeleteCard(profileId) {
	     $.facebox(function () {
        callAjax(webroot + 'my-account-ajax.php', 'mode=deleteCardInfo&profileId=' + profileId, function (t) {
            $.facebox(t);
            setTimeout(function () {
                location.href = webroot + 'my-account.php';
            }, 4000);
        });
    });
 }
function updateCardInfo(frm, v) {

    v.validate();
    if (!v.isValid()){
        return false;
    }
    return true;
   /*  var data = getFrmData(frm);

    //$.facebox('<img src="'+webroot+'facebox/loading.gif">');
    $.facebox(function () {
        callAjax(webroot + 'my-account-ajax.php', data, function (t) {



            //  var ans=parseJsonData(t);
            $.facebox(t);
            //  if(ans.status==0){
            //			alert(ans.msg + '\n'+txtreload);
            //			return;
            //		}
            if (t == "Updated Card Information.") {
                setTimeout(function () {
                    location.href = webroot + 'my-account.php';
                }, 3000);
            }
  
        });
    }); */

}

 