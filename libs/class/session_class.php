<?php
@session_start();
/**
 *  session_class.php SESSION操作类
 *
 * @copyright			(C) 2015-2016 PHP300
 * @license				http://framework.php300.cn
 * @lastmodify			2016-11-26
 */
class Session_class extends System_class{
	
	public $session_name = 'PHP300';
	
	public function __construct(){
		@ini_set('session.name',$this->session_name);
		@ini_set('session.use_cookies','1');
	}
	
	public function set($key, $data, $expiration = 0){
		if($key!='' and $data!=NULL){
			if(is_numeric($expiration) && $expiration > 0){
				session_set_cookie_params($expiration);
			}
			$_SESSION[$key] = $data;
			$this->setclient_sign();
		}
	}
	
	public function setclient_sign(){
		$_SESSION['PHP300_session_info']['ip'] = getIP();
		$_SESSION['PHP300_session_info']['browser'] = getAgentInfo();
	}
	
	public function getclient_sign(){
		return $_SESSION['PHP300_session_info'];
	}
	
	public function get($name=''){
		$info = $this->getclient_sign();
		if(is_array($info)){
			if($info['ip']==getIP() and $info['browser']==getAgentInfo()){
				if($name!=''){
					return $_SESSION[$name];
				}
				return $_SESSION;
			}
		}
	}
	
	public function del($name){
		if($name!=''){
			unset($_SESSION[$name]);
		}
	}
	
	public function delAll(){
		unset($_SESSION);
	}
}
?>