<?php

class WP_JSON_Auth {
	/**
	 * Server object
	 *
	 * @var WP_JSON_ResponseHandler
	 */
	protected $server;

    protected $route = 'auth';

	/**
	 * Constructor
	 *
	 * @param WP_JSON_ResponseHandler $server Server object
	 */
	public function __construct( WP_JSON_ResponseHandler $server ) {
		$this->server = $server;
	}


	public function register_routes( $routes ) {
		$user_routes = array(
            $this->route => array( 
                "register" => array( array( $this, 'register' ),      WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
                "login" => array( array( $this, 'login' ),         WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
                "logout" => array( array( $this, 'logout' ),         WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
                "captcha" => array( array( $this, 'captcha' ),       WP_JSON_Server::READABLE ),
            ),
        );
        return array_merge( $routes, $user_routes );
	}

	
	public function register() {
		echo "1111";exit;
	}
	
	public function captcha() {
		$a = array("a"=>"b");
		return $a;
	}
	
	public function login() {
        $result = array("error_code"=>0,"error_msg"=>"success","data"=>array());
        bigapp_core::set_response($result);
	}
	public function logout() {
        $result = array("error_code"=>0,"error_msg"=>"success","data"=>array());
        bigapp_core::set_response($result);
	}

}
