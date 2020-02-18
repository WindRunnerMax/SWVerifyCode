import cv2
from fnmatch import fnmatch
import os
 
def main():
    filedir = './StrIntell'
    for file in os.listdir(filedir):
        if fnmatch(file, '*.jpg'):
            fileLoc=filedir+"/"+file
            img=cv2.imread(fileLoc)
            # img=cv2.copyMakeBorder(img,5,5,5,5,cv2.BORDER_CONSTANT,value=[255,255,255]) # 扩大
            img = img[3:20, 3:16] # 裁剪 高*宽
            print(img.shape)
            cv2.imwrite(fileLoc, img)
 
if __name__ == '__main__':
    main()
