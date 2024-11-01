<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly

$app = 'sh4-rest';

if( isset($_POST[$app . '_submit']) ){
	if( isset($_POST[$app]) ){
		foreach( (array)$_POST[$app] as $key => $value ){
			$option_name = $app . '_' . $key;
			$value = sanitize_text_field( $value );
			update_option( $option_name, $value );
		}
	}

	if( ! isset($_POST[$app]['enabled']) ){
		$k = $app . '_enabled';
		$value = 0;
		update_option( $k, $value );
	}
}

// $this->initOption();

$current = array();
$current['enabled'] = get_option( $app . '_enabled', 1 );
$current['auth_code'] = get_option( $app . '_auth_code', '' );

$startUrl = '/shiftcontroller/v4/';

// spaghetti starts here
?>

<form method="post" action="">
	<?php settings_fields( $app ); ?>
	<?php //do_settings_sections( $this->app ); ?>

	<label>
		<?php $checked = $current['enabled'] ? ' checked' : ''; ?>
		<input type="checkbox" name="<?php echo $app; ?>[enabled]" value="1" <?php echo $checked; ?>/>
		Enable
	</label>
	</br/>

	<label>
		AuthCode<br>
		<input type="text" name="<?php echo $app; ?>[auth_code]" value="<?php echo esc_attr( $current['auth_code'] ); ?>" />
	</label>
	</br/>

	<p>
		<input name="<?php echo $app; ?>_submit" type="submit" class="button-primary" value="Save" />
	</p>
</form>

<?php if( ! $current['enabled'] ) return; ?>


<style>
.sh4-code {
display: block; padding: 1em; border: #999 1px solid; margin-bottom: 1em;
}
</style>

<h3 class="hc-underline">Get Shifts</h3>
<?php
$url = $startUrl . 'shifts';
$fullUrl = get_rest_url( NULL, $url );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Arguments__</strong>
</p>

<p>
<strong>calendar_id</strong> (__Calendar Id__)<br>
<strong>employee_id</strong> (__Employee Id__)<br>
<strong>from</strong> (__From Date__, YYYYMMDD / __From Date Time__, YYYYMMDDHHMM)<br>
<strong>to</strong> (__To Date__, YYYYMMDD / __To Date Time__, YYYYMMDDHHMM)<br>
<strong>status_id</strong> (publish, draft)<br>
</p>

<?php if( class_exists('SH4_CFields_Model') ) : ?>
<?php
$f = ShiftController4::$instance->root();
$cfieldsQuery = $f->make('SH4_CFields_Query');
$cfields = $cfieldsQuery->read();
?>
<?php if( $cfields ) : ?>
<p>
<strong>__Optional Arguments__</strong>
</p>

<p>
<?php foreach( $cfields as $cfield ) : ?>
<em><?php echo $cfield->getName(); ?></em> (<?php echo $cfield->getLabel(); ?>)<br>
<?php endforeach; ?>
</p>
<?php endif; ?>
<?php endif; ?>

<?php
$today = date( 'Ymd' );
?>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>
</pre>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>?calendar_id=11&from=<?php echo $today; ?>
</pre>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>?from=<?php echo $today; ?>0900&to=<?php echo $today; ?>1430&status_id=draft
</pre>

<h3 class="hc-underline">__Get Shift__</h3>
<?php
$url = $startUrl . 'shifts/&lt;id&gt;';
$fullUrl = get_rest_url( NULL, $url );

$url2 = $startUrl . 'shifts';
$fullUrl2 = get_rest_url( NULL, $url2 );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl2; ?>/123
</pre>

<h3 class="hc-underline">__Delete Shift__</h3>
<?php
$url = $startUrl . 'shifts/&lt;id&gt;';
$fullUrl = get_rest_url( NULL, $url );

$url2 = $startUrl . 'shifts';
$fullUrl2 = get_rest_url( NULL, $url2 );
?>

<p>
<strong>
DELETE <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
DELETE <?php echo $fullUrl2; ?>/123
</pre>

<h3 class="hc-underline">__Create Shift__</h3>
<?php
$url = $startUrl . 'shifts';
$fullUrl = get_rest_url( NULL, $url );
?>

<p>
<strong>
POST <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Arguments__</strong>
</p>

<p>
<strong>calendar_id</strong> (__Calendar Id__)<br>
<strong>employee_id</strong> (__Employee Id__)<br>
<strong>start</strong> (__Start Date Time__, YYYYMMDDHHMM)<br>
<strong>end</strong> (__End Date Time__, YYYYMMDDHHMM)<br>
<strong>status_id</strong> (publish, draft)<br>
<strong>conflict</strong> (__Set to 1 to allow creation of shifts with conflicts__) <em>__optional__</em><br>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
POST <?php echo $fullUrl; ?>

calendar_id: 11
employee_id: 22
start: <?php echo $today; ?>0800
end: <?php echo $today; ?>1430
status_id: publish
</pre>

<p>
__If success, it returns the id of the new shift. Otherwise error messages will be given.__
</p>

<h3 class="hc-underline">__Update Shift__</h3>
<?php
$url = $startUrl . 'shifts/&lt;id&gt;';
$fullUrl = get_rest_url( NULL, $url );

$url2 = $startUrl . 'shifts/';
$fullUrl2 = get_rest_url( NULL, $url2 );
if( '/' == substr($fullUrl2, -1) ){
	$fullUrl2 = substr( $fullUrl2, 0, -1 );
}
?>

<p>
<strong>
PUT <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Arguments__</strong>
</p>

<p>
<strong>calendar_id</strong> (__Calendar Id__) <em>__optional__</em><br>
<strong>employee_id</strong> (__Employee Id__) <em>__optional__</em><br>
<strong>start</strong> (__Start Date Time__, YYYYMMDDHHMM) <em>__optional__</em><br>
<strong>end</strong> (__End Date Time__, YYYYMMDDHHMM) <em>__optional__</em><br>
<strong>status_id</strong> (publish, draft) <em>__optional__</em><br>
<strong>conflict</strong> (__Set to 1 to allow creation of shifts with conflicts__) <em>__optional__</em><br>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
PUT <?php echo $fullUrl2; ?>/123
start: <?php echo $today; ?>0900
end: <?php echo $today; ?>1530
</pre>

<pre class="sh4-code">
PUT <?php echo $fullUrl2; ?>/123
employee_id: 22
</pre>

<pre class="sh4-code">
PUT <?php echo $fullUrl2; ?>/123
status_id: publish
</pre>

<h3 class="hc-underline">__Get Available Employees__</h3>
<?php
$url = $startUrl . 'available-employees';
$fullUrl = get_rest_url( NULL, $url );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Arguments__</strong>
</p>

<p>
<strong>calendar_id</strong> (__Calendar Id__) <em>__optional__</em><br>
<em>__default value__</em>: __all shift-type calendars__<br>

<strong>from</strong> (__Start Date Time__, YYYYMMDDHHMM) <em>__optional__</em><br>
<em>__default value__</em>: __beginning of today__<br>

<strong>to</strong> (__End Date Time__, YYYYMMDDHHMM) <em>__optional__</em><br>
<em>__default value__</em>: __one day from the start__<br>

</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>
</pre>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>?from=<?php echo $today; ?>0900&to=<?php echo $today; ?>1430
</pre>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>?from=<?php echo $today; ?>&calendar_id=123
</pre>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>?calendar_id[]=123&calendar_id[]=456
</pre>

<p>
<strong>__Example return__</strong>
</p>

<pre class="sh4-code">
{
  "from": "<?php echo $today; ?>0900",
  "to": "<?php echo $today; ?>1430",
  "employees": [
    {
      "id": "523",
      "title": "Alice",
      "email": "alice@host.local",
      "username": "alice"
    },
    {
      "id": "527",
      "title": "Eve",
      "email": "eve@host.local",
      "username": "eve"
    }
  ]
}
</pre>

<h3 class="hc-underline">__Get All Calendars__</h3>
<?php
$url = $startUrl . 'calendars';
$fullUrl = get_rest_url( NULL, $url );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>
</pre>

<p>
<strong>__Example return__</strong>
</p>

<pre class="sh4-code">
[
    {
      "id": "12",
      "title": "Barista",
      "type": "shift",
      "active": "1"
    },
    {
      "id": "13",
      "title": "Server",
      "type": "shift",
      "active": "0"
    },
    {
      "id": "14",
      "title": "Holidays",
      "type": "timeoff",
      "active": "0"
    }
]
</pre>

<h3 class="hc-underline">__Get All Employees__</h3>
<?php
$url = $startUrl . 'employees';
$fullUrl = get_rest_url( NULL, $url );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl; ?>
</pre>

<p>
<strong>__Example return__</strong>
</p>

<pre class="sh4-code">
[
    {
      "id": "523",
      "title": "Alice",
      "email": "alice@host.local",
      "username": "alice"
    },
    {
      "id": "527",
      "title": "Eve",
      "email": "eve@host.local",
      "username": "eve"
    }
]
</pre>

<h3 class="hc-underline">__Get Calendars For Employee__</h3>
<?php
$url = $startUrl . 'employees/&lt;id&gt;/calendars';
$fullUrl = get_rest_url( null, $url );

$url2 = $startUrl . 'employees';
$fullUrl2 = get_rest_url( null, $url2 );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl2; ?>/123/calendars
</pre>

<h3 class="hc-underline">__Get Employees Eligible For Calendar__</h3>
<?php
$url = $startUrl . 'calendars/&lt;id&gt;/employees';
$fullUrl = get_rest_url( null, $url );

$url2 = $startUrl . 'calendars';
$fullUrl2 = get_rest_url( null, $url2 );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl2; ?>/654/employees
</pre>

<h3 class="hc-underline">__Add Employee To Calendar__</h3>
<?php
$url = $startUrl . 'calendars/&lt;cid&gt;/employees/&lt;eid&gt;';
$fullUrl = get_rest_url( null, $url );

$url2 = $startUrl . 'calendars';
$fullUrl2 = get_rest_url( null, $url2 );
?>

<p>
<strong>
POST <?php echo $url; ?>
</strong>
</p>

<?php
$url = $startUrl . 'employees/&lt;eid&gt;/calendars/&lt;cid&gt;';
$fullUrl = get_rest_url( null, $url );

$url3 = $startUrl . 'employees';
$fullUrl3 = get_rest_url( null, $url3 );
?>

<p>
<strong>
POST <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
POST <?php echo $fullUrl2; ?>/654/employees/123
</pre>

<pre class="sh4-code">
POST <?php echo $fullUrl3; ?>/123/calendars/654
</pre>

<h3 class="hc-underline">__Remove Employee From Calendar__</h3>
<?php
$url = $startUrl . 'calendars/&lt;cid&gt;/employees/&lt;eid&gt;';
$fullUrl = get_rest_url( null, $url );

$url2 = $startUrl . 'calendars';
$fullUrl2 = get_rest_url( null, $url2 );
?>

<p>
<strong>
DELETE <?php echo $url; ?>
</strong>
</p>

<?php
$url = $startUrl . 'employees/&lt;eid&gt;/calendars/&lt;cid&gt;';
$fullUrl = get_rest_url( null, $url );

$url3 = $startUrl . 'employees';
$fullUrl3 = get_rest_url( null, $url3 );
?>

<p>
<strong>
DELETE <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
DELETE <?php echo $fullUrl2; ?>/654/employees/123
</pre>

<pre class="sh4-code">
DELETE <?php echo $fullUrl3; ?>/123/calendars/654
</pre>

<h3 class="hc-underline">__Get Employee__</h3>
<?php
$url = $startUrl . 'employees/&lt;id&gt;';
$fullUrl = get_rest_url( null, $url );

$url2 = $startUrl . 'employees';
$fullUrl2 = get_rest_url( null, $url2 );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl2; ?>/123
</pre>

<h3 class="hc-underline">__Get Employee By User Id__</h3>
<?php
$url = $startUrl . 'users/&lt;id&gt;/employee';
$fullUrl = get_rest_url( null, $url );

$url2 = $startUrl . 'users';
$fullUrl2 = get_rest_url( null, $url2 );
?>

<p>
<strong>
GET <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
GET <?php echo $fullUrl2; ?>/321/employee
</pre>

<h3 class="hc-underline">__Create Employee__</h3>
<?php
$url = $startUrl . 'employees';
$fullUrl = get_rest_url( NULL, $url );
?>

<p>
<strong>
POST <?php echo $url; ?>
</strong>
</p>

<p>
<strong>__Headers__</strong>
</p>

<p>
X-WP-ShiftController-AuthCode: <?php echo $current['auth_code']; ?>
</p>

<p>
<strong>__Arguments__</strong>
</p>

<p>
<strong>title</strong><br>
<strong>user_id</strong> <em>__optional__</em><br>
<strong>description</strong> <em>__optional__</em><br>
</p>

<p>
__Either title or user_id required.__ __If user_id is given, it will link the new employee to this WordPress user.__
__If user_id is given without the title, it will create a new employee with this WordPress user's full name as employee's title and link this employee to this user.__
</p>

<p>
<strong>__Examples__</strong>
</p>

<pre class="sh4-code">
POST <?php echo $fullUrl; ?>

title: James
</pre>

<p>
__If success, it returns the id of the new employee. Otherwise error messages will be given.__
</p>
