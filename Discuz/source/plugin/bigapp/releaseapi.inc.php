<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . '/libs/env.inc.php';

$paramRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'invalid param',));
$authRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed',));
$svrRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100800, 'error_msg' => 'internal server error',));
if(!isset($_G['groupid']) || 1 != $_G['groupid']){
	echo $authRet;
	die(0);
}
if(!isset($_GET['method']) || !isset(BigAppConf::$releaseApis[$_GET['method']])){
	echo $paramRet;
	die(0);
}
$url = BigAppConf::$releaseApis[$_GET['method']];
$params = array();
switch($_GET['method']){
    case "release_versions":
        $params = array(
            "start" => isset($_REQUEST["start"]) ? intval($_REQUEST["start"]) : 0,
            "limit" => isset($_REQUEST["limit"]) ? intval($_REQUEST["limit"]) : 10,
        );
        break;
    case "release":
        $rmsg = isset($_REQUEST["releasemsg"]) ? $_REQUEST["releasemsg"] : "";
        if(function_exists('iconv')){
            $releasemsg = iconv("UTF-8", CHARSET.'//ignore', $rmsg);
        }else{
            $releasemsg = mb_convert_encoding($rmsg, CHARSET, 'UTF-8');
        }
        $params = array(
            "os"          => isset($_REQUEST["os"]) ? intval($_REQUEST["os"]) : 0,
            "version"     => isset($_REQUEST["version"]) ? $_REQUEST["version"] : "",
            "releasemsg"  => $releasemsg,
            "upgrademode" => isset($_REQUEST["upgrademode"]) ? intval($_REQUEST["upgrademode"]) : 0,
            "taskid"      => isset($_REQUEST["taskid"]) ? $_REQUEST["taskid"] : "",
            "downlink"    => isset($_REQUEST["downlink"]) ? $_REQUEST["downlink"] : "",
            "charset"     => CHARSET,
        );
        break;
    case "mid_page":
        $params = array (
            "url" => isset($_REQUEST["url"]) ? $_REQUEST["url"] : "",
        );
        break;
    case "new_versions":
    case "latest_version":
    default:
        break;
}
$obj = new BkSvr($ak, $sk, 30);
$ret = $obj->getInfo($url, $params, false);
if(false === $ret){
	echo $svrRet;
	die(0);
}
//$ret = $params;

//////////////////////////////////////////////
// GBK编码问题
if ($_GET['method']=="release_versions") {
    if (isset($ret["data"]) && isset($ret["data"]["root"]) && count($ret["data"]["root"])>0) {
        foreach ($ret["data"]["root"] as &$item) {
            if(function_exists('iconv')){
                $item["releasemsg"] = iconv('UTF-8', CHARSET.'//ignore', $item["releasemsg"]);
            }else{
                $item["releasemsg"] = mb_convert_encoding($item["releasemsg"], CHARSET, 'UTF-8');
            }
        }
    }
}
//////////////////////////////////////////////

echo BIGAPPJSON::encode($ret);
die(0);

?>
