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
$_GET['mod'] = 'space';
$_GET['do'] = 'thread';
$_GET['view'] = 'me';
$_GET['from'] = 'space';
$_GET['order'] = 'dateline';
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
		global $GLOBALS;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		$GLOBALS['list'] = (array)$GLOBALS['list'];
		foreach ($GLOBALS['list'] as &$value){
			$value['dateline'] = str_replace('&nbsp;', '', $value['dateline']);	
			$value['dateline'] = preg_replace('/<.*?\>/', '', $value['dateline']);
			$value['lastpost'] = str_replace('&nbsp;', '', $value['lastpost']);		
			$value['lastpost'] = preg_replace('/<.*?\>/', '', $value['lastpost']);
			$value['forum_name'] = $value['forumname'];		
			unset($value['forumname']);
		}
		unset($value);
		$GLOBALS['list'] = bigapp_core::getvalues($GLOBALS['list'], array('/^\d+$/'), array('author', 'forum_name', 'message', 
				'pid', 'subject', 'dateline', 'tid', 'replies', 'views', 'avatar', 'lastpost', 'lastposter', 'authorid', 'fid'));
		$GLOBALS['posts'] = (array)$GLOBALS['posts'];
		foreach ($GLOBALS['posts'] as &$value){
			$value['dateline'] = date('Y-m-d H:i:s', $value['dateline']);
		}
		$GLOBALS['posts'] = bigapp_core::getvalues($GLOBALS['posts'], array('/^\d+$/'),  array('author', 'forum_name', 'message', 
				'pid', 'subject', 'dateline', 'tid', 'replies', 'views', 'avatar', 'lastpost', 'lastposter', 'authorid', 'fid'));
		unset($value);
		if(!empty($_GET['uid'])){
			$variable['avatar'] = avatar($_GET['uid'], 'big', 'true');
		}else{
			$variable['avatar'] = avatar($_G['uid'], 'big', 'true');	
		}
		$variable['avatar'] = str_replace("\r", '', $variable['avatar']);
		$variable['avatar'] = str_replace("\n", '', $variable['avatar']);
		$variable['data'] = (array)$GLOBALS['list'];
		foreach ($GLOBALS['posts'] as $value){
			if(isset($variable['data'][$value['tid']])){
				$variable['data'][$value['tid']]['details'][] = $value;
			}
		}
		
		$variable['data'] = array_values($variable['data']);
		bigapp_core::result($variable);
	}

}

?>
