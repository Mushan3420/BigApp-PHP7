<?php
/**
* @file favforum.php
* @Brief favorate a forum
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

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
		$variable = array();
		if(isset($_G['messageparam'])){
			foreach ($_G['messageparam'] as $value){
				if(is_array($value) && isset($value['favid'])){
					$variable['favid'] = $value['favid'];
					break;
				}
			}
		}
		if(!isset($variable['favid'])){
			$sql = 'SELECT favid FROM ' . DB::table('home_favorite') . ' WHERE uid = ' . $_G['uid'] . ' AND id = ' . $_GET['id'] . ' AND idtype = \'fid\'';
			$query = DB::query($sql);
			while($dbRet = DB::fetch($query)){
				if(isset($dbRet['favid'])){
					$variable['favid'] = $dbRet['favid'];
					break;
				}
			}
		}
		if(!isset($variable['favid'])){
			$variable['favid'] = 0;
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
