<?php
include_once('./common.php');

$backurl=empty($_GET['backurl'])?'user.php':$_GET['backurl'];
if($_SGLOBAL['login']==true){
gourl($backurl);
exit();
}

$ac=$_POST["ac"];
switch ($ac)
{
case "backpass":
$email=getstr($_POST["email"]);

if(submitcheck('_submit')) {
       $query= $_SGLOBAL['db']->query("SELECT uid FROM ".tname("open_member")." where email_valid=1 and email='".$email."'");
       $total=$_SGLOBAL['db']->num_rows($query);
       include_once(S_ROOT.'./source/function_user.php');
       if(is_email($email) && $total==1){

			   //生成邮箱验证链接
			   $backurl='profile.php?ac=edit';
               $email_reg_url=email_reg($email,$backurl);
			   //end邮箱验证链接

			   //加入验证文字
			   $reg_msg='点击以下链接,登录'.$_SC['site_name'].'进行修改密码：'.$email_reg_url;
				
			   //发送验证邮件
			   include_once(S_ROOT.'./source/function_sendmail.php');
			   $email_result=sendmail($email,$_SC['site_name'].'密码找回',$reg_msg);
				
               showmessage('找回邮件已经发送!');
			   gourl('index.php');
			   exit();
      }else{
           showmessage('找回邮件已经发送!');
		   gourl('index.php');
	       $arr['err']=2;
		   exit();
      }
}
$arr['err']=3;
gourl('forget.php');
break;
default:
$smarty->display('forget.dwt');
break;
}


function email_reg($email,$backurl=''){
global $_SGLOBAL,$_SC;
				 $email_reg['email']=$email;
				 $email_reg['ip']=getonlineip(1);
			     $email_reg['salt']=random(6);
				 $email_reg['hash']=substr(md5(md5($email).$email_reg['salt']),8,7);
				 $email_reg['addtime']=$_SGLOBAL['timestamp'];
				 $email_reg['used']=0;
				 $email_reg['backurl']=$backurl;
				 $id=inserttable(tname("open_email_reg"),$email_reg,1,1);
				 $h=$email_reg['hash'];
     return $_SC['site_host']."/?r=".$h;
}

?>