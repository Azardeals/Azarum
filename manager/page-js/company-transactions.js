function addTransaction(company,deal,amount){
	//$.facebox('<img src="'+webroot+'facebox/loading.gif" alt="Loading...">');
    $.facebox(function(){
        callAjax('company-ajax.php', 'mode=addTransaction&company='+company+'&deal='+deal+'&payable_amount='+amount, function(t) {
            $.facebox(t);
        });
    });
}

function submitAddTransaction(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	//$.facebox('Processing...');
	callAjax('company-ajax.php', data, function(t){$.facebox(t);
	
		$(document).bind('close.facebox', function() {
			window.location.reload(true);
		});
	});	  
}