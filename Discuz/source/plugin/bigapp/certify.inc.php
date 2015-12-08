<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define("FILE_PATH", dirname(__FILE__));

require_once FILE_PATH . '/libs/env.inc.php';

if (isset($_REQUEST["ajax"])&&$_REQUEST['ajax']==1) {
    $paramRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'invalid param',));
    $authRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed',));
    $svrRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100800, 'error_msg' => 'internal server error',));
    if(!isset($_G['groupid']) || 1 != $_G['groupid']){
	    echo $authRet;
	    die(0);
    }

    $method = $_REQUEST["method"];
    if ($method=="getkey") {
        $res = BigappEnv::getRemoteAkSk();
        $aksk = array (
            "ak" => "",
            "sk" => "",
        );
        if ($res!==false) {
            $aksk = $res;
        }
		$ret = array (
			"error_code" => 0,
			"error_msg"  => "SUCC",
            "data" => $aksk,
		);
		echo BIGAPPJSON::encode($ret);
        die(0);
    } else {
		$ak = isset($_POST["ak"]) ? $_POST["ak"] : "";
		$sk = isset($_POST["sk"]) ? $_POST["sk"] : "";
		if ($ak=="" || $sk=="") {
			echo $paramRet;
			die(0);   
		}
		Utils::saveLocalAkSk2($ak, $sk);
        ////////////////////////////////////
        $vr = BigappEnv::remoteVerifyAfterSaveAksk();
        $vrint = intval($vr);
        if ($vrint!=0 && $vrint!=1 && $vrint!=2) $vrint=0;
        ////////////////////////////////////
		$ret = array (
			"error_code" => $vrint,
			"error_msg"  => "succ",
		);
		echo BIGAPPJSON::encode($ret);
		die(0);
    }
}

require_once FILE_PATH . '/libs/menu.inc.php';


$str = '';
$str .= '<li>' . sprintf(lang('plugin/bigapp', 'myapp_tip1'), $_G['setting']['plugins']['version']['bigapp'] ) . '</li>';
$str .= '<li>' . lang('plugin/bigapp', 'myapp_tip2') . $_G['setting']['version'] . '</li>';
$str .= '<li>' . lang('plugin/bigapp', 'myapp_tip3') . '</li>';
showtips($str, '', true, $lang['basic_setting']);


$checkinurl = BigAppConf::$mcapis["checkin"];
$apiurl = BigappEnv::getApiUrl();
$sp = strpos($checkinurl,"?")===false ? "?" : "&";
$checkinurl.= $sp."api_url=".urlencode($apiurl);
$checkinurl.= "&verify_info=";

$params = array(
    "ak"=>"",
    "sk"=>"",
    "vertify"=>0,
    "ajaxurl"=>rtrim($_G['siteurl'], '/').'/plugin.php?id=bigapp:certify&ajax=1',
    "checkin"=>$checkinurl,
);
$aksk = BigappEnv::getAkSk();
if ($aksk!==false) {
    $params["ak"] = $aksk["ak"];
    $params["sk"] = $aksk["sk"];

    $appinfo = BigappEnv::getAppInfoFromBigstation();
    if ($appinfo !== false && $appinfo["verified"]==1) {
        $params["vertify"] = 1;
    }

    $params["checkin"].=BigappEnv::getAkSkMd5();
    $params["pack_and_config_url"] = $pack_and_config_url;  //!< defined in libs/menu.inc.php
}

////////////////////////////////////
//$apifile = dirname(dirname(dirname(dirname(__FILE__))))."/api/mobile/";
//$params["api_file_dir"] = $apifile;
//$params["api_file_libs"] = dirname(__FILE__)."/libs/iyz_index.php";
//$params["api_file_exists"] = is_file($apifile."iyz_index.php");


$apifile = "/api/mobile/";
$params["api_file_dir"] = $apifile;
$params["api_file_libs"] = "/source/plugin/bigapp/libs/iyz_index.php";
$params["api_file_exists"] = true; //is_file($apifile."iyz_index.php");
////////////////////////////////////

$tplVars = array(
    "plugin_path"=>BigappEnv::getPluginPath(),
    "myapp" => BigAppConf::$mcapis["myapp"],
    "apiurl" => $apiurl,
);
Utils::loadTemplate(FILE_PATH.'/view/certify.tpl', $params, $tplVars);
runlog('bigapp', 'show certify page succ');
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
