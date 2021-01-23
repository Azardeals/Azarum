

 function editorHide() {
	            document.getElementById('div_id1').style.display = 'none'
	        }									
 function editorShow() {
	            document.getElementById('div_id1').style.display = 'block'
	        }									
			
		function test1(){
	var a = document.page_content_info.cmsc_type.value;//alert (a);
	if(a !=0 ){
	editorHide();
	}else{
	editorShow();
	}
		}
