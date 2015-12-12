<?php
class SendSMS_Wangjian
{
    public static function send($username,$password,$phone,$content) 
    {
        $url = "http://utf8.sms.webchinese.cn/?Uid=$username&Key=$password&smsMob=$phone&smsText=$content";

        $ch = curl_init();
		$timeout = 5;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);

        $retcode = intval($file_contents);
        if ($retcode==0) {
            return array("retcode"=>1,"retmsg"=>"短信平台服务内部错误");
        }
        if ($retcode<0) {
            $retmsg = "未知错误，请联系管理员";
            if($retcode==-1) $retmsg="短信平台发送失败：没有该用户账户";
            else if($retcode==-2) $retmsg="短信平台发送失败：接口密钥不正确";
            else if($retcode==-21) $retmsg="短信平台发送失败：MD5接口密钥加密不正确";
            else if($retcode==-3) $retmsg="短信平台发送失败：短信数量不足";
            else if($retcode==-11) $retmsg="短信平台发送失败：该用户被禁用";
            else if($retcode==-14) $retmsg="短信平台发送失败：短信内容出现非法字符";
            else if($retcode==-4) $retmsg="短信平台发送失败：手机号格式不正确";
            else if($retcode==-41) $retmsg="短信平台发送失败：手机号码为空";
            else if($retcode==-42) $retmsg="短信平台发送失败：短信内容为空";
            else if($retcode==-51) $retmsg="短信平台发送失败：短信签名格式不正确";
            else if($retcode==-6) $retmsg="短信平台发送失败：IP限制";
            return array("retcode"=>1,"retmsg"=>$retmsg);
        }
        $retcode=0;     

        //var_dump($file_contents);

        $result = array (
            "retcode" => 0,
            "retmsg"  => "succ", //self::parseResCode($retcode),
        );
        return $result;
    }
}
?>
