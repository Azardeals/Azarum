var selectedState=0;
var selectCountryFirst;
var txtloading;
//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function updateStates(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(selectCountryFirst);
		return;
	}
	$('#spn-state').html(txtloading+'...');
	callAjax('cities-ajax.php', 'mode=getCharityStates&country='+country+'&selected='+selectedState, function(t){
		$('#spn-state').html(t);
	});
}