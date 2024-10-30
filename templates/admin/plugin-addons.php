<?php
	/**
	 * Provide a dashboard view for the plugin
	 *
	 * This file is used to markup the plugin addon page
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
<style type="text/css">
	/*.cbxrbooking_addon_wrap {
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-orient: horizontal;
		-webkit-box-direction: normal;
		-ms-flex-direction: row;
		flex-direction: row;
		-ms-flex-wrap: wrap;
		flex-wrap: wrap;
		-ms-flex-pack: justify;
		justify-content: space-around;
		margin: 0 -10px 0 -10px
	}

	.cbxrbooking_addon_wrap .cbxbookmark_addon {
		border: 1px solid #e6e6e6;
		border-radius: 3px;
		-webkit-box-flex: 1;
		-ms-flex: 50%;
		flex: 50%;
		margin: 10px;
		max-width: 350px;
		min-width: 200px;
		width: 30%
	}

	.cbxrbooking_addon_wrap .addons-banner-block-item-content {
		display: -webkit-box;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-orient: vertical;
		-webkit-box-direction: normal;
		-ms-flex-direction: column;
		flex-direction: column;
		height: 184px;
		-webkit-box-pack: start;
		-ms-flex-pack: start;
		justify-content: flex-start;
		padding: 24px
	}

	.cbxrbooking_addon_wrap .addons-banner-block-item-icon img {
		max-width: 100%;
	}

	.cbxrbooking_addon_wrap .addons-banner-block-item-content h3 {
		margin-top: 0
	}

	.cbxrbooking_addon_wrap .addons-banner-block-item-content p {
		margin: 0 0 auto
	}*/
</style>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php esc_html_e( 'CBX Booking: Addons', 'cbxrbooking' ); ?></h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3><span><?php esc_html_e( 'CBX Restaurant Booking Addons', 'cbxrbooking' ); ?></span></h3>
						<div class="inside">
							<div id="cbxrbooking_addon_wrap_1" class="cbxrbooking_addon_wrap">
								<div class="cbxbookmark_addon cbxbookmark_addon_proaddon">
									<div class="addons-banner-block-item-icon">
										<a href="https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
										   target="_blank">
											<img src="https://codeboxr.com/wp-content/uploads/productshots/11780-profile.png"
												 alt="CBX Restaurant Booking Pro Addon" />
										</a>
									</div>
									<div class="addons-banner-block-item-content">
										<h3>
											<a href="https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
											   target="_blank">CBX Restaurant Booking Pro Addon</a></h3>
										<p>Pro features for CBX Restaurant Booking. Multiple booking form, multiple
											branch, export booking logs etc and more.</p>
										<a target="_blank" class="button button-primary"
										   href="https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
											From: $25 </a>
									</div>
								</div>
								<div class="cbxbookmark_addon cbxbookmark_addon_mycred">
									<div class="addons-banner-block-item-icon">
										<a href="https://codeboxr.com/product/cbx-restaurant-booking-frontend-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
										   target="_blank">
											<img src="https://codeboxr.com/wp-content/uploads/productshots/11782-profile.png"
												 alt="CBX Restaurant Booking Frontend Addon" />
										</a>
									</div>
									<div class="addons-banner-block-item-content">
										<h3>
											<a href="https://codeboxr.com/product/cbx-restaurant-booking-frontend-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
											   target="_blank">CBX Restaurant Booking Frontend Addon</a></h3>
										<p>This addon plugin helps to manage booking from frontend. Give access to
											booking manager to your staffs without giving access to admin panel.</p>
										<a target="_blank" class="button button-primary"
										   href="https://codeboxr.com/product/cbx-restaurant-booking-frontend-addon/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
											From: $35 </a>
									</div>
								</div>
								<div class="cbxbookmark_addon cbxbookmark_addon_foodmenu">
									<div class="addons-banner-block-item-icon">
										<a href="https://codeboxr.com/product/cbx-restaurant-food-menu/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
										   target="_blank">
											<img src="https://codeboxr.com/wp-content/uploads/productshots/11837-profile.png"
												 alt="CBX Restaurant Food Menu" />
										</a>
									</div>
									<div class="addons-banner-block-item-content">
										<h3>
											<a href="https://codeboxr.com/product/cbx-restaurant-food-menu/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
											   target="_blank">CBX Restaurant Food Menu</a></h3>
										<p>Easy and Quick way of Restaurant Food Menu & Drinks Display inside
											wordpress</p>
										<a target="_blank" class="button button-primary"
										   href="https://codeboxr.com/product/cbx-restaurant-food-menu/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
											Coming Soon ! </a>
									</div>
								</div>


								<div class="cbxbookmark_addon cbxbookmark_addon_pos">
									<div class="addons-banner-block-item-icon">
										<a href="https://codeboxr.com/product/cbx-restaurant-pos-for-wordpress/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
										   target="_blank">
											<img src="https://codeboxr.com/wp-content/uploads/productshots/11838-profile.png"
												 alt="CBX Restaurant POS for Wordpress" />
										</a>
									</div>
									<div class="addons-banner-block-item-content">
										<h3>
											<a href="https://codeboxr.com/product/cbx-restaurant-pos-for-wordpress/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
											   target="_blank">CBX Restaurant POS for Wordpress</a></h3>
										<p>Point of Sale(POS) for Restaurant</p>
										<a target="_blank" class="button button-primary"
										   href="https://codeboxr.com/product/cbx-restaurant-pos-for-wordpress/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
											Coming Soon ! </a>
									</div>
								</div>
								<div class="cbxbookmark_addon cbxbookmark_addon_foodie_html">
									<div class="addons-banner-block-item-icon">
										<a href="https://codeboxr.com/product/foodie-restaurant-cafe-html-template/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
										   target="_blank">
											<img src="https://codeboxr.com/wp-content/uploads/productshots/9980-profile.png"
												 alt="Foodie - Restaurant & Cafe Html Template" />
										</a>
									</div>
									<div class="addons-banner-block-item-content">
										<h3>
											<a href="https://codeboxr.com/product/foodie-restaurant-cafe-html-template/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
											   target="_blank">Foodie - Restaurant & Cafe Html Template</a></h3>
										<p>Foodie is a clean, modern and minimalistic responsive Template. It’s
											especially designed for Restaurant , Cafe, Food or anyone working in the
											Food industry and more.</p>
										<a target="_blank" class="button button-primary"
										   href="https://codeboxr.com/product/foodie-restaurant-cafe-html-template/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
											$17 Buy Now </a>
									</div>
								</div>
								<div class="cbxbookmark_addon cbxbookmark_addon_foodie_wp">
									<div class="addons-banner-block-item-icon">
										<a href="https://codeboxr.com/product/foodie-restaurant-cafe-wordpress-theme/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
										   target="_blank">
											<img src="https://codeboxr.com/wp-content/uploads/productshots/11839-profile.png"
												 alt="Foodie - Restaurant & Cafe Wordpress Theme" />
										</a>
									</div>
									<div class="addons-banner-block-item-content">
										<h3>
											<a href="https://codeboxr.com/product/foodie-restaurant-cafe-wordpress-theme/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking"
											   target="_blank">Foodie - Restaurant & Cafe Wordpress Theme</a></h3>
										<p>Restaurant & Cafe Wordpress Theme</p>
										<a target="_blank" class="button button-primary"
										   href="https://codeboxr.com/product/foodie-restaurant-cafe-wordpress-theme/?utm_source=product&amp;utm_medium=upsell&amp;utm_campaign=cbxrbooking">
											Coming Soon ! </a>
									</div>
								</div>
                            </div>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->
				</div>

			</div> <!-- post-body-content -->
			<?php include( 'sidebar.php' ); ?>
		</div> <!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
	</div> <!-- #poststuff -->
</div> <!-- .wrap -->


<script type="text/javascript">

	jQuery(document).ready(function ($) {
		//if need any js code here
	});

</script>