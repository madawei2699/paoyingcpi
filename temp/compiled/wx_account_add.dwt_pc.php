<!DOCTYPE html>
<html>
<head>
<meta name="Generator" content="APPNAME VERSION" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['_SC']['site_name']; ?></title>
<link href="<?php echo $this->_var['template_path']; ?>/css/common.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/form.css" rel="stylesheet" type="text/css">
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery-1.8.2.min.js" type="text/javascript"></script>
<script src="<?php echo $this->_var['template_path']; ?>/script/utils.min.js" type="text/javascript"></script>
</head>
<body>

<?php echo $this->fetch('lib/page_header.lbi'); ?>
 


<div class="container" id="main">
	<div class="containerBox">
		<div class="boxHeader">
			<h2>&nbsp;&nbsp;</h2>
		</div>
		<div class="content">
			<div class="newTips"> <a href=""> <span id="newMsgNum">0</span>条新消息，点击查看 </a> </div>
			<div class="containerBox boxIndex">
				<div class="rn-box check-box" style="display: block;">
					<form id="form-addprofile">
						<div class="frm">
							<div class="frm-hd">
								<h3 class="frm-t">绑定您的公众号</h3>
								<p class="frm-tip"> </p>
								<p></p>
							</div>
							<div class="frm-nav">
								<div id="regKindBody">
									<div class="frm-bd mp-reg-person">
										<div class="frm-section">
											<div class="section-bd">
												<div class="group frm-control-group extra" id="username_group">
													<label select="option" class="frm-control-label" for="">微信号</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="username" name="username" value="">
														<span id="username_notice" class="desc">英文字符串，如果还没有请前往微信公众号后台设置</span> </div>
												</div>
												<hr>
												<div class="group frm-control-group extra" id="pass1_group">
													<label select="option" class="frm-control-label" for="">密码</label>
													<div class="frm-controls">
														<input type="password" class="frm-controlM" placeholder="" id="password" name="password" value="">
														<span id="password_notice" class="desc">微信公众号登录密码</span> </div>
												</div>
												<hr>
												<div class="group frm-control-group extra" id="appid_group">
													<label select="option" class="frm-control-label" for="">appid(可选)</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="appid" name="appid" value="">
														<span id="appid_notice" class="desc">用于自定义菜单，只有服务号而且开启后才可填</span> </div>
												</div>
												<div class="group frm-control-group extra" id="appsecret_group">
													<label select="option" class="frm-control-label" for="">appsecret(可选)</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="appsecret" name="appsecret" value="">
														<span id="appsecret_notice" class="desc">用于自定义菜单，只有服务号而且开启后才可填</span> </div>
												</div>
												
												
												
											</div>
										</div>
									</div>
								</div>
								<div class="frm-ft">
									<div class="frm-opr"> <a class="btnGreen" id="form-submit" href="javascript:;">绑定</a>
										<input type="hidden" name="backurl" value="<?php echo $this->_var['backurl']; ?>" />
										<input type="hidden" name="_submit" id="_submit" value="submit" />
										<input type="hidden" name="formhash" value="<?php echo $this->_var['formhash']; ?>" />
										<input type="hidden" name="ac" value="addprofile" />
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="sideBar">
			<div class="catalogList">
				<ul class="shaixuan">
					<li class=""> <a href="wx_account.php">公众号管理</a> </li>
					<li class="selected"> <a href="wx_account.php?ac=add">添加公众号</a> </li>
				</ul>
				
			</div>
		</div>
		<div class="clr"></div>
	</div>
</div>


<div style="height:100px"></div>


<?php echo $this->fetch('lib/page_footer.lbi'); ?>

<script>
$('#form-submit').click(function(){
if(check_submit()){
$('#form-addprofile').attr('method','post');
$('#form-addprofile').attr('action','wx_account.php');
$('#form-addprofile').submit();
}
});

function check_submit()
{
    var submit_disabled = false;


	if(!check_username()){
	  submit_disabled = true;
	}

	if(!check_password()){
	  submit_disabled = true;
	}
	
    if ( submit_disabled )
    {
        return false;
    }
	else{
        return true;
	}	
}

function check_weixin_name(){
  weixin_name=$('#weixin_name').val();
  if ( weixin_name.length < 2)
  {
        $('#weixin_name_notice').html('微信名称不能少于2个字');
		$('#weixin_name').focus();
		return false;
  }else if(weixin_name.length>20 )
  {
        $('#weixin_name_notice').html('微信名称不能多于20个字');
		$('#weixin_name').focus();
		return false;
  }
  else
  {
      $('#weixin_name_notice').html('OK');
      return true;
  }
}

function check_username(){
  username=$('#username').val();
  if ( username.length < 2)
  {
        $('#username_notice').html('微信号不能少于2个字');
		$('#username').focus();
		return false;
  }else if(username.length>20 )
  {
        $('#username_notice').html('微信号不能多于20个字');
		$('#username').focus();
		return false;
  }
  else
  {
      $('#username_notice').html('OK');
      return true;
  }
}


function check_ghid(){
  ghid=$('#ghid').val();
  if ( ghid.length < 2)
  {
        $('#ghid_notice').html('GH号不能少于2个字');
		$('#ghid').focus();
		return false;
  }else if(ghid.length>30 )
  {
        $('#ghid_notice').html('GH号不能多于30个字');
		$('#ghid').focus();
		return false;
  }
  else
  {
      $('#ghid_notice').html('OK');
      return true;
  }
}

function check_password(){
  password=$('#password').val();
  if ( password.length>0 && password.length < 2)
  {
        $('#password_notice').html('密码不能少于2个字');
		$('#password').focus();
		return false;
  }else if(password.length>30 )
  {
        $('#password_notice').html('密码不能多于30个字');
		$('#password').focus();
		return false;
  }
  else
  {
      $('#password_notice').html('OK');
      return true;
  }
}



</script>
</body>
</html>