<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Users_Html_Anon_View_Login
{
	public function __construct(
		HC3_Hooks $hooks,
		HC3_Uri $uri
	)
	{
		$this->uri = $hooks->wrap( $uri );
	}

	public function render()
	{
		$back = $this->uri->makeUrl();
		$to = wp_login_url( $back );
		// wp_redirect( $to );

		ob_start();
?>

<a href="<?php echo $to; ?>">__Login__</a>

<META http-equiv="refresh" content="0;URL=<?php echo $to; ?>">

<?php
		return ob_get_clean();
	}
}