<?php
/**
* @file utils.inc.php
* @Brief tools for apis
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
class ApiUtils
{
	public static function getDzRoot()
	{
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		$ret = str_replace('source/plugin/mobile/', '', $_G['siteurl']);
		$ret = str_replace('source/plugin/bigapp/', '', $ret);
		$ret = str_replace('api/mobile/', '', $ret);
		$ret = str_replace('source/plugin/bigapp/', '', $ret);
		if(empty($ret)){
			return $_G['siteurl'];
		}
		return $ret;
	}

	public static function abstractPath($str, $labelName = 'img', $attrName = 'src')
	{
		$regex = '/<' . $labelName . '\s+' . $attrName . '="([\/a-zA-z0-9_\.]+)"/';
		$tmp = $str;
		if(1 === preg_match($regex, $str, $matches) && isset($matches[1]) && !empty($matches[1])){
			$tmp = $matches[1];
		}
		return $tmp;
	}

	public static function getImgPath($str, $labelName = 'img', $attrName = 'src')
	{
		$tmp = self::abstractPath($str, $labelName, $attrName);
		if(0 === strpos($tmp, 'http')){
			return $tmp;
		}
		$tmp = self::getDzRoot() . $tmp;
		return $tmp;
	}

	public static function getAttachPath($path)
	{
		$path = self::abstractPath($path);
		$regex = '/(album|category|common|forum|group|portal|profile|swfupload|temp)_/';
		if(1 !== preg_match($regex, $path, $matches) || !isset($matches[1]) || empty($matches[1])){
			return $path;
		}
		$type = $matches[1];
		global $_G;
		$path = self::getDzRoot() . $_G['setting']['attachurl']  . $type . '/' . $path;
		return $path;
	}

	public static function isOptFix($url)
	{
		$fix = explode('.', $url);
		if(count($fix) >= 1){
			$fix = $fix[count($fix) - 1];
			$fix = strtolower($fix);
			if(in_array($fix, BigAppConf::$optFix)){
				return true;
			}
		}
		return false;
	}
}
?>
