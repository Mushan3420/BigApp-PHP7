<?php
/**
* @file myfavarticle.php
* @Brief favorate my article
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'space';
$_GET['do'] = 'favorite';
$_GET['type'] = 'article';
include_once 'home.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$list = array_values($GLOBALS['list']);
		foreach($list as $key=>$value) {
			$list[$key]['dateline'] = date("Y-m-d H:i",$value['dateline']);
			if(isset($value['url'])){
				unset($list[$key]['url']);
			}
			if(isset($value['url'])){
				unset($list[$key]['url']);
			}
			if(isset($value['icon'])){
				unset($list[$key]['icon']);
			}
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
			'need_more' => $GLOBALS['need_more'],
			'count' => $GLOBALS['count'],
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>
