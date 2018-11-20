<?php
/**
 * AC Woo Helper Settings
 * Thanks https://raw.githubusercontent.com/rayman813/smashing-custom-fields/master/smashing-fields-approach-1/smashing-fields.php
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('AC_Helper_Settings', false)) :

/**
 * WC_Settings_Products.
 */
class AC_Helper_Settings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    
    public function __construct()
    {
        // Hook into the admin menu
        add_action('admin_menu', array( $this, 'add_plugin_page' ));

        // Add Settings and Fields
        add_action('admin_init', array( $this, 'setup_sections' ));
        add_action('admin_init', array( $this, 'setup_fields' ));

      ;
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'AC Helper',
            'manage_options',
            'ach_settings',
            array( $this, 'create_admin_page' )
        );
    }



    public function create_admin_page()
    {
        ?>
      <div class="wrap">
        <h2>ActiveCampaign Helper Settings</h2>
        <?php
            if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
                // $this->admin_notice();
            } ?>
        <form method="POST" action="options.php">
                <?php
                    settings_fields('ach_fields');
        do_settings_sections('ach_fields');
        submit_button(); ?>
        </form>
      </div> <?php
    }
    
    public function admin_notice()
    {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }

    public function setup_sections()
    {
        add_settings_section('ac_section', 'ActiveCampaign Settings', array( $this, 'section_callback' ), 'ach_fields');
        add_settings_section('general_section', 'General Preferences', array( $this, 'section_callback' ), 'ach_fields');
        add_settings_section('advanced_section', 'Advanced Options', array( $this, 'section_callback' ), 'ach_fields');
    }

    public function section_callback($arguments)
    {
        switch ($arguments['id']) {
        case 'ac_section':
          echo 'Your ActiveCampaign <a href="http://www.activecampaign.com/help/using-the-api/" target="_blank" title="View the API documentation on ActiveCampaign website">API key and URL</a> can be obtained in your ActiveCampaign dashboard, under Settings > Developer > API Access.';
          break;
        case 'general_section':
          echo 'General preferences and options.';
          break;
        case 'advanced_section':
          echo 'This section is for code ninjas.';
          break;
      
      }
    }

    public function setup_fields()
    {
        $fields = array(
          array(
            'uid' => 'ac_helper_api_key',
            'label' => 'API Key',
            'section' => 'ac_section',
            'type' => 'text',
            'placeholder' => '',
            'helper' => '(required)',
            'supplemental' => 'Your ActiveCampaign API key.',
          ),
          array(
            'uid' => 'ac_helper_api_url',
            'label' => 'API URL',
            'section' => 'ac_section',
            'type' => 'text',
            'placeholder' => '',
            'helper' => '(required)',
            'supplemental' => 'Your ActiveCampaign API URL.',
          ),

          array(
            'uid' => 'ac_helper_list_id',
            'label' => 'List ID',
            'section' => 'ac_section',
            'type' => 'text',
            'placeholder' => '',
            'helper' => '(required for subscriptions)',
            'supplemental' => 'The default ActiveCampaign list ID to subscribe to.',
          ),

          array(
            'uid' => 'ac_helper_tracking',
            'label' => 'Tracking',
            'section' => 'ac_section',
            'type' => 'select',
            'options' => array(
              'always' => 'Always track',
              'permission' => 'Track with permission',
              'none' => 'Do not track'
            ),
            'supplemental' => 'Enable ActiveCampaign snippet installation and tracking. See docs for permission handling.',
            'default' => array('none')
          ),

          array(
            'uid' => 'ac_helper_subscribe_label',
            'label' => 'Subscribe Text',
            'section' => 'general_section',
            'type' => 'text',
            'css' => 'width: 300px',
            'default' => 'Subscribe to our newsletter',
            'supplemental' => 'The label next to subscribe checkbox presented to users.',
          ),

          array(
            'uid' => 'ac_helper_locations',
            'label' => 'Locations',
            'section' => 'general_section',
            'type' => 'checkbox',
            'options' => array(
              'do_woo_checkout' => 'WooCommerce checkout',
              'do_woo_register' => 'WooCommerce user registration',
              'do_wp_register' => 'WP user registration'
            ),
            'default' => array('woo_checkout', 'woo_register', 'wp_register'),
            'supplemental' => 'Locations where a subscribe checkbox will be presented to user.',
          ),

          array(
            'uid' => 'ac_helper_debug',
            'label' => 'Debug',
            'section' => 'advanced_section',
            'type' => 'checkbox',
            'options' => array(
              'debug' => 'Log debug messages in WP error log'
            ),
            'default' => array()
          ),


          array(
            'uid' => 'ac_helper_event_key',
            'label' => 'Event Key',
            'section' => 'advanced_section',
            'type' => 'text',
            'placeholder' => '',
            'helper' => '(required for tracking events using JS)',
            'supplemental' => 'This key can be found under Settings > Tracking in the AC dashboard.',
          ),


        );
        foreach ($fields as $field) {
            add_settings_field($field['uid'], $field['label'], array( $this, 'field_callback' ), 'ach_fields', $field['section'], $field);
            register_setting('ach_fields', $field['uid']);
        }
    }

    public function field_callback($arguments)
    {
        $value = get_option($arguments['uid']);

        if (! $value) {
            $value = @$arguments['default'];
        }

        switch ($arguments['type']) {
            case 'text':
            case 'password':
            case 'number':
                printf('<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" style="%5$s"/>', $arguments['uid'], $arguments['type'], @$arguments['placeholder'], $value, @$arguments['css']);
                break;
            case 'textarea':
                printf('<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50" style="%5$s">%3$s</textarea>', $arguments['uid'], @$arguments['placeholder'], $value, @$arguments['css']);
                break;
            case 'select':
            case 'multiselect':
                if (! empty($arguments['options']) && is_array($arguments['options'])) {
                    $attributes = '';
                    $options_markup = '';
                    $v = sizeof($value) ? $value : $arguments['default'];
                    foreach ($arguments['options'] as $key => $label) {
                        $options_markup .= sprintf('<option value="%s" %s>%s</option>', $key, selected($v[ array_search($key, $v, true) ], $key, false), $label);
                    }
                    if ($arguments['type'] === 'multiselect') {
                        $attributes = ' multiple="multiple" ';
                    }
                    printf('<select name="%1$s[]" id="%1$s" %2$s style="%4$s">%3$s</select>', $arguments['uid'], $attributes, $options_markup, @$arguments['css']);
                }
                break;
            case 'radio':
            case 'checkbox':
                if (! empty($arguments['options']) && is_array($arguments['options'])) {
                    $options_markup = '';
                    $iterator = 0;
                    foreach ($arguments['options'] as $key => $label) {
                        $iterator++;
                        $options_markup .= sprintf('<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked(@$value[ array_search($key, $value, true) ], $key, false), $label, $iterator);
                    }
                    printf('<fieldset>%s</fieldset>', $options_markup);
                }
                break;
        }

        if ($helper = @$arguments['helper']) {
            printf('<span class="helper"> %s</span>', $helper);
        }

        if ($supplemental = @$arguments['supplemental']) {
            printf('<p class="description">%s</p>', $supplemental);
        }
    }
}

   return new AC_Helper_Settings();

  endif;
