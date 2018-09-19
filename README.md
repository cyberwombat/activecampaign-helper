# ActiveCampaign Tracking Helper

This plugin, which is meant to be customized per your needs, allows filtering of the AC tracking snippet to add an email address. The native AC snippet only checks for logged in users. This plugin allows using hooks (ex: WooCommerce checkout hook) to use an email address from a non logged in user as well as through JavaScript (ex: parsing a form).

This plugin requires the presence of the ActiveCampaign plugin with tracking on.

Add your custom hooks to the `hooks.php` file. There is an example in there to get started.

If the email is not available through a hook you may be able to use JavaScript to load the email instead. Modify the `js/ach.js` file as necessary.

View the source of the page and search for `ac_settings`. If all worked well the email will be in that object.

Ex:

    var php_data = {"ac_settings":{"tracking_actid":"123","site_tracking_default":1},"user_email":"bob@here.com"};