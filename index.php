<?php
/**
* PHP300 - FrameWorks
* http://framework.php300.cn
* 2017年04月01日
* version 1.3.0
*/
if(substr(PHP_VERSION,0,3) > '5.4'){
	
	if(PHP_SAPI=='cli'){
		
		ini_set('include_path',dirname(__FILE__));
	}
	
	/**
	* 引用核心文件
	*/
	include_once('Libs/Php300.php');
	
	if(PHP_SAPI=='cli'){
		
		/**
		* cli模式下的默认入口
		*/
		M('Server') -> index();	
	}else{
		
		/**
		* 默认入口,可按需求修改
		*/
		M('App') -> index();	
	}
}else{
	
	echo '<meta charset="UTF-8">PHP300:请将PHP版本切换至5.4以上运行!';
}