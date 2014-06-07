<?php
include_once('./common.php');
$msgid=intval($_GET['id'])?intval($_GET['id']):0;
if(!$msgid){
 exit;	
}


$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply_info').' where id='.$msgid);
$msg=$_SGLOBAL['db']->fetch_array($query);
if(!$msg){
	exit;
}

$msg['content']=htmlspecialchars_decode($msg['content']);
$msg['addtime']=sgmdate("Y-m-d",$msg['addtime']);     
$msg['url']=$_SC['site_host'].'/appmsg/?id='.$msg['id'];    
$smarty->assign('msg',$msg);
$smarty->display('index.dwt');
?>