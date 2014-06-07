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
							    <?php if ($this->_var['parent_id'] > 0): ?>
								  <h3 class="frm-t">添加自定义菜单子按钮</h3>
								<?php else: ?>
								  <h3 class="frm-t">添加自定义菜单按钮</h3>
								<?php endif; ?>
								<p class="frm-tip"> </p>
								<p></p>
							</div>
							<div class="frm-nav">
								<div id="regKindBody">
									<div class="frm-bd mp-reg-person">
										<div class="frm-section">
											<div class="section-bd">
											
											
												<div class="group frm-control-group extra" id="sort_order_group">
													<label select="option" class="frm-control-label" for="">显示顺序</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="sort_order" name="sort_order" value="0">
														<span id="sort_order_notice" class="desc">&nbsp;</span> </div>
												</div>
												<div class="group frm-control-group extra" id="name_group">
													<label select="option" class="frm-control-label" for="">按钮名称</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="btn_name" name="btn_name" value="">
														<span id="btn_name_notice" class="desc">&nbsp;</span> </div>
												</div>

												<div class="group frm-control-group extra" id="btn_type_group">
													<label select="option" class="frm-control-label" for="">按钮类型</label>
													<div class="frm-controls">
														<select class="frm-controlM"  id="btn_type" name="btn_type">
														   <option value="1" selected>触发关键词</option>
														   <option value="2">跳转网址</option>
														</select>
														<span id="btn_type_notice" class="desc">&nbsp;</span> </div>
												</div>


												<div class="group frm-control-group extra" id="keyword_group">
													<label select="option" class="frm-control-label" for="">关键词</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="keyword" name="keyword" value="">
														<span id="keyword_notice" class="desc">&nbsp;</span> </div>
												</div>

												<div class="group frm-control-group extra" id="url_group">
													<label select="option" class="frm-control-label" for="">跳转网址</label>
													<div class="frm-controls">
														<input type="text" class="frm-controlM" placeholder="" id="url" name="url" value="">
														<span id="url_notice" class="desc">&nbsp;</span> </div>
												</div>
												
																								
											</div>
										</div>
									</div>
								</div>
								<div class="frm-ft">
									<div class="frm-opr"> <a class="btnGreen" id="form-submit" href="javascript:;">提交</a>
										<input type="hidden" name="wxid" value="<?php echo $this->_var['account']['id']; ?>" />
										<input type="hidden" name="parent_id" value="<?php echo $this->_var['parent_id']; ?>" />
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
            
            <?php echo $this->fetch('lib/wx_account_manage_sidebar.lbi'); ?>
            
		<div class="clr"></div>
	</div>
</div>


<div style="height:100px"></div>


<?php echo $this->fetch('lib/page_footer.lbi'); ?>

<script>
$('#form-submit').click(function(){
if(check_submit()){
$('#form-addprofile').attr('method','post');
$('#form-addprofile').attr('action','wx_custommenu.php');
$('#form-addprofile').submit();
}
});

function check_submit()
{
    var submit_disabled = false;

	if(!check_btn_name()){
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

function check_btn_name(){
  btn_name=$('#btn_name').val();
  if ( btn_name.length < 1)
  {
        $('#btn_name_notice').html('按钮名称不能少于1个字');
		$('#btn_name').focus();
		return false;
  }else if(btn_name.length>20 )
  {
        $('#btn_name_notice').html('按钮名称不能多于20个字');
		$('#btn_name').focus();
		return false;
  }
  else
  {
      $('#btn_name_notice').html('OK');
      return true;
  }
}

</script>
</body>
</html>