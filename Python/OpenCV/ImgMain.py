#!/usr/bin/python 
# -*- coding: utf-8 -*-

from fnmatch import fnmatch
from queue import Queue
import matplotlib.pyplot as plt
import cv2
import time
import os
from Convert import Convert
from CharMap import charMap
import requests
import numpy as np



def cutting_img(im,im_position,xoffset = 1,yoffset = 1):
    # 识别出的字符个数
    im_number = len(im_position[1])
    if(im_number>=4): im_number = 4;

    imgArr = []
    # 切割字符
    for i in range(im_number):
        im_start_X = im_position[1][i][0] - xoffset
        im_end_X = im_position[1][i][1] + xoffset
        im_start_Y = im_position[2][i][0] - yoffset
        im_end_Y = im_position[2][i][1] + yoffset
        cropped = im[im_start_Y:im_end_Y, im_start_X:im_end_X]
        imgArr.append(cropped)
        # cv2.imwrite(str(i)+"v.jpg",cropped) # 查看切割效果
    return im_number,imgArr



def main():
    cvt = Convert()
    req = requests.get("http://xxxxxxxxxxxxxxxxxxx/verifycode.servlet")   
    # 注意有些教务加装了所谓云防护，没有请求头会拦截，导致获取不了验证码图片，报错可以打印req.content看看
    img = cvt.run(req.content)
    cv2.imwrite("v.jpg",img) # 查看验证码

    #切割的位置
    im_position = ([7, 7, 7, 7], [[5, 12], [15, 22], [25, 32], [34, 41]], [[4, 15], [4, 15], [4, 15], [4, 15]])

    cutting_img_num,imgArr = cutting_img(img,im_position,1,1)

    # 识别验证码
    result=""
    for i in range(cutting_img_num):
        try:
          template = imgArr[i]
          tempResult=""
          matchingDegree=0.0
          for char in charMap:
            img = np.asarray(charMap[char],dtype = np.uint8)
            res = cv2.matchTemplate(img,template,3) #img原图 template模板   用模板匹配原图
            min_val, max_val, min_loc, max_loc = cv2.minMaxLoc(res)
            if(max_val>matchingDegree):
              tempResult=char
              matchingDegree=max_val
          result += tempResult
          matchingDegree=0.0
        except Exception as err:
          raise Exception
          # print("ERROR "+ str(err))
          pass

    print(result)


if __name__ == '__main__':
  main()