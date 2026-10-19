<?php
// shopping_cart/user/captcha.php - Dynamic GD CAPTCHA Generator
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate random 5-character string
$chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
$length = 5;
$captchaString = '';
for ($i = 0; $i < $length; $i++) {
    $captchaString .= $chars[rand(0, strlen($chars) - 1)];
}

// Store in session
$_SESSION['captcha_code'] = $captchaString;

// Image dimensions
$width = 140;
$height = 45;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg = imagecolorallocate($image, 245, 247, 250);
$textColor = imagecolorallocate($image, 30, 41, 59);
$lineColor1 = imagecolorallocate($image, 203, 213, 225);
$lineColor2 = imagecolorallocate($image, 148, 163, 184);
$dotColor = imagecolorallocate($image, 100, 116, 139);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Add noise lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), ($i % 2 === 0) ? $lineColor1 : $lineColor2);
}

// Add noise dots
for ($i = 0; $i < 80; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $dotColor);
}

// Draw characters
$fontSize = 5; // Built-in GD font
$charWidth = imagefontwidth($fontSize);
$charHeight = imagefontheight($fontSize);
$totalTextWidth = strlen($captchaString) * ($charWidth + 10);
$startX = (int)(($width - $totalTextWidth) / 2);

for ($i = 0; $i < strlen($captchaString); $i++) {
    $x = $startX + ($i * ($charWidth + 10));
    $y = rand(10, 18);
    // Draw character with slight shadow
    $shadowColor = imagecolorallocate($image, 180, 190, 205);
    imagestring($image, $fontSize, $x + 1, $y + 1, $captchaString[$i], $shadowColor);
    imagestring($image, $fontSize, $x, $y, $captchaString[$i], $textColor);
}

// Border
$borderColor = imagecolorallocate($image, 203, 213, 225);
imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

// Output image
header("Content-Type: image/png");
header("Cache-Control: no-cache, must-revalidate");
imagepng($image);
imagedestroy($image);
