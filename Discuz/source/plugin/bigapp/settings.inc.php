<?php
/**
* @file settings.inc.php
* @author youzu
* @version 1.0.0
* @date 2015-11-10
*/

define("FILE_PATH", dirname(__FILE__));

require_once libfile('function/core');
require_once FILE_PATH . '/bigappjson.class.php';
require_once FILE_PATH . '/libs/appdesign.inc.php';

//REQUEST_METHOD_DOMAIN
AppDesign::makeCors($_SERVER['REQUEST_METHOD'], '*');

if(isset($_GET["method"]) && $_GET["method"] == "save") { //setting数据保存，修改
   
   $threadlist_image_mode = '1';
   $enable_pic_opt = '1';
   $reply_button_type = '0';
   
   if(isset($_POST['settings']['threadlist_image_mode']) && is_numeric($_POST['settings']['threadlist_image_mode'])) {
	   $threadlist_image_mode = $_POST['settings']['threadlist_image_mode'];
   }
   
   if(isset($_POST['settings']['enable_pic_opt']) && is_numeric($_POST['settings']['enable_pic_opt'])) {
	   $enable_pic_opt = $_POST['settings']['enable_pic_opt'];
   }
   
   if(isset($_POST['settings']['reply_button_type']) && is_numeric($_POST['settings']['reply_button_type'])) {
	   $reply_button_type = $_POST['settings']['reply_button_type'];
   }
   
	$setting = array(
			'threadlist_image_mode' => $threadlist_image_mode,
			'enable_pic_opt' => $enable_pic_opt,
			'reply_button_type' => $reply_button_type,
    );
	$settings = array('bigapp_settings' => $setting);
	C::t('common_setting')->update_batch($settings);
	
	$result['code'] = '0';
	$result['msg'] = "Succ";
	echo BIGAPPJSON::encode($result);
	die(0); 
} else if(isset($_GET["method"]) && $_GET["method"] == "get") { //获取setting数据
	require_once libfile('function/cache');
	updatecache('setting');

	if(isset($_G['setting']['bigapp_settings'])){
		$_G['setting']['bigapp_settings'] = unserialize($_G['setting']['bigapp_settings']);
	}
	//主题列表多图样式
	if(!isset($_G['setting']['bigapp_settings']['threadlist_image_mode'])){
		$_G['setting']['bigapp_settings']['threadlist_image_mode'] = '1';
	}
	
	//图片优化是否开启
	if(!isset($_G['setting']['bigapp_settings']['enable_pic_opt'])){
		$_G['setting']['bigapp_settings']['enable_pic_opt'] = '1';
	}
	//回复按钮样式
	if(!isset($_G['setting']['bigapp_settings']['reply_button_type'])){
		$_G['setting']['bigapp_settings']['reply_button_type'] = '0';
	}

	$setting = array(
			'threadlist_image_mode' => $_G['setting']['bigapp_settings']['threadlist_image_mode'],
			'enable_pic_opt' => $_G['setting']['bigapp_settings']['enable_pic_opt'],
			'reply_button_type' => $_G['setting']['bigapp_settings']['reply_button_type'],
   );

	$result['data'] = $setting;
	$result['code'] = '0';
	$result['msg'] = "Succ";
	echo BIGAPPJSON::encode($result);
	die(0);
}
  

?>
