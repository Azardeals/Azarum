

 function selectHide() {
	            document.getElementById('div_id1').style.display = 'none';
				document.getElementById('div_id1_c').style.display = 'none';
				
	        }									
 function selectShow() {
	            document.getElementById('div_id1').style.display = 'block';
				document.getElementById('div_id1_c').style.display = 'block';
	            
	        }									
 function editorShow() {
	            document.getElementById('editor_hide').style.display = 'block';
				document.getElementById('editor_hide1').style.display = 'block';
	        }									
 function editorHide() {
	            document.getElementById('editor_hide').style.display = 'none';
				document.getElementById('editor_hide1').style.display = 'none';				
	        }

 function dropdownShow() {
	            document.getElementById('dropShow').style.display = 'block';
				document.getElementById('dropHide').style.display = 'block';
	        }									
 function dropdownHide() {
	            document.getElementById('dropShow').style.display = 'none';
				document.getElementById('dropHide').style.display = 'none';				
	        }	

 function ExternalUrlShow() {
	            document.getElementById('urlShow').style.display = 'block';
				document.getElementById('urlHide').style.display = 'block';
	        }									
 function ExternalUrlHide() {
	            document.getElementById('urlShow').style.display = 'none';
				document.getElementById('urlHide').style.display = 'none';				
	        }			

		function test1(){
	var a = document.frm_navigation.nl_type.value;//alert (a);
	if(a ==0 && a!=""){
	selectShow();
	editorHide();
	dropdownShow();
	ExternalUrlHide();
	}else if(a==1 && a!=""){
	selectHide();
	editorShow();
	dropdownHide();
	ExternalUrlHide();
	}else if(a==2 && a!=""){
	selectHide();
	editorHide();
	dropdownShow();
	ExternalUrlShow();
	}else if(a==""){
	selectHide();
	editorHide();	
	dropdownHide();
	ExternalUrlHide();
	}
		}