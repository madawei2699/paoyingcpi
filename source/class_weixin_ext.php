<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}


//微笑微信公众号扩展类
class wechat_main_class extends wechatCallbackapiTest{
/*	
 
------------------------基本方法----------------------------
只要将return的方法，改为你自己的方法，即可实现自定义的功能
	
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
	
	//用来匹配新用户的fakeid，并记录下微信用户的信息，保存在数据库中，返回数组[uid,province,nickname]
	protected function save_weixin_member()
	
------------------------------------------------------------	
示例，以下是一个让微信用户回复"服务"，让微信用户进行会员注册的功能，已注册会员则返回会员菜单



   	protected function get_keyword($keyword){
	   global $_SGLOBAL,$_SC;
	   
 	   if($keyword=='服务'){
				 $uid=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_member').' where wxid="'.$this->fromUsername.'"');
	             $get_name=getcount(tname('weixin_member_profile'),array('uid'=>$uid,'name'=>'姓名'));

				 
				if(!$get_name){ 
                   $contentStr = '请先输入:'.chr(10).'您的姓名@您所在的公司'.chr(10).chr(10).'即可注册并使用微笑微信高级服务。';
     			   $resultStr = $this->txt_back($contentStr);
				   return $resultStr;   
				}else{			   
				   $resultStr =  $this->custom_autoback();
				   return $resultStr;
				}
				
	   
	   }
	   $uid=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_member').' where wxid="'.$this->fromUsername.'"');
	   $get_name=getcount(tname('weixin_member_profile'),array('uid'=>$uid,'name'=>'姓名'));
	   if(!$get_name ){
	   $msg=getstr($keyword);	   
 		if(strpos($msg,'@')){
		   list($fullname,$corp)=explode('@',$msg,2);
		   $return=0;
		   if(!$fullname || !$corp){
			return $this->txt_back('请输入：' .chr(10). '您的姓名@所在公司');   
		   }
		   $return=$this->save_profile($fullname,$corp);  //记录用户资料
		   if($return){
			  $resultStr =  $this->custom_autoback();
			  return $resultStr;
		   }
		}else{
		  return $this->get_keyword_default($keyword); 
		}
	   }else{
		  return $this->get_keyword_default($keyword); 
	   }
      
	}

	protected function save_profile($fullname,$corp){
      global $_SGLOBAL;
	  $member=$this->save_weixin_member();  //匹配消息，获取微笑微信内的用户信息
      if($member){
		 //保存姓名
		 if(getcount(tname('weixin_member_profile'),array('uid'=>$member['uid'],'name'=>'姓名'))){
		  updatetable(tname('weixin_member_profile'),array('value'=>$fullname),array('uid'=>$member['uid'],'name'=>'姓名')); 
		 }else{
		  inserttable(tname('weixin_member_profile'),array('uid'=>$member['uid'],'name'=>'姓名','value'=>$fullname,'addtime'=>$_SGLOBAL['timestamp']));
		 }
		 //保存公司
		 if(getcount(tname('weixin_member_profile'),array('uid'=>$member['uid'],'name'=>'公司'))){
		  updatetable(tname('weixin_member_profile'),array('value'=>$corp),array('uid'=>$member['uid'],'name'=>'公司')); 
		 }else{
		  inserttable(tname('weixin_member_profile'),array('uid'=>$member['uid'],'name'=>'公司','value'=>$corp,'addtime'=>$_SGLOBAL['timestamp']));
		 }
		  return true;		 
	  }else{
		  return false;
	  }
	}


   
   //自定义的返回结果
   protected function custom_autoback(){
	   global $_SGLOBAL,$_SC;
                   $query=$_SGLOBAL['db']->query("select * from ".tname('weixin_member')." where wxid='".$this->fromUsername."'");
                   $member=$_SGLOBAL['db']->fetch_array($query);
				   $member['profile']=$_SGLOBAL['db']->getall('select * from '.tname('weixin_member_profile').' where uid='.$member['uid']);
				   foreach($member['profile'] as $k=>$v){
					if($v['name']=='姓名') $member['fullname']=$v['value'];
					if($v['name']=='公司') $member['corp']=$v['value'];    
				   }
				   if(!$member['fullname']) $member['fullname']=$member['nickname'];
				   $data[0]['pic']=$_SC['site_host'].'/mpres/wallpaper/1.jpg';
				   $data[0]['title']='您好,'.$member['fullname'].'('.$member['corp'].')'.chr(10).'以下是您能获得的服务：';
				   $data[0]['url']='http://www.sylai.com';

                   $data[1]['title']='订单管理';
				   $data[1]['url']='http://www.sylai.com';


                   $data[2]['title']='物流跟踪';
				   $data[2]['url']='http://www.sylai.com';
				   
				   $data[3]['title']='产品服务支持';
				   $data[3]['url']='http://www.sylai.com';

				   $data[4]['title']='产品培训';
				   $data[4]['url']='http://www.sylai.com';

				   $data[5]['title']='活动促销';
				   $data[5]['url']='http://www.sylai.com';

				   $data[6]['title']='更新我的资料';
				   $data[6]['url']='http://www.sylai.com';
				   
				   $resultStr =  tpl($this->fromUsername,$this->toUsername,$data,'news',0,time());
				   return $resultStr;
	   
   }
*/	
}

//对内推送号扩展类
class wechat_push_class extends wechatCallbackapiTest2{
	


	
}

?>