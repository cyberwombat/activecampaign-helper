<?php
if (!defined('ABSPATH')) {
    exit;
}


if (! class_exists('AC_Helper_Handler', false)) :

/**
 * Admin settings
 */
class AC_Helper_Handler
{
    /**
     * Initialize
     */
    public function __construct()
    {
        add_action('init', array($this, 'ach_start_session'), 1);
        add_action('wp_ajax_ach_track', __CLASS__ . '::ach_ajax_callback_track');
        add_action('wp_ajax_nopriv_ach_track', __CLASS__ . '::ach_ajax_callback_track');
        add_action('wp_ajax_ach_event', __CLASS__ . '::ach_ajax_callback_event');
        add_action('wp_ajax_nopriv_ach_event', __CLASS__ . '::ach_ajax_callback_event');
        add_action('wp_ajax_ach_email', __CLASS__ . '::ach_ajax_callback_email');
        add_action('wp_ajax_nopriv_ach_email', __CLASS__ . '::ach_ajax_callback_email');
        add_action('ach_subscribe', __CLASS__ . '::ach_subscribe', 10, 2);
        add_action('ach_store_email', __CLASS__ . '::ach_store_email', 10, 1);
        add_action('wp_login', __CLASS__ . '::ach_init_email');
    }

    /**
     *  Init sessions if needed
    */
    public function ach_start_session()
    {
        if (!session_id()) {
            @session_start();
        }
    }

    /**
     * Ajax handler - accepts an email from JS
     */
    public static function ach_ajax_callback_email()
    {
        if (wp_verify_nonce($_REQUEST['security'], 'ach_email')) {
            if ($_REQUEST['email']) {
                $success = self::ach_store_email($_REQUEST['email']);
                if ($success) {
                    return wp_send_json(array( 'success' => true, 'email' => $_REQUEST['email']));
                }
                return wp_send_json_error('Invalid email provided');
            }
        }
        wp_send_json_error('Invalid nonce');
    }

    /**
     * Ajax handler - track event
     */
    public static function ach_ajax_callback_event()
    {
        if (wp_verify_nonce($_REQUEST['security'], 'ach_event')) {
            if ($_REQUEST['name']) {
                $success = self::ach_send_event($_REQUEST['name'], $_REQUEST['value'], $_REQUEST['email']);
                if ($success) {
                    return wp_send_json(array( 'success' => true, 'result' => $success));
                }
                return wp_send_json_error('Unable to send event');
            }
        }
        wp_send_json_error('Invalid nonce');
    }

    /**
     * Ajax handler - accepts tracking option
     */
    public static function ach_ajax_callback_track()
    {
      if (wp_verify_nonce($_REQUEST['security'], 'ach_track')) {
            $track = $_REQUEST['tracking'] == 'false'  ? false : true;
            self::ach_set_tracking($track);
            return wp_send_json(array( 'success' => true, 'tracking' => $track ? 'on' : 'off'));
        }
        wp_send_json_error('Invalid nonce');
    }


    /**
     * Initialie with logged in user if avail
     */
    public static function ach_init_email()
    {
        // Don't overwrite
        if (!self::ach_has_email()) {
            $current_user = wp_get_current_user();
            if (isset($current_user->data->user_email)) {
                return self::ach_store_email($current_user->data->user_email);
            }
        }
        return false;
    }

    /**
     * Send event
     *
     * @param string $name - event name
     * @param array $value - event value
     * @param string $email - optional email
     * @return boolean success
     */
    public static function ach_send_event($name, $value, $email)
    {
        $api = new AC_Helper_API();
         return $api->send_event($name, $value, $email);
    }
    
    /**
     * Subscribe user
     *
     * @param string $email
     * @param array $fields - optional extra params for contact_sync
     * @return boolean success
     */
    public static function ach_subscribe($email, $fields = array())
    {
        $api = new AC_Helper_API();
         return $api->subscribe_to_list($email, $fields);
    }

    /**
     * Store email in session
     *
     * @param string $email - email to store
     */
    public static function ach_store_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['ach_email'] = $email;
            return true;
        }
        return false;
    }

    /**
     * Return stored email from session
     *
     * @return string $email
     */
    public static function ach_get_email()
    {
        return self::ach_has_email() ? $_SESSION['ach_email'] : null;
    }

    /**
     * Check if we have email stored
     *
     * @return boolean
     */
    public static function ach_has_email()
    {
        return !empty($_SESSION['ach_email']);
    }

    /**
     * Set tracking allowed
     *
     * @param boolean
     */
    public static function ach_set_tracking($flag)
    {
        $_SESSION['ach_tracking'] = !!$flag;
    }

    /**
     * Return tracking preference
     *
     * @return boolean
     */
    public static function ach_get_tracking()
    {
        return empty($_SESSION['ach_tracking']) ? false : $_SESSION['ach_tracking'];
    }
}

return new AC_Helper_Handler;
endif;
