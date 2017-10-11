PHP300Framework1.3.6
====
### 介绍
&emsp;&emsp;为了更好更快的开发WEB程序,PHP300FrameWork基于PHP300云类库的功能上进行开发,遵守开源免费的原则让开发者更好的扩展或使用本程序,随着PHP框架的日益增多,我们也融入了当前主流框架的一些性质并且优化的更简单更方便,框架代码简介,让您更容易了解本框架的原理用于自身学习.
1.3.6版本支持中文编码，如果需要更高的性能条件请移步[PHP300Framework2.0](https://github.com/xcg340122/PHP300Framework2.0)
### 环境支持
* 支持Windows/Linux服务器环境
* 可运行于包括Apache/Nginx/IIS等WEB服务器和模式 (Nginx中需要设置重定向规则)
* 支持Mysql数据库
### 基本内容
##### 命名空间
&emsp;&emsp;在1.3.1版本中新增命名空间，当使用扩展类需要use空间才能正常继承，控制器中需要以实例文件夹名称作为命名空间前缀，例如：Main实例中的APP控制器文件命名：Main\Action
<br />
例如Admin实例中的控制器则是：Admin\Action
