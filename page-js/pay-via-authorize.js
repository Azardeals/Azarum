var txteditgift;

function askFriendInfo(){
	$.facebox('<img src="'+webroot+'facebox/loading.gif" alt="Loading...">');
	callAjax(webroot+'buy-deal-ajax.php', 'mode=askFriendInfo', function(t){
		$.facebox(t);
	});
}
 

function askFriendInfoGift(){
 
 
	  
		callAjax(webroot+'common-ajax.php', 'mode=giftInfo', function(t){
		$('#giftInfo').html('<a href="javascript:void(0);" class="linkGfit"> Gift For : </a> <span class="user">'+t+'</span> <a href="javascript:void(0);" onclick="return askFriendInfo();" style="font-size:18px; color:#208ef2; text-decoration:none;float:right;padding-right:20px;">'+txteditgift+'</a>');	
		});	 
	 
}

function submitFriendInfo(frm, v){
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	//$.facebox('Processing...');
	callAjax(webroot+'buy-deal-ajax.php', data, function(t){$.facebox(t);});
	 
	askFriendInfoGift();
}