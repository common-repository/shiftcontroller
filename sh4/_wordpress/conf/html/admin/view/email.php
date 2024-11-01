<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Conf_Html_Admin_View_Email
{
	protected $ui = NULL;
	protected $settings = NULL;

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
		$pnames = array( 'email_from', 'email_fromname', 'email_html' );
		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}

		$inputs = array();

		$inputs[] = $this->ui->makeInputText( 'email_from', '__Send Email From Address__', $values['email_from'] );
		$inputs[] = $this->ui->makeInputText( 'email_fromname', '__Send Email From Name__', $values['email_fromname'] );

		$formatOptions = array(
			'1'	=> 'HTML',
			'0'	=> '__Plain Text__',
		);
		$inputs[] = $this->ui->makeInputRadioSet( 'email_html', '__Email Format__', $formatOptions, $values['email_html'] );

		$inputs = $this->ui->makeList( $inputs );

		$out = $this->ui->makeForm(
			'admin/conf/email',
			$this->ui->makeList(
				array( $inputs, $this->ui->makeInputSubmit( '__Save__')->tag('primary') )
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
		$out = '__Email__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		return $return;
	}
}