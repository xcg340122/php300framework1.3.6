<?php

/**
 * @copyright: PHP300Framework
 * @author: Chungui
 */

namespace Libs\Deal;

class Session_class
{
    public $Name = 'PHP300Framework';    //缓存名称

    public $Second = '0';    //缓存周期,单位：秒

    /**
     * 开启session.
     */
    public function start()
    {
        if (!isset($_SESSION)) {
            ini_set('session.name', $this->Name);
            ini_set('session.auto_start', '1');
            ini_set('session.cookie_lifetime', $this->Second);
            session_start();
        }
    }

    /**
     * 获取session.
     *
     * @param 名称 $name
     *
     * @return array or string or object
     */
    public function get($name = '')
    {
        $this->start();
        if (!empty($name)) {
            return (!empty($_SESSION[$name])) ? ($_SESSION[$name]) : (false);
        }

        return $_SESSION;
    }

    /**
     * 设置session.
     *
     * @param 名称 $name
     * @param 值    $value
     *
     * @return array or string or object
     */
    public function set($name = 'php300', $value = '')
    {
        $this->start();
        $_SESSION[$name] = $value;

        return $value;
    }

    /**
     * 删除session.
     *
     * @param 名称 $name
     *
     * @return string
     */
    public function del($name = '')
    {
        $this->start();
        if (empty($name)) {
            session_destroy();
        } else {
            $_SESSION[$name] = null;
        }

        return $name;
    }
}
