<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Post
{
	protected $_postPrefix = 'hc-';

	public function __construct()
	{
		$this->_sanitize();
	}

	public function get( $k = NULL )
	{
		if( ! empty($_POST) ){
			$nonce = isset( $_REQUEST['hc_nonce'] ) ? $_REQUEST['hc_nonce'] : '';
			if( function_exists('wp_verify_nonce') ){
				if( ! wp_verify_nonce( $nonce, 'shiftcontroller' ) ){
					die( 'Security check failed' ); 
				}
			}
		}

		$return = array();

		if( ! empty($_POST) ){
			foreach( array_keys($_POST) as $key ){
				if( substr($key, 0, strlen($this->_postPrefix)) != $this->_postPrefix ){
					continue;
				}
				$my_key = substr($key, strlen($this->_postPrefix));
				$return[$my_key] = $this->_fetch_from_array($_POST, $key);
			}
		}

		if( NULL !== $k ){
			$return = array_key_exists($k, $return) ? $return[$k] : NULL;
		}

		return $return;
	}

	protected function _fetch_from_array($array, $index = '')
	{
		if ( ! isset($array[$index])){
			return FALSE;
		}
		return $array[$index];
	}

	protected function _sanitize()
	{
		if (is_array($_POST) AND count($_POST) > 0){
			foreach ($_POST as $key => $val){
				if( substr($key, 0, strlen($this->_postPrefix)) !== $this->_postPrefix ){
					continue;
				}
				$_POST[$this->_cleanInputKeys($key)] = $this->_cleanInputData($val);
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