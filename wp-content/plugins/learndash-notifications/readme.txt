=== LearnDash Notifications ===
Author: LearnDash
Author URI: https://learndash.com 
Plugin URI: https://learndash.com/add-on/learndash-notifications/
LD Requires at least: 2.5.0
Slug: learndash-notifications
Tags: notifications, emails
Requires at least: 4.9
Tested up to: 4.9
Requires PHP: 7.0
Stable tag: 1.3.0

Send email notifications based on LearnDash actions.

== Description ==

Send email notifications based on LearnDash actions.

This add-on enables a new level of learner engagement within your LearnDash courses. Configure various notifications to be sent out automatically based on what learners do (and do not do) in a course.

This is a perfect tool for bolstering learner engagement, encouragement, promotions, and cross-selling.

= Add-on Features = 

* Automatically Send Notifications
* 13 Available Triggers
* 34 Dynamic Shortcodes
* Delay Notifications
* Choose Recipients

See the [Add-on](https://learndash.com/add-on/learndash-notifications/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE. 

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating. 

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 1.3.0 =
* Added trigger for after the course expires
* Added filter hooks for custom trigger and functions 
* Added course ID and lesson ID to the notification setting fields for upload and approved assignment trigger
* Added status tab to the notifications menu
* Added translation class
* Added cron function to update lesson available notification in the database
* Added default blank value to the AJAX empty select option

* Fixed SQL syntax error
* Fixed assignment essay comment left trigger
* Fixed scheduled notifications to be sent only after the notification timestamp passes the current timestamp
* Fixed lesson and topic URL shortcode returned values
* Fixed logic to exclude unenrolled users
* Fixed logic to prevent sending notification is ID is not numeric

* Updated default comment notification to be disabled for assignment and essay
* Updated SQL in CRUD functions
* Updated delete delayed emails to hourly cron
* Updated send scheduled emails function
* Updated group leader recipient logic
* Updated course completed check to enroll course notification trigger
* Updated lesson available notifications to prevent sending multiple times
* Updated and removed course completed check on essay graded notification
* Updated lesson available scheduled emails function
* Updated dequeue autosave for notification post type
* Updated to hide minor and miscellaneous publishing action widget for notifications
* Updated user deletion LeanrDash data cleanup to hourly cron


View the full changelog [here](https://www.learndash.com/add-on/learndash-notifications/).