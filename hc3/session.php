<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Session_
{
	public function getFlashdata( $key );
	public function setFlashdata( $key, $value, $append = FALSE );

	public function getUserdata( $key );
	public function setUserdata( $key, $value, $append = FALSE );
	public function unsetUserdata( $key );
}

class HC3_Session implements HC3_Session_
{
	protected $_started = FALSE;

	protected $_prefix = 'hitcode_';
	protected $request = NULL;
	protected $encrypt = NULL;

	protected $encryption_key = NULL;

	protected $sess_encrypt_cookie		= FALSE;
	protected $sess_expiration			= 7200;
	protected $sess_expire_on_close		= FALSE;
	protected $sess_match_ip			= FALSE;
	protected $sess_match_useragent		= FALSE;
	protected $sess_cookie_name			= 'hc3_session';
	protected $cookie_prefix				= '';
	protected $cookie_path				= '';
	protected $cookie_domain				= '';
	protected $cookie_secure				= FALSE;
	protected $sess_time_to_update		= 300;
	protected $flashdata_key				= 'flash';
	protected $time_reference				= 'time';
	protected $userdata					= array();
	protected $now;

	protected $builtin_props = array(
		'session_id',
		'ip_address',
		'user_agent',
		'last_activity',
		'user_data'
		);

	/**
	 * Session Constructor
	 *
	 * The constructor runs the session routines automatically
	 * whenever the class is instantiated.
	 */

	public static function instance()
	{
		static $ret = NULL;
		if( NULL === $ret ){
			$ret = new static;
		}
		return $ret;
	}

	public static function start()
	{
		$sessionId = session_id();
		if( ! $sessionId ){
			$sessionOptions = array();
			// $sessionOptions = array( 'read_and_close' => TRUE );
			@session_start( $sessionOptions );
		}
	}

	public function __construct( $prefix = 'shiftcontroller4' )
	{
		// $this->request = $request;
		$this->_prefix = $prefix;

		$this->encryption_key = md5(__FILE__);

		static::start();

		// Set the "now" time.  Can either be GMT or server time, based on the
		// config prefs.  We use this to set the "last activity" time
		$this->now = $this->_get_time();

		// Set the session length. If the session expiration is
		// set to zero we'll set the expiration two years from now.
		if ($this->sess_expiration == 0){
			$this->sess_expiration = (60*60*24*365*2);
		}

	// Set the cookie name
		// $this->sess_cookie_name = $this->cookie_prefix . $this->sess_cookie_name . '_' . $this->_prefix;
		$this->sess_cookie_name = $this->cookie_prefix . $this->sess_cookie_name;

		// Run the Session routine. If a session doesn't exist we'll
		// create a new one.  If it does, we'll update it.
		if ( ! $this->sess_read()){
			$this->sess_create();
		}
		else {
			$this->sess_update();
		}

		// Delete 'old' flashdata (from last request)
		$this->_flashdata_sweep();

		// Mark all new flashdata as old (data will be deleted before next request)
		$this->_flashdata_mark();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current session data if it exists
	 *
	 * @access	public
	 * @return	bool
	 */
	function sess_read()
	{
		// Fetch the cookie
		$session = array_key_exists($this->sess_cookie_name, $_COOKIE) ? $_COOKIE[$this->sess_cookie_name] : FALSE;

		// No cookie?  Goodbye cruel world!...
		if ($session === FALSE)
		{
			// log_message('debug', 'A session cookie was not found.');
			return FALSE;
		}

		// Decrypt the cookie data
		if( $this->encrypt ){
			$session = $this->encrypt->decode($session);
		}
		else {
			// encryption was not used, so we need to check the md5 hash
			$hash	 = substr($session, strlen($session)-32); // get last 32 chars
			$session = substr($session, 0, strlen($session)-32);

			// Does the md5 hash match?  This is to prevent manipulation of session data in userspace
			if ($hash !==  md5($session.$this->encryption_key)){
				// echo 'The session cookie data did not match what was expected. This could be a possible malicious attempt.';
				$this->sess_destroy();
				return FALSE;
			}
		}

		// Unserialize the session array
		$session = $this->_unserialize($session);

		// Is the session data we unserialized an array with the correct format?
		if ( ! is_array($session) OR ! isset($session['session_id']) OR ! isset($session['ip_address']) OR ! isset($session['user_agent']) OR ! isset($session['last_activity'])){
			$this->sess_destroy();
			return FALSE;
		}

		// Is the session current?
		if (($session['last_activity'] + $this->sess_expiration) < $this->now){
			$this->sess_destroy();
			return FALSE;
		}

		// Session is valid!
		$this->userdata = $session;
		unset($session);

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Write the session data
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_write()
	{
		$this->_set_cookie();
	}

	// --------------------------------------------------------------------

	/**
	 * Create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_create()
	{
		$sessid = '';
		while (strlen($sessid) < 32){
			$sessid .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		// $sessid .= $this->request->getIpAddress();

		$this->userdata = array(
			'session_id'	=> md5(uniqid($sessid, TRUE)),
			// 'ip_address'	=> $this->request->getIpAddress(),
			// 'user_agent'	=> substr($this->request->getUserAgent(), 0, 120),
			'last_activity'	=> $this->now,
			'user_data'		=> ''
			);

		// Write the cookie
		$this->_set_cookie();
	}

	// --------------------------------------------------------------------

	/**
	 * Update an existing session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_update()
	{
		// We only update the session every five minutes by default
		if (($this->userdata['last_activity'] + $this->sess_time_to_update) >= $this->now)
		{
			return;
		}

		// Save the old session id so we know which record to
		// update in the database if we need it
		$old_sessid = $this->userdata['session_id'];
		$new_sessid = '';
		while (strlen($new_sessid) < 32)
		{
			$new_sessid .= mt_rand(0, mt_getrandmax());
		}

		// To make the session ID even more secure we'll combine it with the user's IP
		// $new_sessid .= $this->request->getIpAddress();

		// Turn it into a hash
		$new_sessid = md5(uniqid($new_sessid, TRUE));

		// Update the session data in the session data array
		$this->userdata['session_id'] = $new_sessid;
		$this->userdata['last_activity'] = $this->now;

		// _set_cookie() will handle this for us if we aren't using database sessions
		// by pushing all userdata to the cookie.
		$cookie_data = NULL;

		// Write the cookie
		$this->_set_cookie($cookie_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	function sess_destroy()
	{
		// Kill the cookie
		@setcookie(
					$this->sess_cookie_name,
					// addslashes(serialize(array())),
					addslashes(json_encode(array())),
					($this->now - 31500000),
					$this->cookie_path,
					$this->cookie_domain,
					0
				);

		// Kill session data
		$this->userdata = array();
	}

	public function getUserdata($item)
	{
		if( function_exists('get_user_meta') && function_exists('get_current_user_id') && ($currentUserId = get_current_user_id()) ){
			$prefix = $this->getPrefix();
			$userMetaName = $prefix . $item;
			return get_user_meta( $currentUserId, $userMetaName, true );
		}
		else {
			$my_key = $this->getPrefix() . $item;
			if( isset($_SESSION[$my_key]) ){
				return $_SESSION[$my_key];
			}
			return ( ! isset($this->userdata[$item])) ? FALSE : $this->userdata[$item];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch all session data
	 *
	 * @access	public
	 * @return	array
	 */
	function all_userdata()
	{
		$ret = array();
		if( ! isset($_SESSION) ) return $ret;

		$prefix = $this->getPrefix();

		/* get flash data we store in _SESSION */
		foreach( $_SESSION as $key => $v ){
			if( ! (substr($key, 0, strlen($prefix)) == $prefix) )
				continue;
			$my_key = substr($key, strlen($prefix) );
			$ret[ $my_key ] = $v;
		}

		$parent_ret = $this->userdata;
		$ret = array_merge( $ret, $parent_ret );
		return $ret;
	}

	public function getPrefix()
	{
		$ret = $this->_prefix;

		$isWpAdmin = FALSE;

		if( defined('WPINC') && is_admin() ){
			$isWpAdmin = TRUE;
		}
		else {
			if( isset($_GET['hca']) && ('admin' == substr($_GET['hca'], 0, strlen('admin'))) ){
				$isWpAdmin = TRUE;
			}
		}

		if( $isWpAdmin ){
			$ret .= '_wpadmin_';
		}

		return $ret;
	}

	public function setUserdata( $key, $value, $append = FALSE )
	{
	// use user meta
		if( function_exists('update_user_meta') && function_exists('get_current_user_id') && ($currentUserId = get_current_user_id()) ){
			$prefix = $this->getPrefix();
			$userMetaName = $prefix . $key;
			update_user_meta( $currentUserId, $userMetaName, $value );
		}
		else {
			static::start();
			$prefix = $this->getPrefix();

			$newdata = array( $key => $value );

			$parent_newdata = array();
			if (count($newdata) > 0){
				$parent_newdata = array();
				foreach ($newdata as $key => $val){
					if( ! in_array($key, $this->builtin_props) ){
						$my_key = $prefix . $key;
						if( $append ){
							if( ! isset($_SESSION[$my_key]) )
								$_SESSION[$my_key] = array();
							if( ! is_array($_SESSION[$my_key]) )
								$_SESSION[$my_key] = array( $_SESSION[$my_key] );
							$_SESSION[$my_key][] = $val;
						}
						else {
							$_SESSION[$my_key] = $val;
						}
					}
					else {
						$parent_newdata[ $key ] = $val;
					}
				}
			}

			if( $parent_newdata ){
				if (count($parent_newdata) > 0){
					foreach( $parent_newdata as $key => $val){
						$this->userdata[$key] = $val;
					}
				}
				$this->sess_write();
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a session variable from the "userdata" array
	 *
	 * @access	array
	 * @return	void
	 */
	public function unsetUserdata( $key )
	{
		if( function_exists('delete_user_meta') && function_exists('get_current_user_id') && ($currentUserId = get_current_user_id()) ){
			$prefix = $this->getPrefix();
			$userMetaName = $prefix . $key;
			delete_user_meta( $currentUserId, $userMetaName );
		}
		else {
			static::start();
			$parent_newdata = array();

			if( ! in_array($key, $this->builtin_props) ){
				$my_key = $this->getPrefix() . $key;
				unset($_SESSION[$my_key]);
			}
			else {
				$parent_newdata[ $key ] = $val;
			}

			if( $parent_newdata ){
				foreach ($parent_newdata as $key => $val){
					unset($this->userdata[$key]);
				}
				$this->sess_write();
			}
		}

		return $this;
	}

	public function setFlashdata( $name, $value, $append = FALSE )
	{
	// use transients
		if( function_exists('set_transient') && function_exists('get_current_user_id') && ($currentUserId = get_current_user_id()) ){
			$prefix = $this->getPrefix();
			$prefix = $prefix . $currentUserId . '_';  
			$transientName = $prefix . $name;
			set_transient( $transientName, $value, 60 );
		}
		else {
			$newdata = array( $name => $value );
			foreach( $newdata as $key => $val ){
				$flashdata_key = $this->flashdata_key.':new:'.$key;
				$this->setUserdata( $flashdata_key, $val, $append );
			}
		}

		return $this;
	}

	function getFlashdata( $key )
	{
		static $cache = array();
		if( array_key_exists($key, $cache) ){
			return $cache[$key];
		}

		if( function_exists('get_transient') && function_exists('get_current_user_id') && ($currentUserId = get_current_user_id()) ){
			$prefix = $this->getPrefix();
			$prefix = $prefix . $currentUserId . '_';  
			$transientName = $prefix . $key;
			$ret = get_transient( $transientName );
			delete_transient( $transientName );
		}
		else {
			$flashdata_key = $this->flashdata_key.':old:'.$key;
			$ret = $this->getUserdata($flashdata_key);
		}

		$cache[ $key ] = $ret;
		return $ret;
	}

	// ------------------------------------------------------------------------

	/**
	 * Identifies flashdata as 'old' for removal
	 * when _flashdata_sweep() runs.
	 *
	 * @access	private
	 * @return	void
	 */
	protected function _flashdata_mark()
	{
		$userdata = $this->all_userdata();
		foreach ($userdata as $name => $value)
		{
			$parts = explode(':new:', $name);
			if (is_array($parts) && count($parts) === 2)
			{
				$new_name = $this->flashdata_key.':old:'.$parts[1];
				$this->setUserdata($new_name, $value);
				$this->unsetUserdata($name);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Removes all flashdata marked as 'old'
	 *
	 * @access	private
	 * @return	void
	 */

	protected function _flashdata_sweep()
	{
		$userdata = $this->all_userdata();
		foreach ($userdata as $key => $value){
			if (strpos($key, ':old:')){
				$this->unsetUserdata($key);
			}
		}
	}

	protected function _get_time()
	{
		if (strtolower($this->time_reference) == 'gmt'){
			$now = time();
			$time = mktime(gmdate("H", $now), gmdate("i", $now), gmdate("s", $now), gmdate("m", $now), gmdate("d", $now), gmdate("Y", $now));
		}
		else {
			$time = time();
		}

		return $time;
	}

	// --------------------------------------------------------------------

	/**
	 * Write the session cookie
	 *
	 * @access	public
	 * @return	void
	 */
	function _set_cookie($cookie_data = NULL)
	{
		if (is_null($cookie_data)){
			$cookie_data = $this->userdata;
		}

		// Serialize the userdata for the cookie
		$cookie_data = $this->_serialize($cookie_data);

		if( $this->encrypt ){
			$cookie_data = $this->encrypt->encode($cookie_data);
		}
		else {
			// if encryption is not used, we provide an md5 hash to prevent userside tampering
			$cookie_data = $cookie_data.md5($cookie_data.$this->encryption_key);
		}

		$expire = ($this->sess_expire_on_close === TRUE) ? 0 : $this->sess_expiration + time();
		// Set the cookie
		@setcookie(
			$this->sess_cookie_name,
			$cookie_data,
			$expire,
			$this->cookie_path,
			$this->cookie_domain,
			$this->cookie_secure
			);
	}

	protected function _serialize($data)
	{
		if (is_array($data)){
			foreach ($data as $key => $val){
				if (is_string($val)){
					$data[$key] = str_replace('\\', '{{slash}}', $val);
				}
			}
		}
		else {
			if (is_string($data)){
				$data = str_replace('\\', '{{slash}}', $data);
			}
		}
		// return serialize($data);
		$ret = json_encode( $data );
		return $ret;
	}

	protected function _unserialize($data)
	{
		// $data = @unserialize( $this->strip_slashes($data) );
		$data = @json_decode( $this->strip_slashes($data), true );

		if (is_array($data)){
			foreach ($data as $key => $val){
				if (is_string($val)){
					$data[$key] = str_replace('{{slash}}', '\\', $val);
				}
			}
			return $data;
		}

		return (is_string($data)) ? str_replace('{{slash}}', '\\', $data) : $data;
	}

	function strip_slashes($str)
	{
		if (is_array($str)){
			foreach ($str as $key => $val){
				$str[$key] = $this->strip_slashes($val);
			}
		}
		else {
			$str = stripslashes($str);
		}
		return $str;
	}
}