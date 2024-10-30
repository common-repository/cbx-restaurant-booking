<?php
	/**
	 * Provide a admin view for the plugin
	 *
	 * This file is used to markup the admin facing error
	 *
	 * @link       http://codeboxr.com
	 * @since      1.0.0
	 *
	 * @package    cbxrbooking
	 * @subpackage cbxrbooking/admin/templates
	 */

	if ( ! defined( 'WPINC' ) ) {
		die;
	}
?>

<div class="wrap">
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<div class="inside">
							<div class="cbxrbookingcustombootstrap cbxrbooking_wrapper cbxrbookingadminform_wrapper">
								<div class="alert alert-danger" role="alert">
									<p><?php echo $error_text; ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
