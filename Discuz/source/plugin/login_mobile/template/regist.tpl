<div class="rfm">
<table>
  <tbody><tr>
    <th><span class="rq">*</span><label for="phone_aKnMp">手机号:</label></th>
    <td><input type="text" id="phone_aKnMp" name="phone_aKnMp" autocomplete="off" size="25" tabindex="1" class="px" value="" required="" onblur="check_phone_aKnMp_field();"><br><em id="emailmore">&nbsp;</em></td>
    <td class="tipcol" id='td_phone_aKnMp'><i id="tip_phone_aKnMp" class="p_tip" style="display: none;">请输入11位手机号码</i><kbd id="chk_phone_aKnMp" class="p_chk"></kbd></td>
  </tr></tbody>
</table>
</div>

<div class="rfm">
<table>
  <tbody><tr>
    <th><span class="rq">*</span><label for="smscode">短信验证码:</label></th>
    <td>
      <input type="text" id="smscode" name="smscode" autocomplete="off" tabindex="1" class="px" value="" required="" style='width:100px;'>
      &nbsp;<span id='smsbtn'><a class='mwtbtn' href="javascript:sendsmscode();">发送短信验证码</a></span>
      <br><em id="emailmore">&nbsp;</em>
    </td>
    <td class="tipcol"></td>
  </tr></tbody>
</table>
</div>

<script src="<%plugin_path%>/view/js/jquery.js"></script>
<script>
  var jq=jQuery.noConflict();
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
      var params={phone:phone,regist:1};
      console.log(params);
      leftseconds=60;
      disable_smsbtn();
      //alert("send");
      var jq=jQuery.noConflict();
      jq.ajax({
          type: "post",
          async: true,
          url: "<%ajax_api%>&module=pcsmscode",
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
</script>
