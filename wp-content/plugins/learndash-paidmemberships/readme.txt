=== PaidMembershipsPro for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com 
Plugin URI: https://learndash.com/add-on/paidmembershipspro/
LD Requires at least: 2.5
Slug: learndash-paidmemberships
Tags: integration, membership, paid memberships pro,
Requires at least: 4.9
Tested up to: 5.1
Requires PHP: 7.0
Stable tag: 1.1.1

Integrate LearnDash LMS with Paid Memberships Pro.

== Description ==

Integrate LearnDash LMS with Paid Memberships Pro.

PaidMemberships Pro is one of the most popular free membership plugins available for WordPress with robust user statistics and reporting of membership levels. 

With this integration, you can create membership level access and associate the access levels to LearnDash courses. Customers are auto-enrolled into courses after signing-up for membership.

= Integration Features = 

* Associate membership levels to one or more courses
* Auto-expire membership levels after X amount of time
* Create trial membership levels with various payment gateways

See the [Add-on](https://learndash.com/add-on/paidmembershipspro/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE. 

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating. 

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 1.1.1 =
* Updated logic to prevent warning error
* Fixed course enrollment bug
* Removed payment failed action hook
* Removed `pmpro_cancelmembershiplevel` function and use LD native function

View the full changelog [here](https://www.learndash.com/add-on/paidmembershipspro/).