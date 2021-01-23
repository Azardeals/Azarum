$(document).ready(function() {
	$('#files').change(function() {
		//alert($(this).val());
		var files_arr = $('#files').files;
		alert($('#files').files[0]);
		//$($('#files').files).each(function(i) {
			//alert(i);
			//filename = $(this).files[0].val();
			//alert(filename);
		//});
	});
});