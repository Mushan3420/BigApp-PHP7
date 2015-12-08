<?php
class Bigapp_Common{
	
	private static $instance = null;
	private static $requestId = 0;
	private static $print = false;
	private $response = null;
	public static function getInstance(){
		if( null  === self::$instance){
			self::$instance = new Bigapp_Common();
		}
		return self::$instance;
	}
	
	public function __construct(){
		if( 0 === self::$requestId ){
			self::$requestId = time().rand();
		}
		$this->response = array("requestid"=>self::$requestId,"error_code"=>0,"error_msg"=>"SUCC","data"=>array());
	}
	
	public function setResponse($result){
		if(isset($result['error_code']) && isset($result['error_msg'])){
			$this->response['error_code'] = $result['error_code'];
			$this->response['error_msg'] = $result['error_msg'];
			$this->response['data'] = isset($result['data'])?$result['data']:array();
			$this->response['common_info'] = isset($result['common_info'])?$result['common_info']:array();
		}else{
			$this->response['data'] = $result;
		}
		echo json_encode($this->response);
		$this->debug(json_encode($this->response));
		die();
	}
	
	public function getResponse(){
		return $this->response;
	}
	
	public function getRequestId(){
		return self::$requestId;
	}
	
	public function debug($debug){
		$bt = debug_backtrace ();
		if (isset ( $bt [0] ) && isset ( $bt [0] ['file'] )) {
			$c = $bt [0];
		} else if (isset ( $bt [1] ) && isset ( $bt [1] ['file'] )) {
			$c = $bt [1];
		} else if (isset ( $bt [2] ) && isset ( $bt [2] ['file'] )) { 
			$c = $bt [2];
		} else {
			$c = array ('file' => 'faint', 'line' => 'faint' );
		}
	    
		$str = date("Y-m-d H:i:s")."\t";
		$str .=  self::$requestId ."\t";
		$str .=  $_SERVER['REQUEST_URI'] ."\t";
		$str .=  $c ['file'] . ':' . $c ['line'] ."\t";
		
		$str .=  $debug ."\n";
		$this->writeLog($str);
	}
	
	public function writeLog($str,$path=null){
		if(! BigAppConf::$debug) return;
		if(null === $path){
			$path = dirname(dirname( __FILE__ ))."/log/debug.log";
		}
		$fd = @fopen ( $path, "a+" );
		if (is_resource ( $fd )) {
				fputs ( $fd, $str );
				fclose ( $fd );
				self::$print = true;
		} 
	}
	
	public function setResult($result,$path=null){
		$this->response['data'] = $this->formate($result,$path);
        $data = json_encode($this->response);
		$this->debug($data);
		return $data;
	}
	
	public function formate(&$result,$path=null){
		$fields = array();
		if(preg_match('/^\/users\/\d+/i',$path,$match)){
			$fields = array('ID','username','avatar','email','roles');
		}else if(preg_match('/^\/taxonomies\/category\/terms/i',$path,$match)){
			$fields = array('ID','name','count','parent','link');
		}else if(preg_match('/^\/posts/i',$path,$match)){
			$fields = array('ID','title','type','author','link','username','avatar','content'
			,'link','date','modified','featured_image','terms','category','name','parent'
			);
		}else if(preg_match('/^\/posts\/\d+/i',$path,$match)){
			$fields = array('ID','title','type','author','link','username','avatar','content'
			,'link','date','modified','featured_image','terms','category','name','parent'
			);
		}else if(preg_match('/^\/posts\/\d+\//i',$path,$match)){
			$fields = array('ID','title','type','author','link','username','avatar','content'
			,'link','date','modified','featured_image','terms','category','name','parent'
			);
		}
		if(is_array($result) && !empty($fields)){
			foreach($result as $key => &$value){
				if(is_array($value)){
					$this->formate($value,$path);
					if(empty($result[$key])){
						unset($result[$key]);
					}
				}else{
					if(!is_numeric($key) && !in_array($key,$fields)){
						unset($result[$key]);
					}
				}
			}
		}
		return $result;
	}
	
	public function __destruct(){
		if(!self::$print){
			//$output = ob_get_contents(); todo  
		}
	}
	
	
}
