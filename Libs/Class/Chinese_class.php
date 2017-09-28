<?php

/**
* @copyright: PHP300Framework
* @author: Chungui
*
*/

namespace Libs\Deal;

class Chinese
{
	//规则文件路径
	private $RuleFile = FALSE;

	//规则文件内容
	private $RuleContent;

	public function init()
	{
		$this->RuleFile = str_replace(DIRECTORY_SEPARATOR.'Class','',dirname(__FILE__)).DIRECTORY_SEPARATOR.'Plug/Rules.lib';
		if(is_file($this->RuleFile))
		{
			$this->RuleContent = file_get_contents($this->RuleFile);
			if(!empty($this->RuleContent))
			{
				$this->RuleContent = json_decode(base64_decode($this->RuleContent),TRUE);
			}
		}
	}

	/**
	* 处理文件
	*/
	public function Dnfile($fileName = '')
	{
		if(is_file($fileName))
		{
			if(!$this->RuleFile)
			{
				$this->init();
			}
			$ObjName    = './Cache/'.md5($fileName).'.php';
			$CodeTo     = file_get_contents($fileName);
			$fileUpdate = @intval($this->getMiddleStr($CodeTo,'//USTART_','_UEND'));	//获取记录的修改时间
			$UpdateTime = filemtime($fileName);	//控制器最后修改时间
			if(($UpdateTime - $fileUpdate) == 0)
			{
				//控制器无改动
				return $ObjName;
			}
			$NowTime = time();

			$fileType= @mb_detect_encoding($CodeTo, array('UTF-8','GBK','LATIN1','BIG5'));
			$CodeTo = ($fileType != 'UTF-8') ? (@mb_convert_encoding($CodeTo, 'UTF-8', $fileType)) : ($CodeTo);	//进行中文编码兼容
			foreach($this->RuleContent as $key=>$value)
			{
				$CodeTo = @preg_replace('/' . $key . '/', $value, $CodeTo);
			}
			$CodeTo = StripWhitespace($CodeTo);
			if(strpos($CodeTo, '?>') === FALSE)
			{
				$CodeTo .= ' //USTART_'.$UpdateTime.'_UEND ?>';
			}
			else
			{
				$CodeTo .= '<?php //USTART_'.$UpdateTime.'_UEND ?>';
			}
			file_put_contents($ObjName,$CodeTo);
			return $ObjName;
		}
	}

	private function getMiddleStr($Str,$left,$right)
	{
		$arr = explode($left,$Str);
		if(!empty($arr[1]))
		{
			$arr = explode($right,$arr[1]);
			if(!empty($arr[0]))
			{
				return $arr[0];
			}
			return '';
		}
		return '';
	}

}
?>