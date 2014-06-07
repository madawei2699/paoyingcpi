<?php
include_once('./common.php');

/**
 * 生成验证码
*/
include_once('./source/cls_captcha.php');
$img = new captcha(S_ROOT.'data/captcha/','100','20');
@ob_end_clean(); //清除之前出现的多余输入
if (isset($_REQUEST['is_login']))
{
    $img->session_word = 'captcha_login';
}
$img->generate_image();

?>