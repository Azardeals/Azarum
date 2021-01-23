$(document).ready(function (t) {
	slideLocation();
});

function slideLocation(){
    $('.section_droparea').css('display','none');
	$('.selection_area .seleclink').click(function(){
        $(this).toggleClass("active");
        $(this).siblings('.section_droparea').slideToggle();
	});
}

function askFriendInfo(maxqty, mod) {
    var mode = mod ? mod : 'askFriendInfo';
    jQuery.facebox(function () {
        callAjax(webroot + 'buy-deal-ajax.php', 'mode=' + mode + '&maxqty=' + maxqty, function (t) {
            setTimeout(function () {
                $.facebox(t)
            }, 1000);
        });
    })
}

$(window).load(function(){
    $('.reviewsdescription').find('p').viewMore({limit: 300});
})

function showDealReviews()
{
	$(".fulldetails .tabs__flat li, .fulldetails .tabs__flat li a").removeClass('active');
	$("#reviews").parent().addClass('active');
	$("#reviews").addClass('active');
	$(".fulldetails .tabspanel__container .tabspanel__content").hide();
	$(".fulldetails #tabs_content_4").show();
	$(window).scrollTop($('.fulldetails').offset().top);
}

function displayImage(name) {
    var src = 'images/' + name;
    $("#showImg").fadeOut(0).fadeIn(0);
    $('#showImg').html('<img src=' + src + '  class="car-box" alt=""/>');
}


function loadMoreSubdeals(id) {
    callAjax(webroot + 'common-ajax.php', 'mode=loadMoreSubdeals&deal_id=' + id, function (t) {
        var ans = parseJsonData(t);
        $.facebox(t);
    });
}


function getproductAttributeValue(deal_id, deal_option_id, key, obj) {
    var deal_option_value_id = $(obj).val();
    callAjax(webroot + 'common-ajax.php', 'mode=getproductAttributeValue&deal_id=' + deal_id + '&deal_option_id=' + deal_option_id + '&deal_option_value_id=' + deal_option_value_id, function (t) {
        var ans = parseJsonData(t);
        key = parseInt(key) + parseInt(1);
        $('.level_' + key).html(ans.msg);
    });

}

function addSubdealClass(obj ,deal_id){
	$(obj).parent().parent().find('li').each(function(){
		$(this).removeClass('selected');
	});
	$(obj).parent().addClass('selected');
	fetchsubdealLocationList(deal_id);	
}

function fetchsubdealLocationList(deal_id)
{
    var subdeal_id= $('.selected').find('input').val();
    callAjax(webroot + 'common-ajax.php', 'mode=fetchsubdealLocationList&deal_id=' + deal_id + '&subdeal_id=' + subdeal_id, function (t) {
        var ans = parseJsonData(t);
        //	alert(ans.msg);
        $('.selection_area').html(ans.msg);
        com_location= parseInt($('#company_location_id').val());
        $('#start_date').val('');
        $('#end_date').val('');
        fetchCalenderlist(deal_id,com_location,'');
        $('#subDealPrice').html(0);
        slideLocation();
    });
}

$(document).ready(function () {
	setTimeout(function(){
        $('img[deal-src*="/deal-image-crop.php"]').each(function () {
            $(this).attr('src', $(this).attr('deal-src'));
        });
    }, 1000);
    setTimeout(function(){
        $('img[data-src*="/deal-image-crop.php"]').each(function () {
            $(this).attr('src', $(this).attr('data-src'));
        });
    }, 2000);
	
	$('input[name="btn_submit_review"]').click(function() { 
		if($('.star-rating').hasClass('star-rating-on')) {
			$(".erlist_reviews_ratings").hide();
			$(".ratingtable tr:first-child td:first-child .star-rating-control").css('border','none');
			return true;
		}else {
			$(".ratingtable tr:first-child td:first-child .star-rating-control").css('border','1px solid red');
			if($('ul').hasClass('erlist_reviews_ratings')) {
				
			}else {
				$(".ratingtable tr:first-child td:first-child").append('<ul class="errorlist erlist_reviews_ratings"><li><a href="javascript:void(0);">Ratings is mandatory.</a></li></ul>');
			}
			return false;
		}
			
	});
	
	$(document).on('click','.star-rating',function(){
		if($('ul').hasClass('erlist_reviews_ratings')) {
			$(".erlist_reviews_ratings").hide();
			$(".ratingtable tr:first-child td:first-child .star-rating-control").css('border','none');
		}
	});
	
	
});
	
	
function fetchCalenderlist(deal_id, location,obj)
{
    $('#company_location_id').val(location);
    if(obj!= ''){
		var companyLocation= $(obj).text();
		$('.seleclink').html(companyLocation);
		$('.section_droparea').css('display','none');
    }
    $('#start_date').val('');
    $('#end_date').val('');
	if(deal_sub_type >0){
		var subdeal_id= $('.selected').find('input').val();
		callAjax(webroot + 'common-ajax.php', 'mode=fetchCalenderlist&deal_id=' + deal_id + '&subdeal_id=' + subdeal_id + '&location=' + location, function (t) {
			var ans = parseJsonData(t);
			$('.calender_container').html(ans.msg);
			
			$('#subDealPrice').html(0);
			return true;
		});
	}
}

function fetchMonthValue(month,year)
{
    var location= $('#company_location_id').val();
    var subdeal_id= $('.selected').find('input').val();
    deal_id=deal_id;
    callAjax(webroot + 'common-ajax.php', 'mode=fetchCalenderlist&deal_id=' + deal_id + '&subdeal_id=' + subdeal_id + '&location=' + location+ '&month=' + month+ '&year=' + year, function (t) {
        var ans = parseJsonData(t);
        $('.calender_container').html(ans.msg);
        setCalendar();
        return true;
    });        
    return false;
}


var dates = [];

function calenderEvent(obj) {

	if (dates.length == 2) {
	
		dates.splice(0, dates.length);
		$("li").each(function () {
		if ($(this).hasClass("startDate"))
		{
			$(this).removeClass("startDate");
		   
		}
		if ($(this).hasClass("endDate"))
		{
			$(this).removeClass("endDate");
		   
		}
		$('#end_date').val('');
		$('#start_date').val('');
	});
	}
	if (dates.length == 0) {
	startDate = $(obj).attr('id');
	$('#start_date').val(startDate);
	$(obj).parent().addClass('startDate');
	dates.push(startDate);
	$('#subDealPrice').text(0);
	}
	else if (dates.length == 1) {
	endDate = $(obj).attr('id');
	//Check if start date is greater than end date Then swap
	startDate=$('li.startDate').find('a').attr('id');
	if(endDate<startDate){
		$('li.startDate').addClass('endDate').removeClass('startDate');
		$(obj).parent().addClass('startDate');
		//$('#start_date').val($(obj).attr('id'));
	}
	else{
	$(obj).parent().addClass('endDate');
	$('#end_date').val(endDate);
	}
	var  parentIndex= $('li.startDate').index();
	var  endDateIndex= $('li.endDate').index();
		
	$(".dates li:gt("+parentIndex+")").each(function (i) {
	parentIndex++;
		if(parentIndex< endDateIndex){
			if($(this).hasClass("disabled") || $(this).hasClass("Unavailable")){
			if($(this).prev().hasClass("startDate")){

				$('.dates li').removeClass("endDate");
				// $(this).prev().addClass("endDate");
				endDate=$(this).prev().find('a').attr('id');
				alert('Unavailable date lies between check-in and check out date. Plz select another dates');
				endDate="";
			}
			return false;
		}
		if($(this).next().hasClass("disabled") || $(this).next().hasClass("Unavailable")){
		$(".dates li").each(function(){
			if ($(this).hasClass("endDate"))
				{ 
				   $(this).removeClass("endDate");
				}
			}); 

			$(this).removeClass("between");
			$(this).addClass('endDate');
			endDate=$(this).find('a').attr('id');
			//calculateSubdealPrice();
		}else{
				$(this).addClass("between");
		}
		}
		
	});
	dates.push(endDate);
	$('#end_date').val(endDate)
	//alert($('#end_date').val());
	//alert($('#start_date').val());
	setTimeout(highlightSelectedDates, 0);
	calculateSubdealPrice();
	}

		  
}
function highlightSelectedDates(){
		$("li").each(function () {
		$(this).removeClass("between");
	});
		$(".dates li").each(function(){
		if ($(this).hasClass("endDate"))
			{
				if($("li.startDate a").attr('id') > $("li.endDate a").attr('id'))
				{
				$("li.startDate").prevUntil("li.endDate").addClass("between");
				}else
				$("li.startDate").nextUntil("li.endDate").addClass("between");
			}
	});
	
}
function calculateSubdealPrice(){
	startDate = $("#start_date").val();
	endDate = $("#end_date").val();
	if(startDate =="undefined" || startDate ==""){
	return false;
	}
	if(startDate == endDate)
	{
	alert('Check-in date and check-out date should not be same. Please select another dates');	
	return false;
	}	

	var location= $('#company_location_id').val();
	var subdeal_id= $('.selected').find('input').val();
	deal_id=deal_id;
	 callAjax(webroot + 'common-ajax.php', 'mode=calculateSubdealPrice&deal_id=' + deal_id + '&subdeal_id=' + subdeal_id + '&location=' + location+ '&startDate=' + startDate+ '&endDate=' + endDate, function (t) {
			var ans = parseJsonData(t);
			$('#subDealPrice').html(ans.msg.subdeal_price);
			$('ul.list-deatails').html(ans.msg.subdeal_list);
			return true;
		}); 
	return false;

}


$(".dates li").live("mouseover", function () {
if($(this).hasClass("available")){
var $currentObj=$(this);

$("li").each(function () {
	if ($(this).hasClass("startDate"))
	{
		if($("li.startDate a").attr('id') > $currentObj.find('a').attr('id')){
			$("li.startDate").prevUntil($currentObj).addClass("between");
		}else
		   $("li.startDate").nextUntil($currentObj).addClass("between");
	}

});
}
}); 

$(".dates").live("mouseout",function() {
	highlightSelectedDates();
});

	
function setCalendar(){
   
	dates= dates.sort();
   if (dates.length >= 0) {
    //Check Date Exist in this calander
    var startDate=start= new Date(dates[0]);
    
    var enddate;
     
    if(dates[1]){
     enddate= new Date(dates[1]);
      
    }
  
    
    while(start <= enddate){
                 
     var higlightDate=date('Y-m-d',start);
    
      if($("#"+higlightDate))
      {
          
        var classToAdd="";
        
        if(start==startDate){
        
          classToAdd="startDate";
          
        }else if(start<enddate){
          
           classToAdd="between";
          
        }else{
           classToAdd="endDate";
        }
        
        $($("#"+higlightDate)).parent().addClass(classToAdd)
        
      }
        
       var newDate = start.setDate(start.getDate() + 1);
       start = new Date(newDate);          

     
    }
   
   }
  
}


function displaySubdeal(id){
    deal_id=deal_id;
    var src= '/images/loader.gif';

    $('.popup .fixed_container').html('<img src=' + src + '  class="car-box" alt=""/>');
	callAjax(webroot + 'common-ajax.php', 'mode=fetchSubdealPopUp&deal_id=' + deal_id , function (t) {
		var ans = parseJsonData(t);
        if (ans.status == 0) {
            alert('Oops! There was some internal error[3]');
            return;
        }
		$('#wrapper').after(t);
		$('li.selected a:first').trigger('click');
			$('body').addClass('hide__scroll');
		});
      
	return false;
}
	
 function date(format, timestamp) {

        var that = this;

        var txt_words = [
            'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        var formatChr = /\\?(.?)/gi;
        var formatChrCb = function (t, s) {
            return f[t] ? f[t]() : s;
        };
        var _pad = function (n, c) {
            n = String(n);
            while (n.length < c) {
                n = '0' + n;
            }
            return n;
        };
        f = {
            // Day
            d: function () { // Day of month w/leading 0; 01..31
                return _pad(f.j(), 2);
            },
            D: function () { // Shorthand day name; Mon...Sun
                return f.l()
                        .slice(0, 3);
            },
            j: function () { // Day of month; 1..31
                return jsdate.getDate();
            },
            l: function () { // Full day name; Monday...Sunday
                return txt_words[f.w()] + 'day';
            },
            N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
                return f.w() || 7;
            },
            S: function () { // Ordinal suffix for day of month; st, nd, rd, th
                var j = f.j();
                var i = j % 10;
                if (i <= 3 && parseInt((j % 100) / 10, 10) == 1) {
                    i = 0;
                }
                return ['st', 'nd', 'rd'][i - 1] || 'th';
            },
            w: function () { // Day of week; 0[Sun]..6[Sat]
                return jsdate.getDay();
            },
            z: function () { // Day of year; 0..365
                var a = new Date(f.Y(), f.n() - 1, f.j());
                var b = new Date(f.Y(), 0, 1);
                return Math.round((a - b) / 864e5);
            },
            // Week
            W: function () { // ISO-8601 week number
                var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
                var b = new Date(a.getFullYear(), 0, 4);
                return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
            },
            // Month
            F: function () { // Full month name; January...December
                return txt_words[6 + f.n()];
            },
            m: function () { // Month w/leading 0; 01...12
                return _pad(f.n(), 2);
            },
            M: function () { // Shorthand month name; Jan...Dec
                return f.F()
                        .slice(0, 3);
            },
            n: function () { // Month; 1...12
                return jsdate.getMonth() + 1;
            },
            t: function () { // Days in month; 28...31
                return (new Date(f.Y(), f.n(), 0))
                        .getDate();
            },
            // Year
            L: function () { // Is leap year?; 0 or 1
                var j = f.Y();
                return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
            },
            o: function () { // ISO-8601 year
                var n = f.n();
                var W = f.W();
                var Y = f.Y();
                return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
            },
            Y: function () { // Full year; e.g. 1980...2010
                return jsdate.getFullYear();
            },
            y: function () { // Last two digits of year; 00...99
                return f.Y()
                        .toString()
                        .slice(-2);
            },
            // Time
            a: function () { // am or pm
                return jsdate.getHours() > 11 ? 'pm' : 'am';
            },
            A: function () { // AM or PM
                return f.a()
                        .toUpperCase();
            },
            B: function () { // Swatch Internet time; 000..999
                var H = jsdate.getUTCHours() * 36e2;
                // Hours
                var i = jsdate.getUTCMinutes() * 60;
                // Minutes
                var s = jsdate.getUTCSeconds(); // Seconds
                return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
            },
            g: function () { // 12-Hours; 1..12
                return f.G() % 12 || 12;
            },
            G: function () { // 24-Hours; 0..23
                return jsdate.getHours();
            },
            h: function () { // 12-Hours w/leading 0; 01..12
                return _pad(f.g(), 2);
            },
            H: function () { // 24-Hours w/leading 0; 00..23
                return _pad(f.G(), 2);
            },
            i: function () { // Minutes w/leading 0; 00..59
                return _pad(jsdate.getMinutes(), 2);
            },
            s: function () { // Seconds w/leading 0; 00..59
                return _pad(jsdate.getSeconds(), 2);
            },
            u: function () { // Microseconds; 000000-999000
                return _pad(jsdate.getMilliseconds() * 1000, 6);
            },
            // Timezone
            e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
                // The following works, but requires inclusion of the very large
                // timezone_abbreviations_list() function.
                /*              return that.date_default_timezone_get();
                 */
                throw 'Not supported (see source code of date() for timezone on how to add support)';
            },
            I: function () { // DST observed?; 0 or 1
                // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
                // If they are not equal, then DST is observed.
                var a = new Date(f.Y(), 0);
                // Jan 1
                var c = Date.UTC(f.Y(), 0);
                // Jan 1 UTC
                var b = new Date(f.Y(), 6);
                // Jul 1
                var d = Date.UTC(f.Y(), 6); // Jul 1 UTC
                return ((a - c) !== (b - d)) ? 1 : 0;
            },
            O: function () { // Difference to GMT in hour format; e.g. +0200
                var tzo = jsdate.getTimezoneOffset();
                var a = Math.abs(tzo);
                return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
            },
            P: function () { // Difference to GMT w/colon; e.g. +02:00
                var O = f.O();
                return (O.substr(0, 3) + ':' + O.substr(3, 2));
            },
            T: function () {

                return 'UTC';
            },
            Z: function () { // Timezone offset in seconds (-43200...50400)
                return -jsdate.getTimezoneOffset() * 60;
            },
            // Full Date/Time
            c: function () { // ISO-8601 date.
                return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
            },
            r: function () { // RFC 2822
                return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
            },
            U: function () { // Seconds since UNIX epoch
                return jsdate / 1000 | 0;
            }
        };
        this.date = function (format, timestamp) {
            that = this;
            jsdate = (timestamp === undefined ? new Date() : // Not provided
                    (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
                    new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
                    );
            return format.replace(formatChr, formatChrCb);
        };
        return this.date(format, timestamp);
    }	
	

	