<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
$shortcode = 'shiftcontroller4';
?>
<p>
With the following shoftcode you can insert your <strong class="hc-bold">Everyone's Schedule</strong> view into a post or a page. 
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block">
[<?php echo $shortcode; ?>]
</code>
</p>

<p>
By default, this view will display the current week shifts calendar for everyone.
If you need, you can adjust it by supplying additional parameters to control the display:
</p>

<div class="hc-mb3">
	<div class="hc-bold">
		route
	</div>

	<div class="hc-ml3">
		<ul>
			<li class="hc-italic">
				schedule (default)
			</li>
			<li>
				Displays everyone's schedule.
			</li>
		</ul>

		<ul>
			<li class="hc-italic">
				myschedule
			</li>
			<li>
				Displays the schedule of the employee which is linked to the current user if any.
			</li>
		</ul>
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">
		type
	</div>

	<div class="hc-ml3">
		<ul class="hc-italic">
			<li>
				week (default)
			</li>
			<li>
				month
			</li>
			<li>
				list
			</li>
			<li>
				report
			</li>
		</ul>
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">
		groupby
	</div>

	<div class="hc-ml3">
		<ul class="hc-italic">
			<li>
				none (default)
			</li>
			<li>
				employee
			</li>
			<li>
				calendar
			</li>
		</ul>
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">start</div>

	<div class="hc-ml3">
		The start date, <em class="hc-italic">yyyymmdd</em>, for example <em class="hc-italic">20180901</em>. If not supplied, it will start from the current date.
		You can also supply <em class="hc-italic">start-week</em>, <em class="hc-italic">start-month</em>, <em class="hc-italic">start-year</em> if you are using <em class="hc-italic">list</em> or <em class="hc-italic">report</em> view.
		Other options: <em class="hc-italic">first-blank</em>, <em class="hc-italic">first-non-blank</em>.
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">end</div>

	<div class="hc-ml3">
		The end date, <em class="hc-italic">yyyymmdd</em>, for example <em class="hc-italic">20180901</em>.
		You can also supply the range with the plus sign, for example <em class="hc-italic">+3 days</em>.
		Applicable when <strong>type</strong> is <em>list</em> or <em>report</em>.
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">time</div>

	<div class="hc-italic">
		From version 4.7.5
	</div>

	<div class="hc-ml3">
		Exact time, <em class="hc-italic">yyyymmddhhmm</em>, for example <em class="hc-italic">202005281430</em>. Also <em class="hc-italic">now</em> can be used to get the current date and time.
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">calendar</div>

	<div class="hc-ml3">
		Calendar id, for example <em class="hc-italic">123</em>. You can find out the id of a calendar in <em class="hc-italic">Administration &gt; Calendars</em>. If not supplied, it will display shifts of all calendars.
		You can also supply several ids separated by comma.
	</div>

	<div class="hc-ml3 hc-mt1">
		Not applicable when <span class="hc-italic">route="myschedule"</span>.
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">
		employee
	</div>

	<div class="hc-ml3">
		Employee id, for example <em class="hc-italic">321</em>. You can find out the id of an employee in <em class="hc-italic">Administration &gt; Employees</em>. If not supplied, it will display shifts of all employees.
		You can also supply several ids separated by comma.
	</div>

	<div class="hc-ml3 hc-mt1">
		Not applicable when <span class="hc-italic">route="myschedule"</span>.
	</div>
</div>

<div class="hc-mb3">
	<div class="hc-bold">
		hideui
	</div>

	<div class="hc-ml3">
		Optionally you can hide certain user interface elements on the front end page. Separate several options by comma. Possible options include:

		<ul class="hc-italic">
			<li>type</li>
			<li>type-month</li>
			<li>type-week</li>
			<li>type-day</li>
			<li>type-list</li>
			<li>type-report</li>
			<li>groupby</li>
			<li>print</li>
			<li>download</li>
			<li>filter</li>
			<li>filter-calendar</li>
			<li>filter-employee</li>
			<li>filter-conflict</li>
			<li>shiftdetails</li>
			<li>bulk</li>
			<li>pagetitle</li>
			<li>pagemenu</li>
			<li>date-nav</li>
			<li>report-duration</li>
			<li>report-qty</li>
		</ul>
	</div>

	<div class="hc-ml3 hc-mt1">
		Not applicable when <span class="hc-italic">route="myschedule"</span>.
	</div>
</div>

<?php if( class_exists('SH4_Pickup_Boot') ) : ?>
<div class="hc-mb3">
	<div class="hc-bold">
		pickup
	</div>

	<div class="hc-ml3">
		<ul>
			<li class="hc-italic">
				1
			</li>
			<li>
				Display shifts with pickup request.
			</li>
		</ul>

		<ul>
			<li class="hc-italic">
				-1
			</li>
			<li>
				Display shifts with no pickup request.
			</li>
		</ul>

		<ul>
			<li class="hc-italic">
				0 (default)
			</li>
			<li>
				Display all shifts.
			</li>
		</ul>
	</div>
</div>
<?php endif; ?>

<div class="hc-mb3">
	<div class="hc-bold">
		conflict
	</div>

	<div class="hc-italic">
		From version 4.9.17
	</div>

	<div class="hc-ml3">
		<ul>
			<li class="hc-italic">1</li>
			<li>
				Display shifts with conflicts.
			</li>
		</ul>

		<ul>
			<li class="hc-italic">-1</li>
			<li>
				Display shifts with no conflicts.
			</li>
		</ul>

		<ul>
			<li class="hc-italic">0 (default)</li>
			<li>
				Display all shifts.
			</li>
		</ul>
	</div>
</div>

<p>
<h3>Examples</h3>
</p>

<p>
Month calendar for September for calendar #12:
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block hc-maroon">
[<?php echo $shortcode; ?> start="20180901" type="month" calendar="12"]
</code>
</p>

<p>
Week calendar for the current week:
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block hc-maroon">
[<?php echo $shortcode; ?> type="week"]
</code>
</p>

<p>
List shifts in the next 3 days:
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block hc-maroon">
[<?php echo $shortcode; ?> type="list" end="+3 days"]
</code>
</p>

<p>
Do not show the calendar filter and the download button:
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block hc-maroon">
[<?php echo $shortcode; ?> hideui="filter-calendar,download"]
</code>
</p>

<p>
Show the current user schedule.
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block hc-maroon">
[<?php echo $shortcode; ?> route="myschedule"]
</code>
</p>

<p>
Display shifts at the current moment.
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block hc-maroon">
[<?php echo $shortcode; ?> type="list" time="now"]
</code>
</p>

<?php if( class_exists('SH4_Pickup_Boot') ) : ?>
<p>
Display shifts with pickup request.
</p>

<p>
<code class="hc-p2 hc-mt1 hc-mb1 hc-border hc-block hc-maroon">
[<?php echo $shortcode; ?> pickup="1"]
</code>
</p>
<?php endif; ?>