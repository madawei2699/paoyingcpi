<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}
//根据模块序列依次判断加载模块一直到有返回为止，都没返回则加载basic模块
class Default_Module_Processor extends WX_Module_Processor {
	public function respond() {
		global $_SGLOBAL,$wx;
		

		$level = array();
		if (!empty($wx->weixin['account']['modules'])) {
			foreach ($wx->weixin['account']['modules'] as $row) {
				if (!empty($row['displayorder'])) {
					$level[$row['displayorder']] = $row;
				}
			}
		}
		

		if (!empty($level)) {
			$response = '';
			foreach($level as $k=>$v) {
				if (!empty($response)) {
					$wx->response['module'] = $wx->weixin['module'];
					return $response;
					break;
				}
				if (empty($level[$k])) {
					continue;
				}
				$wx->weixin['module'] = $level[$k]['name'];
				$processor = WX_Utility::create_module_processor($wx->weixin['module']);
				$processor->message = $wx->message;
				$processor->module = $wx->weixin['account']['modules'][$wx->weixin['module']];
				$response = $processor->respond();
			}

		}
		
		
		if(!$response){
				$wx->weixin['module'] = 'basic';
				$processor = WX_Utility::create_module_processor($wx->weixin['module']);
				$processor->message = $wx->message;
				$processor->module = $wx->weixin['account']['modules'][$wx->weixin['module']];
				$response = $processor->respond();			
		}
		
		return $response;

	}
	
}
