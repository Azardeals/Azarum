var txtreload;
var txtoops;

function userUpdateWallet(user_id){
	$.facebox(function(){
	
	callAjax('registered-ajax.php', 'mode=updateWallet&user_id='+user_id, function(t){
		 
		setTimeout(function(){$.facebox(t)},1000);
	
			 
	});
	})
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
				alert(txtoops+' '+txtreload);
				return;
			}
			if(ans.status==0){
				alert(ans.msg + '\n'+txtreload);
				return;
			}
			 
			$.facebox(ans.msg); 
			setTimeout(function(){location.reload()}, 3000);
			 
	});
	  
}