<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Layout
{
	public $ui, $uri, $request, $topmenu, $auth, $usersQuery, $usersPresenter;

	public function __construct(
		HC3_Hooks $hooks,
		HC3_Ui $ui,
		HC3_Request $request,
		HC3_Ui_Topmenu $topmenu,

		HC3_Uri $uri,
		HC3_Auth $auth,
		HC3_Users_Query $usersQuery,
		HC3_Users_Presenter $usersPresenter
	)
	{
		$this->ui = $ui;
		$this->uri = $uri;
		$this->request = $request;
		$this->topmenu = $topmenu;

		$this->auth = $hooks->wrap( $auth );
		$this->usersQuery = $hooks->wrap( $usersQuery );
		$this->usersPresenter = $hooks->wrap( $usersPresenter );
	}

	public function render( $content )
	{
		$options = $this->topmenu->getChildren();
		$topmenu = array();
		foreach( $options as $item ){
			$link = $this->ui->makeAhref( $item[0], $item[1] )
				->tag('tab-link')
				;

			if( $this->uri->isFullUrl($item[0]) ){
				$link->newWindow();
			}

			$topmenu[] = $link;
		}
		$topmenu = $this->ui->makeList( $topmenu )
			->gutter(1)
			;

		$topmenuLabel = '__Menu__';

		$currentUser = $this->auth->getCurrentUser();
		$currentUserId = $currentUser->getId();

		if( $currentUserId ){
			$currentUser = $this->usersQuery->findById( $currentUserId );
			$currentUserLabel = $this->usersPresenter->presentTitle( $currentUser );
			// $topmenuLabel = $this->ui->makeListInline( array($topmenuLabel, $currentUserLabel) );
			$topmenuLabel = $currentUserLabel;
		}

		$topmenu = $this->ui->makeCollapse( $topmenuLabel, $topmenu )
			->border(FALSE)
			->arrow('&#9776;')
			;

		$topmenu = $this->ui->makeBlock( $topmenu )
			->tag('border', 'bottom')
			->tag('border-color', 'gray')
			->padding(2)
			;

		$out = $this->ui->makeList()
			->add( $topmenu )
			->add( $content )
			;

		return $out;
	}
}