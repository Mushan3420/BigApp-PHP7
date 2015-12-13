<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();

$username = $_G["username"];
$action = $_REQUEST["action"];
$res = array (
	"retcode" => 100002,
	"retmsg"  => "unknow action"
);
switch ($action) {
	case 'bind':
        // 校验请求参数
        $phone = DzEnv::get_param("phone","",'POST');
        $pcode = DzEnv::get_param("pcode",null,'POST');
        if (!$pcode || !DzValidate::is_phone($phone)) {
			DzEnv::error_result("invalid_param");
		}
		// 校验短信验证码
		if (!C::t("#login_mobile#mobile_login_seccode")->check($phone,$pcode)) {
			DzEnv::error_result("error_smscode");
		}
        // 绑定手机号
        $_POST["phone"] = $phone;
        $_POST["uid"]   = $_G['uid'];
        $res = C::t("#login_mobile#mobile_login_connection")->bind();
        break;
	case 'unbind':
        // 校验验证码
        if (isset($_REQUEST["seccode"])) {
		    require_once LIB_PATH."/seccode.php";
		    if (!DzSecCode::check()) {
			    DzEnv::error_result("error_seccode");
		    }
        }
        // 解除绑定
		$phone = C::t("#login_mobile#mobile_login_connection")->getPhone($username);
		if ($phone!==false) {
			$_POST['ids'] = $phone;
			$res = C::t("#login_mobile#mobile_login_connection")->unbind();
		} else {
			$res["retcode"] = 0;
		}
		break;
	default: break;
}
DzEnv::result($res);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
