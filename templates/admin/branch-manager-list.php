<?php
	/**
	 * Provide a dashboard view for the plugin
	 *
	 * This file is used to markup the branch manager listing
	 *
	 * @link       http://codeboxr.com
	 * @since      1.0.7
	 *
	 * @package    cbxrbooking
	 * @subpackage v/admin/templates
	 */

	if ( ! defined( 'WPINC' ) ) {
		die;
	}
?>
<?php

	//Create an instance of our CBXPollLog class
	$cbxrb_branch_manager_list = new CBXRestaurantBookingBranchManager_List_Table();

?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<h2>
			<?php esc_html_e( 'Branch Manager', 'cbxrbooking' ); ?>
			<?php echo '<a class="button button-primary button-addnew-branch" href="' . admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&view=addedit' ) . '">' . esc_attr__( 'Add New', 'cbxrbooking' ) . '</a>'; ?>
		</h2>
		<?php
			$cbxrb_branch_manager_list->prepare_items();
		?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							<div class="inside">
								<?php $cbxrb_branch_manager_list->views(); ?>
								<form class="cbxrb_branch_manager_listing" id="cbxrb_branch_manager_listing" method="get">
									<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
									<input type="hidden" name="post_type" value="cbxrbooking" />
									<?php $cbxrb_branch_manager_list->search_box( 'Search Branch Manager', 'branchmanagersearch' ); ?>
									<?php $cbxrb_branch_manager_list->display() ?>
								</form>
							</div>
						</div>
					</div>
				</div>
				<?php
					//                    $plugin_data = get_plugin_data(plugin_dir_path(__DIR__) . '/../' . $this->plugin_basename);
					//                    include dirname(__FILE__) . '/templates/sidebar.php';
				?>
			</div>
			<br class="clear">
		</div>
		<!-- #poststuff -->
	</div>
<?php
