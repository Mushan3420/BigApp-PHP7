<?php
/**
* @file myhome.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'myhome';
include_once 'forum.php';

class BigAppAPI {

	static protected $collect = array(); 
	static protected $typeMap = array('1'=>"link",'2'=>'forum');
	static protected $dataFormat = array("id","title","pic","url","type","pid","order","desc");
	function common() {
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
	}

	function output() {
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		$variable['myhome'] = null;

		//$banner = C::t('common_setting')->fetch("bigapp_home_banner",true);
		$banner = self::getSettingsCache("bigapp_home_banner");
		foreach (BigAppConf::$defaultHome as $key => &$value){
    		foreach ($value as &$v){
        		foreach (array('title', 'desc') as $k){
            		if(isset($v[$k])){
                		if(function_exists('iconv')){
                    		$v[$k] = iconv('UTF-8', CHARSET . '//ignore', $v[$k]);
                		}else{
                    		$v[$k] = mb_convert_encoding($v[$k], CHARSET, 'UTF-8');
                		}
            		}
        		}
    		}
		}
		$banner = self::filterVars($banner);
		if(!empty($banner)){
			foreach($banner as $key=>&$value){
				if(isset($value['status']) && $value['status'] == 0){
					unset($banner[$key]);
					continue;
				}
				$value = self::formateResult($value);
				if(isset($value['order'])){
					unset($value['order']);
				}
				if(isset($value['status'])){
					unset($value['status']);
				}
			}
			
			if(self::getSwitch("0")){
				$variable['myhome']['banner'] = self::formatArr($banner);
			}
		}
		
		//$func1 = C::t('common_setting')->fetch("bigapp_home_func1",true);
		$func1 = self::getSettingsCache("bigapp_home_func1");
		
		$func1 = self::filterVars($func1,'func1');
		if(!empty($func1)){
			foreach($func1 as $k => &$v){
				if(isset($v['status']) && $v['status'] == 0){
					unset($func1[$k]);
					continue;
				}
				$v = self::formateResult($v);
				if(isset($v['order'])){
					unset($v['order']);
				}
				if(isset($v['status'])){
					unset($v['status']);
				}
			}
			if(self::getSwitch("1")){
				$variable['myhome']['func'][self::$typeMap['1']] = self::formatArr($func1);
			}
		}
		
		//$func2 = C::t('common_setting')->fetch("bigapp_home_func2",true);
		$func2 = self::getSettingsCache("bigapp_home_func2");
		$func2 = self::filterVars($func2,'func2');
		
		if(!empty($func2)){
			foreach($func2 as $k => &$v){
				if(isset($v['status']) && $v['status'] == 0){
					unset($func2[$k]);
					continue;
				}
				$v = self::formateResult($v);
				if(isset($v['order'])){
					unset($v['order']);
				}
				if(isset($v['status'])){
					unset($v['status']);
				}
			}
			
			if(self::getSwitch("2")){
				$variable['myhome']['func'][self::$typeMap['2']] = self::formatArr($func2);
			}
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}
	
	public static function filterVars($settings,$type='banner'){
		$sort = array();
		
		if(false === $settings){
			$settings = BigAppConf::$defaultHome[$type];
		}
		if(!empty($settings)){
			foreach($settings as $key=>&$value){
				if(!isset($value['order'])){
					$value['order'] = "0";
				}
				$sort[$key] = $value['order'];
			}
			array_multisort($sort,SORT_ASC,$settings);
		}
		return $settings;
	}
	
	public static function formatArr($settings){
		$newSettings = array();
		if(!empty($settings))
		foreach($settings as $k => $v){
			$newSettings[] = $v;
		}
		return $newSettings;
	}
	
	public static function formateResult($setting){
		if(!empty($setting)){
			foreach(self::$dataFormat as $v){
				if(!isset($setting[$v])){
					$setting[$v] = "";
				}else{
					$setting[$v] = self::converString(strval($setting[$v]));
					if(isset($setting['pic'])){
						$setting['pic'] = urldecode($setting['pic']);
						$setting['pic'] = str_replace("&amp;","&",$setting['pic']);
					}
				}
			}
		}
		return $setting;
	}
	
	public static function converString($content){
		/*
		global $_G;
		$charset = strtoupper($_G['charset']);
		if(is_string($content) && strtolower($charset) != 'utf-8' && strtolower($charset) != 'utf8'){
			if(function_exists('iconv')){
				$content = @iconv('UTF-8', 'GBK//ignore', $content);
			}else if(function_exists('mb_convert_encoding')){
				$content = @mb_convert_encoding($content, 'GBK', 'UTF-8');
			}
		}
		*/
		return $content;
	}
	
	public static function getSwitch($fun){
		//$switch = C::t('common_setting')->fetch("bigapp_home_switch".$fun,true);
		$switch = self::getSettingsCache("bigapp_home_switch".$fun);
		$ret = true;
		if(isset($switch['switch']) && $switch['switch'] == 0){
			$ret = false;
		}
		return $ret;
	}
	
	public static function getSettingsCache($key){
		$ret = false;
		global $_G;
		if(isset($_G['setting'][$key]) && !empty($_G['setting'][$key]) ){
			$ret = unserialize($_G['setting'][$key]);
		}
		return $ret;
	}

}

?>
