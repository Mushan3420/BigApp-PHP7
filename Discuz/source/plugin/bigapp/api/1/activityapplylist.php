<?php
/**
* @file activityapplylist.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-10-20
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'activityapplylist';
$_GET['infloat'] = 'yes';
$_GET['handlekey'] = 'activity';
$_GET['inajax'] = 1;

$_config['cookie']['cookiepre'];
$reg = "/^".$_config['cookie']['cookiepre']."(.*)mobile$/i";
foreach($_COOKIE as $key => $val) {
    if (preg_match($reg, $key) && 'no' === $val) {
        setcookie($key, '', 0);
    }   
}
include_once 'forum.php';

class BigAppAPI {

	public function common() 
    {
	}

	public function output()
	{
		if(isset($GET['applylistsubmit']) && 'yes' === $GET['applylistsubmit']){
			BigAppAPI::outputOperation();			
		}else{
			BigAppAPI::outputList();
		}
	}

	public static function outputOperation()
	{
		$variable = array();
		bigapp_core::result(bigapp_core::variable($variable));
	}
	
	public static function outputList() 
    {
		global $_G;
		$variable = array(
			'applylist' => array(),
		);
		if(isset($GLOBALS['applylist']) && !empty($GLOBALS['applylist'])){
			$variable['applylist'] = $GLOBALS['applylist'];
		}
		foreach($variable['applylist'] as &$v){
			$v['can_select'] = 1;
			if($_G['uid'] === $v['uid']){
				$v['can_select'] = 0;
			}
			$v['dateline'] = str_replace('&nbsp;', ' ', $v['dateline']);
			/*$ufielddata = $v['ufielddata'];
			$ufielddata = str_replace('</li><li>', '__s__', $ufielddata);
			$ufielddata = str_replace('<li>', '', $ufielddata);
			$ufielddata = str_replace('</li>', '', $ufielddata);
			$ufielddata = explode('__s__', $ufielddata);
			$v['ufielddata'] = array();
			foreach ($ufielddata as $item){
				$item = explode(':', $item);
				if(is_array($item) && count($item) === 2){
					$k = trim($item[0]);
					$_v = trim($item[1]);
					$k = str_replace('&nbsp;', '', $k);
					$_v = str_replace('&nbsp;', '', $_v);
					$k = preg_replace('/<.*?\>/', '', $k);
					$_v = preg_replace('/<.*?\>/', '', $_v);
					$_v = str_replace("\r\n", ',', $_v);
				}
				$v['ufielddata'][$k] = $_v;
			}
			if(empty($v['ufielddata'])){
				unset($v['ufielddata']);
			}*/
		}
		unset($v);
		bigapp_core::result(bigapp_core::variable($variable));
	}

}
?>
