<?php
/**
* @file uploadpic.inc.php
* @Brief picture uploading transfer file
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
require_once dirname(__FILE__) . '/conf/conf.inc.php';
require_once dirname(__FILE__) . '/libs/utils.inc.php';
require_once dirname(__FILE__) . '/bigappjson.class.php';

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
runlog('bigapp', 'start to upload a picture');
$key = 'icon_image';
if(isset($_REQUEST['key']) && $_REQUEST['key']){
	$key = $_REQUEST['key'];
}
$_key = $key;
if(preg_match('/([a-zA-Z_]+)[0-9]*$/', $key, $matches)){
	$_key = $matches[1];
}
if(!isset(BigAppConf::$imgRequire[$_key])){
	returnData(1, 'invalid_param',$_key."###".$key);
}
$size = BigAppConf::$imgRequire[$_key];
if(isset($_REQUEST['key']) && $_REQUEST['key']){
	$key = $_REQUEST['key'];
}
checkUpload($key, $size);
$url = uploadFile($_FILES[$key]);
if(!isset($size['need_compress']) || 1 == $size['need_compress']){
	$realUrl = Utils::addUrlQueryString($_G['siteurl'] . 'plugin.php', array('size' => $size['width'] . '_' . $size['height'], 'url' => $url, 'id' => 'bigapp:optpic'));
}else{
	$realUrl = $url;
}
runlog('bigapp', 'end to upload a picture, return url: ' . $realUrl);
returnData(0, 'SUCC', array('imgurl' => $realUrl));

function uploadFile($file){
	global $_G;
	$upload = new discuz_upload();
	if(!$upload->init($file, 'common', rand(0, 100000), 'bigapp_' . md5_file($file['tmp_name']))) {
		returnData(7, 'init discuz init failed');
	}
	if(!$upload->save()){
		returnData(8, 'save file as attachment failed');
	}
	$url = $upload->attach['attachment'];
	if(strpos($_G['setting']['attachurl'],'http') === false ){
		$url = $_G['siteurl'] . $_G['setting']['attachurl'] . 'common/' . $url;
	}else{
		$url = $_G['setting']['attachurl'] . 'common/' . $url;
	}
	return $url;
}

function errorMap($errNo)
{
	$errMap = array(
		'1' => '文件大小超出服务器空间大小',
		'2' => '文件超出浏览器限制大小',
		'3' => '文件仅部分被上传',
		'4' => '未找到要上传的文件',
		'5' => '服务器临时文件丢失',
		'6' => '文件写入到临时文件出错',
	);
	$errStr = '上传失败';
	if(isset($errMap[$errNo])){
		$errStr = $errMap[$errNo];
	}
	if(function_exists('iconv')){
		$errStr = iconv('UTF-8', CHARSET . '//ignore', $errStr);
	}else{
		$errStr = mb_convert_encoding($errStr, CHARSET, 'UTF-8');
	}
	return $errStr;
}

function checkUpload($key, $size)
{
	if(UPLOAD_ERR_OK !== $_FILES[$key]['error']){
		returnData(2, 'upload_file_failed', errorMap($_FILES[$key]['error']));
	}
	$fileName = $_FILES[$key]['name'];
	$fileSize = $_FILES[$key]['size'];
	$tmpFile = $_FILES[$key]['tmp_name'];
	$os = substr(PHP_OS, 0, 3);
	$os = strtoupper($os);
	if('WIN' !== $os && (!is_file($tmpFile) || !is_readable($tmpFile))){
		$str = 'it is not a file [ file: ' . $tmpFile . ' ]';
		if(!is_readable($tmpFile)){
			$str = 'temp file is not readable [ file: ' . $tmpFile . ' ' . $os . ' ]';
		}
		returnData(3, 'upload_file_failed', $str);
	}
	$info = @getimagesize($tmpFile);
	if(false === $info){
		returnData(4, 'invalid_file_type');
	}
	
	$width = $info[0];
	$height = $info[1];
	$mimeType = strtolower($info['mime']); //以实际检查为准
	if(isset($size['allow_postfix']) && !in_array($mimeType, $size['allow_postfix'])){
		returnData(5, 'invalid_file_png', $mimeType);
	}
	if(isset($size['need_compress']) && 0 == $size['need_compress']){
		if($width != $size['width'] || $height !== $size['height']){
			returnData(6, 'invalid_file_size_1024_1024', $info);
		}
	}
	if($width < $size['width'] || $height < $size['height']){
		returnData(7, 'invalid_file_size',$info);
	}
	if($fileSize > $size['size']){
		returnData(8, 'invalid_file_too_big');
	}
	return array($tmpFile, $fileName, $fileSize, $width, $height, $mimeType);
}

function returnData($errorCode, $errorMsg, $data = array())
{
	if(function_exists('ob_clean')){
		ob_end_clean();
	}
	$ret = array(
		'error_code' => $errorCode,
		'error_msg' => lang('plugin/bigapp', $errorMsg),
		'data' => $data,
	);
	//header('Access-Control-Allow-Origin: *');
	//header('Access-Control-Allow-Credentials:true');
    if(isset($_GET['callback'])){
        echo $_GET['callback']."(".BIGAPPJSON::encode($ret).")";
    }else{
        echo BIGAPPJSON::encode($ret);
    }
    $content = ob_get_contents();
    ob_end_clean();
	global $_G;
	function_exists('ob_gzhandler') && $_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
    echo $content;
	runlog('bigapp', 'returned data [ ' . BIGAPPJSON::encode($ret) . ' ]');
	exit(0);
}
?>
