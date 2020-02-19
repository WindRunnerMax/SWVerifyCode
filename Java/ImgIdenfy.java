package com.sw;


import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.net.URL;
import java.util.Map;

/**
 * @Author Czy
 * @Date 20/02/17
 * @detail 验证码识别
 */

public class ImgIdenfy {

    private int height = 22;
    private int width = 62;
    private int rgbThres = 150;

    public int[][] binaryImg(BufferedImage img) {
        int[][] imgArr = new int[this.height][this.width];
        for (int x = 0; x < this.width; ++x) {
            for (int y = 0; y < this.height; ++y) {
                if (x == 0 || y == 0 || x == this.width - 1 || y == this.height - 1) {
                    imgArr[y][x] = 1;
                    continue;
                }
                int pixel = img.getRGB(x, y);
                if (((pixel & 0xff0000) >> 16) < this.rgbThres && ((pixel & 0xff00) >> 8) < this.rgbThres && (pixel & 0xff) < this.rgbThres) {
                    imgArr[y][x] = 0;
                } else {
                    imgArr[y][x] = 1;
                }
            }
        }
        return imgArr;
    }

    // 去掉干扰线
    public void removeByLine(int[][] imgArr) {
        for (int y = 1; y < this.height - 1; ++y) {
            for (int x = 1; x < this.width - 1; ++x) {
                if (imgArr[y][x] == 0) {
                    int count = imgArr[y][x - 1] + imgArr[y][x + 1] + imgArr[y + 1][x] + imgArr[y - 1][x];
                    if (count > 2) imgArr[y][x] = 1;
                }
            }
        }
    }

    // 裁剪
    public int[][][] imgCut(int[][] imgArr, int[][] xCut, int[][] yCut, int num) {
        int[][][] imgArrArr = new int[num][yCut[0][1] - yCut[0][0]][xCut[0][1] - xCut[0][0]];
        for (int i = 0; i < num; ++i) {
            for (int j = yCut[i][0]; j < yCut[i][1]; ++j) {
                for (int k = xCut[i][0]; k < xCut[i][1]; ++k) {
                    imgArrArr[i][j-yCut[i][0]][k-xCut[i][0]] = imgArr[j][k];
                }
            }
        }
        return imgArrArr;
    }

    // 转字符串
    public String getString(int[][] imgArr){
        StringBuilder s = new StringBuilder();
        int unitHeight = imgArr.length;
        int unitWidth = imgArr[0].length;
        for (int y = 0; y < unitHeight; ++y) {
            for (int x = 0; x < unitWidth; ++x) {
                s.append(imgArr[y][x]);
            }
        }
        return s.toString();
    }

    // 相同大小直接对比
    private int comparedText(String s1,String s2){
        int n = s1.length();
        int percent = 0;
        for(int i = 0; i < n ; ++i) {
            if (s1.charAt(i) == s2.charAt(i)) percent++;
        }
        return percent;
    }

    /**
     * 匹配识别
     * @param imgArrArr
     * @return
     */
    public String matchCode(int [][][] imgArrArr){
        StringBuilder s = new StringBuilder();
        Map<String,String> charMap = CharMap.getCharMap();
        for (int[][] imgArr : imgArrArr){
            int maxMatch = 0;
            String tempRecord = "";
            for(Map.Entry<String,String> m : charMap.entrySet()){
                int percent = this.comparedText(this.getString(imgArr),m.getValue());
                if(percent > maxMatch){
                    maxMatch = percent;
                    tempRecord = m.getKey();
                }
            }
            s.append(tempRecord);
        }
        return s.toString();
    }

    // 写入硬盘
    public void writeImage(BufferedImage sourceImg) {
        File imageFile = new File("v.jpg");
        try (FileOutputStream outStream = new FileOutputStream(imageFile)) {
            ByteArrayOutputStream out = new ByteArrayOutputStream();
            ImageIO.write(sourceImg, "jpg", out);
            byte[] data = out.toByteArray();
            outStream.write(data);
        } catch (Exception e) {
            System.out.println(e.toString());
        }
    }

    // 控制台打印
    public void showImg(int[][] imgArr) {
        int unitHeight = imgArr.length;
        int unitWidth = imgArr[0].length;
        for (int y = 0; y < unitHeight; ++y) {
            for (int x = 0; x < unitWidth; ++x) {
                System.out.print(imgArr[y][x]);
            }
            System.out.println();
        }
    }

    public static void main(String[] args) {
        ImgIdenfy imgIdenfy = new ImgIdenfy();
        try (InputStream is = new URL("http://xxxxxxxxxxxxxxx/verifycode.servlet").openStream()) {
            BufferedImage sourceImg = ImageIO.read(is);
            imgIdenfy.writeImage(sourceImg); // 图片写入硬盘
            int[][] imgArr = imgIdenfy.binaryImg(sourceImg); // 二值化
            imgIdenfy.removeByLine(imgArr); // 去除干扰先 引用传递
            int[][][] imgArrArr = imgIdenfy.imgCut(imgArr,
                    new int[][]{new int[]{4, 13}, new int[]{14, 23}, new int[]{24, 33}, new int[]{34, 43}},
                    new int[][]{new int[]{4, 16}, new int[]{4, 16}, new int[]{4, 16}, new int[]{4, 16}},
                    4);
            System.out.println(imgIdenfy.matchCode(imgArrArr)); // 识别

//            imgIdenfy.showImg(imgArr); //控制台打印图片
//            imgIdenfy.showImg(imgArrArr[0]); //控制台打印图片
//            System.out.println(imgIdenfy.getString(imgArrArr[0]));


        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}

