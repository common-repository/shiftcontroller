<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_View_Publish
{
	public function __construct(
		HC3_Hooks $hooks,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_Settings $settings
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->settings = $hooks->wrap($settings);
		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$shortcode = 'shiftcontroller4';

		$out = array();

		$shortcodeView = '[' . $shortcode . ']';
		$shortcodeView = $this->ui->makeBlockInline( $shortcodeView )
			->tag('padding', 2)
			->tag('bgcolor', 'silver')
			;
		$shortcodeView = $this->ui->makeLabelled( '__Shortcode__', $shortcodeView );

		$htmlFile = dirname(__FILE__) . '/publish.html.php';
		ob_start();
		require( $htmlFile );
		$shortcodeView = ob_get_contents();
		ob_end_clean();

		$out[] = $shortcodeView;

		$out = $this->ui->makeList( $out );

		$pageIds = HC3_Functions::wpGetIdByShortcode($shortcode);
		if( $pageIds ){
			foreach( $pageIds as $pid => $pcontent ){
				$link = get_permalink( $pid );
				$label = get_the_title( $pid );
				$page = $this->ui->makeAhref( $link, $label )
					->addAttr('target', '_blank')
					;
				$pages[] = $page;
			}
		}
		else {
			$pages[] = '__None__';
		}

		$addNewLink = $this->ui->makeAhref( admin_url('post-new.php'), '__Add New__' )
			->tag('secondary')
			;

		$pages[] = $addNewLink;
		$pages = $this->ui->makeList( $pages );
		$pages = $this->ui->makeLabelled( '__Pages With Shortcode__', $pages );

		$out = $this->ui->makeGrid()
			->add( $out, 8, 12 )
			->add( $pages, 4, 12 )
			;

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			// ->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__Front End__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}