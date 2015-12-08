<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define("FILE_PATH", dirname(__FILE__));

require_once FILE_PATH . '/libs/env.inc.php';

///////////////////////////////////////////////////////
// 获取不到aksk时，跳转到站长认证页面进行设置
$aksk = BigappEnv::getAkSk();
if ($aksk===false) {
    header("Location: admin.php?action=plugins&operation=config&do=23&identifier=bigapp&pmod=certify");
    die(0);
}
$ak = $aksk["ak"];
$sk = $aksk["sk"];
///////////////////////////////////////////////////////

$tplVars = array();
$svalue  = C::t('common_setting')->fetch("bigapp_mobile_setting",false);
$params  = json_decode($svalue, true);
foreach ($params as &$item) {
	$item = str_replace("#u","\\u", $item);
}

//////////////////////////////////////////////
$tm = time();
$dateline = $params["dateline"]?$params["dateline"]:0;
if ($dateline<$tm) {
    // 从taskinfo拉取app_icon和app_name
	$plugin_path = rtrim($_G['siteurl'], '/').'/source/plugin/bigapp';
	$obj = new BkSvr($ak, $sk, 30);
	$url = BigAppConf::$taskInfoUrl;
    $appInfo = BigappEnv::getAppInfoFromBigstation();
    $app_id = isset($appInfo['app_id']) ? $appInfo['app_id'] : 0;
	$ret = $obj->getInfo($url, array('app_id'=>$app_id, 'method'=>'get_latest'), false);
	if(false !== $ret && isset($ret["data"])){
		$params["icon_img"] = $ret["data"]["task_info"]["icon_image"];
		$params["appname"] = $ret["data"]["task_info"]["app_name"];
	}
	if ($params["icon_img"]=="") {
		$params["icon_img"] = $plugin_path."/static/logo.png";
	}
	if ($params["appname"]=="") $params["appname"] = "bigapp";
	//$params["title"] = $params["appname"].$params["title"];
	$params["dateline"] = time()+600; //!< 每10分钟拉一次最新打包的配置

	$pstr = json_encode($params);
	$svalue = str_replace("\\u", "#u", $pstr);
	$sql = "INSERT INTO ".DB::table('common_setting')." values ('bigapp_mobile_setting','$svalue') ".
		"ON DUPLICATE KEY UPDATE svalue=values(svalue)";
	DB::query($sql);

    ////////////////////////////////////////////////
    // appname乱码问题解决
    if(function_exists('iconv')){                                                                    
        $params["appname"] = iconv('UTF-8', CHARSET . '//ignore', $params["appname"]);  
    }else{
        $params["appname"] = mb_convert_encoding($params["appname"], CHARSET, 'UTF-8');
    }   
    //$params["title"] = $params["appname"].$params["title"];
    ////////////////////////////////////////////////
}
//////////////////////////////////////////////


$latest_pkgurl = BigAppConf::$releaseApis["latest_package"];
$con = strpos($latest_pkgurl, "?")===false ? "?" : "&";
$latest_pkgurl.= $con."app_key=$ak&os=1";
$tplVars["androidurl"] = $latest_pkgurl;

if (isset($_GET["method"]) && $_GET["method"]=="down") {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
        $url = $params["iosurl"];
        //echo 'systerm is IOS';
        header("Location: $url");
    }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
        $latest_pkgurl = $tplVars["androidurl"];
		/*
		$latest_pkgurl = BigAppConf::$releaseApis["latest_package"];
		$con = strpos($latest_pkgurl, "?")===false ? "?" : "&";
		$latest_pkgurl.= $con."app_key=$ak&os=1";
		//echo "<a href='$latest_pkgurl";
        */
        header("Location: $latest_pkgurl");
    }else{
        echo 'please open in mobile device!';
    }
    die(0);
}

$tplVars["plugin_path"] = rtrim($_G['siteurl'], '/').'/source/plugin/bigapp';
$tplVars["icon_img"] = $params["icon_img"];
$tplVars["mobile_app_img"] = $params["mobile_app_image"];
if ($tplVars["mobile_app_img"] == "") {
    $tplVars["mobile_app_img"] = $tplVars["plugin_path"]."/static/preview.png";
}
$tplVars["iosurl"] = $params["iosurl"];
Utils::loadTemplate(FILE_PATH.'/view/mobile.tpl', $params, $tplVars);

runlog('bigapp', 'show release page succ');

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
