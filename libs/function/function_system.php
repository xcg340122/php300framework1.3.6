<?php

/**
* 存在返回模型对象,否则返回false
* @param 模型名称 $modelname
* 
* @return Object
*/
function M($modelname) { //返回MODEL
    if ($modelname == '') {
        return false;
    }
    if (is_object($GLOBALS[$modelname])) {
        return $GLOBALS[$modelname];
    }
    return false;
}

/**
 * PHP300()
 * 返回PHP300云对象
 * @return Object
 */
function php300() {
    if (is_object(M('php300'))) {
        $cache_obj = M('php300');
        return $cache_obj;
    } else {
        return false;
    }
}

/**
* 返回配置信息
* @param 配置名称 $configname
* @param 指定配置文件名称 $configfile
* 
* @return String or Array
*/
function C($configname, $configfile = '') {
    $arr = array('PHP300_CON', 'DB', 'U', 'TEMP');
    if ($configfile != '') {
        array_unshift($arr, $configfile);
    }
    foreach ($arr as $val) {
        if (is_array($GLOBALS[$val])) {
            if (isset($GLOBALS[$val][$configname])) {
                return $GLOBALS[$val][$configname];
            }
        }
    }
    if (isset($GLOBALS[$configname])) {
        return $GLOBALS[$configname];
    }
    return false;
}

/**
* 获取参数[Get/Post]
* @param 参数名称 $name
* @param 为空返回的默认文本 $isnull
* @param 获取类型 $type
* @param 过滤函数 $filter
* 
* @return String or Array
*/
function I($name, $isnull = '', $type = 'GET', $filter = htmlspecialchars) {
    if ($name != '') {
        $value = ($type == 'GET') ? ($_GET[$name]) : ($_POST[$name]);
        $value = ($value == NULL) ? ($_POST[$name]) : ($value);
        $value = $value != '' ? $value : $isnull;
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $filter != '' ? $filter($val) : $val;
            }
        }
        return $value;
    } else {
        return $isnull;
    }
}

/**
* 临时设置配置项
* @param 键内容 $key
* @param 值内容 $val
* @param 配置到指定文件 $config
* 
* @return String or Array
*/
function G($key, $val, $config = '') {
    if ($config) {
        $GLOBALS[$config][$key] = $val;
    } else {
        $GLOBALS[$key] = $val;
    }
}

/**
* 返回MYSQL类
* @param 表明 $db
* 
* @return Object
*/
function DB($db = '') {
    if (C('autoconnect') == FALSE) {
        exit('<meta charset="UTF-8">PHP300:您尚未启用DB配置项内的自动连接,无法操作数据库');
    }
    if (extension_loaded('Mysqli')) {
        if (is_object(M('Mysqli'))) {
            $cache_obj = M('Mysqli');
            $cache_obj->set_db($db);
            return $cache_obj;
        } else {
            loads('Mysqli');
            $classname = 'Mysqli_class';
            $classname = new $classname($db);
            return $classname;
        }
    } else {
        exit('<meta charset="UTF-8">PHP300:检测到您的Mysqli扩展没有启动,无法使用Mysql类库操作');
    }
    return false;
}

/**
* 返回地址
* @param 类/方法 $path
* @param 附加参数 $param
* 
* @return String
*/
function U($path = '', $param = '') {
    $params = explode('/', $path);
    if (is_null($params[1])) {
        $params[1] = 'index';
    }
    $u = __APP__ . '?c=' . $params[0] . '&f=' . $params[1] . $param;
    return $u;
}

/**
* 执行处理URL参数,f参数默认等于index
* @param Class $c
* @param Function $f
* 
* @return
*/
function ExecUrl($c, $f = '') {
    $c = htmlspecialchars($c, ENT_QUOTES);
    $f = htmlspecialchars($f, ENT_QUOTES);
    $f = ($f) ? ($f) : ('index');
    $us = array('c' => $c, 'f' => $f);
    use_controller($us);
}

/**
* 调用控制器
* @param 配置数组 $option
* 
* @return
*/
function use_controller($option = array()) {
    if ($option['c'] != '') {
        $classname = substr($option['c'], 0, 1);
        $classname = (checkcase($classname)) ? (ucwords($option['c'])) : ($option['c']); //转换首字符大写
        if (M($classname) == false) {
            M('System')->getErrorPage('找不到' . $classname . '控制器');
        }
        if (method_exists(M($classname), $option['f'])) {
            M($classname)->$option['f']();
            exit();
        } else {
            M('System')->getErrorPage('在' . $classname . '控制器中找不到' . $option['f'] . '方法');
        }
    }
}

/**
* 执行静态路由
* @param 路径内容 $info
* @param 路由配置 $U
* 
* @return
*/
function url_routing($info, $U) {
    $info = str_replace($U['URL_TAIL'], '', $info);
    $url_arr = explode($U['URL_MIDDLE'], $info);
    if ($U['URL_MIDDLE'] != '/') { //处理分隔符
        array_unshift($url_arr, $U['URL_MIDDLE']);
        $url_arr[1] = str_replace('/', '', $url_arr[1]);
    }
    $count = count($url_arr);
    if ($count > 1) {
        $url_arr[2] = $url_arr[2] ? $url_arr[2] : 'index';
        $urls .= 'c=' . $url_arr[1] . '&f=' . $url_arr[2];
        $n = 0;
        for ($i = 3; $i <= $count; $i++) {
            if ($i != $n) {
                $n = $i + 1;
                if ($url_arr[$i] != '') {
                    $n = $i + 1;
                    $urls .= '&' . $url_arr[$i] . '=' . $url_arr[$n];
                }
            }
        }
        $urls = $urls;
        parse_str($urls, $out);
        $us = array('c' => $out['c'], 'f' => $out['f']);
        foreach ($out as $key => $val) {
            if ($key != 'c' and $key != 'f') {
                $_GET[$key] = $val;
            }
        }
        set_define(array('C_NAME' => $us['c'], 'F_NAME' => $us['f']));
        G('C_AND_F', $us, 'U');
    }
}

/**
* 预加载继承类
* @param 类库预加载 $classname
* 
* @return
*/
function firstload($classname) {
    if ($classname != '') {
        $classname = str_replace('_class', '', $classname);
        loads($classname);
    }
}

/**
* 承接加载
* @param 类名称 $classname
* 
* @return
*/
function loads($classname) {
    if ($classname != '' and $classname != 'php300') {
        if (in_array($classname, $GLOBALS['PHP300_CON'] ['CLASSLIST'])) {
            $classname = FILE_PATH . 'Libs/Class/' . $classname . CLASS_NAME;
        } else {
            $p = (PHP_SAPI == 'cli') ? (str_replace('Libs' . DIRECTORY_SEPARATOR . 'Function', '', dirname(__FILE__))) : (FILE_PATH);
            $cachename = $p . 'Cache/' . md5($classname) . '.php';
            if (file_exists($cachename)) {
                $classname = $cachename;
            } else {
                $classname = $p . 'Model/' . $classname . CLASS_NAME;
            }
        }
        if (file_exists($classname)) {
            include_once($classname);
        }
    }
}

/**
* 获取中文匹配正则表达式
* 
* @return String
*/
function return_key() {
    if (file_exists(PLUG . 'php300_match.json')) {
        $keyword = @file_get_contents(PLUG . 'php300_match.json');
        $keyword = json_decode($keyword, true);
        return $keyword;
    }
    return '';
}

/**
* 更新云类库
* @param 系统配置 $CON
* 
* @return
*/
function update_class($CON) {
    if (!file_exists(FILE_PATH . 'Cache')) {
        mkdir(FILE_PATH . 'Cache');
    }
    $cachefile = FILE_PATH . 'Cache/cache_class.php';
    $file_link = @fopen($cachefile, 'w');
    $object_url = 'http://yun.php300.cn/?c=get&SN=' . $CON['SN'] . '&T=' . $CON['TIME'];
    $cacheclass = @file_get_contents($object_url);
    if ($cacheclass == '') {
        echo ($CON['DEBUG']) ? ('<meta charset="UTF-8">当前SN无云程序或获取云程序失败') : ('');
    }
    $results = json_decode($cacheclass, true);
    if ($results != '0') {
        $code = '';
        if (is_array($results['data'])) {
            foreach ($results['data'] as $val) {
                $code .= urldecode($val['function_content']);
            }
        }
        $cacheclass = "<?php class php300_class{ " . $code . " } ?>";
        fwrite($file_link, $cacheclass);
        fclose($file_link);
        if ($CON['CONFUSION']) {
            $confusion = @php_strip_whitespace($cachefile);
            @file_put_contents($cachefile, $confusion);
        }
    } else {
        fclose($file_link);
        echo ($CON['DEBUG']) ? ($results['msg']) : ('');
    }
}

/**
 * 系统基本常量
 * SystemDefineInfo();
 * 
 */
function SystemDefineInfo() {
	$Path = str_replace('\/','/',dirname($_SERVER['PHP_SELF']) . '/');
    $vals = array(
        '__APP__' => $Path,
        '__HOST__' => $_SERVER['HTTP_HOST'],
        '__PORT__' => $_SERVER["SERVER_PORT"],
        '__TMP__' => $Path . 'Template/',
        '__REFERER__' => $_SERVER['HTTP_REFERER'],
        '__PLUG__' => $Path . 'Libs/Plug/',
        '__Jquery__'=>$Path . 'Libs/Plug/JavaScript/Jquery.js',
        '__Layer__'=>$Path . 'Libs/Plug/Layer/layer.js',
        'C_NAME' => $_GET[C('CLASS_NAME')],
        'F_NAME' => $_GET[C('FUNCTION_NAME')],
    );
    set_define($vals);
}

/**
* 常量预设
* @param 常量数组 $array
* 
* @return
*/
function set_define($array) {
    foreach ($array as $key => $val) {
        if (!defined($key)) {
            define($key, $val);
        }
    }
    G('System_constant', $array, 'PHP300_CON');
}

/**
* 展示错误页
* @param 错误文本 $error
* @param 跳转地址 $url
* @param 等待时间 $seconds
* 
* @return
*/
function error($error = '未知错误', $url = '', $seconds = 3) {
    $info = array(
        'message' => $error,
        'url' => $url,
        'seconds' => $seconds,
        'state' => '0',
    );
    show_state_information($info);
}

/**
* 展示成功页
* @param 错误文本 $success
* @param 跳转地址 $url
* @param 等待时间 $seconds
* 
* @return
*/
function success($success = '操作成功', $url = '', $seconds = 3) {
    $info = array(
        'message' => $success,
        'url' => $url,
        'seconds' => $seconds,
        'state' => '1',
    );
    show_state_information($info);
}

/**
* 展示状态页
* @param 状态配置数组 $info
* 
* @return
*/
function show_state_information($info) {
    if (is_array($info)) {
        M('System')->SetVar('message', $info['message']);
        $info['url'] = $info['url'] != '' ? $info['url'] : __REFERER__;
        $info['url'] = $info['url'] != '' ? $info['url'] : '#';
        M('System')->SetVar('url', $info['url']);
        M('System')->SetVar('seconds', $info['seconds'] * 1000);
        $state = $info['state'] == '1' ? '( ^_^ )' : '(*>﹏<*)';
        M('System')->SetVar('state', $state);
        $GLOBALS['TMP']->left_delimiter = '<{';
        $GLOBALS['TMP']->right_delimiter = '}>';
        M('System')->display('PHP300TMP/State', '2');
        exit();
    }
}

/**
 * 缓存类别名
 * 
 * @return Object
 */
function cache() {
    if (is_object(M('Cache'))) {
        return M('Cache');
    }
    return FALSE;
}

/**
 * 文件类别名
 * 
 * @return Object
 */
function files() {
    if (is_object(M('File'))) {
        return M('File');
    }
    return FALSE;
}

/**
 * cookies类别名
 * 
 * @return Object
 */
function cookies() {
    if (is_object(M('Cookies'))) {
        return M('Cookies');
    }
    return FALSE;
}

/**
 * http类别名
 * 
 * @return Object
 */
function http() {
    if (is_object(M('Http'))) {
        return M('Http');
    }
    return FALSE;
}

/**
 * session类别名
 * 
 * @return Object
 */
function session() {
    if (C('SESSION_START')) {
        if (is_object(M('Session'))) {
            return M('Session');
        }
        return FALSE;
    } else {
        exit('<meta charset="UTF-8">PHP300:抱歉,您在配置中没有启用Session,无法进行操作');
    }
}

/**
 * Socket类别名
 * 
 * @return Object
 */
function socket($type='http',$address='0.0.0.0',$port='2345') {
    if (is_object(M('Socket'))) {
        $socket = M('Socket')->option($type,$address,$port);
        return $socket;
    }
    return FALSE;
}

/**
 * 判断字符是否小写
 * 
 * @return Bool
 */
function checkcase($str) {
    if (preg_match('/^[a-z]+$/', $str)) {
        return true;
    } elseif (preg_match('/^[a-z]+$/', $str)) {
        return false;
    }
}

/**
 * 释放垃圾变量
 */
function ReleaseUseless() {
    $vallist = array('keys', 'replace_arr', 'modelval');
    foreach ($vallist as $val) {
        unset($GLOBALS[$val]);
    }
}

/**
* 渲染模板
* @param 模板文件 $filename
* 
* @return
*/
function show($filename = 'index') {
    if ($filename != '') {
        M('System')->display($filename);
    }
}

/**
* 获取渲染内容
* @param 模板名称 $filename
* 
* @return String
*/
function fetch($filename = 'index') {
    if ($filename != '') {
        $content = M('System')->Fetch($filename);
    }
    return $content;
}

/**
* 单体赋值
* @param 键内容 $key
* @param 值内容 $val
* 
* @return
*/
function assign($key, $val) {
    if ($key != '' and $val != '') {
        M('System')->SetVar($key, $val);
    }
}

/**
* 取文本中间
* @param 欲取文本 $content
* @param 文本左边 $l
* @param 文本右边 $r
* 
* @return String
*/
function middle_string($content,$l,$r){
	$var = explode($l,$content);
	if($var[1]!=''){
		$var = explode($r,$var[1]);
		return ($var[0])?($var[0]):('');
	}
	return '';
}