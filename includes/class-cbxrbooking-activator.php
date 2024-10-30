<?php

	/**
	 * Fired during plugin activation
	 *
	 * @link       http://codeboxr.com
	 * @since      1.0.0
	 *
	 * @package    CBXRBooking
	 * @subpackage CBXRBooking/includes
	 */

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    CBXRBooking
	 * @subpackage CBXRBooking/includes
	 * @author     Codeboxr <info@codeboxr.com>
	 */
	class CBXRBooking_Activator {

		/**
		 * Short Description. (use period)
		 *
		 * Long Description.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {
			//check if can activate plugin
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
			check_admin_referer( "activate-plugin_{$plugin}" );

			//create tables
			CBXRBookingHelper::create_table();

			set_transient( 'cbxrbooking_upgraded_notice', 1 );
		}//end activate

	}//end class CBXRBooking_Activator
