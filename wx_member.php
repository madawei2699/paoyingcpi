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
default:
$search_field=getstr($_REQUEST['search_field']);
$search_keyword=getstr($_REQUEST['search_keyword']);

$op_wxid=empty($_REQUEST["op_wxid"])?0:intval($_REQUEST["op_wxid"]);
$page=empty($_REQUEST["page"])?1:intval($_REQUEST["page"]);
$pagesize=empty($_REQUEST["pagesize"])?10:intval($_REQUEST["pagesize"]);
$querystr="";
$queryarray=array();


if($op_wxid>0) $queryarray[]='m.op_wxid='.$op_wxid;
$queryarray[]='w.op_uid='.$_SGLOBAL['uid'];


$querystr="where 1=1";
foreach($queryarray as $k=>$v){
     $querystr=$querystr." and ".$v;
}

$query=$_SGLOBAL['db']->query('select m.*,w.weixin_name from '.tname('weixin_member').' as m inner join '.tname('open_member_weixin').' as w on m.op_wxid=w.id '.$querystr);
$total=$_SGLOBAL['db']->num_rows($query);
$pagenum=intval($total/$pagesize);
if($total%$pagesize){ $pagenum++;}
if($page>$pagenum){ $page=$pagenum;}
$offset=$pagesize*($page - 1);
if($offset<0){ $offset=0;}

$sql='select m.*,w.weixin_name from '.tname('weixin_member').' as m inner join '.tname('open_member_weixin').' as w on m.op_wxid=w.id '.$querystr.' order by m.create_time desc limit '.$offset.','.$pagesize;
$list = $_SGLOBAL['db']->getall($sql);
foreach($list as $k=>$v){
  $list[$k]['headimg']=$_SC['img_url'].'/weixin_headimg/'.$v['fakeid'].'.png';
  $list[$k]['profile']=$_SGLOBAL['db']->getall('select * from '.tname('weixin_member_profile').' where uid='.$v['uid']);
  foreach($list[$k]['profile'] as $key=>$value){
	if($value['name']=='姓名'){
	   	$list[$k]['fullname']=$value['value'];
	}
  }
  	if(!$list[$k]['fullname']) $list[$k]['fullname']=$list[$k]['nickname'];
}


$arr=array(
"pagesize"=>$pagesize,
"page"=>$page,
"nextpage"=>$page+1,
"prepage"=>$page-1,
"op_wxid"=>$op_wxid,
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
//echo json_encode($arr);
$smarty->assign('members',$arr);

$account=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid']);
$smarty->assign('account',$account);
$smarty->display('wx_member.dwt');
break;							   
}
?>								
