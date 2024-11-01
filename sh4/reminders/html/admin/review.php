<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
#[AllowDynamicProperties]
class SH4_Reminders_Html_Admin_Review
{
	public function __construct(
		HC3_Ui $ui,
		HC3_Time $t,
		HC3_Uri $uri,
		HC3_Post $post,

		SH4_Reminders_Command $remindersCommand,
		SH4_Reminders_CommandLog $remindersCommandLog,
		SH4_Reminders_Query $remindersQuery,
		SH4_Reminders_QueryLog $remindersQueryLog,

		SH4_Reminders_CommandSms $remindersCommandSms,
		SH4_Reminders_Sms $sms,

		HC3_Users_Presenter $usersPresenter,
		SH4_Employees_Presenter $employeesPresenter,

		HC3_Request $request,
		HC3_Ui_Layout1 $layout,
		HC3_Hooks $hooks
		)
	{
		$this->ui = $ui;
		$this->t = $t;
		$this->post = $hooks->wrap( $post );
		$this->sms = $hooks->wrap( $sms );

		$this->remindersCommand = $hooks->wrap( $remindersCommand );
		$this->remindersCommandSms = $hooks->wrap( $remindersCommandSms );
		$this->remindersCommandLog = $hooks->wrap( $remindersCommandLog );
		$this->remindersQuery = $hooks->wrap( $remindersQuery );
		$this->remindersQueryLog = $hooks->wrap( $remindersQueryLog );

		$this->uri = $uri;
		$this->layout = $layout;
		$this->request = $request;
		$this->self = $hooks->wrap( $this );

		$this->employeesPresenter = $hooks->wrap( $employeesPresenter );
		$this->usersPresenter = $hooks->wrap( $usersPresenter );
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
		$ret = '__Review Reminders__';
		return $ret;
	}

	public function post()
	{
		$type = $this->post->get('type');
		$date = $this->post->get('date');

		$slug = $this->request->getSlug();
		$params = array(
			'date'	=> $date,
			'type'	=> $type
		);

		$to = $this->uri->makeUrl( array($slug, $params) );

		$ret = array( $to, NULL );
		return $ret;
	}

	public function postSend()
	{
		$params = $this->request->getParams();
		$date = isset($params['date']) ? $params['date'] : $this->t->setNow()->formatDateDb();
		$type = isset($params['type']) ? $params['type'] : SH4_Reminders_Model::RANGE_DAILY;

	// reminders
		$reminders = $this->remindersQuery->find( $type, $date );

		if( in_array($type, [SH4_Reminders_Model::RANGE_DAILY_SMS]) ){
			$command = $this->remindersCommandSms;
		}
		else {
			$command = $this->remindersCommand;
		}

		foreach( $reminders as $reminder ){
			if( $reminder->log ){
				$this->remindersCommandLog->delete( $reminder->log );
			}

			$command->send( $reminder );
		}

		$to = 'admin/reminders/review';
		$params = array(
			'date'	=> $date,
			'type'	=> $type
		);
		$to = $this->uri->makeUrl( array($to, $params) );

		$msg = '__Reminders Sent__';
		$ret = array( $to, $msg );
		return $ret;
	}

	public function postClear()
	{
		$params = $this->request->getParams();
		$date = isset($params['date']) ? $params['date'] : $this->t->setNow()->formatDateDb();
		$type = isset($params['type']) ? $params['type'] : SH4_Reminders_Model::RANGE_DAILY;

	// reminders
		$reminders = $this->remindersQuery->find( $type, $date );

		foreach( $reminders as $reminder ){
			if( $reminder->log ){
				$this->remindersCommandLog->delete( $reminder->log );
			}
		}

		$to = 'admin/reminders/review';
		$params = array(
			'date'	=> $date,
			'type'	=> $type
		);
		$to = $this->uri->makeUrl( array($to, $params) );

		$msg = '__Logs Cleared__';
		$ret = array( $to, $msg );
		return $ret;
	}

	public function render()
	{
		$params = $this->request->getParams();
		$date = isset($params['date']) ? $params['date'] : $this->t->setNow()->formatDateDb();
		$type = isset($params['type']) ? $params['type'] : SH4_Reminders_Model::RANGE_DAILY;

	// post to
		$slug = $this->request->getSlug();
		$to = $this->uri->makeUrl( $slug );

		$params = array(
			'date'	=> $date,
			'type'	=> $type
		);
		$toSend = $this->uri->makeUrl( array($slug . '/send', $params) );
		$toClear = $this->uri->makeUrl( array($slug . '/clear', $params) );

	// reminders
		$reminders = $this->remindersQuery->find( $type, $date );

	// reminder logs
		$notSent = array();
		$logs = array();
		foreach( $reminders as $reminder ){
			if( $reminder->log ){
				$logs[ $reminder->log->id ] = $reminder->log;
			}
			else {
				$notSent[] = 1;
			}
 		}

		ob_start();
?>

<?php
// _print_r( $logs );
?>

<div class="hc-mb3">
<form action="<?php echo $to; ?>" method="post" accept-charset="utf-8">
<fieldset class="hc-p1 hc-border">
	<legend>__Filter__</legend>
	<div class="hc-clearfix">
		<div class="hc-col hc-col-3">
			<div class="hc-p2">
				<?php echo $this->ui->makeInputDatepicker( 'date', NULL, $date ); ?>
			</div>
		</div>
		<div class="hc-col hc-col-7">
			<div class="hc-p2">
				<?php
				$types = array(
					SH4_Reminders_Model::RANGE_DAILY		=> '__Daily__',
					SH4_Reminders_Model::RANGE_WEEKLY	=> '__Weekly__',
					SH4_Reminders_Model::RANGE_MONTHLY	=> '__Monthly__',
				);

				if( $this->sms->isEnabled() ){
					$types[ SH4_Reminders_Model::RANGE_DAILY_SMS ] = '__Daily__' . ' ' . '__SMS__';
				}
				?>

				<?php foreach( $types as $k => $label ) : ?>
					<label>
					<input type="radio" name="hc-type" value="<?php echo $k; ?>"<?php if( $k == $type ) : ?> checked<?php endif; ?>/>
					<?php echo $label; ?>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="hc-col hc-col-2">
			<button type="submit" class="button button-secondary hc-block">__Apply Filter__</button>
		</div>
	</div>
</fieldset>
</form>
</div>

<?php if( $reminders ) : ?>

<table class="wp-list-table widefat fixed striped">

<thead>
<tr>
	<td class="hc-col-4">
		__Employee__
	</td>
	<td class="hc-col-4">
		__User__
	</td>
	<td class="hc-col-1">
		__Shifts__
	</td>
	<td class="hc-col-3">
		__Sent__
	</td>
</tr>
</thead>

<tbody>
<?php foreach( $reminders as $r ) : ?>
	<?php
	$toParams = array();
	$toParams[ 'employee' ] = array( $r->employee->getId() );
	$toParams[ 'type' ] = 'list';
	$toParams[ 'groupby' ] = 'none';

	if( SH4_Reminders_Model::RANGE_DAILY == $r->range ){
		$toParams[ 'start' ] = $r->date;
		$toParams[ 'end' ] = $r->date;
	}

	if( SH4_Reminders_Model::RANGE_DAILY_SMS == $r->range ){
		$toParams[ 'start' ] = $r->date;
		$toParams[ 'end' ] = $r->date;
	}

	if( SH4_Reminders_Model::RANGE_WEEKLY == $r->range ){
		$start = $this->t->setDateDb( $r->date )->setStartWeek()->formatDateDb();
		$end = $this->t->setDateDb( $start )->modify('+1 week')->modify('-1 day')->formatDateDb();
		$toParams[ 'start' ] = $start;
		$toParams[ 'end' ] = $end;
	}

	if( SH4_Reminders_Model::RANGE_MONTHLY == $r->range ){
		$start = $this->t->setDateDb( $r->date )->setStartMonth()->formatDateDb();
		$end = $this->t->setDateDb( $start )->modify('+1 month')->modify('-1 day')->formatDateDb();
		$toParams[ 'start' ] = $start;
		$toParams[ 'end' ] = $end;
	}

	$to = 'schedule';
	$detailsLink = $this->uri->makeUrl( array($to, $toParams) );
	?>

	<tr>
		<td>
			<?php echo $this->employeesPresenter->presentTitle( $r->employee ); ?>
			<?php if( in_array($r->range, [SH4_Reminders_Model::RANGE_DAILY_SMS]) ) : ?>
				<?php $phone = $this->sms->getEmployeePhone( $r->employee->getId() ); ?>
				<a href="<?php echo esc_attr($this->uri->makeUrl('admin/reminders/sms')); ?>" title="__Phone Number__" class="hc-block">
					<?php if( $phone ) : ?><?php echo esc_html( $phone ); ?><?php else : ?>__No Phone Number__<?php endif; ?>
				</a>
			<?php endif; ?>
		</td>
		<td>
			<?php if( $r->user ): ?>
				<?php echo $this->usersPresenter->presentTitle( $r->user ); ?>
				<?php if( in_array($r->range, [SH4_Reminders_Model::RANGE_DAILY_SMS]) ) : ?>
				<?php else : ?>
					<div title="__Email__"><?php echo esc_html($r->user->getEmail()); ?></div>
				<?php endif; ?>
			<?php else : ?>
				__N/A__
			<?php endif; ?>
		</td>
		<td>
			<a href="<?php echo $detailsLink; ?>" class="hc-block" target="_blank"><?php echo count( $r->shifts ); ?></a>
		</td>
		<td>
			<?php if( $r->log ) : ?>
				<?php echo $this->t->setDateTimeDb( $r->log->sent_on )->formatDateWithWeekday(); ?> <?php echo $this->t->formatTime(); ?>
			<?php else : ?>
				<mark>__Not Sent__</mark>
			<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>

</table>

<?php if( $notSent ) : ?>
	<form action="<?php echo $toSend; ?>" method="post" accept-charset="utf-8">
		<p>
			<button type="submit" class="button button-primary hc-xs-block">__Send Reminder Messages__</button>
		</p>
	</form>
<?php endif; ?>

<?php if( $logs ) : ?>
	<form action="<?php echo $toClear; ?>" method="post" accept-charset="utf-8">
		<p>
			<button type="submit" class="button button-secondary hc-xs-block">&times; __Clear Logs__</button>
		</p>
	</form>
<?php endif; ?>

<?php else: ?>

	<p>
		__No Reminders__
	</p>

<?php endif; ?>

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