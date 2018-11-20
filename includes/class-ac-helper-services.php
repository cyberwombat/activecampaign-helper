<?php
if (!defined('ABSPATH')) {
    exit;
}
if (! class_exists('AC_Helper_Services', false)) :
/**
 * Subscription box hook handling
 */
class AC_Helper_Services
{
    /**
     * Initialize
     */
    public function __construct()
    {
        $options = (array)get_option("ac_helper_locations");
        
        if (in_array('do_wp_register', $options)) {
            add_action('register_form', array($this, 'custom_registration_form_handler'));
        }
        add_action('user_register', array($this, 'custom_registration_handler'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'custom_checkout_handler'));
        if (in_array('do_woo_checkout', $options)) {
            add_action( 'wp_head', array($this, 'load_styles' ), 0 );
            add_filter('woocommerce_checkout_fields', array($this, 'custom_override_checkout_fields'));
        }
        if (in_array('do_woo_register', $options)) {
            add_action('woocommerce_register_form', array($this, 'custom_woo_registration_form_handler'));
        }
        add_action('woocommerce_created_customer', array($this, 'custom_woo_registration_handler'));
    }

    /**
     * Add inline styles to adjust things
     */
    public function load_styles()
    {
        // Remove the (optional) in Woo checkout (why is a checkbox marked optional is beyong me)
        echo "<style>.ach__label .optional { display:none; }</style>";        
    }

    /**
     * Add a subscribe checkbox to registration form
     */
    public function custom_registration_form_handler()
    {
        // Modify register form to have a suscribe to newsletter checkbox?>
        <p>
            <label for="subscribe" class="ach__label">
                <input type="checkbox" name="subscribe" id="subscribe" class="input-checkbox ach__input" checked />
                <?php _e(get_option('ac_helper_subscribe_label'), 'ac-helper') ?>
              </label><br><br>
        </p>
        <?php
    }

    /**
     * On registration store email in AC tracking code and subscribe to list
     **/
    public function custom_registration_handler($user_id)
    {
        if (isset($_POST['subscribe']) && $_POST['subscribe']) {
          do_action('ach_subscribe', $_POST['email']);       
        }
         do_action('ach_store_email', $_POST['email']);
    }

    /**
     * Update AC with checkout email
     **/
    public function custom_checkout_handler($order_id)
    {
        if (isset($_POST['subscribe']) && $_POST['subscribe']) {
             do_action('ach_subscribe',$_POST['billing_email']);       
        }
         do_action('ach_store_email', $_POST['billing_email']);
    }

    /**
     * Add subscribe checkbox to checkout
     *
     * @param array $fields
     * @return array $fields
     **/
    public function custom_override_checkout_fields($fields)
    {
        $fields['billing']['subscribe'] = array(
          'type' => 'checkbox',
          'label' => __(get_option('ac_helper_subscribe_label'), 'ac-helper'),
          'label_class' => array('ach__label woocommerce-form__label', 'woocommerce-form__label-for-checkbox'),
          'required' => false,
          'input_class' => array('ach__input woocommerce-form__input', 'woocommerce-form__input-checkbox', 'input-checkbox'),
          'clear'     => true,
          'required' => false
         );

        return $fields;
    }

    /**
     * Add subscribe box to Woo registration form (my-account)
     */
    public function custom_woo_registration_form_handler()
    {
        ?>
        <p class="form-row form-row-wide">
           <label class="ach__label woocommerce-form__label woocommerce-form__label-for-checkbox ">
              <input class="ach__input woocommerce-form__input woocommerce-form__input-checkbox" name="subscribe" type="checkbox" id="subscribe" value="forever"> <span> <?php _e(get_option('ac_helper_subscribe_label'), 'ac-helper') ?></span>
            </label>
        </p>
        <?php
    }

    /**
     * Save the extra register fields.
     *
     * @param int $customer_id Current customer ID.
     * @return void
     */
    public function custom_woo_registration_handler($customer_id)
    {
        if (isset($_POST['susbcribe'])) {
           do_action('ach_subscribe',$_POST['email']);       
        }
         do_action('ach_store_email', $_POST['email']);         
    }
}
return new AC_Helper_Services;
endif;
