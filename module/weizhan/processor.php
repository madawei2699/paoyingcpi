<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}

class Weizhan_Module_Processor extends WX_Module_Processor {	
	public function respond() {
		global $_SGLOBAL,$wx;
		//加载微站
		//未完成（应该是循环读取公众号开启的微站模块列表）		
        $module['name']='weidian';
		$module['mid']=1;
		$processor=$this->create_weizhan_processor($module['name']);
		$processor->module=$module;
		$processor->message=$wx->message;
	    $result=$processor->respond();	
		if($result){
		   return $result;	
		}else{		
		   return false;
		}
	}


	private function create_weizhan_processor($name) {
		$classname = "{$name}_Weizhan_Processor";
		if(!class_exists($classname)) {
			$file = S_ROOT . "./weizhan/module/{$name}/processor.php";
			if(!is_file($file)) {
				trigger_error('WeizhanProcessor Definition File Not Found '.$file, E_USER_ERROR);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('WeizhanProcessor Definition Class Not Found', E_USER_ERROR);
			return null;
		}
		$o = new $classname();
		if($o instanceof Weizhan) {
			return $o;
		} else {
			trigger_error('WeizhanProcessor Class Definition Error', E_USER_ERROR);
			return null;
		}

	}
	
	
}


abstract class Weizhan{
	public $module;
	public $message;
	abstract function respond();			

	protected function resp_text($content) {
		$content = str_replace("\r\n", "\n", $content);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'text';
		$response['Content'] = htmlspecialchars_decode($content);
		return $response;
	}
	protected function resp_image($mid) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'image';
		$response['Image']['MediaId'] = $mid;
		return $response;
	}
	protected function resp_voice($mid) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'voice';
		$response['Voice']['MediaId'] = $mid;
		return $response;
	}
	protected function resp_video(array $video) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'video';
		$response['Video']['MediaId'] = $video['video'];
		$response['Video']['ThumbMediaId'] = $video['thumb'];
		return $response;
	}
	protected function resp_music(array $music) {
		global $_SC;
		$music = array_change_key_case($music);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'music';
		$response['Music'] = array(
			'Title'	=> $music['title'],
			'Description' => $music['description'],
			'MusicUrl' => strpos($music['musicurl'], 'http://') === FALSE ? $_SC['attachurl'] . $music['musicurl'] : $music['musicurl'],
		);
		if (empty($music['hqmusicurl'])) {
			$response['Music']['HQMusicUrl'] = $response['Music']['MusicUrl'];
		} else {
			$response['Music']['HQMusicUrl'] = strpos($music['hqmusicurl'], 'http://') === FALSE ? $_SC['attachurl'] . $music['hqmusicurl'] : $music['hqmusicurl'];
		}
		$response['Music']['ThumbMediaId'] = $music['thumb'];
		return $response;
	}
	protected function resp_news(array $news) {
		$news = array_change_key_case($news);
		if (!empty($news['title'])) {
			$news = array($news);
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = count($news);
		$response['Articles'] = array();
		foreach ($news as $row) {
			$response['Articles'][] = array(
				'Title' => $row['title'],
				'Description' => $row['description'],
				'PicUrl' => $row['picurl'],
				'Url' => $row['url'],
				'TagName' => 'item',
			);
		}
		return $response;
	}

  //生成微站链接
  //$mid  微站模块ID
  //$wxid 微信用户ID
  protected function wz_build_link($query=array()){
	global $_SGLOBAL,$_SC,$wx;
	$mid=$this->module['mid'];
	$op_uid=$wx->weixin['op_uid'];
	$op_wxid=$wx->weixin['op_wxid'];
	$wxid=$wx->message['from'];
	$token=random(6);
	$setarr=array(
	'wxid'=>$wxid,
	'op_uid'=>$op_uid,
	'op_wxid'=>$op_wxid,
	'token'=>$token,
	'mid'=>$mid,
	'expires_in'=>7776000,
	'state'=>0,
	'addtime'=>$_SGLOBAL['timestamp']
	);
	$lasttokenid=$_SGLOBAL['db']->getone('select id from '.tname('wz_token').' where op_uid="'.$op_uid.'" and wxid="'.$wxid.'" and mid='.$mid.' and op_wxid='.$op_wxid.' and state=0 order by addtime desc');
	if($lasttokenid>0){
		$token=$_SGLOBAL['db']->getone('select token from '.tname('wz_token').' where id='.$lasttokenid);
		$token_id=$lasttokenid;
	}else{
	  updatetable(tname('wz_token'),array('state'=>1),array('wxid'=>$wxid,'mid'=>$mid,'op_wxid'=>$op_wxid));
	  $token_id=inserttable(tname('wz_token'),$setarr,1);
    }
	if($query){
	   $querystr='&'.http_build_query($query);
	}
	$link=$_SC['site_host'].'/weizhan/'.$token_id.'/?token='.$token.$querystr;
	return $link;	
  }

	
    //记录消息来源的用户资料,返回微笑微信中用户信息数组,包含[uid,province,nickname]
    protected function save_weixin_member(){
	  global $_SGLOBAL,$wx;
	  $create_time=$wx->message['time'];
	  $wxid=$wx->message['from'];
	  $op_wxid=$wx->weixin['op_wxid'];
	  if($wxid=='') return false;
	  $return=false;
	  $member['uid']=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_member').' where op_wxid='.$op_wxid.' and wxid="'.$wxid.'"');
      $ro = new WX_Remote_Opera();
      $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id=".$op_wxid);
      if($op_wx=$_SGLOBAL['db']->fetch_array($query)){
         $token=$ro->init($op_wx['username'],$op_wx['password']);
	     if($member['uid']>0){
	        $member['province']=$_SGLOBAL['db']->getone('select province from '.tname('weixin_member').' where uid='.$member['uid']);
			$member['nickname']=$_SGLOBAL['db']->getone('select nickname from '.tname('weixin_member').' where uid='.$member['uid']);
	     }else{
		    $msglist=$ro->getmsglist();
		    foreach($msglist as $k=>$v){
			    if($v['date_time']==$create_time){
				  $contactinfo=$ro->getcontactinfo($v['fakeid']);
				  $member['uid']=inserttable(tname('weixin_member'),array('op_wxid'=>$op_wxid,'wxid'=>$wxid,'fakeid'=>$v['fakeid'],'nickname'=>$contactinfo['nick_name'],'username'=>$contactinfo['user_name'],'country'=>$contactinfo['country'],'province'=>$contactinfo['province'],'city'=>$contactinfo['city'],'sex'=>$contactinfo['gender'],'create_time'=>$create_time),1);
                  $member['fakeid']=$v['fakeid'];
				  $member['province']=$contactinfo['province'];
				  $member['nickname']=$contactinfo['nick_name'];
				  break;					
			    }
		    }
	     }
		 
		 //保存头像
		 $ro->getheadimg($member['fakeid']);
		 return $member;
	 }else{
		 return false;
		  
	 }
   }
}
