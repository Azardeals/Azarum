var selectCountryFirst;
var txtload;

//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function updateStates(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(selectCountryFirst);
		return;
	}
	
	$('#spn-state').html(txtload+'...');
	callAjax('cities-ajax.php', 'mode=getStatesForZone&country='+country+'&selected='+selectedState, function(t){
		$('#spn-state').html(t);
	});
}

function addState(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(selectCountryFirst);
		return;
	}
	$.facebox(txtload+'...');
	callAjax('cities-ajax.php', 'mode=addState&country='+country, function(t){
		$.facebox(t);
	});
}

function submitAddState(frm,v,country){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	 
	//$.facebox('Processing...');
	callAjax('cities-ajax.php', data, function(t){
		$.facebox(t);
		updateStates(country);
	});
	  
}

function deleteZone(city,page) {
	requestPopupAjax(city,deleteCityMsg,1,'DeleteZone');  
}
function doRequiredActionDeleteZone(city) {
		callAjax('cities-ajax.php', 'mode=deleteZone&city='+city, function(t) {
			document.location.href = '?delete='+city;
		});
}