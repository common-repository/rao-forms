<?php

/**
 * Fired during plugin activation
 *
 * @link       https://app.raoforms.com
 * @since      1.0.0
 *
 * @package    RFIP
 * @subpackage RFIP/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    RFIP
 * @subpackage RFIP/includes
 * @author     Your Name <email@example.com>
 */
class RFIP_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$version = get_option( 'rfb_connection_db_version', '1.0' );

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'rao_form_connections';

		$sql = "CREATE TABLE $table_name(
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			form_provider varchar(100) NOT NULL,
			provider_form_id varchar(100) NOT NULL,
			rao_form_id varchar(100) NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);

	}

}
