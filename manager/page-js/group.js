function addGroupOption(){

var field= '<div class="aa"><input type ="text" name="group_option[]"><a href="javascript:void(0)" onclick="$(this).parent().remove();">remove</a></div>';
$('#groupOption').append(field);

}