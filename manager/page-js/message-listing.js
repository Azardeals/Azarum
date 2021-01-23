var txtnomsg;
function pageMsgHtml(){
	keyword=$('#search').val();
callAjax(webroot+'manager/messages-ajax.php', 'mode=getMessageHtml&status='+status+'&page='+page+'&keyword='+keyword, function(t){
				var ans = parseJsonData(t);
				$('#msg_html').html(ans.msg);
				$('.medialist>li input[type=checkbox]').bind('click',function() {
	
               if($(this).prop("checked")==true){
				   	   $(this).parent().parent().parent().addClass('selected');
               var el = $("body");
               /* if(el.hasClass('selected')) el.removeClass("selected");
               else */ 
			   el.addClass('selected');
      
		}else{
		 $(this).parent().parent().parent().removeClass('selected');	
		}	
		$('.messagecount').text($('[name="message_ids[]"]:checked').length);
		if($('[name="message_ids[]"]:checked').length==0){
			 $("body").removeClass("selected");
			}
		});
			
		});	
}	
$(document).ready(function() {
	
	pageMsgHtml();
	
	$('.archive').click(function() {
		var ticket_id = $(this).attr('alt');
		var id_val = $(this).attr('id');
		
		callAjax(webroot+'manager/messages-ajax.php', 'mode=archive&ticket='+ticket_id, function(t){
			$('#'+id_val).closest('.tbl_messages').remove();
			if ($('.tbl_messages').size() == 0) {
				$('#no_messages').html(txtnomsg);
			}
		});
	});
	
	$('.unarchive').click(function() {
		var ticket_id = $(this).attr('alt');
		var id_val = $(this).attr('id');
		
		callAjax(webroot+'manager/messages-ajax.php', 'mode=unarchive&ticket='+ticket_id, function(t){
			$('#'+id_val).closest('.tbl_messages').remove();
			if ($('.tbl_messages').size() == 0) {
				$('#no_messages').html(txtnomsg);
			}
		});
	});
	
	$('.medialist>li input[type=checkbox]').live('click',function() {
		       if($(this).prop("checked")==true){
               var el = $("body");
               /* if(el.hasClass('selected')) el.removeClass("selected");
               else */ 
			   $(this).parent().parent().parent().addClass('selected');
			   el.addClass('selected');
	    
		}
		$('.messagecount').text($('[name="message_ids[]"]:checked').length);
		});
		
		$('.backarrow').click(function() {
            if($('body').hasClass('selected')){
                   $('body').removeClass('selected');
               }
          });


           /* for reply container */         
           $('.openreply').click(function() {
               $(this).toggleClass("active");
                   $('.boxcontainer').slideToggle();
           });

/* 	
	$('.mark_as_read').click(function() {
		var ticket_id = $(this).attr('alt');
		var id_val = $(this).attr('id');
		
		callAjax(webroot+'manager/messages-ajax.php', 'mode=markasRead&ticket='+ticket_id, function(t){
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
			
			//$('.msg_flag').remove();
		});
	}); */
});

	

     /* for right side */    
         /*   $('.medialist > li').change(function() {
               $(this).toggleClass("selected");
               var el = $("body");
               if(el.hasClass('selected')) el.removeClass("selected");
               else el.addClass('selected');
               return false; 
           });
 */
 
		
		

          /*  $('body').not('li').click(function(){
			   alert('ss');
               if($('body').hasClass('selected')){
                   $('body').removeClass('selected');
               }
           }); */
           $('.containerwhite').click(function(e){
               e.stopPropagation();
               //return false;
           });


          

           /* for expand all messages on message details page */    
           $('.expandlink').click(function() {
               $(this).toggleClass("active");
               var el = $(".medialist > li");
               if(el.hasClass('bodycollapsed')) el.removeClass("bodycollapsed");
               else el.addClass('bodycollapsed');
               return false; 
           });

           $('body').click(function(){
               if($('.containerwhite').hasClass('bodycollapsed')){
                   $('.containerwhite').removeClass('bodycollapsed');
               }
           });
           $('.containerwhite').click(function(e){
               e.stopPropagation();
               //return false;
           });
		   
		   
		   function selectAll(){
		  // $(".medialist>li input[type='checkbox']").prop('checked',true);
		//  alert($('[name="message_ids[]"]:checked').length);
		   $(".medialist>li input[type='checkbox']").each(function(){
			   console.log($(this).is(':checked'));
			   $(this).prop('checked',true);
			   $(this).parent().parent().parent().addClass('selected');
		   })
		    $('.messagecount').text($('[name="message_ids[]"]:checked').length);
		   }
		   
		  function markasRead(){
		 ticketIds=$('.medialist input[type="checkbox"]').serialize();
		  callAjax('messages-ajax.php', ticketIds + '&mode=markasread', function(t){
			var ans = parseJsonData(t);
					if(ans){
						$('.counts').html(ans.msg);
						pageMsgHtml();
						$('body').removeClass('selected');
					}
		}); 
	}
	function markAsArchive(){
		ticketIds=$('.medialist input[type="checkbox"]').serialize();
		  callAjax('messages-ajax.php', ticketIds + '&mode=markAsArchive', function(t){
				var ans = parseJsonData(t);
					if(ans){
						jQuery.facebox(function () {
							$.facebox(ans.msg)
						
						});
						$('body').removeClass('selected');
						pageMsgHtml();
					} 
		}); 
		
	}

		function markAsUnArchive(){
		ticketIds=$('.medialist input[type="checkbox"]').serialize();
		  callAjax('messages-ajax.php', ticketIds + '&mode=markAsUnArchive', function(t){
			var ans = parseJsonData(t);
					if(ans){
						jQuery.facebox(function () {
							$.facebox(ans.msg);
						
						});
						$('body').removeClass('selected');
						pageMsgHtml();
					} 
			}); 
		
		}
	function markAsUnRead(){
		 ticketIds=$('.medialist input[type="checkbox"]').serialize();
		  callAjax('messages-ajax.php','mode=markAsUnRead&'+ticketIds, function(t){
			var ans = parseJsonData(t);
					if(ans){
						$('.counts').html(ans.msg);
						pageMsgHtml();
						$('body').removeClass('selected');
					}

		}); 
	}