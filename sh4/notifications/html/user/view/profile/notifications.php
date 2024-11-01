<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Notifications_Html_User_View_Profile_Notifications
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_Auth $auth,
		HC3_Notificator $notificator
	)
	{
		$this->self = $hooks->wrap($this);

		$this->ui = $ui;
		$this->layout = $layout;

		$this->auth = $hooks->wrap( $auth );
		$this->notificator = $hooks->wrap( $notificator );
	}

	public function render()
	{
		$user = $this->auth->getCurrentUser();

		$form = $this->ui->makeForm(
			'user/profile/notifications',
			$this->self->form( $user )
			);

		$this->layout
			->setContent( $form )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function header()
	{
		$out = '__Notifications__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		$return['profile'] = array( 'user/profile', '__Profile__' );
		return $return;
	}

	public function form( HC3_Users_Model $user )
	{
		$id = $user->getId();

		$on = ! $this->notificator->isOnForUser( $user );

		$inputs = $this->ui->makeList()
			->add( $this->ui->makeInputCheckbox( 'turnoff', '__Turn Off Notifications__', 1, $on ) )
			;

		$buttons = $this->ui->makeInputSubmit( '__Save__' )
			->tag('primary')
			;

		$out = $this->ui->makeList()
			->add( $inputs )
			->add( $buttons )
			;

		return $out;
	}

}