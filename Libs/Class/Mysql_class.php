<?php

namespace Libs\Deal;

class Mysql
{

	private $Config;

	private $Queryid;

	private $Link = null;

	private $Sql;

	private $Db;

	private $Key;

	private $Fields;

	private $Data;

	private $Page;

	private $Deal = array(
		'join' => '',
		'where'=> '',
		'field'=> '',
		'union'=> '',
		'group'=> '',
		'order'=> '',
		'limit'=> '',
		'page' => '',
		'alias'=> '',
	);

	private $Handle = array('join','where','field','union','group','order','limit','page','alias');

	private $Symbol = array('eq'     => '=','neq'    => '<>' ,'gt'     => '>' ,'lt'     => '<','elt'    =>
		'<=' ,'egt'    => '>=' ,'like'   => 'LIKE','in'     => 'IN','between'=> 'BETWEEN' ,'notnull'=> 'IS NUT NULL','null'   => 'IS NULL');
	/**
	* 配置项
	* @param 配置数组 $config
	*
	*/
	function option($config)
	{
		if(is_array($config))
		{
			$this->Config = $config;
		}
	}

	/**
	* 连接数据库
	*
	*/
	function Connect()
	{
		$this->Link = @mysqli_connect($this->Config['Host'] . ':' . $this->Config['Port'], $this->Config['Username'], $this->Config['Password'], $this->Config['DataBase']);
		if($this->Link != null)
		{
			mysqli_query($this->Link, "set names " . $this->Config['Char']);
			return $this->Link;
		}
		else
		{
			header("status:400 Bad Request");
			Error('PHP::Mysql连接失败!');
		}
	}

	/**
	* 选择数据库
	* @param 数据库 $Db
	*
	*/
	public function SelectDb($Db = '')
	{
		if($this->Link != NULL)
		{
			if(empty($Db))
			{
				$TableList = $this->getTable();
				if($TableList[0])
				{
					$this->Db = $TableList[0];
				}
			}
			else
			{
				$this->Db = $this->Config['Prefix'].$Db;
			}
			$this->orderField();
			return $this;
		}
	}

	/**
	* 排序字段
	*
	*/
	public function orderField()
	{
		if($this->Db != '')
		{
			$Res    = $this->getFields($this->Db);
			$ResArr = array();
			foreach($Res as $key => $val)
			{
				if($val['Default'] == NULL)
				{
					$ResArr[$key] = '';
				}
				else
				{
					$ResArr[$key] = $val['Default'];
				}
				if($val['Key'] == 'PRI')
				{
					$this->Key = $key;
					unset($ResArr[$key]);
				}
			}
		}
		$this->Data = $ResArr;
	}

	/**
	* 执行SQL
	* @param sql语句 $sql
	*
	*/
	private function execute($sql)
	{
		if($this->Link != null)
		{
			$this->Queryid = mysqli_query($this->Link, $sql);
			$Status = ($this->Queryid)?('Success'):('Error');
			if($this->Config['Logs'])
			{
				Logs('PHP300SQL['.$Status.']::'.$sql,'Mysql');
			}
			if($this->Config['Debug'])
			{
				if(!$this->Queryid)
				{
					header("status:400 Bad Request");
					Error('SQL执行失败：'.$sql.'<br />错误反馈:['.$this->Error().']');
				}
			}
			return $this->Queryid;
		}
		else
		{
			header("status:400 Bad Request");
			Error('PHP300::获取数据连接信息失效,请检查配置文件或目标主机状态!');
		}
	}

	/**
	* 执行SQL
	* @param sql语句 $sql
	*
	*/
	function query($sql)
	{
		$this->execute($sql);
	}

	/**
	* 结果集下一个
	*
	*/
	function fetchNext()
	{
		if($this->Queryid)
		{
			$Res = mysqli_fetch_array($this->Queryid, MYSQLI_ASSOC);
			if(!$Res)
			{
				$this->freeResult();
			}
			return $Res;
		}
	}

	/**
	* 结果集记录
	*
	*/
	function freeResult()
	{
		if(is_resource($this->Queryid))
		{
			mysqli_free_result($this->Queryid);
			$this->Queryid = null;
		}
	}

	/**
	* 插入数据
	* @param 数据 $data
	*
	* @return
	*/
	public function insert($data = array())
	{
		if(is_array($data))
		{
			$keys = '';$vals = '';
			foreach($data as $key => $val)
			{
				$this->Data[$key] = $val;
			}
			foreach($this->Data as $key => $val)
			{
				$keys .= $this->addSpecialChar($key) . ',';
				$vals .= "'" . $val . "',";
			}
			$keys = trim($keys, '.,');$vals = trim($vals, '.,');
			$this->Sql = 'INSERT INTO ' . $this->Db . ' (' . $keys . ')VALUES(' . $vals . ')';
			return $this->execute($this->Sql);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* 插入数据(快捷)
	*/
	public function add($data = array())
	{
		return $this->insert($data);
	}

	/**
	* 获取最后插入的ID
	*
	*/
	public function insert_id()
	{
		return mysqli_insert_id($this->Link);
	}

	/**
	* 更新数据
	* @param 数据 $data
	*
	*/
	public function update($data = array())
	{
		foreach($data as $key => $val)
		{
			$vals .= $key . '=' . "'".$val."'";
		}
		$this->Sql = 'UPDATE ' . $this->Db . ' set ' . $vals . $this->Deal['where'];
		return $this->execute($this->Sql);
	}

	/**-+
	* 更新数据(快捷)
	* @param 数据 $data
	*
	*/
	public function save($data = array())
	{
		return $this->update($data);
	}

	/**
	* 查询数据
	*
	*/
	public function select()
	{
		$this->LinkSql();
		if($this->Sql != '')
		{
			$this->execute($this->Sql);$DataList = array();
			while(($Res = $this->fetchNext()) != false)
			{
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
	public function find($key = '')
	{
		if(is_numeric($key))
		{
			$this->where = 'where ' . $this->Key . ' = ' . $key . ' ';
		}
		$this->LinkSql();
		$this->execute($this->Sql);
		$Res = $this->fetchNext();
		$this->freeResult();
		return (is_array($Res))?($Res):(array());
	}

	/**
	* 删除数据
	* @param 主键 $key
	*
	*/
	public function delete($key = '')
	{
		if(is_numeric($key))
		{
			$this->Deal['where'] = 'where ' . $this->Key . ' = ' . $key . ' ';
		}
		$this->LinkSql('delete');
		$Res = $this->execute($this->Sql);
		return $Res;
	}

	/**
	* 删除数据(快捷)
	*
	*/
	public function del($key = '')
	{
		$this->Key($key);
	}


	/**
	* 返回影响记录
	*
	*/
	public function affectedRows()
	{
		return mysqli_affected_rows($this->Link);
	}

	/**
	* 获取主键
	*
	*/
	public function getPrimary($table)
	{
		$this->execute("SHOW COLUMNS FROM ".$table);
		while($Next = $this->fetchNext())
		{
			if($Next['Key'] == 'PRI')
			{
				break;
			}
		}
		return $Next['Field'];
	}

	/**
	* 获取字段列表
	*
	*/
	public function getFields($table)
	{
		$fields = array();
		$this->execute('SHOW COLUMNS FROM `' .$table.'`');
		while($Next = $this->fetchNext())
		{
			$fields[$Next['Field']] = $Next;
		}
		return $fields;
	}

	/**
	* 获取所有表
	*
	*/
	public function getTable()
	{
		echo 'getTable';
		$fields = array();
		$this->execute("SHOW tables");
		$table = array();
		while($Next = $this->fetchNext())
		{
			array_push($table, $Next['Tables_in_' . $this->Config['DataBase']]);
		}
		return $table;
	}

	/**
	* 检测字段
	* @param 数据表 $table
	* @param 字段列表 $array
	*
	*/
	public function checkFields($table, $array)
	{
		$fields   = $this->getFields($table);
		$nofields = array();
		foreach($array as $val)
		{
			if(!array_key_exists($val, $fields))
			{
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
	public function tableExists($table)
	{
		$tables = $this->getTable();
		return in_array($table, $tables) ? 1 : 0;
	}

	/**
	* 字段是否存在
	* @param 数据表 $table
	* @param 字段 $field
	*
	*/
	public function fieldExists($table, $field)
	{
		$fields = $this->getFields($table);
		return array_key_exists($field, $fields);
	}

	/**
	* 获取条数
	* @param sql语句 $sql
	*
	*/
	public function NumRows($sql)
	{
		$this->Queryid = $this->execute($sql);
		return mysqli_num_rows($this->Queryid);
	}

	/**
	* 获取字段数
	* @param sql语句 $sql
	*
	*/
	public function NumFields($sql)
	{
		$this->Queryid = $this->execute($sql);
		return mysqli_num_fields($this->Queryid);
	}

	/**
	* 返回错误内容
	*
	*/
	public function Error()
	{
		return @mysqli_error($this->Link);
	}

	/**
	* 返回错误码
	*
	*/
	public function Errno()
	{
		return intval(@mysqli_errno($this->Link));
	}

	/**
	* 获取版本号
	*
	*/
	public function version()
	{
		if(!is_resource($this->Link))
		{
			$this->Connect();
		}
		return mysqli_get_server_info($this->Link);
	}

	/**
	* 关闭数据库
	*
	*/
	public function Close()
	{
		if(is_resource($this->Link))
		{
			@mysqli_close($this->Link);
		}
	}

	/**
	* 处理数据值
	* @param 值 $value
	*
	*/
	public function addSpecialChar( & $value)
	{
		if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos($value, '`'))
		{
			//不处理包含 * 或者 使用了sql方法。
		}
		else
		{
			$value = '`' . trim($value) . '`';
		}
		if(preg_match("/\b(select|insert|update|delete)\b/i", $value))
		{
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
	public function escape_string( & $value, $key = '', $quotation = 1)
	{
		if($quotation)
		{
			$String = '\'';
		}
		else
		{
			$String = '';
		}
		$value = $String . $value . $String;
		return $value;
	}

	/**
	* 获取最后查询的SQL语句
	*
	*/
	public function getSql()
	{
		return $this->Sql;
	}

	/**
	* 联合查询
	* @param 联合表 $join
	* @param 联合查询方式 $type
	*
	*/
	public function join($join = '', $type = 'left')
	{
		if($join != '')
		{
			$this->Deal['join'] .= ' ' . $type . ' join ' . $join . ' ';
		}
		return $this;
	}

	/**
	* 设置条件
	* @param 条件 $where
	*
	*/
	public function where($where = '')
	{
		if(is_array($where))
		{
			foreach($where as $key => $val)
			{
				$Symbol = (is_array($val))?($this->Symbol[$val[0]]):('=');
				$Symbols= (in_array($Symbol,array('IN','BETWEEN')))?(''):("'");
				if($this->Deal['where'] == '')
				{
					$this->Deal['where'] = ' where ' . $this->addSpecialChar($key) . ' '.$Symbol." '" . $val . "'";
				}
				else
				{
					$this->Deal['where'] .= ' and ' . $this->addSpecialChar($key) . ' '.$Symbol.' '.$Symbols . $val . $Symbols."";
				}
			}
		}
		else
		{
			$this->Deal['where'] = ' where ' . $where . ' ';
		}
		return $this;
	}

	/**
	* 设置字段
	* @param 字段内容 $field
	*
	*/
	public function field($field = '')
	{
		if(is_array($field))
		{
			foreach($field as $val)
			{
				$this->Deal['field'] .= $this->addSpecialChar($val) . ',';
			}
			$this->Deal['field'] = ' ' . trim($this->Deal['field'], '.,') . ' ';
		}
		else
		{
			$this->Deal['field'] = ' ' . $field . ' ';
		}
		return $this;
	}

	/**
	* 排序方式
	* @param 排序 $order
	*
	*/
	public function order($order = '')
	{
		if(!is_array($order))
		{
			$this->Deal['order'] = ' order by ' . $order . ' ';
		}
		else
		{
			foreach($order as $val)
			{
				if($this->Deal['order'] == '')
				{
					$this->Deal['order'] = " order by " . $this->addSpecialChar($val) . ",";
				}
				else
				{
					$this->Deal['order'] .= ',' . $this->addSpecialChar($val) . ',';
				}
				$this->Deal['order'] = trim($this->Deal['order'],'.,');
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
	public function limit($start = 0, $end = 30)
	{
		if(strpos($start,',') && is_string($start))
		{
			$end = explode(',',$start);
			$end = end($end);
		}
		$this->Deal['limit'] = ' limit ' . $start . ',' . $end . ' ';
		return $this;
	}

	/**
	* 查询分组
	* @param 分组 $group
	*
	*/
	public function group($group = '')
	{
		if(is_array($group))
		{
			foreach($groupa as $val)
			{
				if($this->Deal['group'] == '')
				{
					$this->Deal['group'] = " group by " . $this->addSpecialChar($val) . ",";
				}
				else
				{
					$this->Deal['group'] .= '' . $this->addSpecialChar($val) . ',';
				}
			}
			$this->Deal['group'] = trim($this->Deal['group'], '.,');
		}
		else
		{
			$this->Deal['group'] = " group by " . $group . " ";
		}
		return $this;
	}

	/**
	* 联合查询
	* @param 查询内容 $union
	* @param 方式 $all
	*
	*/
	public function union($union = '', $all = false)
	{
		if($union != '')
		{
			$all = ($all) ? (' all') : ('');
			$this->Deal['union'] .= ' union' . $all . '(' . $union . ') ';
		}
		return $this;
	}

	/**
	* 分页
	* @param 当前页 $page
	* @param 条数 $num
	*
	*/
	public function page($page = '1', $num = '10')
	{
		if(is_numeric($page))
		{
			$page = ($page < 1) ? ('1') : ($page);
			$this->Page['page'] = $page;
			$this->Page['num'] = $num;
			$this->Page['status'] = true;
		}
		return $this;
	}

	/**
	* 别名
	* @param 别名 $alias
	*
	*/
	public function alias($alias = '')
	{
		if($alias != '')
		{
			$this->Deal['alias'] = ' ' . $alias . ' ';
		}
		return $this;
	}

	/**
	* SQL语句连接
	* @param 连接类型 $type
	*
	*/
	public function linkSql($Type = 'select')
	{
		switch($Type)
		{
			case 'select':
			$this->Deal['field'] = ($this->Deal['field'] == '') ? (' * ') : ($this->Deal['field']);
			break;
		}
		//连接SQL
		$this->Sql = $Type . ' ' . $this->Deal['field'] . 'from `' . $this->Db . '`' . $this->Deal['alias'] . $this->Deal['union'] . $this->Deal['join'] . $this->Deal['where'] . $this->Deal['group'] . $this->Deal['order'] . $this->Deal['limit'];
		//处理分页
		if($this->Page['status'])
		{
			$this->Page['count'] = $this->NumRows($this->Sql);
			$this->Page['start'] = $this->Page['num'] * ($this->Page['page'] - 1);
			$this->Page['max_page'] = ceil($this->Page['count'] / $this->Page['num']);
			$this->limit($this->Page['start'], $this->Page['num']);
			$this->Page['status'] = false;
			$this->LinkSql();
			return false;
		}
		//初始化所有内容
		foreach($this->Deal as $key=>$val)
		{
			$this->Deal[$key] = '';
		}
		return $this->Sql;
	}
}