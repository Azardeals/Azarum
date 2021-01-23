/* var end_time;
var id ;
var deal;
function deal_id(id){
var deal = id;
}
$(document).ready(function(t){
	updateSecsLeft();

});

function updateSecsLeft(){
	
	var d=new Date();
	var remaining=(end_time - d.valueOf())/1000;
	if(remaining<0){
		$('#spnhrsleft').html('Expired');
		$('#spnmtsleft').html('');
		$('#spnscsleft').html('');
		return;
	}
	$('#spnhrsleft').html(Math.floor(remaining/3600)+' Hours');
	remaining=remaining%3600;

	$('#spnmtsleft').html(Math.floor(remaining/60)+' Minutes');
	remaining=remaining%60;
	$('#spnscsleft').html(Math.floor(remaining)+' Seconds');
	setTimeout('updateSecsLeft();', 1000);
} */

function screenClose(){
$('#popBg').css('display', 'none');
}
