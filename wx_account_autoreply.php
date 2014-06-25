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
case "upload":
upload();
break;
case "add":
add();
break;
case "add_to_db":
add_to_db();
break;
case "edit":
edit();
break;
case "edit_to_db":
edit_to_db();
break;
case "del":
del();
break;	
default:
autoreply_list();
break;							   
}

function add(){
global $_SGLOBAL,$smarty,$_SC;
  $op_wxid=intval($_GET['id'])?intval($_GET['id']):0;
  check_role($op_wxid);
  $query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin').' where op_uid='.$_SGLOBAL['uid'].' and id='.$op_wxid);
  if($account=$_SGLOBAL['db']->fetch_array($query)){
   if($account['headimg']==''){
     $account['headimg']=$_SC['img_url'].'/weixin_headimg/'.$account['fakeid'].'.png';
   }
   $smarty->assign('account',$account);
  }
  if($groups=$_SGLOBAL['db']->getAll('select * from weixin_group')){
  	$smarty->assign('groups',$groups);
  }
  $smarty->display('wx_account_autoreply_add.dwt');	
}

function add_to_db(){
global $_SGLOBAL,$smarty,$_SC;
 $op_wxid=intval($_POST['op_wxid'])?intval($_POST['op_wxid']):0;
 check_role($op_wxid);
 $type=getstr($_POST['type']);
 $reply_type=getstr($_POST['reply_type']);
 $keyword=getstr($_POST['keyword']);
 $url=getstr($_POST['url']);
 $priority=intval($_POST['priority'])?intval($_POST['priority']):0;
 $group_id=getstr($_POST['group_id']);
 $islike=intval($_POST['islike'])?intval($_POST['islike']):0;

 if($type=='keyword' && $keyword==''){
   $json=array('err'=>1,'errmsg'=>'关键词不能为空');
   echo json_encode($json);	
   exit;	
 }

 switch($reply_type){
	case "text":
	  $content=getstr(strip_tags($_POST['content'],'<br>'));
      inserttable(tname('open_member_weixin_autoreply'),array('op_wxid'=>$op_wxid,'group_id'=>$group_id,'type'=>$type,'reply_type'=>$reply_type,'keyword'=>$keyword,'content'=>$content,'islike'=>$islike,'priority'=>$priority,'state'=>1,'addtime'=>$_SGLOBAL['timestamp'],'last_edit_time'=>$_SGLOBAL['timestamp']));
	break;
	case "single_news":
     $title=getstr($_POST['title']);	
     $desc=getstr($_POST['desc']);	
     $content=getstr($_POST['content']);
     $pic=getstr($_POST['pic']);
     $url=getstr($_POST['url']);
     $autoreply_id=inserttable(tname('open_member_weixin_autoreply'),array('op_wxid'=>$op_wxid,'group_id'=>$group_id,'type'=>$type,'reply_type'=>$reply_type,'keyword'=>$keyword,'islike'=>$islike,'priority'=>$priority,'state'=>1,'addtime'=>$_SGLOBAL['timestamp'],'last_edit_time'=>$_SGLOBAL['timestamp']),1);
     inserttable(tname('open_member_weixin_autoreply_info'),array('autoreply_id'=>$autoreply_id,'title'=>$title,'summary'=>$desc,'content'=>$content,'pic'=>$pic,'url'=>$url,'sort_order'=>0,'addtime'=>$_SGLOBAL['timestamp']));
	break;
	case "multi_news":
       $autoreply_id=inserttable(tname('open_member_weixin_autoreply'),array('op_wxid'=>$op_wxid,'group_id'=>$group_id,'type'=>$type,'reply_type'=>$reply_type,'keyword'=>$keyword,'islike'=>$islike,'priority'=>$priority,'state'=>1,'addtime'=>$_SGLOBAL['timestamp'],'last_edit_time'=>$_SGLOBAL['timestamp']),1);
       $msgitem=json_decode($_POST['msgitem'],true);
       foreach($msgitem as $k=>$v){
          inserttable(tname('open_member_weixin_autoreply_info'),array('autoreply_id'=>$autoreply_id,'title'=>getstr($v['title']),'content'=>getstr($v['content']),'pic'=>getstr($v['pic']),'url'=>getstr($v['url']),'sort_order'=>($k+1),'addtime'=>$_SGLOBAL['timestamp']));	
       }
	break;
	default:
	  $err=1;
 }
 
 if(!$err){
  $json=array('err'=>0,'errmsg'=>'添加成功');
 }else{
  $json=array('err'=>$err,'errmsg'=>'添加失败');
 }
 echo json_encode($json);	
 exit;	
}

function edit(){
global $_SGLOBAL,$smarty,$_SC;
 $id=intval($_GET['id'])?intval($_GET['id']):0;
 $autoreply_id=$id;
 $op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('open_member_weixin_autoreply').' where id="'.$id.'" and state>-1');
 $op_uid=$_SGLOBAL['uid'];
 check_role($op_wxid);
 $query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin').' where op_uid="'.$op_uid.'" and id="'.$op_wxid.'"');
 if($account=$_SGLOBAL['db']->fetch_array($query)){	
   $query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply').' where id="'.$id.'" and state>-1');
   if($account['autoreply']=$_SGLOBAL['db']->fetch_array($query)){
     switch($account['autoreply']['reply_type']){
		case "text":
          $account['autoreply']['content']=htmlspecialchars_decode($account['autoreply']['content']);
          $account['autoreply']['content_textarea']=db_to_content(htmlspecialchars_decode($account['autoreply']['content']));
		break; 
		case "single_news":
          $account['autoreply']['singlenews']=$_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id="'.$autoreply_id.'" and state=1'));
          if(!$account['autoreply']['singlenews']){
	         $account['autoreply']['singlenews']['title']='标题';   
          }else{
	         $account['autoreply']['singlenews']['content']=htmlspecialchars_decode($account['autoreply']['singlenews']['content']);   
          }
		break;
		case "multi_news":
          $account['autoreply']['multinews_num']=getcount(tname('open_member_weixin_autoreply_info'),array('autoreply_id'=>$autoreply_id));   
          $account['autoreply']['multinews']=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id='.$autoreply_id.' and state=1 order by sort_order limit 0,8');
          foreach($account['autoreply']['multinews'] as $k=>$v){
	        $account['autoreply']['multinews'][$k]['content']=htmlspecialchars_decode($v['content']);   
          }
		break; 		 
        }
	 }

     if($account['headimg']==''){
       $account['headimg']=$_SC['img_url'].'/weixin_headimg/'.$account['fakeid'].'.png';
     }
     if($groups=$_SGLOBAL['db']->getAll('select * from weixin_group')){
	  	$smarty->assign('groups',$groups);
	  }
     $smarty->assign('account',$account);
  }
  $smarty->display('wx_account_autoreply_edit.dwt');
}

function edit_to_db(){
global $_SGLOBAL,$smarty,$_SC;
 $id=intval($_POST['id'])?intval($_POST['id']):0;
 $autoreply_id=$id;
 $op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('open_member_weixin_autoreply').' where id="'.$id.'" and state>-1');
 $op_uid=$_SGLOBAL['uid'];
 check_role($op_wxid);

 $type=getstr($_POST['type']);
 $reply_type=getstr($_POST['reply_type']);
 $group_id=getstr($_POST['group_id']);
 $keyword=getstr($_POST['keyword']);
 $url=getstr($_POST['url']);
 $priority=intval($_POST['priority'])?intval($_POST['priority']):0;
 $islike=intval($_POST['islike'])?intval($_POST['islike']):0;

 if($type=='keyword' && $keyword==''){
   $json=array('err'=>1,'errmsg'=>'关键词不能为空');
   echo json_encode($json);	
   exit;	
 }

 switch($reply_type){
	case "text":
	  $content=getstr(strip_tags($_POST['content'],'<br>'));
      updatetable(tname('open_member_weixin_autoreply'),array('type'=>$type,'reply_type'=>$reply_type,'group_id'=>$group_id,'keyword'=>$keyword,'content'=>$content,'islike'=>$islike,'priority'=>$priority,'state'=>1,'last_edit_time'=>$_SGLOBAL['timestamp']),array('id'=>$autoreply_id));
	  updatetable(tname('open_member_weixin_autoreply_info'),array('state'=>-1),array('autoreply_id'=>$autoreply_id));
	break;
	case "single_news":
     $title=getstr($_POST['title']);	
     $desc=getstr($_POST['desc']);	
     $content=getstr($_POST['content']);
     $pic=getstr($_POST['pic']);
     $url=getstr($_POST['url']);
     updatetable(tname('open_member_weixin_autoreply'),array('type'=>$type,'reply_type'=>$reply_type,'group_id'=>$group_id,'keyword'=>$keyword,'islike'=>$islike,'priority'=>$priority,'state'=>1,'last_edit_time'=>$_SGLOBAL['timestamp']),array('id'=>$autoreply_id));
	 updatetable(tname('open_member_weixin_autoreply_info'),array('state'=>-1),array('autoreply_id'=>$autoreply_id));
     inserttable(tname('open_member_weixin_autoreply_info'),array('autoreply_id'=>$autoreply_id,'title'=>$title,'summary'=>$desc,'content'=>$content,'pic'=>$pic,'url'=>$url,'sort_order'=>0,'addtime'=>$_SGLOBAL['timestamp']));
	break;
	case "multi_news":
       updatetable(tname('open_member_weixin_autoreply'),array('type'=>$type,'reply_type'=>$reply_type,'group_id'=>$group_id,'keyword'=>$keyword,'islike'=>$islike,'priority'=>$priority,'state'=>1,'last_edit_time'=>$_SGLOBAL['timestamp']),array('id'=>$autoreply_id));
	   updatetable(tname('open_member_weixin_autoreply_info'),array('state'=>-1),array('autoreply_id'=>$autoreply_id));
       $msgitem=json_decode($_POST['msgitem'],true);
       foreach($msgitem as $k=>$v){
          inserttable(tname('open_member_weixin_autoreply_info'),array('autoreply_id'=>$autoreply_id,'title'=>getstr($v['title']),'content'=>getstr($v['content']),'pic'=>getstr($v['pic']),'url'=>getstr($v['url']),'sort_order'=>($k+1),'addtime'=>$_SGLOBAL['timestamp']));	
       }
	break;
	default:
	  $err=1;
 }
 
 if(!$err){
  $json=array('err'=>0,'errmsg'=>'编辑成功');
 }else{
  $json=array('err'=>$err,'errmsg'=>'编辑失败');
 }
 echo json_encode($json);	
 exit;	
}

function del(){
global $_SGLOBAL,$smarty,$_SC;
 $id=intval($_REQUEST['id'])?intval($_REQUEST['id']):0;
 $op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('open_member_weixin_autoreply').' where id="'.$id.'" and state>-1');
 $op_uid=$_SGLOBAL['uid'];
 check_role($op_wxid);
 updatetable(tname('open_member_weixin_autoreply'),array('state'=>-1,'last_edit_time'=>$_SGLOBAL['timestamp']),array('id'=>$id));
 updatetable(tname('open_member_weixin_autoreply_info'),array('state'=>-1),array('autoreply_id'=>$id));
 $json=array('err'=>0,'errmsg'=>'删除成功');
 echo json_encode($json);	
 exit;
}


function upload(){
global $_SGLOBAL,$smarty,$_SC;
	if($_FILES['file1']['name'] != ""){
		//包含上传文件类
		include_once (S_ROOT.'./upload.php');
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
		  $jsondata=array(
		  'err'=>1,
		  'msg'=>$f->errmsg(),
		  );
		}else{
		  //上传结果保存在数组returnArray中。
          $path=$f->saveName;
		  $jsondata=array(
		  'err'=>0,
		  'filename'=>$_SC['img_url'].'/msgs/'.$path,
		  'msg'=>'文件上传成功!请不要修改生成的链接地址!',
		  );
		
		}//end if
		echo json_encode($jsondata);
		exit;
	}	
}


function autoreply_list(){
global $_SGLOBAL,$smarty,$_SC;
$op_wxid=intval($_GET['id'])?intval($_GET['id']):0;
$op_uid=$_SGLOBAL['uid'];
check_role($op_wxid);
$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin').' where op_uid="'.$op_uid.'" and id="'.$op_wxid.'"');
if($account=$_SGLOBAL['db']->fetch_array($query)){
   if($account['headimg']==''){
     $account['headimg']=$_SC['img_url'].'/weixin_headimg/'.$account['fakeid'].'.png';
   }

$page=empty($_REQUEST["page"])?1:intval($_REQUEST["page"]);
$pagesize=empty($_REQUEST["pagesize"])?10:intval($_REQUEST["pagesize"]);
$type=getstr($_GET['type']);
$querystr="";
$queryarray=array();


$queryarray[]='op_wxid="'.$op_wxid.'"';
$queryarray[]='state>-1';
if($type) $queryarray[]='type="'.$type.'"';


$querystr="where 1=1";
foreach($queryarray as $k=>$v){
     $querystr=$querystr." and ".$v;
}

$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply').' '.$querystr);
$total=$_SGLOBAL['db']->num_rows($query);
$pagenum=intval($total/$pagesize);
if($total%$pagesize){ $pagenum++;}
if($page>$pagenum){ $page=$pagenum;}
$offset=$pagesize*($page - 1);
if($offset<0){ $offset=0;}

$sql='select * from '.tname('open_member_weixin_autoreply').' '.$querystr.' order by priority desc limit '.$offset.','.$pagesize;
$list = $_SGLOBAL['db']->getall($sql);

$type_name=array('focus'=>'关注后回复','aftermsg'=>'默认回复','keyword'=>'关键词回复');
$reply_type_name=array('text'=>'文本回复','single_news'=>'单图文回复','multi_news'=>'多图文回复');

foreach($list as $k=>$v){
  $list[$k]['type_name']=$type_name[$v['type']];
  $list[$k]['reply_type_name']=$reply_type_name[$v['reply_type']];
}


$arr=array(
"pagesize"=>$pagesize,
"page"=>$page,
"nextpage"=>$page+1,
"prepage"=>$page-1,
"next_page_url"=>'wx_account_autoreply.php?type='.$type.'&id='.$op_wxid.'&page='.($page+1),
"pre_page_url"=>'wx_account_autoreply.php?type='.$type.'&id='.$op_wxid.'&page='.($page-1),
"op_wxid"=>$op_wxid,
"type"=>$type,
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
$account=array_merge($arr,$account);
$smarty->assign('account',$account);
}
$smarty->display('wx_account_autoreply.dwt');
}

function check_role($id){
 global $_SGLOBAL;
 if(!getcount(tname('open_member_weixin'),array('id'=>$id,'op_uid'=>$_SGLOBAL['uid']))){
	showmessage('出错啦');
	exit; 
 }
}

?>								
