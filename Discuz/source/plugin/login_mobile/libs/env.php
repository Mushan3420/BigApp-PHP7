<?php
require_once dirname(__FILE__)."/validate.php";
require_once dirname(__FILE__)."/utils.inc.php";

class DzEnv
{
    // 是否开启PC端设置
    public static function isEnablePc()
    {/*{{{*/
        global $_G;
        $enable=1;
        if(isset($_G['setting']['login_mobile_setting'])) {
            $setting = unserialize($_G['setting']['login_mobile_setting']);
            $enable = intval($setting["enable"]);
        }
        return ($enable==1);
    }/*}}}*/

    // 是否开启移动端设置
    public static function isEnableMobile()
    {/*{{{*/
        global $_G;
        $enable=1;
        if(isset($_G['setting']['login_mobile_setting'])) {
            $setting = unserialize($_G['setting']['login_mobile_setting']);
            $enable = intval($setting["enable_mobile"]);
        }
        return ($enable==1);
    }/*}}}*/

    // 正常输出
    public static function result(array $result)
    {/*{{{*/
        header("Content-type: application/json");
        echo json_encode($result);
        exit;
    }/*}}}*/

    // 错误输出
    public static function error_result($errormsg,$errcode=100001)
    {/*{{{*/
        $errmsg = lang("plugin/login_mobile", $errormsg);
        $err = array (
            "retcode" => $errcode,
            "retmsg"  => iconv(CHARSET,"UTF-8//ignore",$errmsg),
        );
        self::result($err);
    }/*}}}*/

	// 获取请求参数
    public static function get_param($key, $dv=null, $field='request')
    {/*{{{*/
        if ($field=='GET') {
            return isset($_GET[$key]) ? $_GET[$key] : $dv;
        }
        else if ($field=='POST') {
            return isset($_POST[$key]) ? $_POST[$key] : $dv;
        }
        else {
            return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $dv;
        }
    }/*}}}*/

    // get discuz site's url(discuz root)
    public static function getSiteUrl()
    {/*{{{*/
        global $_G;
		return rtrim($_G['siteurl'], '/');
    }/*}}}*/

    // get bigshop plugin path
    public static function getPluginPath()
    {/*{{{*/
        return self::getSiteUrl().'/source/plugin/login_mobile';
    }/*}}}*/

    // jsonrpc
    public static function jsonrpc($url, $method="GET", $params=array())
    {/*{{{*/
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		if('POST' == $method){
			curl_setopt($ch, CURLOPT_POST, true);
			if(!empty($params)){
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}   
		}else{
			curl_setopt($ch, CURLOPT_HEADER, false);
		}   
		$result = curl_exec($ch);
		curl_close($ch);
		return json_decode($result,true);
    }/*}}}*/

    // get info
    public static function getinfo(&$default_sms, &$clients)
    {/*{{{*/
        $list = array(
          "clients"=>array(
            array("text"=>"上海维信互动","value"=>2,"desc"=>"您可以前往<a href=\\\"http://www.veesing.com/\\\" target=\\\"_blank\\\">上海维信互动官网</a>申请账号"),
            array("text"=>"莫名短信","value"=>"1",
                "desc"=>"您可以前往<a href=\\\"http://www.duanxin.cm/\\\" target=\\\"_blank\\\">莫名短信官网</a>申请账号"),
            array("text"=>"吉信通","value"=>"3","desc"=>"您可以前往<a href=\\\"http://www.winic.org/\\\" target=\\\"_blank\\\">吉信通官网</a>申请账号"),
            array("text"=>"中国网建","value"=>"4","desc"=>"您可以前往<a href=\\\"http://sms.webchinese.com.cn/\\\" target=\\\"_blank\\\">中国网建SMS短信通官网</a>申请账号"),
            array("text"=>"智验科技","value"=>"5","desc"=>"您可以前往<a href=\\\"http://www.zhiyan.net/\\\" target=\\\"_blank\\\">智验科技官网</a>申请账号"),
            array("text"=>"互亿无线","value"=>"6","desc"=>"您可以前往<a href=\\\"http://www.ihuyi.com/\\\" target=\\\"_blank\\\">互亿无线官网</a>申请账号"),
            array("text"=>"游族网络","value"=>"99","desc"=>""),
          )
        );
        try {
			$url = "http://139.196.29.35/login_mobile/index.php/api/index?version=1.3.2";
			$res = self::jsonrpc($url);
            if (empty($res["list"])) return $list;
            $list = $res["list"];
            if (isset($res["default_sms"])) $default_sms = intval($res["default_sms"]);
            if (isset($res["clients"])) $clients = $res["clients"];
        } catch (Exception $e) {

        } 
        return $list;
    }/*}}}*/
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
