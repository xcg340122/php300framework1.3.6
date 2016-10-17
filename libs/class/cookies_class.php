<?php 
/**
 *  cookies_class.php COOKIES操作类
 *
 * @copyright			(C) 2015-2016 PHP300
 * @license				http://framework.php300.cn
 * @lastmodify			2016-10-09
 */
class cookies_class extends system_class{
  
  private $_prefix = '';
  private $_securekey = 'ekOt4_Ut0f3XE-fJcpBvRFrg506jpcuJeixezgPNyALm';
  private $_expire = 3600;
  
  public function __construct($prefix='', $expire=0, $securekey=''){
  
    if(is_string($prefix) && $prefix!=''){ 
      $this->_prefix = $prefix; 
    } 
  
    if(is_numeric($expire) && $expire>0){ 
      $this->_expire = $expire; 
    } 
  
    if(is_string($securekey) && $securekey!=''){ 
      $this->_securekey = $securekey; 
    } 
  
  } 
  
  public function set($name, $value, $expire=0){ 
  
    $cookie_name = $this->getName($name); 
    $cookie_expire = time() + ($expire? $expire : $this->_expire); 
    $cookie_value = $this->pack($value, $cookie_expire); 
    $cookie_value = $this->authcode($cookie_value, 'ENCODE', $this->_securekey); 
  
    if($cookie_name && $cookie_value && $cookie_expire){ 
      setcookie($cookie_name, $cookie_value, $cookie_expire); 
    } 
  
  } 
  
  public function get($name){ 
  
    $cookie_name = $this->getName($name); 
  
    if(isset($_COOKIE[$cookie_name])){ 
  
      $cookie_value = $this->authcode($_COOKIE[$cookie_name], 'DECODE', $this->_securekey); 
      $cookie_value = $this->unpack($cookie_value); 
  
      return isset($cookie_value[0])? $cookie_value[0] : null; 
  
    }else{ 
      return null; 
    } 
  
  } 
  
  public function update($name, $value){ 
  
    $cookie_name = $this->getName($name); 
  
    if(isset($_COOKIE[$cookie_name])){ 
  
      $old_cookie_value = $this->authcode($_COOKIE[$cookie_name], 'DECODE', $this->_securekey); 
      $old_cookie_value = $this->unpack($old_cookie_value); 
  
      if(isset($old_cookie_value[1]) && $old_cookie_vlaue[1]>0){ // 获取之前的过期时间 
  
        $cookie_expire = $old_cookie_value[1]; 
  
        // 更新数据 
        $cookie_value = $this->pack($value, $cookie_expire); 
        $cookie_value = $this->authcode($cookie_value, 'ENCODE', $this->_securekey); 
  
        if($cookie_name && $cookie_value && $cookie_expire){ 
          setcookie($cookie_name, $cookie_value, $cookie_expire); 
          return true; 
        } 
      } 
    } 
    return false; 
  } 
  
  public function clear($name){ 
  
    $cookie_name = $this->getName($name); 
    setcookie($cookie_name); 
  } 
  
  public function setPrefix($prefix){ 
  
    if(is_string($prefix) && $prefix!=''){ 
      $this->_prefix = $prefix; 
    } 
  } 
  
  public function setExpire($expire){ 
  
    if(is_numeric($expire) && $expire>0){ 
      $this->_expire = $expire; 
    } 
  } 
  
  private function getName($name){ 
    return $this->_prefix? $this->_prefix.'_'.$name : $name; 
  } 
  
  private function pack($data, $expire){ 
  
    if($data===''){ 
      return ''; 
    } 
  
    $cookie_data = array(); 
    $cookie_data['value'] = $data; 
    $cookie_data['expire'] = $expire; 
    return json_encode($cookie_data); 
  } 
  
  private function unpack($data){ 
  
    if($data===''){ 
      return array('', 0); 
    } 
  
    $cookie_data = json_decode($data, true); 
  
    if(isset($cookie_data['value']) && isset($cookie_data['expire'])){ 
  
      if(time()<$cookie_data['expire']){ // 未过期 
        return array($cookie_data['value'], $cookie_data['expire']); 
      } 
    } 
    return array('', 0); 
  } 
  
  private function authcode($string, $operation = 'DECODE'){ 
  
    $ckey_length = 4;  // 随机密钥长度 取值 0-32; 
  
    $key = $this->_securekey; 
  
    $key = md5($key); 
    $keya = md5(substr($key, 0, 16)); 
    $keyb = md5(substr($key, 16, 16)); 
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
  
    $cryptkey = $keya.md5($keya.$keyc); 
    $key_length = strlen($cryptkey); 
  
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', 0).substr(md5($string.$keyb), 0, 16).$string; 
    $string_length = strlen($string); 
  
    $result = ''; 
    $box = range(0, 255); 
  
    $rndkey = array(); 
    for($i = 0; $i <= 255; $i++) { 
      $rndkey[$i] = ord($cryptkey[$i % $key_length]); 
    } 
  
    for($j = $i = 0; $i < 256; $i++) { 
      $j = ($j + $box[$i] + $rndkey[$i]) % 256; 
      $tmp = $box[$i]; 
      $box[$i] = $box[$j]; 
      $box[$j] = $tmp; 
    } 
  
    for($a = $j = $i = 0; $i < $string_length; $i++) { 
      $a = ($a + 1) % 256; 
      $j = ($j + $box[$a]) % 256; 
      $tmp = $box[$a]; 
      $box[$a] = $box[$j]; 
      $box[$j] = $tmp; 
      $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256])); 
    } 
  
    if($operation == 'DECODE') { 
      if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) { 
        return substr($result, 26); 
      } else { 
        return ''; 
      } 
    } else { 
      return $keyc.str_replace('=', '', base64_encode($result)); 
    } 
  } 
}
?> 