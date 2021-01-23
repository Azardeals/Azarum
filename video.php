<?php
require_once './application-top.php';
$vid = $_GET['vid'];
$fvid = $_GET['fvid'];
if (isset($_GET['vid']) && $_GET['vid'] != "") {
    $path = FAQ_GALLERY_URL . 'processed_video/' . $_GET['vid'];
}
if (isset($_GET['fvid']) && $_GET['fvid'] != "") {
    $path = FAQ_GALLERY_URL . 'processed_video/' . $_GET['fvid'];
}
?>
<div class="vplayer">
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"   codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"  	  width="500" height="500">
        <param name="movie" value="<?php echo CONF_WEBROOT_URL; ?>flvplayer.swf?file=<?php echo $path; ?>" />
        <param name="quality" value="high" />
        <param name="wmode" value="transparent" />
        <embed src="<?php echo CONF_WEBROOT_URL; ?>flvplayer.swf?file=<?php echo $path; ?>" quality="high" wmode="transparent" width="500" height="500"  type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></embed>
    </object>
</div> 