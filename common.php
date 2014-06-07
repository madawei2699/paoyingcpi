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

$is_ipad   = strripos($_SERVER["HTTP_USER_AGENT"],'ipad');  //判断是否包含ipad关键字
$is_iphone = strripos($_SERVER["HTTP_USER_AGENT"],'iphone');  //判断是否包含iphone关键字
$is_android =strripos($_SERVER['HTTP_USER_AGENT'],'Android'); //判断是否Android;
$is_pc = strripos($_SERVER["HTTP_USER_AGENT"], 'windows nt'); //判断是否为(pc)电脑
$is_ucweb = strripos($_SERVER["HTTP_USER_AGENT"], 'UCWEB'); //判断是否为UC极速模式
if($is_pc){
$_SCONFIG['template']='pc';
}else{
$_SCONFIG['template']='pc';
}

if (!defined('INIT_NO_SMARTY'))
{
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

    if ((DEBUG_MODE & 2) == 2)
    {
        $smarty->direct_output = true;
        $smarty->force_compile = true;
    }
    else
    {
        $smarty->direct_output = false;
        $smarty->force_compile = false;
    }

    $smarty->assign('lang', $_SC['lang']);
    $smarty->assign('charset', $_SC['charset']);
}


$_SGLOBAL['login']=false;
  if(checkauth()){
       $_SGLOBAL['login']=true;
       $query=$_SGLOBAL['db']->query("select uid,fullname,username,email,mobile,state from ".tname("open_member")." where uid=".$_SGLOBAL['supe_uid']);
	   if($rs = $_SGLOBAL['db']->fetch_array($query)) {
	   	$_SGLOBAL['uid'] = $rs['uid'];
		$_SGLOBAL['fullname'] = $rs['fullname'];
		$_SGLOBAL['username'] = $rs['username'];
		$_SGLOBAL['email'] = $rs['email'];
		$_SGLOBAL['mobile'] = $rs['mobile'];
		$_SGLOBAL['state'] = $rs['state'];
	    unset($rs);
	   }//end if
  }
//导航条
include_once(S_ROOT.'./navmenu.php');

//版本
include_once(S_ROOT.'./version.php');

$smarty->assign('template_path','./themes/' . $_SCONFIG['template']);  
$smarty->assign('is_pc', $is_pc);
$smarty->assign('http_ua', $_SERVER["HTTP_USER_AGENT"]);
$smarty->assign('_SC', $_SC);
$smarty->assign('formhash', formhash());  
$smarty->assign('_SGLOBAL', $_SGLOBAL);
$smarty->assign('rand',random(6));
session_save_path(S_ROOT."./data/session_tmp");
session_start();
?>