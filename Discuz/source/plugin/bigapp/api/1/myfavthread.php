<?php
/**
* @file myfavthread.php
* @Brief 
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
		$list = array_values($GLOBALS['list']);
		$tids = array();
		foreach($list as $key=>$value) {
			$tids[] = $value['id'];
		}
		if($tids) {
			$threadinfo = C::t('forum_thread')->fetch_all($tids);
		}
		foreach($list as $key=>$value) {
			$list[$key]['replies'] = $threadinfo[$value['id']]['replies'];
			$list[$key]['author'] = $threadinfo[$value['id']]['author'];
		}
		if(!isset($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] <= 0){
			$_GET['page'] = 1;
		}
		$start = $GLOBALS['perpage'] * ($_GET['page'] - 1);
		$end = count($GLOBALS['list']) + $start;
		if($end >= $GLOBALS['count']){
			$GLOBALS['need_more'] = 0;
		}else{
			$GLOBALS['need_more'] = 1;
		}
		$variable = array(
			'list' => $list,
			'perpage' => $GLOBALS['perpage'],
			'count' => $GLOBALS['count'],
			'need_more' => $GLOBALS['need_more'],
		);
		bigapp_core::result(bigapp_core::variable($variable));
	}

}

?>
