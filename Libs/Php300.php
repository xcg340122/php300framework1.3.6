<?php

/* PHP300: 框架中央处理文件: */
// +----------------------------------------------------------------------+
// | PHP version > 5.4                                                    |
// +----------------------------------------------------------------------+
// | Copyright (c) 2016-2017 Group by PHP300                              |
// +----------------------------------------------------------------------+
// | 本处理文件需要至少5.5版本运行,文件开源,但是请尊重权限,切勿伪造传播.  |
// | PHP300V1.9云类库支持 												  |
// | PHP300Framework v1.3.0											      |
// | 官方网站：http://framework.php300.cn                                 |
// | 在线论坛：http://bbs.php300.cn										  |
// | PHP300极推送：http://push.php300.cn								  |
// | 登录地址：http://yun.php300.cn 在线提交您的函数			          |
// | 文档地址：http://api.php300.cn						                  |
// | 交流QQ群：231201376						                 		  |
// +----------------------------------------------------------------------+

/**
* 
* PHP300框架版本
* 
*/
define('FRAMEWROK_VER', '1.3.0');

/**
* 
* 最后更新时间
* 
*/
define('SYSTEM_UPDATATIME', '2017/04/01');

/**
* 
* 类文件后缀
* 
*/
define('CLASS_NAME', '_class.php');

/**
* 
* 库文件根路径
* 
*/
define('FILE_PATH', str_replace(DIRECTORY_SEPARATOR . 'Libs', '', __DIR__.DIRECTORY_SEPARATOR));

/**
* 
* 方法目录
* 
*/
define('FUNCTION_PATH', FILE_PATH . 'Libs/Function/');

/**
* 
* 用户类目录
* 
*/
define('CLASS_PATH', FILE_PATH . "Model/");

/**
* 
* 插件目录
* 
*/
define('PLUG', FILE_PATH . 'Libs/Plug/');

/**
* 
* 获取目录列表
* 
*/
$ModelList = scandir(CLASS_PATH);

$ClassList = scandir(FILE_PATH . 'Libs/Class/');

$ConList = scandir(FILE_PATH . 'Config');

$FunctionList = scandir(FUNCTION_PATH);

/**
* 
* 屏蔽NOTICE级别错误
* 
*/
error_reporting(E_ALL ^ E_NOTICE);

/**
* 
* 加载配置文件
* 
*/
foreach ($ConList as $val) {
	
    if ($val != "." and $val != "..") {
    	
        if (explode('.', $val)[1] == 'php') {
        	
            include_once (FILE_PATH . 'Config/' . $val);
        }
    }
}

/**
* 
* 判断php.ini是否具有默认时区配置
* 
*/
if(ini_get('date.timezone')==''){
	ini_set('date.timezone','PRC');
}

/**
* 
* 设置默认时区
* 
*/
date_default_timezone_set($CON['TIME_ZONE']);

/**
* 
* 加载方法文件
* 
*/
foreach ($FunctionList as $val) {
    if ($val != "." and $val != "..") {
    	
        $trim = explode('.', $val);
        
        if ($trim[1] == 'php') {
        	
            include_once (FUNCTION_PATH . $val);
        }
    }
}

/**
* 
* cli模式禁用项
* 
*/
if (PHP_SAPI == 'cli') {
    $CliUnset = array(
        'cookies'
    );
    foreach ($ClassList as $key => $val) {
    	
        if (in_array(str_replace(CLASS_NAME, '', $val), $CliUnset)) {
        	
            unset($ClassList[$key]);
            
        }
    }
    G('status', $argv[1], 'PHP300_CON');
}

/**
* 
* 预处理URL
* 
*/
if ($U['URL_WAY'] == '1') {
	
    url_routing($_SERVER['QUERY_STRING'], $U);
    
}

/**
* 
* 预设基本常量
* 
*/
SystemDefineInfo();

/**
* 
* 预注册类
* 
*/
spl_autoload_register("firstload");

/**
* 
* 屏蔽错误
* 
*/
if (!$CON['DEBUG']) {
	
    error_reporting(0);
    
    ini_set("display_errors", "Off");
}

/**
* 
* 同步更新云函数
* 
*/
switch ($CON['UPDATE_WAY']) {
    case '1':
        if (!file_exists(FILE_PATH . 'LOCK')) {
            update_class($CON);
            $file_link = fopen(FILE_PATH . 'LOCK', 'w');
            fwrite($file_link, time());
            fclose($file_link);
        }
        break;

    case '2':
        update_class($CON);
        break;
}

/**
* 
* 引入云函数缓存 
* 
*/
if (file_exists(FILE_PATH . 'Cache/cache_class.php')) {
	
    include_once (FILE_PATH . 'Cache/cache_class.php');
}
if (class_exists('php300_class')) {
	
    $php300 = new php300_class();
    
    G('php300', $php300);
} else {

    class php300_class {
        
    }

    $php300 = new php300_class();
    
    G('php300', $php300);
}

/**
* 
* 模板系统
* 
*/
include_once (PLUG . 'Smarty/Smarty.class.php');

$TMP = new Smarty;

$TMP->caching = $TEMP['TMP_CACHE'];

$TMP->cache_lifetime = $TEMP['TMP_CACHE_TIME'];

$TMP->left_delimiter = $TEMP['TMP_LEFT'];

$TMP->right_delimiter = $TEMP['TMP_RIGHT'];

$function_list = C('TMP_FUNCTION');

/**
* 
* 注册函数
* 
*/
if (is_array($function_list)) {
	
    foreach ($function_list as $val) {
    	
        if (file_exists($val)) {
        	
            $TMP->registerPlugin('function', $val, $val);
            
        }
    }
}

/**
* 
* 缓存变量
* 
*/

G('TMP', $TMP);

G('autoloads', array());

$modelnum = count($ModelList);

/**
* 
* 装载系统内置类库
* 
*/
foreach ($ClassList as $classname) {
	
    if ($classname != "." and $classname != "..") {
    	
		if (explode('.', $classname)[1] != 'php') { continue; }
		
        $classname = FILE_PATH . 'Libs/Class/' . $classname;
        
        if (is_file($classname)) {
        	
            $replace_arr = array(
                FILE_PATH . 'Libs/Class/',
                CLASS_NAME,
                'Class/'
            );
            
            $class = str_replace($replace_arr, '', $classname);
            
            array_push($GLOBALS['autoloads'], $class);
            
        }
    }
}

/**
* 
* 用户控制器
* 
*/
if ($modelnum > 0) {
    foreach ($ModelList as $modelname) {
        if ($modelname != "." and $modelname != "..") {
			if (explode('.', $modelname)[1] != 'php') { continue; }
            $modelnames = $modelname = CLASS_PATH . $modelname;
            $modelval = @file_get_contents($modelname);
            if (is_file($modelname)) {
                $replace_arr = array(CLASS_PATH,CLASS_NAME);
                $model = str_replace($replace_arr, '', $modelname);
                if ($CON['CHINESE_COMPILE']) {
					$modelname = FILE_PATH . 'Cache/' . md5($model) . '.php';
					if(file_exists($modelname)){
						$cachetime = middle_string(file_get_contents($modelname),'//TIME_START_','_TIME_END//');	//获取上次修改时间
						if($cachetime!=''){
							$filemtime = filemtime($modelnames) - $cachetime;	//检测是否更新
							if($filemtime == 0){
								array_push(C('autoloads'), $model);
								continue;
							}
						}
					}
                    $keys = @return_key();
                    if (is_array($keys)) {
                        $fileType = @mb_detect_encoding($modelval, array('UTF-8','GBK','LATIN1','BIG5'
                        ));
                        $modelval = ($fileType != 'UTF-8') ? (@mb_convert_encoding($modelval, 'UTF-8', $fileType)) : ($modelval);	//进行中文编码兼容
                        foreach ($keys as $key => $val) {
                            $modelval = @preg_replace('/' . $key . '/', $val, $modelval);
                        }
                        if ($modelval != '') {
                            @file_put_contents($modelname, $modelval);
                            if (C('PHP300_CON') ['CONFUSION']) {
                                $modelval = @php_strip_whitespace($modelname);
                                @file_put_contents($modelname,'<?php //TIME_START_'.filemtime($modelnames).'_TIME_END// ?>'.$modelval);
                            }
                        }
                    }
                }
                array_push($GLOBALS['autoloads'], $model);
            }
        }
    }
    ReleaseUseless();
}

/**
* 
* 自动实例化
* 
*/
foreach (C('autoloads') as $val) {
    if ($val != '') {
    	
        $classname = $val . '_class';
        
        $GLOBALS[$val] = new $classname();
        
        $G[$val] = $GLOBALS[$val];
    }
}

/**
* 
* 处理错误
* 
*/
set_error_handler(array(
    M('System'),
    'php300_error_handler'
));

if ($U['URL_WAY'] == '1') {
	
    $U = C('C_AND_F');
    
    if (is_array($U)) {
    	
    	/**
		* 
		* URL路由
		* 
		*/
        ExecUrl($U['c'], $U['f']);
    }
}

/**
* 
* 执行URL指针
* 
*/
ExecUrl($_GET[$CON['CLASS_NAME']], $_GET[$CON['FUNCTION_NAME']]);