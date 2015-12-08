<?php
/**
* @file search.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$body = array(
		'formhash' => $_GET['formhash'],
		'srchtxt' => $_GET['keyword'],
		'searchsubmit' => 'yes',
		'tpp' => 10,
		'page' => 1,
		);
if(isset($_GET['tpp'])){
	$body['tpp'] = $_GET['tpp'];
}
if(isset($_GET['page'])){
	$body['page'] = $_GET['page'];
}

BigAppAPI::$tpp = $body['tpp'];
BigAppAPI::$page = $body['page'];
if(is_null($body['formhash']) || is_null($body['srchtxt'])){
	@bigapp_core::result(array('error' => 'param_error'));	
}
if(function_exists('iconv')){
	$body['srchtxt'] = iconv('UTF-8', BIGAPP_CHARSET . '//ignore', $body['srchtxt']);
}else{
	$body['srchtxt'] = mb_convert_encoding($body['srchtxt'], BIGAPP_CHARSET, 'UTF-8');
}
$proto = 'http://';
if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){
	$proto = 'https://';
}
if(isset($_SERVER['SCRIPT_NAME'])){
	$url = $proto . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
}else{
	$url = $proto . $_SERVER['HTTP_HOST'];
}
$url = str_replace('api/mobile/iyz_index.php', '', $url);
$url = str_replace('source/plugin/bigapp/iyz_index.php', '', $url);
$url = rtrim($url, '/') . '/search.php?mod=forum';

$ch = curl_init();
$opt = array(
		CURLOPT_URL     => $url,
		CURLOPT_POST    => 1,
		CURLOPT_POSTFIELDS => http_build_query($body),
		CURLOPT_HEADER  => 1,
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_TIMEOUT         => 5,
		CURLOPT_NOBODY			=> 0,
		CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
		);
curl_setopt_array($ch, $opt);
$cookieStr = '';
foreach ($_COOKIE as $key => $value){
    if(empty($cookieStr)){
        $cookieStr = $key . '=' . urlencode($value);
    }else{
        $cookieStr = $cookieStr . ';' . $key . '=' . urlencode($value);
    }
}
curl_setopt($ch, CURLOPT_COOKIE, $cookieStr);
$data = @curl_exec($ch);
//var_dump($data);exit;
curl_close($ch);
$start = stripos($data, 'location: ');
if(false === $start){
	@bigapp_core::result(array('error' => 'search_failed'));
}
$start += strlen('location: ');
$end = strpos($data, "\r\n", $start);
if(false === $end){
	@bigapp_core::result(array('error' => 'search_failed'));
}
$locUrl = substr($data, $start, $end - $start);
if(false === ($start2 = strpos($locUrl, 'search.php?'))){
	@bigapp_core::result(array('error' => 'search_failed'));
}
$queryString = substr($locUrl, $start2 + strlen('search.php?'));
parse_str($queryString, $arrParam);
foreach ($arrParam as $k => $v){
	$_GET[$k] = $v;
}
unset($_GET['charset']);
include_once 'search.php';

class BigAppAPI {
	public static $tpp = 10;
	public static $page = 1;
	function common() {
		global $_G;
		if(isset($_G['setting']['bigapp_settings'])){
			$tmp = unserialize($_G['setting']['bigapp_settings']);
		}
		if(!isset($tmp['enable_pic_opt'])){
			$tmp['enable_pic_opt'] = 1;
		}
		BigAppConf::$enablePicOpt = (!!$tmp['enable_pic_opt']);
	}

	function output() {
		$total = 0;
		if(isset($GLOBALS['index']['num'])){
			$total = $GLOBALS['index']['num'];
		}
		$ret['tpp'] = BigAppAPI::$tpp;
		$ret['page'] = BigAppAPI::$page;
		$ret['need_more'] = 1;
		if(($ret['page'] - 1) * $ret['tpp'] + count($GLOBALS['threadlist']) >= $total){
			$ret['need_more'] = 0;
		}
		$ret['thread_list']  = @bigapp_core::getvalues($GLOBALS['threadlist'], array('/^\d+$/'), 
				array('tid', 'fid', 'author', 'authorid', 'subject', 'dateline', 'lastpost', 'lastposter', 'views', 'replies', 'heats', 'forumname', 'message'));
		foreach ($ret['thread_list'] as &$thread){
			if(!isset($thread['message'])){
				$thread['message'] = '';
			}
			if(!isset($thread['subject'])){
				$thread['subject'] = '';
			}
			$thread['message'] = preg_replace('/<.*?>/', '', $thread['message']);
			$thread['subject'] = preg_replace('/<.*?>/', '', $thread['subject']);
			$thread['avatar'] = avatar($thread['authorid'], 'big', true);
		}
		unset($thread);
		$ret['thread_list'] = array_values($ret['thread_list']);
		/////////////////////////////////////////////////////////////////
		//BigAppAPI::_getDetails($ret['thread_list']);
		/////////////////////////////////////////////////////////////////
		@bigapp_core::result(bigapp_core::variable($ret));
	}


	//////////////////////////////////////////////////////////////////
        // copy from forumdisplay.php
	static protected $collect = array();
	protected function _getPictures(&$threadInfo)
	{
		global $_G;
		$collect = &BigAppAPI::$collect;
		foreach ($threadInfo as $tid => &$info){
			$collect = array();
			$message = str_replace("\r", '', $info['message']);
			$message = str_replace("\n", '', $message);
			$message = str_replace('\r', '', $message);
			$message = str_replace('\n', '', $message);
			if(function_exists('iconv')){
				$message = iconv(CHARSET, 'UTF-8//ignore', $message);
			}else{
				$message = mb_convert_encoding($message, 'UTF-8', CHARSET);
			}
			$message = preg_replace_callback('/\[img.*?\](.*?)\[\/img\]|\[attach\]([0-9]+)\[\/attach\]|\[i.*\](.*)' . 
					'\[\/i\]|\[.*?\]|\\n|\\r/', 'BigAppAPI::imgCallback', $message);
			if(function_exists('mb_substr')){
				$message = mb_substr($message, 0, 1000, 'UTF-8');
			}else{
				$message = substr($message, 0, 2000);
			}
			/*$find = array(':)', ':(', ':D', ':\'(', ':@', ':o', ':P', ':$', ';P', ':L', ':Q', ':lol', ':loveliness:', 
					':funk:', ':curse:', ':dizzy:', ':shutup:', ':sleepy:', ':hug:', ':victory:', ':time:', ':kiss:', ':handshake', ':call:');
			$replace = array("\xF0\x9F\x98\x8C", "\xF0\x9F\x98\x94",  "\xF0\x9F\x98\x83", "\xF0\x9F\x98\xAD", "\xF0\x9F\x98\xA0", 
					"\xF0\x9F\x98\xB2", "\xF0\x9F\x98\x9C", "\xF0\x9F\x98\x86", "\xF0\x9F\x98\x9D",  "\xF0\x9F\x98\x93",  "\xF0\x9F\x98\xAB", 
					"\xF0\x9F\x98\x81", "\xF0\x9F\x98\x8A", "\xF0\x9F\x98\xB1", "\xF0\x9F\x98\xA4", "\xF0\x9F\x98\x96", "\xF0\x9F\x98\xB7", 
					"\xF0\x9F\x98\xAA", "\xF0\x9F\x98\x9A", "\xE2\x9C\x8C", "\xE2\x8F\xB0", "\xF0\x9F\x92\x8B", "\xF0\x9F\x91\x8C", "\xF0\x9F\x93\x9E");
			$message = str_replace($find, $replace, $message);*/
			loadcache(array('smilies', 'smileytypes'));
	
			foreach($_G['cache']['smilies']['replacearray'] as $id => $img) {
			    $pattern = $_G['cache']['smilies']['searcharray'][$id];
			    $message = preg_replace($pattern, '[表情]', $message);
			}
			
			if(function_exists('mb_strlen')){
				if(mb_strlen($message, 'UTF-8') > 30){
					$message = mb_substr($message, 0, 30, 'UTF-8') . '...';
				}
			}else{
				if(strlen($message) > 60){
					$message = substr($message, 0, 30) . '...';
				}
			}
			$info['message'] = '__DONT_DICONV_TO_UTF8___' . $message;
			$attachments = array();
			$infoAttrs = $info['attachments'];
			foreach ($collect as $attach){
				if(is_numeric($attach)){
					if(isset($infoAttrs[$attach])){
						$url = ($infoAttrs[$attach]['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
						$url .= $infoAttrs[$attach]['attachment'];
						$tmp = parse_url($url);
						if(!isset($tmp['scheme'])){
							$url = ApiUtils::getDzRoot() . $url;
						}
						$info['attachment_urls'][] = $url;
						unset($infoAttrs[$attach]);
					}
					continue;
				}
				$tmp = parse_url($attach);
				if(!isset($tmp['scheme'])){
					$url = ApiUtils::getDzRoot() . $attach;
				}else{
					$url = str_replace('source/plugin/mobile/', '', $attach);
					$url = str_replace('source/plugin/mobile/', '', $url);
				}
				$info['attachment_urls'][] = $url;
			}
			global $_G;
			//feed others
			foreach ($infoAttrs as $aid => $aInfo){
				$url = ($aInfo['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
				$url .= $aInfo['attachment'];
				$tmp = parse_url($url);
				if(!isset($tmp['scheme'])){
					$url = ApiUtils::getDzRoot() . $url;
				}
				$info['attachment_urls'][] = $url;
			}
		}
		unset($info);
	}

	protected static function imgCallback($matches)
	{
		$collect = &BigAppAPI::$collect;
		if(isset($matches[1]) && !empty($matches[1])){
			$collect[] = $matches[1];
		}else if(isset($matches[2]) && !empty($matches[2])){
			$collect[] = $matches[2];
		}
		return '';
	}

	protected function _getDetails(&$list)
	{
		//check whether thread list image mode is open
		global $_G;
		if(isset($_G['setting']['bigapp_settings'])){
    		$_G['setting']['bigapp_settings'] = unserialize($_G['setting']['bigapp_settings']);
		}
		if(!isset($_G['setting']['bigapp_settings']['threadlist_image_mode']) || !$_G['setting']['bigapp_settings']['threadlist_image_mode']){
			return ;
		}
		$tids = array();
		foreach ($list as $l){
			$tids[] = $l['tid'];
		}
		if(empty($tids)){
			return ;
		}
		$sql = 'SELECT pid, tid, first FROM ' . DB::table('forum_post') . ' WHERE tid IN (' . implode(', ', $tids) . ')';
		$query = DB::query($sql);
		$threadInfo = array();
		$pids = array();
		while($tmp = DB::fetch($query)){
			//if(!isset($threadInfo[$tmp['tid']]) || $threadInfo[$tmp['tid']]['pid'] > $tmp['pid']){
			if(!!$tmp['first']){
				if(isset($pids[$threadInfo[$tmp['tid']]['pid']])){
					unset($pids[$threadInfo[$tmp['tid']]['pid']]);
				}
				$threadInfo[$tmp['tid']] = array('pid' => $tmp['pid']);
				$pids[$tmp['pid']] = $tmp['pid'];
			}
		}
		if(empty($pids)){
			return ;
		}
		$pids = array_values($pids);
		$sql = 'SELECT pid, tid, message FROM ' . DB::table('forum_post') . ' WHERE pid IN (' . implode(', ', $pids) . ')';
		$query = DB::query($sql);
		while($tmp = DB::fetch($query)){
			$threadInfo[$tmp['tid']]['message'] = $tmp['message'];
		}
		$sql = 'SELECT aid, tid, tableid, pid FROM ' . DB::table('forum_attachment') . ' WHERE pid IN (' . implode(', ', $pids) . ')';
		$tbIdx = array();
		$query = DB::query($sql); 
		while($tmp = DB::fetch($query)){
			if($tmp['tableid'] < 10){
				$threadInfo[$tmp['tid']]['aid'][] = $tmp['aid'];
				$tbIdx[$tmp['tableid']][] = $tmp['aid'];
			}
		}
		foreach ($tbIdx as $tableId => $aids){
			$sql = 'SELECT aid, tid, attachment, description, remote, isimage FROM ' . DB::table('forum_attachment_' . 
					$tableId) . ' WHERE aid IN (' . implode(', ', $aids) . ')';
			$query = DB::query($sql);
			while($tmp = DB::fetch($query)){
				$isImage = $tmp['isimage'];
				if($tmp['isimage'] && !$_G['setting']['attachimgpost']){
					$isImage = 0;
				}
				if($isImage){
					$threadInfo[$tmp['tid']]['attachments'][$tmp['aid']] = array('attachment' => $tmp['attachment'], 
							'description' => $tmp['description'], 'remote' => $tmp['remote'], 'isimage' => $isImage);
				}
			}
		}
		BigAppAPI::_getPictures($threadInfo);
		foreach ($list as &$l){
			$l['attachment_urls'] = array();
			$l['message_abstract'] = '';
			if(isset($threadInfo[$l['tid']]['message'])){
				$l['message_abstract'] = $threadInfo[$l['tid']]['message'];
			}
			if(isset($threadInfo[$l['tid']]['attachment_urls'])){
				$l['attachment_urls'] = $threadInfo[$l['tid']]['attachment_urls'];
			}
			if(true === BigAppConf::$enablePicOpt){
				foreach ($l['attachment_urls'] as &$_url){
					if(ApiUtils::isOptFix($_url)){
						$_url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:optpic&mod=__x__&size=' . urlencode(BigAppConf::$thumbSize) . '&url=' . urlencode($_url);
						$_url = str_replace('source/plugin/mobile/', '', $_url);
						$_url = str_replace('source/plugin/bigapp/', '', $_url);
					}
				}
				unset($_url);
			}
		}
		unset($l);
	}
	////////////////////////////////////////////////////////////////

}

?>
