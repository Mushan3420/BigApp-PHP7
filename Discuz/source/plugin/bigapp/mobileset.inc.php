<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define("FILE_PATH", dirname(__FILE__));
require_once FILE_PATH . '/libs/menu.inc.php';
require_once FILE_PATH . '/libs/verify.inc.php';

$svalue  = C::t('common_setting')->fetch("bigapp_mobile_setting",false);
$params  = json_decode($svalue, true);
foreach ($params as &$item) {
	$item = str_replace("#u","\\u", $item);
}
if (!$params["iosurl"]) $params["iosurl"]="";
if (!$params["appdesc"]) $params["appdesc"]="";
$params["ajaxurl"] = rtrim($_G['siteurl'], '/').'/plugin.php?id=bigapp:mobileapi';
$params["mobileurl"] = rtrim($_G['siteurl'], '/').'/plugin.php?id=bigapp:mobile';

$imgUrl = rtrim($_G['siteurl'], '/').'/'.BigAppConf::$upfileUrl.'&key='.urlencode('mobile_app_image_s');
$tplVars = array(
    "plugin_path"=>rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp',
    "imgUrl" => $imgUrl,
);

///////////////////////////////////
updatecache('setting');
if(isset($_G['setting']['bigapp_pcset'])){
	$_G['setting']['bigapp_pcset'] = unserialize($_G['setting']['bigapp_pcset']);
}
$params["ajaxurl2"] = rtrim($_G['siteurl'], '/').'/plugin.php?id=bigapp:pcset&inajax=1';
$params["moburl_switch"] = 0;
$params["moburl"] = "";

if (isset($_G['setting']['bigapp_pcset']['moburl_switch'])) {
    $params["moburl_switch"] = $_G['setting']['bigapp_pcset']['moburl_switch'];
}
if (isset($_G['setting']['bigapp_pcset']['moburl'])) {
    $params["moburl"] = $_G['setting']['bigapp_pcset']['moburl'];
}
///////////////////////////////////

Utils::loadTemplate(FILE_PATH.'/view/mobileset.tpl', $params, $tplVars);

runlog('bigapp', 'show release page succ');

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
