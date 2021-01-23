function verifyUserEmail(user_name, user_email, member_id, user_code, user_city) {
	callAjax(webroot + 'common-ajax.php', 'mode=verifyUserEmail&user_name=' + user_name + '&email=' + encodeURIComponent(user_email) + '&member_id=' + member_id + '&code=' + user_code + '&city=' + user_city, function (t) {
		var ans = parseJsonData(t);
		if (ans === false) {
			alert(txtoops + ' ' + txtreload);
			return;
		}
		if (ans.status == 0) {
			$.facebox('<div class="div_error"><ul><li>' + txtemailfail + '</li></ul></div>');
			return;
		}
		if (ans.status == 1) {
			$.facebox('<div class="div_msg"><ul><li>' + txtemailsent + '</li></ul></div>');
			return;
		}
	});
}



function changeCity(val) {
	val = $("#city option:selected").html();
	$("#cityname").html(val);
}

function selectCityRedirect(){
	var city = parseInt($('#city').val());
	selectCity(city, 1);
	return false;
}

function selectSessionCityRedirect() {
	var city = parseInt($('#city').val());
	selectSessionCity(city, 1);
	return false;
}
	


