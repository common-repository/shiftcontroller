<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_CrudFactory implements HC3_ICrudFactory
{
	protected $prefix = 'hc3-';

	public function __construct( $prefix )
	{
		$this->prefix = $prefix;
	}

	public function make( $entity, $multi = TRUE )
	{
		$postType = $this->prefix . $entity;
		$return = new HC3_Crud_Wordpress_CustomPost( $postType );
		return $return;
	}
}
