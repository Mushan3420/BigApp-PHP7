<?php
class SendSMS_Huyi
{
    public static function send($username,$password,$phone,$content) 
    {
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
        $post_data = "account=$username&password=$password&mobile=".$phone."&content=".rawurlencode("$content");
        $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		$return_str = curl_exec($curl);
		curl_close($curl);
        $resarr = self::xml_to_array($return_str);
        $res = array(
            "retcode" => 0,
            "retmsg" => "succ",
        );
        if (isset($resarr["SubmitResult"])) {
            $res["retcode"] = $resarr["SubmitResult"]["code"];
            $res["retmsg"]  = $resarr["SubmitResult"]["msg"];
        }
        if ($res["retcode"]==2) $res["retcode"]=0;
		return $res;
    }

    private static function xml_to_array($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
				$subxml= $matches[2][$i];
				$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = self::xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
