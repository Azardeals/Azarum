function closediv(){
	$('.system_message').css("display","none");
}

$(function () {
	$("#rep_email,#rep_password").on('input', function() {
		$(this).parent().parent().addClass('active');
	});
	$("#rep_email,#rep_password").focus( function() {
		$(this).parent().parent().addClass('active');
	});
	if($("#rep_email").val()!==""){
			$(".user").addClass('active');
	}
	if($("#rep_password").val()!==""){
		$(".key").addClass('active');
	}
});