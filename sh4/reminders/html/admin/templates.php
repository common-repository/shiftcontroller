<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Reminders_Html_Admin_Templates
{
	public function __construct(
		HC3_Ui $ui,
		HC3_Time $t,
		HC3_Uri $uri,
		HC3_Post $post,

		HC3_Settings $settings,
		SH4_Reminders_Sms $sms,
		SH4_Notifications_Template $notificationsTemplate,

		HC3_Request $request,
		HC3_Ui_Layout1 $layout,
		HC3_Hooks $hooks
		)
	{
		$this->ui = $ui;
		$this->t = $t;
		$this->post = $hooks->wrap( $post );
		$this->settings = $hooks->wrap( $settings );
		$this->sms = $hooks->wrap( $sms );

		$this->notificationsTemplate = $hooks->wrap( $notificationsTemplate );

		$this->uri = $uri;
		$this->layout = $layout;
		$this->request = $request;
		$this->self = $hooks->wrap( $this );
	}

	public function breadcrumbs()
	{
		$ret = array();
		$ret['admin'] = array( 'admin', '__Administration__' );
		$ret['admin/reminders'] = array( 'admin/reminders', '__Reminders__' );
		return $ret;
	}

	public function header()
	{
		$ret = '__Edit Templates__';
		return $ret;
	}

	public function pnames()
	{
		$ret = [
			'reminders_template_subject_daily',
			'reminders_template_subject_weekly',
			'reminders_template_subject_monthly'
		];

		$smsEnabled = $this->sms->isEnabled();
		if( $smsEnabled ){
			$ret[] = 'reminders_template_subject_dailysms';
			$ret[] = 'reminders_template_body_dailysms';
		}

		return $ret;
	}

	public function post()
	{
		$pnames = $this->self->pnames();
		foreach( $pnames as $pname ){
			$this->settings->set( $pname, $this->post->get($pname) );
		}

		$to = 'admin/reminders/templates';
		$msg = '__Settings Updated__';
		$ret = array( $to, $msg );

		return $ret;
	}

	public function postReset()
	{
		$pnames = $this->self->pnames();
		foreach( $pnames as $pname ){
			$this->settings->reset( $pname );
		}

		$to = 'admin/reminders/templates';
		$msg = '__Settings Updated__';
		$ret = array( $to, $msg );

		return $ret;
	}

	public function render()
	{
		$pnames = $this->self->pnames();

		$v = array();
		foreach( $pnames as $pname ){
			$v[ $pname ] = $this->settings->get( $pname );
		}

	// post to
		$slug = $this->request->getSlug();
		$to = $this->uri->makeUrl( $slug );
		$toReset = $this->uri->makeUrl( $slug . '/reset' );

		$smsEnabled = $this->sms->isEnabled();

		// $tags = $this->notificationsTemplate->getTags( $calendar );
		// $tags = $this->notificationsTemplate->getTags( );

		ob_start();
?>

<form action="<?php echo $to; ?>" method="post" accept-charset="utf-8">

<div class="hc-clearfix hc-mxn2">
	<div class="hc-col hc-col-9 hc-px2">
		<div class="hc-mb2">
			<b><u>__Email__</u></b>
		</div>

		<div class="hc-mb2">
			<label>
				<b>__Subject__: __Tomorrow Active Shift Reminder__</b>
				<input type="text" class="hc-block" name="hc-reminders_template_subject_daily" value="<?php echo esc_attr($v['reminders_template_subject_daily']); ?>">
			</label>
		</div>

		<div class="hc-mb2">
			<label>
				<b>__Subject__: __Next Week Active Shift Reminder__</b>
				<input type="text" class="hc-block" name="hc-reminders_template_subject_weekly" value="<?php echo esc_attr($v['reminders_template_subject_weekly']); ?>">
			</label>
		</div>

		<div class="hc-mb2">
			<label>
				<b>__Subject__: __Next Month Active Shift Reminder__</b>
				<input type="text" class="hc-block" name="hc-reminders_template_subject_monthly" value="<?php echo esc_attr($v['reminders_template_subject_monthly']); ?>">
			</label>
		</div>

		<div class="hc-mb2">
			<b>__Each Shift Details__</b>
			<br/>
			__It depends on the shift calendar, the following notification template is used__: <strong>__Email__: __Shift Published__ (__Employee__)</strong>
		</div>

		<?php if( $smsEnabled ) : ?>
			<div class="hc-mb2">
				<b><u>__SMS__</u></b>
			</div>

			<div class="hc-mb2">
				<label>
					<b>__Tomorrow Active Shift Reminder__</b>
					<input type="text" class="hc-block" name="hc-reminders_template_subject_dailysms" value="<?php echo esc_attr($v['reminders_template_subject_dailysms']); ?>">
				</label>
			</div>
		<?php endif; ?>
	</div>

	<div class="hc-col hc-col-3 hc-px2">
		<ul>
			<li>
				<strong>__Template Tags__</strong>
			</li>
			<li>{DATELABEL}</li>
			<li>{EMPLOYEE_ID}</li>
			<li>{EMPLOYEE_NAME}</li>
		</ul>
	</div>
</div>

<p>
<button type="submit" class="button button-primary hc-xs-block">__Save__</button>
</p>

</form>

<form action="<?php echo $toReset; ?>" method="post" accept-charset="utf-8">

<p>
<button type="submit" class="button button-secondary hc-xs-block">&times; __Reset To Defaults__</button>
</p>

</form>

<?php
		$ret = trim( ob_get_clean() );

		$this->layout
			->setContent( $ret )
			->setHeader( $this->self->header() )
			->setBreadcrumb( $this->self->breadcrumbs() )
			;

		$ret = $this->layout->render();
		return $ret;
	}
}