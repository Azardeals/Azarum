function likeDeal(deal_id, txt) {
    callAjax(webroot + 'common-ajax.php', 'mode=likeDeal&deal_id=' + deal_id + '&txt=' + txt, function (t) {
        var ans = parseJsonData(t);
        if (ans === false) {
            alert(txtoops + txtreload);
            return;
        }
        if (ans.status == 0) {
            $.facebox(ans.msg);
            return;
        }

        $('.likeDeal_'+ deal_id).html(ans.merchantText);
        if (ans.msg == 'login') {
            location.href = webroot + 'buy-deal.php';
        }
    });
}
function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function(e) {
      $('.deal_image').attr('src', e.target.result);
      $('.deal_image').css('width','100px');
    };

    reader.readAsDataURL(input.files[0]);
  }
}

function fetchsubCategory(id, obj) {
    var class1 = $(obj).parent().attr('class');

    callAjax(webroot + 'common-ajax.php', 'mode=fetchsubCategory&id=' + id, function (t) {
        var ans = parseJsonData(t);

        if (ans === false) {
            alert(txtoops + txtreload);
            return;
        }
        if (ans.status == 0) {
            alert(ans.msg + '\n' + txtreload);
            return;
        }


        if ($(obj).parent().has('ul').length > 0) {

            $(obj).parent().parent().find('ul').remove();

        }
        $('.' + class1).append(ans.str);
    });
}

var webroot;
var image_not_loaded_msg = 'Image cannot be loaded. Make sure the path is correct and image exist.';
var msg = "";

function citySelector() {

    $('#div-citySelector').slideDown(500);
    $('#cityOuter').slideDown(500);
    if ($('#div-citySelector').html() == '') {
        listCitiesForSelection();
    }
}

function listCitiesForSelection(c) {
    var data = 'mode=listCities';
    if (c)
        data += '&c=' + c;
    $('#div-citySelector').html('<img src="' + webroot + 'images/ajax2.gif">');
    callAjax(webroot + 'common-ajax.php', data, function (t) {
        var ans = parseJsonData(t);
        if (ans.status == 0) {
            alert('Oops! There was some internal error[1]');
            return;
        }
        
        $('#div-citySelector').html(t);
    });
}

function hideCitySelector() {
    $('#div-citySelector').slideUp(500);
    $('#cityOuter').slideUp(500);
}

function selectCity(id, url) {
    callAjax(webroot + 'common-ajax.php', 'mode=selectCity&id=' + id, function (t) {
        var ans = parseJsonData(t);

        if (ans === false) {
            alert('Oops! There was some internal error.');
            return;
        }
        if (ans.status == 0) {
            alert(ans.msg);
            return;
        }
        if (url != 0 && typeof ans.link != 'undefined') {
            location.href = webroot;

        } else {
            location.href = webroot;
        }
    });
}

function selectSessionCity(id, url) {
    callAjax(webroot + 'common-ajax.php', 'mode=selectSessionCity&id=' + id, function (t) {
        var ans = parseJsonData(t);

        if (ans === false) {
            alert('Oops! There was some internal error.');
            return;
        }
        if (ans.status == 0) {
            alert(ans.msg);
            return;
        }
        location.href = webroot;
    });
}

function buySubDeal(id, forFriend, url, frm_data, sub_deal_id){
    var company_loaction_id= $('#company_location_id').val();
    var startDate= $("#start_date").val();
    var endDate= $("#end_date").val();
    
    if (company_loaction_id != '' && company_loaction_id != undefined) {
        company_loaction_id = company_loaction_id;
    } else {
        company_loaction_id = '';
    }
    
	if(deal_sub_type==2){
		if((deal_type == 0)&&(deal_sub_type==2)){

			if (startDate == '' || startDate == undefined) {
				alert(checkin);
				return false;

			} 
			 if (endDate == '' || endDate == undefined) {
				alert(checkout);
				return false;

			} 
		}else {
			endDate = '';
			startDate='';
		}
		if(startDate == endDate){
			alert('Check-in date and check-out date should not be same. Please select another dates');	
			return false;
		}
	}else{
		endDate = '';
		startDate='';
	}
    if (frm_data != '' && frm_data != undefined) {
        frm_data = frm_data.serialize();
    } else {
        frm_data = '';
    }
	
    frm_data += '&mode=selectDealToCart&id=' + id + '&forFriend=' + ((forFriend) ? '1' : '0') + '&sub_deal_id=' + sub_deal_id+'&company_loaction_id=' + company_loaction_id+'&startDate=' + startDate+'&endDate=' + endDate;

    callAjax(webroot + 'common-ajax.php', frm_data, function (t) {
        $('.error').remove();
        var ans = parseJsonData(t);
		response(ans,url);
    });
}

function buyDeal(id, forFriend, url, frm_data, sub_deal_id) {	
    if (frm_data != '' && frm_data != undefined) {
        frm_data = frm_data.serialize();
    } else {
        frm_data = '';
    }
	
    frm_data += '&mode=selectDealToCart&id=' + id + '&forFriend=' + ((forFriend) ? '1' : '0') + '&sub_deal_id=' + sub_deal_id;

    callAjax(webroot + 'common-ajax.php', frm_data, function (t) {
        $('.error').remove();
        var ans = parseJsonData(t);
        
        response(ans,url);
    });
}

function addToCart(id, forFriend, url, frm_data, sub_deal_id) {

    if (frm_data != '' && frm_data != undefined) {
        frm_data = frm_data.serialize();

    } else {
        frm_data = '';
    }

    frm_data += '&mode=selectDealFORADDTOCart&id=' + id + '&forFriend=' + ((forFriend) ? '1' : '0') + '&sub_deal_id=' + sub_deal_id;

    callAjax(webroot + 'common-ajax.php', frm_data, function (t) {
        $('.error').remove();
        var ans = parseJsonData(t);
		response(ans,url);
    });
}

function response(ans, url){
    if (ans === false) {
        alert('Oops! There was some internal error. Please retry by reloading the page or contact administrator if problem persists.');
        return;
    }

    if (ans.status == 0) {

        if (ans.error) {
            if (ans['error']['option']) {
                for (i in ans['error']['option']) {

                    $('#option-' + i).after('<span class="error">' + ans['error']['option'][i] + '</span>');
                }
            }
        } else {
            $('.more-links').after('<span class="error" style="color:red; ">' + ans.msg + '</span>');
            $('.div_error').find('li').html();

        }
        return;
    }

    if (ans.status == 1 && ans.url == "") {

        $('.more-links').after('<span class="error product-added" style="color:red; ">' + ans.msg['message'] + '</span>');
        $('.spancount').html(ans.msg['dealSize']);
        return;
    }

    if (url == 0) {
        location.href = webroot + 'buy-deal.php';
    } else {
        if (ans.url != "") {
            //  alert(ans.url);
            location.href = ans.url
        }
        else
            $.facebox(txtsessionexpire);
    }
}

function popUpLogin(c) {
    var data = 'mode=popUpLogin';
    if (c)
        data += '&c=' + c;
    
    callAjax(webroot + 'common-ajax.php', data, function (t) {
        alert(t);
        var ans = parseJsonData(t);
        $('#showPopUp').html(ans.msg);
    });
}

function setPage(page, frm) {
    frm.elements['page'].value = page;
    frm.submit();
}

function updateLanguage(val, url) {
    callAjax(webroot + 'common-ajax.php', 'mode=updateLanguage&val=' + val, function (t) {
        $.facebox(t);
        location.href = webroot + 'manager' + url;
    });


}

function updateLanguageMerchant(val, url) {
    callAjax(webroot + 'common-ajax.php', 'mode=updateLanguage&val=' + val, function (t) {
        $.facebox(t);
        location.href = webroot + 'merchant' + url;
    });
}

function updateLanguageRepresentative(val, url) {
    callAjax(webroot + 'common-ajax.php', 'mode=updateLanguage&val=' + val, function (t) {
        $.facebox(t);
        location.href = webroot + 'representative' + url;
    });
}

function updateLanguageFront(val) {
    callAjax(webroot + 'common-ajax.php', 'mode=updateLanguage&val=' + val, function (t) {

        $.facebox(t);
        location.reload(true);
    });
}

function setDisable(v) {
    v.validate();
    if (v.isValid()) {
        $('input[type="submit"]').attr('onclick', 'return false;');
        return true;
    }
    else {
        return false;
    }
}

function likeMerchant(id, txt,pagename) { 
    callAjax(webroot + 'common-ajax.php', 'mode=likeMerchant&id=' + id + '&txt=' + txt, function (t) {
        var ans = parseJsonData(t);

        if (ans === false) {
            alert(txtoops + txtreload);
            return;
        }
        if (ans.status == 0) {
            alert(ans.msg + '\n' + txtreload);
            return;
        }
        $('#likeMerchant_' + id).replaceWith(ans.merchantText);
        if (ans.msg == 'login') {
            location.href = webroot + 'login.php';
        }
    });
}
function likeMerchantWithReload(id, txt,pagename) { 
    callAjax(webroot + 'common-ajax.php', 'mode=likeMerchant&id=' + id + '&txt=' + txt, function (t) {
        var ans = parseJsonData(t);

        if (ans === false) {
            alert(txtoops + txtreload);
            
        }
        if (ans.status == 0) {
            alert(ans.msg + '\n' + txtreload);
            
        }
        $('#likeMerchant_' + id).replaceWith(ans.merchantText);
        if (ans.msg == 'login') {
            location.href = webroot + 'login.php';
        }
		location.reload();
    });
}

function getproductAttributeValue(deal_id, deal_option_id, key, obj) {

    var deal_option_value_id = $(obj).val();
    callAjax(webroot + 'common-ajax.php', 'mode=getproductAttributeValue&deal_id=' + deal_id + '&deal_option_id=' + deal_option_id + '&deal_option_value_id=' + deal_option_value_id, function (t) {
        var ans = parseJsonData(t);
        key = parseInt(key) + parseInt(1);
        $('.level_' + key).html(ans.msg);
    });
}

function fetchQuickViewHtmlJS(dealId, type, limit){
    $('.popup').remove();
    callAjax(webroot + 'common-ajax.php', 'mode=fetchQuickViewHtml&dealId=' + dealId + '&type=' + type + '&limit=' + limit, function (t){
        var ans = parseJsonData(t);
        if (ans.status == 0) {
            alert('Oops! There was some internal error[2]');
            return;
        }
        
		$('#wrapper').after(t);
		$('body').addClass('hide__scroll');
	});
}

function updateStates(country){

	if(isNaN(parseInt(country))){
		$('#state_id').html(selectCountryFirst);
		return;
	}
	
	callAjax(webroot +'common-ajax.php', 'mode=getStates&country='+country+'&selected='+selectedState, function(t){
        var ans = parseJsonData(t);
		$('#state_id').html(ans.msg);
	});
}

function checkValidEmailAddress() {
	var subscribe_email_address = $("#sub_email").val();
	if(subscribe_email_address == "" || subscribe_email_address == placehoder_name) {
		requestPopupAjax(1,placehoder_name,false);  
		return false;
	} else {
		return true;
	}
}

function getalldeals(page) {
    ShowLoder($('.dealsContainer'));
    data = $('#page_search').serialize();
    data += '&mode=pageSearch&page=' + page ;
    callAjax(webroot + 'common-ajax.php', data, function (t) {
        var ans = parseJsonData(t);
        $('.paginglink').remove();
        $('.loader--container').remove();
        $('.dealsContainer').append(ans.msg['html']);
        if(ans.msg['dealIds']){
            dealIds = dealIds.concat(ans.msg['dealIds']);
            fetchdealids(dealIds);
        }
    });
}

function ShowLoder(obj){
    var src= '/images/loader.gif';
    obj.append('<div class= "loader--container"><img src=' + src + '  alt=""/></div>');
}

function fetchdealids(dealIds) {
    dealIds = dealIds;
}
    
function fetchNext(id) {
    last = id;
    id = "" + id;
    /*  alert(id); */
    var current = dealIds.indexOf(id);
    var next = current + 1;
    dealId = dealIds[next];
    type = 'deal';
    limit = 1;
   /*   alert(dealId); */
    if (dealId > 0) {
       
        fetchQuickViewHtmlJS(dealId, type, limit);
    } else {
       /*   $('html, body').animate({
            scrollTop: $("#loadingcontent").offset().top - 75
        }, 1000, function () {
        }); */
        $('.pagination').trigger('click');
        //  fetchNext(last);
    }
}

function fetchPrevious(id) {
    id = "" + id;
    var current = dealIds.indexOf(id);
    var prev = current - 1;
    dealId = dealIds[prev];
    type = 'deal';
    limit = 1;
    if (dealId > 0) {
        fetchQuickViewHtmlJS(dealId, type, limit);
    } else {
        alert('No deal has been found');
    }
}

$(function () {
    var elem = "";
    var settings = {
        mode: "toggle",
        limit: 500,
    };
    var text = "";
    $.fn.viewMore = function (options) {

        $.extend(settings, options)
        text = $(this).html();
        elem = this;
        initialize();
    };

    function initialize() {

        $(elem).each(function () {

            var extraText = $(this).html().substr(settings.limit, $(this).html().length)
            if ($(this).html().length > settings.limit + 50) {
                $(this).html($(this).html().substr(0, settings.limit));
                $(this).append("<span style='display:none' class='read_more'>" + extraText + "</span>")
                $(this).append("<span class='read_more_toggle link'>" + ".. Read More" + "</span>");
            }
        });
    }
    $('.read_more_toggle').live('click', function () {

        $(this).parent().find('.read_more').toggle();

        if ($(this).parent().find('.read_more').is(':visible')) {
            $(this).text('.. Read Less');
        } else {
            $(this).text('.. Read More');
        }
    });
});

/**
* Creating confirmation popup from Facebox
**/
function createFormHtml(label, successLink, haveConfirmBox) {
	tempHtml = '<div class="popupContent">'+label+'</div><div class="gap"></div>';
	if(haveConfirmBox) {
	tempHtml+='<div class="field-wraper"> <div class="field_cover"><a href="'+successLink+'" onclick="successRequest()" class="btn btn--primary faceboxAnchor">'+yes+'</a> <a class="btn btn--primary faceboxAnchor" onclick="cancelTruncateRequest()" value="Cancel">'+cancel+'</a></div></div>';
	}
	return tempHtml;
}

function cancelTruncateRequest() {
	$(document).trigger('close.facebox');
	return false;
}

function successRequest(successLink) {
	return true;
}

function requestPopup(anchorTarget, label, haveConfirmBox) {
	event.preventDefault();
	successLink = $(anchorTarget).attr('href');
	buttons = false;
	if(haveConfirmBox==1) {	buttons = true; 
	}else{ buttons = false;	}
	
	html = createFormHtml(label, successLink, buttons);
	$.facebox(html);
			
}

/**
* Creating confirmation Ajax popup from Facebox
**/
function requestPopupAjax(target, label, haveConfirmBox, attachFunName='') {
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	buttons = false;
	if(haveConfirmBox==1) {	buttons = true; 
	}else{ buttons = false;	}
	
	html = createAjaxFormHtml(target,label, buttons,attachFunName);
	$.facebox(html);
			
}
function createAjaxFormHtml(target, label, haveConfirmBox, attachFunName='') {
	tempHtml = '<div class="popupContent">'+label+'</div><div class="gap"></div>';
	if(haveConfirmBox) {
	tempHtml+='<div class="field-wraper"> <div class="field_cover"><a onclick="doRequiredAction'+attachFunName+'('+target+')" class="btn btn--primary faceboxAnchor ajaxRequest">'+yes+'</a> <a class="btn btn--primary faceboxAnchor" onclick="cancelTruncateRequest()" value="Cancel">'+cancel+'</a></div></div>';
	}
	return tempHtml;
}

$(document).ready(function() {
	/*
	* Removed the 'City Deal' Nav link, if User selected Location as 'All Cities' from header(Area Selection).
	*/
	/* if($("#globalCitySelected").attr('data-rel') == 0) {	
		$( "ul.verticaltabs__nav li a" ).each(function( index ) {
		  var navItm = $( this ).attr('rel');
		  if(navItm == "tabs-city-deals") {
				$(this).parent().hide();
			}
		  
		  
		});
	} */

});






