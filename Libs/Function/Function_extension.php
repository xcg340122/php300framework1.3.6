<?php

/**
 * 获取客户端IP
 * @return string
 */
function getIP() {
    if (getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
    else
        $ip = "Unknow";
    return $ip;
}

/**
 * 获取浏览器类型(0=未知,1=IE,2=火狐,3=QQ,4=UC,5=Edge,6=谷歌,7=Opera,8=Safari,9=微信浏览器)
 * @return int
 */
function getAgentInfo() {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $brower = array(
        'MSIE' => 1,
        'Firefox' => 2,
        'QQBrowser' => 3,
        'QQ/' => 3,
        'UCBrowser' => 4,
        'MicroMessenger' => 9,
        'Edge' => 5,
        'Chrome' => 6,
        'Opera' => 7,
        'OPR' => 7,
        'Safari' => 8,
        'Trident/' => 1
    );
    $browser_num = 0; //未知 
    foreach ($brower as $bro => $val) {
        if (stripos($agent, $bro) !== false) {
            $browser_num = $val;
            break;
        }
    }
    return $browser_num;
}

/**
 * 请求类型
 *@return String
 */
function get_Request(){
    return strtolower($_SERVER['REQUEST_METHOD']);
}

/**
 * 是否为POST
 * @return bool
 */
function is_Post(){
    return (get_Request()=='post')?(TRUE):(FALSE);
}

/**
 * 是否为GET
 * @return bool
 */
function is_Get(){
    return (get_Request()=='get')?(TRUE):(FALSE);
}