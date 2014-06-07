<?php
session_write_close();

ini_set('session.auto_start', 0);                    //关闭session自动启动

ini_set('session.cookie_lifetime', 0);            //设置session在浏览器关闭时失效

ini_set('session.gc_maxlifetime', 3600);  //session在浏览器未关闭时的持续存活时间

@define('IN_SYS', TRUE);
define('D_BUG', '0');

D_BUG?error_reporting(7):error_reporting(0);
$_SGLOBAL = $_SCONFIG =$_SCOOKIE = $_WZ = array();

//程序目录
define('S_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//基本文件
include_once(S_ROOT.'./config.php');

//通用函数
include_once(S_ROOT.'./source/function_common.php');

//微站函数
include_once(S_ROOT.'./source/function_weizhan.php');

//时间
$mtime = explode(' ', microtime());
$_SGLOBAL['timestamp'] = $mtime[1];
$_SGLOBAL['supe_starttime'] = $_SGLOBAL['timestamp'] + $mtime[0];

//本站URL
if(empty($_SC['siteurl'])) $_SC['siteurl'] = getsiteurl();

//链接数据库
dbconnect();

?>