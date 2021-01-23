function reloadSecureImage(id){
	if(!id) id='security_image';
	document.getElementById(id).src=webroot + 'securimage/securimage_show_forgot_password.php?sid='+Math.random();
}

function closediv(){
	$('.system_message').css("display","none");
}

$(function () {
	$("#user_email").on('input', function() {
		$(".mail").addClass('active');
	});
	$("input[name='security_code']").on('input', function() {
		$(".secure").addClass('active');
	});
	
	
});