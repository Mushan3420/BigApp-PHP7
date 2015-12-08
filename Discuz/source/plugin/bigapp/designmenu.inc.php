<?php 

global $_G;
define("FILE_PATH", dirname(__FILE__));

require_once FILE_PATH . '/conf/conf.inc.php';
require_once FILE_PATH . '/libs/appdesign.inc.php';
require_once FILE_PATH . '/bigappjson.class.php';
require_once FILE_PATH . "/libs/env.inc.php";

//REQUEST_METHOD_DOMAIN
AppDesign::makeCors($_SERVER['REQUEST_METHOD'], '*');

if(isset($_GET["method"]) && $_GET["method"] == "get") {
	//视图
	
	$appinfo = BigappEnv::getAppInfoFromBigstation();
	$appid = 0;
	if (isset($appinfo["app_id"])) $appid = $appinfo["app_id"];
	$pack_and_config_url = rtrim(BigAppConf::$mcapis["app"],"/")."/".$appid;
	
	$result['code'] = '0';
	$result['msg'] = 'Succ';
	$result['data'] = $pack_and_config_url;
	
	echo BIGAPPJSON::encode($result);
	die(0);
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
