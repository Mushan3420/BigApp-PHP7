<?php
/**
* @file cmpage.class.php
* @Brief enable clickable for phone icon in thread list web page
* @author youzu
* @version 1
* @date 2015-04-16
*/
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(__FILE__).'/addslashes.php';
require_once dirname(__FILE__) . '/conf/conf.inc.php';

class plugin_bigapp {
    public function common() {
        !defined('MOBILE_API_OUTPUT') && define('MOBILE_API_OUTPUT', 1);
    }

    public function getPushEnableStatus()
    {
        global $_G;
        if(isset($_G['setting']['bigapp_push_config'])  && is_string($_G['setting']['bigapp_push_config'])){
            $_G['setting']['bigapp_push_config'] = unserialize($_G['setting']['bigapp_push_config']);
        }
        if(!isset($_G['setting']['bigapp_push_config']['push_enabled'])){
            $_G['setting']['bigapp_push_config']['push_enabled'] = 1;
        }
        return $_G['setting']['bigapp_push_config']['push_enabled'];    
    }

}
/*class plugin_bigapp_forum extends plugin_bigapp
{
}*/
class plugin_bigapp_home extends plugin_bigapp
{
    public function spacecp_pm_message($param)
    {
        $enablePush = $this->getPushEnableStatus();
        if('do_success' !== $param['param'][0] || !$enablePush){
            runlog('bigapp', 'push condition invalid or push is disabled [ param: ' . json_encode($param) . ', enable pushed: ' . intval($enablePush)  . ' ]');
            return ;
        }
        $arrNotice = $this->_getNotice();
        if(!empty($arrNotice)){
            require_once dirname(__FILE__) . '/libs/pushmsg.inc.php';
            foreach ($arrNotice as $notice){
                $ret = PushMsg::sendMessage($notice['touid'], $notice['title'], $notice['content'], 1, $notice['extra'], 1);    
                $result = 'fail';
                if(true === $ret){
                    $result = 'succ';
                }
                runlog('bigapp', '[pcweb]try to send message notice [ uid: ' . $notice['extra']['uid'] . ', touid: ' . 
                        $notice['touid'] . ', user: ' . $notice['extra']['user'] . ', touser: ' . $notice['extra']['touser'] . ", result: $result ].");
            }
        }
    }

    public function spacecp_friend_message($param)
    {
        global $_G;
        $enablePush = $this->getPushEnableStatus();
        if('request_has_been_sent' !== $param['param'][0] || !$enablePush){
            runlog('bigapp', 'push condition invalid or push is disabled [ param: ' . json_encode($param) . ', enable pushed: ' . intval($enablePush)  . ' ]');
            return ;
        }
        $sql = 'SELECT uid, username FROM ' . DB::table('common_member') . ' WHERE uid = ' . $_G['uid'] . ' OR uid = ' . $_REQUEST['uid'];
        $query = DB::query($sql);
        $uid = null;
        $user = null;
        $touid = null;
        $touser = null;
        while($tmp = DB::fetch($query)){
            if($_G['uid'] == $tmp['uid']){
                $uid = $_G['uid'];
                $user = $tmp['username'];
                continue;
            }
            $touid = $tmp['uid'];
            $touser = $tmp['username'];
        }
        if(!is_null($uid) && !is_null($user) && !is_null($touid) && !is_null($touser)){
            if(function_exists('iconv')){
                   $user = iconv(CHARSET, 'UTF-8//ignore', $user);
                   $touser = iconv(CHARSET, 'UTF-8//ignore', $touser);
            }else{
                $user = mb_convert_encoding($user, 'UTF-8', CHARSET);
                $touser = mb_convert_encoding($touser, 'UTF-8', CHARSET);
            }    
            $title = '您收到好友请求';
            $content = "用户 ${user} 请求添加您为好友，详情点击查看";
            $extra = array('user' => '__DONT_DICONV_TO_UTF8___' . $user, 'touser' => '__DONT_DICONV_TO_UTF8___' . $touser, 'uid' => $uid, 'touid' => $touid);
            require_once dirname(__FILE__) . '/libs/pushmsg.inc.php';
            $ret = PushMsg::sendMessage($touid, $title, $content, 3, $extra, 1);
            $result = 'fail';
            if(true === $ret){
                $result = 'succ';
            }
            runlog('bigapp', "[pcweb]try to send friend request message [ uid: $uid, touid: $touid, user: $user, touser: $touser, result: $result ].");
        }
    }

    protected function _getNotice()
    {
        global $_G;
        $uid = null;
        $user = null;
        $tousers = array();
        $message = $_POST['message'];
        $users = array();
        if(isset($_POST['selUsers']) && !empty($_POST['selUsers'])){
            $users = (array)$_POST['selUsers'];
        }
        if(isset($_POST['username']) && !empty($_POST['username'])){
            $users = array_merge($users, (array)$_POST['username']);    
        }
        if(isset($_POST['users']) && !empty($_POST['users'])){
            $users = array_merge($users, (array)$_POST['users']);
        }
        if(!empty($users)){
            foreach ($users as &$user){
                $user = "'" . mysql_real_escape_string($user) . "'";
            }
            unset($user);
            $strUsers = implode(', ', $users);
            $sql = 'SELECT uid, username FROM ' . DB::table('common_member') . ' WHERE uid = ' . $_G['uid'] . ' OR username IN (' . $strUsers . ')';
            $query = DB::query($sql);
            while($tmp = DB::fetch($query)){
                if($_G['uid'] == $tmp['uid']){
                    $uid = $_G['uid'];
                    $user = $tmp['username'];
                    continue;
                }
                $tousers[] = array('touid' => $tmp['uid'], 'touser' => $tmp['username']);    
            }
        }else if(isset($_POST['topmuid']) || isset($_GET['touid'])){
            $uid = isset($_POST['topmuid']) ? $_POST['topmuid'] : $_GET['touid'];
            $sql = 'SELECT uid, username FROM ' . DB::table('common_member') . ' WHERE uid = ' . $_G['uid'] . ' OR uid = ' . $uid;
            $query = DB::query($sql);
            while($tmp = DB::fetch($query)){
                if($_G['uid'] == $tmp['uid']){
                    $uid = $_G['uid'];
                    $user = $tmp['username'];
                    continue;
                }
                $tousers[] = array('touid' => $tmp['uid'], 'touser' => $tmp['username']);
            }
        }
        
        if(function_exists('iconv')){
            $user = iconv(CHARSET, 'UTF-8//ignore', $user);
            $message = iconv(CHARSET, 'UTF-8//ignore', $message);
        }else{ 
            $user = mb_convert_encoding($user, 'UTF-8', CHARSET);
            $message = mb_convert_encoding($message, 'UTF-8', CHARSET);
        } 
        foreach ($tousers as &$touser){
            if(function_exists('iconv')){
                $touser['touser'] = iconv(CHARSET, 'UTF-8//ignore', $touser['touser']);
            }else{
                $touser['touser'] = mb_convert_encoding($touser['touser'], 'UTF-8', CHARSET);
            }
        }
        unset($touser);
        $ret = array();
        if(!is_null($uid) && !is_null($user) && !empty($tousers) && !empty($message)){
            $tmp['uid'] = $uid;
            $tmp['user'] = '__DONT_DICONV_TO_UTF8___' . $user;
            $tmp['message'] = '__DONT_DICONV_TO_UTF8___' . $message;
            foreach ($tousers as $touser){
                if($touser['touid'] != $tmp['uid']){
                    $tmp['touid'] = $touser['touid'];
                    $tmp['touser'] = '__DONT_DICONV_TO_UTF8___' . $touser['touser'];
                    $title = '您有新的消息，请注意查收';
                    $content = "用户 ${user} 向您发来新消息，详情点击查看";
                    $extra = $tmp;
                    $ret[] = array('touid' => $tmp['touid'], 'title' => $title, 'content' => $content, 'extra' => $extra);
                }
            }
        }
        return $ret;
    }
}

class plugin_bigapp_forum extends plugin_bigapp
{
    public function post_message($param)
    {
        global $_G;
        $enablePush = $this->getPushEnableStatus();
        if('post_reply_succeed' !== $param['param'][0] || !$enablePush){
            runlog('bigapp', 'push condition invalid or push is disabled [ param: ' . json_encode($param) . ', enable pushed: ' . intval($enablePush)  . ' ]');
            return ;
        }
        if(isset($param['param'][2]['fid']) && isset($param['param'][2]['pid']) && isset($param['param'][2]['tid'])){
            $fid = $param['param'][2]['fid'];
            $tid = $param['param'][2]['tid'];
            $pid = $param['param'][2]['pid'];
            $reppid = null;
            if(isset($_REQUEST['reppid']) && intval($_REQUEST['reppid']) > 0){
                $reppid = intval($_REQUEST['reppid']);
            }
            $uid = null;
            $touid = null;
            $user = null;
            $touser = null;
            $subject = null;
            if(is_null($reppid)){
                $sql = 'SELECT first, author, authorid, subject FROM ' . DB::table('forum_post') . ' where tid = ' . $tid . ' AND first = 1 OR pid = ' . $pid;
            }else{
                $sql = 'SELECT pid, author, authorid, subject FROM ' . DB::table('forum_post') . ' where pid = ' . $pid . ' OR pid = ' . $reppid;
            }
            $query = DB::query($sql);
            while($tmp = DB::fetch($query)){
                if(isset($tmp['first']) && $tmp['first'] == 1 || !is_null($reppid) && $reppid == $tmp['pid']){
                    $touid = $tmp['authorid'];
                    $touser = $tmp['author'];
                    $subject = $tmp['subject'];
                    continue;
                }
                $uid = $tmp['authorid'];
                $user = $tmp['author'];
            }
            if(!is_null($uid) && !is_null($user) && !is_null($touser) && !is_null($touid) && !is_null($subject) && $touid != $uid){
                if(function_exists('iconv')){
                    $user = iconv(CHARSET, 'UTF-8//ignore', $user);        
                    $touser = iconv(CHARSET, 'UTF-8//ignore', $touser);        
                    $subject = iconv(CHARSET, 'UTF-8//ignore', $subject);        
                }else{
                    $user = mb_convert_encoding($user, 'UTF-8', CHARSET);    
                    $touser = mb_convert_encoding($touser, 'UTF-8', CHARSET);    
                    $subject = mb_convert_encoding($subject, 'UTF-8', CHARSET);    
                }
                $title = '您有新的回帖';
                if(is_null($reppid)){
                    $content = "用户 ${user} 回复了您的主题 ${subject}，详情点击查看";
                }else{
                    $content = "用户 ${user} 回复了您的回帖，详情点击查看";
                }
                $extra = array('subject' => '__DONT_DICONV_TO_UTF8___' . $subject, 'author' => $uid, 'pid' => $pid, 'fid' => $fid, 'tid' => $tid, 'authorid' => $uid);    
                require_once dirname(__FILE__) . '/libs/pushmsg.inc.php';
                $ret = PushMsg::sendMessage($touid, $title, $content, 2, $extra, 1);
                $result = 'fail';
                if(true === $ret){
                    $result = 'succ';
                }
                runlog('bigapp', "[pcweb]try to send reply message [ subject: $subject, author: $user, ownerid: $touid, result: $result ].");    
            }
        }
    }
/*
    public function forumdisplay_thread_subject_output()
    {
        global $_G;
        $ret = array();
        if(isset($_G['forum_threadcount'])){
            $count = $_G['forum_threadcount'];
            for($i = 0; $i < $count; $i++){
                $mobile = '';
                if(isset($_G['forum_threadlist'][$i]['mobile']) && $_G['forum_threadlist'][$i]['mobile'] &&
                    isset($_G['cache']['plugin']['bigapp']['app_download_url'])){
                    $tmp = <<<MULTI
                        <script type="text/javascript">
                            var items = document.getElementById("zuozhe_shi_xieronglin_%s").parentNode.getElementsByTagName("img");
                            var regEx = /mobile/g;
                            for(var i = 0; i < items.length; i++){
                                if(regEx.test(items[i].getAttributeNode("src").nodeValue)){
                                    var attr = items[i].attributes;
                                    var par = items[i].parentNode;
                                    par.removeChild(items[i]);
                                    var img=document.createElement("img");
                                    for(var j = 0; j < attr.length; j++){
                                        img.setAttribute(attr.item(j).nodeName, attr.item(j).nodeValue);
                                    }
                                    var a = document.createElement("a");
                                    a.setAttribute("href", "%s");
                                    a.setAttribute("title", "%s");
                                    a.setAttribute("target", "_blank");
                                    a.appendChild(img);
                                    par.appendChild(a);
                                    attr = par = img = a = null;
                                }
                            }
                            items = regEx = null;
                        </script>    
MULTI;
                    $js = sprintf($tmp, $i, $_G['cache']['plugin']['bigapp']['app_download_url'], lang('plugin/bigapp', 'download_tip'));  //phone icon is clickable
                    $html = '<input type="hidden" id="zuozhe_shi_xieronglin_' . $i . '"></input>'; //for js location, nothing else
                    $ret[] = $html . $js;
                }else{
                    $ret[] = '';
                }
            }    
        }
        return $ret;
    }
*/

    ///////////////////////////////////////////////
    // 发帖小尾巴
    public function viewthread_postbottom_output()
    {   
        global $_G, $postlist;
        //1. 判断是否可以显示二维码
        $isqrcode = false;
        require_once libfile('function/core');
        require_once libfile('function/cache');
        //updatecache('setting');
        if ($_G['setting']["bigapp_mobile_setting"]) {
            $params = json_decode($_G['setting']["bigapp_mobile_setting"], true);
            if (isset($params["downurl"]) && $params["downurl"]!="") {
                $isqrcode = true;
            }   
        }   
        //2. 加上小尾巴
        foreach($postlist as $k => &$post) {
            if($post['mobiletype'] == 7) {
                $tail = lang('plugin/bigapp', 'thread_tail_message');
                if ($isqrcode) $tail = lang('plugin/bigapp', 'thread_tail_message_qrcode');
                $post["message"] .= $tail;
            }
            //$post["message"] .= "[mobiletype:".$post['mobiletype']."]";
        }   
        return array();
    }   
}


class plugin_bigapp_misc extends plugin_bigapp
{
    public function mobile() {
        global $_G;
        if(isset($_G['setting']['bigapp_pcset'])){ 
            $pcset = unserialize($_G['setting']['bigapp_pcset']);
            if ($pcset["moburl_switch"] == 1) {
                $url = rtrim($_G["siteurl"],"/")."/plugin.php?id=bigapp:mobile";
				header("Location: $url");
                return;
            } 
            else if ($pcset["moburl_switch"] == 2) {
                $url = $pcset["moburl"];
                if ($url != "") {
					header("Location: $url");
					return;
                }
            }
        }
		//header('Location: http://www.baidu.com/');
    }
}

?>
