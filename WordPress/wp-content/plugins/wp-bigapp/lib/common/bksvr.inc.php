<?php
require_once (dirname(__FILE__)."/bigappjson.class.php");
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
		$chrset = strtoupper(get_bloginfo('charset'));
	
		if(function_exists('iconv')){
			foreach ($param as &$v){
				$v = iconv($chrset, 'UTF-8//ignore', $v);
			}
		}else{
			foreach ($param as &$v){
				$v = mb_convert_encoding($v, 'UTF-8', $chrset);
			}	
		}
		
		if(isset($v)){
			unset($v);
		}
	}
	
	public function getInfo($url, $param, $checkRes = true)
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
		
		$this->_checkCode($param);
		$sign = $this->_makeSign(true, $url, $param);
		$param['sign'] = $sign;
		
		$tmp = addUrlQueryString($url, $param);
		$arrResult = $this->curlInfo($tmp);
		if(true === $checkRes){
			if(is_array($arrResult) && $arrResult['error_code'] == 0){
				return $arrResult['data'];
			}
			return false;
		}
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
		$tmp = addUrlQueryString($url, $check);
		$arrResult = $this->curlInfo($tmp, $param);
		if(true === $checkRes){
			if(is_array($arrResult) && $arrResult['error_code'] == 0){
				return $arrResult['data'];
			}
			return false;
		}
		return $arrResult;
	}
	public function curlInfo($url, $postData = null) {
		$ch = curl_init();
		if(!is_null($postData)){
        	$curlPost = http_build_query($postData);
        	curl_setopt($ch, CURLOPT_POST, 1);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		}
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $data = curl_exec($ch);
		$errorInfo = curl_error($ch);
		if(!empty($errorInfo)){
            $data = array();
		}
        curl_close($ch);
		return BIGAPPJSON::decode($data);
	}

}
//$tmp = new BkSvr('11', '22');
//var_dump($tmp->getInfo('http://www.baidu.com/?a=b&c=1', array('xxx' => 2, 'yyy' => 3)));
?>
