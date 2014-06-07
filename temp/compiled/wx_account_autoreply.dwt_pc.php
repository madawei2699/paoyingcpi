<!DOCTYPE html>
<html>
<head>
<meta name="Generator" content="APPNAME VERSION" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['_SC']['site_name']; ?></title>
<link href="<?php echo $this->_var['template_path']; ?>/css/common.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/table.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/jquery_cbox.css" rel="stylesheet" type="text/css">
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery-1.8.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $this->_var['template_path']; ?>/script/utils.min.js"></script>
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery.colorbox-min.js" type="text/javascript"></script>
</head>
<body>


<?php echo $this->fetch('lib/page_header.lbi'); ?>
 


<div class="container-wrapper">
	<div class="container" id="main">
		<div class="containerBox">
			<div class="appTitle normalTitle2">
				<div class="vipuser">
					<div class="logo"> <img width="100" height="100" src="<?php echo $this->_var['account']['headimg']; ?>" onerror="this.src='<?php echo $this->_var['_SC']['siteurl']; ?>themes/pc/mpres/htmledition/images/default_avator.png'"> </div>
					<div id="nickname"> <strong><?php echo $this->_var['account']['weixin_name']; ?></strong></div>
					<div id="weixinid">微信号:<?php echo $this->_var['account']['username']; ?></div>
				</div>
				<div class="clr"></div>
			</div>
			<div class="content">
				<div class="newTips"> <a href=""> <span id="newMsgNum">0</span>条新消息，点击查看 </a> </div>
				<div class="cLine">
					<h3 class="left" id="msg_title"> </h3>
					<div class="searchbar right"> </div>
					<div class="clr"></div>
				</div>
				<div class="cLineB">
					<h3 class="left">自动回复设置  </h3>
					<div class="clr"></div>
				</div>
				<div style="margin:10px 0;" class="float-p"><button class="btnGrayS right" style="margin-right: 0;" id="newRule" onclick="window.location.href='wx_account_autoreply.php?ac=add&id=<?php echo $this->_var['account']['id']; ?>';">添加规则</button><input type="hidden" value="<?php echo $this->_var['account']['id']; ?>" id="op_wxid" /></div>
				<div class="cLine"> 
				<?php if ($this->_var['account']['total'] > 0): ?>
				<div class="cLine">
					<div class="pageNavigator right"> <span> <a class="prePage" href="<?php echo $this->_var['account']['pre_page_url']; ?>"> 上一页 </a> </span> <span class="pageNum"><?php echo $this->_var['account']['page']; ?> / <?php echo $this->_var['account']['pagenum']; ?>(总计:<?php echo $this->_var['account']['total']; ?>)</span> <span> <a class="nextPage" href="<?php echo $this->_var['account']['next_page_url']; ?>"> 下一页 </a> </span> </div>
				</div>

					<div class="msgWrap">
						<table width="100%" cellspacing="0" cellpadding="0" border="0" class="ListProduct">
							<thead>
								<tr>
								    <th>触发类型</th>
									<th>优先级</th>
									<th>关键词</th>
									<th>关键词匹配</th>
									<th>链接</th>
									<th>回复类型</th>
									<th>操作</th>
								</tr>
							</thead>
							<tbody>
								<tr></tr>
							<?php $_from = $this->_var['account']['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'acc');if (count($_from)):
    foreach ($_from AS $this->_var['acc']):
?>
							<tr>
							    <td> <?php echo $this->_var['acc']['type_name']; ?>    </td>
							    <td> <?php echo $this->_var['acc']['priority']; ?></td>
								<td> <?php echo $this->_var['acc']['keyword']; ?> </td>
								<td>
								<?php if ($this->_var['acc']['islike'] == 0): ?>
								  完全匹配
								<?php endif; ?>
								<?php if ($this->_var['acc']['islike'] == 1): ?>
								  部分匹配
								<?php endif; ?>
								</td>
								<td> <?php echo $this->_var['acc']['url']; ?> </td>
								<td> <?php echo $this->_var['acc']['reply_type_name']; ?> </td>
								<td class="norightborder">&#12288;<a  class="btnGreen" href="wx_account_autoreply.php?ac=edit&id=<?php echo $this->_var['acc']['id']; ?>">编辑</a>&#12288;<a class="btnGray delbutton" data-id="<?php echo $this->_var['acc']['id']; ?>" href="javascript:;">删除</a></td>
							</tr>
							<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
							
									</tbody>
							
						</table>
					</div>
				<div class="cLine">
					<div class="pageNavigator right"> <span> <a class="prePage" href="<?php echo $this->_var['account']['pre_page_url']; ?>"> 上一页 </a> </span> <span class="pageNum"><?php echo $this->_var['account']['page']; ?> / <?php echo $this->_var['account']['pagenum']; ?>(总计:<?php echo $this->_var['account']['total']; ?>)</span> <span> <a class="nextPage" href="<?php echo $this->_var['account']['next_page_url']; ?>"> 下一页 </a> </span> </div>
				</div>
					
					<?php else: ?>
					<table width="100%" cellspacing="0" cellpadding="0" border="0" class="ListProduct">
						<tbody>
							<tr>
								<td>还没有添加任何规则！</td>
							</tr>
						</tbody>
					</table>
					<?php endif; ?> </div>
			</div>
            
            <?php echo $this->fetch('lib/wx_account_manage_sidebar.lbi'); ?>
            
			<div class="clr"></div>
		</div>
	</div>
	 
</div>
 


<?php echo $this->fetch('lib/page_footer.lbi'); ?>


<div style='display:none'>
	<div id='inline_content' style='padding:10px; background:#fff;'> </div>
</div>

<script>
$(function(){
  $('.delbutton').live('click',function(){
    if(confirm('是否确定要删除？')){
	  $.post('wx_account_autoreply.php',{ac:'del',id:$(this).attr('data-id')},function(d){
		  if(d.err==0){
			  alert(d.errmsg);
			  window.location.href='wx_account_autoreply.php?type=<?php echo $this->_var['account']['type']; ?>&id='+$('#op_wxid').val();
		  }else{
			alert('删除失败');  
		  }
		  
	  },'json');
	}
  });

});
</script>
</body>
</html>