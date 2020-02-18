#!/usr/bin/python 
# -*- coding: utf-8 -*-

from fnmatch import fnmatch
from queue import Queue
import matplotlib.pyplot as plt
import cv2
import time
import os
from Convert import Convert
import requests



def _get_static_binary_image(img, threshold = 140):
  '''
  手动二值化
  '''

  img = Image.open(img)
  img = img.convert('L')
  pixdata = img.load()
  w, h = img.size
  for y in range(h):
    for x in range(w):
      if pixdata[x, y] < threshold:
        pixdata[x, y] = 0
      else:
        pixdata[x, y] = 255

  return img


def cfs(im,x_fd,y_fd):
  '''用队列和集合记录遍历过的像素坐标代替单纯递归以解决cfs访问过深问题
  '''

  # print('**********')

  xaxis=[]
  yaxis=[]
  visited =set()
  q = Queue()
  q.put((x_fd, y_fd))
  visited.add((x_fd, y_fd))
  offsets=[(1, 0), (0, 1), (-1, 0), (0, -1)]#四邻域

  while not q.empty():
      x,y=q.get()

      for xoffset,yoffset in offsets:
          x_neighbor,y_neighbor = x+xoffset,y+yoffset

          if (x_neighbor,y_neighbor) in (visited):
              continue  # 已经访问过了

          visited.add((x_neighbor, y_neighbor))

          try:
              if im[x_neighbor, y_neighbor] == 0:
                  xaxis.append(x_neighbor)
                  yaxis.append(y_neighbor)
                  q.put((x_neighbor,y_neighbor))

          except IndexError:
              pass
  # print(xaxis)
  if (len(xaxis) == 0 | len(yaxis) == 0):
    xmax = x_fd + 1
    xmin = x_fd
    ymax = y_fd + 1
    ymin = y_fd

  else:
    xmax = max(xaxis)
    xmin = min(xaxis)
    ymax = max(yaxis)
    ymin = min(yaxis)
    #ymin,ymax=sort(yaxis)

  return ymax,ymin,xmax,xmin

def detectFgPix(im,xmax):
  '''搜索区块起点
  '''

  h,w = im.shape[:2]
  for y_fd in range(xmax+1,w):
      for x_fd in range(h):
          if im[x_fd,y_fd] == 0:
              return x_fd,y_fd

def CFS(im):
  '''切割字符位置
  '''

  zoneL=[]#各区块长度L列表
  zoneWB=[]#各区块的X轴[起始，终点]列表
  zoneHB=[]#各区块的Y轴[起始，终点]列表

  xmax=0#上一区块结束黑点横坐标,这里是初始化
  for i in range(10):

      try:
          x_fd,y_fd = detectFgPix(im,xmax)
          # print(y_fd,x_fd)
          xmax,xmin,ymax,ymin=cfs(im,x_fd,y_fd)
          L = xmax - xmin
          H = ymax - ymin
          zoneL.append(L)
          zoneWB.append([xmin,xmax])
          zoneHB.append([ymin,ymax])

      except TypeError:
          return zoneL,zoneWB,zoneHB

  return zoneL,zoneWB,zoneHB


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
        cv2.imwrite(str(i)+"v.jpg",cropped) # 查看切割效果
    return im_number,imgArr



def main():
    cvt = Convert()
    req = requests.get("http://xxxxxxxxxxxxxxx/verifycode.servlet")
    img = cvt.run(req.content)
    cv2.imwrite("v.jpg",img)

    #切割的位置
    im_position = CFS(img) # Auto

    print(im_position)

    maxL = max(im_position[0])
    minL = min(im_position[0])

    # 如果有粘连字符，如果一个字符的长度过长就认为是粘连字符，并从中间进行切割
    if(maxL > minL + minL * 0.7):
        maxL_index = im_position[0].index(maxL)
        minL_index = im_position[0].index(minL)
        # 设置字符的宽度
        im_position[0][maxL_index] = maxL // 2
        im_position[0].insert(maxL_index + 1, maxL // 2)
        # 设置字符X轴[起始，终点]位置
        im_position[1][maxL_index][1] = im_position[1][maxL_index][0] + maxL // 2
        im_position[1].insert(maxL_index + 1, [im_position[1][maxL_index][1] + 1, im_position[1][maxL_index][1] + 1 + maxL // 2])
        # 设置字符的Y轴[起始，终点]位置
        im_position[2].insert(maxL_index + 1, im_position[2][maxL_index])

    # 切割字符，要想切得好就得配置参数，通常 1 or 2 就可以
    cutting_img_num,imgArr = cutting_img(img,im_position,1,1)

    # # 直接使用库读取图片识别验证码 
    # result=""
    # for i in range(cutting_img_num):
    #     try:
    #       template = imgArr[i]
    #       tempResult=""
    #       matchingDegree=0.0
    #       filedirWarehouse = 'Warehouse/StrIntell/'
    #       for fileImg in os.listdir(filedirWarehouse):
    #         if fnmatch(fileImg, '*.jpg'):
    #           # print(file)
    #           img = cv2.imread(filedirWarehouse+fileImg,0)
    #           res = cv2.matchTemplate(img,template,3) #img原图 template模板   用模板匹配原图
    #           min_val, max_val, min_loc, max_loc = cv2.minMaxLoc(res)
    #           # print(str(i)+" "+file.split('.')[0]+" "+str(max_val))
    #           if(max_val>matchingDegree):
    #             tempResult=fileImg.split('.')[0]
    #             matchingDegree=max_val
    #       result+=tempResult
    #       matchingDegree=0.0
    #     except Exception as err:
    #       print("ERROR "+ str(err))
    #       pass
    # print('切图：%s' % cutting_img_num)
    # print('识别为：%s' % result)



if __name__ == '__main__':
  main()