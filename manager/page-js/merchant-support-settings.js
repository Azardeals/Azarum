var txtselectrecipient;

$(document).ready(function() {	
	if ($('#send_alerts').val() == 0) {
		$('.recipients_block').closest('tr').hide();
	}
	
	$('#chk_notify_users').click(function() {
		var isChecked = $(this).attr('checked') ? true : false;
		
		if (isChecked) {
			$('.recipients_block').closest('tr').show();
		} else {
			$('.recipients_block').closest('tr').hide();
			$('#err_recipients').html('');
		}
	});
	
	$('#btn_submit').click(function() {
		var notifyUsers = $('#chk_notify_users').attr('checked') ? true : false;
		
		if (notifyUsers) {
			var n = $('#chk_recipients:checked').length;
			
			if (n==0) {
				$('#err_recipients').html(txtselectrecipient);
				return false;
			}
		}
		
		$('#frmMerchantSupportSettings').submit();
	});
});