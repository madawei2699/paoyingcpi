<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}

class Welcome_Module_Processor extends WX_Module_Processor {
	public function respond() {
		global $wx;
		
		$result=$this->focus_autoback();	 
        if($result){
		  return $result;
		}else{		
		  $default = '欢迎关注';
		  return $this->resp_text($default);		
		}
	}
	
	
   protected function focus_autoback(){
		global $_SGLOBAL,$_SC,$wx;
		$op_wxid=$wx->weixin['op_wxid'];
		
                   $rand_pic=array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28');
				   
				   $query=$_SGLOBAL['db']->query('select * from '.tname('open_member_weixin_autoreply').' where type="focus" and op_wxid="'.$op_wxid.'" and state=1 order by priority desc'); 
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
	
}
