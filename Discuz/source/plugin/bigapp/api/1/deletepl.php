<?php
/**
* @file deletepl.php
* @Brief 
* @author youzu
* @version 1
* @date 2015-04-08
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'pm';
$_GET['op'] = 'delete';
$_GET['folder'] = '';
$_GET = array_merge($_GET, $_POST);
if(!isset($_GET['deletepm_deluid']) || empty($_GET['deletepm_deluid'])){
	bigapp_core::result(array('error' => 'param_error'));
	exit(0);
}
$_GET['deletepm_deluid'] = explode('_', $_GET['deletepm_deluid']);
if(empty($_GET['deletepm_deluid'])){
	bigapp_core::result(array('error' => 'param_error'));
	exit(0);
}
if(isset($_POST['deletepm_deluid'])){
	unset($_POST['deletepm_deluid']);
}
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
		$variable = array(
			'pmid' => $GLOBALS['return']
		);
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
