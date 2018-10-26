# ActiveCampaign Helper 

This plugin extends the functionality of the native ActiveCampaign plugin (as written by ActiveCampaign) by providing the following functionality:

### Site tracking
The native WP ActiveCampaign plugin only tracks emails for logged in user. This plugin provides hooks add the email from a variety of sources such as through JavaScript or WordPress action hooks. In addition it provides methods to store a user preference as far as tracking is concerned for privacy or GDPR. The plugin can either insert its own tracking code (native AC plugin not required) or make use of the native AC plugin

### Subscription
This plugin optionally adds a subscribe to newsletter checkbox for:
- WooCommerce checkout
- WooCommerce registration
- WordPress registration
... more to come

### Event tracking
The plugin offers a 'trackEvent' JS method to easily use AC event tracking.


## Available hooks

### ach_store_email

If the email is available through an action hook we can then call the `ach_store_email` hook to store our email.

    // Sample function - here we use the login email (AC does this already but its a clear example)
    function sample_track_login($login, $user) {
      do_action('ach_store_email', $user->user_email)
    }
    add_action('wp_login', 'sample_track_login',  10, 2);

### ach_subscribe

Subscribe an email to an AC list (as defined by list ID in settings).

## Using the JavaScript functions

The plugin offers 3 convenience functions. 

### acs.storeEmail
This function accepts an email address and will add it to the AC tracking snippet. This is to be used in situations where the email is potentially available but not possible to grab through a WP action hook (for example an on checkout hook which can be handled through PHP). 

It accepts 2 parameters:
- email - an email to add to tracking
- callback - optional callback to inspect response

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
## Changelog

#### 0.0.4 
* Initial release

