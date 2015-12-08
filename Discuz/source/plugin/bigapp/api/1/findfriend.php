<?php

/***********************************************************
 * @file:   newfriend.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-07-13 12:16:04
 * @modify: 2015-07-13 12:16:04
 * @brief:  可能认识的人
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'friend';
$_GET['op'] = 'find';
include_once 'home.php';


class BigAppAPI {

	public function common() 
    {
	}

	public function output() 
    {
		global $_G;
        $map = array();
        foreach ($GLOBALS["nearlist"] as $uid => &$item) {
            $map[$uid] = $item;
        }
        foreach ($GLOBALS["onlinelist"] as $uid => &$item) {
            $map[$uid] = $item;
        }

        

        $variable = array (
            "count" => count($map),
            //"list" => $map,
        	"list" => array_values(mobile_core::getvalues($map, array('/^.+?$/'), array('uid','username','groupid'))),
        );

		foreach ($variable["list"] as &$item) {
            $uid = $item["uid"];
            $gid = $item["groupid"];
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
