<?php
require_once dirname(dirname(__FILE__)) . '/bigappjson.class.php';
require_once dirname(__FILE__) . '/localdns.inc.php';
class BkSvr
{
	protected $_ak = null;
	protected $_sk = null;
	protected $_expire = null;
	protected $_v = null;

	public function __construct($ak, $sk, $expire = null, $v = null)
	{
		$this->_ak = $ak;
		$this->_sk = $sk;
		$this->_expire = $expire;
		$this->_v = $v;
	}

	protected function _makeSign($isGet, $url, &$param)
	{
		$tmp = parse_url($url);
		if(!is_array($tmp) || !isset($tmp['scheme']) || !isset($tmp['host'])){
			return false;
		}
		$str = '';
		if(isset($tmp['scheme'])){
			$str .= $tmp['scheme'] . '://';
		}
		$split = '';
		if(isset($tmp['user'])){
			$str .= $tmp['user'];
			$split = '@';
		}
		if(isset($tmp['pass'])){
			$str .= ':' . $tmp['pass'];
			$split = '@';
		}
		$str .= $split . $tmp['host'];
		if(isset($tmp['port'])){
			$str .= ':' . $tmp['port'];
		}
		if(isset($tmp['path'])){
			$str .= $tmp['path'];
		}
		if(isset($tmp['query'])){
			parse_str($tmp['query'], $__r);
			$param = array_merge($param, $__r);
		}
		$param['app_key'] = $this->_ak;
		if(isset($param['app_secret'])){
			unset($param['app_secret']);
		}
		ksort($param);
		$basicString = 'GET';
		if(!$isGet){
			$basicString = 'POST';
		}
		$basicString .= $str;
		foreach ($param as $k => $v){
			$basicString .= $k . '=' . $v;
		}
		$basicString .= $this->_sk;
		/*if($param['method'] == 'check_update'){
			echo $basicString;
		}*/
		return md5(urlencode($basicString));
	}

	protected function _checkCode(&$param)
	{
		$chrset = strtoupper(CHARSET);
		if(function_exists('iconv')){
			foreach ($param as &$v){
				$v = iconv(CHARSET, 'UTF-8//ignore', $v);
			}
		}else{
			foreach ($param as &$v){
				$v = mb_convert_encoding($v, 'UTF-8', CHARSET);
			}	
		}
		if(isset($v)){
			unset($v);
		}
	}
	
	public function getInfo($url, $param, $checkRes = true, $checkCode = true)
	{
		if(!isset($param['timestamp'])){
			$param['timestamp'] = time();
		}
		if(!isset($param['expire']) && !is_null($this->_expire)){
			$param['expire'] = $this->_expire;
		}
		if(!isset($param['v']) && !is_null($this->_v)){
			$param['v'] = $this->_v;
		}
		true === $checkCode && $this->_checkCode($param);
		$sign = $this->_makeSign(true, $url, $param);
		$param['sign'] = $sign;
		$tmp = Utils::addUrlQueryString($url, $param);
		$rawResult = $this->_curlInfo($tmp);
		$arrResult = @BIGAPPJSON::decode($rawResult, true);
		if(true === $checkRes){
			if(is_array($arrResult) && $arrResult['error_code'] == 0){
				runlog('bigapp', "need check and succ, url: ${tmp}, ret: $rawResult");
				return $arrResult['data'];
			}
			runlog('bigapp', "need check but failed, url: ${tmp}, ret: $rawResult");
			return false;
		}
		runlog('bigapp', "need not check, finished, url: ${tmp}, ret: $rawResult");
		return $arrResult;
	}

	public function getInfoByPost($url, $param, $checkRes = true)
	{
		if(!isset($param['timestamp'])){
			$check['timestamp'] = time();
		}
		if(!isset($param['expire']) && !is_null($this->_expire)){
			$check['expire'] = $this->_expire;
		}
		if(!isset($param['v']) && !is_null($this->_v)){
			$check['v'] = $this->_v;
		}
		$this->_checkCode($param);
		$sign = $this->_makeSign(true, $url, $check);
		$check['sign'] = $sign;
		$tmp = Utils::addUrlQueryString($url, $check);
		$rawResult = $this->_curlInfo($tmp, $param);
		$arrResult = @BIGAPPJSON::decode($rawResult, true);
		if(true === $checkRes){
			if(is_array($arrResult) && $arrResult['error_code'] == 0){
				runlog('bigapp', "post, need check and succ, url: ${tmp}, ret: $rawResult");
				return $arrResult['data'];
			}
			runlog('bigapp', "post, need check but failed, url: ${tmp}, ret: $rawResult");
			return false;
		}
		runlog('bigapp', "post, need not check, finished, url: ${tmp}, ret: $rawResult");
		return $arrResult;
	}
	protected function _curlInfo($url, $postData = null)
	{
        $data = "";
        $urlarr = array($url);
		$domres = LocalDNS::getDomainUrls($url);
		$domain = $domres["domain"];
		if (count($domres["urlarr"])>0) {
			$urlarr = array_merge($urlarr, $domres["urlarr"]);
		}
        foreach ($urlarr as $k => $ithurl) {
			$ch = curl_init();
            if ($k!=0 && $domain!="") {
				$header = array ("Host: $domain");
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			} 

			if(!is_null($postData)){
				$curlPost = http_build_query($postData);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
				runlog('bigapp', 'post method, post filed: ' . $curlPost);
			}
			curl_setopt($ch, CURLOPT_URL, $ithurl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
			$data      = curl_exec($ch);
			$errorInfo = curl_error($ch);
            $httpCode  = curl_getinfo($ch,CURLINFO_HTTP_CODE);
			if($httpCode!=200 || !empty($errorInfo)){
				runlog('bigapp', 'url: ' . $ithurl . ', httpcode: '.$httpCode.', error: ' . $errorInfo);
				curl_close($ch);
                continue;
			}
			//runlog('bigapp', 'url: ' . $ithurl . ', httpcode: '.$httpCode.', error: ' . $errorInfo);
			if(empty($data) && empty($postData)){
				runlog('bigapp', 'get info by curl failed, try to use file_get_contents');
				curl_close($ch);
                break;
			}
			curl_close($ch);
            runlog('bigapp', "get info by curl succ. [$url] [$ithurl]");
			return $data;
		}
        $tmp = file_get_contents($url);
		if(!empty($tmp)){
			runlog('bigapp', 'use file_get_contents succ, use this result');
			$data = $tmp;
		}
        return $data;
	}
}
//$tmp = new BkSvr('11', '22');
//var_dump($tmp->getInfo('http://www.baidu.com/?a=b&c=1', array('xxx' => 2, 'yyy' => 3)));
?>
