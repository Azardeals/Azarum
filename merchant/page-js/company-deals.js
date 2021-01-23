var txtload;
var txtreload;
var txtoops;
var txtselectadd;

function addAddress(company,deal_id){ 
  
	if(isNaN(parseInt(company))){
		$('#spn-dealAddress').html('');
		return;
	}
	$('#spn-dealAddress').html(txtload+'...');
	 
	callAjax(webroot+'common-ajax.php', 'mode=getAddress&company='+company+'&deal_id='+deal_id, function(t){
	 var ans=parseJsonData(t);
	if(ans===false){
				requestPopupAjax(1,txtoops+' '+txtreload,0);  
				return;
			}
			if(ans.status==0){
				$('#spn-dealAddress').html(ans.msg);
				return;
			}
		$('#spn-dealAddress').html(ans.msg);
	});
}

function addAddressEdit(company){
	if(isNaN(parseInt(company))){
		$('#spn-dealAddress').html('');
		return;
	}
	$('#spn-dealAddress').html(txtload+'...');
	callAjax(webroot+'common-ajax.php', 'mode=getAddress&company='+company+'&selected='+selectedCompany, function(t){
		$('#spn-dealAddress').html(t);
	});
}

function updateMaxCoupons(val){
	var totalAddress = $('input[id*=dac_address_capacity]').length;
	var totalValue =0;
	var checkCheckbox = 0; 
	for(var i = 1; i <= totalAddress; i++){
		var checkid = 'dac_address_id'+i;
		var id = 'dac_address_capacity'+i;
		if(document.getElementById(checkid).checked == false){
			checkCheckbox++;
			$("#"+id).val(0);
		}else{
		 
		 
		
		var newValue = $("#"+id).val();//document.getElementById(id).value;
		if(parseInt(newValue)) totalValue = (parseInt(totalValue)+parseInt(newValue)) ;
		}
		
	}
	if(checkCheckbox == totalAddress) $.facebox(txtselectadd);
	document.getElementById('deal_max_coupons').value = totalValue;
}

function deleteDeal(id)
{
	requestPopupAjax(id,deletemsg,1,'DeleteDeal');   
}
function doRequiredActionDeleteDeal(id) {
	jQuery.facebox(function() {
	callAjax('deals-ajax.php', 'mode=deleteDeal&id='+id, function(t){
		
			var ans=parseJsonData(t);
			if(ans===false){
				$.facebox(txtoops + t);
				return;
			}
			$.facebox(ans.msg);
			if(ans.status==0) return;
			location.reload();
		});
	});
}
	