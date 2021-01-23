var selectedState=0;
var txtreload;
var txtload='Loading';
//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function serachCity(city){
	
	$('#displayStates').html(txtload+'...');
	callAjax(webroot+'common-ajax.php', 'mode=citySearch&city='+city, function(t){
 
		var ans=parseJsonData(t);
			 
			 
			if(ans.status==0){
				alert(ans.msg + '\n'+txtreload);
				return;
			}
			$('#displayStates').html(ans.msg);
	});
}