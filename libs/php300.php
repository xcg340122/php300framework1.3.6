<?php
/**
* PHP300V1.9云类库版
*PHP300Frameworkv1.2.0
*官方网站：http://framework.php300.cn
*在线论坛：http://bbs.php300.cn
*登录地址：http://yun.php300.cn 在线提交您的函数
*交流QQ群：231201376
*/
define('FRAMEWROK_VER','1.2.0');	//PHP300框架版本

define('SYSTEM_UPDATATIME','2016/10/16');	//最后更新时间

define('CLASS_NAME','_class.php');

define('FILE_PATH',str_replace(DIRECTORY_SEPARATOR.'libs','',dirname(__FILE__).DIRECTORY_SEPARATOR));
//系统根目录

define('FUNCTION_PATH',FILE_PATH.'libs/function/');	//方法目录

define('CLASS_PATH',FILE_PATH."model/");	//用户类目录

define('PLUG',FILE_PATH.'libs/plug/');	//插件目录

$modellist = scandir(CLASS_PATH);

$classlist = scandir(FILE_PATH.'libs/class/');

$conlist = scandir(FILE_PATH.'config');

$functionlist = scandir(FUNCTION_PATH);

error_reporting(E_ALL ^ E_NOTICE);	//屏蔽NOTICE级别错误

date_default_timezone_set('Asia/Shanghai');	//设置默认时区

foreach($conlist as $val){	//加载配置文件
	if($val!="." and $val!=".."){
		$trim = explode('.',$val);
		if($trim[1]=='php'){
			include_once(FILE_PATH.'config/'.$val);
		}
	}
}

foreach($functionlist as $val){	//加载方法文件
	if($val!="." and $val!=".."){
		$trim = explode('.',$val);
		if($trim[1]=='php'){
			include_once(FUNCTION_PATH.$val);
		}
	}
}

spl_autoload_register("firstload");	//预注册类

if(!$CON['DEBUG']){	//屏蔽错误
	error_reporting(0);
	ini_set("display_errors", "Off");
}

switch($CON['UPDATE_WAY']){	//同步更新云函数
	case '1':
	if(!file_exists(FILE_PATH.'LOCK')){
		update_class($CON);
		$file_link = fopen(FILE_PATH.'LOCK','w');
		fwrite($file_link,time());
		fclose($file_link);
	}
		break;
	case '2':
		update_class($CON);
		break;
}

if(file_exists(FILE_PATH.'cache/cache_class.php')){
	include_once(FILE_PATH.'cache/cache_class.php');	//引入云函数缓存
}

if(class_exists('php300_class')){
	$GLOBALS['php300'] = new php300_class();
}else{
	class php300_class{ };
	$GLOBALS['php300'] = new php300_class();
}

include_once(PLUG.'Smarty/Smarty.class.php');	//模板系统

$TMP = new Smarty;

$TMP->caching = $TEMP['TMP_CACHE'];

$TMP->cache_lifetime = $TEMP['TMP_CACHE_TIME'];

$TMP->left_delimiter = $TEMP['TMP_LEFT'];

$TMP->right_delimiter = $TEMP['TMP_RIGHT'];

$GLOBALS['TMP'] = $TMP;

$GLOBALS['autoloads'] = array();

$modelnum = count($modellist);

foreach($classlist as $classname){	//装载系统内置类库
	if($classname!="." and $classname!=".."){
		$classname = FILE_PATH.'libs/class/'.$classname;
		if(is_file($classname)){
			$replace_arr = array(FILE_PATH.'libs/class/',CLASS_NAME,'class/');
			$class = str_replace($replace_arr,'',$classname);
			array_push($GLOBALS['autoloads'],$class);
		}
	}
}


if($modelnum > 0){
	foreach($modellist as $modelname){	//用户自定义类库
		if($modelname!="." and $modelname!=".."){
			$modelname = CLASS_PATH.$modelname;
			if(is_file($modelname)){
				$replace_arr = array(CLASS_PATH,CLASS_NAME);
				$model = str_replace($replace_arr,'',$modelname);
				array_push($GLOBALS['autoloads'],$model);
			}
		}	
	}
}

foreach($GLOBALS['autoloads'] as $val){	//自动实例化
	if($val!=''){
		$classname = $val.'_class';
		$GLOBALS[$val] = new $classname();
		$G[$val] = $GLOBALS[$val];
	}
}

set_error_handler(array($GLOBALS['system'],'php300_error_handler'));	//处理错误

system_define_info();	//预设基本常量

if($U['URL_WAY'] == '1'){
	url_routing($_SERVER['QUERY_STRING'],$U);	//URL路由
}

exec_url($_GET['c'],$_GET['f']);	//执行URL指针