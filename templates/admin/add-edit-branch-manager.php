<?php
	/**
	 * Provide a dashboard view for the plugin
	 *
	 * This file is used to markup the backend Add/Edit branch
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
	global $wpdb;
	$counter = 1;
	if ( isset( $_GET['id'] ) && intval( $_GET['id'] ) > 0 ) {
		$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

		$bm_id = absint( $_GET['id'] );
		$data  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $cbxrb_bm_table_name WHERE id=%d", $bm_id ), 'ARRAY_A' );

		$addressinfo = unserialize( $data['address'] );
	}

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<div id='cbxrbloading' style='display:none'></div>
	<div id="icon-options-general" class="icon32"></div>
	<h2>
		<?php esc_html_e( 'Branch Manager', 'cbxrbooking' ); ?>
		<?php echo '<a class="button button-primary button-large" href="' . admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager' ) . '">' . esc_html__( 'Back to Lists', 'cbxrbooking' ) . '</a>'; ?>
	</h2>
	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<div id="cbxrb_branch_manager" class="meta-box-sortables ui-sortable">

					<div class="postbox">
						<h3><span><?php esc_html_e( 'Add/Edit Branch', 'cbxrbooking' ); ?></span></h3>
						<div class="inside">
							<form id="cbxrb-bm-account-form" action="" method="post">

								<div class="cbxrb-msg-box below-h2 hidden"><p class="cbxrb-msg-text"></p></div>
								<?php isset( $data['id'] ) ? $id = $data['id'] : $id = 0; ?>
								<input name="cbxrb-bm-acc-id" id="cbxrb-bm-acc-id" type="hidden"
									   value="<?php echo $id; ?>" />
								<?php wp_nonce_field( 'add_new_acc', 'new_acc_verifier' ); ?>
								<table class="form-table">
									<?php isset( $data['name'] ) ? $name = $data['name'] : $name = ""; ?>
									<tr valign="top">
										<th class="row-title" scope="row">
											<label class="cbxrb-bm-acc cbxrb-bm-label"
												   for="name"><?php esc_html_e( 'Branch Name', 'cbxrbooking' ); ?></label>
										</th>

										<td><input name="name" id="name" type="text" value="<?php echo $name; ?>"
												   class="cbxrb-bm-acc regular-text" required /></td>
									</tr>

									<?php isset( $data['description'] ) ? $description = $data['description'] : $description = ""; ?>
									<tr valign="top">
										<th class="row-title" scope="row">
											<label class="cbxrb-bm-acc cbxrb-bm-label"
												   for="description"><?php esc_html_e( 'Description', 'cbxrbooking' ); ?></label>
										</th>
										<td>
                                            <textarea name="description" id="description" cols="50" rows="6"
													  class="cbxrb-bm-acc regular-text"><?php echo $description; ?></textarea>
										</td>
									</tr>

									<tr valign="top">
										<th class="row-title" scope="row" colspan="2">
											<h3><?php esc_html_e( 'Address', 'cbxrbooking' ); ?></h3>
										</th>
									</tr>

									<?php isset( $addressinfo['line'] ) ? $address_line = $addressinfo['line'] : $address_line = ""; ?>
									<tr valign="top" class="cbxrb_bm_address_details">
										<th class="row-title" scope="row">
											<label class="cbxrb-bm-acc cbxrb-bm-label"
												   for="address_line"><?php esc_html_e( 'Address Line', 'cbxrbooking' ); ?></label>
										</th>
										<td>
											<input name="address[line]" id="address_line" type="text"
												   value="<?php echo $address_line; ?>"
												   class="cbxrb-bm-acc-line regular-text" />

										</td>
									</tr>

									<?php isset( $addressinfo['city'] ) ? $address_city = $addressinfo['city'] : $address_city = ""; ?>
									<tr valign="top" class="cbxrb_bm_address_details">
										<th class="row-title" scope="row">
											<label class="cbxrb-bm-acc cbxrb-bm-label"
												   for="address_city"><?php esc_html_e( 'City', 'cbxrbooking' ); ?></label>
										</th>
										<td>
											<input name="address[city]" id="address_city" type="text"
												   value="<?php echo $address_city; ?>"
												   class="cbxrb-bm-acc-city regular-text" />
										</td>
									</tr>

									<?php isset( $addressinfo['state'] ) ? $address_state = $addressinfo['state'] : $address_state = ""; ?>
									<tr valign="top" class="cbxrb_bm_address_details">
										<th class="row-title" scope="row">
											<label class="cbxrb-bm-acc cbxrb-bm-label"
												   for="address_state"><?php esc_html_e( 'State/Province/Region', 'cbxrbooking' ); ?></label>
										</th>
										<td>
											<input name="address[state]" id="address_state" type="text"
												   value="<?php echo $address_state; ?>"
												   class="cbxrb-bm-acc-state regular-text" />
										</td>
									</tr>

									<?php isset( $addressinfo['postal'] ) ? $address_postal = $addressinfo['postal'] : $address_postal = ""; ?>
									<tr valign="top" class="cbxrb_bm_address_details">
										<th class="row-title" scope="row">
											<label class="cbxrb-bm-acc cbxrb-bm-label"
												   for="address_postal"><?php esc_html_e( 'Zip/Postal Code', 'cbxrbooking' ); ?></label>
										</th>
										<td>
											<input name="address[postal]" id="address_postal" type="text"
												   value="<?php echo $address_postal; ?>"
												   class="cbxrb-bm-acc-postal regular-text" />
										</td>
									</tr>

									<?php isset( $addressinfo['country'] ) ? $address_country = $addressinfo['country'] : $address_country = ""; ?>
									<tr valign="top">
										<th class="row-title" scope="row">
											<label class="cbxrb-bm-acc cbxrb-bm-label"
												   for="address_country"><?php esc_html_e( 'Country', 'cbxrbooking' ); ?></label>
										</th>
										<td>
											<select name="address[country]" id="address_country" class="chosen-select">
												<option value="none">
													<?php esc_html_e( 'Select Country', 'cbxrbooking' ); ?>
												</option>
												<?php
													foreach ( CBXRBookingHelper::getAllCountries() as $country_code => $country_name ) {

														?><

														<option value="<?php echo $country_code; ?>" <?php if ( $country_code == $address_country ) {
															echo 'selected="selected"';
														} ?> > <?php echo $country_name; ?>
														</option>
													<?php } ?>
											</select>
										</td>
									</tr>

									<tr valign="top">
										<th class="row-title" scope="row"></th>
										<td>
											<input id="cbxrb-new-bm-acc" class="button-primary" type="submit"
												   name="cbxrb-new-bm-acc"
												   data-add-value="<?php esc_html_e( 'Add new Branch', 'cbxrbooking' ); ?>"
												   data-update-value="<?php esc_html_e( 'Update Branch', 'cbxrbooking' ); ?>"
												   value="<?php
												       if ( isset( $data['name'] ) && $data['name'] != '' ) {
													       esc_html_e( 'Update Branch', 'cbxrbooking' );
												       } else {
													       esc_html_e( 'Add new Branch', 'cbxrbooking' );
												       }
											       ?>" />
										</td>
									</tr>
								</table>

							</form>

						</div> <!-- .inside -->
					</div> <!-- .postbox -->

				</div> <!-- .meta-box-sortables .ui-sortable -->
			</div> <!-- post-body-content -->
			<?php
				$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../../' . $this->plugin_basename );
				include( 'sidebar.php' );
			?>
		</div> <!-- #post-body .metabox-holder .columns-2 -->
        <div class="clear clearfix"></div>
	</div> <!-- #poststuff -->
</div> <!-- .wrap -->