<?php
/* 上海维信互动短信接口 */

function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}


class SendSMS_Veesing
{
    private static $_api = "http://121.199.16.178/webservice/sms.php?method=Submit";

    public static function send($username,$password,$phone,$content) 
    {
        //$content = "您的验证码是：4852。请不要把验证码泄露给其他人。";
        $post_data = "account=$username&password=$password&mobile=$phone&content=".rawurlencode($content);

        $url = self::$_api;
        $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		$xml = curl_exec($curl);
		curl_close($curl);

        //var_dump($xml);
        $arr = xml_to_array($xml);

        $code = intval($arr['SubmitResult']['code']);
        if ($code==2) $code=0;

        $result = array (
            "retcode" => $code,
            "retmsg"  => $arr['SubmitResult']['msg'],
        );

        return $result;
    }
}
?>
