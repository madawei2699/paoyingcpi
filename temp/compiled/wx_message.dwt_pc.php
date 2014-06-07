<!DOCTYPE html>
<html>
<head>
<meta name="Generator" content="APPNAME VERSION" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['_SC']['site_name']; ?></title>
<link href="<?php echo $this->_var['template_path']; ?>/css/common.css" rel="stylesheet" type="text/css">
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
			<div class="boxHeader">
				<h2>消息管理</h2>
			</div>
			<div class="content">
				<div class="newTips"> <a href=""> <span id="newMsgNum">0</span>条新消息，点击查看 </a> </div>
				<div class="cLine">
					<h3 class="left" id="msg_title"> 全部消息 </h3>
					<div class="searchbar right">
						<select id="search_field" class="txt left" style="height:29px;">
						<option value="province" selected>地区</option>
						<option value="content">内容</option>
						<option value="nickname">提问人</option>
						</select>
						<input type="text" id="msgSearchInput" class="txt left" value="" placeholder="输入内容搜索">
						<button id="msgSearchBtn" href="javascript:;" class="btnGrayS left" title="搜索" type="button">搜索</button>
					</div>
					<div class="clr"></div>
				</div>
				<div class="cLine">
					<div class="pageNavigator right"> <span> <a class="prePage" href="javascript:;"> 上一页 </a> </span> <span class="pageNum"></span> <span> <a class="nextPage" href="javascript:;"> 下一页 </a> </span> </div>
				</div>
				<div class="listTitle">
					<div class="left title msg">消息</div>
					<div class="right title opt">操作</div>
				</div>
				<ul id="listContainer">
				</ul>
				<input id="list_page" value="0" type="hidden" />
				<input id="list_pagenum" value="0" type="hidden" />
				<input id="replyed" value="0" type="hidden" />
				<div class="cLine">
					<div class="pageNavigator right"> <span> <a class="prePage" href="javascript:;"> 上一页 </a> </span> <span class="pageNum"></span> <span> <a class="nextPage" href="javascript:;"> 下一页 </a> </span> </div>
				</div>
			</div>
			<div class="sideBar">
				<div class="catalogList">
					<ul class="shaixuan">
						<li class="selected" data-replyed="0"> <a href="javascript:;">全部消息</a> </li>
						<li class=""  data-replyed="1"> <a href="javascript:;">未回复消息</a> </li>
						<li class=""  data-replyed="2"> <a href="javascript:;">已回复消息</a> </li>
						<!--
						<li class=" subCatalogList "> <a href="">今天</a> </li>
						<li class="  "> <a href="">星标消息</a> </li>
					    -->
					</ul>
					<!--
					<hr>
					<ul>
					    <li class="inline"><a href="javascript:;">推送设置</a></li>
					</ul>
					-->
					
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	 
</div>
 


<?php echo $this->fetch('lib/page_footer.lbi'); ?>



<div style='display:none'>
			<div id='inline_content' style='padding:10px; background:#fff;'>
			</div>
</div>

				 <script>
				 $(function(){
                     list();
					 
					 $('.catalogList ul.shaixuan li').click(function(){
						 replyed=$(this).attr('data-replyed');
						 $('#replyed').val(replyed);
						 $('.catalogList ul.shaixuan li').removeClass('selected');
						 $(this).addClass('selected');
						 $('#msg_title').html($(this).find('a').text());
						 list();
					 });
					 
					 $('#msgSearchBtn').click(function(){
                               $('#list_page').val(1);
							   list();
                     });

                     $('.prePage').click(function(){
                               if(parseInt($('#list_page').val())>1){
                                    $('#list_page').val(parseInt($('#list_page').val())-1);
	                                list();
                               }
                     });

                     $('.nextPage').click(function(){
                              if(parseInt($('#list_page').val())<parseInt($('#list_pagenum').val())){
                                    $('#list_page').val(parseInt($('#list_page').val())+1);
	                                list();
                              }
                     });
					 
					 
				 });


				 
	function list(){
		var data= new Object;
		data.ac='msg_list';
		data.replyed=$('#replyed').val();
		data.page=$('#list_page').val();
		data.search_field=$('#search_field').val();	
		data.search_keyword=$('#msgSearchInput').val();
	 $.ajax({
         type:'POST',
	     dataType:'json',
         url: 'wx_message.php',
         data:data,
         async: true,
         success:function(json){
              var str='';
	          if(json.total>0){
	              $(json.list).each(function(i,n){


                   str+=''+
					'<li data-id="'+n.id+'" id="msgListItem'+n.id+'" class="msgListItem buddyRichInfoC " style="z-index: 99999;"> <a class="msgSender left" href="wx_singlemsg.php?wxid='+n.wxid+'" target="_blank"> <img width="48" height="48" class="avatar left" data-fakeid="'+n.fakeid+'" src="'+n.headimg+'"> </a>'+
						'<div class="wxMsgArea">'+
							'<div class="opt oper right"> <a title="快捷回复" class="icon18 iconReply" data-tofakeid="'+n.fakeid+'" data-id="'+n.id+'" href="javascript:;"></a> </div>'+
							'<div class="opt msgTime right"> '+n.addtime+' </div>'+
							'<a data-fakeid="'+n.fakeid+'" target="_blank" href="wx_singlemsg.php?wxid='+n.wxid+'" class="msgSender left">'+n.nickname+'</a><span style="color:#AAAAAA;font-size: 12px;">&nbsp;&nbsp;'+n.province+'&nbsp;&nbsp;'+n.city+'('+n.weixin_name+')</span>';
							
							if(n.replyed==1){
							  str+='<span style="color:green;font-size: 12px;">&nbsp;&nbsp;已回复</span>';	
							}
							
							str+=''+											
							'<span data-fakeid="'+n.fakeid+'" class="remarkName left"></span>'+
							'<div class="wxMsg clr"> '+n.content+' </div>'+
						'</div>'+
						'<div class="clr"></div>'+
						'<div class="quickReplyBox" id="quickReplyBox'+n.id+'">'+
							'<div class="cLine c-b">快速回复:</div>'+
							'<div class="cLine">'+
								'<textarea class="quickReplyTxt"></textarea>'+
							'</div>'+
							'<div class="cLine">'+
								'<button class="btnGreenS quickReplyOK">发送</button>'+
								'<a href="javascript:;" class="quickReplyPickup">收起</a> </div>'+
						'</div>'+
					'</li>';


					  
		         });	
		        $('#listContainer').html(str);
				$('#list_page').val(json.page);
				$('#list_pagenum').val(json.pagenum);
				$('.pageNum').html('&nbsp;&nbsp;'+json.page+'&nbsp;/&nbsp;'+json.pagenum+'&nbsp;&nbsp;');
			   
			    $('.iconReply').click(function(){
						id=$(this).attr('data-id');
						$('#quickReplyBox'+id).toggle(); 
				});
				
				$('.quickReplyPickup').click(function(){
					$(this).parents('.quickReplyBox').hide();
				});
				
				$('.quickReplyOK').click(function(){
					question_id=$(this).parents('li').attr('data-id');
					content=$('#quickReplyBox'+question_id+' .quickReplyTxt').val();
					$.post('wx_message.php',{ac:'send_msg',question_id:question_id,content:content},function(d){
						alert('回复成功');
						$('#quickReplyBox'+question_id+' .quickReplyTxt').val('');
				    });
				});
				
	         }else{
		        $('#listContainer').html('');
				$('#list_page').val(json.page);
				$('#list_pagenum').val(json.pagenum);
				$('.pageNum').html('&nbsp;&nbsp;'+json.page+'&nbsp;/&nbsp;'+json.pagenum+'&nbsp;&nbsp;');
			 }
         }});
	  }//end function	
	  
   function convertArray(o) { 
         var v = {};
         for (var i in o) {
         if (typeof (v[o[i].name]) == 'undefined') v[o[i].name] = o[i].value;
         else v[o[i].name] += "," + o[i].value;
         }
         return v;
   }
   
   $(".inline").colorbox({href:"#inline_content",inline:true, width:"700px",height:"430px"});	    	  			 
 	  			 
				 </script>
</body>
</html>