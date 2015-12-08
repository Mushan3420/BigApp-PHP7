<?php

class WP_JSON_Posts {
	/**
	 * Server object
	 *
	 * @var WP_JSON_ResponseHandler
	 */
	protected $server;
	protected $route = 'posts';

	/**
	 * Constructor
	 *
	 * @param WP_JSON_ResponseHandler $server Server object
	 */
	public function __construct(WP_JSON_ResponseHandler $server) {
		$this->server = $server;

		$this->comments = new WP_JSON_Comments($server);
	}

	/**
	 * Register the post-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
        $post_routes = array(
            $this->route => array(
                "get_posts" =>array( array($this,"get_posts"),WP_JSON_Server::READABLE ),
                "get_post" =>array( array($this,"get_post"),WP_JSON_Server::READABLE ),
            ),
        );
		$post_routes = $this->comments->register_routes( $post_routes );

		return array_merge( $routes, $post_routes );
	}

	/**
	 * Get revisions for a specific post.
	 *
	 * @param int $id Post ID
	 * @uses wp_get_post_revisions
	 * @return WP_JSON_Response
	 */
	public function get_revisions( $id ) {
		$id = (int) $id;

		$parent = get_post( $id, ARRAY_A );

		if ( empty( $id ) || empty( $parent['ID'] ) ) {
            json_error(BigAppErr::$post['code'],"Invalid post ID.");
		}

		if ( ! json_check_post_permission( $parent, 'edit' ) ) {
            json_error(BigAppErr::$post['code'],__("Sorry, you cannot view the revisions for this post."));
 		}

		// Todo: Query args filter for wp_get_post_revisions
		$revisions = wp_get_post_revisions( $id );

		$struct = array();
		foreach ( $revisions as $revision ) {
			$post = get_object_vars( $revision );

			$struct[] = $this->prepare_post( $post, 'view-revision' );
		}

		return $struct;
	}

	/**
	 * Retrieve posts.
	 *
	 * @since 3.4.0
	 *
	 * The optional $filter parameter modifies the query used to retrieve posts.
	 * Accepted keys are 'post_type', 'post_status', 'number', 'offset',
	 * 'orderby', 'order','s','cat','post_per_page'.
	 *
	 * @uses wp_get_recent_posts()
	 * @see get_posts() for more on $filter values
	 *
	 * @param array $filter Parameters to pass through to `WP_Query`
	 * @param string $context The context; 'view' (default) or 'edit'.
	 * @param string|array $type Post type slug, or array of slugs
	 * @param int $page Page number (1-indexed)
	 * @return stdClass[] Collection of Post entities
	 */
	public function get_posts( $filter = array(), $context = 'view', $type = 'post', $page = 1 ) {
		$query = array();

		// Validate post types and permissions
		$query['post_type'] = array();

		foreach ( (array) $type as $type_name ) {
			$post_type = get_post_type_object( $type_name );
			
			//如果不允许show_in_json，那么则报错，说明不允许json展示，直接拒绝，否则将这个信息加入
			if ( ! ( (bool) $post_type ) || ! $post_type->show_in_json ) {
                json_error(BigAppErr::$post['code'],"Invalid post type");
			}
			//只有这几个值：post/page/attachment/revision/nav_menu_item
			$query['post_type'][] = $post_type->name;
		}

		global $wp;

		// Allow the same as normal WP
		$valid_vars = apply_filters('query_vars', $wp->public_query_vars);

		// If the user has the correct permissions, also allow use of internal
		// query parameters, which are only undesirable on the frontend
		//
		// To disable anyway, use `add_filter('json_private_query_vars', '__return_empty_array');`

		//private_query_vars: ["offset","posts_per_page","posts_per_archive_page","showposts","nopaging","post_type","post_status","category__in","category__not_in","category__and","tag__in","tag__not_in","tag__and","tag_slug__in","tag_slug__and","tag_id","post_mime_type","perm","comments_per_page","post__in","post__not_in","post_parent","post_parent__in","post_parent__not_in"]
		if ( current_user_can( $post_type->cap->edit_posts ) ) {
			$private = apply_filters( 'json_private_query_vars', $wp->private_query_vars );
			$valid_vars = array_merge( $valid_vars, $private );
		}

		// Define our own in addition to WP's normal vars
		$json_valid = array( 'posts_per_page' );
		$valid_vars = array_merge( $valid_vars, $json_valid );

		// Filter and flip for querying
		$valid_vars = apply_filters( 'json_query_vars', $valid_vars );
		$valid_vars = array_flip( $valid_vars );

		// Exclude the post_type query var to avoid dodging the permission
		// check above
		unset( $valid_vars['post_type'] );
		//["m","p","posts","w","cat","withcomments","withoutcomments","s","search","exact","sentence","calendar","page","paged","more","tb","pb","author","order","orderby","year","monthnum","day","hour","minute","second","name","category_name","tag","feed","author_name","static","pagename","page_id","error","comments_popup","attachment","attachment_id","subpost","subpost_id","preview","robots","taxonomy","term","cpage","post_type","post_format","json_route","posts_per_page"]
		//对所有的query_var做过滤
		foreach ( $valid_vars as $var => $index ) {
			if ( isset( $filter[ $var ] ) ) {
				$query[ $var ] = apply_filters( 'json_query_var-' . $var, $filter[ $var ] );
			}
		}
        //后门为收藏列表留的
        if(isset($filter['_bigapp_post_ids'])){
            if(!is_array($filter['_bigapp_post_ids'])){
                $filter['_bigapp_post_ids'] = explode($filter['_bigapp_post_ids'],',');
            }
            $query['post__in'] = $filter['_bigapp_post_ids'] ;
        }

		// Special parameter handling
		$query['paged'] = absint( $page );

        show_debug($query,__FILE__,__LINE__);
		//重新建立主循环，从DB中找出指定文章
		$post_query = new WP_Query();
		$posts_list = $post_query->query( $query );
        show_debug($posts_list,__FILE__,__LINE__);
		$response   = new WP_JSON_Response();
		//输出页码等信息到头部
		//但期望是能输出到json中
		$response->query_navigation_headers( $post_query );

		if ( ! $posts_list ) {
			$response->set_data( array() );
			return $response;
		}

		// holds all the posts data
		$struct = array();

        $only_one = 0;
        $show_type = 'list';
        if(isset($filter['only_one']) && $filter['only_one'] == 1){ //这地方是为了banner链接是文章的带有搜索条件的情况,设置的标示
            $only_one = 1;
            $show_type = 'row';
        }

		foreach ( $posts_list as $post ) {
			$post = get_object_vars( $post );

			// Do we have permission to read this post?
			if ( ! json_check_post_permission( $post, 'read' ) ) {
				continue;
			}

			$post_data = $this->prepare_post( $post, $context ,$show_type);
			if (  $post_data == false ) {
				continue;
			}

			$struct[] = $post_data;
		}
        if($only_one == 1){
		    $response->set_data( count($struct)?$struct[0]:$struct );
        }else{
		    $response->set_data( $struct );
        }
		return $response;
	}

	/**
	 * Create a new post for any registered post type.
	 *
	 * @since 3.4.0
	 * @internal 'data' is used here rather than 'content', as get_default_post_to_edit uses $_REQUEST['content']
	 *
	 * @param array $content Content data. Can contain:
	 *  - post_type (default: 'post')
	 *  - post_status (default: 'draft')
	 *  - post_title
	 *  - post_author
	 *  - post_excerpt
	 *  - post_content
	 *  - post_date_gmt | post_date
	 *  - post_format
	 *  - post_password
	 *  - comment_status - can be 'open' | 'closed'
	 *  - ping_status - can be 'open' | 'closed'
	 *  - sticky
	 *  - post_thumbnail - ID of a media item to use as the post thumbnail/featured image
	 *  - custom_fields - array, with each element containing 'key' and 'value'
	 *  - terms - array, with taxonomy names as keys and arrays of term IDs as values
	 *  - terms_names - array, with taxonomy names as keys and arrays of term names as values
	 *  - enclosure
	 *  - any other fields supported by wp_insert_post()
	 * @return array Post data (see {@see WP_JSON_Posts::get_post})
	 */
	public function create_post( $data ) {
		unset( $data['ID'] );

		$result = $this->insert_post( $data );
		if ( $result == false) {
            json_error(BigAppErr::$post['code'],"create post faild!");
		}

		$response = json_ensure_response( $this->get_post( $result, 'edit' ) );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Create a new post for any registered post type.
	 *
	 * @deprecated
	 * @internal 'data' is used here rather than 'content', as get_default_post_to_edit uses $_REQUEST['content']
	 *
	 * @param array $content Content data. (see {@see WP_JSON_Posts::create_post})
	 * @return array Post data (see {@see WP_JSON_Posts::get_post})
	 */
	public function new_post( $data ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'WPAPI-1.2', 'WP_JSON_Posts::create_post' );

		return $this->create_post( $data );
	}

	/**
	 * Retrieve a post.
	 *
	 * @uses get_post()
	 * @param int $id Post ID
	 * @param string $context The context; 'view' (default) or 'edit'.
	 * @return array Post entity
	 */
	public function get_post( $id, $context = 'view' ,$is_featured_image=false) {
		$id = (int) $id;

		$post = get_post( $id, ARRAY_A );
        show_debug($post,__FILE__,__LINE__);

		if ( empty( $id ) || empty( $post['ID'] ) ) {
            json_error(BigAppErr::$post['code'],"get post faild!",$id);
		}

		$checked_permission = 'read';
		$checked_post = $post;
		if ( 'inherit' === $post['post_status'] && $post['post_parent'] > 0 ) {
            $_temp_post = get_post( $post['post_parent'], ARRAY_A );  
            show_debug($_temp_post,__FILE__,__LINE__);
            if($post['post_type'] != 'attachment'){    //fix for 特色图关联的文章被删的情况
                $checked_post = $_temp_post;
                if ( 'revision' === $post['post_type'] ) {
                    $checked_permission = 'edit';
                }
            }
		}
		if ( ! json_check_post_permission( $checked_post, $checked_permission ) ) {
            json_error(BigAppErr::$post['code'],BigAppErr::$post['msg'],"cant read this post.",$id);
		}
		$response = new WP_JSON_Response();
		$data = $this->prepare_post( $post, $context );
        if($data == false){
            json_error(BigAppErr::$post['code'],"prepare post faild.",$id);
        }
        $favorite_model = new WP_JSON_Favorite($this->server);
        $data['is_favorited'] = $favorite_model->check_favorited($id);      //true/false    是否收藏标示

        $post_views = new WP_JSON_PostViews($this->server);
        $post_views->process_postviews($id);        //记录浏览量

        if($is_featured_image == true){
		    $response->set_data(array($data));     //如果是特征图,返回数据,为了兼容多图模式
        }else{
		    $response->set_data($data);     //非特征图修改为非数组格式,用于前端缓存及兼容.
        }
		return $response;
	}

	/**
	 * Edit a post for any registered post type.
	 *
	 * The $data parameter only needs to contain fields that should be changed.
	 * All other fields will retain their existing values.
	 *
	 * @since 3.4.0
	 * @internal 'data' is used here rather than 'content', as get_default_post_to_edit uses $_REQUEST['content']
	 *
	 * @param int $id Post ID to edit
	 * @param array $data Data construct, see {@see WP_JSON_Posts::create_post}
	 * @param array $_headers Header data
	 * @return true on success
	 */
	public function edit_post( $id, $data, $_headers = array() ) {
		$id = (int) $id;

		$post = get_post( $id, ARRAY_A );

		if ( empty( $id ) || empty( $post['ID'] ) ) {
            json_error(BigAppErr::$post['code'],"post id is not valid",$id);
		}

		if ( isset( $_headers['IF_UNMODIFIED_SINCE'] ) ) {
			// As mandated by RFC2616, we have to check all of RFC1123, RFC1036
			// and C's asctime() format (and ignore invalid headers)
			$formats = array( DateTime::RFC1123, DateTime::RFC1036, 'D M j H:i:s Y' );

			foreach ( $formats as $format ) {
				$check = WP_JSON_DateTime::createFromFormat( $format, $_headers['IF_UNMODIFIED_SINCE'] );

				if ( $check !== false ) {
					break;
				}
			}

			// If the post has been modified since the date provided, return an error.
			if ( $check && mysql2date( 'U', $post['post_modified_gmt'] ) > $check->format('U') ) {
                json_error(BigAppErr::$post['code'],"There is a revision of this post that is more recent.");
			}
		}

		$data['ID'] = $id;

		$retval = $this->insert_post( $data );
		if ( $retval == false ) {
			return $retval;
		}

		return $this->get_post( $id, 'edit' );
	}

	/**
	 * Delete a post for any registered post type
	 *
	 * @uses wp_delete_post()
	 * @param int $id
	 * @return true on success
	 */
	public function delete_post( $id, $force = false ) {
		$id = (int) $id;

		$post = get_post( $id, ARRAY_A );

		if ( empty( $id ) || empty( $post['ID'] ) ) {
            json_error(BigAppErr::$post['code'],"post id is Invalid",$id);
		}

		if ( ! json_check_post_permission( $post, 'delete' ) ) {
            json_error(BigAppErr::$post['code'],"Sorry, you are not allowed to delete this post.");
		}

		$result = wp_delete_post( $id, $force );

		if ( ! $result ) {
            json_error(BigAppErr::$post['code'],"The post cannot be deleted.");
		}

		if ( $force ) {
			return array( 'message' => __( 'Permanently deleted post' ) );
		} else {
			// TODO: return a HTTP 202 here instead
			return array( 'message' => __lan( 'Deleted post' ),'id'=>$id );
		}
	}

	/**
	 * Get all public post types
	 *
	 * @uses self::get_post_type()
	 * @return array List of post type data
	 */
	public function get_post_types() {
		$data = get_post_types( array(), 'objects' );

		$types = array();

		foreach ($data as $name => $type) {
			$type = $this->get_post_type( $type, true );
			if ( is_wp_error( $type ) ) {
				continue;
			}

			$types[ $name ] = $type;
		}

		return $types;
	}

	/**
	 * Get a post type
	 *
	 * @param string|object $type Type name, or type object (internal use)
	 * @param boolean $context What context are we in?
	 * @return array Post type data
	 */
	public function get_post_type( $type, $context = 'view' ) {
		if ( ! is_object( $type ) ) {
			$type = get_post_type_object( $type );
		}

		if ( $type->show_in_json === false ) {
            json_error(BigAppErr::$post['code'],"Cannot view post type");
		}

		if ( $context === true ) {
			$context = 'embed';
			_deprecated_argument( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', '$context should be set to "embed" rather than true' );
		}

		$data = array(
			'name'         => $type->label,
			'slug'         => $type->name,
			'description'  => $type->description,
			'labels'       => $type->labels,
			'queryable'    => $type->publicly_queryable,
			'searchable'   => ! $type->exclude_from_search,
			'hierarchical' => $type->hierarchical,
			'meta'         => array(
				'links' => array(
					'self'       => get_json_url_post_get_post_type( $type->name ),
					'collection' => get_json_url_post_get_post_type( ),
				),
			),
		);

		// Add taxonomy link
		$relation = 'http://wp-api.org/1.1/collections/taxonomy/';
		$url = get_json_url_taxonomies_get_taxonomies();
		$url = add_query_arg( 'type', $type->name, $url );
		$data['meta']['links'][ $relation ] = $url;

		if ( $type->publicly_queryable ) {
			if ( $type->name === 'post' ) {
				$data['meta']['links']['archives'] = get_json_url_posts_list();
			} else {
				$data['meta']['links']['archives'] = get_json_url_post_get_post_type($type->name);
			}
		}

		return apply_filters( 'json_post_type_data', $data, $type, $context );
	}

	/**
	 * Get the registered post statuses
	 *
	 * @return array List of post status data
	 */
	public function get_post_statuses() {
		$statuses = get_post_stati(array(), 'objects');

		$data = array();

		foreach ($statuses as $status) {
			if ( $status->internal === true || ! $status->show_in_admin_status_list ) {
				continue;
			}

			$data[ $status->name ] = array(
				'name'         => $status->label,
				'slug'         => $status->name,
				'public'       => $status->public,
				'protected'    => $status->protected,
				'private'      => $status->private,
				'queryable'    => $status->publicly_queryable,
				'show_in_list' => $status->show_in_admin_all_list,
				'meta'         => array(
					'links' => array()
				),
			);
			if ( $status->publicly_queryable ) {
				if ( $status->name === 'publish' ) {
					$data[ $status->name ]['meta']['links']['archives'] = get_json_url_posts_list();
				} else {
					$data[ $status->name ]['meta']['links']['archives'] = get_json_url_post_get_post_statuses( $status->name );
				}
			}
		}

		return apply_filters( 'json_post_statuses', $data, $statuses );
	}

	/**
	 * Prepares post data for return in an XML-RPC object.
	 *
	 * @access protected
	 *
	 * @param array $post The unprepared post data
	 * @param string $context The context for the prepared post. (view|view-revision|edit|embed|single-parent)
	 * @return array The prepared post data
	 */
	protected function prepare_post( $post, $context = 'view',$show_type= 'row' ) {
		// Holds the data for this post.
		$_post = array( 'ID' => (int) $post['ID'] );

		$post_type = get_post_type_object( $post['post_type'] );

		if ( ! json_check_post_permission( $post, 'read' ) ) {
            return false;
		}

		$previous_post = null;
		if ( ! empty( $GLOBALS['post'] ) ) {
			$previous_post = $GLOBALS['post'];
		}
		$post_obj = get_post( $post['ID'] );

		// Don't allow unauthenticated users to read password-protected posts
		if ( ! empty( $post['post_password'] ) ) {
			if ( ! json_check_post_permission( $post, 'edit' ) ) {
                return false;
			}
			// Fake the correct cookie to fool post_password_required().
			// Without this, get_the_content() will give a password form.
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher = new PasswordHash( 8, true );
			$value = $hasher->HashPassword( $post['post_password'] );
			$_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = wp_slash( $value );
		}

		$GLOBALS['post'] = $post_obj;
		setup_postdata( $post_obj );
        //comment num
        $comment_num = $this->comments->get_comments_num_by_post_id($_post['ID']);

		// prepare common post fields
        $post_content = '';
        if($show_type == 'row'){
            $post_content = $post['post_content'];
        }
		$post_fields = array(
			'title'           => get_the_title( $post['ID'] ), // $post['post_title'],
			'status'          => $post['post_status'],
			'type'            => $post['post_type'],
			'author'          => (int) $post['post_author'],
			'content'         => apply_filters( 'the_content', $post_content ),
			'parent'          => (int) $post['post_parent'],
			#'post_mime_type' => $post['post_mime_type'],
			'link'            => get_json_url_posts_list( $post['ID'] ),
		);

		$post_fields_extended = array(
			'excerpt'        => $this->prepare_excerpt( $post['post_excerpt'] ),    //摘要
			'comment_status' => $post['comment_status'],
            'comment_num'    => (int) $comment_num,
			#'slug'           => $post['post_name'],
			#'guid'           => apply_filters( 'get_the_guid', $post['guid'] ),
			#'menu_order'     => (int) $post['menu_order'],
			#'ping_status'    => $post['ping_status'],
			#'sticky'         => ( $post['post_type'] === 'post' && is_sticky( $post['ID'] ) ),
		);

        $post_fields_raw = array();
        if($show_type == 'row'){
            $post_fields_raw = array(
                'title_raw'   => $post['post_title'],
                'content_raw' => $post['post_content'],
                'excerpt_raw' => $post['post_excerpt'],
                'guid_raw'    => $post['guid'],
                #'post_meta'   => $this->handle_get_post_meta( $post['ID'] ),
            );
        }

		// Dates
		$timezone = json_get_timezone();

		if ( $post['post_date_gmt'] === '0000-00-00 00:00:00' ) {
			$post_fields['date']              = null;
			$post_fields_extended['date_tz']  = null;
			$post_fields_extended['date_gmt'] = null;
		}
		else {
			$post_date                        = WP_JSON_DateTime::createFromFormat( 'Y-m-d H:i:s', $post['post_date'], $timezone );
			$post_fields['date']              = json_mysql_to_rfc3339( $post['post_date'] );
			$post_fields_extended['date_tz']  = $post_date->format( 'e' );
			$post_fields_extended['date_gmt'] = json_mysql_to_rfc3339( $post['post_date_gmt'] );
		}

		if ( $post['post_modified_gmt'] === '0000-00-00 00:00:00' ) {
			$post_fields['modified']              = null;
			$post_fields_extended['modified_tz']  = null;
			$post_fields_extended['modified_gmt'] = null;
		}
		else {
			$modified_date                        = WP_JSON_DateTime::createFromFormat( 'Y-m-d H:i:s', $post['post_modified'], $timezone );
			$post_fields['modified']              = json_mysql_to_rfc3339( $post['post_modified'] );
			$post_fields_extended['modified_tz']  = $modified_date->format( 'e' );
			$post_fields_extended['modified_gmt'] = json_mysql_to_rfc3339( $post['post_modified_gmt'] );
		}

		// Authorized fields
		// TODO: Send `Vary: Authorization` to clarify that the data can be
		// changed by the user's auth status
		if ( json_check_post_permission( $post, 'edit' ) ) {
			$post_fields_extended['password'] = $post['post_password'];
		}

		// Consider future posts as published
		if ( $post_fields['status'] === 'future' ) {
			$post_fields['status'] = 'publish';
		}

		// Fill in blank post format
		$post_fields['format'] = get_post_format( $post['ID'] );

		if ( empty( $post_fields['format'] ) ) {
			$post_fields['format'] = 'standard';
		}

		if ( 0 === $post['post_parent'] ) {
			$post_fields['parent'] = null;
		}

		if ( ( 'view' === $context || 'view-revision' == $context ) && 0 !== $post['post_parent'] ) {
			// Avoid nesting too deeply
			// This gives post + post-extended + meta for the main post,
			// post + meta for the parent and just meta for the grandparent
			$parent = get_post( $post['post_parent'], ARRAY_A );
			$post_fields['parent'] = $this->prepare_post( $parent, 'embed' );
		}
		
		// Merge requested $post_fields fields into $_post
		$_post = array_merge( $_post, $post_fields );
		// Include extended fields. We might come back to this.
		$_post = array_merge( $_post, $post_fields_extended );
		if ( 'edit' === $context ) {
			if ( json_check_post_permission( $post, 'edit' ) ) {
				$_post = array_merge( $_post, $post_fields_raw );
			} else {
				$GLOBALS['post'] = $previous_post;
				if ( $previous_post ) {
					setup_postdata( $previous_post );
				}
                json_error(BigAppErr::$post['code'],"post id is not valid",$id);
			}
		} elseif ( 'view-revision' == $context ) {
			if ( json_check_post_permission( $post, 'edit' ) ) {
				$_post = array_merge( $_post, $post_fields_raw );
			} else {
				$GLOBALS['post'] = $previous_post;
				if ( $previous_post ) {
					setup_postdata( $previous_post );
				}
                return false;
			}
		}

		// Entity meta
		$links = array(
			'self'       => get_json_url_posts_list( $post['ID'] ),
			'author'     => get_json_url_users_get_user( $post['post_author'] ),
			'collection' => get_json_url_posts_list( ),
		);

		if ( 'view-revision' != $context ) {
			$links['replies'] = get_json_url_comments_get_comments(  $post['ID'] );
			$links['version-history'] = get_json_url_post_get_revisions( $post['ID'] );
		}

		#$_post['meta'] = array( 'links' => $links );

		if ( ! empty( $post['post_parent'] ) ) {
			$_post['meta']['links']['up'] = get_json_url_posts_list(  (int) $post['post_parent'] );
		}

		$GLOBALS['post'] = $previous_post;
		if ( $previous_post ) {
			setup_postdata( $previous_post );
		}
        //控制发表评论状态
        if($_post['comment_status'] == 'closed'){
            $comment_type = 0;
        }else{
            $comment_type = bigapp_core::check_comment_status();
            if($comment_type ==  0 && $_post['comment_status'] == 'open'){
                $comment_type = 1;
            }
        }
        $_post['comment_type'] = $comment_type;
        //浏览量次数
        $post_views = new WP_JSON_PostViews($this->server);
        $_post['views'] = $post_views->get_views_by_id($post['ID']);
		return apply_filters( 'json_prepare_post', $_post, $post, $context );
	}

	/**
	 * Retrieve the post excerpt.
	 *
	 * @return string
	 */
	protected function prepare_excerpt( $excerpt ) {
		if ( post_password_required() ) {
			return __( 'There is no excerpt because this is a protected post.' );
		}

		$excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $excerpt ) );

		if ( empty( $excerpt ) ) {
			return null;
		}

		return $excerpt;
	}

	/**
	 * Embed post type data into taxonomy data
	 *
	 * @uses self::get_post_type()
	 * @param array $data Taxonomy data
	 * @param array $taxonomy Internal taxonomy data
	 * @param string $context Context (view|embed)
	 * @return array Filtered data
	 */
	public function add_post_type_data( $data, $taxonomy, $context = 'view' ) {
		if ( $context !== 'embed' ) {
			$data['types'] = array();
			foreach( $taxonomy->object_type as $type ) {
				$data['types'][ $type ] = $this->get_post_type( $type, 'embed' );
			}
		}

		return $data;
	}

	/**
	 * Helper method for {@see create_post} and {@see edit_post}, containing shared logic.
	 *
	 *
	 * @param array $data Post data to insert.
	 *
	 * @return int|WP_Error
	 */
	protected function insert_post( array $data ) {
		$post   = array();
		$update = ! empty( $data['ID'] );

		if ( $update ) {
			$current_post = get_post( absint( $data['ID'] ) );

			if ( ! $current_post ) {
                json_error(BigAppErr::$post['code'],"post id is not valid",$id);
			}

			$post['ID'] = absint( $data['ID'] );
		} else {
			// Defaults
			$post['post_author']   = 0;
			$post['post_password'] = '';
			$post['post_excerpt']  = '';
			$post['post_content']  = '';
			$post['post_title']    = '';
		}

		// Post type
		if ( ! empty( $data['type'] ) ) {
			// Changing post type
			$post_type = get_post_type_object( $data['type'] );

			if ( ! $post_type ) {
                json_error(BigAppErr::$post['code'],"Invalid post type");
			}

			$post['post_type'] = $data['type'];
		} elseif ( $update ) {
			// Updating post, use existing post type
			$current_post = get_post( $data['ID'] );

			if ( ! $current_post ) {
                json_error(BigAppErr::$post['code'],"Invalid post ID.");
			}

			$post_type = get_post_type_object( $current_post->post_type );
			$post['post_type'] = $current_post->post_type;
		} else {
			// Creating new post, use default type
			$post['post_type'] = apply_filters( 'json_insert_default_post_type', 'post' );
			$post_type = get_post_type_object( $post['post_type'] );

			if ( ! $post_type ) {
                json_error(BigAppErr::$post['code'],"Invalid post type");
			}
		}

		// Permissions check
		if ( $update ) {
			if ( ! json_check_post_permission( $post, 'edit' ) ) {
                json_error(BigAppErr::$post['code'],__("Sorry, you are not allowed to edit this post."));
			}

			if ( $post_type->name != get_post_type( $data['ID'] ) ) {
                json_error(BigAppErr::$post['code'],"The post type may not be changed.");
			}
		} else {
			if ( ! json_check_post_permission( $post, 'create' ) ) {
                json_error(BigAppErr::$post['code'],__("Sorry, you are not allowed to post on this site."));
			}
		}

		// Post status
		if ( ! empty( $data['status'] ) ) {
			$post['post_status'] = $data['status'];

			switch ( $post['post_status'] ) {
				case 'draft':
				case 'pending':
					break;
				case 'private':
					if ( ! json_check_post_permission( $post, 'publish_posts' ) ) {
                        json_error(BigAppErr::$post['code'],__("Sorry, you are not allowed to create private posts in this post type"));
					}
					break;
				case 'publish':
				case 'future':
					if ( ! json_check_post_permission( $post, 'publish_posts' ) ) {
                        json_error(BigAppErr::$post['code'],__("Sorry, you are not allowed to publish posts in this post type"));
					}
					break;
				default:
					if ( ! get_post_status_object( $post['post_status'] ) ) {
						$post['post_status'] = 'draft';
					}
					break;
			}
		}

		// Post title
		if ( ! empty( $data['title'] ) ) {
			$post['post_title'] = $data['title'];
		}

		// Post date
		if ( ! empty( $data['date'] ) ) {
			$date_data = json_get_date_with_gmt( $data['date'] );

			if ( ! empty( $date_data ) ) {
				list( $post['post_date'], $post['post_date_gmt'] ) = $date_data;
			}
		} elseif ( ! empty( $data['date_gmt'] ) ) {
			$date_data = json_get_date_with_gmt( $data['date_gmt'], true );

			if ( ! empty( $date_data ) ) {
				list( $post['post_date'], $post['post_date_gmt'] ) = $date_data;
			}
		}

		// Post slug
		if ( ! empty( $data['name'] ) ) {
			$post['post_name'] = $data['name'];
		}

		// Author
		if ( ! empty( $data['author'] ) ) {
			// Allow passing an author object
			if ( is_object( $data['author'] ) ) {
				if ( empty( $data['author']->ID ) ) {
                    json_error(BigAppErr::$post['code'],"Invalid author object.");
				}
				$data['author'] = (int) $data['author']->ID;
			} else {
				$data['author'] = (int) $data['author'];
			}

			// Only check edit others' posts if we are another user
			if ( $data['author'] !== get_current_user_id() ) {
				if ( ! json_check_post_permission( $post, 'edit_others_posts' ) ) {
                    json_error(BigAppErr::$post['code'],"You are not allowed to edit posts as this user.");
				}

				$author = get_userdata( $data['author'] );

				if ( ! $author ) {
                    json_error(BigAppErr::$post['code'],"Invalid author ID.");
				}
			}

			$post['post_author'] = $data['author'];
		}

		// Post password
		if ( ! empty( $data['password'] ) ) {
			$post['post_password'] = $data['password'];

			if ( ! json_check_post_permission( $post, 'publish_posts' ) ) {
                json_error(BigAppErr::$post['code'],__("Sorry, you are not allowed to create password protected posts in this post type"));
			}
		}

		// Content and excerpt
		if ( ! empty( $data['content_raw'] ) ) {
			$post['post_content'] = $data['content_raw'];
		}

		if ( ! empty( $data['excerpt_raw'] ) ) {
			$post['post_excerpt'] = $data['excerpt_raw'];
		}

		// Parent
		if ( ! empty( $data['parent'] ) ) {
			$parent = get_post( $data['parent'] );
			if ( empty( $parent ) ) {
                json_error(BigAppErr::$post['code'],"Invalid post parent ID.");
			}

			$post['post_parent'] = $parent->ID;
		}

		// Menu order
		if ( ! empty( $data['menu_order'] ) ) {
			$post['menu_order'] = $data['menu_order'];
		}

		// Comment status
		if ( ! empty( $data['comment_status'] ) ) {
			$post['comment_status'] = $data['comment_status'];
		}

		// Ping status
		if ( ! empty( $data['ping_status'] ) ) {
			$post['ping_status'] = $data['ping_status'];
		}

		// Post format
		if ( ! empty( $data['post_format'] ) ) {
			$formats = get_post_format_slugs();

			if ( ! in_array( $data['post_format'], $formats ) ) {
                json_error(BigAppErr::$post['code'],"Invalid post format.");
			}
			$post['post_format'] = $data['post_format'];
		}

		// Pre-insert hook
		$can_insert = apply_filters( 'json_pre_insert_post', true, $post, $data, $update );

		if ( is_wp_error( $can_insert ) ) {
			return $can_insert;
		}

		// Post meta
		// TODO: implement this
		$post_ID = $update ? wp_update_post( $post, true ) : wp_insert_post( $post, true );

		if ( is_wp_error( $post_ID ) ) {
			return $post_ID;
		}

		// If this is a new post, add the post ID to $post
		if ( ! $update ) {
			$post['ID'] = $post_ID;
		}

		// Post meta
		if ( ! empty( $data['post_meta'] ) ) {
			$result = $this->handle_post_meta_action( $post_ID, $data );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Sticky
		if ( isset( $data['sticky'] ) ) {
			if ( $data['sticky'] ) {
				stick_post( $post_ID );
			} else {
				unstick_post( $post_ID );
			}
		}

		do_action( 'json_insert_post', $post, $data, $update );

		return $post_ID;
	}

	/**
	 * Delete a comment.
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id
	 * @param int $comment
	 * @param boolean $force
	 * @return array|WP_Error
	 */
	public function delete_comment( $id, $comment, $force = false ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'WPAPI-1.2', 'WP_JSON_Comments::delete_comment' );

		return $this->comments->delete_comment( $id, $comment, $force );
	}

	/**
	 * Retrieve comments.
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id
	 * @return array
	 */
	public function get_comments( $id ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'WPAPI-1.2', 'WP_JSON_Comments::get_comments' );

		return $this->comments->get_comments( $id );
	}

	/**
	 * Retrieve comments.
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id
	 * @return array
	 */
	public function get_comment( $comment ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'WPAPI-1.2', 'WP_JSON_Comments::get_comment' );

		return $this->comments->get_comment( $comment );
	}

	/**
	 * Prepares comment data for returning as a JSON response.
	 *
	 * @param stdClass $comment
	 * @param array $requested_fields
	 * @param string $context
	 * @return array
	 */
	protected function prepare_comment( $comment, $requested_fields = array( 'comment', 'meta' ), $context = 'single' ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'WPAPI-1.2', 'WP_JSON_Comments::prepare_comment' );

		return $this->comments->_deprecated_call( 'prepare_comment', array( $comment, $requested_fields, $context ) );
	}

	/**
	 * Update/add/delete meta for a post.
	 *
	 * @param int $post_id
	 * @param array $data
	 * @return bool|WP_Error
	 */
	protected function handle_post_meta_action( $post_id, $data ) {
		$handler = new WP_JSON_Meta_Posts( $this->server );

		return $handler->handle_inline_meta( $post_id, $data['post_meta'] );
	}

	/**
	 * Retrieve all meta for a post.
	 *
	 * @param int $post_id Post ID
	 * @return (array[]|WP_Error) List of meta object data on success, WP_Error otherwise
	 */
	protected function handle_get_post_meta( $post_id ) {
		$handler = new WP_JSON_Meta_Posts( $this->server );

		return $handler->get_all_meta( $post_id );
	}

	/**
	 * Retrieve custom fields for object
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id Object ID
	 * @return (array[]|WP_Error) List of meta object data on success, WP_Error otherwise
	 */
	public function get_all_meta( $id ) {
		_deprecated_function( 'WP_JSON_Posts::get_all_meta', 'WPAPI-1.2', 'WP_JSON_Meta_Posts::get_all_meta' );

		$handler = new WP_JSON_Meta_Posts( $this->server );
		return $handler->get_all_meta( $id );
	}

	/**
	 * Add meta to a post
	 *
	 * Ensures that the correct location header is sent with the response.
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id Post ID
	 * @param array $data {
	 *     @type string|null $key Meta key
	 *     @type string|null $key Meta value
	 * }
	 * @return bool|WP_Error
	 */
	public function add_meta( $id, $data ) {
		_deprecated_function( 'WP_JSON_Posts::add_meta', 'WPAPI-1.2', 'WP_JSON_Meta_Posts::add_meta' );

		$handler = new WP_JSON_Meta_Posts( $this->server );
		return $handler->add_meta( $id, $data );
	}

	/**
	 * Retrieve custom field object.
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id Object ID
	 * @param int $mid Metadata ID
	 * @return array|WP_Error Meta object data on success, WP_Error otherwise
	 */
	public function get_meta( $id, $mid ) {
		_deprecated_function( 'WP_JSON_Posts::get_meta', 'WPAPI-1.2', 'WP_JSON_Meta_Posts::get_meta' );

		$handler = new WP_JSON_Meta_Posts( $this->server );
		return $handler->get_meta( $id, $mid );
	}

	/**
	 * Add meta to an object
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id Object ID
	 * @param array $data {
	 *     @type string|null $key Meta key
	 *     @type string|null $key Meta value
	 * }
	 * @return bool|WP_Error
	 */
	public function update_meta( $id, $mid, $data ) {
		_deprecated_function( 'WP_JSON_Posts::update_meta', 'WPAPI-1.2', 'WP_JSON_Meta_Posts::update_meta' );

		$handler = new WP_JSON_Meta_Posts( $this->server );
		return $handler->update_meta( $id, $mid, $data );
	}

	/**
	 * Delete meta from an object
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $id Object ID
	 * @param int $mid Metadata ID
	 * @return array|WP_Error Message on success, WP_Error otherwise
	 */
	public function delete_meta( $id, $mid ) {
		_deprecated_function( 'WP_JSON_Posts::delete_meta', 'WPAPI-1.2', 'WP_JSON_Meta_Posts::delete_meta' );

		$handler = new WP_JSON_Meta_Posts( $this->server );
		return $handler->delete_meta( $id, $mid, $data );
	}

	/**
	 * Prepares meta data for return as an object
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param int $post Object ID
	 * @param stdClass $data Metadata row from database
	 * @param boolean $is_raw Is the value field still serialized? (False indicates the value has been unserialized)
	 * @return array|WP_Error Meta object data on success, WP_Error otherwise
	 */
	protected function prepare_meta( $post, $data, $is_raw = false ) {
		_deprecated_function( 'WP_JSON_Posts::prepare_meta', 'WPAPI-1.2', 'WP_JSON_Meta_Posts::prepare_meta' );

		$handler = new WP_JSON_Meta_Posts( $this->server );
		return $handler->_deprecated_call( 'prepare_meta', array( $post, $data, $is_raw ) );
	}

	/**
	 * Check if the data provided is valid data
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param mixed $data Data to be checked
	 * @return boolean Whether the data is valid or not
	 */
	protected function is_valid_meta_data( $data ) {
		_deprecated_function( 'WP_JSON_Posts::is_valid_meta_data', 'WPAPI-1.2', 'WP_JSON_Meta_Posts::is_valid_meta_data' );

		$handler = new WP_JSON_Meta_Posts( $this->server );
		return $handler->_deprecated_call( 'is_valid_meta_data', array( $post, $data, $is_raw ) );
	}

	/**
	 * Check if we can read a post
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param array $post Post data
	 * @return boolean Can we read it?
	 */
	protected function check_read_permission( $post ) {
		_deprecated_function( 'WP_JSON_Posts::check_read_permission', 'WPAPI-1.2', 'json_check_post_permission' );

		return json_check_post_permission( $post, 'read' );
	}

	/**
	 * Check if we can edit a post
	 *
	 * @deprecated WPAPI-1.2
	 *
	 * @param array $post Post data
	 * @return boolean Can we edit it?
	 */
	protected function check_edit_permission( $post ) {
		_deprecated_function( 'WP_JSON_Posts::check_edit_permission', 'WPAPI-1.2', 'json_check_post_permission' );

		return json_check_post_permission( $post, 'edit' );
	}
}
