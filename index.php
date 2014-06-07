<?php
include_once('./common.php');
$h=getstr($_GET['r']);
$hash=substr($h,0,7);
if($hash!=''){
gourl('r.php?h='.$hash);
exit();
}


if($_SGLOBAL['login']==true){
 gourl('user.php');	
 exit;
}
$smarty->display('index.dwt');

?>