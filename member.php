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
case "check_email":
$email=getstr($_POST["email"]);
$total=getcount(tname('open_member_user'),array('op_uid'=>$_SGLOBAL['uid'],'email'=>$email));	   
include_once('./source/function_user.php');
if(is_email($email) && $total==0){
$arr['err']=0;
}else{
$arr['err']=1;
}
echo json_encode($arr);
break;
case "upload_avator":
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}

	
	if($_FILES['file1']['name'] != ""){
		//包含上传文件类
		require_once ('upload.php');
		//设置文件上传目录
		$savePath = "uploads/avators/";
		//允许的文件类型
		$fileFormat = array('gif','jpg','jpge','png','bmp');
		//文件大小限制，单位: Byte，1KB = 1000 Byte
		//0 表示无限制，但受php.ini中upload_max_filesize设置影响
		$maxSize = 0;
		//覆盖原有文件吗？ 0 不允许  1 允许 
		$overwrite = 1;
		//初始化上传类
		$f = new Upload( $savePath, $fileFormat, $maxSize, $overwrite);
		//如果想生成缩略图，则调用成员函数 $f->setThumb();
		//参数列表: setThumb($thumb, $thumbWidth = 0,$thumbHeight = 0)
		//$thumb=1 表示要生成缩略图，不调用时，其值为 0
		//$thumbWidth  缩略图宽，单位是像素(px)，留空则使用默认值 130
		//$thumbHeight 缩略图高，单位是像素(px)，留空则使用默认值 130
		//$f->setThumb(1);
		
		//参数中的uploadinput是表单中上传文件输入框input的名字
		//后面的0表示不更改文件名，若为1，则由系统生成随机文件名
		if (!$f->run('file1',1)){
			//通过$f->errmsg()只能得到最后一个出错的信息，
			//详细的信息在$f->getInfo()中可以得到。
			$jsondata ="{";
		    $jsondata = $jsondata . chr(34)."err".chr(34).":1,"; 
            $jsondata = $jsondata . chr(34)."msg".chr(34).":".chr(34).$f->errmsg().chr(34);
			$jsondata = $jsondata . "}";
		}else{
		//上传结果保存在数组returnArray中。
        $path=$f->saveName;
        $jsondata = $jsondata . "{";
        $jsondata = $jsondata . chr(34)."err".chr(34).":0,"; 
        $jsondata = $jsondata . chr(34)."filename".chr(34).":".chr(34).$path.chr(34).",";
        $jsondata = $jsondata . chr(34)."msg".chr(34).":".chr(34)."文件上传成功!请不要修改生成的链接地址！".chr(34); 
        $jsondata = $jsondata . "}";
		}//end if
		echo $jsondata;
	}
break;
case "delmember":
$page=empty($_POST["page"])?1:intval($_POST["page"]);
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}
$uid=intval($_POST['uid'])?intval($_POST['uid']):0;
$check=getcount(tname("open_member_user"),array('op_uid'=>$_SGLOBAL['uid'],'uid'=>$uid));
if(!$check){
gourl('member.php?page='.$page);
exit();
}

$_SGLOBAL['db']->query('delete from '.tname('open_member_user').' where uid='.$uid);
gourl('member.php?page='.$page);
exit();

break;
case "addmember":
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}

$email=getstr($_POST["email"]);

if(submitcheck('_submit')) {
$total=getcount(tname('open_member_user'),array('op_uid'=>$_SGLOBAL['uid'],'email'=>$email));	   
include_once('./source/function_user.php');
if(is_email($email) && $total==0){

$setarr["email"]=$email;
$setarr["regtime"]=$_SGLOBAL['timestamp'];	
$setarr['fullname']=getstr($_POST['fullname']);
$setarr['gender']=getstr($_POST['gender']);
$setarr['mobile']=getstr($_POST['mobile']);
$setarr['avator']=getstr($_POST['avator']);
$setarr['state']=1;
$setarr['weixin_code']=random(4,1);

$setarr["salt"]=random(6);
$pass1=getstr($_POST['password']);
if($pass1==''){
  $pass1=random(6);
}
$passwordmd5=md5($pass1);
$setarr['username']=$email;
$setarr["password"]=md5($passwordmd5.$setarr["salt"]);



//加入验证文字
$fullname= $setarr['fullname'];
$reg_msg=$fullname.'你好,<br />'.'邀请你成为公众号客服，用你的个人微信号来接收回复咨询问题<br />输入:<br />客服绑定@你的手机号@'.$setarr['weixin_code'].'<br />进行绑定。';
				
//发送验证邮件
include_once('./source/function_sendmail.php');
$email_result=sendmail($email,$weixin_name.'邀请您成为客服',$reg_msg);
$setarr['op_uid']=$_SGLOBAL['uid'];
inserttable(tname("open_member_user"),$setarr);
gourl('member.php');
exit();
}
}
gourl('member.php?ac=add');
exit();
break;	
case "editmember":
$page=empty($_POST["page"])?1:intval($_POST["page"]);
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}
$uid=empty($_POST['uid'])?0:getstr($_POST['uid']);
$check=getcount(tname("open_member_user"),array('op_uid'=>$_SGLOBAL['uid'],'uid'=>$uid));
if(!$check){
gourl('member.php');
exit();
}
$setarr['fullname']=getstr($_POST['fullname']);
$setarr['gender']=getstr($_POST['gender']);
$setarr['corp']=getstr($_POST['corp']);
$setarr['position']=getstr($_POST['position']);
$setarr['country']=empty($_POST['country'])?0:getstr($_POST['country']);
$setarr['province']=empty($_POST['province'])?0:getstr($_POST['province']);
$setarr['city']=empty($_POST['city'])?0:getstr($_POST['city']);
$setarr['district']=empty($_POST['district'])?0:getstr($_POST['district']);
$setarr['mobile']=getstr($_POST['mobile']);
$setarr['avator']=getstr($_POST['avator']);
$setarr['intro']=content_to_db($_POST['intro']);
$setarr['tag']=getstr($_POST['tag']);

$pass1=getstr($_POST['password']);
if($pass1!=''){
$setarr["salt"]=random(6);
$passwordmd5=md5($pass1);
$setarr["password"]=md5($passwordmd5.$setarr["salt"]);
}

if(submitcheck('_submit')) {
if($setarr['fullname']==''||$setarr['mobile']==''){
     gourl('user.php');
	 exit();
}


updatetable(tname('open_member_user'), $setarr, array('uid'=>$uid));

gourl('member.php?page='.$page);
exit();
}
break;
case "add":
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}

$smarty->display('member_add.dwt');

break;
case "edit":
$page=empty($_REQUEST["page"])?1:intval($_REQUEST["page"]);
$smarty->assign('page',$page);

$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}

$uid=empty($_REQUEST['uid'])?0:intval($_REQUEST['uid']);
$check=getcount(tname("open_member_user"),array('op_uid'=>$_SGLOBAL['uid'],'uid'=>$uid));
if(!$check){
gourl('member.php');
exit();
}
$query=$_SGLOBAL['db']->query("select * from ".tname('open_member_user')." where uid=".$uid);
$profile=$_SGLOBAL['db']->fetch_array($query);
$profile['gender_id'] = $profile['gender'];
$profile['gender']=$gender[$profile['gender']];
if($profile['avator']==''){ $profile['avator']='user1.jpg';}
$profile['avator_file']=$profile['avator'];
$profile['avator']='/uploads/avators/'.$profile['avator'];
$profile['country_id'] = $profile['country'];
$profile['province_id'] = $profile['province'];
$profile['city_id'] = $profile['city'];
$profile['district_id'] = $profile['district'];
$profile['country'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$profile['country']);
$profile['province'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$profile['province']);
$profile['city'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$profile['city']);
$profile['district'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$profile['district']);
$profile['intro'] = db_to_content($profile['intro']);

$smarty->assign('profile', $profile);

$smarty->display('member_edit.dwt');
break;		
case "member_list":
$search_field=getstr($_POST['search_field']);
$search_keyword=getstr($_POST['search_keyword']);
$page=empty($_POST["page"])?1:intval($_POST["page"]);
$pagesize=empty($_POST["pagesize"])?6:intval($_POST["pagesize"]);
$querystr="";
$queryarray=array();

$queryarray[]='m.op_uid='.$_SGLOBAL['uid'];
if($search_keyword!=""){ $queryarray[]=$search_field." like '%".$search_keyword."%'";}//end if
$querystr="where 1=1";
foreach($queryarray as $k=>$v){
     $querystr=$querystr." and ".$v;
}

$query=$_SGLOBAL['db']->query("SELECT m.* from ".tname('open_member_user')." as m ".$querystr);
$total=$_SGLOBAL['db']->num_rows($query);
$pagenum=intval($total/$pagesize);
if($total%$pagesize){ $pagenum++;}
if($page>$pagenum){ $page=$pagenum;}
$offset=$pagesize*($page - 1);
if($offset<0){ $offset=0;}

$sql="SELECT m.* from ".tname('open_member_user')." as m ".$querystr." order by m.uid desc limit ".$offset.",".$pagesize;
$member_list = $_SGLOBAL['db']->getall($sql);

foreach($member_list as $k=>$v)
{
	if($member_list[$k]['avator']==''){
		if($member_list[$k]['gender']==1){
		 $member_list[$k]['avator']='user1.jpg';
		}else{
			$member_list[$k]['avator']='user2.jpg';
		}
    }
    $member_list[$k]['avator']='/uploads/avators/'.$member_list[$k]['avator'];
		$member_list[$k]['country'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$member_list[$k]['country']);
		$member_list[$k]['province'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$member_list[$k]['province']);
		$member_list[$k]['city'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$member_list[$k]['city']);
		$member_list[$k]['district'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$member_list[$k]['district']);
	
}
$arr=array(
"pagesize"=>$pagesize,
"page"=>$page,
"pagenum"=>$pagenum,
"total"=>$total,
"offset"=>$offset,
"err"=>0
);

if($total>0){
	                             $count=1;
								 foreach($member_list as $k=>$v){
									$member_list[$k]['count']=$count+$offset;
								    $arr['member_list'][]=$member_list[$k];
									$count++;
								 }
}
echo json_encode($arr);
exit;
break;		
default:
$page=empty($_REQUEST["page"])?1:intval($_REQUEST["page"]);
$smarty->assign('page',$page);
$smarty->display('member.dwt');
break;							   
}



?>								
