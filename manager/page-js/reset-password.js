
$(function () {
	$("input[name='password']").on('input', function() {
		$(this).parent().parent().addClass('active');
	});
	$("input[name='confirm_password']").on('input', function() {
		$(this).parent().parent().addClass('active');
	});


});
