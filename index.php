<?php

/**
* PHP300Framework默认入口
*/
if(substr(PHP_VERSION,0,3) < 5.3){
	
	exit('<meta charset="UTF-8">PHP300:请将PHP版本切换至5.2以上运行!');
	
}
include_once('LibS/Php300.php');

$Php300 -> setdefault('Main','App','index');//实例项目,控制器,方法

$Php300 -> Run();