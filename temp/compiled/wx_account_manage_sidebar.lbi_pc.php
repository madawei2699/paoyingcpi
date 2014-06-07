			<div class="sideBar">
				<div class="catalogList">
					<ul class="shaixuan">
						<li class=""> <a href="wx_account.php?ac=manage&id=<?php echo $this->_var['account']['id']; ?>">功能管理</a> </li>
						<li class=""> <a href="wx_account_autoreply.php?type=focus&id=<?php echo $this->_var['account']['id']; ?>">被关注时自动回复</a> </li>
						<li class=""> <a href="wx_account_autoreply.php?type=aftermsg&id=<?php echo $this->_var['account']['id']; ?>">接收消息时自动回复</a> </li>
						<li class=""> <a href="wx_account_autoreply.php?type=keyword&id=<?php echo $this->_var['account']['id']; ?>">关键词自动回复</a> </li>
                        <li class=""> <a href="wx_custommenu.php?id=<?php echo $this->_var['account']['id']; ?>">自定义菜单</a> </li>
					</ul>
				</div>
			</div>
