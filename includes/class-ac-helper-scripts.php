<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Extension of WP_Scripts to allow filtering wp_localize_scripts
 */
class AC_Helper_Scripts extends WP_Scripts
{
    public function localize($handle, $object_name, $l10n)
    {
        $l10n = apply_filters('script_l10n', $l10n, $handle, $object_name);
        return parent::localize($handle, $object_name, $l10n);
    }
}
