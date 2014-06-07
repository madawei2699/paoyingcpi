<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}

class Weizhan { 
  protected $data=array();
  protected $method;
  protected $token_id;
  protected $token_info=array();
  protected $module_info=array();
  protected $weixin_info=array();
  protected $ua=array(); //http_user_agent
  protected $template;
  protected $smarty;
  protected $isauth;
	
  public function __construct($data,$method){
		$this->data=$data;
		$this->method=$method;
		$this->token_id=$this->data['token_id'];
		$this->get_token_info();
		$this->get_module_info();
		$this->get_weixin_info();
		$this->get_template();
		$this->isauth=$this->wz_checkauth();
		//$this->isauth=0;
		$this->wz_record();
		$this->wz_config();
  }
 
  private function wz_config(){
	global $_WZ,$_SC;
	$_WZ=$this->token_info;
    $_WZ['isauth']=$this->isauth;
	$_WZ['op_uid']=$this->token_info['op_uid'];
    $_WZ['img_url']=$_SC['site_host'].'/uploads';
	$_WZ['smarty']=$this->smarty;
	
	
    define('INDEX', $_SC['site_host'].'/weizhan/'.$this->token_info['id'].'/');
    $template_path=$_SC['site_host'].'/weizhan/module/'.$this->module_info['module_dir'].'/themes/' . $this->template;
    $_WZ['smarty']->assign('INDEX',INDEX);
    $_WZ['smarty']->assign('module_dir',$this->module_info['module_dir']);
    $_WZ['smarty']->assign('template_path',$template_path);
    $_WZ['smarty']->assign('_SC', $_SC);
    $_WZ['smarty']->assign('_SGLOBAL', $_SGLOBAL);
    $_WZ['smarty']->assign('rand',random(6));
    session_save_path(S_ROOT."./data/session_tmp");
    session_start();
  }
 
  private function get_token_info(){
	  global $_SGLOBAL;	  
      $query=$_SGLOBAL['db']->query('select id,op_uid,op_wxid,wxid,mid,token,state,expires_in from '.tname('wz_token').' where id="'.$this->token_id.'"');
      $this->token_info=$_SGLOBAL['db']->fetch_array($query);
  }
  
  private function get_module_info(){
	  global $_SGLOBAL;
	  $query=$_SGLOBAL['db']->query('select id,module_name,module_dir,module_default_template from '.tname('wz_module').' where id="'.$this->token_info['mid'].'"');
      $this->module_info=$_SGLOBAL['db']->fetch_array($query);
  }

  private function get_weixin_info(){
	  global $_SGLOBAL;
      $query=$_SGLOBAL['db']->query('select op_uid,headimg,qrcode,weixin_name,username from '.tname('open_member_weixin').' where id='.$this->token_info['op_wxid'].' and state=1');
      $this->weixin_info=$_SGLOBAL['db']->fetch_array($query);	  
  }

  private function get_ua(){
	  global $_SGLOBAL,$_SERVER,$_SC;
	  $this->ua['is_mac']     = strripos($_SERVER["HTTP_USER_AGENT"],'Macintosh');  //判断是否包含mac电脑关键字 
      $this->ua['is_ipad']    = strripos($_SERVER["HTTP_USER_AGENT"],'ipad');  //判断是否包含ipad关键字
      $this->ua['is_iphone']  = strripos($_SERVER["HTTP_USER_AGENT"],'iphone');  //判断是否包含iphone关键字
      $this->ua['is_android'] = strripos($_SERVER['HTTP_USER_AGENT'],'Android'); //判断是否Android;
      $this->ua['is_pc']      = strripos($_SERVER["HTTP_USER_AGENT"], 'windows nt'); //判断是否为(pc)电脑
      $this->ua['is_ucweb']   = strripos($_SERVER["HTTP_USER_AGENT"], 'UCWEB'); //判断是否为UC极速模式
      $this->ua['is_weixin']  = strripos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger'); //判断是否为微信浏览器
	  $_SC['ua']=$this->ua;
	  
  }
  
  private function get_template(){
	  global $_SGLOBAL,$_SC;
	  $this->get_ua();
	  if($this->ua['is_pc'] || $this->ua['is_mac']){		  
        $this->template=$_SGLOBAL['db']->getone('select value from '.tname('wz_weixin_setting').' where op_wxid='.$this->token_info['op_wxid'].' and mid='.$this->token_info['mid'].' and var="pc_template"');
	  }else{
        $this->template=$_SGLOBAL['db']->getone('select value from '.tname('wz_weixin_setting').' where op_wxid='.$this->token_info['op_wxid'].' and mid='.$this->token_info['mid'].' and var="mobile_template"');
	  }
      if(!$this->template){
        $this->template=$this->module_info['module_default_template'];
      }
	  
	  header('Cache-control: private');
      header('Content-type: text/html; charset='.$_SC['charset']);

      /* 创建 Smarty 对象。*/
      include_once(S_ROOT . './source/cls_template.php');
      $smarty = new cls_template;
      $smarty->cache_lifetime = 1;//$_SCONFIG['cache_time'];
      $smarty->template_dir   = S_ROOT . './module/'. $this->module_info['module_dir'].'/themes/' . $this->template;
      $smarty->cache_dir      = S_ROOT . './temp/caches';
      $smarty->compile_dir    = S_ROOT . './temp/compiled';
	  $smarty->compile_id =  $this->module_info['module_dir'].'_'.$this->weixin_info['op_uid'];
      $smarty->direct_output = false;
      $smarty->force_compile = false;
      $smarty->assign('lang', $_SC['lang']);
      $smarty->assign('charset', $_SC['charset']);
	  $this->smarty=$smarty;		  	  
  }


  private function wz_checkauth(){
	global $_SGLOBAL,$_COOKIE;
    if($_COOKIE['site_auth']){
		@list($password, $token_id) = explode(" ", authcode($_COOKIE['site_auth'], 'DECODE'));
		$_SGLOBAL['supe_token_id'] = intval($token_id);
		if($password && $_SGLOBAL['supe_token_id']){			
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("wz_session")." WHERE token_id='".$_SGLOBAL['supe_token_id']."'");
			if($session = $_SGLOBAL['db']->fetch_array($query)) {
                    if($session['password'] == $password) {
				        $token_mid=$_SGLOBAL['db']->getone('select mid from '.tname('wz_token').' where id="'.$_SGLOBAL['supe_token_id'].'"');
				        $token_op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('wz_token').' where id="'.$_SGLOBAL['supe_token_id'].'"');
					    if($token_mid==$this->token_info['mid'] && $token_op_wxid==$this->token_info['op_wxid']){
					       updatetable(tname('wz_token'),array('state'=>1),array('wxid'=>$session['wxid'],'mid'=>$this->token_info['mid'],'op_wxid'=>$this->token_info['op_wxid']));	
					       $_SGLOBAL['supe_wxid'] = addslashes($session['wxid']);
					       $this->wz_insertsession($session);//更新session		
						   return $_SGLOBAL['supe_token_id'];
					    }			
					 }
			}
		}
	}
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("wz_token")." WHERE wxid='".$this->token_info['wxid']."' and mid='".$this->token_info['mid']."' and op_wxid='".$this->token_info['op_wxid']."' and state=0");
	if($wz = $_SGLOBAL['db']->fetch_array($query)) {
		if($wz['token'] == $this->token_info['token']){
				updatetable(tname('wz_token'),array('state'=>1),array('wxid'=>$this->token_info['wxid'],'mid'=>$this->token_info['mid'],'op_wxid'=>$this->token_info['op_wxid']));
				$_SGLOBAL['supe_wxid'] = addslashes($wz['wxid']);
				$session = array('token_id' => $wz['id'], 'wxid' => $_SGLOBAL['supe_wxid'], 'password' => $this->token_info['token']);
				$this->wz_insertsession($session);//登录
				$cookietime=3600 * 24 * 15;
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
  private function wz_insertsession($setarr) {
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
  private function wz_record(){
	global $_SGLOBAL, $_SC,$_SERVER;
	$get=$this->data;
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


  protected function err_msg($msg){
	echo $msg;  
  }
}  

?>