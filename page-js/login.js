var txtreload;
var txtoops;
var txtemailfail;
var txtemailsent;




function verifyUserEmail(user_name,user_email,member_id,user_code,user_city) {
	
	callAjax(webroot+'common-ajax.php', 'mode=verifyUserEmail&user_name='+user_name+'&email='+user_email+'&member_id='+member_id+'&code='+user_code+'&city='+user_city, function(t){
		
		var ans=parseJsonData(t);
			 
		if(ans===false){
			alert(txtoops+' '+txtreload);
			return;
		}
		$('.system-notice').find('.close').trigger('click');
		if(ans.status==0){
			$.facebox('<div class="div_error"><ul><li>'+txtemailfail+'</li></ul></div>');
			return;
		}
		
		if(ans.status == 1){
			$.facebox('<div class="div_msg"><ul><li>'+txtemailsent+'</li></ul></div>'); 
			return;
		}
	});
}

function showLoginForm(obj){
    $(obj).parents().find('li').removeClass('current');
    $(obj).parent().addClass('current');
     // $("#registerationform").fadeOut("slow");
    $('#loginform').css('display','block');
    $('#registerationform').css('display','none');
	$('input[name="user_email"]').attr('onchange', "");
}   

function showRegisterationForm(obj){
	$('input[name="user_email"]').attr('onchange', "checkUnique($(this), 'tbl_users', 'user_email', 'user_id', $('#user_id'), []);");
    $(obj).parents().find('li').removeClass('current');
    $(obj).parent().addClass('current');
   /*   $("#loginform").fadeOut("slow");
      $("#registerationform").fadeIn("slow"); */
   
   $('#loginform').css('display','none');
    $('#registerationform').css('display','block');
	
}   