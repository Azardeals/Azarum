$(window).load(function(){
$('#companyVoucherDiv').hide();
});
$('.box.searchform_filter .title').live('click',function(){
			$(this).toggleClass("active");
		  $('.togglewrap').slideToggle();
	  });
var txtoops;
function totalCoupon(mode)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		
		$('#full_summary').html(t);
		if(mode == 'puchased'){
		$('#companyVoucherDiv').hide();
		}
		
	});
	$('li').children().removeClass('selected');
	//remove all selected
	//$(this).addClass('selected');   - NOT working here
}

function searchCoupon(id)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode=searchCoupon&id='+id, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		
		$('#full_summary').html(t);
		
		
	});
	$('li').children().removeClass('selected');
	//remove all selected
	//$(this).addClass('selected');   - NOT working here
}
function totalSaved(mode)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		$('#full_summary').html(t);
	});
	$('li').children().removeClass('selected'); //remove all selected
	//$(this).addClass('selected');   - NOT working here
}

function totalSavedByMerchant(mode)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		$('#full_summary').html(t);
	});
	$('li').children().removeClass('selected'); //remove all selected
	//$(this).addClass('selected');   - NOT working here
}

function totalSavedByMerchantPagination(mode,page){

$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode+'&page='+page, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		$('#full_summary').html(t);
	});
	$('li').children().removeClass('selected');
}

//Default Dashboard to show
$(document).ready(function() 
{
	//ESUTStatus('regStatus');
	//$(this).addClass('selected');
});


function dealPurchased(mode)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		$('#full_summary').html(t);
	});
	$('li').children().removeClass('selected'); //remove all selected
	//$(this).addClass('selected');   - NOT working here



}

function alldealPurchased(mode)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		$('#full_summary').html(t);
	});
	$('li').children().removeClass('selected'); //remove all selected
	//$(this).addClass('selected');   - NOT working here



}

function dealExpire(mode)
{
	$('#full_summary').html('<div align="center"><div  class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		$('#full_summary').html(t);
	});
	$('li').children().removeClass('selected'); //remove all selected
	//$(this).addClass('selected');   - NOT working here



}

function charity(mode)
{
	$('#full_summary').html('<div align="center"><div class="loader"><img align="center" src="images/ajax-loader10.gif" alt="Loading..."><br>Loading data...</div></div>');
	//regStatus		-- For Student Registration
	callAjax('index-ajax.php', 'mode='+mode, function(t){
		$('#full_summary').show();
		$('#full_summary').addClass('index-ajax');
		$('#full_summary').html(t);
	});
	$('li').children().removeClass('selected'); //remove all selected
	//$(this).addClass('selected');   - NOT working here
}

function approveDeal(id,CONF_ADMIN_COMMISSION_TYPE){
	var flag= false;  
	if(CONF_ADMIN_COMMISSION_TYPE=='1') {
		mode = 'checkdealcommission';
	}else if(CONF_ADMIN_COMMISSION_TYPE=='3') {
		mode = 'checkCompanyCommission';
	}else if(CONF_ADMIN_COMMISSION_TYPE=='2') {
		mode = 'checkCitywiseCommission';
	}else {
		mode = 'checkCompanyCommission';
	}
	callAjax('deals-ajax.php', 'mode='+mode+'&id='+id, function(t){
		var ans=parseJsonData(t);
		if(ans===false){
			$.facebox(txtoops + t);
			return;
		}
        
		if(ans.commission>0){ 
			flag=true;
		}else{
			requestPopupAjax(ans.id,txtCompCommission,1,mode);     
		}
		if(flag==true){
           doRequiredActionApproveDeal(id);	
        }

	});

}

function doRequiredActioncheckdealcommission(id) {
	window.location.href = '/manager/add-deals.php?commssion=1&edit='+id;
}function doRequiredActioncheckCompanyCommission(id) {
	window.location.href = '/manager/companies.php?commssion=1&edit='+id;
}function doRequiredActioncheckCitywiseCommission(id) {
	window.location.href = '/manager/cities.php?commssion=1&edit='+id;
}

function doRequiredActionApproveDeal(id) {
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	callAjax('deals-ajax.php', 'mode=approveDeal&id='+id, function(t1){
		var ans1=parseJsonData(t1);
		if(ans1===false){
			$.facebox(txtoops + t1);
			return;
		}
		$.facebox(ans1.msg);
		if(ans1.status==0) return;
		setTimeout( "location.reload();",3000);
		
	});		
}

function disapproveDeal(id){
	
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	callAjax('deals-ajax.php', 'mode=disapproveDeal&id='+id, function(t){
		var ans=parseJsonData(t);
		if(ans===false){
			$.facebox(txtoops + t);
			return;
		}
		$.facebox(ans.msg);
		if(ans.status==0) return;
		location.reload();
	});
}

function displayInfo(val){
/* $.mbsmessage(val, false); */
}
function hideInfo(){
/* $('#mbsmessage').hide(); */
}

function payToMerchant(id){
	 
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	 
	callAjax('deals-ajax.php', 'mode=payToMerchant1&deal_id='+id, function(t){
		$.facebox(t);
	});
}

function submitPayToMerchant(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	//$.facebox('Processing...');
	callAjax('deals-ajax.php', data, function(t){
	$.facebox(t);
	});
	  
}


function cancelDeal(id){
	requestPopupAjax(id,cancelMsg,1,'CancelDeal');
}
function doRequiredActionCancelDeal(id) {
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	callAjax('deals-ajax.php', 'mode=cancelDeal&id='+id, function(t){
		var ans=parseJsonData(t);
		if(ans===false){
			$.facebox(txtoops + t);
			return;
		}
		$.facebox(ans.msg);
		if(ans.status==0) return;
		location.reload();
	});
}

function checkRefundAbility(v){
	jQuery.facebox(function() {
		callAjax('index-ajax.php', 'mode=checkforrefund&v='+v+'&req=dashboard', function(t){
			$.facebox(t);
		});
	});
}

function doRefundVoucher(v){
	jQuery.facebox(function() {
		callAjax('index-ajax.php', 'mode=dorefundvoucher&v='+v, function(t){
			$.facebox(t);
			document.Src_frm.submit();
		});
	});
}
