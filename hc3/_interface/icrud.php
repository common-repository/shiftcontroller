<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_ICrud
{
	public function count( $args = array() );
	public function create( $array );
	public function read( $args = array() );
	public function update( $id, $array );
	public function delete( $id );
	public function deleteAll();

	public function withMeta();
	public function updateMeta( $id, $array );
}
