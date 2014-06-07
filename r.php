<?php
include_once('./common.php');


$h=getstr($_GET['h']);
$hash=substr($h,0,7);
$email= $_SGLOBAL['db']->getone("select email from ".tname("open_email_reg")." where hash='".$hash."' and addtime>'".($_SGLOBAL['timestamp']-(24*3600))."' and used=0");
include_once('./source/function_user.php');
if(is_email($email))
{
    $query= $_SGLOBAL['db']->query("SELECT uid,username,state FROM ".tname("open_member")." where email='".$email."'");
    $user=$_SGLOBAL['db']->fetch_array($query);
	if($user){
	  updatetable(tname('open_member'),array('email_valid'=>1),array('uid'=>$user['uid']));
	  $backurl=$_SGLOBAL['db']->getone("select backurl from ".tname("open_email_reg")." where hash='".$hash."' and addtime>'".($_SGLOBAL['timestamp']-(24*3600))."'");
	  $backurl=empty($backurl)?'user.php':$backurl;

	  $setarr = array(
		'uid' =>$user["uid"],
		'username' => addslashes($user['username']),
		'password' => md5($user["uid"]."|".$_SGLOBAL["timestamp"])//本地密码随机生成
	  );
	  //清理更新在线session
	  insertsession($setarr);


	  $cookietime=0;
      $cookietime=3600 * 24 * 15;
	  //设置cookie
	  ssetcookie('auth', authcode($setarr["password"].' '.$setarr["uid"], 'ENCODE'),$cookietime);
	  ssetcookie('loginuser', $user['username'],$cookietime);
	  ssetcookie('_refer', '');
	  //标记这个登录码使用次数
	  $used= $_SGLOBAL['db']->getone("select used from ".tname("open_email_reg")." where email='".$email."' and hash='".$hash."' and addtime>'".($_SGLOBAL['timestamp']-(24*3600))."'");
	  $used=$used+1;
	  updatetable(tname('open_email_reg'),array('used'=>$used),array('email'=>$email));
	  //页面跳转
	  gourl($backurl);
	  exit();
	}
}
showmessage('验证码错误或已经过期,请重新注册');
gourl('register.php?backurl='.urlencode($backurl));
exit();
?>