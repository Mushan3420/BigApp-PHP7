<?php
/***********************************************************
 * @file:   smilies.php
 * @author: tangyangyu(tangyy@youzu.com)
 * @create: 2015-08-6 15:30:14
 * @brief:  拉取表情map数据
 ***********************************************************/

if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once "forum.php";

class BigAppAPI {

	function common() {
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
	}
	
	
	function output() {
		global $_G;
		
		//如果拉取过，就在db里面标明
		if(isset($_REQUEST['pull']) && isset($_REQUEST['pull']) == '1') {
			$setting = array('bigapp_similes_zip' => '1');
		    C::t('common_setting')->update_batch($setting);
			
			$variable['pull'] = '1';
		} else {			
			if(!isset($_REQUEST['type']) || isset($_REQUEST['type']) && $_REQUEST['type'] == '0') {
				//$type_cnt = 0;
				foreach(C::t('forum_imagetype')->fetch_all_by_type('smiley') as $type) {
					$available = $type['available'];
					if($available == '0')	continue;
					
					$id = $type['typeid'];
					$tmp = array();
					$tmp['name'] = $type['name'];
					$tmp['directory'] = $type['directory'];
					$smiley_cnt = 0;
					
					foreach(C::t('common_smiley')->fetch_all_by_typeid_type($id, 'smiley') as $smiley) {
						$tmp['smiley'][$smiley_cnt]['code'] = $smiley['code'];
						$tmp['smiley'][$smiley_cnt]['url'] = $smiley['url'];
						$tmp['smiley'][$smiley_cnt]['id'] = $smiley['id'];
						
						$smiley_cnt ++;
					}
					
					$variable['smilies'][] = $tmp;
				}
				
			}
		}
		
		bigapp_core::result(bigapp_core::variable($variable));
	}
	

}

?>