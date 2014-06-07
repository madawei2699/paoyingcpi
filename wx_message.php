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
case "send_msg":
$content=getstr($_POST['content']);
$question_id=empty($_POST["question_id"])?0:intval($_POST["question_id"]);
include_once(S_ROOT.'./source/class_weixin.php');
echo send_reply2($question_id,$content);
break;	
case "msg_list":

$op_wx=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin').' where state=1 and op_uid='.$_SGLOBAL['uid']);
foreach($op_wx as $k=>$v){
 	$op_wxid[]=$v['id'];
}
$search_field=getstr($_POST['search_field']);
$search_keyword=getstr($_POST['search_keyword']);
$replyed=empty($_POST["replyed"])?0:intval($_POST["replyed"]);

$page=empty($_POST["page"])?1:intval($_POST["page"]);
$pagesize=empty($_POST["pagesize"])?10:intval($_POST["pagesize"]);
$querystr="";
$queryarray=array();

if($replyed>0){
  if($replyed==1){
       $queryarray[]='q.replyed=0'; //未回复
  }
  if($replyed==2){
       $queryarray[]='q.replyed=1'; //已回复
  }
}

$queryarray[]='m.op_wxid '.db_create_in($op_wxid);
if($search_keyword!=""){
	if($search_field=='nickname'){
       $queryarray[]='m.'.$search_field." like '%".$search_keyword."%'";
	}else{
       $queryarray[]='q.'.$search_field." like '%".$search_keyword."%'";
	}
}//end if

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
	$list[$k]['headimg']='/uploads/weixin_headimg/'.$v['fakeid'].'.png';
	$list[$k]['weixin_name']=$_SGLOBAL['db']->getone('select weixin_name from '.tname('open_member_weixin').' where id='.$v['op_wxid']);
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
$smarty->display('wx_message.dwt');
break;							   
}
?>								
