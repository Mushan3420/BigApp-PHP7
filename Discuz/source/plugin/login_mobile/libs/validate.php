<?php
class DzValidate
{
    // 判断是否手机号
    public static function is_phone($str) 
    {
        return preg_match("/^1\d{10}$/i", $str); 
    }
    // 判断是否email
    public static function is_email($str) 
    {
        return preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/i", $str);
    }
    // 判断是否url
    public static function is_url($str) 
    { 
        return preg_match("/^(https?:\/\/)?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/?)|(\/[^\s]+)+\/?)$/i", $str);
    } 
}
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
