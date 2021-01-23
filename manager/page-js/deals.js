var txtload;
var txtreload;
var txtoops;
var txtsettledeal;

function saleSummary(id){ //<![CDATA[
var company;
	var merchant;

	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	 

	callAjax('deals-ajax.php', 'mode=saleSummary&id='+id, function(t){
 
	var ans=parseJsonData(t);
	$.facebox(ans.msg);
	var company = ans.company;
	var merchant = ans.merchant;
	var charity = ans.charity;
  setTimeout("plot("+company+","+merchant+","+charity+")",2000);
	});
 
 //]]>
}

function plot(company,merchant,charity){
 
   plot2 = $.jqplot('pie2', [[['Comapny Profit',company],['Merchant Profit',merchant],['Charity',charity]]], {
   gridPadding: {top:0, bottom:100, left:0, right:0},
      seriesDefaults:{renderer:$.jqplot.PieRenderer, trendline:{show:true}},
      legend:{show:true}    
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

function unrejectDeal(id){
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	callAjax('deals-ajax.php', 'mode=unrejectDeal&id='+id, function(t){
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

function approveDeal_bckup(id){
	
	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
	callAjax('deals-ajax.php', 'mode=approveDeal&id='+id, function(t){
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

function disapproveDeal(id){
	
	jQuery.facebox(function() {
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
    });
}

function markDealPaid(id){
 requestPopupAjax(id,txtsettledeal,1,'MarkDealPaid');     
}
function doRequiredActionMarkDealPaid(id) {
		jQuery.facebox(function() {
		callAjax('deals-ajax.php', 'mode=markDealPaid&id='+id, function(t){
			var ans=parseJsonData(t);
			if(ans===false){
				$.facebox(txtoops + t);
				return;
			}
			$.facebox(ans.msg);
			if(ans.status==0) return;
			location.reload();
		});
	});
}

function deleteDeal(id)
{
	requestPopupAjax(id,deletemsg,1,'DeleteDeal');   
}	
function doRequiredActionDeleteDeal(id) {
	jQuery.facebox(function() {
	callAjax('deals-ajax.php', 'mode=deleteDeal&id='+id, function(t){
			var ans=parseJsonData(t);
			if(ans===false){
				$.facebox(txtoops + t);
				return;
			}
			$.facebox(ans.msg);
			if(ans.status==0) return;
			location.reload();
		});
	});
}


function payToMerchant(id, amount){
	 
	jQuery.facebox(function() {
	 
	callAjax('deals-ajax.php', 'mode=payToMerchant1&deal_id='+id+'&payable_amount='+amount, function(t){
		$.facebox(t);
	});
    });
}

function submitPayToMerchant(frm,v){ 
	var v;
	v.validate();
	if(!v.isValid()) return;
	var data=getFrmData(frm);
	//$.facebox('Processing...');
	jQuery.facebox(function() {
		callAjax('deals-ajax.php', data, function(t) {
			$.facebox(t);
		});
	});
	  
}
var deal_id=0;



function addAddress(company,deal_id){
	if(isNaN(parseInt(company))){
		$('#spn-dealAddress').html('');
		return;
	}
	$('#spn-dealAddress').html('Loading...');
	callAjax('deals-ajax.php', 'mode=getAddress&company='+company+'&deal_id='+deal_id, function(t){
	
	var ans=parseJsonData(t);
	if(ans===false){
				requestPopup(1,txtoops+' '+txtreload,0)
				return;
			}
			if(ans.status==0){
				requestPopup(1,ans.msg + '\n'+txtreload,0)
				return;
			}
		$('#spn-dealAddress').html(ans.msg);
	});
}

function addAddressEdit(company){
	if(isNaN(parseInt(company))){
		$('#spn-dealAddress').html('');
		return;
	}
	$('#spn-dealAddress').html('Loading...');
	callAjax('deals-ajax.php', 'mode=getAddress&company='+company+'&selected='+selectedCompany, function(t){
		$('#spn-dealAddress').html(t);
	});
}


function mainDeal(id,city_id){
	
	/* $.facebox('<img src="'+webroot+'facebox/loading.gif">');
	callAjax('deals-ajax.php', 'mode=mainDeal&id='+id+'&city='+city_id, function(t){
	
		var ans=parseJsonData(t);
		if(ans===false){
			$.facebox(txtoops + t);
			return;
		}
		$.facebox(ans.msg); 
		if(ans.status==0) return;
		location.reload();
	});*/
        
        jQuery.facebox(function() {
		callAjax('deals-ajax.php', 'mode=mainDeal&id='+id+'&city='+city_id, function(t){
                    var ans=parseJsonData(t);
		if(ans===false){
			$.facebox(txtoops + t);
			return;
		}
               
                if(ans.status==1) {
		//location.reload();
		setTimeout(function(){$.facebox(ans.msg);location.reload();},1000);
            }
            if(ans.status==0){ return;
		location.reload();
            }
	});
	})
}

function upcomingMainDeal(id,city_id){
	
//	$.facebox('<img src="'+webroot+'facebox/loading.gif">');
        jQuery.facebox(function() {
	callAjax('deals-ajax.php', 'mode=mainDeal&id='+id+'&city='+city_id, function(t){
	
		var ans=parseJsonData(t);
		if(ans===false){
			$.facebox(txtoops + t);
			return;
		}
		$.facebox(ans.msg);
		if(ans.status==0) return;
		location.reload();
	});
})
}

function updateMaxCoupons(val){
	var totalAddress = $('input[id*=dac_address_capacity]').length;
	var totalValue =0;
	var checkCheckbox = 0; 
	for(var i = 1; i <= totalAddress; i++){
		var checkid = 'dac_address_id'+i;
		var id = 'dac_address_capacity'+i;
		if(document.getElementById(checkid).checked == false){
			checkCheckbox++;
			$("#"+id).val(0);
		}else{
		 
		 
		
		var newValue = $("#"+id).val();//document.getElementById(id).value;
		if(parseInt(newValue)) totalValue = (parseInt(totalValue)+parseInt(newValue)) ;
		}
		
	}
	if(checkCheckbox == totalAddress) $.facebox('Please check at least one address');
	document.getElementById('deal_max_coupons').value = totalValue;
}

 