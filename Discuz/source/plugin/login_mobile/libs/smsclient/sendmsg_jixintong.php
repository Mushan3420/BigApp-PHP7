<?php
class SendSMS_Jixintong
{
    public static function send($username,$password,$phone,$content) 
    {
        $url = "http://service.winic.org/sys_port/gateway/?id=%s&pwd=%s&to=%s&content=%s&time=";
		$id  = urlencode("$username");
		$pwd = urlencode("$password");
		$to = urlencode($phone);
		$content = urlencode(iconv("UTF-8","GBK//ignore",$content));
		$rurl = sprintf($url, $id, $pwd, $to, $content);
		//printf("url=%s\n", $rurl);
		$ret = file($rurl);

        runlog("login_mobile", "jixintong_res: ".json_encode($ret));
        $arr = explode("/",$ret[0]);
        $code = intval($arr[0]);
        $retmsg = "succ";
        if($code!=0) $retmsg = "发送失败，请联系短信平台";

        $result = array (
            "retcode" => $code,
            "retmsg"  => $retmsg,
        );
        return $result;
    }
}
?>
