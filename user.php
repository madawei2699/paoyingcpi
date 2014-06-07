<?php
include_once('./common.php');
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php');
exit();
}

if($_SGLOBAL['role']!=0&&$_SGLOBAL['role']!=1){
	gourl('index.php');
	exit();
}

$datearr=array( "天 ", "一 ", "二 ", "三 ", "四 ", "五 ", "六 ");                
$ac=$_REQUEST["ac"];
switch ($ac)
{
default:
gourl('wx_account.php');
exit;
$smarty->display('user.dwt');
break;							   
}
?>								
