<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Crud_Wordpress_CustomPost implements HC3_ICrud
{
	protected $wp_type = NULL;
	protected $core_fields = array();
	protected $withMeta = FALSE;

	protected $fields_to_me = array('ID' => 'id', 'post_status' => 'status');
	protected $fields_to_wp = array('id' => 'ID', 'status' => 'post_status');

	protected $idField = 'id';

	static $read_query_cache = array();
	protected $read_use_cache = TRUE;

	protected $default_sort = array();

	public function __construct( $postType, $convertToMe = array(), $convertToWp = array() )
	{
		$this->wp_type = $postType;

		if( ! post_type_exists($this->wp_type) ){
			// echo "REGISTERING POST TYPE '" . $this->wp_type . "'<br>";
			register_post_type(
				$this->wp_type,
				array(
					// 'public' => TRUE,
					'public' => FALSE,
					// 'publicly_queryable' => TRUE,
					'publicly_queryable' => false,
					'has_archive' => FALSE,
					'exclude_from_search' => TRUE,
					'show_in_menu' => FALSE,
					'show_in_nav_menus'	=> FALSE,
					'show_in_rest' => TRUE,

					// 'show_in_menu' => TRUE,
					// 'show_in_nav_menus'	=> TRUE,
					// 'show_in_rest' => TRUE,
					// 'show_ui'		=> TRUE,
					// 'menu_position'	=> 5,
					)
				);

			add_filter( 'user_has_cap', array($this, 'wpAllowEditPost'), 10, 3 );
		}

		if( $convertToMe ){
			foreach( $convertToMe as $k => $v ){
				$this->fields_to_me[ $k ] = $v;
			}
		}

		if( $convertToWp ){
			foreach( $convertToWp as $k => $v ){
				$this->fields_to_wp[ $k ] = $v;
			}
		}
	}

	public function withMeta()
	{
		$this->withMeta = TRUE;
		return $this;
	}

	public function wpAllowEditPost( $allcaps, $cap, $args )
	{
		if ( 'edit_post' != $args[0] ){
			return $allcaps;
		}

		if( ! isset($args[2]) ){
			return $allcaps;
		}

	// Load the post data:
		$post = get_post( $args[2] );

		if( ! ($post && isset($post->post_type)) ){
			return $allcaps;
		}

		if( $post->post_type != $this->wp_type ){
			return $allcaps;
		}

		$allcaps[ $cap[0] ] = TRUE;
		return $allcaps;
	}

	public function convert_to_wp( $values )
	{
		$core_values = array();
		$meta_values = array();

		foreach( $values as $k => $v ){
			if( isset($this->fields_to_wp[$k]) ){
				$wp_k = $this->fields_to_wp[$k];
				$core_values[$wp_k] = $v;
			}
			else {
				$meta_values[ (string) $k ] = $v;
			}
		}

		$return = array( $core_values, $meta_values );
		return $return;
	}

	public function set_search_in( $fields = array() ){
		$this->search_in = $fields;
		return $this;
	}

// READ
	public function read_prepare_query( $args = array() )
	{
		$q = array(
			'post_type'		=> $this->wp_type,
			'post_status'	=> array('any', 'trash', 'draft'),
			'perm'			=> 'readable',
			// 'no_found_rows' => FALSE,
			);

		$meta_query = array();

		foreach( $args['WHERE'] as $w ){
			list( $k, $compare, $v ) = $w;
			if( 'NOTIN' == $compare ){
				$compare = 'NOT IN';
			}

			$test = array($k => array($compare, $v));
			list( $core_where, $meta_where ) = $this->convert_to_wp( $test );

// echo 'CORE WHERE';
// _print_r( $core_where );
// echo 'META WHERE';
// _print_r( $meta_where );

		// don't query main table except ID, post_name, post_title as it's too difficult at the moment
			if( $core_where ){
				foreach( $core_where as $k => $more ){
					list( $compare, $v ) = $more;
					switch( $k ){
						case 'ID':
							switch( $compare ){
								case '=':
									$q['p'] = $v;
									break;
								case '<>':
									$q['post__not_in'] = array($v);
									break;
								case 'IN':
									$q['post__in'] = $v;
									break;
								case 'NOTIN':
								case 'NOT IN':
									if( ! is_array($v) ){
										$v = array($v);
									}
									$q['post__not_in'] = $v;
									break;
							}
							break;

						case 'post_name':
							switch( $compare ){
								case '=':
									$q['post_name__in'] = array($v);
									break;
								case 'IN':
									$q['post_name__in'] = $v;
									break;
							}
							break;

						case 'post_title':
							switch( $compare ){
								case '=':
									$q['title'] = $v;
									break;
							}
							break;

						case 'post_status':
							switch( $compare ){
								case '=':
								case 'IN':
									$q['post_status'] = $v;
									break;
							}
							break;
					}
				}
			}

			if( $meta_where ){
				foreach( $meta_where as $k => $more ){
					list( $compare, $v ) = $more;
					$meta_query[] = array(
						'key' => $k,
						'compare' => $compare,
						'value' => $v,
						);
				}
			}
		}
		if( $meta_query ){
			$meta_query['relation'] = 'AND';
		}
// _print_r( $q );
// exit;

		if( $args['SORT'] ){
			$wp_orderby = array();

			$sort = array();
			foreach( $args['SORT'] as $s ){
				$sort[$s[0]] = $s[1];
			}

			list( $sort_core, $sort_meta ) = $this->convert_to_wp( $sort );

			if( $sort_core ){
				$wp_orderby = array_merge( $wp_orderby, $sort_core );
			}

			if( $sort_meta ){
				$meta_orderby = array();
				foreach( $sort_meta as $k => $v ){
					$meta_query[ $k . '_clause' ] = array(
						'key'	=> $k,
						);
					$meta_orderby[ $k . '_clause' ] = $v;
				}
				$wp_orderby = array_merge( $wp_orderby, $meta_orderby );
			}

			$q['orderby'] = $wp_orderby;
		}

		if( $args['LIMIT'] ){
			$q['posts_per_page'] = $args['LIMIT'][0];
			if( $args['LIMIT'][1] ){
				$q['offset'] = $args['LIMIT'][1];
			}
		}
		else {
			$q['posts_per_page'] = -1;
		}

		if( $args['SEARCH'] && $this->search_in ){
			$search = array();
			reset( $this->search_in );
			foreach( $this->search_in as $k ){
				$search[$k] = $args['SEARCH'];
			}
			list( $core_search, $meta_search ) = $this->convert_to_wp( $search );

			if( $core_search ){
				$q['s'] = $args['SEARCH'];
			}

			if( $meta_search ){
				$submeta_query = array();
				$submeta_query['relation'] = 'OR';

				foreach( $meta_search as $k2 => $v2 ){
					$submeta_query[] = array(
						'key'     => $k2,
						'value'   => $v2,
						'compare' => 'LIKE',
						);
				}

				$meta_query[] = $submeta_query;
			}
		}

		if( $meta_query ){
			$q['meta_query'] = $meta_query;
		}

		return $q;
	}

	public function count( $args = array() )
	{
		$return = NULL;

		if( NULL === $args ){
			return $return;
		}

		$args = HC3_Functions::tempCrudPrepareArgs( $args, $this->idField );

		if( $this->read_use_cache ){
			$cache_key = $this->wp_type . ':count' . json_encode($args);
			if( array_key_exists($cache_key, self::$read_query_cache) ){
				// echo "ON CACHE: '$sql'<br>";
				$return = self::$read_query_cache[$cache_key];
				return $return;
			}
		}

		$q = $this->read_prepare_query( $args );

		if( $q !== NULL ){
			$wp_query = new WP_Query( $q );
			$return = $wp_query->found_posts;
		}

		return $return;
	}

	public function read( $args = array() )
	{
		$return = array();

		if( NULL === $args ){
			return $return;
		}

		$args = array_merge( $this->default_sort, $args );

		$args = HC3_Functions::tempCrudPrepareArgs( $args, $this->idField );

		if( $this->read_use_cache ){
			$cache_key = $this->wp_type . ':' . json_encode($args);
			if( isset(self::$read_query_cache[$cache_key]) ){
				// echo "ON CACHE: '$cache_key'<br>";
				$return = self::$read_query_cache[$cache_key];
				return $return;
			}
		}

		$q = $this->read_prepare_query( $args );

// $args = array(
    // 'post_type'   => 'your_post_type',
    // 'post_status' => 'publish',

    // 'meta_query' => array(
        // 'relation' => 'AND',
        // array(
            // 'key'     => 'longitude-key',
            // 'value'   => '',
            // 'compare' => 'NOT'
        // ),
        // array(
            // 'key'     => 'latitude-key',
            // 'value'   => '',
            // 'compare' => 'NOT'
        // ),
        // array(
            // 'key'     => 'name-key',
            // 'value'   => '',
            // 'compare' => 'NOT'
        // ),
    // )
// );
		if( $q !== NULL ){
// echo 'QUERY<br>';
// _print_r( $q );
// exit;

// _print_r( $q );
			$wp_query = new WP_Query( $q );
			$posts = $wp_query->get_posts();

// echo "POSTS";
// _print_r( $posts );
// global $wpdb;
// _print_r( $wpdb->queries );
// exit;
			$return = array();
			$count = count($posts);
			for( $ii = 0; $ii < $count; $ii++ ){
				$meta = get_metadata( 'post', $posts[$ii]->ID );
				$values = array_map( function($n){return $n[0];}, $meta );
				$keys = array_keys( $values );
				foreach( $keys as $k ){
					$metaK = 'meta_' . $k;
					$values[ $metaK ] = $values[$k];
				}
				reset( $this->fields_to_me );
				foreach( $this->fields_to_me as $wp_field => $my_field ){
					$values[ $my_field ] = $posts[$ii]->{$wp_field};
				}

				$key_id = $values[ $this->idField ];
				$return[ $key_id ] = $values;
			}
		}

		if( $this->read_use_cache ){
			self::$read_query_cache[$cache_key] = $return;
		}

		return $return;
	}

// CREATE
	public function create( $values )
	{
		$return = $values;

		list( $core_values, $meta_values ) = $this->convert_to_wp( $values );

		$postarr = $core_values;
		$postarr['post_type'] = $this->wp_type;

		if( ! array_key_exists('post_status', $postarr) ){
			$postarr['post_status'] = 'publish';
		}

		if( $meta_values ){
			$postarr['meta_input'] = $meta_values;
		}

		$id = wp_insert_post( $postarr, TRUE );

		if( is_wp_error($id) ){
			$error = '__Database Error__' . ': ' . $id->get_error_message();
			throw new Exception( $error );
		}

		$return['id'] = $id;
		return $return;
	}

// UPDATE
	public function update( $id, $values )
	{
		$return = TRUE;

		if( ! $id ){
			return;
		}

		$values[$this->idField] = $id;
		$return = $values;

		unset( $values['id'] );
		list( $core_values, $meta_values ) = $this->convert_to_wp( $values );

		if( $core_values ){
			$core_values['ID'] = $id;
			wp_update_post( $core_values );
		}

		if( $meta_values ){
			foreach( $meta_values as $k => $v ){
				update_post_meta( $id, $k, $v );
			}
		}

		return $return;
	}

	public function updateMeta( $id, $values )
	{
		$return = TRUE;

		if( ! $id ){
			return;
		}

		if( $values ){
			foreach( $values as $k => $v ){
				$k = (string) $k;
				update_post_meta( $id, $k, $v );
			}
		}

		return $return;
	}


// DELETE
	public function delete( $id )
	{
		$return = FALSE;

		if( ! $id ){
			return $return;
		}

		$return = wp_delete_post( $id, TRUE );
		return $return;
	}

	public function deleteAll()
	{
		global $wpdb;

		$sql = '
DELETE `posts`, `pm`
FROM `' . $wpdb->prefix . 'posts` AS `posts` 
LEFT JOIN `' . $wpdb->prefix . 'postmeta` AS `pm` ON `pm`.`post_id` = `posts`.`ID`
WHERE `posts`.`post_type` = \'' . $this->wp_type . '\'';

		$result = $wpdb->query($sql);

		$return = TRUE;
		return $return;
	}
}
