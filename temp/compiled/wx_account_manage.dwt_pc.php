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
				<div class="cLine">在这里设置公众号开启的模块</div>
				<div class="msgWrap">
                 <table width="100%" cellspacing="0" cellpadding="0" border="0" class="ListProduct">
                 <thead>
                 <tr>
                  <th>模块名称</th>
				  <th>状态</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <tr></tr>
                
                <?php $_from = $this->_var['modules']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'm');if (count($_from)):
    foreach ($_from AS $this->_var['m']):
?> 
                <tr>
			  <td><?php echo $this->_var['m']['title']; ?></td>
			  <td><input type="radio" name="<?php echo $this->_var['m']['mid']; ?>" class="module_state" data-mid="<?php echo $this->_var['m']['mid']; ?>" value="1" data-id="<?php echo $this->_var['account']['id']; ?>"  <?php if ($this->_var['m']['enabled'] == 1): ?>checked<?php endif; ?> />开启<input type="radio" name="<?php echo $this->_var['m']['mid']; ?>" class="module_state" data-mid="<?php echo $this->_var['m']['mid']; ?>" value="0" data-id="<?php echo $this->_var['account']['id']; ?>"  <?php if ($this->_var['m']['enabled'] == 0): ?>checked<?php endif; ?> />关闭</td>
              <td class="norightborder">功能管理</td>
                </tr>
             <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>                               

              </tbody>
            </table>
          </div>


				
				
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
$('.module_state').change(function(){
	state=$(this).val();
	$.post('wx_account.php',{ac:'update_module',id:$(this).attr('data-id'),mid:$(this).attr('data-mid'),state:state},function(json){
	},'json');
});
</script>
</body>
</html>