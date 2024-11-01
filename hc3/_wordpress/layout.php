<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Layout implements HC3_ILayout
{
	public $enqueuer;

	public function __construct( 
		HC3_Enqueuer $enqueuer
		)
	{
		$this->enqueuer = $enqueuer;
	}

	public function renderPrint( $content )
	{
		$content = '' . $content;

		$content = str_replace( 'hc-table-header-wpadmin', '', $content );
		$content = str_replace( 'hc-table-header', '', $content );

		$body = array();
		$body[] = '<body>';
		$body[] = '<div class="hc-app-container" id="hc3">';
		$body[] = $content;
		$body[] = '</div>';
		$body[] = '</body>';
		$body = implode("\n", $body);

		$head = array();
		$head[] = '<head>';
		$head[] = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
		$head[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

		$styles = $this->enqueuer->getStyles();

		foreach( $styles as $handle => $src ){
			$head[] = '<link rel="stylesheet" type="text/css" id="hc3-style-' . $handle . '" href="' . $src . '">';
		}

		$head[] = '</head>';
		$head = join("\n", $head) . "\n";

		$out = array();
		$out[] = '<!DOCTYPE html>';
		$out[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
		$out[] = $head;
		$out[] = $body;

		if( ! (defined('HC3_DEV_INSTALL') && HC3_DEV_INSTALL) ){
			$out[] = '<script language="JavaScript">';
			$out[] = 'window.print();';
			$out[] = '</script>';
		}

		$out[] = '</html>';

		$out = implode("\n", $out);

		return $out;
	}

	public function render( $content )
	{
		$content = '' . $content;

		$body = array();
		// $body[] = '<body>';
		$body[] = '<div class="wrap">';
		$body[] = '<div class="hc-app-container" id="hc3">';
		$body[] = $content;
		$body[] = '</div><!-- #hc3 -->';
		$body[] = '</div>';
		// $body[] = '</body>';
		$body = implode("\n", $body);

		$head = $this->head();

		$out = array();
		// $out[] = '<!DOCTYPE html>';
		// $out[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
		// $out[] = $head;
		$out[] = $body;
		// $out[] = '</html>';

		$out = implode("\n", $out);
		return $out;
	}

	public function head()
	{
		$head = array();

		// $head[] = '<head>';
		// $head[] = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
		// $head[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

		$scripts = $this->enqueuer->getScripts();
		$styles = $this->enqueuer->getStyles();
// echo "MAKING HEAD";
		foreach( $styles as $handle => $src ){
			// wp_enqueue_style( $handle, $src );
		}

		foreach( $scripts as $handle => $src ){
			// wp_enqueue_script( $handle, $src );
		}

		// $head[] = '</head>';
		// $head = join("\n", $head) . "\n";

		return $head;
	}
}