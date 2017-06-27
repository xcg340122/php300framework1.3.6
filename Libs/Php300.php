<?php

/**
* PHP300Framework驱动控制器
* @author:Chungui
* @copyright:PHP300
*/
namespace Libs\PHP300;

class Php300Deal {
	
	/**
	* 框架版本
	* @var String
	*/
	public $Version;
	
	/**
	* 框架路径
	* @var String
	*/
	public $CorePath;
	
	/**
	* 框架更新时间
	* @var String
	*/
	public $UpDateTime;
	
	public $ActionName = '';
	
	public $ClassName = '';
	
	public $FunctionName = '';
	
	private $ClassTail = '_class';
	
	private $ActionCount = 0;
	
	private $ClassList = array();
	
	private $FunctionList = array();
	
	private $ConfigList = array();
	
	function __construct()
	{
		if(PHP_SAPI=='cli'){
			ini_set('include_path',dirname(__FILE__));
		}
		$this->Version = '1.3.1';
		$this->UpDateTime = '2017/04/22';		
		$this->init();
	}
	
	/**
	* 初始化框架
	* 
	*/
	private function init()
	{
		$this->setCorePath();
		$this->ConfigList = $this->getDir($this->CorePath.'Config');
		$this->ActionList = $this->getDir($this->CorePath.'Action');
		$this->FunctionList = $this->getDir($this->getfullPath('Function'));
		$this->ClassList = $this->getDir($this->getfullPath('Class'));
		$this->loadConfig();
		$this->loadFunction();
		$this->loadView();
		$this->loadClass();
		$this->Actioncount();
		spl_autoload_register(array(&$this,'Autoload'));
	}
	
	/**
	* 自动加载
	* @param 类名 $Class
	* 
	*/
	public function Autoload($Class)
	{
		$Path = (strpos($Class,'Action'))?($this->CorePath . 'Action/' . str_replace('\Action','',$Class)):($this->CorePath . 'Libs/Class/' . $Class);
		$Path = $Path  . '.php';
		if(is_file($Path)){
			include_once($Path);
		}else{
			ShowText('PHP300::找不到类 -> '.$Class,true);
		}
	}
	
	/**
	* 加载公共方法文件
	* 
	*/
	function loadFunction()
	{
		if(count($this->FunctionList) > 0){
			$FunctionPath = $this->getfullPath('Function').DIRECTORY_SEPARATOR;
			foreach($this->FunctionList as $key=>$val){ if($this->getExtension($val) === 'php'){ $this->Readload($FunctionPath.$val); } }
		}
	}
	
	/**
	* 加载公共配置文件
	* 
	*/
	function loadConfig()
	{
		if(count($this->ConfigList) > 0){
			$ConfigPath = $this->CorePath . 'Config' . DIRECTORY_SEPARATOR;
			foreach($this->ConfigList as $key=>$val){ if($this->getExtension($val) === 'php'){ $this->Readload($ConfigPath.$val,substr($val,0,-4),'Config'); } } }
	}
	
	/**
	* 加载视图引擎
	* 
	*/
	function loadView()
	{
		$this->loadPlug('Smarty/Smarty.class.php');
		$View = new \Smarty;
		$this->setView($View);
	}
	
	/**
	* 加载系统扩展类
	* 
	*/
	public function loadClass()
	{
		if(count($this->ClassList) > 0){
			$ClassPath = $this->getfullPath('Class') . DIRECTORY_SEPARATOR;
			foreach($this->ClassList as $key=>$val){ if($this->getExtension($val) === 'php'){ $this->Readload($ClassPath.$val,substr($val,0,-4)); } } $this->setConstant(); $this->ConnMysql(); }
	}
	
	/**
	* 加载插件
	* @param 插件路径 $File
	* 
	*/
	public function loadPlug($File)
	{
		$PlugPath = $this->getfullPath('Plug').DIRECTORY_SEPARATOR.$File;
		if(is_file($PlugPath)){
			include_once($PlugPath);
		}
	}
	
	/**
	* 路由操作
	* 
	*/
	public function RunRoute()
	{
		$UrlConfig = $this->ReadConfig('Url','Url');
		$this->Directly($UrlConfig);
		if($UrlConfig['Switch']){
			$QueryArr = $this->Arrgd(Receive('server.QUERY_STRING'),$UrlConfig);
		}
	}
	
	/**
	* 分拣数据
	* @param 访问数组 $Arr
	* @param 配置文件 $Config
	* 
	*/
	function setAccess($Arr,$Config)
	{
		$this->ActionName = $Arr['Action'];
		$this->ClassName = $Arr['Class'];
		$this->FunctionName = $Arr['Function'];
	}
	
	/**
	* 普通get执行
	* 
	*/
	function Directly($UrlConfig)
	{
		$this->ActionName = Receive($UrlConfig['Action'],$this->ActionName);
		$this->ClassName = Receive($UrlConfig['Class'],$this->ClassName);
		$this->FunctionName = Receive($UrlConfig['Function'],$this->FunctionName);
	}
	
	/**
	* 设置框架根目录
	* 
	*/
	function setCorePath()
	{
		$Path = str_replace(DIRECTORY_SEPARATOR.'Libs','',dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$this->CorePath = $Path;
	}

	/**
	* 返回相关路径
	* @param 关联目录 $Path
	* 
	* @return String
	*/
	function getfullPath($Path)
	{	
		$Path = $this->CorePath.'Libs'.DIRECTORY_SEPARATOR.$Path;
		return $Path;
	}
	
	/**
	* 截取文件后缀
	* @param 文件名 $file
	* 
	* @return String
	*/
	function getExtension($File)
	{
		$Arr = explode('.', $File);
		return end($Arr);
	}
	
	/**
	* 获取目录文件列表
	* @param 目录路径 $Path
	* 
	* @return Array
	*/
	function getDir($Path)
	{
		if(is_dir($Path)){
			$List = scandir($Path);
			foreach($List as $key=>$val){if ($val == "." or $val == "..") {	unset($List[$key]); } }
			return $List;
		}
		return array();
	}
	
	/**
	* 引入文件
	* 
	*/
	public function Readload($Path,$name='',$Type='Class')
	{	
		if(is_file($Path)){
			if($Type==='Config'){
				global $PHP300Res;
				$PHP300Res[$name] = include_once($Path);
			}else{
				include_once($Path);	
			}
		}
	}
	
	/**
	* 读取配置
	* @param 配置名称 $key
	* @param 指定文件 $file
	* 
	* @return String or Bool
	*/
	public function ReadConfig($keys='',$file='')
	{
		global $PHP300Res;
		if($keys){
			if(is_array($PHP300Res)){
				foreach($PHP300Res as $key=>$val){ if($file){ if($file==$key){return (isset($val[$keys]))?($val[$keys]):(false);} }else{ return $val;}	}
			}
		}
		return $PHP300Res;
	}
	
	/**
	* 创建操作对象
	* 
	* @return
	*/
	public function CreateObj()
	{
		$App =  '\\'.$this->ActionName . '\Action\\' . $this->ClassName . $this->ClassTail;
		$App = new $App;
		return $App;
	}
	
	/**
	* 设置模板引擎
	* @param 引擎对象 $Obj
	*/
	function setView(&$Obj)
	{
		$ViewConfig = $this->ReadConfig('View','View');
		$Obj -> template_dir = $this->CorePath . 'Template';
		$Obj -> compile_dir = $this->CorePath . 'Cache';
		$Obj -> caching = $ViewConfig['Cache'];
		$Obj -> cache_lifetime = $ViewConfig['Cache.Time'];
		$Obj -> left_delimiter = $ViewConfig['Left'];
		$Obj -> right_delimiter = $ViewConfig['Right'];
		glovar('View',$Obj,'OS');
	}
	
	/**
	* 处理Url数组结构
	* @param 参数内容 $QueryUrl
	* 
	*/
	function Arrgd($QueryUrl,$Config)
	{
	    $Info = str_replace($Config['Tail'] , '', $QueryUrl);
	    $UrlArr = explode($Config['Division'] , $Info);
	    if ($Config['Division'] != '/') {
	        array_unshift($UrlArr, $Config['Division']);
	        $UrlArr[1] = str_replace('/', '', $UrlArr[1]);
	    }
	    $ArrCount = count($UrlArr);$Url='';$End=0;
	    if ($ArrCount > 1) {
	    	$Action = (isset($UrlArr[1]))?($UrlArr[1]):($this->ActionName);
	    	$Class = (isset($UrlArr[2]))?($UrlArr[2]):($this->ClassName);
	    	$Function = (isset($UrlArr[3]))?($UrlArr[3]):($this->FunctionName);
	    	$AccessArr = array('Action'=>$Action,'Class'=>$Class,'Function'=>$Function);
	        for ($Ned = 4; $Ned <= $ArrCount; $Ned++) { if ($Ned != $End) { $End = $Ned + 1; if (isset($UrlArr[$Ned])) { $End = $Ned + 1; if(isset($UrlArr[$Ned]) and isset($UrlArr[$End])){ $Url .= '&' . $UrlArr[$Ned] . '=' . $UrlArr[$End];} } } }
	        parse_str($Url, $UrlNed);
	        $_GET = $UrlNed;
	        $this->setAccess($AccessArr,$Config);
	    }
	}
	
	/**
	* 设置默认访问
	* @param 实例项目名称 $Action
	* @param 控制器名称 $Class
	* @param 方法名称 $Function
	* 
	*/
	public function setdefault($Action='Main',$Class='App',$Function='index')
	{
		if(empty($this->ActionName)){ $this->ActionName = $Action; }
		if(empty($this->ClassName)){ $this->ClassName = $Class;}
		if(empty($this->FunctionName)){ $this->FunctionName = $Function; }
	}
	
	/**
	* 统计实例项目数
	* 
	*/
	function Actioncount()
	{
		$Path = $this->CorePath . '/Action';
		$DirArr = $this->getDir($Path);
		$this->ActionCount = count($DirArr);
	}
	
	/**
	* 自动连接Mysql
	* 
	*/
	function ConnMysql()
	{
		$MysqlConfig = $this->ReadConfig('Mysql','Mysql');
		if($MysqlConfig['Connect']){
			if(is_array($MysqlConfig)){$Mysql = Libs('Mysql'); $Mysql->option($MysqlConfig); $Mysql->Connect();glovar('Mysql',$Mysql,'OS');}
		}
	}
	
	/**
	* 设置常量和配置信息
	*/
	function setConstant()
	{
		$UrlConfig = $this->ReadConfig('Url','Url');
		$SystemConfig = $this->ReadConfig('System','System');
		$Path = str_replace('\/','/',dirname($_SERVER['PHP_SELF']) . '/');
		$DefineArr =  array(
			'__APP__' => $Path,
			'__TMP__' => $Path . 'Template/',
			'__PLUG__' => 'Libs/Plug/',
			'A_NAME' => Receive($UrlConfig['Action']),
			'C_NAME' => Receive($UrlConfig['Class']),
			'F_NAME' => Receive($UrlConfig['Function']),
			'FRAMEWROK_VER' => $this->Version
			);
		foreach ($DefineArr as $key => $value) {
			define($key,$value);
		}
		ini_set('date.timezone',$SystemConfig['Time.zone']);
	}

	/**
	 * 绑定实例
	 * @param  string $action [实例名称]
	 */
	public function bindAction($action = '')
	{
		if(!empty($action)){ $this->Action = $action; }
	}
	
	/**
	* 重载方法
	* @param 名称 $name
	* @param 参数 $arguments
	* 
	* @return Object Or Bool
	*/
	public function __call($name, $arguments) 
    {
    	$Class = '\Libs\Deal\\'.$name;
    	if(class_exists($Class)){
			$Class = new $Class;
			return $Class;
		}
		return FALSE;
    }
	
	/**
	* Start OS
	* 
	*/
	public function Run($RunRoute=true)
	{
		if($RunRoute){
			$this -> RunRoute();
		}
		$App = $this->CreateObj();
		$function = $this -> FunctionName;
		$App -> $function();
	}
}

/**
* 实例化驱动对象
* 
*/
$Php300 = new Php300Deal();

glovar('PHP300',$Php300,'OS');

glovar('Runcount','0','OS');