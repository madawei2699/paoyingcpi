<?php
include_once('./common.php');
$backurl='index.php';
if($_SGLOBAL['login']==true){
gourl($backurl);
exit();
}


$ac=$_POST["ac"];

switch($ac)
{
case "login":
if(submitcheck('_submit')) {


  if($_SC['captcha']){	
    if (empty($_POST['captcha']))
    {
     showmessage('验证码不能为空');
	 gourl('login.php');
	 exit();
    }
  
    include_once('./source/cls_captcha.php');
    $validator = new captcha();
    $validator->session_word = 'captcha_login';
    if (!$validator->check_word(($_POST['captcha'])))
    {
       showmessage('验证码错误');
	   gourl('login.php');
	   exit();
    }
  }
	
	
    $username=getstr($_POST["username"]);
    $passwordmd5=md5(getstr($_POST["password"]));


    include_once('./source/function_user.php');
    //邮箱登陆----------------------------
    if(is_email($username))
    {
           $query= $_SGLOBAL['db']->query("select username from ".tname("open_member")." where email='".$username."' and email_valid=1");
           $username_e = $_SGLOBAL['db']->result($query);
           if($username_e) $username=$username_e;
    }
	
	
	$query= $_SGLOBAL['db']->query("SELECT * FROM ".tname("open_member")." where username='".$username."' and state=1");
    $total= $_SGLOBAL['db']->getone("SELECT count(uid) FROM ".tname("open_member")." where username='".$username."' and state=1");
    if($total==0){
          //不存在此用户
          showmessage('用户名或密码错误!');
          $arr['err']=1;
          //echo json_encode($arr);
          gourl($backurl);
		  exit();
    }else{
         $rs=$_SGLOBAL['db']->fetch_array($query);
         if($rs["password"]!=md5($passwordmd5.$rs["salt"])){
           //用户名或密码错误
           showmessage('用户名或密码错误!');
           $arr['err']=2;
           //echo json_encode($arr);
           gourl($backurl);
		   exit();
         }elseif($rs['state']!=1){
           //帐号未开通
           showmessage('帐号未开通!');
           $arr['err']=3;
           //echo json_encode($arr);
           gourl($backurl);
		   exit();
			 
         }else{
           //$jsondata = $jsondata . chr(34)."err".chr(34).":0";
	       $setarr = array(
		   'uid' =>$rs["uid"],
		   'username' => addslashes($rs['username']),
		   'password' => md5($rs["uid"]."|".$_SGLOBAL["timestamp"])//本地密码随机生成
	       );
	       //清理在线session
	       insertsession($setarr);
    
	       $cookietime=0;
	       if($_POST['remeber']){
	          $cookietime=3600 * 24 * 15;
	       }
	       //设置cookie
	       ssetcookie('auth', authcode($setarr["password"].' '.$setarr["uid"], 'ENCODE'),$cookietime);
	       ssetcookie('loginuser', $rs['username'],$cookietime);
	       ssetcookie('_refer', '');		
		   $arr['err']=0;
           gourl($backurl);
		   exit();
        }//end if
   }//end if
   gourl($backurl);
   exit();  
}else{
   $arr['err']=4;
  // echo json_encode($arr);
  gourl($backurl);
  exit();
}//end submitcheck
break;
default:
gourl('index.php');
exit;
//$smarty->display('login.dwt');
}
?>