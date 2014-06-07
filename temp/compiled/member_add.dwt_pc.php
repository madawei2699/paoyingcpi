<!DOCTYPE html>
<html>
<head>
<meta name="Generator" content="APPNAME VERSION" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['_SC']['site_name']; ?></title>
<link href="<?php echo $this->_var['template_path']; ?>/css/common.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/form.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/jquery_cbox.css" rel="stylesheet" type="text/css">
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery-1.8.2.min.js" type="text/javascript"></script>
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery.upload.js" type="text/javascript"></script>
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
					<form id="form-addmember">
						<div class="frm">
							<div class="frm-hd">
								<h3 class="frm-t">为你的公众号添加客服</h3>
								<p class="frm-tip"> </p>
								<p></p>
							</div>
							<div class="frm-nav">
								<div id="regKindBody">
									<div class="frm-bd mp-reg-person">
										<div class="frm-section">
											<div class="section-bd">
											
											
											
												<div class="group frm-control-group extra" id="mobile_group">
													<label select="option" class="frm-control-label" for="">手机号</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="mobile" name="mobile" value="">
														<span id="mobile_notice" class="desc">用户绑定推送号</span> </div>
												</div>
												<div class="group frm-control-group extra" id="name_group">
													<label select="option" class="frm-control-label" for="">姓名</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="fullname" name="fullname" value="">
														<span id="fullname_notice" class="desc">如果名字包含分隔号“·”，请勿省略。</span> </div>
												</div>

												<div class="group frm-control-group extra" id="email_group">
													<label select="option" class="frm-control-label" for="">邮箱</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="email" name="email" value="">
														<span id="email_notice" class="desc">用于接收绑定码</span> </div>
												</div>

												<div class="group frm-control-group extra" id="password_group">
													<label select="option" class="frm-control-label" for="">子系统密码</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="password" name="password" value="">
														<span id="password_notice" class="desc">用于扩展功能，可以不填</span> </div>
												</div>																								
												
											</div>
										</div>
									</div>
								</div>
								<div class="frm-ft">
									<div class="frm-opr"> <a class="btnGreen" id="form-submit" href="javascript:;">保存</a>
										<input type="hidden" name="backurl" value="<?php echo $this->_var['backurl']; ?>" />
										<input type="hidden" name="_submit" id="_submit" value="submit" />
										<input type="hidden" name="formhash" value="<?php echo $this->_var['formhash']; ?>" />
										<input type="hidden" name="ac" value="addmember" />
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
					<li class=""> <a href="member.php">客服管理</a> </li>
					<li class="selected"> <a href="member.php?ac=add">添加客服</a> </li>
				</ul>
			</div>
		</div>
		<div class="clr"></div>
	</div>
</div>
 


<?php echo $this->fetch('lib/page_footer.lbi'); ?>
 

<script>
$('#form-submit').click(function(){
if(check_submit()){
$('#form-addmember').attr('method','post');
$('#form-addmember').attr('action','member.php');
$('#form-addmember').submit();
}
});

function check_submit()
{
    var submit_disabled = false;

	if(!check_email()){
	  submit_disabled = true;
	}


	if(!check_fullname()){
	  submit_disabled = true;
	}


	if(!check_mobile()){
	  submit_disabled = true;
	}

	if(!check_pwd()){
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



function check_email(){
  var submit_disabled = false;	
  email=$('#email').val();
  if (email == '')
  {
    $('#email_notice').html("邮箱不能为空");
    submit_disabled = true;
  }
  else if (!Utils.isEmail(email))
  {
    $('#email_notice').html("请填写正确的邮箱");
    submit_disabled = true;
  }else{
  var checkurl = 'member.php';
  $.ajax({
         type:'POST',
	     dataType:'json',
         url: checkurl,
         data:'ac=check_email&email='+email,
         async: false,
         success:function(json){
            if ( json.err == 0 )
            {
                $('#email_notice').html('可以申请');
            }
            else
            {
                $('#email_notice').html('该邮箱已经被注册');
                submit_disabled = true;
            }
  }}); 	  
  }

    if ( submit_disabled )
    {
        return false;
    }
	else{
        return true;
	}	  	
}

function check_pwd(){
  pass=$('#password').val();
  if(pass.length>0 &&	pass.length<6){
	$('#password_notice').html('密码不能少于6位个字');
    $('#password').focus();
	return false;  
  }else if(pass.length>15){
	$('#password_notice').html('密码不能多于15位个字');
    $('#password').focus();
  }else{
    $('#password_notice').html('OK');
    return true;
  }
	
}



function check_fullname(){
  fullname=$('#fullname').val();
  if ( fullname.length < 2)
  {
        $('#fullname_notice').html('姓名不能少于2个字');
		$('#fullname').focus();
		return false;
  }else if(fullname.length>15 )
  {
        $('#fullname_notice').html('姓名不能多于15个字');
		$('#fullname').focus();
		return false;
  }
  else
  {
      $('#fullname_notice').html('OK');
      return true;
  }
}

function check_mobile(){
  mobile=$('#mobile').val();
  if (mobile == '')
  {
    $('#mobile_notice').html("手机号不能为空");
    $('#mobile').focus();
    return false;
  }
  else if (!Utils.isMobile(mobile))
  {
    $('#mobile_notice').html("请按手机格式填写");
    $('#mobile').focus();
    return false;
  }
  else
  {
      $('#mobile_notice').html('OK');
      return true;
  }
}
</script>
</body>
</html>