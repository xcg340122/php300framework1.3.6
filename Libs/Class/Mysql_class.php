<?php

namespace Libs\Deal;

class Mysql {
	
	private $config;
	
	private $queryid;
	
	private $link = null;
	
	private $sql;
	
	private $db;
	
	private $key;
	
	private $fields;
	
	private $data;
	
	private $page;
	
	private $deal = array(
		'join' => '',
		'where' => '',
		'field' => '',
		'union' => '',
		'group' => '',
		'order' => '',
		'limit' => '',
		'page' => '',
		'alias' => '',
	);
	
	private $handle = array('join','where','field','union','group','order','limit','page','alias');
	
	/**
	* 配置项
	* @param 配置数组 $config
	* 
	*/
	function option($config){
		if(is_array($config)){
			$this->config = $config;
		}
	}
	
	/**
	* 连接数据库
	* 
	*/
	function Connect(){
		$this->link  = @mysqli_connect($this->config['Host'] . ':' . $this->config['Port'], $this->config['Username'], $this->config['Password'], $this->config['DataBase']);
		if($this->link != null){
			mysqli_query($this->link, "set names " . $this->config['Char']);
        	return $this->link;
		}else{
			Error('PHP::Mysql连接失败!');
		}
	}
	
	/**
	* 选择数据库
	* @param 数据库 $Db
	* 
	*/
	public function SelectDb($Db=''){
		if($this->link!=NULL){
			if(empty($Db)){
				$TableList = $this->getTable();
				if($TableList[0]){
					$this->db = $TableList[0];
				}
			}else{
				$this->db = $this->config['Prefix'].$Db;
			}
			$this->orderField();
			return $this;
		}
	}
	
	/**
	* 排序字段
	* 
	*/
	public function orderField() {
        if ($this->db != '') {
            $Res = $this->getFields($this->db);
            $ResArr = array();
            foreach ($Res as $key => $val) {
                if ($val['Default'] == NULL) {
                    $ResArr[$key] = '';
                } else {
                    $ResArr[$key] = $val['Default'];
                }
                if ($val['Key'] == 'PRI') {
                    $this->key = $key;
                    unset($ResArr[$key]);
                }
            }
        }
        $this->data = $ResArr;
    }
	
	/**
	* 执行SQL
	* @param sql语句 $sql
	* 
	*/
	private function execute($sql) {
		if($this->link != null){
			$this->queryid = mysqli_query($this->link, $sql);
			$Status = ($this->queryid)?('Success'):('Error');
			if($this->config['Logs']){ Logs('PHP300SQL['.$Status.']::'.$sql,'Mysql');}
			if($this->config['Debug']){  Error('SQL执行失败：'.$sql.'<br />错误反馈:['.$this->Error().']'); }
        	return $this->queryid;
		}else{
			Error('PHP300::获取数据连接信息失效,请检查配置文件或目标主机状态!');
		}
    }
    
    /**
	* 执行SQL
	* @param sql语句 $sql
	* 
	*/
    function query($sql){
		$this->execute($sql);
	}
	
	/**
	* 结果集下一个
	* 
	*/
	function fetchNext(){
		if($this->queryid){
			$Res = mysqli_fetch_array($this->queryid, MYSQLI_ASSOC);
	        if (!$Res) {
	            $this->freeResult();
	        }
	        return $Res;
		}
	}
	
	/**
	* 结果集记录
	* 
	*/
	function freeResult() {
        if (is_resource($this->queryid)) {
            mysqli_free_result($this->queryid);
            $this->queryid = null;
        }
    }
    
    /**
	* 插入数据
	* @param 数据 $data
	* 
	* @return
	*/
	public function insert($data = array()) {
        if (is_array($data)) {
        	$keys='';$vals='';
        	foreach ($data as $key => $val) {
                $this->data[$key] = $val;
            }
            foreach ($this->data as $key => $val) {
                $keys .= $this->addSpecialChar($key) . ',';
                $vals .= "'" . $val . "',";
            }
            $keys = trim($keys, '.,');$vals = trim($vals, '.,');
            $this->sql = 'INSERT INTO ' . $this->db . ' (' . $keys . ')VALUES(' . $vals . ')';
            return $this->execute($this->sql);
        } else {
            return FALSE;
        }
    }
    
    /**
	* 插入数据(快捷)
	*/
	public function add($data = array()) {
        return $this->insert($data);
    }
    
    /**
	* 获取最后插入的ID
	* 
	*/
    public function insert_id() {
        return mysqli_insert_id($this->link);
    }
    
    /**
	* 更新数据
	* @param 数据 $data
	* 
	*/
    public function update($data = array()) {
        foreach ($data as $key => $val) {
            $vals .= $key . '=' . "'".$val."'";
        }
        $this->sql = 'UPDATE ' . $this->db . ' set ' . $vals . $this->deal['where'];
        return $this->execute($this->sql);
    }
	
	/**-+
	* 更新数据(快捷)
	* @param 数据 $data
	* 
	*/
	public function save($data = array()) {
        return $this->update($data);
    }
    
    /**
	* 查询数据
	* 
	*/
    public function select() {
        $this->linkSql();
        if ($this->sql != '') {
            $this->execute($this->sql);$DataList = array();
            while (($Res = $this->fetchNext()) != false) {
                 $DataList[] = $Res;
            }
            $this->freeResult();
        }
        return (is_array($DataList))?($DataList):(array());
    }
    
    /**
	* 查询单个数据
	* @param 主键 $key
	* 
	*/
    public function find($key = '') {
        if (is_numeric($key)) {
            $this->where = 'where ' . $this->key . ' = ' . $key . ' ';
        }
        $this->linkSql();
        $this->execute($this->sql);
        $Res = $this->fetchNext();
        $this->freeResult();    
        return (is_array($Res))?($Res):(array());
    }
    
    /**
	* 删除数据
	* @param 主键 $key
	* 
	*/
    public function delete($key = '') {
        if (is_numeric($key)) {
            $this->deal['where'] = 'where ' . $this->key . ' = ' . $key . ' ';
        }
        $this->linkSql('delete');
        $Res = $this->execute($this->sql);
        return $Res;
    }
    
    /**
	* 删除数据(快捷)
	* 
	*/
	public function del($key = ''){
		$this->key($key);
	}
    
    
    /**
	* 返回影响记录
	* 
	*/
    public function affectedRows() {
        return mysqli_affected_rows($this->link);
    }
    
    /**
	* 获取主键
	* 
	*/
    public function getPrimary($table) {
        $this->execute("SHOW COLUMNS FROM ".$table);
        while ($Next = $this->fetchNext()) {
            if ($Next['Key'] == 'PRI'){
				break;
			}  
        }
        return $Next['Field'];
    }
    
    /**
	* 获取字段列表
	* 
	*/
    public function getFields($table) {
        $fields = array();
        $this->execute('SHOW COLUMNS FROM `' .$table.'`');
        while ($Next = $this->fetchNext()) {
            $fields[$Next['Field']] = $Next;
        }
        return $fields;
    }
    
    /**
	* 获取所有表
	* 
	*/
    public function getTable() {
    	echo 'getTable';
        $fields = array();
        $this->execute("SHOW tables");
        $table = array();
        while ($Next = $this->fetchNext()) {
            array_push($table, $Next['Tables_in_' . $this->config['DataBase']]);
        }
        return $table;
    }
    
    /**
	* 检测字段
	* @param 数据表 $table
	* @param 字段列表 $array
	* 
	*/
    public function checkFields($table, $array) {
        $fields = $this->getFields($table);
        $nofields = array();
        foreach ($array as $val) {
            if (!array_key_exists($val, $fields)) {
                $nofields[] = $val;
            }
        }
        return $nofields;
    }
    
    /**
	* 表是否存在
	* @param 数据表 $table
	* 
	*/
    public function tableExists($table) {
        $tables = $this->getTable();
        return in_array($table, $tables) ? 1 : 0;
    }
    
    /**
	* 字段是否存在
	* @param 数据表 $table
	* @param 字段 $field
	* 
	*/
    public function fieldExists($table, $field) {
        $fields = $this->getFields($table);
        return array_key_exists($field, $fields);
    }
    
    /**
	* 获取条数
	* @param sql语句 $sql
	* 
	*/
    public function NumRows($sql) {
        $this->queryid = $this->execute($sql);
        return mysqli_num_rows($this->queryid);
    }
    
    /**
	* 获取字段数
	* @param sql语句 $sql
	* 
	*/
    public function NumFields($sql) {
        $this->queryid = $this->execute($sql);
        return mysqli_num_fields($this->queryid);
    }
    
    /**
	* 返回错误内容
	* 
	*/
    public function Error() {
        return @mysqli_error($this->link);
    }
    
    /**
	* 返回错误码
	* 
	*/
    public function Errno() {
        return intval(@mysqli_errno($this->link));
    }
    
    /**
	* 获取版本号
	* 
	*/
    public function version() {
        if (!is_resource($this->link)) {
            $this->Connect();
        }
        return mysqli_get_server_info($this->link);
    }
    
    /**
	* 关闭数据库
	* 
	*/
    public function Close() {
        if (is_resource($this->link)) {
            @mysqli_close($this->link);
        }
    }
    
    /**
	* 处理数据值
	* @param 值 $value
	* 
	*/
    public function addSpecialChar(&$value) {
        if ('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos($value, '`')) {//不处理包含* 或者 使用了sql方法。
        } else {
            $value = '`' . trim($value) . '`';
        }
        if (preg_match("/\b(select|insert|update|delete)\b/i", $value)) {
            $value = preg_replace("/\b(select|insert|update|delete)\b/i", '', $value);
        }
        return $value;
    }
    
    /**
	* 转义值
	* @param 值 $value
	* @param 键 $key
	* @param 是否转义 $quotation
	* 
	*/
    public function escape_string(&$value, $key = '', $quotation = 1) {
        if ($quotation) {
            $String = '\'';
        } else {
            $String = '';
        }
        $value = $String . $value . $String;
        return $value;
    }
    
    /**
	* 获取最后查询的SQL语句
	* 
	*/
    public function getSql() {
        return $this->sql;
    }
    
    /**
	* 联合查询
	* @param 联合表 $join
	* @param 联合查询方式 $type
	* 
	*/
    public function join($join = '', $type = 'left') {
        if ($join != '') {
           $this->deal['join'] .= ' ' . $type . ' join ' . $join . ' ';
        }
        return $this;
    }
    
    /**
	* 设置条件
	* @param 条件 $where
	* 
	*/
    public function where($where = '') {
        if (is_array($where)) {
            foreach ($where as $key => $val) {
                if ($this->deal['where'] == '') {
                    $this->deal['where'] .= ' where ' . $this->addSpecialChar($key) . " = '" . $val . "'";
                } else {
                    $this->deal['where'] .= ' and `' . $this->addSpecialChar($key) . "` = '" . $val . "'";
                }
            }
        } else {
            $this->deal['where'] = ' where ' . $where . ' ';
        }
        return $this;
    }
    
    /**
	* 设置字段
	* @param 字段内容 $field
	* 
	*/
    public function field($field = '') {
        if (is_array($field)) {
            foreach ($field as $val) {
                $this->deal['field'] .= $this->addSpecialChar($val) . ',';
            }
            $this->deal['field'] = ' ' . trim($this->deal['field'], '.,') . ' ';
        } else {
            $this->deal['field'] = ' ' . $field . ' ';
        }
        return $this;
    }
    
    /**
	* 排序方式
	* @param 排序 $order
	* 
	*/
    public function order($order = '') {
        if (!is_array($order)) {
            $this->deal['order'] = ' order by ' . $order . ' ';
        }else{
			foreach ($order as $val) {
                if ($this->deal['order'] == '') {
                    $this->deal['order'] = " order by " . $this->addSpecialChar($val) . ",";
                } else {
                    $this->deal['order'] .= ',' . $this->addSpecialChar($val) . ',';
                }
                $this->deal['order'] = trim($this->deal['order'],'.,');
            }
		}
        return $this;
    }
    
    /**
	* 条数限制
	* @param 起始数 $start
	* @param 条数 $end
	* 
	*/
    public function limit($start = 0, $end = 30) {
        if(strpos($start,',') && is_string($start)){
			$end = explode(',',$start);
			$end = end($end);	
		}
		$this->deal['limit'] = ' limit ' . $start . ',' . $end . ' ';
        return $this;
    }
    
    /**
	* 查询分组
	* @param 分组 $group
	* 
	*/
    public function group($group = '') {
        if (is_array($group)) {
            foreach ($groupa as $val) {
                if ($this->deal['group'] == '') {
                    $this->deal['group'] = " group by " . $this->addSpecialChar($val) . ",";
                } else {
                    $this->deal['group'] .= '' . $this->addSpecialChar($val) . ',';
                }
            }
            $this->deal['group'] = trim($this->deal['group'], '.,');
        } else {
            $this->deal['group'] = " group by " . $group . " ";
        }
        return $this;
    }
    
    /**
	* 联合查询
	* @param 查询内容 $union
	* @param 方式 $all
	* 
	*/
    public function union($union = '', $all = false) {
        if ($union != '') {
            $all = ($all) ? (' all') : ('');
            $this->deal['union'] .= ' union' . $all . '(' . $union . ') ';
        }
        return $this;
    }
    
    /**
	* 分页
	* @param 当前页 $page
	* @param 条数 $num
	* 
	*/
    public function page($page = '1', $num = '10') {
        if (is_numeric($page)) {
            $page = ($page < 1) ? ('1') : ($page);
            $this->page['page'] = $page;
            $this->page['num'] = $num;
            $this->page['status'] = true;
        }
        return $this;
    }
    
    /**
	* 别名
	* @param 别名 $alias
	* 
	*/
    public function alias($alias = '') {
        if ($alias != '') {
            $this->deal['alias'] = ' ' . $alias . ' ';
        }
        return $this;
    }
    
    /**
	* SQL语句连接
	* @param 连接类型 $type
	* 
	*/
    public function linkSql($Type = 'select') {
        switch ($Type) {
            case 'select':
                $this->fields = ($this->fields == '') ? (' * ') : ($this->fields);
                break;
        }
        //连接SQL
        $this->sql = $Type . ' ' . $this->fields . 'from `' . $this->db . '`' . $this->deal['alias'] . $this->deal['union'] . $this->deal['join'] . $this->deal['where'] . $this->deal['group'] . $this->deal['order'] . $this->deal['limit'];
        //处理分页
        if ($this->page['status']) {
            $this->page['count'] = $this->NumRows($this->sql);
            $this->page['start'] = $this->page['num'] * ($this->page['page'] - 1);
            $this->page['max_page'] = ceil($this->page['count'] / $this->page['num']);
            $this->limit($this->page['start'], $this->page['num']);
            $this->page['status'] = false;
            $this->linkSql();
            return false;
        }
        //初始化所有内容
        foreach ($this->deal as $key=>$val) {
            $this->deal[$key] = '';
        }
        return $this->sql;
    }
}