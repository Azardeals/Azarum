var end_time;

$(document).ready(function(t){
	updateSecsLeft();
	initialize();
});

/**---- function to load google map --------**/
function initialize() {

	var geocoder;
	var map;

    geocoder = new google.maps.Geocoder();
    var address = $('#address').val();
	
    geocoder.geocode( { 'address': address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			var myOptions = {
				zoom: 16,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}

			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

			map.setCenter(results[0].geometry.location);
			var marker = new google.maps.Marker({
				map: map,
				position: results[0].geometry.location
			});
		}
    });
}
/**----------------**/
function updateSecsLeft(){
	var d=new Date();
	var hours;
	var mins;
	var secs;
	
	var remaining=(end_time - d.valueOf())/1000;
	if(remaining<0){
		$('#timeleft').html('<p>Expired</p>');
		$('#spnmtsleft').html('');
		$('#spnscsleft').html('');
		return;
	}
	$('#spnhrsleft').html(Math.floor(remaining/3600)+' Hours');
	hours = Math.floor(remaining/3600);
	remaining=remaining%3600;
	$('#spnmtsleft').html(Math.floor(remaining/60)+' Minutes');
	mins = Math.floor(remaining/60);
	remaining=remaining%60;
	$('#spnscsleft').html(Math.floor(remaining)+' Seconds');
	secs = Math.floor(remaining);
	setTimeout('updateSecsLeft();', 1000);
	$('#timeleft').html('<p>'+hours+' Hours, '+mins+' minutes, <br>'+secs+' Seconds</p>');
}

 function searchCms(frm){
    var data=getFrmData(frm);
    $('#commentary_users').html('<img src="images/ajax2.gif" alt="Loading Result....">Loading Result....');
	
    callAjax( webroot + 'deal-discussion-ajax.php', data, function(t){
        $('#commentary_users').html(t);
    });
} 
function viewMore(deal_id){
	$('#vmore').remove();
	$("ul#dicuss_more").append('<img src="'+webroot+'images/ajax2.gif" id="loder-image">');
	var $elements = $('ul#dicuss_more').children('li');
	var lengthm = $elements.length;
	//$.facebox('Please wait...');
	$.post( webroot + "deal-discussion-ajax.php",{mode:"discussionPosts",countm:lengthm,deal_id:deal_id},function(res){ 
		$("ul#dicuss_more").html(res);
		$("#hideMore").html('<a onclick="hideMoreInfo('+deal_id+');" href="javascript:void(0);">Hide Info</a>');
		$("ul#dicuss_more").slideDown('slow');
	//	$.facebox.close();
		}); 
	$('#loder-image').remove();	
}


function hideMoreInfo(deal_id){
	$('#vmore').remove();
	$("ul#dicuss_more").append('<img src="'+webroot+'images/ajax2.gif" id="loder-image">');
	var $elements = $('ul#dicuss_more').children('li');
	var lengthm = $elements.length;
	//$.facebox('Please wait...');
	$.post( webroot + "deal-discussion-ajax.php",{mode:"hidePosts",countm:lengthm,deal_id:deal_id},function(res){ 
		$("ul#dicuss_more").html(res);
		$("#hideMore").html('<a onclick="viewMore('+deal_id+');" href="javascript:void(0);">More Info</a>');
		$("ul#dicuss_more").slideDown('slow');
	//	$.facebox.close();
		}); 
	$('#loder-image').remove();	
}

function reviewMore(deal_id){
	 
	$("ul#review_more").append('<img src="'+webroot+'images/ajax2.gif" id="loder-image">');
	var $elements = $('ul#review_more').children('li');
	var lengthm = $elements.length;
	//$.facebox('Please wait...');
	$.post( webroot + "deal-discussion-ajax.php",{mode:"showReview",countm:lengthm,deal_id:deal_id},function(res){ 
		$("ul#review_more").html(res);
		$("#hideReview").html('<a onclick="hideReviewInfo('+deal_id+');" href="javascript:void(0);">Hide Info</a>');
		$("ul#review_more").slideDown('slow');
	//	$.facebox.close();
		}); 
	$('#loder-image').remove();	
}

function hideReviewInfo(deal_id){
	 
	$("ul#review_more").append('<img src="'+webroot+'images/ajax2.gif" id="loder-image">');
	var $elements = $('ul#review_more').children('li');
	var lengthm = $elements.length;
	//$.facebox('Please wait...');
	$.post( webroot + "deal-discussion-ajax.php",{mode:"hideReview",countm:lengthm,deal_id:deal_id},function(res){ 
		$("ul#review_more").html(res);
		$("#hideReview").html('<a onclick="reviewMore('+deal_id+');" href="javascript:void(0);">More Info</a>');
		$("ul#review_more").slideDown('slow');
	//	$.facebox.close();
		}); 
	$('#loder-image').remove();	
}

if(document.getElementById("vmore")!= null){
$(document).ready(function() {
    var $elements = $('ul#commentary_users').children('li');
//alert($elements.length);
var lengthm = $elements.length;
if(lengthm<4){ 
document.getElementById("vmore").style.display='none';
}else{
document.getElementById("vmore").style.display='block';
}
});
}

function viewMoreAddress(deal_id){
	$('#vmoreAddress').remove();
	$('#vmoreAddress1').show();
	
	$("ul#Address").append('<img src="'+webroot+'images/ajax2.gif" id="loder-image">');
	var $elements = $('ul#Address').children('li');
	var lengthm = $elements.length;
	  var lengthm = lengthm/2; 
	 
	  
	//$.facebox('Please wait...');
	$.post( webroot + "deal-discussion-ajax.php",{mode:"moreAddress",countma:lengthm,deal_id:deal_id},function(res){ 
	 
		$("#Address").html(res);
		$("#Address").slideDown('slow');
	//	$.facebox.close();
		}); 
	$('#loder-image').remove();	
}

function hideAddress(deal_id){
	$('#vmoreAddress1').hide();
	$('#vmoreAddress').show();
	
	$("ul#Address").append('<img src="'+webroot+'images/ajax2.gif" id="loder-image">');
	var $elements = $('ul#Address').children('li');
	var lengthm = $elements.length;
	  var lengthm = 2; 
	 
	  
	//$.facebox('Please wait...');
	$.post( webroot + "deal-discussion-ajax.php",{mode:"hideAddress",countma:lengthm,deal_id:deal_id},function(res){ 
 
		$("#Address").html(res);
		$("#Address").slideDown('slow');
	//	$.facebox.close();
		}); 
	$('#loder-image').remove();	
}

/*   ------------- Gallery script -----------------*/

/* $(document).ready(function() {		
	
	//Execute the slideShow
	slideShow();

}); */

function slideShow() {

	//Set the opacity of all images to 0
	$('#gallery a').css({opacity: 0.0});
	
	//Get the first image and display it (set it to full opacity)
	$('#gallery a:first').css({opacity: 1.0});
	
	//Set the caption background to semi-transparent
	$('#gallery .caption').css({opacity: 0.7});

	//Resize the width of the caption according to the image width
	$('#gallery .caption').css({width: $('#gallery a').find('img').css('width')});
	
	//Get the caption of the first image from REL attribute and display it
	$('#gallery .content').html($('#gallery a:first').find('img').attr('rel'))
	.animate({opacity: 0.7}, 400);
	
	//Call the gallery function to run the slideshow, 6000 = change to next image after 6 seconds
	setInterval('gallery()',6000);
	
}

function gallery() {
	
	//if no IMGs have the show class, grab the first image
	var current = ($('#gallery a.show')?  $('#gallery a.show') : $('#gallery a:first'));

	//Get next image, if it reached the end of the slideshow, rotate it back to the first image
	var next = ((current.next().length) ? ((current.next().hasClass('caption'))? $('#gallery a:first') :current.next()) : $('#gallery a:first'));	
	
	//Get next image caption
	var caption = next.find('img').attr('rel');	
	
	//Set the fade in effect for the next image, show class has higher z-index
	next.css({opacity: 0.0})
	.addClass('show')
	.animate({opacity: 1.0}, 1000);

	//Hide the current image
	current.animate({opacity: 0.0}, 6000)
	.removeClass('show');
	
	//Set the opacity to 0 and height to 1px
	$('#gallery .caption').animate({opacity: 0.0}, { queue:false, duration:0 }).animate({height: '1px'}, { queue:true, duration:300 });	
	
	//Animate the caption, opacity to 0.7 and heigth to 100px, a slide up effect
	$('#gallery .caption').animate({opacity: 0.7},100 ).animate({height: '100px'},500 );
	
	//Display the content
	$('#gallery .content').html(caption);
	
	
}


 
    $(document).ready(function(){
        /* $("#loopedSlider").jCarouselLite({
		btnNext: '.next',
		btnPrev: '.previous',
		start: 0,
		visible:3,
		speed: 1000,
		auto: false
	}); */
       
    });
	
	$(document).ready(function(){
       /*  $("#loopedSlider2").jCarouselLite({
		btnNext: '.next2',
		btnPrev: '.previous2',
		start: 0,
		visible:1,
		speed: 1000,
		auto: false
	}); */
       
    });
 
                function displayImage(name){
					var src ='images/'+name;
					
					$("#showImg").fadeOut(0).fadeIn(0);
					
					$('#showImg').html('<img src='+name+'  class="car-box" alt=""/>');
				}
 

function shoeInfo(){
$("#company_info").show(0);
$("#company_map").hide();
$("#infoActive").addClass('stay');
$("#mapActive").removeClass('stay');

}

function shoeMap(){
$("#company_info").hide();
$("#company_map").show(0);
$("#infoActive").removeClass('stay');
$("#mapActive").addClass('stay');
} 


function screenClose(){
$('#popBg').css('display', 'none');
}