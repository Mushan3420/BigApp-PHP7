<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_mobile_login_sms extends discuz_table
{
	public function __construct() {
		$this->_table = 'mobile_login_sms';
		$this->_pk = 'id';
		parent::__construct();
	}

    public function query()
    {
        $return = array(
            "totalProperty" => 0,
            "root" => array(),
        );
        $key    = isset($_REQUEST["key"]) ? $_REQUEST["key"] : "";
        $sort   = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "sendtime";
        $dir    = isset($_REQUEST["dir"]) ? $_REQUEST["dir"] : "DESC";
        $start  = isset($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
        $limit  = isset($_REQUEST["limit"]) ? $_REQUEST["limit"] : 0;
        $where = "1";
        if ($key!="") $where.= " AND (phone like '%$key%' or msg like '%$key%')";

        $sql = "SELECT SQL_CALC_FOUND_ROWS * ".
               "FROM ".DB::table($this->_table)." ".
               "WHERE $where ".
               "ORDER BY `$sort` $dir";
        if ($limit>0) $sql.= " LIMIT $start,$limit";
        $query = DB::query($sql);
		while($row = DB::fetch($query)) {
            $row["sendtime"] = date("Y-m-d H:i:s", $row["sendtime"]);
            $row["msg"] = iconv(CHARSET, "UTF-8//ignore", $row["msg"]);
			$return["root"][] = $row;
		}
        $query = DB::query("select FOUND_ROWS() AS total");
        if ($row = DB::fetch($query)) {
            $return["totalProperty"] = $row["total"];
        }
        return $return;
    }


    public function save($phone, $msg)
    {
        $sendtime = time();
        $data = array (
            "id" => 0,
            "phone" => $phone,
            "msg" => iconv("UTF-8",CHARSET."//ignore",$msg),
            "sendtime" => $sendtime,
        );
        DB::insert($this->_table, $data);
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
