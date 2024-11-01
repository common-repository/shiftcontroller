<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Ui_Element_Ahref extends HC3_Ui_Abstract_Element
{
	protected $uiType = 'ahref';
	protected $to = NULL;
	protected $actionMode = FALSE;

	public function __construct( $to, $label = NULL )
	{
		$this->to = $to;
		if( $label === NULL ){
			$label = $to;
		}
		parent::__construct( 'a', $label );

		// $title = strip_tags( $label );
		// $title = substr_count( $label, "\n" );

		// $title = str_replace( $tags, "\n", $label );
		// $title = strip_tags( $title );
		// $title = preg_replace( "/(\n){2,}/", "\n", $title );

		$title = $label;

		$tags = array('</div>', '</p>','<br />', '<br>' );
		$title = str_replace( $tags, ' ', $title );

		$tags = array('&gt;', '&lt;' );
		$title = str_replace( $tags, '', $title );

		$title = strip_tags( $title );
		$title = preg_replace( "/(\s){2,}/", " ", $title );

		$this
			->addAttr( 'title', $title )
			;
	}

	public function actionMode( $set = TRUE )
	{
		$this->actionMode = $set;
		return $this;
	}

	public function isActionMode()
	{
		return $this->actionMode;
	}

	public function getTo()
	{
		return $this->to;
	}

	public function newWindow( $set = TRUE )
	{
		$this->addAttr('target', '_blank');
		return $this;
	}
}