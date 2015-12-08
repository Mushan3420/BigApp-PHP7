<?php

/***********************************************************
 * @file:   article.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-09-29 12:21:35
 * @modify: 2015-09-29 12:21:35
 * @brief:  article.php
 ***********************************************************/
class Bigapp_Portal_Article
{
    private static $pageSize = 20;

    // 根据文章id获取文章列表
    public static function getByArticleIds(array &$aidarr)
    {/*{{{*/
        $res = array(
            "total" => 0,
            "list"  => array(),
            "incache" => 0,
        );
        if (empty($aidarr)) {
            return $res;
        }
        // 先从cache中读取
        $aids = implode(",",$aidarr);
        $key = $aids;
        $v = self::load_getarticle_cache($key);
        if ($v!==false) {
            $res = $v;
            $res["incache"] = 1;
        }
        // 再查表
        else {
			$wheresql = "aid in ($aids) AND status=0 AND url=''";
			$query = C::t('portal_article_title')->fetch_all_by_sql($wheresql);
			foreach ($query as $im) {
				$at = self::getFields("aid,catid,uid,username,title,author,from,fromurl,summary,pic,allowcomment,owncomment,status,dateline", $im);
                $at["attachment_urls"] = self::getPictures($at["aid"], $at["pic"]);
				    if(isset($at["attachment_urls"][0])) {
							 $at["pic"] = $at["attachment_urls"][0];
					 }
                $at["dateline"] = date("Y-m-d H:i", $at["dateline"]);
				$res["list"][] = $at;
			}
            $res["total"] = count($res["list"]);
            self::save_getarticle_cache($key, $res);
        }
        return $res;
    }/*}}}*/

    // 根据频道id获取文章列表，并排除指定的文章
    public static function getArticalInChannelExceptAids(array &$cidarr, array &$aidarr, $page)
    {/*{{{*/
        $limit = self::$pageSize;
        $start = ($page-1) * $limit;
        $res = array(
            "total" => 0,
            "total_page" => 0,
            "list"  => array(),
            "incache" => 0,
        );
        if (empty($cidarr) || $page<1) {
            return $res;
        }

        // 先从cache中读取
		$aids = implode(",",$aidarr);
        $cids = implode(",", $cidarr);
        $key = $aids."_".$cids."_".$page;
        $v = self::load_getarticle_cache($key);
        if ($v!==false) {
            $res = $v;
            $res["incache"] = 1;
        }
        // 再查表
        else {
			// query current page
			$wheresql = "catid in ($cids) AND status=0 AND url=''";
			if (!empty($aidarr)) {
				$wheresql.= " AND aid not in ($aids)";
			}
            $sql = 'SELECT SQL_CALC_FOUND_ROWS * '.
                   'FROM '.DB::table('portal_article_title').' '.
                   'WHERE '.$wheresql.' '.
                   'ORDER BY dateline DESC '.
                   'LIMIT '.$start.', '.$limit;
			$query = DB::query($sql);
			while($im = DB::fetch($query)){
				$at = self::getFields("aid,catid,uid,username,title,author,from,fromurl,summary,pic,allowcomment,owncomment,status,dateline", $im);
                $at["attachment_urls"] = self::getPictures($at["aid"], $at["pic"]);
					 if(isset($at["attachment_urls"][0])) {
							 $at["pic"] = $at["attachment_urls"][0];
					 }
                $at["dateline"] = date("Y-m-d H:i", $at["dateline"]);
                $res["list"][] = $at;
			}
            // query count
			$sql = 'select FOUND_ROWS() AS total';
			$query = DB::query($sql);
			$total = 0;
			while($tmp = DB::fetch($query)){
				$total = $tmp['total'];
			}
            $res["total"] = $total;
            $res["total_page"] = intval(($total-1)/$limit)+1;

            if (!empty($res["list"])) {
				self::save_getarticle_cache($key, $res);
            }
        }
        return $res;
    }/*}}}*/

    // 清除缓存
    public static function clearCache(array &$cidarr, array &$aidarr) 
    {/*{{{*/
		global $_G;
        $cacheKeys = array();

        //1. 文章id缓存
        $aids = implode(",",$aidarr);
		$cachekey = "bigapp:getarticle:$aids";
		loadcache($cachekey);
		if (isset($_G['cache'][$cachekey])) {
            $cacheKeys[] = $cachekey;
		}

        //2. 频道id和指定的文章ids的缓存（所有页）
        $limit = self::$pageSize;
		$aids = implode(",",$aidarr);
        $cids = implode(",", $cidarr);
        $page = 1;
        $key = $aids."_".$cids."_".$page;
        $v = self::load_getarticle_cache($key);
        if ($v!==false) {
            $total = $v["count"];
            $page_counts = intval(($total-1)/$limit)+1;
            for ($i=1; $i<=$page_counts; ++$i) {
                $key = $aids."_".$cids."_".$i;
                $cachekey = "bigapp:getarticle:$key";
				loadcache($cachekey);
				if (isset($_G['cache'][$cachekey])) {
					$cacheKeys[] = $cachekey;
				}
            }
        }

        //3. 删除
		if (!empty($cacheKeys)) {
			C::t('common_syscache')->delete((array)$cacheKeys);
		}
    }/*}}}*/


    /* ----------------------------------------------------------- */

    private static function getFields($fields, array &$arr)
    {/*{{{*/
        $fieldarr = explode(",", $fields);
        $res = array();
        foreach ($fieldarr as $f) {
            $res[$f] = $arr[$f];
        }
        return $res;
    }/*}}}*/

    private static function load_getarticle_cache($key)
    {/*{{{*/
		global $_G;
		$cachekey = "bigapp:getarticle:$key";
		$res = loadcache($cachekey);
		if (isset($_G['cache'][$cachekey])) {
			$expire = $_G['cache'][$cachekey]["expire"];
			if ($expire>time()) {
				return $_G['cache'][$cachekey];
			}
		}
		return false;
    }/*}}}*/

    private static function save_getarticle_cache($key, $value)
    {/*{{{*/
		$cachekey = "bigapp:getarticle:$key";
		$value['expire'] = time() + 3600; //!< 过期时间
		savecache($cachekey, $value);
    }/*}}}*/

    private static function getPictures($aid, $pic) 
    {/*{{{*/
        $res = array();
        global $_G;
        $curl = rtrim($_G['siteurl'], '/');
        $siteurl = str_replace("/api/mobile","",$curl);
        $picurl_prefix = rtrim($siteurl, '/')."/data/attachment/";
        if ($pic!="") {
            $res[] = $picurl_prefix.$pic;
        }
        return $res;
    }/*}}}*/

}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
