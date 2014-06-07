<?php
if(!defined('IN_SYS')) {
	exit('Access Denied');
}

$_SGLOBAL['navmenu'] = array(
 array('title'=>'公众号管理','url'=>'wx_account.php'),
 array('title'=>'消息管理','url'=>'wx_message.php'),
 array('title'=>'客服管理','url'=>'member.php'),
 array('title'=>'账号管理','url'=>'profile.php?ac=edit'),
);



?>