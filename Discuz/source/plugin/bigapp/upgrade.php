<?php
/**
* @file upgrade.php
* @Brief for upgrade operation, empty now
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('bigapp_checkin') . "` (" .
  "`id` int(11) AUTO_INCREMENT," .
  "`uid` int(4) UNIQUE COMMENT '用户id'," .
  "`date` date DEFAULT NULL COMMENT '签到日期'," .
  "`days` int(4) NOT NULL DEFAULT '1' COMMENT '连续签到天数'," .
  "`score` int(4) DEFAULT '0' COMMENT '用户签到积分'," .
  "PRIMARY KEY (`id`)" .
") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户签到表';";

runquery($sql);

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

$finish = TRUE;
?>

