<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
class HC3_Email
{
	protected $emailHtml = 1;
	protected $emailFrom;
	protected $emailFromName;

	public function __construct(
		HC3_Settings $settings
		)
	{
		$this->emailHtml = $settings->get( 'email_html' );
		$this->emailFrom = $settings->get( 'email_from' );
		$this->emailFromName = $settings->get( 'email_fromname' );
	}

	public function send( $to, $subj, $msg )
	{
		if( $this->emailHtml ){
			$msg = nl2br( $msg );
		}
		else {
			$msg = strip_tags( $msg );
		}

		add_filter( 'wp_mail_content_type', array($this, 'set_html_mail_content_type') );
		add_filter( 'wp_mail_charset',		array($this, 'set_charset') ); 

		$headers = array();
		if( strlen($this->emailFrom) ){
			$headers[] = 'From: ' . $this->emailFromName . ' <' . $this->emailFrom . '>';
		}

		@wp_mail( $to, $subj, $msg, $headers );

		remove_filter( 'wp_mail_content_type', array($this, 'set_html_mail_content_type') );
		remove_filter( 'wp_mail_charset',		array($this, 'set_charset') ); 

		return $this;
	}

	public function set_html_mail_content_type()
	{
		$return = $this->emailHtml ? 'text/html' : 'text/plain';
		return $return;
	}

	public function set_charset( $charset )
	{
		$return = 'utf-8';
		return $return;
	}
}