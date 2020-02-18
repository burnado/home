=== MemberPress for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com 
Plugin URI: https://learndash.com/add-on/memberpress/
LD Requires at least: 2.5
Slug: learndash-memberpress
Tags: integration, membership, memberpress,
Requires at least: 4.9
Tested up to: 5.2
Requires PHP: 7.0
Stable tag: 2.0

Integrate LearnDash LMS with MemberPress.

== Description ==

Integrate LearnDash LMS with MemberPress.

MemberPress is a premium WordPress membership plugin that excels in memberships, grouping, coupons, reminders, reports, and more.

With this integration you can create membership levels in MemberPress and associate the access levels to LearnDash courses. Customers are then auto-enrolled into courses after signing-up for membership.

= Integration Features = 

* Associate membership levels to one or more courses
* Automatic removal upon membership cancellation
* Create trial membership levels with various payment gateways

See the [Add-on](https://learndash.com/add-on/memberpress/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE. 

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating. 

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 2.0 =
* Added retroactive course access for existing members and transactions
* Added subscription hooks to enroll and unenroll users
* Added translation class
* Added user enrolled check before enroll user to course
* Added support for sub corporate account
* Added function to remove course access counter on user unenrollment
* Added `maybe_update_course_access_timestamp_to_first_subscription()` function and its filter hooks
* Added cron jobs
* Updated user access when a membership is updated
* Updated to prevent users unenrolled from course if the subscription is not expired even if it's cancelled
* Updated to improve course access counter and fix membership cancellation
* Updated overall transaction and subscription course enrollment and unenrollment process
* Updated to not unenroll user from a transaction that has subscription
* Updated `transaction_expired` function
* Update POT file
* Fixed delete subscription issue
* Fixed reset course access timestamp to first subscription timestamp

View the full changelog [here](https://www.learndash.com/add-on/memberpress/).