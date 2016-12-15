=== Smart ID ===
Contributors: Smart ID Estonia OÜ
Author URI: https://smartid.ee
Plugin URL: https://smartid.ee/plugins/wp/
Tags: ID-card, IDcard, smartID, mobile-ID, mobileID, identification, sign, signing, digital signing, security, eIDAS, OAuth, OAUTH2, token
Requires at least: 4.5
Tested up to: 4.7
Stable tag: trunk

== Description==
Allow your visitors to login to Wordpress with any ID-card, mobile-ID or other authentication methods eg mobile apps, social accounts, two step verification. Except e-mail and password - this is so outdated!!!
Smart ID is authentication Gateway, Logis as a Service, that uses external servers to identify customers. 
User name, identity code and other data are supplied depend of login method.
Installation is very easy, just install & use short code [smart_id] to have login buttons and [contract id="123"] to show document template with instant signing.

== Installation ==

= Automatic =
* In the admin panel under plugins page, click Add New
* go to Upload tab
* browse "Smart_ID" and click install now
* Click Active plugin
* Go to Smart ID section on left side to activate you site
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
[contract id="123"] to show document template with instant signing.

Identification with ID-card & mobile-ID works everywhere.
Signing with mobile-ID works everywhere. Safari and Firefox also in incognito works fine.
ID-card signing don’t work with MS Edge and Chrome incognito window.

If something goes wrong try to shortly remove ID-card or restart browser. 
Working with Mac - just restart it, that's goes fast.

Support email: help@smartid.ee

== Screenshots ==
1. Admin view

== Changelog ==
= 1.0 =
* Initial release.
= 1.2.2 =
* Oauth2
* mobil-ID
* Document signing
= 1.2.4 =
* Minor changes, tested with WP 4.6
= 1.2.5 =
* Minor changes, tested with WP 4.6.1, social accounts logo changed
= 1.2.6 =
* api subdomain change to id.smartid.ee
= 1.2.7 =
* tested with WP 4.7, login popup