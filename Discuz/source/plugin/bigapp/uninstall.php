<?php
/**
* @file uninstall.php
* @Brief uninstall script, empty now
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = "drop table  if exists `" . DB::table('bigapp_checkin') . "`";

runquery($sql);


/*
// 删除表之后所有已绑定的第三方登录（微信）都会失效
$sql = "drop table  if exists `" . DB::table('bigapp_connection') . "`";
runquery($sql);
*/

$finish = TRUE;
?>
