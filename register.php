<?php
include_once('./common.php');
$backurl=empty($_GET['backurl'])?'user.php':$_GET['backurl'];
$smarty->assign('backurl', $backurl);
$ac=$_POST["ac"];
switch ($ac)
{
case "check_email":
$email=getstr($_POST["email"]);
$checkemail=getcount(tname('open_member'),array('email'=>$email));
include_once('./source/function_user.php');
if(is_email($email) && $checkemail==0){
$arr['err']=0;
}else{
$arr['err']=1;
}
echo json_encode($arr);
break;
case "reg":
$email=getstr($_POST["email"]);
$pass1=getstr($_POST["pass1"]);
$pass2=getstr($_POST["pass2"]);

if(submitcheck('_submit')) {
       $total=getcount(tname('open_member'),array('email'=>$email));	   
       include_once(S_ROOT.'./source/function_user.php');
       if(is_email($email) && $total==0 && $pass1==$pass2 && $pass1!=''){
                $salt=random(6);
	            $passwordmd5=md5($pass1);
	            $password=md5($passwordmd5.$salt);
                $setarr=array(
                   "email"=>$email,
                   "username"=>$email,
	               "password"=>$password,
	               "salt"=>$salt,
	               "state"=>1,
	               "email_valid"=>1,  //开启邮箱验证后，把这里设为0，这样必须验证邮箱才能登录
                   "regtime"=>$_SGLOBAL['timestamp']
                );
	            $uid=$_SGLOBAL['db']->getone("select uid from ".tname("open_member")." where email='$email'");
	            if(!$uid){
                   $uid=inserttable(tname("open_member"), $setarr,1);
	            }else{
                    updatetable(tname("open_member"),$setarr,array("uid"=>$uid));	  
	            }

			   //生成邮箱验证链接
			   $backurl=$_POST['backurl'];
               $email_reg_url=email_reg($email,$backurl);
			   //end邮箱验证链接

			   //加入验证文字
			   $reg_msg='欢迎注册'.$_SC['site_name'].'。<br />请点击以下链接，完成'.$_SC['site_name'].'的注册：<br /><a href="'.$email_reg_url.'">点此链接</a> <br /> 或者复制以下字符串到浏览器地址栏：<br />'.$email_reg_url.' <br />
如您有任何问题，请发邮件至 service@sylai.com或私信新浪微博账号： <a href="http://weibo.com/sylaicom">@乘亿科技</a> <br /><br /><br /><br /><br /><br />'.date("Y-m-d");
				
			   //发送验证邮件,请设置了邮件信息后再取消注释
			   //include_once(S_ROOT.'./source/function_sendmail.php');
			   //$email_result=sendmail($email,$_SC['sitename'].'注册确认',$reg_msg);
				
               showmessage('请登录您的邮箱完成注册!');
			   gourl('index.php');	  
			   exit;
      }else{
           showmessage('表单有误，请重新填写!');
	       $arr['err']=2;
	       gourl('register.php');
		   exit();
      }
}
$arr['err']=3;
gourl('register.php');
break;
default:
$smarty->display('register.dwt');
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