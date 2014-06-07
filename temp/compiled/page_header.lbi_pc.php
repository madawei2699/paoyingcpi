<div class="header" id="header">
	<div class="logo_area">
		<div class="wrapper"> <img alt="<?php echo $this->_var['_SC']['site_name']; ?>" onclick="javascript:location.href='/'" src="<?php echo $this->_var['template_path']; ?>/images/logo.png"> <span class="hd_login_info"> <span><a style="padding-right:0;" href=""><?php echo $this->_var['_SGLOBAL']['fullname']; ?></a></span> <span class="none"><a href="#">帮助中心</a></span> <span>|<a href="logout.php" >退出</a></span> </span>
			<div class="logo_notify_list none" id="headNotifyList"> </div>
		</div>
	</div>
	<div class="navigator">
		<ul class="textLarge">
			<?php $_from = $this->_var['_SGLOBAL']['navmenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav');if (count($_from)):
    foreach ($_from AS $this->_var['nav']):
?>
			<li><a href="<?php echo $this->_var['nav']['url']; ?>"><?php echo $this->_var['nav']['title']; ?></a></li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</ul>
	</div>
</div>