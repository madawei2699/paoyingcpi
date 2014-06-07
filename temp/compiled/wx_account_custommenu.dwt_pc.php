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
					<h3 class="left" id="msg_title">自定义菜单只有服务号才可使用</h3>
					<div class="searchbar right"> </div>
					<div class="clr"></div>
				</div>
				
                
          
				<div class="msgWrap">
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="ListProduct">
              <thead>
                <tr>
				  <th>显示顺序</th>
                  <th>按钮名称</th>
				  <th>按钮类型</th>
				  <th>关键词</th>
				  <th>跳转网址</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody> 
		<?php if ($this->_var['total'] > 0): ?>	  
         <?php $_from = $this->_var['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'p');if (count($_from)):
    foreach ($_from AS $this->_var['p']):
?> 
              <tr>
			  <td><?php echo $this->_var['p']['sort_order']; ?></td>
			  <td><?php echo $this->_var['p']['btn_name']; ?></td>
			  <td><?php echo $this->_var['p']['btn_type_show']; ?></td>
			  <td><?php echo $this->_var['p']['keyword']; ?></td>
			  <td><?php echo $this->_var['p']['url']; ?></td>
              <td class="norightborder">&#12288;<a  href="wx_custommenu.php?ac=edit&id=<?php echo $this->_var['p']['id']; ?>">编辑</a>&#12288;<a  href="wx_custommenu.php?ac=add&id=<?php echo $this->_var['id']; ?>&parent_id=<?php echo $this->_var['p']['id']; ?>">添加子按钮</a>&#12288;<a id="<?php echo $this->_var['p']['id']; ?>" class=" del" href="#">删除</a></td>
                </tr>
          <?php $_from = $this->_var['p']['son']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sp');if (count($_from)):
    foreach ($_from AS $this->_var['sp']):
?>
                <tr>
			  <td><div class="node"><?php echo $this->_var['sp']['sort_order']; ?></div></td>
			  <td><?php echo $this->_var['sp']['btn_name']; ?></td>
			  <td><?php echo $this->_var['sp']['btn_type_show']; ?></td>
			  <td><?php echo $this->_var['sp']['keyword']; ?></td>
			  <td><?php echo $this->_var['sp']['url']; ?></td>
              <td class="norightborder">&#12288;<a href="wx_custommenu.php?ac=edit&id=<?php echo $this->_var['sp']['id']; ?>">编辑</a>&#12288;<a id="<?php echo $this->_var['sp']['id']; ?>" class=" del" href="#">删除</a></td>
                </tr>
		  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
     <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>                               
		  <?php endif; ?>
              </tbody>
            </table>
			<a  class="btnGreen" href="wx_custommenu.php?ac=add&id=<?php echo $this->_var['id']; ?>">添加按钮</a>&nbsp;&nbsp;
<a id="<?php echo $this->_var['id']; ?>" class="btnGray update" href="#">更新微信</a>&nbsp;&nbsp;<a id="<?php echo $this->_var['id']; ?>" class="btnGray get" href="#">读取菜单</a>

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
$('.del').click(function(){
	if(confirm('确定要删除这个按钮吗，注意这回删除此按钮的所有子按钮?')){
	   window.location.href='wx_custommenu.php?ac=del&id='+$(this).attr('id');
	}
	
});
$('.update').click(function(){
	if(confirm('更新成功后24小时自动刷新，或者你可以重新关注公众号来立刻刷新')){
	   $.post('wx_custommenu.php',{ac:'update',id:$(this).attr('id')},function(json){
		 if(json.errcode==0){
			alert('自定义菜单已成功更新'); 
		 }else{
			 alert('自定义菜单更新失败,请稍候再试');  
		 }
	   },'json');
	}
	
});

$('.get').click(function(){
	if(confirm('注意：读取菜单会覆盖在微笑微信里保存的现有菜单，要继续读取吗？')){
	   $.post('wx_custommenu.php',{ac:'get',id:$(this).attr('id')},function(err){
		 if(err==0){
			alert('自定义菜单已成功读取'); 
			window.location.reload();
		 }else{
			 alert('自定义菜单读取失败,请稍候再试');  
		 }
	   });
	}
	
});
</script>
</body>
</html>