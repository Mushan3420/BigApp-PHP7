<?php
if (!defined('IN_DISCUZ')) {
	exit('ACCESS DENIED');
} 
if (!$_G['uid']) return;
$username = $_G["username"];

require_once dirname(__FILE__).'/libs/env.php';

$phone = C::t("#login_mobile#mobile_login_connection")->getPhone($username);


$lang = array (
    'unbind_phone' => '解除手机绑定',
    'bind_phone' => '绑定手机 <span style="color:red">（绑定手机后，你可以使用手机号作为用户名登录）</span>',
    'label_bt' => '必填',
    'label_bind_phone' => '绑定手机号',
    'label_seccode' => '验证码',
    'label_smscode' => '短信验证码',
    'label_smscode_send' => '发送短信验证码',

    'label_hasbind' => '当前绑定的手机号是：',
    'label_btn_bind' => '提交',
    'label_btn_unbind' => '解除手机绑定',
    'label_resend_sms' => '秒后重新发送',
    'label_input_phone' => '请输入正确的手机号码',
    'label_bind_success' => '绑定成功',
    'label_unbind_success' => '已解除手机绑定',
);
foreach ($lang as $k=>&$v) {
    $v = diconv($v, "UTF-8", CHARSET);
}


if ($phone!==false) {
    $tag = "unbind";
    $page_title = $lang["unbind_phone"];  
}
else {
	$tag = "bind";
	$page_title = $lang["bind_phone"];
}

$plugin_path = DzEnv::getPluginPath();
$seccode_url = $plugin_path."/index.php?version=4&module=seccode";
$ajax_api = $plugin_path."/index.php?version=4";

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
