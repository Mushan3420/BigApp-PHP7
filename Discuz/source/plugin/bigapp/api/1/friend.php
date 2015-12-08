<?php

/***********************************************************
 * @file:   friend.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-07-13 10:39:09
 * @modify: 2015-07-13 10:39:09
 * @brief:  我的好友列表
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class BigAppAPI {

	public function common() 
    {
	}

	public function output() 
    {
		global $_G;
        $variable = array (
            "count" => $GLOBALS['count'],
            //"list" => $GLOBALS['list'],
        	"list" => array_values(mobile_core::getvalues($GLOBALS['list'], array('/^.+?$/'), array('uid','username','gid','groupid','adminid'))),
        );
		foreach ($variable["list"] as &$item) {
            $uid = $item["uid"];
            $gid = $item["groupid"];
            //unset($item["gid"]);
            //$item["groupid"] = $gid;
            $item["groupname"] = "unknown";
			if(isset($_G['cache']['usergroups'][$gid]['grouptitle'])){
				$item['groupname'] = preg_replace('/<.*?\>/', '', $_G['cache']['usergroups'][$gid]['grouptitle']);
			}
            $item["avatar"] =  avatar($uid, 'big', 'true');
        }
        bigapp_core::result(bigapp_core::variable($variable));
	}
}


// vim600: sw=4 ts=4 fdm=marker syn=php
?>
