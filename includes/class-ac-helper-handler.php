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
        add_action('wp_login', __CLASS__ . '::ach_init_email', 10, 2);
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
    public static function ach_init_email($user_login, $user)
    {
        return self::ach_store_email($user->user_email);
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
     * Store email in session and optionally cookie
     *
     * @param string $email - email to store
     */
    public static function ach_store_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            AC_Helper::log('Storing email in session');
            $_SESSION['ach_email'] = $email;

            if (self::ach_get_tracking()) {
                AC_Helper::log('Storing email in cookie');
                setcookie('ach_email', $email, time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
            }
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
        $email = null;
     
        if (!empty($_COOKIE['ach_email'])) {
            $_SESSION['ach_email'] = $_COOKIE['ach_email'];
        }

        if (!empty($_SESSION['ach_email'])) {
            $email = $_SESSION['ach_email'];
        }

        AC_Helper::log('Fetching email in session: '.($email ? $email : 'no email found'));
        return $email;
    }

    /**
     * Check if we have email stored
     *
     * @return boolean
     */
    public static function ach_has_email()
    {
        $is = !empty($_SESSION['ach_email']) || !empty($_COOKIE['ach_email']);
        AC_Helper::log('Check if email in session: '.($is ? 'yes' : 'no'));
        return $is;
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
        return empty($_SESSION['ach_tracking']) ? get_option("ac_helper_tracking")[0] : $_SESSION['ach_tracking'];
    }
}

return new AC_Helper_Handler;
endif;
