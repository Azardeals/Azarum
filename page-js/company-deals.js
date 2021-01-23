var txtload;
function addAddress(company,deal_id){ 
  
	if(isNaN(parseInt(company))){
		$('#spn-dealAddress').html('');
		return;
	}
	$('#spn-dealAddress').html(txtload+'...');
	 
	callAjax(webroot+'common-ajax.php', 'mode=getAddress&company='+company+'&deal_id='+deal_id, function(t){
	 
		$('#spn-dealAddress').html(t);
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