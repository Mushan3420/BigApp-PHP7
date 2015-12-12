<?php

class SendSMS_Youzu {

    protected static $_sendUrl = "http://sms.uuzuonline.com/api/sms/sendCode";
    protected static $_checkUrl = "http://sms.uuzuonline.com/api/sms/checkCode";
    protected static $_app_id = '8UVLMuYSZL';
    protected static $_app_key = "USIvJTFLdGzhdPnugT";
    public function __construct() {
        
    }

    public function send($appid,$appkey,$phone,$msg)
    {
        self::$_app_id = $appid;
        self::$_app_key = $appkey;
        $res = self::sendCode($phone,$msg);
        $result = array (
            "retcode" => $res["status"],
            "retmsg"  => $res["desc"],
        );
        return $result;
    }

    public function post($data, $target) {
        $url_info = parse_url($target);
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $data;

        $fd = fsockopen($url_info['host'], 80);
        fwrite($fd, $httpheader);
        $gets = "";
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);
        if ($gets != '') {
            $start = strpos($gets, '<?xml');
            if ($start > 0) {
                $gets = substr($gets, $start);
            }
        }
        return $gets;
    }

    /**
     * @param type $phone
     * @return type
     */
    public function sendCode($phone, $strText) {
        $url = self::$_sendUrl;
        $queryArr = array(
            "mobile" => $phone,
            "app_id" => self::$_app_id,
            "content" => $strText,
            "type" => '1',
            "code_length" => '6',
            "expires_in" => '1800',
            "time" => time()
        );
        ksort($queryArr);
        $verify = strtolower(md5(self::build_query($queryArr) . self::$_app_key));
        $queryArr['verify'] = $verify;
		//KC_LOG_INFO("MisSendMsgDetail: ".serialize($queryArr));
        $url = self::addUrlQueryString('error', $url, $queryArr);
        $result = @file_get_contents($url);
		//KC_LOG_INFO("MisSendMsgDetail: ".$result);
        return json_decode($result, true);
    }

    public function build_query($query_data, $encoding = false) {
        $res = '';
        $count = count($query_data);
        $i = 0;
        foreach ($query_data as $k => $v) {
            if ($encoding === true) {
                $v = urlencode($v);
            }
            if ($i < $count - 1) {
                $res .= $k . '=' . $v . '&';
            } else {
                $res .= $k . '=' . $v;
            }
            $i ++;
        }
        return $res;
    }

    public function addUrlQueryString($tpl, $inputUrl, $arrParam = array()) {
        if (empty($arrParam)) {
            return $inputUrl;
        }

        if (strpos($inputUrl, '/') === 0) {
            $inputUrl = 'http://' . $_SERVER['HTTP_HOST'] . $inputUrl;
        }

        $arrUrl = parse_url($inputUrl);
        if (!$arrUrl || !isset($arrUrl['scheme']) || !isset($arrUrl['host'])) {
            if (!is_null($tpl)) {
                throw new Exception('internal ' . $tpl . ' invalid param url has been received  [ url: ' . $inputUrl . ' ].', 1);
            }
            throw new Exception('internal invalid param url has been received [ url: ' . $inputUrl . ' ].');
        }
        $url = $arrUrl['scheme'] . '://';
        if (isset($arrUrl['user'])) {
            $url .= $arrUrl['user'];
            if (isset($arrUrl['pass'])) {
                $url .= ':' . $arrUrl['pass'];
            }
            $url .= '@';
        }
        $url .= $arrUrl['host'];
        if (isset($arrUrl['port'])) {
            $url .= ':' . $arrUrl['port'];
        }
        $split = '/?';
        if (isset($arrUrl['path'])) {
            $url .= $arrUrl['path'];
            $split = '?';
        }
        $qs = http_build_query($arrParam);
        if (isset($arrUrl['query'])) {
            $url .= $split . $arrUrl['query'];
            $split = '&';
        }
        $url .= $split . $qs;
        if (isset($arrUrl['fragment'])) {
            $url .= '#' . $arrUrl['fragment'];
        }
        return $url;
    }
}

?>
