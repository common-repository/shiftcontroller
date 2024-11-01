<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_ExceptionArray extends Exception
{
	protected $errors = array();

	public function __construct( $errors )
	{
		$this->errors = $errors;
	}

	public function getErrors()
	{
		return $this->errors;
	}
}