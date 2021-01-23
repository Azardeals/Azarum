var txtselectfirst = '';
$('#city_selector').live("change", function(){
	window.location = "newsletter-subscribers.php?city="+$(this).find("option:selected").val();
});

function downloadSelected(){
	var haschecked = false;
	$('input[name="listing_id[]"]').each(function(i){
		if(this.checked === true){
			document.form_listing.submit();
			haschecked = true;
			return false;
		}
	});
	if(haschecked !== true){
		$.facebox(txtselectfirst);
	}
	return false;
}