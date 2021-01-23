function get_share_url( share_par, main_url, main_title ) { 

	if( main_title=='' ){	main_title = document.title;}			
	if( main_url=='' ){	main_url = window.location.href;}
	
	var share_url = encodeURI(main_url);
	var share_title = encodeURI(main_title);
				

	var social_share_arr = {'facebook_share':'facebook_share','linkedin_share':'linkedin_share','gplus_share':'gplus_share','pinterest_share':'pinterest_share','twitter_share':'twitter_share'};
	
	if( share_par != '' ) {			
		if( social_share_arr[share_par]== 'facebook_share'){
			return 'http://www.facebook.com/share.php?u='+share_url+'&amp;title='+share_title;
		}else if( social_share_arr[share_par]== 'linkedin_share'){
			return 'http://www.linkedin.com/shareArticle?mini=true&url='+share_url+'&title='+share_title+'&source='+share_url;
		}else if( social_share_arr[share_par]== 'gplus_share'){
			return 'https://plus.google.com/share?url='+share_url;
		}else if( social_share_arr[share_par]== 'pinterest_share'){
			return 'http://pinterest.com/pin/create/button/?url='+share_url+'&description='+share_title;
		}else if( social_share_arr[share_par]== 'twitter_share'){
			return 'http://twitter.com/share?text='+share_title+'&url='+share_url;
		}		
	
	}
	return false;
}		

function get_share_count(script_url, share_url){

	if( share_url=='' ){	share_url = window.location.href;}
	
	var share_url = encodeURI(share_url);		

	if( script_url != '' ) {

		jQuery.ajax({
			url:script_url,
			type: 'POST',
			dataType: 'json',
			data:{action:'social_media', 'post_url':share_url},
			success:function(msg){				
					jQuery('#facebook_count').html(msg.fb_share_count);
					jQuery('#linkedin_count').html(msg.linkedin_share_count);
					jQuery('#googleplus_count').html(msg.googleplus_share_count);
					jQuery('#pinterest_count').html(msg.pinterest_share_count);
			},
			failure: function(){
				console.log('Ajax Failed.');
			}
		});			
	}
}


function social_media_share( social_media_name, _url, $w, $h ){
	
	$url = get_share_url( social_media_name, _url );
	if(!$w){ $w = 460;}	if(!$h){ $h = 580;	}
	var child = window.open($url, 'sharer', 'toolbar=0,status=0,width='+$w+',height='+$h);
	/* var timer = setInterval(checkChild, 500);
	function checkChild(){if(child.closed){clearInterval(timer); window.location.reload();}} */
}