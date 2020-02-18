# 强智教务系统验证码识别

```
PHP 目录下提供切割对比方式识别
Python 目录下提供CNN方式与OpenCV方式识别
```

## EXAMPLE
教务系统版本有所不同  
我们学校的验证码只有`['1', '2', '3', 'b', 'c', 'm', 'n', 'v', 'x', 'z']`字符  
图像大小为  `22*62`  类似于下图   

![image](https://raw.githubusercontent.com/WindrunnerMax/SWVerifyCode/master/Example.jpg)  

## 识别率
以供CNN作测试集使用的128张验证码为标本测试识别率

Python CNN | Python CV | PHP CM
---|--- | ---
96.87% | 100.00% | 93.75%


