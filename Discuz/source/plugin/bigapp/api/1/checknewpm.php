<?php
/**
* @file checknewpm.php
* @Brief check whether there are new pms
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
define('APPTYPEID', 0);
define('CURSCRIPT', 'member');
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();
loaducenter();
$newpm['newpm'] = intval(uc_pm_checknew($_G['uid']));
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
echo BIGAPPJSON::encode($newpm);
die(0);
?>
