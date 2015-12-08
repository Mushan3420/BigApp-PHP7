<?php
/**
* @file mythread.php
* @Brief 
* @author youzu
* @version 1
* @date 2015-04-03
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
		if(isset($GLOBALS['data']['my']['threadlist']) && is_array($GLOBALS['data']['my']['threadlist'])){
			foreach ($GLOBALS['data']['my']['threadlist'] as $tid => &$value){
				$value['dateline'] = str_replace('&nbsp;', '', $value['dateline']);
				$value['lastpost'] = str_replace('&nbsp;', '', $value['lastpost']);
				if(isset($_G['cache']['forums'][$value['fid']])){
					$value['forum_name'] = $_G['cache']['forums'][$value['fid']]['name'];
				}
			}
		}
		unset($value);
		if(isset($_GET['type']) && 'reply'=== $_GET['type'] && isset($GLOBALS['data']['my']['threadlist']) && is_array($GLOBALS['data']['my']['threadlist'])){
			foreach ($GLOBALS['data']['my']['threadlist'] as $tid => &$value){
				if(!isset($GLOBALS['data']['my']['tids'][$tid]) || empty($GLOBALS['data']['my']['tids'][$tid])){
					$value['details'] = array();
					continue;
				}
				foreach ((array)$GLOBALS['data']['my']['tids'][$tid] as $pid){
					if(isset($GLOBALS['data']['my']['posts'][$pid])){
						$GLOBALS['data']['my']['posts'][$pid]['forum_name'] = '';
						$fid = $GLOBALS['data']['my']['posts'][$pid]['fid'];
						if(isset($_G['cache']['forums'][$fid])){
							$GLOBALS['data']['my']['posts'][$pid]['forum_name'] = $_G['cache']['forums'][$fid]['name'];
						}
						$value['details'][] = $GLOBALS['data']['my']['posts'][$pid];
					}
				}
			}
			/*$variable = array(
				'data' => $GLOBALS['data']['my']['threadlist'], 
				'perpage' => $GLOBALS['perpage'],
			);
			bigapp_core::result($variable);
			*/
		}
	}

}

?>
