<?php
/**
 *  session_class.php SESSION操作类
 *
 * @copyright			(C) 2015-2016 PHP300
 * @license				http://framework.php300.cn
 * @lastmodify			2016-10-15
 */
class session_class extends system_class{
	
	protected $client_name = 'PHP300_ID';
	
	protected $server_str = '_session_';
	
	protected $id = '';
	
	protected $data = array();
	
	public function __construct(){
		$id = cookies()->get($this->client_name);
		if($id==''){
			$id = $this->create_id();
			cookies()->set('PHP300_ID',$id);
		}
		$data = cache()->get($this->server_str.$id);
		if($data['ip']==''){
			cache()->set($this->server_str.$id,array('ip'=>getIP()));
		}
		$this->id = $this->server_str.$id;
	}
	
	public function create_id(){
		$id = time().base64_encode(rand('100','999'));
		return $id;
	}
	
	public function set($key, $data, $expiration = 0){
		if($key!='' and $data!=NULL){
			$res = cache()->get($this->id);
			$res['data'][$key] = $data;
			cache()->set($this->id,$res,$expiration);
		}
	}
	
	public function get($name=''){
		$data = cache()->get($this->id);
		if($name!=''){
			return $data['data'][$name];
		}
		return $data['data'];
	}
	
	public function del($name){
		if($name!=''){
			$res = cache()->get($this->id);
			unset($res['data'][$name]);
			cache()->set($this->id,$res);
		}
	}
	
	public function delAll(){
		cache()->del($this->id);
	}
}
?>