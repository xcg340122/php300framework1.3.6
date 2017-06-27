<?php

/**
* 调用控制器
* 
* @return Object
*/
function Action($Name)
{
	$Php300 = Glovar('PHP300','','OS');
	$Runcount = Glovar('Runcount','','OS');
	if($Runcount > 10){
		ShowText('PHP300::检测到调用死循环,程序已终止 -> '.$Name,true);
	}
	$Arr = explode('\\',$Name);
	switch(count($Arr)){
		case 1:
		CheckRepeatRun($Php300->ClassName,$Arr[0]);
		$Php300->ClassName = $Arr[0];
			break;
		case 2:
		CheckRepeatRun($Php300->ActionName,$Arr[0]);
		$Php300->ActionName = $Arr[0];
		$Php300->ClassName = $Arr[1];
			break;
	}
	return $Php300->CreateObj();
}

/**
* 调用类库方法
* @param 类库名称 $Name
* 
* @return Object
*/
function Libs($Name)
{
	$Php300 = Glovar('PHP300','','OS');
	$Obj = $Php300 -> $Name();
	if(is_object($Obj)){
		return $Obj;
	}
	Error('PHP::没有找到该扩展类 -> '.$Name);
}


/**
* 获取配置项
* @param 配置项键 $key
* @param 指定配置文件 $file
* 
* @return Array
*/
function Config($key='',$file='')
{
	global $Php300;
	return $Php300->ReadConfig($key,$file);
}

/**
* 接收参数
* @param 参数名称 $name
* @param 为空返回 $null
* @param 过滤方法 $function
* 
* @return Array or String
*/
function Receive($name='',$null='',$function='htmlspecialchars')
{
	if(strpos($name,'.')){
		$method = explode('.',$name);
		$name = $method[1];
		$method = $method[0];
	}else{
		$method = '';
	}
	switch(strtolower($method)){
		case 'get': $Data = & $_GET; break;
		case 'post': $Data = & $_POST; break;
		case 'put': parse_str(file_get_contents('php://input'),$Data); break;
		case 'globals': $Data = & $GLOBALS; break;
		case 'session': $Data = & $_SESSION; break;
		case 'server': $Data = & $_SERVER; break;
		default:
			 switch($_SERVER['REQUEST_METHOD']) { 	
			 	default: $Data  = & $_GET; break;
                case 'POST': $Data  = & $_POST; break; 
                case 'PUT': parse_str(file_get_contents('php://input'),$Data); break;  };break;
	}
	if(!empty($Data[$name])){
		if(is_array($Data[$name])){
			foreach ($Data as $key => $val) {
				 $Data[$key] = (function_exists($function))?($function($val)):($val);
			}
       		return $Data[$name];
		}else{
			$value = (function_exists($function))?($function($Data[$name])):($Data[$name]);
			return ($value)?($value):(($null)?($null):(''));
		}
	}else{
		return $null;
	}
}

/**
* 
* @param session名称 $name
* @param session值 $val
* 
*/
function Session($name='',$val='',$expire=3600)
{
	$Config = Config('Session','System');
	$Session = Libs('Session');
	if($Config['Session.start']){
		$Session::_init($Config);
		$Session::setExpire($expire);
		$Session::start();
		if(empty($name) and empty($val)){ return $Session; }
		if(empty($val) and !empty($name)){ $val = $Session::get($name); return $val; }
		if(!empty($name) and !empty($val)){ $Session::set($name,$val); return; }
	}
	return $Session;
}

/**
* 操作Mysql
* @param 表名 $table
* 
* @return Object
*/
function Db($table = '')
{
	$Mysql = Glovar('Mysql','','OS');
	if(is_object($Mysql)){
		if(!empty($table)){
			$Mysql->SelectDb($table);
		}
		return $Mysql;
	}
	Error('PHP300::没有连接到数据库,无法进行操作');
}

/**
* 设定全局变量
* @param 键 $key
* @param 值 $val
* @param 域 $region
* 
* @return String Or Array
*/
function Glovar($key='',$val='',$region='User')
{
	global $php300global;
	if(empty($key)){ return (is_array($php300global[$region]))?($php300global[$$region]):(array()); }
	if(empty($val)){ return (isset($php300global[$region][$key]))?($php300global[$region][$key]):(''); }
	$php300global[$region][$key] = $val;
}

/**
* 渲染并展示模板
* @param 模板路径 $Template
* 
*/
function Show($Template='index')
{
	if(!empty($Template)){
		$ViewConfig = Config('View','View');
		$View = Glovar('View','','OS');
		$Tail = (!empty($ViewConfig['Tail']))?($ViewConfig['Tail']):('.html');
		$View -> display($Template . $Tail);
	}
}

/**
* 设置模板变量
* @param 键 $key
* @param 值 $val
* 
*/
function Assign($key,$val)
{	
	if(!empty($key) or !empty($val)){
		$View = Glovar('View','','OS');
		$View -> assign($key,$val);
	}
}

/**
* 渲染并获取模板
* @param 模板路径 $Template
* 
*/
function Fetch($Template='index')
{
	if(!empty($Template)){
		$ViewConfig = Config('View','View');
		$View = Glovar('View','','OS');
		$Tail = (!empty($ViewConfig['Tail']))?($ViewConfig['Tail']):('.html');
		return $View -> fetch($Template . $Tail);
	}
}

/**
* 检查是否重复使用
*/
function CheckRepeatRun($A,$B)
{
	global $Runcount;
	if($A===$B){
		$Runcount++;
	}
}

/**
* 处理错误信息
* @param 错误代码 $errno
* @param 错误内容 $errstr
* @param 错误文件 $errfile
* @param 错误行数 $errline
* 
*/
function getError($errno,$errstr,$errfile,$errline)
{
	$Config = Config('System','System');
	switch($errno){
		case E_USER_WARNING:
			$errstr = "<b>WARNING 错误</b>$errstr";
		break;
		default: 
			$errstr =  "未定义的内容:[$errstr]"; 
		break;
	}
	if(!$Config['Debug']){
		ShowText('站点出现问题,请及时联系站长!',true);
	}
	$Data = array('errno' => $errno,'errstr' => $errstr,'errfile' => $errfile,'errline' => $errline);
	Logs('PHP300::'.$errstr.'  文件:'.$errfile.'  行数:'.$errline,'Error');
	ShowError($Data);
}
set_error_handler('getError');


/**
* 展示状态页
* @param 配置参数 $Data
* 
*/
function ShowPage($Data,$isLog=true,$Page='')
{
	if (is_array($Data)) {
		$Config = Config('System','System');
		$BackUrl = Receive('server.HTTP_REFERER');
		$Url = (!empty($BackUrl))?($BackUrl):('#');
		$Seconds = (isset($Data['Seconds']) && is_numeric($Data['Seconds']))?($Data['Seconds']):(3);
		$Info = array(
			'Url' => (!empty($Data['Url']))?($Data['Url']):($Url),
			'Seconds' => $Seconds * 1000,
			'Status' => ($Data['Status']=='1')?('( ^_^ )'):('(*>﹏<*)'),
			'Msg' => (!empty($Data['Msg']))?($Data['Msg']):('无'),
		);
		$Page = (!empty($Page))?($Page):($Config['Status.Template']);
		if($isLog){ Logs($Data['Msg']); }
        Assign('Data',$Info);
        Show($Page);
        exit();
    }
}

/**
* 展示错误页
* @param 数据 $Data
* 
*/
function ShowError($Data)
{
	$View = Glovar('View','','OS');
	$View -> left_delimiter = '<{';
	$View -> right_delimiter = '}>';
	$Config = Config('System','System');
	Assign('Data',$Data);
	Show($Config['Error.Template']);
    exit();
}

/**
* 状态页扩展展示方法(成功页)
* @param 提示内容 $Msg
* @param 跳转地址 $Url
* @param 跳转秒数 $Seconds
* 
*/
function Success($Msg,$Url='',$Seconds=3)
{
	$Data = array('Msg'=>$Msg,'Status' => '1','Seconds' => $Seconds,'Url' => $Url);
	ShowPage($Data);
}

/**
* 状态页扩展展示方法(成功页)
* @param 提示内容 $Msg
* @param 跳转地址 $Url
* @param 跳转秒数 $Seconds
* 
*/
function Error($Msg,$Url='',$Seconds=3)
{
	$Data = array('Msg'=>$Msg,'Status' => '0','Seconds' => $Seconds,'Url' => $Url);
	ShowPage($Data);
}

/**
* 记录系统日志
* @param 日志内容 $Msg
* 
*/
function Logs($Msg,$File='Logs')
{
	if(!empty($Msg)){
		$Enter = getEnter();
		error_log($Msg .$Enter.'记录时间：'.date('Y-m-d H:i:s').$Enter.$Enter ,3,'Logs/'.$File.'.log');
	}
}

/**
* 显示一段文本
*/
function ShowText($Text,$isOver = false,$Char='UTF-8')
{
	echo('<meta charset="'.$Char.'">'.$Text);
	if($isOver){
		exit();
	}
}

/**
* 获取不同系统换行符
* 
*/
function getEnter(){
	return (strtolower(substr(PHP_OS, 0, 3))=='win')?("\r\n"):("\n");
}