var txtdelcomp;
var txtnotallowed;
var txtinactive;
var txtstatusup;
var txtCompActive;

function deleteCompany(company,page) {
	
	callAjax('company-ajax.php', 'mode=deleteCompany&company='+company, function(t) {
	 
		var total_deals = parseInt(t);
		if ( total_deals > 0 ) {
			requestPopup(1,txtnotallowed,0);
		} else {
			requestPopupAjax(company,txtdelcomp,1,'DeleteComp');
		}
	});
}
function doRequiredActionDeleteComp(company) {
	document.location.href = '?delete='+company;
}

function activeCompany(company_id,status){
	
	var status;
	var company_id;
 	if(status=='0') 
	{
		callAjax('company-ajax.php', 'mode=disapproveUser&company_id='+company_id, function(t){
			if(t == 1){
				//$("#comment-status"+company_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
				$.facebox(txtstatusup);
				$(document).bind('close.facebox', function() {
					window.location.reload(true);
				});
			}else{
				requestPopup(1,txtinactive,0);
				$('#original_span'+company_id).html('');
				$('#original_span'+company_id).html('<span class="statustab addmarg" id="comment-status'+company_id+'" onclick="activeCompany('+company_id+',0);"><span class="switch-labels" data-off="Active" data-on="Inactive"></span><span class="switch-handles"></span></span>');
			}
			
		});
	}
 	if(status=='1'){
		//$('#comment-status'+company_id).html('<img src="../images/ajax2.gif" alt="Loading...">');
		callAjax('company-ajax.php', 'mode=approveUser&company_id='+company_id, function(t){
			var ans=parseJsonData(t);
			if(ans.status == 1){
				$.facebox(txtstatusup);
				$(document).bind('close.facebox', function() {
					window.location.reload(true);
				});
			}else if (ans.status==2){ 
				requestPopupAjax(ans.id,txtCompActive,1,'checkCompanyCommission');  	
				
			} else{
				requestPopup(1,txtinactive,0);
				$('#original_span'+company_id).html('');
				$('#original_span'+company_id).html('<span class="statustab addmarg active" id="comment-status'+company_id+'" onclick="activeCompany('+company_id+',1);"><span class="switch-labels" data-off="Active" data-on="Inactive"></span><span class="switch-handles"></span></span>');
			} 
		});
	}

}
function doRequiredActioncheckCompanyCommission(id) {
	window.location.href = '/manager/companies.php?commssion=1&edit='+id;
}
 function companyChangePassword(company_id){
	jQuery.facebox(function() {
			callAjax('company-ajax.php', 'mode=changePassword&company_id='+company_id, function(t){
			$.facebox(t);
		});
	});
	
} 

function submitChangePassword(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	jQuery.facebox(function() {
	callAjax('company-ajax.php', data, function(t){$.facebox(t);});
	});
	  
}

function companyLocation(company){
	jQuery.facebox(function() {
		callAjax('company-ajax.php', 'mode=companyLocations&company='+company, function(t) {
			$.facebox(t);
		});
	});
}

function addTransaction(company, amount){
	jQuery.facebox(function() {
		callAjax('company-ajax.php', 'mode=addTransaction&company='+company+'&payable_amount='+amount, function(t) {
			$.facebox(t);
		});
	});	
}

function submitAddTransaction(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	
    jQuery.facebox(function() {
		callAjax('company-ajax.php', data, function(t) {
			$.facebox(t);
			$(document).bind('close.facebox', function() {
				window.location.reload(true);
			});
		});
	});
}