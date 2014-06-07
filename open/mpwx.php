<?php
include_once('../common.php');
include_once(S_ROOT.'./module/wx.php');

//define your token
define("TOKEN", $_SC['api_token']);
$wx = new WX();
$wx->run();
?>