<?php
	/**
	 * Provide a dashboard view for the plugin
	 *
	 * This file is used to markup the admin setting page
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
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php esc_html_e( 'CBX Booking: Setting', 'cbxrbooking' ); ?></h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<div class="inside">
							<?php
								$this->settings_api->show_navigation();
								$this->settings_api->show_forms();
							?>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div>
				<!--<div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <h3><span><?php /*esc_html_e( 'CBX Restaurant Booking Addons', 'cbxrbooking' ); */ ?></span></h3>
                        <div class="inside">
                            <div id="cbxrbooking_addon_wrap">
                                <div class="cbxbookmark_addon cbxbookmark_addon_proaddon">
                                    <div class="addons-banner-block-item-icon">
                                        <a href="https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking" target="_blank">
                                            <img src="https://codeboxr.com/wp-content/uploads/productshots/11780-profile.png"
                                                 alt="CBX Restaurant Booking Pro Addon"/>
                                        </a>
                                    </div>
                                    <div class="addons-banner-block-item-content">
                                        <h3><a href="https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
                                               target="_blank">CBX Restaurant Booking Pro Addon</a></h3>
                                        <p>Pro features for CBX Restaurant Booking. Multiple booking form, multiple branch, export booking logs etc and more.</p>
                                        <a target="_blank" class="button button-primary"
                                           href="https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
                                            From: $20 </a>
                                    </div>
                                </div>
                                <div class="cbxbookmark_addon cbxbookmark_addon_mycred">
                                    <div class="addons-banner-block-item-icon">
                                        <a href="https://codeboxr.com/product/cbx-restaurant-booking-frontend-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
                                           target="_blank">
                                            <img src="https://codeboxr.com/wp-content/uploads/productshots/11782-profile.png"
                                                 alt="CBX Restaurant Booking Frontend Addon"/>
                                        </a>
                                    </div>
                                    <div class="addons-banner-block-item-content">
                                        <h3><a href="https://codeboxr.com/product/cbx-restaurant-booking-frontend-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
                                               target="_blank">CBX Restaurant Booking Frontend Addon</a></h3>
                                        <p>This addon plugin helps to manage booking from frontend. Give access to booking manager to your staffs without giving access to admin panel.</p>
                                        <a target="_blank" class="button button-primary"
                                           href="https://codeboxr.com/product/cbx-restaurant-booking-frontend-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
                                            From: $20 </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->

			</div> <!-- post-body-content -->
			<?php
            //include( 'sidebar.php' );
			include(cbxrbooking_locate_template( 'admin/sidebar.php'));
			?>
		</div> <!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
	</div> <!-- #poststuff -->
</div> <!-- .wrap -->