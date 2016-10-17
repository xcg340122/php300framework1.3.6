<?php
/**
 *  mysqli_class.php MYSQLI数据库类
 *
 * @copyright			(C) 2015-2016 PHP300
 * @license				http://framework.php300.cn
 * @lastmodify			2016-10-05
 */
class mysqli_class extends system_class{

	public $config = null;//数据库配置信息
	
	public $link = null;//数据库连接资源句柄
	
	protected $lastqueryid = null;//最近一次查询资源句柄
	
	protected $querycount = 0; //统计数据库查询次数
	
	public $sql = '';//SQL
	
	public $db = '';//当前数据表
	
	protected $where = '';//条件
	
	protected $order = '';//排序
	
	protected $group = '';//分组
	
	protected $limit = '';//条数
	
	protected $join = '';//联合
	
	protected $union = '';//筛选
	
	public $fields = '';//字段
	
	protected $data = array();//数据
	
	protected $key = '';//主键
	
	protected $alias='';//别名
	
	public $page = array();//分页
	
	public function __construct($db='') {
		if($db!=''){
			$this->set_db($db);
		}
	}
	
	public function set_db($db=''){
		if($this->link==NULL){
			return false;	
		}
		if($db!=''){
			$this->db = $this->config['prefix'].$db;
		}else{
			$res = $this->get_table();
			if(is_array($res)){
				$this->db = $res[0];
			}
		}
		$this->order_field();
	}
	
	public function open($config) {
		$this->config = $config;
		if($config['autoconnect']) {
			$this->connect();
		}
	}

	public function connect() {
		 $this->link = @mysqli_connect($this->config['hostname'].':'.$this->config['port'],$this->config['username'],$this->config['password'],$this->config['database']);
		 if($this->link==NULL){
		 	$this->halt('mysqli - 数据库连接失败!');
			return false;
		 };
		 mysqli_query($this->link,"set names ".$this->config['charset']);
		 return $this->link;
	}
	
	public function order_field(){
		 if($this->db != ''){
		 	$res = $this->get_fields();
			 foreach($res as $key=>$val){
				if($val['Default']==NULL){
					$r[$key] = '';
				}else{
					$r[$key] = $val['Default'];
				}
				if($val['Key']=='PRI'){
					$this->key = $key;
					unset($r[$key]);
				}
			 }
		 }
		 $this->data = $r;
	}
	 
	private function execute($sql) {
		if(C('sqldebug')){
			echo $sql;
		}
		$this->lastqueryid = mysqli_query($this->link,$sql) or $this->halt(mysqli_connect_error(), $sql);
		$this->querycount++;
		return $this->lastqueryid;
	}
	 
	public function select() {
		$this->link_sql();
		if($this->sql!=''){
			$this->query($this->sql);
			$datalist = array();
			while(($rs = $this->fetch_next()) != false) {
				if($key) {
					$datalist[$rs[$key]] = $rs;
				} else {
					$datalist[] = $rs;
				}
			}
			$this->free_result();
		}
		return $datalist;
	}
	
	public function fetch_next() {
		$res = mysqli_fetch_array($this->lastqueryid,MYSQLI_ASSOC);
		if(!$res) { 
			$this->free_result();
		}
		return $res;
	}
	
	
	public function free_result() {
		if(is_resource($this->lastqueryid)) {
			mysqli_free_result($this->lastqueryid);
			$this->lastqueryid = null;
		}
	}
	
	 
	public function query($sql) {
		return $this->execute($sql);
	}
	

	public function insert($data=array()) {
		if(is_array($data)){
			foreach($data as $key=>$val){
				$this->data[$key] = $val;
			}
			foreach($this->data as $key=>$val){
				$keys .= $key.',';
				$vals .= "'".$val."',";
			}
			$keys = trim($keys,'.,');
			$vals = trim($vals,'.,');
			$this->sql = 'INSERT INTO '.$this->db.' ('.$keys.')VALUES('.$vals.')';
			return $this->query($this->sql);
		}else{
			return FALSE;
		}
	}
	
	public function add($data=array()){
		return $this->insert($data);
	}
	
	public function insert_id() {
		return mysqli_insert_id($this->link);
	}
	
	
	public function update($data=array()) {
		foreach($data as $key=>$val){
				$vals .= $key.'='.$val;
		}
		$this->sql = 'UPDATE '.$this->db.' set '.$vals.$this->where;
		return $this->execute($this->sql);
	}
	
	
	public function save($data=array()){
		return $this->update($data);
	}
	
	
	public function affected_rows() {
		return mysqli_affected_rows($this->link);
	}
	

	public function get_primary($table) {
		$this->execute("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_next()) {
			if($r['Key'] == 'PRI') break;
		}
		return $r['Field'];
	}

	public function get_fields() {
		$fields = array();
		$this->execute("SHOW COLUMNS FROM ".$this->db);
		while($r = $this->fetch_next()) {
			$fields[$r['Field']] = $r;
		}
		return $fields;
	}
	
	public function get_table() {
		$fields = array();
		$this->query("SHOW tables");
		$tab = array();
		while($r = $this->fetch_next()) {
			array_push($tab,$r['Tables_in_'.$this->config['database']]);
		}
		return $tab;
	}


	public function check_fields($table, $array) {
		$fields = $this->get_fields($table);
		$nofields = array();
		foreach($array as $v) {
			if(!array_key_exists($v, $fields)) {
				$nofields[] = $v;
			}
		}
		return $nofields;
	}

	public function table_exists($table) {
		$tables = $this->get_table();
		return in_array($table, $tables) ? 1 : 0;
	}

	public function field_exists($table, $field) {
		$fields = $this->get_fields($table);
		return array_key_exists($field, $fields);
	}

	public function num_rows($sql) {
		$this->lastqueryid = $this->execute($sql);
		return mysqli_num_rows($this->lastqueryid);
	}

	public function num_fields($sql) {
		$this->lastqueryid = $this->execute($sql);
		return mysqli_num_fields($this->lastqueryid);
	}

	public function error() {
		return @mysqli_error($this->link);
	}

	public function errno() {
		return intval(@mysqli_errno($this->link)) ;
	}

	public function version() {
		if(!is_resource($this->link)) {
			$this->connect();
		}
		return mysqli_get_server_info($this->link);
	}

	public function close() {
		if (is_resource($this->link)) {
			@mysqli_close($this->link);
		}
	}
	
	public function halt($message = '', $sql = '') {
		$sql = ($sql!='')?($sql):('无');
		$message = ($message!='')?($message):('无');
		$errno = ($this->errno()!='')?($this->errno()):('未知');
		$error = ($this->error()!='')?($this->error()):('未知');
		if($this->config['debug']) {
			$this->errormsg = "数据库出错<br />SQL语句：{$sql}<br />错误原因：{$error}<br />错误代码：".$errno."<br />信息：".$message;
			$msg = $this->errormsg;
			$this->get_error_page($msg);
		} else {
			return false;
		}
	}
	
	
	public function add_special_char(&$value) {
		if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos ( $value, '`')) {//不处理包含* 或者 使用了sql方法。
		} else {
			$value = '`'.trim($value).'`';
		}
		if (preg_match("/\b(select|insert|update|delete)\b/i", $value)) {
			$value = preg_replace("/\b(select|insert|update|delete)\b/i", '', $value);
		}
		return $value;
	}
	
	public function escape_string(&$value, $key='', $quotation = 1) {
		if ($quotation) {
			$q = '\'';
		} else {
			$q = '';
		}
		$value = $q.$value.$q;
		return $value;
	}
	
	public function get_sql(){
		return $this->sql;
	}
	
	public function join($join='',$type='left'){
		if($join!=''){
			$this->join .= ' '.$type.' join '.$join.' ';
		}
		return $this;
	}
	
	public function where($where=''){
		if(is_array($where)){
			foreach($where as $key=>$val){		
				if($this->where ==''){
					$this->where .= ' where `'.$key."` = '".$val."'";
				}else{
					$this->where .= ' and `'.$key."` = '".$val."'";
				}
			}
		}else{
			$this->where = ' where '.$where.' ';
		}
		return $this;
	}
	
	public function field($field=''){
		if(is_array($field)){
			foreach($field as $val){
				$this->fields .= $val.',';
			}
			$this->fields = ' '.trim($this->fields,'.,').' ';
		}else{
			$this->fields = ' '.$field.' ';
		}
		return $this;
	}
	
	public function order($order=''){
		if($order!=''){
			$this->order = ' order by '.$order.' ';
		}
		return $this;
	}
	
	public function limit($start=0,$end=30){
		if(is_numeric($start)){
			$this->limit = ' limit '.$start.','.$end.' ';
			
		}
		return $this;
	}
	
	public function group($group=''){
		if(is_array($group)){
			foreach($groupa as $val){
				if($this->group ==''){
					$this->group = " group by `".$val."`,";
				}else{
					$this->group .= '`'.$val.'`,';
				}
			}
			$this->group = trim($this->group,'.,');
		}else{
			$this->group = " group by ".$group." ";
		}
		return $this;
	}
	
	public function union($union='',$all=false){
		if($union!=''){
			$all = ($all)?(' all'):('');
			$this->union .=  ' union'.$all.'('.$union.') ';
		}
		return $this;
	}
	
	public function page($page='1',$num='10'){
		if(is_numeric($page)){
			$page = ($page<1)?('1'):($page);
			$this->page['page'] = $page;
			$this->page['num'] = $num;
			$this->page['status'] = true;
		}
		return $this;
	}
	
	public function alias($alias=''){
		if($alias!=''){
			$this->alias = ' '.$alias.' ';
		}
		return $this;
	}
	
	public function find($key=''){
		if(is_numeric($key)){
			$this->where = 'where '.$this->key.' = '.$key.' ';
		}
		$this->link_sql();
		$this->query($this->sql);
		$res = $this->fetch_next();
		$this->free_result();
		return $res;
	}
	
	public function del($key=''){
		if(is_numeric($key)){
			$this->where = 'where '.$this->key.' = '.$key.' ';
		}
		$this->link_sql('delete');
		$res = $this->query($this->sql);
		return $res;
	}
	
	public function link_sql($type='select'){
		switch($type){
			case 'select':
			$this->fields = ($this->fields=='')?(' * '):($this->fields);
				break;
		}
		$this->sql = $type.' '.$this->fields.'from `'.$this->db.'`'.$this->alias.$this->union.$this->join.$this->where.$this->group.$this->order.$this->limit;
		if($this->page['status']){
			$this->page['count'] = $this->num_rows($this->sql);
			$this->page['start'] = $this->page['num']*($this->page['page']-1);
			$this->page['max_page'] =  ceil($this->page['count']/$this->page['num']);
			$this->limit($this->page['start'],$this->page['num']);
			$this->page['status'] = false;
			$this->link_sql();
			return false;
		}
		$arr = array('fields','alias','union','join','where','group','order','limit');
		foreach($arr as $val){
			$this->$val = '';
		}
		return $this->sql;
	}
}
?>