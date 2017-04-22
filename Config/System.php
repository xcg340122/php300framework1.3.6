<?php
return array(
	
	/**
	* 系统设置
	*/
	'System' => array(
	
		'Time.zone' => 'Asia/Shanghai',		//系统时区
		
		'Debug' => true,					//是否开启debug
		
		'Logs' => true,						//是否开启日志记录
		
		'Upload' => 'Uploads/',				//上传路径
		
		'Status.Template' => 'Php300/Msg',	//状态页模板
		
		'Error.Template'  => 'Php300/Error',//系统错误页模板
		
	),
	
	/**
	* Session设置
	*/
	'Session' => array(
	
		'Session.start' => true,	//是否开启session
	
		'Cookie.domain' => '',		//session有效域
		
		'Session_name' => 'Php300',	//session名称
		
		'Session_path' => '',		//session保存路径
		
		'Session_callback'	=> '',	//session回调
	
	),
	
	/**
	* 云平台配置信息
	*/
	'YUN' => array(
	
		'SN' => '',				//云平台SN
		
		'Type' => '0',			//0：不启用云平台,1：启用并同步一次,2：启用并永久同步
		'Confusion' => false,	//是否进行代码压缩
	)
	
);