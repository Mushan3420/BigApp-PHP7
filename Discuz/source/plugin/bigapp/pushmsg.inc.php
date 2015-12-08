<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(__FILE__).'/libs/env.inc.php';

if (isset($_REQUEST["ajax"])&&$_REQUEST['ajax']==1)
{
    require_once dirname(__FILE__)."/models/push/JpushClient.php";

    $paramRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'invalid param',));
    $authRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed',));
    $svrRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100800, 'error_msg' => 'internal server error',));
    if(!isset($_G['groupid']) || 1 != $_G['groupid']){
	    echo $authRet;
	    die(0);
    }
    //1. 发送消息
    if ($_REQUEST["action"]=='submit') {
        $params = array (
			"alias" => "all",
            "title" => $_REQUEST["title"],
            "content" => $_REQUEST["msg"],
            "istest" => 0,
        );
        $ret = Bigapp_JpushClient::sendMessage($params);
        echo $ret;
    } 
    //2. 获取消息列表
    else if ($_REQUEST["action"]=='query') {
        $resData = C::t("#bigapp#bigapp_push_message")->query();
        echo BIGAPPJSON::encode(array("data"=>$resData));
    }
    //3. 未知的action
    else {
        echo $paramRet;
    }
    die(0);
}

require_once dirname(__FILE__).'/libs/menu.inc.php';
require_once dirname(__FILE__).'/libs/verify.inc.php';

$params = array(
    "groupid" => isset($_G['groupid']) ? intval($_G['groupid']) : 7,
    "appid" => $appid,
    "api" => BigappEnv::getSiteUrl()."/plugin.php?id=bigapp:pushmsg&ajax=1",
);
$tplVars = array(
    "plugin_path"=>BigappEnv::getPluginPath(),
);
Utils::loadTemplate(dirname(__FILE__).'/view/pushmsg.tpl', $params, $tplVars);
runlog('bigapp', 'show pushmsg page succ');
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
