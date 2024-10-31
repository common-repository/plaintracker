=== Plain Tracker ===
Contributors: Plainware
Tags: timesheet, time sheets, time card, time tracker, time report, log time
License: GPLv2 or later
Stable tag: 2.2.4
Requires at least: 4.8
Tested up to: 6.6
Requires PHP: 7.0

A timesheet plugin to track and report time by projects and workers.

== Description ==

**Plain Tracker** is a lightweight WordPress timesheet plugin to keep track of hours by projects and workers. Because we love simple tools, to make use of **Plain Tracker** you need to follow these straightforward steps:

* Define your team activities.
* Create your projects.
* Associate workers with projects and activities.
* [Record time](https://www.plaintracker.net/create-time-card/) that your workers spend on each activity and project.
* Analyze your time reports.
* [Print timesheets](https://www.plaintracker.net/print-worker-timesheet/) for your workers, activities, projects and customers.

###Quick Facts###
* **PlainTracker** works in [WordPress backend/admin area](https://www.plaintracker.net/admin-dashboard/) (**wp-admin** part of your WordPress website).
* By default, all WordPress **Administrators** have full rights in **Plain Tracker**.
* Any WordPress user can be assigned as a worker in **Plain Tracker**.
* You can also assign any WordPress user to be an approver of others timesheets.

== Support ==
Please contact us at https://www.plaintracker.net/

Author: Plainware
Author URI: https://www.plainware.com

== Installation ==
= Automatic installation =
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser.
To do an automatic install:

* Log in to your WordPress admin panel, navigate to the **Plugins** menu and click **Add New**.
* In the search field type "PlainTracker" and click **Search Plugins**.
* Once you've found the plugin you can install it by clicking **Install Now**.

= Manual Installation =
The manual installation method involves downloading the plugin and uploading it to your web server.

* Download the plugin file and unzip it.
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's _wp-content/plugins/_ directory.
* Activate the plugin through the 'Plugins' menu in the WordPress admin.

== Screenshots ==

1. View time log.
2. Edit time records of a timesheet.
3. The list of current activities.
4. Assign projects and activities to a worker.

== Frequently Asked Questions ==

= How to create a time record? =

Simply go to Plain Tracker > Create time card. For detailed instructions please see this help page [How to create a time record](https://www.plaintracker.net/create-time-card/).

= How to list current time records or find a specific record? =

Go to Plain Tracker > View time log and use the filtering and navigation options to narrow down your search. For detailed instructions please see this help page [How to browse time records](https://www.plaintracker.net/list-time-record/).

= How to print a monthly timesheet for a worker? =

Go to Plain Tracker > Workers, find that worker, then in their detail screen click View time log. The time card list will be filtered for this worker only. Use other filtering and navigation options to narrow down your search to the whole month. For detailed instructions please refer to this help page [How to print worker's timesheet](https://www.plaintracker.net/print-worker-timesheet/).

= I don't want to allow our workers access the back-end of the site, how to make PlainTracker accessible from front-end? =

Use [plaintracker] shortcode in any post or page.

== Changelog ==

= 2.2.4 (2024-10-19) =
* Some framework code updates.

= 2.2.3 (2024-08-13) =
* BUG: The list of activities were displayed incomplete on the Worker profile page.
* Added language files.
* A few visual tweaks.

= 2.2.2 (2024-07-26) =
* Added a login link from a page or post with our shortcode.

= 2.2.1 (2024-07-25) =
* BUG: Workers couldn't add timesheets for themselves. Now a worker can go to My Timesheets, Add new.
* Added an option for an administrator to test accessing the system as a worker.

= 2.2.0 (2024-07-11) =
* Added timesheets functionality.
* Added approvers for workers.

= 2.1.0 (2024-06-14) =
* Added start and end dates for projects. Now it is possible to use projects as pay periods.
* Redesigned many parts of the admin area for more efficient work and navigation within the plugin.
* Added the option for employees to submit their time cards for further approval by managers.

= 2.0.2 (2024-01-30) =
* Redesigned admin area for quicker page load and navigation.

= 2.0.1 (2024-01-11) =
* Minor fixes.

= 2.0.0 (2023-12-23) =
* Plain Tracker makes a pivot and becomes a timesheet application to record and track time of employees.

= 1.2.5 =
* Minor fixes.

= 1.2.4 =
* Code optimization.

= 1.2.3 =
* Added bulk actions for activities.
* Code optimization.

= 1.2.2 =
* Code optimization.

= 1.2.1 =
* BUG: a fatal error could happen as the plugin could try to handle not its own actions.

= 1.2.0 =
* Code optimization.

= 1.1.3 =
* Code optimization.

= 1.1.2 =
* Added a form to add multiple records per for one day.

= 1.1.1 =
* Added option to change activities display order.
* Code optimization.

= 1.1.0 =
* Code optimization.

= 1.0.2 =
* Code optimization.

= 1.0.1 =
* Code optimization.

= 1.0.0 =
* Initial release.