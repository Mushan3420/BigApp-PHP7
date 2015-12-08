<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

$_GET['mod'] = "misc";
$_GET['action'] = "rate";
$_GET['inajax'] = 1;
$_GET['infloat'] = 'yes';
$_GET['ajaxtarget'] = 'fwin_content_viewratings';
include_once 'forum.php';

class BigAppAPI 
{
    public function common()
    {
    }

    public function output()
    {
        global $_G;

        //echo  json_encode($_G['group']); die(0);
        //echo  json_encode($maxratetoday); die(0);
        // 评分项列表
        $raterange = $_G['group']['raterange'];
        $maxratetoday = getratingleft($raterange);
        $creditmap = $_G['setting']['extcredits'];
        $ratelist = array();
        $isself = 0;
        foreach ($raterange as $extcredits => &$im) {
            if (!isset($creditmap[$extcredits])) continue;
            $im["extcredits"] = $extcredits;
            $im["title"] = $creditmap[$extcredits]["title"];
            $im["todayleft"] = $maxratetoday[$extcredits];
            $ratelist[] = $im;
            if ($im['isself']==1) $isself=1;
        }

        // 评分理由列表
        $reasons = explode("\n",$_G['setting']["userreasons"]);
        foreach ($reasons as $k => &$v) {
            $v = trim($v);
            if ($v=="") unset($reasons[$k]);
        }
        
        $variable = array (
            "status"   => isset($_G['messageparam'][0]) ? 0 : 1,
            "isself"   => $isself,
            "ratelist" => $ratelist,
            "reasons"  => $reasons,
        );
        bigapp_core::result(bigapp_core::variable($variable));
        die(0);
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
