import requests
import cv2
import numpy as np
import sys
from Convert import Convert

class GetImg(object):
    """docstring for GetImg"""
    def __init__(self):
        super(GetImg, self).__init__()
    
    def run(self):
        count = 1
        cvt = Convert()
        while True:
            print("第",count,"张")
            req = requests.get("http://XXXXXXXXXXXXXX/jsxsd/verifycode.servlet")
            with open("pv.jpg",'wb') as fb: 
                fb.write(req.content)
            img = cvt.run(req.content)
            cv2.imwrite("v.jpg",img)
            mark = input()
            if mark == "" : continue;
            count += 1
            cv2.imwrite("TrainImg/%s.jpg" % (mark),img)

if __name__ == '__main__':
    GetImg().run()