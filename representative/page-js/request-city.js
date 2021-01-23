var selectedState=0;


//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function updateStates(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html('Select Country First');
		return;
	}
	$('#spn-state').html('Loading...');
	callAjax(webroot+'cities-ajax.php', 'mode=getStates&country='+country+'&selected='+selectedState, function(t){
		$('#spn-state').html(t);
	});
}