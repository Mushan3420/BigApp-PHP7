<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_mobile_login_seccode extends discuz_table
{
	public function __construct() {
		$this->_table = 'mobile_login_seccode';
		$this->_pk = 'phone';
		parent::__construct();
	}

    public function save($phone, $code)
    {
        $exptime = time()+3600;
        $sql = "REPLACE INTO ".DB::table("mobile_login_seccode")." (phone,seccode,expire) VALUES ".
               "('$phone','$code',$exptime)";
        DB::query($sql);
    }

    public function check($phone,$pcode)
    {
        $nt = time();
        $sql = "SELECT seccode FROM ".DB::table($this->_table)." WHERE phone='$phone' AND expire>=$nt";
        $query = DB::query($sql);
		if ($row = DB::fetch($query)) {
            return $pcode==$row["seccode"];
        }
        return false;
    }

    public function get_last_record($phone)
    {
        $sql = "SELECT phone,seccode,expire FROM ".DB::table($this->_table)." WHERE phone='$phone'" ;
        return DB::fetch_first($sql); 
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
