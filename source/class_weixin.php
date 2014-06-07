<?php
/*
当前版本0.2.12 最后修改日期 2014.3.19 by heisai
update log 2014.3.19
模拟登录类调整自动开启开发模式的URL

update log 2014.3.5
根据官方调整checksign

updata log 2014.2.8
修复客服功能里的一个BUG

updata log 2014.2.4
增加获取微信二维码函数

updata log 2014.1.21
更新排重机制，防止连续收到多次重复信息和事件

updata log 2014.1.17
更新对应微信官方接口的排重机制，防止连续收到多次重复信息

updata log 2014.1.8
更新wz_link_build 包含公众号ID
  
update log 2014.1.3
微调get_account_info方法

update log 2013.12.23
改进客服绑定流程，输入手机号@验证码，即可绑定

update log 2013.12.6
修复纯文字回复标签处理的BUG

update log 2013.11.29
更新wz_build_link 里参数链接方式

update log 2013.11.25
推送号ck_wx方法调整，解决因取不到user_name而导致客服功能失效
现在比对fakeid。
现在向推送号发送@fakeid 来获取个人微信的fakeid来进行绑定

UPDATE LOG 2013.11.16
将模拟登录的cookie保存到不同文件

update log 2013.10.30
更新公众号类中的wz_link_build 修复BUG

update log 2013.10.29
更新模拟登录类里的方法，适应新版本微信公众平台

update log 2013.10.21
微站链接生成方法，可以带参数了

update log 2013.10.20
修复关键词应答,对“0”没反应的问题

update log 2013.10.18
加入微站相关内容
将tpl等3个方法，封装入接收类

update log 2013.10.12
//生成微站链接函数
//$mid  微站模块ID
//$wxid 微信用户ID
function wz_build_link($mid,$wxid)
	
	
update log 2013.9.22
class wechatCallbackapiTest
增加protected function txt_back($content)  返回文字输出
增加protected function save_weixin_member()  保存微信用户资料
重写protected function ck_wx_member($msg)
重写protected function send_to_member($msg,$question_id='',$province='',$nickname='',$to_uid=0,$op_wx=array())
重写protected function question_tpl($msg,$question_id='',$province='',$nickname='',$op_wx=array())


update log 2013.9.18
更新getheadimg()  照片保存路径的问题

update log 2013.9.16
增加获取公众号基本信息方法 public function get_account_info()

update log 2013.9.3
增加一键开启开发模式并配置接口信息

update log 2013.8.30
增加回复上下文模板函数 reply_tpl($member,$reply_id,$content)

update log 2013.8.11
删除多余内容
调整结构以适应类扩展

update log 2013.8.8
增加自定义菜单

update log 2013.8.1
//记录每次自动回复 （暂缓）
自动回复如果没设置封面，自动从wallpaper中设置

update log 2013.7.25
增加关键词自动回复函数keyword_autoback

update log 2013.7.23
增加接受消息后自动回复函数autoback

update log 2013.7.18
从数据库读取关注后显示的信息


update log 2013.7.17
private function send_to_member   调整了设置  如果是非public公共号，在open_member_user表格里获取用户的weixin_xxxx信息


update log 2013.7.16
整合推送端类
将自由函数归类
微信连接文件更新
增加 回复函数模板tpl

update log 2013.7.12
增加函数send_reply3  成员在网页上进行回复
取消法搜搜客服称位，根据公众号名字来命名
更新函数send_to_lawyer  判定推送条件（由会员设置）
*/
if(!defined('IN_SYS')) {
	exit('Access Denied');
}


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
		$this->cookieFile=S_ROOT.'data/cookies/weixin/cookie_'.$this->user.'.txt';
		$this->loginFile=S_ROOT.'data/cookies/weixin/login_'.$this->user.'.txt';
		$this->lastTimeFile=S_ROOT.'data/cookies/weixin/last_'.$this->user.'.txt';
		
		
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
	
	public function sendmsg($content,$fromfakeid,$token){ //发送消息给指定人
		$url="https://mp.weixin.qq.com/cgi-bin/singlesend";
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,'https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&token='.$this->token.'&lang=zh_CN');
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
		$post['t']='ajax-response';
		$post['imgcode']='';
		$post['mask']=false;
		$post['random']=math_random();
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
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIEFILE,$this->cookieFile);
		curl_setopt($ch,CURLOPT_REFERER,$url);	
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
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
	
	public function getheadimg($fromfakeid){
		$url="https://mp.weixin.qq.com/misc/getheadimg";
		$ch=curl_init($url);
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

	public function getmsglist(){
		$url="https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&token=".$this->token."&lang=zh_CN";
		$ch=curl_init($url);
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





//接收端公众号
class wechatCallbackapiTest
{
	protected $timestamp,
	          $op_uid,
			  $op_wxid,
	          $fromUsername,
	          $toUsername,
			  $msgid;
	
	
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->timestamp=$this->checkSignature()){
        	echo $echoStr;
        	//exit;
			return true;
        }else
		{
			return false;
		}
    }

    public function responseMsg()
    {
		global $_SGLOBAL,$_SC;
		
		$timestamp=$this->timestamp;
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName; //开发者微信号
                $keyword = trim($postObj->Content);
				$Event=$postObj->Event;
				$EventKey=$postObj->EventKey;
				$msgid=$postObj->MsgId;
                $time = time();
				
				if($this->check_post($postStr)){
					echo $resultStr;
					exit;
				}				
				
				$op_wxid=$this->check_ghid($toUsername);
				if(!$op_wxid){
					 exit();
				}else{
				  	 $op_uid=$_SGLOBAL['db']->getone('select op_uid from '.tname('open_member_weixin').' where id='.$op_wxid);
					 $this->op_wxid=$op_wxid;
					 $this->op_uid=$op_uid;
                     $this->fromUsername=$fromUsername; 
                     $this->toUsername=$toUsername;
					 $this->msgid=$msgid; 
				}
				

                //判断是否关注事件
				if($Event=='subscribe' || $keyword=="hello2bizuser"){
				   $resultStr=$this->get_subscribe();	
					echo $resultStr;
					exit;
				}
				
				if($keyword=="msg2bizuser"){
				    $resultStr=$this->msg_autoback();	
					echo $resultStr;
					exit;
				}
				
				//自定义菜单事件
				if($Event=='CLICK'){
					$resultStr=$this->get_eventkey($EventKey);
				    	
					echo $resultStr;
					exit;				
				}

				//正常的消息						             
				if($keyword!='')
                {
                          
					$resultStr=$this->get_keyword($keyword);	 
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }
        }else {
        	echo "";
        	exit;
        }

    }
	
	private function check_post($postStr){
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
	
	//默认的获取关注事件，返回输出结果
	protected function get_subscribe(){
		return $this->focus_autoback();
	}
	
	//默认的获取自定义菜单点击函数，返回输出结果
	protected function get_eventkey($eventkey){
		return $this->click_autoback($eventkey);
	}
	
	//默认的获取关键词函数，返回输出结果
	protected function get_keyword($keyword){
      return $this->get_keyword_default($keyword);
	}
	
	//基本的关键词处理函数，返回输出结果
	protected function get_keyword_default($keyword){
		$msg=getstr($keyword);					  						  
		$msg=str_replace("＃","#",$msg);
		$time=time();
		
		switch($msg){
		default:
			if(substr($msg,0,1)=='@'){
				list($at,$uid)=explode('@',$msg,2);
                $contentStr = "@命令已经成功发送。";
                $resultStr = $this->txt_back($contentStr);
			}else{
				$member_num=$this->ck_member_wx($msg); 
				if(strpos($msg,'#')){
						 if($member_num==0){
							           $msgType = "text";
                	                   $contentStr = "请在#前输入正确的回复号。";
                                       $resultStr = $this->txt_back($contentStr);
	
						 }else{
							 $resultStr=$this->msg_autoback();
						 }
				}else{

					     $resultStr=$this->keyword_autoback($msg);
								   
								   
				}
		    }
			return $resultStr;
		}
	}
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return $timestamp;
		}else{
			return false;
		}
	}

    protected function focus_autoback(){
		global $_SGLOBAL,$_SC;
		
                   $rand_pic=array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');
				   $autoreply_type_id=$_SGLOBAL['db']->getone('select afteradd_autoreply_type_id from '.tname('open_member_weixin').' where id='.$this->op_wxid); 
				   if($autoreply_type_id==1){
					   
					$afteradd=db_to_content(htmlspecialchars_decode($_SGLOBAL['db']->getone('select afteradd from '.tname('open_member_weixin').' where id='.$this->op_wxid)));
                	$resultStr = $this->txt_back($afteradd);
					return $resultStr;
				   }
				   if($autoreply_type_id==2){
					$data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_focusreply_info').' where wxid='.$this->op_wxid.' and autoreply_type_id=2');
					if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					$data[0]['summary']=htmlspecialchars_decode($data[0]['summary']); 
					$resultStr =  $this->tpl($data,'news',0,time());
					return $resultStr;
				   }
				   if($autoreply_type_id==3){
					$data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_focusreply_info').' where wxid='.$this->op_wxid.' and autoreply_type_id=3 order by sort_order');
					if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					$resultStr =  $this->tpl($data,'news',0,time());
					return $resultStr;
				   }		
	}

    protected function click_autoback($keyword=''){
	               global $_SGLOBAL,$_SC;
				   $resultStr='';
				   if($keyword==''){
					 return $this->msg_autoback();   
				   }
				   
				   $autoreply_list=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply').' where wxid='.$this->op_wxid);
				   $autoreply_id=0;
				   foreach($autoreply_list as $k=>$v){
					   $kw_arr=explode(chr(32),$v['keyword']);
                       $kw_arr=array_unique($kw_arr);
                       $kw_arr_num=count($kw_arr);
					   if($v['islike']==0){
						 foreach($kw_arr as $key=>$value){
							if($keyword==$value){
							   $autoreply_id=$v['id'];
							   break;	
							}
						 }
						 if($autoreply_id>0) break;
					   }else{
						 foreach($kw_arr as $key=>$value){
							if(mb_strstr($keyword,$value,0,'utf-8')){
							   $autoreply_id=$v['id'];
							   break;	
							}
						 }
						 if($autoreply_id>0) break;
					   }
				   }
				   
				    if($autoreply_id==0){
				      return $this->msg_autoback();
					}else{
						$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply').' where id='.$autoreply_id);
						if($autoreply=$_SGLOBAL['db']->fetch_array($query)){
							if($autoreply['autoreply_type_id']==1){
					               $content=htmlspecialchars_decode($autoreply['content']);
				   				   $content=db_to_content(htmlspecialchars_decode($content));
                	               $resultStr = $this->txt_back($content);
					             return $resultStr;
							}
							
							$rand_pic=array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');

				            if($autoreply['autoreply_type_id']==2){
					                  $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id='.$autoreply['id'].' and autoreply_type_id=2');
									  if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
									  $data[0]['summary']=htmlspecialchars_decode($data[0]['summary']);                            
					                  $resultStr =  $this->tpl($data,'news',0,time(),3);
					                  return $resultStr;
				            }
				            if($autoreply['autoreply_type_id']==3){
					                 $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id='.$autoreply['id'].' and autoreply_type_id=3 order by sort_order');
									 if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					                 $resultStr = $this->tpl($data,'news',0,time(),3);
					                 return $resultStr;
				            }
						}else{
				           return $this->msg_autoback();
						}
						
					}
		
	}

	
	protected function keyword_autoback($keyword=''){
	               global $_SGLOBAL,$_SC;
				   $resultStr='';
				   if($keyword==''){
					 return $this->msg_autoback();   
				   }
				   
				   $autoreply_list=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply').' where wxid='.$this->op_wxid);
				   $autoreply_id=0;
				   foreach($autoreply_list as $k=>$v){
					   $kw_arr=explode(chr(32),$v['keyword']);
                       $kw_arr=array_unique($kw_arr);
                       $kw_arr_num=count($kw_arr);
					   if($v['islike']==0){
						 foreach($kw_arr as $key=>$value){
							if($keyword==$value){
							   $autoreply_id=$v['id'];
							   break;	
							}
						 }
						 if($autoreply_id>0) break;
					   }else{
						 foreach($kw_arr as $key=>$value){
							if(mb_strstr($keyword,$value,0,'utf-8')){
							   $autoreply_id=$v['id'];
							   break;	
							}
						 }
						 if($autoreply_id>0) break;
					   }
				   }

				    if(!$autoreply_id){
				      return $this->msg_autoback();
					}else{
						$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply').' where id='.$autoreply_id);
						if($autoreply=$_SGLOBAL['db']->fetch_array($query)){
							if($autoreply['autoreply_type_id']==1){
					               $content=htmlspecialchars_decode($autoreply['content']);
   			   					   $content=db_to_content(htmlspecialchars_decode($content));
                	               $resultStr = $this->txt_back($content);
					             return $resultStr;
							}


							$rand_pic=array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');
							
				            if($autoreply['autoreply_type_id']==2){
					                  $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id='.$autoreply['id'].' and autoreply_type_id=2');
									  if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
									  $data[0]['summary']=htmlspecialchars_decode($data[0]['summary']);                            
					                  $resultStr =  $this->tpl($data,'news',0,time(),3);
					                  return $resultStr;
				            }
				            if($autoreply['autoreply_type_id']==3){
					                 $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id='.$autoreply['id'].' and autoreply_type_id=3 order by sort_order');
									 if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					                 $resultStr =  $this->tpl($data,'news',0,time(),3);
					                 return $resultStr;
				            }
						}else{
				           return $this->msg_autoback();
						}
						
					}
		
	}
	
    protected function msg_autoback(){
	               global $_SGLOBAL,$_SC;
				   $resultStr='';
				   $autoreply_type_id=$_SGLOBAL['db']->getone('select aftermsg_autoreply_type_id from '.tname('open_member_weixin').' where id='.$this->op_wxid); 
				   if($autoreply_type_id==1){
					$content=htmlspecialchars_decode($_SGLOBAL['db']->getone('select aftermsg from '.tname('open_member_weixin').' where id='.$this->op_wxid));
					$content=db_to_content(htmlspecialchars_decode($content));
                	$resultStr = $this->txt_back($content);
					return $resultStr;
				   }
				   
				   $rand_pic=array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');
				   if($autoreply_type_id==2){
					$data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_msgreply_info').' where wxid='.$this->op_wxid.' and autoreply_type_id=2');
					if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					$data[0]['summary']=htmlspecialchars_decode($data[0]['summary']);                            
					$resultStr =  $this->tpl($data,'news',0,time(),2);
					return $resultStr;
				   }
				   if($autoreply_type_id==3){
					$data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_msgreply_info').' where wxid='.$this->op_wxid.' and autoreply_type_id=3 order by sort_order');
					if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					$resultStr =  $this->tpl($data,'news',0,time(),2);
					return $resultStr;
				   }
		
		
	}


    //记录消息来源的用户资料,返回微笑微信中用户信息数组,包含[uid,province,nickname]
    protected function save_weixin_member(){
	  global $_SGLOBAL;
	  $create_time=$this->timestamp;
	  $wxid=$this->fromUsername;
	  $op_wxid=$this->op_wxid;
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
				  $member['province']=$contactinfo['province'];
				  $member['nickname']=$contactinfo['nick_name'];
				  break;					
			    }
		    }
	     }
		 
		 //保存头像
		 $member['fakeid']=$_SGLOBAL['db']->getone('select fakeid from '.tname('weixin_member').' where uid='.$member['uid']);
		 $ro->getheadimg($member['fakeid']);
		 return $member;
	 }else{
		 return false;
		  
	 }
   }


    //绑定公众号，接收用户的提问
    protected function ck_member_wx($msg){
	  global $_SGLOBAL;
 	  $create_time=$this->timestamp;
	  $wxid=$this->fromUsername;
	  $op_wxid=$this->op_wxid;
	  if($wxid=='') return false;
	  $return=false;
	  $member=$this->save_weixin_member();  //匹配消息，获取微笑微信内的用户信息
       
		
		//将消息发给谁 
		if(strpos($msg,'#')){
		  list($reply_id,$content)=explode('#',$msg,2);
		  $question_uid=$_SGLOBAL['db']->getone('select q.uid from '.tname('weixin_question').' as q inner join '.tname('weixin_reply').' as r on q.id=r.question_id  where r.id='.intval($reply_id));
		  if($question_uid!=$member['uid']){
			return 0;  
		  }
		  $to_uid=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_reply').' where id='.intval($reply_id));		  		  
		  if($to_uid>0){
		    $q_arr['to_uid']=$to_uid;
		    $q_arr['uid']=$member['uid'];
		    $q_arr['content']=$content;
		    $q_arr['province']=$member['province'];
		    $q_arr['addtime']=$create_time;
	        $id=inserttable(tname('weixin_question'),$q_arr,1);		
	        $return=$this->send_to_member($content,$id,$member['province'],$member['nickname'],$to_uid);		  						
		  }
		}else{
		  $to_uid=0;		  
		 /*测试内容
		  if($uid==2){
			  $to_uid=1;
		  }else{
			  $to_uid=0;
		  }
         //测试内容END*/
			

		  $q_arr['to_uid']=$to_uid;		 
		  $content=$msg;
		  $q_arr['uid']=$member['uid'];
		  $q_arr['content']=$content;
		  $q_arr['province']=$member['province'];
		  $q_arr['addtime']=$create_time;
	      $id=inserttable(tname('weixin_question'),$q_arr,1);		
	      $return=$this->send_to_member($content,$id,$member['province'],$member['nickname'],$to_uid);		  
		}
	  return $return;
    }




    //将接收的微信提问，通过推送号发送给成员微信号
    protected function send_to_member($msg,$question_id='',$province='',$nickname='',$to_uid=0,$op_wx=array()){
	  global $_SGLOBAL;
	  $return=false;
      
	  //推送号
	  $ro = new WX_Remote_Opera();
      $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_pushweixin')." where op_uid=".$this->op_uid);
      if($op_pushwx=$_SGLOBAL['db']->fetch_array($query)){
         $token=$ro->init($op_pushwx['username'],$op_pushwx['password']);
	  }
	  
	  $count=0;
	  $newmsg=$this->question_tpl($msg,$question_id,$province,$nickname);
	  $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id=".$this->op_wxid);
      $op_wx=$_SGLOBAL['db']->fetch_array($query);
	  
	  if($to_uid>0){
		  $count=1;
		  $fakeid=$_SGLOBAL['db']->getone('select weixin_fakeid from '.tname('open_member_user').' where op_uid='.$this->op_uid.' and uid='.$to_uid);
		  $ro->sendmsg($newmsg,$fakeid,$token);
	  }else{
		if($op_wx['public']==1){
		  $memberlist=$_SGLOBAL['db']->getall('select * from '.tname('open_member_user').' where weixin_state=1');
		}else{
		  $memberlist=$_SGLOBAL['db']->getall('select * from '.tname('open_member_user').' where weixin_state=1 and op_uid='.$this->op_uid);
		}
		foreach($memberlist as $k=>$v){
			$ispush=0;
    		if($v['weixin_push']==1){
				 $ispush=1;
			}elseif($v['weixin_push']==2){
			  	$my_province=$_SGLOBAL['db']->getone('select region_name from '.tname('region').' where region_id='.$v['province']);
				if($my_province==$province){
					 $ispush=1;
				}
			}
			
			if($ispush==1){
		      $ro->sendmsg($newmsg,$v['weixin_fakeid']);
		      $count++; 
		    }
		}
	  }
	    $return=$count;
	  return $return;	
    }

   //问题上下文模板
   protected function question_tpl($msg,$question_id='',$province='',$nickname='',$op_wx=array()){
	 global $_SGLOBAL;
	  $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id=".$this->op_wxid);
      $op_wx=$_SGLOBAL['db']->fetch_array($query);
	 $newmsg='['.$op_wx['weixin_name'].']来自'.$province.'的'.$nickname.'提问： '.chr(10).$msg.chr(10).chr(10).'  (回复格式: '.$question_id.'#内容)';	  
     return $newmsg; 
   }	

   //返回文字结果
	protected function txt_back($content){
			     $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
                 $msgType = "text";
             return sprintf($textTpl, $this->fromUsername, $this->toUsername, time(), $msgType, $content);
		
	}  
  


//消息模版
protected function tpl($data,$type = 'news',$flg = 0,$time,$tp=1){
   global $_SC;
   $fu=$this->fromUsername;
   $tu=$this->toUsername;
   if($type == 'news'){
         $num  = count($data);  //统计数量
         if($num > 1){  //返回多条
           $add = $this->news_add($data,$tp);
           $tpl = " <xml>
           <ToUserName><![CDATA[".$fu."]]></ToUserName>
           <FromUserName><![CDATA[".$tu."]]></FromUserName>
           <CreateTime>".$time."</CreateTime>
           <MsgType><![CDATA[news]]></MsgType>
           <Content><![CDATA[%s]]></Content>
           <ArticleCount>".$num."</ArticleCount>
           <Articles>
           ".$add."
           </Articles>
           <FuncFlag>".$flag."</FuncFlag>
           </xml> ";
           return $tpl;
        }else{   //返回单条
           if($data[0]['wz_mid']>0){
	          $data[0]['url']=$this->wz_build_link($data[0]['wz_mid'],$data[0]['query']);
	       }else{
	          $data[0]['url']=htmlspecialchars_decode($data[0]['url']);
	       }
		   if($data[0]['url']=='') $data[0]['url']=$_SC['site_host']."/wx_appmsg.php?id=".$data[0]['id']."&tp=".$tp;
           $tpl = " <xml>
           <ToUserName><![CDATA[".$fu."]]></ToUserName>
           <FromUserName><![CDATA[".$tu."]]></FromUserName>
           <CreateTime>".$time."</CreateTime>
           <MsgType><![CDATA[news]]></MsgType>
           <Content><![CDATA[%s]]></Content>
           <ArticleCount>1</ArticleCount>
           <Articles>
           <item>
           <Title><![CDATA[".$data[0]['title']."]]></Title>
           <Description><![CDATA[".$data[0]['summary']."]]></Description>
           <PicUrl><![CDATA[".$data[0]['pic']."]]></PicUrl>
           <Url><![CDATA[".$data[0]['url']."]]></Url>
           </item>
           </Articles>
           <FuncFlag>".$flag."</FuncFlag>
           </xml> ";
           return $tpl;
        }
   }elseif($type == 'text'){
        $tpl = "<xml>
        <ToUserName><![CDATA[".$fu."]]></ToUserName>
        <FromUserName><![CDATA[".$tu."]]></FromUserName>
        <CreateTime>".$time."</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[".$data."]]></Content>
        <FuncFlag>".$flag."</FuncFlag>
        </xml>";
        return $tpl;
   }
}

//追加模版
protected function news_add($data,$tp){
	global $_SC;
    $add = "";
    foreach ($data as $k=>$v){
    if($data[$k]['wz_mid']>0){
	  $data[$k]['url']=$this->wz_build_link($data[$k]['wz_mid'],$data[$k]['query']);
	}else{
	  $data[$k]['url']=htmlspecialchars_decode($data[$k]['url']);
	}
    if($data[$k]['url']=='') $data[$k]['url']=$_SC['site_host']."/wx_appmsg.php?id=".$v['id']."&tp=".$tp;
	$add .= "<item>
      <Title><![CDATA[".$v['title']."]]></Title>
      <Description><![CDATA[".$v['summary']."]]></Description>
      <PicUrl><![CDATA[".$v['pic']."]]></PicUrl>
      <Url><![CDATA[".$data[$k]['url']."]]></Url>
      </item>  ";
    }
    return $add;
}

//生成微站链接
//$mid  微站模块ID
//$wxid 微信用户ID
protected function wz_build_link($mid,$query=array()){
	global $_SGLOBAL,$_SC;
	$wxid=$this->fromUsername;
	$op_wxid=$this->op_wxid;
	$token=random(6);
	$setarr=array(
	'wxid'=>$wxid,
	'op_wxid'=>$op_wxid,
	'token'=>$token,
	'mid'=>$mid,
	'expires_in'=>3600,
	'state'=>0,
	'addtime'=>$_SGLOBAL['timestamp']
	);
	$lasttokenid=$_SGLOBAL['db']->getone('select id from '.tname('wz_token').' where wxid="'.$wxid.'" and mid='.$mid.' and op_wxid='.$op_wxid.' and state=0 and addtime>"'.($_SGLOBAL['timestamp']-3600).'" order by addtime desc');
	if($lasttokenid>0){
		$token=$_SGLOBAL['db']->getone('select token from '.tname('wz_token').' where id='.$lasttokenid);
	}else{
	  updatetable(tname('wz_token'),array('state'=>1),array('wxid'=>$wxid,'mid'=>$mid,'op_wxid'=>$op_wxid));
	  inserttable(tname('wz_token'),$setarr);
    }
	if($query){
	   $querystr='&'.http_build_query($query);
	}
	$link=$_SC['site_host'].'/weizhan/?wxid='.$wxid.'&token='.$token.'&mid='.$mid.$querystr;
	return $link;	
}



	
	//$op_wxid->open_member_weixin的id    比对ghid
	protected function check_ghid($ghid=''){
		global $_SGLOBAL;
		$op_wxid=$_SGLOBAL['db']->getone('select id from '.tname('open_member_weixin').' where ghid="'.$ghid.'" and state=1');
		if($op_wxid>0){
		  return $op_wxid;
		}else{
		  return false;	
		}
	}


}


//推送端公众号
class wechatCallbackapiTest2
{
	
	protected $timestamp,
	 $op_uid,
	 $op_wxid,
	 $fromUsername,
	 $toUsername;
		 
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->timestamp=$this->checkSignature()){
        	echo $echoStr;
        	//exit;
			return true;
        }else
		{
			return false;
		}
    }

    public function responseMsg()
    {
		global $_SGLOBAL;
		$timestamp=$this->timestamp;
		
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
				$Event=$postObj->Event;
				$EventKey=$postObj->EventKey;
				$msgid=$postObj->MsgId;
                $time = time();
				
				if($this->check_msg($postObj)){
					echo $resultStr;
					exit;
				}				
				
				$this->op_wxid=$this->check_ghid($toUsername);
				if(!$this->op_wxid){
					 exit();
				}else{
				  	 $this->op_uid=$_SGLOBAL['db']->getone('select op_uid from '.tname('open_member_pushweixin').' where id='.$this->op_wxid);
					 $this->fromUsername=$fromUsername; 
                     $this->toUsername=$toUsername; 
				}
				

				


                //判断是否关注事件
				if($Event=='subscribe'){
					$resultStr=$this->focus_autoback();
					echo $resultStr;
					exit;
				}

										             
				if(!empty( $keyword ))
                {
                          $resultStr=$this->get_keyword($keyword);
                	      echo $resultStr;
                }else{
                	echo "Input something...";
                }
        }else {
        	echo "";
        	exit;
        }

    }

	private function check_msg($obj){
		global $_SGLOBAL;
		$msg_content=json_encode($obj);
		$arr=json_decode($msg_content,true);
		if($arr['Event']){
		  return false;	
		}
		$msgid=$_SGLOBAL['db']->getone('select msgid from '.tname('weixin_message').' where msgid="'.$arr['MsgId'].'"');
		if($msgid){
			return true;
		}else{
		    inserttable(tname('weixin_message'),array('msgid'=>$arr['MsgId'],'msg_content'=>$msg_content));
			return false;	
		}
	}	

    protected function focus_autoback(){
                    $content = "请输入格式: 手机号码@绑定码 来绑定您的帐号,例如: ".chr(10)."1350000000@abcdef ";
                	$resultStr = $this->txt_back($content);
                    return $resultStr;
		
	}
    	
	protected function get_keyword($keyword){
		$msg=getstr($keyword);
	    //回复问题
	    $msg=str_replace("＃","#",$msg);

        if(strpos($msg,'#')){
			$reply_state=$this->send_reply($msg);
			if($reply_state<0){
                $resultStr = $this->txt_back("回复失败，请确定您拥有客服权限");
			}elseif($reply_state==2){
                $resultStr = $this->txt_back("回复失败，您的同事已经回复了本问题:)");
			}elseif($reply_state==1){
                $resultStr = $this->txt_back("您已经成功回复了客户的问题。");
			}
			  return $resultStr; 
		}
										   
							
		//微信绑定		   
		if(strpos($msg,'@')){
			$uid=$this->ck_wx($msg);
			
			if($uid>0){
                $content = "您的微信已经成功绑定，您将可以用微信收取和回复咨询。";
                $resultStr = $this->txt_back($content);
				return $resultStr;
			}else{
                $content = "绑定失败，请输入格式: 手机号码@绑定码 来绑定您的帐号,例如: ".chr(10)."1350000000@abcdef ";
                $resultStr = $this->txt_back($content);
				return $resultStr;
			}
		}else{
			$uid=$this->check_member();
			if($uid>0){
               $content = "请输入格式: ".chr(10).chr(10)."问题编号#内容".chr(10).chr(10)."来回复来自公众号的问题,例如: ".chr(10)."122#您好，您的问题我们已经收到。";
               $resultStr = $this->txt_back($content);
		       return $resultStr;
			}else{
               $content = "请输入格式: 手机号码@绑定码 来绑定您的帐号,例如: ".chr(10)."1350000000@abcdef ";
               $resultStr = $this->txt_back($content);
		       return $resultStr;
			}
		}
	}


	//$op_wxid->open_member_pushweixin的id    比对ghid
	protected function check_ghid($ghid=''){
		global $_SGLOBAL;
		$op_wxid=$_SGLOBAL['db']->getone('select id from '.tname('open_member_pushweixin').' where ghid="'.$ghid.'" and state=1');
		if($op_wxid>0){
		  return $op_wxid;
		}else{
		  return false;	
		}
	}


    //推送端检查成员资料，绑定微信和成员的资料
    protected function ck_wx($msg){
	  global $_SGLOBAL;
	  $create_time=$this->timestamp;
	  $wxid=$this->fromUsername;
	  list($mobile,$weixin_code)=explode('@',$msg,2);
	  $mobile=getstr($mobile);
	  $weixin_code=getstr($weixin_code);
	  $uid=$_SGLOBAL['db']->getone('select uid from '.tname('open_member_user').' where op_uid='.$this->op_uid.' and state=1 and weixin_state=0 and mobile="'.$mobile.'" and weixin_code="'.$weixin_code.'"');
	  if(!$uid) $uid=0;
      if($uid>0){
          $ro = new WX_Remote_Opera();
          $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_pushweixin')." where id=".$this->op_wxid);
          if($op_wx=$_SGLOBAL['db']->fetch_array($query)){
             $ro->init($op_wx['username'],$op_wx['password']);
		  }
		  $msglist=$ro->getmsglist();
		  foreach($msglist as $k=>$v){
			    if($v['date_time']==$create_time){
				   updatetable(tname('open_member_user'),array('weixin_state'=>1,'weixin_wxid'=>$wxid,'weixin_fakeid'=>$v['fakeid']),array('uid'=>$uid,'op_uid'=>$this->op_uid));					
				   break;					
			    }
		  }
	  }
	  return $uid;
    }

    protected function check_member(){
	  global $_SGLOBAL;
	  $create_time=$this->timestamp;
	  $wxid=$this->fromUsername;
	  $uid=$_SGLOBAL['db']->getone('select uid from '.tname('open_member_user').' where op_uid='.$this->op_uid.' and weixin_state=1 and weixin_wxid="'.$wxid.'"');
	  if($uid>0){
		return $uid;  
	  }else{
		return 0;  
	  }
	}

    //推送端检查成员资料，如果是绑定的成员，通过推送端回复用户的提问
    protected function send_reply($msg){
	  global $_SGLOBAL;
	  $create_time=$this->timestamp;
	  $wxid=$this->fromUsername;
	  list($question_id,$content)=explode('#',$msg,2);
	  $uid=$_SGLOBAL['db']->getone('select uid from '.tname('open_member_user').' where op_uid='.$this->op_uid.' and weixin_state=1 and weixin_wxid="'.$wxid.'"');
      if($uid>0){
         $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_user')." where op_uid=".$this->op_uid." and uid=".$uid);
         $member=$_SGLOBAL['db']->fetch_array($query);
		 $member['province'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$member['province']);
         $asker_uid=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_question').' where id='.$question_id);
		 $op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('weixin_member').' where uid='.$asker_uid); 
		 $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id='".$op_wxid."' and op_uid='".$this->op_uid."'");
		 if($op_wx=$_SGLOBAL['db']->fetch_array($query)){
		 
		   $fakeid=$_SGLOBAL['db']->getone('select fakeid from '.tname('weixin_member').' where uid='.$asker_uid);
		   if($fakeid){
			 $replyed=$_SGLOBAL['db']->getone('select replyed from '.tname('weixin_question').' where id='.$question_id);
			 if($replyed==0){
	           $reply_id=inserttable(tname('weixin_reply'),array('uid'=>$uid,'question_id'=>$question_id,'content'=>$content,'addtime'=>$create_time),1);
			   updatetable(tname('weixin_question'),array('replyed'=>1),array('id'=>$question_id));
               $ro = new WX_Remote_Opera();
               $token=$ro->init($op_wx['username'],$op_wx['password']);
	           $replymsg=$this->reply_tpl($member,$reply_id,$content);     			                
		       $ro->sendmsg($replymsg,$fakeid,$token);
			   return 1;
			 }else{
				 return 2;
			 }
		   }else{
			 return -3;   
		   }
		 }else{
			return -2; 
		 }
      }else{
		return -1;  
	  }
   }
   
   //回复上下文模板
   protected function reply_tpl($member,$reply_id,$content){
	
	$replymsg=$member['fullname'].'回复您：'.chr(10).$content.chr(10).chr(10).'单独回复：'.chr(10).$reply_id.'#内容';     			                
	return $replymsg;   
   }


   //返回文字结果
	protected function txt_back($content){
			     $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
                 $msgType = "text";
             return sprintf($textTpl, $this->fromUsername, $this->toUsername, time(), $msgType, $content);
		
	}  
  

		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return $timestamp;
		}else{
			return false;
		}
	}
}


//通过OP平台网页上回复问题
function send_reply2($question_id,$content){
	  global $_SGLOBAL;
      if($question_id>0){
         $asker_uid=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_question').' where id='.$question_id);
		 $op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('weixin_member').' where uid='.$asker_uid); 
		 $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id=".$op_wxid." and op_uid=".$_SGLOBAL['uid']);
		 if($op_wx=$_SGLOBAL['db']->fetch_array($query)){		 
		   $fakeid=$_SGLOBAL['db']->getone('select fakeid from '.tname('weixin_member').' where uid='.$asker_uid);
		   if($fakeid){
	         $reply_id=inserttable(tname('weixin_reply'),array('uid'=>0,'question_id'=>$question_id,'content'=>$content,'addtime'=>$_SGLOBAL['timestamp']),1);
			 updatetable(tname('weixin_question'),array('replyed'=>1),array('id'=>$question_id));
             $ro = new WX_Remote_Opera();
             $token=$ro->init($op_wx['username'],$op_wx['password']);
	         $replymsg=$content;	             
		     $ro->sendmsg($replymsg,$fakeid,$token);
		   }
		 }
      }
}

//通过成员平台网页上回复问题
function send_reply3($question_id,$content){
	  global $_SGLOBAL;
	  
	  $uid=$_SGLOBAL['uid'];  //成员的uid
      if($question_id>0){
       if($uid>0){
         $query=$_SGLOBAL['db']->query("select * from ".tname('member')." where uid=".$uid);
         $member=$_SGLOBAL['db']->fetch_array($query);
		 $member['province'] = $_SGLOBAL['db']->getone("SELECT region_name FROM " . tname('region') ." WHERE region_id = ".$member['province']);
         $asker_uid=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_question').' where id='.$question_id);
		 $op_wxid=$_SGLOBAL['db']->getone('select op_wxid from '.tname('weixin_member').' where uid='.$asker_uid); 
		 $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id=".$op_wxid);
		 if($op_wx=$_SGLOBAL['db']->fetch_array($query)){		 
		   $fakeid=$_SGLOBAL['db']->getone('select fakeid from '.tname('weixin_member').' where uid='.$asker_uid);
		   if($fakeid){
	         $reply_id=inserttable(tname('weixin_reply'),array('uid'=>$uid,'question_id'=>$question_id,'content'=>$content,'addtime'=>$_SGLOBAL['timestamp']),1);
			 updatetable(tname('weixin_question'),array('replyed'=>1),array('id'=>$question_id));
             $ro = new WX_Remote_Opera();
             $token=$ro->init($op_wx['username'],$op_wx['password']);
			 if($member['member_tp']==0){
	           $replymsg=$op_wx['weixin_name'].''.$member['fullname'].'回复您：'.chr(10).$content.chr(10).chr(10).'一对一交流格式：'.chr(10).$reply_id.'#您要说的话';	             
			 }
		     $ro->sendmsg($replymsg,$fakeid,$token);
		   }
		 }
	   }
      }
}



//////////////////////
/*
* 功能: 成对匹配html标签对, 跟javascript的$.getElementById() 方法 一样.
* 实现方法: 成对匹配html标签对(多层嵌套也能完整匹配)
            ( 没有用到递归, 而是通过位置回退方法、顺序进行匹配 )
* 参数: 
    @string: $content: 输入内容; 
    @string: $id 标签的id; 
    @string: $return_type   设定返回值的类型,
                可选返回 'endpos'(结束位置) 或者 'substr'(截取结果). 
* 返回:  数字 或 字符串 , 取决于 $return_type的设置. 

* @author: 王奇疏 

*/
function getElementById( $content , $id , $return_type='substr' ) {
// 匹配唯一标记的标签对
    if ( preg_match( '@<([a-z]+)[^>]*id=[\"\']?'.$id.'[\"\']?[^>]*>@i' , $content , $res ) ){
        
        $start = $next_pos = strpos( $content , $res[0] );
        ++$next_pos;

        $start_tag = '<'.$res[1]; // 开始标签
        $end_tag = '</'.$res[1].'>'; // 结束标签
        $i = 1;
        $j = 0; // 防死循环　　　　  
        
        // 只要计数大于0, 就继续查,查到计数器为0为止, 就是最终的关闭标签.
        while ( $i > 0 && $j < 1024 ){
             $p_start = stripos( $content , $start_tag , $next_pos );
            $p_end = stripos( $content , $end_tag , $next_pos );
            if ( false === $p_start && false !== $p_end ){

                $next_pos = $p_end + 1;

                break;

           }            
            // 如果
            elseif ( $p_start > $p_end ){
                $next_pos = $p_end + 1;
                --$i;
            }
            else{
                $next_pos = $p_start + 1;
                ++$i;
            }
        }
        if ( $j == 1024 ){
            exit( '调用getElementById时出现错误::<font color="red">您的标签'.htmlspecialchars( "{$start_tag} id='{$id}'>" ).' 在使用时根本没有闭合,不符合xhtml,系统强制停止匹配</font>.' ); 
        }
        // 返回结果
        if ( 'substr' == $return_type ){
            return substr( $content , $start , $next_pos-$start + strlen( $end_tag ) );
        }
        elseif ( 'endpos' == $return_type ){
            return $next_pos + strlen( $end_tag ) - 1 ;
        }
        else{
            return false;
        }
    }
    else{
        return false;
    }
}


function getElementByClass( $content , $class , $return_type='substr' ) {
// 匹配唯一标记的标签对
    if ( preg_match( '@<([a-z]+)[^>]*class=[\"\']?'.$class.'[\"\']?[^>]*>@i' , $content , $res ) ){
        
        $start = $next_pos = strpos( $content , $res[0] );
        ++$next_pos;

        $start_tag = '<'.$res[1]; // 开始标签
        $end_tag = '</'.$res[1].'>'; // 结束标签
        $i = 1;
        $j = 0; // 防死循环　　　　  
        
        // 只要计数大于0, 就继续查,查到计数器为0为止, 就是最终的关闭标签.
        while ( $i > 0 && $j < 1024 ){
             $p_start = stripos( $content , $start_tag , $next_pos );
            $p_end = stripos( $content , $end_tag , $next_pos );
            if ( false === $p_start && false !== $p_end ){

                $next_pos = $p_end + 1;

                break;

           }            
            // 如果
            elseif ( $p_start > $p_end ){
                $next_pos = $p_end + 1;
                --$i;
            }
            else{
                $next_pos = $p_start + 1;
                ++$i;
            }
        }
        if ( $j == 1024 ){
            exit( '调用getElementByClass时出现错误::<font color="red">您的标签'.htmlspecialchars( "{$start_tag} id='{$id}'>" ).' 在使用时根本没有闭合,不符合xhtml,系统强制停止匹配</font>.' ); 
        }
        // 返回结果
        if ( 'substr' == $return_type ){
            return substr( $content , $start , $next_pos-$start + strlen( $end_tag ) );
        }
        elseif ( 'endpos' == $return_type ){
            return $next_pos + strlen( $end_tag ) - 1 ;
        }
        else{
            return false;
        }
    }
    else{
        return false;
    }
}


/*
* 功能: php版getElementsByTag , 根据标签名获取 标签列表.(多层嵌套也正常匹配)
* 参数:
$str 被查找的字符串;
$start_tag 开始标记;
$close_tag 关闭标记;
[可选] $tag_slashe 加在 标记 之前, 用于转义的字符(目的是防止混淆正常字符和标记), 默认false;
[可选] $begin_pos , 用于指定开始查找的位置, 默认0 . 该参数在模仿类似js的语法: obj.getElementsByTagName() , 查找指定对象之下的标签列表时非常有用处;
[可选] $end_pos , 用于指定结束位置, 默认0 .
* 返回: 查找成功返回数组列表, 查找没有结果则返回false.
* 作者: 王奇疏
*/
function getElementsByTag( $str , $start_tag , $close_tag , $tag_slashe=false , $begin_pos=0 , $end_pos=0 ) {
$list = array(  );
$start = $begin_pos;// 临时存储字符位置
$end = $end_pos;
$start_pos = $close_pos = 0; // 每一对标记的起止位置
$stack = array(); // 一个数组, 用来借助栈的作用保存上一次循环的数据.    
$s_len = strlen( $start_tag ); // 标记本身的长度
$c_len = strlen( $close_tag );
$slashe_ord = ord( $tag_slashe ); // 转义符
while( false !== ( $start = stripos( $str , $start_tag , $start ) ) ) {
$i = 0; // 标记 计数器 
$j = 1024; // 最大循环计数器 , 防死循环
$start_pos = $start; // 初始化每对标记的起止位置
$close_pos = $start_pos + $s_len; // (close_tag的开始位置应在start_tag之后)
$stack = array();
while ( $j > 0 ){
// 一次搜索两种标记: $start_tag , $close_tag
if ( $start_pos > $start ) {
$start_pos = stripos( $str , $start_tag , $start_pos );
}
$close_pos = stripos( $str , $close_tag , $close_pos );
// 如果 找到的标记 的前面一个字符是转义符 , 则再重新搜索一次.
if ( false !== $start_pos && $slashe_ord === ord( $str[ $start_pos - 1 ] ) ) {
$start_pos = stripos( $str , $start_tag , $start_pos + $s_len );
}
if ( false !== $close_pos && $slashe_ord === ord( $str[ $close_pos - 1 ] ) ) {
$close_pos = stripos( $str , $close_tag , $close_pos + $c_len );
}
// 把 关闭标记的位置 存进栈内, 保持只存2条.
if ( $j === 1024 ) {
$stack[] = $close_pos; // (第1次多存1条) 
                    }
$stack[] = $close_pos;
// 开始标记 大于 上一个关闭标记,  
if ( $start_pos > ( $prev = array_shift( $stack ) ) ) {
$prev += $c_len;
break;
}
// 找不到开始标签时, 从哪开头?
elseif ( false === $start_pos ) {
$prev += $c_len;
break;
}
// 找不到闭合的标签时, 从哪开头?怎么处理?
elseif ( false === $close_pos ) {
show_match_error( $str , $start , $start_tag , $close_tag  );
return false;
}
else {
$start_pos += $s_len;
$close_pos += $c_len;
}
--$j;
}
if ( $j == 0 ) {
show_match_error( $str , $start , $start_tag , $close_tag  );
return false;
}
$list[] = substr( $str , $start , $prev - $start );
$start = $prev;
}
return $list;
}
// 仅仅用于显示匹配错误信息的函数
function show_match_error( $str , $sub_start , $start_tag , $close_tag ) {
$count_line = substr( $str , 0 , $sub_start );
$count_line = substr_count( $count_line , PHP_EOL ) + 1;
trigger_error( '<div style="padding: 10px;font-family:tahoma;font-size:12px; border:1px solid #c1c1c1; "><strong>出现标签未闭合的错误：</strong><br /><br />程序设定的<br />开始标记是: <font color="red">'.htmlspecialchars( "{$start_tag} " ).'</font> <br />闭合标记是: <font color="red">'.htmlspecialchars( "{$close_tag} " ).'</font> <br /><font color="red"><br />现检查到有一个标记没有闭合，不符合xhtml规范，已经停止匹配.</font><br /><br />未闭合标记的位置在原文中从<font color="red">第'.$count_line.'行</font>开始 , 在周围缺少闭合标签. <br />(<font color="red">第'.$count_line.'行</font>)大约是从以下字符开始, 请查看您原来的数据,检查标记是否闭合完整: <br />"<pre>'.htmlspecialchars( substr( $str , $sub_start , 80 ) ).'</pre> "</div>' );
}

function math_random () {
  return (float)rand()/(float)getrandmax();
}
?>