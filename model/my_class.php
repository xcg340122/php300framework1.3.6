<?php
/*
*这是个演示类文件,自定义的类可以继承system_class类
*/

class my_class extends system_class{
	
	public function index(){
		
		M('system')->set_var('hello_txt','欢迎使用PHP300Framework V'.FRAMEWROK_VER.'<br /><br />本次更新时间：'.FUNCTION_UPDATATIME);
		M('system')->display('index');
	}
	
	/**
	* db_use()
	* 数据库演示
	*/
	
	public function db_use(){	//可在数据库配置文件设置[autoconnect]为true自动连接数据库
		if(DB()->link!=NULL){
			$res = DB() -> select('*','test_table','id=1');	//查询sql
			print_r($res[0]);
		}else{
			echo '数据库未连接！';
		}
	}
	
	/**
	* cookie_use()
	* cookies操作演示
	*/
	
	public function cookie_use(){
		M('cookies') ->set('NOW',time());	//设置cookies
		$now = M('cookies') ->get('NOW');	//获取cookies
		if($now==''){
			echo 'cookies写入成功,三秒后自动刷新显示...<script>setTimeout(\'location.reload()\',3000)</script>';
		}else{
			echo 'COOKIES记录时间：'.date('Y-m-d H:i:s',$now).'<br />当前时间：'.date('Y-m-d H:i:s',time());
			M('cookies') -> clear('NOW');	//清除cookies
		}
	}
}
?>