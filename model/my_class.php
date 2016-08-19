<?php
/**
*这是个演示类文件,自定义的类可以继承system_class类
* 测试生成的资源默认保存在uploads文件夹下
*/

class my_class extends system_class{
	
	public function index(){
		M('system')->set_var('hello_txt','欢迎使用PHP300Framework V'.FRAMEWROK_VER.'<br /><br />本次更新时间：'.FUNCTION_UPDATATIME);
		M('system')->display('index');
	}
	
	/**
	* db_use()
	* 数据库演示
	*  url：index.php?c=my&f=db_use
	*/
	
	public function db_use(){	//可在数据库配置文件设置[autoconnect]为true自动连接数据库
		if(DB()->link!=NULL){
			$res = DB() -> get_one('*','test_tab','id=1');	//查询sql (查询单条)
			$res = DB() -> select('*','test_tab',"username<>''");	//查询sql (查询多条)
			print_r($res);
			print_r($res);
		}else{
			echo '数据库未连接！';
		}
	}
	
	/**
	* cookie_use()
	* cookies操作演示
	* url：index.php?c=my&f=cookie_use
	*/
	
	public function cookie_use(){
		M('cookies') ->set('NOW',time());	//设置cookies
		$now = M('cookies') ->get('NOW');	//获取cookies
		if($now==''){
			echo 'cookies写入成功,三秒后自动刷新显示...<script>setTimeout(\'location.reload()\',3000)</script>';
		}else{
			echo 'COOKIES记录时间：'.date('Y-m-d H:i:s',$now).'<br />延迟3秒...<br />当前时间：'.date('Y-m-d H:i:s',time());
			M('cookies') -> clear('NOW');	//清除cookies
		}
	}
	
	/**
	* http_use()
	* http操作演示
	* url：index.php?c=my&f=http_use
	*/
	
	public function  http_use(){
		//GET
		$content = M('http')->get('http://zlmc.qq.com/');	//获取页面源码
		echo $content;
		//POST
		$arr = array(
			'username'=>'test',
			'password'=>'test',
		);
		$content = M('http')->post('http://www.test.com',$arr);
		print_r($content);
	}
	
	/**
	* image_use()
	* 图片操作演示 (本操作需要提前开启GD库)
	* url：index.php?c=my&f=image_use
	*/
	
	public function image_use(){
		$imagepath = C('UPLOAD').'1.jpg';	//从配置文件读取上传路径信息
		//设置图片
		M('image')->setImage($imagepath);
		//获取信息
		echO '图片大小：'.M('image')->Imagesize();
		echo '<br />图片后缀：'.M('image')->Extension();
		echo '<br />图片MIME：'.M('image')->mime();	
		//缩放
		M('image')->resize(300,180);
		M('image')->save(C('UPLOAD').'resize.jpg');
		echo '<br />缩放操作已完成：'.C('UPLOAD').'resize.jpg';	
		//修剪
		M('image')->setImage($imagepath);	//重新设置操作的目标图片,否则将会将上面的操作继续保留
		M('image')->crop(80,75,120,100);
		M('image')->save(C('UPLOAD').'crop.jpg');
		echo '<br />修剪操作已完成：'.C('UPLOAD').'crop.jpg';	
		//加入水印文字
		M('image')->setImage($imagepath);
		M('image')->AddText('PHP300','./Arial.ttf',20,'bottom-left','228,6,0');
		//注意上面的字体使用系统自带的字体需要在前面加上./否则会出错,最好把字体拷贝到网站目录写文件地址
		M('image')->save(C('UPLOAD').'addText.jpg');
		echo '<br />加入水印文字操作已完成：'.C('UPLOAD').'addText.jpg';
		//设置灰色
		M('image')->setImage($imagepath);
		M('image')->grayscale();
		M('image')->save(C('UPLOAD').'grayscale.jpg');
		echo '<br />设置灰色操作已完成：'.C('UPLOAD').'grayscale.jpg';
		//调整亮度
		M('image')->setImage($imagepath);
		M('image')->brightness(70);
		M('image')->save(C('UPLOAD').'brightness.jpg');
		echo '<br />调整亮度操作已完成：'.C('UPLOAD').'brightness.jpg';
	}
	
	/**
	* file_use()
	* 文件操作演示
	* url：index.php?c=my&f=file_use
	*/
	
	public function file_use(){
		//创建多层文件夹
		M('file')->createDir(C('UPLOAD').'test1/'.date('Y-m-d').'/');
		echo '创建文件夹完成：'.C('UPLOAD').'test1/'.date('Y-m-d').'/';
		//写出文本
		M('file')->writetxt(C('UPLOAD').'test.txt','您好，这是php300测试写出文本文件');
		echo '<br />写出文本完成：'.C('UPLOAD').'test.txt';
	}
}
?>