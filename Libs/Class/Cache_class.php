<?php
/**
 *  cache_class.php 缓存操作类
 *
 * @copyright			(C) 2015-2016 PHP300
 * @license				http://framework.php300.cn
 * @lastmodify			2016-10-11
 */
class Cache_class extends System_class {

  private $_cachepath = 'Cache/';

  private $_cachename = 'default';

  private $_extension = '.cache';

  public function isCached($key) {
    if (false != $this->_loadCache()) {
      $cachedData = $this->_loadCache();
      return isset($cachedData[$key]['data']);
    }
  }

  public function set($key, $data, $expiration = 0) {
    $storeData = array(
      'time'   => time(),
      'expire' => $expiration,
      'data'   => serialize($data)
    );
    $dataArray = $this->_loadCache();
    if (true === is_array($dataArray)) {
      $dataArray[$key] = $storeData;
    } else {
      $dataArray = array($key => $storeData);
    }
    $cacheData = json_encode($dataArray);
	$handle = fopen($this->getCacheDir(),'a+');
	fwrite($handle,$cacheData);
	fclose($handle);
    return $this;
  }

  public function get($key, $timestamp = false) {
    $cachedData = $this->_loadCache();
    (false === $timestamp) ? $type = 'data' : $type = 'time';
    if (!isset($cachedData[$key][$type])) return null; 
    return unserialize($cachedData[$key][$type]);
  }

  public function getAll($meta = false) {
    if ($meta === false) {
      $results = array();
      $cachedData = $this->_loadCache();
      if ($cachedData) {
        foreach ($cachedData as $k => $v) {
          $results[$k] = unserialize($v['data']);
        }
      }
      return $results;
    } else {
      return $this->_loadCache();
    }
  }

  public function del($key) {
    $cacheData = $this->_loadCache();
    if (true === is_array($cacheData)) {
      if (true === isset($cacheData[$key])) {
        unset($cacheData[$key]);
        $cacheData = json_encode($cacheData);
        file_put_contents($this->getCacheDir(), $cacheData);
      }
    }
    return $this;
  }

  public function delExpired() {
    $cacheData = $this->_loadCache();
    if (true === is_array($cacheData)) {
      $counter = 0;
      foreach ($cacheData as $key => $entry) {
        if (true === $this->_checkExpired($entry['time'], $entry['expire'])) {
          unset($cacheData[$key]);
          $counter++;
        }
      }
      if ($counter > 0) {
        $cacheData = json_encode($cacheData);
        file_put_contents($this->getCacheDir(), $cacheData);
      }
      return $counter;
    }
  }

  public function delAll() {
    $cacheDir = $this->getCacheDir();
    if (true === file_exists($cacheDir)) {
      $cacheFile = fopen($cacheDir, 'w');
      fclose($cacheFile);
    }
    return $this;
  }

  private function _loadCache() {
    if (true === file_exists($this->getCacheDir())) {
      $file = file_get_contents($this->getCacheDir());
      return json_decode($file, true);
    } else {
      return false;
    }
  }

  public function getCacheDir() {
    if (true === $this->_checkCacheDir()) {
      $filename = $this->getCache();
      $filename = preg_replace('/[^0-9a-z\.\_\-]/i', '', strtolower($filename));
      return $this->getCachePath() . $this->_getHash($filename) . $this->getExtension();
    }
  }

  private function _getHash($filename) {
    return sha1($filename);
  }

  private function _checkExpired($timestamp, $expiration) {
    $result = false;
    if ($expiration !== 0) {
      $timeDiff = time() - $timestamp;
      ($timeDiff > $expiration) ? $result = true : $result = false;
    }
    return $result;
  }

  private function _checkCacheDir() {
    if (!is_dir($this->getCachePath()) && !mkdir($this->getCachePath(), 0775, true)) {
      throw new Exception('Unable to create cache directory ' . $this->getCachePath());
    } elseif (!is_readable($this->getCachePath()) || !is_writable($this->getCachePath())) {
      if (!chmod($this->getCachePath(), 0775)) {
        throw new Exception($this->getCachePath() . ' must be readable and writeable');
      }
    }
    return true;
  }

  public function setCachePath($path) {
    $this->_cachepath = $path;
    return $this;
  }


  public function getCachePath() {
    return $this->_cachepath;
  }

  public function setCache($name) {
    $this->_cachename = $name;
    return $this;
  }

  public function getCache() {
    return $this->_cachename;
  }

  public function setExtension($ext) {
    $this->_extension = $ext;
    return $this;
  }

  public function getExtension() {
    return $this->_extension;
  }

}
