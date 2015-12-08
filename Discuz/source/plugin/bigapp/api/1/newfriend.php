<?php

/***********************************************************
 * @file:   newfriend.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-07-13 12:16:04
 * @modify: 2015-07-13 12:16:04
 * @brief:  好友�列表
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'friend';
$_GET['op'] = 'request';


include_once 'home.php';


class BigAppAPI {

	public function common() 
    {
	}

	public function output() 
    {
		global $_G;

		$only_count = false;
		if (isset($_GET["only_count"]) && $_GET["only_count"]==1) {
			$only_count = true;
		}
        if ($only_count) {
            $variable = array (
                "count" => $GLOBALS['count'],
            );
            bigapp_core::result(bigapp_core::variable($variable));
			return;
        }

        $variable = array (
            "count" => $GLOBALS['count'],
            //"list" => $GLOBALS['list'],
        	"list" => array_values(mobile_core::getvalues($GLOBALS['list'], array('/^.+?$/'), array('fuid','fusername','note','dateline','gid'))),
            //"groups" => $GLOBALS['groups'],
        );

        $uidarr = array();
		foreach ($variable["list"] as &$item) {
            $uid = $item["fuid"];
            $username = $item["fusername"];
            unset($item["fuid"]);
            unset($item["fusername"]);
            $item["uid"] = $uid;
            $item["username"] = $username;
            $item["avatar"] =  avatar($uid, 'big', 'true');
            $uidarr[] = $uid;
        }
        ////////////////////////////////
        // map group
        $ugmap = BigAppAPI::get_user_group_map($uidarr);
        foreach ($variable["list"] as &$item) { 
            $uid = $item["uid"];
			$item["groupname"] = "unknown";
            if (!isset($ugmap[$uid])) {
                $item["groupid"] = 0;
            } else {
                $gid = $ugmap[$uid];
                $item["groupid"] = $gid;
				if(isset($_G['cache']['usergroups'][$gid]['grouptitle'])){
					$item['groupname'] = preg_replace('/<.*?\>/', '', $_G['cache']['usergroups'][$gid]['grouptitle']);
				}
            }
        }
        ////////////////////////////////


        bigapp_core::result(bigapp_core::variable($variable));
	}

    private function get_user_group_map($uidarr)
    {
		$ugmap  = array();
        if (count($uidarr)==0) return $ugmap;
        $uids = implode(",", $uidarr);
        $sql = "SELECT uid,groupid FROM ".DB::table('common_member')." WHERE uid in ($uids)";
		$subQuery = DB::query($sql);
		while($im = DB::fetch($subQuery)){
			 $ugmap[$im['uid']] = $im['groupid'];
		}
        return $ugmap;
    }
}



// vim600: sw=4 ts=4 fdm=marker syn=php
?>
