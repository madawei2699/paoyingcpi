<!DOCTYPE html>
<html>
<head>
<meta name="Generator" content="APPNAME VERSION" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['_SC']['site_name']; ?></title>
<link href="<?php echo $this->_var['template_path']; ?>/css/common.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/table.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/jquery_cbox.css" rel="stylesheet" type="text/css">
<link href="<?php echo $this->_var['template_path']; ?>/css/wxm_appmsg_list15def8.css" rel="stylesheet" type="text/css">
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery-1.8.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $this->_var['template_path']; ?>/script/utils.min.js"></script>
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery.upload.js" type="text/javascript"></script>
<link href="/ueditor/themes/default/css/ueditor.css" rel="stylesheet" type="text/css">
<script src="/ueditor/ueditor.config.js" type="text/javascript"></script>
<script src="/ueditor/ueditor.all.min.js" type="text/javascript"></script>
<style>
.news {
	display: none;
}
.appMsgEdit .container .containerBox .content {
	border: 0 none;
	float: none;
	margin: 0;
	padding: 0;
	width: auto;
}
</style>
</head>
<body>

<?php echo $this->fetch('lib/page_header.lbi'); ?>
 


<div class="container-wrapper">
	<div class="appMsgEdit">
		<div class="container" id="main">
			<div class="containerBox">
				<div class="appTitle normalTitle2">
					<div class="vipuser">
						<div class="logo"> <img width="100" height="100" src="<?php echo $this->_var['account']['headimg']; ?>" onerror="this.src='<?php echo $this->_var['_SC']['siteurl']; ?>themes/pc/mpres/htmledition/images/default_avator.png'"> </div>
						<div id="nickname"> <strong><?php echo $this->_var['account']['weixin_name']; ?></strong></div>
						<div id="weixinid">微信号:<?php echo $this->_var['account']['username']; ?></div>
						<input type="hidden" id="op_wxid" value="<?php echo $this->_var['account']['id']; ?>" />
					</div>
					<div class="clr"></div>
				</div>
				<div class="content">
					<div class="newTips"> <a href=""> <span id="newMsgNum">0</span>条新消息，点击查看 </a> </div>
					<div class="cLine">
						<div style="margin-left:20px;margin-right:20px;">
							<h3 class="left">添加自动回复</h3>
							<a href="wx_account_autoreply.php?type=<?php echo $this->_var['account']['type']; ?>&id=<?php echo $this->_var['account']['id']; ?>" class="btnGrayS right">返回</a>
							<input type="radio" value="text" name="reply_type" checked />
							文本回复
							<input type="radio" value="single_news" name="reply_type"  />
							单图文回复
							<input type="radio" value="multi_news"  name="reply_type"  />
							多图文回复
							<br />
							<input type="radio" value="focus" name="type"  checked  />
							关注时
							<input type="radio" value="aftermsg" name="type"  />                          
							接收消息时
							<input type="radio" value="keyword" name="type"  />                          
                            接收关键词时							
                            <br />							
							关键词设：<input class="txt" type="text" value="" id="keyword" /><input type="radio" name="islike" value="0" checked  />完全匹配<input type="radio" name="islike" value="1"  />部分匹配(多个用空格分开)
							<br />
							优先级：<input class="txt" type="text" value="" id="priority" />
							
							</div>
						<div class="clr"></div>
					</div>
					<div class="cLine">
						<div id="txtnews" class="news">
							<div class="msgSenderPlugin">
								<ul class="tab">
									<li data-type="1" class="selected chooseMedia chooseMedia1">文字</li>
									<!--
									<li data-type="3" class="chooseMedia vioce chooseMedia3">语音</li>
									<li data-type="2" class="chooseMedia chooseMedia2">图片</li>
									<li data-type="4" class="chooseMedia chooseMedia4">视频</li>
									<li data-type="10" class="chooseMedia chooseMedia10" style="display: none;">图文消息</li>
									-->
								</ul>
								<div class="panel">
									<div class="txtArea">
										<div class="functionBar">
											<div class="opt"><!-- <a href="javascript:;" class="icon18C iconEmotion block">表情</a>--> </div>
											<div class="tip"> </div>
											<div class="emotions">
												<table cellspacing="0" cellpadding="0">
												</table>
												<div class="emotionsGif"></div>
											</div>
											<div class="clr"></div>
										</div>
										<div class="editArea">
											<textarea style="display: none;"></textarea>
											<div contenteditable="true" style="overflow-y: auto; overflow-x: hidden;"></div>
										</div>
									</div>
									<div class="mediaArea none"> </div>
									<div id="showVerifyCode" class="none"> <br>
										<img src=""> <a id="changeVerifyCode" href="javascript:;">换一张</a>
										<div> 请输入验证码
											<input type="text" value="" name="" id="imgcode">
										</div>
									</div>
									<div class="btnArea">
										<button class="save btnGreen">保存</button>
									</div>
								</div>
							</div>
						</div>
						<div id="singlenews" class="news">
							<div class="z oh msg-edit">
								<div class="left msg-preview">
									<div class="msg-item-wrapper" id="appmsg" data-appid="" data-create-time="">
										<div class="msg-item appmsgItem">
											<h4 class="msg-t"> <span class="i-title"> </span> </h4>
											<p class="msg-meta"><span class="msg-date"></span></p>
											<div class="cover">
												<p class="default-tip" style="">封面图片</p>
												<img src="" class="i-img" style="display:none"> </div>
											<p class="msg-text i-desc"></p>
											<div class="i-content" style="display:none"></div>
											<div class="i-url" style="display:none"></div>

										</div>
										<div class="msg-opr">
											<ul class="f0 msg-opr-list">
												<li class="b-dib opr-item"><a class="block tc opr-btn edit-btn" href=""><span class="th vm dib opr-icon edit-icon">编辑</span></a></li>
												<li class="b-dib opr-item"><a class="block tc opr-btn del-btn" href="javascript:;" data-mid=""><span class="th vm dib opr-icon del-icon">删除</span></a></li>
											</ul>
										</div>
										<div class="msg-hover-mask"></div>
										<div class="msg-mask"><span class="dib msg-selected-tip"></span></div>
									</div>
								</div>
								<div style="margin-top: 30.8px;" class="msg-edit-area" id="msgEditArea">
									<div class="rel msg-editer-wrapper">
										<div class="msg-editer">
											<label for="" class="block">标题</label>
											<input class="msg-input" id="title" value="" type="text">
											<label for="" class="block"><span id="upload-tip" class="upload-tip r">大图片建议尺寸：720像素 * 400像素</span>封面</label>
											 <div class="cover-area">
                                                 <div class="oh z cover-hd">
                                                       <a  href="#inline_content" class="icon28C upload-btn">上传</a>
                                                 </div>
                                               </div> 
											 <input class="msg-input" id="pic" type="hidden">
											<a id="desc-block-link" style="padding-top: 10px;" href="javascript:(function(){jQuery('#desc-block').show();jQuery('#desc-block-link').hide()})()" class="url-link block">添加摘要</a>
											<div style="display: none;" id="desc-block" class="none">
												<label class="block">摘要</label>
												<textarea name="" id="desc" class="msg-txta"></textarea>
											</div>
											<label for="" class="block">正文</label>
											<textarea name="content" id="myEditor1" style="height:300px;"></textarea>
											
											
											
											<a id="url-block-link" style="padding-top: 10px;" href="javascript:(function(){jQuery('#singlenews #url-block').show();jQuery('#singlenews #url-block-link').hide()})()" class="url-link block">添加跳转链接</a>
											<div style="display: none;" id="url-block" class="none">
												<label for="" class="block">跳转链接</label>
												<input class="msg-input" id="url" value="" type="text">
											</div>
										</div>
										<div class="oh z shadow"> <span class="left ls"></span><span class="right rs"></span> </div>
										<span style="margin-top: 0px;" class="abs msg-arrow a-out"></span> <span style="margin-top: 0px;" class="abs msg-arrow a-in"></span> </div>
								</div>
							</div>
							<p class="tc msg-btn"> <!--<a href="javascript:;" id="previewAppMsg" class="btnGreen">发送预览</a>--> <a href="javascript:;" id="save" class="btnGreen">保存</a> </p>
						</div>
						<div id="multinews" class="news">
							<div class="z oh msg-edit">
								<div class="left msg-preview">
								
								
									<div class="msg-item-wrapper" id="appmsg" data-appid="" data-create-time="">
										<div class="msg-item multi-msg">
										

											<div id="appmsgItem1" class="appmsgItem">
												<p class="msg-meta"> <span class="msg-date">  </span> </p>
												<div class="cover">
													<p class="default-tip" style="">封面图片</p>
													<h4 class="msg-t"> <span class="i-title">标题</span> </h4>
													<ul class="abs tc sub-msg-opr">
														<li class="b-dib sub-msg-opr-item"> <a href="javascript:;" class="th icon18 iconEdit" data-rid="1">编辑</a> </li>
													</ul>
													<img src="" class="i-img" style="display:none">
												 </div>
											</div>
											<div class="rel sub-msg-item appmsgItem" id="appmsgItem2"> <span class="thumb"> <span class="default-tip" style="">缩略图</span> <img src="" class="i-img" style="display:none"> </span>
												<h4 class="msg-t"> <span class="i-title">标题</span> </h4>
												<ul class="abs tc sub-msg-opr">
													<li class="b-dib sub-msg-opr-item"> <a href="javascript:;" class="th icon18 iconEdit" data-rid="2">编辑</a> </li>
													<li class="b-dib sub-msg-opr-item"> <a href="javascript:;" class="th icon18 iconDel" data-rid="2">删除</a> </li>
												</ul>
											</div>
											
											
											<div class="sub-add"> <a href="javascript:;" class="block tc sub-add-btn"><span class="vm dib sub-add-icon"></span>增加一条</a> </div>
										</div>
										<div class="msg-opr">
											<ul class="f0 msg-opr-list">
												<li class="b-dib opr-item"><a class="block tc opr-btn edit-btn" href=""><span class="th vm dib opr-icon edit-icon">编辑</span></a></li>
												<li class="b-dib opr-item"><a class="block tc opr-btn del-btn" href="javascript:;" data-mid=""><span class="th vm dib opr-icon del-icon">删除</span></a></li>
											</ul>
										</div>
										<div class="msg-hover-mask"></div>
										<div class="msg-mask"><span class="dib msg-selected-tip"></span></div>
									</div>
									
									
									
								</div>
								<div style="margin-top: 32.8px;" class="msg-edit-area" id="msgEditArea">
									<div class="rel msg-editer-wrapper">
										<div class="msg-editer">
											<label for="" class="block">标题</label>
											<input class="msg-input" id="title" type="text">
											<label for="" class="block"><span id="upload-tip" class="upload-tip r">大图片建议尺寸：720像素 * 400像素</span>封面</label>                                     
											 <div class="cover-area">
                                                 <div class="oh z cover-hd">
                                                       <a  href="#inline_content" class="icon28C upload-btn">上传</a>
                                                 </div>
                                               </div> 
											 <input class="msg-input" id="pic" type="hidden">
											<label for="" class="block">正文</label>
											<textarea name="content" id="myEditor2" style="height:300px;"></textarea>
											
											
											<a id="url-block-link" style="padding-top: 10px;" href="javascript:(function(){jQuery('#multinews #url-block').show();jQuery('#multinews #url-block-link').hide()})()" class="url-link block">添加跳转链接</a>
											<div style="display: none;" id="url-block" class="none">
												<label for="" class="block">跳转链接</label>
												<input class="msg-input" id="url" type="text">
											</div>
										</div>
										<div class="oh z shadow"> <span class="left ls"></span><span class="right rs"></span> </div>
										<span style="margin-top: 0px;" class="abs msg-arrow a-out"></span> <span style="margin-top: 0px;" class="abs msg-arrow a-in"></span> </div>
								</div>
							</div>
							<p class="tc msg-btn"> <!--<a href="javascript:;" id="previewAppMsg" class="btnGreen">发送预览</a>--> <a href="javascript:;" id="save" class="btnGreen">保存</a> </p>
						</div>
					</div>
				</div>
				<div class="clr"></div>
			</div>
		</div>
	</div>
</div>

</div>
 


<?php echo $this->fetch('lib/page_footer.lbi'); ?>


<div style='display:none'>
	<div id='inline_content' style='padding:10px; background:#fff;'>
		<p>选择封面:
			<input type="file" id="file1" name="file1" style="width:300" value="">
		</p>
		<p>
			<input type="button" id="beginupload" name="beginupload" value="开始上传" class="form-button" />
		</p>
		<p id="popdiv_msg"></p>
	</div>
</div>
<script>
$(function(){
    var reply_type=$('input[name=reply_type]:checked').val();
    var type =$('input[name=type]:checked').val();

	$('.news').hide();
    if(reply_type=="text"){
      $('#txtnews').show();	
    }
    if(reply_type=="single_news"){
      $('#singlenews').show();	
    }
    if(reply_type=="multi_news"){
      $('#multinews').show();	
    }


  $('input[name=reply_type]').click(function(){
    reply_type=$(this).val();
	$('.news').hide();
    if(reply_type=="text"){
      $('#txtnews').show();	
    }
    if(reply_type=="single_news"){
      $('#singlenews').show();	
    }
    if(reply_type=="multi_news"){
      $('#multinews').show();	
    }
  });
  
  $('input[name=type]').click(function(){
    type=$(this).val();
  });


   ac="add_to_db";
/*文字回复*/
   $('#txtnews .save').live('click',function(){
	     var data=new Object();
		 data.ac=ac;
         data.op_wxid=$('#op_wxid').val();
		 data.priority=$('#priority').val();
		 data.keyword=$('#keyword').val();
		 data.islike=$('input[name=islike]:checked').val();
		 data.content=$('#txtnews textarea').val();
		 data.type=type;
		 data.reply_type='text';
		 var msgitem=new Object();
		 $.post('wx_account_autoreply.php',data,function(d){
			 if(d.err==0){
			   alert(d.errmsg);
			   window.location.href='wx_account_autoreply.php?type='+data.type+'&id='+data.op_wxid;
			 }else{
			   alert(d.errmsg);
			 }
		 },'json');
   });
   
   $('#txtnews .editArea div').live('keyup',function(){
	   $('#txtnews textarea').val($(this).html());
   });
/*文字回复 end*/	
	
/*单图文 start*/
    var s_title;
	var s_url;
	var s_pic;
	var s_content;
	var s_desc;
	
	var editor1 = new UE.ui.Editor();
    editor1.render('myEditor1');

    //读取现有数据
		 
			 if(!s_title) s_title=$('#singlenews .appmsgItem').find('.i-title').html();
			 if(!s_desc) s_desc=$('#singlenews .appmsgItem').find('.i-desc').html();
			 if(!s_pic) s_pic=$('#singlenews .appmsgItem').find('.i-img').attr('src');
			 if(!s_content) s_content=$('#singlenews .appmsgItem').find('.i-content').html();
			 if(!s_url) s_url=$('#singlenews .appmsgItem').find('.i-url').html();
            
	         $('#singlenews #msgEditArea #title').val(s_title);
	         $('#singlenews #msgEditArea #desc').val(s_desc);
			 if(s_content){
				  editor1.ready(function(){
                        editor1.setContent(s_content);
                  });
	         }
	         $('#singlenews #msgEditArea #pic').val(s_pic);
	         $('#singlenews #msgEditArea #url').val(s_url);
	//end读取现有数据


   $('#singlenews #msgEditArea #title').live('keyup',function(){
	  $('#singlenews .appmsgItem .i-title').html($(this).val());
	  s_title=$(this).val();
   });

   $('#singlenews #msgEditArea #desc').live('keyup',function(){
	  $('#singlenews .appmsgItem .i-desc').html($(this).val());
	  s_desc=$(this).val();
   });


   editor1.addListener('selectionchange',function(){
      s_content=editor1.getContent();
   });

   
   $('#singlenews #msgEditArea #pic').live('blur',function(){
	    $('#singlenews .appmsgItem .i-img').attr('src',$(this).val());
		$('#singlenews .appmsgItem .i-img').show();
	    $('#singlenews .appmsgItem .default-tip').hide();
	    s_pic=$(this).val();
   });

   $('#singlenews #msgEditArea #url').live('blur',function(){
	    s_url=$(this).val();
   });
   

   $('#singlenews #save').live('click',function(){
	     var data=new Object();
		 data.ac=ac;
         data.op_wxid=$('#op_wxid').val();
		 data.keyword=$('#keyword').val();
		 data.priority=$('#priority').val();
		 data.islike=$('input[name=islike]:checked').val();
		 data.reply_type='single_news';
		 data.type=type;
		 data.title=s_title;
		 data.desc=s_desc;
		 data.content=s_content;
		 data.pic=s_pic;
		 data.url=s_url;
		 $.post('wx_account_autoreply.php',data,function(d){
			 if(d.err==0){
			   alert(d.errmsg);
			   window.location.href='wx_account_autoreply.php?type='+data.type+'&id='+data.op_wxid;
			 }else{
			   alert(d.errmsg);
			 }
		 },'json');
		 
   });
	
	

		
/*单图文 end */


/*多图文 start*/	
	
    var appmsgitem_count=$('#multinews .appmsgItem').size();  
    var current_rid=1;  //编辑区域的data-rid
	
	var title=new Array(); //标题数组
    var url=new Array();   //外链
	var pic=new Array();   //图片链接
	var content=new Array();  //正文
		
    var editor2 = new UE.ui.Editor();
    editor2.render('myEditor2');	

    //读取现有数据
		 $('#multinews .appmsgItem').each(function(i,n){
			 var r_id=$(this).find('.iconEdit').attr('data-rid');
			 if(!title[r_id]) title[r_id]=$(this).find('.i-title').html();
			 if(!pic[r_id]) pic[r_id]=$(this).find('.i-img').attr('src');
			 if(!content[r_id]) content[r_id]=$(this).find('.i-content').html();
			 if(!url[r_id]) url[r_id]=$(this).find('.i-url').html();

			 if(i>1) appmsgitem_count=i+1;
			 if(i==0){
	                   if(title[r_id]) $('#multinews #msgEditArea #title').val(title[current_rid]);
                       if(content[r_id]){
						   editor2.ready(function(){
                                  editor2.setContent(content[r_id]);
                           });
	                   }
	                   $('#multinews #msgEditArea #pic').val(pic[r_id]);
	                   $('#multinews #msgEditArea #url').val(url[r_id]);
			 }
			 appmsgitem_count=i+1;
	     });	 	
	//end读取现有数据


	
	var time=formatDate(new Date(), "yyyy-MM-dd");
    $('.msg-date').html(time);
	
	  
  $('.appmsgItem').live({'mouseenter':function(){
	  $(this).addClass('sub-msg-opr-show');
  },'mouseleave':function(){
	  $(this).removeClass('sub-msg-opr-show');
  }
  });
  
  $(".sub-add").click(function () {
             
			count=$('#multinews .appmsgItem').size();

            if (count >= 8){
				 alert("你最多只可以加入8条图文消息");
			}else{  
			  appmsgitem_count++;         
			
			  str=''+
			  '<div id="appmsgItem'+appmsgitem_count+'" class="rel sub-msg-item appmsgItem">'+
			  ' <span class="thumb">'+
			  '                 <span style="" class="default-tip">缩略图</span>'+
			  '                 <img style="display:none" class="i-img" src="">'+
			  ' </span>'+
			  '       <h4 class="msg-t">'+
			  '                    <span class="i-title">标题</span>'+
			  '       </h4>'+
			  '       <ul class="abs tc sub-msg-opr">'+
			  '         <li class="b-dib sub-msg-opr-item">'+
			  '           <a data-rid="'+appmsgitem_count+'" class="th icon18 iconEdit" href="javascript:;">编辑</a>'+
			  '         </li>'+
			  '         <li class="b-dib sub-msg-opr-item">'+
			  '           <a data-rid="'+appmsgitem_count+'" class="th icon18 iconDel" href="javascript:;">删除</a>'+
			  '         </li>'+
			  '       </ul>     </div>';
              $(str).insertBefore($(this));
			}

   });
   
   $('.iconDel').live('click',function(){
	  rid=$(this).attr('data-rid');
	  count=$('#multinews .appmsgItem').size();
	  if(confirm("确认删除此消息？")){
	    if(count>2){
	      $('#multinews #appmsgItem'+rid).remove();
	    }else{
		  alert('多图文不能少于2条');  
	    }
	  }
   });
   
   $('.iconEdit').live('click',function(){
	   var r = 580,
	   j = $("#multinews .msg-preview").offset().top;
	   p=$(this).attr('data-rid');
	   $('#multinews .appmsgItem').each(function(i,m){
		    if($(this).attr('id')=='appmsgItem'+p){
				n=i+1;
			}
	   });
	   content[current_rid]=editor2.getContent();   	   
	   current_rid=$(this).attr('data-rid');
	   $('#multinews #msgEditArea #title').val('');
	   $('#multinews #msgEditArea #pic').val('');
	   $('#multinews #msgEditArea #url').val('');
	   if(title[current_rid]) $('#multinews #msgEditArea #title').val(title[current_rid]);
       if(content[current_rid]){
		    editor2.setContent(content[current_rid]);
	   }else{
		    editor2.setContent('');
	   }
	   $('#multinews #msgEditArea #pic').val(pic[current_rid]);
	   $('#multinews #msgEditArea #url').val(url[current_rid]);

	   t=$('#multinews #appmsgItem'+$(this).attr('data-rid'));
       i = t.outerHeight();
       n > 4 ? ($("#multinews #msgEditArea").css("margin-top", t.offset().top - j - r + i + 30 + "px"), $("#multinews #msgEditArea .a-out").css("margin-top", r - i / 2 - 54 + "px"), $("#msgEditArea .a-in").css("margin-top", r - i / 2 - 54 + "px")) : ($("#multinews #msgEditArea").css("margin-top", t.offset().top - j + 30 + "px"), $("#multinews #msgEditArea .a-out").css("margin-top", "0px"), $("#multinews #msgEditArea .a-in").css("margin-top", "0px"))
   });


   $('#multinews #msgEditArea #title').live('keyup',function(){
	  $('#multinews #appmsgItem'+current_rid+' .i-title').html($(this).val());
	  title[current_rid]=$(this).val();
   });


   editor2.addListener('selectionchange',function(){
      content[current_rid]=editor2.getContent();
   });

   
   $('#multinews #msgEditArea #pic').live('blur',function(){
	    $('#multinews #appmsgItem'+current_rid+' .i-img').attr('src',$(this).val());
		$('#multinews #appmsgItem'+current_rid+' .i-img').show();
	    $('#multinews #appmsgItem'+current_rid+' .default-tip').hide();
	     pic[current_rid]=$(this).val();
   });

   $('#multinews #msgEditArea #url').live('blur',function(){
	     url[current_rid]=$(this).val();
   });


   $('#multinews #save').live('click',function(){
	     var data=new Object();
		 data.ac=ac;
         data.op_wxid=$('#op_wxid').val();
		 data.priority=$('#priority').val();
		 data.keyword=$('#keyword').val();
		 data.islike=$('input[name=islike]:checked').val();
		 data.type=type;
		 data.reply_type="multi_news";
		 var msgitem=new Object();
		 $('#multinews .appmsgItem').each(function(i,n){
			 var r_id=$(this).find('.iconEdit').attr('data-rid');
			 if(!title[r_id]) title[r_id]='标题';
			  if(!pic[r_id]) pic[r_id]='';
			  if(!content[r_id]) content[r_id]='';
			  if(!url[r_id]) url[r_id]='';
			 msgitem[i]={r_id:r_id,title:title[r_id],pic:pic[r_id],content:content[r_id],url:url[r_id]};
	     });	 
		 data.msgitem=obj2str(msgitem);
		 $.post('wx_account_autoreply.php',data,function(d){
			 if(d.err==0){
			   alert(d.errmsg);
			   window.location.href='wx_account_autoreply.php?type='+data.type+'&id='+data.op_wxid;
			 }else{
			   alert(d.errmsg);
			 }
		 },'json');
		 
   });

   
 /* 多图文 end */  

/*上传图片*/
    $(".upload-btn").colorbox({inline:true, width:"50%",height:"400px"});

	$('#beginupload').click(function() {
      if($('#file1').val()!=''){									 
      $('#file1').upload('wx_account_autoreply.php?ac=upload', function(json) {
		   if(json.err==0){
			if(reply_type=="single_news"){
			   $('#singlenews .appmsgItem .i-img').attr('src',json.filename);
		       $('#singlenews .appmsgItem .i-img').show();
	           $('#singlenews .appmsgItem .default-tip').hide();
	           s_pic=json.filename;
			}			   
			if(reply_type=="multi_news"){
	         $('#multinews #appmsgItem'+current_rid+' .i-img').attr('src',json.filename);
		     $('#multinews #appmsgItem'+current_rid+' .i-img').show();
	         $('#multinews #appmsgItem'+current_rid+' .default-tip').hide();
	         pic[current_rid]=json.filename;
		    }
			 $("#inline_content").colorbox.close();
		   }else{
			 $('#popdiv_msg').html(json.msg);  
		   }
      }, 'json');
	  }else{
			 $('#popdiv_msg').html('必须选择一个图文件');  
	  }
    });
/*上传图片 end*/
  
});


//下面这个方法是将json对象转换为字符串  
function obj2str(o){  
    var r = [];  
    if(typeof o =="string") return "\""+o.replace(/([\'\"\\])/g,"\\$1").replace(/(\n)/g,"\\n").replace(/(\r)/g,"\\r").replace(/(\t)/g,"\\t")+"\"";  
    if(typeof o =="undefined") return "undefined";  
    if(typeof o == "object"){  
        if(o===null) return "null";  
        else if(!o.sort){  
            for(var i in o)  
                r.push('"'+i+'":'+obj2str(o[i]))  
            r="{"+r.join()+"}"  
        }else{  
            for(var i =0;i<o.length;i++)  
                r.push(obj2str(o[i]))  
            r="["+r.join()+"]"  
        }  
        return r;  
    }  
    return o.toString();  
} 

function formatDate(e,t){var n=e.getFullYear(),r="0"+(e.getMonth()+1);r=r.substring(r.length-2);var i="0"+e.getDate();i=i.substring(i.length-2);var s="0"+e.getHours();s=s.substring(s.length-2);var o="0"+e.getMinutes();return o=o.substring(o.length-2),t.replace("yyyy",n).replace("MM",r).replace("dd",i).replace("hh",s).replace("mm",o)}
</script>
</body>
</html>