<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';

/* 发送测试短信 */
if (isset($_REQUEST["ajax"])) {
    $params = array (
        "smsid" => DzEnv::get_param("smsid", "1"),
        "phone" => DzEnv::get_param("phone", ""),
        "username" => DzEnv::get_param("username", ""),
        "password" => DzEnv::get_param("password", ""),
        "template1" => DzEnv::get_param("template1", ""),
    );
    if (!DzValidate::is_phone($params["phone"])) {
        DzEnv::result(array("retcode"=>10001,"retmsg"=>"请输入11位手机号"));
    }
    require_once dirname(__FILE__).'/libs/sms.php';
    $content = $params["template1"];
    $result = SendSMS::send($params["username"], $params["password"], $params["phone"], $content, $params["smsid"]);
    $result["params"] = $result;
    DzEnv::result($result);
}

/* 保存设置 */
if (isset($_REQUEST["username"])) {
    $params = array (
        "smsid" => DzEnv::get_param("smsid", "1"),
        "username" => DzEnv::get_param("username", ""),
        "password" => DzEnv::get_param("password", ""),
        "template1" => DzEnv::get_param("template1", ""),
        "template2" => DzEnv::get_param("template2", ""),
    );
    C::t('common_setting')->update_batch(array("login_mobile_smsset"=>$params));
    updatecache('setting');
    $landurl = 'action=plugins&operation=config&do='.$pluginid.'&identifier=login_mobile&pmod=z_smsset';
	cpmsg('plugins_edit_succeed', $landurl, 'succeed');
}

/////////////////////////////////////////////////////////////
// show page
/////////////////////////////////////////////////////////////
require_once dirname(__FILE__).'/libs/menu.inc.php';


$default_sms = 1;
$clients = array (
    array("text"=>"上海维信互动","value"=>2,"desc"=>"您可以前往<a href=\\\"http://www.veesing.com/\\\" target=\\\"_blank\\\">上海维信互动官网</a>申请账号"),
    array("text"=>"莫名短信","value"=>"1","desc"=>"您可以前往<a href=\\\"http://www.duanxin.cm/\\\" target=\\\"_blank\\\">莫名短信官网</a>申请账号"),
    array("text"=>"吉信通","value"=>"3","desc"=>"您可以前往<a href=\\\"http://www.winic.org/\\\" target=\\\"_blank\\\">吉信通官网</a>申请账号"),
);
$list = DzEnv::getinfo($default_sms, $clients);


$params = array(
    "testapi"   => DzEnv::getSiteUrl()."/plugin.php?id=login_mobile:z_smsset&ajax=1",
    "username"  => "",
    "password"  => "",
    "template1" => "这是一条测试短信，请忽略。",
    "template2" => "您的验证码是：【变量】。",
    "list" => $list,
    "smsid" => $default_sms,
    "clients" => $clients,
);

if (isset($_G['setting']['login_mobile_smsset'])){
	$appcfg = unserialize($_G['setting']['login_mobile_smsset']);
    isset($appcfg["smsid"]) && $params["smsid"]=$appcfg["smsid"];
    isset($appcfg["username"]) && $params["username"]=iconv(CHARSET, "UTF-8//ignore", $appcfg["username"]);
    isset($appcfg["password"]) && $params["password"]=$appcfg["password"];
    if (isset($appcfg["template1"]) && $appcfg["template1"]!="") {
        $params["template1"] = iconv(CHARSET, "UTF-8//ignore", $appcfg["template1"]);
    }
    if (isset($appcfg["template2"]) && $appcfg["template2"]!="") {
        $params["template2"] = iconv(CHARSET, "UTF-8//ignore", $appcfg["template2"]);
    }
}

$tplVars = array(
    "plugin_path"=>DzEnv::getPluginPath(),
);
MobileLogin_Utils::loadTemplate(dirname(__FILE__).'/view/z_smsset.tpl', $params, $tplVars);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
