<?php
/**
 * @file appdesign.inc.php
 * @Brief 
 * @author tangyy
 * @version 1.0.0
 * @date 2015-09-24
 */

define("FILE_PATH", dirname(dirname(__FILE__)));

require_once FILE_PATH . '/conf/conf.inc.php';
require_once FILE_PATH . '/libs/utils.inc.php';
require_once FILE_PATH . '/bigappjson.class.php';


class AppDesign {
	
	public static function getDefaultInfo() {
		$initNavigations = array(
			"selected" => "0",
			"navi" => array(
			),
		);

		$defaultInfo = array(
			'1' => array(
					"id" => "1",
					"name" => "首页",
					"icon_type" => "3_24",
					"view_type" => "1",
					"button_type" => "1",
					"tab_cfg" => array(
						"tab_type" => "1",
						"title" => "",
						"title_cfg" => array(
							//array('name' => "", "icon_type" => "1_1", "view_type" => "0"),
							//array('name' => "", "icon_type" => "1_1", "view_type" => "0"),
						),
						"home_page" => BigAppConf::$newDefaultHome,
						"navi_page" => $initNavigations,
						"wap_page" => "",
                   "use_wap_name" => "1"						
					),				
			),
			'2' => array(
					"id" => "2",
					"name" => "论坛",
					"icon_type" => "3_25",
					"view_type" => "2",
					"button_type" => "2",
					"tab_cfg" => array(
						"displayid" => "",
						"forbiddenid" => "",
						"type" => '1', //1平铺，2顶部，3侧边
					),				
			),
			'3' => array(
					"id" => "3",
					"name" => "发帖",
					"icon_type" => "3_26",
					"view_type" => "3",
					"button_type" => "3",
					//"tab_cfg" => array(),				
			),
			'4' => array(
					"id" => "4",
					"name" => "站内信",
					"icon_type" => "3_27",
					"view_type" => "4",
					"button_type" => "4",
					//"tab_cfg" => array(),				
			),
			'5' => array(
					"id" => "5",
					"name" => "我的",
					"icon_type" => "3_28",
					"view_type" => "5",
					"button_type" => "5",
					//"tab_cfg" => array(),				
			),
		);
		
		return $defaultInfo;
	}
	
	public static function getViewsData() {
		//兼容性视图数据处理
		
		//视图列表
		$res = C::t("common_setting")->fetch("bigapp_view_list", true);

		if(isset($res[0]) && empty($res[0])) {
		//if(true) {
			$list = array(
				"1" => Utils::converGbkString("首页"),
				"2" => Utils::converGbkString("论坛"),
				"3" => Utils::converGbkString("发帖"),
				"4" => Utils::converGbkString("站内信"),
				"5" => Utils::converGbkString("我的"),
				"6" => Utils::converGbkString("搜索"),
			//	"7" => Utils::converGbkString("扫一扫"),
			);
			
			$settings = array("bigapp_view_list" => $list);
			C::t('common_setting')->update_batch($settings);
			
			################################################
			$view_id = 11;
			for($i=1; $i<=5; $i++) {
				$succRet['data'] = C::t('common_setting')->fetch("bigapp_button_id_".$i."_setting_edit", true);
				
				//老插件的自定义的页面，获取后新建作为一个新的视图加到视图列表中
				if(isset($succRet['data']['button_type']) && '1' == $succRet['data']['button_type']) {
					//重新组织视图结构
				   $setting = $succRet['data']['tab_cfg'];
				   if(!isset($setting['title'])) {
						$setting['title'] = '';
				   }
				   if(!isset($setting['name'])) {
						$setting['name'] = $succRet['data']['name']; 
				   }
				   if(/*'3' == $setting['tab_type'] &&*/ !isset($setting['use_wap_name'])) {
					   $setting['use_wap_name'] = '1';
				   }
				   if(!isset($setting['title_cfg'])) {
					   $setting['title_cfg'] = array(
							//array('name' => "", "icon_type" => "1_1", "view_type" => "0"),
							//array('name' => "", "icon_type" => "1_1", "view_type" => "0"),
						);
				   }
				   
				   if(!isset($setting['id'])) {
					   $setting['id'] = $view_id;
				   }
				
					$settings = array("bigapp_view_".$view_id => $setting);
					$ret = C::t('common_setting')->update_batch($settings);
					
					if($ret) {
						$viewData['data'] = C::t('common_setting')->fetch("bigapp_view_" . $view_id, true);
						//更新view视图列表
						$ret = C::t("common_setting")->fetch("bigapp_view_list", true);	
						$ret[$view_id] = $viewData['data']['name'];

						$settings = array("bigapp_view_list" => $ret);
						C::t('common_setting')->update_batch($settings);		
					}
					
					//替换掉老的视图数据
					$succRet['data']['tab_cfg'] = $setting;
					$succRet['data']['view_type'] = strval($view_id);
					
					$settings = array('bigapp_button_id_' . $i . '_setting_edit' => $succRet['data']);
					C::t('common_setting')->update_batch($settings);
					
					$view_id++;
				}
			}
			
			$list = C::t("common_setting")->fetch("bigapp_view_list", true);
			################################################
			
		} else {
			$list = $res;
		}
		
		$views = array();

		foreach($list as $key => $value) {
			$res = C::t('common_setting')->fetch("bigapp_view_".$key, true);
			
			if(isset($res[0]) && empty($res[0])) {
			//if(true) {
				if(intval($key) <= 5) {
					$btnSetting = AppDesign::getDefaultButtonSetting($key);
					$view = $btnSetting["tab_cfg"];
					$view["name"] = $btnSetting["name"];
				} else if(intval($key) == 6){
					$view = array();
					$view["name"] = Utils::converGbkString("搜索");
				} else {
					//$view = array();
					//$view["name"] = Utils::converGbkString("扫一扫");
				}
				
				$settings = array("bigapp_view_" . $key => $view);
				C::t('common_setting')->update_batch($settings);
			} else {
				$view = $res;
			}
			
			$view['id'] = strval($key);
			
			array_push($views, $view);
		}
		
		//内部视图
		$inner_views = array();
		//外部视图
		$outer_views = array();
		
		foreach($views as $view) {
			if(isset($view['tab_type'])) {
				//if(!isset($view['title_name'])) {
				//	$view['title'] = "";
				//}
				
				if(!isset($view['title_cfg'])) {
					$view['title_cfg'] = array(
						array('name' => "", "icon_type" => "0_0", "view_type" => "0"),
						array('name' => "", "icon_type" => "0_0", "view_type" => "0"),
					);
				}
				
				if(isset($view['home_page'])) unset($view['home_page']);
				if(isset($view['navi_page'])) unset($view['navi_page']);
				if(isset($view['wap_page'])) unset($view['wap_page']);
				if(isset($view['title_cfg'])) unset($view['title_cfg']);
				
				$view['ctrl_flag'] = '1';
				$outer_views[$view['id']] = $view;
			} else {
				if(6 == $view['id']) {
					$view['ctrl_flag'] = '2';
				} else {
					$view['ctrl_flag'] = '1';
				}
				
				$inner_views[$view['id']] = $view;
			}
		}
		
		$result = array(
			"inner_views" => $inner_views,
			"outer_views" => $outer_views,
		);
		
		return $result;
	}
	
	public static function getDefaultButtonSetting($selected) {
		if(!in_array($selected, array('1','2','3','4','5'))) {
			return false;
		}
		
		$defaultInfo = AppDesign::getDefaultInfo();
		$defaultInfo[$selected] = AppDesign::filterSettings($defaultInfo[$selected], true);
		
		//add view_type
		$defaultInfo[$selected]['view_type'] = $defaultInfo[$selected]['button_type'];
		
		return $defaultInfo[$selected];
	}
	
	public static function filterSettings($button_setting, $isDefault=false) {
		//自定义按钮才可能需要处理
		if(isset($button_setting['name'])) {
			$button_setting['name'] = Utils::converGbkString($button_setting['name']);
		}
		
		
		if(!isset($button_setting['tab_cfg'])) {
			return $button_setting;
		}
			
		if($isDefault) {
			//默认配置中汉字仍然需要进行gbk转码
			AppDesign::filterViews($button_setting['tab_cfg']);
		} else {
			AppDesign::filterViews($button_setting['tab_cfg'], false);
		}
		
		return $button_setting;
	}
	
	//$flag用来标识是否进行gbk汉字转码处理
	public static function filterViews(&$tab_cfg, $flag=true) {
		//filter $$hashKey from FE
		if(isset($tab_cfg['$$hashKey'])) unset($tab_cfg['$$hashKey']); 
		
		if(isset($tab_cfg['title_cfg']) && is_array($tab_cfg['title_cfg'])) {
			foreach($tab_cfg['title_cfg'] as &$title) {
				if(isset($title['$$hashKey'])) unset($title['$$hashKey']);
			}
		}
		
		//自定义view视图才可能需要处理	
		if(isset($tab_cfg['home_page']) && is_array($tab_cfg['home_page'])) { //处理单页
			foreach($tab_cfg['home_page'] as &$setting) {				
				if(isset($setting['type']) && in_array($setting['type'], array('1','2','3'))) {
					//banner,func, hot
					$setting['setting'] = AppDesign::processCommSetting($setting['setting'], $flag);	
				} else if(isset($setting['type']) && '4' === $setting['type']){
					//recommend 处理中文
					$setting['setting'] = AppDesign::processRecommSetting($setting['setting'], $flag);
				}
			}
		}
		
		if(isset($tab_cfg['navi_page']['navi']) && is_array($tab_cfg['navi_page']['navi'])) { //处理导航页
			foreach($tab_cfg['navi_page']['navi'] as &$navi) {
				if(isset($navi['navi_name'])) {
					$navi['navi_name'] = Utils::converGbkString($navi['navi_name'], $flag);
				}
				
				//导航内的单页
				foreach($navi['navi_setting']['home_page'] as &$setting) {				
					if(isset($setting['type']) && in_array($setting['type'], array('1','2','3'))) {
						//banner,func, hot
						$setting['setting'] = AppDesign::processCommSetting($setting['setting'], $flag);	
					} else if(isset($setting['type']) && '4' === $setting['type']){
						//recommend 处理中文
						$setting['setting'] = AppDesign::processRecommSetting($setting['setting'], $flag);
					}
				}
			}
		}
		
		//处理视图头部标题以及视图名称
		
		if(isset($tab_cfg['name'])) $tab_cfg['name'] = Utils::converGbkString($tab_cfg['name'], $flag);
		
		if(isset($tab_cfg['title'])) $tab_cfg['title'] = Utils::converGbkString($tab_cfg['title'], $flag);
		if(isset($tab_cfg['title_cfg'])) {
			foreach($tab_cfg['title_cfg'] as &$cfg) {
				$cfg['name'] = Utils::converGbkString($cfg['name'], $flag);
			}
		}
	}
	
	private static function processRecommSetting($settings, $flag=true) {
		if(!empty($settings)) {
			if(!empty($settings['data_src_cfg']['recomm_setting'])){
				$recommsettings = $settings['data_src_cfg']['recomm_setting'];
				
				foreach($recommsettings as &$recommsetting) {
					$recommsetting['module'] = Utils::converGbkString($recommsetting['module'], $flag);
					$recommsetting['title'] = Utils::converGbkString($recommsetting['title'], $flag);
				}
				
				$settings['data_src_cfg']['recomm_setting'] = $recommsettings;
			} 
		}
		
		return $settings;
	}
	
	private static function processCommSetting($settings, $flag=true) {
		if(!empty($settings)){
			foreach($settings as $key => &$value){
				$value['title'] = Utils::converGbkString(urldecode($value['title']), $flag);
				$value['pic'] = Utils::converGbkString(urldecode(urldecode($value['pic'])), $flag);
				$value['pic'] = str_replace("&amp;","&",$value['pic']);
				$value['pic'] = Utils::addUrlQueryString($value['pic'],array("_v"=>time()));
				$value['url'] = Utils::converGbkString(urldecode($value['url']), $flag);
				$value['desc'] = Utils::converGbkString(urldecode($value['desc']), $flag);

				$preg = array(
						'2'=>'/\w+-(\d+)-(\d+)-(\d+)\.htm/i',
						'3'=>'/\w+-(\d+)-(\d+)\.htm/i',
						'4'=>'/\w+-(\d+)-(\d+)\.htm/i',
				//		'5'=>'/\w+-(\d+)-(\d+)\.htm/i',
						);

				if($value['type'] === '2'){
					if(isset($preg[$value['type']]) 
							&& preg_match($preg[$value['type']],$value['url'],$matches) ){
						if(isset($matches[1]))
							$value['pid'] = $matches[1];
					}else{
						if(preg_match('/tid=(\d+)/i', $value['url'], $matches)){
							if(isset($matches[1]))
								$value['pid'] = $matches[1];
						}
					}
				}

				if($value['type'] === '3'){
					if(isset($preg[$value['type']]) 
							&& preg_match($preg[$value['type']],$value['url'],$matches) ){
						if(isset($matches[1]))
							$value['pid'] = $matches[1];
					}else{
						if(preg_match('/fid=(\d+)/i', $value['url'], $matches)){
							if(isset($matches[1]))
								$value['pid'] = $matches[1];
						}
					}
				}
				
				if($value['type'] === '4'){
					if(isset($preg[$value['type']]) 
							&& preg_match($preg[$value['type']],$value['url'],$matches) ){
						if(isset($matches[1]))
							$value['pid'] = $matches[1];
					}else{
						if(preg_match('/aid=(\d+)/i', $value['url'], $matches)){
							if(isset($matches[1]))
								$value['pid'] = $matches[1];
						}
					}
				}
				
				if($value['type'] === '5'){					
					if(preg_match('/catid=(\d+)/i', $value['url'], $matches)){
						if(isset($matches[1]))
							$value['pid'] = $matches[1];
					} else {
						//频道url设定别名或者跳转链接
						$urlArr = parse_url($value['url']);
						
						if(!isset($urlArr['path'])) {
							//没别名
							$catid = '-1';
						} else {
							$pathArr = explode('/', $urlArr['path']);
							$aliasname = $pathArr[count($pathArr) - 1] != "" ? $pathArr[count($pathArr) - 1] : $pathArr[count($pathArr) - 2];
							
							if("" == $aliasname) {
								$catid = '-1';
							} else {
								$sql = "SELECT catid FROM " . DB::table('portal_category'). " WHERE url = '" . $aliasname . "' OR foldername = '". $aliasname ."' LIMIT 1";
								$query = DB::query($sql);
								$tmp = DB::fetch($query);
						
								$catid = isset($tmp['catid']) ? $tmp['catid']: '-1';
							}
						}
						
						$value['pid'] = $catid;
					}
				}
				
				runlog('bigapp', "debug >>>>>>>>> info:".json_encode($value));
				//$value['pid'] = 0 ;
				//$value['pid'] = self::getPid($value['type'],$value['url'],$value['pid']);
				runlog('bigapp', "debug end >>>>>>>>> info pid >>>>>>>>:".$value['pid']);
			}
		}
		return $settings;
	}
	
	public static function makeCors($request_method, $origin='') {
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
	
	//生效之前，对前端传过来的数据过滤脏数据
	public static function procFrontData($buttonInfo) {
		global $_G;
		
		$button_type = $buttonInfo['button_type'];
		$button_id = $buttonInfo['id'];
		
		if('1' === $button_type) {
			$tab_cfg = $buttonInfo['tab_cfg'];
			
			if('1' === $tab_cfg['tab_type']) {
				//去掉冗余tab页面配置
				if(isset($tab_cfg['navi_page'])) unset($tab_cfg['navi_page']);
				if(isset($tab_cfg['wap_page'])) unset($tab_cfg['wap_page']);
				
				$recommend_setting = $tab_cfg['home_page'][3]['setting'];
						
				if('1' === $recommend_setting['data_src_cfg']['type']) {
					if(isset($recommend_setting['data_src_cfg']['recomm_setting'])) 
						unset($recommend_setting['data_src_cfg']['recomm_setting']);
				}
				
				if('2' === $recommend_setting['data_src_cfg']['type']) {
					if(isset($recommend_setting['data_src_cfg']['content_setting'])) 
						unset($recommend_setting['data_src_cfg']['content_setting']);
				}
				
				$tab_cfg['home_page'][3]['setting'] = $recommend_setting;
			}
			
			if('2' === $tab_cfg['tab_type']) {
				//去掉冗余tab页面配置
				if(isset($tab_cfg['home_page'])) unset($tab_cfg['home_page']);
				if(isset($tab_cfg['wap_page'])) unset($tab_cfg['wap_page']);
				
				//去掉导航里面冗余tab页面配置
				for($i = 0; $i < count($tab_cfg['navi_page']['navi']); $i++) {
					$navi_setting = $tab_cfg['navi_page']['navi'][$i]['navi_setting'];
					
					if('1' === $navi_setting['tab_type']) {
						if(isset($navi_setting['wap_page'])) unset($navi_setting['wap_page']);
						//去掉单页面上的冗余的推荐配置
						
						$recommend_setting = $navi_setting['home_page'][3]['setting'];
						
						if('1' === $recommend_setting['data_src_cfg']['type']) {
							if(isset($recommend_setting['data_src_cfg']['recomm_setting'])) 
								unset($recommend_setting['data_src_cfg']['recomm_setting']);
						}
						
						if('2' === $recommend_setting['data_src_cfg']['type']) {
							if(isset($recommend_setting['data_src_cfg']['content_setting'])) 
								unset($recommend_setting['data_src_cfg']['content_setting']);
						}
						
						$navi_setting['home_page'][3]['setting'] = $recommend_setting;
					}
					
					if('3' === $navi_setting['tab_type']) {
						if(isset($navi_setting['home_page'])) unset($navi_setting['home_page']);
					}
					
					$tab_cfg['navi_page']['navi'][$i]['navi_setting'] = $navi_setting;
				}
				
			}
			
			if('3' === $tab_cfg['tab_type']) {
				if(isset($tab_cfg['home_page'])) unset($tab_cfg['home_page']);
				if(isset($tab_cfg['navi_page'])) unset($tab_cfg['navi_page']);
			}
			
			
			if(isset($buttonInfo['tab_cfg']['title_cfg'])) {
				
				$tab_cfg['title_cfg'] = array();
				
				foreach($buttonInfo['tab_cfg']['title_cfg'] as $title_cfg) {
					if(isset($title_cfg['view_type']) && is_numeric($title_cfg['view_type']) && '0' != $title_cfg['view_type'] && '' != $title_cfg['icon_type']) {
						//由tab_cfg中的view_type获取view信息
						###############################
						$view_id =$title_cfg['view_type'];
						//过滤掉搜索视图，如果后台没启用搜索
			         if($view_id != '6' || AppDesign::isEnableSearch()) {
							$title_cfg['button_name'] = Utils::converGbkString($title_cfg['name'], false);
							unset($title_cfg['name']);
							
							if(intval($view_id) >10 || 1 === intval($view_id)) {
								$title_cfg['title_button_type'] = '1';
								$succRet['data'] = C::t('common_setting')->fetch("bigapp_view_" . $view_id, true);
								if(isset($succRet['data']['tab_type'])) {
									$title_cfg['tab_type'] = $succRet['data']['tab_type'];
								}
							} else {
								$title_cfg['title_button_type'] = $view_id;
							}
							
							$title_cfg['view_link'] = $_G['siteurl']."iyz_index.php?iyzmobile=1&iyzversion=1&module=viewinfo&vid=" . $view_id;
							
							//if(isset($title_cfg['view_type'])) unset($title_cfg['view_type']);
							if(isset($title_cfg['$$hashKey'])) unset($title_cfg['$$hashKey']);
							$tab_cfg['title_cfg'][]= $title_cfg;
					  }
						###############################
					}
				}
			}
			
			$buttonInfo['tab_cfg'] = $tab_cfg;
		}
		
		return $buttonInfo;
	}
	
	//functions for mobile
	public static function getTabCfgInfo($buttonInfo) {
		global $_G;
		$tab_cfg = array();
		
		$button_type = $buttonInfo['button_type'];
		
		//自定义类别的按钮，需根据tab_type,构建tab页面数据
		if('1' === $button_type) {
			$tab_cfg = AppDesign::getViewTabCfgInfo($buttonInfo['tab_cfg']);
		}
		 
		return $tab_cfg;
	}
	
	//获取视图的数据
	public static function getViewTabCfgInfo($viewInfo) {
		global $_G;
		$tab_cfg = array();
		
		//自定义类别的按钮，需根据tab_type,构建tab页面数据
		if(isset($viewInfo['tab_type']) && is_numeric($viewInfo['tab_type'])) {
			$tab_cfg['tab_type'] = $viewInfo['tab_type'];
			if(isset($viewInfo['title'])) {
				$tab_cfg['title'] = Utils::converGbkString($viewInfo['title'], false);
			}
			
			if(isset($viewInfo['title_cfg'])) {
				$tab_cfg['title_cfg'] = array();
				
				foreach($viewInfo['title_cfg'] as $title_cfg) {
					//var_dump($title_cfg) or die();
					if(isset($title_cfg['view_type']) && is_numeric($title_cfg['view_type']) && '0' != $title_cfg['view_type'] && '' != $title_cfg['icon_type']) {
						//由tab_cfg中的view_type获取view信息
						###############################
						$view_id =$title_cfg['view_type'];
						
						if($view_id != '6' || AppDesign::isEnableSearch()) {
							if(isset($title_cfg['name'])) {
								$title_cfg['button_name'] = Utils::converGbkString($title_cfg['name'], false);
								unset($title_cfg['name']);
							}
							
							if(intval($view_id) >10 || 1 === intval($view_id)) {
								$title_cfg['title_button_type'] = '1';
								$succRet['data'] = C::t('common_setting')->fetch("bigapp_view_" . $view_id, true);
								if(isset($succRet['data']['tab_type'])) {
									$title_cfg['tab_type'] = $succRet['data']['tab_type'];
								}
							} else {
								$title_cfg['title_button_type'] = $view_id;
							}
							
							$title_cfg['view_link'] = $_G['siteurl']."iyz_index.php?iyzmobile=1&iyzversion=1&module=viewinfo&vid=" . $view_id;
							if(isset($title_cfg['view_type'])) unset($title_cfg['view_type']);
							if(isset($title_cfg['$$hashKey'])) unset($title_cfg['$$hashKey']);
							
							$tab_cfg['title_cfg'][]= $title_cfg;
						}
						###############################
					}
				}
			}
			
			if('1' === $tab_cfg['tab_type']) {
				//单页页面类型
				$home_page = AppDesign::sortForArea($viewInfo['home_page']);
				//var_dump($home_page) or die();
				$tab_cfg['home_page'] = AppDesign::areaMapProcessForClient($home_page, $button_id, $navi_id = "");
				
			} else if('2' === $tab_cfg['tab_type']) { 
			 	//导航页面类型
				$navi_page = $viewInfo['navi_page'];
				$navi_page['navi'] = AppDesign::sortForRecomm($navi_page['navi']);
				$tab_cfg['navi_page'] = AppDesign::recommMapProcessForClient($navi_page['navi'], $button_id);
			} else { 
				//tab_type == 3, wap页面. 后续加类型可以扩展
				$tab_cfg['wap_page'] = $viewInfo['wap_page'];
				$tab_cfg['use_wap_name'] = isset($viewInfo['use_wap_name']) ? $viewInfo['use_wap_name'] : '1';
			}
		}
		
		return $tab_cfg;
	}
	
	private static function isEnableSearch() {
		$ret = C::t('common_setting')->fetch('search', true);

		$enable_search = false;
				
		if(!empty($ret) && is_array($ret)) {
			foreach($ret as $key => $value) {
				if(isset($value['status']) && $value['status'] == '1') {
					if($key == 'forum' || $key == 'group')
						$enable_search = true;
				}
			}
		}
		
		return $enable_search;
	}
	
	private static function sortForRecomm($navi) {
		$arr = $navi;
		$res = array();
	
	    //过滤掉未启用的导航设置
		foreach($arr as $navi_setting) {
			if('1' === $navi_setting['flag']) {
				array_push($res, $navi_setting);
			}
		}	
		
		//根据导航设置的排序来排序
		$orderArr=array();
		$idArr=array();
	
		for($i=0; $i < count($res); $i++){
			$orderArr[] = $res[$i]['order'];
			$idArr[] = $res[$i]['id'];
		}
		
		array_multisort($orderArr, SORT_ASC, $idArr, SORT_ASC, $res);
		
		//对每一个导航页内的设置进一步排序
		foreach($res as &$navi_setting) {
			//导航设置里面只支持单页和wap页面两种类型,对单页内的设置进行排序
			if('1' === $navi_setting['navi_setting']['tab_type']) {
				$navi_setting['navi_setting']['home_page'] = AppDesign::sortForArea($navi_setting['navi_setting']['home_page']);
				//$navi_setting['setting'] = AppDesign::areaMapProcessForClient($navi_setting['setting']);
			}
		}
		
		return $res;
	}
	
	private static function sortForArea($home_page) {
		$arr = $home_page;
		$res = array();
	
	    //过滤掉未启用的区域设置
		foreach($arr as $area) {
			if('1' === $area['enable']) {
				array_push($res, $area);
			}
		}	
		
		//根据区域设置的排序来排序
		$newArr=array();
	
		for($i=0; $i < count($res); $i++){
			$newArr[] = $res[$i]['sort'];
		}
		
		array_multisort($newArr , $res);
		#######################################
		foreach($res as &$area) {
			if('4' === $area['type']) continue; //推荐区的setting内容无需进行排序
			
			$newsetting = array();
			foreach($area['setting'] as $setting) {
				if('1' === $setting['flag']) {
					array_push($newsetting, $setting);
				}
			}
			
			$newArr=array();
	
			for($i=0; $i < count($newsetting); $i++){
				$newArr[] = $newsetting[$i]['order'];
			}
			array_multisort($newArr , $newsetting);
			
			unset($area['setting']);
			$area['setting'] = $newsetting;
		}
		
		#######################################
		return $res;
	}
	
	private static function recommMapProcessForClient(&$navi, $button_id) {
		
		//对每一个导航页内的设置进行处理
		foreach($navi as &$navi_setting) {
			unset($navi_setting['flag']);
			unset($navi_setting['order']);
			$navi_id = $navi_setting['id'];
			unset($navi_setting['id']);
			
			//导航设置里面只支持单页和wap页面两种类型
			if('1' === $navi_setting['navi_setting']['tab_type']) {
				$navi_setting['navi_setting']['home_page'] = AppDesign::areaMapProcessForClient($navi_setting['navi_setting']['home_page'], $button_id, $navi_id);
			} else if('3' === $navi_setting['navi_setting']['tab_type']) {
				//
			}
		}
		
		return $navi;
	}
	
	private static function areaMapProcessForClient(&$home_page, $button_id, $navi_id) {
		global $_G;
		
		foreach($home_page as &$area) {
			unset($area['sort']);
			unset($area['enable']);
			switch($area['type']) {
				case '1':
					$area['type'] = 'banner';
					break;
				case '2':
					$area['type'] = 'func';
					break;
				case '3':
					$area['type'] = 'hot';
					break;
				case '4':
					$area['type'] = 'recomm';
					break;
			}			
		}
		
		$moduleMap = array(
							Utils::converGbkString('最新帖子') => 'new',
							Utils::converGbkString('热门帖子') => 'hot',
							Utils::converGbkString('精华帖子') => 'digest',
						);
						
		foreach($home_page as &$area) {
			if('recomm' === $area['type']) {
				//推荐区数据单独映射处理
				$newsetting = array();
				
				$setting = $area['setting'];
				$pic_mode = $setting['type'];//图形显示模式，多图or无图
				
				$newsetting['type'] = $setting['data_src_cfg']['type'];
				
				if('1' === $newsetting['type']) {
					//内容型
					$rmlink =  $_G['siteurl']."iyz_index.php?iyzmobile=1&iyzversion=1&module=contentthread";
					$content_setting = base64_encode(json_encode($setting['data_src_cfg']['content_setting']));
					
					$data_link = $rmlink . "&buttonid=".$button_id."&navid=".$navi_id."&style=".$pic_mode."&setting=".$content_setting;
					$newsetting['thread_config'][] = array("data_link" => $data_link); 
					$area['recommend'] = $newsetting;
					//var_dump($area['setting']) or die();
					unset($area['setting']);
				} else if('2' === $newsetting['type']) {
					//推荐型
					$arr = $setting['data_src_cfg']['recomm_setting'];
					
					$newrecommsetting = array();
					foreach($arr as $setting) {
						if('1' === $setting['flag']) {
							array_push($newrecommsetting, $setting);
						}
					}
					
					$newArr=array();
			
					for($i=0; $i < count($newrecommsetting); $i++){
						$newArr[] = $newrecommsetting[$i]['order'];
					}
					array_multisort($newArr , $newrecommsetting);
					
					$rmlink =  $_G['siteurl']."iyz_index.php?iyzmobile=1&iyzversion=2&module=indexthread";
					
					foreach($newrecommsetting as &$setting) {
						//var_dump($setting) or die();
						unset($setting['flag']);
						unset($setting['order']);
						$setting['data_link'] = $rmlink . "&buttonid=".$button_id."&navid=".$navi_id."&style=".$pic_mode."&view=".$moduleMap[$setting['module']]."&displayid=".$setting['display_id']."&forbiddenid=" . $setting['forbidden_id'];
						//var_dump($setting['data_link']) or die();
						unset($setting['display_id']);
						unset($setting['forbidden_id']);
					}
					$newsetting['thread_config'] = $newrecommsetting;
					$area['recommend'] = $newsetting;
					unset($area['setting']);
				}
			} else {			
				foreach($area['setting'] as &$setting1) {
					unset($setting1['id']);
					unset($setting1['order']);
					unset($setting1['flag']);
				}
				
				//if('hot' === $area['type']) var_dump($home_page[2]) or die();
			}
		}
		
		return $home_page;
	}
	
	//判断对应id的视图是否处于使用之中
	public static function isViewInUse($id, $viewid) {
		if($id == $viewid) return true;
		
		//继续遍历下一层视图
		$view = C::t('common_setting')->fetch("bigapp_view_".$id, true);
		
		if(isset($view[0]) && empty($view[0])) {
			return false;
		} else {
			if(isset($view['title_cfg']) && is_array($view['title_cfg'])) {
				$used = false;
				foreach($view['title_cfg'] as $title) {
					//避免视图引用自身，造成无限循环
					if(is_numeric($title['view_type']) && $id != $title['view_type']) {
						$used = $used || AppDesign::isViewInUse($title['view_type'], $viewid);
					}
					//减枝，缩短时间
					if($used == true) break;
				}
				
				return $used;
			} else {
				return false;
			}
		}
		
		return false;
	}
	
}

?>
