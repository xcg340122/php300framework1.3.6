此版本已停用,请移步至[PHP300Framework2.0](https://github.com/xcg340122/PHP300Framework2.0)
====
[![](https://img.shields.io/badge/download%20-417k-brightgreen.svg)](https://framework.php300.cn/)
[![](https://img.shields.io/badge/version-1.3.6-blue.svg)](http://api.php300.cn/313845)
[![](https://img.shields.io/badge/group-480-brightgreen.svg)](https://jq.qq.com/?_wv=1027&k=5exsSYT)
[![](https://img.shields.io/badge/document-online-brightgreen.svg)](http://api.php300.cn/)
### 介绍
&emsp;&emsp;为了更好更快的开发WEB程序,PHP300FrameWork基于PHP300云类库的功能上进行开发,遵守开源免费的原则让开发者更好的扩展或使用本程序,随着PHP框架的日益增多,我们也融入了当前主流框架的一些性质并且优化的更简单更方便,框架代码简介,让您更容易了解本框架的原理用于自身学习.
1.3.6版本支持中文编码，如果需要更高的性能条件请移步[PHP300Framework2.0](https://github.com/xcg340122/PHP300Framework2.0)<br />
这里展示部分文档,更多介绍请详见：[在线手册](http://api.php300.cn)

### 环境支持
* 支持Windows/Linux服务器环境
* 可运行于包括Apache/Nginx/IIS等WEB服务器和模式 (Nginx中需要设置重定向规则)
* 支持Mysql数据库
### 基本内容
##### 命名空间
&emsp;&emsp;在1.3.1版本中新增命名空间，当使用扩展类需要use空间才能正常继承，控制器中需要以实例文件夹名称作为命名空间前缀，例如：Main实例中的APP控制器文件命名：`Main\Action`
<br />
例如Admin实例中的控制器则是：`Admin\Action`
#### 控制器
在Action实例项目目录中,我们可以创建多个项目控制器操作,例如：Admin。Home等等，系统默认有个Main项目，我们可以在里面创建控制器文件，下面是一个标准的控制器代码：
```php
<?php

namespace Main\Action;

class App_class{
	
	public function index()
	{
		ShowText('Hello World');
	}
	
}
```
#### 注意事项
在Linux中对文件的格式和命名的要求比较严谨，例如我们的控制器文件名为`User_class.php`，方法名称为`Getinfo`，假如代码运行在windows上，我们可以忽略大小写的进行访问：`http://www.abc.com/index.php/user/getinfo`，但是在Linux中您是无法进行访问的，我们则需要进行严格的大小写区分，您需要这么访问：`http://www.abc.com/index.php/User/Getinfo`，也就是说，在有些地方涉及到地址操作的时候需要注意您的服务器操作系统，例如在我们使用Url方法生成地址的时候Linux环境下则需要：`Url('User/Getinfo')`。

##### 命名注意事项
还是回到大小写的问题，在windows中，`User_class.php`和`User_class.PHP`都是可以进行访问的，而在Linux中，我们则需要统一使用小写后缀，否则框架将无法正常运行。

##### 权限注意事项
在Linux环境中，需要将Cache文件夹和Logs文件夹设为读写权限，某则无法写入编译的缓存文件和日志文件，系统将无法正常运行。

#### 系统方法
* Action
* Libs
* Config
* Receive
* Db
* Success
* Error
* Cache
* Session
* Cookie
* Show
* Assign
* Fetch
* Url
* Glovar
##### Action方法
###### 使用方法
> `Action`方法用于快速调用外部控制器方法,支持跨实例调用，例如调用Admin实例中的User控制器的getUserInfo方法：
```php
Action('Admin\User')->getUserInfo();
```
> 如果只是调用本实例项目中的方法，则不需要写实例项目名称，直接写控制器名称，例如调用本实例中的Order控制器中的Update方法：
```php
Action('Order')->Update();
```
方法参数：`Action($Name)`
##### Libs方法
###### 使用方法
> `Libs`方法用于返回系统扩展类库对象，可以用来调用系统扩展类库中的操作方法，例如调用File类中的createDir方法：
```php
Libs('File')->createDir('Uploads/Zip/'.date('Ymd'));
```
> 默认返回对象，如果目标类库不存在则会提示错误信息，系统类库文件存在于框架根目录中的Libs\Class中。
方法参数：`Libs($Name)`
##### Config方法
###### 使用方法
> `Config`方法将会返回您的配置文件信息，您可以在Config目录中创建您自己的配置文件信息，其规则为：
```php
return array(
	'配置名称' => '配置值'
);
```
> `Config`使用方法：该方法默认需要指定一个配置名称，如果这个名称存在于多个配置文件，则需要指定第二个参数来指定获取配置值，如果两个参数都为空则返回全部的配置信息数组，例如读取Mysql配置文件信息：
```php
$MysqlCon = Config('Mysql','Mysql');
```
方法参数：`Config($key='',$file='')`
##### Receive方法
###### 使用方法
> `Receive`用于获取参数信息，支持的参数类型：`Get`,`Post`,`Put`,`Globals`,`Session`,`Server`
参数提供四个参数，分别是：接收参数名称，为空默认值，是否安全编码，编码函数
Receive使用方法，例如获取客户端Post过来的Name参数：
```php
$Name = Receive('Name');
```
> 如果需要Name是空的，其可以指定第二个参数来进行返回默认值，如：
```php
$Name = Receive('Name','没有内容');
```
> 如果需要指定固定获取方法，例如固定接受get过来的Name参数：
```php
$Name = Receive('get.Name');
```
> 该方法支持参数过滤，其可以自定义第四个参数来自定义过滤方法，系统默认为`htmlspecialchars`
如果不想对参数进行过滤，第三个参数设为false即可
方法参数：`Receive($name='',$null='',$isEncode=true,$function='htmlspecialchars')`
##### Db方法
###### 使用方法
> Db方法是用于操作Mysql的快捷方法，其可以指定一个表用来快捷操作，操作前需要连接上Mysql数据库，具体连接方法请见Mysql配置文件介绍
该方法返回对象，可以来调用Mysql类库提供的方法,如果不填写参数则会返回Mysql类对象
例如查询User表里面符合年龄等于20的所有用户
```php
$UserList = Db('User')->where(array('age'=>'20'))->select();
```
例如获取id为3的用户信息
```php
$UserInfo = Db('User')->find(3);
```
方法参数：`Db($table = '')`
##### Success方法
###### 使用方法
> Success用于展示一个成功信息
该方法返需要填写一个信息来展示
例如展示一个登陆成功的信息：
```php
Success('登陆成功','Http://www.test.com/Admin/User/Index');
```
> 当我们没有连写Url的时候，系统则会判断有无上一页，如果有系统则会跳转至上一页，否则将不会进行跳转。

方法参数：`Success($Msg,$Url='',$Seconds=3)`
##### Error方法
###### 使用方法
> Error方法用于展示一个错误信息
该方法返需要填写一个信息来展示
例如展示一个登陆失败的信息：
```php
Error('登陆失败');
```
> 当我们没有连写Url的时候，系统则会判断有无上一页，如果有系统则会跳转至上一页，否则将不会进行跳转。

方法参数：`Error($Msg,$Url='',$Seconds=3)`
##### Cache方法
###### 使用方法
> Cache方法用于缓存轻量级数据,可用于快速存取
该方法提供三个参数，分别是：cache名称，cache的值，cache过期时间
例如设置一个Cache：
```php
Cache('Name','张三');
```
例如获取一个Cache：
```php
$Name = Cache('Name');
```
例如删除一个Cache：
```php
Cache('Name',NULL);
```
清空当前所有Cache内容
```php
Cache(NULL,NULL);
```
设定一个一小时后过期的Cache
```php
Cache('Name','张三',60);
```
方法参数：`Cache($name = '', $val = '', $expire = true)`
##### Session方法
###### 使用方法
> Session方法是用于快捷的操作Session扩展类库，支持各种SESSION回话的操作
该方法提供三个参数，分别是：session名称，session的值，session过期时间（单位为秒）
该方法返回对象，可以来调用Session类库提供的方法
例如设置一个session回话变量：
```php
Session('Userinfo',array('Name'=>'小明','Time'=>'1492855725'));
```
例如获取一个session回话变量：
```php
$UserInfo = Session('Userinfo');
```
例如删除一个session回话变量：
```php
Session('Userinfo',NULL);
```
清空当前所有会话内容
```php
Session(NULL,NULL);
```
设定一个一小时后过期的会话
```
Session('code',rand(1000,9999),3600);
```
方法参数：`Session($name='',$val='',$expire=0)`
##### Cookie方法
###### 使用方法
> Cookie方法用户设置客户端浏览器的cookie数据
该方法提供三个参数，分别是：cookie名称，cookie的值，cookie过期时间
例如设置一个cookie：
```php
Cookie('Name','张三');
```
例如获取一个cookie：
```php
$Name = Cookie('Name');
```
例如删除一个cookie：
```php
Cookie('Name',NULL);
```
清空当前所有cookie内容
```php
Cookie(NULL,NULL);
```
设定一个一小时后过期的cookie
```php
Cookie('Name','张三',time() + 3600);
```
方法参数：`Cookie($name = '', $val = '', $expire = '0')`
##### Show方法
###### 使用方法
> Show方法用于快速渲染并展示一个视图页面，相关的配置内容请参见配置文件中的View配置
该方法返需要指定一个页面路径来进行渲染和展示，默认将定位至Template目录下，所有视图文件需要置入这个文件夹下才可以正常展示
例如渲染展示视图目录下的Index文件：
```php
Show('Index');
```
例如展示视图目录中Admin目录下的Login文件
```php
Show('Admin/Login');
```
方法参数：`Show($Template='index')`
##### Assign方法
###### 使用方法
> Assign方法用于快速设置一个视图变量，用于视图中输出或其他操作
该方法提供两个参数，分别是键和值
例如设置一个Name视图变量
```php
Assign('Name','小明');
```
模板中使用：
```php
{$Name}
```
方法参数：`Assign($key,$val)`
##### Fetch方法
###### 使用方法
> Fetch方法用于快速渲染并返回一个视图页面，相关的配置内容请参见配置文件中的View配置
该方法返需要指定一个页面路径来进行渲染并返回，默认将定位至Template目录下，所有视图文件需要置入这个文件夹下才可以正常展示
例如渲染获取视图目录下的Index文件：
```php
Fetch('Index');
```
例如获取视图目录中Admin目录下的Login文件
```php
Fetch('Admin/Login');
```
方法参数：`Fetch($Template='index')`
##### Url方法
###### 使用方法
> Url方法用于快速生成URL地址
```php
$Url = Url('User/UserInfo','id=20');
```
第一个参数为必填参数，至少需要有两项，用/号分割，前两个分别是【控制器/方法名】，第二个参数则是选填，用于传递动态参数，如果您需要传递连贯参数可以使用以下方式：
```php
$Url = Url('User/UserInfo/id/20');
```
方法参数：`Url($name,$parm = '')`
##### Glovar方法
###### 使用方法
> Glovar用于设置和获取一个全局变量信息，设置的信息将可以跨页面和作用域调用
Glovar使用方法：改方法提供三个参数，分别是键，值，域，如果只填写键则会获取该键的全局变量，如果填写值则会对键进行变量值设置，如果填写域，下次获取将需要填写域名称。


例如设置一个User全局变量：
```php
Glovar('User',array('Name'=>'小明','Time'=>'1492855725'));
```
例如获取User全局变量：
```php
$UserInfo = Glovar('User');
```
例如设置Name变量到Admin域：
```php
Glovar('Nmae','小明','Admin');
```
例如获取Admin域中的Name变量：
```php
$Name = Glovar('Name','','Admin');
```
域的作用则是防止同一个变量名称之间存在冲突，划分域来区分变量之间的作用范围

方法参数：`Glovar($key='',$val='',$region='User')`

#### 数据模型
##### 添加数据
> 当我们需要添加数据到Mysql中的时候，可以使用insert($data = array())方法来进行添加数据
参数接收一个Array类型的数据，数据的格式为：字段=>储存内容
添加成功后如果表中具有主键字段则返回主键，否则返回bool型
别名：`add($data = array())`
示例：
```php
$UserId = Db('user')->insert(array('name'=>'张三','age'=>25));

$UserId = Db('article')->add(array('title'=>'标题','content'=>'文章内容'));
```
##### 删除数据
> 当我们需要删除某条数据的时候，可以使用delete($key = '')方法来进行删除数据
参数为可选，可接收一个主键，如果提供主键则可以快速删除相关内容，否则将会按条件删除
删除完成返回bool型
数据类型：主键（int）
别名：`del($key = '')`
示例：
```php
Db('user')->where(array('name'=>'张三'))->delete();		//删除name等于张三的数据

Db('user')->delete(6);		//删除主键为6的数据
```
##### 修改数据
> 当我们需要修改某条数据的时候，可以使用update($data = array())方法来进行更新数据
参数接收一个Array类型的数据，更新条件可使用where定义，数据的格式为：字段=>储存内容
修改完毕返回相关数据主键
别名：`save($data = array())`
示例：
```php
Db('user')->where(array('phone'=>'15056902037'))->update(array('name'=>'张三'));		//将手机号15056902037的用户的name改成张三

Db('user')->where(array('id'=>8))->update(array('name'=>'张三'));	//将id等于8的用户的name字段改成张三
```
##### 查询数据
> 查询数据分为单条查询和多条查询
单条查询（`find($key = '')`）
参数为可选，接收一个int类型的主键，如果传入主键则快速查询并返回相关数据，也可以配合where使用，该方法返回一维数组
示例：
```php
Db('user')->find(6);		//查询主键为6的数据

Db('user')->where(array('name'=>'张三'))->find();		//查询name等于张三的数据
```
多条查询（`select()`）
无参数，可配合where进行条件筛选，该方法返回多维数组
示例：
```php
Db('user')->where(array('name'=>'张三'))->select();		//查询name等于张三的相关数据

Db('user')->select();		//查询user表的全部数据
```
##### 连贯操作
###### alias
> alias用于设置当前数据表的别名，便于使用其他的连贯操作例如join方法等。
别名 - `alias($name=string)`
```php
Db('user')->alias('a')->select()
```
###### field
> field方法属于模型的连贯操作方法之一，主要目的是标识要返回或者操作的字段，可以用于查询和写入操作。
接收一个数组，也可以是字符串，数组会进行安全处理，而字符串则不会，建议使用数组
字段 - `field($data=array or string)`
```php
Db('user')->field(array('name','age','qq'))->select()
```
###### where
> where方法用于定义sql语句中的条件信息，可传数组也支持字符串，当传入数组则会进行安全处理，建议使用数组，支持普通查询、表达式查询、快捷查询、区间查询、组合查询
条件 - `where($data=array or string)`
```php
Db('user')->where(array('name'=>'小明'))->select()
```
当我们有一些比较特殊的的查询时，需要传入特殊查询标识符，比如，我们需要查询年龄小于20的用户列表：
```php
Db('user')->where(array('age'=>array('lt','20')))->select()
```
示例（模糊查询）：
```php
Db('user')->where(array('name'=>array('like','%张')))->select()
```
特殊操作符意义：
* eq => 大于

* neq => 不等于

* gt =>大于

* lt => 小于

* elt => 小于等于

* egt => 大于等于

* like => 模糊查询

* between => 区间查询

* notnull => 非NULL

* null => 为NULL
###### join
> join方法也是连贯操作方法之一，用于根据两个或多个表中的列之间的关系，从这些表中查询数据。
第一个参数为连接的字符，第二个参数为连接的类型，默认为left
关联 - `join($name=string,$type='left')`
```php
Db('comment')->join('user on user.id=comment.userid')->select()
```
###### order
> order方法属于模型的连贯操作方法之一，用于对操作的结果排序。
排序 - `order($field=string)`
```php
Db('user')->order('id desc')->select()
```
###### limit
> limit方法也是模型类的连贯操作方法之一，主要用于指定查询和操作的数量，特别在分页查询的时候使用较多。
该方法第一个参数为查询的起始位置。第二个为查询的条数
条数 - `limit($start=int,$num=int)`
```php
Db('user')->limit(0,30)->select()
```
###### group
> group方法也是连贯操作方法之一，通常用于结合合计函数，根据一个或多个列对结果集进行分组
分组 - `group($name='')`
```php
Db('user')->group('age')->select()
```
示例（查询B表条数并按A表排序）：
```php
Db('user')->alias('a')->field(array('count(b.id) as comment_count','a.id','a.name'))->join('comment as b on b.userid=a.id')->group('a.id')->select();
```
###### union
> union操作用于合并两个或多个 SELECT 语句的结果集。
合并 - `union($name='')`
```php
Db('user')->union('select * from msg')->select()
```
###### page
> page方法也是模型的连贯操作方法之一，简化了传统的分页查询过程。
该方法提供两个参数，第一个参数为查询的页数，第二个参数为查询的条数
分页 - `page($page='1',$num='10')`
```php
Db('user')->page(8,30)->select()
```
##### 其他操作
* 执行SQL语句：
  * query($sql)
* 结果集下一个
  * fetchNext()
* 结果集记录
  * freeResult()
* 获取最后插入的ID
  * insert_id()
* 返回影响记录
  * affectedRows()
* 获取主键
  * getPrimary($table)
* 获取字段列表
  * getFields($table)
* 获取所有表
  * getTable()
* 表是否存在
  * tableExists($table)
* 字段是否存在
  * fieldExists($table, $field)
* 获取条数
  * NumRows($sql)
* 获取字段数
  * NumFields($sql)
* 获取版本号
  * version()
* 关闭数据库
  * Close()
* 获取最后查询的SQL语句
  * getSql()
示例（获取数据库版本号）:
```php
echo Db()->version();
```
