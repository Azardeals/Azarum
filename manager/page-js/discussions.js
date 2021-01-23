var txtsuredel;

function approveComment(comment_id,status){//alert(comment_id);
	
	var status;
	var comment_id;
 	if(status=='0') 
	{ 	
		$("#comment-status"+comment_id).html('<img src="'+webroot+'manager/images/ajax2.gif" alt="Loading...">');
	callAjax('cms-ajax.php', 'mode=disapproveComment&comment_id='+comment_id, function(t){
		$('#comment'+comment_id).html('Disapproved');
		var dell = "return (confirm(txtsuredel));";
		$('#comment-status'+comment_id).html('<a href="javascript:void(0);" onclick="approveComment('+comment_id+',1);" class="btn green">Approve Comment</a> <a href="javascript:void(0);" onclick="approveComment('+comment_id+',2);" class="btn">Pending</a> <a href="discussions.php?delete='+comment_id+'" onclick="'+dell+'" class="btn delete">Delete Comment</a><a href="discussions.php?edit='+comment_id+'" class="btn gray">Edit</a>'); 
		
	});
	}
 	if(status=='1'){
	$('#comment-status'+comment_id).html('<img src="'+webroot+'manager/images/ajax2.gif" alt="Loading...">');
	callAjax('cms-ajax.php', 'mode=approveComment&comment_id='+comment_id, function(t){
	$('#comment'+comment_id).html('Approved');
	var dell = "return (confirm(txtsuredel));";
	$('#comment-status'+comment_id).html('<a href="javascript:void(0);" onclick="approveComment('+comment_id+',0);" class="btn delete"> Disapprove Comment </a><a href="javascript:void(0);" onclick="approveComment('+comment_id+',2);" class="btn"> Pending </a><a href="discussions.php?delete='+comment_id+'" onclick="'+dell+'" class="btn delete"> Delete Comment </a><a href="discussions.php?edit='+comment_id+'" class="btn gray">Edit</a>');
	});
	
}
	 	if(status=='2'){
	$('#comment-status'+comment_id).html('<img src="'+webroot+'manager/images/ajax2.gif" alt="Loading...">');
	callAjax('cms-ajax.php', 'mode=pending&comment_id='+comment_id, function(t){
	$('#comment'+comment_id).html('Pending');
	var dell = "return (confirm(txtsuredel));";
	$('#comment-status'+comment_id).html('<a href="javascript:void(0);" onclick="approveComment('+comment_id+',1);" class="btn green"> Approve Comment </a><a href="javascript:void(0);" onclick="approveComment('+comment_id+',0);" class="btn delete"> Disapprove Comment</a><a href="discussions.php?delete='+comment_id+'" onclick="'+dell+'" class="btn delete"> Delete Comment </a><a href="discussions.php?edit='+comment_id+'" class="btn gray">Edit</a>');
	});
	
}

}