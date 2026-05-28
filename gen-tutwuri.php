<?php
$img = imagecreatetruecolor(100, 100);
$bg = imagecolorallocate($img, 30, 144, 255); // DodgetBlue
$fg = imagecolorallocate($img, 255, 255, 255); // White
imagefilledrectangle($img, 0, 0, 100, 100, $bg);
imagestring($img, 3, 15, 40, "TUT WURI", $fg);
imagepng($img, "public/images/tutwuri.png");
imagedestroy($img);
echo "Placeholder generated.\n";
