=== eID Easy ===
Plugin Name: eID Easy
Contributors: EID Easy OÜ
Author URI: https://eideasy.com
Plugin URL: https://eideasy.com
Tags: eParaksts, eParaksts Mobile, eID Karte, ID-card, IDcard, smartID, mobile-ID, mobileID, identification, security, eID, IDaaS, eIDAS, OAuth, OAUTH2, Personas apliecība, Asmens tapatybės kortelė,  Cartão de Cidadão, beid, belgium identity card
Requires at least: 4.5
Tested up to: 5.6.1
Stable tag: trunk
License: GPLv2 or later

== Description==
This plugin makes secure identification and creating Qualified Electronic Signatures using eID methods much easier than implementing these identification methods yourself. Supported methods among others are: Belgian eID card, Latvian Personas apliecība, Latvian eParaksts Mobile, Lithuanian Asmens tapatybės kortelė and M. parašas, Portugese Cartão de Cidadão, Estonian ID card + Mobile-ID, Smart-ID. Each method can be turned on and off individually.

Plugin implements eideasy.com Oauth 2.0 protocol like Facebook and Google login.

After plugin installation you need to register your site to activate your site and get the Oauth credentials. Also your e-mail needs to be verified.

It is using service and API-s from https://eideasy.com. To activate the signing service is needed to create user account and copy credentials from there into the plugin configuration.

== Filters and actions ==
There are several filters for customizing the plugin behaviour using add_filter() Wordpress function.
1. Filter "eideasy_login" enables customizing the page where user will be redirected after login completed.
2. Filter "eideasy_new_user_email" enabled setting user e-mail to something else that default idcode@local.localhost
3. Filters to customize login buttons look and feel are: "ee-id-card-login, ee-mobile-id-login, lv-id-card-login, lt-id-card-login, lt-mobile-id-login, pt-id-card-login, smart-id-login, google-login, facebook-login, agrello-id-login"
4. Action eideasy_user_created. Will be executed when new user has been created. Will get new user ID and user data as arguments.
5. Action eideasy_after_logged_in. Will be executed every time when user info has been received and just before setting login cookie. Gets user data and user ID as arguments.
6. Action eideasy_user_identified. Runs immediately after user data has been received and includes array of data returned by eID Easy.
7. Filter "eideasy_select_country". Enables to set country for login, especially useful for Smart ID and Freja eID methods.

== Tips and tricks ==
Use shortcode [eid_easy] to get login button,
Use shortcode [contract id="123ABC"] to create document signing page. Get the actual contract ID value from https://id.eideasy.com

Since Wordpress needs user e-mail and e-mail for users is not part of the data received during identification then fake e-mail is created. Change the new user account e-mail with add_filter() and filter eideasy_new_user_email. By default email will be idcode@local.localhost

Support email: help@eideasy.com
Support phone +372 555 29 332

eID Easy terms and conditions can be found here https://eideasy.com/terms-of-service/, privacy policy here https://eideasy.com/privacy-policy/

== Screenshots ==
1. Admin view
2. Login view

== Changelog ==

= 5.2 =
WooCommerce integration is supporting variable products.

= 5.1 =
Filter "eideasy_select_country" added. Useful for Smart ID and Freja eID methods.

= 5.0 =
WooCommerce integration added. Age verification possible during the checkout.
Major refactoring

= 4.6 =
Added action eideasy_user_identified. Runs immediately after user data has been received.
Added option to only identify people. If this option is checked then no users are logged in nor are any accounts created.

= 4.5 =
Default Estonian users e-mail is @eesti.ee
Allow connecting users with eID method after logged in with password
Thank you https://www.linkedin.com/in/rrosimannus/

= 4.4.1 =
If login has finished then stop processing. Do not let other plugins log the user out.

= 4.4 =
Belgium ID card added.
Latvia eParaksts Mobile ID added.
Fixed bug where sometimes popup was opened as well next to the redirect in mobile browsers

= 4.3.1 =
Added action eideasy_after_logged_in

= 4.3.0 =
Use OAuth redirect in mobile browsers instead of popup

= 4.2.7 =
Bigger and better Smart-ID login button

= 4.2.6 =
Fix updating user without POST from the user page

= 4.2.5 =
Microsoft and IIS better image urls detection

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
Added filter eideasy_new_user_email to allow editing new user username/email
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
Added filter "eideasy_login" to decide where to redirect after login process is completed. Default is redirecting to home page.

= 3.1 =
Intranet mode available. Site admins can add ID code to users manually and disable automatic user registration.
This allows limiting secure login to specific groups of people only
