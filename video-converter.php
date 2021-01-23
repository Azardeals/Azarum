<?php

require_once './application-top.php';
$strVideoDirectory = IMAGE_GALLERY_PATH . 'video';
$strArchiveDirectory = IMAGE_GALLERY_PATH . "converted-video";
$dh = opendir($strVideoDirectory);
while (($file = readdir($dh)) !== false) {
    if ($file != '.' && $file != '..' && !is_dir("$strVideoDirectory/$file")) {
        $arrExt = @explode('.', $file);
        $strFNameWithoutExt = $arrExt[0];
        $sourceVideoFile = "$strVideoDirectory/$file";
        echo'<br/>' . $sourceVideoFile;
        $destVideoFile = IMAGE_GALLERY_PATH . "processed_video/$strFNameWithoutExt" . ".flv";
        #$sourceImageFile = "$destVideoFile/$file" ;
        $destImageFile = IMAGE_GALLERY_PATH . "processed_images" . "/$strFNameWithoutExt" . ".jpg";
        # CREATE FLV 	
        exec("ffmpeg -i $sourceVideoFile -deinterlace -ar 44100 -ab 64 -b 400k -f flv -qmin 3 -qmax 6 $destVideoFile");
        # CREATE Image from FLV 	
        exec("ffmpeg -i $sourceVideoFile -f mjpeg -s 100x100 -vframes 1 -ss 8 -an $destImageFile");
        # Now move video files to the Archive folder
        if (copy($sourceVideoFile, $strArchiveDirectory . "/$file")) {
            unlink($sourceVideoFile);
        }
    }
}
closedir($dh);
$strVideoDirectoryFaq = FAQ_GALLERY_PATH . 'video';
$strArchiveDirectoryFaq = FAQ_GALLERY_PATH . "converted-video";
$dhFaq = opendir($strVideoDirectoryFaq);
while (($file = readdir($dhFaq)) !== false) {
    if ($file != '.' && $file != '..' && !is_dir("$strVideoDirectoryFaq/$file")) {
        $arrExt = @explode('.', $file);
        $strFNameWithoutExt = $arrExt[0];
        $sourceVideoFile = "$strVideoDirectoryFaq/$file";
        echo'<br/>' . $sourceVideoFile;
        $destVideoFile = FAQ_GALLERY_PATH . "processed_video/$strFNameWithoutExt" . ".flv";
        $destImageFile = FAQ_GALLERY_PATH . "processed_images" . "/$strFNameWithoutExt" . ".jpg";
        # CREATE FLV 	
        exec("ffmpeg -i $sourceVideoFile -deinterlace -ar 44100 -ab 64 -b 400k -f flv -qmin 3 -qmax 6 $destVideoFile");
        # CREATE Image from FLV 	
        exec("ffmpeg -i $sourceVideoFile -f mjpeg -s 100x100 -vframes 1 -ss 8 -an $destImageFile");
        # Now move video files to the Archive folder
        if (copy($sourceVideoFile, $strArchiveDirectoryFaq . "/$file")) {
            unlink($sourceVideoFile);
        }
    }
}
closedir($dhFaq);
