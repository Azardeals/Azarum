var txtchange;
var txtaprovecom;
function approveComment(ele,comment_id) {
	requestPopupAjax(comment_id,txtchange+' '+txtaprovecom,1,'ApproveComment');  
}
function doRequiredActionApproveComment(comment_id) {
		callAjax('blogs-ajax.php', 'mode=approve_comment&comment_id='+comment_id, function(t){
		
			var ans = parseJsonData(t);
			$.facebox(ans.msg);
			if (ans.status == 1) {
				$(ele).hide();
			}
		});
}