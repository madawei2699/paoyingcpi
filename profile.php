<?php
include_once('./common.php');
if($_SGLOBAL['login']==false){
gourl('index.php');
exit();
}

if($_SGLOBAL['role']!=0&&$_SGLOBAL['role']!=1){
	gourl('index.php');
	exit();
}


$ac=$_REQUEST["ac"];
switch ($ac)
{
case "check_email":
$email=getstr($_POST["email"]);
$query= $_SGLOBAL['db']->query("SELECT m.username FROM ".tname("open_member")." as m where m.email_valid=1 and m.email='".$email."'");
$total=$_SGLOBAL['db']->num_rows($query);
include_once('./source/function_user.php');
if(is_email($email) && $total==0){
$arr['err']=0;
}else{
$arr['err']=1;
}
echo json_encode($arr);
break;
case "editprofile":
$page=empty($_POST["page"])?1:intval($_POST["page"]);
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}



$uid=$_SGLOBAL['uid'];
if(!$uid){
gourl('user.php');
exit();
}
$fullname=getstr($_POST['fullname']);
$mobile=getstr($_POST['mobile']);
$pass1=getstr($_POST["pass1"]);
if($pass1!=''){
$salt=random(6);
$passwordmd5=md5($pass1);
$password=md5($passwordmd5.$salt);
$setarr['salt']=$salt;
$setarr['password']=$password;
}
$setarr['fullname']=$fullname;
$setarr['mobile']=$mobile;
if(submitcheck('_submit')) {

	
include_once(S_ROOT.'./source/function_user.php');	 
$query= $_SGLOBAL['db']->query("SELECT * FROM ".tname('open_member')." where uid=".$uid);
$total=$_SGLOBAL['db']->num_rows($query);
if($total==0){
    gourl('user.php');
	exit();
}else{
	
	updatetable(tname('open_member'), $setarr, array('uid'=>$uid));
    showmessage('修改成功');
    gourl('user.php?page='.$page);
	exit;
		
}//end if
gourl('user.php');
exit();
}
break;
case "edit":
$page=empty($_REQUEST["page"])?1:intval($_REQUEST["page"]);
$smarty->assign('page',$page);

$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}

$uid=$_SGLOBAL['uid'];
if(!$uid){
gourl('user.php');
exit();
}
$query=$_SGLOBAL['db']->query("select * from ".tname('open_member')." where uid=".$uid);
$profile=$_SGLOBAL['db']->fetch_array($query);
$smarty->assign('profile', $profile);


$ur_here = '<a href=".">首页</a>';
$ur_here .=' > 修改资料';
$smarty->assign('ur_here',$ur_here);  // 当前位置
$smarty->display('profile_edit.dwt');
break;
default:
break;
}
?>