var selectedState=0;
var txtselectcountry;
var txtload;


//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function updateStates(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(txtselectcountry);
		return;
	}
	$('#spn-state').html(txtload+'...');
	callAjax(webroot+'cities-ajax.php', 'mode=getStates&country='+country+'&selected='+selectedState, function(t){
		$('#spn-state').html(t);
	});
}