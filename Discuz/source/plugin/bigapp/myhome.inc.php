<?php
/**
* @file myhome.inc.php
* @Brief my home configuration for admin center 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once dirname(__FILE__) . '/conf/conf.inc.php';
require_once dirname(__FILE__) . '/libs/utils.inc.php';
require_once dirname(__FILE__) . '/libs/menu.inc.php';
$fun = "1";
$desc = 0;
if(isset($_GET['fun']) && $_GET['fun'] == "1"){
	$fun = "1";
}

if(isset($_GET['fun']) && $_GET['fun'] == "2"){
	$fun = "2";
	$desc = 1;
}

$tpl = "banner.tpl";
if(isset($_GET['tpl']) && $_GET['tpl'] == 'func'){
	$tpl = "func.tpl";
	if($fun == 2){
		$tpl = "func1.tpl";
	}
}else{
	$fun = "0";
}


$url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:homeapi';
$params = array();
$params['setBanner'] = $url . '&method=setBanner';
$params['getBanner'] = $url . '&method=getBanner';
$params['setFunc'] = $url . '&method=setFunc&fun='.$fun;
$params['getFunc'] = $url . '&method=getFunc&fun='.$fun;
$params['setSwitch'] = $url . '&method=setSwitch&fun='.$fun;
$params['getSwitch'] = $url . '&method=getSwitch&fun='.$fun;
$params['banner_image_s'] = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('banner_image_s');
$params['func_image_s'] = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('func_image_s');
$params['func_forum_image_s'] = rtrim($_G['siteurl'], '/') . '/' . BigAppConf::$upfileUrl . '&key=' . urlencode('func_forum_image_s');
$params['imageSize'] = BigAppConf::$imgRequire;
$params['descVisible'] = $desc;
$params['setThreadSetting'] = $url . '&method=setThreadSetting';
$params['getThreadSetting'] = $url . '&method=getThreadSetting';
$params['setPortalSetting'] = $url . '&method=setPortalSetting';

$tplVars = array("plugin_path"=>rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp','html'=>'');
if(isset($_GET['tpl']) && $_GET['tpl'] == 'threadsetting') {
	$tpl = "threadsetting.tpl";
	
	loadcache('portalcategory');
    $portalcategory = $_G['cache']['portalcategory'];
	$setting = C::t('common_setting')->fetch("bigapp_home_threadsetting", false);
	if(!empty($setting)){
		$setting = json_decode($setting, true);
	}
	$title_new = false && isset($setting['title_new'])?Utils::converGbkString($setting['title_new']):Utils::converGbkString('最新');
	$sort_new = isset($setting['sort_new'])?$setting['sort_new']:1;
	$enable_new = (isset($setting['enable_new'])?$setting['enable_new']:true) !== 'false'?'checked':'';
	
	$title_hot = false && isset($setting['title_hot'])?Utils::converGbkString($setting['title_hot']):Utils::converGbkString('热门');
	$sort_hot = isset($setting['sort_hot'])?$setting['sort_hot']:2;
	$enable_hot = (isset($setting['enable_hot'])?$setting['enable_hot']:true)!== 'false'?'checked':'';
	
	$title_fav = false && isset($setting['title_fav'])?Utils::converGbkString($setting['title_fav']):Utils::converGbkString('精华');
	$sort_fav = isset($setting['sort_fav'])?$setting['sort_fav']:3;
	$enable_fav = (isset($setting['enable_fav'])?$setting['enable_fav']:true)!== 'false'?'checked':'';
	
	$new = Utils::converGbkString('最新帖子');
	$hot = Utils::converGbkString('热门帖子');
	$fav = Utils::converGbkString('精华帖子');
	$html = <<<EOF
	<tr>
      <td valign='top'><input id='enable_new' type='checkbox' name='new_check' $enable_new/></td>
      <td><input type='text' id='sort_new' class='txt' style="width:20px" value="$sort_new"/></td>
      <td>$new</td>
	  <td><input type='text' id='title_new' class='txt' style="width:100px" value="$title_new" disabled/></td>
    </tr>
	
	<tr>
      <td valign='top'><input id='enable_hot' type='checkbox' name='new_check' $enable_hot/></td>
      <td><input type='text' id='sort_hot' class='txt' style="width:20px" value="$sort_hot"/></td>
      <td>$hot</td>
	  <td><input type='text' id='title_hot' class='txt' style="width:100px" value="$title_hot" disabled/></td>
    </tr>
	
	<tr>
      <td valign='top'><input id='enable_fav' type='checkbox' name='new_check' $enable_fav/></td>
      <td><input type='text' id='sort_fav' class='txt' style="width:20px" value="$sort_fav"/></td>
      <td>$fav</td>
	  <td><input type='text' id='title_fav' class='txt' style="width:100px" value="$title_fav" disabled/></td>
    </tr>
EOF;
	
	if(!empty($portalcategory)){
		foreach($portalcategory as $cat){
			if($cat['closed'] != 1){
				$enable = $sort = 0;
				$title = $cat['catname'];
				if(isset($setting['portal']) && !empty($setting['portal'])){
					foreach($setting['portal'] as $category){
						if($category['id'] == $cat['catid']){
							$sort = isset($category['sort'])?$category['sort']:10;
						    $enable = isset($category['enable'])?$category['enable']:0;
							$title = isset($category['title'])?Utils::converGbkString($category['title']):$cat['catname'];
							break;
						}
					}
				}
				$html .= "<tr class='myportal'>
										  <td valign='top'><input class='myportal_enable' type='checkbox' ". ( $enable == 1 ? "checked" : "" )."/></td>
										  <td><input type='text' class='txt myportal_sort' style='width:20px' value='".$sort."'/></td>
										  <input type='hidden' class='myportal_id'  value='".$cat['catid']."'/>
										  <td>".$cat['catname']."</td>
										  <td><input type='text'  class='txt myportal_title' style='width:100px' value='".$title."'/></td>
									  </tr>";
				
			}
		}
	}
	$tplVars['html'] = $html;
}

if(isset($_GET['tpl']) && $_GET['tpl'] == 'portal') {
	$tpl = "portal.tpl";
	
	loadcache('portalcategory');
    $portalcategory = $_G['cache']['portalcategory'];
	$setting = C::t('common_setting')->fetch("bigapp_home_portalsetting", false);
	if(!empty($setting)){
		$setting = json_decode($setting, true);
	}
	$html = "<tr class='myportal'>
					  <td valign='top'><input class='myportal_enable' type='checkbox' name='new_check' checked disabled/></td>
					  <td><input type='text' class='txt myportal_sort' style='width:20px' value='1' disabled/></td>
					  <input type='hidden' class='myportal_id'  value='0'/>
					  <td>".$lang['portal_home']."</td>
					  <td><input type='text'  class='txt myportal_title' style='width:100px' value='".$lang['portal_home_title']."'/></td>
					</tr>";
	if(!empty($portalcategory)){
		foreach($portalcategory as $cat){
			if($cat['closed'] != 1){
				$enable = $sort = 0;
				$title = $cat['catname'];
				if(isset($setting['portal']) && !empty($setting['portal'])){
					foreach($setting['portal'] as $category){
						if($category['id'] == $cat['catid']){
							$sort = isset($category['sort'])?$category['sort']:10;
						    $enable = isset($category['enable'])?$category['enable']:0;
							if(isset($category['title'])){
								$title = Utils::converGbkString($category['title']);
							}
							break;
						}
					}
				}
				$html .= "<tr class='myportal'>
										  <td valign='top'><input class='myportal_enable' type='checkbox' ". ( $enable == 1 ? "checked" : "" )."/></td>
										  <td><input type='text' class='txt myportal_sort' style='width:20px' value='".$sort."'/></td>
										  <input type='hidden' class='myportal_id'  value='".$cat['catid']."'/>
										  <td>".$cat['catname']."</td>
										  <td><input type='text' class='txt myportal_title' style='width:100px' value='".$title."'/></td>
									  </tr>";
				
			}
		}
	}
	$tplVars['html'] = $html;
	
}

if(isset($_GET['debug']) && $_GET['debug'] == '1'){
	echo json_encode($params);exit;
}

Utils::loadTemplate(dirname(__FILE__) . '/view/'.$tpl, $params ,$tplVars);
runlog('bigapp', "show $tpl page succ");
?>
