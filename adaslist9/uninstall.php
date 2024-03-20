<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://web-pro.store
 * @since      1.0.0
 *
 * @package    Adas_Divi
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'khdivi_label_color' );
delete_option( 'khdivi_text_color' );
delete_option( 'khdivi_exportbg_color' );
delete_option( 'khdivi_bg_color' );
delete_option( 'divi_form_id_setting' );
delete_option( 'Enable_data_saving_checkbox' );
delete_option( 'items_per_page' );
