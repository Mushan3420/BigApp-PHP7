<?php

/***********************************************************
 * @file:   localdns.php
 * @author: mawentao(mawt@youzu.com)
 * @create: 2015-10-15 09:10:31
 * @modify: 2015-10-15 09:10:31
 * @brief:  本地DNS解析服务，
 *          解决部分地区无法解析youzu.com域名问题
 ***********************************************************/
class LocalDNS
{
    private static $_dns = array (
        "app.youzu.com" => array (
            "domain" => "app.youzu.com",  //!< 影响Header中的Host
            "iplist" => array (
				"115.231.89.12",
				"115.231.89.14",
				"61.164.159.11",
				"61.164.159.12",
				"122.226.50.136",
				"122.226.50.137",
            )
        ),
        "test93.youzu.com" => array (
            "domain" => "",
            "iplist" => array (
			    "192.168.180.93:8080"
            )
        ),
    );

    public static function getDomainUrls($url)
    {
        $domain = "";
        $urlarr  = array();
		$tempu=parse_url($url);
		$domain = $tempu['host'];
		if ( isset(self::$_dns[$domain]) ) {
            foreach (self::$_dns[$domain]["iplist"] as $ip) {
                $urlarr[] = str_replace($domain,$ip,$url);
            }
            if (isset(self::$_dns[$domain]["domain"])) {
                $domain = self::$_dns[$domain]["domain"];
            }
        }
        return array (
            "domain" => $domain,
            "urlarr" => $urlarr,
        );
    }
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
