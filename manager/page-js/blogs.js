var txtchange;
var txtaproveblog;

function approveBlog(ele,blog_id) {
	requestPopupAjax(blog_id,txtchange+' '+txtaproveblog,1,'ApproveBlog');  
}
function doRequiredActionApproveBlog(blog_id) {
		callAjax('blogs-ajax.php', 'mode=approve_blog&blog_id='+blog_id, function(t){
		
			var ans = parseJsonData(t);
			$.facebox(ans.msg);
			if (ans.status == 1) {
				$(ele).hide();
			}
		});	
}