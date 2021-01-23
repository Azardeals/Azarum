var txtnomsg;

$(document).ready(function() {
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
	
	$('.mark_as_read').click(function() {
		var ticket_id = $(this).attr('alt');
		var id_val = $(this).attr('id');
		
		callAjax(webroot+'manager/messages-ajax.php', 'mode=markasread&ticket='+ticket_id, function(t){
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
	});
});



     /* for right side */    
           $('.medialist > li').change(function() {
               $(this).toggleClass("selected");
               var el = $("body");
               if(el.hasClass('selected')) el.removeClass("selected");
               else el.addClass('selected');
               return false; 
           });

           $('body').click(function(){
               if($('body').hasClass('selected')){
                   $('body').removeClass('selected');
               }
           });
           $('.containerwhite').click(function(e){
               e.stopPropagation();
               //return false;
           });


          $('.backarrow').click(function() {
               $(this).removeClass("selected");
          });


           /* for reply container */         
           $('.openreply').live('click',function() {
               $(this).toggleClass("active");
                   $('.boxcontainer').slideToggle();
           });


           /* for expand all messages on message details page */    
           $('.expandlink').live('click',function() {
               $(this).toggleClass("active");
               var el = $(".medialist > li");
               if(el.hasClass('bodycollapsed')) el.removeClass("bodycollapsed");
               else el.addClass('bodycollapsed');
               return false; 
           });
		     $('.name').live('click',function() {
				 var obj= $(this).parent().parent().parent();
				 if(obj.hasClass('bodycollapsed')){
					obj.removeClass('bodycollapsed'); 
				 }else
				obj.addClass('bodycollapsed'); 
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