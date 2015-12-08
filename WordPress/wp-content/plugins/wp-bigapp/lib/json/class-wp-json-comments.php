<?php

class WP_JSON_Comments {
    /**
     * Base route name. 
     */ 
    protected $route = "comments";
    protected $server = null;

	public function __construct(WP_JSON_ResponseHandler $server=null) {
		$this->server = $server;
    }
	/**
	 * Register the comment-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
		$routes[ $this->route] = array(
            "get_comments" =>array( array($this,"get_comments"),WP_JSON_Server::READABLE ),
            "get_comment" =>array( array($this,"get_comment"),WP_JSON_Server::READABLE ),
            "delete_comment" =>array( array($this,"delete_comment"),WP_JSON_Server::DELETABLE || WP_JSON_Server::READABLE),
            "add_comment" =>array( array($this,"add_comment"),WP_JSON_Server::CREATABLE || WP_JSON_Server::READABLE),
            "my_comments" =>array( array($this,"my_comments"),WP_JSON_Server::CREATABLE || WP_JSON_Server::READABLE),
		);
		return $routes;
	}
    /**
     * add new comment
     * @param int $id post id
     * @param string $comment  comment value
     * @param int $parent_id 父评论的ID
     */
    public function add_comment($id,$comment,$author='',$email='',$parent_id = 0,$type=0){
		if ( empty( $id) || empty($comment) ) {
            json_error(BigAppErr::$comment['code'],"empty id or comment");
		}
        $user_id = get_current_user_id();
        $comment_type = bigapp_core::check_comment_status();
        if($comment_type == 2 && $user_id == 0){
            if($author == '' or $email == ''){
                json_error(BigAppErr::$comment['code'],'need email or author');
            }
            if(false == check_email($email)){
                json_error(BigAppErr::$comment['code'],'email format is wrong');
            }
        }
        if($comment_type == 3){
            if($user_id  == 0){
                json_error(BigAppErr::$login['code'],'need login');
            }
        }
        $commentdata = array("comment_post_ID"=>$id,
            'comment_content'=>$comment,
            'comment_approved'=>1,
            'comment_author' => $author,
            'comment_author_email' => $email,
            'comment_parent' => $parent_id,
            "user_ID"=>$user_id);
        $result = wp_new_comment($commentdata);

		if ( ! $result ) {
            json_error(BigAppErr::$comment['code'],"creat new comment failed");
		}
		return array( 'id'=>$result );
    }

	/**
	 * Delete a comment.
	 *
	 * @uses wp_delete_comment
	 * @param int $id Post ID
	 * @param int $comment Comment ID
	 * @param boolean $force Skip trash
	 * @return array
	 */
	public function delete_comment( $comment, $force = false ) {
		$comment = (int) $comment;

		if ( empty( $comment ) ) {
            json_error(BigAppErr::$comment['code'],BigAppErr::$comment['msg'],__lan("comment id invalid"));
		}

		$comment_array = get_comment( $comment, ARRAY_A );

		if ( empty( $comment_array ) ) {
            json_error(BigAppErr::$comment['code'],BigAppErr::$comment['msg'],__lan("comment id invalid"));
		}
        $user_id = get_current_user_id();
        if($user_id == 0){      //未登录用户,不能删除评论
            json_error(BigAppErr::$login['code'],BigAppErr::$login['msg'],__lan("need login"));
        }

		if ( ! current_user_can(  'edit_comment', $comment_array['comment_ID'] )  && $user_id != $comment_array['user_id'] ) {
            json_error(BigAppErr::$comment['code'],BigAppErr::$comment['msg'],__lan("no auth to delete comment "));
		}

		$result = wp_delete_comment( $comment_array['comment_ID'], $force );

		if ( ! $result ) {
            json_error(BigAppErr::$comment['code'],BigAppErr::$comment['msg'],__lan("delete comment failed"));
		}

		return array( 'message' => __( 'Deleted comment' ),'id'=>$comment );
	}

	/**
	 * Retrieve comments
	 *
	 * @param int $id Post ID to retrieve comments for
	 * @return array List of Comment entities
     * filter: array(pre_page,status)
     * page : 页码
     * 返回的都是审核完毕的
	 */
	public function get_comments( $id,$filter=array(),$page=1) {

		$post = get_post( $id, ARRAY_A );

		if ( empty( $post['ID'] ) ) {
            json_error(BigAppErr::$comment['code'],"post id is empty",$id);
		}

        show_debug($post,__FILE__,__LINE__);
		if ( ! json_check_post_permission( $post, 'read' ) ) {
            json_error(BigAppErr::$comment['code'],"cannot read this post.");
		}
        $quer_vars = array('pre_page');
        $number = 10;
        if(isset($filter['pre_page'])){
            $number = intval($filter['pre_page']);
        }
        if($page < 1){
            $page = 1;
        }
        $offset = ($page - 1) * $number;
        $status = 'approve';
        if(isset($filter['status'])){
            $status = $filter['status'];
        }
		$comments = get_comments( array('post_id' => $id,'number'=>$number,'status'=>$status,'offset'=>$offset) );
        show_debug($comments,__FILE__,__LINE__);

		$struct = array();

		foreach ( $comments as $comment ) {
			$struct[] = $this->prepare_comment( $comment, array( 'comment', 'meta' ), 'collection' );
		}

		return $struct;
	}
    /**
     * get comments num by post id
     * 没有考虑嵌套评论数目
     */
    public function get_comments_num_by_post_id($post_id){

		$comments = get_comments( array('post_id' => $post_id ) );

		$struct = array();

		foreach ( $comments as $comment ) {
            if ( $comment->comment_approved != 1){
                continue;
            }
			$struct[] = $comment;
		}
        return count($struct);
    }

	/**
	 * Retrieve a single comment
	 *
	 * @param int $comment Comment ID
	 * @return array Comment entity
	 */
	public function get_comment( $comment ) {
		$comment_data = get_comment( $comment );
        show_debug($comment_data,__FILE__,__LINE__);

		if ( empty( $comment ) ) {
            json_error(BigAppErr::$comment['code'],"cannot get comment.",$comment);
		}

		$data[] = $this->prepare_comment( $comment_data );

		return $data;
	}

	/**
	 * Prepares comment data for returning as a JSON response.
	 *
	 * @param stdClass $comment Comment object
	 * @param array $requested_fields Fields to retrieve from the comment
	 * @param string $context Where is the comment being loaded?
	 * @return array Comment data for JSON serialization
	 */
	protected function prepare_comment( $comment, $requested_fields = array( 'comment', 'meta' ), $context = 'single' ) {
		$fields = array(
			'ID'   => (int) $comment->comment_ID,
			'post' => (int) $comment->comment_post_ID,
		);

		$post = (array) get_post( $fields['post'] );

		// Content
		$fields['content'] = apply_filters( 'get_comment_text', $comment->comment_content, $comment );

		// Status
		switch ( $comment->comment_approved ) {
			case 'hold':
			case '0':
				$fields['status'] = 'hold';
				break;

			case 'approve':
			case '1':
				$fields['status'] = 'approved';
				break;

			case 'spam':
			case 'trash':
			default:
				$fields['status'] = $comment->comment_approved;
				break;
		}

		// Type
		$fields['type'] = apply_filters( 'get_comment_type', $comment->comment_type );

		if ( empty( $fields['type'] ) ) {
			$fields['type'] = 'comment';
		}

		// Parent
		if ( ( 'single' === $context || 'single-parent' === $context ) && (int) $comment->comment_parent ) {
			$parent_fields = array( 'meta' );

			if ( $context === 'single' ) {
				$parent_fields[] = 'comment';
			}
			$parent = get_comment( $comment->comment_parent );
            show_debug($parent,__FILE__,__LINE__);

			$fields['parent'] = $this->prepare_comment( $parent, $parent_fields, 'single-parent' );
		}else{
		    $fields['parent'] = (int) $comment->comment_parent;
        }

		// Author
		if ( (int) $comment->user_id !== 0 ) {
			$fields['author'] = (int) $comment->user_id;
		} else {
            if(empty($comment->comment_author)){
                $a_name = __('Anonymous');
            }else{
                $a_name = $comment->comment_author;
            }
			$fields['author'] = array(
				'ID'     => 0,
				'name'   => $a_name,
				'URL'    => $comment->comment_author_url,
				'avatar' => json_get_avatar_url( $comment->comment_author_email ),
			);
		}

        
		// Date
		$timezone     = json_get_timezone();
		$comment_date = WP_JSON_DateTime::createFromFormat( 'Y-m-d H:i:s', $comment->comment_date, $timezone );

		$fields['date']     = $comment->comment_date ;
		$fields['date_tz']  = $comment_date->format( 'e' );
		$fields['date_gmt'] = json_mysql_to_rfc3339( $comment->comment_date_gmt );

		// Meta
		$meta = array(
			'links' => array(
				'up' => get_json_url_posts_list((int) $comment->comment_post_ID ) ,
			),
		);

		if ( 0 !== (int) $comment->comment_parent ) {
			$meta['links']['in-reply-to'] = get_json_url_comments_get_comments((int) $comment->comment_post_ID, (int) $comment->comment_parent  );
		}

		if ( 'single' !== $context ) {
			$meta['links']['self'] = get_json_url_comments_get_comments( (int) $comment->comment_post_ID, (int) $comment->comment_ID );
		}

		// Remove unneeded fields
		$data = array();

		if ( in_array( 'comment', $requested_fields ) ) {
			$data = array_merge( $data, $fields );
		}

		if ( in_array( 'meta', $requested_fields ) ) {
			$data['meta'] = $meta;
		}

		return apply_filters( 'json_prepare_comment', $data, $comment, $context );
	}

	/**
	 * Call protected method from {@see WP_JSON_Posts}.
	 *
	 * WPAPI-1.2 deprecated a bunch of protected methods by moving them to this
	 * class. This proxy method is added to call those methods.
	 *
	 * @param string $method Method name
	 * @param array $args Method arguments
	 * @return mixed Return value from the method
	 */
	public function _deprecated_call( $method, $args ) {
		return call_user_func_array( array( $this, $method ), $args );
	}
    /**
     * 获取某个人已经发表过的评论.包括评论内容,评论时间,评论文章id/title/url
     * 默认的是每页10个.
     * @param filter= array(pre_page,page)
     * return  
     */
    public function my_comments($filter=array(),$page=10){
        $struct = array();
        $number = 10;       //每页的评论数目
        if(isset($filter['pre_page'])){
            $number = intval($filter['pre_page']);
        }
        $offset = 0;        //偏移量
        if(isset($filter['page']) && $filter['page'] > 0){
            $offset = (intval($filter['page']) -1) * $number;
        }else{
            $offset = $page>0?($page-1) * $number:0;
        }
        $user_id = get_current_user_id();
        show_debug("my_comments:user_id".$user_id,__FILE__,__LINE__);
        if($user_id == 0){      //not login return empty

        }else{
            $arg['orderby'] = 'comment_date';
            $arg['order'] = 'desc';
            $arg['number'] = $number;
            $arg['offset'] = $offset;
            $arg['user_id'] = $user_id;
            $comments = get_comments($arg);

            $post_ids = array();
            $post_infos = array();
            foreach ( $comments as $comment ) {
                $post_ids[] = intval($comment->comment_post_ID);
            }
            show_debug($post_ids,__FILE__,__LINE__);
            if($post_ids){
                $post_model = new WP_JSON_Posts($this->server);
                $filter['_bigapp_post_ids'] = $post_ids;
                $response = $post_model->get_posts($filter);
                $lists = $response->get_data();
                foreach($lists as $list){
                    $post_info[$list['ID']] = $list;
                }
            }
            foreach ( $comments as $comment ) {
                $comment = $this->prepare_comment( $comment, array( 'comment', 'meta' ), 'collection' );
                if(isset($post_info[$comment['post']])){
                    $comment['post_info']['title'] = $post_info[$comment['post']]['title'];
                    $comment['post_info']['link'] = $post_info[$comment['post']]['link'];
                }else{
                    continue;
                }
                $struct[] = $comment;
            }
        }
        return $struct;
    }
}
