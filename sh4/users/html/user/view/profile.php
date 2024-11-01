<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Users_Html_User_View_Profile
{
	public function __construct(
		SH4_App_Acl $appAcl,

		HC3_Auth $auth,
		HC3_IPermission $permission,
		SH4_App_Query $appQuery,

		HC3_Ui $ui,
		HC3_Ui_Layout1 $layout,

		HC3_Hooks $hooks
	)
	{
		$this->ui = $ui;
		$this->layout = $layout;

		$this->auth = $hooks->wrap( $auth );
		$this->permission = $hooks->wrap( $permission );
		$this->appQuery = $hooks->wrap( $appQuery );
		$this->appAcl = $hooks->wrap( $appAcl );

		$this->self = $hooks->wrap($this);
	}

	public function render()
	{
		$options = $this->self->menu();
		$out = array();
		foreach( $options as $item ){
			list( $href, $label ) = $item;
			$this_menu = $this->ui->makeAhref( $href, $label )
				->tag('tab-link')
				;
			$out[] = $this_menu;
		}
		$out = $this->ui->makeList( $out );

		$this->layout
			->setContent( $out )
			->setBreadcrumb( $this->self->breadcrumb() )
			->setHeader( $this->self->header() )
			// ->setMenu( $this->self->menu() )
			;

		$out = $this->layout->render();
		return $out;
	}

	public function menu()
	{
		$ret = array();

	// if have roles
		if( $this->appAcl->checkUserProfile() ){
			$ret['roles'] = array( 'user/profile/roles', '__My Roles__' );
		}

		if( ! defined('WPINC') ){
			$ret['profile'] = array( 'user/profile/edit', '__Edit My Details__' );
		}

		return $ret;
	}

	public function header()
	{
		$out = '__Profile__';
		return $out;
	}

	public function breadcrumb()
	{
		$return = array();
		// $return['admin'] = array( 'admin', '__Administration__' );
		if( defined('WPINC') && (! is_admin()) ){
			$return['schedule'] = array( '', '&larr; ' . '__Schedule__' );
		}

		return $return;
	}
}