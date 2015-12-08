<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define("FILE_PATH", dirname(__FILE__));

require_once FILE_PATH . '/libs/env.inc.php';
require_once FILE_PATH . '/libs/appcfg.inc.php';

if (isset($_REQUEST["ajax"])&&$_REQUEST['ajax']==1) {
    $paramRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'invalid param',));
    $authRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed',));
    $svrRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100800, 'error_msg' => 'internal server error',));
    if(!isset($_G['groupid']) || 1 != $_G['groupid']){
	    echo $authRet;
	    die(0);
    }
    $params = array (
        "qq_login"     => $_POST["qq_login"],
        "wechat_login" => $_POST["wechat_login"],
        "weibo_login"  => $_POST["weibo_login"],
	);
    saveAppConfigure($params);
	$ret = array (
		"error_code" => 0,
		"setting" => BIGAPPJSON::encode($params),
	);
	echo BIGAPPJSON::encode($ret);
	die(0);
}

require_once FILE_PATH . '/libs/menu.inc.php';

$params = getAppConfigure();

$params["ajaxurl"] = rtrim($_G['siteurl'], '/').'/plugin.php?id=bigapp:appcfg&ajax=1';
$tplVars = array(
    "plugin_path"=>rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp',
);
Utils::loadTemplate(FILE_PATH.'/view/appcfg.tpl', $params, $tplVars);

runlog('bigapp', 'show appcfg page succ');
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
