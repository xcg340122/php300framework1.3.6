<?php

/**
* 调用控制器
*
* @return Object
*/
function Action($Name)
{
	$Php300   = Glovar('PHP300','','OS');
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
	$Obj    = $Php300 -> $Name();
	if(is_object($Obj)){
		return $Obj;
	}
	header("status:400 Bad Request");
	Error('没有找到该扩展类 -> '.$Name);
}

/**
* 获取配置项
* @param 配置项键 $key
* @param 指定配置文件 $file
*
* @return Array
*/
function Config($key = '',$file = '')
{
	$Php300 = Glovar('PHP300','','OS');
	return $Php300->ReadConfig($key,$file);
}

/**
* 接收参数
* @param 参数名称 $name
* @param 为空返回 $null
* @param 是否进行转码 $isEncode
* @param 过滤方法 $function
*
* @return Array or String
*/
function Receive($name = '',$null = '',$isEncode = true,$function = 'htmlspecialchars')
{
	if(strpos($name,'.')){
		$method = explode('.',$name);
		$name   = $method[1];
		$method = $method[0];
	}
	else
	{
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
		switch($_SERVER['REQUEST_METHOD']){
			default: $Data = & $_GET; break;
			case 'POST': $Data = & $_POST; break;
			case 'PUT': parse_str(file_get_contents('php://input'),$Data); break;
		};break;
	}
	if(!empty($Data[$name])){
		if(is_array($Data[$name])){
			foreach($Data[$name] as $key => $val){
				$Data[$key] = ($isEncode)?((function_exists($function))?($function($val)):($val)):($val);
			}
			return $Data[$name];
		}
		else
		{
			$value = ($isEncode)?((function_exists($function))?($function($Data[$name])):($Data[$name])):($Data[$name]);
			return ($value)?($value):(($null)?($null):(''));
		}
	}
	else
	{
		if(is_array($Data)){

		}
		return $null;
	}
}

/**
* Session操作
* @param session名称 $name
* @param session值 $val
* @param session过期时间 $expire
*
*/
function Session($name = '',$val = '',$expire = '0')
{
	$Session = Libs('Session');
	$Session -> Second = $expire;
	if($name === ''){
		return $Session->get();
	}
	if($name != '' && $val === ''){
		return $Session->get($name);
	}
	if($name && $val){
		return $Session->set($name,$val);
	}
	if($name && is_null($val)){
		return $Session->del($name);
	}
	if(is_null($name) && is_null($val)){
		$Session -> del();
	}
}

/**
* Cookie操作
* @param cookie名称 $name
* @param cookie值 $value
* @param cookie参数 $option
*
*/
function Cookie($name = '', $val = '', $expire = '0')
{
	$prefix = 'PHP300_';
	if($name === ''){
		return $_COOKIE;
	}
	if($name != '' && $val === ''){
		return (!empty($_COOKIE[$prefix.$name]))?($_COOKIE[$prefix.$name]):(NULL);
	}
	if($name && $val){
		return setcookie($prefix.$name,$val,$expire);
	}
	if($name && is_null($val)){
		return setcookie($prefix.$name,$val,time() - 1);
	}
	if(is_null($name) && is_null($val)){
		$_COOKIE = NULL;
	}
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
* Cache操作
* @param cache名称 $name
* @param cache值 $val
* @param 过期时间 $expire
* 
* @return
*/
function Cache($name = '', $val = '', $expire = true)
{
	$Cache = Libs('Cache');
	if($name === ''){
		return $Cache;
	}
	if($name != '' && $val === ''){
		$value = $Cache::get($name);
		return (!empty($value))?($value):(NULL);
	}
	if($name && $val){
		return $Cache::set($name, $val ,$expire);
	}
	if($name && is_null($val)){
		return $Cache::delete($name);
	}
	if(is_null($name) && is_null($val)){
		$Cache::delete();
	}
}

/**
* 生成URL
* @param 地址 $name
* @param 参数 $parm
*
* @return String
*/
function Url($name,$parm = '')
{
	if(!empty($name)){
		$SSL      = (isSSL())?('https://'):('http://');
		$Port     = Receive('server.SERVER_PORT');$Port     = ($Port != '80')?($Port):('');
		$ExecFile = explode('.php',Receive('server.PHP_SELF'));
		$Path     = (count($ExecFile) > 0)?($ExecFile[0].'.php'):('');
		$Url      = $SSL.Receive('server.HTTP_HOST').$Port.$Path;
		if(strpos($name,'/')){
			$PathArr = explode('/',$name);
			foreach($PathArr as $val){
				if(!empty($val)){
					$Url .= '/' . $val;
				}
			}
			if(!empty($parm))
			{
				$Url .= '?'.$parm;
			}
		}
		return $Url;
	}
}

/**
* 判断是否为SSL连接
* 本方法来自于网友提供
*
* @return Bool
*/
function isSSL()
{
	$HTTPS = Receive('server.HTTPS');
	$PORT = Receive('server.SERVER_PORT');
	if(isset($HTTPS) && ('1' == $HTTPS || 'on' == strtolower($HTTPS)))
	{
		return true;
	}
	elseif(isset($PORT) && ('443' == $PORT ))
	{
		return true;
	}
	return false;
}

/**
* 设定全局变量
* @param 键 $key
* @param 值 $val
* @param 域 $region
*
* @return String Or Array
*/
function Glovar($key = '',$val = '',$region = 'User')
{
	global $php300global;
	if(empty($key)){
		return (is_array($php300global[$region]))?($php300global[$$region]):(array());
	}
	if(empty($val)){
		return (isset($php300global[$region][$key]))?($php300global[$region][$key]):('');
	}
	$php300global[$region][$key] = $val;
}

/**
* 渲染并展示模板
* @param 模板路径 $Template
*
*/
function Show($Template = 'index')
{
	if(!empty($Template)){
		$ViewConfig = Config('View','View');
		$View       = Glovar('View','','OS');
		$Tail       = (!empty($ViewConfig['Tail']))?($ViewConfig['Tail']):('.html');
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
function Fetch($Template = 'index')
{
	if(!empty($Template)){
		$ViewConfig = Config('View','View');
		$View       = Glovar('View','','OS');
		$Tail       = (!empty($ViewConfig['Tail']))?($ViewConfig['Tail']):('.html');
		return $View -> fetch($Template . $Tail);
	}
}

/**
* 展示状态页
* @param 配置参数 $Data
* @param 是否记录日志 $isLog
* @param 渲染模板页 $Page
*
*/
function ShowPage($Data,$isLog = true,$Page = '')
{
	if(is_array($Data)){
		$Config = Config('System','System');
		$BackUrl= Receive('server.HTTP_REFERER');
		$Url    = (!empty($BackUrl))?($BackUrl):('#');
		$Seconds = (isset($Data['Seconds']) && is_numeric($Data['Seconds']))?($Data['Seconds']):(3);
		$Info = array(
			'Url'    => (!empty($Data['Url']))?($Data['Url']):($Url),
			'Seconds'=> $Seconds * 1000,
			'Status' => ($Data['Status'] == '1')?('( ^_^ )'):('(*>﹏<*)'),
			'Msg'    => (!empty($Data['Msg']))?($Data['Msg']):('无'),
		);
		$Page = (!empty($Page))?($Page):($Config['Status.Template']);
		$View = Glovar('View','','OS');
		$View -> left_delimiter = '<{';
		$View -> right_delimiter = '}>';Glovar('View',$View,'OS');
		if($isLog){
			Logs($Data['Msg']);
		}
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
	Glovar('View',$View,'OS');
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
function Success($Msg,$Url = '',$Seconds = 3)
{
	$Data = array('Msg'    =>$Msg,'Status' =>'1','Seconds'=>$Seconds,'Url'    =>$Url);
	ShowPage($Data);
}

/**
* 状态页扩展展示方法(成功页)
* @param 提示内容 $Msg
* @param 跳转地址 $Url
* @param 跳转秒数 $Seconds
*
*/
function Error($Msg,$Url = '',$Seconds = 3)
{
	$Data = array('Msg'    =>$Msg,'Status' =>'0','Seconds'=>$Seconds,'Url'    =>$Url);
	ShowPage($Data);
}


/**
* 云函数调用
* @param 更新周期 $Update
*
* @return Object
*/
function Yun($Update=FALSE)
{
	$fileName = './Libs/Class/Yun_class.php';
	if(file_exists($fileName)){
		$YunObj = Libs('Yun');
		if(is_numeric($Update)){
			$Time = time();
			$DendTime = ($Time - $YunObj->UpdateTime);
			if($DendTime > $Update){
				if(@unlink($fileName)){
					$YunObj = Yun();
				}else{
					ShowText('Yun函数处理文件异常,请检查增删权限!',TRUE);	
				}
			}
		}
		return $YunObj;
	}else{
		//获取云函数
		$Config = Config('YUN','System');
		if($Config['Type'] == '1'){
			if($Config['SN'] == ''){
				ShowText('请先配置系统文件中的SN!',TRUE);
			}
			$objectUrl = 'http://yun.php300.cn/index.php/Function/GetFunction/SN/'.$Config['SN'].'/T/'.time();
			$cacheClass = @file_get_contents($objectUrl);
			if(!empty($cacheClass)){
				$cacheClass = json_decode($cacheClass,true); $Code='';
				foreach($cacheClass['data'] as $val){
					$Code .= base64_decode($val['function_content']);
				}
				$UpdateTime = 'public $UpdateTme = \''.time().'\';';
				$cacheCode = "<?php namespace Libs\Deal; class Yun{ ".$UpdateTime.' '.$Code." } ?>";
				file_put_contents($fileName,StripWhitespace($cacheCode));
				include_once($fileName);
				return Libs('Yun');
			}
		}
	}
}

/**
* 记录系统日志
* @param 日志内容 $Msg
*
*/
function Logs($Msg,$File = 'Logs')
{
	if(!empty($Msg)){
		$Enter = getEnter();
		error_log($Msg .$Enter.'记录时间：'.date('Y-m-d H:i:s').$Enter.$Enter ,3,'Logs/'.$File.'.log');
	}
}

/**
* 显示一段文本
* @param 显示的文本 $Text
* @param 是否结束脚本 $isOver
* @param 输出的编码类型 $Char
*
*/
function ShowText($Text,$isOver = false,$Char = 'UTF-8')
{
	echo('<meta charset="'.$Char.'">'.$Text);
	if($isOver){
		exit();
	}
}

/**
* 检查是否重复使用
*/
function CheckRepeatRun($A,$B)
{
	global $Runcount;
	if($A === $B){
		$Runcount++;
	}
}

/**
* 获取不同系统换行符
*
*/
function getEnter()
{
	return (strtolower(substr(PHP_OS, 0, 3)) == 'win')?("\r\n"):("\n");
}

/**
* 压缩代码
* @param 代码内容 $content
*
* @return String
*/
function StripWhitespace($content)
{
	$stripStr   = '';
	$CodeArr    = token_get_all($content);
	$last_space = false;
	for($i = 0, $j = count($CodeArr); $i < $j; $i++){
		if(is_string($CodeArr[$i])){
			$last_space = false;
			$stripStr .= $CodeArr[$i];
		}
		else
		{
			switch($CodeArr[$i][0]){
				//过滤各种PHP注释
				case T_COMMENT:
				case T_DOC_COMMENT:
				break;
				//过滤空格
				case T_WHITESPACE:
				if(!$last_space){
					$stripStr .= ' ';
					$last_space = true;
				}
				break;
				default:
				$last_space = false;
				$stripStr .= $CodeArr[$i][1];
			}
		}
	}
	return $stripStr;
}

/**
* 获取客户端IP地址
*
*/
function getClientip()
{
	if(getenv("HTTP_CLIENT_IP")){
		$ip = getenv("HTTP_CLIENT_IP");
	}
	else
	if(getenv("HTTP_X_FORWARDED_FOR")){
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	}
	else
	if(getenv("REMOTE_ADDR")){
		$ip = getenv("REMOTE_ADDR");
	}
	else
	{
		$ip = "Unknow";
	}
	return $ip;
}