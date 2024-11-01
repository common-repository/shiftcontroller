<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_App_Html_View_User_Pref
{
	protected $ui = NULL;
	protected $settings = NULL;

	public function __construct(
		HC3_Hooks $hooks,

		HC3_Post $post,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,
		HC3_Time $t,

		HC3_Settings $settings
		)
	{
		$this->ui = $ui;
		$this->layout = $layout;
		$this->t = $t;
		$this->post = $hooks->wrap($post);

		$this->settings = $hooks->wrap($settings);
		$this->self = $hooks->wrap($this);
	}

	public function post()
	{
		$take = array(
			'pref_csv_separator',
		);

		$session = HC3_Session::instance();
		foreach( $take as $k ){
			$v = $this->post->get($k);
			$session->setUserdata( $k, $v );
		}

		$ret = array( 'user/profile/pref', '__Settings Updated__' );
		return $ret;
	}

	public function get()
	{
		$values = array();
		$pnames = array(
			'pref_csv_separator' => $this->settings->get('pref_csv_separator'),
		);

		$session = HC3_Session::instance();
		foreach( $pnames as $pname => $v ){
			$values[ $pname ] = $v;
			$v2 = $session->getUserdata( $pname );
			if( $v2 ){
				$values[ $pname ] = $v2;
			}
		}

		$inputs = array();

		$inputs[] = $this->ui->makeInputSelect(
			'pref_csv_separator',
			'__CSV Delimiter__',
			array(
				','	=> ',',
				';'	=> ';',
				),
			$values['pref_csv_separator']
		);

		$inputs = $this->ui->makeList( $inputs );

		$out = $this->ui->makeForm(
			'user/profile/pref',
			$this->ui->makeList(
				array( $inputs, $this->ui->makeInputSubmit('__Save__')->tag('primary') )
				)
			);

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
		$out = '__Preferences__';
		return $out;
	}

	public function breadcrumb()
	{
		$ret = array();
		$ret['profile'] = array( 'user/profile', '__Profile__' );
		return $ret;
	}
}