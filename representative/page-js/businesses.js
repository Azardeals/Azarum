 var compntinact;

function activeCompany(company_id,status){//alert(company_id);
	
	var status;
	var company_id;
 	if(status=='0') 
	{ 	
		
		callAjax('company-ajax.php', 'mode=disapproveUser&company_id='+company_id, function(t){
			if(t == 1){
				$("#comment-status"+company_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
				$('#comment-status'+company_id).html('<span class="statustab addmarg active"  onclick="activeCompany('+company_id+',1);"><span class="switch-labels" data-off="Active" data-on="Inactive"></span><span class="switch-handles"></span></span>');
				/* $.mbsmessage('Status Updated.', false); */
			}else{
				alert(compntinact);
			}		
			
		});
	}
 	if(status=='1'){
		$('#comment-status'+company_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
		callAjax('company-ajax.php', 'mode=approveUser&company_id='+company_id, function(t){
			 
			if(t == 1){
				$('#comment-status'+company_id).html('<span class="statustab addmarg "  onclick="activeCompany('+company_id+',0);"><span class="switch-labels" data-off="Active" data-on="Inactive"></span><span class="switch-handles"></span></span>');
				/* $.mbsmessage('Status Updated.', false); */
			}else{
				alert(compntinact);
			}	 
			
		});
	
	}

}