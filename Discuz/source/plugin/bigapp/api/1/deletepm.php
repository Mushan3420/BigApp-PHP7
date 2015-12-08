<?php
/**
* @file deletepm.php
* @Brief 
* @author youzu
* @version 1
* @date 2015-04-08
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';

if(!isset($_GET['inner_pmid'])){
	if(!isset($_GET['deletepm_pmid']) || empty($_GET['deletepm_pmid'])){
		bigapp_core::result(array('error' => 'param_error'));
		exit(0);
	}
	$_GET['deletepm_pmid'] = explode('_', $_GET['deletepm_pmid']);
	if(empty($_GET['deletepm_pmid'])){
		bigapp_core::result(array('error' => 'param_error'));
		exit(0);
	}
	$delPids = array();
	foreach ($_GET['deletepm_pmid'] as $innerId){
		$ret = accessMe($innerId);
		if(isset($ret['Message']['messageval']) && 'delete_pm_success' === $ret['Message']['messageval']){
			$delPids[] = $innerId;
		}
	}
	bigapp_core::result(array('delete_succ_ids' => $delPids));
	exit(0);
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'pm';
$_GET['op'] = 'delete';
$_GET['deletesubmit'] = 1;
$_GET['inajax'] = 1;
$_GET['ajaxtarget'] = '';
$_GET['deletepm_pmid'] = array($_GET['inner_pmid']);
$_GET['handlekey'] = 'pmdeletehk_' . $_GET['deletepm_pmid'][0];

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

};

function prepareCurl(&$ch, $pmid)
{
	global $_G;
	if(true === BigAppConf::$debug){
		$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
	}
	$proto = 'http://';
	if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){
		$proto = 'https://';
	}
	if(isset($_SERVER['SCRIPT_NAME'])){
		$url = $proto . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?';
	}else{
		$url = $proto . $_SERVER['HTTP_HOST'] . '/?';
	}
	if(isset($_SERVER['QUERY_STRING'])){
		$url = $url . $_SERVER['QUERY_STRING'] . '&inner_pmid=' . urlencode($pmid);
	}else{
		$url = $url . 'inner_pmid=' . urlencode($pmid);
	}
	$opt = array(
		CURLOPT_URL     => $url,
		CURLOPT_POST    => 0,
		CURLOPT_HEADER  => 0,
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_TIMEOUT         => 5,
		CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
	);
	if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){
        $opt[CURLOPT_SSL_VERIFYHOST] = 1;
        $opt[CURLOPT_SSL_VERIFYPEER] = FALSE;
    }
	if(isset($_SERVER['HTTP_COOKIE'])){
		$opt[CURLOPT_COOKIE] = $_SERVER['HTTP_COOKIE'];
	}
	curl_setopt_array($ch, $opt);
}

function accessMe($pmid)
{
	global $_G;
	if(true === BigAppConf::$debug){
		$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
	}
	$ch = curl_init();
	prepareCurl($ch, $pmid);
	$data = curl_exec($ch);
	curl_close($ch);
	$ret = BIGAPPJSON::decode($data, true);
	if(!is_array($ret)){
		return 0;
	}
	return $ret;
}

?>
