<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_Admin_View_TurnOnOff
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Enqueuer $enqueuer,

		HC3_Auth $auth,
		HC3_IPermission $permission
		)
	{
		$this->self = $hooks->wrap( $this );
		$this->ui = $hooks->wrap( $ui );

		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap($permission);
		$this->enqueuer = $enqueuer;
	}

	public function render( $return )
	{
		$out = array();
		$out[] = $this->self->renderInput();
		$out = $this->ui->makeListInline( $out )->gutter(1);

		$to = 'admin/notifications/turnonoff';
		$out = $this->ui->makeForm( $to, $out );

		$out = $this->ui->makeBlock( $out )
			->addAttr('class', 'sh4-skip-notifications')
			;

		if( isset($return[3]) ){
			$return[3] = $this->ui->makeList( array($return[3], $out) );
		}
		else {
			$return[3] = $out;
		}

		return $return;
	}

	public function renderInput()
	{
		$return = NULL;

		$currentUser = $this->auth->getCurrentUser();
		$isAdmin = $this->permission->isAdmin( $currentUser );

		if( ! $isAdmin ){
			return $return;
		}

		$this->enqueuer->addScript('notifications', 'sh4/notifications/assets/js/skip-notifications.js?hcver=' . SH4_VERSION);

		$isOff = HC3_Session::instance()->getUserdata('noNotification');
		$return = $this->ui->makeInputCheckbox( 'notifications_turnoff', '__Skip Notifications__', 1, $isOff );

		return $return;
	}
}