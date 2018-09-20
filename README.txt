=== ActiveCampaign Tracking Helper ===
Contributors: Enradia
Tags: ActiveCampaign
Version: 0.0.2
Requires PHP: 5.0.0
Requires at least: 3.0
Tested up to: 4.9.6
Contributor: cyberwombat
Stable tag: trunk
License: MIT
License URI: https://opensource.org/licenses/MIT
Contributors: cyberwombat
Donate link: https://www.paypal.com/donate/?token=SiMqCFR8nI8ciqqKR8EpxBhGrBTAt6ye5kevdwvLF5MGjGTAO_oN7o-vDlWvRiBrZopSw0&country.x=US&locale.x=US


This plugin allows filtering of the AC tracking snippet to add an email address. The native AC snippet only checks for logged in users. This plugin allows using hooks (ex: WooCommerce checkout hook) to use an email address from a non logged in user as well as through JavaScript (ex: parsing a form).

This plugin requires the presence of the ActiveCampaign plugin with tracking on.


== Installation ==

Ensure the ActiveCampaign plugn from AC is installed and set up in order for this plugin to work.


== Usage ==

This plugin can accept an email either through an action hook or through JavaScript from your own custom files.


= Hook example =

If the email is available through an action hook we can then call the `ach_store_email` hook to store our email.

    // Sample function - here we use the login email (AC does this already but its a clear example)
    function sample_track_login($login, $user) {
      do_action('ach_store_email', $user->user_email)
    }
    add_action('wp_login', 'sample_track_login',  10, 2);


= JS example =

The included JS script checks for the presence of the global `ach_email` var. If it's populated it will be AJAX sent to the plugin. 


== Testing ==

View the source of the page and search for `ac_settings`. If all worked well the email will be in that object.

Ex:

    var php_data = {"ac_settings":{"tracking_actid":"123","site_tracking_default":1},"user_email":"bob@here.com"};


== Changelog ==
 
= 0.0.1 =
* Initial version

= 0.0.2 =
* Added hooks and global js

