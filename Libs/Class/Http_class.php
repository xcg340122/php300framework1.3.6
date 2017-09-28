<?php

/**
 * @copyright: PHP300Framework
 * @author: Chungui
 */

namespace Libs\Deal;

class Http_class
{
    /**
     * 请求标识.
     */
    public $useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en)';

    /**
     * 是否创建临时文件.
     */
    public $useRandomCookieFile = false;

    /**
     * 临时文件后缀
     */
    public $randomCookieFilePrefix = 'phphc';

    protected $_cookieFile = null;

    public $lastpageFile = null;

    protected $defaults = [
        'url'           => '',
        'post'          => null,
        'headers'       => null,
        'ref'           => '',

        'header'        => false,
        'nobody'        => false,
        'timeout'       => 15,

        'tofile'        => null,

        'attempts_max'  => 1,
        'attempts_delay'=> 10,

        'curl'           => [],
    ];

    protected $ch;

    /**
     * 初始化.
     */
    public function init()
    {
        if ($this->useRandomCookieFile) {
            $this->setRandomCookieFile();
        }
    }

    public static function from($params = [])
    {
        $client = new self();

        foreach ($params as $key => $val) {
            $client->$key = $val;
        }

        $client->init();

        return $client;
    }

    public function __get($name)
    {
        $getter = 'get'.$name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        throw new Exception('Property "'.get_class($this).'.'.$name.'" is not defined.');
    }

    /**
     * 运行http请求响应标头。
     *
     * @param string $url    request url.
     * @param array  $params request params.
     *
     * @return string|bool 通常情况下返回响应, 如果是true则结果到文件等于false则请求失败.
     */
    public function head($url, $params = [])
    {
        $params['url'] = $url;
        $params['header'] = true;
        $params['nobody'] = true;

        return $this->request($params);
    }

    /**
     * 运行http GET请求。
     *
     * @param string $url    request url.
     * @param array  $params request params.
     *
     * @return string|bool 通常情况下返回响应, 如果是true则结果到文件等于false则请求失败.
     * @抛出CException当“去整理”是定义和文件不是可写.
     */
    public function get($url, $params = [])
    {
        $params['url'] = $url;

        return $this->request($params);
    }

    /**
     * 运行http POST请求。
     *
     * @param string $url    request url.
     * @param array  $post   post data.
     * @param array  $params request params.
     *
     * @return string|bool 通常情况下返回响应, 如果是true则结果到文件等于false则请求失败.
     */
    public function post($url, $post = [], $params = [])
    {
        $params['url'] = $url;
        $params['post'] = $post;

        return $this->request($params);
    }

    /**
     * 下载文件.
     *
     * @param string $url    request url.
     * @param string $dest   file destination.
     * @param array  $params request params.
     *
     * @return bool true 下载成功 false 下载失败
     */
    public function download($url, $dest, $params = [])
    {
        $params['url'] = $url;
        $params['tofile'] = $dest;

        return $this->request($params);
    }

    /**
     * 运行http请求。
     *
     * @param array $params request params.
     *
     * @return string|bool returns response in the usual case, true when
     *                     结果到文件和假如果请求失败。
     */
    public function request($params)
    {
        $params += $this->defaults;

        if (isset($this->ch)) {
            curl_close($this->ch);
            $this->ch = null;
        }

        $ch = $this->createCurl($params);

        if (isset($params['tofile'])) {
            $tofile = fopen($params['tofile'], 'wb');

            if (!$tofile) {
                throw new CException(__CLASS__." couldn't open file '{$params['tofile']}' for edit.");
            }
            curl_setopt($ch, CURLOPT_FILE, $tofile);
        }

        do {
            // 用于http请求
            $res = curl_exec($ch);
        } while (
            $res === false && //
            --$params['attempts_max'] != 0 &&
            sleep($params['attempts_delay']) !== false
        );

        if (isset($params['tofile'])) {
            fclose($tofile);
            if ($res === false) {
                unlink($params['tofile']);
            }
        }

        $this->ch = $ch;

        // 保存请求内容到文件
        if ($this->lastpageFile != null) {
            file_put_contents($this->lastpageFile, $res);
        }

        return $res;
    }

    /**
     * 创建多个请求
     *
     * @param array $requests requests parameters [key] => [params array]
     * @param array $defaults default request paremeters
     *
     * @return array http request results array [key] => [result string]
     *               请求数组键用于不同的结果
     */
    public function multiRequest($requests, $defaults = [])
    {
        if (empty($requests)) {
            return [];
        }

        $defaults += $this->defaults;

        $mh = curl_multi_init();

        $handles = [];

        foreach ($requests as $key => $request) {
            $ch = $this->createCurl($request + $defaults);

            curl_multi_add_handle($mh, $ch);

            $handles[$key] = $ch;
        }

        $active = null;

        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $results = [];
        foreach ($handles as $key => $ch) {
            $results[$key] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);

        return $results;
    }

    protected function createCurl($params)
    {
        $options = [
            CURLOPT_URL           => $params['url'],
            CURLOPT_HEADER        => $params['header'],
            CURLOPT_TIMEOUT       => $params['timeout'],
            CURLOPT_USERAGENT     => $this->useragent,
            CURLOPT_RETURNTRANSFER=> !isset($params['tofile']),
            CURLOPT_NOBODY        => $params['nobody'],
            CURLOPT_FOLLOWLOCATION=> 1,
            CURLOPT_ENCODING      => '',
        ];

        if (!empty($params['ref'])) {
            $options[CURLOPT_REFERER] = $params['ref'];
        }

        if ($params['post'] !== null) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params['post'];
        }

        if ($params['headers'] !== null) {
            $options[CURLOPT_HTTPHEADER] = $params['headers'];
        }

        $cookieFile = $this->getCookieFile();
        if ($cookieFile !== null) {
            $options[CURLOPT_COOKIEFILE] = $cookieFile;
            $options[CURLOPT_COOKIEJAR] = $cookieFile;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $params['curl'] + $options);

        return $ch;
    }

    // Getters #

    public function getCookieFile()
    {
        return $this->_cookieFile;
    }

    /**
     * 返回错误列表.
     *
     * @return string
     */
    public function getLastError()
    {
        return isset($this->ch) ? curl_error($this->ch) : null;
    }

    /**
     * 返回历史请求信息.
     *
     * @see curl_getinfo
     *
     * @param int $opt
     *
     * @return mixed
     */
    public function getInfo($opt = null)
    {
        return isset($this->ch) ? curl_getinfo($this->ch, $opt) : null;
    }

    /**
     * 历史http请求列表.
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->getInfo(CURLINFO_HTTP_CODE);
    }

    /**
     * 最后一个请求的url.
     *
     * @return string
     */
    public function getEffectiveUrl()
    {
        return $this->getInfo(CURLINFO_EFFECTIVE_URL);
    }

    /**
     * 历史cookies
     * 警告,不可以使用 getInfo() 和 getLastError() 这个调用函数获取内容.
     *
     * @return array
     */
    public function getCookies()
    {
        if (!$this->getCookieFile()) {
            return [];
        }
        unset($this->ch);
        $text = file_get_contents($this->getCookieFile());

        $cookies = [];
        foreach (explode("\n", $text) as $line) {
            $parts = explode("\t", $line);
            if (count($parts) === 7) {
                $cookies[$parts[5]] = $parts[6];
            }
        }

        return $cookies;
    }

    /**
     * 设置Cookies文件.
     *
     * @param string $fname
     * @param string $clear.
     *
     * @return
     */
    public function setCookieFile($fname, $clear = true)
    {
        $this->_cookieFile = $fname;

        if ($clear) {
            $this->clearCookieFile();
        }
    }

    /**
     * 设置随机Cookies文件.
     *
     * @return
     */
    public function setRandomCookieFile()
    {
        $fileName = tempnam(sys_get_temp_dir(), $this->randomCookieFilePrefix);
        $this->setCookieFile($fileName, true);
    }

    /**
     * 清空cookies的文件.
     */
    public function clearCookieFile()
    {
        $cookieFile = $this->getCookieFile();
        if ($cookieFile !== null) {
            file_put_contents($cookieFile, '');
        }
    }

    public function __destruct()
    {
        unset($this->ch);

        $cookieFile = $this->getCookieFile();
        if ($cookieFile !== null) {
            unlink($cookieFile);
        }
    }
}
