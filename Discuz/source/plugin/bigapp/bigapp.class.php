<?php
/**
* @file bigapp.class.php
* @Brief mobile plugin main class
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ') && !defined('IN_BIGAPP')) {
	exit('Access Denied');
}

require_once dirname(__FILE__) . '/conf/conf.inc.php';
require_once dirname(__FILE__) . '/apiutils.inc.php';
require_once dirname(__FILE__) . '/bigappjson.class.php';

class mobileplugin_bigapp
{
	public function common()
	{
		global $_G;
		if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'iyz_index.php') !== false){
			if(true === BigAppConf::$debug){
				$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
			}
			$_G['pluginrunlist'][] = 'bigapp';
			$this->_disableSecCode();
			if(true === $this->_requireAPI('common')){
				BigAppAPI::common();	
			}
		}
	}

	public function global_bigapp()
	{
		global $_G;
		if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'iyz_index.php') !== false){
			if(true === BigAppConf::$debug){
				$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
			}
			if(true === $this->_requireAPI('output')){
				BigAppAPI::output();		
			}
		}
	}


	protected function _requireAPI($method = null)
	{
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		if(class_exists('BigAppAPI', false)){
			if(empty($method)){
				return true;
			}
			if(method_exists('BigAppAPI', $method)){
				return true;
			}
			return false;
		}
		if(!isset($_GET['module'])){
			return false;
		}
		$iyzVer = 1;
		if(isset($_GET['iyzversion']) && is_numeric($_GET['iyzversion'])){
			$iyzVer = intval($_GET['iyzversion']);
			if($iyzVer > BIGAPP_PLUGIN_VERSION){
				$iyzVer = BIGAPP_PLUGIN_VERSION;
			}
		}
		$apifile = dirname(__FILE__) . '/api/' . $iyzVer . '/' . $_GET['module'] . '.php';

		if(file_exists($apifile)) {
			require_once $apifile;
		} else {
			if($iyzVer > 1) {
				for($i = $iyzVer; $i >= 1; $i--) {
					$apifile = dirname(__FILE__) . '/api/' . $i . '/'.$_GET['module'].'.php';
					if(file_exists($apifile)) {
						require_once $apifile;
						break;
					}
				}
			}
		}
		if(class_exists('BigAppAPI', false)){
			if(empty($method)){
				return true;
			}
			if(method_exists('BigAppAPI', $method)){
				return true;
			}
		}
		return false;
	}

	protected function _disableSecCode()
	{
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		//make client's operation more simple
		$_G['setting']['seccodedata'] =  BIGAPPJSON::decode('{"cloudip":"0","rule":{"register":{"allow":"0","numlimit":"",' . 
				'"timelimit":"60"},"login":{"allow":"0","nolocal":"0","pwsimple":"0","pwerror":"0","outofday":"","numiptry":"",' . 
				'"timeiptry":"60"},"post":{"allow":"0","numlimit":"","timelimit":"60","nplimit":"","vplimit":""},"password":{"allow":"0"},' . 
				'"card":{"allow":"0"}},"minposts":"","type":"0","width":100,"height":30,"scatter":"","background":"0","adulterate":"0",' . 
				'"ttf":"0","angle":"0","warping":"0","color":"0","size":"0","shadow":"0","animator":"0"}', true);
		$tmp = BIGAPPJSON::decode('{"allowmobile":1,"mobileseccode":0,"mobilehotthread":1,"mobiledisplayorder3":1}', true);
		$_G['setting']['mobile'] = $tmp + $_G['setting']['mobile'];
		$_G['setting']['secqaa']['status'] = 0;
		$_G['cache']['plugin']['dsu_paulsign']['ftopen'] = 0; //close dsu plugin location
	}
}

class mobileplugin_bigapp_forum extends mobileplugin_bigapp
{
	public function misc_bigapp_message($param) {
		global $_G;
		if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'iyz_index.php') !== false){
			if(true === BigAppConf::$debug){
				$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
			}
			if(true === $this->_requireAPI('misc_bigapp_message')){
				BigAppAPI::misc_bigapp_message($param);
			}
		}
	}
}

class bigapp_core {

	function result($result) {
		
		global $_G;
		ob_end_clean();
		function_exists('ob_gzhandler') && $_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
		header("Content-type: application/json");
		if(!defined('REQUEST_METHOD_DOMAIN')){
			define('REQUEST_METHOD_DOMAIN', 'http://wsq.discuz.qq.com');
		}
		bigapp_core::make_cors($_SERVER['REQUEST_METHOD'], REQUEST_METHOD_DOMAIN);

        ////////////////////////////////////////////////
        // 小顺提出的需求：端通过此字段标识请求
        $result["request_id"] = isset($_REQUEST['request_id']) ? $_REQUEST['request_id'] : 0;
        ////////////////////////////////////////////////

		$result = bigapp_core::json(bigapp_core::format($result));
		
		
		if(defined('FORMHASH')) {
			echo empty($_GET['jsoncallback_'.FORMHASH]) ? $result : $_GET['jsoncallback_'.FORMHASH].'('.$result.')';
		} else {
			echo $result;
		}
		exit;
	}

	function format($result) {
		switch (gettype($result)) {
			case 'array':
				foreach($result as $_k => $_v) {
					$result[$_k] = bigapp_core::format($_v);
				}
				break;
			case 'boolean':
			case 'integer':
			case 'double':
			case 'float':
				$result = (string)$result;
				break;
		}
		return $result;
	}

	function json($encode) {
		if(!empty($_GET['debug']) && defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) {
			return debug($encode);
		}
		return BIGAPPJSON::encode($encode);
	}

	function getvalues($variables, $keys, $subkeys = array()) {
		$return = array();
		$variables = (array)$variables;
		foreach($variables as $key => $value) {
			foreach($keys as $k) {
				if($k{0} == '/' && preg_match($k, $key) || $key == $k) {
					if($subkeys) {
						$return[$key] = bigapp_core::getvalues($value, $subkeys);
					} else {
						if(!empty($value) || !empty($_GET['debug']) || (is_numeric($value) && intval($value) === 0 )) {
							$return[$key] = is_array($value) ? bigapp_core::arraystring($value) : (string)$value;
						}
					}
				}
			}
		}
		return $return;
	}

	function arraystring($array) {
		foreach($array as $k => $v) {
			$array[$k] = is_array($v) ? bigapp_core::arraystring($v) : (string)$v;
		}
		return $array;
	}

	function variable($variables = array()) {
		global $_G;
		if(in_array('mobileoem', $_G['setting']['plugins']['available'])) {
			$check = C::t('#mobileoem#mobileoem_member')->fetch($_G['uid']);
		}
		$globals = array(
			'cookiepre' => $_G['config']['cookie']['cookiepre'],
			'auth' => $_G['cookie']['auth'],
			'saltkey' => $_G['cookie']['saltkey'],
			'member_uid' => $_G['member']['uid'],
			'member_username' => $_G['member']['username'],
			'member_avatar' => avatar($_G['member']['uid'], 'big', true),
			'groupid' => $_G['groupid'],
			'formhash' => FORMHASH,
			'ismoderator' => $_G['forum']['ismoderator'],
			'readaccess' => $_G['group']['readaccess'],
			'notice' => array(
				'newpush' => $check['newpush'] ? 1 : 0,
				'newpm' => dintval($_G['member']['newpm']),
				'newprompt' => dintval(($_G['member']['newprompt'] - $_G['member']['category_num']['mypost']) >= 0 ? ($_G['member']['newprompt'] - $_G['member']['category_num']['mypost']) : 0),
				'newmypost' => dintval($_G['member']['category_num']['mypost']),
			)
		);
		$globals['member_avatar'] = str_replace("\r", '', $globals['member_avatar']);
		$globals['member_avatar'] = str_replace("\n", '', $globals['member_avatar']);
		if(!empty($_GET['submodule']) == 'checkpost') {
			$apifile = 'source/plugin/mobile/api/'.$_GET['version'].'/sub_checkpost.php';
			if(file_exists($apifile)) {
				require_once $apifile;
				$globals = $globals + mobile_api_sub::getvariable();
			}
		}
		$pluginvariables = array();
		$xml = array(
			'Version' => $_GET['version'],
			'Charset' => strtoupper($_G['charset']),
			'Variables' => array_merge($globals, $variables),
		);
		if($pluginvariables) {
			$xml['pluginVariables'] = $pluginvariables;
		}
		if(!empty($_G['messageparam'])) {
			$message_result = lang('plugin/mobile', $_G['messageparam'][0], $_G['messageparam'][2]);
			if($message_result == $_G['messageparam'][0]) {
				$vars = explode(':', $_G['messageparam'][0]);
				if (count($vars) == 2) {
					$message_result = lang('plugin/' . $vars[0], $vars[1], $_G['messageparam'][2]);
					$_G['messageparam'][0] = $vars[1];
				} else {
					$message_result = lang('message', $_G['messageparam'][0], $_G['messageparam'][2]);
				}
			}
			$message_result = strip_tags($message_result);

			if(defined('IS_WEBVIEW') && IS_WEBVIEW && in_array('mobileoem', $_G['setting']['plugins']['available'])) {
				include_once DISCUZ_ROOT.'./source/plugin/mobileoem/discuzcode.func.php';
				include mobileoem_template('common/showmessage');
				if(!empty($_GET['debug'])) {
					exit;
				}
				$content = ob_get_contents();
				ob_end_clean();
				$xml['Variables']['datatype'] = -1;
				$xml['Variables']['webview_page'] = $content;
				return $xml;
			}

			if($_G['messageparam'][4]) {
				$_G['messageparam'][0] = "custom";
			}
			if ($_G['messageparam'][3]['login'] && !$_G['uid']) {
				$_G['messageparam'][0] .= '//' . $_G['messageparam'][3]['login'];
			}
			$xml['Message'] = array("messageval" => $_G['messageparam'][0], "messagestr" => $message_result);
			if($_GET['mobilemessage']) {
				$return = bigapp_core::json($xml);
				header("HTTP/1.1 301 Moved Permanently");
				header("Location:discuz://" . rawurlencode($_G['messageparam'][0]) . 
						"//" . rawurlencode(diconv($message_result, $_G['charset'], "utf-8")) . ($return ? "//" . rawurlencode($return) : '' ));
				exit;
			}
		}
		return $xml;
	}

	function diconv_array($variables, $in_charset, $out_charset) {
		foreach($variables as $_k => $_v) {
			if(is_array($_v)) {
				$variables[$_k] = bigapp_core::diconv_array($_v, $in_charset, $out_charset);
			} elseif(is_string($_v)) {
				$variables[$_k] = diconv($_v, $in_charset, $out_charset);
			}
		}
		return $variables;
	}

	function make_cors($request_method, $origin = '') {

		$origin = $origin ? $origin : REQUEST_METHOD_DOMAIN;

		if ($request_method === 'OPTIONS') {
			header('Access-Control-Allow-Origin:'.$origin);

			header('Access-Control-Allow-Credentials:true');
			header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
			header('Access-Control-Max-Age:1728000');
			header('Content-Type:text/plain charset=UTF-8');
			header("status: 204");
			header('HTTP/1.0 204 No Content');
			header('Content-Length: 0',true);
			//header('Content-Type: text/html',true);
			flush();
		}

		if ($request_method === 'POST') {

			header('Access-Control-Allow-Origin:'.$origin);
			header('Access-Control-Allow-Credentials:true');
			header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
		}

		if ($request_method === 'GET') {

			header('Access-Control-Allow-Origin:'.$origin);
			header('Access-Control-Allow-Credentials:true');
			header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
		}
	}

	function usergroupIconId($groupid) {
		global $_G;
		if($_G['cache']['usergroupIconId']) {
			return $_G['cache']['usergroupIconId']['variable'][$groupid];
		}
		loadcache('usergroupIconId');
		if(!$_G['cache']['usergroupIconId'] || TIMESTAMP - $_G['cache']['usergroupIconId']['expiration'] > 3600) {
			loadcache('usergroups');
			$memberi = 0;
			$return = array();
			foreach($_G['cache']['usergroups'] as $groupid => $data) {
				if($data['type'] == 'member') {
					if(!$memberi && $groupid == $_G['setting']['newusergroupid']) {
						$memberi = 1;
					}
					if($memberi > 0) {
						$return[$groupid] = $memberi++;
					}
				} elseif($data['type'] == 'system' && $groupid < 4) {
					$return[$groupid] = 'admin';
				} elseif($data['type'] == 'special') {
					$return[$groupid] = 'special';
				}
			}
			savecache('usergroupIconId', array('variable' => $return, 'expiration' => TIMESTAMP));
			return $return[$groupid];
		} else {
			return $_G['cache']['usergroupIconId']['variable'][$groupid];
		}
	}

	function activeHook($module, $mobileapihook, &$param, $isavariables = false) {
		global $_G;
		if($isavariables) {
			$mobileapihook[$module] = array(
			    'variables' => $mobileapihook[$module]['variables']
			);
		}
		foreach($mobileapihook[$module] as $hookname => $hooks) {
			foreach($hooks as $plugin => $hook) {
				if(!$hook['allow'] || !in_array($plugin, $_G['setting']['plugins']['available'])) {
					continue;
				}
				if(!preg_match('/^[\w\_\.]+\.php$/i', $hook['include'])) {
					continue;
				}
				include_once DISCUZ_ROOT . 'source/plugin/' . $plugin . '/' . $hook['include'];
				if(!class_exists($hook['class'], false)) {
					continue;
				}
				if(!isset($pluginclasses[$hook['class']])) {
					$pluginclasses[$hook['class']] = new $hook['class'];
				}
				if(!method_exists($pluginclasses[$hook['class']], $hook['method'])) {
					continue;
				}
				if(!$isavariables) {
					$value[$module.'_'.$hookname][$plugin] = $pluginclasses[$hook['class']]->$hook['method']($param);
				} else {
					$pluginclasses[$hook['class']]->$hook['method']($param);
				}
			}
		}
		if(!$isavariables) {
			return $value;
		}
	}
}

?>
