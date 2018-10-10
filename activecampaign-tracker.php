<?php
/*
Plugin Name:  Active Campaign Helper
Description:  Modifies Active Campaign tracking code to allow insertion of email address from other sources such as hooks - useful when user is not logged in yet we have access to their email such as WooCommerce checkout hook.
Version:      0.0.1
Author:       cyberwombat
Author URI:   https://github.com/cyberwombat
*/

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 *  Init sessions if needed
 */
function ach_start_session()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'ach_start_session', 1);

/**
 * Extension of WP_Scripts to allow filtering wp_localize_scripts
 */
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

/**
 * Create a wp_localize_scripts filter to modify the AC tracking code
 *
 * @param array $data
 * @param string $handle
 * @param string $object_name
 * @return array filtered data  */

function ach_track($data, $handle, $object_name)
{ //&& !isset($data['user_email'])
    if ('site_tracking' == $handle && 'php_data' == $object_name && ach_has_email()) {
        $data['user_email'] = ach_get_email();
    }
    return $data;
}
add_filter('script_l10n', 'ach_track', 100, 3);


/**
 * Load a localized JS handler we can modify to capture emails
 */
function ach_enqueue_scripts()
{
    wp_enqueue_script(
        'ach_handler',
        plugin_dir_url(__FILE__) . '/js/ach.js',
        array( 'jquery' ),
        false,
        true
    );

    $ach_params = array(
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce' =>  wp_create_nonce('ach_track')
    );

    wp_localize_script('ach_handler', 'ach_params', $ach_params);
}

add_action('wp_enqueue_scripts', 'ach_enqueue_scripts');


/**
 * Ajax handler - accepts an email from JS
 */
function ach_ajax_callback()
{
    if (wp_verify_nonce($_REQUEST['security'], 'ach_track')) {
        ach_store_email($_REQUEST['email']);
        wp_send_json($_REQUEST['email']);
    }
}
add_action('wp_ajax_ach_track', 'ach_ajax_callback');
add_action('wp_ajax_nopriv_ach_track', 'ach_ajax_callback');


/**
 * Store email in session
 *
 * @param string $email - email to store
 */
function ach_store_email($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['ac_user_email'] = $email;
    }
}
// Create an action for this
add_action('ach_store_email', 'ach_store_email', 10, 1);

/**
 * Return stored email from session
 *
 * @return string $email
 */
function ach_get_email()
{
    return $_SESSION['ac_user_email'];
}

/**
 * Check if we have email stored
 *
 * @return boolean
 */

function ach_has_email()
{
    return !empty($_SESSION['ac_user_email']);
}

/**
 * Susbcribe to a list
 *
 * Use `ach_subscribe` action to subscribe an email
 *
 * @param string $url - list POST action url
 * @param string $email - email to subscribe
 * @param string $form - form number (the u or f param in AC forms)
 * @param array @params - array of params from hidden form fields 
 * @return
 */
function ach_subscribe($url, $email, $form, $params = array())
{
    // Not sure if these are consistent across board - let's set them up but allow override
    $defaults = array('c' => '0', 'm' => '0', 'act' => 'sub' , 'v' => '2', 'u' => $form, 'f' => $form);
    $data = array_merge($defaults, $params, array('email' => $email));

    // use key 'http' even if you send the request to https://...
    $options = array(
      'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
      )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
}

// Create an action for this
add_action('ach_subscribe', 'ach_subscribe', 10, 4);
