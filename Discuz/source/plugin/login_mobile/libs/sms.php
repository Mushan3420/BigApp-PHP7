<?php
class SendSMS
{
    public static $clients = array ();

    // 获取验证码短信
    public static function getSecodeMessage($code)
    {
        global $_G;
        $template = "您的验证码是：【变量】";
		if (isset($_G['setting']['login_mobile_smsset'])){
			$setting = unserialize($_G['setting']['login_mobile_smsset']);
            if (isset($setting["template2"]) && $setting["template2"]!="") {
                $template = iconv(CHARSET, "UTF-8//ignore", $setting["template2"]);
            }
        }
        $msg = preg_replace("/【变量】/i", $code, $template);
        return $msg;
    }

    public static function send($appid,$appkey,$phone,$msg,$smsid=1)
    {
        $s = intval($smsid);
        switch ($s) {
            case 99: $res = self::send_youzu($appid,$appkey,$phone,$msg); break;
            case 6:  $res = self::send_huyi($appid,$appkey,$phone,$msg); break;
            case 5:  $res = self::send_zhiyan($appid,$appkey,$phone,$msg); break;
            case 4:  $res = self::send_wangjian($appid,$appkey,$phone,$msg); break;
            case 3:  $res = self::send_jixintong($appid,$appkey,$phone,$msg); break;
            case 2:  $res = self::send_veesing($appid,$appkey,$phone,$msg); break;
            case 1:
            default: $res = self::send_moming($appid,$appkey,$phone,$msg); break;
        }
		if ($res["retcode"]==0) {
			C::t("#login_mobile#mobile_login_sms")->save($phone,$msg);
        }
        return $res;
    }

    private static function send_huyi($appid,$appkey,$phone,$msg)
    {
        require_once dirname(__FILE__)."/smsclient/sendmsg_huyi.php";
        return SendSMS_Huyi::send($appid,$appkey,$phone,$msg);
    }

    private static function send_zhiyan($appid,$appkey,$phone,$msg)
    {
        require_once dirname(__FILE__)."/smsclient/sendmsg_zhiyan.php";
        return SendSMS_Zhiyan::send($appid,$appkey,$phone,$msg);
    }

    private static function send_wangjian($appid,$appkey,$phone,$msg)
    {
        require_once dirname(__FILE__)."/smsclient/sendmsg_zgwj.php";
        return SendSMS_Wangjian::send($appid,$appkey,$phone,$msg);
    }

    private static function send_youzu($appid,$appkey,$phone,$msg)
    {
        require_once dirname(__FILE__)."/smsclient/sendmsg_youzu.php";
        return SendSMS_Youzu::send($appid,$appkey,$phone,$msg);
    }

    private static function send_moming($appid,$appkey,$phone,$msg)
    {
        require_once dirname(__FILE__)."/smsclient/sendmsg_moming.php";
        return SendSMS_Moming::send($appid,$appkey,$phone,$msg);
    }

    private static function send_veesing($appid,$appkey,$phone,$msg)
    {
        require_once dirname(__FILE__)."/smsclient/sendmsg_veesing.php";
        return SendSMS_Veesing::send($appid,$appkey,$phone,$msg);
    }

    private static function send_jixintong($appid,$appkey,$phone,$msg)
    {
        require_once dirname(__FILE__)."/smsclient/sendmsg_jixintong.php";
        return SendSMS_Jixintong::send($appid,$appkey,$phone,$msg);
    }
}
?>
