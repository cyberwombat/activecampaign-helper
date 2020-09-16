# ActiveCampaign Helper

This plugin extends the functionality of the native ActiveCampaign plugin (as written by ActiveCampaign) by providing the following functionality:

### Site tracking

The native WP ActiveCampaign plugin only tracks emails for logged in user. This plugin provides hooks add the email from a variety of sources such as through JavaScript or WordPress action hooks. In addition it provides methods to store a user preference as far as tracking is concerned for privacy or GDPR. The plugin can either insert its own tracking code (native AC plugin not required) or make use of the native AC plugin.

Note: Do not add the AC tracking snippet by hand (such as in the theme) as this plugin cannot intercept it to inject the email address.

### Subscription

This plugin optionally adds a subscribe to newsletter checkbox for:

-   WooCommerce checkout
-   WooCommerce registration
-   WordPress registration
    ... more to come

### Event tracking

The plugin offers a 'trackEvent' JS method to easily use AC event tracking.

## Available hooks

### ach_store_email

If the email is available through an action hook we can then call the `ach_store_email` hook to store our email.

    // Sample function - here we use the login email (AC does this already but its a clear example)
    function sample_track_login($login, $user) {
      do_action('ach_store_email', $user->user_email);
    }
    add_action('wp_login', 'sample_track_login',  10, 2);

### ach_subscribe

Subscribe an email to an AC list (as defined by list ID in settings).

    do_action('ach_subscribe', 'foo@example.org');

You can obtain the list ID from AC by hovering your cursor above the list title in the list admin - the url will contain a `listid` parameter.

Here's an example on capturing the email field from a Contact Form 7 form and sending it to an AC list.

    // After send hook available from CF7
    add_action('wpcf7_mail_sent', function ($cf7) {
      $submission = WPCF7_Submission::get_instance();
      if ($submission) {
        $data = $submission->get_posted_data();

        // _wpcf7 is the contact form ID - in this case we want to match for form ID 12345
        if ($data['_wpcf7'] ==  '12345') {
            // This would depend on the name of the field. In this case the CF7 email field is called "your-email"
            $email = $data['your-email'];

            // Call the AC helper action hook
            do_action('ach_subscribe', $email);
        }
    }

## Available filters

### ach_list

This filter can be used to modify the list ID before subscribe. Create a custom function such as:

```
function change_list_id( $preset_list_id ) {
    return $new_id;
}
add_filter( 'ach_list', 'change_list_id', 10, 1 );
```

# ach_tags

This filter can be used to add tags before subscribe. Create a custom function to return an array of tags such as:

```
function add_tags_id() {
    return array('foo', 'bar');
}
add_filter( 'ach_tags', 'add_tags', 10, 0 );
```

## Using the JavaScript functions

The plugin offers 3 convenience functions.

### acs.storeEmail

This function accepts an email address and will add it to the AC tracking snippet. This is to be used in situations where the email is potentially available but not possible to grab through a WP action hook (for example an on checkout hook which can be handled through PHP).

It accepts 2 parameters:

-   email - an email to add to tracking
-   callback - optional callback to inspect response

### acs.setTracking

This function accepts a true/false and should be called from some privacy/GDPR check in order to mark user as not trackable.

### acs.sendEvent

This function accepts an event name, optional value and optional email and will send an event to ActiveCampaign.

Provided is a snippet you can place in an HTML widget to explore:

```
<script>
jQuery(function($) {
  $('#ach_test_submit').on('click', function () {
    acs.storeEmail($('#ach_email').val(), function(err, res) {
      console.log(err || res)
    })
  })
  $('#ach_test_track_on').on('click', function () {
    acs.setTracking(true, function (err, res) {
      console.log(err || res)
    })
  })
  $('#ach_test_track_off').on('click', function () {
    acs.setTracking(false, function (err, res) {
      console.log(err || res)
    })
  })
  $('#ach_test_event').on('click', function () {
    acs.sendEvent('test', 'chocolate', $('#ach_email').val(),  function (err, res) {
      console.log(err || res)
    })
  })
})
</script>
<input id="ach_email" placeholder="Your email" style="width:100%" value="foo@example.org"/>
<div style="text-align:center">
<input type="submit" value="Submit email" id="ach_test_submit"/><br>
<input type="submit" value="Send event with email" id="ach_test_event"/>
<input type="submit" value="Don't track me" id="ach_test_track_off"/><br>
<input type="submit" value="Track me" id="ach_test_track_on"/>
</div>
```

## Debugging

If the debug checkbox is checked or the wp config vars `WP_DEBUG` is true, events will be sent to the WordPress error log.
You may need to enable the log by setting `WP_DEBUG_LOG` params in `wp-config.php`:

```
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Note: The log is usually located in the `wp-content` folder.

## Todo

Integrate ACs new tracking code.

```
<script type="text/javascript">
    (function(e,t,o,n,p,r,i){e.prismGlobalObjectAlias=n;e.pgo=e.pgo||function(){(e.pgo.q=e.pgo.q||[]).push(arguments)};e.pgo.l=(new Date).getTime();r=t.createElement("script");r.src=o;r.async=true;i=t.getElementsByTagName("script")[0];i.parentNode.insertBefore(r,i)})(window,document,"https://prism.app-us1.com/prism.js","pgo");

    pgo('setAccount', 'xxxx');
    pgo('setTrackByDefault', true);
    pgo('setEmail', 'Email_Address_Goes_Here');
    pgo('process');
</script>
```

## Changelog

#### 0.0.4

-   Initial release from merge

#### 0.0.5

-   Bug fix

#### 0.0.6

-   Change priority of WP_Scripts override

#### 0.0.7

-   Misc cleanup

#### 0.0.8

-   Add example

#### 0.0.9

-   Add PHP cookie storage
-   Update on login function

#### 0.0.10

-   Update tracking code

#### 0.0.11

-   Change Woo checkout placement

#### 0.0.12

-   Add ach_list filter to dynamicallyt modify list ID before subscribe

#### 0.0.13

-   Add ach_tags filter to dynamicallyt add tags before subscribe
