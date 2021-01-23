var txtnomsg;

$(document).ready(function() {
	$('.archive').click(function() {
		var ticket_id = $(this).attr('alt');
		var id_val = $(this).attr('id');
		
		callAjax(webroot+'merchant/messages-ajax.php', 'mode=archive&ticket='+ticket_id, function(t){
			$('#'+id_val).closest('.tbl_messages').remove();
			if ($('.tbl_messages').size() == 0) {
				$('#no_messages').html(txtnomsg);
			}
		});
	});
	
	$('.unarchive').click(function() {
		var ticket_id = $(this).attr('alt');
		var id_val = $(this).attr('id');
		
		callAjax(webroot+'merchant/messages-ajax.php', 'mode=unarchive&ticket='+ticket_id, function(t){
			$('#'+id_val).closest('.tbl_messages').remove();
			if ($('.tbl_messages').size() == 0) {
				$('#no_messages').html(txtnomsg);
			}
		});
	});
	
	$('.mark_as_read').click(function() {
		var ticket_id = $(this).attr('alt');
		var id_val = $(this).attr('id');
		
		callAjax(webroot+'merchant/messages-ajax.php', 'mode=markasread&ticket='+ticket_id, function(t){
			if (status == 1) {
				$('#'+id_val).closest('.tbl_messages').remove();
			}
			
			if ($('.tbl_messages').size() == 0) {
				$('#no_messages').html(txtnomsg);
			}
			
			if (t > 0) {
				$('#msg_counter').html(t);
			} else {
				$('#msg_counter').remove();
			}
			
			$('.msg_flag').remove();
		});
	});
});