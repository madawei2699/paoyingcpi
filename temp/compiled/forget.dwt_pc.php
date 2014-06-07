<!DOCTYPE html>
<html>
<head>
<meta name="Generator" content="APPNAME VERSION" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['_SC']['site_name']; ?></title>
<link href="<?php echo $this->_var['template_path']; ?>/css/common2.css" rel="stylesheet" type="text/css"> 
<link href="<?php echo $this->_var['template_path']; ?>/css/form.css" rel="stylesheet" type="text/css"> 
<script src="<?php echo $this->_var['template_path']; ?>/script/jquery-1.8.2.min.js" type="text/javascript"></script>
<script src="<?php echo $this->_var['template_path']; ?>/script/utils.min.js" type="text/javascript"></script>
</head>
<body>

<div class="header" id="header">
     <div class="logo_area">       
     <div class="wrapper">         
     <img alt="<?php echo $this->_var['_SC']['site_name']; ?>" onclick="javascript:location.href='/'" src="<?php echo $this->_var['template_path']; ?>/images/logo.png">
     <span class="hd_login_info">              
                  </span>
        <div class="logo_notify_list none" id="headNotifyList">         </div>
     </div>     
     </div>
     
</div>



<div class="container" id="main">
   <div class="containerBox boxIndex">
   <div class="rn-box check-box" style="display: block;">
        <form id="form-forget">      
        <div class="frm">
             <div class="frm-hd">
              <h3 class="frm-t">请输入您的邮箱,找回您的密码。</h3>
                <p class="frm-tip">  </p>
                  <p></p>
              </div>
              <div class="frm-nav">

                
                <div id="regKindBody"> <div class="frm-bd mp-reg-person">   
                <div class="frm-section">     <div class="section-bd">

                <div class="group frm-control-group extra" id="email_group">         
                <label select="option" class="frm-control-label" for="">邮箱</label>
                     <div class="frm-controls">
                     <input type="text" class="frm-controlM" placeholder="" id="email" name="email" value="">
                     <span id="email_notice" class="desc">&nbsp;</span>
                     </div>
                </div>

                
                
                
                     </div>   </div> </div> </div>
                     <div class="frm-ft">
                         <div class="frm-opr">
                             <a class="btnGreen" id="form-submit" href="javascript:;">继续</a>
               <input type="hidden" name="backurl" value="<?php echo $this->_var['backurl']; ?>" />
               <input type="hidden" name="_submit" id="_submit" value="submit" />
               <input type="hidden" name="formhash" value="<?php echo $this->_var['formhash']; ?>" />
               <input type="hidden" name="ac" value="backpass" />
                         </div>
                     </div>
              </div></div>  
             </form>    
   </div>
</div>
</div>




<?php echo $this->fetch('lib/page_footer.lbi'); ?>


<script type="text/javascript">

$(function(){
    $('#form-submit').click(function(){
	  if(checkform()){	
        $('#form-forget').attr('method','post');
        $('#form-forget').attr('action','forget.php');
        $('#form-forget').submit();
	  }
	});

});



function checkform()
{
  submit_disabled = false;
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
  }
  
  if (!submit_disabled){
	 return true;  
  }else{
	 return false;  
  }
}//end function
</script>
</body></html>