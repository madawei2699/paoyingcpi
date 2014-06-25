<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}


//微笑核心类
class WX{
	private $token = '';
	private $events = array();
	private $modules = array();
	private $matcher = null;
	public $message = array();
	public $response = array();
	public $keyword = array();
	
	public $weixin=array();

	public function __construct() {
		$this->token = TOKEN;
	}


	
	public function run(){
		global $_SGLOBAL;		
		if(empty($this->token)) {
			exit('Access Denied');
		}
		if(!WX_Utility::check_sign($this->token)) {;
			exit('Access Denied');
		}
		if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
			exit($_GET['echostr']);
		}
		if(strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
			
			//消息防重
		    if(WX_Utility::check_post($postStr)){
					echo $resultStr;
					exit;
			}				
			
			
			$this->message = WX_Utility::parse($postStr);
			if (empty($this->message)) {
				WX_Utility::logging('waring', 'Request Failed');
				exit('Request Failed');
			}
			
			
			//初始化	
			$this->weixin['op_wxid']=WX_Utility::get_weixin($this->message['to']);
			if(!$this->weixin['op_wxid']){
			    exit('cant find this weixin');
			}else{
				$this->weixin['op_uid']=$_SGLOBAL['db']->getone('select op_uid from '.tname('open_member_weixin').' where id='.$this->weixin['op_wxid']);
			}

			
			//获取启用的微信模块
	        $modules = $_SGLOBAL['db']->getall('select wm.*,m.name from '.tname('weixin_module').' as wm inner join '.tname('open_module').' as m on wm.mid=m.mid where wm.enabled=1 and wm.op_uid="'.$this->weixin['op_uid'].'" and wm.op_wxid="'.$this->weixin['op_wxid'].'" order by displayorder desc');
            foreach($modules as $k=>$v){
			   	$this->weixin['account']['modules'][$v['name']]=$v;				
			}
			
		    $this->modules = array_keys($this->weixin['account']['modules']);
		    $this->modules[] = 'welcome';
		    $this->modules[] = 'default';
		    $this->modules = array_unique($this->modules);


						
			WX_Utility::logging('trace', $this->message);
			$this->response = $this->matcher();   //根据消息类型选择消息匹配模块
			$this->response['content'] = $this->process(); //加载消息匹配模块						
			
			//如果没消息匹配模块，加载default模块
			if(empty($this->response['content']) || (is_array($this->response['content']) && $this->response['content']['type'] == 'text' && empty($this->response['content']['content'])) || (is_array($this->response['content']) && $this->response['content']['type'] == 'news' && empty($this->response['content']['items']))) {
				$this->response['module'] = 'default';
				$this->response['content'] = $this->process();
			}
			WX_Utility::logging('response', $this->response);
			$resp = WX_Utility::response($this->response['content']);
			$mapping = array(
				'[from]' => $this->message['from'],
				'[to]' => $this->message['to'],
			);
			echo str_replace(array_keys($mapping), array_values($mapping), $resp);
			exit;
		}
		
	}
	
	
	private function matcher(){
		if (method_exists($this, 'matcher_'.strtolower($this->message['msgtype']))) {
			$response = call_user_func(array($this, 'matcher_'.strtolower($this->message['msgtype'])));
		}
		return $response;
	}
	
	/**
	 * 事件模块、规则匹配器
	 */
	private function matcher_event() {
		global $_SGLOBAL;
		$response = array('module' => '');
		//订阅
		if($this->message['event'] == 'subscribe') {
			$response['module'] = 'welcome';
		}
		//退订
		if($this->message['event'] == 'unsubscribe') {
			
		}
		//扫描条码
		if($this->message['event'] == 'scan') {

		}
		//位置
		if($this->message['event'] == 'location') {

		}
		//菜单
		if($this->message['event'] == 'CLICK') {
			$this->message['content'] = $this->message['eventkey'];
		}
		return $response;
	}

	private function matcher_text() {
		global $_SGLOBAL;
		$response = array('module' => '');
		return $response;
	}

	private function matcher_image() {
		$response = array('module' => '');
		return $response;
	}

	private function matcher_voice() {
		$response = array('module' => '');
		return $response;
	}

	private function matcher_video() {
		$response = array('module' => '');
		return $response;
	}

	private function matcher_location() {
		$response = array('module' => '');
		return $response;
	}

	private function matcher_link() {
		$response = array('module' => '');
		return $response;
	}	
	
	
	
	
	
	
	private function process(){
		$response = false;
		if (empty($this->response['module']) || !in_array($this->response['module'], $this->modules)) {
			return false;
		}
		$processor = WX_Utility::create_module_processor($this->response['module']);
		$processor->message = $this->message;
		$processor->module = $this->weixin['account']['modules'][$this->response['module']];
		$response = $processor->respond();
		if(empty($response)) {
			return false;
		}
		return $response;		
	}
	
}


//微笑工具类
class WX_Utility{
	public static function root_path() {
		static $path;
		if(empty($path)) {
			$path = dirname(__FILE__);
			$path = str_replace('\\', '/', $path);
		}
		return $path;
	}

	public static function check_sign($token) {
		$signkey = array($token, $_GET['timestamp'], $_GET['nonce']);
		sort($signkey,SORT_STRING);
		$signString = implode($signkey);
		$signString = sha1($signString);
		if($signString == $_GET['signature']){
			return true;
		}else{
			return false;
		}
	}

	public static function create_module_processor($name) {
		$classname = "{$name}_Module_Processor";
		if(!class_exists($classname)) {
			$file = WX_Utility::root_path() . "/{$name}/processor.php";
			if(!is_file($file)) {
				trigger_error('ModuleProcessor Definition File Not Found '.$file, E_USER_ERROR);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleProcessor Definition Class Not Found', E_USER_ERROR);
			return null;
		}
		$o = new $classname();
		if($o instanceof WX_Module_Processor) {
			return $o;
		} else {
			trigger_error('ModuleProcessor Class Definition Error', E_USER_ERROR);
			return null;
		}
	}


	/**
	 * 分析请求数据
	 * @param string $request 接口提交的请求数据
	 * 具体数据格式与微信接口XML结构一致
	 *
	 * @return array 请求数据结构
	 */
	public static function parse($message) {
		$packet = array();
		if (!empty($message)){
			$obj = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
			if($obj instanceof SimpleXMLElement) {
				$packet['from'] = strval($obj->FromUserName);
				$packet['to'] = strval($obj->ToUserName);
				$packet['time'] = strval($obj->CreateTime);
				$packet['type'] = strval($obj->MsgType);
				$packet['event'] = strval($obj->Event);
				$packet['msgid'] = strval($obj->MsgId);

				foreach ($obj as $variable => $property) {
					$packet[strtolower($variable)] = (string)$property;
				}
				if($packet['type'] == 'event') {
					$packet['type'] = $packet['event'];
					unset($packet['content']);
				}
			}
		}
		return $packet;
	}

	/**
	 * 按照响应内容组装响应数据
	 * @param array $packet 响应内容
	 *
	 * @return string
	 */
	public static function response($packet) {
		if (!is_array($packet)) {
			return $packet;
		}
		if(empty($packet['CreateTime'])) {
			$packet['CreateTime'] = time();
		}
		if(empty($packet['MsgType'])) {
			$packet['MsgType'] = 'text';
		}
		if(empty($packet['FuncFlag'])) {
			$packet['FuncFlag'] = 0;
		} else {
			$packet['FuncFlag'] = 1;
		}
		return self::array2xml($packet);
	}

	public static function logging($level = 'info', $message = '') {
		if(1) {
			return true;
		}
		$filename =S_ROOT . '/data/logs/' . date('Ymd') . '.log';
		mkdirs(dirname($filename));
		$content = date('Y-m-d H:i:s') . " {$level} :\n------------\n";
		if(is_string($message)) {
			$content .= "String:\n{$message}\n";
		}
		if(is_array($message)) {
			$content .= "Array:\n";
			foreach($message as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message == 'get') {
			$content .= "GET:\n";
			foreach($_GET as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message == 'post') {
			$content .= "POST:\n";
			foreach($_POST as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		$content .= "\n";

		$fp = fopen($filename, 'a+');
		fwrite($fp, $content);
		fclose($fp);
	}

	public static function array2xml($arr, $level = 1, $ptagname = '') {
		$s = $level == 1 ? "<xml>" : '';
		foreach($arr as $tagname => $value) {
			if (is_numeric($tagname)) {
				$tagname = $value['TagName'];
				unset($value['TagName']);
			}
			if(!is_array($value)) {
				$s .= "<{$tagname}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tagname}>";
			} else {
				$s .= "<{$tagname}>".self::array2xml($value, $level + 1)."</{$tagname}>";
			}
		}
		$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
		return $level == 1 ? $s."</xml>" : $s;
	}
	
	//判断是否是绑定过的微信公众号,返回系统中微信公众号的编号$op_wxid
	public static function get_weixin($ghid){
		global $_SGLOBAL;
		$op_wxid=$_SGLOBAL['db']->getone('select id from '.tname('open_member_weixin').' where ghid="'.$ghid.'" and state=1');
		if($op_wxid>0){
		  return $op_wxid;
		}else{
		  return 0;	
		}
	}

    //消息和事件防重
	public static function check_post($postStr){
		global $_SGLOBAL;
	    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);	
		$content=json_encode($obj);
		$arr=json_decode($content,true);
		if($arr['Event']){
		  $event=getcount(tname('weixin_event'),array('fromusername'=>$arr['FromUserName'],'createtime'=>$arr['CreateTime']));
		  if($event>0){
		    return true;  
		  }else{
		    inserttable(tname('weixin_event'),array('fromusername'=>$arr['FromUserName'],'createtime'=>$arr['CreateTime'],'tousername'=>$arr['ToUserName'],'msgtype'=>$arr['MsgType'],'event'=>$arr['Event'],'event_content'=>$content));
		  return false;
		  }
		}
		
		$msgid=$_SGLOBAL['db']->getone('select msgid from '.tname('weixin_message').' where msgid="'.$arr['MsgId'].'"');
		if($msgid){
			return true;
		}else{
		    inserttable(tname('weixin_message'),array('msgid'=>$arr['MsgId'],'fromusername'=>$arr['FromUserName'],'createtime'=>$arr['CreateTime'],'tousername'=>$arr['ToUserName'],'msgtype'=>$arr['MsgType'],'msg_content'=>$content));
			return false;	
		}
	}

	
		
}



//微笑模块之消息和事件回复处理类
abstract class WX_Module_Processor {
	public $message;
	public $module;
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
				'Url' => $row['content'],
				'TagName' => 'item',
			);
		}
		return $response;
	}	
}



//模拟登录CLASS
class WX_Remote_Opera{
	private $token;
	private $user;
	
	private $cookieFile;
	private $loginFile;
	private $lastTimeFile;
	
	private $expire = 3600;
	
	public function init($user,$password){  //初始化，登录微信平台
	
	    /*验证码
        $url = 'http://mp.weixin.qq.com/cgi-bin/verifycode?username=';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        preg_match('/^Set-Cookie: (.*?);/m', curl_exec($ch), $m);
        //echo $m[1];
		//exit;
     	curl_close($ch);
        */
		$this->user=$user;		
		$this->cookieFile=S_ROOT.'./data/cookies/weixin/cookie_'.$this->user.'.txt';
		$this->loginFile=S_ROOT.'./data/cookies/weixin/login_'.$this->user.'.txt';
		$this->lastTimeFile=S_ROOT.'./data/cookies/weixin/last_'.$this->user.'.txt';
		
		
		if(!file_exists($this->cookieFile)){
          $fh = fopen($this->cookieFile,"w");
          fclose($fh);		    
        }
		
		if(!file_exists($this->loginFile)){
          $fh = fopen($this->loginFile,"w");
          fclose($fh);		    
        }

		if(!file_exists($this->lastTimeFile)){
          $fh = fopen($this->lastTimeFile,"w");
          fclose($fh);		    
        }

        $needLogin=true;
		$nowTime=time();
		if($lastTime=file_get_contents($this->lastTimeFile)){
			
		}else{
		   $lastTime=0;	
		}
		
		if(($nowTime-$lastTime)<=$this->expire){
		   $needLogin=false;	
		}
	    if($needLogin==true){	
		    $url="https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
		    $ch=curl_init($url);
		    $post['username']=$user;
		    $post['pwd']=md5($password);
		    $post['f']='json';
		    $post['imgcode']='';
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		    curl_setopt($ch,CURLOPT_HEADER,1);
		    curl_setopt($ch,CURLOPT_REFERER,'https://mp.weixin.qq.com/cgi-bin/loginpage?t=wxm2-login&lang=zh_CN');
		    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
		    curl_setopt($ch,CURLOPT_POST,1);
		    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		    curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookieFile);
		    $html=curl_exec($ch);
		    preg_match('/[\?\&]token=(\d+)"/',$html,$t);
		    $token=$t[1];
		    curl_close($ch);
		    if($token){
		       file_put_contents($this->lastTimeFile,$nowTime);
  		       file_put_contents($this->loginFile,$token);
		       $this->token=$token;
  		       return $token;
		    }else{
		       return false;	
		    }
	    }else{
		    if($token=file_get_contents($this->loginFile)){
		        //file_put_contents($this->lastTimeFile,$nowTime);
				$this->token=$token;
				return $token;
			}else{
		        return false;	
			}		
	    }
	}
		
	public function test_login($user,$password){
		    $url="https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
		    $ch=curl_init($url);
		    $post['username']=$user;
		    $post['pwd']=md5($password);
		    $post['f']='json';
		    $post['imgcode']='';
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		    curl_setopt($ch,CURLOPT_HEADER,1);
		    curl_setopt($ch,CURLOPT_REFERER,'https://mp.weixin.qq.com/cgi-bin/loginpage?t=wxm2-login&lang=zh_CN');
		    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
		    curl_setopt($ch,CURLOPT_POST,1);
		    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		    curl_setopt($ch,CURLOPT_COOKIEJAR,S_ROOT.'data/cookies/weixin/cookie.txt');
		    $html=curl_exec($ch);
		    preg_match('/[\?\&]token=(\d+)"/',$html,$t);
		    $token=$t[1];
		    curl_close($ch);
  		    return $token;
	}

    //获取公众号基本信息
    public function get_account_info() {
	    $url="https://mp.weixin.qq.com/cgi-bin/settingpage?t=setting/index&action=index&token=".$this->token."&lang=zh_CN";
        $ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$html=curl_exec($ch);
		curl_close($ch);
		$info = array();
	    preg_match('/(\{"user_name.*\})/', $html, $match);
	    $info = json_decode($match[1], true);
	    preg_match('/uin.*?"([0-9]+?)"/', $html, $match);
	    $info['fakeid'] = $match[1];
		preg_match_all('/<div[^>]*class="meta_content"[^>]*>(.*?)<\/div>/si',$html, $match);
        $info['nickname']=trim(strip_tags($match[1][1]));
		$fh = file_get_contents($this->cookieFile); 
	    preg_match('/(gh_[a-z0-9A-Z]+)/', $fh, $match);
	    $info['ghid'] = $match[1];		
		return $info;
    }
	
	public function sendmsg($content,$fromfakeid){ //发送消息给指定人
		$url="https://mp.weixin.qq.com/cgi-bin/singlesend";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,'https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&token='.$this->token.'&lang=zh_CN');
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['t']='ajax-response';
		$post['imgcode']='';
		$post['mask']=false;
		$post['lang']='zh_CN';
		$post['tofakeid']=$fromfakeid;
		$post['type'] =1;
		$post['content']=$content;
		$post['token']=$this->token;
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$html=curl_exec($ch);
		curl_close($ch);
	}
	
	public function getcontactinfo($fromfakeid){
		$url="https://mp.weixin.qq.com/cgi-bin/getcontactinfo";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['ajax']='1';
		$post['f']='json';
		$post['lang']='zh_CN';
		$post['t']='ajax-getcontactinfo';
		$post['fakeid'] =$fromfakeid;
		$post['token']=$this->token;
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$html=curl_exec($ch);
		curl_close($ch);
		$arr=json_decode($html,true);
		return $arr['contact_info'];				
	}

	public function getgroupinfo($fromfakeid){
		$url="https://mp.weixin.qq.com/cgi-bin/getcontactinfo";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['ajax']='1';
		$post['f']='json';
		$post['lang']='zh_CN';
		$post['t']='ajax-getcontactinfo';
		$post['fakeid'] =$fromfakeid;
		$post['token']=$this->token;
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$html=curl_exec($ch);
		curl_close($ch);
		$arr=json_decode($html,true);
		return $arr['groups']['groups'];				
	}
	
	public function getheadimg($fromfakeid){
		$url="https://mp.weixin.qq.com/misc/getheadimg";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['fakeid'] =$fromfakeid;
		$post['token']=$this->token;
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$headimg=curl_exec($ch);
		curl_close($ch);	
		$PNG_SAVE_DIR = S_ROOT.'uploads'.DIRECTORY_SEPARATOR.'weixin_headimg'.DIRECTORY_SEPARATOR;
        $file = fopen($PNG_SAVE_DIR.$fromfakeid.".png","w");//打开文件准备写入
		fwrite($file,$headimg);//写入
        fclose($file);//关闭
	}


	public function getqrcode($fromfakeid){
		$url="https://mp.weixin.qq.com/misc/getqrcode";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['fakeid'] =$fromfakeid;
		$post['token']=$this->token;
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$headimg=curl_exec($ch);
		curl_close($ch);	
		$PNG_SAVE_DIR = S_ROOT.'uploads'.DIRECTORY_SEPARATOR.'weixin_qrcode'.DIRECTORY_SEPARATOR;
        $file = fopen($PNG_SAVE_DIR.$fromfakeid.".png","w");//打开文件准备写入
		fwrite($file,$headimg);//写入
        fclose($file);//关闭
	}
	
	public function getcontactlist($pagesize=10,$page=0){
		$url="https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&pagesize=".$pagesize."&pageidx=".$page."&type=0&groupid=0&token=".$this->token."&lang=zh_CN";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$html=curl_exec($ch);
		curl_close($ch);
		preg_match('%(?<=\"contacts\"\:)(.*)(?=}\)\.contacts)%', $html, $result);
		return json_decode($result[1],true);
	}

	public function getmsglist($count=20){
		$url="https://mp.weixin.qq.com/cgi-bin/message?t=message/list&action=&keyword=&count=".$count."&day=7&filterivrmsg=0&token=".$this->token."&lang=zh_CN";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$html=curl_exec($ch);
		preg_match('%(?<=\"msg_item\"\:)(.*)(?=}\)\.msg_item)%', $html, $result);
		curl_close($ch);
		return json_decode($result[1],true);
	}
	
	
	
	private function get_access_token($appid,$appsecret){
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
		$arr = json_decode(file_get_contents($url),1);
        return $arr;
	}
	
	//创建自定义菜单
	public function create_menu($appid,$appsecret,$data){
		$arr = $this->get_access_token($appid,$appsecret);
		if($arr['access_token']){
           $ACCESS_TOKEN=$arr['access_token'];
			$ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$ACCESS_TOKEN}");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tmpInfo = curl_exec($ch);
            if (curl_errno($ch)) {
              echo 'Errno'.curl_error($ch);
            }
            curl_close($ch);
            return json_decode($tmpInfo,1);       			
		}else{		
		  return $arr;	
		}
	}
	
	//查询自定义菜单
	public function get_menu($appid,$appsecret){
		$arr = $this->get_access_token($appid,$appsecret);
		if($arr['access_token']){
           $ACCESS_TOKEN=$arr['access_token'];
		   $url="https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$ACCESS_TOKEN;
		   $arr = json_decode(file_get_contents($url),1);
		   return $arr;
		}else{		
		  return $arr;	
		}
	}
	
    //删除自定义菜单
	public function del_menu($appid,$appsecret){
		$arr = $this->get_access_token($appid,$appsecret);
		if($arr['access_token']){
           $ACCESS_TOKEN=$arr['access_token'];
		   $url="https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$ACCESS_TOKEN;
		   $arr = json_decode(file_get_contents($url),1);
		   return $arr;
		}else{		
		  return $arr;	
		}
	}
	
	//关闭编辑模式
	public function close_editmode(){
		$url="https://mp.weixin.qq.com/misc/skeyform?form=advancedswitchform&lang=zh_CN";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['flag']=0;
        $post['type']=1;   
		$post['token']=$this->token;
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$html=curl_exec($ch);
		curl_close($ch);
		return json_decode($html,true);		
	}
	
    //开启开发者模式
	public function open_developmode(){
		$url="https://mp.weixin.qq.com/misc/skeyform?form=advancedswitchform&lang=zh_CN";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['flag']=1;
        $post['type']=2;   
		$post['token']=$this->token;
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$html=curl_exec($ch);
		curl_close($ch);
		//preg_match('%(?<=\"contacts\"\:)(.*)(?=}\)\.contacts)%', $html, $result);
		return json_decode($html,true);		
	}
	
	//接口配置信息
	public function set_api($api_token,$api_url){
		$url="https://mp.weixin.qq.com/advanced/callbackprofile?t=ajax-response&token=".$this->token."&lang=zh_CN";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['callback_token']=$api_token;
        $post['url']=$api_url;   
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		$html=curl_exec($ch);
		curl_close($ch);
		//preg_match('%(?<=\"contacts\"\:)(.*)(?=}\)\.contacts)%', $html, $result);
		return json_decode($html,true);		
	}
	
	//一键配置接口
	public function quick_set_api($api_token,$api_url){
		$this->close_editmode();
		$this->open_developmode();
		return $this->set_api($api_token,$api_url);
	}
}
?>