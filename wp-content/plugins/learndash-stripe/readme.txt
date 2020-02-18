=== Stripe for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com 
Plugin URI: https://learndash.com/add-on/stripe/
LD Requires at least: 2.5
Slug: learndash-stripe
Tags: integration, payment gateway, stripe
Requires at least: 5.0
Tested up to: 5.2
Requires PHP: 7.0
Stable tag: 1.3.0

Integrate LearnDash LMS with Stripe.

== Description ==

Integrate LearnDash LMS with Stripe.

LearnDash comes with the ability to accept payment for courses by leveraging PayPal. Using this add-on, you can quickly and easily accept payments using the Stripe payment gateway. Use it with PayPal, or just use Stripe - the choice is yours!

= Integration Features = 

* Accept payments using Stripe
* Automatic user creation and enrollment
* Compatible with built-in PayPal option
* Lightbox overlay

See the [Add-on](https://learndash.com/add-on/stripe/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE. 

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating. 

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 1.3.0 =
* Added new Stripe checkout integration button
* Added `receipt_email` prop to Stripe API Charge object
* Added endpoint secret setting
* Added webhook URL readonly setting
* Added new checkout fields toggle scripts
* Updated Stripe PHP SDK
* Updated the order of PK and SK fields on settings page
* Updated Stripe plan to comply with latest Stripe API
* Updated language files
* Removed new checkout code from legacy checkout class
* Fixed Undefined Index error

View the full changelog [here](https://www.learndash.com/add-on/stripe/).