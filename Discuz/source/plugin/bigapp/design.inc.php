<?php 

global $_G;

//非管理员账户登录，提示无权限访问
if(!isset($_G['groupid']) || $_G['groupid'] != 1) {
	exit('Access Denied');
}

define("FILE_PATH", dirname(__FILE__));

require_once FILE_PATH . '/conf/conf.inc.php';
require_once FILE_PATH . '/libs/utils.inc.php';
require_once FILE_PATH . '/libs/appdesign.inc.php';
require_once FILE_PATH . '/bigappjson.class.php';
require_once libfile('function/cache');

foreach(BigAppConf::$defaultButtonSkeleton as $key => &$value) {
	$value['name'] = Utils::converGbkString($value['name']);
}

//REQUEST_METHOD_DOMAIN
AppDesign::makeCors($_SERVER['REQUEST_METHOD'], '*');

if(isset($_GET["method"]) && $_GET["method"]=="global") {

		$btn_list = array(
		array(
			"id" => "1", 
			"value" => Utils::converGbkString("自定义tab")
		),
		array(
			"id" => "2", 
			"value" => Utils::converGbkString("版块")
		),
		array(
			"id" => "3",
			"value" => Utils::converGbkString("首页发帖")
		),
		array(
			"id" => "4",
		   "value" => Utils::converGbkString("消息")
		),
		array(
			"id" =>"5", 
			"value" => Utils::converGbkString("我的")
		),
		);

		$tab_list = array(
		array(
			"id" => "1", 
			"value" => Utils::converGbkString("单页面")
		),
		array(
			"id" => "2",
		   "value" => Utils::converGbkString("导航页面")
		),
		array(
			"id" => "3",
			"value" => Utils::converGbkString("WAP页面")
		),
		);

		$result = array(
			"btn_list" => $btn_list,
			"tab_list" => $tab_list,
		);

		echo BIGAPPJSON::encode($result);
		die(0);
} else if(isset($_GET["method"]) && $_GET["method"]=="global1") {
		$btn_list = array(
		array(
			"id" => "1", 
			"value" => Utils::converGbkString("自定义tab")
		),
		array(
			"id" => "2", 
			"value" => Utils::converGbkString("版块")
		),
		array(
			"id" => "3",
			"value" => Utils::converGbkString("首页发帖")
		),
		array(
			"id" => "4",
		   "value" => Utils::converGbkString("消息")
		),
		array(
			"id" =>"5", 
			"value" => Utils::converGbkString("我的")
		),
		);

		$tab_list = array(
		array(
			"id" => "1", 
			"value" => Utils::converGbkString("单页面")
		),
		array(
			"id" => "2",
		   "value" => Utils::converGbkString("导航页面")
		),
		array(
			"id" => "3",
			"value" => Utils::converGbkString("WAP页面")
		),
		);

		$result = array(
			"btn_list" => $btn_list,
			"tab_list" => $tab_list,
			"views" =>  AppDesign::getViewsData(),
		);

		echo BIGAPPJSON::encode($result);
		die(0);
} else if(isset($_GET["method"]) && $_GET["method"] == "skeleton") {
	   //获取按钮骨架数据
		updatecache('setting');
	   if(isset($_G['setting']['bigapp_buttons_skeleton_edit'])){
			$succRet['data'] = unserialize($_G['setting']['bigapp_buttons_skeleton_edit']);
		} else {
			$succRet['data'] = C::t('common_setting')->fetch("bigapp_buttons_skeleton_edit", true);
		}
		
		//没取到数据，保存默认数据
		if(isset($succRet['data'][0]) && empty($succRet['data'][0])){
			$settings = array('bigapp_buttons_skeleton_edit' => BigAppConf::$defaultButtonSkeleton);
			C::t('common_setting')->update_batch($settings);
			
			$succRet['data'] = BigAppConf::$defaultButtonSkeleton;
		}
 
		$succRet['code'] = '0';
		$succRet['msg'] = 'Succ';
		echo BIGAPPJSON::encode($succRet);
		die(0);
} else if(isset($_GET["method"]) && $_GET["method"] == "page") {
	   $selected  = isset($_GET["selected"]) ? $_GET["selected"] : '1';
		
      //获取选定的页面数据
		updatecache('setting');
		if(isset($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit'])){
			$succRet['data'] = unserialize($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit']);
		} else {
			$succRet['data'] = C::t('common_setting')->fetch('bigapp_button_id_'.$selected.'_setting_edit', true);
		}

		//没取到数据，保存默认数据
		if(isset($succRet['data'][0]) && empty($succRet['data'][0])) {
			$btnSetting = AppDesign::getDefaultButtonSetting($selected);
			//兼容性处理，加view_type
			$btnSetting['view_type'] = isset($btnSetting['view_type']) ? $btnSetting['view_type'] : $btnSetting['button_type'];

			if(false === $btnSetting) {
				$succRet['code'] = '1';
				$succRet['msg'] = 'Fail';
			} else {
				$settings = array("bigapp_button_id_" . $selected . "_setting_edit" => $btnSetting);
				C::t('common_setting')->update_batch($settings);
				
				$succRet['code'] = '0';
				$succRet['msg'] = 'Succ';
				$succRet['data'] = $btnSetting;
			}
		} else {
			//兼容性处理，加view_type
			if(!isset($succRet['data']['view_type'])) {
				$succRet['data']['view_type'] = $succRet['data']['button_type'];
			 }
		}
		
		//兼容性处理，视图数据通过view_type来取，故丢弃原tab_cfg结构数据，简化结构
		unset($succRet['data']['tab_cfg']);
		
		$succRet['code'] = '0';
		$succRet['msg'] = 'Succ';
		echo BIGAPPJSON::encode($succRet);
      die(0);
} else if(isset($_GET["method"]) && $_GET["method"] == "save") {
	$selected  = isset($_GET["selected"]) ? $_GET["selected"] : '1';
	
	//保存或者修改配置
	if(empty($_REQUEST["settings"])){
		$result['code'] = '1';
		$result['msg'] = "Fail";
	} else {
		runlog('bigapp', "post button info [ settings:".$_REQUEST['settings']."]");
		
		//校验前端setting数据
		if ($_REQUEST['settings'] == base64_encode(base64_decode($_REQUEST['settings']))){
			$setting = json_decode(base64_decode($_REQUEST['settings']),true);
			runlog('bigapp', "post info:request is base64_encode, setting:".json_encode($setting));
		}else{
			$setting = json_decode($_REQUEST['settings'],true);
		}
		
		//组装视图数据
		$view_id = $setting['view_type'];

		updatecache('setting');
		if(isset($_G['setting']['bigapp_view_' . $view_id])) {
			$res = unserialize($_G['setting']['bigapp_view_' . $view_id]);
		} else {
			$res = C::t('common_setting')->fetch('bigapp_view_' . $view_id, true);
		}
		
		if(isset($res[0]) && empty($res[0])) {
			$result['code'] = '1';
			$result['msg'] = "Fail";
			
			echo BIGAPPJSON::encode($result);
			die(0);
		} else {
			if(isset($res['tab_type'])) {
				//自定义类型
				$setting['button_type'] = '1';
				$setting['tab_cfg'] = $res;
			} else {
				$setting['tab_cfg'] = array();
			}
			
			$setting['view_type'] = $view_id;
		}
		
		//兼容性处理,修改button_type,1为自定义按钮,取决于视图
		$setting['button_type'] = $view_id;
		if(intval($view_id) >10 || 1 === intval($view_id)) {
			$setting['button_type'] = '1';
		}
		
		//设置数据先预处理，然后保存
		$setting = AppDesign::filterSettings($setting);
		
		$settings = array('bigapp_button_id_' . $selected . '_setting_edit' => $setting);
		$ret = C::t('common_setting')->update_batch($settings);
		
		if(!$ret) {
			$result['code'] = '1';
			$result['msg'] = "Fail";
		} else {
			//获取保存后的页面数据
			updatecache('setting');
			if(isset($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit'])){
				$succRet['data'] = unserialize($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit']);
			} else {
				$succRet['data'] = C::t('common_setting')->fetch('bigapp_button_id_'.$selected.'_setting_edit', true);
			}
			
			//更新按钮sketelon,如果按钮数据发生了变化
			if(isset($_G['setting']['bigapp_buttons_skeleton_edit'])){
				$skeleton = unserialize($_G['setting']['bigapp_buttons_skeleton_edit']);
			} else {
				$skeleton = C::t('common_setting')->fetch('bigapp_buttons_skeleton_edit', true);
			}
			
			$skeleton[intval($selected) - 1]['name'] = $succRet['data']['name'];
			$skeleton[intval($selected) - 1]['icon_type'] = $succRet['data']['icon_type'];
			
			$settings = array('bigapp_buttons_skeleton_edit' => $skeleton);
			$ret = C::t('common_setting')->update_batch($settings);
			
			if(!$ret) {
				$result['code'] = '1';
				$result['msg'] = "Fail";
			} else {
				$result['code'] = '0';
				$result['msg'] = "Succ"; 
				$result['data'] = $succRet['data'];
			}
		}
		
	}
	
	echo BIGAPPJSON::encode($result);
	die(0);
} else if(isset($_GET["method"]) && $_GET["method"] == "apply") {
	//应用按钮骨架配置
	updatecache('setting');
	if(isset($_G['setting']['bigapp_buttons_skeleton_edit'])){
		$skeleton = unserialize($_G['setting']['bigapp_buttons_skeleton_edit']);
	} else {
		$skeleton = C::t('common_setting')->fetch('bigapp_buttons_skeleton_edit', true);
	}
	
	if(isset($skeleton[0]) && empty($skeleton[0])){
		//没取到数据，保存默认骨架配置
		$settings = array('bigapp_buttons_skeleton' => BigAppConf::$defaultButtonSkeleton);
	} else {
		$settings = array('bigapp_buttons_skeleton' => $skeleton);
	}
	
	C::t('common_setting')->update_batch($settings);
	
	//应用按钮的数据配置
	for($selected = 1; $selected <= 5; $selected++) {
		$selected = strval($selected);
		
		//获取指定按钮的编辑中的页面设置
		updatecache('setting');
		if(isset($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit'])){
			$succRet['data'] = unserialize($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit']);
		} else {
			$succRet['data'] = C::t('common_setting')->fetch('bigapp_button_id_'.$selected.'_setting_edit', true);
		}

		if(isset($succRet['data'][0]) && empty($succRet['data'][0])){
			$succRet['data'] = AppDesign::getDefaultButtonSetting($selected);
		}
		
		//替换到生效的页面设置
		$btnSetting = AppDesign::procFrontData($succRet['data']);
		
		$settings = array("bigapp_button_id_" . $selected . "_setting" => $btnSetting);
		
		C::t('common_setting')->update_batch($settings);	
	}
	
	$result['code'] = '0';
	$result['msg'] = "Succ";
	
	echo BIGAPPJSON::encode($result);
	die(0);
} else if(isset($_GET["method"]) && $_GET["method"] == "roback") {
	updatecache('setting');
	if(isset($_G['setting']['bigapp_buttons_skeleton'])){
		$skeleton = unserialize($_G['setting']['bigapp_buttons_skeleton']);
	} else {
		$skeleton = C::t('common_setting')->fetch('bigapp_buttons_skeleton', true);
	}
	
	$skeleton = C::t('common_setting')->fetch("bigapp_buttons_skeleton", true);
	
	//恢复到上一次有效的设置
	if(isset($skeleton[0]) && empty($skeleton[0])){
		//没取到生效的数据,恢复默认值
		$settings = array('bigapp_buttons_skeleton_edit' => BigAppConf::$defaultButtonSkeleton);
	} else {
		$settings = array('bigapp_buttons_skeleton_edit' => $skeleton);
	}
	
	C::t('common_setting')->update_batch($settings);
	
	for($selected = 1; $selected <= 5; $selected++) {
		$selected = strval($selected);

		//获取指定按钮的上一次生效的页面设置
		updatecache('setting');
		if(isset($_G['setting']['bigapp_button_id_'.$selected.'_setting'])){
			$succRet['data'] = unserialize($_G['setting']['bigapp_button_id_'.$selected.'_setting']);
		} else {
			$succRet['data'] = C::t('common_setting')->fetch('bigapp_button_id_'.$selected.'_setting', true);
		}
		
		if(isset($succRet['data'][0]) && empty($succRet['data'][0])){
			//没取到生效的数据,恢复默认值
			$succRet['data'] = AppDesign::getDefaultButtonSetting($selected);
		}
		
		//替换到生效的页面设置
		$settings = array("bigapp_button_id_" . $selected . "_setting_edit" => $succRet['data']);
		$ret = C::t('common_setting')->update_batch($settings);
		
		if(!$ret) {
			$result['code'] = '1';
			$result['msg'] = "Fail";
			echo BIGAPPJSON::encode($result);
			die(0);
	   }
	}
	
   //当前页面的数据，需返回给前端	
	$selected  = isset($_GET["selected"]) ? $_GET["selected"] : '1';
	
	updatecache('setting');
	if(isset($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit'])){
		$succRet['data'] = unserialize($_G['setting']['bigapp_button_id_'.$selected.'_setting_edit']);
	} else {
		$succRet['data'] = C::t('common_setting')->fetch('bigapp_button_id_'.$selected.'_setting_edit', true);
	}
	
	$result['code'] = '0';
	$result['msg'] = "Succ"; 

	if(isset($succRet['data']['tab_cfg'])) {
		unset($succRet['data']['tab_cfg']);
	}
	$result['data'] = $succRet['data'];
	
	echo BIGAPPJSON::encode($result);
	die(0);
} 

$tpl = 'index.tpl';
$params = array();
$params['image_base_path'] = rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/view/design/dist/';
$params['method_base_path'] = rtrim($_G['siteurl'], '/');
$params['banner_image_s'] = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('banner_image_s');
$params['func_image_s'] = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('func_image_s');
$params['func_forum_image_s'] = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('func_forum_image_s');
$params['imageSize'] = BigAppConf::$imgRequire;

$tplVars = array("source_path"=>rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp/view/design/dist/');
Utils::loadTemplate(dirname(__FILE__) . '/view/design/dist/'.$tpl, $params ,$tplVars);

runlog('bigapp', 'show release page succ');

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
