<?php
/**
 * @file utils.inc.php
 * @Brief 
 * @author youzu
 * @version 1.0.0
 * @date 2015-07-07
 */
if(!defined('BIGAPPJSONFILE')){
	require_once dirname(dirname(__FILE__)) . '/bigappjson.class.php';
}
class Utils
{
	public static function jumpToLogin($text = 'invalid_aks', $moduleName = 'buildapp')
	{
		Utils::clearLocalAkSk();
		showmessage(lang('plugin/bigapp', $text), '/discuz/admin.php?action=plugins&operation=config&do='.$pluginid.'&identifier=bigapp&pmod=' . $moduleName, array(), array('alert' => 'error'));
	}

	public static function includeJs($jsPath)
	{
		static $inited = false;
		if(false === $inited){
			$str = '';
			$str .= '<script type="text/javascript" src="' . $jsPath . 'jquery.js"> </script>';
			$str .= '<script type="text/javascript" src="' . $jsPath . 'jquery.min.js"> </script>';
			$str .= '<script type="text/javascript" src="' . $jsPath . 'ajaxfileupload.js"> </script>';
			$str .= '<script type="text/javascript" src="' . $jsPath . 'uploadpic.js"> </script>';
			$str .= '<script type="text/javascript" src="' . $jsPath . 'uploadpic.js"> </script>';
			$str .= '<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js" type="text/javascript"></script>';
			$str .= '<script type="text/javascript" src="' . $jsPath . 'evol.colorpicker.js"> </script>';
			$str .= '<script type="text/javascript" src="' . $jsPath . 'updateschedule.js"> </script>';
			echo $str;
			$inited = true;
		}
	}

	public static function diconv($charset1, $charset2, $str)
    {
		if(function_exists('iconv')){
			$msg = iconv($charset1, $charset2 . '//ignore', $str);	
		}else{
			$msg = mb_convert_encoding($str, $charset2, $charset1);
		}
    }

	public static function includeCss($cssPath)
	{
		static $inited = false;
		if(false === $inited){
			$str ='';
			$str .= '<link href="' . $cssPath . 'demo.css" rel="stylesheet" />'; 
			$str .= '<link href="' . $cssPath . 'evol.colorpicker.min.css" rel="stylesheet" />';
			echo $str;
			$inited = true;
		}
	}

	public static function readLocalAkSk($file)
	{
		$tmp = @file_get_contents($file);
		$tmp2 = BIGAPPJSON::decode($tmp, true);
		if(!is_array($tmp2) || !isset($tmp2['app_key']) || !isset($tmp2['app_secret'])){
			return false;
		}
		return $tmp2;
	}

	public static function readLocalAkSk2()
	{
		global $_G;
		require_once libfile('function/core');
		require_once libfile('function/cache');
		updatecache('setting');
		//$bigapp_aksk = 'bigapp_aksk_' . md5($_G['siteurl']);
		$bigapp_aksk = 'bigapp_aksk';  //!< 不要把siteurl带上，api接口和管理页接口的siteurl是不同的!!!
		if(isset($_G['setting'][$bigapp_aksk]) && !is_array($_G['setting'][$bigapp_aksk])){
			$_G['setting'][$bigapp_aksk] = unserialize($_G['setting'][$bigapp_aksk]);
		}
		if(isset($_G['setting'][$bigapp_aksk]['app_key']) && isset($_G['setting'][$bigapp_aksk]['app_secret'])){
			runlog('bigapp', 'read local ak sk succ [ ak: ' . $_G['setting'][$bigapp_aksk]['app_key'] . ', sk: ' . $_G['setting'][$bigapp_aksk]['app_secret']  . ' ]');
			return $_G['setting'][$bigapp_aksk];
		}
		runlog('bigapp', 'read local ak sk failed');
		return false;
	}

	public static function saveLocalAkSk2($ak, $sk)
	{
		global $_G;
		//$bigapp_aksk = 'bigapp_aksk_' . md5($_G['siteurl']);
		$bigapp_aksk = 'bigapp_aksk';
		$setting = array('app_key' => $ak, 'app_secret' => $sk);
		$settings = array($bigapp_aksk => $setting);
		$_settings = array();
		foreach($settings as $key => $value) {
			$key = addslashes($key);
			$value = addslashes(is_array($value) ? serialize($value) : $value);
			$_settings[] = "('$key', '$value')";
		} 
		if($_settings) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`) VALUES ".implode(',', $_settings));
		}
		$_G['setting'][$bigapp_aksk] = $setting;
		runlog('bigapp', 'save local ak sk finished');
	}

	public static function saveLocalAkSk($file, $ak, $sk){
		if(empty($ak) || empty($sk)){
			return false;
		}
		$content = array('app_key' => $ak, 'app_secret' => $sk);
		$content = BIGAPPJSON::encode($content);
		return @file_put_contents($file, $content);
	}

	public static function clearLocalAkSk()
	{
		global $_G;
		$bigapp_aksk = 'bigapp_aksk'; // . md5($_G['siteurl']);
		if(isset($_G['setting']['bigapp_aksk'])){
			unset($_G['setting']['bigapp_aksk']);
		}
		if(isset($_G['setting'][$bigapp_aksk])){
			unset($_G['setting'][$bigapp_aksk]);
		}
		$table = @DB::table('common_setting');
		@DB::query("DELETE FROM $table WHERE skey = 'bigapp_aksk' LIMIT 1");
		@DB::query("DELETE FROM $table WHERE skey = '$bigapp_aksk' LIMIT 1");
		runlog('bigapp', 'clear local ak sk succ');
	}

	public static function getDefPkg()
	{
		global $_G;
		$rndStr = self::_randString();
		if(isset($_G['siteurl'])){
			$tmp = @parse_url($_G['siteurl']);
			if(is_array($tmp)){
				$host = $tmp['host'];
				$hostArr = array();
				$tmp = explode('.', $host);
				foreach ($tmp as $v){
					if(preg_match('/^[a-zA-Z]+$/', $v)){
						$hostArr[] = $v;
					}
				}
				$num = count($hostArr);
				if(2 <= $num){
					$pkg = $hostArr[$num - 1] . '.' . $hostArr[$num - 2] . '.clan' . $rndStr;
					if(is_numeric($hostArr[$num - 1])){
						$pkg = 'com.' . $hostArr[$num - 2] . '.clan' . $rndStr;
					}
					return $pkg;
				}
			}
		}
		$pkg = 'com.youzu.clan' . $rndStr;
		return $pkg;
	}

	protected static function _randString($len = 16)
	{
		$output = '';
		for($i = 0; $i < $len; $i++){
			$chr = mt_rand(0, 25);
			$oct = chr(ord('a') + $chr);
			$output .= $oct;
		}
		return $output;
	}

	public static function addUrlQueryString($inputUrl, $arrParam = array()){
		if(empty($arrParam)){
			return $inputUrl;
		}
		$arrUrl = parse_url($inputUrl);
		if(!$arrUrl || !isset($arrUrl['scheme']) || !isset($arrUrl['host'])){
			return false;
		}
		$url = $arrUrl['scheme'] . '://';
		if(isset($arrUrl['user'])){
			$url .= $arrUrl['user'];
			if(isset($arrUrl['pass'])){
				$url .= ':' . $arrUrl['pass'];
			}
			$url .= '@';
		}
		$url .= $arrUrl['host'];
		if(isset($arrUrl['port'])){
			$url .= ':' . $arrUrl['port'];
		}
		$split = '/?';
		if(isset($arrUrl['path'])){
			$url .= $arrUrl['path'];
			$split = '?';
		}
		$qs = http_build_query($arrParam);
		if(isset($arrUrl['query'])){
			parse_str($arrUrl['query'], $queryArr);
			if(!empty($arrParam)){
				$arrParam = array_merge($queryArr,$arrParam);
				$qs = http_build_query($arrParam);
			}else{
				$qs = $arrUrl['query'];
			}
		}
		$url .= $split . $qs;
		if(isset($arrUrl['fragment'])){
			$url .= '#' . $arrUrl['fragment'];
		}
		return $url;
	}

	public static function loadTemplate($tpl, $vars ,$tplVars=null)
	{
		$json = BIGAPPJSON::encode($vars);
		$js_script = '<script type="text/javascript"> v = eval(\'(' . $json . ")');</script>\n";
				$content = @file_get_contents($tpl);
				if(false === $content){
				return false;
				}
				runlog('bigapp', '>>>>>>charset: ' . CHARSET);
				if(is_string($content) && strtolower(CHARSET) != 'utf-8' && strtolower(CHARSET) != 'utf8'){
				if(function_exists('iconv')){
				runlog('bigapp', '>>>>>>use iconv to convert...');
				$content = @iconv('UTF-8', 'GBK//ignore', $content);
				}else if(function_exists('mb_convert_encoding')){
				runlog('bigapp', '>>>>>>use mb_convert_encoding to convert...');
				$content = @mb_convert_encoding($content, 'GBK', 'UTF-8');
				}
				}

				$tplVars['js_script'] = $js_script;
				$tplVars['app_charset'] = CHARSET;
				if(is_array($tplVars)){
				foreach($tplVars as $key => $value){
				$content = str_replace("<%".$key."%>",$value,$content);
				$content = str_replace("<% ".$key." %>",$value,$content);
				}
				}
				echo $content;
	}

	public static function getFile($url, $dest = null)
	{
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
		$ret = curl_exec($ch);
		if(0 != curl_errno($ch)){
			return false;
		}
		if(is_null($dest)){
			return $ret;
		}
		file_put_contents($dest, $ret);
		return true;
	}

	public static function filterPid($settings){
		if(!empty($settings)){
			foreach($settings as $key => &$value){
				$value['title'] = self::converGbkString(urldecode($value['title']));
				$value['pic'] = self::converGbkString(urldecode(urldecode($value['pic'])));
				$value['pic'] = str_replace("&amp;","&",$value['pic']);
				$value['pic'] = self::addUrlQueryString($value['pic'],array("_v"=>time()));
				$value['url'] = self::converGbkString(urldecode($value['url']));
				$value['desc'] = self::converGbkString(urldecode($value['desc']));

				$preg = array(
						'2'=>'/\w+-(\d+)-(\d+)-(\d+)\.htm/i',
						'3'=>'/\w+-(\d+)-(\d+)\.htm/i',
						);

				if($value['type'] == 2){
					if(isset($preg[$value['type']]) 
							&& preg_match($preg[$value['type']],$value['url'],$matches) ){
						if(isset($matches[1]))
							$value['pid'] = $matches[1];
					}else{
						if(preg_match('/tid=(\d+)/i', $value['url'], $matches)){
							if(isset($matches[1]))
								$value['pid'] = $matches[1];
						}
					}
				}

				if($value['type'] == 3){
					if(isset($preg[$value['type']]) 
							&& preg_match($preg[$value['type']],$value['url'],$matches) ){
						if(isset($matches[1]))
							$value['pid'] = $matches[1];
					}else{
						if(preg_match('/fid=(\d+)/i', $value['url'], $matches)){
							if(isset($matches[1]))
								$value['pid'] = $matches[1];
						}
					}
				}
				runlog('bigapp', "debug >>>>>>>>> info:".json_encode($value));
				//$value['pid'] = 0 ;
				//$value['pid'] = self::getPid($value['type'],$value['url'],$value['pid']);
				runlog('bigapp', "debug end >>>>>>>>> info pid >>>>>>>>:".$value['pid']);
			}
		}
		return $settings;
	}
	
	public static function getPid($type,$url,$pid=0){
		global $_G;
		$threadRule = isset($_G['setting']['rewriterule']['forum_viewthread'])?$_G['setting']['rewriterule']['forum_viewthread']:null;
		$forumRule = isset($_G['setting']['rewriterule']['forum_forumdisplay'])?$_G['setting']['rewriterule']['forum_forumdisplay']:null;
		
		if(intval($pid) == 0){
			 //thread-{tid}-{page}-{prevpage}.html
		     if($type == 2 && $threadRule){
				 $ret1 = preg_match('/(.*?)({\w+})(.*?)({\w+})(.*?)({\w+})\.htm/i',$threadRule,$matche1);
				 $url = str_replace($_G['siteurl'],'',$url);
				 //$url = str_replace('http://www.3body.com/','',$url);
				 //runlog('bigapp', "debug >>>>>>>>> site url:".$_G['siteurl']);
				 //runlog('bigapp', "debug >>>>>>>>> url:".$url);
				 $ret2 = preg_match('/(.*?)(\d+)(.*?)(\d+)(.*?)(\d+)\.htm/i',$url,$matche2);
				 //runlog('bigapp', "debug >>>>>>>>> matche1:".json_encode($matche1));
				 //runlog('bigapp', "debug >>>>>>>>> matche2:".json_encode($matche2));
				 if($ret1 && $ret2 && !empty($matche2))
				 foreach($matche1 as $k=>$v){
					if($v == '{tid}'){
						$pid = $matche2[$k];
						break;
					}
				 }
			 }
			 //forum-{fid}-{page}.html
			 if($type == 3 && $forumRule){
				 $ret1 = preg_match('/(.*?)({\w+})(.*?)({\w+})?\.htm/i',$forumRule,$matche1);
				 $url = str_replace($_G['siteurl'],'',$url);
				 //$url = str_replace('http://www.3body.com/','',$url);
				 $ret2 = preg_match('/(.*?)(\d+)(.*?)(\d+)?\.htm/i',$url,$matche2);
				 if($ret1 && $ret2 && !empty($matche2))
				 foreach($matche1 as $k=>$v){
					if($v == '{fid}'){
						$pid = $matche2[$k];
						break;
					}
				 }
			 }
		}
		return $pid;
	}

	public static function converGbkString($content, $flag = true){
		if($flag === false) return $content; //不需要转，默认是需要进行gbk转
		
		global $_G;
		$charset = strtoupper($_G['charset']);
		if(is_string($content) && strtolower($charset) != 'utf-8' && strtolower($charset) != 'utf8'){
			if(function_exists('iconv')){
				$content = @iconv('UTF-8', 'GBK//ignore', $content);
			}else if(function_exists('mb_convert_encoding')){
				$content = @mb_convert_encoding($content, 'GBK', 'UTF-8');
			}
		}
		return $content;
	}

	public static function getCheckJson()
	{
		global $_G;
		if(file_exists(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/sysdata/cache_mobile.php')){
			@require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/sysdata/cache_mobile.php';
		}else{
			$mobilecheck = '[]';
		}
		if(isset($mobilecheck)){
			$mobilecheck = BIGAPPJSON::decode($mobilecheck, true);
			if(!is_array($mobilecheck)){
				$mobilecheck = array();
			}
		}
		if(isset($_G['charset'])){
			$mobilecheck['charset'] = $_G['charset'];
		}
		if(isset($_G['setting']['version'])){
			$mobilecheck['discuzversion'] = $_G['setting']['version'];
		}
		if(isset($_G['setting']['sitename'])){
			$mobilecheck['sitename'] = $_G['setting']['sitename'];
		}
		if(isset($_G['setting']['ucenterurl'])){
			$mobilecheck['ucenterurl'] = $_G['setting']['ucenterurl'];
		}
		$mobilecheck['plugin_info'] = array('mobile' => array('enabled' => 0, 'version' => 'NA'), 'bigapp' => array('enabled' => 0, 'version' => 'NA'));
		//die(json_encode($_G['setting']['plugins']));
		$lostPlugin = array();
		foreach ($mobilecheck['plugin_info'] as $plugin => &$info){
			if(isset($_G['setting']['plugins']['available']) && in_array($plugin, $_G['setting']['plugins']['available'])){
				$info['enabled'] = 1;
			}else{
				$lostPlugin[] = $plugin;
			}
			if(isset($_G['setting']['plugins']['version'][$plugin])){
				$info['version'] = $_G['setting']['plugins']['version'][$plugin];
			}
		}
		$mobilecheck['bigapp_api_status'] = 'AVAILABLE';
		if(!empty($lostPlugin)){
			$mobilecheck['bigapp_api_status'] = 'UNAVAILABLE, plugin(s) [ ' . implode(', ', $lostPlugin) . ' ] do not exist or have been closed';
		}
		$mobDir = dirname(dirname(__FILE__)) . '/mobile';
		if(!is_dir($mobDir)){
			$mobilecheck['plugin_info']['mobile']['dir_check'] = 0;
			$mobilecheck['bigapp_api_status'] = 'UNAVAILABLE, mobile plugin dir [' . $mobDir . '] does not exist';
		}
		$mobilecheck['inner_edition'] = '__INNER_EDITION__';
		if(strpos($mobilecheck['inner_edition'], 'INNER_EDITION') !== false){
			$mobilecheck['inner_edition'] = 'NA';
		}
		$mobilecheck['mobile_enabled'] = 0;
		if(isset($_G['setting']['mobile']['allowmobile']) && $_G['setting']['mobile']['allowmobile']){
			$mobilecheck['mobile_enabled'] = 1;
		}
		$mobilecheck['bbclosed'] = 0;
		if(isset($_G['setting']['bbclosed']) && $_G['setting']['bbclosed']){
			$mobilecheck['bbclosed'] = 1;
		}
		return $mobilecheck;
	}
}
?>
