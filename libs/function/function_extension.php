<?php
/**
* 获取客户端IP
* @return string
*/
function getIP(){ 
	if (getenv("HTTP_CLIENT_IP")) 
	$ip = getenv("HTTP_CLIENT_IP"); 
	else if(getenv("HTTP_X_FORWARDED_FOR")) 
	$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if(getenv("REMOTE_ADDR")) 
	$ip = getenv("REMOTE_ADDR"); 
	else $ip = "Unknow"; 
	return $ip; 
}

/**
* 是否是IE浏览器
* @return string
*/
function is_ie(){
    $type_web = $_SERVER['HTTP_USER_AGENT'];
    $tmp1 = explode("comp",$type_web); 
    $tmp2 = explode("IE",$tmp1[1]); 
    if($tmp2[0]=="atible; MS"){
        return true; 
    }
    return false; 
} 