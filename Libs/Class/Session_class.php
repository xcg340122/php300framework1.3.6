<?php

/**
* @copyright: PHP300Framework
* @author: Chungui
* 
*/

namespace Libs\Deal;

class Session {
	
	public $Name = 'PHP300';	//缓存名称
	
	public $Second = '0';	//缓存周期,单位：秒
	
	public function start(){
		ini_set('session.name',$this->Name);
		ini_set('session.auto_start','1');
		ini_set('session.cookie_lifetime',$Second);
	}
}