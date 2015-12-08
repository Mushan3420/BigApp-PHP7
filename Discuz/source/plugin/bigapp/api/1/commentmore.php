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

$_GET['mod'] = 'misc';
$_GET['action'] = 'commentmore';
$_GET['tid'] = intval($_GET['tid']);
$_GET['pid'] = intval($_GET['pid']);
$_GET['page'] = isset($_GET['page'])?intval($_GET['page']):1;
$_GET['inajax'] = '1';
$_GET['ajaxtarget'] = 'comment_'.$_GET['pid'];

include_once 'forum.php';

class BigAppAPI {

	public function common() 
    {
		global $_G;
	}

	public function output() 
    {
		global $_G;
		if(!empty($GLOBALS['totalcomment'])){
			$totalcomment = strip_tags($GLOBALS['totalcomment']);
			$info=explode(' ',$totalcomment);
			$data = array();
			foreach($info as $index => $comment){
				if($comment){
					if(is_numeric($comment)){
						$data[$index/2]['score'] = $comment;
					}else{
							$data[$index/2]['option'] = $comment;
					}
				}
			}
			$GLOBALS['totalcomment'] = array_reverse($data);
		}
		$variable = array();
		$variable['comments'] = $GLOBALS['comments'];
		$variable['totalcomment'] = $GLOBALS['totalcomment'];
		$variable['count'] = $GLOBALS['count'];
		$variable['need_more'] = $GLOBALS['count'] > $_GET['page']*$_G['setting']['commentnumber'] ? 1: 0;
		bigapp_core::result(bigapp_core::variable($variable));
	}

}
?>
