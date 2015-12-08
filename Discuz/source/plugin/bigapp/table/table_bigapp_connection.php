<?php

/***********************************************************
 * @file:   table_bigapp_connection.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-08-24 15:05:00
 * @modify: 2015-08-24 15:05:00
 * @brief:  table_bigapp_connection.php
 ***********************************************************/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_bigapp_connection extends discuz_table {

	private $_fields;

	public function __construct() {
		$this->_table = 'bigapp_connection';
		$this->_pk = 'id';
		$this->_fields = array('id', 'uid', 'openid', 'platid', 'status', 'param');
		$this->_pre_cache_key = 'bigapp_connection_';
		$this->_cache_ttl = 0;

		parent::__construct();
	}

	public function fetch_fields_by_openid_platid($openid, $platid, $fields = array()) {
		$fields = (array)$fields;
		if(!empty($fields)) {
			$field = implode(',', array_intersect($fields, $this->_fields));
		} else {
			$field = '*';
		}
		return DB::fetch_first('SELECT %i FROM %t WHERE openid=%s and platid=%d and status=0',
                   array($field, $this->_table, $openid, $platid));
	}

    public function fetch_fields_by_uid_platid($uid, $platid, $fields=array()) {
		$fields = (array)$fields;
		if(!empty($fields)) {
			$field = implode(',', array_intersect($fields, $this->_fields));
		} else {
			$field = '*';
		}
		return DB::fetch_first('SELECT %i FROM %t WHERE uid=%d and platid=%d and status=0',
                   array($field, $this->_table, $uid, $platid));
    }

    public function save(array &$item) {
        $uid = $item["uid"];
        $openid = $item["openid"];
        $platid = $item["platid"];
        $status = $item["status"];
        $param  = $item["param"];
        $sql = "INSERT INTO ".DB::table($this->_table)." (uid,openid,platid,status,param) VALUES ".
               "($uid, '$openid', $platid, $status, '$param') ".
               "ON DUPLICATE KEY UPDATE status=values(status), param=values(param)";
        DB::query($sql);
    }

/*
	public function update_by_openid($openId, $data) {

		return DB::update($this->_table, $data, DB::field('openId', $openId));
	}
*/
}


// vim600: sw=4 ts=4 fdm=marker syn=php
