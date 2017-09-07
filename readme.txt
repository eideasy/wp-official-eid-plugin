=== Smart ID ===
Contributors: Smart ID Estonia OÜ
Author URI: https://smartid.ee
Plugin URL: https://smartid.ee/plugins/wp/
Tags: ID-card, IDcard, smartID, mobile-ID, mobileID, identification, security, eIDAS, OAuth, OAUTH2, Personas apliecība, Asmens tapatybės kortelė,  Cartão de Cidadão
Requires at least: 4.5
Tested up to: 4.8.1
Stable tag: trunk

== Description==
Easiest way to identify users on your site via Latvian Personas apliecība, Lithuanian Asmens tapatybės kortelė and M. parašas, Portugese Cartão de Cidadão, Estonian ID card + Mobile-ID, Smart-ID and other secure login methods to your wordpress. No more forcing people to create yet another user account.
Smart ID is authentication Gateway, Logib as a Service, that uses external servers to identify customers.
User name, identity code and other data are supplied depending of login method.
Installation is very easy, just install & use short code [smart_id] to have login buttons in your page or post.

== Installation ==

= Automatic =
* In the admin panel under plugins page, click Add New
* browse "Smart_ID" and click install now
* Click Activate plugin
* Go to Smart ID section on left side to activate you site
* PS MULTISITE!! Each multisite instance needs to activate Smart-ID separately.
* Enjoy!

= Manual =
* Extract zip file to your wp-content/plugins directory.
* In the admin panel under plugins, activate "Smart ID".
* Enjoy!

= API Connection =
Smart ID APIs are constantly changing and being updated. We monitor these changes and automatically update our APIs, so that you can be sure that Smart ID will always run smoothly and with the most up-to-date API calls.

== Frequently Asked Questions ==
Use shortcode 
[smart_id] to get login button, 

Identification with ID-card & mobile-ID works everywhere.

Support email: help@smartid.ee
Support phone +372 555 29 332

== Screenshots ==
1. Admin view
2. Login view

== Changelog ==
= 1.2.7 =
* tested with WP 4.7, login popup
= 1.2.9 =
* Better cleanup after deleting user
= 1.3.1 =
* Latvian ID-card Personas apliecība support
* Login type buttons configurable
* API keys manually configurable, helpful while going live
* Contract signing deprecated
= 1.3.2 =
* Improved flow if not all methods are enabled
= 2.0 =
* Login flow improved, users need to make one less click
* Added more builtin login methods. Lithuanian and Portugese cards.
* Multisite support
* Better error handling during signup, eg if e-mail already exists