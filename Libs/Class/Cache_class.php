<?php

/**
* @copyright: PHP300Framework
* @author: Chungui
*
*/

namespace Libs\Deal;

class Cache
{
	
	/**
	* 缓存目录名
	*/
	protected $saveFolder = './Cache/';
	
	/**
	* 设置文件名
	*/
	protected $settingFile = './Cache/settings.cache.php';
	
	/**
	* 缓存后缀
	*/
	protected $ext = '.cache.php';
	
	/**
	* 缓存数据
	*/
	protected $data = null;
	
	/**
	* 缓存对象
	*/
	protected static $Cache = null;

	public function __construct()
	{
		$this->data = $this->run();
	}

	/**
	* 静态加载方法
	* @param 加载名称 $name
	* @param 加载参数 $args
	* 
	* @return
	*/
	public static function __callStatic($name, $args)
	{
		if(!self::$Cache)
		self::$Cache = new self();
		if($name == 'set')
		{
			if(count($args) == 2) self::$Cache->set($args[0], $args[1]);
			if(count($args) == 3) self::$Cache->set($args[0], $args[1], $args[2]);
		}
		else
		if($name == 'get')
		{
			if(count($args) == 0) return self::$Cache->get();
			if(count($args) == 1) return self::$Cache->get($args[0]);
		}
		else
		if($name == 'has')
		{
			if(count($args) == 1) return self::$Cache->has($args[0]);
		}
		else
		if($name == 'delete')
		{
			if(count($args) == 0) self::$Cache->delete();
			if(count($args) == 1) self::$Cache->delete($args[0]);
		}
	}

	/**
	* 设置缓存
	* @param 缓存名称 $name
	* @param 缓存值 $value
	* @param 缓存周期 $time
	* 
	* @return
	*/
	protected function set($name, $value, $time = true)
	{
		if($time !== true)
		{
			$this->data[$name] = time() + (60 * $time);
		}
		else
		{
			$this->data[$name] = $time;
		}
		
		$this->setContent($name, $value);
	}


	/**
	* 获取缓存
	* @param 缓存名称 $name
	* 
	* @return String
	*/
	protected function get($name = null)
	{
		if(is_string($name))
		{
			if(!$this->fileExist($name))
			return null;
			else
			return $this->getContent($name);
		}
		else
		if($name == null)
		{
			$out = array();
			foreach($this->data as $key=>$value)
			{
				if($this->fileExist($key))
				$out[$key] = $this->getContent($key);;
			}
			return $out;
		}
		else
		if(is_array($name))
		{
			$out = array();
			foreach($name as $value)
			{
				if(isset($this->data[$value]))
				$out[$value] = $this->getContent($value);;
			}
			return $out;
		}
		return null;
	}


	/**
	* 文件has值
	* @param 名称 $name
	* 
	* @return
	*/
	protected function has($name)
	{
		if(isset($this->data[$name]))
		{
			if($this->fileExist($name))
			{
				return true;
			}
			else
			{
				unset($this->data[$name]);
				return false;
			}
		}
		if($this->fileExist($name))
		{
			$this->deleteFile($name);
		}
		return false;
	}

	/**
	* 删除缓存
	* @param 缓存名称 $name
	* 
	* @return
	*/
	protected function delete($name = null)
	{
		if(is_string($name)){
			$this->deleteFile($name);
			if(isset($this->data[$name]))
			{
				unset($this->data[$name]);
			}
		}
		else
		if(is_array($name))
		{
			foreach($name as $value){
				$this->deleteFile($value);
				if(isset($this->data[$value]))
				unset($this->data[$value]);
			}
		}
		else
		if($name === null)
		{
			$this->data = array();
			$this->deleteAll();
		}
	}

	/**
	* 初始化缓存对象
	* 
	* @return
	*/
	protected function run()
	{
		if($this->data == null)
		$this->data = $this->getSettings();
		$out = $this->data;
		foreach($this->data as $key=>$value)
		{
			if($value < time() or $value == false)
			{
				unset($out[$key]);
				$this->deleteFile($key);
			}
		}
		$this->data = $out;
		return $out;
	}

	/**
	* 帮的缓存目录
	* 
	* @return
	*/
	protected function build()
	{
		chmod('./Cache', 0770);
		if(!file_exists( $this->saveFolder)){
			mkdir($this->saveFolder, 0770);
			$htaccess = '<Files "*">order allow,denydeny from all</Files>';
			file_put_contents($this->saveFolder.'.htaccess',$htaccess);
			file_put_contents($this->settingFile, json_encode(array()));
		}
	}

	/**
	* 文件状态
	* @param 名称 $name
	* 
	* @return
	*/
	protected function fileExist($name)
	{
		if(file_exists($this->saveFolder.$name.$this->ext))
		return true;
		else
		return false;
	}

	/**
	* 获取内容
	* @param 名称 $name
	* 
	* @return
	*/
	protected function getContent($name)
	{
		if($this->fileExist($name))
		return json_decode(file_get_contents($this->saveFolder.$name.$this->ext));
		else
		return null;
	}

	/**
	* 设置内容
	* @param 名称 $name
	* @param 值 $value
	* 
	* @return
	*/
	protected function setContent($name, $value)
	{
		file_put_contents($this->saveFolder.$name.$this->ext,json_encode($value));
	}

	/**
	* 设置配置
	* @param 值 $data
	* 
	* @return
	*/
	protected function setSettings($data)
	{
		file_put_contents($this->settingFile, json_encode($data));
	}

	/**
	* 获取配置
	* 
	* @return
	*/
	protected function getSettings()
	{
		if(!file_exists($this->settingFile))
		{
			$this->build();
			return array();
		}
		else
		{
			return json_decode(file_get_contents($this->settingFile), true);
		}
	}

	/**
	* 删除文件
	* @param 名称 $name
	* 
	* @return
	*/
	protected function deleteFile($name)
	{
		if($this->fileExist($name))
		unlink($this->saveFolder.$name.$this->ext);
	}

	/**
	* 删除全部文件
	* 
	* @return
	*/
	protected function deleteAll()
	{
		foreach(glob($this->saveFolder. '/*') as $file){
			if(file_exists($file)){
				unlink($file);
			}
		}
	}

	/**
	* 构造函数_销毁处理
	* 
	* @return
	*/
	public function __destruct()
	{
		$this->setSettings($this->data);
	}
}
?>