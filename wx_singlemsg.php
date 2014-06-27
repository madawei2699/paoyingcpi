<?php
include_once('./common.php');
$url =  "http://".$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if($_SGLOBAL['login']==false){
gourl('index.php?backurl='.urlencode($url));
exit();
}

$datearr=array( "天 ", "一 ", "二 ", "三 ", "四 ", "五 ", "六 ");                
$ac=$_REQUEST["ac"];
switch ($ac)
{
case "msg_list":
$op_wx=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid']);
$uid=empty($_POST["uid"])?0:intval($_POST["uid"]);
foreach($op_wx as $k=>$v){
 	$op_wxid[]=$v['id'];
}
$search_field=getstr($_POST['search_field']);
$search_keyword=getstr($_POST['search_keyword']);
$page=empty($_POST["page"])?1:intval($_POST["page"]);
$pagesize=empty($_POST["pagesize"])?10:intval($_POST["pagesize"]);
$querystr="";
$queryarray=array();

$queryarray[]='m.uid='.$uid;
$queryarray[]='m.op_wxid '.db_create_in($op_wxid);
if($search_keyword!=""){ $queryarray[]='q.'.$search_field." like '%".$search_keyword."%'";}//end if

$querystr="where 1=1";
foreach($queryarray as $k=>$v){
     $querystr=$querystr." and ".$v;
}

$query=$_SGLOBAL['db']->query("SELECT * from ".tname('weixin_question')." as q inner join ".tname('weixin_member')." as m on q.uid=m.uid ".$querystr);
$total=$_SGLOBAL['db']->num_rows($query);
$pagenum=intval($total/$pagesize);
if($total%$pagesize){ $pagenum++;}
if($page>$pagenum){ $page=$pagenum;}
$offset=$pagesize*($page - 1);
if($offset<0){ $offset=0;}

$sql="SELECT * from ".tname('weixin_question')." as q inner join ".tname('weixin_member')." as m on q.uid=m.uid ".$querystr." order by q.addtime desc limit ".$offset.",".$pagesize;
$list = $_SGLOBAL['db']->getall($sql);

foreach($list as $k=>$v)
{
	$user_headimg='/uploads/weixin_headimg/'.$v['fakeid'].'.png';
	if(file_exists(S_ROOT.'.'.$user_headimg)){
		$list[$k]['headimg']=$user_headimg;
	}else{
		$list[$k]['headimg']='/uploads/weixin_headimg/default.png';
	}
	$list[$k]['weixin_name']=$_SGLOBAL['db']->getone('select weixin_name from '.tname('open_member_weixin').' where id='.$v['op_wxid']);
	$list[$k]['group_name']=$_SGLOBAL['db']->getone('select name from weixin_group where id='.$v['group_id']);
	$list[$k]['replylist']=$_SGLOBAL['db']->getall('select r.content,r.addtime,w.nickname,w.fakeid from '.tname('weixin_reply').' as r left join '.tname('open_member_user').' as o on r.uid=o.uid left join '.tname('weixin_member').' as w on o.weixin_fakeid=w.fakeid where r.question_id='.$v['id']);
	foreach($list[$k]['replylist'] as $key=>$value){
		if($list[$k]['replylist'][$key]['uid']==0){
		$list[$k]['replylist'][$key]['fullname']=$list[$k]['weixin_name'];
		}
		$kf_headimg='/uploads/weixin_headimg/'.$list[$k]['replylist'][$key]['fakeid'].'.png';
		if(file_exists(S_ROOT.'.'.$kf_headimg)){
			$list[$k]['replylist'][$key]['headimg']=$kf_headimg;
		}else{
			$list[$k]['replylist'][$key]['headimg']=$_SC['img_url'].'/weixin_headimg/default.png';
		}
		$list[$k]['replylist'][$key]['addtime']=sgmdate("Y-m-d H:i:s",$list[$k]['replylist'][$key]['addtime']);
	}
	$list[$k]['addtime']=sgmdate("Y-m-d H:i:s",$list[$k]['addtime']);
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
								 foreach($list as $k=>$v){
									$list[$k]['count']=$count+$offset;
								    $arr['list'][]=$list[$k];
									$count++;
								 }
}
echo json_encode($arr);
exit;
break;				
default:
$wxid=getstr($_GET['wxid']);
$user=$_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query('select * from '.tname('weixin_member').' where wxid="'.$wxid.'"'));
$smarty->assign('user',$user);
$smarty->display('wx_singlemsg.dwt');
break;							   
}
?>								
