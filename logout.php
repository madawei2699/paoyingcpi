<?php
include_once('./common.php');
if($_SGLOBAL['login']==true){
       $_SGLOBAL['login']==false;
       clearcookie();
}//end if
gourl('login.php');
exit();
?>