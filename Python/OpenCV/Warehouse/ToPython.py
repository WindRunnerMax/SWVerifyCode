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
          binary = binary + "'" +fileImg.split(".")[0] + "'" + ":" + str(img.tolist()) + ","
          # cv2.imwrite("test.jpg", np.array(img.tolist()))
    binary = "charMap = {" + binary + "}" 
    with open("CharMap.py",'w+') as f: 
        f.write(binary)
          