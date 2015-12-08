<?php
/**
* @file editpost.php
* @Brief 
* @author youzu
* @version 1
* @date 2015-04-03
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'post';
$_GET['action'] = 'edit';
$_GET['editsubmit'] = 'yes';
$_GET['extra'] = '';
$_GET['posttime'] = time();
$_GET['wysiwyg'] = 1;
$_GET['usesig'] = 1;
$_GET['allownoticeauthor'] = 1;
include_once 'forum.php';

class BigAppAPI
{
	public function common()
	{
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
	}

	public function output()
	{
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		bigapp_core::result(bigapp_core::variable($G));			
	}

}

?>
