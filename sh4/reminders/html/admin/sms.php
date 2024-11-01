<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Reminders_Html_Admin_Sms
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Post $post,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_IPermission $permission,

		SH4_Reminders_Sms $sms,
		SH4_App_Query $appQuery,
		SH4_Calendars_Presenter $calendarsPresenter,
		SH4_Calendars_Query $calendarsQuery,

		SH4_Employees_Query $employeesQuery,

		SH4_Employees_Presenter $employeesPresenter,
		HC3_Users_Presenter $usersPresenter,
		HC3_Users_Query $usersQuery
		)
	{
		$this->sms = $hooks->wrap($sms);
		$this->post = $hooks->wrap($post);
		$this->self = $hooks->wrap($this);
		$this->ui = $ui;
		$this->layout = $layout;

		$this->permission = $hooks->wrap($permission);

		$this->employeesQuery = $hooks->wrap( $employeesQuery );

		$this->calendarsQuery = $hooks->wrap( $calendarsQuery );
		$this->calendarsPresenter = $hooks->wrap( $calendarsPresenter );

		$this->appQuery = $hooks->wrap( $appQuery );
		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );
		$this->usersPresenter = $hooks->wrap( $usersPresenter );
		$this->usersQuery = $hooks->wrap( $usersQuery );
	}

	public function menu()
	{
		$ret = array();

		if( $this->sms->isEnabled() ){
			$ret['smssetting'] = array( admin_url('admin.php?page=wp-sms-settings'), 'WP SMS' . ': ' . '__Settings__' );
			$ret['smsoutbox'] = array( admin_url('admin.php?page=wp-sms-outbox'), 'WP SMS' . ': ' . '__Outbox__' );
		}

		return $ret;
	}

	public function post()
	{
		$phones = $this->post->get('phone');

		$error = false;
		foreach( $phones as $employeeId => $phoneNo ){
			$phoneNo = trim( $phoneNo );
			if( ! $phoneNo ) continue;

			$phoneNo = preg_replace( '/\s+/', '', $phoneNo );
			$phoneNo = str_replace( '-', '', $phoneNo );
			$phoneNo = str_replace( '.', '', $phoneNo );
			$phoneNo = str_replace( '(', '', $phoneNo );
			$phoneNo = str_replace( ')', '', $phoneNo );

			if( '+' !== substr($phoneNo, 0, 1) ){
				$error[] = $employeeId;
				continue;
			}

			if( ! ctype_digit(substr($phoneNo, 1)) ){
				$error[] = $employeeId;
				continue;
			}

			if( (strlen($phoneNo) < 8) or (strlen($phoneNo) > 15) ){
				$error[] = $employeeId;
				continue;
			}

			$phones[ $employeeId ] = $phoneNo;
		}

		if( $error ){
			$errored = [];
			foreach( $error as $id ) $errored[] = $phones[$id];
			$msg = '__Please enter phone number in international format__';
			$msg .= ': ' . join( ', ', $errored );
			$ret = array( 'admin/reminders/sms', $msg, true );

			return $ret;
		}

		foreach( $phones as $employeeId => $phoneNo ){
			// $this->sms->setUserPhone( $userId, $phoneNo );
			$this->sms->setEmployeePhone( $employeeId, $phoneNo );
		}

		$ret = array( 'admin/reminders/sms', '__Saved__' );
		return $ret;
	}

	public function render()
	{
		$smsEnabled = $this->sms->isEnabled();

		$entries = $this->appQuery->findAllUsersWithEmployee();
		$listEmployee = $this->employeesQuery->findActive();
		unset( $listEmployee[0] );

		$tableColumns = $this->self->listingColumns();

		$keys = array_keys( $tableColumns );
		$firstKey = array_shift( $keys );

		$tableRows = array();
		// foreach( $entries as $e ){
		foreach( $listEmployee as $e ){
			$row = $this->self->listingCell( $e );
			$tableRows[] = $row;
		}

		$row = [];
		$btn = $this->ui->makeInputSubmit( '__Save__' )->tag('primary');

		$row['phone'] = $btn;

		$tableRows[] = $row;

		$content = $this->ui->makeTable( $tableColumns, $tableRows );
		$content = $this->ui->makeForm( 'admin/reminders/sms', $content );

		$help = '<b>' . '__SMS Text notifications are implemented by WP SMS plugin.__' . '</b>';

		$help2 = '';
		$help2 .= '<p><b><u>__Please enter phone number in international format__</u></b></p>';
		$help2 .= '<p><mark>+[CountryCode][AreaCode][PhoneNumber]</mark></p>';
		$help2 .= '<table class="wp-list-table widefat fixed striped">';
		$help2 .= '<tr><th>__Example__</th><th>__Local Format__</th><th>__International Format__</th></tr>';
		$help2 .= '<tr><td>__US__</td><td>(415) 555-2671</td><td>+1 415 555 2671</td></tr>';
		$help2 .= '<tr><td>__UK__</td><td>020 7183 8750</td><td>+44 20 7183 8750</td></tr>';
		$help2 .= '</table>';

		$content = $this->ui->makeList( [$help, $content, $help2] );

		$this->layout
			->setContent( $content )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function breadcrumb()
	{
		$ret = array();

		$ret['admin'] = array( 'admin', '__Administration__' );
		$ret['admin/reminders'] = array( 'admin/reminders', '__Reminders__' );
		$ret['admin/reminders/sms'] = array( 'admin/reminders/sms', '__SMS__' );

		return $ret;
	}

	public function header()
	{
		$out = '__Employee Phone Numbers__';
		return $out;
	}

	public function listingColumns()
	{
		$ret = array(
			'employee' => '__Employee__',
			'user' => '__WordPress User__',
			'phone' => '__Phone Number__',
			);
		return $ret;
	}

	public function listingCell( SH4_Employees_Model $employee )
	{
		$ret = array();

		$employeeId = $employee->getId();

		if( $employee ){
			$employeeView = $this->employeesPresenter->presentTitle( $employee );
			$href =  'admin/employees/' . $employeeId;
			$employeeView = $this->ui->makeAhref( $href, $employeeView );
		}
		else {
			$employeeView = '__N/A__';
		}
		$ret['employee'] = $employeeView;

		$user = $this->appQuery->findUserByEmployee( $employee );
		if( $user ){
			$userView = $this->usersPresenter->presentTitle( $user );
			$userId = $user->getId();
			$href = get_edit_user_link( $userId );
			$userView = $this->ui->makeAhref( $href, $userView );
		}
		else {
			$userView = '__N/A__';
		}
		$ret['user'] = $userView;

		$val = '';
		$val = $this->sms->getEmployeePhone( $employeeId );
		if( $user && (! $val) ){
			$val = $this->sms->getUserPhone( $userId );
		}

		$input = $this->ui->makeElement('input')
			->addAttr('name', 'hc-phone[' . $employeeId . ']' )
			->addAttr('class', 'hc-field')
			->addAttr('class', 'hc-block')
			->addAttr('class', 'hc-full-width')
			->addAttr('value', $val)
			->addAttr('type', 'text' )
			// ->addAttr('type', 'tel' )
			// ->addAttr( 'pattern', '[+]{1}[0-9]{7,14}' )
			;

		// $input = $this->ui->makeInputText( 'phone[' . $id . ']', null, $val );
		$ret['phone'] = $input;

		return $ret;
	}

	public function listingCell_User( HC3_Users_Model $model )
	{
		$return = array();
		$id = $model->getId();

		$titleView = $this->usersPresenter->presentTitle( $model );

		$href =  get_edit_user_link($id);
		$titleView = $this->ui->makeAhref( $href, $titleView );

		$return['title'] = $titleView;

		$employee = $this->appQuery->findEmployeeByUser( $model );

		// $employeeActions = array();
		if( $employee ){
			$employeeView = $this->employeesPresenter->presentTitle( $employee );
			$href =  'admin/employees/' . $employee->getId();
			$employeeView = $this->ui->makeAhref( $href, $employeeView );
			// $employeeActions[] = array( 'admin/users/' . $id . '/employee/0', NULL, '__Unlink__' );
		}
		else {
			$employeeView = '__N/A__';
		}

		$return['employee'] = $employeeView;

		$val = $this->sms->getUserPhone( $id );
		
		$input = $this->ui->makeElement('input')
			->addAttr('name', 'hc-phone[' . $id . ']' )
			->addAttr('class', 'hc-field')
			->addAttr('class', 'hc-block')
			->addAttr('class', 'hc-full-width')
			->addAttr('value', $val)
			->addAttr('type', 'text' )
			// ->addAttr('type', 'tel' )
			// ->addAttr( 'pattern', '[+]{1}[0-9]{7,14}' )
			;

		// $input = $this->ui->makeInputText( 'phone[' . $id . ']', null, $val );
		$return['phone'] = $input;

		return $return;
	}
}