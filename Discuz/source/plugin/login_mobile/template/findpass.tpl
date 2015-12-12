<?xml version="1.0" encoding="utf-8"?>
<root><![CDATA[
<div id="main_messaqge_LqcK7">
  <div class="rfm bw0" style='width:550px;'></div>
  <div id="layer_lostpw_LqcK7" style="display: none;">
    <h3 class="flb">
      <em id="returnmessage3_LqcK7">找回密码</em>
      <span><a href="javascript:;" class="flbc" onclick="hideWindow('login')" title="关闭">关闭</a></span>
    </h3>
    <div class="c cl">
       <div class="rfm">
        <table>
          <tr>
            <th><span class="rq">*</span><label for="phone_aKnMp">手机号:</label></th>
            <td><input type="text" id="phone_aKnMp" name="phone_aKnMp" autocomplete="off" size="25" tabindex="1" class="px" value="" required="" onblur="check_phone_aKnMp_field();"><br><em id="emailmore">&nbsp;</em></td>
            <td class="tipcol" id='td_phone_aKnMp'>
              <i id="tip_phone_aKnMp" class="p_tip" style="display: none;">请输入11位手机号码</i><kbd id="chk_phone_aKnMp" class="p_chk"></kbd>
            </td>
          </tr>
        </table>
      </div>
      <div class="rfm">
        <table>
          <tr>
            <th><span class="rq">*</span><label for="lostpw_username">短信验证码:</label></th>
            <td><input type="text" name="smscode" id="smscode" size="25" value="" tabindex="1" class="px"></td>
            <td style='padding-left:10px;'><span id='smsbtn'><a class='mwtbtn' href="javascript:sendsmscode();">发送短信验证码</a></span></td>
          </tr>
        </table>
      </div>
	  <div class="rfm">
        <table>
          <tr>
            <th><span class="rq">*</span><label for="lostpw_email">新密码:</label></th>
            <td><input type="password" name="passwd1" id="passwd1" size="25" value="" tabindex="1" class="px"></td>
            <td></td>
          </tr>
        </table>
      </div>
	  <div class="rfm">
        <table>
          <tr>
            <th><span class="rq">*</span><label for="lostpw_email">重复新密码:</label></th>
            <td><input type="password" name="passwd2" id="passwd2" size="25" value="" tabindex="1" class="px"></td>
            <td></td>
          </tr>
        </table>
      </div>
     

      <div class="rfm mbw bw0">
        <table>
          <tr>
            <th></th>
            <td><button class="pn pnc" name="lostpwsubmit" tabindex="100" onclick='submit_pass();'><span>提交</span></button></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>


<script src="<%plugin_path%>/template/jquery-pc.js"></script>
<script type="text/javascript" reload="1">
display('layer_lostpw_LqcK7');
$('phone_aKnMp').focus();

  function mwtHasClass(domid,cls) {
      var dom=document.getElementById(domid);
	  return dom.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
  }
  function mwtAddClass(domid,cls) {
	  if (!mwtHasClass(domid,cls)) 
          document.getElementById(domid).className += " "+cls;
  }
  function mwtRemoveClass(domid,cls) {
	  if (mwtHasClass(domid,cls)) {
		  var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
          var dom=document.getElementById(domid);
		  dom.className=dom.className.replace(reg,' ');
	  }
  }
  function is_phone_number(a)
  {   
      if(!(/^1[1|2|3|4|5|6|7|8|9][0-9]\d{8}$/.test(a))){
		  return false;
	  }   
	  return true;
  }

  function my_trim_string(str) {
      return str.replace(/^\s+/g,"").replace(/\s+$/g,"");
  }

  function check_phone_aKnMp_field() {
      var dom=document.getElementById("phone_aKnMp");
      var dom_tip = document.getElementById("tip_phone_aKnMp");
      var dom_chk =  document.getElementById("chk_phone_aKnMp");
      var dom_td  = document.getElementById("td_phone_aKnMp");
      var value = my_trim_string(dom.value);
      dom.value = value;
	  dom_tip.style.display = "none";
      mwtRemoveClass("td_phone_aKnMp","p_right");
      if (!is_phone_number(value)) {
          dom_chk.innerHTML = "请输入11位手机号码";
          return "";
      }
      dom_chk.innerHTML = "";
      mwtAddClass("td_phone_aKnMp","p_right");
      return value;
  }

  var leftseconds=0;
  function sendsmscode() {
      var phone=check_phone_aKnMp_field();
      if (!is_phone_number(phone)) {
          document.getElementById("phone_aKnMp").focus();
          return;
      }
      var params={phone:phone};
      console.log(params);
      leftseconds=60;
      disable_smsbtn();
      //alert("send");
      var jq=jQuery.noConflict();
      jq.ajax({
          type: "post",
          async: true,
          url: "<%plugin_path%>/index.php?version=4&module=pcsmscode",
		  data: params,
		  dataType: "json",
		  complete: function(res) {
		  },
          success: function (res) {
              if (res.retcode!=0) {
                  leftseconds=0;
                  alert(res.retmsg);
              }
          },
          error: function (data) {
		      leftseconds=0;
		      alert("error: "+data);
          }
      });
  }
  
  function disable_smsbtn() {
      var dom = document.getElementById('smsbtn');
      dom.disabled=true;
      dom.innerHTML = leftseconds+" 秒后重新发送";
      --leftseconds;
      if (leftseconds<=0) {
          dom.innerHTML = "<a href='javascript:sendsmscode();'>发送短信验证码</a>";
          return; 
      }
      setTimeout(disable_smsbtn, 1000);
  }

  function submit_pass()
  {
      var phone=check_phone_aKnMp_field();
      if (!is_phone_number(phone)) {
          document.getElementById("phone_aKnMp").focus();
          return;
      }
      var dom=document.getElementById("smscode");
      var pcode=my_trim_string(dom.value);
      dom.value=pcode;
      if (pcode=="") {
          dom.focus();
          return;
      }
      dom=document.getElementById("passwd1");
      var passwd1=my_trim_string(dom.value);
      dom.value=passwd1;
      if (passwd1=="") {
          dom.focus();
          return;
      }
	  dom=document.getElementById("passwd2");
      var passwd2=my_trim_string(dom.value);
      if(passwd1!=passwd2) {passwd2=""; alert("两次输入的密码不一致");}
      if (passwd2=="") {
		  dom.value="";
          dom.focus();
          return;
      }
      var params = {
          "phone": phone,
          "password": passwd1,
          "pcode": pcode
      };
      console.log(params);
      var jq=jQuery.noConflict();
      jq.ajax({
          type: "post",
          async: true,
          url: "<%plugin_path%>/index.php?version=4&module=resetpass",
		  data: params,
		  dataType: "json",
		  complete: function(res) {
		  },
          success: function (res) {
              if (res.retcode!=0) {
                  alert(res.retmsg);
              } else {
                  alert("您的密码已重置");
                  hideWindow('login');
              }
          },
          error: function (data) {
		      leftseconds=0;
		      alert("error: "+data);
          }
      });
  }
</script>]]></root>
