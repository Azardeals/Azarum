function dealPurchased(mode)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	
    callAjax('index-ajax.php', 'mode='+mode+'&sales='+true, function(t){
		$('#full_summary').show();
		$('#full_summary').html(t);
	});
}


function send_data(frm)
{
    var frm = $(frm).serialize();
    data = '&'+frm;
    
    var mode = 'alldealPurchased';
    
    $('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	
    callAjax('index-ajax.php', 'mode='+mode+data+'&sales='+true, function(t){
		$('#full_summary').show();
		$('#full_summary').html(t);
	});
}