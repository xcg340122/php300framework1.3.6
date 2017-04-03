<?php

/**
 *  Socket_class.php Socket操作类
 *
 * @copyright			(C) 2015-2016 PHP300
 * @license				http://framework.php300.cn
 * @lastmodify			2017-04-01
 */
use Workerman\Worker;
include_once(FILE_PATH.'Libs/Plug/WorkerMan/Autoloader.php');

class Socket_class extends System_class {
	
	public $Workerman;
	
	public $Adderss = '';
	
	public $Count = 4;
	
	/**
	* 设置参数
	* @param 类型 $type
	* @param 地址 $adderss
	* @param 端口 $port
	* 
	* @return
	*/
	public function Option($type='http',$adderss,$port){
		$this->Adderss = ($adderss=='')?('http://0.0.0.0:2345'):($type.'://'.$adderss.':'.$port);
		$this->Workerman =  new Worker($this->Adderss);
		return $this;
	}
	
	/**
	* 运行socket
	* 
	* @return
	*/
	public function Run(){
		Worker::runAll();
	}
	
	/**
	* 停止socket
	* 
	* @return
	*/
	public function Stop(){
		Worker::stopAll();
	}

}
