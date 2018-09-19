<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/**
 * Custom hooks
 *
 * Add whatever action handler is desired and srore the email using ach_store_email
 */


// Sample function - here we use the login email (AC does this already but its a clear example)
// function sample_track_login($login, $user) {
//    ach_store_email($user->user_email);
// }
// add_action('wp_login', 'sample_track_login',  10, 2);
