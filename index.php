<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title></title>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<style type="text/css">
body {
font-family: verdana;
font-size: 12px;
margin: 30px 70px;
padding: 0px;
background-color: #2c2b2b;
color: #C5C5C5;
}

h1 {
font-size: 16px;
margin: 30px 0px 10px 0px;
}

h2 {
font-size: 12px;
margin: 10px 0px 10px 0px;
padding-top: 20px;
}

a.tf { /* thumbnail frame */
height: 410px;
width: 410px;
margin: 5px 5px 5px 0px;
}

a.tf {
float: left;
padding: 0px;
text-decoration: none;
font-size: 7px;
font-family: "Small Fonts";
background-position: center;
background-repeat: no-repeat;
}

a {
color: #B7B297;
}

a.tf {
border: 1px solid #3c3a2e;
color: #5c5c5c;
background-color: #e8e8e8;
}

a.tf span {
background-color: #e8e8e8;
}
</style>
</head>

<body>

<div>
<?php

$dirparts = explode('/', getcwd());
$dir = $dirparts[count($dirparts) - 1];
echo "<h1>$dir</h1>\n";

foreach(scandir('.') as $file) {
    $cachepath = './.gallerycache/';
    @mkdir($cachepath);
    chmod($cachepath, 0755);
    if(preg_match('/.(jpeg|jpg)/i', $file)) {
        $c = $cachepath.$file;

        if(!file_exists($c)) {
            // resize image
            $src = imagecreatefromjpeg($file);
            $exif = exif_read_data($file);
            list($w, $h, $t) = getimagesize($file);
            $ratio = $w / $h;
            if($ratio > 1) {
                $nw = 400;
                $nh = 400/$ratio;
            } else {
                $nw = 400*$ratio;
                $nh = 400;
            }

            $dst = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $rot = array(3 => 180, 6 => 90, 8 => 270);
            if($rot[$exif['Orientation']])
                $dst = rotateImage($dst, $rot[$exif['Orientation']]);
            imagejpeg($dst, $c, 85);
            imagedestroy($dst);
            chmod($c, 0644);
        }
        
        echo "<a class=\"tf\" href=\"$file\" style=\"background-image: url('$c')\"><span>$file</span></a>\n";
    }
}

// included because GD's imagerotate is broken in php 5.2
function rotateImage($img, $rotation) {
    $width = imagesx($img);
    $height = imagesy($img);
    switch($rotation) {
        case 90: $newimg= @imagecreatetruecolor($height , $width );break;
        case 180: $newimg= @imagecreatetruecolor($width , $height );break;
        case 270: $newimg= @imagecreatetruecolor($height , $width );break;
        case 0: return $img;break;
        case 360: return $img;break;
    }
    if($newimg) {
        for($i = 0;$i < $width ; $i++) {
            for($j = 0;$j < $height ; $j++) {
                $reference = imagecolorat($img,$i,$j);
                switch($rotation) {
                    case 90: if(!@imagesetpixel($newimg, ($height - 1) - $j, $i, $reference )){return false;}break;
                    case 180: if(!@imagesetpixel($newimg, $width - $i, ($height - 1) - $j, $reference )){return false;}break;
                    case 270: if(!@imagesetpixel($newimg, $j, $width - $i, $reference )){return false;}break;
                }
            }
        }
        return $newimg; 
    }
    return false;
}
?>
</div>

</body>
</html>
