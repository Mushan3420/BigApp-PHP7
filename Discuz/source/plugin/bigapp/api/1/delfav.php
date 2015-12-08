<?php
/**
* @file delfav.php
* @Brief delete favarate
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'favorite';
$_GET['op'] = 'delete';
$_GET['type'] = 'all';
$_GET['checkall'] = 1;

include_once 'home.php';

class BigAppAPI {

	function common() {
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
	}

	function output() {
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		$variable = array('messageval' => 'unkown_error', 'messagestr' => 'unkown error');
		if(isset($_G['Message'])){
			$variable = $_G['Message'];
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
