<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_mobile_login_connection extends discuz_table 
{
	public function __construct() {
		$this->_table = 'mobile_login_connection';
		$this->_pk = 'phone';
		parent::__construct();
	}

    public function getUserName($phone)
    {
        $sql = "SELECT username FROM ".DB::table($this->_table)." WHERE phone='$phone'";
        $query = DB::query($sql);
        if ($row = DB::fetch($query)) {
            return $row["username"];
        }
        return false;
    }

    public function getPhone($username)
    {
        $sql = "SELECT phone FROM ".DB::table($this->_table)." WHERE username='$username'";
        $query = DB::query($sql);
        if ($row = DB::fetch($query)) {
            return $row["phone"];
        }
        return false;
    }

    public function save($phone,$username)
    {
        $data = array (
            "phone" => $phone,
            "username" => $username,
            "addtime" => time(),
        );
        DB::insert($this->_table,$data);
    }

    public function query()
    {
		$return = array(
            "totalProperty" => 0,
            "root" => array(),
        );
        $key    = isset($_REQUEST["key"]) ? iconv("UTF-8", CHARSET."//ignore", $_REQUEST["key"]) : "";
        $status = isset($_REQUEST["status"]) ? $_REQUEST["status"] : 0;  //!< 0:全部,1:已手机认证
        $sort   = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "regdate";
        $dir    = isset($_REQUEST["dir"]) ? $_REQUEST["dir"] : "DESC";
        $start  = isset($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
        $limit  = isset($_REQUEST["limit"]) ? $_REQUEST["limit"] : 20;

        $where = "1";
        if ($key!="") {
            if (DzValidate::is_phone($key)) {
                $where.= " AND b.phone='$key'";
            } else {
				$where.= " AND (a.username like '%$key%')";
            }
        }
        if ($status==0) {
            $where.=" AND isnull(phone)";
        }
        if ($status==1) {
            $where.=" AND !isnull(phone)";
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS a.uid,a.username,a.regdate,b.phone ".
               "FROM ".DB::table("common_member")." as a LEFT JOIN ".DB::table($this->_table)." AS b on a.username=b.username ".
               "WHERE $where ".
               "ORDER BY `$sort` $dir ".
               "LIMIT $start,$limit";
        $query = DB::query($sql);
		while($row = DB::fetch($query)) {
            $row["regdate"] = date("Y-m-d H:i:s", $row["regdate"]);
            $row["username"] = iconv(CHARSET, "UTF-8//ignore", $row["username"]);
			$return["root"][] = $row;
		}
        $query = DB::query("select FOUND_ROWS() AS total");
        if ($row = DB::fetch($query)) {
            $return["totalProperty"] = $row["total"];
        }
        return $return;
    }

    // 用户选择器
	public function query2()
    {
        $return = array(
            "totalProperty" => 0,
            "root" => array(),
        );
        $key    = isset($_REQUEST["key"]) ? iconv("UTF-8", CHARSET."//ignore", $_REQUEST["key"]) : "";
        $status = isset($_REQUEST["status"]) ? $_REQUEST["status"] : -1;
        $sort   = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "regdate";
        $dir    = isset($_REQUEST["dir"]) ? $_REQUEST["dir"] : "DESC";
        $start  = isset($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
        $limit  = isset($_REQUEST["limit"]) ? $_REQUEST["limit"] : 0;

        $where = "1";
		if ($key!="") {
            if (DzValidate::is_phone($key)) {
                $where.= " AND c.phone='$key'";
            } else {
				$where.= " AND (a.username like '%$key%' or b.realname like '%$key%')";
            }
        }
        if ($status==0) {
            $where.=" AND isnull(phone)";
        }
        if ($status==1) {
            $where.=" AND !isnull(phone)";
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS a.uid,a.username,a.email,a.regdate,b.realname,b.address,b.mobile,c.phone ".
               "FROM ".DB::table("common_member")." AS a JOIN ".DB::table("common_member_profile")." as b on a.uid=b.uid ".
               "LEFT JOIN ".DB::table("mobile_login_connection")." AS c on a.username=c.username ".
               "WHERE $where ".
               "ORDER BY `$sort` $dir ";
        if ($limit>0) $sql.= " LIMIT $start,$limit";
        $query = DB::query($sql);
		while($row = DB::fetch($query)) {
            $row["regdate"] = date("Y-m-d H:i:s", $row["regdate"]);
            $row["username"] = iconv(CHARSET, "UTF-8//ignore", $row["username"]);
            $row["realname"] = iconv(CHARSET, "UTF-8//ignore", $row["realname"]);
			$return["root"][] = $row;
		}
        $query = DB::query("select FOUND_ROWS() AS total");
        if ($row = DB::fetch($query)) {
            $return["totalProperty"] = $row["total"];
        }
        return $return;
    }

    public function unbind()
    {
        $return = array (
            "retcode" => 0,
            "retmsg"  => "已解除绑定",
        );
        $phones = explode(",",$_POST["ids"]);
        if (empty($phones)) return $return;
        foreach ($phones as $phone) {
            if (DzValidate::is_phone($phone)) {
                $sql = "DELETE FROM ".DB::table($this->_table)." WHERE phone='$phone'";
                runlog("login_mobile", $sql);
                DB::query($sql);
            }
        }
        return $return;
    }

    public function bind()
    {
        $return = array (
            "retcode" => 0,
            "retmsg"  => "已绑定",
        );
        $phone = $_POST["phone"];
        $uid = intval($_POST["uid"]);
        if (!DzValidate::is_phone($phone) || $uid==0) {
            return array (
                "retcode" => 100001,
                "retmsg"  => "非法的请求参数",
            );
        }
        $im = DB::fetch_first("SELECT username FROM ".DB::table("common_member")." WHERE uid=$uid");
        if (empty($im)) {
            return array (
                "retcode" => 100002,
                "retmsg"  => "用户不存在或已删除",
            );
        }
        $username = $im["username"];
        $im = DB::fetch_first("SELECT username FROM ".DB::table($this->_table)." WHERE phone='$phone'");
        if (!empty($im)) {
            return array (
                "retcode" => 100003,
                "retmsg"  => "该手机号已被绑定",
            );
        }
        $data = array(
            "phone" => $phone,
            "username" => $username,
            "addtime" => time(),
        );
        DB::insert($this->_table, $data);

        return $return;
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
