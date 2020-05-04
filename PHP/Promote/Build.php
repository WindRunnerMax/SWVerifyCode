<?php

/**
 * @Author Czy
 * @Date 20/05/1
 * @Detail ImgIdenfy
 */

namespace VerifyCode;
require_once("ImgIdenfy.php");

$library = fopen("library", "r+");
$info = fread($library,filesize("library"));
if(!$info) $charMap = [];
else $charMap = unserialize(gzuncompress($info));
while (1) {
    $img = imagecreatefromjpeg("http://grdms.sdust.edu.cn:8081/security/jcaptcha.jpg"); //获取图片
    imagejpeg($img,"v.jpg"); // 写入硬盘
    list($result, $imgStringArr) = ImgIdenfy::build($img, $charMap, 250, 100);
    echo($result."\n");
    $input = fgets(STDIN);
    if(isset($input[0]) && $input[0] === "$") break;
    $n = strlen($input) - 2;
    for ($i=0; $i < $n; $i++) {
        if(!isset($result[$i]) || $input[$i] !== $result[$i]) $charMap[$input[$i].mt_rand(1, 10000)] = $imgStringArr[$i];
    }
    echo count($charMap)."\n";
    ftruncate($library,0);
    rewind($library);
    fwrite($library,gzcompress(serialize($charMap)));
}
fclose($library);