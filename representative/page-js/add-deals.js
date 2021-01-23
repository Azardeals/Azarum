
var deal_id=0;
var txtoops;
var txtreload;
var checkAdressMsg;


function addAddress(company,deal_id){ 
  
	if(isNaN(parseInt(company))){
		$('#spn-dealAddress').html('');
		return;
	}
	$('#spn-dealAddress').html('Loading...');
	 
	callAjax(webroot+'common-ajax.php', 'mode=getAddress&company='+company+'&deal_id='+deal_id, function(t){
	 var ans=parseJsonData(t);
	if(ans===false){
				alert(txtoops+' '+txtreload);
				return;
			}
			if(ans.status==0){
				$('#spn-dealAddress').html(ans.msg);
				//alert(ans.msg + '\nPlease reload the page and try again');
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
	$('#spn-dealAddress').html('Loading...');
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
	if(checkCheckbox == totalAddress) $.facebox(checkAdressMsg);
	document.getElementById('deal_max_coupons').value = totalValue;
}
 