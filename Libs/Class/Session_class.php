<?php

/**
* @copyright: PHP300Framework
* @author: Chungui
* 
*/

namespace Libs\Deal;

class Session {
	
	/**
	* 启动Session
	* 
	*/
    static function start() {
    	if (!isset($_SESSION)) { session_start();}
        if (!isset($_SESSION['__HTTP_Session_Info'])) {
            $_SESSION['__HTTP_Session_Info'] = 1;
        } else {
            $_SESSION['__HTTP_Session_Info'] = 2;
        }
        Session::setExpire('3600');
    }

    /**
	* 暂停Session
	* 
	*/
    static function pause() {
        session_write_close();
    }

    /**
	* 清空Session
	* 
	*/
    static function clearLocal() {
        $local = Session::localName();
        unset($_SESSION[$local]);
    }

    /**
	* 清空Session
	* 
	*/
    static function clear() {
        $_SESSION = array();
    }

    /**
	* 销毁Session
	* 
	*/
    static function destroy() {
        unset($_SESSION);
        session_destroy();
    }

    /**
	* 检测SessionID
	* 
	*/
    static function detectID() {
        if(session_id()!='') {
            return session_id();
        }
        if (Session::useCookies()) {
            if (isset($_COOKIE[Session::name()])) {
                return $_COOKIE[Session::name()];
            }
        } else {
            if (isset($_GET[Session::name()])) {
                return $_GET[Session::name()];
            }
            if (isset($_POST[Session::name()])) {
                return $_POST[Session::name()];
            }
        }
        return null;
    }

    /**
	* 设置或者获取当前Session name
	* @param name $name
	* 
	*/
    static function name($name = null) {
        return isset($name) ? session_name($name) : session_name();
    }

   /**
   * 设置或者获取当前SessionID
   * @param id $id
   * 
   */
    static function id($id = null) {
        return isset($id) ? session_id($id) : session_id();
    }

    /**
	* 设置或者获取当前Session保存路径
	* @param 路径 $path
	* 
	*/
    static function path($path = null) {
        return !empty($path)? session_save_path($path):session_save_path();
    }

    /**
	* 设置Session 过期时间
	* @param 时间 $time
	* @param 是否记录时间 $add
	* 
	*/
    static function setExpire($time, $add = false) {
        if ($add) {
            if (!isset($_SESSION['__HTTP_Session_Expire_TS'])) {
                $_SESSION['__HTTP_Session_Expire_TS'] = time() + $time;
            }

            // update session.gc_maxlifetime
            $currentGcMaxLifetime = Session::setGcMaxLifetime(null);
            Session::setGcMaxLifetime($currentGcMaxLifetime + $time);

        } elseif (!isset($_SESSION['__HTTP_Session_Expire_TS'])) {
                $_SESSION['__HTTP_Session_Expire_TS'] = $time;
        }
    }

    /**
	* 设置Session 闲置时间
	* @param 时间 $time
	* 
	*/
    static function setIdle($time, $add = false) {
        if ($add) {
            $_SESSION['__HTTP_Session_Idle'] = $time;
        } else {
            $_SESSION['__HTTP_Session_Idle'] = $time - time();
        }
    }

    /**
	* 取得Session 有效时间
	* 
	*/
    static function sessionValidThru() {
        if (!isset($_SESSION['__HTTP_Session_Idle_TS']) || !isset($_SESSION['__HTTP_Session_Idle'])) {
            return 0;
        } else {
            return $_SESSION['__HTTP_Session_Idle_TS'] + $_SESSION['__HTTP_Session_Idle'];
        }
    }

    /**
	* 检查Session 是否过期
	* 
	*/
    static function isExpired() {
        if (isset($_SESSION['__HTTP_Session_Expire_TS']) && $_SESSION['__HTTP_Session_Expire_TS'] < time()) {
            return true;
        } else {
            return false;
        }
    }

    /**
	* 检查Session 是否闲置
	* 
	*/
    static function isIdle() {
        if (isset($_SESSION['__HTTP_Session_Idle_TS']) && (($_SESSION['__HTTP_Session_Idle_TS'] + $_SESSION['__HTTP_Session_Idle']) < time())) {
            return true;
        } else {
            return false;
        }
    }

    /**
	* 更新Session 闲置时间
	* 
	*/
    static function updateIdle() {
        $_SESSION['__HTTP_Session_Idle_TS'] = time();
    }

    /**
	* 设置Session 对象反序列化时候的回调函数,返回之前设置
	* @param 函数 $callback
	* 
	*/
    static function setCallback($callback = null) {
        $return = ini_get('unserialize_callback_func');
        if (!empty($callback)) {
            ini_set('unserialize_callback_func',$callback);
        }
        return $return;
    }

    /**
	* 设置Session 是否使用cookie
	* @param 引用cookie $useCookies
	* 
	*/
    static function useCookies($useCookies = null) {
        $return = ini_get('session.use_cookies') ? true : false;
        if (isset($useCookies)) {
            ini_set('session.use_cookies', $useCookies ? 1 : 0);
        }
        return $return;
    }

    /**
	* 检查Session 是否新建
	* 
	*/
    static function isNew() {
        return !isset($_SESSION['__HTTP_Session_Info']) ||
            $_SESSION['__HTTP_Session_Info'] == HTTP_SESSION_STARTED;
    }


    /**
	* 取得当前项目的Session 值
	* @param session $name
	* 
	*/
    static function getLocal($name) {
        $local = Session::localName();
        if (!is_array($_SESSION[$local])) {
            $_SESSION[$local] = array();
        }
        return $_SESSION[$local][$name];
    }

    /**
	* 取得当前项目的Session 值
	* @param 名称 $name
	* 
	*/
    static function get($name) {
        if(isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }else {
            return null;
        }
    }

    /**
	* 设置当前项目的Session 值
	* @param 名称 $name
	* @param 值 $value
	* 
	*/
    static function setLocal($name, $value) {
        $local = Session::localName();
        if (!is_array($_SESSION[$local])) {
            $_SESSION[$local] = array();
        }
        if (null === $value) {
            unset($_SESSION[$local][$name]);
        } else {
            $_SESSION[$local][$name] = $value;
        }
        return;
    }

    /**
	* 设置当前项目的Session 值
	* @param 名称 $name
	* @param 值 $value
	* 
	*/
    static function set($name, $value) {
        if (null === $value) {
            unset($_SESSION[$name]);
        } else {
            $_SESSION[$name] = $value;
        }
        return ;
    }

    /**
	* 检查Session 值是否已经设置
	* @param 名称 $name
	* 
	*/
    static function is_setLocal($name) {
        $local = Session::localName();
        return isset($_SESSION[$local][$name]);
    }

    /**
	* 检查Session 值是否已经设置
	* @param 名称 $name
	* 
	*/
    static function is_set($name) {
        return isset($_SESSION[$name]);
    }

    /**
	* 设置或者获取 Session localname
	* @param 名称 $name
	* 
	*/
    static function localName($name = null) {
        $return = (isset($GLOBALS['__HTTP_Session_Localname'])) ? $GLOBALS['__HTTP_Session_Localname'] : null;
        if (!empty($name)) {
            $GLOBALS['__HTTP_Session_Localname'] = md5($name);
        }
        return $return;
    }

    /**
	* Session 初始化
	* 
	*/
    static function _init($Config) {
        ini_set('session.auto_start', 0);
        if (is_null(Session::detectID())) {
            Session::id(uniqid(dechex(mt_rand())));
        }
        // 设置Session有效域名
        Session::setCookieDomain($Config['Cookie.domain']);
        //设置当前项目运行脚本作为Session本地名
        Session::localName('PHP300');
        Session::name($Config['Session_name']);
        Session::path($Config['Session_path']);
        Session::setCallback($Config['Session_callback']);
    }

    /**
	* 设置Session use_trans_sid
	* @param 跨页传递 $useTransSID
	* 
	*/
    static function useTransSID($useTransSID = null) {
        $return = ini_get('session.use_trans_sid') ? true : false;
        if (isset($useTransSID)) {
            ini_set('session.use_trans_sid', $useTransSID ? 1 : 0);
        }
        return $return;
    }

    /**
	* 设置Session cookie_domain
	* @param session域 $sessionDomain
	* 
	*/
    static function setCookieDomain($sessionDomain = null) {
        $return = ini_get('session.cookie_domain');
        if(!empty($sessionDomain)) {
            ini_set('session.cookie_domain', $sessionDomain);//跨域访问Session
        }
        return $return;
    }


    /**
	* 设置Session gc_maxlifetime值
	* @param 最大值 $gcMaxLifetime
	* 
	*/
    static function setGcMaxLifetime($gcMaxLifetime = null) {
        $return = ini_get('session.gc_maxlifetime');
        if (isset($gcMaxLifetime) && is_int($gcMaxLifetime) && $gcMaxLifetime >= 1) {
            ini_set('session.gc_maxlifetime', $gcMaxLifetime);
        }
        return $return;
    }

    /**
	* 设置Session gc_probability 值
	* @param undefined $gcProbability
	* 
	*/
    static function setGcProbability($gcProbability = null) {
        $return = ini_get('session.gc_probability');
        if (isset($gcProbability) && is_int($gcProbability) && $gcProbability >= 1 && $gcProbability <= 100) {
            ini_set('session.gc_probability', $gcProbability);
        }
        return $return;
    }

    /**
	* 当前Session文件名
	* 
	*/
    static function getFilename() {
        return Session::path().'/sess_'.session_id();
    }
}