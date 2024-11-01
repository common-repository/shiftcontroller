<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_INotificator
{
	public function queue( HC3_Users_Model $user, $key, $msg );
	public function send();

	public function setOn();
	public function setOff();
	public function isOn();

	public function setOnForUser( HC3_Users_Model $user );
	public function setOffForUser( HC3_Users_Model $user );
	public function isOnForUser( HC3_Users_Model $user );
}

class HC3_Notificator implements HC3_INotificator
{
	protected $_queue = array();
	protected $off = FALSE;

	public $hooks;
	public $self;
	public $email;
	public $settings;
	public $translate;
	public $usersQuery;
	public $auth;

	public function __construct(
		HC3_Hooks $hooks,

		HC3_Post $post,

		HC3_Email $email,
		HC3_Translate $translate,
		HC3_Settings $settings,

		HC3_Users_Query $usersQuery,
		HC3_Auth $auth
		)
	{
		$this->hooks = $hooks;
		$this->self = $hooks->wrap( $this );
		$this->email = $email;

		$this->settings = $settings;
		$this->translate = $hooks->wrap( $translate );
		$this->usersQuery = $hooks->wrap( $usersQuery );
		$this->auth = $hooks->wrap( $auth );
	}

	public function isOn()
	{
		$ret = $this->off ? FALSE : TRUE;
		return $ret;
	}

	public function setOff()
	{
		$this->off = TRUE;
		$this->session()->setUserdata('noNotification', 1);
		return $this;
	}

	public function setOn()
	{
		$this->off = FALSE;
		$this->session()->setUserdata('noNotification', 0);
		return $this;
	}

	public function session()
	{
		return HC3_Session::instance();
	}

	public function queue( HC3_Users_Model $user, $key, $msg )
	{
		$isOff = $this->session()->getUserdata('noNotification');
		if( $isOff ){
			$this->setOff();
		}

		if( $this->off ){
			return $this;
		}

		$to = $user->getEmail();
		if( ! $to ){
			return $this;
		}

		if( ! $this->self->isOnForUser($user) ){
			return $this;
		}

		$key = $to . '-' . $key;
		if( ! isset($this->_queue[$key]) ){
			$this->_queue[$key] = array();
		}

		$this->_queue[$key][] = array( $to, $msg );

		return $this;
	}

	public function send()
	{
		if( $this->off ){
			return $this;
		}

		$log = array();

		$currentUserId = $this->auth->getCurrentUserId();
		$currentUser = $this->usersQuery->findById( $currentUserId );

		foreach( $this->_queue as $key => $msgs ){
// $_start = microtime( true );
			$finalMsg = array();
			foreach( $msgs as $m ){
				list( $to, $msg ) = $m;

				$msg = explode("\n", $msg);
				$subj = array_shift( $msg );
				$msg = join("\n", $msg);

				$finalMsg[] = $msg;
			}
			$finalMsg = join( "<br>\n\n", $finalMsg );

			$finalSubj = $subj;
			if( count($this->_queue[$key]) > 1 ){
				$finalSubj .= ' (' . count($this->_queue[$key]) . ')';
			}

			$finalSubj = $this->translate->translate( $finalSubj );
			$finalMsg =  $this->translate->translate( $finalMsg );

			if( defined('HC3_PROFILER') && HC3_PROFILER ){
				$log[] = array( get_class($this->email) . '->send()', array($to, $finalSubj, $finalMsg) );
			}
			else {
				$this->email->send( $to, $finalSubj, $finalMsg );
			}

// $_duration = microtime( true ) - $_start;
// $_duration = number_format( $_duration, 4 ) . ' sec';
// $_thisMsg = 'email to ' . $to . ' took ' . $_duration;
// echo $_thisMsg . '<br>';
// $_msg[] = $_thisMsg;

			if( defined('HC3_PROFILER') && HC3_PROFILER ){
				$msg = '__Email Sent To__' . ': ' . $to;
				$this->session()->setFlashdata( 'message', $msg, TRUE );
				// $this->log( $log );
			}
		}

// if( $_msg ){
	// exit;
// }

		$this->_queue = array();

		if( $log ){
			$this->log( $log );
		}
	}

	public function log( $logs )
	{
		$now = time();
		$date = date( "F j, Y, g:i a", $now );

		$out = array();

		foreach( $logs as $log ){
			list( $transport, $content ) = $log;

			$out[] = $transport;
			$out[] = $date;

			if( ! is_array($content) ){
				$content = array( $content );
			}
			foreach( $content as $c ){
				$out[] = $c;
			}

			$out[] = "";
		}

		$out = join("\n", $out);

		if( ! defined('HC3_ROOT_PATH') ){
			return;
		}

		$outFile = HC3_ROOT_PATH . '/emaillog.txt';
		$fp = fopen( $outFile, 'a' );
		fwrite( $fp, $out . "\n\n" );
		fclose( $fp );
	}

	public function setOnForUser( HC3_Users_Model $user )
	{
		$key = $this->_settingsKeyForUser( $user, 'on' );

		$this->settings->set( $key, 1 );
		return $this;
	}

	public function setOffForUser( HC3_Users_Model $user )
	{
		$key = $this->_settingsKeyForUser( $user, 'on' );

		$this->settings->set( $key, 0 );
		return $this;
	}

	public function isOnForUser( HC3_Users_Model $user )
	{
		$key = $this->_settingsKeyForUser( $user, 'on' );

		$return = $this->settings->get( $key );
		if( NULL === $return ){
			$return = TRUE;
		}

		return $return;
	}

	protected function _settingsKeyForUser( HC3_Users_Model $user, $pname )
	{
		$userId = $user->getId();

		$return = array();
		$return[] = 'notificator';
		$return[] = 'user';
		$return[] = $userId;

		$return[] = $pname;

		$return = join( '_', $return );

		return $return;
	}
}