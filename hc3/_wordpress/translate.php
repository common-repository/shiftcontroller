<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Translate implements HC3_ITranslate
{
	protected $domain = 'hitcode';
	protected $locale = '';
	protected $dir = '';

	public function __construct( $domain, $pluginDir, $locale = '' )
	{
		$this->domain = $domain;
		$this->locale = $locale;

		$langDir = plugin_basename($pluginDir) . '/languages';
		$langFullDir = $pluginDir . '/languages';

		add_filter( 'locale', array($this, 'setWpLocale') );
// echo "LOAD TEXTDOMAIN '$domain', $langDir<br>\n";

		// $load_result = load_plugin_textdomain( $domain, '', $langDir );
		$locale = get_locale();

		$userLocale = get_user_locale();
		if( strlen($userLocale) ){
			$locale = $userLocale;
		}

		$locale = apply_filters( 'plugin_locale', $locale, $domain );

		$mofile = $domain . '-' . $locale . '.mo';
		$fullMofile = $langFullDir . '/' . $mofile;
		$load_result = load_textdomain( $domain, $fullMofile );

		if( ! $load_result ){
			$load_result = load_plugin_textdomain( $domain, '', $langDir );
		}

// echo $load_result ? "SLOADOK<br>\n" : "SLOADFAIL<br>\n";
// exit;

		remove_filter( 'locale', array($this, 'setWpLocale') );

		$this->dir = $langFullDir;
	}

	public function getLocale()
	{
		return $this->locale;
	}

	public function setWpLocale( $locale )
	{
		if( $this->locale ){
			$locale = $this->locale;
		}
		return $locale;
	}

	public function getOptions()
	{
		$return = array();

		$files = HC3_Functions::listFiles( $this->dir, 'file', '.mo' );
		reset( $files );
		foreach( $files as $f ){
			if( substr($f, 0, strlen($this->domain) + 1) != $this->domain . '-' ){
				continue;
			}
			$option = substr( $f, strlen($this->domain) + 1, -3 );
			$return[] = $option;
		}

		return $return;
	}

	public function __( $str )
	{
		return __($str, $this->domain);
	}

	public function _x( $str, $context )
	{
		return _x($str, $context, $this->domain);
	}

	public function _n( $singular, $plural, $count )
	{
		return _n($singular, $plural, $count, $this->domain);
	}

	public function translate( $string )
	{
		$string = "" . $string;
		preg_match_all( '/__(.+)__/U', $string, $ma );

		$replace = array();
		$count = count($ma[0]);
		for( $ii = 0; $ii < $count; $ii++ ){
			$what = $ma[0][$ii];
			$replace[$what] = $what;
		}

		foreach( $replace as $what => $from ){
			$from = substr( $what, 2, -2 );
			$to = $this->__($from);
			$string = str_replace( $what, $to, $string );
		}

		return $string;
	}
}