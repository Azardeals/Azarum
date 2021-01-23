								
 function editorShow() {
	             document.getElementById('div_id1').style.display = 'block';
				 document.getElementById('div_id').style.display = 'block';

	        }									
 function editorHide() {
	             document.getElementById('div_id1').style.display = 'none';
				 document.getElementById('div_id').style.display = 'none';

	        }			

		function test1(){
	var a = document.page_content_info.cmsc_type.value; //alert (a);
	if(a ==0 ){
	
	editorShow();
	}else if(a==1 && a!=""){

	editorHide();
	}else if(a==2 && a!=""){

	editorHide();	
	}else if(a==""){
	editorHide();
	}		}