<?php
session_write_close();

ini_set('session.auto_start', 0);                    //关闭session自动启动

ini_set('session.cookie_lifetime', 0);            //设置session在浏览器关闭时失效

ini_set('session.gc_maxlifetime', 3600);  //session在浏览器未关闭时的持续存活时间

@define('IN_SYS', TRUE);
define('D_BUG', '0');

D_BUG?error_reporting(7):error_reporting(0);
$_SGLOBAL = $_SCONFIG = $_SCOOKIE = array();

//程序目录
define('S_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//基本文件
include_once(S_ROOT.'./config.php');

//通用函数
include_once(S_ROOT.'./source/function_common.php');

//时间
$mtime = explode(' ', microtime());
$_SGLOBAL['timestamp'] = $mtime[1];
$_SGLOBAL['supe_starttime'] = $_SGLOBAL['timestamp'] + $mtime[0];

//本站URL
if(empty($_SC['siteurl'])) $_SC['siteurl'] = getsiteurl();

//链接数据库
dbconnect();



$_SCONFIG['template']='base';
header('Cache-control: private');
header('Content-type: text/html; charset='.$_SC['charset']);
/* 创建 Smarty 对象。*/
require(S_ROOT . './source/cls_template.php');
$smarty = new cls_template;
$smarty->cache_lifetime = 1;//$_SCONFIG['cache_time'];
$smarty->template_dir   = S_ROOT . './themes/' . $_SCONFIG['template'];
$smarty->cache_dir      = S_ROOT . './temp/caches';
$smarty->compile_dir    = S_ROOT . './temp/compiled';
$smarty->compile_id = $_SCONFIG['template'];
$smarty->direct_output = false;
$smarty->force_compile = false;
$smarty->assign('lang', $_SC['lang']);
$smarty->assign('charset', $_SC['charset']);

//版本
include_once(S_ROOT.'./version.php');

$smarty->assign('template_path','./themes/' . $_SCONFIG['template']);  
$smarty->assign('_SC', $_SC);
$smarty->assign('_SGLOBAL', $_SGLOBAL);
session_save_path(S_ROOT."./data/session_tmp");
session_start();
?>