<?php
if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

$_GET['mod'] = "misc";
$_GET['action'] = "viewratings";
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
        $creditmap = $_G['setting']['extcredits'];
        $variable = array (
            "list" => $GLOBALS["loglist"],
        );
        foreach ($variable["list"] as $k => &$im) {
            //$im["credit"]
            $extcredits = $im["extcredits"];
            if (!isset($creditmap[$extcredits])) {
                unset($variable["list"][$k]);
            } else {
                $im["credit"] = $creditmap[$extcredits]["title"];
                unset($im["pid"]);
                unset($im["extcredits"]);
                unset($im["dateline"]);
            }
        }
        bigapp_core::result(bigapp_core::variable($variable));
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
