<?php
	/**
	 * Provide a dashboard view for the plugin
	 *
	 * This file is used to markup the booking logs manager
	 *
	 * @link       http://codeboxr.com
	 * @since      1.0.7
	 *
	 * @package    cbxrbooking
	 * @subpackage cbxrbooking/admin/templates
	 */

	if ( ! defined( 'WPINC' ) ) {
		die;
	}
?>

<?php


	$cbxrbookinglog = new CBXRestaurantBookingLog_List_Table();

	//Fetch, prepare, sort, and filter CBXRestaurantBookingLog data
	$cbxrbookinglog->prepare_items();
?>

<div class="wrap cbxrbookinglog_wrapper">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Restaurant Booking: Log Manager', 'cbxrbooking' ); ?>
	</h1>
    <div class="cbxrbookinglog_button_wrapper">
	<a href="#"
	   class="page-title-action addnewbooking_wrappanel_trig"><?php echo esc_html__( 'Add New Booking', 'cbxrbooking' ); ?></a>
	<?php if ( defined( 'CBXRBOOKINGPROADDON_PLUGIN_NAME' ) ) : ?>
		<a target="_blank" href="<?php echo admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogscalender' ); ?>"
		   class="page-title-action button-default"><?php echo esc_html__( 'Booking Calendar View', 'cbxrbookingproaddon' ); ?></a>
	<?php endif; ?>
    </div>
	<div id="addnewbooking_wrappanel">

		<?php
			if ( is_array( $booking_forms ) && sizeof( $booking_forms ) > 0 ) {
                echo '<p>'.esc_html__('Choose booking form', 'cbxrbooking').': ';
				//echo '<ul class="cbxrbookinglog_admin_logforms_sels">';
				foreach ( $booking_forms as $booking_forms_id => $booking_forms_title ) {
					echo '<span class="cbxrbookinglog_admin_logforms_sel"><a class="" target="_blank"  href="' . esc_url( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&form_id=' . $booking_forms_id . '&log_id=0' ) ) . '" >' . stripslashes( $booking_forms_title ) . '</a></span>';
				}

				//echo '</ul>';
				'</p>';
			} else {
				$url = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&log_id=0' );
				echo '<div class="notice notice-warning inline">';
				echo '<p>' . esc_html__( 'No active booking form found.', 'cbxrbooking' ) . ' <a target="_blank" href="' . admin_url( 'edit.php?post_type=cbxrbooking' ) . '" class="">' . esc_html__( 'Add New Booking Form', 'cbxrbooking' ) . '</a><span class="clear clearfix"></span></p>';
				echo '<div class="clear clearfix"></div>';
				echo '</div>';
			}
		?>
	</div>


	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<div class="inside">

							<form class="cbxrbooking_logs" id="cbxrbooking_logs" method="post">
								<?php $cbxrbookinglog->views(); ?>

								<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
								<input type="hidden" name="post_type" value="cbxrbooking" />
								<?php $cbxrbookinglog->search_box( esc_html__( 'Search Log', 'cbxrbooking' ), 'bookinglogsearch' ); ?>

								<?php
									$cbxbetweendates = isset( $_REQUEST['date_range'] ) ? $_REQUEST['date_range'] : 'all';
									$status          = ( isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all' );

									$cbxrblogfromDate = isset( $_REQUEST['cbxrblogfromDate'] ) ? $_REQUEST['cbxrblogfromDate'] : date( 'Y-m-01 H:i:s' );
									$cbxrblogtoDate   = isset( $_REQUEST['cbxrblogtoDate'] ) ? $_REQUEST['cbxrblogtoDate'] : date( 'Y-m-d H:i:s' );
								?>
								<input type="hidden" name="date_range" value="<?php echo $cbxbetweendates; ?>"
									   id="date_range_input" />
								<input type="hidden" name="status" value="<?php echo $status; ?>" />

								<div id="cbx-between-dates-toggle" class="cbx-between-dates-toggle clear"
									 style="display: none; float: none !important;">

									<?php esc_html_e( 'From', 'cbxrbooking' ); ?>
									<input type="text" id="cbxrblogfromDate" name="cbxrblogfromDate"
										   style="width:12%;"
										   value="<?php echo $cbxrblogfromDate; ?>"
										   class="cbxrblogfromDate" />
									<?php esc_html_e( 'To', 'cbxrbooking' ); ?>
									<input type="text" id="cbxrblogtoDate" name="cbxrblogtoDate"
										   style="width:12%;"
										   value="<?php echo $cbxrblogtoDate; ?>"
										   class="cbxrblogtoDate" />
									<input type="submit" name="cbxfilter_action"
										   id="cbx-post-query-submit" class="button" value="Filter">
								</div>
								<?php $cbxrbookinglog->display() ?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>