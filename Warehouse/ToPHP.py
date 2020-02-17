import cv2
import os
from fnmatch import fnmatch
import numpy as np
np.set_printoptions(threshold=np.inf)

if __name__ == '__main__':
    binary = ""
    for fileImg in os.listdir("StrIntell/"):
        if fnmatch(fileImg, '*.jpg'):
          img = cv2.imread("StrIntell/"+fileImg,0)
          listImgUnit = img.tolist()
          for i,item in enumerate(listImgUnit) :
              for k,item2 in enumerate(item):
                  if item2 > 125 : listImgUnit[i][k] = 255;
                  else : listImgUnit[i][k] = 0;
          binary = binary + "'" +fileImg.split(".")[0] + "'" + "=>" + str(listImgUnit) + ","
          # cv2.imwrite("test.jpg", np.array(img.tolist()))
    binary = "<?php $charMap = [" + binary + "] ?>" 
    with open("CharMap.php",'w+') as f: 
        f.write(binary)
          