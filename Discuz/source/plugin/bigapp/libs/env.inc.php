<?php
/******************************************
 * @file env.inc.php
 * @brief global environment manage
 * @author youzu
 * @version 1.0.0
 * @date 2015-07-07
 *****************************************/

require_once("utils.inc.php");
require_once("bksvr.inc.php");
require_once(dirname(__FILE__)."/../bigappjson.class.php");
require_once(dirname(__FILE__)."/../conf/conf.inc.php");

class BigappEnv
{
    private static $_aksk = false;
    private static $_appInfo = false;

    // get discuz site's url(discuz root)
    public static function getSiteUrl()
    {/*{{{*/
        global $_G;
		return rtrim($_G['siteurl'], '/');
    }/*}}}*/

    // get bigapp apiurl
    public static function getApiUrl()
    {/*{{{*/
        return self::getSiteUrl()."/api/mobile/iyz_index.php";
    }/*}}}*/

    // get bigapp plugin path
    public static function getPluginPath()
    {/*{{{*/
        return self::getSiteUrl().'/source/plugin/bigapp';
    }/*}}}*/

    // get aksk. fail return false;
    public static function getAkSk()
    {/*{{{*/
        if (!self::$_aksk) {
            $res = Utils::readLocalAkSk2();
            if ($res!==false && isset($res["app_key"]) && isset($res["app_secret"])) {
                self::$_aksk = array (
					"ak" => $res["app_key"],
					"sk" => $res["app_secret"],
				);
            }
        }
        return self::$_aksk;
    }/*}}}*/

    // get aksk's encryption string
    public static function getAkSkMd5()
    {/*{{{*/
        $aksk = self::getAkSk();
        if ($aksk===false) return "";
        $vf = BIGAPPJSON::encode($aksk);
        return md5($vf);
    }/*}}}*/

    // get appinfo from bigstation
    public static function getAppInfoFromBigstation()
    {/*{{{*/
        if (!self::$_appInfo) {
			$apiurl  = self::getApiUrl();
			$akskmd5 = self::getAkSkMd5();
			if ($akskmd5!="") {
				$url = BigAppConf::$mcapis["checkaksk"];
				$aksk = self::getAkSk();
				$params = array (
					"apiurl" => $apiurl,
					"aksk" => $akskmd5,
				);
				$obj = new BkSvr($aksk["ak"], $aksk["sk"], 30);
				$ret = $obj->getInfo($url, $params, false);
				if (false !== $ret && isset($ret["data"])) {
					self::$_appInfo = $ret["data"];
				}
			}
        }
        return self::$_appInfo;
    }/*}}}*/

    // remote verify after save aksk
    public static function remoteVerifyAfterSaveAksk()
    {/*{{{*/
        $aksk = self::getAkSk();
        $params = array (
            "api_url"   => self::getApiUrl(),
            "site_type" => "discuz",
        );
        $obj = new BkSvr($aksk["ak"], $aksk["sk"], 30);
        $url = BigAppConf::$mcapis["autoverify"];
		$ret = $obj->getInfo($url, $params, false);
		if (false !== $ret && isset($ret["data"])) {
			return $ret["data"]["check_status"];
		}
        return 0;
    }/*}}}*/
    

    // import bigapp model
    public static function import_model($modelName)
    {/*{{{*/
        $model_path = dirname(__FILE__)."/../models/";
        $file = $model_path.$modelName;
        include_once($file);
    }/*}}}*/
}



// vim600: sw=4 ts=4 fdm=marker syn=php
?>
