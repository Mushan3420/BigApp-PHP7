<?php
/**
* @file myportal.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

if(!isset($_GET['mod']) || !in_array($_GET['mod'],array('list','view'))){
	exit('Access Denied');
}

include_once 'portal.php';

class BigAppAPI {

	function common() {
		global $_G;
		 $_G['makehtml'] = true;//hack for header other url
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
	}

	function output() {
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}

		$variable['data'] = "";
		$formatRec = array('aid','catid','title','summary','pic','dateline','catname','content','url','contents');
		if('list' == $_GET['mod']){
			$variable['data'] = array();
			$_G['catid'] = $catid = max(0,intval($_GET['catid']));
			$page = max(1, intval($_GET['page']));
			$cat = category_remake($catid);
			if(!empty($cat)){
				$wheresql = category_get_wheresql($cat);
				$list = category_get_list($cat, $wheresql, $page);
				if(!empty($list)){
					$articleList = array();
					foreach($list['list'] as $key => $value){
						if(!empty($value['pic'])){
							$tmp = parse_url($value['pic']);
							if(!isset($tmp['scheme'])){
								$url = ApiUtils::getDzRoot() . $value['pic'];
							}else{
								$url = str_replace('source/plugin/mobile/', '', $attach);
								$url = str_replace('source/plugin/mobile/', '', $url);
							}
							$list['list'][$key]['pic'] = $url;
						}
						//暂时不支持存在url 跳转的文章
						if(isset($value['url']) && !empty($value['url'])){
							unset($list['list'][$key]);
							continue;
						}
						foreach($value as $k=>$v){
							if(!in_array($k,$formatRec)){
								unset($list['list'][$key][$k]);
							}
						}
						$articleList[] = $list['list'][$key];
					}
					$variable['data'] = $articleList;
					$variable['perpage'] = $cat['perpage'];
					$variable['needmore'] = count($articleList) < $cat['perpage'] ? '0':'1';
				}
			}
		}else{
			$aid = empty($_GET['aid'])?0:intval($_GET['aid']);
			$article = C::t('portal_article_title')->fetch($aid);
			if(!empty($article)){
				$content = C::t('portal_article_content')->fetch_all($aid);
				if(is_array($content)){
					foreach($content as $i => $c){
						if($i != 0){
							$content[0]['content'] .= $c['content'];
						}
					}
				}
				$article = array_merge($content[0],$article);
				foreach($article as $k=>$v){
					if(!in_array($k,$formatRec)){
						unset($article[$k]);
					}
				}
				$article['content'] = self::filterContent($article['content']);
				$article['dateline'] = date('Y-m-d H:i',$article['dateline']);
				$article['share_url'] = rtrim(ApiUtils::getDzRoot(), '/') . '/portal.php?mod=view&aid='.$aid;
				$variable['data'] = $article;
			}
		}
		bigapp_core::result(bigapp_core::variable($variable));
	}
	
	public function filterContent($content){
		$content = preg_replace("/<a[^>]*>(.*)<\/a>/isU",'${1}',$content);
		$content = preg_replace("/<img(.*)?src=/isU",'<img src=',$content);
		$content = BigAppAPI::_findimg($content);
		$content = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $content);
		$content = preg_replace('/(&nbsp;){2,}/', '', $content);
		return $content;
	}
	
	function _findimg($string) {
		return preg_replace_callback('/(<img src=\")(.+?)(\".*?\>)/is', function($matches) { return BigAppAPI::_parseimg($matches[1], $matches[2], $matches[3]); }, $string);
	}

	function _parseimg($before, $img, $after) {
		$before = stripslashes($before);
		$after = stripslashes($after);
		if (!in_array(strtolower(substr($img, 0, 6)), array('http:/', 'https:', 'ftp://'))) {
			global $_G;
			$img = $_G['siteurl'] . $img;
		}
		return $before . $img . $after;
	}

}

?>
