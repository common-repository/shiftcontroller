<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Enqueuer implements HC3_IEnqueuer
{
	protected $scripts = array();
	protected $styles = array();
	protected $uri = NULL;

	protected $prfx = 'hc3-';

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Uri $uri
		)
	{
		$this->uri = $hooks->wrap($uri);
	}

	public function addScript( $handle, $path )
	{
		wp_enqueue_script( 'jquery' );
		if( substr($handle, 0, strlen($this->prfx)) != $this->prfx ){
			$handle = $this->prfx . $handle;
		}

		$path = $this->_fullPath( $path );
		$this->scripts[ $handle ] = $path;
// echo "ENQUEUEING SCRIPT: '$handle', '$path'<br>";
		wp_enqueue_script( $handle, $path );

		return $this;
	}

	public function addStyle( $handle, $path )
	{
		if( substr($handle, 0, strlen($this->prfx)) != $this->prfx ){
			$handle = $this->prfx . $handle;
		}

		$path = $this->_fullPath( $path );
		$this->styles[ $handle ] = $path;

		wp_enqueue_style( $handle, $path );

		return $this;
	}

	public function getScripts()
	{
		wp_enqueue_script( 'jquery' );
		$return = $this->scripts;
		return $return;
	}

	public function getStyles()
	{
		$return = $this->styles;
		return $return;
	}

	protected function _fullPath( $src )
	{
		if( $this->uri->isFullUrl($src) ){
			return $src;
		}

		if( defined('HC3_DEV_URL') && HC3_DEV_URL && (substr($src, 0, strlen('hc3/')) == 'hc3/') ){
			$src = HC3_DEV_URL . substr($src, strlen('hc3/'));
			return $src;
		}

		$assets_web_dir = $this->uri->assetsPath( $src );

		if( substr($assets_web_dir, -1) != '/' ){
			$test = explode('/', $assets_web_dir);
			$last_part = array_pop( $test );
			if( strpos($last_part, '.') !== FALSE ){
				$assets_web_dir = dirname($assets_web_dir);
			}
		}
		if( substr($assets_web_dir, -1) != '/' ){
			$assets_web_dir = $assets_web_dir . '/';
		}

		$src = $assets_web_dir . $src;
		return $src;
	}
}