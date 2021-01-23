/**
 * 
 */

var qty;
var giftqty;
var minBuy;
var maxBuy;
var frmValidator;

if (typeof deal_id == 'undefined') {
    var deal_id;
}

var addresses;

$(document).ready(function () {
  walletChargeConfirmation();
});


/* deal end time calculation */
var end_time;



/* deal end time calculation */


$(document).ready(function () {

     loadPage('payment');


	
}); 





function walletChargeConfirmation() {
    $('#tabs1').html('<img src="' + webroot + 'facebox/loading.gif">');
    callAjax(webroot + 'buy-deal-ajax.php', 'mode=walletChargeConfirmation', function (t) {
        /* $('#paymentInfo').show(); */
        $('#tabs1').html(t);
       
    });
}



function updateDropDown(dealkey, obj) {

    dealkey = dealkey;
    company_address_id = $(obj).val();
//alert(dealkey);

    callAjax(webroot + 'common-ajax.php', 'mode=updateDropDown&dealkey=' + dealkey + '&company_address_id=' + company_address_id, function (t) {
        var ans = parseJsonData(t);
        if (ans.status == 1) {
            var id = dealkey.replace(/[^A-Za-z0-9]/gi, '');
            //  alert( $('#'+id).html());
            $('#' + id).html(ans.msg);
            //     $('.qty_select_box').trigger( "change" );
            //   $.mbsmessage(txtaddressupdated, true);

            callAjax(webroot + 'common-ajax.php', 'mode=updateCart', function (t) {
                var ans = parseJsonData(t);
                $.mbsmessage('Cart updated.', true);
                updateCart(ans.cart_vals);
            });
            return;
        }

        else {
            $.facebox(ans.msg);
        }
    });
}

function showInfodiv(wallet) {

    $('#tabs3').html('<img src="' + webroot + 'facebox/loading.gif">');
    if (typeof wallet === "undefined")
        wallet = 0;
    //alert(wallet);
    callAjax(webroot + 'buy-deal-ajax.php', 'mode=authform&wallet=' + wallet, function (t) {
        var ans = parseJsonData(t);
        if (ans.status == 0) {
            $.facebox(ans.msg);
        } else {
              $('#tabs3').html(t);
            $('#tabs3').show();
        }
    });
}

function showCimInfo(newCard) {
    $('#tabs4').html('<img src="' + webroot + 'facebox/loading.gif">');
    if (typeof newCard === "undefined")
        newCard = '';
    callAjax(webroot + 'buy-deal-ajax.php', 'mode=addBuyCard&newCard=' + newCard, function (t) {
     
        $('#tabs4').html(t);
    });
}

function redirectPaypal() {
    $('#tabs2').html('<img src="' + webroot + 'facebox/loading.gif">');
    callAjax(webroot + 'buy-deal-ajax.php', 'mode=paypal', function (t) {
      
        $('#tabs2').html(t);
    });
}





function doExecuteRequest(el, data) {

    // $.mbsmessage(txtprocessing);
    callAjax(webroot + 'common-ajax.php', data, function (t) {

        var ans = parseJsonData(t);

        //   $.mbsmessage.close();
        if (el.disabled)
            el.disabled = false;
        if (ans === false) {
            alert(txtoops + ' ' + txtreload);
            return false;
        }
        if (ans.status == 0) {
            jQuery.facebox(function () {
                setTimeout(function () {
                    $.facebox(ans.msg)
                }, 500);
            });
            // $.facebox(ans.msg);
            return false;
        } else {
            if (typeof ans.cart_vals != "undefined") {
                //     $.mbsmessage('Cart updated.', true);
                updateCart(ans.cart_vals);
            } else {
                location.reload(true);
            }
            return true;
        }
    });

    return false;
}

function updateCart(cart_vals) {
	
    if (typeof cart_vals == "undefined")
        return;
    var ptotal = 0;
    var stotal = 0;
    var gtotal = 0;
    $.each(cart_vals['cart'], function (i) {
        var key_id = (this.key).replace(/[^A-Za-z0-9]/g, '');

        if (typeof this.error != "undefined") {
            var err_el = $('#error_msg_' + this.key_id);
            err_el.html(this.error);
            err_el.fadeIn("slow", function () {
            });
        } else {
            $('#error_msg_' + this.key_id).fadeOut("slow", function () {
                $(this).text('');
            });
        }

        $('#price_' + key_id).text(formatNumber(this.price, true));
        ptotal = this.qty * this.price;
        $('#ptotal_' + key_id).text(formatNumber(ptotal, true));
        stotal += ptotal;
    });
	$('.tax').text(formatNumber(cart_vals['tax'], true));
    $('#cart_sub_total').text(formatNumber(stotal, true));
    $('.cart_sub_total').text(formatNumber(stotal, true));
    $('#cart_discount').text(formatNumber(cart_vals['discount']['value'], true));
    if (parseFloat(cart_vals['discount']['value']) > 0) {
        $('#coupon_value_box').html('<span>' + cart_vals['discount']['code'] + '</span> -' + formatNumber(cart_vals['discount']['value'], true) + ' <span id="removeDiscount" style="color:#000;cursor:pointer;">X</span>');
    } else {
        $('#coupon_value_box').text('-' + formatNumber(cart_vals['discount']['value'], true));
    }
	
    if (typeof cart_vals['shipping'] != 'undefined' && parseFloat(cart_vals['shipping']) >= 0) {
        stotal = stotal + formatNumber(cart_vals['shipping']);
        $('#cart_shipping_charges').text(formatNumber(cart_vals['shipping'], true));
    }
	stotal = stotal+cart_vals['tax'];
    gtotal = stotal - cart_vals['discount']['value'];
    if (gtotal < 0)
        gtotal = 0;
    $('.cart_grand_total').text(formatNumber(gtotal, true));

    if (cart_vals['cart_options'] != undefined) {
        $("#cart_summary .cart_summary_options").remove();
		$("#cart_summary .Info-tbl-in").remove();
        $("#cart_summary tr:first").after(cart_vals['cart_options']);
    }


    return false;
}
function formatNumber(number, with_currency) {
    if (typeof number == "undefined")
        return 0;
    if (with_currency === true) {
        return (cleft + (Math.round(number * 100) / 100).toString() + cright);
    }
    return (Math.round(number * 100) / 100);
}




function updateShippingDetails(frm, v) {
    v.validate();
    if (!v.isValid())
        return false;
    var data = getFrmData(frm);
    callAjax(webroot + 'common-ajax.php', data, function (t) {
        var ans = parseJsonData(t);
        if (ans === false) {
            alert(txtoops + ' ' + txtreload);
            return false;
        }
        if (ans.status == 1) {
            if (typeof ans.cart_vals != 'undefined')
                updateCart(ans.cart_vals);
            //loadPage('payment');
			loadPage('reviewOrder');
            $.mbsmessage(ans.msg, true);
            return false;
        }
        $.facebox(ans.msg);
        return false;
    });
    return false;
}


function loadPage(id) {
    $('.cartboxes').css('display', 'none');
    $('.step').removeClass('selected');
    var container = $('#' + id);
    container.css('display', 'block');
    container.parent().css('display', 'block');
    container.parent().parent().prevAll( ".step" ).addClass('selected');
    container.html('<img src="' + webroot + 'facebox/loading.gif">');
    if (id == 'shipping') {
        var data = 'mode=loadShippingForm';
    } else if(id == 'reviewOrder'){
     var data = 'mode=loadReviewData';
    }else {
        var data = 'mode=loadPaymentMethods';
    }
  
    callAjax(webroot + 'buy-deal-ajax.php', data, function (t) {

        /* if (parseInt(t) === 1) {
            loadPage('payment');
            return false;
        } */
		if (parseInt(t) === 1) {
            loadPage('reviewOrder');
            return false;
        }
		
        $('.deal-product').css('display', 'none');
        if (t == 'emptyCart') {
            location.reload(true);
            exit();
        }
        container.html(t);
        if (t === txtsessionexpire) {

            $('.topsection ').html('');
            return false;
        }
        container.css('display', 'block');
        $(document).scrollTop(0);

        if (id == 'shipping') {
           
            $('.shipment_boxes').css('display', 'block');
            $('#ship_sum_container').css('display', 'table-row');
            $('#ship_country').trigger('change');
			$('.step__top ').html(' <h5>' + txtshippingAdd + '</h5><a  class="linknormal" href="javascript:void(0)" onclick="return resetForm(\'frmShipping\');">' + txtaddnew + '</a>');
            setTimeout(function () {
                $.each(addresses, function (i) {

                    if (addresses[i]['deafult'] > 0) {
                        $("#ship_state option").attr('selected', false)
                                .filter('[value="' + addresses[i]['ship_state'] + '"]')
                                .attr('selected', true);

                        //   $("#ship_state").trigger('change');

                        setTimeout(function () {
                            $("#ship_city option").attr('selected', false)
                                    .filter('[value="' + addresses[i]['ship_city'] + '"]')
                                    .attr('selected', true);
                        }, 400);

                    }
                });
            }, 700);
        }
        if (id == 'payment') {
         $('.topsection ').html('<a style="float:right;" class="green_button" href="javascript:void(0)" onclick="return setbackForm();">' + txtbackbutton + '</a><h2>' + txtselectpaymthod + '</h2>');
            $('.right-pnl').css('display', 'block');
            walletChargeConfirmation();
           // $('#payment').trigger("click");
            paymenttabScript();
        }
        return false;
    });
    return false;
}
function setbackForm(){
	var data = 'mode=loadShippingForm';
	callAjax(webroot + 'buy-deal-ajax.php', data, function (t) {

        if (parseInt(t) === 1) {
             //location.reload(true);
			 window.location.href=webroot+'buy-deal';
            return false;
        }
		else{
			$('.make-payment').css('display','none');
			loadPage('shipping');
		}
	});
}
function setAddress(el, sid) {

    if (typeof addresses[sid] == "undefined")
        return false;
    $("ul.add_list li a").removeClass('active');
    $('.green_button').css('display', 'none');
    $(el)./* parent(). */addClass("active");
    var trg_change = false;
    var trg_change2 = false;
    $.each(addresses[sid], function (i) {
        if (i == 'ship_country' && this.toString() != $('#ship_country').val()) {
            trg_change = true;
        }
        if (i == 'ship_state' && this.toString() != $('#ship_state').val()) {
            trg_change2 = true;
        }

        $('#frmShipping').find('input[name="' + i + '"], select[name="' + i + '"]').val(this.toString());

        if (i == 'ship_city') {
            $("#ship_city").val(addresses[sid]['ship_city_name']);
        }

    });

    if (trg_change === true) {
         $("#btn_save_shipadr").prop('disabled', true);
        $('#ship_country').trigger('change');

         setTimeout(function () {
            $("#ship_state option").attr('selected', false)
                    .filter('[value="' + addresses[sid]['ship_state'] + '"]')
                    .attr('selected', true);
            $('#ship_state').trigger('change');
        }, 900);

            setTimeout(function () {
            $("#ship_city option").attr('selected', false)
                    .filter('[value="' + addresses[sid]['ship_city'] + '"]')
                    .attr('selected', true);
        }, 500);

    }

    $("#btn_save_shipadr").prop('disabled', false);
    setTimeout(function () {
        $('.green_button').css('display', 'block');
    }, 2000);
    return false;
}
$('.errorTr').live('click', function () {
    $(this).find('td').fadeOut('slow', function () {
        $(this).text("")
    });
});


function loadStates(el) {
    var data = 'mode=loadStates&country_id=' + el.value;
    callAjax(webroot + 'buy-deal-ajax.php', data, function (t) {
        var ans = parseJsonData(t);
        if (ans === false) {
            alert(txtoops + ' ' + txtreload);
            return false;
        }
        if (ans.status == 0) {
            $.facebox(ans.msg);
            return false;
        } else {
            $('#ship_state').html('');
            if (ans.states != "") {
                $.each(ans.states, function (i) {
                    $('#ship_state').append('<option value="' + i + '">' + this.toString() + '</option>');
                });
            } else {
                $('#ship_state').html('<option value="">Select</option>');
            }
        }
        return false;
    });
    return false;
}
$('#ship_country').live('change', function () {
    updateShippingCharges(this);
});

function updateShippingCharges(el) {
    if (typeof el == "undefined")
        return false;
    if (el.value == "") {
        return false;
    }
    el.disabled = true;
    var data = 'mode=updateShippingCharges&cid=' + el.value;
    doExecuteRequest(el, data);
    return false;
}

function loadCities(el) {
    var data = 'mode=loadCities&state_id=' + el.value;
    callAjax(webroot + 'buy-deal-ajax.php', data, function (t) {
        var ans = parseJsonData(t);
        if (ans === false) {
            alert(txtoops + ' ' + txtreload);
            return false;
        }
        if (ans.status == 0) {
            $.facebox(ans.msg);
            return false;
        } else {
            $('#ship_city').html('');
            $.each(ans.cities, function (i) {
                $("#ship_city").val(this.toString());
                //$('#ship_city').append('<option value="'+i+'">'+this.toString()+'</option>');
            });



        }
        return false;
    });
    return false;
}


function resetForm(id) {
    if (id == 'frmShipping') {
        $('#' + id).find('input[type="text"], select').val("");
        $("#" + id).find('input[name="uaddr_id"]').val(0);
        $("#" + id).find('select[name="ship_state"]').html('');
        $("#" + id).find('select[name="ship_city"]').html('');
    } else {
        $('#' + id).find('input[type="text"], select').val("");
    }
    document.getElementById(id).elements[0].focus();
    return false;
}

function setDisableCreditCardButton(v) {
    v.validate();
    if (!v.isValid()) {
        return false;
    }
    else {

        $('input[type="submit"]').attr('onclick', 'return false;');
        return true;
    }
}