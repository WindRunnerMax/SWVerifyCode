<?php

/**
 * @Author Czy
 * @Date 20/05/1
 * @Detail ImgIdenfy
 */

namespace VerifyCode;
require_once("ImgIdenfy.php");

$img = imagecreatefromjpeg("http://grdms.sdust.edu.cn:8081/security/jcaptcha.jpg"); //获取图片
imagejpeg($img,"v.jpg"); // 写入硬盘

$library = fopen("library", "r+");
$info = fread($library,filesize("library"));
if(!$info) $charMap = [];
else $charMap = unserialize(gzuncompress($info));
$result = ImgIdenfy::run($img, $charMap, 250, 100);
echo($result."\n");
fclose($library);