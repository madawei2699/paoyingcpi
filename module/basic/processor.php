<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}

class Basic_Module_Processor extends WX_Module_Processor {
	public function respond() {
		global $_SGLOBAL,$wx;	
		$result=$this->get_keyword($wx->message['content']);	 
        if($result){
		  return $result;
		}else{
		  $default = '您的消息我们已经收到';
		  return $this->resp_text($default);
		}
	}


	//默认的获取关键词函数，返回输出结果
	protected function get_keyword($keyword){
		global $wx;
		if(!$keyword){
		  return false;	
		}
				
		$msg=getstr(trim($keyword));
        $msg=$this->SBC_DBC($msg,1);
		
		
		$result=$this->keyword_autoback($msg);
		if($result){
			return $result;			
		}
		
		if($wx->message['msgid']){
		  $member_num=$this->ck_member_wx($msg);
		}
		return $this->msg_autoback();
	}

		
	protected function keyword_autoback($keyword=''){
	               global $_SGLOBAL,$_SC,$wx;
				   $op_wxid=$wx->weixin['op_wxid'];
				   $from_user=$wx->message['from'];
				   $resultStr='';
				   if($keyword==''){
					 return false;   
				   }
				   //销魂宝二期风险测评
				   if($keyword=='m_xhbeq'){
				   		$survey_url="http://jferic.com/weixin/survey2.php?from_user='$from_user'";
                        $record=array(
                                'title' =>'跑赢CPI[销魂宝二期]风险测评',
                                'description' =>'请根据个人真实情况回答。通过测试是成为销魂宝会员的必要条件。',
                                'picUrl' => 'http://jferic.com/weixin/img/survey2.jpg',
                                'content' =>$survey_url
                        );
                        $resultStr = $this->resp_news($record);
                        return $resultStr;
				   }
				   
				   $autoreply_list=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply').' where state=1 and op_wxid="'.$op_wxid.'" and type="keyword"');
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
				      return false;
					}else{
						$query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply').' where id="'.$autoreply_id.'" and state=1');
						if($autoreply=$_SGLOBAL['db']->fetch_array($query)){
							if($autoreply['reply_type']=='text'){
					               $content=htmlspecialchars_decode($autoreply['content']);
   			   					   $content=db_to_content(htmlspecialchars_decode($content));
                	               $resultStr = $this->resp_text($content);
					             return $resultStr;
							}


							$rand_pic=array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');
							
				            if($autoreply['reply_type']=='single_news'){
					                  $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id="'.$autoreply['id'].'" and state=1');
									  $data[0]['picurl']=$data[0]['pic'];
									  if($data[0]['picurl']=='') $data[0]['picurl']=$_SC['img_url'].'/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
									  $data[0]['description']=htmlspecialchars_decode($data[0]['summary']);                            
					                  if($data[0]['url']=='') $data[0]['url']=$_SC['site_host']."/appmsg/?id=".$data[0]['id']."&tp=3";
									  
									  $resultStr =  $this->resp_news($data);
					                  return $resultStr;
				            }
				            if($autoreply['reply_type']=='multi_news'){
					                 $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id='.$autoreply['id'].' and state=1 order by sort_order limit 0,8');
									 foreach($data as $k=>$v){
											$data[$k]['picurl']=$data[$k]['pic'];
					                        if($data[$k]['url']=='') $data[$k]['url']=$_SC['site_host']."/appmsg/?id=".$data[$k]['id']."&tp=3";
									        $data[$k]['description']=htmlspecialchars_decode($data[$k]['summary']);                            
									 }
									 if($data[0]['picurl']=='') $data[0]['picurl']=$_SC['img_url'].'/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
				
					                 $resultStr =  $this->resp_news($data);
					                 return $resultStr;
				            }
						}else{
				           return false;
						}
						
					}
				    return false;	
		
	}
	
    protected function msg_autoback(){
		global $_SGLOBAL,$_SC,$wx;
		$op_wxid=$wx->weixin['op_wxid'];
		
                   $rand_pic=array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');
				   
				   $query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply').' where type="aftermsg" and op_wxid="'.$op_wxid.'" and state=1 order by priority desc'); 
				   if($msg=$_SGLOBAL['db']->fetch_array($query)){
					 switch($msg['reply_type']){
						 case "text":
					       $content=db_to_content(htmlspecialchars_decode($msg['content']));
                	       $resultStr = $this->resp_text($content);
					       return $resultStr;
						 break;
						 case "single_news":
					       $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id="'.$msg['id'].'" and state=1');  
						   if($data){
					        if($data[0]['url']=='') $data[0]['url']=$_SC['site_host']."/appmsg/?id=".$data[0]['id']."&tp=1";
				            $data[0]['picurl']=$data[0]['pic'];
					        if($data[0]['picurl']=='') $data[0]['picurl']=$_SC['img_url'].'/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					        $data[0]['description']=htmlspecialchars_decode($data[0]['summary']);                            
					        $resultStr =  $this->resp_news($data);
					        return $resultStr;
						   }
						 break;
						 case "multi_news":
					        $data=$_SGLOBAL['db']->getall('select * from '.tname('open_member_weixin_autoreply_info').' where autoreply_id="'.$msg['id'].'" and state=1 order by sort_order limit 0,8');
					        foreach($data as $k=>$v){
					           if($data[$k]['url']=='') $data[$k]['url']=$_SC['site_host']."/appmsg/?id=".$data[$k]['id']."&tp=1";
						       $data[$k]['picurl']=$data[$k]['pic'];
						       $data[$k]['description']=htmlspecialchars_decode($data[$k]['summary']);                            
					        }
					        if($data[0]['picurl']=='') $data[0]['picurl']=$_SC['img_url'].'/mpres/wallpaper/'.$rand_pic[array_rand($rand_pic,1)].'.jpg';
					        $resultStr =  $this->resp_news($data);
					        return $resultStr;
						 break;
					 }					   					   
				   }
				   return false;
		
	}


    //记录消息来源的用户资料,返回微笑微信中用户信息数组,包含[uid,province,nickname]
    protected function save_weixin_member(){
	  global $_SGLOBAL,$wx;
	  $create_time=$wx->message['time'];
	  $wxid=$wx->message['from'];
	  $op_wxid=$wx->weixin['op_wxid'];
	  if($wxid=='') return false;
	  $return=false;
	  $query=$_SGLOBAL['db']->query('select uid,fakeid,province,nickname from '.tname('weixin_member').' where op_wxid='.$op_wxid.' and wxid="'.$wxid.'"');
	  $member=$_SGLOBAL['db']->fetch_array($query);
      $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id='".$op_wxid."'");
      if($op_wx=$_SGLOBAL['db']->fetch_array($query)){
         $ro = new WX_Remote_Opera();
         $token=$ro->init($op_wx['username'],$op_wx['password']);
	     if(!$member){
		    $msglist=$ro->getmsglist();
		    foreach($msglist as $k=>$v){
			    if($v['date_time']==$create_time){
				  $contactinfo=$ro->getcontactinfo($v['fakeid']);
				  $member['uid']=inserttable(tname('weixin_member'),array('op_wxid'=>$op_wxid,'wxid'=>$wxid,'fakeid'=>$v['fakeid'],'nickname'=>$contactinfo['nick_name'],'username'=>$contactinfo['user_name'],'country'=>$contactinfo['country'],'province'=>$contactinfo['province'],'city'=>$contactinfo['city'],'sex'=>$contactinfo['gender'],'create_time'=>$create_time),1);
				  $member['fakeid']=$v['fakeid'];
				  $member['province']=$contactinfo['province'];
				  $member['nickname']=$contactinfo['nick_name'];
		          //保存头像
                  $ro->getheadimg($member['fakeid']);
                  break;
			    }
		    }
	     }
		 return $member;
	 }else{
		 return false;
		  
	 }
   }


    //绑定公众号，接收用户的提问
    protected function ck_member_wx($msg){
	  global $_SGLOBAL,$wx;
	  $create_time=$wx->message['time'];
	  $wxid=$wx->message['from'];
	  $op_wxid=$wx->weixin['op_wxid'];
	  if($wxid=='') return false;
	  $return=false;
	  $member=$this->save_weixin_member();  //匹配消息，获取微笑微信内的用户信息
	  //将消息发给谁 
	  $to_uid=0;		  
	  $q_arr['to_uid']=$to_uid;		 
	  $content=$msg;
	  $q_arr['uid']=$member['uid'];
	  $q_arr['content']=$content;
	  $q_arr['province']=$member['province'];
	  $q_arr['addtime']=$create_time;
	  $id=inserttable(tname('weixin_question'),$q_arr,1);		
	  $return=$this->send_to_member($content,$id,$member['province'],$member['nickname'],$to_uid);		  
	  return $return;
    }




    //将接收的微信提问，通过推送号发送给成员微信号
    protected function send_to_member($msg,$question_id='',$province='',$nickname='',$to_uid=0,$op_wx=array()){
	  global $_SGLOBAL,$wx;
	  $return=false;
	  $op_wxid=$wx->weixin['op_wxid'];
	  $op_uid=$wx->weixin['op_uid'];
	  

	  $count=0;
	  $newmsg=$this->question_tpl($msg,$question_id,$province,$nickname);
	  $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id='".$op_wxid."'");
      $op_wx=$_SGLOBAL['db']->fetch_array($query);
	  $ro = new WX_Remote_Opera();
      $ro->init($op_wx['username'],$op_wx['password']);
      $memberlist=$_SGLOBAL['db']->getall('select * from '.tname('open_member_user').' where weixin_state=1 and op_uid="'.$op_uid.'"');
	  foreach($memberlist as $k=>$v){
		   $ro->sendmsg($newmsg,$v['weixin_fakeid']);
		   $count++; 
	  }
	  $return=$count;
	  return $return;	
    }

   //问题上下文模板
   protected function question_tpl($msg,$question_id='',$province='',$nickname='',$op_wx=array()){
	 global $_SGLOBAL,$wx;
 	  $op_wxid=$wx->weixin['op_wxid'];
	  $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id=".$op_wxid);
      $op_wx=$_SGLOBAL['db']->fetch_array($query);
	 $newmsg='['.$op_wx['weixin_name'].']来自'.$province.'的'.$nickname.'提问： '.chr(10).$msg.chr(10).chr(10).'  (回复格式: '.$question_id.'#内容)';	  
     return $newmsg; 
   }	


/**
 *  将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
 *
 * @access  public
 * @param   string　$str　待转换字串
 * @return  string
 */
  private function SBC_DBC($str, $args2) {
    $DBC = Array(
        '０' , '１' , '２' , '３' , '４' ,
        '５' , '６' , '７' , '８' , '９' ,
        'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,
        'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,
        'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,
        'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,
        'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,
        'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,
        'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,
        'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,
        'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,
        'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,
        'ｙ' , 'ｚ' , '－' , '　' , '：' ,
        '．' , '，' , '／' , '％' , '＃' ,
        '！' , '＠' , '＆' , '（' , '）' ,
        '＜' , '＞' , '＂' , '＇' , '？' ,
        '［' , '］' , '｛' , '｝' , '＼' ,
        '｜' , '＋' , '＝' , '＿' , '＾' ,
        '￥' , '￣' , '｀'
    );

    $SBC = Array( // 半角
        '0', '1', '2', '3', '4',
        '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E',
        'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T',
        'U', 'V', 'W', 'X', 'Y',
        'Z', 'a', 'b', 'c', 'd',
        'e', 'f', 'g', 'h', 'i',
        'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's',
        't', 'u', 'v', 'w', 'x',
        'y', 'z', '-', ' ', ':',
        '.', ',', '/', '%', '#',
        '!', '@', '&', '(', ')',
        '<', '>', '"', '\'','?',
        '[', ']', '{', '}', '\\',
        '|', '+', '=', '_', '^',
        '$', '~', '`'
    );

    if ($args2 == 0) {
        return str_replace($SBC, $DBC, $str);  // 半角到全角
    } else if ($args2 == 1) {
        return str_replace($DBC, $SBC, $str);  // 全角到半角
    } else {
        return false;
    }
  }
	
}
