<?php
include_once('./common.php');

$ur_here = '<a href=".">首页</a>';
$ur_here .=' > 创始团队';
$smarty->assign('ur_here',$ur_here);  // 当前位置
$smarty->display('team.dwt');
?>