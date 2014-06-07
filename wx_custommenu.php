
<?php
include_once('./common.php');

$ac=$_REQUEST["ac"];
switch ($ac)
{
case "get":
$id=intval($_REQUEST['id'])?intval($_REQUEST['id']):0;
check_role($id);
include_once(S_ROOT.'./source/class_weixin.php');
$appid=$_SGLOBAL['db']->getone('select appid from '.tname('open_member_weixin').' where id='.$id);
$appsecret=$_SGLOBAL['db']->getone('select appsecret from '.tname('open_member_weixin').' where id='.$id);					
$ro = new WX_Remote_Opera();
$arr=$ro->get_menu($appid,$appsecret);
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
//print_r($arr);
  echo 0;
}else{
  echo 1;	
}
break;
case "add":
$parent_id=intval($_GET['parent_id'])?intval($_GET['parent_id']):0;
$id=intval($_GET['id'])?intval($_GET['id']):0;
$smarty->assign('id',$id);
if($id>0) check_role($id);
$smarty->assign('parent_id',$parent_id);

$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid'].' and id='.$id);
if($account=$_SGLOBAL['db']->fetch_array($query)){
   $account['headimg']=$_SC['siteurl'].'uploads/weixin_headimg/'.$account['fakeid'].'.png';
   $smarty->assign('account',$account);
}


$smarty->display('wx_account_custommenu_add.dwt');
break;
case "addprofile":
$wxid=intval($_POST['wxid'])?intval($_POST['wxid']):0;
$parent_id=intval($_POST['parent_id'])?intval($_POST['parent_id']):0;
$setarr=array(
'wxid'=>$wxid,
'parent_id'=>$parent_id,
'sort_order'=>intval($_POST['sort_order'])?intval($_POST['sort_order']):0,
'btn_type'=>intval($_POST['btn_type'])?intval($_POST['btn_type']):0,
'btn_name'=>getstr($_POST['btn_name']),
'keyword'=>getstr($_POST['keyword']),
'url'=>getstr($_POST['url']),
'addtime'=>$_SGLOBAL['timestamp'],
);
$count=getcount(tname('open_member_weixin_custommenu'),array('wxid'=>$wxid,'parent_id'=>0));
if($parent_id==0 && $count>2){
	showmessage('自定义菜单主按钮不能超过3个');
	gourl('wx_custommenu.php?id='.$wxid);
    exit;	
}

$count=getcount(tname('open_member_weixin_custommenu'),array('wxid'=>$wxid,'parent_id'=>$parent_id));
if($parent_id>0 && $count>4){
	showmessage('自定义菜单子按钮不能超过5个');
	gourl('wx_custommenu.php?id='.$wxid);
    exit;	
}
$new_id=inserttable(tname('open_member_weixin_custommenu'),$setarr,1);
gourl('wx_custommenu.php?id='.$wxid);
break;							   
case "edit":	
$id=intval($_GET['id'])?intval($_GET['id']):0;
$wxid=$_SGLOBAL['db']->getone('select wxid from '.tname('open_member_weixin_custommenu').' where id='.$id);
check_role($wxid);

$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid'].' and id='.$wxid);
if($account=$_SGLOBAL['db']->fetch_array($query)){
   $account['headimg']=$_SC['siteurl'].'uploads/weixin_headimg/'.$account['fakeid'].'.png';
   $smarty->assign('account',$account);
}


$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_custommenu').' where id='.$id);
if($btn=$_SGLOBAL['db']->fetch_array($query)){
   $smarty->assign('btn',$btn);
}
$smarty->display('wx_account_custommenu_edit.dwt');
break;
case "editprofile":
$id=intval($_POST['id'])?intval($_POST['id']):0;
$wxid=$_SGLOBAL['db']->getone('select wxid from '.tname('open_member_weixin_custommenu').' where id='.$id);
check_role($wxid);

$setarr=array(
'sort_order'=>intval($_POST['sort_order'])?intval($_POST['sort_order']):0,
'btn_type'=>intval($_POST['btn_type'])?intval($_POST['btn_type']):0,
'btn_name'=>getstr($_POST['btn_name']),
'keyword'=>getstr($_POST['keyword']),
'url'=>getstr($_POST['url']),
);
updatetable(tname('open_member_weixin_custommenu'),$setarr,array('id'=>$id));
gourl('wx_custommenu.php?id='.$wxid);
exit;	
break;
case "del":
$id=intval($_GET['id'])?intval($_GET['id']):0;
$wxid=$_SGLOBAL['db']->getone('select wxid from '.tname('open_member_weixin_custommenu').' where id='.$id);
check_role($wxid);
$_SGLOBAL['db']->query('delete from '.tname('open_member_weixin_custommenu').' where id='.$id);
$_SGLOBAL['db']->query('delete from '.tname('open_member_weixin_custommenu').' where parent_id='.$id);
gourl('wx_custommenu.php?id='.$wxid);
break;
case "update":
$id=intval($_REQUEST['id'])?intval($_REQUEST['id']):0;
check_role($id);
$list=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_custommenu').' where parent_id=0 and wxid='.$id.' order by sort_order');
foreach($list as $k=>$v){
	$data['button'][$k]['name']=urlencode($v['btn_name']);
	if($v['btn_type']==1){
	      $data['button'][$k]['type']='click';
		  $data['button'][$k]['key']=urlencode($v['keyword']);

	}
	
	if($v['btn_type']==2){
		 $data['button'][$k]['type']='view';
	     $data['button'][$k]['url']=urlencode($v['url']);
	}
	
	$list[$k]['son']=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_custommenu').' where parent_id='.$v['id'].' order by sort_order');
    foreach($list[$k]['son'] as $key=>$value){
      $data['button'][$k]['sub_button'][$key]['name']=urlencode($value['btn_name']);
	  if($value['btn_type']==1){
		   $data['button'][$k]['sub_button'][$key]['type']='click';
	       $data['button'][$k]['sub_button'][$key]['key']=urlencode($value['keyword']);
	  }
	  if($value['btn_type']==2){
		   $data['button'][$k]['sub_button'][$key]['type']='view';
	       $data['button'][$k]['sub_button'][$key]['url']=urlencode($value['url']);
	  }
	}
}
//print_r(urldecode(json_encode($data)));
$appid=$_SGLOBAL['db']->getone('select appid from '.tname('open_member_weixin').' where id='.$id);
$appsecret=$_SGLOBAL['db']->getone('select appsecret from '.tname('open_member_weixin').' where id='.$id);					
include_once(S_ROOT.'./source/class_weixin.php');
$ro = new WX_Remote_Opera();
$return=$ro->create_menu($appid,$appsecret,urldecode(json_encode($data)));
echo json_encode($return);	
//print_r($return);

break;
default:
$id=intval($_GET['id'])?intval($_GET['id']):0;
check_role($id);
$smarty->assign('id',$id);
$total=getcount(tname('open_member_weixin_custommenu'),array('wxid'=>$id));
$smarty->assign('total',$total);

$list=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_custommenu').' where parent_id=0 and wxid='.$id.' order by sort_order');
foreach($list as $k=>$v){
	if($v['btn_type']==1) $list[$k]['btn_type_show']='关键词';
	if($v['btn_type']==2) $list[$k]['btn_type_show']='网址跳转';
	$list[$k]['son']=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_custommenu').' where parent_id='.$v['id'].' order by sort_order');
    foreach($list[$k]['son'] as $key=>$value){
	  if($value['btn_type']==1) $list[$k]['son'][$key]['btn_type_show']='关键词';
	  if($value['btn_type']==2) $list[$k]['son'][$key]['btn_type_show']='网址跳转';
	}
}
$smarty->assign('list',$list);


$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid'].' and id='.$id);
if($account=$_SGLOBAL['db']->fetch_array($query)){
   $account['headimg']=$_SC['siteurl'].'uploads/weixin_headimg/'.$account['fakeid'].'.png';
   $smarty->assign('account',$account);
}
$smarty->display('wx_account_custommenu.dwt');
}

function check_role($id){
 global $_SGLOBAL;
 if(!getcount(tname('open_member_weixin'),array('id'=>$id,'op_uid'=>$_SGLOBAL['uid']))){
	exit(); 
 }
}
?>