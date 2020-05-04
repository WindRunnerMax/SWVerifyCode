<?php

/**
 * @Author Czy
 * @Date 20/05/1
 * @Detail ImgIdenfy
 */

namespace VerifyCode;
require_once("ImgIdenfy.php");

// 测试压缩
// $library = fopen("library-ori.txt", "r+");
// $info = fread($library,filesize("library-ori.txt"));
// $zip = gzcompress($info);
// var_dump(strlen($info));
// var_dump(strlen($zip));
// var_dump($info === gzuncompress($zip));

// 转换library-ori.txt为library
// $libraryOri = fopen("library-ori.txt", "r");
// $info = fread($libraryOri,filesize("library-ori.txt"));
// $library = fopen("library", "w+");
// fwrite($library,gzcompress($info));
// fclose($library);
// fclose($libraryOri);

// 写入空数据
// $info = serialize([]);
// $library = fopen("library", "w+");
// fwrite($library,gzcompress($info));
// fclose($library);

// 测试读取
// $library = fopen("library", "r+");
// $info = fread($library,filesize("library"));
// if(!$info) $charMap = [];
// else $charMap = unserialize(gzuncompress($info));
// echo count($charMap);
// ftruncate($library,0);
// rewind($library);
// fwrite($library,gzcompress(serialize($charMap)));
// fclose($library);

// 字符倾斜校正测试 线性回归 最小二乘法拟合

// $img = [
//     [1,1,1,1,1,0,0,0,0,1,1,1,1,0,0,0,0,0,0,0,0,0,1,1],
//     [1,1,1,1,1,0,0,0,0,1,1,1,0,0,0,0,0,0,0,0,0,0,0,1],
//     [1,1,1,1,0,0,0,0,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0],
//     [1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0],
//     [1,1,1,1,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,0,0,0,0],
//     [1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0,0,0,0],
//     [1,1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,0,0,0,0],
//     [1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0],
//     [1,1,1,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0],
//     [1,1,1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0],
//     [1,1,1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0],
//     [1,1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0],
//     [1,1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1],
//     [1,1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1],
//     [1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1],
//     [1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1],
//     [1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1],
//     [1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1,1],
//     [1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1,1],
//     [1,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1,1],
//     [1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1,1],
//     [1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1,1],
//     [0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1,1,1],
//     [0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1,1,1],
//     [0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1,1,1],
//     [0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1,1,1],
// ];
// ImgIdenfy::showImg($img);
// $mixX = 0.0;
// $mixY = 0.0;
// $mixXX = 0.0;
// $mixXY = 0.0;
// $yCount = count($img);
// $xCount = count($img[0]);
// foreach($img as $i => $line) {
//     $x = 0;
//     $xValidCount = 0;
//     foreach($line as $k => $unit) {
//         if($unit === 0) {
//             $x += $k;
//             ++$xValidCount;
//         }
//     }
//     if($xValidCount) {
//         $pointX = $x/$xValidCount;
//         $pointY = $yCount - $i;
//         $mixX += $pointX;
//         $mixY += $pointY;
//         $mixXX += ($pointX*$pointX);
//         $mixXY += ($pointX*$pointY);
//     }
// }
// $linearK = -($mixXY - $mixX*$mixY/$yCount) / ($mixXX - $mixX*$mixX/$yCount);
// // if($linearK > -1 && $linearK < 1) return $img;
// $whirlImg = [];
// foreach($img as $i => $line) {
//     $pointY = $i;
//     if(!isset($whirlImg[$pointY])) $whirlImg[$pointY]=[];
//     foreach($line as $pointX => $unit) {
//         if(!isset($whirlImg[$pointY][$pointX])) $whirlImg[$pointY][$pointX]=1;
//         // $newY = (int)($pointY*sqrt(1+$linearK*$linearK)/$linearK);
//         $newY = (int)($pointY);
//         $newX = (int)($pointX-$pointY/$linearK);
//         if($newX >= 0 && $newX < $xCount && $newY >= 0 && $newY < $yCount) $whirlImg[$newY][$newX] = $unit;
//     }
// }

// $finishedImg = [];
// for ($i=0; $i < $xCount; ++$i) { 
//     for($k=0; $k < $yCount; ++$k) {
//         if($whirlImg[$k][$i] !== 1){
//             for($y = 0;$y < $yCount;++$y) $finishedImg[$y][] = $whirlImg[$y][$i];
//             break;
//         }
//     }
// }
// ImgIdenfy::showImg($finishedImg);

// 字符倾斜校正测试 投影法
// $img = [
// [1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,1,1,1,1,1,1,1,],
// [1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,],
// [1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,],
// [1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,],
// [1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,],
// [1,1,1,1,0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0,0,0,0,1,],
// [1,1,1,0,0,0,0,0,0,0,0,1,1,1,1,1,1,0,0,0,0,0,0,0,0,],
// [1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,],
// [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,],
// [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,],
// [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,],
// [1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,],
// [1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,],
// [1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,],
// [1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,],
// [1,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0,0,1,1,],
// [1,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,],
// [1,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,],
// [0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,],
// [0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,],
// [0,0,0,0,0,0,0,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,1,1,1,],
// [0,0,0,0,0,0,0,0,1,1,1,1,1,0,0,0,0,0,0,0,0,0,1,1,1,],
// [1,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,1,1,1,],
// [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,],
// [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,],
// [1,1,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,1,1,1,],
// [1,1,1,1,0,0,0,0,0,0,0,0,1,1,1,0,0,0,0,0,0,0,1,1,1,],
// [1,1,1,1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,],
// ];
// function showImg($img){
//     $xCount = count($img[0]);
//     $yCount = count($img); 
//     for ($i=0; $i < $yCount; $i++) { 
//         for ($k=0; $k < $xCount; $k++) { 
//             echo $img[$i][$k];
//         }
//         echo "\n";
//     }
// }
// showImg($img);
// $reverseImg = [];
// $yCount = count($img);
// $xCount = count($img[0]);
// for ($i=0; $i < $yCount; ++$i) { 
//     $pointY = $yCount - $i - 1;
//     for($k=0; $k < $xCount; ++$k) {
//         $reverseImg[$k][$i] = $img[$pointY][$k];
//     }
// }
// ImgIdenfy::showImg($reverseImg);

// list($yCount,$xCount) = [$xCount,$yCount];

// function whirl($img, $yCount, $xCount, $linearK){
//     $whirlImg = [];
//     foreach($img as $i => $line) {
//         $pointY = $yCount - $i - 1;
//         if(!isset($whirlImg[$pointY])) $whirlImg[$pointY]=[];
//         foreach($line as $pointX => $unit) {
//             if(!isset($whirlImg[$pointY][$pointX])) $whirlImg[$pointY][$pointX]=1;
//             // $newY = (int)($pointY*sqrt(1+$linearK*$linearK)/$linearK);
//             $newY = (int)($pointY - $pointX*$linearK);
//             $newX = (int)($pointX);
//             if($unit === 0 && ($newY < 0 || $newY >= $yCount)) return [$yCount+1, $img];
//             if($newX >= 0 && $newX < $xCount && $newY >= 0 && $newY < $yCount) $whirlImg[$newY][$newX] = $unit;
//         }
//     }
//     $cutImg = [];
//     $height = $yCount ;
//     foreach ($whirlImg as $j => $line) {
//         foreach ($line as $k => $v) {
//             if($v !== 1) {
//                 --$height;
//                 break;
//             }
//         }
//     }
//     return [$yCount - $height, $whirlImg];
// }

// $min = $yCount;
// $minImg = $reverseImg;
// for ($k= -0.5 ; $k <= 0.5; $k = $k + 0.05) { 
//     list($tempMin, $tempMinImg) = whirl($reverseImg, $yCount, $xCount, $k);
//     if($tempMin < $min) {
//         $min = $tempMin;
//         $minImg = $tempMinImg;
//     }
// }
// echo $min."\n";
// showImg($minImg);
// $removedImg = [];
// foreach ($minImg as $j => $line) {
//     foreach ($line as $k => $v) {
//         if($v !== 1) {
//             $removedImg[] = $line;
//             break;
//         }
//     }
// }
// $reverseImg = [];
// $xCount = count($removedImg[0]);
// $yCount = count($removedImg);
// $reverseImg = [];
// for ($i=0; $i < $xCount; ++$i) { 
//     for($k=0; $k < $yCount; ++$k) {
//         $pointX = $xCount - $i - 1;
//         $reverseImg[$i][$k] = $removedImg[$k][$pointX];
//     }
// }
// ImgIdenfy::showImg($reverseImg);
// showImg($reverseImg);
