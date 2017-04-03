<?php
/*
php300系统配置文件
UPDATE_WAY => 同步方式;
0 = 关闭云同步
1 = 初次运行时同步,同步成功后将不会在同步,同步成功后所在的根目录会产生一个LOCK文件,删除后则程序将回到未同步状态(推荐);
2 = 每次运行都会同步;
2016年4月28日
*/

/**
* 
* 同步方式
* 
*/
$CON['UPDATE_WAY'] = '0';

/**
* 
* 用户SN(如需使用云函数请登录yun.php300.cn查看账号SN)
* 
*/
$CON['SN'] = '';

/**
* 
* 调用返回时间戳
* 
*/
$CON['TIME'] = time();

/**
* 
* 默认时区
* 
*/
$COM['TIME_ZONE'] = 'Asia/Shanghai';

/**
* 
* 是否开启调试信息
* 
*/
$CON['DEBUG'] = true;

/**
* 
* 开启代码压缩(推荐为true,如开启后出错请设置为false)
* 
*/
$CON['CONFUSION'] = true;

/**
* 
* 开启日志记录
* 
*/
$CON['LOGS'] = true;

/**
* 
* 上传文件路径(请自行修改)
* 
*/
$CON['UPLOAD'] = 'Uploads/';

/**
* 
* 是否开启中文控制器编译
* 
*/
$CON['CHINESE_COMPILE'] = false;

/**
* 
* 默认控制器参数名称
* 
*/
$CON['CLASS_NAME'] = 'c';

/**
* 
* 默认方法参数名称
* 
*/
$CON['FUNCTION_NAME'] = 'f';

/**
* 
* 系统类列表
* 
*/
$CON['CLASSLIST'] = array('Cookies','Http','Mysqli','System','Image','File','Cache','Session','Socket');

/**
* 
* 系统方法列表
* 
*/
$CON['FUNCTIONLIST'] = array('System','Extension');

/**
* 
* 是否默认开启session(如程序不需要操作session可设置为false)
* 
*/
$CON['SESSION_START'] = true;

$GLOBALS['PHP300_CON'] = $CON;
?>