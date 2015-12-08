<?php
/**
* @file checkpost.php
* @Brief for check post
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class BigAppAPI{
	function common() {
		global $_G;
		if(!isset($_GET['fid']) && isset($_GET['tid'])){
			$sql = 'SELECT fid FROM ' . DB::table('forum_thread') . ' WHERE tid = ' . intval($_GET['tid']);
			$query = DB::query($sql);
			$fid = null;
			while($tmp = DB::fetch($query)){
				$fid = $tmp['fid'];
				break;
			}
			loadforum($fid, null);
		}
		$apifile = dirname(__FILE__).'/sub_checkpost.php';
		if(file_exists($apifile)) {
			require_once $apifile;
		}
		bigapp_core::result(bigapp_core::variable(bigapp_api_sub::getvariable()));
	}
	function output() {}
}

?>
