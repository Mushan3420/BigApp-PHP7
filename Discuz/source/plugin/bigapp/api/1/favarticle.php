<?php
/**
* @file favarticle.php
* @Brief favorate a article
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'favorite';
$_GET['type'] = 'article';
include_once 'home.php';

class mobile_api {

	function common() {
	}

	function output() {
		$variable = array();
		
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
		if(!isset($variable['favid']) || empty($variable['favid'])){
            $sql = 'SELECT favid FROM ' . DB::table('home_favorite') . ' WHERE uid = ' . $_G['uid'] . ' AND id = ' . $_GET['id'] . ' AND idtype = \'aid\'';
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
		
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>
