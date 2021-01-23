$(document).ready(function(){
    
        /* for left side */    
		$('.menutrigger').click(function() {
            $(this).toggleClass("active");
			var el = $("body");
			if(el.hasClass('toggled-left')) el.removeClass("toggled-left");
			else el.addClass('toggled-left');
            return false; 
        });
        /* $('body').click(function(){
            if($('body').hasClass('toggled-left')){
                $('.menutrigger').removeClass("active");
                $('body').removeClass('toggled-left');
            }
        });
 */
        $('.left_portion').click(function(e){
            e.stopPropagation();
        });
		
		
      
		/* for language menu */  
        $('.language').click(function() {
            $(this).toggleClass("active");
        });
    
      
       
    
        /* for profile links */         
        $('.profileinfo').click(function() {
            $(this).toggleClass("active");
            $('.profilelinkswrap').slideToggle("600");
        });
    
    
    
        /* for globally actions menus */         
        $('.droplink').click(function() {
            $(this).toggleClass("active");
			/* return false;  */
        });
            $('html').click(function(){
            if($('.droplink').hasClass('active')){
                $('.droplink').removeClass('active');
            }
        }); 
		 $('.droplink').click(function(e){
            e.stopPropagation();
        });
    
    
	  /* for search form toggle */		 			 
	  $('.togglelink a, .box.searchform_filter .title').click(function(){
		  $(this).toggleClass("active");
		  $('.togglewrap').slideToggle();
	  });
    
    $('#select_all_ids').click(function(){
		if($(this).is(':checked')){
		$("input[type='checkbox']").prop('checked',true);
		}else{
		$("input[type='checkbox']").prop('checked',false); 
		}
	});
    
    $(".tbl_form tbody:first").find('tr:first').find('td:first').addClass('first-child');
    
});

function updateLeftNavDisplayStatus(ele,display_status) {
	callAjax('left-nav-display-ajax.php', 'status='+display_status, function(t){});
}