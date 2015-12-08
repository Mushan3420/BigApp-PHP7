<?php

class WP_JSON_Taxonomies {
	/**
	 * Register the taxonomy-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	protected $route = 'taxonomies';
	public function register_routes( $routes ) {
		$tax_routes = array(
            $this->route=> array(
                "get_nav_list" => array( array( $this, 'get_nav_list' ), WP_JSON_Server::READABLE ),
                "get_banner_info" => array( array( $this, 'get_banner_info' ), WP_JSON_Server::READABLE ),
			),
		);
		return array_merge( $routes, $tax_routes );
	}
    /**
     * 获取头部自定义展示的导航栏list,可能包括tag/category/nav_menu三种
     * @param 
     * @return array
     */
    public function get_nav_list($context='view'){
        $switch = get_option(BigAppConf::$option_menu_switch,0);
        $menu_list = array();
        if($switch == 1){
            $menu_list  = sort_by_key(json_decode(get_option(BigAppConf::$option_menu_conf,"{}"),true),'rank');
            $banner_confs = json_decode(get_option(BigAppConf::$option_banner_conf),true);
            foreach($menu_list as &$list){
                $list['banner_list'] = $this->get_banner_info($list['ID'],$banner_confs);
            }
        }else{      //默认首页请求,地址固定,由端上请求
        }
        return $menu_list;
    }
    /**
     * 获取自定义banner信息
     * @param id 菜单
     * @return array(type,id)
     */
    public function get_banner_info($id,$banner_confs){
        $lists = array();
        if(isset($banner_confs[$id])){
            $lists = $banner_confs[$id];
        }else{
            return $list;
        }
        $lists = sort_by_key($lists,'rank');
        foreach($lists as &$list){  //如果有一些特定链接，在这边做转换
            $list['link_org'] = $list['link'];
            $list['link'] = convert_link2json($list['link'],$list['type']);
        }
        return $lists;
    }

	/**
	 * Get taxonomies for a post type
	 * @param string $type Post type
	 * @param string $context Context (view|embed)
	 * @return array Taxonomy data
	 */
	public function get_taxonomies_for_type( $type, $context = 'view' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', __CLASS__ . '::get_taxonomies( $type )' );

		return $this->get_taxonomies( $type, $context );
	}

	/**
	 * Get taxonomies
	 *
	 * @param string|null $type A specific post type for which to retrieve taxonomies (optional)
	 * @return array Taxonomy data
	 */
	public function get_taxonomies( $type = null, $context = 'view' ) {
		if ( null === $type ) {
			$taxonomies = get_taxonomies( '', 'objects' );
		} else {
			$taxonomies = get_object_taxonomies( $type, 'objects' );
		}

		$data = array();

		foreach ( $taxonomies as $tax_type => $value ) {
			$tax = $this->prepare_taxonomy_object( $value, $context );
			if ( is_wp_error( $tax ) ) {
				continue;
			}

			$data[] = $tax;
		}

		return $data;
	}

	/**
	 * Get taxonomies (legacy route with support for passing $type)
	 *
	 * @deprecated
	 * @see get_taxonomy_object
	 *
	 * @param string $type Post type to get taxonomies for (deprecated)
	 * @param string $taxonomy Taxonomy slug
	 * @return array Taxonomy data
	 */
	public function get_taxonomy( $type, $taxonomy ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', __CLASS__ . '::get_taxonomy_object' );

		return $this->get_taxonomy_object( $taxonomy );
	}

	/**
	 * Get taxonomies
	 *
	 * @param string $taxonomy Taxonomy slug
	 * @return array Taxonomy data
	 */
	public function get_taxonomy_object( $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );

		if ( empty( $tax ) ) {
			return new WP_Error( 'json_taxonomy_invalid_id', __( 'Invalid taxonomy ID.' ), array( 'status' => 404 ) );
		}

		return $this->prepare_taxonomy_object( $tax );
	}

	/**
	 * Prepare a taxonomy for serialization
	 *
	 * @deprecated
	 * @see prepare_taxonomy_object
	 *
	 * @param stdClass $taxonomy Taxonomy data
	 * @param string|null $type Post type to get taxonomies for (deprecated)
	 * @param string $context Context (view|embed). True/false are allowed for backwards compatibility, and map to embed/view respectively.
	 * @return array Taxonomy data
	 */
	protected function prepare_taxonomy( $taxonomy, $type = null, $context = 'view' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', __CLASS__ . '::prepare_taxonomy_object' );

		return $this->prepare_taxonomy_object( $taxonomy, $context );
	}

	/**
	 * Prepare a taxonomy object for serialization
	 *
	 * @param stdClass $taxonomy Taxonomy data
	 * @param string $context Context (view|embed). True/false are allowed for backwards compatibility, and map to embed/view respectively.
	 * @return array Taxonomy data
	 */
	protected function prepare_taxonomy_object( $taxonomy, $context = 'view' ) {
		if ( $taxonomy->public === false ) {
            json_error(BigAppErr::$taxonomy['code'],BigAppErr::$post['msg'],__lan("Cannot view taxonomy"));
		}

		// Backwards compatibility with _in_collection parameter
		if ( $context === true ) {
			$context = 'embed';
			_deprecated_argument( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', '$context should be set to "embed" rather than true' );
		}
		elseif ( $context === false ) {
			$context = 'view';
			_deprecated_argument( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', '$context should be set to "view" rather than false' );
		}

		$base_url = '/taxonomies/' . $taxonomy->name;

		$data = array(
			'name'         => $taxonomy->label,
			'slug'         => $taxonomy->name,
			'labels'       => $taxonomy->labels,
			'types'        => $taxonomy->object_type,
			'show_cloud'   => $taxonomy->show_tagcloud,
			'hierarchical' => $taxonomy->hierarchical,
			'meta'         => array(
				'links' => array(
					'archives' => json_url( $base_url . '/terms' ),
					'collection' => json_url( '/taxonomies' ),
					'self' => json_url( $base_url )
				)
			),
		);

		return apply_filters( 'json_prepare_taxonomy', $data, $taxonomy, $context );
	}

	/**
	 * Add taxonomy data to post type data
	 *
	 * @param array $data Type data
	 * @param array $post Internal type data
	 * @param boolean $_in_taxonomy The record being filtered is a taxonomy object (internal use)
	 * @return array Filtered data
	 */
	public function add_taxonomy_data( $data, $type, $context = 'view' ) {
		if ( $context !== 'embed' ) {
			$data['taxonomies'] = $this->get_taxonomies( $type->name, 'embed' );
		}

		return $data;
	}

	/**
	 * Get terms for a post type (legacy route with support for passing $type)
	 *
	 * @deprecated
	 * @see get_taxonomy_terms
	 *
	 * @param string $type Post type for which to fetch taxonomies (deprecated)
	 * @param string $taxonomy Taxonomy slug
	 * @return array Term collection
	 */
	public function get_terms( $type, $taxonomy ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', __CLASS__ . '::get_taxonomy_terms' );

		return $this->get_taxonomy_terms( $taxonomy );
	}
    /**
     * 获取导航菜单列表,有多个菜单,其中只有一个主菜单
     */
    public function get_nav_menus($filter= array()){
        return $this->get_taxonomy_terms('nav_menu',$filter);
    }
    /**
     * 获取某个导航菜单列表详情，可能是自定义链接，页面，或者分类
     * id :菜单的term_id
     */
    public function get_nav_menu($id,$filter= array()){
        $items = wp_get_nav_menu_items($id);
        if($items == false){
            #json_error(BigAppErr::$taxonomy['code'],BigAppErr::$taxonomy['msg'],"Invalid menu ID.$id");
        }
        $strcut = array();
        foreach($items as $item){
            $struct[] = $this->prepare_nav_menu($item);
        }
        return $struct;
    }
    /**
     * 获取分类列表
     */
    public function get_categorys($filter= array()){
        return $this->get_taxonomy_terms('category',$filter);
    }
    /**
     * 获取tag列表
     */
    public function get_post_tags($filter= array()){
        return $this->get_taxonomy_terms('post_tag',$filter);
    }

	/**
	 * Get all terms for a post type
	 * base function
	 * @param string $taxonomy Taxonomy slug:category,post_tag,nav_menu,link_category,post_format
	 * @return array Term collection
	 */
	public function get_taxonomy_terms( $taxonomy, $filter = array() ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
            json_error(BigAppErr::$taxonomy['code'],BigAppErr::$taxonomy['msg'],__lan("Invalid taxonomy ID."));
		}

		$args = array(
			'hide_empty' => false,
		);
		
		// Allow args in get_terms function. This is a partial list and does not include hide_empty and cache_domain.  
		$valid_vars = array(
			'orderby',
			'order',
			'exclude',
			'exclude_tree',
			'include',
			'number',
			'fields',
			'slug',
			'parent',
			'hierarchical',
			'child_of',
			'get',
			'name__like',
			'description__like',
			'pad_counts',
			'offset',
			'search',
		);
		
		foreach ( $valid_vars as $var ) {
			if ( isset( $filter[ $var ] ) ) {
				$args[ $var ] = apply_filters( 'json_tax_query_var-' . $var, $filter[ $var ] );
			}
		}
		
		$terms = get_terms( $taxonomy, $args );

		if ( is_wp_error( $terms ) ) {
            json_error(BigAppErr::$taxonomy['code'],BigAppErr::$taxonomy['msg'],"get_term return error");
		}

		$data = array();
		foreach ( $terms as $term ) {
			$data[] = $this->prepare_taxonomy_term( $term );
		}

		return $data;
	}

	/**
	 * Get term for a post type
	 *
	 * @deprecated
	 * @see get_taxonomy_term
	 *
	 * @param string $type Post type (deprecated)
	 * @param string $taxonomy Taxonomy slug
	 * @param string $term Term slug
	 * @param string $context Context (view/view-parent)
	 * @return array Term entity
	 */
	public function get_term( $type, $taxonomy, $term, $context = 'view' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', __CLASS__ . '::get_taxonomy_term( $taxonomy, $term )' );

		return $this->get_taxonomy_term( $taxonomy, $term, $context );
	}

	/**
	 * Get term for a post type
	 *
	 * @param string $taxonomy Taxonomy slug
	 * @param string $term Term slug
	 * @param string $context Context (view/view-parent)
	 * @return array Term entity
     * 获取当个分类信息的接口:action=get_taxonomy_term&taxonomy=category&term=3
	 */
	public function get_taxonomy_term( $taxonomy, $term, $context = 'view' ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
            json_error(BigAppErr::$taxonomy['code'],BigAppErr::$taxonomy['msg'],"Invalid taxonomy ID.");
		}
		$data = get_term( $term, $taxonomy );

		if ( empty( $data ) or is_wp_error($data)) {
            json_error(BigAppErr::$taxonomy['code'],BigAppErr::$taxonomy['msg'],"Invalid taxonomy ID.");
		}

		return $this->prepare_taxonomy_term( $data, $context );
	}

	/**
	 * Add term data to post data
	 *
	 * @param array $data Post data
	 * @param array $post Internal post data
	 * @param string $context Post context
	 * @return array Filtered data
	 */
	public function add_term_data( $data, $post, $context ) {
		$post_type_taxonomies = get_object_taxonomies( $post['post_type'] );
		$terms = wp_get_object_terms( $post['ID'], $post_type_taxonomies );
		$data['terms'] = array();

		foreach ( $terms as $term ) {
			$data['terms'][ $term->taxonomy ][] = $this->prepare_taxonomy_term( $term );
		}

		return $data;
	}

	/**
	 * Prepare term data for serialization
	 *
	 * @deprecated
	 * @see prepare_taxonomy_term
	 *
	 * @param array|object $term The unprepared term data
	 * @param string|null $type Post type to get taxonomies for (deprecated)
	 * @return array The prepared term data
	 */
	protected function prepare_term( $term, $type = null, $context = 'view' ) {
		_deprecated_function( __CLASS__ . '::' . __FUNCTION__, 'WPAPI-1.1', __CLASS__ . '::prepare_taxonomy_term( $term )' );

		return $this->prepare_taxonomy_term( $term, $context );
	}


	/**
	 * Prepare term data for serialization
	 *
	 * @param array|object $term The unprepared term data
	 * @return array The prepared term data
	 */
	protected function prepare_taxonomy_term( $term, $context = 'view' ) {
		$base_url = '/taxonomies/' . $term->taxonomy . '/terms';

		$data = array(
			'ID'          => (int) $term->term_taxonomy_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'description' => $term->description,
			'taxonomy'    => $term->taxonomy,
			'parent'      => (int) $term->parent,
			'count'       => (int) $term->count,
			'link'        => $this->get_posts_list_url_by_taxonomy( $term),
			'meta'        => array(
				'links' => array(
					'collection' => json_url( $base_url ),
					'self'       => json_url( $base_url . '/' . $term->term_id ),
				),
			),
		);

		if ( ! empty( $data['parent'] ) && $context === 'view' ) {
			$data['parent'] = $this->get_taxonomy_term( $term->taxonomy, $data['parent'], 'view-parent' );
		} elseif ( empty( $data['parent'] ) ) {
			$data['parent'] = null;
		}

		return apply_filters( 'json_prepare_term', $data, $term, $context );
	}
    public function get_nav_menu_url_and_type($nav_menu){
        $ret = array("url"=>$nav_menu->url,'type'=>$nav_menu->type);
        $nav_type = $nav_menu->type;
        $nav_id = $nav_menu->object_id;
        switch ($nav_type){
        case "custom":      //自定义链接
            $url = $nav_menu->url;
            if (rtrim($url,'/') == get_bloginfo('wpurl')) {
                $url = get_json_url_posts_list();
                $nav_type = "taxonomy";         //前端要展示列表
            }
            break;
        case "taxonomy":    //分类
            $param['filter[cat]'] = $nav_id;
            $url = get_json_url_posts_list( 0, $param );
            break;
        case "post_type":   //页面
            $url = get_json_url_posts_list( $nav_id);
            break;
        default:
            $url = '';
        }
        $ret['url'] = $url;
        $ret['type'] = $nav_type;
        return $ret;
    }
    /**                        
     * 定制化url              
     * 获取分类/tag下面的所有的文章列表
     */
    public function get_posts_list_url_by_taxonomy($term){
        if ($term->taxonomy == 'category'){
            $param['filter[cat]'] = $term->term_id;
        }elseif ($term->taxonomy == 'post_tag'){
            $param['filter[tag]'] = $term->name;
        }
        $url = get_json_url_posts_list( 0, $param );
        return apply_filters('json_posts_url_by_taxonomy',$url);
    }
    /**
     * format nav menu info
	 * @return array The prepared term data
     */
    protected function prepare_nav_menu($nav_menu,$context='view'){
        $url_and_type = $this->get_nav_menu_url_and_type($nav_menu);
		$data = array(
			'ID'          => (int) $nav_menu->ID,
			'name'        => $nav_menu->title,
			'description' => $nav_menu->description,
			'parent'      => (int) $nav_menu->menu_item_parent,
			'count'       => 0,
            'object'      => $nav_menu->object,     //custom/category/page
            'type'        => $url_and_type['type'],       //custom/taxonomy/post_type
			'link'        => $url_and_type['url'],
		);

		if ( ! empty( $data['parent'] ) && $context === 'view' ) {
			$data['parent'] = $this->get_nav_menu( $data['parent'], 'view-parent' );
		} elseif ( empty( $data['parent'] ) ) {
			$data['parent'] = null;
		}
		return apply_filters( 'json_prepare_nav_menu', $data, $nav_menu, $context );
    }
}
