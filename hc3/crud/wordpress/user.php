<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Crud_Wordpress_User implements HC3_ICrud
{
	protected $wp_type = NULL;
	protected $core_fields = array();
	protected $idField = 'id';
	protected $withMeta = FALSE;

	protected $fields_to_me = array('ID' => 'id');
	protected $fields_to_wp = array('id' => 'ID');

	static $read_query_cache = array();
	protected $read_use_cache = TRUE;

	protected $default_sort = array();

	protected function _convertFromWp( $userdata )
	{
		$return = array(
			'id'			=> $userdata->ID,
			'email'			=> $userdata->user_email,
			'display_name'	=> $userdata->display_name,
			'username'		=> $userdata->user_login,
			);
		return $return;
	}

	public function withMeta()
	{
		$this->withMeta = TRUE;
		return $this;
	}

	protected function _convertToWp( $values )
	{
		$core = array();
		$meta = array();

		if( array_key_exists('id', $values) ){
			$core['ID'] = $values['id'];
			unset( $values['id'] );
		}
		if( array_key_exists('email', $values) ){
			$core['user_email'] = $values['email'];
			unset( $values['email'] );
		}
		if( array_key_exists('display_name', $values) ){
			$core['display_name'] = $values['display_name'];
			unset( $values['display_name'] );
		}
		if( array_key_exists('username', $values) ){
			$core['user_login'] = $values['username'];
			unset( $values['username'] );
		}

		if( array_key_exists('role', $values) ){
			$meta['role'] = $values['role'];
			unset( $values['role'] );
		}

		if( $values ){
			$meta = $meta + $values;
		}

		$return = array( $core, $meta );
		return $return;
	}

	public function set_search_in( $fields = array() ){
		$this->search_in = $fields;
		return $this;
	}

// READ
	public function read_prepare_query( $args = array() )
	{
		$q = array();
		$meta_query = array();

		foreach( $args['WHERE'] as $w ){
			list( $k, $compare, $v ) = $w;
			if( 'NOTIN' == $compare ){
				$compare = 'NOT IN';
			}

			$test = array($k => array($compare, $v));
			list( $core_where, $meta_where ) = $this->_convertToWp( $test );

// _print_r( $args['WHERE'] );
// echo 'CORE WHERE';
// _print_r( $core_where );
// echo 'META WHERE';
// _print_r( $meta_where );
// exit;
		// don't query main table except ID, post_name, post_title as it's too difficult at the moment
			if( $core_where ){
				foreach( $core_where as $k => $more ){
					list( $compare, $v ) = $more;
					switch( $k ){
						case 'ID':
							switch( $compare ){
								case '=':
								case 'IN':
									if( (! is_array($v)) && (! $v) ){
										$return = NULL;
										return $return;
									}
									$q['include'] = $v;
									break;

								case '<>':
								case 'NOTIN':
								case 'NOT IN':
									$q['exclude'] = $v;
									break;
							}
							break;
					}
				}
			}

			if( $meta_where ){
				foreach( $meta_where as $k => $more ){
					list( $compare, $v ) = $more;
					
					switch( $k ){
						case 'role':
							switch( $compare ){
								case '=':
								case 'IN':
									$q['role__in'] = is_array($v) ? $v : array($v);
									break;

								case '<>':
								case 'NOTIN':
								case 'NOT IN':
									$q['role__not_in'] = is_array($v) ? $v : array($v);
									break;
							}
							break;

						default:
							$meta_query[] = array(
								'key'     => $k,
								'compare' => $compare,
								'value'   => $v,
								);
							break;
					}
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

			list( $sort_core, $sort_meta ) = $this->_convertToWp( $sort );

			foreach( $sort_core as $k => $ascdesc ){
				$q['orderby'] = $k;
				$q['order'] = $ascdesc;
			}

			if( $sort_meta ){
				foreach( $sort_meta as $k => $ascdesc ){
					$q['orderby'] = 'meta_value';
					$q['order'] = $ascdesc;
					$q['meta_key'] = $k;
				}
			}
		}

		if( $args['LIMIT'] ){
			$q['number'] = $args['LIMIT'][0];
			if( $args['LIMIT'][1] ){
				$q['offset'] = $args['LIMIT'][1];
			}
		}
		else {
			// $q['posts_per_page'] = -1;
		}

		if( $args['SEARCH'] ){
			$q['search'] = $args['SEARCH'];
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
			$q['number'] = 1;
			$wp_users_query = new WP_User_Query( $q );
			$return = $wp_users_query->get_total();
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

// _print_r( $args );

		if( $this->read_use_cache ){
			$cache_key = $this->wp_type . ':' . json_encode($args);
			if( isset(self::$read_query_cache[$cache_key]) ){
				// echo "ON CACHE: '$cache_key'<br>";
				$return = self::$read_query_cache[$cache_key];
				return $return;
			}
		}

// $args = array();
		$q = $this->read_prepare_query( $args );

		if( $q !== NULL ){
// echo 'QUERY<br>';
// _print_r( $q );
// exit;
			$wp_users_query = new WP_User_Query( $q );
			$wp_users = $wp_users_query->get_results();

			$return = array();
			$count = count($wp_users);
			for( $ii = 0; $ii < $count; $ii++ ){
				// $meta = get_metadata( 'post', $posts[$ii]->ID );
				// $values = array_map( function($n){return $n[0];}, $meta );

				$values = $this->_convertFromWp( $wp_users[$ii] );
				$key_id = $values['id'];
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
	}

// UPDATE
	public function update( $id, $values )
	{
	}

	public function updateMeta( $id, $values )
	{
	}

// DELETE
	public function delete( $id )
	{
	}

	public function deleteAll()
	{
		$return = TRUE;
		return $return;
	}
}
