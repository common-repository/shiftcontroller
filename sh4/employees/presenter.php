<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface SH4_Employees_IPresenter
{
	public function presentTitle( SH4_Employees_Model $employee );
	public function export( SH4_Employees_Model $model, $withContacts = true );
}

class SH4_Employees_Presenter implements SH4_Employees_IPresenter
{
	public $self, $ui, $settings, $appQuery;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Settings $settings,
		SH4_App_Query $appQuery,
		HC3_Ui $ui
	)
	{
		$this->ui = $ui;
		$this->settings = $hooks->wrap( $settings );
		$this->appQuery = $hooks->wrap($appQuery);
	}

	public function export( SH4_Employees_Model $model, $withContacts = true )
	{
		$ret = array();

		$ret['id'] = $model->getId();
		$ret['title'] = $model->getTitle();
		if( $withContacts ){
			$user = $this->appQuery->findUserByEmployee( $model );
			$ret['email'] = $user ? $user->getEmail() : null;
			$ret['username'] = $user ? $user->getUsername() : null;
		}

		return $ret;
	}

	public function presentDescription( SH4_Employees_Model $employee )
	{
		$return = $employee->getDescription();

		// if( defined('WPINC') ){
			// $return = do_shortcode( $return );
		// }

		return $return;
	}

	public function presentTitle( SH4_Employees_Model $employee )
	{
		$ret = $employee->getTitle();
		$ret = esc_html( $ret );

		$showUserEmployee = $this->settings->get('shifts_show_employee_user');
		if( $showUserEmployee ){
			$user = $this->appQuery->findUserByEmployee( $employee );
			if( $user ){
				$userView = $user->getUsername();
				$userView = esc_html( $userView );
				$ret .= ' (' . $userView . ')';
			}
		}

		if( $employee->isArchived() ){
			$ret .= ' [' . '__Archived__' . ']';
		}

		return $ret;
	}
}