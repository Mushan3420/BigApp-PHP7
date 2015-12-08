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

$params = array (
    //"download_title" => $_POST["download_title"],
    "title"   => $_POST["title"],
    "iosurl"  => $_POST["ios_url"],
    "appdesc" => $_POST["appdesc"],
    "mobile_app_image" => $_POST["mobile_app_image"],
);


// 从taskinfo拉取app_icon和app_name
$plugin_path = rtrim($_G['siteurl'], '/').'/source/plugin/bigapp';
$obj = new BkSvr($ak, $sk, 30);
$url = BigAppConf::$taskInfoUrl;
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
$params["title"] = $params["appname"].$params["title"];
$params["dateline"] = time()+600; //!< 每10分钟拉一次最新打包的配置
//////////////////////////////////////////////////////////////
// 二维码链接生成短地址
$downurl = rtrim($_G['siteurl'], '/').'/plugin.php?id=bigapp:mobile&method=down';
$params["downurl"] = createShortUrl($downurl);
//////////////////////////////////////////////////////////////

$pstr = json_encode($params);
$svalue = str_replace("\\u", "#u", $pstr);
$sql = "INSERT INTO ".DB::table('common_setting')." values ('bigapp_mobile_setting','$svalue') ".
       "ON DUPLICATE KEY UPDATE svalue=values(svalue)";
DB::query($sql);
$ret = array (
    "error_code" => 0,
    "sql" => $svalue,
);

//echo $svalue;

echo BIGAPPJSON::encode($ret);
die(0);



// 发送http请求
function httpRequest($url ,$method = 'GET',$params = null)
{/*{{{*/
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if('POST' == $method){
		curl_setopt($ch, CURLOPT_POST, true);
		if(!empty($params)){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
	}else{
		curl_setopt($ch, CURLOPT_HEADER, false);
	}
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}/*}}}*/

// 生成短地址
function createShortUrl($url, $retry = 3)
{/*{{{*/
	if(empty($url)){
		return $url;
	}
	while($retry > 0) {
		//$dwz = "http://dwz.cn/create.php";
		$dwz = "http://s.youzu.com/gen.php";
		$data=array('url'=>$url);
		$res = httpRequest($dwz , 'POST' ,$data);
		$result =json_decode($res,true);
		$shortUrl = $url;
		if(isset($result['tinyurl'])){
			$shortUrl = $result['tinyurl'];
			break;
		}
		$retry--;
	}
	return $shortUrl;
}/*}}}*/

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
