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

$sql = "drop table  if exists `" . DB::table('mobile_login_connection') . "`";
//runquery($sql);
$sql = "drop table  if exists `" . DB::table('mobile_login_seccode') . "`";
runquery($sql);
$sql = "drop table  if exists `" . DB::table('mobile_login_sms') . "`";
runquery($sql);


$finish = TRUE;
?>
