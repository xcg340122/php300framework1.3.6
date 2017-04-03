<?php

/**
 * php300系统类
 * 系统类已继承云类库,其它类直接继承System_class
 */

class System_class extends php300_class {

    public function __construct() {
    	
        if (C('autoconnect', 'DB')) { 	
            DB()->open(C('DB', 'DB'));          
        }
        if (is_array(C('System_constant'))) {   	
            foreach (C('System_constant') as $key => $val) {        	
                $this->SetVar($key, $val);               
            }
        }
    }

	/**
	* 渲染模板
	* @param 文件名 $names
	* @param 渲染类型 $type
	* 
	* @return
	*/
    public function display($names = 'index', $type = '1') {
        $tmp_names = $names . C('TMP_TAIL');
        if (!M('TMP')->templateExists($tmp_names)) {
            $this->getErrorPage('找不到' . $tmp_names . '模板页');
        }
        $m = ($type == '1') ? (C('TMP_TAIL')) : ('.html');
        M('TMP')->display($names . $m);
    }
    
    /**
	* 获取全部模板变量
	* @param 变量名称 $vername
	* 
	* @return array
	*/
    public function getVars($vername = '') {
        return M('TMP')->get_template_vars($vername);
    }
    
    /**
	* 展示错误页
	* @param 错误文本 $error_txt
	* @param 模板文件 $tmp_names
	* 
	* @return
	*/
    public function getErrorPage($error_txt = '未知错误', $tmp_names = 'PHP300TMP/PHP300Error') { //调用错误页
        if (C('DEBUG', 'PHP300_CON')) {
            if (C('LOGS', 'PHP300_CON')) {
                $this->record_logs($error_txt);
            }
            $this->DelTmp();
            $this->SetVar('error_txt', $error_txt);
            M('TMP')->left_delimiter = '<{';
            M('TMP')->right_delimiter = '}>';
            if (PHP_SAPI == 'cli') {
                $tmp_names = str_replace('Libs' . DIRECTORY_SEPARATOR . 'Class', '', dirname(__FILE__)) . 'Template/' . $tmp_names;
            }
            $this->display($tmp_names, '2');
            exit();
        }
        exit('<meta charset="UTF-8">站点出现问题啦,快及时联系站长哟!');
    }
    
    /**
	* 设置模板变量
	* @param undefined $varname
	* @param undefined $val
	* 
	* @return
	*/
    public function SetVar($varname = '', $val = '') { //设置模板变量
        M('TMP')->assign($varname, $val);
    }
    
    /**
	* 清除全部变量赋值
	* @return
	*/
    public function DelVars() {
        M('TMP')->clearAllAssign();
    }
    
    /**
	* 清除指定变量赋值,参数可以是个string也可以是个array
	* @param 变量名称 $varname
	* 
	* @return
	*/
    public function DelVar($varname = '') {
        M('TMP')->clearAssign($varname);
    }
    
    /**
	* 清除缓存文件,参数为空的话则清除全部缓存文件
	* @param 模板文件 $tmpname
	* 
	* @return
	*/
    public function DelTmp($tmpname = '') {
        if ($tmpname != '') {
            //$this->del_cache($tmpname);
            $tmpname = $tmpname . C('TMP_TAIL');
        } else {
            unset($tmpname);
        }
        M('TMP')->clearCompiledTemplate($tmpname);
    }
    
    /**
	* 清空缓存,非缓存文件
	* @param 模板文件 $tmpname
	* 
	* @return
	*/
    public function DelCache($tmpname = '') {
        if ($tmpname != '') {
            M('TMP')->cache->clear($tmpname . C('TMP_TAIL'));
        } else {
            M('TMP')->cache->clearAll();
        }
    }
    
    /**
	* 编译全部模板文件,参数为true的话则只编译修改过的文件,为false的话则强制编译全部文件
	* @param 是否只编译修改过的文件 $force
	* 
	* @return
	*/
    public function ComTmp($force = true) {
        M('TMP')->compileAllTemplates(C('TMP_TAIL'), $force);
    }
    
    /**
	* 获取模板内容
	* @param 模板文件 $names
	* 
	* @return String
	*/
    public function Fetch($names = 'index') {
        $tmp_names = $names . C('TMP_TAIL');
        if (!M('TMP')->templateExists($tmp_names)) {
            $this->getErrorPage('找不到' . $tmp_names . '模板页');
        }
        return M('TMP')->fetch($names . C('TMP_TAIL'));
    }
    
    /**
	* 重载方法
	* @param 方法名称 $method
	* @param 参数 $args
	* 
	* @return
	*/
    public function __call($method, $args) {
        $error = "貌似找不到<b>" . $method . "</b>方法哟~!";
        $this->getErrorPage($error);
        return false;
    }
    
    /**
	* 错误文件错误钩子
	* @param 错误句柄 $errno
	* @param 错误文本 $errstr
	* @param 错误文件 $errfile
	* @param 错误行数 $errline
	* 
	* @return
	*/
    public function php300_error_handler($errno, $errstr, $errfile, $errline) {
        if ($errno < 8) {
            $error_txt = "错误级别：$errno<br />错误信息：$errstr<br />错误文件：$errfile<br />错误行数：$errline";
            $this->getErrorPage($error_txt);
        }
    }
    
    /**
	* 错误日志记录
	* @param 错误文本 $errortxt
	* 
	* @return
	*/
    public function record_logs($errortxt) {
        if ($errortxt != '') {
            $errortxt = str_replace('<br />', '，', $errortxt) . " --- 生成时间：" . date('Y-m-d H:i:s', time()) . "\r\n\r\n";
            if (PHP_SAPI == 'cli') {
                $p = str_replace('Libs' . DIRECTORY_SEPARATOR . 'Class', '', dirname(__FILE__));
            }
            error_log($errortxt, 3, $p . 'Logs/error_logs.log');
        }
    }

}
