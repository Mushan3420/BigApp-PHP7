<?php
require_once dirname(__FILE__).'/../bigappjson.class.php';
require_once dirname(__FILE__).'/utils.inc.php';

function getPortalConfigure($setting=null)
{
	$config[] = array('title' => Utils::converGbkString('首页'),'module'=> Utils::converGbkString('门户首页'), 'enable'=> '1', 'sort' => 1,'id'=>'0','type'=>'4');
    global $_G;
    loadcache('portalcategory');
    $portalcategory = $_G['cache']['portalcategory'];
	
	if(false && !empty($portalcategory)){
		foreach($portalcategory as $cat){
			if($cat['closed'] != 1){
				if(isset($setting['portal']) && !empty($setting['portal'])){
					foreach($setting['portal'] as $category){
						if($category['id'] == $cat['catid'] && $category['enable'] == 1){
							$config[] = array('title' => Utils::converGbkString($category['title']),'module'=>$cat['catname'], 'enable'=> '1', 'sort' => $category['sort'],'id'=>$cat['catid'],'type'=>'4');
						}
					}
				}
			}
		}
		$newArr=array();
		for($j=0; $j < count($config); $j++){
			$newArr[]=$config[$j]['sort'];
		}
		array_multisort($newArr , $config);
	}
    return $config;
}
?>
