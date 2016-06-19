<?php
/**
 * @file mypm.php
 * @Brief
 * @author youzu
 * @version 1
 * @date 2015-04-09
 */

if (!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}


class BigAppAPI
{

    function common()
    {
    }

    function output()
    {
        $search = array ("'<script[^>]*?>.*?</script>'si",
                "'<img.*src=\"(.*)\" .*>'iU",
                "'<a.*href=\"(.*)\".*target=\"_blank\">(.*)<\/a>'iU",
                "'<[\/\!]*?[^<>]*?>'si",
                "'&(quot|#34);'i",
                "'&(amp|#38);'i",
                "'&(lt|#60);'i",
                "'&(gt|#62);'i",
                "'&(nbsp|#160);'i",
                "'&(iexcl|#161);'i",
                "'&(cent|#162);'i",
                "'&(pound|#163);'i",
                "'&(copy|#169);'i",
                "'\\\\r\\\\n'i",
                );

        $replace = array ("",
                "[img]\\1[/img]",
                "[url]\\1[/url]",
                "",
                "\"",
                "&",
                "<",
                ">",
                " ",
                chr(161),
                chr(162),
                chr(163),
                chr(169),
                "\r\n",
                );
            
        foreach ($GLOBALS['list'] as &$value) {
            if (isset($value['message'])) {
                $message = preg_replace($search, $replace, $value['message']);
                $message = preg_replace_callback(
                    "/&#(\d+);/i",
                    function ($m) {
                        return chr($m[1]);
                    },
                    $message
                );
                if (function_exists('iconv')) {
                    $message = iconv(CHARSET, 'UTF-8//ignore', $message);
                } else {
                    $message = mb_convert_encoding($message, 'UTF-8', CHARSET);
                }
                $message = $ret = preg_replace_callback('/\[img](.*)\[\/img]/U', 'BigAppAPI::callback', $message);
               
                $value['message'] = '__DONT_DICONV_TO_UTF8___' . $message;
            }
            if (isset($value['msgtoid'])) {
                $value['msgtoid_avatar'] = avatar($value['msgtoid'], 'big', 'true');
                $value['msgtoid_avatar'] = str_replace("\r", '', $value['msgtoid_avatar']);
                $value['msgtoid_avatar'] = str_replace("\n", '', $value['msgtoid_avatar']);
            }
            if (isset($value['msgfromid'])) {
                $value['msgfromid_avatar'] = avatar($value['msgfromid'], 'big', 'true');
                $value['msgfromid_avatar'] = str_replace("\r", '', $value['msgfromid_avatar']);
                $value['msgfromid_avatar'] = str_replace("\n", '', $value['msgfromid_avatar']);
            }
        }
        unset($value);

        $variable = array(
            'list' => bigapp_core::getvalues($GLOBALS['list'], array('/^\d+$/'), array('plid', 'isnew', 'pmnum', 'lastupdate',
                    'lastdateline', 'authorid', 'author', 'pmtype', 'subject', 'members', 'dateline', 'touid', 'pmid', 'lastauthorid',
                    'lastauthor', 'lastsummary', 'msgfromid', 'msgfrom', 'message', 'msgtoid', 'msgtoid_avatar', 'msgfromid_avatar', 'tousername')),
            'count' => $GLOBALS['count'],
            'perpage' => $GLOBALS['perpage'],
            'page' => intval($GLOBALS['page']),
        );
         
        $start = $variable['perpage'] * ($variable['page'] - 1);
        $end = count($variable['list']) + $start;
        if ($end >= $variable['count']) {
            $variable['need_more'] = 0;
        } else {
            $variable['need_more'] = 1;
        }
        if ($_GET['subop']) {
            $variable = array_merge($variable, array('pmid' => $GLOBALS['pmid']));
        }
        $variable['list'] = array_values($variable['list']);
        bigapp_core::result(bigapp_core::variable($variable));
    }

    static function callback($matches)
    {
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
        );
        
        if (!isset($matches[1])) {
            return $matches[0];
        }
        
        $baseName = basename($matches[1]);
                //baseName:smile.gif
                $dir = "";
                $url_info = parse_url($matches[1]);
        if (isset($url_info['path'])) {
            $array = explode('/', $url_info['path']);
                  
            if (sizeof($array) - 2 >= 0) {
                $dir = $array[sizeof($array) - 2];
            } else {
                $dir = "";
            }
        } else {
            $dir = "";
        }

        $out = preg_match('/static\/image\/smiley/', $matches[1], $_matches);
        
        if (1 === $out && 1 === count($_matches)) {
            $message = "{".$dir."/".$baseName."}";
        } else {
            $message = "[图片]";
        }
        
        if (function_exists('iconv')) {
            $message = iconv(CHARSET, 'UTF-8//ignore', $message);
        } else {
            $message = mb_convert_encoding($message, 'UTF-8', CHARSET);
        }
        
        //$message = '__DONT_DICONV_TO_UTF8___' . $message;
        
        return $message;
    }
}
