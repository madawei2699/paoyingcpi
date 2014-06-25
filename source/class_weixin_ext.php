<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}


//微笑微信公众号扩展类
class wechat_main_class extends wechatCallbackapiTest{
	//默认的获取自定义菜单点击函数，返回输出结果
	protected function get_eventkey($eventkey){
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
		                  $resultStr =  $this->tpl1($data,'news',0,time(),3);
		                  return $resultStr;
	            }
	            if($autoreply['autoreply_type_id']==3){
		                 $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id='.$autoreply['id'].' and autoreply_type_id=3 order by sort_order');
						 if($data[0]['pic']=='') $data[0]['pic']=$_SC['site_host'].'/themes/pc/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
		                 $resultStr = $this->tpl1($data,'news',0,time(),3);
		                 return $resultStr;
	            }
			}else{
	           return $this->msg_autoback();
			}
			
		}
	}

	//消息模版
	protected function tpl1($data,$type = 'news',$flg = 0,$time,$tp=1){
	   global $_SC;
	   $fu=$this->fromUsername;
	   $tu=$this->toUsername;
	   if($type == 'news'){
	         $num  = count($data);  //统计数量
	         if($num > 1){  //返回多条
	           $add = $this->news_add1($data,$tp);
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
	           <Url><![CDATA[".$data[0]['content']."]]></Url>
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
	protected function news_add1($data,$tp){
		global $_SC;
	    $add = "";
	    foreach ($data as $k=>$v){
	    
		$add .= "<item>
	      <Title><![CDATA[".$v['title']."]]></Title>
	      <Description><![CDATA[".$v['summary']."]]></Description>
	      <PicUrl><![CDATA[".$v['pic']."]]></PicUrl>
	      <Url><![CDATA[".$data[$k]['content']."]]></Url>
	      </item>  ";
	    }
	    return $add;
	}


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