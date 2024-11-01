=== ShiftController Employee Shift Scheduling ===
Contributors: Plainware
Tags: shift, employee, staff, rota, volunteer
License: GPLv2 or later
Stable tag: 4.9.69
Requires at least: 4.1
Tested up to: 6.6
Requires PHP: 5.6

Schedule staff and shifts anywhere at anytime online from your WordPress powered website.

== Description ==

ShiftController is a lightweight, easy to use WordPress staff scheduling and rostering, rota planning plugin for any business that needs to manage and schedule employees.

####Keep Organized####
Associate your employees with calendars, configure shift types, assign managers, and keep your staff scheduling under control anywhere at anytime online from your WordPress powered website!

####Escape Schedule Conflicts####
Quickly see and correct any conflicts due to overlapping shifts or timeoffs. Each conflicting entry is highlighted in the schedule calendar so you will not miss it.

####Mobile Friendly####
Responsive design that works perfectly well for iPhone, Android, Blackberry, Windows as well as for desktops, laptops and tablets.

###Pro Version Features###

__Bulk Edit__
The [Bulk Edit](https://www.shiftcontroller.com/bulk-actions/) function lets you change or delete multiple shifts at once.

__Repeat Shifts__
Quickly [create new or repeat existing shifts](https://www.shiftcontroller.com/recurring-shifts/) months ahead.

__Shift Pickup__
With the [Shift Pickup](https://www.shiftcontroller.com/shift-pickup/) module shifts can be marked as requested for pickup, and other employees can pick them up.

__Schedule Templates__
Quickly create new week schedules from [schedule templates](https://www.shiftcontroller.com/schedule-templates/).

__Custom Fields__
[Additional fields](https://www.shiftcontroller.com/custom-fields/) for shifts to keep custom information.

__Schedule Limits__
[Limit total number or duration of shifts](https://www.shiftcontroller.com/schedule-limits/) within a day, a week, or a month.

Please visit [our website](https://www.shiftcontroller.com "WordPress Employee Scheduling") for more info and [get the Premium version now!](https://www.shiftcontroller.com/order/).

== Support ==
Please contact us at https://www.shiftcontroller.com/contact/

Author: Plainware
Author URI: https://www.shiftcontroller.com

== Installation ==

1. After unzipping, upload everything in the `shiftcontroller` folder to your `/wp-content/plugins/` directory (preserving directory structure).

2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Front-end of the plugin.
2. Back-end of the plugin.

== Upgrade Notice ==
The upgrade is simply - upload everything up again to your `/wp-content/plugins/` directory, then go to the ShiftController menu item in the admin panel. It will automatically start the upgrade process if any needed.

== Changelog ==

= 4.9.69 =
* BUG: Critical error when trying to link an employee to a user account.

= 4.9.68 =
* Extended REST API and WordPress API options: added options to get calendars, get and create employees, add and remove employees to/from calendars.

= 4.9.67 =
* Added an option to de-select a field value for Drop Down types of custom fields if it is not set as required. (Pro version).
* Minor fixes

= 4.9.66 =
* BUG: the schedule display crushed if there was a pending time off within the displayed date range.

= 4.9.65 =
* Added a rich-text editor for employee's description.
* Disallowed HTML in employee, calendar and shift time names.

= 4.9.64 =
* Added a setting if to display an abbreviated label in the schedule compact view.

= 4.9.63 =
* Added a setting if to confirm publishing shifts.
* Added 2 new options for "start" parameter of the shortcode: first-blank, first-non-blank.

= 4.9.62 =
* Added an option to let employees unassign themselves from a shift, the shift becomes open after that. (Pro Version).

= 4.9.61 =
* BUG: the "Custom Time" text wasn't translatable in some places.
* BUG: the Print View link wasn't working properly on some front end pages.

= 4.9.60 =
* Several optimizations for database queries that should improve performance.
* BUG: a front end page with route="myschedule" could redirect to everyone's schedule after shift update.

= 4.9.59 =
* Minor fixes

= 4.9.58 =
* Minor fixes

= 4.9.57 =
* BUG: the "Assigned Shift" filter was not working in the schedule view.
* BUG: an error might have appeared after a template shift was deleted.
* Modified the SMS module so that a phone number can be associated directly with the employee, even if there is no a linked user account.

= 4.9.56 =
* BUG: a shift wasn't displayed in the schedule if the shift's employee was later removed from the calendar of the shift.
* Adjusted style of the output to better fit into front-end themes, especially in dark mode.

= 4.9.55 =
* BUG: shift time might be displayed incorrectly on dates when the daylight saving time clock change happened.
* BUG: REST API GET /shiftcontroller/v4/shifts endpoint might work incorrectly under some Wordpress configurations.
* Added a page break in the schedule list print view after each calendar or employee, so each section is printed on a new page.

= 4.9.54 =
* Added new options for the "hideui" shortcode parameter: date-nav, report-duration, report-qty.

= 4.9.53 =
* Bug: changing date option wasn't available for shifts and time off that were full day or many days.

= 4.9.52 =
* Bug: the form error messages were not displayed for form inputs after the first one.
* Bug: previous text might remain in form inputs.

= 4.9.51 =
* Added an option to specify the CSV delimiter in shifts export file. Both default setting in Administration, Date and Time, and personal preference in Profile, Preference.

= 4.9.50 =
* In the schedule day view we extended the end time range to fully display overnight shifts.
* Modified the session code that didn't work properly with action result messages and user preferences in some setups.

= 4.9.49 =
* Added an option to change calendar and employee for a template shift in Schedule Templates module of the Pro version.

= 4.9.48 =
* Added a new REST API end point to find available employees during a certain date and time range.

= 4.9.47 =
* Added a delete shift email notification for employees and managers/admins.

= 4.9.46 =
* BUG: Calendar filter in schedule view didn't work due to error in version 4.9.44.
* BUG: When a shift copied to another employee, the new employee didn't get a notification.

= 4.9.45 =
* BUG: Couldn't create shifts for a selected employee due to error in version 4.9.44.

= 4.9.44 =
* BUG: Shifts of archived employees and calendars were not visible in the schedule view.
* BUG: Custom fields module may have produced a critical error (Pro Version).

= 4.9.43 =
* Minor fixes to ensure compatibility with PHP 8.

= 4.9.42 =
* Minor fixes to display better for RTL languages.

= 4.9.41 =
* BUG: Copy To Another Employee link was visible to users who were not allowed to do this action.
* Minor fixes.

= 4.9.40 =
* Added an option to choose shift, time off or both in when sending reminders.

= 4.9.39 =
* BUG: In Administration, Employees, various actions were not working properly for the first item in the list.
* BUG: footer custom date picker in the schedule view didn't work properly.
* Copy & Change Employee option from the Change Employee screen moved to a separate acton Copy To Another Employee.
* In the shortcode "calendar" parameter can accept negative value to skip specified calendar(s), for example [shiftcontroller4 calendar="-123,-321"]
* Added an option to make a copy of schedule template (Pro Version).
* Added an option to edit time in template shifts in schedule templates (Pro Version).
* Minor fixes.

= 4.9.38 =
* For Schedule Limits module, if an employee has individual limits, they override possible common limits set for all employees (Pro Version).
* Minor fixes.

= 4.9.37 =
* Minor fixes.

= 4.9.36 =
* Minor fixes in upgrade from legacy version 3 module.

= 4.9.35 =
* Added Custom Number of Days option for shift schedule templates module (Pro Version).

= 4.9.34 =
* Include custom fields too when copying a shift to another employee.
* Added new options for the "hideui" shortcode parameter: filter, pagetitle, pagemenu.

= 4.9.33 =
* Added an option to copy shift to a new employee.

= 4.9.32 =
* Added a setting to specify the max end date for iCal feed.
* PHP 8.2 compatibility fixes.
* BUG: The date picker input might not be working for employees.

= 4.9.31 =
* Minor fixes.

= 4.9.30 =
* BUG: If an employee wasn't allowed to view Everyone's Schedule, their date selection form didn't work in the My Schedule page.

= 4.9.29 =
* BUG: If an employee wasn't allowed to edit published shifts, but allowed to create published shifts, they still could unpublish a shift, edit it, then publish back.
* Added an option to make a custom field required (Pro version).
* Added a setting to define the date range when employees can create shifts for themselves. For example, from the start of the current week to the end of next month.

= 4.9.28 =
* Added a shortcode parameter hideui="bulk" to disable bulk actions for shifts (Pro version).

= 4.9.27 =
* Added a setting if to enable slim view for month schedule grouped by calendar or employee.

= 4.9.26 =
* Minor fixes.

= 4.9.25 =
* Minor fixes.

= 4.9.24 =
* BUG: The feed export file had broken columns if different shifts had different set of columns like custom fields or break time start/end.
* Minor fixes.

= 4.9.23 =
* BUG: If shift was with a break, the break time didn't display in notifications.
* BUG: If a custom field contained options with special characters, they didn't display corrrectly.

= 4.9.22 =
* Added an option to query custom fields in the API and export calls.

= 4.9.21 =
* Added bulk actions for employees in admin view.
* BUG: wrong links were given in Profile:iCal Sync, Administration:Shifts Feed, Administration:Reminders, Administration:Shift Pickup Request Reminders.

= 4.9.20 =
* After creating shifts from a week template, it gets back to this week schedule rather than to the current week.
* Don't send a confusing "pick up request cancelled" notification to the original employee of the shift after the shift has been picked up by a new employee. (Pro version).
* Added a setting to display n (3,4,5,6) weeks in the schedule view.
* BUG: managers (not admins) weren't allowed to create shifts from schedule templates. (Pro version).

= 4.9.19 =
* Added the filter detail if any to the print view of the schedule.
* BUG: The order of shifts in schedule templates didn't follow the Week Starts On setting, always starting from Sunday. (Pro version).
* BUG: False conflicts with changing employee of an existing shift, the bug appeared since 4.9.16.

= 4.9.18 =
* A bit of modification for version 4.9.17, when doing pickup and employee assign bulk actions on grouped open shifts, pick only one of the grouped shifts as otherwise it would create overlapping conflicts for the employee. (Pro version).

= 4.9.17 =
* BUG: If open shifts were grouped together in the schedule view, the bulk action process handled only one of them rather than all. (Pro version).
* Added a shortcode parameter to display shifts with or without conflicts.

= 4.9.16 =
* Added "Shifts With Conflicts" filter option for the schedule view, available for logged in users only.
* BUG: It might display "new shift" and "new time off" links in schedule list view for users whose permissions didn't allow that.

= 4.9.15 =
* BUG: There was an error related to timezone settings with PHP 8.1.
* Default shift duration can be selected with half-hour steps (was full hour before).

= 4.9.14 =
* Custom fields for shifts in schedule templates, and they can edited too before making new real shifts from template. (Pro version).
* Shift order in schedule view now also considers the employee sort order.
* BUG: Shift create from REST API might fail with the Pro version.

= 4.9.13 =
* Added bulk actions for calendars in admin view.
* Do not display calendar label in schedule view if only one calendar exists.
* Changed a bit misleading "Shift Type" naming to "Shift Time".
* Added break start/end times to shift export file.

= 4.9.12 =
* BUG: Custom fields were not displayed for calendar managers when creating a shift.
* BUG: Error when displaying a schedule day view for a disabled day of week.
* BUG: When displaying shifts with the same time, their sort order wasn't following the calendars sort order.
* Added 2 week view.

= 4.9.11 =
* BUG: Time reports may have been calculated incorrectly with overnight shifts.

= 4.9.10 =
* BUG: Changes in version 4.9.9 broke the timezone setting in the iCal output.
* BUG: Custom fields were not copied then copying an existing calendar to a new one (Pro version).
* BUG: It didn't allow a valid mobile phone number if it was shorter than 11 digits.
* Custom fields can now be copied from another calendar (Pro version).

= 4.9.9 =
* BUG: Custom fields might not be displayed in the JSON feeds output.
* Added an option to copy settings from an existing calendar when creating a new calendar.
* Added SMS text reminders for employees for the next day shift.
* Minor bug fixes.

= 4.9.8 =
* BUG: Labels of shift types ending in 12:00am (end of day) weren't displayed in the schedule view.
* BUG: Viewers (view only managers) were not able to view shifts of their calendars.
* BUG: 4 week view showed today’s date for all days
* Weekly reminders can now be sent on any day of the previous week, not only the last one.
* Minor bug fixes.

= 4.9.7 =
* Added Danish language.
* Minor bug fixes.

= 4.9.6 =
* BUG: schedule template shifts could not be created if the calendar had only one employee.
* Added {EMPLOYEE_ID} and {EMPLOYEE_NAME} to reminder subject templates.
* Modified Limits module to allow setting limits per specific employee. (Pro Version)
* Modified Limits module to allow min hours or number of shifts. (Pro Version)
* Modified Schedule Templates module to allow selection of available employees for open shifts. (Pro Version)
* Minor bug fixes.

= 4.9.5 =
* BUG: wrong markup in admin config for reminders that might cause the form submit failure on some sites.
* BUG: employees might have less permissions than defined in some configurations

= 4.9.4 =
* BUG: weekly and monthly reminders were resending every day if the cron job ran daily.

= 4.9.3 =
* Added monthly reminders for the Email Reminders module.
* Added Shift Pickup Email Reminders module which sends out daily, weekly or monthly reminders of shifts with pickup request.

= 4.9.2 =
* Added "Change Date" option for shifts.

= 4.9.1 =
* BUG: fixed session and REST errors in Wordpress SiteHealth.

= 4.9.0 =
* Added Email Reminders module.
* BUG: if employees were allowed to edit their shifts, the time change didn't work.

= 4.8.9 =
* Added default schedule view type and group by settings.
* Automatic notifications when changing shift employee: Shift Unpublished for the old employee, and Shift Published for the new one.

= 4.8.8 =
* BUG: An error in session function that lead to double action result messages and caching of form values.

= 4.8.7 =
* Added pickup shortcode parameter to display shifts that have (or have no) pickup request.
* Minor fixes.

= 4.8.6 =
* Added a few more settings for REST API shifts list interface.
* Separate internal schedule preference settings storage for admin area and front end schedule, otherwise it might have produced issues.
* Minor fixes.

= 4.8.5 =
* Added short time formats like 8pm or 8p, no minutes if time is like 8:00pm.
* Don't show shift conflicts for not logged in users.
* BUG: In schedule limits module shifts in the last day of the range were counted incorrectly.

= 4.8.4 =
* Added REST API and WordPress Filters API for shifts.

= 4.8.3 =
* Added an option to change shift type on existing shifts.

= 4.8.2 =
* Added the "year" range in the Schedule Limits module (Pro Version).
* BUG: Schedule Templates module may produce a fatal error after a calendar was deleted.
* For Custom Fields added an option to allow edit for visitors (Pro Version).
* Updated translation files.
* PHP 8 compatibility fixes.
* BUG: draft shifts were not accessible for visitors even if permission settings allowed it.

= 4.8.1 =
* In Schedule Limits added a setting if to take into account premliminary shifts (in the new shift process).
* Enhanced permissions in calendars - to "Create" we also added "Edit" and "Delete" permissions.

= 4.8.0 =
* Schedule Limits module: changed number and duration inputs to allow any number (Pro Version).
* Redesigned the admin New Calendar Manager page to group users by WordPress role to work better with a large number of users.
* Added Swedish translation.

= 4.7.9 =
* Added Schedule Limits module (Pro Version).
* Fixed iCal issues when description and custom fields contained multiple lines.

= 4.7.8 =
* Added Schedule Templates module (Pro Version).
* Added calendar description to iCal export.
* BUG: newlines in comments could break iCal export.

= 4.7.7 =
* Shift types now can have the same start/end times if they have different lunch breaks.

= 4.7.5 =
* Added an option to list shifts for an exact date and time, including current time.
* BUG: The front end page with shortcode didn't properly redirect to login form if an authenticated user is required to view the page.
* BUG: Localized datepicker may have worked incorrectly

= 4.7.4 =
* Added display sort order configuration for calendars and employees.

= 4.7.3 =
* Added radio and checkbox custom fields (Pro Version).

= 4.7.2 =
* BUG: Fixed bugs with false links to new shift/timeoff when the current user isn't allowed to perform these actions.
* Added JSON shifts output feed option (in Administration/Shifts Feed).

= 4.7.1 =
* Optimized week and month schedule views so it should be rendered faster.

= 4.7.0 =
* ShiftController's ownership is changed to plainware.com

= 4.6.2 =
* Added Next/Previous labels for schedule navigation links.
* Added labels for new shift/timeoff links in the schedule view.
* Make new shift/timeoff links visually muted for past dates.
* Added a setting if to disable using of Draft shift status.
* Moved Everyone Schedule / My Schedule to separate links in main menu

= 4.6.1 =
* BUG: "loopback request" error in Wordpress SiteHealth.

= 4.6.0 =
* Added a setting if a conflict is considered for overlapping shifts only in the same calendar.
* BUG: there was a "REST API" error in Wordpress SiteHealth.

= 4.5.9 =
* Added label for textarea custom field in the admin area.
* When creating a shift or assigning new or another employee, unavailable employees are displayed below available ones.
* Added a setting for default shift duration.
* In the admin calendars list view we added the quantity of notifications and permission options enabled for each calendar.
* The schedule view remembers last filtered calendars and employees.

= 4.5.8 =
* Added an option to filter our open shifts from a display, i.e. see assigned shifts only.
* Added a new 4 weeks schedule view.
* Added employee description to the shift details view.
* Added My Profile link to the schedule view on a shortcode front end page.
* Make it compatible with Polylang multi language plugin - if a front end page is set to a language, then we switch to that language.

= 4.5.7 =
* Calendar permission settings were not working correctly for visitors (not logged in users).
* Added the timezone setting.
* BUG: Notifications still sent the shift type title even if it was disabled (instead of the time).

= 4.5.6 =
* Added a permission settings per each calendar if to allow employees from other calendars see shifts in this calendar. It configures the feature added in version 4.5.5.
* BUG: Custom fields were not saved when repeating a shift for multiple days (Pro Version).

= 4.5.5 =
* BUG: Employees could see shifts from other calendars that they are not allowed to participate in.

= 4.5.4 =
* Added a setting to define the time increment for time selectors (5, 10, 15 etc minutes).
* Added a setting to define if the lunch break input is needed.

= 4.5.3 =
* BUG: A calendar manager could not create open published shifts.

= 4.5.2 =
* BUG: Open shifts from different calendars were also grouped which was wrong.
* New setting if to automatically create a pickup request for open published shifts. (Pro Version)
* Disabled rich text/HTML editor for custom fields if edited from front end. (Pro Version)

= 4.5.1 =
* Group multiple open shifts for the same time in the schedule view.
* New setting if employees are allowed to create shifts for themselves with conflicts (overlapping existing shifts).
* BUG: compatibility issue - the date picker might have failed to work with some front end themes.

= 4.5.0 =
* BUG: The weekly repeat function included the current day of the week even if it was unchecked (Pro Version).
* Removed hyphens and underscores from the abbreviated labels if the full labels contained any.

= 4.4.9 =
* In the month schedule view the shift widget now displays abbreviated label of the calendar title or of the employee name, depending on the grouping selected.
* Modified the permissions part so that if an employee can't see others shifts in a calendar, so this employee can't also see others employees names.

= 4.4.8 =
* Added "Every 2 Weeks", "Every 3 Weeks", "Every 4 Weeks" shifts repeat options (Pro Version).
* BUG: The Email From Name setting was not taking effect.
* BUG: Backward schedule navigation button worked incorrectly in the day view.
* BUG: Minor translation related and other fixes.

= 4.4.7 =
* Schedule header with date labels now sticks to the top when scrolling down the schedule view.
* BUG: Another attempt to fix a fatal error in print view in some configurations.

= 4.4.6 =
* Changed calendar description field to a wysiwyg editor. Also WordPress shortcodes are now processed in this field.
* A small change when creating multiple shifts: for example if there are 2 new shifts for 1 Sep and 1 Oct, after submit you return back to 1 Sep calendar, rather jumping forward to 1 Oct.
* BUG: Attempted to fix a fatal error in print view in some configurations.

= 4.4.5 =
* Changed how links to shifts in calendar are displayed. That allows other links in custom fields if any to work properly.
* Schedule navigation links now in the footer too.

= 4.4.4 =
* Custom textarea fields now use rich text/HTML editor (Pro Version).
* Shifts duration summary view now separates shifts and time off.

= 4.4.3 =
* BUG: Wrong URLs if the server address was configured with a port.
* If from the schedule week or month view switching to the day view, for the current week/month it shows today rather than the first day of the week/month.

= 4.4.2 =
* Added an option to select all employees when creating a shift.
* Added "viewers" for calendars - users who can only view shifts.

= 4.4.1 =
* Added a setting to change email sender name and address.
* New notification to all employees when other employee picks up a shift (Pro Version).

= 4.4.0 =
* BUG: Repeat shifts function didn't work after 4.3.9 version update.

= 4.3.9 =
* Modified "X Days On / Y Days Off" automatic repeat option to accept manual entry that enables any repeating shift schedule.
* Internal code change to enable WordPress' apply_filters() function call ShiftController functions.
* BUG: forms with multiple action buttons may not have worked properly in some installations.

= 4.3.8 =
* Edit custom fields in one form rather than save button per each field (Pro Version).
* New notification to manager when an employee picks up a shift (Pro Version).
* Added an option to import existing WordPress users as employees.
* Added an option to select calendars when creating a new employee.

= 4.3.7 =
* BUG: Wrong urls for our plugin if the website was configured for a port other than 80.
* BUG: Multiday shifts were not shown if they begin before the displayed range.
* When repeating a shift with custom fields, these fields get pre-filled with the current values from this shift.
* Added several automatic repeat options (like weekly, biweekly, etc) when creating a new shift (Pro Version).

= 4.3.6 =
* BUG: in 4.3.5 we used a function that is available since WordPress 5.0 that broke setups with earlier versions.

= 4.3.5 =
* BUG: Error when creating multiday shifts when choosing a start date that was not initially appeared on screen.
* Added calendar description to the new shift form.
* Redesigned the multiple days selector so that the checkbox and the date label are stacked rather than shown on one line as sometimes the date got truncated on narrow screens.
* For localized installations prioritize our translations over the ones from the Translate WordPress site as the latter may appear incomplete.
* Added "Toggle On" options when assigning employees to calendars in the admin area.

= 4.3.4 =
* BUG: There was an error when creating a new shift from a front end page with the filter parameter for multiple calendars.
* Added a link to the administration options in the front end page.
* Added "sh4-bulk-form" CSS class for the bulk form so it can be hidden in the front end if needed.

= 4.3.3 =
* Minor code fixes to remove warnings in PHP 7 in iCal exports.
* Added from/to date parameters for shifts feed output.
* BUG: Managers were not able to view or edit custom fields (only admins could).
* BUG: Employees could pickup past shifts.
* BUG: Shift titles on mouseover were without spaces.
* BUG: When in a front end page configured for "My Schedule", after any action on shifts it returned back to "Everyone Schedule".
* BUG: In WP 5.0 time selector was not working in a front end page.
* BUG: There was an error when saving a post with ShiftController shortcode in Gutenberg or some other page builders like Elementor.

= 4.3.2 =
* Added the all week select in the shift repeat and new recurring shift screens. (Pro Version)
* Added French translation.
* Minor fixes to address possible errors with shift break times.
* Minor fix to make sure jQuery is loaded in the front end for WordPress 5.0.
* BUG: custom fields didn't show up in the iCal feed. (Pro Version)

= 4.3.1 =
* Updated the translation file.
* Added the date label when selecting a shift type after calendar view.
* Minor fix in the view when selecting repeating dates for new shifts.
* Added a week number in the schedule week view.
* Added a setting if to send notification emails in HTML or plain text.
* BUG: Employees could delete their published shifts if they were only allowed to create draft shifts.

= 4.3.0 =
* If employees/visitors are not configured to view others shifts in all calendars, then the Everyone Schedule view is not available for employees and visitors.
* BUG: Minor error notice "Undefined index: cfield_details". (Pro Version)
* BUG: Custom fields were not included in email notifications after shift create. (Pro Version)
* Added a setting if to hide hours counters in the schedule view.
* Added the "Toggle Selected" button in the bulk actions form. (Pro Version)

= 4.2.9 =
* Added shifts duration summary per day in the week view.
* BUG: An error occured if WordPress timezone was set to an UTC relative option (like UTC-7 etc).

= 4.2.8 =
* BUG: Creating new shifts from a front end page failed if the "type" shortcode parameter was used.

= 4.2.7 =
* Added a new drop down type for custom fields. (Pro Version)
* Custom fields are now also included in the iCal export. (Pro Version)
* BUG: Shifts view permissions may be processed incorrectly for employees' iCal export.

= 4.2.6 =
* BUG: Schedule download didn't work properly in WordPress front end.
* BUG: After reschedule notification emails still displayed old time.
* BUG: Error in the iCal export feed for employees.
* BUG: It didn't allow to update a custom field if there was already another one with the same label in another calendar. (Pro Version)
* BUG: Overlap conflicts function may show false alerts sometimes.
* Schedule day view now shows 7 days at once.

= 4.2.5 =
* Optimizations to improve speed for heavy schedules with many employees and shifts.

= 4.2.4 =
* BUG: "Not allowed" error when trying to create new open shifts.

= 4.2.3 =
* BUG: Date labels were not visible in the print view.
* BUG: Calendar type (shift/timeoff) was reset after editing a calendar.

= 4.2.2 =
* Added the description field for employees which is also displayed when creating shifts or changing employees.
* BUG: The shortcode parameter route="myschedule" doesn't bring up the correct view.
* BUG: The repeat function didn't correctly added shift lunch breaks (Pro Version).

= 4.2.1 =
* BUG: the shifts were not showing in the week/month view for the last day for the week/month.

= 4.2.0 =
* Redesigned the new shift process so that shifts can be created for multiple employees at once.
* Shortcode can now show the My Schedule page, the shifts of the currently logged in user.
* Added a shortcode parameter to disable the link to the shift details (hideui="shiftdetails").
* Custom fields added to notification templates (Pro Version).
* BUG: it wasn't allowing to create a lunch break for overnight shifts, like shift 11pm-3am and break 1am-2am.

= 4.1.6 =
* Added the day shifts calendar view.
* Added a setting to specify min and max time, which is convienient if you don't need 24 hours.
* When creating new shifts, the initial visible dates calendar extended to 4 weeks.
* BUG: a date wasn't preselected in the Select Dates view when adding a shift from calendar (Pro Version).
* BUG: bulk action form to change employee was displayed even if it wasn't allowed for the current user (Pro Version).
* BUG: filtering for "open shifts" was not working in the schedule view.

= 4.1.5 =
* Added several shortcode parameters to configure the front end view.
* BUG: lunch break was not removed if the checkbox was unchecked when changing shift time.

= 4.1.4 =
* BUG: error when an employee tried to create own shift even if it was allowed.

= 4.1.3 =
* BUG: custom fields added to the export download did not work properly if multiple calendars were using custom fields. (Pro Version)
* Added a function to change or assign employee for multiple shifts at once. (Pro Version)

= 4.1.2 =
* Custom fields are now added to the export download file too. (Pro Version)
* Updated JavaScript files to help avoid conflicts with other plugins and themes.

= 4.1.1 =
* Added a setting for a calendar if it is for time off. This lets to create 2 separate buttons for new shifts and time off. Also the shifts in the time off calendars are not counted for the totals results in the schedule view.
* Added a check to avoid shift types with duplicated start and end times.
* BUG: Timezone was not properly set in the iCal feed.
* BUG: Full day shift type title was not shown in the schedule view.

= 4.1.0 =
* Added custom fields module for the Pro addon.
* BUG: error when creating a new shift type (on a site with PHP 7.1).
* BUG: error when creating a shift for a calendar which had only one, custom time shift type.

= 4.0.8 =
* Added shift detailed information on mouse over in month schedule view.
* Added CSV feed of upcoming shifts (in Administration, Upcoming Shifts Feed), it can be used to export shifts to other applications.
* BUG: error after trying to change employee (appeared since 4.0.7).

= 4.0.7 =
* Added an option to create multiple open shifts at once.
* Display current timezone in the iCal details page.
* Added links for iCal feeds per employee.
* Added an option to skip notification emails on shift create or update.
* Added an option to hide certain days of the week.
* Added a description field for calendars, the calendar description is displayed in the shift detailed view.
* BUG: several calendar permission settings might work incorrectly.

= 4.0.6 =
* Fixed a few translation issues.
* Added a configuration if to show shift type title in the calendar.
* BUG: Time off were not imported if upgrading from v3.

= 4.0.5 =
* Enabled HTML editor for email notification templates.
* BUG: Fatal error when deleting a calendar.
* BUG: Language file wasn't get loaded properly.

= 4.0.4 =
* Added an option to turn off/on email notifications per user.
* Modified the admin schedule view so it's a bit faster now, not reloading the whole page after shift update.
* BUG: The repeat action wasn't creating shifts properly (Pro version).
* BUG: The shifts on the last day of the week were not shown in the week view.
* BUG: The shift type title were not recognized in the calendar view for overnight shifts.
* A slight visual adjustment in the calendar view to make grid borders a bit more prominent.

= 4.0.3 =
* Added schedule month view.
* Added an option to create custom time shifts.
* Added an option to edit shift time.
* Added duration column for the CSV/Excel download file.
* Added the shift detailed view window.
* BUG: Upgrade from v.3 failed if there were several employees with the same name.

= 4.0.2 =
* BUG: Upgrade from v.3 didn't work properly.

= 4.0.1 =
* Added the shift pickup module for the Pro addon.

= 4.0.0 =
* A new major update.

= 3.2.4 =
* Fixed the ical feed that might have failed with certain timezones.
* Minor code updates and fixes.

= 3.2.3 =
* Fixed the non working Shift Templates button in the shift edit form.
* Minor code updates and fixes.

= 3.2.2 =
* Removed potentially vulnerable own copy of PHPMailer library.

= 3.2.1 =
* In the admin view added filter options: filter by status and by type (shift/timeoff).

= 3.2.0 =
* BUG: iCal sync link was not working for some devices.
* BUG: timeoff list didn't show if grouping by location was set as a default view.
* BUG: setup failed if one of WordPress roles contained spaces.
* Added a configuration setting if to send notification when a released shift is picked up.

= 3.1.9 =
* In the "Shift available" notification after shift release removed the old employee name that was confusing.
* Added a global BCC field to send copies of all automatic notifications.

= 3.1.8 =
* User can now save the current calendar view configuration as default.
* Pro: shift comments are added to the iCal export.
* Pro: when a shift is realeased, notification can be sent to all staff members.
* Added French language.
* Minor code updates.

= 3.1.7 =
* Added Danish, German and Dutch languages.
* Minor code updates.

= 3.1.6 =
* BUG: certain actions were giving a 404 error if the admin panel was used in the front end with a shortcode.

= 3.1.5 =
* BUG: If ShiftController was activated but not yet setup, editing user accounts in WordPress gave error.
* Minor code updates.

= 3.1.4 =
* BUG: For iCal output if a shift had a break it was giving a wrong end time.
* BUG: Employees can not release shifts from their control panel.
* Minor PHP compatibility fixes

= 3.1.3 =
* Making "add" links appear constantly rather than on mouse over that caused issues on several platforms.
* BUG: Preferred availability setting could give a fault conflict alert.

= 3.1.2 =
* BUG: fixed the "range" shortcode parameters like "2 weeks" or "8 days" after they stopped working properly in 3.1.0.
* BUG: the shifts copy function didn't make use of the selected date, copying just to the next week.
* Not grouped month calendar is displayed in detailed view.

= 3.1.1 =
* BUG: it was not possible to click on an open shift in the "group by location" view.

= 3.1.0 =
* Added a configurable option for employees to view draft shifts, create and edit shifts.
* Added an option to copy shifts from a certain week (or month) to another week, so this feature can be used as a sort of schedule templates.
* Added an option to disable certain days of the week. So for example if you don't work on Saturdays and Sundays, it will not show them in the calendar leaving more screen space for work days.
* Colors for locations can now be manually picked rather than assigned automatically.
* Added the day view with timebar for a better visual overview.
* Changed CSS and font icons libraries to greatly reduce CSS and icon files sizes - faster speed and smaller distrib size.
* Employees can edit their own availability preferences if allowed by the admin.
* BUG: If the shortcode is set to "by=staff" or "by=location" then the front end can not change to the view without grouping by.

= 3.0.9 =
* BUG: The "Disable Email" setting was not taking effect.
* BUG: Ajax actions didn't work in the admin panel for https websites.

= 3.0.8 =
* BUG: Appeared after 3.0.7 after adding the shift break option. When entering a shift and the shift end time is the next day, error "The break should not be longer than the shift itself" was returned.

= 3.0.7 =
* Added shift breaks option, the duration of a break is not counted toward the total hours worked.
* Following introduction of shift breaks, timeoff icon changed: coffee is for lunch breaks now, timeoff is marked with the away icon.
* Added reports page to display number of shifts and time worked.
* BUG: Print view was corrupted when clicking the printer icon button.
* Sync users from WordPress with their display name rather than first/last name
* Replaced JavaScript timepicker by a regular dropdown because it was causing too many compatibility issues.
* Added location change option in the bulk edit form [Pro].
* Redesigned Ajax calls that should greatly improve the load speed for many actions.

= 3.0.6 =
* BUG: The start and end time inputs were not working when opening the bulk edit form in the Shift Series tab.
* BUG: The delete action didn't work in the bulk edit form in the Shift Series tab.
* BUG: After the delete action in the With All bulk edit form the calendar view was not properly refreshed.
* BUG: Error if filter the shifts by location in the calendar.

= 3.0.5 =
* Added the iCal export option (to Google Calendar or any other application capable of receiving iCal feed).
* Moved the Users menu under Configuration.
* BUG: Fixed the print view in Chrome.

= 3.0.4 =
* BUG: The link to the WP page to edit a user account was not working.
* BUG: When in the shortcode by="location" is used the logged in user cannot see their own shifts.
* BUG: Setup on new installs might fail under certain configurations.
* Now comments are added to notifications emails too [Premium].
* BUG: In the bulk form when opening and closing the subforms several times their inputs became disabled.
* Added a setting if to show the shift end time.
* Made the shift view text a bit larger.
* New shortcode parameter to hide certain user interface elements.

= 3.0.3 =
* BUG: Shift Release and Shift Pickup settting menu items were not localized.
* BUG: Fatal error when trying to delete a user account.
* BUG: "User deleted" message was not localized.
* Remember the last choice of the "Comment Visible To" option.
* Modified the timepicker library to avoid possible conflicts with other libraries from other plugins.

= 3.0.2 =
* BUG: Fatal error on new setup caused by a change in ver. 3.0.1
* Fixed the help page on shortcode parameter within the admin panel.
* Added a few more params options for the shortcode.

= 3.0.1 =
* BUG: There was an empty, unlabelled drop down box in the admin area in Configuration > Settings
* Modified the datepicker library to avoid possible conflicts with other libraries from other plugins.
* The add links in calendar now lead directly to the shift creation, without the shift/timeoff selection. Timeoffs are now created in the Timeoff Requests area.
* In the shift create form the location and the time are now on the same page to make the process quicker.
* Fixed success message after timeoff creation (was saying "shift added" rather than "timeoff added").
* Remember the last choice of several options in the shift creation form: status, skip notification email.
* Re-added shift templates.
* Added draft shifts for the admin todo page.
* Added the "With All" shift group action to perform bulk actions on all displayed shifts.
* Added an option to assign multiple staff members at once when creating a shift.
* Added a red triangle for open shifts for easier notice.

= 3.0.0 =
* A new major ShiftController update, almost completely reworked!

= 2.4.1 =
* A small fix in code that might break redirects with WP high error reporting level.

= 2.4.0 =
* A fix for multiple staff ids in the shortcode param.

= 2.3.9 =
* A slight optimization on login/logout internal process.

= 2.3.7 =
* BUG: On plugin complete uninstall might delete all WordPress tables.

= 2.3.6 =
* BUG: (Pro Versions) multiple shifts could be deleted when deleting a single shift created as non recurring from shift edit form in the Delete tab.
* An option to color code shifts in the calendar according to the location
* Added the "within" parameter option for the shortcode to display shifts that are on now and within the specified tim

= 2.3.5 =
* Configuration option to set min and max values for time selection dropdowns, that will speed up time selection.
* Drop our database tables on plugin uninstall (delete) from WordPress admin. Also release the license code for the Pro version so it can be reused in another installation.
* Backend appearance restyled for a closer match to the latest WordPress version.
* Cleaned and optimized some files thus reducing the package size.

= 2.3.4 =
* Shift pickup links didn't work for staff members on the everyone schedule page (shortcode page).

= 2.3.3 =
* JavaScript error when staff picking up free shifts from everyone schedule page (shortcode page).

= 2.3.2 =
* A fix in session handling function that lead to an error on first user access of the system.

= 2.3.1 =
* Archived staff members are now not showing in the stats display if they have no shifts during the requested period.
* In the shortcode if you need to filter more than one location or employee, now you can supply a comma separated list of ids, for example [shiftcontroller staff="1,8"].
* Also if you do not want to show the list of locations in the shortcode page, you can supply the location parameter as 0 so it will list shifts for all locations [shiftcontroller location="0"]

= 2.3.0 =
* Added more options for shortcode to filter by location or by staff, as well as specify the start and end date and how many days to show.
* Extended options for the shift notes premium module, now one can define who can see the shift note - everyone, staff members, this shift staff or admin only.

= 2.2.9 =
* If more than one locations are available in the "Everyone Schedule" then it first asks to choose a location first.
* Added the description field for locations. If specified, it will be given in the "Everyone Schedule" and "Shift Pick Up" parts if more than one location available.
* Redesigned the "Everyone Schedule" (wall) page view so that lists all upcoming shifts in a simple list. It is supposed to eliminate all the compatibility issues for the shortcode page display as the calendar output would look cumbersome under certain themes.

= 2.2.8 =
* If there are open shifts in more than one location, an employee is asked to choose a location first, then the available shifts in this location ara displayed.
* Minor fixes and code updates

= 2.2.7 =
* Added an option to supply parameters to the shortcode to define the range (week or month) and the starting date, please check out the Configuration > Shortcode page
* Minor fixes and code updates

= 2.2.6 =
* Minor fixes and code updates

= 2.2.5 =
* BUG: In the schedule list view, if you choose filtering by location, the shifts for all locations were still displayed as if there were no filter applied.
* BUG: When creating a new shift, if you selected one or several employees to assign right now, but there was a validation error (no location selected, or the start and end times were incorrect), it showed a database error.

= 2.2.4 =
* Fixed an issue with shortcode that might be moving into infinite loop for admin and staff users
* An option to color code shifts in the calendar according to the employee
* An option to hide the shift end time for the employees
* An option to disable shift email notifications
* Minor fixes and code updates

= 2.2.3 =
* Reworked the calendar view controls - now the list and stats display can also be filtered by location and by employee.
* Fix with the timezone assignment
* Locations are sorted properly in the form dropdown
* Wrong employee name when a time off was requested by an employee
* when synchronizing users from WordPress you can append the original WP role name to the staff name

= 2.2.2 =
* Configure which user levels can take shifts
* Assign employees to shifts from the calendar view
* Fixed a problem with irrelevant email notifications
* Select multiple staff members or define the required number of employees when creating a shift

= 2.2.1 =
* Fixed problem when shortcode was not working properly

= 2.2.0 =
* Shift history module
* More convenient schedule views (show calendar by location and by staff member, week or month view)
* Updated view framework (Bootstrap 3)
* Minor code optimizations and bug fixes

= 2.1.1 =
* Login log module
* BUG: Select All in Timoffs and Shift Trades admin views were not working
* BUG: Repeating options were not active in the Premium version
* Minor code optimizations and bug fixes

= 2.1.0 =
* Fixed bug when email notification was not sent after publishing just one shift
* Remove location label if just one location is configured
* Shift notes view in the calendar
* Archived users do not appear in the dropdown list when creating or editing shifts


= 2.0.6 =
* Shifts month calendar

= 2.0.5 =
* Shifts list in a table view and CSV/Excel export

= 2.0.4 =
* Custom weekdays for recurring shifts

= 2.0.3 =
* Display shifts grouped by locations

= 2.0.2 =
* Public employee schedule calendar and minor bug fixes

= 2.0.1 =
* Bug fix: error when creating a new user in the free version.

= 2.0.0 =
* Completely reworked calendar view and the premium version.

= 1.0.2 =
* Bug fixes: time display, forgotten password and password change, email notification on a new timeoff.

= 1.0.1 =
* Bug fixes after not complete form in setup and error after timeoff delete.

= 1.0.0 =
* Initial release

Thank You.