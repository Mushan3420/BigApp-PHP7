<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$pName = 'bigapp';
//1. try to enable mobile plugin if it already exists
$sql = 'UPDATE '  . DB::table('common_plugin') . " SET available = 1 WHERE identifier = 'mobile'";
runquery($sql);
@include_once libfile('cache/setting', 'function');
build_cache_setting();

//2. check whether mobile plugin has been installed
loadcache('setting');
if(!isset($_G['setting']['plugins']['available']) || !is_array($_G['setting']['plugins']['available']) || !in_array('mobile', $_G['setting']['plugins']['available'])){
	showmessage($installlang['mobile_not_exists']);
	$finish = FALSE;
	return ;
}
updatecache(array('mobile:mobile'));
//3. enable mobile access
$sql = 'SELECT svalue FROM ' . DB::table('common_setting') . " WHERE skey = 'mobile'";
$dbRet = DB::fetch_first($sql);
if(!isset($dbRet['svalue'])){
	showmessage($installlang['mobile_is_broken']);
	$finish = FALSE;
	return ;
}
$arrConf = @unserialize($dbRet['svalue']);
if(!is_array($arrConf) || !isset($arrConf['allowmobile'])){
	showmessage($installlang['mobile_is_broken']);
	$finish = FALSE;
	return ;
}
$arrConf['allowmobile'] = 1;
$sValue = serialize($arrConf);
$sValue = $this->link->escape_string($sValue);
$sql = 'UPDATE ' . DB::table('common_setting') . " SET svalue = '$sValue' WHERE skey = 'mobile'";
runquery($sql);
build_cache_setting();

$sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('bigapp_checkin') . "` (" .
  "`id`	int(11) AUTO_INCREMENT," .
  "`uid` int(4) UNIQUE COMMENT '用户id'," .
  "`date` date DEFAULT NULL COMMENT '签到日期'," .
  "`days` int(4) NOT NULL DEFAULT '1' COMMENT '连续签到天数'," .
  "`score` int(4) DEFAULT '0' COMMENT '用户签到积分'," .
  "PRIMARY KEY (`id`)" . 
") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户签到表';";

runquery($sql);

///////////////////////////////////////////////

$sql = "CREATE TABLE IF NOT EXISTS `".DB::table('bigapp_connection')."` (".
  "`id` int(12) unsigned NOT NULL AUTO_INCREMENT,".
  "`uid` mediumint(8) unsigned NOT NULL,".
  "`openid` char(32) NOT NULL DEFAULT '',".
  "`platid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1:wechart,2:sina_weibo',".
  "`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:OK',".
  "`param` text NOT NULL DEFAULT '',".
  "PRIMARY KEY (`id`),".
  "UNIQUE KEY `uk_uid_platid` (`uid`,`platid`),".
  "UNIQUE KEY `uk_openid_platid` (`openid`,`platid`)".
") ENGINE=InnoDB DEFAULT CHARSET=utf8";

runquery($sql);

///////////////////////////////////////////////

$sql = "CREATE TABLE IF NOT EXISTS `".DB::table('bigapp_push_message')."` (".
  "`id` int(12) unsigned NOT NULL AUTO_INCREMENT,".
  "`toalias` varchar(40) NOT NULL DEFAULT '',".
  "`msgtype` tinyint(2) unsigned NOT NULL DEFAULT '1',".
  "`msgmask` tinyint(1) unsigned NOT NULL DEFAULT '3',".
  "`msgtitle` varchar(64) NOT NULL,".
  "`msg` varchar(128) NOT NULL DEFAULT '',".
  "`extra` varchar(1024) NOT NULL DEFAULT '',".
  "`istest` tinyint(1) unsigned NOT NULL DEFAULT '1',".
  "`addtime` int(10) unsigned NOT NULL DEFAULT '0',".
  "PRIMARY KEY (`id`)".
") ENGINE=InnoDB DEFAULT CHARSET=utf8";

runquery($sql);

///////////////////////////////////////////////
$srcFile = dirname(__FILE__) . '/repfiles/iyz_index.php';
$destFile = dirname(dirname(dirname(dirname(__FILE__)))) . '/api/mobile/iyz_index.php';
$input = @file_get_contents($srcFile);
if(!file_exists($destFile) && !empty($input)){
	@file_put_contents($destFile, $input);
}

$finish = TRUE;
?>
