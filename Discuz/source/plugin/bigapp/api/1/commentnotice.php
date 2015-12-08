<?php
/**
* @file comment.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-10-20
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'space';
$_GET['do'] = 'notice';
$_GET['view'] = 'mypost';
$_GET['type'] = 'pcomment';

include_once 'home.php';

class BigAppAPI {

	public function common() 
    {
		global $_G;
	}

	public function output() 
    {
		global $_G;
		$variable = array();
		$variable['list'] = array_values($GLOBALS['list']);
		$variable['count'] = $GLOBALS['count'];
		$variable['need_more'] = $GLOBALS['count'] > $GLOBALS['page']*$GLOBALS['perpage'] ? 1: 0;
		bigapp_core::result(bigapp_core::variable($variable));
	}

}
?>
