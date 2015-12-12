<?php
class SendSMS_Zhiyan
{
    public static function send($username,$password,$phone,$content) 
    {
		$url = "https://sms.zhiyan.net/sms/match_send.json";
		$json_arr = array(
			"mobile" => $phone,
			"content" => $content,
			"appId"=>$username,
			"apiKey"=>$password,
			"extend" => "",
			"uid" => ""
		);
		$array =json_encode($json_arr);
		//初始化curl
		$ch = curl_init();
		//参数设置
		$res= curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec ($ch);
		curl_close($ch);
		//echo($result);
        $js = json_decode($result, true);
        $res = array (
            "retcode" => 0,
            "retmsg"  => "succ",
        );
        if ($js["result"]=="FAIL") {
            $res["retcode"] = 101;
            $res["retmsg"]  = $js["reason"];
        }
		return $res;
    }
}
?>
