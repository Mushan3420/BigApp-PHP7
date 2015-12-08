<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: pollvote.php 32489 2013-01-29 03:57:16Z monkey $
*/
//note Í¶Æ±´¦Àí @ Discuz! X2.5

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class BigAppAPI {
	function misc_bigapp_message($param) {
		if(isset($param['param'][0]) && 'thread_poll_succeed' === $param['param'][0]){
			bigapp_core::result(bigapp_core::variable(array()));
		}
	}
}

?>
