<?php

class WP_JSON_Users {
	/**
	 * Server object
	 *
	 * @var WP_JSON_ResponseHandler
	 */
	protected $server;
	protected $route = 'users';

	/**
	 * Constructor
	 *
	 * @param WP_JSON_ResponseHandler $server Server object
	 */
	public function __construct( WP_JSON_ResponseHandler $server ) {
		$this->server = $server;
	}

	/**
	 * Register the user-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
		$user_routes = array(
            $this->route => array(
                "get_users" => array( array( $this, 'get_users' ), WP_JSON_Server::READABLE ),
				"get_current_user" => array( array( $this, 'get_current_user' ), WP_JSON_Server::READABLE ),
                "get_user" => array( array( $this, 'get_user' ), WP_JSON_Server::READABLE ),
				"edit_metas" => array( array( $this, 'edit_user_metas'), WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"get_user_metas" => array( array( $this, 'get_user_metas'),  WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"get_posts" => array( array( $this, 'get_user_posts'),  WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
				"getTplList" => array( array( $this, 'getTplList'),  WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
            ),	
		);
		return array_merge( $routes, $user_routes );
	}

	/**
	 * Retrieve users.
	 *
	 * @param array $filter Extra query parameters for {@see WP_User_Query}
	 * @param string $context optional
	 * @param int $page Page number (1-indexed)
	 * @return array contains a collection of User entities.
	 */
	public function get_users( $filter = array(), $context = 'view', $page = 1 ) {
        #return array(); //先关闭
		if ( ! current_user_can( 'list_users' ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("don't allow to list users"));
		}

		$args = array(
			'orderby' => 'user_login',
			'order'   => 'ASC'
		);
		$args = array_merge( $args, $filter );

		$args = apply_filters( 'json_user_query', $args, $filter, $context, $page );

		// Pagination
		$args['number'] = empty( $args['number'] ) ? 10 : absint( $args['number'] );
		$page           = absint( $page );
		$args['offset'] = ( $page - 1 ) * $args['number'];

		$user_query = new WP_User_Query( $args );

		if ( empty( $user_query->results ) ) {
			return array();
		}

		$struct = array();

		foreach ( $user_query->results as $user ) {
			$struct[] = $this->prepare_user( $user, $context );
		}

		return $struct;
	}

	/**
	 * Retrieve the current user
	 *
	 * @param string $context
	 * @return mixed See
	 */
	public function get_current_user( $context = 'view' ) {
		
		//$current_user_id = get_current_user_id(); todo 
		$current_user_id = apply_filters( 'determine_current_user', false );
		
		if ( empty( $current_user_id ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("need login"));
		}

		$response = $this->get_user( $current_user_id, $context );
		

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! ( $response instanceof WP_JSON_ResponseInterface ) ) {
			$response = new WP_JSON_Response( $response );
		}

		return $response;
	}
	
	public function edit_user_metas( $context = 'view' ) {

		$current_user_id = apply_filters( 'determine_current_user', false );
		
		if ( empty( $current_user_id ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("need login"));
		}
		
		$allow_keys = array('fav','avatar','feedback');//收藏，反馈，头像
		
		$meta_key = isset($_POST['meta_key'])?$_POST['meta_key']:null;
		$meta_value = isset($_POST['meta_value'])?$_POST['meta_value']:null;
		$unique = isset($_POST['unique'])?true:false;
		
		if(empty($meta_key) || !in_array($meta_key,$allow_keys)){
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("meta key not allow."));
		}
		
		$metas = get_user_meta($current_user_id, $meta_key);
		$result = false;
		if($metas){
			if($unique){
				$result = update_user_meta( $current_user_id, $meta_key, $meta_value );
			}else{
				$result = update_user_meta( $current_user_id, $meta_key, $meta_value );
			}
			
		}else{
			$result = add_user_meta( $current_user_id, $meta_key, $meta_value );
		}
		return array('result' => $result);
	}
	
	public function get_user_metas( $context = 'view' ) {

		$current_user_id = apply_filters( 'determine_current_user', false );
		
		if ( empty( $current_user_id ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("need login"));
		}
		
		$allow_keys = array('fav','avatar','feedback');
		
		$meta_key = isset($_REQUEST['meta_key'])?$_REQUEST['meta_key']:null;
		
		if(empty($meta_key) || !in_array($meta_key,$allow_keys)){
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"meta key not allow.");
		}
		
		$result = get_user_meta($current_user_id, $meta_key);
		
		return array($meta_key => $result);
	}

	/**
	 * Retrieve a user.
	 *
	 * @param int $id User ID
	 * @param string $context
	 * @return response
	 */
	public function get_user( $id, $context = 'view' ) {
		$id = (int) $id;
		//$current_user_id = get_current_user_id(); todo 
		$current_user_id = apply_filters( 'determine_current_user', false );

		if ( $current_user_id !== $id && ! current_user_can( 'list_users' ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("Sorry, you are not allowed to view this user."));
		}

		$user = get_userdata( $id );

		if ( empty( $user->ID ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("Invalid user ID ."));
		}

		return $this->prepare_user( $user, $context );
	}

	/**
	 *
	 * Prepare a User entity from a WP_User instance.
	 *
	 * @param WP_User $user
	 * @param string $context One of 'view', 'edit', 'embed'
	 * @return array
	 */
	protected function prepare_user( $user, $context = 'view' ) {
		$user_fields = array(
			'ID'          => $user->ID,
			'username'    => $user->user_login,
			'name'        => $user->display_name,
			'first_name'  => $user->first_name,
			'last_name'   => $user->last_name,
			'nickname'    => $user->nickname,
			'slug'        => $user->user_nicename,
			'URL'         => $user->user_url,
			'avatar'      => json_get_avatar_url( $user->user_email ),
			'description' => $user->description,
		);

		$user_fields['registered'] = date( 'c', strtotime( $user->user_registered ) );

		if ( $context === 'view' || $context === 'edit' ) {
			$user_fields['roles']        = $user->roles;
			$user_fields['capabilities'] = $user->allcaps;
			$user_fields['email']        = false;
		}

		if ( $context === 'edit' ) {
			// The user's specific caps should only be needed if you're editing
			// the user, as allcaps should handle most uses
			$user_fields['email']              = $user->user_email;
			$user_fields['extra_capabilities'] = $user->caps;
		}

		$user_fields['meta'] = array(
			'links' => array(
				'self' => get_json_url_users_get_user( $user->ID ),
				'archives' => get_json_url_users_get_posts( $user->ID ),
			),
		);

		return apply_filters( 'json_prepare_user', $user_fields, $user, $context );
	}

	/**
	 * Add author data to post data
	 *
	 * @param array $data Post data
	 * @param array $post Internal post data
	 * @param string $context Post context
	 * @return array Filtered data
	 */
	public function add_post_author_data( $data, $post, $context ) {
		$author = get_userdata( $post['post_author'] );

		if ( ! empty( $author ) ) {
			$data['author'] = $this->prepare_user( $author, 'embed' );
		}

		return $data;
	}

	/**
	 * Add author data to comment data
	 *
	 * @param array $data Comment data
	 * @param array $comment Internal comment data
	 * @param string $context Data context
	 * @return array Filtered data
	 */
	public function add_comment_author_data( $data, $comment, $context ) {
		if ( (int) $comment->user_id !== 0 ) {
			$author = get_userdata( $comment->user_id );

			if ( ! empty( $author ) ) {
				$data['author'] = $this->prepare_user( $author, 'embed' );
			}
		}

		return $data;
	}

	protected function insert_user( $data ) {
		$user = new stdClass;
		
		if ( ! empty( $data['ID'] ) ) {
			$existing = get_userdata( $data['ID'] );

			if ( ! $existing ) {
                json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("Invalid user ID"));
			}

			if ( ! current_user_can( 'edit_user', $data['ID'] ) ) {
                json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("Sorry, you are not allowed to edit users."));
			}

			$user->ID = $existing->ID;
			$update = true;
		} else {
			if ( ! current_user_can( 'create_users' ) ) {
                json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("Sorry, you are not allowed to create users."));
			}

			$required = array( 'username', 'password', 'email' );

			foreach ( $required as $arg ) {
				if ( empty( $data[ $arg ] ) ) {
                    json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("Missing parameter $arg"));
				}
			}

			$update = false;
		}

		// Basic authentication details
		if ( isset( $data['username'] ) ) {
			$user->user_login = $data['username'];
		}

		if ( isset( $data['password'] ) ) {
			$user->user_pass = $data['password'];
		}

		// Names
		if ( isset( $data['name'] ) ) {
			$user->display_name = $data['name'];
		}

		if ( isset( $data['first_name'] ) ) {
			$user->first_name = $data['first_name'];
		}

		if ( isset( $data['last_name'] ) ) {
			$user->last_name = $data['last_name'];
		}

		if ( isset( $data['nickname'] ) ) {
			$user->nickname = $data['nickname'];
		}

		if ( ! empty( $data['slug'] ) ) {
			$user->user_nicename = $data['slug'];
		}

		// URL
		if ( ! empty( $data['URL'] ) ) {
			$escaped = esc_url_raw( $user->user_url );

			if ( $escaped !== $user->user_url ) {
                json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],__lan("Invalid user URL."));
			}

			$user->user_url = $data['URL'];
		}

		// Description
		if ( ! empty( $data['description'] ) ) {
			$user->description = $data['description'];
		}

		// Email
		if ( ! empty( $data['email'] ) ) {
			$user->user_email = $data['email'];
		}
		

		// Role
		if ( ! empty( $data['role'] ) ) {
			$user->role = $data['role'];
		}

		// Pre-flight check
		$user = apply_filters( 'json_pre_insert_user', $user, $data );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$user_id = $update ? wp_update_user( $user ) : wp_insert_user( $user );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$user->ID = $user_id;

		do_action( 'json_insert_user', $user, $data, $update );

		return $user_id;
	}

	/**
	 * Edit a user.
	 *
	 * The $data parameter only needs to contain fields that should be changed.
	 * All other fields will retain their existing values.
	 *
	 * @param int $id User ID to edit
	 * @param array $data Data construct
	 * @param array $_headers Header data
	 * @return true on success
	 */
	public function edit_user( $id, $data, $_headers = array() ) {
		$id = absint( $id );
        
		if ( empty( $id ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"User ID must be supplied.");
		}

		// Permissions check
		if ( ! current_user_can( 'edit_user', $id ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Sorry, you are not allowed to edit this user.");
		}

		$user = get_userdata( $id );
		
		if ( ! $user ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"User ID is invalid.");
		}

		$data['ID'] = $user->ID;
		
		// Update attributes of the user from $data
		$retval = $this->insert_user( $data );
		
		if ( is_wp_error( $retval ) ) {
			return $retval;
		}

		return $this->get_user( $id );
	}

	/**
	 * Create a new user.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function create_user( $data ) {
        $status = true;
		if ( ! current_user_can( 'create_users' ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Sorry, you are not allowed to create users..");
		}

		if ( ! empty( $data['ID'] ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Cannot create existing user..");
		}

		$user_id = $this->insert_user( $data );

		if ( is_wp_error( $user_id ) ) {
            $status = false;
		}

		$response = $this->get_user( $user_id );

		if ( ! $response instanceof WP_JSON_ResponseInterface ) {
			$response = new WP_JSON_Response( $response );
		}

		$response->set( $status );

		return $response;
	}

	/**
	 * Create a new user.
	 *
	 * @deprecated
	 *
	 * @param $data
	 * @return mixed
	 */
	public function new_user( $data ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'WPAPI-1.2', 'WP_JSON_Users::create_user' );

		return $this->create_user( $data );
	}

	/**
	 * Delete a user.
	 *
	 * @param int $id
	 * @param bool force
	 * @return true on success
	 */
	public function delete_user( $id, $force = false, $reassign = null ) {
		$id = absint( $id );

		if ( empty( $id ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Invalid user ID.");
		}

		// Permissions check
		if ( ! current_user_can( 'delete_user', $id ) ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Sory ,you are not allowed to delete this user.");
		}

		$user = get_userdata( $id );

		if ( ! $user ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Invalid user ID.");
		}

		if ( ! empty( $reassign ) ) {
			$reassign = absint( $reassign );

			// Check that reassign is valid
			if ( empty( $reassign ) || $reassign === $id || ! get_userdata( $reassign ) ) {
                json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"Invalid user ID.");
			}
		} else {
			$reassign = null;
		}

		$result = wp_delete_user( $id, $reassign );

		if ( ! $result ) {
            json_error(BigAppErr::$user['code'],BigAppErr::$user['msg'],"The user cannot be deleted..");
		} else {
			return array( 'message' => __( 'Deleted user' ) );
		}
	}
    function get_user_posts($user_id){

    }
    /**
     * 判断是否安装了open_social 插件
     * 本插件依赖这个插件,实现第三方登录
     * tpl:third party login
     * return true/false
     */
    function checkTpl(){
        $flag = is_plugin_active('open-social/open-social.php');    
        if($flag == false){
            if(function_exists('open_social_activation')){
                $flag = true;
            }
        }
        return $flag;
    }
    /**
     * 获取支持哪些第三方登录
     * 如果一个都不支持,返回的就是空数组
     * return array()
     */
    function getTplList(){
        $list = array();
        if(!$this->checkTpl()){
            return $list;
        }
        $osop = get_option('osop');
        if(isset($osop['QQ']) && $osop['QQ'] == 1){       //启用
            $list[] = array('type'=>"qq",'akey'=>$osop['QQ_AKEY'],'skey'=>$osop['QQ_SKEY']);
        }
        if(isset($osop['SINA']) && $osop['SINA'] == 1){       //启用
            $list[] = array('type'=>"sina",'akey'=>$osop['SINA_AKEY'],'skey'=>$osop['SINA_SKEY']);
        }
        if(isset($osop['WECHAT']) && $osop['WECHAT'] == 1){       //启用
            $list[] = array('type'=>"wechat",'akey'=>$osop['WECHAT_AKEY'],'skey'=>$osop['WECHAT_SKEY']);
        }
        return $list;
    }
}
