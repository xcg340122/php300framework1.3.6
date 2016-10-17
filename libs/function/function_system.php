<?php
/**
* M(模型名称)
* 存在返回模型对象,否则返回false
*/

function M($modelname){	//返回MODEL
	if($modelname==''){
		return false;
	}
	if(class_exists($modelname.'_class')){
		return $GLOBALS[$modelname];
	}else{
		return false;
	}
}

/**
* PHP300()
* 返回PHP300云对象
*/
function php300(){
	if(is_object(M('php300'))){
        $cache_obj = M('php300');
        return $cache_obj;
    }else{
		return false;
	}
}

/**
* C(配置名称)
* 存在返回配置值,否则返回false
*/

function C($configname,$configfile=''){	//返回配置信息
	$arr = array('PHP300_CON','DB','U','TEMP');
	if($configfile!=''){
		array_unshift($arr,$configfile);
	}
	foreach($arr  as $val){
		if($GLOBALS[$val][$configname]!=''){
			return $GLOBALS[$val][$configname];
		}
	}
	if(is_array($GLOBALS[$configname])){
		return $GLOBALS[$configname];
	}
	return false;
}

/**
* I(参数名称)
* 返回指定参数名称GET或POST的值,如果不指定type则默认优先获取GET
*/

function I($name,$isnull='',$type='GET',$filter=htmlspecialchars){
    if($name!=''){
        $value = $type=='GET' ? $_GET[$name] : $_POST[$name];
        $value = $value!='' ? $value : $isnull;
        if(is_array($value)){
            foreach($value as $key=>$val){
                $value[$key] = $filter!='' ? $filter($val) : $val;
            }
        }
        return $value;
    }else{
        return $isnull;
    }
}

/**
* DB()
* 返回MYSQL类
*/

function DB($db=''){
    if($GLOBALS['DB']['autoconnect']==FALSE){
		exit('php300:您尚未启用DB配置项内的自动连接,无法操作数据库');
	}
    if(is_object(M('mysqli'))){
        $cache_obj = M('mysqli');
        $cache_obj->set_db($db);
        return $cache_obj;
    }else{
        loads('mysqli');
        $classname = 'mysqli_class';
        $classname = new $classname($db);
        return $classname;
    }
    return false;
}

/**
* exec_url(模型,方法)
* 执行处理URL参数,f参数默认等于index
*/

function exec_url($c,$f=''){
	$c = htmlspecialchars($c,ENT_QUOTES);
	$f = htmlspecialchars($f,ENT_QUOTES);
	$f = ($f)?($f):('index');
	$us = array('c'=>$c,'f'=>$f);
	use_controller($us);
}

/**
* use_controller(配置数组)
* 调用控制器
*/
function use_controller($option=array()){
	if($option['c']!=''){
		if(!isset($GLOBALS[$option['c']])){
			$GLOBALS['system']->get_error_page('找不到'.$option['c'].'控制器');
		}
		if(method_exists($GLOBALS[$option['c']],$option['f'])){
			$function = $option['f'];
			$vals = array('C_NAME'=>$option['c'],'F_NAME'=>$function);
			set_define($vals);
			$GLOBALS[$option['c']] -> $function();
			exit();
		}else{
			$GLOBALS['system']->get_error_page('在'.$option['c'].'控制器中找不到'.$option['f'].'方法');
		}
	}
}

/**
* url_routing(URL全路径,配置信息)
* 执行静态路由
*/
function url_routing($info,$U){
	$info = str_replace($U['URL_TAIL'],'',$info);
	$url_arr = explode($U['URL_MIDDLE'],$info);
	if($U['URL_MIDDLE']!='/'){	//处理分隔符
		array_unshift($url_arr,$U['URL_MIDDLE']);
		$url_arr[1] = str_replace('/','',$url_arr[1]);
	}
	$count = count($url_arr);
	if($count > 1){
		$url_arr[2] = $url_arr[2] ? $url_arr[2] : 'index';
		$urls .= 'c='.$url_arr[1].'&f='.$url_arr[2];
		$n = 0;
		for($i=3;$i<=$count;$i++){
			if($i != $n){
				$n = $i + 1;
				if($url_arr[$i]!=''){
					$n = $i + 1;
					$urls .= '&'.$url_arr[$i].'='.$url_arr[$n];
				}
			}	
		}
		$urls = $urls;
		parse_str($urls,$out);
		$us = array('c'=>$out['c'],'f'=>$out['f']);
		foreach($out as $key=>$val){
			if($key!='c' and $key!='f'){
				$_GET[$key] = $val;
			}
		}
		use_controller($us);
	}
}

/**
* firstload(类名称)
* 预加载继承类
*/
function firstload($classname){
	if($classname!=''){
		$classname = str_replace('_class','',$classname);
		loads($classname);
	}
}

/**
* loads(类名称)
* 承接加载
*/
function loads($classname){
	if($classname!='' and $classname!='php300'){
		if(in_array($classname,$GLOBALS['PHP300_CON'] ['CLASSLIST'])){
			$classname = FILE_PATH.'libs/class/'.$classname.CLASS_NAME;
		}else{
			$classname = 'model/'.$classname.CLASS_NAME;
		}
		if(file_exists($classname)){
			include_once($classname);
		}
	}
}

/**
* update_class(配置信息)
* 更新云类库
*/
function update_class($CON){
	if(!file_exists(FILE_PATH.'cache')){
		mkdir(FILE_PATH.'cache');
		}
	$cachefile = FILE_PATH.'cache/cache_class.php';
	$file_link = @fopen($cachefile,'w');
	$object_url = 'http://yun.php300.cn/?c=get&SN='.$CON['SN'].'&T='.$CON['TIME'];
	$cacheclass = @file_get_contents($object_url);
	if($cacheclass==''){
		echo ($CON['DEBUG'])?('当前SN无云程序或获取云程序失败'):('');
		}
	$results = json_decode($cacheclass,true);
	if($results!='0'){
		$code = '';
		if(is_array($results['data'])){
			foreach($results['data'] as $val){
				$code .= urldecode($val['function_content']);
			}
		}
		$cacheclass = "<?php class php300_class{ ".$code." } ?>";
		fwrite($file_link,$cacheclass);
		fclose($file_link);
		if($CON['CONFUSION']){
			$confusion =  @php_strip_whitespace($cachefile);
			@file_put_contents($cachefile,$confusion);
		}
	}else{
		fclose($file_link);
		echo ($CON['DEBUG'])?($results['msg']):('');
		}
	}

/**
* 系统基本常量
* system_define_info();
*/
function system_define_info(){
	$vals = array(
		'__APP__'=>dirname($_SERVER['PHP_SELF']).'/',
		'__HOST__'=>$_SERVER['HTTP_HOST'],
		'__PORT__'=>$_SERVER["SERVER_PORT"],
		'__TMP__'=>dirname($_SERVER['PHP_SELF']).'/template/',
		'__REFERER__'=>$_SERVER['HTTP_REFERER'],
	);
	set_define($vals);
}

/**
* 常量预设
* set_define(关联数组)
*/
function set_define($vals){
	foreach($vals as $key=>$val){
		define($key,$val);
		$GLOBALS['system']->set_var($key,$val);
	}
}

/**
* 错误页
*/
function error($error='未知错误',$url='',$seconds=3){
	$info = array(
		'message'=>$error,
		'url'=>$url,
		'seconds'=>$seconds,
		'state'=>'0',
	);
	show_state_information($info);
}

/**
* 成功页
*/
function success($success='操作成功',$url='',$seconds=3){
	$info = array(
		'message'=>$success,
		'url'=>$url,
		'seconds'=>$seconds,
		'state'=>'1',
	);
	show_state_information($info);
}

/**
* 展示状态页
* show_state_information(关联数组);
*/
function show_state_information($info){
	if(is_array($info)){
		M('system')->set_var('message',$info['message']);
		$info['url'] = $info['url']!='' ? $info['url'] : __REFERER__;
		$info['url'] = $info['url']!='' ? $info['url'] : '#';
		M('system')->set_var('url',$info['url']);
		M('system')->set_var('seconds',$info['seconds']*1000);
		$state = $info['state']=='1' ? '( ^_^ )' : '(*>﹏<*)';
		M('system')->set_var('state',$state);
		$GLOBALS['TMP']->left_delimiter = '<{';
		$GLOBALS['TMP']->right_delimiter = '}>';
		M('system')->display('php300_tmp/state');
	}
}

/**
* 缓存类别名
*/
function cache(){
	if(is_object(M('cache'))){
		return M('cache');
	}
	return FALSE;
}

/**
* 文件类别名
*/
function files(){
	if(is_object(M('file'))){
		return M('file');
	}
	return FALSE;
}

/**
* cookies类别名
*/
function cookies(){
	if(is_object(M('cookies'))){
		return M('cookies');
	}
	return FALSE;
}

/**
* http类别名
*/
function http(){
	if(is_object(M('http'))){
		return M('http');
	}
	return FALSE;
}

/**
* session类别名
*/

function session(){
	if(is_object(M('session'))){
		return M('session');
	}
	return FALSE;
}