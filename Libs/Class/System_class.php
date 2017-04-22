<?php

namespace Libs\Deal;

class System {
	
	
	/**
	* 重载方法
	* @param 名称 $name
	* @param 参数 $arguments
	* 
	*/
	public function __call($name, $arguments) 
    {
    	Error('PHP300::没有捕获到该方法活动 -> ' .$name);
    }
	
}