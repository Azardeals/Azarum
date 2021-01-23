var txtcatdel;
var txtsuredel;
function deleteCategory(category,page) {
	
	callAjax('common-ajax.php', 'mode=isParentCategory&category='+category, function(t1) {
		var totalCount = parseInt(t1);
		if(totalCount > 0) {
			requestPopup(1,txtChildCatdel,0); 
		}else {
			callAjax('common-ajax.php', 'mode=deleteCategory&category='+category, function(t) {
				
				var total_deals = parseInt(t);
				
				if ( total_deals > 0 ) {
					requestPopup(1,txtcatdel,0); 
				} else {
					requestPopupAjax(category,txtsuredel,1,'DeleteCategory');  
					
				}
			});	
		}
	});	
}

function doRequiredActionDeleteCategory(category) {
	document.location.href = '?delete='+category;
}

function goBack() {
  window.history.back();
}
