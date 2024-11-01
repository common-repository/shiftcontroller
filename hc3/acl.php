<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Acl_
{
	public function register( $slug, $handler );
	public function getHandlerArgs( $slug );
	public function check( $slug, $params = array() );
}

class HC3_Acl implements HC3_Acl_
{
	public $dic;
	protected $handlers = array();

	public function __construct( HC3_Dic $dic )
	{
		// echo "INIT ACL!";
		$this->dic = $dic;
	}

	public function check( $slug, $params = array() )
	{
		$return = TRUE;

		list( $handler, $args ) = $this->getHandlerArgs( $slug );
		list( $handler, $method ) = is_array($handler) ? $handler : array( $handler, 'check' );

		if( $handler ){
			$handler = $this->dic
				->make( $handler )
				;

			if( $params ){
				$args[] = $params;
			}

			$checkResult = call_user_func_array( array($handler, $method), $args );
			if( $checkResult === FALSE ){
				if( defined('HC3_DEV_INSTALL') ){
					// echo "ACL NOT ALLOWING: " . $handler->_getClass() . '->' . $method . '()' . '<br>';
				}
				$return = FALSE;
			}
		}

		return $return;
	}

	public function register( $slug, $handler )
	{
		$this->handlers[$slug] = $handler;
		return $this;
	}

	public function getHandlerArgs( $slug )
	{
// echo "GETTING FOR '$slug'<br><br>";
// print_r( array_keys($this->handlers) );
		list( $handler, $args ) = $this->_findHandlerArgs( $slug, $this->handlers );
		$return = array( $handler, $args );
		return $return;
	}

	protected function _findHandlerArgs( $slug, $array )
	{
		$handler = NULL;
		$args = array();

	// exact match
		if( isset($array[$slug]) ){
			$handler = $array[$slug];
		}
	// wildcards
		else {
			// if we have wildcards
			$config_keys = array_keys($array);

			if( strpos($slug, ':') !== FALSE ){
				$slug = str_replace(':', '/', $slug);
			}

			$sluga = explode('/', $slug);
			$count_sluga = count($sluga);
			// echo "SLUG: '$slug'<br>";
			// _print_r( $sluga );

			$parametered_keys = array();
			foreach( $config_keys as $k ){
				if( strpos($k, '{') === FALSE ){
					continue;
				}
				$parametered_keys[] = $k;
			}

			reset( $parametered_keys );
			foreach( $parametered_keys as $k ){
				$kk = $k;
				if( strpos($kk, ':') !== FALSE ){
					$kk = str_replace(':', '/', $kk);
				}
				$ka = explode('/', $kk);

			// check if this one matches
				if( count($ka) != $count_sluga ){
					continue;
				}

				$match = TRUE;
				$parametered_args = array();
				for( $ii = 0; $ii < $count_sluga; $ii++ ){
					if( strpos($ka[$ii], '{') !== FALSE ){
						$parametered_args[] = $sluga[$ii];
					}
					else {
						if( $ka[$ii] != $sluga[$ii] ){
							$match = FALSE;
						}
					}
				}

				if( $match ){
					$handler = $array[$k];
					foreach( $parametered_args as $parametered_arg ){
						$args[] = $parametered_arg;
					}
					break;
				}
			}
		}

		$return = array( $handler, $args );
		return $return;
	}
}