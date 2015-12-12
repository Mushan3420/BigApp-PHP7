<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

//1. 请求参数校验
$phone  = DzEnv::get_param("phone",null,'POST');
if (!DzValidate::is_phone($phone)) { 
	DzEnv::error_result("phone_error");
}

//2. 手机号校验
$username = C::t("#login_mobile#mobile_login_connection")->getUserName($phone);
$regist = isset($_REQUEST["regist"]) ? true : false;
if ($regist && $username!==false) {
    DzEnv::error_result("phone_used");
}
if (!$regist && $username===false) {
    DzEnv::error_result("phone_not_regist");
}

//3. 短信平台设置校验
if (!isset($_G['setting']['login_mobile_smsset'])){
    DzEnv::error_result("sms_notset");
}
$appcfg = unserialize($_G['setting']['login_mobile_smsset']);
$smsid    = isset($appcfg["smsid"]) ? $appcfg["smsid"] : "1";
$username = isset($appcfg["username"]) ? $appcfg["username"] : "";
$password = isset($appcfg["password"]) ? $appcfg["password"] : "";

//4. 防攻击校验
$rd = C::t("#login_mobile#mobile_login_seccode")->get_last_record($phone);
if (!empty($rd)) {
    $expire = intval($rd["expire"]);
    $diff = $expire-time();
    if ($diff>=(3600-60)) {
        DzEnv::error_result("sms_wait");
    }
}

//5. 生成验证码
require_once LIB_PATH."/seccode.php";
require_once LIB_PATH.'/sms.php';
$code = DzSecCode::mkcode(4,true);
$content = SendSMS::getSecodeMessage($code);

//6. 发送短信
$res = SendSMS::send($username, $password, $phone, $content, $smsid);
C::t("#login_mobile#mobile_login_seccode")->save($phone,$code);
//7. 返回
DzEnv::result($res);
?>
