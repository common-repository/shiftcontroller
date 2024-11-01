<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Request_
{
	public function initParam( $k, $v );
	public function setParam( $k, $v );
	public function getSlug();
	public function getParams( $withoutDefault = FALSE );

	public function getMethod();
	public function getIpAddress();
	public function getUserAgent();
	public function getCookie( $index );
	public function getReferrer();

	public function isPrintView();
	public function isAjax();
}

class HC3_Request implements HC3_Request_
{
	protected $url = null;
	protected $initParams = array();
	protected $setParams = array();
	public $realSlug = '';
	public $post, $uri;

	public function __construct(
		HC3_Uri $uri,
		HC3_Post $post,

		HC3_UriAction $uriAction
		)
	{
		if( ! defined('WPINC') ){
			$this->_sanitizeGet();
			$this->_sanitizeCookie();
		}

		$method = $this->getMethod();
		if( $method != 'get' ){
			$this->uri = $uriAction;
			if( $referrer = $this->getReferrer() ){
				$uri->fromUrl( $referrer );
				// echo "URI FROM URL '$referrer'<br>";
				// exit;
			}
		}
		else {
			$this->uri = $uri;
		}

		$this->post = $post;
	}

	public function getReferrer()
	{
		$return = NULL;
		if( isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) ){
			$return = $_SERVER['HTTP_REFERER'];
		}
		return $return;
	}

	public function isAjax()
	{
		$return = FALSE;
		if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ){
			$return = TRUE;
		}
		return $return;
	}

	public function initParam( $k, $v )
	{
		$this->initParams[ $k ] = $v;
		return $this;
	}

	public function setParam( $k, $v )
	{
		$this->setParams[ $k ] = $v;
		return $this;
	}

	public function setSlug( $slug )
	{
		$this->uri->setSlug( $slug );
		return $this;
	}

	public function getSlug()
	{
		$hca = $this->post->get('hca');
		if( $hca ){
			$slug = $hca;
		}
		else {
			$slug = $this->uri->getSlug();
		}

		if( null === $slug ){
			$slug = '';
		}

		return $slug;
	}

	public function setRealSlug( $slug )
	{
		$this->realSlug = $slug;
		return $this;
	}

	public function getRealSlug()
	{
		return $this->realSlug;
	}

	public function getParams( $withoutDefault = FALSE )
	{
		$return = $this->uri->getParams();

		if( ! $withoutDefault ){
			$return = array_merge( $this->initParams, $return );
		}

		$return = array_merge( $return, $this->setParams );
		return $return;
	}

	public function isPrintView()
	{
		$return = FALSE;
		$params = $this->getParams();
		if( array_key_exists('print', $params) && ($params['print']) ){
			$return = TRUE;
		}
		return $return;
	}

	public function getMethod()
	{
		$return = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';
		return $return;
	}

	public function getIpAddress()
	{
		$return = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		return $return;
	}

	public function getUserAgent()
	{
		$return = ( ! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
		return $return;
	}

	public function getCookie( $index )
	{
		return $this->_fetch_from_array( $_COOKIE, $index );
	}

	protected function _fetch_from_array($array, $index = '')
	{
		if ( ! isset($array[$index])){
			return FALSE;
		}
		return $array[$index];
	}

	protected function _sanitizeGet()
	{
		if (is_array($_GET) AND count($_GET) > 0){
			foreach ($_GET as $key => $val){
				$_GET[$this->_cleanInputKeys($key)] = $this->_cleanInputData($val);
			}
		}
	}

	protected function _sanitizeCookie()
	{
		if (is_array($_COOKIE) AND count($_COOKIE) > 0){
			unset($_COOKIE['$Version']);
			unset($_COOKIE['$Path']);
			unset($_COOKIE['$Domain']);

			foreach ($_COOKIE as $key => $val){
				$_COOKIE[$this->_cleanInputKeys($key)] = $this->_cleanInputData($val);
			}
		}
	}

	protected function _cleanInputKeys( $str )
	{
		if ( ! preg_match("/^[a-z0-9:_\/\-\~]+$/i", $str)){
			exit('Disallowed Key Characters on: ' . '"' . esc_html($str) . '"' . '<br>');
		}
		return $str;
	}

	protected function _cleanInputData( $str )
	{
		if( is_array($str) ){
			$new_array = array();
			foreach ($str as $key => $val){
				$new_array[$this->_cleanInputKeys($key)] = $this->_cleanInputData($val);
			}
			return $new_array;
		}

		/* We strip slashes if magic quotes is on to keep things consistent
		   NOTE: In PHP 5.4 get_magic_quotes_gpc() will always return 0 and
			it will probably not exist in future versions at all.
		*/
		$need_strip = FALSE;
		if( version_compare(PHP_VERSION, '5.4', '<') && get_magic_quotes_gpc() ){
			$need_strip = TRUE;
		}
		elseif( defined('WPINC') ){
			$need_strip = TRUE;
		}

		if( $need_strip ){
			$str = stripslashes($str);
		}

		// Remove control characters
		$str = HC3_Functions::removeInvisibleCharacters($str);

		// Standardize newlines
		if (strpos($str, "\r") !== FALSE){
			$str = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $str);
		}

		return $str;
	}
}