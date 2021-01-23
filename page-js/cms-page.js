
    
     /* for sticky right panel */
      if($(window).width()>1050){
        function sticky_relocate() {
            var window_top = $(window).scrollTop();
            var div_top = $('.fixed__panel').offset().top -110;
            var sticky_left = $('#fixed__panel');
            if((window_top + sticky_left.height()) >= ($('#footer').offset().top - 100)){
                var to_reduce = ((window_top + sticky_left.height()) - ($('#footer').offset().top - 100));
                var set_stick_top = -100 - to_reduce;
                sticky_left.css('top', set_stick_top+'px');
            }else{
                sticky_left.css('top', '110px');
                if (window_top > div_top) {
                    $('#fixed__panel').addClass('stick');
                } else {
                    $('#fixed__panel').removeClass('stick');
                }
            }
        }

        $(function () {
            $(window).scroll(sticky_relocate);
            sticky_relocate();
        });
  }      
    
    
 $(document).ready(function(){  
  /* for right filters  */    
    $('.link__filter').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if(el.hasClass('filter__show')) el.removeClass("filter__show");
        else el.addClass('filter__show');
        return false; 
    });
    $('body').click(function(){
        if($('body').hasClass('filter__show')){
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    });

    $('.filter__overlay').click(function(){
        if($('body').hasClass('filter__show')){
            $('.link__filter').removeClass("active");
            $('body').removeClass('filter__show');
        }
    }); 
    
     $('.section__filter').click(function(e){
            e.stopPropagation();
        });
  
}) ;

  /* for right categories  */     
 $('.box__head-link').click(function(){

  if($(this).hasClass('active')){
      $(this).removeClass('active');
      $(this).siblings('.box__head-body').slideUp();
      return false;
  }
  $('.box__head-link').removeClass('active');
  $(this).addClass("active");

      $('.box__head-body').slideUp();
      $(this).siblings('.box__head-body').slideDown();
      return;
});
       
    
    
    
