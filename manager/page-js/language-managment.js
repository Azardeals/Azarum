var selectedState=0;


//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function changeLanguage(lang){
	 
	callAjax('language-ajax.php', 'mode=defaultLanguage&lang='+lang, function(t){
		$.facebox(t);
		setTimeout(function(){ document.location.reload(true);}, 2000);
	});
}

 