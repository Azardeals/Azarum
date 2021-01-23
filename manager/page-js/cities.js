var selectedState=0;
var selectCountryFirst;
var txtload;

//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function updateStates(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(selectCountryFirst);
		return;
	}
	$('#spn-state').html(txtload+'...');
	callAjax('cities-ajax.php', 'mode=getStates&country='+country+'&selected='+selectedState, function(t){
		$('#spn-state').html(t);
	});
}

function addState(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(selectCountryFirst);
		return;
	}
jQuery.facebox(function () {
	callAjax('cities-ajax.php', 'mode=addState&country='+country, function(t){
		$.facebox(t);
	});
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

function deleteCity(city,page) {
	
	callAjax('cities-ajax.php', 'mode=deleteCity&city='+city, function(t) {
		var total_deals = parseInt(t);
		if ( total_deals > 0 ) {
			requestPopup(1,cityDeletion,0);
		} else {
			requestPopupAjax(city,deleteCityMsg,1,'DeleteCity'); 
			
		}
	});
}
function doRequiredActionDeleteCity(city) {
	document.location.href = '?delete='+city;
}

 function deleteMultipleCities(){
	 if($('[name="cities[]"]:checked').length ==0 ){
		 requestPopup(1,city_alert,0);
		 return false;
	 }
	 requestPopupAjax(1,deleteCityMsg,1,'DeleteMultipleCities'); 
 }
 function doRequiredActionDeleteMultipleCities(city) {
	 city_ids=$('.tbl_data input[type="checkbox"]').serialize();
	 callAjax('cities-ajax.php',  city_ids + '&mode=deleteMultipleCities', function (t) {
			  var ans = parseJsonData(t);
				if(ans){
					jQuery.facebox(function () {
						$.facebox(ans.msg)
					setTimeout(function () {
						location.reload()
					}, 1500);
					});
				}
		});
}