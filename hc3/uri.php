<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_IUri
{
	public function setAssetsPath( $path );
	public function assetsPath( $src );
	public function getSlug();
	public function getParams();
	public function makeUrl( $slug = NULL, $proto = NULL );
	public function baseUrl();
	public function fromUrl( $url );
	public function isFullUrl( $url );
	public function currentUrl();
}

if( ! defined('HC3_URI_NO_PORT') ) define( 'HC3_URI_NO_PORT', true );

class HC3_Uri implements HC3_IUri
{
	protected $_hca = 'hca';
	protected $_hcj = 'hcj';
	// protected $_hcr = 'hcr';
	protected $_hcr = '';
	protected $_hcrValue = NULL;

	protected $_separatorToParams = ':';
	// protected $_separatorToParams = '*';
	protected $_suffixForMulti = '_';
	protected $_separatorForMulti = '|';

	protected $baseUrl = NULL;
	protected $baseParams = array();
	protected $slug = NULL;
	protected $rawParams = array();
	protected $params = array();

	protected $assetsPath = NULL;

	public function __construct()
	{
		$this->fromUrl( $this->currentUrl() );
		if( strlen($this->_hcr) ){
			$this->_hcrValue = mt_rand(1000, 9999);
		}
	}

	public function setAssetsPath( $path )
	{
		$this->assetsPath = $path;
		return $this;
	}

	public function assetsPath( $src )
	{
		$return = $this->assetsPath ? $this->assetsPath : $this->baseUrl();
		return $return;
	}

	public function setSlug( $slug )
	{
		$this->slug = $slug;
		return $this;
	}

	public function getSlug()
	{
		return $this->slug;
	}

	public function getParams()
	{
		return $this->params;
	}

	public function makeUrl( $slug = NULL, $proto = NULL )
	{
		$params = array();
		if( is_array($slug) ){
			list( $slug, $params ) = $slug;
		}

		if( $this->isFullUrl($slug) ){
			return $slug;
		}

		if( $slug == '-referrer-' ){
			if( isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) ){
				$this->fromUrl( $_SERVER['HTTP_REFERER'] );
				$slug = $this->getSlug();
				// $return = $_SERVER['HTTP_REFERER'];
				// return $return;
			}
		}

		$href_params = $this->baseParams;

		$hca_param = NULL;
		if( $slug ){
			$hca_param = $slug;

		// pesist params within the same slug
			if( $slug == $this->slug && ($params !== NULL) ){
				$params = array_merge( $this->params, $params );
			}
		}

	// pesist params within the same slug
		if( (NULL == $slug) && (! $this->slug) ){
			$params = array_merge( $this->params, $params );
		}

		if( $params ){
			$params_string = $this->_buildHcaParams( $params );
			if( strlen($params_string) ){
				$hca_param .= $this->_separatorToParams . $params_string;
			}
		}

		if( $hca_param ){
		// add random to avoid unnecessary caching
			$href_params[$this->_hca] = $hca_param;
			if( $this->_hcrValue ){
				$href_params[$this->_hcr] = $this->_hcrValue;
			}
		}

		$return = $this->baseUrl;
		if( $href_params ){
			$href_params = http_build_query($href_params);
			$href_params = urldecode( $href_params );

			$glue = (strpos($return, '?') === FALSE) ? '?' : '&';
			$return .= $glue . $href_params;
		}

		if( NULL !== $proto ){
// echo "PROTO = '$proto'<br>";
			$starts = array( 'https://', 'http://', '//' );
			foreach( $starts as $prefix ){
				if( substr($return, 0, strlen($prefix)) == $prefix ){
					$return = $proto . substr($return, strlen($prefix));
					break;
				}
			}
		}

		return $return;
	}

	public function baseUrl()
	{
		$return = $this->baseUrl;
		return $return;
	}

	public function fromUrl( $url )
	{
		$purl = parse_url( $url );

		$baseUrl = $purl['scheme'] . '://'. $purl['host'];
		if( isset($purl['port']) && (80 != $purl['port']) ){
			if( FALSE === strpos($purl['host'], ':') ){
				$baseUrl .= ':' . $purl['port'];
			}
		}
		$baseUrl .= $purl['path'];

		$this->baseUrl = $baseUrl;
		$this->baseParams = array();
		// $this->slug = NULL;

		if( isset($purl['query']) && $purl['query']){
			parse_str( $purl['query'], $base_params );

		/* grab our hca */
			if( isset($base_params[$this->_hca]) ){
				// $this->slug = NULL;
				$hca = $base_params[$this->_hca];

			// trim slashes
				$hca = trim($hca, '/');

				$slug = $hca;
				$params = array();

				if( strpos($slug, $this->_separatorToParams) !== FALSE ){
					list( $slug, $params ) = explode($this->_separatorToParams, $slug, 2);
					$params = explode('/', $params);
				}

				$this->slug = $slug;
				$this->rawParams = $params;
				$this->params = $this->_parseHcaParams( $params );
			}

		/* base params */
			unset( $base_params[$this->_hca] );
			$this->baseParams = $base_params;
		}
		else {
			$this->slug = NULL;
		}

		unset( $this->baseParams[$this->_hcj] );

		return $this;
	}

	public function isFullUrl( $url )
	{
		$ret = false;

		if( is_array($url) ){
			$url = array_shift($url);
		}

		if( null === $url ){
			$url = '';
		}

		$prfx = array( 'http://', 'https://', '//', 'webcal://' );
		reset( $prfx );
		foreach( $prfx as $prf ){
			if( substr($url, 0, strlen($prf)) == $prf ){
				$ret = true;
				return $ret;
			}
		}

		if( strpos($url, '.php') !== false ){
			$ret = true;
			return $ret;
		}

		return $ret;
	}

	public function currentUrl()
	{
		$noPort = defined( 'HC3_URI_NO_PORT' ) && HC3_URI_NO_PORT ? true : false;

		$return = 'http';
		if( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on') ){
			$return .= 's';
		}

		$return .= "://";

		$host = '';
		if( isset($_SERVER['HTTP_HOST']) && $_SERVER['SERVER_PORT'] != '80'){
			if( FALSE === strpos($_SERVER['HTTP_HOST'], ':') ){
				$host .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'];
			}
			else {
				$host .= $_SERVER['HTTP_HOST'];
			}
		}
		else {
			$host = $_SERVER['HTTP_HOST'];
		}

		if( $noPort ){
			$pos = strpos( $host, ':' );
			if( false !== $pos ){
				$host = substr( $host, 0, $pos );
			}
		}

		$return .= $host;

		if ( ! empty($_SERVER['REQUEST_URI']) ){
			$return .= $_SERVER['REQUEST_URI'];
		}
		else {
			$return .= $_SERVER['SCRIPT_NAME'];
		}

		$return = urldecode( $return );
		return $return;
	}

	protected function _buildHcaParams( $params )
	{
		$return = array();

		foreach( $params as $k => $v ){
			if( is_array($v) ){
				if( ! $v ){
					continue;
				}
				$k = $k . $this->_suffixForMulti;
				$v = HC3_Functions::glueArray( $v );
				// $v = join($this->_separatorForMulti, $v);
			}
			else {
				if( $v === NULL ){
					continue;
				}
			}
			$return[] = $k . '/' . $v;
		}

		$return = join('/', $return);
		return $return;
	}

	protected function _parseHcaParams( $rawParams )
	{
		$return = array();
		for( $ii = 0; $ii < count($rawParams); $ii = $ii + 2 ){
			if( ! isset($rawParams[$ii + 1]) ){
				break;
			}

			$key = $rawParams[$ii];
			$value = $rawParams[$ii + 1];

			$needArray = (substr($key, -strlen($this->_suffixForMulti)) == $this->_suffixForMulti) ? TRUE : FALSE;
			if( $needArray ){
				$key = substr($key, 0, -strlen($this->_suffixForMulti));
				$value = HC3_Functions::unglueArray( $value );
				// $value = explode($this->_separatorForMulti, $value);
			}

			if( array_key_exists($key, $return) && $needArray ){
				$return[$key] = array_merge( $return[$key], $value );
			}
			else {
				$return[$key] = $value;
			}
		}

		return $return;
	}
}
