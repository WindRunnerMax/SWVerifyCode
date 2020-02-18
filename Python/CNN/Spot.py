import numpy as np
import tensorflow as tf
import cv2
import os
import random
import time
import sys
 
# number
number = ['1', '2', '3', 'b', 'c', 'm', 'n', 'v', 'x', 'z']
 
# 图像大小
IMAGE_HEIGHT = 22  # 80
IMAGE_WIDTH = 62  # 160
MAX_CAPTCHA = 4
 
char_set = number
CHAR_SET_LEN = len(char_set)  #10
valid_path = "TestImg/"
model_path = "model/"
 
 
X = tf.placeholder(tf.float32, [None, IMAGE_HEIGHT * IMAGE_WIDTH])
Y = tf.placeholder(tf.float32, [None, MAX_CAPTCHA * CHAR_SET_LEN])
keep_prob = tf.placeholder(tf.float32)  # dropout
 
 
# 定义CNN
def crack_captcha_cnn(w_alpha=0.01, b_alpha=0.1):
    x = tf.reshape(X, shape=[-1, IMAGE_HEIGHT, IMAGE_WIDTH, 1])
 
    # 3 conv layer
    w_c1 = tf.Variable(w_alpha * tf.random_normal([3, 3, 1, 32]))
    b_c1 = tf.Variable(b_alpha * tf.random_normal([32]))
    conv1 = tf.nn.relu(tf.nn.bias_add(tf.nn.conv2d(x, w_c1, strides=[1, 1, 1, 1], padding='SAME'), b_c1))
    conv1 = tf.nn.max_pool(conv1, ksize=[1, 2, 2, 1], strides=[1, 2, 2, 1], padding='SAME')
    conv1 = tf.nn.dropout(conv1, keep_prob)
 
    w_c2 = tf.Variable(w_alpha * tf.random_normal([3, 3, 32, 64]))
    b_c2 = tf.Variable(b_alpha * tf.random_normal([64]))
    conv2 = tf.nn.relu(tf.nn.bias_add(tf.nn.conv2d(conv1, w_c2, strides=[1, 1, 1, 1], padding='SAME'), b_c2))
    conv2 = tf.nn.max_pool(conv2, ksize=[1, 2, 2, 1], strides=[1, 2, 2, 1], padding='SAME')
    conv2 = tf.nn.dropout(conv2, keep_prob)
 
    w_c3 = tf.Variable(w_alpha * tf.random_normal([3, 3, 64, 64]))
    b_c3 = tf.Variable(b_alpha * tf.random_normal([64]))
    conv3 = tf.nn.relu(tf.nn.bias_add(tf.nn.conv2d(conv2, w_c3, strides=[1, 1, 1, 1], padding='SAME'), b_c3))
    conv3 = tf.nn.max_pool(conv3, ksize=[1, 2, 2, 1], strides=[1, 2, 2, 1], padding='SAME')
    conv3 = tf.nn.dropout(conv3, keep_prob)
 
    # Fully connected layer
    w_d = tf.Variable(w_alpha * tf.random_normal([3 * 8 * 64, 1024]))
    b_d = tf.Variable(b_alpha * tf.random_normal([1024]))
    dense = tf.reshape(conv3, [-1, w_d.get_shape().as_list()[0]])
    dense = tf.nn.relu(tf.add(tf.matmul(dense, w_d), b_d))
    dense = tf.nn.dropout(dense, keep_prob)
 
    w_out = tf.Variable(w_alpha * tf.random_normal([1024, MAX_CAPTCHA * CHAR_SET_LEN]))
    b_out = tf.Variable(b_alpha * tf.random_normal([MAX_CAPTCHA * CHAR_SET_LEN]))
    out = tf.add(tf.matmul(dense, w_out), b_out)
    # out = tf.nn.softmax(out)
    return out
 
 
# 向量转回文本
def vec2text(vec):
    char_pos = vec.nonzero()[0]
    text = []
    for i, c in enumerate(char_pos):
        text.append(char_set[c % CHAR_SET_LEN])
    return "".join(text)
 
 
def predict_captcha(captcha_image):
    output = crack_captcha_cnn()
 
    saver = tf.train.Saver()
    with tf.Session() as sess:
        saver.restore(sess, tf.train.latest_checkpoint(model_path))
 
        predict = tf.argmax(tf.reshape(output, [-1, MAX_CAPTCHA, CHAR_SET_LEN]), 2)
        text_list = sess.run(predict, feed_dict={X: [captcha_image], keep_prob: 1})
 
        text = text_list[0].tolist()
        vector = np.zeros(MAX_CAPTCHA * CHAR_SET_LEN)
        i = 0
        for n in text:
            vector[i * CHAR_SET_LEN + n] = 1
            i += 1
        return vec2text(vector)
 

if not os.path.exists(valid_path):
    print('Image does not exist, please check!, path:"{}"'.format(os.path.abspath(valid_pathb)))
    sys.exit()
image_list = os.listdir(valid_path)
 
 
output = crack_captcha_cnn()
 
saver = tf.train.Saver()
with tf.Session() as sess:
    saver.restore(sess, tf.train.latest_checkpoint(model_path))
 
    predict = tf.argmax(tf.reshape(output, [-1, MAX_CAPTCHA, CHAR_SET_LEN]), 2)
    
    count = 0
    acceptCount = 0
    for image_ in image_list:
        count += 1
        text_ = image_.split('.')[0]
        image_p = os.path.join(valid_path, image_)
        # 单张图片预测
        image = np.float32(cv2.imread(image_p, 0))
        image = image.flatten() / 255
 
        text_list = sess.run(predict, feed_dict={X: [image], keep_prob: 1})
 
        text = text_list[0].tolist()
        vector = np.zeros(MAX_CAPTCHA * CHAR_SET_LEN)
        i = 0
        for n in text:
            vector[i * CHAR_SET_LEN + n] = 1
            i += 1
        predict_text= vec2text(vector)
 
        print("真实值: {0}  预测值: {1}".format(text_, predict_text),text_ == predict_text)
        if text_ == predict_text: acceptCount += 1;
    print("测试集准确率",acceptCount,count,acceptCount/count)

