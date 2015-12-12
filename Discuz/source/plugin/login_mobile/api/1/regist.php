<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

/* 使用短信验证码校验
//1. 校验验证码
require_once LIB_PATH."/seccode.php";
if (!DzSecCode::check()) {
	DzEnv::error_result("error_seccode");
}
*/
//2. 校验请求参数
$phone = DzEnv::get_param("phone","",'POST');
$username = DzEnv::get_param("username",null,"POST");
$password = DzEnv::get_param("password",null,"POST");
$pcode = DzEnv::get_param("pcode",null,'POST');
if (!$username || !$password || !$pcode || !DzValidate::is_phone($phone)) {
	DzEnv::error_result("invalid_param");
}
//3. 校验短信验证码
if (!C::t("#login_mobile#mobile_login_seccode")->check($phone,$pcode)) {
	DzEnv::error_result("error_smscode");
}
//4. 注册
require_once LIB_PATH."/uc.php";
$username = iconv('UTF-8', CHARSET.'//ignore', urldecode($username));
$email = "mob_$phone@null.null";
$profile = array (
    "mobile" => $phone,
);
$uid = DzUc::regist($username,$password,$email,$profile);
if (!is_numeric($uid)) {
	DzEnv::error_result($uid);
}
C::t("#login_mobile#mobile_login_connection")->save($phone,$username);
//5. 注册成功
$result = array (
    "retcode" => 0,
    "retmsg" => lang("plugin/login_mobile","regist_succ"),
    "uid" => $uid,
);
DzEnv::result($result);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
