<?php
include_once('./common.php');
$backurl=empty($_POST['backurl'])?'user.php':$_POST['backurl'];
if($_SGLOBAL['login']==false){
gourl($backurl);
exit();
}

$type   = !empty($_REQUEST['type'])   ? intval($_REQUEST['type'])   : 0;
$parent = !empty($_REQUEST['parent']) ? intval($_REQUEST['parent']) : 0;

$arr['regions'] = get_regions($type, $parent);
$arr['type']    = $type;
$arr['target']  = !empty($_REQUEST['target']) ? stripslashes(trim($_REQUEST['target'])) : '';
$arr['target']  = htmlspecialchars($arr['target']);

echo json_encode($arr);


/**
 * 获得指定国家的所有省份
 *
 * @access      public
 * @param       int     country    国家的编号
 * @return      array
 */
function get_regions($type = 0, $parent = 0)
{
	global $_SGLOBAL;	
    $sql = "SELECT region_id, region_name FROM " . tname('region') ." WHERE region_type = '".$type."' AND parent_id = '".$parent."'";
    return $_SGLOBAL['db']->getall($sql);
}
?>