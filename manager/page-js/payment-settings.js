$(document).ready(function(){
	$("input[name='authorize_po_key']").blur(function(){
		transactionkey=$(this).val();
		login_appId=$("input[name='authorize_po_account_id']").val();
		if($("select[name='authorize_active']").val()==1){
			callAjax('api_checks.php', 'login_app_id=' + login_appId + '&transactionkey='+transactionkey+'&mode=verify_merchant_authrized', function (t) {
				  var ans = parseJsonData(t);
					if(ans.status==0){
						$.facebox(ans.msg);
					}
			});
		}
	});
});


