<?php

/***********************************************************
 * @file:   newthread.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-10-13 22:16:04
 * @modify: 2015-10-13 22:16:04
 * @brief:  ·¢±íÌû×Ó
 ***********************************************************/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class BigAppAPI {

	public function common() 
    {
        $_POST["mobiletype"] = 7;
	}

	public function output() 
    {
    }
}



// vim600: sw=4 ts=4 fdm=marker syn=php
?>
