<?php
include_once('./common.php');
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit;
}

$datearr=array( "天 ", "一 ", "二 ", "三 ", "四 ", "五 ", "六 ");                
$ac=$_REQUEST["ac"];
switch ($ac)
{
case "update_module":
$id=intval($_POST['id'])?intval($_POST['id']):0;
check_role($id);
$mid=intval($_POST['mid'])?intval($_POST['mid']):0;
$state=intval($_POST['state'])?intval($_POST['state']):0;
$setarr=array(
'op_uid'=>$_SGLOBAL['uid'],
'op_wxid'=>$id,
'mid'=>$mid,
'enabled'=>$state,
'displayorder'=>$mid,
);
inserttable(tname('weixin_module'),$setarr,1,true);
break;
case "upload":
	if($_FILES['file1']['name'] != ""){
		//包含上传文件类
		require_once ('upload.php');
		//设置文件上传目录
		$savePath = "uploads/msgs/";
		//允许的文件类型
		$fileFormat = array('gif','jpg','jpeg','png','bmp');
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
        $jsondata = $jsondata . chr(34)."filename".chr(34).":".chr(34).$_SC['site_host'].'/uploads/msgs/'.$path.chr(34).",";
        $jsondata = $jsondata . chr(34)."msg".chr(34).":".chr(34)."文件上传成功!请不要修改生成的链接地址！".chr(34); 
        $jsondata = $jsondata . "}";
		}//end if
		echo $jsondata;
	}
break;	
case "add":
$smarty->display('wx_account_add.dwt');
break;
case "addprofile":
$account['username']=getstr($_POST['username']);
$account['password']=getstr($_POST['password']);
$account['appid']=getstr($_POST['appid']);
$account['appsecret']=getstr($_POST['appsecret']);
include_once(S_ROOT.'./source/class_weixin.php');
$ro = new WX_Remote_Opera();
$token=$ro->test_login($account['username'],$account['password']);
if($token!=''){	
$ro->init($account['username'],$account['password']);
$info=$ro->get_account_info();	
$setarr=array(
        'op_uid'=>$_SGLOBAL['uid'],
        'ghid'=>$info['ghid'],
		'headimg'=>$_SC['img_url'].'/weixin_headimg/'.$info['fakeid'].'.png',
		'qrcode'=>$_SC['img_url'].'/weixin_qrcode/'.$info['fakeid'].'.png',
        'weixin_name'=>$info['nickname'],
        'username'=>$account['username'],
        'password'=>$account['password'],
        'fakeid'=>$info['fakeid'],
        'state'=>1,
        'appid'=>$account['appid'],
        'appsecret'=>$account['appsecret'],
        'addtime'=>$_SGLOBAL['timestamp'],
);
updatetable(tname('open_member_weixin'),array('state'=>0,'password'=>''),array('username'=>$account['username'],'state'=>1));
$id=inserttable(tname('open_member_weixin'),$setarr,1);

//备份自定义菜单
if(getstr($_POST['appid'])){
$arr=$ro->get_menu(getstr($_POST['appid']),getstr($_POST['appsecret']));
if(!$arr['errcode']){
$_SGLOBAL['db']->query('delete from '.tname('open_member_weixin_custommenu').' where wxid='.$id);
foreach($arr['menu']['button'] as $k=>$v){
	$parent_id=inserttable(tname('open_member_weixin_custommenu'),array(
	'wxid'=>$id,
	'sort_order'=>$k,
	'btn_type'=>$v['type']=='view'?2:1,
	'btn_name'=>$v['name']?$v['name']:'',
	'keyword'=>$v['key']?$v['key']:'',
	'url'=>$v['url']?$v['url']:'',
	'addtime'=>$_SGLOBAL['timestamp']
	),1);
	if($v['sub_button']){
      foreach($arr['menu']['button'][$k]['sub_button'] as $key=>$value){
		  inserttable(tname('open_member_weixin_custommenu'),array(
	         'wxid'=>$id,
			 'parent_id'=>$parent_id,
	         'sort_order'=>$key,
	         'btn_type'=>$value['type']=='view'?2:1,
	         'btn_name'=>$value['name']?$value['name']:'',
	         'keyword'=>$value['key']?$value['key']:'',
	         'url'=>$value['url']?$value['url']:'',
	         'addtime'=>$_SGLOBAL['timestamp']
	      ));
	  }
	}
}
}	
}

$ro->getheadimg($info['fakeid']);
$ro->getqrcode($info['fakeid']);
$ro->quick_set_api($_SC['api_token'],$_SC['api_url']);
if(!$arr['errcode']){
$ro->create_menu(getstr($_POST['appid']),getstr($_POST['appsecret']),urldecode(json_encode($arr['menu'])));
}
}else{
	showmessage('公众号用户名或密码错误，绑定失败');
}
gourl('wx_account.php');
exit;	
break;							   
case "edit":	
$id=intval($_GET['id'])?intval($_GET['id']):0;
check_role($id);

$query=$_SGLOBAL['db']->query('select id,username,appid,appsecret from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid'].' and id='.$id);
if($account=$_SGLOBAL['db']->fetch_array($query)){
	   $account['headimg']=$_SC['img_url'].'/weixin_headimg/'.$account['fakeid'].'.png';

   $smarty->assign('account',$account);
}
$smarty->display('wx_account_edit.dwt');
break;
case "editprofile":
$id=intval($_POST['id'])?intval($_POST['id']):0;
$account['username']=getstr($_POST['username']);
$account['password']=getstr($_POST['password']);
$account['appid']=getstr($_POST['appid']);
$account['appsecret']=getstr($_POST['appsecret']);

check_role($id);

include_once(S_ROOT.'./source/class_weixin.php');
$ro = new WX_Remote_Opera();
$token=$ro->test_login($account['username'],$account['password']);
if($token!=''){
$ro->init($account['username'],$account['password']);
$info=$ro->get_account_info();	
$setarr=array(
                   'ghid'=>$info['ghid'],
		           'headimg'=>$_SC['img_url'].'/weixin_headimg/'.$info['fakeid'].'.png',
		           'qrcode'=>$_SC['img_url'].'/weixin_qrcode/'.$info['fakeid'].'.png',
                   'weixin_name'=>$info['nickname'],
                   'username'=>$account['username'],
                   'password'=>$account['password'],
                   'fakeid'=>$info['fakeid'],
                   'state'=>1,
                   'appid'=>$account['appid'],
                   'appsecret'=>$account['appsecret'],
);
updatetable(tname('open_member_weixin'),array('state'=>0,'password'=>''),array('username'=>$account['username'],'state'=>1));
updatetable(tname('open_member_weixin'),$setarr,array('op_uid'=>$_SGLOBAL['uid'],'id'=>$id));
updatetable(tname('weixin_member'),array('state'=>1),array('op_wxid'=>$id));

//备份自定义菜单
if(getstr($_POST['appid'])){
$arr=$ro->get_menu(getstr($_POST['appid']),getstr($_POST['appsecret']));
if(!$arr['errcode']){
$_SGLOBAL['db']->query('delete from '.tname('open_member_weixin_custommenu').' where wxid='.$id);
foreach($arr['menu']['button'] as $k=>$v){
	$parent_id=inserttable(tname('open_member_weixin_custommenu'),array(
	'wxid'=>$id,
	'sort_order'=>$k,
	'btn_type'=>$v['type']=='view'?2:1,
	'btn_name'=>$v['name']?$v['name']:'',
	'keyword'=>$v['key']?$v['key']:'',
	'url'=>$v['url']?$v['url']:'',
	'addtime'=>$_SGLOBAL['timestamp']
	),1);
	if($v['sub_button']){
      foreach($arr['menu']['button'][$k]['sub_button'] as $key=>$value){
		  inserttable(tname('open_member_weixin_custommenu'),array(
	         'wxid'=>$id,
			 'parent_id'=>$parent_id,
	         'sort_order'=>$key,
	         'btn_type'=>$value['type']=='view'?2:1,
	         'btn_name'=>$value['name']?$value['name']:'',
	         'keyword'=>$value['key']?$value['key']:'',
	         'url'=>$value['url']?$value['url']:'',
	         'addtime'=>$_SGLOBAL['timestamp']
	      ));
	  }
	}
}
}	
}

$ro->getheadimg($info['fakeid']);
$ro->getqrcode($info['fakeid']);
$ro->quick_set_api($_SC['api_token'],$_SC['api_url']);
if(!$arr['errcode']){
  $ro->create_menu(getstr($_POST['appid']),getstr($_POST['appsecret']),urldecode(json_encode($arr['menu'])));
}
}else{
	showmessage('公众号用户名或密码错误，绑定失败');
}

gourl('wx_account.php');
exit;	
break;
case "pushedit":
$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_pushweixin').' where op_uid='.$_SGLOBAL['uid']);
if($account=$_SGLOBAL['db']->fetch_array($query)){	
   $smarty->assign('account',$account);
}
$smarty->display('wx_account_pushedit.dwt');
break;
case "pusheditprofile":
$username=getstr($_POST['username']);
$password=getstr($_POST['password']);

include_once(S_ROOT.'./source/class_weixin.php');
$ro = new WX_Remote_Opera();
$token=$ro->test_login($username,$password);
if($token!=''){
$beenadd=getcount(tname('open_member_weixin'),array('username'=>$username,'state'=>1));	

if($beenadd>0){
   showmessage('此公众号已被绑定，不能作为推送号');
   gourl('wx_account.php?ac=pushedit');
   exit;
}
	
$ro->init($username,$password);	
$info=$ro->get_account_info();
$setarr=array(
'ghid'=>$info['ghid'],
'weixin_name'=>$info['nickname'],
'username'=>getstr($_POST['username']),
'password'=>getstr($_POST['password']),
'signature'=>$info['signature'],
'country'=>$info['country'],
'province'=>$info['province'],
'city'=>$info['city'],
'verifyInfo'=>$info['verifyInfo'],
'bindUserName'=>$info['bindUserName'],
'account'=>$info['account'],
'fakeid'=>$info['fakeid'],
'state'=>1,
);
if($_SGLOBAL['db']->getone('select id from '.tname('open_member_pushweixin').' where op_uid='.$_SGLOBAL['uid'])){
  updatetable(tname('open_member_pushweixin'),$setarr,array('op_uid'=>$_SGLOBAL['uid'])); 
}else{
  $setarr['op_uid']=$_SGLOBAL['uid'];	
  $Setarr['addtime']=$_SGLOBAL['timestamp'];
  inserttable(tname('open_member_pushweixin'),$setarr);	
}
updatetable(tname('open_member_user'),array('weixin_state'=>0),array('op_uid'=>$_SGLOBAL['uid']));

$ro->getheadimg($info['fakeid']);
$ro->getqrcode($info['fakeid']);
$ro->quick_set_api($_SC['push_api_token'],$_SC['push_api_url']);
}else{
   showmessage('微信用户名或密码错误，或者此微信已被设置');
   gourl('wx_account.php?ac=pushedit');
   exit;	
}
gourl('wx_account.php?ac=pushedit');
exit;	
break;
case "manage":
$op_uid=$_SGLOBAL['uid'];
$id=intval($_GET['id'])?intval($_GET['id']):0;
check_role($id);
$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid'].' and id='.$id.' and state>-1');
if($account=$_SGLOBAL['db']->fetch_array($query)){
   $account['headimg']=$_SC['img_url'].'/weixin_headimg/'.$account['fakeid'].'.png';
   $smarty->assign('account',$account);
}

//获取所有模块
$modules=$_SGLOBAL['db']->getall('select * from '.tname('open_module').' where ispublic=1');
foreach($modules as $k=>$v){
   $modules[$k]['enabled']=getcount(tname('weixin_module'),array('op_uid'=>$op_uid,'op_wxid'=>$id,'mid'=>$v['mid'],'enabled'=>1));
}
$smarty->assign('modules',$modules);
$smarty->display('wx_account_manage.dwt');
break;
case "del":
$id=intval($_GET['id'])?intval($_GET['id']):0;
check_role($id);
updatetable(tname('open_member_weixin'),array('password'=>'','state'=>-1),array('id'=>$id));
updatetable(tname('weixin_member'),array('state'=>-1),array('op_wxid'=>$id));
gourl('wx_account.php');
break;
default:
$total=getcount(tname('open_member_weixin'),array('op_uid'=>$_SGLOBAL['uid']));
$smarty->assign('total',$total);
$account=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid'].' and state>-1');
foreach($account as $k=>$v){
  $account[$k]['weidian_state']=$_SGLOBAL['db']->getone('select value from '.tname('wz_weixin_setting').' where op_wxid='.$v['id'].' and mid=1 and var="state"');	
  $account[$k]['headimg']=$_SC['img_url'].'/weixin_headimg/'.$v['fakeid'].'.png';
}
$smarty->assign('account',$account);
$smarty->display('wx_account.dwt');
break;							   
}

function check_role($id){
 global $_SGLOBAL;
 if(!getcount(tname('open_member_weixin'),array('id'=>$id,'op_uid'=>$_SGLOBAL['uid']))){
	exit(); 
 }
}

?>								
