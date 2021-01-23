<?php
ob_start();
session_start();
$is_admin_for_file_manager = 0;
$path_for_images = '';
if (!isset($_SESSION['WYSIWYGFileManagerRequirements']) || !is_file($_SESSION['WYSIWYGFileManagerRequirements'])){
	die('File manager authentication not set.');
}
require_once $_SESSION['WYSIWYGFileManagerRequirements'];
if($is_admin_for_file_manager !== 1){
	die('File manager authentication not set.');
}
if(strpos($_POST["folder"], $path_for_images) !== 0){
	exit('Not Authorized!!'); /* To stop front users from deleting files from un-authorized folders. */
}
$root=$_SERVER["DOCUMENT_ROOT"] . '/public';
$newfolder = $root . $_POST["folder"];

function remfolder($dir) {

	if(!file_exists($dir)) return true;

	$cnt = glob($dir . "/" . "*");

	foreach ($cnt as $f) {
	  if(is_file($f)) {
		unlink($f);
	  }

	  if(is_dir($f)) {
		remfolder($f);
	  }
	}

	return rmdir($dir);

}

if(file_exists ($newfolder)) {
	//delete the folder
	remfolder($newfolder);
} else {
	//do nothing
}

echo $_POST["folder"];

?>