<?php

/**
 * Fired during plugin uninstall
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXRBooking
 * @subpackage CBXRBooking/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    CBXRBooking
 * @subpackage CBXRBooking/includes
 * @author     Codeboxr <info@codeboxr.com>
 */
class CBXRBooking_Uninstall {

	/**
	 * remove all created database and option value
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {
		global $wpdb;

		$settings = new CBXRBooking_Settings_API( );


		$delete_global_config = $settings->get_option( 'delete_global_config', 'cbxrbooking_tools', 'no' );
		if ( $delete_global_config == 'yes' ) {

			//delete plugin global options
			$option_values = CBXRBookingHelper::getAllOptionNames();
			foreach ($option_values as $option_value ){
				delete_option($option_value['option_name']);
			}

			//delete tables created by this plugin
			$table_names = CBXRBookingHelper::getAllDBTablesList();
			$sql = "DROP TABLE IF EXISTS " . implode(', ', array_values($table_names));
			$query_result = $wpdb->query($sql);

			//deleted all 'cbxrbooking' type form posts
			global $post;
			$args = array( 'posts_per_page' => -1, 'post_type' => 'cbxrbooking', 'post_status' => 'any');

			$myposts = get_posts( $args );
			foreach ( $myposts as $post ) : CBXRBookingHelper::setup_postdata( $post );
				$post_id = intval($post->ID);
				//delete the post
				wp_delete_post( $post_id, true );
			endforeach;
			CBXRBookingHelper::wp_reset_postdata();

		}//if allowed to delete

	}//end uninstall
}//end class CBXRBooking_Uninstall