$(document).ready(function(){
	ele=document.getElementById('conf_email_sending_method');
	checkPearMailExt(ele);
	$("input[name='conf_facebook_secret_key']").blur(function(){
		secretkey=$(this).val();
		appId=$("input[name='conf_facebook_api_key']").val();
		callAjax('api_checks.php', 'app_id=' + appId + '&secretkey='+secretkey+'&mode=fb_authentication', function (t) {
			  var ans = parseJsonData(t);
				if(ans.status==0){
					$.facebox(ans.msg);
				}
		});
	})
});

function checkPearMailExt(ele)
{
	var val=ele.value;
	if(val == "" || isNaN(val))
	{
		ele.value=1;
		return true;
	}
	else if(parseInt(val) === 2)
	{
		callAjax('common-ajax.php', 'mode=chkpearmailext&val='+val, function(t){
			if(parseInt(t) !== 1)
			{
				$.facebox(t);
				ele.value=1;
			}
		});
	}
	return true;
}

