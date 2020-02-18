=== WooCommerce for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com
Plugin URI: https://learndash.com/add-on/woocommerce/ 
LD Requires at least: 3.0
Slug: learndash-woocommerce
Tags: integration, woocommerce,
Requires at least: 4.9
Tested up to: 5.2
Requires PHP: 7.0
Stable tag: 1.6.0

Integrate LearnDash LMS with WooCommerce.

== Description ==

Integrate LearnDash LMS with WooCommerce.

WooCommerce is the most popular shopping cart software for WordPress. Most WordPress themes are compatible with WooCommerce. This add-on allows you to sell your LearnDash created courses with the WooCommerce shopping cart.

= Integration Features = 

* Easily map courses to products
* Associate one, or multiple courses to a single product
* Automatic course access removal
* Works with any payment gateway
* Works with WooCommerce Subscription

See the [Add-on](https://learndash.com/add-on/woocommerce/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE. 

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating. 

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 1.6.0 =
* Added COD payment method to manual payment method
* Added filter hook for course selector class name
* Added convert retroactive tool cron job to AJAX batch processing
* Added filter hook for auto complete order
* Added `payment_complete` hook to auto complete transaction
* Added reset course access counter function and reset access counter when user is already enrolled
* Added set course access from value from the start of a subscription instead of resetting it
* Added `any` param to subscription function to get all subscription orders
* Added make tax fields visible for course product type
* Updated adjust `select2` JS and CSS to hide select field on initial load
* Updated change courses selector to use `select2` to allow search and select UI
* Updated change variable name for subscriptions related hook function
* Updated virtual and downloadable product as auto-complete item
* Updated slug in translation class
* Updated lower per batch tools value to 10
* Updated null coalescing operator to make the plugin compatible with `PHP < 7`
* Updated check if order has been paid before being auto completed
* Updated load text domain function
* Updated skip order that is part of subscription in retroactive tool
* Fixed add course selector border CSS to override LD core styles
* Fixed hook for `learndash_delete_user_data` function
* Fixed variation product subscription cancellation did not unenroll users from courses
* Fixed undefined index error warning
* Fixed remove access on order refund for variable product
* Fixed variation product autocomplete bug
* Fixed AJAX retroactive tool
* Fixed change text domain
* Fixed fatal error because `wcs_order_contains_subscription()` only accepts `WC_Order` object

View the full changelog [here](https://www.learndash.com/add-on/woocommerce/).