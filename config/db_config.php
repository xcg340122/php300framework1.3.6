<?php
/*
PHP300数据库配置文件
*/
$DB['hostname'] = '127.0.0.1';	//数据库主机地址

$DB['username'] = 'root';	//数据库用户名

$DB['password']	= 'root';	//数据库密码

$DB['port']	= 3306;	//数据库端口

$DB['database'] = 'test';	//数据库名称

$DB['charset'] = 'utf8';	//数据库编码

$DB['debug'] = true;	//SQL调试,开启后将会提示SQL错误

$DB['autoconnect'] = false;	//自动连接,设置好数据库参数即可设置为true

$DB['pconnect'] = false;	//开启长连接

$DB['sqldebug'] = false;	//开启后将自动打印SQL语句

$DB['mysqli'] = true;	//是否使用mysqli连接

$GLOBALS['DB'] = $DB;