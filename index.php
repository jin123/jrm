<?php
	//加载框架入口文件
	header("Content-type: text/html; charset=utf-8");
if (get_magic_quotes_gpc()) {
	function stripslashes_deep($value){
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value); 
		return $value; 
	}
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE); 
}
	define('THINK_PATH', './ThinkPHP/');
	define('APP_NAME','App');
	define('APP_PATH', './App/');
	define('APP_DEBUG', true);
	define('RUNTIME_PATH',APP_PATH."/Runtime/");
    require './ThinkPHP/ThinkPHP.php';