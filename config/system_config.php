<?php
/*
php300系统配置文件
UPDATE_WAY => 同步方式;
0 = 关闭云同步
1 = 初次运行时同步,同步成功后将不会在同步,同步成功后所在的根目录会产生一个LOCK文件,删除后则程序将回到未同步状态(推荐);
2 = 每次运行都会同步;
2016年4月28日
*/
$CON['UPDATE_WAY'] = '0'; //同步方式

$CON['SN'] = ''; //用户SN(如需使用云函数请登录yun.php300.cn查看账号SN)

$CON['TIME'] = time(); //调用返回时间戳

$CON['PHP300_AUTO'] = true; //是否自动实例化类
	
$CON['DEBUG'] = true; 	//是否开启调试信息

$CON['CONFUSION'] = true;	//开启代码压缩(推荐为true,如开启后出错请设置为false)

$CON['LOGS'] = true;	//开启日志记录

$CON['UPLOAD'] = 'Uploads/';	//上传文件路径(请自行修改)

$CON['SYSTEMLIST'] = array('cookies','http','mysql','mysqli','system','image','file');	//系统类列表

$GLOBALS['PHP300_CON'] = $CON;	//赋值全局配置
?>