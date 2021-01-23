
jQuery(document).ready(function(){
$('input:radio[name="schedule"]').change(function(){
    if($(this).val() == 'schedule'){
      $('#datepicker').show();
       //bindDateTimePicker('schedule', "dd-mm-yy", {minDate: "+0d"});
       //     $('.ui-datepicker-trigger').css('display', 'block');
    }else
      
    {
      
      $('#datepicker').hide();
    }
});
});

function fetchDeal(id){
//alert(id);
//$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	if(id!=""){
	callAjax('mailchimp-ajax.php', 'mode=fetchDeal&id='+id, function(t){
	//$.facebox(t);
	var obj= JSON.parse(t);
	//JSON.stringify({x: 5, y: 6});
		$('#main_deal_id').html(obj.dropdown1);
		//$('#other_deal_id').html(obj.checkbox1);
		});

	}


}
function fetchDealList(cat_id){
//alert($('#city_id').val());
//$.facebox('<img src="'+webroot+'facebox/loading.gif">');
cityId= $('#city_id').val();

	if(cat_id!=""){
	callAjax('mailchimp-ajax.php', 'mode=fetchDealList&cityId='+cityId+'&cat_id='+cat_id, function(t){
	//$.facebox(t);
	var obj= JSON.parse(t);
	//JSON.stringify({x: 5, y: 6});
		$('#main_deal_id').html(obj.dropdown1);
		//$('#other_deal_id').html(obj.checkbox1);
		});

	}


}
function fetchOtherDeal(deal_id){
cityId= $('#city_id').val();
category_id=$('#category_id').val();
if(deal_id!="" && (cityId!='')){
	callAjax('mailchimp-ajax.php', 'mode=fetchOtherDeal&cityId='+cityId+'&deal_id='+deal_id+'&category_id='+category_id, function(t){
	//$.facebox(t);
	var obj = JSON.parse(t);
	$('#other_deal_id').html(obj.checkbox1);
		});

	}else{
            alert('Please select city and category both');
        }

}

function showPreview(){
var maindeal= $('#maindeal').val();
if(maindeal==""){
alert('Select your main deal');
return false;
}
var flag= false;
var checkboxlen= $('#campaign').find("input:checkbox").length;
if(checkboxlen>0){
var value= $('input:checkbox:checked').length;
if(value >0){
flag= true;
}
}else
{
flag= true;
}

if(flag){
//for ( instance in CKEDITOR.instances )
//CKEDITOR.instances[instance].updateElement();

var data= $('#campaign').serialize();
//console.log(data);

callAjax('mailchimp-ajax.php', 'mode=fetchMaindealInfo&data='+data, function(t){
$.facebox(t);
//	var obj= JSON.parse(t);
});

}else{
alert('Please select one other deal');
}


}

