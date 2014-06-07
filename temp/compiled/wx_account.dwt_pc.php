<!DOCTYPE html>
<html>
<head>
<meta name="Generator" content="APPNAME VERSION" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['_SC']['site_name']; ?></title>
<link href="<?php echo $this->_var['template_path']; ?>/css/common.css" rel="stylesheet" type="text/css"> 
<link href="<?php echo $this->_var['template_path']; ?>/css/table.css" rel="stylesheet" type="text/css"> 
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery-1.8.2.min.js" type="text/javascript"></script> 
<script type="text/javascript" src="<?php echo $this->_var['template_path']; ?>/script/utils.min.js"></script>
</head>
<body>


<?php echo $this->fetch('lib/page_header.lbi'); ?>




<div class="container-wrapper">
	<div class="container" id="main">
		<div class="containerBox">
			<div class="boxHeader">
				<h2>公众号管理</h2>
			</div>
			<div class="content">
				<div class="newTips"> <a href=""> <span id="newMsgNum">0</span>条新消息，点击查看 </a> </div>
				<div class="cLine">
					<h3 class="left" id="msg_title">  </h3>
					<div class="searchbar right">
					</div>
					<div class="clr"></div>
				</div>
				
			<?php if ($this->_var['total'] > 0): ?>
				<div class="msgWrap">
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="ListProduct">
              <thead>
                <tr>
                  <th>公众号名称</th>
				  <th>状态</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <tr></tr>
                
     <?php $_from = $this->_var['account']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'acc');if (count($_from)):
    foreach ($_from AS $this->_var['acc']):
?> 
                <tr>
			  <td><p><a title="点击进入功能管理" href="wx_account.php?ac=edit&id=<?php echo $this->_var['acc']['id']; ?>"><img width="40" height="40" src="<?php echo $this->_var['acc']['headimg']; ?>" onerror="this.src='<?php echo $this->_var['_SC']['siteurl']; ?>themes/pc/mpres/htmledition/images/default_avator.png'"></a></p><p><?php echo $this->_var['acc']['weixin_name']; ?></p></td>
			  <td><?php if ($this->_var['acc']['state'] == 1): ?>已绑定<?php else: ?>尚未绑定<?php endif; ?></td>
                  <td class="norightborder">&#12288;<a  class="btnGreen" href="wx_account.php?ac=edit&id=<?php echo $this->_var['acc']['id']; ?>">编辑</a>&#12288;<a class="btnGreen" href="wx_account.php?ac=manage&id=<?php echo $this->_var['acc']['id']; ?>">功能管理</a>&#12288;<a id="<?php echo $this->_var['acc']['id']; ?>" class="btnGray del_account" href="#">删除</a></td>
                </tr>
     <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>                               

              </tbody>
            </table>
          </div>
		  <?php endif; ?>
								
			</div>
			<div class="sideBar">
				<div class="catalogList">
					<ul class="shaixuan">
						<li class="selected"> <a href="wx_account.php">公众号管理</a> </li>
						<li class=""> <a href="wx_account.php?ac=add">添加公众号</a> </li>
						<!--
						<li class=" subCatalogList "> <a href="">今天</a> </li>
						<li class="  "> <a href="">星标咨询</a> </li>
					    -->
					</ul>
					
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	 
</div>


<?php echo $this->fetch('lib/page_footer.lbi'); ?>

<script>
$(function(){
	$('.del_account').live('click',function(){
		if(confirm('确定要删除这个公众号吗？')){
			window.location.href='wx_account.php?ac=del&id='+$(this).attr('id');
		}
	});
});
</script>
</body></html>