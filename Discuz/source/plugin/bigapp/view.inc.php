<?php 

global $_G;
define("FILE_PATH", dirname(__FILE__));

require_once FILE_PATH . '/conf/conf.inc.php';
require_once FILE_PATH . '/libs/utils.inc.php';
require_once FILE_PATH . '/libs/appdesign.inc.php';
require_once FILE_PATH . '/bigappjson.class.php';


//REQUEST_METHOD_DOMAIN
AppDesign::makeCors($_SERVER['REQUEST_METHOD'], '*');

if(isset($_GET["method"]) && $_GET["method"] == "get") {
	//视图
	$view_id  = isset($_GET["vid"]) ? $_GET["vid"] : '-1';
	
	if('-1' == $view_id) {
		$result['code'] = '0';
		$result['msg'] = "Succ";
		
		echo BIGAPPJSON::encode($result);
		die(0);  
	}
	
	$res = C::t('common_setting')->fetch("bigapp_view_".$view_id, true);

	if(isset($res[0]) && empty($res[0])) {
		//视图1是可以删除的
		if(intval($view_id) <= 5 && intval($view_id) > 1) {
			$btnSetting = AppDesign::getDefaultButtonSetting($view_id);
			
			$view = $btnSetting["tab_cfg"];
			$view["name"] = $btnSetting["name"];
		} else if(intval($view_id) === 6){
			$view["name"] = "搜索";
		} else {
			$result['code'] = '0';
			$result['msg'] = "Fail";
			
			echo BIGAPPJSON::encode($result);
			die(0); 
		}
		
		$settings = array("bigapp_view_" . $key => $view);
		C::t('common_setting')->update_batch($settings);
	} else {
		$view = $res;
	}	
	
	$result['code'] = '0';
	$result['msg'] = "Succ";
	
	if(isset($view['title_cfg']) && is_array($view['title_cfg'])) {
		foreach($view['title_cfg'] as &$title) {
			if(isset($title['$$hashKey'])) {
				unset($title['$$hashKey']);
			}
		}
	}
	
	$result['data'] = $view;
	
	echo BIGAPPJSON::encode($result);
	die(0);
} else if(isset($_GET["method"]) && $_GET["method"] == "save") {
	//新增和编辑视图
	$view_id  = isset($_GET["vid"]) ? $_GET["vid"] : '-1';

	if('-1' === $view_id) {
		$result['code'] = '1';
		$result['msg'] = "Fail";
		echo BIGAPPJSON::encode($result);
		die(0);
	}
	
	//保存或者修改配置
	if(!empty($_REQUEST["settings"])){
		runlog('bigapp', "post view info [ settings:".$_REQUEST['settings']."]");
		
		if ($_REQUEST['settings'] == base64_encode(base64_decode($_REQUEST['settings']))){
			$setting = json_decode(base64_decode($_REQUEST['settings']),true);
			runlog('bigapp', "post info:request is base64_encode, setting:".json_encode($setting));
		}else{
			$setting = json_decode($_REQUEST['settings'],true);
		}
		
		AppDesign::filterViews($setting);
		
		$settings = array("bigapp_view_" . $view_id => $setting);
		$ret = C::t('common_setting')->update_batch($settings);
		
		if($ret) {
			$succRet['data'] = C::t('common_setting')->fetch("bigapp_view_" . $view_id, true);
			
			//更新view视图列表
			$ret = C::t("common_setting")->fetch("bigapp_view_list", true);	
			
			$ret[$view_id] = $succRet['data']['name'];
			
			$settings = array("bigapp_view_list" => $ret);
			C::t('common_setting')->update_batch($settings);
			
			$result['code'] = '0';
			$result['msg'] = "Succ";
			$result['views'] = AppDesign::getViewsData();
			//$result['data'] = $succRet['data'];
		} else {
			$result['code'] = '1';
			$result['msg'] = "Fail";
		}
	} else {
		$result['code'] = '1';
		$result['msg'] = "Fail";
	}
	
	echo BIGAPPJSON::encode($result);
	die(0);
	
} else if(isset($_GET["method"]) && $_GET["method"] == "delete") {
	//删除指定的视图
	$view_id  = isset($_GET["vid"]) ? $_GET["vid"] : '-1';
	
	if('-1' === $view_id) {
		$result['code'] = '1';
		$result['msg'] = "Fail";
	} else {
	   //检测删除的视图是否正在使用
	   $used1 = false;
		$used2 = false;	   
	   
		for($i = 1; $i <= 5; $i++) {
			if($used1 || $used2) break;
			
			//获取选定的页面数据
			$res = C::t('common_setting')->fetch("bigapp_button_id_". $i ."_setting_edit", true);
			if(isset($res[0]) && empty($res[0])) {
				continue;
			} else {
				//老的数据，需要加上view_type
				if(!isset($res['view_type']) && isset($res['button_type'])) {
					$res['view_type'] = $res['button_type'];
				}
				$used1 = AppDesign::isViewInUse($res['view_type'], $view_id);
            }
			
			//获取选定的页面数据,应用中的
			$res = C::t('common_setting')->fetch("bigapp_button_id_". $i ."_setting", true);
			if(isset($res[0]) && empty($res[0])) {
				continue;
			} else {
				//老的数据，需要加上view_type
				if(!isset($res['view_type']) && isset($res['button_type'])) {
					$res['view_type'] = $res['button_type'];
				}
				
				$used2 = AppDesign::isViewInUse($res['view_type'], $view_id);
			}
		}
		
		if($used1) { //删除的视图处于保存中，不能删除
			$result['code'] = '2';
			$result['msg'] = "Fail";
		} else if($used2) { //删除的视图处于生效中，不能删除
			$result['code'] = '3';
			$result['msg'] = "Fail";
		} else {
			$res = C::t('common_setting')->delete("bigapp_view_" . $view_id);
			//$res = true;
			
			if(!$res) { //删除失败
				$result['code'] = '1';
				$result['msg'] = "Fail";
			} else { //删除成功
			   //更新view视图列表
			   //视图列表
				$res = C::t("common_setting")->fetch("bigapp_view_list", true);
				
				if(isset($res[$view_id])) {
					unset($res[$view_id]);
					
					$settings = array("bigapp_view_list" => $res);
					C::t('common_setting')->update_batch($settings);
				}
				
				$result['code'] = '0';
				$result['msg'] = "Succ";
				$result['views'] = AppDesign::getViewsData();
			}
		}
	}
	
	echo BIGAPPJSON::encode($result);
	die(0);
}

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
