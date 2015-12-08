<?php
/**
 * @file viewthread.php
 * @Brief 
 * @author youzu
 * @version 1
 * @date 2015-04-03
 */
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
class BigAppAPI {
	
	function unparsesmiles(&$message) {
		global $_G;
		
		$new_replacearray = preg_replace("/[\s]+smilieid(.*)alt/", "", $_G['cache']['smilies']['replacearray']);
		$new_replacearray = preg_replace("/=\"\"/", "", $new_replacearray);
		
		static $enablesmiles;
		if($enablesmiles === null) {
			$enablesmiles = false;
			if(!empty($_G['cache']['smilies']) && is_array($_G['cache']['smilies'])) {
				$enablesmiles = true;
			}
		}
		$message = str_replace($_G["siteurl"], "", $message); 
		$enablesmiles && $message = preg_replace($new_replacearray, $_G['cache']['smilies']['searcharray'], $message/*, $_G['setting']['maxsmilies']*/);
	
		$message = str_replace('\\',"", $message);
		$message = str_replace('</',"", $message);
		$message = str_replace('/>',"", $message);
		return $message;
	}
	
	function common() {
		global $_G;
		
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		if($_G['ppp'] < 10){
			$_G['ppp'] = 10;
		}
		if(isset($_REQUEST['ppp']) && is_int($_REQUEST['ppp'])){
			$_G['ppp'] = $_REQUEST['ppp'];
		}
		$_G['forum']['jammer'] = 0; //close jammer
		if(isset($_G['setting']['bigapp_settings'])){
            $tmp = unserialize($_G['setting']['bigapp_settings']);
        }
        if(!isset($tmp['enable_pic_opt'])){
            $tmp['enable_pic_opt'] = 1;   
        }
		
		//楼层跳转
		if(isset($_REQUEST['postno'])){
			$postno = intval($_REQUEST['postno']);
			$postInfo = BigAppAPI::_jumpThread($postno);
		}
        BigAppConf::$enablePicOpt = (!!$tmp['enable_pic_opt']);	
	}

	static $collect = array();
	static $attachments = array();

	function modifyPost2(&$postList)
	{
		$collect = &BigAppAPI::$collect;
		$attachments = &BigAppAPI::$attachments;
	
		$idx = 0;
		foreach ($postList as &$post){
			if(isset($post['authorid']) && $post['authorid']){
				$post['avatar'] = avatar($post['authorid'], 'big', true); 
				$variable['avatar'] = str_replace("\r", '', $variable['avatar']);
				$variable['avatar'] = str_replace("\n", '', $variable['avatar']);
			}else{
				$post['avatar'] = '';
			}
			$addStr = '';
			$collect = array();
			$attachments = $post['attachments'];
			//$post['message'] = self::unparsesmiles($post['message']);
			if(function_exists('iconv')){
				$post['message'] = iconv(CHARSET, 'UTF-8//ignore', $post['message']);
			}else{
				$post['message'] = mb_convert_encoding($post['message'], 'UTF-8', CHARSET);
			}
			$result = preg_replace_callback('/(\\r\\n)|(\\r)|(\\n)|<img.*?src="(.+?)".*?\/\>|' . 
					'\[attach\]([0-9]+?)\[\/attach\]|(<a.*?href=".*?\.swf.*?".*?\>.*?\.swf.*?<\/a\>)|' . 
					'(<a.*?>.*?<\/a\>)|(<a.*?\/>)|(http:\/\/|ftp:\/\/|https:\/\/){0,1}(([a-zA-Z0-9\._-]+\.[a-zA-Z]{2,6})|' . 
					'([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,4})*(\/[a-zA-Z0-9\&%_\.\/-;=?-~\-]*)?/', 
					'BigAppAPI::callback2', $post['message']);
			$result = '__DONT_DICONV_TO_UTF8___' . $result;
			foreach ($attachments as $attach){
				//$attach['absurl'] = ApiUtils::getDzRoot() . $attach['url'] . $attach['attachment'];
				if(strpos($attach['url'],'http') === false ){
					$attach['absurl'] = ApiUtils::getDzRoot() . $attach['url'] . $attach['attachment'];
				}else{
					$attach['absurl'] = $attach['url'] . $attach['attachment'];
				}
				global $_G;
				if(true === BigAppConf::$enablePicOpt && ApiUtils::isOptFix($attach['absurl']) ){
					$attach['absurl'] = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:optpic&mod=__x__&size=' . urlencode(BigAppConf::$detailSize) . '&url=' . $attach['absurl'];
					$attach['absurl'] = str_replace('source/plugin/mobile/', '', $attach['absurl']);
					$attach['absurl'] = str_replace('source/plugin/bigapp/', '', $attach['absurl']);
				}
				if($attach['isimage']){
					$desc = (isset($attach['description']) ? $attach['description'] : '');
					$atPrefix = lang('plugin/bigapp', 'attachment');
					if(function_exists('iconv')){
                        $adesc = iconv(CHARSET, 'UTF-8//ignore', $desc);
						$atPrefix = iconv(CHARSET, 'UTF-8//ignore', $atPrefix);
                    }else{
                        $adesc = mb_convert_encoding($desc, 'UTF-8', CHARSET);
						$atPrefix = mb_convert_encoding($atPrefix, 'UTF-8', CHARSET);
                    }

					if(isset($attach['description']) && $attach['description']){
						$addStr .= '<br />' . $atPrefix . ': ' . $adesc;
					}
                    $addStr .= '<br /><img src="' . $attach['absurl'] . '" alt="' . $adesc . "\" />";
					$collect[] = array('type' => 2, 'url' => $attach['absurl'], 'description' => $desc);
				}
			}
			$result .= $addStr;
			$result = str_replace('</tr><td', '<td', $result); 
			$result = str_replace('<table', '<table border="1" width="100%"', $result);
			
			$post['message'] = $result;
			if(empty($post['attachments'])){
				$post['attachments'] = array();	
			}else{
				$post['attachments'] = array_values($post['attachments']);
			}
			$idx += 1;
		}
		
	}

	static function callback2($matches)
	{
		global $_G;
		$collect = &BigAppAPI::$collect;
		$attachments = &BigAppAPI::$attachments;
		if(isset($matches[1]) && !empty($matches[1])){
			return '<br />';
		}
		//回车、换行或者swf格式，直接删除
		if(isset($matches[2]) && !empty($matches[2]) || isset($matches[3]) && !empty($matches[3]) || isset($matches[6]) && !empty($matches[6])){
			return '';
		}
		//非链接形式的URL地址，增加可点击
		if(isset($matches[9])){
			$url = $matches[0];
			if(empty($matches[9])){
				$url = 'http://' . $url;
			}
			return '<a href="' . $url . '" target="_blank">' . $matches[0] . '</a>';
		}
		//普通形式的、可点击的，直接返回
		if(isset($matches[7]) || isset($matches[8])){
			return $matches[0];
		}
		//图片或表情
		$smiles = array(
			'smile.gif' => "\xF0\x9F\x98\x8C",
			'sad.gif' => "\xF0\x9F\x98\x94",
			'biggrin.gif' => "\xF0\x9F\x98\x83",
			'cry.gif' => "\xF0\x9F\x98\xAD",
			'huffy.gif' => "\xF0\x9F\x98\xA0",
			'shocked.gif' => "\xF0\x9F\x98\xB2",
			'shocked.png' => "\xF0\x9F\x98\xB2",
			'tongue.gif' => "\xF0\x9F\x98\x9C",
			'shy.gif' => "\xF0\x9F\x98\x86",
			'titter.gif' => "\xF0\x9F\x98\x9D",
			'sweat.gif' => "\xF0\x9F\x98\x93",
			'mad.gif' => "\xF0\x9F\x98\xAB",
			'lol.gif' => "\xF0\x9F\x98\x81",
			'loveliness.gif' => "\xF0\x9F\x98\x8A",
			'funk.gif' => "\xF0\x9F\x98\xB1",
			'curse.gif' => "\xF0\x9F\x98\xA4",
			'dizzy.gif' => "\xF0\x9F\x98\x96",
			'shutup.gif' => "\xF0\x9F\x98\xB7",
			'sleepy.gif' => "\xF0\x9F\x98\xAA",
			'hug.gif' => "\xF0\x9F\x98\x9A",
			'victory.gif' => "\xE2\x9C\x8C",
			'time.gif' => "\xE2\x8F\xB0",
			'kiss.gif' => "\xF0\x9F\x92\x8B",
			'handshake.gif' => "\xF0\x9F\x91\x8C",
			'call.gif' => "\xF0\x9F\x93\x9E",
			'sun.png' => "\xF0\x9F\x8C\x9E",
		);	
		if(isset($matches[4]) && !empty($matches[4])){
/*			$out = preg_match('/smile.gif|sad.gif|biggrin.gif|cry.gif|huffy.gif|shocked.gif|shocked.png|tongue.gif|shy.gif|titter.gif|sweat.gif|' .
					'mad.gif|lol.gif|loveliness.gif|funk.gif|curse.gif|dizzy.gif|shutup.gif|sleepy.gif|hug.gif|victory.gif|time.gif|' . 
					'kiss.gif|handshake.gif|call.gif|sun.png/', $matches[4], $_matches); */
					
			$out = preg_match('/static\/image\/smiley/', $matches[4], $_matches);
			
			if(1 === $out && 1 === count($_matches)){
				$out2 = preg_match('/\/[a-zA-Z0-9_]*.gif|.png|.jpg$/', $matches[4], $_matches);
				
				if(1 === $out && 1 === count($_matches)){
					$url = $matches[4];
					$url = str_replace('source/plugin/mobile/', '', $url);
					$url = str_replace('source/plugin/bigapp/', '', $url);
		
					$ret = '<img class="smile-png" src="' . $url . '?id=bigapp:getsmile" />';
					return $ret;
				}
				//return $smiles[$_matches[0]];
			}
			//src matched
			$arrUrl = parse_url($matches[4]);
			if(!isset($arrUrl['scheme'])){
				$ret = ApiUtils::getDzRoot() . $matches[4];
			}else{
				$ret = str_replace('source/plugin/mobile/', '', $matches[4]);
				$ret = str_replace('source/plugin/bigapp/', '', $ret);
			}
			if(true === BigAppConf::$enablePicOpt && ApiUtils::isOptFix($ret)){
				$ret = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:optpic&mod=__x__&size=' . urlencode(BigAppConf::$detailSize) . '&url=' . urlencode($ret);
			}
			$collect[] = array('type' => 1, 'url' => $ret);  //orginal picture
			//$ret = 'src="' . $ret . '"';
			$ret = str_replace($matches[4], $ret, $matches[0]);
			return $ret;
		}
		if(isset($matches[5]) && !empty($matches[5])){
			$url = $matches[5];
			if(true === BigAppConf::$enablePicOpt && ApiUtils::isOptFix($url)){
				$url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:optpic&mod=__x__&size=' . urlencode(BigAppConf::$detailSize) . '&url=' . urlencode($matches[5]);
				$url = str_replace('source/plugin/mobile/', '', $url);
				$url = str_replace('source/plugin/bigapp/', '', $url);
			}
			return '<img src="' . $url . '"></img>';
		}
		//attach matched
		if(!isset($matches[5])){
			return $matches[0];
		}
		if(!isset($attachments[$matches[5]])){
			return $matches[0];
		}
		if(!$attachments[$matches[5]]['isimage']){
			return ''; //if not an image, ignore
		}
		$url = $attachments[$matches[5]]['url'] . $attachments[$matches[5]]['attachment'];;
		$tmp = parse_url($url);
		if(!isset($tmp['scheme'])){
			$url = ApiUtils::getDzRoot() . $attachments[$matches[5]]['url'] . $attachments[$matches[5]]['attachment'];
		}
		if(true === BigAppConf::$enablePicOpt && ApiUtils::isOptFix($url)){
			$url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:optpic&mod=__x__&size=' . urlencode(BigAppConf::$detailSize) . '&url=' . urlencode($url);
			$url = str_replace('source/plugin/mobile/', '', $url);
			$url = str_replace('source/plugin/bigapp/', '', $url);
		}
		$collect = array('type' => 2, 'url' => $url, 'description' => (isset($attachments[$matches[5]]['description']) ? $attachments[$matches[5]]['description'] : ''));
		unset($attachments[$matches[5]]);
		return '<img src="' . $url . '"></img>';	
	}

	function getPollInfo()
	{
		global $_G;
		$info = array('is_poll' => 0);
		if(!isset($_G['forum_thread']['special']) || 1 != $_G['forum_thread']['special'] || empty($GLOBALS['polloptions'])){
			return $info;
		}
		$info['is_poll'] = 1;
		$info['data'] = array(
			'allowvotethread' => 0,  //主题当前是否允许投票（如果关闭或过期时，则不允许投票，不要显示投票按钮）
			'allowvoteusergroup' => 0, //当前用户组是否具有参与投票的资格，如果没有，则客户端可以考虑提示用户所在用户组无资格
			'allowvotepolled' => 1, //在前两个参数均为1的情况下，该参数指示用户是否可以投票，为1时表示可以投票，为0时表示已经投过票了，客户端可以考虑予以提示
			'optiontype' => 'radio', //展示选项时，展示的选择按钮是什么类型，可能的值还有checkbox
			'isimagepoll' => 0, //选项是否是图片，只要有一个选项是图片，则该字段就是1，但有可能有的选项没有图片，需要UE提供一张默认图片作为填充
			'voterscount' => 0, //一共有多少用户参与了投票
			'visiblepoll' => 0, //在用户所在用户组具有投票资格的情况下，是否提示投票后即可见投票结果，这也会决定客户端后续的行为，一旦该值为1，表示用户尚未投票，不应该展示投票结果，但投票后，客户端应该立即展示投票结果
			'multiple' => 0, //是否可以多选
			'maxchoices' => 1, //最多可以选择几项进行投票
			'remaintime' => array('day' => -1, 'hour' => -1, 'minute' => -1, 'second' => -1),  //剩余多少时间，-1表示不限制
			'options' => array(),
		);
		if(isset($GLOBALS['allowvotethread'])){
			$info['data']['allowvotethread'] = intval($GLOBALS['allowvotethread']);
		}
		if(isset($GLOBALS['allwvoteusergroup'])){
			$info['data']['allowvoteusergroup'] = intval($GLOBALS['allwvoteusergroup']);
		}
		if(isset($GLOBALS['optiontype'])){
			$info['data']['optiontype'] = $GLOBALS['optiontype'];
		}
		if(isset($GLOBALS['allowvotepolled'])){
			$info['data']['allowvotepolled'] = intval($GLOBALS['allowvotepolled']);
		}
		if(isset($GLOBALS['isimagepoll'])){
			$info['data']['isimagepoll'] = intval($GLOBALS['isimagepoll']);
		}
		if(isset($GLOBALS['voterscount'])){
			$info['data']['voterscount'] = intval($GLOBALS['voterscount']);
		}
		if(isset($GLOBALS['visiblepoll']) && $GLOBALS['visiblepoll']  && $_G['group']['allowvote']){
			$info['data']['visiblepoll'] = intval($GLOBALS['visiblepoll']);
		}
		if(isset($GLOBALS['polloptions'])){
			$info['data']['options'] = $GLOBALS['polloptions'];
		}
		if(isset($GLOBALS['multiple'])){
			$info['data']['multiple'] = intval($GLOBALS['multiple']);
		}
		if(isset($GLOBALS['maxchoices'])){
			$info['data']['maxchoices'] = intval($GLOBALS['maxchoices']);
		}
		if(isset($_G['forum_thread']['remaintime']) && !empty($_G['forum_thread']['remaintime'])){
			$info['data']['remaintime'] = array(
				'day' => $_G['forum_thread']['remaintime'][0],
				'hour' => $_G['forum_thread']['remaintime'][1],
				'minute' => $_G['forum_thread']['remaintime'][2],
				'second' => $_G['forum_thread']['remaintime'][3],
			);
		}
		foreach ($info['data']['options'] as &$option){
			if(empty($option['imginfo'])){
				unset($option['imginfo']);
				continue;
			}
			$option['imginfo'] = array(
				'image_small' => ApiUtils::getDzRoot() . $option['imginfo']['small'],
				'image' => ApiUtils::getDzRoot() . $option['imginfo']['big']
			);
			unset($option['width']);
			unset($option['color']);
		}
		unset($option);
		return $info;
	}

	function output() {
		global $_G, $thread;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		if ($GLOBALS['hiddenreplies']) {
			foreach ($GLOBALS['postlist'] as $k => $post) {
				if (!$post['first'] && $_G['uid'] != $post['authorid'] && $_G['uid'] != $_G['forum_thread']['authorid'] && !$_G['forum']['ismoderator']) {
					$GLOBALS['postlist'][$k]['message'] = lang('plugin/mobile', 'mobile_post_author_visible');
					$GLOBALS['postlist'][$k]['attachments'] = array();
				}
			}
		}	
		$_G['thread']['lastpost'] = dgmdate($_G['thread']['lastpost']);
		$_G['thread']['ordertype'] = $GLOBALS['ordertype'];
		if (!empty($_GET['viewpid'])) {
			$GLOBALS['postlist'][$_GET['viewpid']] = $GLOBALS['post'];
		}
		if(!$_G['thread']['maxposition']){
			$_G['thread']['maxposition'] = "1";
		}
		if ($GLOBALS['rushreply']) {
			$_G['thread']['rushreply'] = $GLOBALS['rushreply'];
			$_G['thread']['rushresult'] = $GLOBALS['rushresult'];
		}
		foreach ($GLOBALS['comments'] as $pid => $comments) {
			$comments = bigapp_core::getvalues($comments, array('/^\d+$/'), array('id', 'tid', 'pid', 'author', 'authorid', 'dateline', 'comment', 'avatar'));
			foreach ($comments as $k => $c) {
				$comments[$k]['avatar'] = avatar($c['authorid'], 'small', true);
				$comments[$k]['dateline'] = dgmdate($c['dateline'], 'u');
			}
			$GLOBALS['comments'][$pid] = $comments;
		}		
		
		$variable = array(
				'thread' => $_G['thread'],
				'fid' => $_G['fid'],
				'postlist' => array_values(bigapp_core::getvalues($GLOBALS['postlist'], array('/^\d+$/'), array('uid', 'pid', 'tid', 
						'author', 'first', 'dbdateline', 'dateline', 'username', 'adminid', 'memberstatus', 'authorid', 'username', 
						'groupid', 'memberstatus', 'status', 'message', 'number', 'memberstatus', 'groupid', 'attachment', 'attachments', 
						'attachlist', 'imagelist', 'anonymous', 'extcredits2', 'posts', 'threads', 'authortitle', 'position', 'postreview', 'isWater'))),
				'allowpostcomment' => $_G['setting']['allowpostcomment'],
				'comments' => $GLOBALS['comments'],
				'commentcount' => $GLOBALS['commentcount'],
				'imagelist' => array(),
				'ppp' => $_G['ppp'],
				'totalpage' => $GLOBALS['totalpage'],
				'setting_rewriterule' => $_G['setting']['rewriterule'],
				'setting_rewritestatus' => $_G['setting']['rewritestatus'],
				'forum_threadpay' => $_G['forum_threadpay'],
				'cache_custominfo_postno' => $_G['cache']['custominfo']['postno'],
				);
				
		//帖子举报
		$variable['report']['enable'] = '1';
		$variable['report']['handlekey']='miscreport'.$variable['postlist'][0]['tid'];

		$language_file = 'source/language/lang_template.php';
                
		if(file_exists($language_file)) {
			require_once $language_file;
		}
		$report_msg = explode(",", $lang['report_reason_message']);
		
		foreach($report_msg as $key => $msg) {
			#$report_msg[$key] = preg_replace('/[|\'/', '', $msg);                    
			$msg = str_replace('[', '', $msg);
			$msg = str_replace(']', '', $msg);
			$msg = str_replace("'", "", $msg);
			$report_msg[$key] = $msg;
		}
		
		if(empty($report_msg[0])) {
			$variable['report']['content'] = array();
		} else {
			$variable['report']['content'] = $report_msg;
		}
		
		foreach ($variable['postlist'] as &$_item){
			$_item['dateline'] = preg_replace('/<.*?\>/', '', $_item['dateline']);
		}
		unset($_item);

		if(!empty($GLOBALS['threadsortshow'])) {
			$optionlist = array();
			foreach ($GLOBALS['threadsortshow']['optionlist'] AS $key => $val) {
				$val['optionid'] = $key;
				$optionlist[] = $val;
			}
			if(!empty($optionlist)) {
				$GLOBALS['threadsortshow']['optionlist'] = $optionlist;
				$GLOBALS['threadsortshow']['threadsortname'] = $_G['forum']['threadsorts']['types'][$thread['sortid']];
			}
		}

		$threadsortshow = bigapp_core::getvalues($GLOBALS['threadsortshow'], array('/^(?!typetemplate).*$/'));
		if(!empty($threadsortshow)) {
			$variable['threadsortshow'] = $threadsortshow;
		}
		foreach($variable['postlist'] as $k => &$post) {
			
			if (!$_G['forum']['ismoderator'] && $_G['setting']['bannedmessages'] & 1 && (($post['authorid'] && !$post['username']) || 
					($_G['thread']['digest'] == 0 && ($post['groupid'] == 4 || $post['groupid'] == 5 || $post['memberstatus'] == '-1')))) {
				$message = lang('forum/template', 'message_banned');
			} elseif (!$_G['forum']['ismoderator'] && $post['status'] & 1) {
				$message = lang('forum/template', 'message_single_banned');
			} elseif ($GLOBALS['needhiddenreply']) {
				$message = lang('forum/template', 'message_ishidden_hiddenreplies');
			} elseif ($post['first'] && $_G['forum_threadpay']) {
				$message = lang('forum/template', 'pay_threads') . ' ' . $GLOBALS['thread']['price'] . ' ' . 
						$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['unit'] . 
						$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'];
			} elseif ($_G['forum_discuzcode']['passwordlock']) {
				$message = lang('forum/template', 'message_password_exists');
			} else {
				$message = '';
			}	
			//回帖举报		
			##############################
			/*if($_G['uid'] != $variable['postlist'][$k]['authorid']) {
					$variable['postlist'][$k]['report']['enable'] = '1';
					$variable['postlist'][$k]['report']['handlekey']='miscreport'.$variable['postlist'][$k]['pid'];
					$variable['postlist'][$k]['report']['content'] = lang('plugin/bigapp', 'report_reason_message');
			} else {
					$variable['postlist'][$k]['report']['enable'] = '0';
			}*/
			##############################

			if ($message) {
				$variable['postlist'][$k]['message'] = $message;
			}
			if ($post['anonymous'] && !$_G['forum']['ismoderator']) {
				$variable['postlist'][$k]['username'] = $variable['postlist'][$k]['author'] = $_G['setting']['anonymoustext'];
				$variable['postlist'][$k]['adminid'] = $variable['postlist'][$k]['groupid'] = $variable['postlist'][$k]['authorid'] = 0;
				if ($post['first']) {
					$variable['thread']['authorid'] = 0;
				}
			}
			if (strpos($variable['postlist'][$k]['message'], '[/tthread]') !== FALSE) {
				$matches = array();
				preg_match('/\[tthread=(.+?),(.+?)\](.*?)\[\/tthread\]/', $variable['postlist'][$k]['message'], $matches);
				$variable['postlist'][$k]['message'] = preg_replace('/\[tthread=(.+?)\](.*?)\[\/tthread\]/', lang('plugin/qqconnect', 
						'connect_tthread_message', array('username' => $matches[1], 'nick' => $matches[2])), $variable['postlist'][$k]['message']);
			}	
			
			$variable['postlist'][$k]['message'] = preg_replace("/<a\shref=\"([^\"]+?)\"\starget=\"_blank\">\[viewimg\]<\/a>/is", "<img src=\"\\1\" />", $variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['message'] = BigAppAPI::_findimg($variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['message'] = str_replace('!post_hide_reply_hidden!', lang('plugin/bigapp', 'post_hide_reply_hide'), $variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['message'] = str_replace('post_hide_reply_hidden', lang('plugin/bigapp', 'post_hide_reply_hide'), $variable['postlist'][$k]['message']);
			if ($GLOBALS['aimgs'][$post['pid']]) {
				$imagelist = array();
				foreach ($GLOBALS['aimgs'][$post['pid']] as $aid) {
					$extra = '';
					$url = BigAppAPI::_parseimg('', $GLOBALS['postlist'][$post['pid']]['attachments'][$aid]['url'] . $GLOBALS['postlist'][$post['pid']]['attachments'][$aid]['attachment'], '');
					if ($GLOBALS['postlist'][$post['pid']]['attachments'][$aid]['thumb']) {
						$extra = 'file="' . $url . '" ';
						$url .= '.thumb.jpg';
					}
					$extra .= 'attach="' . $post['pid'] . '" ';
					if (strexists($variable['postlist'][$k]['message'], '[attach]' . $aid . '[/attach]')) {
						$variable['postlist'][$k]['message'] = str_replace('[attach]' . $aid . '[/attach]', '<div class="img"><img src="' . $url . '" ' . $extra . '/></div>'/*mobile_image($url, $extra)*/, $variable['postlist'][$k]['message']);
						unset($variable['postlist'][$k]['attachments'][$aid]);
					} elseif (!in_array($aid, $_G['forum_attachtags'][$post['pid']])) {
						$imagelist[] = $aid;
					}
				}
				$variable['postlist'][$k]['imagelist'] = $imagelist;
			}
			$variable['postlist'][$k]['message'] = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['message'] = preg_replace('/(&nbsp;){2,}/', '', $variable['postlist'][$k]['message']);
			$variable['postlist'][$k]['dateline'] = strip_tags($post['dateline']);
			$variable['postlist'][$k]['groupiconid'] = bigapp_core::usergroupIconId($post['groupid']);
			
			if($post['first']){
				$post['recommends'] = $_G['thread']['recommends'];
				$post['recommend_add'] = $_G['thread']['recommend_add'];
				$post['recommend_sub'] = $_G['thread']['recommend_sub'];
				$post['enable_recommend'] = 0;
				if(($_G['group']['allowrecommend'] || !$_G['uid']) && $_G['setting']['recommendthread']['status']){
					$post['enable_recommend'] = 1;
					$post['click2login'] = 0;
					if(!$_G['uid']){
						$post['click2login'] = 1;
					}
				}
				$post['addtext'] = $_G['setting']['recommendthread']['addtext'];
				$post['subtext'] = $_G['setting']['recommendthread']['subtracttext'];
				$post['recommend_value'] = $_G['group']['allowrecommend'];
				$post['recommended'] = 0;
				if(C::t('forum_memberrecommend')->fetch_by_recommenduid_tid($_G['uid'], $post['tid'])) {
					$post['recommended'] = 1;
				}
			}else{
				$post['enable_support'] = 0;
				@preg_match('/^x([0-9\.]+)/i', $_G['setting']['version'], $matches);
				$num = 0;
				if(isset($matches[1])){
					$num = $matches[1];
				}
				if($num >= 3.1 && !$_G['forum_thread']['special'] && !$rushreply && !$hiddenreplies && 
						$_G['setting']['repliesrank'] && !$post['first'] && !($post['isWater'] && $_G['setting']['filterednovote'])){
					$post['enable_support'] = 1;
					$post['click2login'] = 0;
					if(!$_G['uid']){
						$post['click2login'] = 1;
					}
				}
				if(function_exists('iconv')){
					$post['supporttext'] = iconv('UTF-8', CHARSET . '//ignore', '支持');
					$post['opposetext'] = iconv('UTF-8', CHARSET . '//ignore', '反对');
				}else{
					$post['supporttext'] = mb_convert_encoding('支持', CHARSET, 'UTF-8');
					$post['opposetext'] = mb_convert_encoding('反对', CHARSET, 'UTF-8');
				}
				$post['support'] = 0;
				$post['oppose'] = 0;
				if(isset($post['postreview']['support'])){
					$post['support'] = $post['postreview']['support'];
				}
				if(isset($post['postreview']['against'])){
					$post['oppose'] = $post['postreview']['against'];
				}
				unset($post['isWater']);
				unset($post['postreview']);
			}
		}
		unset($post);

		foreach($GLOBALS['aimgs'] as $pid => $aids) {
			foreach($aids as $aid) {
				$variable['imagelist'][] = $GLOBALS['postlist'][$pid]['attachments'][$aid]['url'].$GLOBALS['postlist'][$pid]['attachments'][$aid]['attachment'];
			}
		}

		$variable['special_poll'] = BigAppAPI::getPollInfo();
		if(!empty($GLOBALS['rewardprice'])) {
			$variable['special_reward']['rewardprice'] = $GLOBALS['rewardprice'].' '.$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['title'];
			$variable['special_reward']['bestpost'] = $GLOBALS['bestpost'];
		}
		if(!empty($GLOBALS['trades'])) {
			$variable['special_trade'] = $GLOBALS['trades'];
		}
		if(!empty($GLOBALS['debate'])) {
			$variable['special_debate'] = $GLOBALS['debate'];
		}
		if(!empty($GLOBALS['activity'])) {
			$variable['special_activity'] = $GLOBALS['activity'];
			$variable['special_activity']['allapplynum'] = $GLOBALS['allapplynum'];
			if ($_G['setting']['activitycredit'] && $GLOBALS['activity']['credit'] && !$GLOBALS['applied']) {
				$variable['special_activity']['creditcost'] = $GLOBALS['activity']['credit'] . ' ' . $_G['setting']['extcredits'][$_G['setting']['activitycredit']]['title'];
			}
			$setting = array();
			foreach ($GLOBALS['activity']['ufield']['userfield'] as $field) {
				$setting[$field] = $_G['cache']['profilesetting'][$field];
			}
			$variable['special_activity']['joinfield'] = bigapp_core::getvalues($setting, array('/./'), array('fieldid', 'formtype', 'available', 'title', 'formtype', 'choices'));
			$variable['special_activity']['userfield'] = $GLOBALS['ufielddata']['userfield'];
			$variable['special_activity']['extfield'] = $GLOBALS['ufielddata']['extfield'];
			$variable['special_activity']['basefield'] = bigapp_core::getvalues($GLOBALS['applyinfo'], array('message', 'payment'));
			$variable['special_activity']['closed'] = $GLOBALS['activityclose'];
			if ($GLOBALS['applied'] && $GLOBALS['isverified'] < 2) {
				if (!$GLOBALS['isverified']) {
					$variable['special_activity']['status'] = 'wait';
				} else {
					$variable['special_activity']['status'] = 'joined';
				}
				if (!$GLOBALS['activityclose']) {
					$variable['special_activity']['button'] = 'cancel';
				}
			} elseif (!$GLOBALS['activityclose']) {
				if ($GLOBALS['isverified'] != 2) {
					$variable['special_activity']['status'] = 'join';
				} else {
					$variable['special_activity']['status'] = 'complete';
				}
				$variable['special_activity']['button'] = 'join';
			}	
		}

		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		BigAppAPI::modifyPost2($variable['postlist']);
		if(isset($variable['thread']['tid'])){
			$variable['thread']['share_url'] = rtrim(ApiUtils::getDzRoot(), '/') . '/forum.php?mod=viewthread&tid=' . $variable['thread']['tid'];
		}else{
			$variable['thread']['share_url'] = '';
		}
		$variable['jump#pid'] = isset($_G['jump#pid']) ? $_G['jump#pid'] : "0" ;
		$variable['page'] = isset($_G['page']) ? $_G['page'] : "1" ;
		bigapp_core::result(bigapp_core::variable($variable));
	}
		
	function _findimg($string) {
		return preg_replace('/(<img src=\")(.+?)(\".*?\>)/ise', "BigAppAPI::_parseimg('\\1', '\\2', '\\3')", $string);
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
	
	function _jumpThread($postno){
		global $_G;
		$ptid = !empty($_GET['tid']) ? intval($_GET['tid']) : 0;
		if($ptid) {
			$thread = get_thread_by_tid($ptid);
		}
		if($postno) {
			if(getstatus($thread['status'], 3)) {
				$rowarr = C::t('forum_post')->fetch_all_by_tid_position($thread['posttableid'], $ptid, $postno);
				$pid = $rowarr[0]['pid'];
			}
			if($pid) {
				$post = C::t('forum_post')->fetch($thread['posttableid'], $pid);
				if($post['invisible'] != 0) {
					$post = array();
				}
				$pid = 0;
			} else {
				$postno = $postno > 1 ? $postno - 1 : 0;
				$post = C::t('forum_post')->fetch_visiblepost_by_tid($thread['posttableid'], $ptid, $postno);
				$pid = $post['pid'];
			}
		}
		$ordertype = !isset($_GET['ordertype']) && getstatus($thread['status'], 4) ? 1 : $ordertype;
		if($thread['special'] == 2 || C::t('forum_threaddisablepos')->fetch($tid)) {
			$curpostnum = C::t('forum_post')->count_by_tid_dateline($thread['posttableid'], $tid, $post['dateline']);
		} else {
			if($thread['maxposition']) {
				$maxposition = $thread['maxposition'];
			} else {
				$maxposition = C::t('forum_post')->fetch_maxposition_by_tid($thread['posttableid'], $tid);
			}
			$thread['replies'] = $maxposition;
			$curpostnum = $post['position'];
		}
		if($ordertype != 1) {
			$page = ceil($curpostnum / $_G['ppp']);
		} elseif($curpostnum > 1) {
			$page = ceil(($thread['replies'] - $curpostnum + 3) / $_G['ppp']);
		} else {
			$page = 1;
		}
		$_G['jump#pid'] = $ret['pid'] = isset($pid) ? $pid : 0 ;
		$_G['page'] = $ret['page'] = $page;
		return $ret;
	}
	
}

?>
