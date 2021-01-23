var selectedState=0;
var selectCountryFirst;
var txtload;
var txtstatusup;
//$(document).ready(function(){updateStates(document.frmCity.city_country.value);});

function updateStates(country){
	if(isNaN(parseInt(country))){
		$('#spn-state').html(selectCountryFirst);
		return;
	}
	$('#spn-state').html(txtload+'...');
	callAjax('cities-ajax.php', 'mode=getAffiliateStates&country='+country+'&selected='+selectedState, function(t){
		$('#spn-state').html(t);
	});
}


function activeAffiliate(affiliate_id,status){//alert(affiliate_id);
	var status;
	var affiliate_id;
 	if(status=='0') 
	{ 	
		
		callAjax('registered-ajax.php', 'mode=disapproveUser&affiliate_id='+affiliate_id, function(t){
				txtstatusup=parseJsonData(t);
				/* $("#comment-status"+affiliate_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
				$('#comment-status'+affiliate_id).html('<a href="javascript:void(0);" onclick="activeAffiliate('+affiliate_id+',1);"><img src="images/orange-radio-unchk.png" /></a>'); */
				if(typeof txtstatusup != "undefined")
				$.facebox(txtstatusup.msg);
				$(document).bind('close.facebox', function() {
					window.location.reload(true);
				});
			
		});
	}
 	if(status=='1'){
		//$('#comment-status'+affiliate_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
		callAjax('registered-ajax.php', 'mode=approveUser&affiliate_id='+affiliate_id, function(t){
					txtstatusup=parseJsonData(t);
				/* $('#comment-status'+affiliate_id).html('<a href="javascript:void(0);" onclick="activeAffiliate('+affiliate_id+',0);"><img src="images/green-radio-unchk.png" /></a>'); */
				if(typeof txtstatusup != "undefined")
				$.facebox(txtstatusup.msg);
				$(document).bind('close.facebox', function() {
					window.location.reload(true);
				});
		});
	
	}

}