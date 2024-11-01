<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
interface HC3_Users_IQuery
{
	public function findById( $id );
	public function findByEmail( $email );
	public function findManyById( array $ids );
	public function findAll();
}