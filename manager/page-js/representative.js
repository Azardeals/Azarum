var selectedState=0;
var selectCountryFirst;

//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function updateStates(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(selectCountryFirst);
		return;
	}
	$('#spn-state').html('Loading...');
	callAjax('cities-ajax.php', 'mode=getRepresentativeStates&country='+country+'&selected='+selectedState, function(t){
		$('#spn-state').html(t);
	});
}


function activeRepresentative(rep_id,status){//alert(rep_id);
	
	var status;
	var rep_id;
 	if(status=='0') 
	{ 	
		
		callAjax('registered-ajax.php', 'mode=disapproveRepUser&rep_id='+rep_id, function(t){
			 
				//$("#comment-status"+rep_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
				$.facebox('Status Updated.');
				$(document).bind('close.facebox', function() {
					window.location.reload(true);
				});
				//$('#comment-status'+rep_id).html('<a href="javascript:void(0);" onclick="activeRepresentative('+rep_id+',1);"><img src="images/orange-radio-unchk.png" /></a>');
				/* $.mbsmessage('Status Updated.', false); */
			 
			
		});
	}
 	if(status=='1'){
		//$('#comment-status'+rep_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
		callAjax('registered-ajax.php', 'mode=approveRepUser&rep_id='+rep_id, function(t){
		 
				$.facebox('Status Updated.');
				$(document).bind('close.facebox', function() {
					window.location.reload(true);
				});
				
				//$('#comment-status'+rep_id).html('<a href="javascript:void(0);" onclick="activeRepresentative('+rep_id+',0);"><img src="images/green-radio-unchk.png" /></a>');
				/* $.mbsmessage('Status Updated.', false); */
			 
			
		});
	
	}

}

 function payNow(rep_id){
	$.facebox('<img src="'+webroot+'facebox/loading.gif" alt="Loading...">');
	
	callAjax('registered-ajax.php', 'mode=payNow&rep_id='+rep_id, function(t){
		$.facebox(t);
	});
	
} 

function submitPayNow(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	//$.facebox('Processing...');
	callAjax('registered-ajax.php', data, function(t){$.facebox(t);});
	  
}