var deal_id = 0;
var txtload;
var txtreload;
var txtoops;
var txtselectadd;

function addRemoveBookingRequestDate(obj, dealId, subdealId)
{
    date = $(obj).attr("id");
    //var price = $(obj).find(".text-info").text();
    jQuery.facebox(function () {
        callAjax('deals-ajax.php', 'mode=addRemoveBookingRequestDate&dealId=' + dealId + '&sub_deal_id=' + subdealId + '&date=' + date, function (t) {
            var ans = parseJsonData(t);
            if (ans === false) {
                $.facebox(txtoops + ' ' + t);
                return;
            }
            $.facebox(ans.msg);
        });
    });
}
function addQuantityPrice(obj, dealId, subdealId) {
    date = $(obj).attr("id");
    jQuery.facebox(function () {
        callAjax('deals-ajax.php', 'mode=fetchBookingDateForm&dealId=' + dealId + '&sub_deal_id=' + subdealId + '&date=' + date, function (t) {
            $.facebox(t);
        });
    });
}

function bookingFormSubmit(frm, v)
{
     date = $("input[name=dbdate_date]").val();
    if (v == '' || v == 'undefined') {
        $.facebox('Invalid Request!');
        return false;
    }
    v.validate();
    if (!v.isValid()) {
        return false;
    }
    var data = getFrmData(frm);

    jQuery.facebox(function () {

        callAjax('deals-ajax.php', data, function (t) {
            common_msg = "<span class='common_msg' style='font-size: 18px;' >Updated Successfully</span>";
            $('#' + date).trigger('click');
        });
    });

}

function deleteOnlinebookingDealRecord(dbdate_id) {
    
    date = $("input[name=dbdate_date]").val();
    //if(r){
    jQuery.facebox(function () {

        callAjax('deals-ajax.php', 'mode=deleteOnlinebookingDealRecord&dbdate_id=' + dbdate_id, function (t) {
            common_msg = "<span class='common_msg' style='font-size: 18px;' >Blocked Successfully</span>";
            $('#'+date).trigger('click');
        });
    });

    //}

}
function requestFormSubmit(frm, v)
{
    if (v == '' || v == 'undefined') {
        $.facebox('Invalid Request!');
        return false;
    }
    v.validate();
    if (!v.isValid()) {
        return false;
    }

    var data = getFrmData(frm);

    date = $("input[name=dbdate_date]").val();
    jQuery.facebox(function () {
        callAjax('deals-ajax.php', data, function (t) {
            var ans = parseJsonData(t);
            if (ans.msg == "Date Unavailable")
            {
                $('#' + date).parent().addClass('block');
                $('#' + date).parent().prop('title', 'Unavailable');
            } else {
                $('#' + date).parent().removeClass('block');
                $('#' + date).parent().prop('title', 'Available');
            }
            $.facebox(ans.msg);
        });
    });
}

function checkCharity() {
    var amount = parseInt($('#charity_amount').val());
    if ((amount != 0) && amount != "") {
        if ($('#deal_charity').val() == "") {
            requestPopupAjax(1,txtCharity,0);
            return false;
        } else
            return true;
    } else
        return true;
}
function addAddress(company, deal_id) {

    if (isNaN(parseInt(company))) {
        $('#spn-dealAddress').html('');
        return;
    }
    $('#spn-dealAddress').html(txtload + '...');

    callAjax(webroot + 'common-ajax.php', 'mode=getAddress&company=' + company + '&deal_id=' + deal_id, function (t) {
        var ans = parseJsonData(t);
        if (ans === false) {
            requestPopupAjax(1,txtoops + ' ' + txtreload,0);
            return;
        }
        if (ans.status == 0) {
            $('#spn-dealAddress').html(ans.msg);
            //alert(ans.msg + '\nPlease reload the page and try again');
            return;
        }
        $('#spn-dealAddress').html(ans.msg);
		//setTimeout(checkQty,1500);
    });
	
	
}

function changeAddress(company, deal_id, previous_company_id)
{
	confirmPopup(companyChangeMessage,company, deal_id, previous_company_id);
}
function confirmPopup(label, company, deal_id, previous_company_id) {
	event.preventDefault();
	html = '<div class="popupContent">'+label+'</div><div class="gap"></div>';
	html+='<div class="field-wraper"> <div class="field_cover"><a onclick="addAddress('+company+','+ deal_id+')" class="btn btn--primary faceboxAnchor">'+yes+'</a> <a class="btn btn--primary faceboxAnchor" onclick="cancelRequest('+previous_company_id+')" value="Cancel">'+cancel+'</a></div></div>';
	$.facebox(html);			
}
function cancelRequest(previous_company_id) { 
	/* $('select[name="deal_company"]').val(previous_company_id);
	$(document).trigger('close.facebox'); */
    return false;
}

function checkQty(){
	e = $.Event('keyup');
	e.keyCode= 13; // enter

	$('input[id*=dac_address_capacity]').trigger(e);
     //   $('input[id*=dac_address_capacity]').trigger('change');

}
function addAddressEdit(company) {

    if (isNaN(parseInt(company))) {
        $('#spn-dealAddress').html('');
        return;
    }
    $('#spn-dealAddress').html(txtload + '...');
    callAjax(webroot + 'common-ajax.php', 'mode=getAddress&company=' + company + '&selected=' + selectedCompany, function (t) {
        $('#spn-dealAddress').html(t);
    });
}

function updateMaxCoupons(val) {
    var totalAddress = $('input[id*=dac_address_capacity]').length;
    var totalValue = 0;
    var checkCheckbox = 0;
    for (var i = 1; i <= totalAddress; i++) {
        var checkid = 'dac_address_id' + i;
        var id = 'dac_address_capacity' + i;
        if (document.getElementById(checkid).checked == false) {
            checkCheckbox++;
            $("#" + id).val(0);
        } else {



            var newValue = $("#" + id).val();//document.getElementById(id).value;
            if (parseInt(newValue))
                totalValue = (parseInt(totalValue) + parseInt(newValue));
        }

    }
    if (checkCheckbox == totalAddress)
        $.facebox(txtselectadd);
  
    document.getElementById('deal_max_coupons').value = totalValue * dayDiff;
}

function shippingInfoValidate(frm) {
    $(".err_msg").remove();

    if ($("select[name='deal_shipping_type']").find("option:selected").val() == 0) {
        if ($("input[name='deal_shipping_charges_us']").val() == '') {
            $("input[name='deal_shipping_charges_us']").after("<span style='color:#f00' class='err_msg'><br/>Shipping Charges for US is mandatory</span>");
            return false;
        }
    }

    if ($("select[name='deal_shipping_type']").find("option:selected").val() == 1) {
        if ($("input[name='deal_shipping_charges_worldwide']").val() == '') {
            $("input[name='deal_shipping_charges_worldwide']").after("<span style='color:#f00' class='err_msg'><br/>Shipping Charges for Worldwide is mandatory</span>");
            return false;
        }
    }
    return true;
}
function deleteSubdeal(sub_deal_id) {
	requestPopupAjax(sub_deal_id,deletemsg,1,'DeleteSubdeal');   
}
function doRequiredActionDeleteSubdeal(sub_deal_id) {
	jQuery.facebox(function () {
            callAjax('deals-ajax.php', 'mode=deleteSubdeal&sub_deal_id=' + sub_deal_id, function (t) {
                location.reload();
            });
        });
}

function addSubDeall(option_row) {
    size = $('.subdealtable').size();
    option_row = size + 1;
    html = '<tbody id="subdeal-option' + option_row + '" class="subdealtable">';

    html += '  <tr>';
    html += '    <td>Deal Name</td><td><input type="text" name="sub_deal[' + option_row + '][sdeal_name]" value="" /></td></tr>';
    html += '<tr><td>Deal  Price</td><td><input type="text" name="sub_deal[' + option_row + '][sdeal_original_price]" value="" /></td></tr>';
    html += '<tr><td>Deal  Discount</td><td><input type="text" name="sub_deal[' + option_row + '][sdeal_discount]" value="" /><select name="sub_deal[' + option_row + '][sdeal_discount_is_percentage]">';
    html += '      <option value="0">Fixed Amount</option>';
    html += '      <option value="1">%</option>';
    html += '    </select>&nbsp;&nbsp;</td></tr>';
    html += '<tr><td>Deal Max Coupon</td><td><input type="text" name="sub_deal[' + option_row + '][sdeal_max_coupons]" value="" /></td></tr>';
    html += '<tr><td>Deal  Status</td><td><select name="sub_deal[' + option_row + '][sdeal_active]">';
    html += '      <option value="1">Active</option>';
    html += '      <option value="0">Inactive</option>';
    html += '    </select>&nbsp;&nbsp;<a class="button gray"onclick="$(\'#subdeal-option' + option_row + '\').remove();">Remove</a></td></tr>';


    html += '</tbody>';

    $('#subdeal tfoot').before(html);

    option_row++;
    //  alert(html);

}

function removeDigitalFile(productId)
{
	requestPopupAjax(productId,confirmMsg,1,'RemoveDigitalFile');     
}
function doRequiredActionRemoveDigitalFile(productId) {
        var productId = parseInt(productId);
        jQuery.facebox(function () {
            callAjax('deals-ajax.php', 'mode=deleteDigitalFile&productId=' + productId, function (t) {
				location.reload();
            });
        });
}

function viewTaxRate() {
    var taxClassId = $('#deal_taxclass_id').val();
    if (taxClassId == "") {
        requestPopupAjax(1,txtTaxClass,0);
        return false;
    }
    jQuery.facebox(function () {
        callAjax('deals-ajax.php', 'mode=fetchTaxRate&classId=' + taxClassId, function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
        });
    });
}

function checkformValidation(obj,v)
{
    v.validate();
	if(!v.isValid()){
      return false;  
    }
	/* if($(obj).validate() === false)
	{
	alert($(obj).validate()); 
	$('html,body').animate({
	scrollTop: $(".errorlist").offset().top},
	'slow');
	return false;
	}	 */
}


function updateFormRequirements(el, o) {
    value = el.val();
    //if(eval("'"+value+"'" + o.operator + "'"+o.val+"'")==false) return;
    switch (o.operator) {
        case 'eq':
            if (!(value == o.val))
                return;
            break;
        case 'ne':
            if (!(value != o.val))
                return;
            break;
        case 'lt':
            if (!(value < o.val))
                return;
            break;
        case 'le':
            if (!(value <= o.val))
                return;
            break;
        case 'gt':
            if (!(value > o.val))
                return;
            break;
        case 'ge':
            if (!(value >= o.val))
                return;
            break;
    }
  $("#"+o.form_id).unbind("submit");
    eval(o.validator_requirements + '.' + o.fldname + '=' + o.requirement);
    eval(o.validator_object + '=$("#' + o.form_id + '").validation(' + o.validator_requirements + ', ' + o.validator_formatting + ');');
}	