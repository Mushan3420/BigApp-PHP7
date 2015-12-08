<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file bigapp_core.class.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/07 20:46:33
 *  
 **/
class bigapp_core{

    //logout
    public static function yz_auth_logout(){
        wp_logout();
        $result = array("error_code"=>0,"error_msg"=>"success","data"=>array());
        self::set_response($result);
    }

    //register
    public static function yz_auth_register(){
        if (!isset($_SESSION)) {
            session_start();
            session_regenerate_id(TRUE);
        }

        $result = array("error_code"=>0,"error_msg"=>"success","data"=>array());

        if ( !get_option('users_can_register') ) {
            $result['error_code'] = -2;
            $result['error_msg']  =  "users can not register";	
        }
        $user_login = isset($_POST['user_login'])?$_POST['user_login']:null;
        //$user_email = isset($_POST['user_email'])?$_POST['user_email']:null;
		$user_email = '';
        $password = isset($_POST['password'])?$_POST['password']:null;
        $captcha = isset($_POST['captcha'])?$_POST['captcha']:null;

        if( empty($user_login) || empty($password) ){
            $result['error_code'] = -1;
            $result['error_msg']  =  "user_login or password or can not be null";
            //$result['data']  =  $_POST;			
        }
		
		if(!validate_username($user_login)){
			$result['error_code'] = -3;
            $result['error_msg']  =  "invalid username";
		}
        /*
        if (empty($_SESSION['captcha']) || strtolower(trim($captcha)) != $_SESSION['captcha']) {
            $result['error_code'] = -1;
            $result['error_msg']  =  "captcha invalid ";
            $result['data']  =  $_POST;	
            $result['data']['sess_captcha']  =  $_SESSION['captcha'];	
        }
		*/

        if( 0 === $result['error_code'] ){
            $user_id = wp_create_user($user_login,$password,$user_email);

            if ( is_wp_error($user_id) ) {
                $result['error_code'] = $user_id->get_error_code();
                $result['error_msg']  =  implode(' ',$user_id->get_error_messages());	
            }else{
                $result['data']  =  array("uid"=>$user_id);	
            }
        }

        self::set_response($result);
    }

    public static function yz_auth_getcaptcha(){
        include_once( dirname( __FILE__ ) . '/captcha/captcha.php' );
        session_start();
        $captcha = new SimpleCaptcha();
        $captcha->CreateImage();
        die();
    }
    public static function set_response($result=null){
        if(empty($result)){
            $result = array("error_code"=>0,"error_msg"=>"success","data"=>array());
        }

        global $bigapp_common;
        $bigapp_common = Bigapp_Common::getInstance();
        $bigapp_common->setResponse($result);
    }
    /**
     * 获取post的第一个图片，生成缩略图
     * 如果没有上传图片, 返回空字符串
     * 返回图片的json = array('url','width','height')
     */
    public static function getFirstImage($post_id,$size ='thumbnail') {
        $info = array('src'=>'',
                    'width'=>0,
                    'height'=>0);
        $args = array(
            'numberposts' => 1,
            'order'=> 'ASC',
            'post_mime_type' => 'image',
            'post_parent' => $postId,
            'post_status' => null,
            'post_type' => 'attachment'
        );
        $attachments = get_children($args);

        if(!$attachments) {
            return $info;
        }
        // 获取缩略图中的第一个图片, 并组装成 HTML 节点返回
        $image = array_pop($attachments);
        $imageSrc = wp_get_attachment_image_src($image->ID, $size);
        if($imageSrc){
            $info['src']= $imageSrc[0];
            $info['width'] = $imageSrc[1];
            $info['height'] = $imageSrc[2];
        }
        return $info;
    }
    /**
     * 获取文章的缩略图
     */
    public static function get_thumbnail_by_post_id($post_id,$size = 'post-thumbnail',$thumbnail_first=false){
        $image = "";
        global $bigapp_support_thumb;
        if( $bigapp_support_thumb == false || $thumbnail_first == false){
            $image = self::getFirstImage($post_id,$size);
        }else{
            $image = get_the_post_thumbnail($post_id,$size);
        }
        return $image;
    }
    /**
     * 判断评论的状态
     * return :
     *  0:不允许评论
     *  1,允许匿名评论
     *  2,不允许匿名评论
     *  3,必须是注册用户评论
     */
    public static function check_comment_status(){
        global $comment_status;
        if(isset($comment_status)){
            return $comment_status;
        }
        $comment_status = 0;
        $d_status = get_option('default_comment_status');
         if ( "open" == $d_status ){     //open 允许评论,closed,不允许评论
             $comment_status = 1;
         }else{
             return $comment_status;
         }
        $r_status = get_option('require_name_email');
        if ( 1 == $r_status){       //1 必须填写姓名邮箱
            $comment_status = 2;
        }
        $r_status = get_option('comment_registration');
        if ( 1 == $r_status){       //1 必须注册
            $comment_status = 3;
        }
        return $comment_status;
    }

}



/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
