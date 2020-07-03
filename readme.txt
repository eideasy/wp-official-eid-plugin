=== eID Easy ===
Contributors: Smart ID Estonia OÜ
Author URI: https://eideasy.com
Plugin URL: https://eideasy.com
Tags: ID-card, IDcard, smartID, mobile-ID, mobileID, identification, security, eID, IDaaS, eIDAS, OAuth, OAUTH2, Personas apliecība, Asmens tapatybės kortelė,  Cartão de Cidadão, EmiratesID
Requires at least: 4.5
Tested up to: 5.4.2
Stable tag: trunk

== Description==
This plugin makes secure identification of people much easier than implementing these identification methods yourself. Supported methods among others are: Latvian Personas apliecība, Lithuanian Asmens tapatybės kortelė and M. parašas, Portugese Cartão de Cidadão, Estonian ID card + Mobile-ID, Smart-ID. Each method can be turned on and off individually.

Most login actions are FREE. The ones that cost money (Mobile-ID, Smart-ID app, etc) are paid because their operator charges money.  Smart ID gets volume discounts and is able to offer similar price in same range as making direct contract. Plus you do not neet to pay minimal monthly fee.

Plugin implements eideasy.com Oauth 2.0 protocol like Facebook and Google login.

After plugin installation you need to register your site to activate your site and get the Oauth credentials. Also your e-mail needs to be verified.

== Filters and actions ==
There are several filters for customizing the plugin behaviour using add_filter() Wordpress function.
1. Filter "eideasy_login" enables customizing the page where user will be redirected after login completed.
2. Filter "eideasy_new_user_email" enabled setting user e-mail to something else that default idcode@local.localhost
3. Filters to customize login buttons look and feel are: "ee-id-card-login, ee-mobile-id-login, lv-id-card-login, lt-id-card-login, lt-mobile-id-login, pt-id-card-login, smart-id-login, google-login, facebook-login, agrello-id-login"
4. Action eideasy_user_created. Will be executed when new user has been created. Will get new user ID and user data as arguments.

== Tips and tricks ==
Use shortcode [eid_easy] to get login button,
Use shortcode [contract id="123ABC"] to create document sign page. Get the actual contract ID value from id.eideasy.com

Since Wordpress needs user e-mail and e-mail for users is not part of the data received during identification then fake e-mail is created. Change the new user account e-mail with add_filter() and filter eideasy_new_user_email. By default email will be idcode@local.localhost

Support email: help@eideasy.com
Support phone +372 555 29 332

== Screenshots ==
1. Admin view
2. Login view

== Changelog ==

= 4.2.2 =
Do not activate new methods when upgrading plugin

= 4.2.1 =
Shortcode login improvements

= 4.2 =
Added action eideasy_user_created after registering new user from ID login.

= 4.1.1 =
Improved way of registering JS files.

= 4.1 =
Added filters so login buttons code can be customized easily to match your site identity.

= 4.0 =
Upgrading base system to eideasy.com

= 3.8 =
New Estonian e-ID symbolics
Added filter smartid_new_user_email to allow editing new user username/email
Fixed custom redirect config error

= 3.7.2 =
Added Agrello .ID login method

= 3.6.0 =
After login URL is manually changeable

= 3.5.2 =
Better detection of login in popup window

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