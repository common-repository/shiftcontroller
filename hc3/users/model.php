<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Users_Model
{
	const STATUS_ACTIVE = 'active';
	const STATUS_SUSPENDED = 'suspended';
	const ROLE_ADMIN = 'admin';

	private $id = NULL;
	private $displayName = NULL;
	private $email = NULL;
	private $username = NULL;

	public function __construct( $id, $username, $email, $displayName )
	{
		$this->id = $id;
		$this->username = $username;
		$this->email = $email;
		$this->displayName = $displayName;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getDisplayName()
	{
		return $this->displayName;
	}
}
