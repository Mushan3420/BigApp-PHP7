<?php

/***********************************************************
 * @file:   table_bigapp_push_message.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-11-16 17:08:16
 * @modify: 2015-11-16 17:08:16
 * @brief:  table_bigapp_push_message.php
 ***********************************************************/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_bigapp_push_message extends discuz_table {

	private $_fields;

	public function __construct() {
		$this->_table = 'bigapp_push_message';
		$this->_pk = 'id';
		$this->_fields = array('id','toalias','msgtype','msgmask','msgtitle','msg','extra','isdel','addtime');
		$this->_pre_cache_key = 'bigapp_push_message_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}

    public function query()
    {
        $return = array(
            "totalProperty" => 0,
            "root" => array(),
        );
        $key    = isset($_REQUEST["key"]) ? mysql_real_escape_string($_REQUEST["key"]) : "";
        $sort   = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "addtime";
        $dir    = isset($_REQUEST["dir"]) ? $_REQUEST["dir"] : "DESC";
        $start  = isset($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
        $limit  = isset($_REQUEST["limit"]) ? $_REQUEST["limit"] : 10;
        $where = "1";
        if ($key!="") $where.= " AND (msgtitle like '%$key%' or msg like '%$key%')";
		$sql = "SELECT SQL_CALC_FOUND_ROWS toalias,msgtitle,msg,istest,addtime ".
               "FROM ".DB::table($this->_table)." ".
               "WHERE $where ".
               "ORDER BY `$sort` $dir";
        if ($limit>0) $sql.= " LIMIT $start,$limit";
        $query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$return["root"][] = $row;
		}
        $query = DB::query("select FOUND_ROWS() AS total");
        if ($row = DB::fetch($query)) {
            $return["totalProperty"] = $row["total"];
        }

        global $_G;
        $charset = $_G['charset'];
        foreach ($return["root"] as &$item) {
            $item["sendtime"] = date("Y-m-d H:i:s", $item["addtime"]);
            //$item["msgtitle"] = iconv($charset, "utf-8//ignore", $item["msgtitle"]);
            //$item["msg"] = iconv($charset, "utf-8//ignore", $item["msg"]);
            unset($item["addtime"]);
        }

        return $return;
    }

    public function save(array &$item) 
    {
        global $_G;
        $charset = $_G['charset'];
        $data = array (
            "id" => 0,
			"toalias" => $item["alias"],
			"msgtype" => $item["message_type"],
			"msgmask" => $item["mask"],
			"msgtitle" => iconv("utf-8", $charset."//ignore", $item["title"]),
			"msg" => iconv("utf-8", $charset."//ignore", $item["content"]),
			"extra" => $item["extra"],
            "istest" => $item["istest"],
			"addtime" => time(),
        );
        DB::insert($this->_table, $data);
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
