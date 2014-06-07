<?php
include_once('./common.php');
include_once(S_ROOT.'./source/class_rest.php');
$rest = RestUtils::processRequest();
$request_vars=$rest->getRequestVars();
$data=$rest->getData();
$method=$rest->getMethod();

$token_id=$request_vars[0];
$query=$_SGLOBAL['db']->query('select * from '.tname('wz_token').' where id="'.$token_id.'"');
$token=$_SGLOBAL['db']->fetch_array($query);

if(!$token){
  echo 'wrong site';
  exit;	
}
$_WZ = $token;


$query=$_SGLOBAL['db']->query('select * from '.tname('wz_module').' where id='.$token['mid']);
$module=$_SGLOBAL['db']->fetch_array($query);
if(!$module['id']){
         echo 'wrong module';
exit;	
}

$query=$_SGLOBAL['db']->query('select op_uid from '.tname('open_member_weixin').' where id='.$token['op_wxid'].' and state=1');
$weixin=$_SGLOBAL['db']->fetch_array($query);
if(!$weixin){
  echo 'wrong wx';
  exit;	
}


//获取特定微笑微信用户的模板设置信息
$module['profile']=$_SGLOBAL['db']->getall('select * from '.tname('wz_module_profile').' where op_uid='.$weixin['op_uid'].' and module_id='.$token['mid']);
$module['module_template']=$_SGLOBAL['db']->getone('select value from '.tname('wz_weixin_setting').' where op_wxid='.$token['op_wxid'].' and mid='.$token['mid'].' and var="template"');
if(!$module['module_template']){
  $module['module_template']=$module['module_default_template'];
}


$smarty=template($module,$weixin);
if(wz_checkauth($token['wxid'],$data['token'],$token['mid'],$token['op_wxid'])){
	$_WZ['isauth']=true;
}else{
	$_WZ['isauth']=false;
}
wz_record($data);


$_WZ['op_uid']=$weixin['op_uid'];

define('INDEX', $_SC['site_host'].'/weizhan/'.$token_id.'/');
$template_path=$_SC['site_host'].'/weizhan/module/'.$module['module_dir'].'/themes/' . $module['module_template'];
$smarty->assign('INDEX',INDEX);
$smarty->assign('module_dir',$module['module_dir']);
$smarty->assign('template_path',$template_path);
$smarty->assign('_SC', $_SC);
$smarty->assign('formhash', formhash());  
$smarty->assign('_SGLOBAL', $_SGLOBAL);
$smarty->assign('rand',random(6));
session_save_path(S_ROOT."./data/session_tmp");
session_start();


if(file_exists(S_ROOT.'./module/'. $module['module_dir'] . '/index.php')){
   include_once(S_ROOT.'./module/'. $module['module_dir'] . '/index.php');	
}


/*
$controller_file = S_ROOT."./module/" . $request_vars[0] . "/index.php";
if(!is_file($controller_file)){
	echo json_encode(array('error'=>'wrong controller'));
	exit;
}else{
	require_once(realpath($controller_file));
}
$_SC['myurl']=$_SC['site_host'].'/'.$_GET['url'];
$class_name='ctrl_'.$request_vars[0];
$controller = new $class_name($data,$method);//load the controller
$controller->$request_vars[1]();
*/



?>