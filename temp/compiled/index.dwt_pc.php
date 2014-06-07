<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
<meta name="Generator" content="APPNAME VERSION" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8">
  <link href="<?php echo $this->_var['template_path']; ?>/css/index.css" rel="stylesheet" type="text/css"> 
  <title><?php echo $this->_var['_SC']['site_name']; ?></title>
  <script src="<?php echo $this->_var['template_path']; ?>/script/jquery-1.8.2.min.js" type="text/javascript"></script>
</head>
<body>
<div id="header">
  <div class="wrapper">
    <a href="/" class="dib"><img src="<?php echo $this->_var['template_path']; ?>/images/logo.png"/></a>
    <span class="hd_login_info">
    <span></span><a href="register.php">立即注册</a>&nbsp;&nbsp;
    </span>
  </div>
</div>
<div id="banner">
  <div class="wrapper">
    <div class="login-panel">

      <!--<span class="arrow"></span>-->
      <h3>登录</h3>
      
<div class="login-mod">
  <div class="login-err-panel dn" id="err_area">
    <span class="icon-wrapper"><i class="icon24-login err" style="margin-top:-.2em;*margin-top:0;"></i></span><span id="err_tips"></span>
  </div>

  <form id="login-form">
    <div class="login-form">
    <div class="login-un">
      <span class="icon-wrapper"><i class="icon24-login un"></i></span>
      <input type="text" id="username" name="username" placeholder="用户名或邮箱"/>
    </div>
    <div class="login-pwd">
      <span class="icon-wrapper"><i class="icon24-login pwd"></i></span>
      <input id="password" name="password" type="password" placeholder="密码"/>
    </div>
    </div>

  <div class="login-code-panel" id="verify_area" <?php if ($this->_var['_SC']['captcha'] == false): ?> style="display:none" <?php endif; ?>>
    <input type="text" id="verify" name="captcha"/>
    <span class="icon-wrapper"><img id="img_captcha" src="captcha.php?is_login=1&<?php echo $this->_var['rand']; ?>" alt="captcha" style="vertical-align: middle;cursor: pointer;" onClick="this.src='captcha.php?is_login=1&'+Math.random()" /></span>
    <a class="login-change-code" href="javascript:;" id="changeimg_link" onclick="$('#img_captcha').click();">换一张</a>
  </div>

  <div class="login-help-panel">
    <a  class="login-forget-pwd" href="forget.php">忘记密码？</a>
    <input name="remember" value="1" type="checkbox" checked="checked" />记住帐号
  </div>
            <input type="hidden" name="_submit" id="_submit" value="submit" />
            <input type="hidden" name="formhash" value="<?php echo $this->_var['formhash']; ?>" />
            <input type="hidden" name="backurl" value="<?php echo $this->_var['backurl']; ?>" />
            <input type="hidden" name="ac" value="login" />

  </form>

  <div class="login-btn-panel">
    <a class="login-btn" title="点击登录" href="#" id="login_button">登录</a>
  </div>

  
  
</div>
    </div>
    <dl class="qrcode-panel">
      <dt><img src="<?php echo $this->_var['template_path']; ?>/images/qrcode_for_gh_8b34f94aeb41_430.jpg" width="150px" /></dt>
      <dd style="font-size:12px">
        微信扫描<br/>
       关注我们      </dd>
    </dl>
  </div>
</div>
<div id="contain">
  <div class="wrapper">

  </div>
</div>

<div style="height:50px"></div>

<?php echo $this->fetch('lib/page_footer.lbi'); ?>

<script>
	
$(function(){  

    $('#login_button').click(function(){
        $('#login-form').attr('method','post');
        $('#login-form').attr('action','login.php');
        $('#login-form').submit();
	});
	
       $('#verify')  
         .bind('keyup',function(event) {  
          if(event.keyCode==13){  
                $('#login_button').click();  
          }  
       });
	   
	          $('#password')  
         .bind('keyup',function(event) {  
          if(event.keyCode==13){  
                $('#login_button').click();  
          }  
       });            
}); 	
</script>

</body>
</html>
