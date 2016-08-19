<?php
/* Smarty version 3.1.29, created on 2016-08-20 04:08:51
  from "D:\WWW\php300framework\template\php300_tmp\php300_error.html" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_57b76753c62036_12744396',
  'file_dependency' => 
  array (
    '60c44a3b619e7a7261447782bd0f3d8d68e84c32' => 
    array (
      0 => 'D:\\WWW\\php300framework\\template\\php300_tmp\\php300_error.html',
      1 => 1471635857,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_57b76753c62036_12744396 ($_smarty_tpl) {
$_smarty_tpl->compiled->nocache_hash = '1415057b76753c0e179_16926599';
?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
body {
  background-color: #ECECEC;
  font-family: 'Open Sans', sans-serif;
  font-size: 14px;
  color: #3c3c3c;
}
.php300_error{width:100%;margin:0 auto;}
.php300_error p:first-child {
  text-align: center;
  font-size: 50px;
  font-weight: bold;
  line-height: 100px;
  letter-spacing: 5px;
  color: #fff;
}

.php300_error p:first-child span {
  cursor: pointer;
  text-shadow: 0px 0px 2px #686868,
    0px 1px 1px #ddd,
    0px 2px 1px #d6d6d6,
    0px 3px 1px #ccc,
    0px 4px 1px #c5c5c5,
    0px 5px 1px #c1c1c1,
    0px 6px 1px #bbb,
    0px 7px 1px #777,
    0px 8px 3px rgba(100, 100, 100, 0.4),
    0px 9px 5px rgba(100, 100, 100, 0.1),
    0px 10px 7px rgba(100, 100, 100, 0.15),
    0px 11px 9px rgba(100, 100, 100, 0.2),
    0px 12px 11px rgba(100, 100, 100, 0.25),
    0px 13px 15px rgba(100, 100, 100, 0.3);
  -webkit-transition: all .1s linear;
  transition: all .1s linear;
}

.php300_error p:first-child span:hover {
  text-shadow: 0px 0px 2px #686868,
    0px 1px 1px #fff,
    0px 2px 1px #fff,
    0px 3px 1px #fff,
    0px 4px 1px #fff,
    0px 5px 1px #fff,
    0px 6px 1px #fff,
    0px 7px 1px #777,
    0px 8px 3px #fff,
    0px 9px 5px #fff,
    0px 10px 7px #fff,
    0px 11px 9px #fff,
    0px 12px 11px #fff,
    0px 13px 15px #fff;
  -webkit-transition: all .1s linear;
  transition: all .1s linear;
}

.php300_error p:not(:first-child) {
  text-align: center;
  color: #666;
  font-family: cursive;
  font-size: 20px;
  text-shadow: 0 1px 0 #fff;
  letter-spacing: 1px;
  line-height: 2em;
  margin-top: 10px;
}
</style>
<title>PHP300_程序异常</title>
</head>
<body>
<div class="php300_error">
	<p>php300程序异常</p>
	<p><?php echo $_smarty_tpl->tpl_vars['error_txt']->value;?>
</p>
	<p><?php echo date('20y-m-d h:i',time());?>
</p>
</div>
</body>
</html><?php }
}
