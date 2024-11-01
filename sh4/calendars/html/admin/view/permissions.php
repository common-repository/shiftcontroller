<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Calendars_Html_Admin_View_Permissions
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		SH4_Calendars_Permissions $calendarsPermissions,
		SH4_Calendars_Query $calendarsQuery,
		SH4_Calendars_Presenter $calendarsPresenter
	)
	{
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->cp = $hooks->wrap( $calendarsPermissions );
		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );
		$this->calendarsPermissions = $hooks->wrap( $calendarsPermissions );
	}

	public function render( $id )
	{
		$calendar = $this->calendarsQuery->findById($id);
		$permissionsOptions = $this->calendarsPermissions->options();

		$options = array();
		foreach( $permissionsOptions as $o ){
			$label = array_shift( $o );
			$employeeOption = array_shift( $o );
			$visitorOption = array_shift( $o );
			$employee2Option = array_shift( $o );
			// list( $label, $employeeOption, $visitorOption ) = $o;

			$option = array();
			$option[] = $label;
			$option[] = $employeeOption ? $employeeOption[0] : NULL;
			$option[] = $visitorOption ? $visitorOption[0] : NULL;
			$option[] = $employee2Option ? $employee2Option[0] : NULL;

			$options[] = $option;
		}

		$header = array(
			'action'	=> NULL,
			'employee'	=> '<div class="hc-align-center">' . '__Calendar Employees__' . '</div>',
			'employee2'	=> '<div class="hc-align-center">' . '__Other Employees__' . '</div>',
			'visitor'	=> '<div class="hc-align-center">' . '__Visitor__' . '</div>',
			);

		$rows = array();
		foreach( $options as $k => $conf ){
			list( $label, $kEmployee, $kVisitor, $kEmployee2 ) = $conf;

			$row = array(
				'action'	=> $label,
				'employee'	=> $kEmployee ? $this->ui->makeInputCheckbox( $kEmployee, NULL, 1, $this->cp->get($calendar, $kEmployee) ) : NULL,
				'visitor'	=> $kVisitor ? $this->ui->makeInputCheckbox( $kVisitor, NULL, 1, $this->cp->get($calendar, $kVisitor) ) : NULL,
				'employee2'	=> $kEmployee2 ? $this->ui->makeInputCheckbox( $kEmployee2, NULL, 1, $this->cp->get($calendar, $kEmployee2) ) : NULL,
				);

			$keys = array_keys( $row );
			foreach( $keys as $kk ){
				if( 'action' === $kk ) continue;
				if( isset($row[$kk]) ){
					$row[$kk] = '<label class="hc-block hc-align-center">' . $row[$kk] . '</label>';
				}
			}

			$rows[] = $row;
		}

		$out = $this->ui->makeTable( $header, $rows );

		$buttons = $this->ui->makeInputSubmit( '__Save__')
			->tag('primary')
			;
		$out = $this->ui->makeList( array($out, $buttons) );

		$out = $this->ui->makeForm(
			'admin/calendars/' . $id . '/prm',
			$out
			);

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb($calendar) )
			->setHeader( $this->self->header($calendar) )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header( SH4_Calendars_Model $model )
	{
		$out = '__Permissions__';
		return $out;
	}

	public function breadcrumb( SH4_Calendars_Model $model )
	{
		$calendarId = $model->getId();
		$calendarTitle = $this->calendarsPresenter->presentTitle( $model );

		$return = array();
		$return['admin'] = array( 'admin', '__Administration__' );
		$return['calendars'] = array( 'admin/calendars', '__Calendars__' );
		$return['calendars/edit'] = $calendarTitle;
		return $return;
	}
}
