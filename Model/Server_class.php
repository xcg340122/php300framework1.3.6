<?php

/**
 * Socket演示类
 * Workerman手册：http://doc3.workerman.net/
 * 在线测试socket：http://www.blue-zero.com/WebSocket/
 */
class Server_class extends System_class {

	/**
	* 
	* @var 绑定IP
	* 
	*/
    private $Adderss = '127.0.0.1';
    
    /**
	* 
	* @var 使用端口
	* 
	*/
    private $Port = 5000;
    
    /**
	* 
	* @var 设置当前socket实例启动进程数
	* 
	*/
    private $Count = 4;
    /**
	* 
	* @var 当前socket对象
	* 
	*/
    private $Socket;
    
    /**
	* 
	* @var 当前socket进程ID
	* 
	*/
    private $Id = '1';
    
    /**
	* 
	* @var 当前socket名称
	* 
	*/
    private $Name = 'PHP300';
    
    /**
	* 通过访问该控制器的index方法启动Server
	* 支持websocket,tcp,udp协议
	* @return
	*/
    public function index() {
        if (PHP_SAPI != 'cli') {
            error('请在cli(命令行)模式下运行本方法');
    	}
    	$this->Socket = socket('websocket',$this->Adderss,$this->Port);
    	$this->init();
	}
	
	/**
	* 客户端连接回调
	*/
	static public function onConnect($Connect){
		$Connect->send('Welcome');
	}
	
	/**
	* 客户单断开回调
	*/
	static public function onClose($Connect){
		
		
	}
	
	/**
	* 服务器接收到消息回调
	*/
	static public function onMessage($Connect,$Data){
		
		$Connect->send('Receive message：'.$Data);
	}
	
	/**
	* 当服务端启动时回调
	*/
	static public function onStart($Socket){
		
		
	}
	
	/**
	* 当服务端停止时回调
	*/
	static public function onStop($Socket){
		
		
	}
	
	/**
	* 当服务端重启时回调
	*/
	static public function onReload($Socket){
		
		
	}
	
	/**
	* 当客户端连接发生错误时回调
	*/
	static public function onError($Connect,$Code,$Msg){
		
		
	}
	
	/**
	* 初始化
	*/
	private function init(){
		$this->Option();
		$this->Socket->Workerman->onWorkerStart = function($Socket){
			Server_class::onStart($Socket);
		};
		$this->Socket->Workerman->onWorkerReload = function($Socket){
			Server_class::onReload($Socket);
		};
		$this->Socket->Workerman->onWorkerStop = function($Socket){
			Server_class::onStop($Socket);
		};
		$this->Socket->Workerman->onConnect = function($Connect){
			Server_class::onConnect($Connect);
		};
		$this->Socket->Workerman->onClose = function($Connect){
			Server_class::onClose($Connect);
		};
		$this->Socket->Workerman->onMessage = function($Connect,$Data){
			Server_class::onMessage($Connect,$Data);
		};
		$this->Socket->Workerman->onError = function($Connect,$Code,$Msg){
			Server_class::onError($Connect,$Code,$Msg);	
		};
		$this->Socket->Run();
	}
	
	/**
	* 设置socket属性
	*/
	private function Option(){
		$this->Socket->Workerman->id = $this->Id;
		$this->Socket->Workerman->name = $this->Name;
		$this->Socket->Workerman->count = $this->Count;
	}
}
