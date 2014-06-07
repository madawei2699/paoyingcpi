<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}


function wz_checkauth($wxid,$token,$mid,$op_wxid){
	global $_SGLOBAL;
    if($_COOKIE['site_auth']){
		@list($password, $token_id) = explode(" ", authcode($_COOKIE['site_auth'], 'DECODE'));
		$_SGLOBAL['supe_token_id'] = intval($token_id);
		if($password && $_SGLOBAL['supe_token_id']){			
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("wz_session")." WHERE token_id=".$_SGLOBAL['supe_token_id']);
			if($session = $_SGLOBAL['db']->fetch_array($query)) {
                    if($session['password'] == $password) {
				        $token_mid=$_SGLOBAL['db']->getone('select mid from '.tname('wz_token').' where id='.$_SGLOBAL['supe_token_id']);
				        $token_op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('wz_token').' where id='.$_SGLOBAL['supe_token_id']);
					    if($token_mid==$mid && $token_op_wxid==$op_wxid){
					       updatetable(tname('wz_token'),array('state'=>1),array('wxid'=>$session['wxid'],'mid'=>$mid,'op_wxid'=>$op_wxid));	
					       $_SGLOBAL['supe_wxid'] = addslashes($session['wxid']);
					       wz_insertsession($session);//更新session		
						   return $_SGLOBAL['supe_token_id'];
					    }			
					 }
			}
		}
	}
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("wz_token")." WHERE wxid='".$wxid."' and mid=".$mid." and op_wxid=".$op_wxid." and state=0");
	if($wz = $_SGLOBAL['db']->fetch_array($query)) {
		if($wz['token'] == $token){
				updatetable(tname('wz_token'),array('state'=>1),array('wxid'=>$wxid,'mid'=>$mid,'op_wxid'=>$op_wxid));
				$_SGLOBAL['supe_wxid'] = addslashes($wz['wxid']);
				$session = array('token_id' => $wz['id'], 'wxid' => $_SGLOBAL['supe_wxid'], 'password' => $token);
				wz_insertsession($session);//登录
				$cookietime=3600;//3600 * 24 * 15;
				//设置cookie
	            ssetcookie('site_auth', authcode($session["password"].' '.$session["token_id"], 'ENCODE'),$cookietime);
				$_SGLOBAL['supe_token_id']=$session['token_id'];
				return $_SGLOBAL['supe_token_id'];
		}
	} 


	obclean();
	ssetcookie('site_auth', '', -86400 * 365);
	return 0;			
}

//添加wz_session
function wz_insertsession($setarr) {
	global $_SGLOBAL, $_SCONFIG;
    
	$_SGLOBAL['db']->query("DELETE FROM ".tname("wz_session")." WHERE token_id='$setarr[token_id]'");	
	
	//添加在线
	$ip = getonlineip();
	$setarr['lastactivity'] = $_SGLOBAL['timestamp'];
	$setarr['ip'] = $ip;

	inserttable(tname("wz_session"), $setarr, 0, true, 0);

	$_SGLOBAL['supe_token_id'] = $setarr['token_id'];
}

//添加微站访问记录
function wz_record($get){
	global $_SGLOBAL, $_SC;
    reset ($get);
    foreach ($get as $k=>$v){
		
		  if($k=='wxid') $wxid=getstr($get[$k]);
		  if($k=='token') $token=getstr($get[$k]);
		  if($k=='mid')  $mid=intval($get[$k])?intval($get[$k]):0;
		
          if($k=='wxid'||$k=='token'||$k=='mid'){
			   unset($get[$k]);
			   continue;
		  }else{
			$get[$k]=getstr($get[$k]);  
		  }
		 $get[$k]=getstr($get[$k]);  
    }
	$query=json_encode($get);
	$arr=array(
	'token_id'=>$_SGLOBAL['supe_token_id'],
	'query'=>$query,
	'ip'=>getonlineip(),
	'user_agent'=>$_SERVER["HTTP_USER_AGENT"],
	'wxid'=>$wxid,
	'token'=>$token,
	'mid'=>$mid,
	'addtime'=>$_SGLOBAL['timestamp']
	);
	$record_id=inserttable(tname('wz_record'),$arr,1);
	return $record_id;
}

//获取微站profile
//返回数组
function get_profile($profile){
	global $_SGLOBAL;
		 foreach($profile as $k=>$v){
			 if($profile[$k]['parent_id']==0){ 
			    $arr[$v['var']][$v['sort']]=$v['value'];
			 }else{
				$var=$_SGLOBAL['db']->getone('select var from '.tname('wz_module_profile').' where id='.$v['parent_id']);
				$sort=$_SGLOBAL['db']->getone('select sort from '.tname('wz_module_profile').' where id='.$v['parent_id']);
				$arr[$var][$sort][$v['var']]=$v['value'];
				$arr[$var][$sort]['pid']=$v['parent_id'];
			 }
		  }
		  foreach($arr as $k=>$v){
			if(count($arr[$k])==1){
				$arr[$k]=reset($arr[$k]);
			}
		  }

    return $arr; 	
}


function template($module,$weixin){
	global $_SC;   
	header('Cache-control: private');
    header('Content-type: text/html; charset='.$_SC['charset']);

    /* 创建 Smarty 对象。*/
    require(S_ROOT . './source/cls_template.php');
    $smarty = new cls_template;

    $smarty->cache_lifetime = 1;//$_SCONFIG['cache_time'];
    $smarty->template_dir   = S_ROOT . './module/'. $module['module_dir'].'/themes/' . $module['module_template'];
    $smarty->cache_dir      = S_ROOT . './temp/caches';
    $smarty->compile_dir    = S_ROOT . './temp/compiled';
	$smarty->compile_id =  $module['module_dir'].'_'.$weixin['op_uid'];
    $smarty->direct_output = false;
    $smarty->force_compile = false;

    $smarty->assign('lang', $_SC['lang']);
    $smarty->assign('charset', $_SC['charset']);
	return $smarty;	
}

?>
