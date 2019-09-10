<?php
if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('AC_Helper_Track', false)) :


/**
 * AC tracking handler
 */
class AC_Helper_Track
{
    /**
     * Initialize
     */
    public function __construct()
    {
        add_filter('script_l10n', array($this, 'ach_track'), 100, 3);
        add_action('wp_enqueue_scripts', array($this, 'ach_enqueue_scripts'));
    }

    /**
     * Create a wp_localize_scripts filter to modify the AC tracking code
     *
     * @param array $data
     * @param string $handle
     * @param string $object_name
     * @return array filtered data  */

    public function ach_track($data, $handle, $object_name)
    {
        if ('site_tracking' == $handle && 'php_data' == $object_name && AC_Helper_Handler::ach_has_email()) {
            AC_Helper::log('Native AC intercepted');
            $data['user_email'] = AC_Helper_Handler::ach_get_email();
        }
        return $data;
    }

    /**
     * Load a localized JS handler we can modify to capture emails
     */
    public function ach_enqueue_scripts()
    {
        wp_enqueue_script('ach_handler', plugin_dir_url(__FILE__) . '../js/ac_helper.js', array( 'jquery' ), false, true);

        $ach_params = array(
            "debug" => get_option('ac_helper_debug'), //defined('SCRIPT_DEBUG') && SCRIPT_DEBUG &&
            "trackid" => get_option('ac_helper_track_id'),
            "site_tracking" => 0,
            "user_email" => AC_Helper_Handler::ach_get_email(),
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => array(
              'email' =>  wp_create_nonce('ach_email'),
              'track' =>  wp_create_nonce('ach_track'),
              'event' =>  wp_create_nonce('ach_event')
            )
        );

     
        $tracking = get_option("ac_helper_tracking")[0];

        
        // If native AC plugin installed let's check tracking
        $disable = false;
        if (class_exists('ActiveCampaignWordPress')) {
            $ac = get_option("settings_activecampaign");
            $disable = @$ac['site_tracking'] == 1;
        }

        if ($disable) {
            AC_Helper::log('Native AC enabled w tracking so disabling plugin tracking');
            $track = false;
        } else {
            $track = ($tracking == 'always') || ($tracking == 'permission' && AC_Helper_Handler::ach_get_tracking());

            AC_Helper::log('Tracking set to '.$tracking);

            if ($tracking == 'permission') {
                AC_Helper::log('Tracking approval '.(AC_Helper_Handler::ach_get_tracking() ? '' : 'not') . ' received');
            }
        }
      
        $ach_params["site_tracking"] = $track;

        AC_Helper::log('Setting track flag to ' . ($track ? 'on' : 'off'));
        

        wp_localize_script('ach_handler', 'ach_params', $ach_params);
    }
}

return new AC_Helper_Track;
endif;
