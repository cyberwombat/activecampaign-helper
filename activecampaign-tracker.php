<?php
/*
Plugin Name:  Active Campaign Helper
Description:  Modifies Active Campaign tracking code to allow insertion of email address from other sources such as hooks - useful when user is not logged in yet we have access to their email such as WooCommerce checkout hook.
Version:      0.0.1
Author:       Enradia
Author URI:   https://enradia.com
*/

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


// Init sessions if needed
function ach_start_session()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'ach_start_session', 1);


// Extension of WP_Scripts to allow filtering wp_localize_scripts
class Filterable_Scripts extends WP_Scripts
{
    public function localize($handle, $object_name, $l10n)
    {
        $l10n = apply_filters('script_l10n', $l10n, $handle, $object_name);
        return parent::localize($handle, $object_name, $l10n);
    }
}

add_action('init', function () {
    $GLOBALS['wp_scripts'] = new Filterable_Scripts;
});

// Create a wp_localize_scripts filter to modify the AC tracking code
function ach_track($data, $handle, $object_name)
{ //&& !isset($data['user_email'])
 if ('site_tracking' == $handle && 'php_data' == $object_name && ach_has_email()) {
     $data['user_email'] = ach_get_email();
 }
    return $data;
}
add_filter('script_l10n', 'ach_track', 100, 3);

// Load a localized JS handler we can modify to capture emails
function ach_enqueue_scripts()
{
    wp_enqueue_script(
        'ach_handler',
        plugin_dir_url(__FILE__) . '/js/ach.js',
        array( 'jquery' )
    );

    $ach_params = array(
      'ajax_url' => admin_url('admin-ajax.php'), 
      'nonce' =>  wp_create_nonce('ach_track')      
    );

    wp_localize_script('ach_handler', 'ach_params', $ach_params);
}

add_action('wp_enqueue_scripts', 'ach_enqueue_scripts');

// Ajax handler - accepts an email from JS
function ach_ajax_callback()
{
    if (wp_verify_nonce($_REQUEST['security'], 'ach_track')) {
        ach_store_email($_REQUEST['email']);
        wp_send_json($_REQUEST['email']);
    }
}
add_action("wp_ajax_ach_track", "ach_ajax_callback");
add_action("wp_ajax_nopriv_ach_track", "ach_ajax_callback");

// Store email in session
function ach_store_email($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['ac_user_email'] = $email;
    }
}

// Fetch session email
function ach_get_email()
{
    return $_SESSION['ac_user_email'];
}

// Check if we have email stored
function ach_has_email()
{
    return !empty($_SESSION['ac_user_email']);
}

// Load custom hooks file
require_once('hooks.php');
