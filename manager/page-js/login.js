function closediv(){
	$('.system_message').css("display","none");
}

$(function () {
	
	if($("#username").val()!==""){
		
			$(".user").addClass('active');
	}	
	if($("#password").val()!==""){
		
			$(".key").addClass('active');
	}
	$("#username").on('input', function() {
		$(".user").addClass('active');
	});
	
	$("#password").on('input', function() {
		$(".key").addClass('active');
	});
});