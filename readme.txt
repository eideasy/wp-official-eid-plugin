=== Smart ID ===
Contributors: Smart ID Estonia OÜ
Author URI: https://smartid.ee
Plugin URL: https://smartid.ee/how
Tags: ID-card, IDcard, smartID, mobile-ID, mobileID, identification, security, eIDAS, OAuth, OAUTH2, Personas apliecība, Asmens tapatybės kortelė,  Cartão de Cidadão
Requires at least: 4.5
Tested up to: 5.3
Stable tag: trunk

== Description==
This plugin makes secure identification of people much easier than implementing these identification methods yourself. Supported methods among others are: Latvian Personas apliecība, Lithuanian Asmens tapatybės kortelė and M. parašas, Portugese Cartão de Cidadão, Estonian ID card + Mobile-ID, Smart-ID. Each method can be turned on and off individually.

Most login actions are FREE. The ones that cost money (Mobile-ID, Smart-ID app, etc) are paid because their operator charges money.  Smart ID gets volume discounts and is able to offer similar price in same range as making direct contract. Plus you do not neet to pay minimal monthly fee.

Plugin implements smartid.ee Oauth 2.0 protocol like Facebook and Google login.

After plugin installation you need to register your site to activate your site and get the Oauth credentials. Also your e-mail needs to be verified.

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

== Frequently Asked Questions ==
Use shortcode 
[smart_id] to get login button, 
[contract id="123ABC"] to create document sign page. Get the actual ID value from id.smartid.ee

Identification with ID-card & mobile-ID works everywhere.

Support email: help@smartid.ee
Support phone +372 555 29 332

== Screenshots ==
1. Admin view
2. Login view

== Changelog ==

= 3.4.1 =
Make sure sending empty ID code on custom profile page does not remove ID code from user

= 3.4 =
Wordpress 5 testing
Signing pages reintroduce due high customer demand
Better multi country support

= 3.2.1 =
Fixed bug where ID code was lost for the user when updating in some cases

= 3.2 =
Added filter "smartid_login" to decide where to redirect after login process is completed. Default is redirecting to home page.

= 3.1 =
Intranet mode available. Site admins can add ID code to users manually and disable automatic user registration.
This allows limiting secure login to specific groups of people only

= 2.1 =
Optional Debug mode for login issues detection on server side

= 2.0 =
* Login flow improved, users need to make one less click
* Added more builtin login methods. Lithuanian and Portugese cards.
* Multisite support
* Better error handling during signup, eg if e-mail already exists
= 1.3.2 =
* Improved flow if not all methods are enabled
= 1.3.1 =
* Latvian ID-card Personas apliecība support
* Login type buttons configurable
* API keys manually configurable, helpful while going live
* Contract signing deprecated
= 1.2.9 =
* Better cleanup after deleting user