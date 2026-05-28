<?php
$logoPath = __DIR__ . '/public/images/logo.png';
$logoBwPath = __DIR__ . '/public/images/logo-bw.png';
$tutwuriPath = __DIR__ . '/public/images/tutwuri.png';
$tutwuriBwPath = __DIR__ . '/public/images/tutwuri-bw.png';

function makeGrayscale($src, $dest) {
    if (!file_exists($src)) return false;
    
    $info = getimagesize($src);
    if (!$info) return false;
    
    $mime = $info['mime'];
    if ($mime == 'image/jpeg') $img = imagecreatefromjpeg($src);
    elseif ($mime == 'image/png') $img = imagecreatefrompng($src);
    elseif ($mime == 'image/gif') $img = imagecreatefromgif($src);
    else return false;
    
    if (!$img) return false;
    
    // Convert to grayscale
    imagefilter($img, IMG_FILTER_GRAYSCALE);
    
    // Increase contrast slightly to look more like a "sketch"
    imagefilter($img, IMG_FILTER_CONTRAST, -20); 
    
    imagepng($img, $dest);
    imagedestroy($img);
    return true;
}

// Convert logo.png to logo-bw.png
if (file_exists($logoPath)) {
    makeGrayscale($logoPath, $logoBwPath);
}

// Ensure tutwuri exists or is an image. If it is empty or text, recreate a placeholder
$isTutwuriValid = file_exists($tutwuriPath) && @getimagesize($tutwuriPath);
if (!$isTutwuriValid) {
    // Create a dummy placeholder image for tutwuri
    $img = imagecreatetruecolor(100, 100);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $fg = imagecolorallocate($img, 0, 0, 0);
    imagefilledrectangle($img, 0, 0, 100, 100, $bg);
    imagestring($img, 3, 20, 40, "Tut Wuri", $fg);
    imagepng($img, $tutwuriPath);
    imagedestroy($img);
}

makeGrayscale($tutwuriPath, $tutwuriBwPath);

echo "Logos generated.\n";
