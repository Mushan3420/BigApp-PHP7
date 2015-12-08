<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__) . '/conf/conf.inc.php';
require_once dirname(__FILE__) . '/libs/env.inc.php';

$paramRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100801, 'error_msg' => 'invalid param', 'show_tips' => '__DONT_DICONV_TO_UTF8___参数错误'));
$authRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'auth failed', 'show_tips' => '__DONT_DICONV_TO_UTF8___必须是管理员帐号才可执行此操作'));
$akskRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed', 'show_tips' => '__DONT_DICONV_TO_UTF8___您尚未在应用设置中填写jpush的appkey或master_secret，无法发送测试消息'));
$svrRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100804, 'error_msg' => 'internal server error', 'show_tips' => '__DONT_DICONV_TO_UTF8___服务器内部错误'));
$aliasRet = array('request_id' => rand(1000000, 10000000000),'error_code' => 100805, 'error_msg' => 'invalid alias', 'show_tips' => '__DONT_DICONV_TO_UTF8___请在网络良好的环境下开启客户端');

header('Content-Type: text/html; charset=utf-8');
if(!isset($_G['groupid']) || 1 != $_G['groupid']){
	echo $authRet;
	die(0);
}
$aksk = BigappEnv::getAkSk();
$ak = $aksk["ak"];
$sk = $aksk["sk"];
$obj = new BkSvr($ak, $sk, 30);
$ret = $obj->getInfo(BigAppConf::$pushUrl, $_GET, false, false);
if(false === $ret || 0 != $ret['error_code']){
	if(100020 == $ret['error_code']){
		$aliasRet['show_tips'] .= '并以' . $_G['username'] . '帐号登录，然后重试';
		$aliasRet = BIGAPPJSON::encode($aliasRet);
		echo $aliasRet;
		die(0);		
	}
	if(100021 == $ret['error_code']){
		echo $akskRet;
		die(0);
	}
	echo $svrRet;
	die(0);
}
$ret['show_tips'] = '__DONT_DICONV_TO_UTF8___测试消息发送成功，稍后您手机应该能收到消息';
echo BIGAPPJSON::encode($ret);
runlog('bigapp', 'send test message succ');
die(0);
?>
