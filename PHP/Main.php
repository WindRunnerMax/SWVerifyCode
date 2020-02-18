<?php
/**
 * ImgIdenfy
 */
namespace ImgIdenfy;
require_once("CharMap.php");

class ImgIdenfy {

    private static $height = 22;
    private static $width = 62;
    private static $rgbThres = 150;

    // 二值化
    public static function binaryImage($im){
        $imgArr = [];
        for($x = 0;$x < self::$width;$x++) {
            for($y =0;$y < self::$height;$y++) {
                if($x === 0 || $y === 0 || $x === self::$width - 1 || $y === self::$height - 1){
                    $imgArr[$y][$x] = 1;
                    continue;
                }
                $rgb = imagecolorat($im, $x, $y);
                $rgb = imagecolorsforindex($im, $rgb);
                if($rgb['red'] < self::$rgbThres && $rgb['green'] < self::$rgbThres && $rgb['blue'] < self::$rgbThres) {
                    $imgArr[$y][$x] = 0;
                } else {
                    $imgArr[$y][$x] = 1;
                }
            }
        }
        return $imgArr;
    }

    // 输出图片
    public static function showImg($imgArr){
        foreach($imgArr as $xImage) {
            foreach($xImage as $yImage) {
                echo($yImage);
            }
            echo "\n";
        }
    }

    // 裁剪
    public static function cutImg($img,$arrX,$arrY,$n){
        $imgArr = [];
        for($i = 0;$i < $n; ++$i){
            $unitImg = [[]];
            for ($j=$arrY[$i][0]; $j < $arrY[$i][1]; $j++) { 
                for ($k=$arrX[$i][0]; $k < $arrX[$i][1]; $k++) { 
                    $unitImg[$j][$k] = $img[$j][$k];
                }
            }
            array_push($imgArr, $unitImg);
        }
        return $imgArr;
    }

    // 转为字符串
    public static function getString($img) {
        $s = "";
        foreach($img as $image) {
            foreach($image as $string) {
                $s .= $string;
            }
        }
        return $s;
    }

    // 降噪
    public static function removeByLine($imgArr) {
        $xCount = count($imgArr[0]);
        $yCount = count($imgArr); 
        for ($i=1; $i < $yCount-1 ; $i++) { 
            for ($k=1; $k < $xCount-1; $k++) { 
                if($imgArr[$i][$k] === 0){
                    $countOne = $imgArr[$i][$k-1] + $imgArr[$i][$k+1] + $imgArr[$i+1][$k] + $imgArr[$i-1][$k];
                    if($countOne > 2) $imgArr[$i][$k] = 1;
                } 
            }
        }
        return $imgArr;
    }

    // 相同字符串长度，直接对比
    public static function comparedText($s1,$s2){
        $n = strlen($s1);
        $percent = 0;
        for ($i=0; $i < $n; $i++) { 
            $s1[$i] === $s2[$i] ? $percent++ : "";
        }
        return $percent;
    }

    // 匹配
    public static function matchCode($imgArr,$charMap){
        $record = "";
        foreach ($imgArr as $img) {
            $maxMatch = 0;
            $tempRecord = "";
            foreach ($charMap as $key => $value) {
                // similar_text(ImgIdenfy::getString($img),$value,$percent);
                $percent = self::comparedText(ImgIdenfy::getString($img), $value);
                if($percent > $maxMatch){
                    $maxMatch = $percent;
                    $tempRecord = $key;
                }
            }
            $record = $record.$tempRecord;
        }
        return $record;
    }

}

// =================== 测试 =====================
function test($charMap){
    $list = glob('TestImg/*.jpg');
    $acceptCount = 0;
    $count = 0;
    foreach ($list as $v) {
        # code...
        $count++;
        $img = imagecreatefromjpeg($v);
        $imgArr = ImgIdenfy::binaryImage($img);
        $imgArr = ImgIdenfy::removeByLine($imgArr);
        $imgArrArr = ImgIdenfy::cutImg($imgArr,[[4, 13], [14, 23], [24, 33], [34, 43]],[[4, 16], [4, 16], [4, 16], [4, 16]],4);
        if(ImgIdenfy::matchCode($imgArrArr,$charMap) === explode(".",explode("/", $v)[1])[0]) $acceptCount++;
    }
    echo $acceptCount/$count;
}
// test($charMap);



// =================== 主函数 =====================
function main($charMap){
    $img = imagecreatefromjpeg("http://xxxxxxxxxxxxxxxxx/verifycode.servlet"); //获取图片
    imagejpeg($img,"v.jpg"); // 写入硬盘
    $imgArr = ImgIdenfy::binaryImage($img); // 二值化
    $imgArr = ImgIdenfy::removeByLine($imgArr); // 降噪
    $imgArrArr = ImgIdenfy::cutImg($imgArr,[[4, 13], [14, 23], [24, 33], [34, 43]],[[4, 16], [4, 16], [4, 16], [4, 16]],4); // 切割
    echo ImgIdenfy::matchCode($imgArrArr,$charMap); // 识别

    // ImgIdenfy::showImg($imgArr); // 显示图片
    // ImgIdenfy::showImg($imgArrArr[0]);
    // echo ImgIdenfy::getString($imgArrArr[0]);
    // ImgIdenfy::showImg($imgArrArr[1]);
    // echo ImgIdenfy::getString($imgArrArr[1]);
    // ImgIdenfy::showImg($imgArrArr[2]);
    // echo ImgIdenfy::getString($imgArrArr[2]);
    // ImgIdenfy::showImg($imgArrArr[3]);
    // echo ImgIdenfy::getString($imgArrArr[3]);
}

main($charMap);


