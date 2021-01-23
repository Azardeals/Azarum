/**
 * 
 */
 
/*  $(document).ready(function(){
	 $(".radio").dgStyle();
 
	 
	$(".radio-orange").dgStyle();
	$(".checkbox").dgStyle();
}); */
var txtreload;
 

 function updateCatsubs(city,cat_id){
 /* INSERT */
	$('#msg').remove();
	callAjax(webroot + 'my-account-ajax.php', 'mode=updateCatsubs&city='+city+'&cat_id='+cat_id, function(t){
		var ans=parseJsonData(t);
		
		if(ans.status==0){
			alert(ans.msg + '\n'+txtreload);
			return;
		}
		$('body').append('<div id="msg"><div id="message">'+ans.msg+'</div></div>');
		setTimeout(function(){$('#msg').remove();}, 2000);
	});
  
 
  
 } 
  function insertParentChildCat(city,cat_id, cat_code){
 /* INSERT */
	$('#msg').remove();
	callAjax(webroot + 'my-account-ajax.php', 'mode=insertParentChildCat&city='+city+'&cat_id='+cat_id+'&cat_code='+cat_code, function(t){
		var ans=parseJsonData(t);
		
		if(ans.status==0){
			alert(ans.msg + '\n'+txtreload);
			return;
		}
                $('#'+cat_code+'_'+city).html(ans.str);
		$('body').append('<div id="msg"><div id="message">'+ans.msg+'</div></div>');
		setTimeout(function(){$('#msg').remove();}, 2000);
	});
  
 
  
 }
 
 function deleteParentChildCat(city,cat_id, cat_code){
 /* INSERT */
	$('#msg').remove();
	callAjax(webroot + 'my-account-ajax.php', 'mode=deleteParentChildCat&city='+city+'&cat_id='+cat_id+'&cat_code='+cat_code, function(t){
		var ans=parseJsonData(t);
		
		if(ans.status==0){
			alert(ans.msg + '\n'+txtreload);
			return;
		}
                $('#'+cat_code+'_'+city).html(ans.str);
		$('body').append('<div id="msg"><div id="message">'+ans.msg+'</div></div>');
		setTimeout(function(){$('#msg').remove();}, 2000);
	});
  
 
  
 }
 function insertCatsubs(city,cat_id){
 /* DELETE */
//alert('no');
	$('#msg').remove();
	callAjax(webroot + 'my-account-ajax.php', 'mode=deleteCatsubs&city='+city+'&cat_id='+cat_id, function(t){
		
		var ans=parseJsonData(t);
			
		if(ans.status==0){
			alert(ans.msg + '\n'+txtreload);
			return;
		}
		$('body').append('<div id="msg"><div id="message">'+ans.msg+'</div></div>');
		setTimeout(function(){$('#msg').remove();}, 2000);
	});
 
 }

function subscriptionCategory(frm){
    var data=getFrmData(frm);
    $('#subCategory').html('<img src="'+webroot+'images/ajax2.gif" alt="Loading Result....">Loading Result....');
	
    callAjax(webroot + 'my-account-ajax.php',  data, function(t){
	 
        $('#subCategory').html(t);
    });
}
var txtload;
function serachCity(city){
	
	$('#displayStates').html(txtload+'...');
	callAjax(webroot+'common-ajax.php', 'mode=citystateSearch&city='+city, function(t){
 
		var ans=parseJsonData(t);
			 
			 
			if(ans.status==0){
				alert(ans.msg + '\n'+txtreload);
				return;
			}
			$('#displayStates').html(ans.msg);
	});
}