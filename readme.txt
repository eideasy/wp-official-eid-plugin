=== Smart-ID ===
Contributors: Smart ID Estonia OÜ
Author URI: https://smartid.ee
Plugin URL: https://smartid.ee/plugins/wp/
Tags: ID-card, mobile-ID, identification, signing, digital signing, security
Requires at least: 4.5
Tested up to: 4.5.3
Stable tag: trunk

== Description==
Allow your visitors to login to Wordpress with Estonian ID-card and mobile-ID.
Smart-ID is authentication method that uses external servers to identify customers. 
User name, identity code and e-mail are identified. 
In addition dynamic javascript, needed for logging in, is downloaded from Smart-ID servers and Apache web server records website user IP address into its standard access.log
Installation very easy, just install & use short code [smart-id] to have login buttons and [contract id="123"] to show document template with instant signing.

== Installation ==

= Automatic =
* In the admin panel under plugins page, click Add New
* go to Upload tab
* browse "Smart_ID" and click install now
* Click Active plugin
* Go to Smart-ID section on left side to activate you site
* Enjoy!

= Manual =
* Extract zip file to your wp-content/plugins directory.
* In the admin panel under plugins, activate "Smart-ID".
* Enjoy!

= API Connection =
Smart-ID APIs are constantly changing and being updated. We monitor these changes and automatically 
update our APIs, so that you can be sure that Smart-ID will always run smoothly and with the most up-to-date 
API calls.

== Frequently Asked Questions ==
Use shortcode [smart-id] to get login button
Support email: help@smartid.ee

== Screenshots ==

== Changelog ==
= 1.0 =
* Initial release.
= 1.2.2 =
* Oauth2
* mobil-ID
* Document signing