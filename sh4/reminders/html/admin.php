<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Reminders_Html_Admin
{
	public function __construct(
		HC3_UriAction $uriAction,
		HC3_Uri $uri,
		HC3_Post $post,
		SH4_Reminders_Sms $sms,

		HC3_Settings $settings,
		HC3_Request $request,
		HC3_Ui_Layout1 $layout,
		HC3_Hooks $hooks
		)
	{
		$this->sms = $hooks->wrap( $sms );
		$this->settings = $hooks->wrap( $settings );
		$this->post = $hooks->wrap( $post );
		$this->uriAction = $uriAction;
		$this->uri = $uri;
		$this->layout = $layout;
		$this->request = $request;
		$this->self = $hooks->wrap( $this );
	}

	public function breadcrumbs()
	{
		$ret = array();
		$ret['admin'] = array( 'admin', '__Administration__' );
		return $ret;
	}

	public function header()
	{
		$ret = '__Reminders__';
		return $ret;
	}

	public function menu()
	{
		$ret = array();

		$ret['templates'] = array( 'admin/reminders/templates', '__Edit Templates__' );
		$ret['review'] = array( 'admin/reminders/review', '__Review Reminders__' );
		$ret['sms'] = array( 'admin/reminders/sms', '__SMS__' );

		return $ret;
	}

	public function post()
	{
		$take = array(
			'reminders_setup', 'reminders_daily', 'reminders_weekly', 'reminders_monthly', 'reminders_include',
		);

		if( $this->sms->isEnabled() ){
			$take[] = 'reminders_daily_sms';
		}

		foreach( $take as $k ){
			$v = $this->post->get($k);
			$this->settings->set( $k, $v );
		}

		$return = array( 'admin/reminders', '__Settings Updated__' );
		return $return;
	}

	public function render()
	{
		$values = array();
		$pnames = array(
			'reminders_setup', 'reminders_daily', 'reminders_weekly', 'reminders_monthly', 'reminders_include',
		);

		if( $this->sms->isEnabled() ){
			$pnames[] = 'reminders_daily_sms';
		}

		foreach( $pnames as $pname ){
			$values[$pname] = $this->settings->get($pname);
		}

	// cron link
		$cronParams = array();
		$cronSlug = 'cron';
		// $cronLink = $this->uriAction->makeUrl( array($cronSlug, $cronParams) );
		$cronLink = site_url( '/?hcs=sh4&hca=' . $cronSlug );

	// post to
		$slug = $this->request->getSlug();
		$to = $this->uri->makeUrl( $slug );

		ob_start();
?>

<form action="<?php echo $to; ?>" method="post" accept-charset="utf-8">

<?php if( 1 OR ! $values['reminders_setup'] ) : ?>
<p>
<mark class="hc-p1">__Please Note__</mark>
</p>

<p>
For shifts reminders to work, you'll need to set up a cron job. Cron job is a process that runs periodically at your web server.
Log in to your web hosting control panel and go to Cron Jobs. Add a cron job that will be pulling the following command every day once a day.
</p>

<p>
	<code>
	wget -O /dev/null '<?php echo $cronLink; ?>'
	</code>
</p>

<p>
Alternatively, you can make use of a web cron service (search the web for <i>web cron</i>) and make them pull the following link every day once a day.
</p>

<p>
	<code>
	<?php echo $cronLink; ?>
	</code>
</p>

<?php endif; ?>

<p>
<label>
<input type="checkbox" name="hc-reminders_setup" value="1"<?php if( $values['reminders_setup'] ) : ?> checked<?php endif; ?>/>
__Yes, I've set up the cron job__
</label>
</p>

<?php if( $values['reminders_setup'] ) : ?>
<p>
<label>
<input type="checkbox" name="hc-reminders_daily" value="1"<?php if( $values['reminders_daily'] ) : ?> checked<?php endif; ?>/>
__Tomorrow Active Shift Reminder__
</label>
</p>

<?php if( $this->sms->isEnabled() ) : ?>
<p>
<label>
<input type="checkbox" name="hc-reminders_daily_sms" value="1"<?php if( $values['reminders_daily_sms'] ) : ?> checked<?php endif; ?>/>
__Tomorrow Active Shift Reminder SMS Text__
</label>
</p>
<?php endif; ?>

<p>
<label>
<input type="checkbox" name="hc-reminders_weekly" value="1"<?php if( $values['reminders_weekly'] ) : ?> checked<?php endif; ?>/>
__Next Week Active Shift Reminder__
</label>
</p>

<p>
<label>
<input type="checkbox" name="hc-reminders_monthly" value="1"<?php if( $values['reminders_monthly'] ) : ?> checked<?php endif; ?>/>
__Next Month Active Shift Reminder__
</label>
</p>

<p>
<span class="pw-inline-list">
	<label>
	<input type="radio" name="hc-reminders_include" value="all"<?php if( 'all' == $values['reminders_include'] ) : ?> checked<?php endif; ?>/>
	__Shift__ & __Time Off__
	</label>

	<label>
	<input type="radio" name="hc-reminders_include" value="shift"<?php if( 'shift' == $values['reminders_include'] ) : ?> checked<?php endif; ?>/>
	__Shift__
	</label>

	<label>
	<input type="radio" name="hc-reminders_include" value="timeoff"<?php if( 'timeoff' == $values['reminders_include'] ) : ?> checked<?php endif; ?>/>
	__Time Off__
	</label>
</span>
</p>

<?php endif; ?>

<p>
<?php if( $values['reminders_setup'] ) : ?>
<button type="submit" class="button button-primary button-large hc-xs-block">__Save__</button>
<?php else: ?>
<button type="submit" class="button button-primary button-large hc-xs-block">__Continue__</button>
<?php endif; ?>
</p>

</form>

<?php
		$ret = trim( ob_get_clean() );

		$this->layout
			->setContent( $ret )
			->setHeader( $this->self->header() )
			->setMenu( $this->self->menu() )
			->setBreadcrumb( $this->self->breadcrumbs() )
			;

		$ret = $this->layout->render();
		return $ret;
	}
}