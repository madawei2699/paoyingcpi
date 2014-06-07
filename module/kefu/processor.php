<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}

class Kefu_Module_Processor extends WX_Module_Processor {
	public function respond() {
		global $_SGLOBAL,$wx,$_SC;
		 $op_wxid=$wx->weixin['op_wxid'];
 	     $op_uid=$wx->weixin['op_uid'];
		 $msg=getstr(trim($wx->message['content']));
         $msg=$this->SBC_DBC($msg,1);
		 if(strpos($msg,'#')){
			$reply_state=$this->send_reply($msg);
			if($reply_state<0){
                $result = $this->resp_text($reply_state);//"回复失败，请确定您拥有客服权限");
			}elseif($reply_state==2){
                $result = $this->resp_text("回复失败，您的同事已经回复了本问题:)");
			}elseif($reply_state==1){
                $result = $this->resp_text("您已经成功回复了用户的问题。");
			}
			return $result; 
		 }
		 
		 //客服认证
		 if(mb_substr($msg,0,5,'utf-8')=='客服绑定@'){
			$regmsg=mb_substr($msg,5,mb_strlen($msg)-5,'utf-8');
			$uid=$this->kefu_reg($regmsg);
			if($uid>0){
                $content = "您的微信已经成功绑定，您将可以用微信收取和回复咨询。";
                return $this->resp_text($content);
			}else{
				if($uid==-1){
                  $content = "你已经绑定！";
				}else{
                  $content = "绑定失败，请输入格式: 客服绑定@手机号码@绑定码 来绑定您的帐号,例如: ".chr(10)."客服注册@1350000000@abcdef ";
				}
				return $this->resp_text($content);
			}
		 }
		 
		 return false;		 
    }

    protected function kefu_reg($msg){
	  global $_SGLOBAL,$wx,$_SC;
 	  $op_wxid=$wx->weixin['op_wxid'];
 	  $op_uid=$wx->weixin['op_uid'];
	  $create_time=$wx->message['time'];
	  $wxid=$wx->message['from'];
	  list($mobile,$weixin_code)=explode('@',$msg,2);
	  $mobile=getstr($mobile);
	  $weixin_code=getstr($weixin_code);
	  $kefu=$_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query('select * from '.tname('open_member_user').' where op_uid="'.$op_uid.'" and state=1 and mobile="'.$mobile.'" and weixin_code="'.$weixin_code.'"'));
	  if(!$kefu){
		   $uid=0;
	  }elseif($kefu['weixin_state']==1){
		   $uid=-1;
	  }else{
	       $uid=$kefu['uid'];
	  }
	  if($uid>0){
	      $query=$_SGLOBAL['db']->query('select uid,fakeid,province,nickname from '.tname('weixin_member').' where op_wxid='.$op_wxid.' and wxid="'.$wxid.'"');
	      $member=$_SGLOBAL['db']->fetch_array($query);
		  if(!$member){
            $ro = new WX_Remote_Opera();
            $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id='".$op_wxid."'");
            if($op_wx=$_SGLOBAL['db']->fetch_array($query)){
               $ro->init($op_wx['username'],$op_wx['password']);
		    }
		    $msglist=$ro->getmsglist();
		    foreach($msglist as $k=>$v){
			    if($v['date_time']==$create_time){
				   updatetable(tname('open_member_user'),array('weixin_state'=>1,'weixin_fakeid'=>$v['fakeid']),array('uid'=>$uid,'op_uid'=>$op_uid));					
				   break;					
			    }
		    }
		  }else{
			updatetable(tname('open_member_user'),array('weixin_state'=>1,'weixin_fakeid'=>$member['fakeid']),array('uid'=>$uid,'op_uid'=>$op_uid));					
		  }
	  }
	  return $uid;
	}



    //推送端检查成员资料，如果是绑定的成员，通过推送端回复用户的提问
    protected function send_reply($msg){
	  global $_SGLOBAL,$wx,$_SC;
 	  $op_wxid=$wx->weixin['op_wxid'];
 	  $op_uid=$wx->weixin['op_uid'];
	  $create_time=$wx->message['time'];
	  $wxid=$wx->message['from'];
	  $fakeid=$_SGLOBAL['db']->getone('select fakeid from '.tname('weixin_member').' where wxid="'.$wxid.'" limit 1');
	  list($question_id,$content)=explode('#',$msg,2);
	  $uid=$_SGLOBAL['db']->getone('select uid from '.tname('open_member_user').' where op_uid="'.$op_uid.'" and weixin_state=1 and weixin_fakeid="'.$fakeid.'"');
      if($uid>0){
         $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_user')." where op_uid='".$op_uid."' and uid=".$uid);
         $member=$_SGLOBAL['db']->fetch_array($query);
         $asker_uid=$_SGLOBAL['db']->getone('select uid from '.tname('weixin_question').' where id="'.$question_id.'"');
		 $query=$_SGLOBAL['db']->query("select * from ".tname('open_member_weixin')." where id='".$op_wxid."' and op_uid='".$op_uid."'");
		 if($op_wx=$_SGLOBAL['db']->fetch_array($query)){
		 
		   $asker_fakeid=$_SGLOBAL['db']->getone('select fakeid from '.tname('weixin_member').' where uid="'.$asker_uid.'"');
		   if($asker_fakeid){
			 $replyed=$_SGLOBAL['db']->getone('select replyed from '.tname('weixin_question').' where id="'.$question_id.'"');
			 if($replyed==0){
	           $reply_id=inserttable(tname('weixin_reply'),array('uid'=>$uid,'question_id'=>$question_id,'content'=>$content,'addtime'=>$create_time),1);
			   updatetable(tname('weixin_question'),array('replyed'=>1),array('id'=>$question_id));
               $ro = new WX_Remote_Opera();
               $token=$ro->init($op_wx['username'],$op_wx['password']);
	           $replymsg=$this->reply_tpl($member,$reply_id,$content);     			                
		       $ro->sendmsg($replymsg,$asker_fakeid);
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
	  $replymsg=$content;     			                
	  return $replymsg;   
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
