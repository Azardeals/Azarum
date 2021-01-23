function closediv(){
	$('.system_message').css("display","none");
}

$(function () {
	$("#merchant_email").on('input', function() {
		$(".user").addClass('active');
	});
	
	$("#merchant_password").on('input', function() {
		$(".key").addClass('active');
	});
	if($("#merchant_email").val()!== ""){
		
			$(".user").addClass('active');
	}	
	if($("#merchant_password").val()!==""){
		
			$(".key").addClass('active');
	}
});