var selectCountryFirst;
var txtload;
//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});
function getsamevalue(obj) {
    value1 = $(obj).val();
    $('.taxrule_taxrate_id').not(obj).each(function () {
        if ($(this).val() == value1) {
			requestPopup(1,labelTaxRate,0);  
            $(obj).val('');
        }
    });
}
function addRule(taxrateId, basedOn) {
    var element = [0];
    count = $('#tax-rule tbody tr').length;

    $('#spn-state').html(txtload + '...');
    $('.taxrule_taxrate_id').each(function () {
        element.push($(this).val());
    });


    callAjax('index-ajax.php', 'mode=getTaxRate&taxrate_id=' + element, function (t) {

        var html = '<tr id="tax-rule-row' + count + '" ><td><select class="taxrule_taxrate_id"  onchange= "getsamevalue(this);" name="data[' + count + '][taxrule_taxrate_id]">' + t + '</select></td>';
		//option += '<option value="2">Billing Address</option>';
        var option = '<option value="0">Select</option>';
        option += '<option value="1">Store Address</option>';
        
        option += '<option value="3">Shipping Address</option>';
        html += '<td><select id="taxrule_tax_based_on" name="data[' + count + '][taxrule_tax_based_on]">' + option + '</select></td>';
        html += '<td class="left"><a class="button small" onclick="$(\'#tax-rule-row' + count + '\').remove();">' + remove + '</a>';
        $('#tax-rule tbody').append(html);
    });
}



function deleteTaxRuleRecord(taxrule_id, count) {
	requestPopupAjax(taxrule_id,deleteCityMsg,1,'DeleteTax');  
}
function doRequiredActionDeleteTax(taxrule_id) {
        callAjax('index-ajax.php', 'mode=deleteTaxRuleRecord&taxrule_id=' + taxrule_id, function (t) {
            var ans = parseJsonData(t);
            $.facebox(ans.msg);
            $('#tax-rule-row' + taxrule_id).remove();
        });
}