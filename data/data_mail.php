<?php
if(!defined('IN_SYS')) exit('Access Denied');
$_SGLOBAL['mail']=Array
	(
	'mailsend' => 2,
	'maildelimiter' => '0',
	'mailusername' => 1,
	'server' => 'smtp.qq.com',
	'port' => '25',
	'auth' => '1',
	'from' => $_SC['notification_email'].' '.$_SC['site_name'],
	'auth_username' => $_SC['notification_email'],
	'auth_password' => $_SC['notification_email_pwd']
	)
?>