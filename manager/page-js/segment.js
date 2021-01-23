

function deleteSegment(type,id){
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	if(type=="static"){
	callAjax('mailchimp-ajax.php', 'mode=deletestaticSegment&id='+id, function(t){
		$.facebox(t);
			setTimeout(function(){
   window.location.reload(1);
}, 5000);
		});

	}else{
		callAjax('mailchimp-ajax.php', 'mode=deletesavedSegment&id='+id, function(t){
			$.facebox(t);
			setTimeout(function(){
   window.location.reload(1);
}, 5000);
		});

	}
	//location.reload();
}

function selecttype(val){
	if(val=="static"){
	var email='<div id="staticBox"><label>Emails must be seprated by commas</label><textarea name="recipients">Enter a email address</textarea></div>';
	$('#InnerBox').html(email);
	}
	if(val=="dynamic"){

	//	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
		 

		callAjax('mailchimp-ajax.php', 'mode=getlist', function(t){

		$('#InnerBox').html(t);
		$('#InnerBox').append('<div id="a"></div>');
		
		});

	}

}



function fetchValues(){



		var value=$('#fields').val();
		if(value.indexOf('interests')==0){
		var type="group";
		}
	else{
	var type="merge";
	}

	var resp=getOptions(value,type);
		
}


function getOptions(value,type){
callAjax('mailchimp-ajax.php', 'mode=options&value='+value+'&type='+type, function(result){
	var resp= JSON.parse(result);
	$('#a').html('<div id="oper"><select name="op" id="op"></select></div><div id="condi"><select name="value" id="value"></select></div>');
	
$('#oper').html(resp['op']);
	$('#condi').html(resp['value']);
	
	});

}