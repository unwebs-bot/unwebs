<?php
/**
 * 스팸방지 이미지 캡챠 생성
 *
 * 보안 강화: CSRF 방지, 세션 고정 공격 방지
 */

// 동일 도메인 Referer 검증 (CSRF / 외부 직접 호출 차단)
$allowed_referer = isset($_SERVER['HTTP_REFERER']) &&
    isset($_SERVER['HTTP_HOST']) &&
    strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false;
if (!$allowed_referer) {
    header('HTTP/1.0 403 Forbidden');
    header('X-Content-Type-Options: nosniff');
    exit('Forbidden');
}

// 보안 헤더 설정
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if (session_status() === PHP_SESSION_NONE) {
  // 세션 보안 설정
  ini_set('session.cookie_httponly', 1);
  ini_set('session.cookie_samesite', 'Strict');
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
  }
  session_start();
}

// 세션 재생성 (세션 고정 공격 방지) - 캡챠 생성 시마다
if (!isset($_SESSION['uw_captcha_initialized'])) {
  session_regenerate_id(true);
  $_SESSION['uw_captcha_initialized'] = true;
}

// 랜덤 코드 생성 (cryptographically secure)
$captcha_code = '';
for ($i = 0; $i < 6; $i++) {
  $captcha_code .= random_int(0, 9);
}

// 세션에 저장
$_SESSION['uw_captcha_code'] = $captcha_code;

// 이미지 생성
$width = 150;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// 색상 정의
$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 50, 50, 50);
$line_color = imagecolorallocate($image, 180, 180, 180);
$noise_color = imagecolorallocate($image, 150, 150, 150);

// 배경 채우기
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// 테두리
imagerectangle($image, 0, 0, $width - 1, $height - 1, $line_color);

// 노이즈 라인 추가
for ($i = 0; $i < 5; $i++) {
  imageline(
    $image,
    rand(0, $width),
    rand(0, $height),
    rand(0, $width),
    rand(0, $height),
    $line_color
  );
}

// 노이즈 점 추가
for ($i = 0; $i < 100; $i++) {
  imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// 텍스트 그리기
$font_size = 5; // 내장 폰트 사용
$x = 15;
for ($i = 0; $i < strlen($captcha_code); $i++) {
  $y = rand(10, 15);
  imagestring($image, $font_size, $x, $y, $captcha_code[$i], $text_color);
  $x += rand(18, 22);
}

// 헤더 설정 및 출력
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

imagepng($image);
imagedestroy($image);
exit;
