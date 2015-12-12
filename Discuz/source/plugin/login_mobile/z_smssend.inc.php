<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';


if (isset($_REQUEST["ajax"])) 
{
    $groupid = $_G["groupid"];
    if ($groupid!=1) {
        DzEnv::result(array("retcode"=>-1,"retmsg"=>"很抱歉，你没有权限进行此操作"));
    }

    switch($_GET["action"]) {
        case "sendmsg":
            $phone = DzEnv::get_param("phone", "");
            $msg   = DzEnv::get_param("msg", "");
			if (!DzValidate::is_phone($phone)) {
				DzEnv::result(array("retcode"=>10001,"retmsg"=>"请输入11位手机号"));
			}
            if ($msg=="") {
				DzEnv::result(array("retcode"=>10001,"retmsg"=>"短信内容不能为空"));
            }
			if (!isset($_G['setting']['login_mobile_smsset'])){
				DzEnv::error_result("sms_notset");
			}
			$appcfg = unserialize($_G['setting']['login_mobile_smsset']);
			$smsid    = isset($appcfg["smsid"]) ? $appcfg["smsid"] : "1";
			$username = isset($appcfg["username"]) ? $appcfg["username"] : "";
			$password = isset($appcfg["password"]) ? $appcfg["password"] : "";
			require_once dirname(__FILE__).'/libs/sms.php';
			$res = SendSMS::send($username, $password, $phone, $msg, $smsid);
            DzEnv::result($res);
            break;
        case "query":
            $res = C::t("#login_mobile#mobile_login_connection")->query2(); 
            DzEnv::result($res);
            break;
        default:
            $res = array("retcode"=>100001,"retmsg"=>"unkown action");
            DzEnv::result($res);
    }
}


/////////////////////////////////////////////////////////////
// show page
/////////////////////////////////////////////////////////////
require_once dirname(__FILE__).'/libs/menu.inc.php';

$params = array(
    "ajaxapi" => DzEnv::getSiteUrl()."/plugin.php?id=login_mobile:z_smssend&ajax=1&",
    //"userapi" => DzEnv::getSiteUrl()."/plugin.php?id=login_mobile:z_userlist&ajax=1&",
);

/*
if (isset($_G['setting']['login_mobile_smsset'])){
	$appcfg = unserialize($_G['setting']['login_mobile_smsset']);
    isset($appcfg["username"]) && $params["username"]=$appcfg["username"];
    isset($appcfg["password"]) && $params["password"]=$appcfg["password"];
}*/

$tplVars = array(
    "plugin_path"=>DzEnv::getPluginPath(),
);
MobileLogin_Utils::loadTemplate(dirname(__FILE__).'/view/z_smssend.tpl', $params, $tplVars);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
