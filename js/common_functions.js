$(document).ready(function() { 	
    /* for forms elements */         
    function floatLabel(inputType){
        $(inputType).each(function(){
           var $this = $(this);
           var text_value = $(this).val();

            // on focus add class "active" to label
            $this.focus(function(){
                $this.closest('.searchbar,.subscribeform').addClass("active");
            });

            // on blur check field and remove class if needed
            $this.blur(function(){
                if($this.val() === '' || $this.val() === 'blank'){
                    $this.closest('.searchbar,.subscribeform').removeClass('active');
                }
            });

            // Check input values on postback and add class "active" if value exists
            if(text_value!=''){
                $this.closest('.searchbar,.subscribeform').addClass("active");
            }

        });

    }
    
    // Add a class of "floatLabel" to the input field
    floatLabel(".searchbar input[type='text'],.subscribeform input[type='text']");

    /* for navigation drop down */    
    $('.navchild').hover(function() {
        var el = $("body");
        if($(window).width()>767){
            $(this).toggleClass("active");
            el.toggleClass("nav_show");
			$(".vtabs_link a").removeClass('selected');
			$(".vtabs_link:first a").addClass('selected');
			$(".vtabs_link a").parents('.verticaltabs:first').find(".verticaltabs__content").hide();
			var activeTab = $(".vtabs_link:first a").attr("rel"); 
			$("#"+activeTab).show();
        }    
        return false; 
    });

    /* for top advertisement */    
    $(".hidelink").click(function(){
       $(".topadvertisement").hide();
    });
    
    /* for placeholders */    
    $(function() {
        $('input, textarea').placeholder();
    });


    /* for mobile navigations */	
    $('.link__mobilenav').click(function(){
        if($(this).hasClass('active')){
            $(this).removeClass('active');
            $(this).siblings('.navigations > li .subnav').slideUp();
            return false;
        }
        $('.link__mobilenav').removeClass('active');
        $(this).addClass("active");
        if($(window).width()<767){
            $('.navigations > li .subnav').slideUp();
            $(this).siblings('.navigations > li .subnav').slideDown();
        }
        return;
    });
    
    
    /* for footer */
    if($(window).width()<767){
        $('.gridspanel__title').click(function(){

        if($(this).hasClass('active')){
            $(this).removeClass('active');
            $(this).siblings('.gridspanel__content').slideUp();
            return false;
        }
        $('.gridspanel__title').removeClass('active');
        $(this).addClass("active");
            $('.gridspanel__content').slideUp();
            $(this).siblings('.gridspanel__content').slideDown();
            return;
        });
    }
    
    /* for fixed header */

    $(window).scroll(function(){
        body_height = $("#body").position();
        scroll_position = $(window).scrollTop();
        if(body_height.top < scroll_position)
            $("body").addClass("fixed");
        else
            $("body").removeClass("fixed");
    });

    
    /* for mobile toggle navigation */    
    $('.navs_toggle').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if(el.hasClass('toggled_left')) el.removeClass("toggled_left");
        else el.addClass('toggled_left');
        return false; 
    });
    
    $('body').click(function(){
        if($('body').hasClass('toggled_left')){
            $('.navs_toggle').removeClass("active");
            $('body').removeClass('toggled_left');
        }
    });

    $('.mobile__overlay').click(function(){
        if($('body').hasClass('toggled_left')){
            $('.navs_toggle').removeClass("active");
            $('body').removeClass('toggled_left');
        }
    });
    
    /* for user profile  */    
    $('.user__account').click(function() {
        $(this).toggleClass("active");
        var el = $("body");
        if(el.hasClass('toggled_right')) el.removeClass("toggled_right");
        else el.addClass('toggled_right');
        return false; 
    });
    
    $('body').click(function(){
        if($('body').hasClass('toggled_right')){
            $('.user__account').removeClass("active");
            $('body').removeClass('toggled_right');
        }
    });

    $('.navigations__overlay').click(function(){
        if($('body').hasClass('toggled_right')){
            $('.user__account').removeClass("active");
            $('body').removeClass('toggled_right');
        }
    });

       
    $('.section_secondary,.section_primary').click(function(e){
        e.stopPropagation();
    });
    
    $(".verticaltabs__content").hide();
    $('.verticaltabs__container').find(".verticaltabs__content:first").show();
    $(".vtabs_link a").hover(function() {
       $(this).parents('.verticaltabs:first').find(".verticaltabs__content").hide();
        var activeTab = $(this).attr("rel"); 
        $("#"+activeTab).show();
        $(this).parents('.verticaltabs:first').find(".vtabs_link a").removeClass("selected");
        $(this).addClass("selected");
        return false;
    });
    
    
    /* for normal tabs */
    $(".tabspanel__content").hide();
    $('.tabspanel__container').find(".tabspanel__content:first").show();

    /* if in tab mode */
    $(".normaltabs li a").click(function() {
        $(this).parents('.tabspanel:first').find(".tabspanel__content").hide();
        var activeTab = $(this).attr("rel"); 
        $("#"+activeTab).fadeIn();		

        $(this).parents('.tabspanel:first').find(".normaltabs li a").removeClass("active");
        $(this).addClass("active");

        $(".togglehead").removeClass("active");
        $(".togglehead[href^='"+activeTab+"']").addClass("active");
        return false;
    });
    
    /* if in drawer mode */
    $(".togglehead").click(function() {

        $(this).parents('.tabspanel__container:first').find(".tabspanel__content").hide();
        var d_activeTab = $(this).attr("rel");
        console.log($(this).parents('.tabspanel__container:first').offset().top);
        $(window).scrollTop($(this).parents('.tabspanel__container:first').offset().top-50);
        if($(this).hasClass("active")){
            $(".togglehead").removeClass("active");
            $(this).parents('.tabspanel:first').find(".normaltabs li a").removeClass("active");
            return false;
        }else{
            $("#"+d_activeTab).fadeIn();
        }

        $(".togglehead").removeClass("active");
        $(this).addClass("active");

        $(this).parents('.tabspanel:first').find(".normaltabs li a").removeClass("active");
        $(".normaltabs li a[rel^='"+d_activeTab+"']").addClass("active");
        return;
    });

    
    /* for welcome message */    
    function showContent(){
        setTimeout(function(){
            $('.welcome_msg').fadeIn(1000).addClass("animated fadeInDown");
            setTimeout(function(){
                $('.welcome_msg').fadeOut(1000).removeClass("fadeInDown").addClass('fadeOutUp');
            },5000);
        },1500);
    }
    
    window.onload = showContent;


   /* for header drop down */  
   if($(window).width()>1050){    
        $('.topnav > li.dropdown').hover(function() {
            $(this).toggleClass("active");
        });
    }
    
    /* for city selector */    
    $('.selector__link').click(function(){
        $(this).toggleClass("is_active");
        $('.selector__wrap').slideToggle();
    });
    
    $('.dropsection').click(function(e){
        e.stopPropagation();
        //return false;
    });
    
    if($(window).width()>767){
        /* for header drop down */  
        $('.dropdown--trigger-cities').click(function() {
            $(this).toggleClass("active");
            var el = $("body");
            if(el.hasClass('toggled_cities')) {
                
                el.removeClass("toggled_cities");
          
            }
            else el.addClass('toggled_cities');
               
        });
        
        $('body').click(function(){
            if($('body').hasClass('toggled_cities')){
                $('.dropdown--trigger-cities').removeClass("active");
                $('body').removeClass('toggled_cities');
            }
        });
        
        $('.dropsection').on("touchstart",function(e){
            e.stopPropagation();
            //return false;
        });
        
        $('body').on("touchstart",function(){
            if($('body').hasClass('toggled_cities')){
                $('.dropdown--trigger-cities').removeClass("active");
                $('body').removeClass('toggled_cities');
            }
        });
        
        $('.dropdown--trigger-nav').click(function() {
            $(this).toggleClass("active");
            var el = $("body");
            if(el.hasClass('toggled_nav')) el.removeClass("toggled_nav");
            else el.addClass('toggled_nav');
        });
        
        $('.dropdown--trigger-nav').on("touchstart",function() {
            $('.dropdown--trigger-nav').trigger('click');
          
        });

        $('body').click(function(){
            if($('body').hasClass('toggled_nav')){
                $('.dropdown--trigger-nav').removeClass("active");
                $('body').removeClass('toggled_nav');
            }
        });

        $('body').on("touchstart",function(){
            if($('body').hasClass('toggled_nav')){
                $('.dropdown--trigger-nav').removeClass("active");
                $('body').removeClass('toggled_nav');
            }
        });
    }
 });