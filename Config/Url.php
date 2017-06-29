<?php
return array(
	
	/**
	* URL访问配置
	*/
	'Url' => array(
			
		'Switch' => true,	//路由开关
		
		'Tail' => '.html',	//伪静态后缀
	
		'Action' => 'a',	//实例项目参数
		
		'Class' => 'c',		//控制器参数
		
		'Function' => 'f',	//方法参数
		
		'default.Action'	=>	'Main',	//默认实例
		
		'default.Function'	=>	'index'	//默认方法
		
		),
		
	/**
	* 路由匹配
	*/
	'Routing' => array(



		),
);
?>