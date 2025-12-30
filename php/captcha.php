<?php
session_start();

$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $chars[random_int(0, strlen($chars) - 1)];
}

$_SESSION['captcha'] = $code;

$img = imagecreatetruecolor(160, 50);
$bg = imagecolorallocate($img, 255, 255, 255);
$text = imagecolorallocate($img, 0, 0, 0);

imagefilledrectangle($img, 0, 0, 160, 50, $bg);

for ($i = 0; $i < 10; $i++) {
    $noise = imagecolorallocate($img, rand(100,255), rand(100,255), rand(100,255));
    imageline($img, rand(0,160), rand(0,50), rand(0,160), rand(0,50), $noise);
}

imagestring($img, 5, 35, 15, $code, $text);

header('Content-type: image/png');
imagepng($img);
imagedestroy($img);
