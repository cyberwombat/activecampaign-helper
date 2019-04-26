<?php
/*
 * Plugin Name: ActiveCampaign Helper
 * Description: Provide easy subscribe options in checkout and registration and add email tracking ability. To get started active the plugin and edit your <a href="options-general.php?page=ach_settings">settings</a>.
 * Author: Enradia
 * Author URI: https://github.com/cyberwombat/activecampaign-helper
 * Version: 0.0.9
 * Requires at least: 4.4
 * Tested up to: 4.9.6
 * WC requires at least: 3.0
 * WC tested up to: 3.4
 * Text Domain: ac-helper
 */



if (!defined('ABSPATH')) {
    exit;
}


/**
 * Required minimums and constants
 */
define('AC_HELPER_VERSION', '0.0.9');
define('AC_HELPER_MIN_PHP_VER', '5.6.0');
define('AC_HELPER_MIN_WC_VER', '3.0.0');
define('AC_HELPER_MAIN_FILE', __FILE__);

if (!class_exists('AC_Helper')):

  class AC_Helper
  {

    /**
     * @var Singleton The reference the *Singleton* instance of this class
     */
      private static $instance;

      /**
       * Returns the *Singleton* instance of this class.
       *
       * @return Singleton The *Singleton* instance.
       */
      public static function get_instance()
      {
          if (null === self::$instance) {
              self::$instance = new self();
          }
          return self::$instance;
      }

      /**
       * Private clone method to prevent cloning of the instance of the
       * *Singleton* instance.
       *
       * @return void
       */
      private function __clone()
      {
      }

      /**
       * Private unserialize method to prevent unserializing of the *Singleton*
       * instance.
       *
       * @return void
       */
      private function __wakeup()
      {
      }

      /**
       * Notices (array)
       * @var array
       */
      public $notices = array();

      /**
       * Protected constructor to prevent creating a new instance of the
       * *Singleton* via the `new` operator from outside of this class.
       */
      protected function __construct()
      {
          add_action('admin_init', array($this, 'check_environment'));
          add_action('admin_notices', array($this, 'admin_notices'), 15);
          add_action('plugins_loaded', array($this, 'init'));
      }

      
      /**
       * Initialize
       */
      public function init()
      {

        // Don't hook anything else in the plugin if we're in an incompatible environment
          if (self::get_environment_warning()) {
              return;
          }

        
          include_once dirname(__FILE__) . '/includes/class-ac-helper-handler.php';
          include_once dirname(__FILE__) . '/includes/class-ac-helper-services.php';
          include_once dirname(__FILE__) . '/includes/class-ac-helper-scripts.php';
          include_once dirname(__FILE__) . '/includes/class-ac-helper-track.php';
          include_once dirname(__FILE__) . '/includes/class-ac-helper-api.php';

          if (is_admin()) {
              include_once dirname(__FILE__) . '/includes/class-ac-helper-settings.php';
          }
       
          load_plugin_textdomain('ac-helper', false, plugin_basename(dirname(__FILE__)) . '/languages');
         
          add_action('init', function () {
              $GLOBALS['wp_scripts'] = new AC_Helper_Scripts;
          }, 0);
      }



      /**
       * Allow this class and other classes to add slug keyed notices (to avoid duplication)
       *
       * @param string slug
       * @param string class
       * @param string message
       */
      public function add_admin_notice($slug, $class, $message)
      {
          $this->notices[$slug] = array(
           'class' => $class,
            'message' => $message,
          );
      }

      /**
       * The backup sanity check, in case the plugin is activated in a weird way,
       * or the environment changes after activation. Also handles upgrade routines.
       */
      public function check_environment()
      {
          $environment_warning = self::get_environment_warning();

          if ($environment_warning && is_plugin_active(plugin_basename(__FILE__))) {
              $this->add_admin_notice('bad_environment', 'error', $environment_warning);
          }

          $track_id = get_option('ac_helper_track_id');

          if (!$track_id && !(isset($_GET['page'], $_GET['section']) && 'wc-settings' === $_GET['page'] && 'ac_helper' === $_GET['section'])) {
              $api_key = get_option('ac_helper_api_key');
                 
              if ($api_key) {
                  $ac = new AC_Helper_API;
                  $id = $ac->fetch_track_id();

                  if ($id) {
                      update_option('ac_helper_track_id', $id);
                  }
              }

              if (!$id) {
                  $setting_link = self::get_setting_link();
                  $this->add_admin_notice('prompt_connect', 'notice notice-warning', sprintf(__('ActiveCampaign Helper is almost ready. To get started, <a href="%s">set your ActiveCampaign credentials</a>.', 'ac-helper'), $setting_link));
              }
          }
      }

      /**
       * Updates the plugin version in db
       *
       * @return bool
       */
      private static function _update_plugin_version()
      {
          delete_option('ac_helper_version');
          update_option('ac_helper_version', AC_HELPER_VERSION);

          return true;
      }

      /**
       * Handles upgrade routines.
       */
      public function install()
      {
          if (!defined('AC_HELPER_INSTALLING')) {
              define('AC_HELPER_INSTALLING', true);
          }

          $this->_update_plugin_version();
      }

      /**
       * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
       * found or false if the environment has no problems.
       */
      public static function get_environment_warning()
      {
          if (version_compare(phpversion(), AC_HELPER_MIN_PHP_VER, '<')) {
              $message = __('ActiveCampaign Susbcriber - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'ac-helper');

              return sprintf($message, AC_HELPER_MIN_PHP_VER, phpversion());
          }

          

          return false;
      }

      /**
       * Adds plugin action links
       */
      public function plugin_action_links($links)
      {
          $setting_link = self::get_setting_link();

          $plugin_links = array(
            '<a href="' . $setting_link . '">' . __('Settings', 'ac-helper') . '</a>',
        );
          return array_merge($plugin_links, $links);
      }

      /**
       * Get plugin setting link.
       *
       * @return string Setting link
       */
      public function get_setting_link()
      {
          return admin_url('admin.php?page=ach_settings');
      }

      /**
       * Display admin notices and warnings
       */
      public function admin_notices()
      {
          foreach ((array) $this->notices as $notice_key => $notice) {
              echo "<div class='" . esc_attr($notice['class']) . "'><p>";
              echo wp_kses($notice['message'], array('a' => array('href' => array())));
              echo '</p></div>';
          }
      }

      
      /**
       * What rolls down stairs
       * alone or in pairs,
       * and over your neighbor's dog?
       * What's great for a snack,
       * And fits on your back?
       * It's log, log, log
       */
      public static function log($log)
      {
          if (get_option('ac_helper_debug') || defined('WP_DEBUG') && WP_DEBUG) {
              if (is_array($log) || is_object($log)) {
                  error_log(print_r($log, true));
              } else {
                  error_log('AC Helper - ' . $log);
              }
          }
      }
  }

  $GLOBALS['ac_helper'] = AC_Helper::get_instance();

endif;
