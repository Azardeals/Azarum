var frmValidator;
var txtload;
var txtreload;
var txtoops;
var txtsuredel;
var txtstatusup;
function activeUser(user_id,status){//alert(user_id);
	
	var status;
	var user_id;
 	if(status=='0') 
	{ 	
		/* $("#comment-status"+user_id).html('<img src="../images/ajax2.gif" alt="Loading...">'); */
		callAjax('cms-ajax.php', 'mode=disapproveUser&user_id='+user_id, function(t){
			//$('#comment'+user_id).html('<a href="javascript:void(0);" onclick="activeUser('+user_id+',1);" ><img src="images/orange-radio-unchk.png" /></a>');
			$.facebox(txtstatusup);
			$(document).bind('close.facebox', function() {
				window.location.reload(true);
			});
			//var dell = "return (confirm(txtsuredel));";
			
			/* $('#comment-status'+user_id).html('<a href="javascript:void(0);" onclick="activeUser('+user_id+',1);" class="btn green"><img src="images/orange-radio-unchk.png" /></a><a href="discussions.php?delete='+user_id+'" onclick="'+dell+'" class="btn delete">Delete User</a><a href="javascript:void(0);" onClick="userChangePassword('+user_id+');" class="btn">Change Password</a>  ');  */
			
		});
	}
 	if(status=='1'){
		/* $('#comment-status'+user_id).html('<img src="../images/ajax2.gif" alt="Loading...">'); */
		
		callAjax('cms-ajax.php', 'mode=approveUser&user_id='+user_id, function(t){
			//$('#comment'+user_id).html('<a href="javascript:void(0);" onclick="activeUser('+user_id+',0);" ><img src="images/green-radio-unchk.png" /></a>');
			$.facebox('Status Updated.', false);
			$(document).bind('close.facebox', function() {
				window.location.reload(true);
			});
			//var dell = "return (confirm('Are you sure to delete?'));";
			/* $('#comment-status'+user_id).html('<a href="javascript:void(0);" onclick="activeUser('+user_id+',0);" class="btn delete">Inactive</a><a href="discussions.php?delete='+user_id+'" onclick="'+dell+'" class="btn delete">Delete User</a><a href="javascript:void(0);" onClick="userChangePassword('+user_id+');" class="btn">Change Password</a> '); */
		});
	
	}

}


 function userChangePassword(user_id){
	//$.facebox('<img src="'+webroot+'facebox/loading.gif" alt=txtload+"...">');
	
	//callAjax('registered-ajax.php', 'mode=changePassword&user_id='+user_id, function(t){
	//	$.facebox(t);
	//});
	
	jQuery.facebox(function() {
		callAjax('registered-ajax.php', 'mode=changePassword&user_id='+user_id, function(t){
		setTimeout(function(){$.facebox(t)},1000);
	});
	}) 
	
} 

function submitChangePassword(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	//$.facebox('Processing...');
	callAjax('registered-ajax.php', data, function(t){$.facebox(t);});
	  
}

 function userUpdateWallet(user_id){
	//$.facebox('<img src="'+webroot+'facebox/loading.gif" alt="Loading...">');
	
	$.facebox(function(){callAjax('registered-ajax.php', 'mode=updateWallet&user_id='+user_id, function(t){
		 
			$.facebox(t); 
	
			 
	});});
	
} 

function submitUserUpdateWallet(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
 
	//$.facebox('Processing...');
	callAjax('registered-ajax.php', data, function(t){ 
		 /*  $.facebox(t);   */
		 
		 var ans=parseJsonData(t);
			 
			if(ans===false){
				requestPopup(1,txtoops+' '+txtreload,0);  
				return;
			}
			if(ans.status==0){
				requestPopup(1,ans.msg + '\n'+txtreload,0);  
				return;
			}
			 
			$('#wallet_'+ans.id).html(ans.wallet); 
			$.facebox(ans.msg); 
			 
	});
	  
}

 