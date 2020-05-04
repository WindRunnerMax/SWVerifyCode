<?php

/**
 * @Author Czy
 * @Date 20/05/1
 * @Detail ImgIdenfy
 */

namespace VerifyCode;

class ImgIdenfy {

    private static $height = 0;
    private static $width = 0;

    // 二值化
    private static function binaryImage($image){
        $img = [];
        for($y = 0;$y < self::$width;$y++) {
            for($x =0;$x < self::$height;$x++) {
                if($y === 0 || $x === 0 || $y === self::$width - 1 || $x === self::$height - 1){
                    $img[$x][$y] = 1;
                    continue;
                }
                $rgb = imagecolorat($image, $y, $x);
                $rgb = imagecolorsforindex($image, $rgb);
                if($rgb['red'] < 255 && $rgb['green'] < 230 && $rgb['blue'] < 220) {
                    $img[$x][$y] = 0;
                } else {
                    $img[$x][$y] = 1;
                }
            }
        }
        return $img;
    }

    // 输出图片
    public static function showImg($img){
        foreach($img as $line) {
            foreach($line as $unit) {
                echo($unit);
            }
            echo "\n";
        }
    }

    // 裁剪
    private static function cutImg($img){
        $xCount = count($img[0]);
        $yCount = count($img);
        $xFilter = [];
        for($x = 0;$x < $xCount;$x++) {
            $filter = true;
            for($y = 0;$y < $yCount;$y++)  $filter = $filter && ($img[$y][$x] === 1);
            if($filter) $xFilter[] = $x;
        }
        $xImage = array_values(array_diff(range(0, $xCount-1), $xFilter));
        $wordImage = [];
        $preX = $xImage[0] - 1;
        $wordCount = 0;
        foreach($xImage as $xKey => $x) {
            if($x != ($preX + 1))  $wordCount++;
            $preX = $x;
            for($y = 0;$y < $yCount;$y++) $wordImage[$wordCount][$y][] = $img[$y][$x];
        }
        $cutImg = [];
        foreach($wordImage as $i => $image) {
            $xCount = count($image[0]);
            $yCount = count($image);
            $start = 0;
            for ($j=0; $j < $yCount; ++$j) { 
                $stopFlag = false;
                for ($k=0; $k < $xCount; ++$k) { 
                    if ($image[$j][$k] === 0) {
                        $start = $j;
                        $stopFlag = true;
                        break;
                    }
                }
                if($stopFlag) break;
            }
            $stop = $yCount-1;
            for ($j=$yCount-1; $j >= 0; --$j) { 
                $stopFlag = false;
                for ($k=0; $k < $xCount; ++$k) { 
                    if ($image[$j][$k] === 0) {
                        $stop = $j;
                        $stopFlag = true;
                        break;
                    }
                }
                if($stopFlag) break;
            }
            for ($k=$start; $k <= $stop ; ++$k) { 
                $cutImg[$i][] = $image[$k];
            }
            // self::showImg($cutImg[$i]);
            $cutImg[$i] = self::adjustImg($cutImg[$i]);
            // self::showImg($cutImg[$i]);
        }
        return $cutImg;
    }

    // 旋转
    private static function whirl($img, $yCount, $xCount, $linearK){
        $whirlImg = [];
        foreach($img as $i => $line) {
            $pointY = $yCount - $i - 1;
            if(!isset($whirlImg[$pointY])) $whirlImg[$pointY]=[];
            foreach($line as $pointX => $unit) {
                if(!isset($whirlImg[$pointY][$pointX])) $whirlImg[$pointY][$pointX]=1;
                $newY = (int)($pointY - $pointX*$linearK);
                $newX = (int)($pointX);
                if($unit === 0 && ($newY < 0 || $newY >= $yCount)) return [$yCount+1, $img];
                if($newX >= 0 && $newX < $xCount && $newY >= 0 && $newY < $yCount) $whirlImg[$newY][$newX] = $unit;
            }
        }
        $cutImg = [];
        $height = $yCount;
        foreach ($whirlImg as $j => $line) {
            foreach ($line as $k => $v) {
                if($v !== 1) {
                    --$height;
                    break;
                }
            }
        }
        return [$yCount - $height, $whirlImg];
    }

    // 倾斜调整
    private static function adjustImg($img){
        $reverseImg = [];
        $yCount = count($img);
        $xCount = count($img[0]);
        for ($i=0; $i < $yCount; ++$i) { 
            $pointY = $yCount - $i - 1;
            for($k=0; $k < $xCount; ++$k) {
                $reverseImg[$k][$i] = $img[$pointY][$k];
            }
        }
        list($yCount,$xCount) = [$xCount,$yCount];
        $min = $yCount;
        $minImg = $reverseImg;
        for ($k= -0.5 ; $k <= 0.5; $k = $k + 0.05) { 
            list($tempMin, $tempMinImg) = self::whirl($reverseImg, $yCount, $xCount, $k);
            if($tempMin < $min) {
                $min = $tempMin;
                $minImg = $tempMinImg;
            }
        }
        $removedImg = [];
        foreach ($minImg as $j => $line) {
            foreach ($line as $k => $v) {
                if($v !== 1) {
                    $removedImg[] = $line;
                    break;
                }
            }
        }
        $reverseImg = [];
        $xCount = count($removedImg[0]);
        $yCount = count($removedImg);
        $reverseImg = [];
        for ($i=0; $i < $xCount; ++$i) { 
            for($k=0; $k < $yCount; ++$k) {
                $pointX = $xCount - $i - 1;
                $reverseImg[$i][$k] = $removedImg[$k][$pointX];
            }
        }
        return $reverseImg;
    }



    // 转为字符串
    private static function getString($img) {
        $s = "";
        $xCount = count($img[0]);
        $yCount = count($img); 
        for ($i=1; $i < $yCount-1 ; $i++) { 
            for ($k=1; $k < $xCount-1; $k++) { 
                $s .= $img[$i][$k];
            }
        }
        return $s;
    }

    // 降噪 补偿
    private static function noiseReduce($img) {
        $xCount = count($img[0]);
        $yCount = count($img); 
        for ($i=1; $i < $yCount-1 ; $i++) { 
            for ($k=1; $k < $xCount-1; $k++) { 
                if($img[$i][$k] === 0){
                    $countOne = $img[$i][$k-1] + $img[$i][$k+1] + $img[$i+1][$k] + $img[$i-1][$k];
                    if($countOne > 2) $img[$i][$k] = 1;
                } 
                if($img[$i][$k] === 1){
                    $countZero = $img[$i][$k-1] + $img[$i][$k+1] + $img[$i+1][$k] + $img[$i-1][$k];
                    if($countZero < 2) $img[$i][$k] = 0;
                } 
            }
        }
        return $img;
    }

    // 对比
    private static function comparedText($s1,$s2){
        $s1N = strlen($s1);
        $s2N = strlen($s2);
        $i = -1;
        $k = -1;
        $percent = -abs($s1N - $s2N) * 0.1;
        while(++$i<$s1N && $s1[$i]) {}
        while(++$k<$s2N && $s2[$k]) {}
        while ($i<$s1N && $k<$s2N) ($s1[$i++] === $s2[$k++]) ? $percent++ : "";
        return $percent;
        // $percent = 0;
        // $N = $s1N < $s2N ? $s1N : $s2N;
        // for ($i=0; $i < $N; ++$i) { 
        //     ($s1[$i] === $s2[$i]) ? $percent++ : "";
        // }
        // return $percent;
    }

    // 匹配
    private static function matchCode($imgGroup,$charMap){
        $record = "";
        $imgStringArr = [];
        foreach ($imgGroup as $img) {
            $maxMatch = 0;
            $tempRecord = "";
            $s = ImgIdenfy::getString($img);
            foreach ($charMap as $key => $value) {
                // similar_text(ImgIdenfy::getString($img),$value,$percent);
                $percent = self::comparedText($s , $value);
                if($percent > $maxMatch){
                    $maxMatch = $percent;
                    $tempRecord = $key[0];
                }
            }
            $record = $record.$tempRecord;
            $imgStringArr[] = $s;
        }
        return [$record, $imgStringArr];
    }

    public static function build($image, $charMap, $width, $height){
        self::$width = $width;
        self::$height = $height;
        $img = self::binaryImage($image); // 二值化
        $img = self::noiseReduce($img); // 降噪 补偿
        $imgGroup = self::cutImg($img); // 切割 矫正
        list($result, $imgStringArr) = self::matchCode($imgGroup,$charMap); // 识别
        return [$result, $imgStringArr];
    }

    public static function run($image, $charMap, $width, $height){
        self::$width = $width;
        self::$height = $height;
        $img = self::binaryImage($image); // 二值化
        $img = self::noiseReduce($img); // 降噪 补偿
        $imgGroup = self::cutImg($img); // 切割 矫正
        list($result, $_) = self::matchCode($imgGroup,$charMap); // 识别
        return $result;
    }

}

# https://www.cnblogs.com/zyfd/p/9952464.html