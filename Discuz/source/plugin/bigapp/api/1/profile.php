<?php
/**
* @file profile.php
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
	}
	function output() {
		global $_G;
		$GLOBALS['space']['avatar'] = '';
		if(isset($GLOBALS['space']['uid']) && !empty($GLOBALS['space']['uid'])){
			$GLOBALS['space']['avatar'] = avatar($GLOBALS['space']['uid'], 'big', true);
			$GLOBALS['space']['avatar'] = str_replace("\r", '', $GLOBALS['space']['avatar']);
			$GLOBALS['space']['avatar'] = str_replace("\n", '', $GLOBALS['space']['avatar']);
		}
		$tmp = bigapp_core::getvalues($GLOBALS['space'], array('uid', 'username', 'regdate', 
				'avatar', 'credits', 'gender', 'posts', 'threads', 'constellation',
                'realname', 'group', 'self', 'friends', 'feedfriend'));
		$tmp['self'] = strval($tmp['self']);
		foreach($_G['setting']['extcredits'] as $idx => $detail){
			if(isset($GLOBALS['space']['extcredits' . $idx])){
				$tmp['extcredits'][] = array('value' => $GLOBALS['space']['extcredits' . $idx], 'name' => $detail['title']);
			}
		}
		if(!isset($tmp['posts'])){
			$tmp['posts'] = 0;
		}
		if(!isset($tmp['threads'])){
			$tmp['threads'] = 0;
		}
		$tmp['posts'] = $tmp['posts'] - $tmp['threads'];
		if($tmp['posts'] < 0){
			$tmp['posts'] = 0;
		}
		$GLOBALS['space'] = $tmp;
		$GLOBALS['space']['group'] = bigapp_core::getvalues($GLOBALS['space']['group'], array('grouptitle'));
		if(empty($GLOBALS['space']['group'])){
			unset($GLOBALS['space']['group']);
		}else{
			$GLOBALS['space']['group']['grouptitle'] = preg_replace('/<.*?\>/', '', $GLOBALS['space']['group']['grouptitle']);
		}
		if(!isset($GLOBALS['space']['constellation'])){
			$GLOBALS['space']['constellation'] = '';
		}

        $GLOBALS['space']['is_my_friend'] = 0;
        if (isset($GLOBALS['space']['feedfriend']) && $GLOBALS['space']['feedfriend']!="") {
            $member_uid = $_G['uid'];
            $feedfriend = $GLOBALS['space']['feedfriend'];
            if ($member_uid == $feedfriend) {
				$GLOBALS['space']['is_my_friend'] = 1;
            } else {
				$arr = explode(",", $feedfriend);
				if (count($arr)>0 && in_array($member_uid, $arr)) {
					$GLOBALS['space']['is_my_friend'] = 1;
				}
			}
        }
	}

}

?>
