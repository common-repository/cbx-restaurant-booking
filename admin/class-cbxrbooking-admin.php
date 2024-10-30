<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://codeboxr.com
 * @since      1.0.0
 *
 * @package    CBXRBooking
 * @subpackage CBXRBooking/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CBXRBooking
 * @subpackage CBXRBooking/admin
 * @author     Codeboxr <info@codeboxr.com>
 */
class CBXRBooking_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	public $mail_format;

	public $mail_from_address;

	public $mail_from_name;

	public $settings_api;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		//get plugin base file name
		$this->plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $plugin_name . '.php' );

		//get instance of setting api
		$this->settings_api = new CBXRBooking_Settings_API();
	}//end constructor method

	// remove date dropdown
	public function remove_date_dropdown() {
		$screen = get_current_screen();

		if ( 'cbxrbooking' === $screen->post_type ) {
			add_filter( 'months_dropdown_results', '__return_empty_array' );
		}
	}//end remove_date_dropdown

	/**
	 * Initialize setting
	 */
	public function setting_init() {
		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		//initialize settings
		$this->settings_api->admin_init();
	}//end setting_init

	/**
	 * Global Setting Sections and titles
	 *
	 * @return type
	 */
	public function get_settings_sections() {
		$settings_sections = array(

			array(
				'id'    => 'cbxrbooking_global',
				'title' => esc_html__( 'Default Form Setting', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_email',
				'title' => esc_html__( 'Global Email Template', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_tools',
				'title' => esc_html__( 'Tools', 'cbxrbooking' )
			)

		);

		return apply_filters( 'cbxbooking_setting_sections', $settings_sections );
	}

	/**
	 * Global Setting Fields
	 *
	 * @return array
	 */
	public function get_settings_fields() {

		$reset_data_link = add_query_arg( 'cbxrbooking_fullreset', 1,
			admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingsettings' ) );

		$table_names = CBXRBookingHelper::getAllDBTablesList();
		$table_html  = '<p id="cbxpoll_plg_gfig_info"><strong>' . esc_html__( 'Following database tables will be reset/deleted.',
				'cbxrbooking' ) . '</strong></p>';

		$table_counter = 1;
		foreach ( $table_names as $key => $value ) {
			$table_html .= '<p>' . str_pad( $table_counter, 2, '0', STR_PAD_LEFT ) . ' - ' . $value . '</p>';
			$table_counter ++;
		}
		$table_html .= '<p style="margin-bottom: 20px;"><a id="cbxrbooking_info_trig" href="#">' . esc_html__( 'Show/hide details',
				'cbxrbooking' ) . '</a></p>';
		$table_html .= '<div id="cbxrbooking_resetinfo" style="display: none;">';
		$table_html .= '<br/><p><strong>' . esc_html__( 'Following option values created by this plugin(including addon) from wordpress core option table',
				'cbxrbooking' ) . '</strong></p>';


		$option_values = CBXRBookingHelper::getAllOptionNames();
		$table_html    .= '<table class="widefat widethin" id="cbxrbooking_table_data">
	<thead>
	<tr>
		<th class="row-title">' . esc_attr__( 'Option Name', 'cbxrbooking' ) . '</th>
		<th>' . esc_attr__( 'Option ID', 'cbxrbooking' ) . '</th>
		<th>' . esc_attr__( 'Data', 'cbxrbooking' ) . '</th>
	</tr>
	</thead>
	<tbody>';
		$i             = 0;
		foreach ( $option_values as $key => $value ) {
			$alternate_class = ( $i % 2 == 0 ) ? 'alternate' : '';
			$i ++;
			$table_html .= '<tr class="' . esc_attr( $alternate_class ) . '">
									<td class="row-title"><label for="tablecell">' . esc_attr( $value['option_name'] ) . '</label></td>
									<td>' . esc_attr( $value['option_id'] ) . '</td>
									<td><code style="overflow-wrap: break-word; word-break: break-all;">' . $value['option_value'] . '</code></td>
								</tr>';

		}

		$table_html .= '</tbody>
	<tfoot>
	<tr>
		<th class="row-title">' . esc_attr__( 'Option Name', 'cbxrbooking' ) . '</th>
		<th>' . esc_attr__( 'Option ID', 'cbxrbooking' ) . '</th>
		<th>' . esc_attr__( 'Data', 'cbxrbooking' ) . '</th>
	</tr>
	</tfoot>
</table>';

		$settings_builtin_fields =
			array(
				'cbxrbooking_global' => array(
					'global_date_format'  => array(
						'name'    => 'global_date_format',
						'label'   => esc_html__( 'Date Format', 'cbxrbooking' ),
						'desc'    => esc_html__( 'What will be the date format?. This date format will use in global activities like booking logs, calendar, branch, export etc.',
							'cbxrbooking' ),
						'type'    => 'select',
						'default' => '0',
						'options' => CBXRBookingHelper::getAllDateFormat(),
					),
					'global_time_format'  => array(
						'name'    => 'time_format',
						'label'   => esc_html__( 'Time Format', 'cbxrbooking' ),
						'desc'    => esc_html__( 'What will be the hour format? 12 or 24. This time format will use in global activities like booking logs, calendar, branch, export etc.',
							'cbxrbooking' ),
						'type'    => 'select',
						'default' => '24',
						'options' => array(
							'12' => esc_html__( '12 Hour', 'cbxrbooking' ),
							'24' => esc_html__( '24 Hour', 'cbxrbooking' ),
						),
					),
					/*'formsubmit'          => array(
						'name'     => 'formsubmit',
						'label'    => esc_html__( 'Form Submit', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Form submission method.', 'cbxrbooking' ),
						'type'     => 'radio',
						'default'  => 'ajax',
						'options'  => array(
							'refresh' => esc_html__( 'Refresh', 'cbxrbooking' ),
							'ajax'    => esc_html__( 'Ajax', 'cbxrbooking' )
						),
						//'desc_tip' => true,
					),*/
					'showform_successful' => array(
						'name'     => 'showform_successful',
						'label'    => esc_html__( 'Form show (after successful submission)', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Show form after successful submission.', 'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => 'on',
						//'desc_tip' => true,
					),
					'show_credit'         => array(
						'name'     => 'show_credit',
						'label'    => esc_html__( 'Show Credit Under Form', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Show Credit Under Form.', 'cbxrbooking' ),
						'type'     => 'radio',
						'options'  => array(
							'yes' => esc_html__( 'Yes', 'cbxrbooking' ),
							'no'  => esc_html__( 'No', 'cbxrbooking' ),
						),
						'default'  => 'yes',
						//'desc_tip' => true,
					),

				),

				'cbxrbooking_tools' =>
					array(
						'delete_global_config' => array(
							'name'     => 'delete_global_config',
							'label'    => esc_html__( 'On Uninstall delete plugin data', 'cbxrbooking' ),
							'desc'     => __( 'Delete Global Config data and custom table created by this plugin on uninstall. <strong>Please note that this can not be undone and you keep proper backup of full database before using feature.</strong>',
								'cbxrbooking' ),
							'type'     => 'radio',
							'options'  => array(
								'yes' => esc_html__( 'Yes', 'cbxrbooking' ),
								'no'  => esc_html__( 'No', 'cbxrbooking' ),
							),
							'default'  => 'no',
							//'desc_tip' => true,
						),
						'reset_data'           => array(
							'name'     => 'reset_data',
							'label'    => esc_html__( 'Reset all data', 'cbxrbooking' ),
							'desc'     => sprintf( __( 'Resets plugin setting, tables created by this plugin and cbxrbooking type all posts
<a class="button button-primary" onclick="return confirm(\'%s\')" href="%s">Reset Data</a>', 'cbxrbooking' ),
									esc_html__( 'Are you sure to reset all data, this process can not be undone?',
										'cbxrbooking' ), $reset_data_link ) . $table_html,
							'type'     => 'html',
							'default'  => 'off',
							//'desc_tip' => true,
						)
					),
				'cbxrbooking_email' => array(
					'headerimage'         => array(
						'name'     => 'headerimage',
						'label'    => esc_html__( 'Header Image', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Url To email you want to show as email header.Upload Image by media uploader.',
							'cbxrbooking' ),
						'type'     => 'file',
						'default'  => '',
						//'desc_tip' => true,
					),
					'footertext'          => array(
						'name'     => 'footertext',
						'label'    => esc_html__( 'Footer Text', 'cbxrbooking' ),
						'desc'     => __( 'The text to appear at the email footer. Syntax available - <code>{sitename}</code>',
							'cbxrbooking' ),
						'type'     => 'wysiwyg',
						'default'  => '{sitename}',
						//'desc_tip' => true,
					),
					'basecolor'           => array(
						'name'     => 'basecolor',
						'label'    => esc_html__( 'Base Color', 'cbxrbooking' ),
						'desc'     => esc_html__( 'The base color of the email.', 'cbxrbooking' ),
						'type'     => 'color',
						'default'  => '#557da1',
						//'desc_tip' => true,
					),
					'backgroundcolor'     => array(
						'name'     => 'backgroundcolor',
						'label'    => esc_html__( 'Background Color', 'cbxrbooking' ),
						'desc'     => esc_html__( 'The background color of the email.', 'cbxrbooking' ),
						'type'     => 'color',
						'default'  => '#f5f5f5',
						//'desc_tip' => true,
					),
					'bodybackgroundcolor' => array(
						'name'     => 'bodybackgroundcolor',
						'label'    => esc_html__( 'Body Background Color', 'cbxrbooking' ),
						'desc'     => esc_html__( 'The background colour of the main body of email.',
							'cbxrbooking' ),
						'type'     => 'color',
						'default'  => '#fdfdfd',
						//'desc_tip' => true,
					),
					'headingcolor' => array(
						'name'     => 'headingcolor',
						'label'    => esc_html__( 'Email Heading Color', 'cbxrbooking' ),
						'type'     => 'color',
						'default'  => '#ffffff',
						//'desc_tip' => true,
					),
					'bodytextcolor'       => array(
						'name'     => 'bodytextcolor',
						'label'    => esc_html__( 'Body Text Color', 'cbxrbooking' ),
						'desc'     => esc_html__( 'The body text colour of the main body of email.',
							'cbxrbooking' ),
						'type'     => 'color',
						'default'  => '#505050',
						//'desc_tip' => true,
					),
				)
			);

		$settings_fields = array(); //final setting array that will be passed to different filters

		$sections = $this->get_settings_sections();


		foreach ( $sections as $section ) {
			if ( ! isset( $settings_builtin_fields[ $section['id'] ] ) ) {
				$settings_builtin_fields[ $section['id'] ] = array();
			}
		}

		foreach ( $sections as $section ) {
			$settings_builtin_fields_section_id = $settings_builtin_fields[ $section['id'] ];
			$settings_fields[ $section['id'] ]  = apply_filters( 'cbxbooking_global_' . $section['id'] . '_fields',
				$settings_builtin_fields_section_id );
		}


		$settings_fields = apply_filters( 'cbxbooking_global_fields', $settings_fields ); //final filter if need

		return $settings_fields;
	}

	/**
	 * Register Custom Post Type cbxrbooking
	 *
	 * @since    3.7.0
	 */
	public function create_cbxrbooking_post_type() {
		$labels = array(
			'name'          => _x( 'Booking Forms', 'Post Type General Name', 'cbxrbooking' ),
			'singular_name' => _x( 'Booking Form', 'Post Type Singular Name', 'cbxrbooking' ),
			'menu_name'     => esc_html__( 'CBX Booking', 'cbxrbooking' ),
			//'parent_item_colon'  => esc_html__( 'Parent Item:', 'cbxrbooking' ),
			'all_items'     => esc_html__( 'Booking Forms', 'cbxrbooking' ),
			'view_item'     => esc_html__( 'View Booking Form', 'cbxrbooking' ),
			'add_new_item'  => esc_html__( 'Add New Booking Form', 'cbxrbooking' ),
			'add_new'       => esc_html__( 'Add New Form', 'cbxrbooking' ),
			'edit_item'     => esc_html__( 'Edit Booking Form', 'cbxrbooking' ),
			'update_item'   => esc_html__( 'Update Booking Form', 'cbxrbooking' ),
			'search_items'  => esc_html__( 'Search Booking Form', 'cbxrbooking' ),
			//'not_found'          => esc_html__( 'Not found', 'cbxrbooking' ),
			//'not_found_in_trash' => esc_html__( 'Not found in Trash',  ),
		);

		$args = array(
			'label'               => esc_html__( 'CBX Restaurant Booking', 'cbxrbooking' ),
			'description'         => esc_html__( 'CBX Restaurant Booking', 'cbxrbooking' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'menu_icon'           => plugins_url( 'assets/images/cbxbooking_menu_icon.png?v=2',
				dirname( __FILE__ ) ),
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
		);
		register_post_type( 'cbxrbooking', $args );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$branch_manager_page_hook = $this->plugin_screen_hook_suffix = add_submenu_page( 'edit.php?post_type=cbxrbooking',
			esc_html__( 'Branch Manager', 'cbxrbooking' ), esc_html__( 'Branch Manager', 'cbxrbooking' ),
			'manage_options', 'cbxrbookingbranchmanager', array(
				$this,
				'display_branch_manager_listing_page'
			) );
		add_action( "load-$branch_manager_page_hook", array( $this, 'cbxrbooking_branch_manager_listing' ) );

		//restaurant log listing
		$log_page_hook = $this->plugin_screen_hook_suffix = add_submenu_page( 'edit.php?post_type=cbxrbooking',
			esc_html__( 'Bookings Logs', 'cbxrbooking' ), esc_html__( 'Bookings Logs', 'cbxrbooking' ),
			'manage_options', 'cbxrbookinglogs', array(
				$this,
				'display_log_listing_page'
			) );

		//add screen option save option
		add_action( "load-$log_page_hook", array( $this, 'cbxrbooking_loglisting' ) );
		/*if ( ! session_id() ) {
			session_start();
		}*/

		//add settings for this plugin
		$setting_page_hook = $this->plugin_screen_hook_suffix = add_submenu_page(
			'edit.php?post_type=cbxrbooking', esc_html__( 'Setting', 'cbxrbooking' ),
			esc_html__( 'Setting', 'cbxrbooking' ),
			'manage_options', 'cbxrbookingsettings', array( $this, 'display_plugin_admin_settings' )
		);

		//add addons page for this plugin
		$addons_page_hook = $this->plugin_screen_hook_suffix = add_submenu_page(
			'edit.php?post_type=cbxrbooking', esc_html__( 'Addons', 'cbxrbooking' ),
			esc_html__( 'Addons', 'cbxrbooking' ),
			'manage_options', 'cbxrbookingaddons', array( $this, 'display_plugin_addons' )
		);
	}

	/**
	 * Display settings
	 * @global type $wpdb
	 */
	public function display_plugin_admin_settings() {
		global $wpdb;
		$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../' . $this->plugin_basename );

		//include( 'templates/admin-settings-display.php' );
		include( cbxrbooking_locate_template( 'admin/admin-settings-display.php' ) );
	}

	/**
	 * Display addons
	 * @global type $wpdb
	 */
	public function display_plugin_addons() {
		global $wpdb;
		$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../' . $this->plugin_basename );

		//include( 'templates/plugin-addons.php' );
		include( cbxrbooking_locate_template( 'admin/plugin-addons.php' ) );
	}

	/**
	 * Listing of incoming posts Column Header
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function columns_header( $columns ) {
		//unset native date col
		unset( $columns['date'] );

		//add cols
		$columns['shortcode'] = esc_html__( 'Shortcode', $this->plugin_name );
		$columns['branch']    = esc_html__( 'Branch Name', $this->plugin_name );
		$columns['status']    = esc_html__( 'Status', $this->plugin_name );
		$columns['count']     = esc_html__( 'Submission Count', $this->plugin_name );

		return $columns;
	}


	/**
	 * Listing of form each row of post type.
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function custom_column_row( $column, $post_id ) {
		$setting['status'] = get_post_meta( $post_id, '_cbxrbookingformmeta_status', true );
		$setting['branch'] = get_post_meta( $post_id, '_cbxrbookingformmeta_branch', true );

		switch ( $column ) {
			case 'shortcode':
				echo '<span class="cbxrbookingshortcode" title="' . esc_html__( "Copy to clipboard",
						"cbxrbooking" ) . '">[cbxrbooking id="' . $post_id . '"]</span>';
				break;
			case 'branch':
				if ( $setting['branch'] !== '' && intval( $setting['branch'] ) > 0 ) {
					$branch_id = intval( $setting['branch'] );
					global $wpdb;
					$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';
					$all_branches        = $wpdb->get_results( "SELECT id, name FROM $cbxrb_bm_table_name",
						'ARRAY_A' );
					foreach ( $all_branches as $single_branch ) {
						if ( intval( $single_branch['id'] ) === $branch_id ) {
							$edit_link   = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&view=addedit&id=' . $branch_id );
							$return_link = stripslashes( $single_branch['name'] ) . '<a title="' . esc_html__( 'Edit Branch',
									'cbxrbooking' ) . '" href="' . $edit_link . '"> (' . esc_html__( 'Edit',
									'cbxrbooking' ) . ')</a>';
							echo $return_link;
						}
					}
				}
				break;
			case 'status':
				$enable = 1;
				if ( $setting['status'] !== '' ) {
					$enable = intval( $setting['status'] );
				}
				echo '<input data-postid="' . $post_id . '" ' . ( ( $enable == 1 ) ? ' checked="checked" ' : '' ) . ' type="checkbox"  value="' . $enable . '" class="js-switch cbxrbookingjs-switch" autocomplete="off" />';
				break;
			case 'count':
				$submission_count = intval( get_post_meta( $post_id, '_cbxrbookingmeta_submission_count', true ) );
				$logs_link        = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&form_id=' . $post_id );
				$return_link      = '<a title="' . esc_html__( 'View booking log for this form',
						'cbxrbooking' ) . '" href="' . $logs_link . '"><span id="cbxrbooking_form_resetcounter_' . $post_id . '" class="cbxrbooking_form_resetcounter">' . $submission_count . '</span></a>' . ' <a title="' . esc_attr__( 'Click to reset form submission count',
						'cbxrbooking' ) . '" class="cbxrbooking_form_resetcounter_trig" href="#" data-currentcount="' . $submission_count . '" data-formid="' . $post_id . '"  data-countertarget="cbxrbooking_form_resetcounter_' . $post_id . '">' . esc_html__( 'Reset Count',
						'cbxrbooking' ) . '</a>';


				echo $return_link;
		}
	}

	/**
	 * Sortable count column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function custom_column_sortable( $columns ) {
		$columns['count']  = 'count';
		$columns['branch'] = 'branch';

		return $columns;
	}

	/**
	 * Restaurant log listing page
	 */
	public function display_log_listing_page() {

		if ( isset( $_GET['log_id'] ) ) {
			$error_template = false;
			$error_text     = '';

			$log_id = intval( $_GET['log_id'] );

			global $wpdb;
			$rbookinglog_table = $wpdb->prefix . "cbxrbooking_log_manager";
			$logs_link         = esc_url( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs' ) );

			if ( intval( $log_id ) > 0 ) {

				$log_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $rbookinglog_table WHERE id = %d",
					$log_id ) );

				if ( is_null( $log_data ) ) {
					$logs_link      = esc_url( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs' ) );
					$error_text     = esc_html__( 'Sorry! Requested booking record couldn\'t found.',
							'cbxrbooking' ) . ' <a class="btn btn-primary" role="button" href="' . $logs_link . '">' . esc_html__( 'Go to Bookings Log',
							'cbxrbooking' ) . '</a>';
					$error_template = true;


				}

				$form_id = intval( $log_data->form_id );

				//if no form id found return
				if ( $form_id == 0 ) {
					$error_text     = esc_html__( 'Invalid Form or Form id missing', 'cbxrbooking' );
					$error_template = true;
				}

				$form_id_post_type = get_post_type( $form_id );

				//if form id doesn't return proper post id then return
				if ( $form_id_post_type === false || $form_id_post_type !== 'cbxrbooking' ) {

					$error_text = esc_html__( 'Sorry! Requested booking form couldn\'t found.',
							'cbxrbooking' ) . ' <a class="btn btn-primary"  href="' . $logs_link . '">' . esc_html__( 'Go to Bookings Log',
							'cbxrbooking' ) . '</a>';

					$error_template = true;
				}


				//exceptional case, we will allow to edit the log if the form is still disabled
				//get the meta status for the form
				$meta_status = get_post_meta( $form_id, '_cbxrbookingformmeta_status', true );
				//if ( $meta_status === false || $meta_status != 1 )
				if ( $meta_status === false ) {
					//if($log_id == 0){

					$error_text     = esc_html__( 'Booking form is disabled',
							'cbxrbooking' ) . ' <a class="btn btn-primary"  href="' . $logs_link . '">' . esc_html__( 'Go to Bookings Log',
							'cbxrbooking' ) . '</a>';
					$error_template = true;
					//}

				} //nothing saved


				//get the meta values for the form
				$meta = get_post_meta( $form_id, '_cbxrbookingmeta', true );

				if ( $meta === false ) {
					$form_edit_link = esc_url( admin_url( 'post.php?post=' . $form_id . '&action=edit' ) );
					$error_text     = esc_html__( 'Sorry, Booking form setting missing, please edit the booking form once.',
							'cbxrbooking' ) . '<a class="btn btn-primary pull-left"  href="' . $logs_link . '">' . esc_html__( 'Go to Bookings Log',
							'cbxrbooking' ) . ' <a class="btn btn-primary"  href="' . $logs_link . '">' . esc_html__( 'Go to Bookings Log',
							'cbxrbooking' ) . '</a>';
					$error_template = true;
				} //nothing saved

				if ( ! is_array( $meta ) ) {
					$meta = array();
				} else {
					$meta = array_filter( $meta );
				}
			} else {
				$form_id = isset( $_GET['form_id'] ) ? intval( $_GET['form_id'] ) : 0;

				//if no form id found return
				if ( $form_id == 0 ) {
					$error_text     = esc_html__( 'Invalid Form or Form id missing', 'cbxrbooking' );
					$error_template = true;
				}

				$form_id_post_type = get_post_type( $form_id );

				//if form id doesn't return proper post id then return
				if ( $form_id_post_type === false || $form_id_post_type !== 'cbxrbooking' ) {

					$error_text = esc_html__( 'Sorry! Requested booking form couldn\'t found.',
							'cbxrbooking' ) . ' <a class="btn btn-primary"  href="' . $logs_link . '">' . esc_html__( 'Go to Booking Logs',
							'cbxrbooking' ) . '</a>';

					$error_template = true;
				}


				//get the meta status for the form
				$meta_status = get_post_meta( $form_id, '_cbxrbookingformmeta_status', true );
				if ( $meta_status === false || $meta_status != 1 ) {
					$error_text     = esc_html__( 'Booking form is disabled',
							'cbxrbooking' ) . ' <a class="btn btn-primary"  href="' . $logs_link . '">' . esc_html__( 'Go to Booking Logs',
							'cbxrbooking' ) . '</a>';
					$error_template = true;

				} //nothing saved


				//get the meta values for the form
				$meta = get_post_meta( $form_id, '_cbxrbookingmeta', true );

				if ( $meta === false ) {
					$form_edit_link = esc_url( admin_url( 'post.php?post=' . $form_id . '&action=edit' ) );
					$error_text     = esc_html__( 'Sorry, Booking form setting missing, please edit the booking form once.',
							'cbxrbooking' ) . ' <a class="btn btn-primary"  href="' . $logs_link . '">' . esc_html__( 'Go to Booking Logs',
							'cbxrbooking' ) . '</a>';
					$error_template = true;
				} //nothing saved

				if ( ! is_array( $meta ) ) {
					$meta = array();
				} else {
					$meta = array_filter( $meta );
				}
			}

			if ( $error_template ) {
				include( cbxrbooking_locate_template( 'admin/admin-logform-error.php' ) );
			} else {
				include( cbxrbooking_locate_template( 'admin/admin-booking-logform.php' ) );
			}


		} else {
			if ( ! class_exists( 'CBXRestaurantBooking_Loglisting' ) ) {
				require_once( plugin_dir_path( __FILE__ ) . '/../includes/class-cbxrbooking-log.php' );
			}


			global $post;
			$booking_forms = array();
			$args          = array(
				'post_type'      => 'cbxrbooking',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => '_cbxrbookingformmeta_status',
						'value'   => 1,
						'compare' => '=',
					),
				),
			);

			$posts_list = get_posts( $args );

			foreach ( $posts_list as $post ) :
				setup_postdata( $post );
				$id                   = get_the_ID();
				$title                = get_the_title();
				$booking_forms[ $id ] = $title;
			endforeach;
			wp_reset_postdata();

			include( cbxrbooking_locate_template( 'admin/admin-booking-logs.php' ) );

		}
	}

	/**
	 * Set options for log listing result
	 *
	 * @param $new_status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public function cbxrbooking_log_results_per_page( $new_status, $option, $value ) {
		if ( 'cbxrbooking_log_results_per_page' == $option ) {
			return $value;
		}

		return $new_status;
	}

	/**
	 * Add screen option for log listing
	 */
	public function cbxrbooking_loglisting() {
		$option = 'per_page';
		$args   = array(
			'label'   => esc_html__( 'Number of logs per page:', 'cbxrbooking' ),
			'default' => 50,
			'option'  => 'cbxrbooking_log_results_per_page'
		);
		add_screen_option( $option, $args );

		//get_current_screen()->render_list_table_columns_preferences();
	}

	/**
	 * Add screen option for branch manager listing
	 */
	public function cbxrbooking_branch_manager_listing() {
		$option = 'per_page';
		$args   = array(
			'label'   => esc_html__( 'Number of branch manager per page:', 'cbxrbooking' ),
			'default' => 50,
			'option'  => 'cbxrbooking_branch_manager_per_page'
		);
		add_screen_option( $option, $args );
	}

	public function cbxrbooking_log_results_columns($columns = array()){
		$columns = array(
			'cb'           => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'           => esc_html__( 'ID', 'cbxrbooking' ),
			'form_id'      => esc_html__( 'Form', 'cbxrbooking' ),
			'branch_id'    => esc_html__( 'Branch', 'cbxrbooking' ),
			'secret'       => esc_html__( 'Code', 'cbxrbooking' ),
			'booking_date' => esc_html__( 'Date', 'cbxrbooking' ),
			'booking_time' => esc_html__( 'Time', 'cbxrbooking' ),
			'party_size'   => esc_html__( 'Size', 'cbxrbooking' ),
			'user_name'    => esc_html__( 'Name', 'cbxrbooking' ),
			'user_email'   => esc_html__( 'Email', 'cbxrbooking' ),
			'user_phone'   => esc_html__( 'Phone', 'cbxrbooking' ),
			'user_message' => esc_html__( 'Message', 'cbxrbooking' ),
			'user_ip'      => esc_html__( 'IP', 'cbxrbooking' ),
			'status'       => esc_html__( 'Status', 'cbxrbooking' )
		);

		return $columns;
	}

	/**
	 * Add metabox for custom post type cbxrbooking
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes_cbxrbooking_form() {
		$post_id = intval( get_the_ID() );
		$meta_id = 'cbxrbookingformmetabox_settings_' . $post_id;
		add_meta_box(
			$meta_id, esc_html__( 'Settings', 'cbxrbooking' ), array(
			$this,
			'cbxrbookingformmetabox_settings'
		), 'cbxrbooking', 'normal', 'high'
		);
	}

	/**
	 * Render Metabox under custom post type
	 *
	 * @param $post
	 *
	 * @since 1.0
	 *
	 */
	public function cbxrbookingformmetabox_settings( $post ) {
		$meta_new = new CBXRbookingFormmetasettings( 'cbxrbookingmetabox' );
		//$meta_new = new CBXRbookingFormmetasettings();

		$form_sections = $this->cbxrbookingform_meta_settings_sections( $post );

		$form_fields = $this->cbxrbookingform_meta_settings_fields( $post );
		$meta_new->cbxrbookingform_show_metabox( $form_sections, $form_fields, $post );
	}//end cbxrbookingformmetabox_settings

	/**
	 * Meta form settings section for CBXForm
	 * @return type
	 */
	public function cbxrbookingform_meta_settings_sections( $post ) {
		$sections = array(
			array(
				'id'    => 'cbxrbooking_style',
				'title' => esc_html__( 'General', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_booking_schedule',
				'title' => esc_html__( 'Schedule', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_email_admin',
				'title' => esc_html__( 'Admin Alert', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_email_user',
				'title' => esc_html__( 'User Alert', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_cancel_booking',
				'title' => esc_html__( 'Booking Cancel', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_misc',
				'title' => esc_html__( 'Misc', 'cbxrbooking' )
			),
			array(
				'id'    => 'cbxrbooking_integration',
				'title' => esc_html__( 'Integration', 'cbxrbooking' )
			)
		);


		return $sections;
	}//end cbxrbookingform_meta_settings_sections

	/**
	 * Meta form fields for meta settings sections
	 *
	 * @return type
	 */
	public function cbxrbookingform_meta_settings_fields( $post ) {

		//$min_party_size      = $this->get_min_party_size();
		//$max_party_size      = $this->get_max_party_size();
		//$require_name        = $this->get_require_name();
		//$require_email       = $this->get_require_email();
		//$require_phone       = $this->get_require_phone();
		//$default_state       = $this->get_default_state();
		//$early_bookings      = $this->get_early_bookings();
		//$late_bookings       = $this->get_late_bookings();
		//$date_pre_selection  = $this->get_date_pre_selection();

		$post_id = intval( $post->ID );


		$time_interval  = $this->get_time_interval();
		$week_starts_on = $this->get_week_starts_on();
		$early_cancels  = $this->get_early_cancels();

		//some default values from global config
		$global_date_format  = $this->settings_api->get_option( 'global_date_format', 'cbxrbooking_global', 0 );
		$global_time_format  = $this->settings_api->get_option( 'global_time_format', 'cbxrbooking_global', 24 );
		//$form_submit         = $this->settings_api->get_option( 'formsubmit', 'cbxrbooking_global', 'ajax' );
		$showform_successful = $this->settings_api->get_option( 'showform_successful', 'cbxrbooking_global', 'on' );
		$show_credit         = $this->settings_api->get_option( 'show_credit', 'cbxrbooking_global', 'no' );

		$form_submission_count = intval( get_post_meta( $post_id, '_cbxrbookingmeta_submission_count', true ) );

		$settings_builtin_fields =
			array(
				'cbxrbooking_style'            =>
					array(
						'min_party_size'   => array(
							'name'    => 'min_party_size',
							'label'   => esc_html__( 'Min Party Size', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Select minimum party size for bookings.', 'cbxrbooking' ),
							'type'    => 'select',
							'default' => 1,
							'options' => CBXRBookingHelper::get_party_sizes()
						),
						'max_party_size'   => array(
							'name'    => 'max_party_size',
							'label'   => esc_html__( 'Max Party Size', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Select maximum party size for bookings.', 'cbxrbooking' ),
							'type'    => 'select',
							'default' => 100,
							'options' => CBXRBookingHelper::get_party_sizes()
						),
						'require_name'     => array(
							'name'    => 'require_name',
							'label'   => esc_html__( 'Require Name', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Is Name is mandatory to request a booking', 'cbxrbooking' ),
							'type'    => 'select',
							'default' => 'name-yes',
							'options' => array(
								'name-no'  => esc_html__( 'No', 'cbxrbooking' ),
								'name-yes' => esc_html__( 'Yes', 'cbxrbooking' ),
							)
						),
						'require_email'    => array(
							'name'    => 'require_email',
							'label'   => esc_html__( 'Require Email', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Is Email address is mandatory to request a booking',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => 'email-yes',
							'options' => array(
								'email-yes' => esc_html__( 'Yes', 'cbxrbooking' ),
								'email-no'  => esc_html__( 'No', 'cbxrbooking' ),
							)
						),
						'require_phone'    => array(
							'name'    => 'require_phone',
							'label'   => esc_html__( 'Require Phone', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Is Phone number is mandatory to request a booking',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => 'phone-no',
							'options' => array(
								'phone-no'  => esc_html__( 'No', 'cbxrbooking' ),
								'phone-yes' => esc_html__( 'Yes', 'cbxrbooking' ),
							)
						),
						'success_message'  => array(
							'name'    => 'success_message',
							'label'   => esc_html__( 'Success Message', 'cbxrbooking' ),
							'type'    => 'wysiwyg',
							'default' => __( 'Booking request submitted successfully. Booking code: <code>{booking_code}</code>',
								'cbxrbooking' ),
							'desc'    => __( 'Syntax available - <code>{booking_code}</code>', 'cbxrbooking' )
						),
						'guest_activation' => array(
							'name'     => 'guest_activation',
							'label'    => esc_html__( 'Guest Email Activation', 'cbxrbooking' ),
							'desc'     => __( 'Enable/Disable (To make this feature work need to enable user email notification on and user email template should have the tag syntax <code>{activation_link}</code>)',
								'cbxrbooking' ),
							'type'     => 'checkbox',
							'default'  => '',
							//'desc_tip' => true,
						),
						'default_state'    => array(
							'name'    => 'default_state',
							'label'   => esc_html__( 'Default Booking Status', 'cbxrbooking' ),
							'desc'    => esc_html__( 'What will be status when a new booking is requested?',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => 'pending',
							'options' => CBXRBookingHelper::get_default_states()
						),
						'banned_email'     => array(
							'name'        => 'banned_email',
							'label'       => esc_html__( 'Banned Email', 'cbxrbooking' ),
							'desc'        => esc_html__( 'You can block bookings from specific email addresses. Enter each email address on a separate line.',
								'cbxrbooking' ),
							'type'        => 'text_repeat',
							'default'     => '',
							'placeholder' => esc_html__( 'Email Address', 'cbxrbooking' ),
						),
						'banned_ip'        => array(
							'name'    => 'banned_ip',
							'label'   => esc_html__( 'Banned IP', 'cbxrbooking' ),
							'desc'    => esc_html__( '	
You can block bookings from specific IP addresses. Enter each IP address on a separate line. Be aware that many internet providers rotate their IP address assignments, so an IP address may accidentally refer to a different user. Also, if you block an IP address used by a public connection, such as cafe WIFI, a public library, or a university network, you may inadvertantly block several people.',
								'cbxrbooking' ),
							'type'    => 'text_repeat',
							'default' => '',

							'placeholder' => esc_html__( 'IP Address', 'cbxrbooking' ),
						),
						'text_beforeform'  => array(
							'name'    => 'text_beforeform',
							'label'   => esc_html__( 'Text Before Form', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Text Appear Before Form.', 'cbxrbooking' ),
							'type'    => 'wysiwyg',
							'default' => ''
						),
						'text_afterform'   => array(
							'name'    => 'text_afterform',
							'label'   => esc_html__( 'Text After Form', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Text Appear Before Form.', 'cbxrbooking' ),
							'type'    => 'wysiwyg',
							'default' => ''
						),
						'user_consent'     => array(
							'name'     => 'user_consent',
							'label'    => esc_html__( 'Show consent checkbox', 'cbxrbooking' ),
							'desc'     => esc_html__( 'Show user consent checkbox to accept privacy policy. It will show wordpress default privacy policy page link',
								'cbxrbooking' ),
							'type'     => 'checkbox',
							'default'  => 'on',
							//'desc_tip' => true,
						),
					),
				'cbxrbooking_booking_schedule' =>
					array(
						'date_format'          => array(
							'name'    => 'date_format',
							'label'   => esc_html__( 'Date Format', 'cbxrbooking' ),
							'desc'    => esc_html__( 'What will be the date format?. This date format will use in this booking form activities like booking entry form, edit, email etc.',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => $global_date_format,
							'options' => CBXRBookingHelper::getAllDateFormat(),
						),
						'time_format'          => array(
							'name'    => 'time_format',
							'label'   => esc_html__( 'Time Format', 'cbxrbooking' ),
							'desc'    => esc_html__( 'What will be the hour format? 12 or 24. This time format will use in this booking form activities like booking entry form, edit, email etc.',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => $global_time_format,
							'options' => array(
								'12' => esc_html__( '12 Hour', 'cbxrbooking' ),
								'24' => esc_html__( '24 Hour', 'cbxrbooking' ),
							),
						),
						'schedule_event'       => array(
							'name'     => 'schedule_event',
							'label'    => esc_html__( 'Week Days Schedule', 'cbxrbooking' ),
							'desc'     => __( 'Define the weekly schedule during which you accept bookings. If there is day duplication/repeat then the schedule comes first in order of top to bottom gets the higher priority. <strong>Please note that, schedule added last will get the higher priority</strong>',
								'cbxrbooking' ),
							'type'     => 'scheduler',
							'default'  => '',
							'weekdays' => array(
								'1' => _x( 'Mon', 'Monday abbreviation', 'cbxrbooking' ),
								'2' => _x( 'Tue', 'Tuesday abbreviation', 'cbxrbooking' ),
								'3' => _x( 'Wed', 'Wednesday abbreviation', 'cbxrbooking' ),
								'4' => _x( 'Thu', 'Thursday abbreviation', 'cbxrbooking' ),
								'5' => _x( 'Fri', 'Friday abbreviation', 'cbxrbooking' ),
								'6' => _x( 'Sat', 'Saturday abbreviation', 'cbxrbooking' ),
								'0' => _x( 'Sun', 'Sunday abbreviation', 'cbxrbooking' )
							),
						),
						'scheduler_exceptions' => array(
							'name'    => 'scheduler_exceptions',
							'label'   => esc_html__( 'Specific Day Exceptions', 'cbxrbooking' ),
							'desc'    => __( 'Define special opening hours for holidays, events or other needs. Leave the time empty if you\'re closed all day.<strong>Please note that, schedule added last will get the higher priority</strong>',
								'cbxrbooking' ),
							'type'    => 'scheduler_exceptions',
							'default' => '',
						),
						'early_bookings'       => array(
							'name'    => 'early_bookings',
							'label'   => esc_html__( 'Early Bookings', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Select how early customers can make their booking.',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => 'cbxrbooking',
							'options' => CBXRBookingHelper::get_early_bookings()
						),
						'late_bookings'        => array(
							'name'    => 'late_bookings',
							'label'   => esc_html__( 'Late Bookings', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Select how late customers can make their booking.',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => 'cbxrbooking',
							'options' => CBXRBookingHelper::get_late_bookings()
						),
						/*'date_pre_selection'   => array(
							'name'    => 'date_pre_selection',
							'label'   => esc_html__( 'Date Pre-selection', 'cbxrbooking' ),
							'desc'    => esc_html__( 'When the booking form is loaded, should it automatically attempt to select a valid date?', 'cbxrbooking' ),
							'type'    => 'select',
							'default' => 'cbxrbooking',
							'options' => array(
								'today' => esc_html__( 'Select today if valid', 'cbxrbooking' ),
								'next'  => esc_html__( 'Select today or next valid date', 'cbxrbooking' ),
								'empty' => esc_html__( 'Leave empty', 'cbxrbooking' ),
							)
						),*/
						'time_interval'        => array(
							'name'    => 'time_interval',
							'label'   => esc_html__( 'Time Interval', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Select the number of minutes between each available time.',
								'cbxrbooking' ),
							'type'    => 'select',
							'default' => 0,
							'options' => $time_interval
						),
						'week_starts_on'       => array(
							'name'    => 'week_starts_on',
							'label'   => esc_html__( 'Week Starts On', 'cbxrbooking' ),
							'desc'    => esc_html__( 'Select the first day of the week.', 'cbxrbooking' ),
							'type'    => 'select',
							'default' => '0',
							'options' => $week_starts_on
						),
					),
				'cbxrbooking_email_admin'      => array(
					'status'   => array(
						'name'     => 'status',
						'label'    => esc_html__( 'On/Off', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Status of Email', 'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => '',
						//'desc_tip' => true,
					),
					'format'   => array(
						'name'    => 'format',
						'label'   => esc_html__( 'E-mail Format', 'cbxrbooking' ),
						'desc'    => esc_html__( 'Select the format of the E-mail.', 'cbxrbooking' ),
						'type'    => 'select',
						'default' => 'html',
						'options' => array(
							'html'  => esc_html__( 'HTML', 'cbxrbooking' ),
							'plain' => esc_html__( 'Plain', 'cbxrbooking' )
						)
					),
					/*'name'     => array(
						'name'           => 'name',
						'label'          => esc_html__( 'From Name', 'cbxrbooking' ),
						'desc'           => __( 'Name of sender. Syntax available - <code>{sitename}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{sitename}',
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'from'     => array(
						'name'           => 'from',
						'label'          => esc_html__( 'From Email', 'cbxrbooking' ),
						'desc'           => esc_html__( 'From Email Address.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => get_bloginfo( 'admin_email' ),
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => false,
						'show_type'      => array(),
					),*/
					'to'       => array(
						'name'           => 'to',
						'label'          => esc_html__( 'To Email', 'cbxrbooking' ),
						'desc'           => esc_html__( 'To Email Address.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => get_bloginfo( 'admin_email' ),
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'reply_to' => array(
						'name'           => 'reply_to',
						'label'          => esc_html__( 'Reply To', 'cbxrbooking' ),
						'desc'           => __( 'Reply To Email Address. Syntax available - <code>{user_email}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{user_email}',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'subject'  => array(
						'name'           => 'subject',
						'label'          => esc_html__( 'Subject', 'cbxrbooking' ),
						'desc'           => esc_html__( 'Email Subject.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'New Booking Request Notification', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'heading'  => array(
						'name'           => 'heading',
						'label'          => esc_html__( 'Heading', 'cbxrbooking' ),
						'desc'           => esc_html__( 'Email Template heading.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'New Booking Request', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'body'     => array(
						'name'     => 'body',
						'label'    => esc_html__( 'Body', 'cbxrbooking' ),
						'desc'     => __( 'Email Body.  Syntax available - <code>{user_name}, {user_email}, {booking_date}, {booking_time}, {party_size}, {booking_phone}, {booking_message}, {booking_ip}, {booking_code}, {booking_status}, {booking_log_url}</code>',
							'cbxrbooking' ),
						'type'     => 'wysiwyg',
						'default'  => 'Hi, Admin
                            
A new booking request is made. Here is the details:

Booking Code: {booking_code}
Phone: {booking_phone}
Name: {user_name}
Email:{user_email}
Booking Date: {booking_date}
Booking Time: {booking_time}
Party Size: {party_size}

Booking Status: {booking_status}

Booking IP: {booking_ip}
Message: {booking_message}

{booking_log_url}

Please check & do necessary steps and give feedback to client.
Thank you.',
						//'desc_tip' => true,
					),
					'cc'       => array(
						'name'           => 'cc',
						'label'          => esc_html__( 'CC', 'cbxrbooking' ),
						'desc'           => esc_html__( 'Email CC, for multiple use comma.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'bcc'      => array(
						'name'           => 'bcc',
						'label'          => esc_html__( 'BCC', 'cbxrbooking' ),
						'desc'           => esc_html__( 'Email BCC, for multiple use comma', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					)
				),
				'cbxrbooking_email_user'       => array(
					'status'                             => array(
						'name'     => 'status',
						'label'    => __( 'On/Off', 'cbxrbooking' ),
						'desc'     => __( 'Status of Email', 'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => '',
						//'desc_tip' => true,
					),
					'format'                             => array(
						'name'    => 'format',
						'label'   => esc_html__( 'E-mail Format', 'cbxrbooking' ),
						'desc'    => esc_html__( 'Select the format of the E-mail.', 'cbxrbooking' ),
						'type'    => 'select',
						'default' => 'html',
						'options' => array(
							'html'  => __( 'HTML', 'cbxrbooking' ),
							'plain' => __( 'Plain', 'cbxrbooking' )
						)
					),
					/*'name'                               => array(
						'name'           => 'name',
						'label'          => esc_html__( 'From Name', 'cbxrbooking' ),
						'desc'           => __( 'Name of sender.  Syntax available - <code>{sitename}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{sitename}',
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'from'                               => array(
						'name'           => 'from',
						'label'          => esc_html__( 'From Email', 'cbxrbooking' ),
						'desc'           => esc_html__( 'From Email Address.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => get_bloginfo( 'admin_email' ),
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => false,
						'show_type'      => array(),
					),*/
					'to'                                 => array(
						'name'           => 'to',
						'label'          => esc_html__( 'To Email', 'cbxrbooking' ),
						'desc'           => __( 'To Email Address. Syntax available - <code>{user_email}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{user_email}',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'reply_to'                           => array(
						'name'           => 'reply_to',
						'label'          => esc_html__( 'Reply To', 'cbxrbooking' ),
						'desc'           => __( 'Reply To Email Address.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => get_bloginfo( 'admin_email' ),
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'new_booking_user_email_alert'       => array(
						'name'    => 'new_booking_user_email_alert',
						'label'   => esc_html__( 'New Booking User Email Alert', 'cbxrbooking' ),
						'desc'    => esc_html__( 'User gets email for new booking request', 'cbxrbooking' ),
						'type'    => 'title',
						'default' => ''
					),
					'new_subject'                        => array(
						'name'           => 'new_subject',
						'label'          => __( 'New Request Email Subject', 'cbxrbooking' ),
						'desc'           => __( 'Email subject user will receive when they make an initial booking request.',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'New Booking Request Notification', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'new_heading'                        => array(
						'name'           => 'new_heading',
						'label'          => __( 'New Request Email Heading', 'cbxrbooking' ),
						'desc'           => __( 'Email heading user will receive when they make an initial booking request.',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'New Booking Request', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'new_body'                           => array(
						'name'     => 'new_body',
						'label'    => __( 'New Request Email Body', 'cbxrbooking' ),
						'desc'     => sprintf( __( 'Email content user will receive when they make an initial booking request. Syntax available - <code>{user_name}, {user_email}, {booking_date}, {booking_time}, {party_size}, {booking_phone}, {booking_message}, {booking_ip}, {booking_code}, {booking_status}, {activation_link}, {cancel_link}</code>. Please note,  <code>{cancel_link}</code> syntax works as per form\'s <a href="%s"><strong>Booking Cancel</strong></a> setting.',
							'cbxrbooking' ),
							admin_url( 'post.php?post=' . $post->ID . '&action=edit#cbxrbooking_cancel_booking' ) ),
						'type'     => 'wysiwyg',
						'default'  => 'Hi, {user_name}
                            
We got a booking request for email address {user_email}.

Booking Details: 

Booking Status: {booking_status}
Booking Code: {booking_code}
Name: {user_name}
Email: {user_email}
Phone: {booking_phone}
Booking Date: {booking_date}
Booking Time: {booking_time}
Party Size: {party_size}
Message: {booking_message}

{activation_link}

{cancel_link}

We will check and get back to you soon.
Thank you.',
						//'desc_tip' => true,
					),
					'confirmed_booking_user_email_alert' => array(
						'name'    => 'confirmed_booking_user_email_alert',
						'label'   => esc_html__( 'Confirmed Booking User Email Alert', 'cbxrbooking' ),
						'desc'    => esc_html__( 'User gets email for booking request confirmed', 'cbxrbooking' ),
						'type'    => 'title',
						'default' => ''
					),
					'confirmed_subject'                  => array(
						'name'           => 'confirmed_subject',
						'label'          => esc_html__( 'Booking Request Confirmed Email Subject', 'cbxrbooking' ),
						'desc'           => esc_html__( 'Email subject user will receive when admin confirmed booking request.',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Request Confirmed Notification', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'confirmed_heading'                  => array(
						'name'           => 'confirmed_heading',
						'label'          => esc_html__( 'Booking Request Confirmed Email Heading', 'cbxrbooking' ),
						'desc'           => __( 'Email heading user will receive when admin confirmed booking request.',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Request Confirmed', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'confirmed_body'                     => array(
						'name'     => 'confirmed_body',
						'label'    => esc_html__( 'Booking Request Confirmed Email Body', 'cbxrbooking' ),
						'desc'     => __( 'Email content user will receive when admin confirmed booking request. Syntax available - <code>{user_name}, {user_email}, {booking_date}, {booking_time}, {party_size}, {booking_phone}, {booking_message}, {booking_ip}, {booking_code}, {booking_status}</code>',
							'cbxrbooking' ),
						'type'     => 'wysiwyg',
						'default'  => 'Hi, {user_name}

Your booking request has been confirmed. 

Booking Details: 

Booking Status: {booking_status}
Booking Code: {booking_code}
Name: {user_name}
Email: {user_email}
Phone: {booking_phone}
Booking Date: {booking_date}
Booking Time: {booking_time}
Party Size: {party_size}
Message: {booking_message}

We look forward to serve you soon.

Thank you.',
						//'desc_tip' => true,
					),
				),
				'cbxrbooking_cancel_booking'   => array(
					'booking_cancel'      => array(
						'name'     => 'booking_cancel',
						'label'    => esc_html__( 'Booking Cancel', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Enable/Disable capability if guest can cancel booking',
							'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => '',
						//'desc_tip' => true,
					),
					'cancel_status'       => array(
						'name'    => 'cancel_status',
						'label'   => esc_html__( 'Default Cancel Status', 'cbxrbooking' ),
						'desc'    => esc_html__( 'Default status when user cancel a booking.', 'cbxrbooking' ),
						'type'    => 'select',
						'default' => 'html',
						'options' => array(
							'canceled'       => esc_html__( 'Cancelled', 'cbxrbooking' ),
							'cancel-request' => esc_html__( 'Cancel Request', 'cbxrbooking' )
						)
					),
					'early_cancel'        => array(
						'name'    => 'early_cancel',
						'label'   => esc_html__( 'Early Cancel Booking', 'cbxrbooking' ),
						'desc'    => esc_html__( 'Select how early customers can cancel their booking.',
							'cbxrbooking' ),
						'type'    => 'select',
						'default' => 'cbxrbooking',
						'options' => $early_cancels
					),
					'cancel_admin_alert'  => array(
						'name'    => 'cancel_admin_alert',
						'label'   => esc_html__( 'Cancel Request Admin Alert', 'cbxrbooking' ),
						'desc'    => esc_html__( 'Admin get email when user request to cancel booking',
							'cbxrbooking' ),
						'type'    => 'title',
						'default' => ''
					),
					'cancel_admin_status' => array(
						'name'     => 'cancel_admin_status',
						'label'    => __( 'Email Alert Status', 'cbxrbooking' ),
						'desc'     => __( 'Enable/disable email alert. Admin will receive email alert when user request to cancel a booking.',
							'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => '',
						//'desc_tip' => true,
					),

					'format'                       => array(
						'name'    => 'format',
						'label'   => __( 'E-mail Format', 'cbxrbooking' ),
						'desc'    => __( 'Select the format of the E-mail.', 'cbxrbooking' ),
						'type'    => 'select',
						'default' => 'html',
						'options' => array(
							'html'  => esc_html__( 'HTML', 'cbxrbooking' ),
							'plain' => esc_html__( 'Plain', 'cbxrbooking' )
						)
					),
					/*'name'                         => array(
						'name'           => 'name',
						'label'          => __( 'From Name', 'cbxrbooking' ),
						'desc'           => __( 'Name of sender. Syntax available - <code>{sitename}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{sitename}',
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'from'                         => array(
						'name'           => 'from',
						'label'          => __( 'From Email', 'cbxrbooking' ),
						'desc'           => __( 'From Email Address.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => get_bloginfo( 'admin_email' ),
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => false,
						'show_type'      => array(),
					),*/
					'to'                           => array(
						'name'           => 'to',
						'label'          => __( 'To Email', 'cbxrbooking' ),
						'desc'           => __( 'To Email Address.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => get_bloginfo( 'admin_email' ),
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'reply_to'                     => array(
						'name'           => 'reply_to',
						'label'          => __( 'Reply To', 'cbxrbooking' ),
						//'desc'           => __('Reply To Email Address.', 'cbxrbooking'),
						'desc'           => __( 'Reply To Email Address. Syntax available - <code>{user_email}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{user_email}',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'subject'                      => array(
						'name'           => 'subject',
						'label'          => esc_html__( 'Subject', 'cbxrbooking' ),
						'desc'           => esc_html__( 'Email Subject.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Cancel Request Notification', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'heading'                      => array(
						'name'           => 'heading',
						'label'          => esc_html__( 'Heading', 'cbxrbooking' ),
						'desc'           => esc_html__( 'Email Template Heading.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Cancel Request', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'body'                         => array(
						'name'     => 'body',
						'label'    => esc_html__( 'Body', 'cbxrbooking' ),
						'desc'     => __( 'Email Body. Syntax available - <code>{user_name}, {user_email}, {booking_date}, {booking_time}, {party_size}, {booking_phone}, {booking_message}, {booking_ip}, {booking_code}, {booking_status}</code>',
							'cbxrbooking' ),
						'type'     => 'wysiwyg',
						'default'  => 'Hi, Admin
                            
A booking cancel request is made. 

Booking Details: 

Booking Status: {booking_status}
Booking Code: {booking_code}
Name: {user_name}
Email: {user_email}
Phone: {booking_phone}
Booking Date: {booking_date}
Booking Time: {booking_time}
Party Size: {party_size}
Message: {booking_message}



Thank you.',
						//'desc_tip' => true,
					),
					'cc'                           => array(
						'name'           => 'cc',
						'label'          => __( 'CC', 'cbxrbooking' ),
						'desc'           => __( 'Email CC, for multiple use comma.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'bcc'                          => array(
						'name'           => 'bcc',
						'label'          => __( 'BCC', 'cbxrbooking' ),
						'desc'           => __( 'Email BCC, for multiple use comma', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'cancel_user_alert'            => array(
						'name'    => 'cancel_user_alert',
						'label'   => esc_html__( 'Cancel Approve User Alert', 'cbxrbooking' ),
						'desc'    => esc_html__( 'User request for cancel booking, user will get either request processing email alert or request confirm alert when admin approve or default request status is cancelled.',
							'cbxrbooking' ),
						'type'    => 'title',
						'default' => ''
					),
					'format_user_mail'             => array(
						'name'    => 'format_user_mail',
						'label'   => __( 'E-mail Format', 'cbxrbooking' ),
						'desc'    => __( 'Select the format of the E-mail.', 'cbxrbooking' ),
						'type'    => 'select',
						'default' => 'html',
						'options' => array(
							'html'  => __( 'HTML', 'cbxrbooking' ),
							'plain' => __( 'Plain', 'cbxrbooking' )
						)
					),
					/*'name_user_mail'               => array(
						'name'           => 'name_user_mail',
						'label'          => __( 'From Name', 'cbxrbooking' ),
						'desc'           => __( 'Name of sender. Syntax available - <code>{sitename}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{sitename}',
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'from_user_mail'               => array(
						'name'           => 'from_user_mail',
						'label'          => __( 'From Email', 'cbxrbooking' ),
						'desc'           => __( 'From Email Address.', 'cbxrbooking' ),
						'type'           => 'text',
						'default'        => get_bloginfo( 'admin_email' ),
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => false,
						'show_type'      => array(),
					),*/
					'to_user_mail'                 => array(
						'name'           => 'to_user_mail',
						'label'          => __( 'To Email', 'cbxrbooking' ),
						'desc'           => __( 'To Email Address.  Syntax available - <code>{user_email}</code>',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => '{user_email}',
						'desc_tip'       => true,
						'label_selector' => false,
						'value_selector' => true,
						'show_type'      => array( 'email' ),
					),
					'cancel_user_alert_progress'   => array(
						'name'    => 'cancel_user_alert_progress',
						'label'   => esc_html__( 'Cancel Request User Alert-Received', 'cbxrbooking' ),
						'desc'    => '',
						'type'    => 'subtitle',
						'default' => ''
					),
					'cancel_user_status_progress'  => array(
						'name'     => 'cancel_user_status_progress',
						'label'    => __( 'Request Received Email Alert Status', 'cbxrbooking' ),
						'desc'     => __( 'Enable/disable email alert. If not set auto cancel booking as per user request then user will get email alert saying booking cancel request is in progress.',
							'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => '',
						//'desc_tip' => true,
					),
					'cancel_request_subject'       => array(
						'name'           => 'cancel_request_subject',
						'label'          => __( 'Booking Cancel Request Email Subject', 'cbxrbooking' ),
						'desc'           => __( 'Email subject user will receive when cancel request is in processing',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Cancel Request Received Notification',
							'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'cancel_request_heading'       => array(
						'name'           => 'cancel_request_heading',
						'label'          => __( 'Booking Cancel Request Email Heading', 'cbxrbooking' ),
						'desc'           => __( 'Email heading user will receive when cancel request is in processing.',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Cancel Request Received', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'cancel_request_body'          => array(
						'name'     => 'cancel_request_body',
						'label'    => __( 'Booking Cancel Request Email Body', 'cbxrbooking' ),
						'desc'     => __( 'Email content user will receive when cancel request is cancel-request. Syntax available - <code>{user_name}, {user_email}, {booking_date}, {booking_time}, {party_size}, {booking_phone}, {booking_message}, {booking_ip}, {booking_code}, {booking_status}</code>',
							'cbxrbooking' ),
						'type'     => 'wysiwyg',
						'default'  => 'Hi, {user_name}
                            
We got cancel request for your booking. 

Booking Details: 

Booking Status: {booking_status}
Booking Code: {booking_code}
Phone: {booking_phone}
Booking Date: {booking_date}
Booking Time: {booking_time}
Party Size: {party_size}
Message: {booking_message}

Your request to cancel booking will be reviewed by staff.

Thank you.',
						//'desc_tip' => true,
					),
					'cancel_user_alert_cancelled'  => array(
						'name'    => 'cancel_user_alert_cancelled',
						'label'   => esc_html__( 'Cancel Request User Alert-Approved', 'cbxrbooking' ),
						'desc'    => '',
						'type'    => 'subtitle',
						'default' => ''
					),
					'cancel_user_status_cancelled' => array(
						'name'     => 'cancel_user_status_cancelled',
						'label'    => __( 'Request Cancelled Email Alert Status', 'cbxrbooking' ),
						'desc'     => __( 'Enable/disable email alert. If set auto cancel on request or admin accept cancel request  then user will get email alert saying booking is cancelled.',
							'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => '',
						//'desc_tip' => true,
					),
					'canceled_subject'             => array(
						'name'           => 'canceled_subject',
						'label'          => __( 'Booking Request Canceled Email Subject', 'cbxrbooking' ),
						'desc'           => __( 'Email subject user will receive when admin canceled booking request.',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Request Canceled Notification', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'canceled_heading'             => array(
						'name'           => 'canceled_heading',
						'label'          => __( 'Booking Request Canceled Email Heading', 'cbxrbooking' ),
						'desc'           => __( 'Email heading user will receive when admin canceled booking request.',
							'cbxrbooking' ),
						'type'           => 'text',
						'default'        => esc_html__( 'Booking Request Canceled', 'cbxrbooking' ),
						'desc_tip'       => true,
						'label_selector' => true,
						'value_selector' => true,
						'show_type'      => array(),
					),
					'canceled_body'                => array(
						'name'     => 'canceled_body',
						'label'    => esc_html__( 'Booking Request Canceled Email Body', 'cbxrbooking' ),
						'desc'     => __( 'Email content user will receive when admin canceled booking request. Syntax available - <code>{user_name}, {user_email}, {booking_date}, {booking_time}, {party_size}, {booking_phone}, {booking_message}, {booking_ip}, {booking_code}, {booking_status}</code>',
							'cbxrbooking' ),
						'type'     => 'wysiwyg',
						'default'  => 'Hi, {user_name}
                            
Your booking request has been cancelled. 

Booking Details: 

Booking Status: {booking_status}
Booking Code: {booking_code}
Phone: {booking_phone}
Booking Date: {booking_date}
Booking Time: {booking_time}
Party Size: {party_size}
Message: {booking_message}


Thank you.',
						//'desc_tip' => true,
					)
				),
				'cbxrbooking_misc'             => array(
					/*'formsubmit'                   => array(
						'name'     => 'formsubmit',
						'label'    => __( 'Form Submit', 'cbxrbooking' ),
						'desc'     => __( 'Form submission method.', 'cbxrbooking' ),
						'type'     => 'radio',
						'default'  => $form_submit,
						'options'  => array(
							'refresh' => __( 'Refresh', 'cbxrbooking' ),
							'ajax'    => __( 'Ajax', 'cbxrbooking' )
						),
						//'desc_tip' => true,
					),*/
					'showform_successful'          => array(
						'name'     => 'showform_successful',
						'label'    => __( 'Form show (after successful submission)', 'cbxrbooking' ),
						'desc'     => __( 'Show form after successful submission.', 'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => $showform_successful,
						//'desc_tip' => true,
					),
					'show_credit'                  => array(
						'name'     => 'show_credit',
						'label'    => __( 'Show Credit Under Form', 'cbxrbooking' ),
						'desc'     => __( 'Show Credit Under Form.', 'cbxrbooking' ),
						'type'     => 'radio',
						'options'  => array(
							'yes' => 'Yes',
							'no'  => 'No'
						),
						'default'  => $show_credit,
						//'desc_tip' => true,
					),
					'enable_form_submission_limit' => array(
						'name'     => 'enable_form_submission_limit',
						'label'    => esc_html__( 'Enable/Disable', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Enable/Disable Booking Limit.', 'cbxrbooking' ),
						'type'     => 'checkbox',
						'default'  => 'off',
						//'desc_tip' => true,
					),
					'form_submission_limit_val'    => array(
						'name'     => 'form_submission_limit_val',
						'label'    => esc_html__( 'Max Public Booking Count Limit', 'cbxrbooking' ),
						'desc'     => esc_html__( 'Booking form will not display if booking count is crossed.',
								'cbxrbooking' ) . '  ' . sprintf( __( '<strong>Current Count: <span id="cbxrbooking_form_resetcounter_' . $post->ID . '" class="cbxrbooking_form_resetcounter">%d</span></strong>',
								'cbxrbooking' ),
								$form_submission_count ) . ' <a title="' . esc_attr__( 'Click to reset form submission count',
								'cbxrbooking' ) . '"  class="cbxrbooking_form_resetcounter_trig" href="#" data-currentcount="' . $form_submission_count . '" data-formid="' . $post->ID . '"  data-countertarget="cbxrbooking_form_resetcounter_' . $post->ID . '">' . esc_html__( 'Reset Count',
								'cbxrbooking' ) . '</a>',
						'type'     => 'number',
						'default'  => 0,
						//'desc_tip' => true,
					),
					'limit_error_message'          => array(
						'name'     => 'limit_error_message',
						'label'    => esc_html__( 'Limit Crossed Message', 'cbxrbooking' ),
						'desc'     => esc_html__( 'General message after form successful submission.',
							'cbxrbooking' ),
						'type'     => 'wysiwyg',
						'default'  => 'Sorry! Booking limit has crossed. We are not accepting any more request. Thank you.',
						//'desc_tip' => true,
					),
				)
			);


		$settings_meta_fields = array(); //final setting array that will be passed to different filters

		$meta_sections = $this->cbxrbookingform_meta_settings_sections( $post );

		foreach ( $meta_sections as $meta_section ) {
			if ( ! isset( $settings_builtin_fields[ $meta_section['id'] ] ) ) {
				$settings_builtin_fields[ $meta_section['id'] ] = array();
			}
		}


		foreach ( $meta_sections as $meta_section ) {
			$settings_builtin_fields_meta_section        = $settings_builtin_fields[ $meta_section['id'] ];
			$settings_meta_fields[ $meta_section['id'] ] = apply_filters( 'cbxrbooking_meta_' . $meta_section['id'] . '_fields',
				$settings_builtin_fields_meta_section );
		}


		return $settings_meta_fields;
	}


	/**
	 * Get the default state dropdown
	 *
	 * @return type
	 */
	public function get_default_state() {
		return CBXRBookingHelper::get_default_states();
	}


	/**
	 * Get the early bookings dropdown
	 *
	 * @return type
	 */
	public function get_early_bookings() {
		return CBXRBookingHelper::get_early_bookings();
	}

	/**
	 * Get the late bookings dropdown
	 *
	 * @return type
	 */
	public function get_late_bookings() {
		return CBXRBookingHelper::get_late_bookings();
	}

	/**
	 * Get the time dropdown
	 *
	 * @return type
	 */
	public function get_time_interval() {
		return CBXRBookingHelper::get_time_interval();
	}

	/**
	 * Get the week starts on dropdown
	 *
	 * @return type
	 */
	public function get_week_starts_on() {
		return CBXRBookingHelper::get_week_starts_on();
	}

	/**
	 * Get the early bookings cancel dropdown
	 *
	 * @return type
	 */
	public function get_early_cancels() {
		return CBXRBookingHelper::get_early_cancels();
	}


	/**
	 * Save meta values for form
	 *
	 * @param int $post_id The ID of the post being save
	 * @param bool                Whether or not the user has the ability to save this post.
	 */

	public function save_post_cbxrbooking_form( $post_id, $post ) {

		$post_type    = 'cbxrbooking';
		$action_array = array();

		// If this isn't a 'cbxrbooking' post, don't update it.
		if ( $post_type != $post->post_type ) {
			return;
		}

		if ( ! empty( $_POST['cbxrbookingmetabox'] ) ) {

			$postData = $_POST['cbxrbookingmetabox'];

			/* foreach ($postData['fields'] as $key => $value) {
				 if ($value['type'] == 'submit' || $value['type'] == 'reset') {
					 $action_array[$key] = $value;
					 unset($postData['fields'][$key]);
				 }
			 }*/

			//$postData['fields'] = array_merge($postData['fields'], $action_array);
			if ( $this->user_can_save( $post_id, 'cbxrbookingmetabox', $postData['nonce'] ) ) {
				unset( $postData['nonce'] );
				/*if (!isset($postData['fields'])) {
					$postData['fields'] = array();
				}*/

				update_post_meta( $post_id, '_cbxrbookingmeta', $postData );
			}
		}
	}//end save_post_cbxrbooking_form

	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param int $post_id The ID of the post being save
	 * @param            $action
	 * @param            $nonce
	 *
	 * @return bool
	 */
	public function user_can_save( $post_id, $action, $nonce ) {
		$is_autosave    = wp_is_post_autosave( $post_id );
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $nonce ) && wp_verify_nonce( $nonce, $action ) );

		// Return true if the user is able to save; otherwise, false.
		return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;
	}

	/**
	 * Form Enable/Disable Ajax
	 */
	public function cbxrbooking_form_enable_disable() {
		check_ajax_referer( 'cbxrbooking', 'security' );

		$enable  = ( isset( $_POST['enable'] ) && $_POST['enable'] != null ) ? intval( $_POST['enable'] ) : 0;
		$post_id = ( isset( $_POST['postid'] ) && $_POST['postid'] != null ) ? intval( $_POST['postid'] ) : 0;

		if ( $post_id > 0 ) {
			update_post_meta( $post_id, '_cbxrbookingformmeta_status', $enable );
		}
		echo $enable;
		wp_die();
	}

	/**
	 * Form counter reset
	 */
	public function cbxrbooking_form_form_resetcounter() {
		check_ajax_referer( 'cbxrbooking', 'security' );


		$post_id = ( isset( $_POST['postid'] ) && $_POST['postid'] != null ) ? intval( $_POST['postid'] ) : 0;

		if ( $post_id > 0 ) {
			update_post_meta( $post_id, '_cbxrbookingmeta_submission_count', 0 );
		}
		echo 1;
		wp_die();
	}//end cbxrbooking_form_form_resetcounter

	/**
	 * Set branch to any form from ajax request
	 */
	public function cbxrbooking_branch_selection() {
		check_ajax_referer( 'cbxrbooking', 'security' );

		$branch_id = ( isset( $_POST['branchid'] ) && $_POST['branchid'] != null ) ? intval( $_POST['branchid'] ) : 0;
		$post_id   = ( isset( $_POST['postid'] ) && $_POST['postid'] != null ) ? intval( $_POST['postid'] ) : 0;

		if ( $post_id > 0 ) {
			update_post_meta( $post_id, '_cbxrbookingformmeta_branch', $branch_id );
		}
		echo $branch_id;
		wp_die();
	}//end cbxrbooking_branch_selection

	/**
	 * Get forms for any branch from ajax request
	 */
	public function cbxrbooking_branch_to_form() {
		check_ajax_referer( 'cbxrbooking', 'security' );

		$branch_id    = ( isset( $_POST['branch_id'] ) && $_POST['branch_id'] != null ) ? intval( $_POST['branch_id'] ) : 0;
		$branch_forms = array();

		global $wpdb;

		$posts_table    = $wpdb->prefix . "posts"; //post table
		$postmeta_table = $wpdb->prefix . "postmeta"; // postmeta table


		$sql_select = "SELECT DISTINCT posts.ID as id, posts.post_title as form_name FROM $posts_table as posts ";

		$where_sql = $wpdb->prepare( "posts.post_type = %s AND posts.post_status = %s", 'cbxrbooking', 'publish' );
		$join      = '';

		if ( $branch_id > 0 ) {
			$where_sql .= ( $where_sql != '' ) ? ' AND ' : '';
			$join      .= " JOIN $postmeta_table postmeta ON posts.ID = postmeta.post_id";
			$where_sql .= $wpdb->prepare( "postmeta.meta_key = %s AND postmeta.meta_value = %d",
				'_cbxrbookingformmeta_branch', $branch_id );
		}

		$sortingOrder = " ORDER BY posts.post_title desc ";
		$branch_forms = $wpdb->get_results( "$sql_select $join WHERE $where_sql $sortingOrder", ARRAY_A );

		//echo wp_json_encode( $branch_forms );
		//wp_die();
		wp_send_json( $branch_forms );
	}//end cbxrbooking_branch_to_form

	/**
	 * add branch dropdown filter
	 */
	public function admin_posts_filter_add_branch_dropdown() {
		$post_type = 'cbxrbooking';
		if ( isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}

		//add filter to the post type you want
		if ( 'cbxrbooking' === $post_type ) {
			global $wpdb;
			$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

			$all_branches = $wpdb->get_results( "SELECT id, name FROM $cbxrb_bm_table_name", 'ARRAY_A' );
			$branches_kv  = array();
			foreach ( $all_branches as $single_branch ) {
				$branches_kv[ $single_branch['name'] ] = $single_branch['id'];
			}

			?>
            <select name="branch_id">
                <option value=""><?php _e( 'All Branch', 'cbxrbooking' ); ?></option>
				<?php
				$current_branch = isset( $_GET['branch_id'] ) ? intval( $_GET['branch_id'] ) : 0;
				foreach ( $branches_kv as $label => $value ) {
					printf(
						'<option value="%s"%s>%s</option>',
						$value,
						$value == $current_branch ? ' selected="selected"' : '',
						$label
					);
				}
				?>
            </select>
		<?php }
	}//end admin_posts_filter_add_branch_dropdown

	/**
	 * add branch filter to query
	 *
	 * @param $query
	 */
	function admin_posts_parse_query_modification( $query ) {
		global $pagenow;

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'cbxrbooking' && isset( $_GET['branch_id'] ) && intval( $_GET['branch_id'] ) > 0 ) {
			$query->query_vars['meta_key']   = '_cbxrbookingformmeta_branch';
			$query->query_vars['meta_value'] = intval( $_GET['branch_id'] );
		}
	}//end admin_posts_parse_query_modification

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

		global $post_type;

		$page               = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : '';

		/*$booking_core_hooks = array(
			'cbxrbooking_page_cbxrbookinglogs',
			'cbxrbooking_page_cbxrbookingsettings',
			'cbxrbooking_page_cbxrbookingbranchmanager',
			'cbxrbooking_page_cbxrbookingaddons',
		);*/

		$booking_core_pages = array(
			'cbxrbookinglogs',
			'cbxrbookingsettings',
			'cbxrbookingbranchmanager',
			'cbxrbookingaddons',
		);

		wp_register_style( 'select2', plugin_dir_url( __FILE__ ) . '../assets/vendors/select2/css/select2.min.css', array(),
			$this->version );


		wp_register_style( 'cbxrbookingcustombootstrap',plugin_dir_url( __FILE__ ) . '../assets/css/cbxrbookingcustombootstrap.css', array(), $this->version,
			'all' );

		wp_register_style( 'switchery', plugin_dir_url( __FILE__ ) . '../assets/vendors/switchery/switchery.min.css',
			array( 'cbxrbookingcustombootstrap' ), $this->version, 'all' );
		wp_register_style( 'flatpickr', plugin_dir_url( __FILE__ ) . '../assets/vendors/flatpickr/flatpickr.min.css',
			array( 'cbxrbookingcustombootstrap' ), $this->version, 'all' );
		wp_register_style( 'jquery-webui-popover',
			plugin_dir_url( __FILE__ ) . '../assets/vendors/jquery-webui-popover/jquery.webui-popover.css',
			array( 'cbxrbookingcustombootstrap' ), $this->version, 'all' );

		wp_register_style( 'cbxrbooking-setting',
			plugin_dir_url( __FILE__ ) . '../assets/css/cbxrbooking-setting.css',
			array( 'select2', 'wp-color-picker' ), $this->version, 'all' );

		wp_register_style( 'cbxrbooking-admin', plugin_dir_url( __FILE__ ) . '../assets/css/cbxrbooking-admin.css',
			array(
				'cbxrbookingcustombootstrap',
				'switchery',
				'flatpickr',
				'jquery-webui-popover',
			), $this->version, 'all' );

		if ( ( in_array( $hook, array( 'edit.php', 'post.php', 'post-new.php' ) ) && 'cbxrbooking' == $post_type ) || in_array( $page, $booking_core_pages )
		) {


			wp_enqueue_style( 'cbxrbookingcustombootstrap' );
			wp_enqueue_style( 'switchery' );
			wp_enqueue_style( 'flatpickr' );
			wp_enqueue_style( 'jquery-webui-popover' );
			wp_enqueue_style( 'cbxrbooking-admin' );
		}

		//for setting page
		if ( $page == 'cbxrbookingsettings' ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_media();

			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'cbxrbooking-setting' );
		}

		//$admin_slugs = CBXRBookingHelper::admin_page_slugs();
		if ( ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'edit.php' ) && $post_type == 'cbxrbooking' || in_array( $page, $booking_core_pages ) ) {
			wp_register_style( 'cbxrbooking-branding',
				plugin_dir_url( __FILE__ ) . '../assets/css/cbxrbooking-branding.css',
				array(),
				$this->version );
			wp_enqueue_style( 'cbxrbooking-branding' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		$page               = isset( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : '';

		global $wp_locale;
		global $post_type;


		/*$booking_core_hooks = array(
			'cbxrbooking_page_cbxrbookinglogs',
			'cbxrbooking_page_cbxrbookingsettings',
			'cbxrbooking_page_cbxrbookingbranchmanager',
			'cbxrbooking_page_cbxrbookingaddons',
		);*/

		$booking_core_pages = array(
			'cbxrbookinglogs',
			'cbxrbookingsettings',
			'cbxrbookingbranchmanager',
			'cbxrbookingaddons',
		);

		wp_register_script( 'cbxrbookingjsevents',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxrbookingjsevents.js', array(
				'jquery',
				'wp-color-picker'
			), $this->version, true );

		wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . '../assets/vendors/select2/js/select2.min.js',
			array( 'jquery' ), $this->version, true );
		wp_register_script( 'cbxrbooking-setting',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxrbooking-setting.js', array( 'jquery', 'select2' ),
			$this->version, true );


		wp_register_script( 'jquery-validate',
			plugin_dir_url( __FILE__ ) . '../assets/vendors/jquery.validate.min.js', array( 'jquery' ), $this->version,
			true );
		wp_register_script( 'switchery', plugin_dir_url( __FILE__ ) . '../assets/vendors/switchery/switchery.js',
			array( 'jquery' ), $this->version, true );
		wp_register_script( 'flatpickr', plugin_dir_url( __FILE__ ) . '../assets/vendors/flatpickr/flatpickr.min.js',
			array( 'jquery' ), $this->version, true );
		wp_register_script( 'jquery-mustache', plugin_dir_url( __FILE__ ) . '../assets/vendors/mustache/jquery.mustache.js',
			array( 'jquery' ), $this->version, true );
		wp_register_script( 'mustache', plugin_dir_url( __FILE__ ) . '../assets/vendors/mustache/mustache.min.js', array(
			'jquery-mustache',
			'jquery'
		), $this->version, true );
		wp_register_script( 'jquery-sortable',
			plugin_dir_url( __FILE__ ) . '../assets/vendors/jquery-sortable-min.js', array( 'jquery' ), $this->version,
			true );

		wp_register_script( 'jquery-webui-popover',
			plugin_dir_url( __FILE__ ) . '../assets/vendors/jquery-webui-popover/jquery.webui-popover.js', array( 'jquery' ), $this->version,
			true );

		wp_register_script( 'cbxrbookingadminbranchmanager',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxrbooking-admin-branch-manager.js', array( 'jquery' ),
			$this->version, true );
		wp_register_script( 'cbxrbookingadminformlist',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxrbooking-admin-form-list.js', array( 'jquery' ),
			$this->version, true );
		wp_register_script( 'cbxrbookingadminloglist',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxrbooking-admin-booking-log-list.js', array( 'jquery' ),
			$this->version, true );
		wp_register_script( 'cbxrbookingadminlogform',
			plugin_dir_url( __FILE__ ) . '../assets/js/cbxrbooking-admin-booking-log-form.js', array(
				'cbxrbookingjsevents',
				'jquery',
				'jquery-validate',
				'flatpickr'
			), $this->version, true );

		$flatpickr_inline_weekdays_shorthand = array_values( $wp_locale->weekday_abbrev );


		$flatpickr_inline_weekdays_longhand = array_values( $wp_locale->weekday );


		$flatpickr_inline_months_longhand = array_values( $wp_locale->month );


		$flatpickr_inline_months_shorthand = array_values( $wp_locale->month_abbrev );

		$flatpickr_inline_js = '
	        flatpickr.l10ns.en.weekdays.shorthand = ' . json_encode( $flatpickr_inline_weekdays_shorthand ) . ';	   
	        flatpickr.l10ns.en.weekdays.longhand = ' . json_encode( $flatpickr_inline_weekdays_longhand ) . ';	   
	        flatpickr.l10ns.en.months.longhand = ' . json_encode( $flatpickr_inline_months_shorthand ) . ';	   
	        flatpickr.l10ns.en.months.longhand = ' . json_encode( $flatpickr_inline_months_longhand ) . ';	   
	        flatpickr.l10ns.en.rangeSeparator = "' . esc_html__( ' to ', 'cbxrbooking' ) . '";            
            flatpickr.l10ns.en.scrollTitle = "' . esc_html__( 'Scroll to increment', 'cbxrbooking' ) . '";
            flatpickr.l10ns.en.toggleTitle = "' . esc_html__( 'Click to toggle', 'cbxrbooking' ) . '";
			';

		wp_add_inline_script( 'flatpickr', $flatpickr_inline_js, 'after' );

		// Localize the script with new data
		$translation_array = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'cbxrbooking' ),
		);
		wp_localize_script( 'cbxrbookingadminformlist', 'cbxrbookingadminformlistObj', $translation_array );

		// Localize the script with new data
		$translation_array = array(
			'ajaxurl'               => admin_url( 'admin-ajax.php' ),
			'nonce'                 => wp_create_nonce( 'cbxrbooking' ),
			'no_booking_form_found' => esc_html__( 'No Booking Form Found', 'cbxrbooking' ),
			'select_booking_form'   => esc_html__( 'Select Booking Form', 'cbxrbooking' ),
		);
		wp_localize_script( 'cbxrbookingadminloglist', 'cbxrbookingadminloglistObj', $translation_array );

		$global_twenty_four_hour_format = true;

		$cbxrbooking_global_setting = get_option( 'cbxrbooking_global', null );
		if ( isset( $cbxrbooking_global_setting['time_format'] ) && intval( $cbxrbooking_global_setting['time_format'] ) == 12 ) {
			$global_twenty_four_hour_format = false;
		}

		// Localize the script with new data
		$translation_array = array(
			'ajaxurl'                        => admin_url( 'admin-ajax.php' ),
			'nonce'                          => wp_create_nonce( 'rbooking_formentry' ),
			'required'                       => esc_html__( 'This field is required.', 'cbxrbooking' ),
			'email'                          => esc_html__( 'Please enter a valid email address.', 'cbxrbooking' ),
			'validation_msg_required'        => esc_html__( 'This field is required.',
				'cbxrbookingfrotendlogaddon' ),
			'validation_msg_email'           => esc_html__( 'Please enter a valid email address.',
				'cbxrbookingfrotendlogaddon' ),
			'no_booking_form_found'          => esc_html__( 'No Booking Form Found', 'cbxrbooking' ),
			'select_booking_form'            => esc_html__( 'Booking Form', 'cbxrbooking' ),
			'global_twenty_four_hour_format' => $global_twenty_four_hour_format,
			'forms_data'                     => array(),
            'update_booking_label'           => esc_html__('Update Booking', 'cbxrbooking'),
		);
		wp_localize_script( 'cbxrbookingadminlogform', 'cbxrbookingadminlogformObj', $translation_array );


		$cbxrbooking_setting_js_vars = apply_filters( 'cbxrbooking_setting_js_vars',
			array(
				'please_select' => esc_html__( 'Please Select', 'cbxrbooking' ),
				'upload_title'  => esc_html__( 'Window Title', 'cbxrbooking' )
			) );

		wp_localize_script( 'cbxrbooking-setting', 'cbxrbooking_setting', $cbxrbooking_setting_js_vars );

		if ( ( in_array( $hook, array(
					'edit.php',
					'post.php',
					'post-new.php'
				) ) && 'cbxrbooking' == $post_type ) || in_array( $page, $booking_core_pages )
		) {

			wp_enqueue_script( 'cbxrbookingjsevents' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-validate' );
			wp_enqueue_script( 'switchery' );
			wp_enqueue_script( 'flatpickr' );
			wp_enqueue_script( 'jquery-mustache' );
			wp_enqueue_script( 'mustache' );
			wp_enqueue_script( 'jquery-sortable' );
			wp_enqueue_script( 'jquery-webui-popover' );


			wp_enqueue_script( 'cbxrbookingadminbranchmanager' );
			wp_enqueue_script( 'cbxrbookingadminformlist' );
			wp_enqueue_script( 'cbxrbookingadminloglist' );
			wp_enqueue_script( 'cbxrbookingadminlogform' );

		}

		//for setting page
		if ( $page == 'cbxrbookingsettings' ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'cbxrbooking-setting' );
		}

	}

	/**
	 * Add metabox for custom post type cbxfeedbackform && cbxfeedbackbtn
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes_form() {

		add_meta_box(
			'cbxrbookingmetabox_shortcode', esc_html__( 'Get the Shortcode', 'cbxrbooking' ), array(
			$this,
			'cbxrbookingmetabox_shortcode_display'
		), 'cbxrbooking', 'side', 'low'
		);

		add_meta_box(
			'cbxrbookingmetabox_bookingformstatus', esc_html__( 'Status', 'cbxrbooking' ), array(
			$this,
			'cbxrbookingmetabox_bookingformstatus_display'
		), 'cbxrbooking', 'side', 'low'
		);

		add_meta_box(
			'cbxrbookingmetabox_bookingformbranch', esc_html__( 'Branch', 'cbxrbooking' ), array(
			$this,
			'cbxrbookingmetabox_bookingformbranch_display'
		), 'cbxrbooking', 'side', 'low'
		);

	}//end add_meta_boxes_form

	/**
	 * Render Metabox under custom post type
	 *
	 * @param $post
	 *
	 * @since 1.0
	 *
	 */
	public function cbxrbookingmetabox_shortcode_display( $post ) {
		echo '<span class="cbxrbookingshortcode"  title="' . esc_html__( "Copy to clipboard",
				"cbxrbooking" ) . '">[cbxrbooking id="' . $post->ID . '"]</span>';
		echo '<div class="clear"></div>';
	}

	/**
	 * Render Metabox under custom post type
	 *
	 * @param $post
	 *
	 * @since 1.0
	 *
	 */
	public function cbxrbookingmetabox_bookingformstatus_display( $post ) {
		$new_status = get_post_meta( $post->ID, '_cbxrbookingformmeta_status', true );

		$enable = 1;
		if ( $new_status !== '' ) {
			$enable = intval( $new_status );
		} else {
			update_post_meta( $post->ID, '_cbxrbookingformmeta_status', $enable );
		}

		echo '<div class="">' . esc_html__( 'Off/On', 'cbxrbooking' ) . '
                    </div>
                        <input data-postid="' . $post->ID . '" ' . ( ( $enable == 1 ) ? ' checked="checked" ' : '' ) . ' type="checkbox"  value="' . $enable . '" class="js-switch cbxrbookingjs-switch" autocomplete="off" />
                    <div>
                </div>';
	}

	/**
	 * Render Metabox under custom post type
	 *
	 * @param $post
	 *
	 * @since 1.0
	 *
	 */
	public function cbxrbookingmetabox_bookingformbranch_display( $post ) {
		global $wpdb;
		$branch_id = get_post_meta( $post->ID, '_cbxrbookingformmeta_branch', true );

		$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

		$all_branches = $wpdb->get_results( "SELECT id, name FROM $cbxrb_bm_table_name", 'ARRAY_A' );

		echo '<select class="form-control cbxrbookingjs-branch-select" name="branch_id" id="branch_id" data-postid="' . $post->ID . '"><option value="0">' . esc_html__( 'Select Branch',
				'cbxrbooking' ) . '</option>';

		foreach ( $all_branches as $single_branch ) {
			$selected = ( ( $branch_id > 0 && $branch_id == $single_branch['id'] ) ? ' selected="selected" ' : '' );
			echo '<option ' . $selected . ' value="' . $single_branch['id'] . '">' . stripslashes( esc_html__( $single_branch['name'],
					'cbxrbooking' ) ) . '</option>';
		}
		echo '</select>';

	}

	/**
	 * Backend form submit handle
	 */
	public function rbooking_backend_entrysubmit() {

		//if backend form submit and also nonce verified then go
		if ( isset( $_POST['rbooking_token'] ) && wp_verify_nonce( $_POST['rbooking_token'], 'rbooking_formentry' ) ) {

			global $wpdb;

			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;

			$rbookinglog_table = $wpdb->prefix . "cbxrbooking_log_manager";
			$post_data         = $_POST;

			$form_id    = isset( $post_data['cbxrb_formid'] ) ? intval( $post_data['cbxrb_formid'] ) : 0;
			$booking_id = isset( $post_data['cbxrb_booking_id'] ) ? intval( $post_data['cbxrb_booking_id'] ) : 0;

			// sanitization
			$preferred_date = isset( $post_data['cbxrb_preferred_date'] ) ? sanitize_text_field( $post_data['cbxrb_preferred_date'] ) : '';
			$preferred_time = isset( $post_data['cbxrb_preferred_time'] ) ? sanitize_text_field( $post_data['cbxrb_preferred_time'] ) : '';
			$party          = isset( $post_data['cbxrb_party'] ) ? intval( $post_data['cbxrb_party'] ) : 1;
			$name           = isset( $post_data['cbxrb_name'] ) ? sanitize_text_field( $post_data['cbxrb_name'] ) : '';
			$email          = isset( $post_data['cbxrb_email'] ) ? sanitize_email( $post_data['cbxrb_email'] ) : '';
			$phone          = isset( $post_data['cbxrb_phone'] ) ? sanitize_text_field( $post_data['cbxrb_phone'] ) : '';
			$message        = isset( $post_data['cbxrb_message'] ) ? sanitize_text_field( $post_data['cbxrb_message'] ) : '';

			$page_url = esc_url( admin_url( 'edit.php?post_type=cbxrbooking' ) );
			$page_url = add_query_arg( 'page', 'cbxrbookinglogs', $page_url );

			// validation
			$hasError          = false;
			$validation_errors = array();

			if ( $form_id == 0 ) {
				$validation_errors['top_errors']['cbxrb_formid']['formid_empty'] = esc_html__( 'Invalid booking form or booking form doesn\'t exists in backend.',
					'cbxrbooking' );
			}


			$meta = '';

			// form setting field validation
			if ( $form_id > 0 ) {

				// check post type is cbxrbooking
				$form_id_post_type = get_post_type( $form_id );
				if ( $form_id_post_type === false || $form_id_post_type !== 'cbxrbooking' ) {
					$validation_errors['top_errors']['cbxrb_formid']['formid_invalid'] = esc_html__( 'Booking form doesn\'t exists in backend.',
						'cbxrbooking' );
				}

				// get form setting
				$meta = get_post_meta( $form_id, '_cbxrbookingmeta', true );
				if ( $meta != '' ) {
					if ( $meta['settings']['cbxrbooking_style']['require_name'] === 'name-yes' && empty( $name ) ) {
						$validation_errors['cbxrb_name']['name_empty'] = esc_html__( 'Please enter your name',
							'cbxrbooking' );
					}

					if ( $meta['settings']['cbxrbooking_style']['require_email'] === 'email-yes' && empty( $email ) ) {
						$validation_errors['cbxrb_email']['email_empty'] = esc_html__( 'Please enter email address',
							'cbxrbooking' );
					}

					if ( ! empty( $email ) && ! is_email( $email ) ) {
						$validation_errors['cbxrb_email']['email_invalid'] = esc_html__( 'Please provide valid email address',
							'cbxrbooking' );
					}

					if ( $meta['settings']['cbxrbooking_style']['require_phone'] === 'phone-yes' && empty( $phone ) ) {
						$validation_errors['cbxrb_phone']['phone_empty'] = esc_html__( 'Please enter your phone number',
							'cbxrbooking' );
					}


					//check if email banned
					if ( isset( $meta['settings']['cbxrbooking_style']['banned_email'] ) ) {
						$banned_emails = $meta['settings']['cbxrbooking_style']['banned_email'];
						if ( is_array( $banned_emails ) && in_array( $email, $banned_emails ) ) {
							$validation_errors['top_errors']['cbxrb_email']['email_banned'] = esc_html__( 'Sorry! your email address is banned.',
								'cbxrbooking' );
						}
					}

					//check if ip banned
					/*if ( isset( $meta['settings']['cbxrbooking_style']['banned_ip'] ) ) {
						$banned_ips = $meta['settings']['cbxrbooking_style']['banned_ip'];
						if ( is_array( $banned_ips ) && in_array( $_SERVER['REMOTE_ADDR'], $banned_ips ) ) {
							$validation_errors['top_errors']['cbxrb_ip']['ip_banned'] = esc_html__( 'Sorry! your ip address is banned.', 'cbxrbooking' );
						}
					}*/


					$min_party_size = intval( $meta['settings']['cbxrbooking_style']['min_party_size'] );
					$max_party_size = $meta['settings']['cbxrbooking_style']['max_party_size'] != '' ? intval( $meta['settings']['cbxrbooking_style']['max_party_size'] ) : 100;
					// swap party size if min > max
					if ( $min_party_size > $max_party_size ) {
						$swap           = $max_party_size;
						$max_party_size = $min_party_size;
						$min_party_size = $swap;
					}

					if ( ! ( $party >= $min_party_size && $party <= $max_party_size ) ) {
						$validation_errors['cbxrb_party']['party_not_in_range'] = esc_html__( 'Party size is not in between minimum and maximum party size', 'cbxrbooking' );
					}

				}//end if meta exists

				//check if date empty or valid
				if ( empty( $preferred_date ) ) {
					//$hasError                                                = true;
					$validation_errors['cbxrb_preferred_date']['date_empty'] = esc_html__( 'Sorry, Date is empty', 'cbxrbooking' );
				} elseif ( ! CBXRBookingHelper::validateDate( $preferred_date, 'Y-m-d' ) ) {
					//$hasError                                                  = true;
					$validation_errors['cbxrb_preferred_date']['date_invalid'] = esc_html__( 'Sorry! Date is invalid.', 'cbxrbooking' );
				}


				//check if time empty or valid
				if ( empty( $preferred_time ) ) {
					//$hasError                                                = true;
					$validation_errors['cbxrb_preferred_time']['time_empty'] = esc_html__( 'Sorry! Time is empty.',
						'cbxrbooking' );
				} elseif ( ! CBXRBookingHelper::validateDate( $preferred_time, 'H:i' ) ) {
					//$hasError                                                  = true;
					$validation_errors['cbxrb_preferred_time']['time_invalid'] = esc_html__( 'Sorry! Time is invalid.',
						'cbxrbooking' );
				}
			}//end if form exists


			$validation_errors = apply_filters( 'cbxrbooking_admin_form_validation_errors', $validation_errors, $post_data, $form_id, $booking_id );

			if ( sizeof( $validation_errors ) > 0 ) {
				$cbxrbooking_validation_errors['error'] = $validation_errors;//to send in ajax
                wp_send_json( $cbxrbooking_validation_errors ); //ajax
			}


			$default_status = isset( $meta['settings']['cbxrbooking_style']['default_state'] ) ? $meta['settings']['cbxrbooking_style']['default_state'] : 'pending';
			$new_status     = isset( $post_data['cbxrb_status'] ) ? sanitize_text_field( $post_data['cbxrb_status'] ) : $default_status;


			$data_safe['form_id']      = $form_id;
			$data_safe['booking_date'] = $preferred_date;
			$data_safe['booking_time'] = $preferred_time;
			$data_safe['name']         = $name;
			$data_safe['email']        = $email;
			$data_safe['party_size']   = $party;
			$data_safe['phone']        = $phone;
			$data_safe['message']      = $message;

			//$show_form   = 1;
			$messages    = array();
			$success_arr = array();

			//update
			if ( $booking_id > 0 ) {

				//get the old data for the booking log
				$log_data   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $rbookinglog_table WHERE id = %d AND form_id = %d",
					$booking_id, $form_id ) );
				$old_status = $log_data->status;


				$secret = $log_data->secret;

				$meta_data = $log_data->metadata;
				$meta_data = maybe_unserialize( $meta_data );

				$meta_data = apply_filters( 'cbxrbooking_admin_form_meta_data_before_update', $meta_data,
					$post_data, $form_id, $booking_id, $secret );

				$data_safe['user_ip']  = $log_data->user_ip;
				$data_safe['status']   = $new_status;
				$data_safe['metadata'] = maybe_serialize( $meta_data );
				$data_safe['mod_by']   = $user_id;
				$data_safe['mod_date'] = current_time( 'mysql' );

				$data_safe = apply_filters( 'cbxrbooking_admin_form_data_before_update', $data_safe, $form_id,
					$booking_id );

				$where = array(
					'id'      => $booking_id,
					'form_id' => $form_id
				);

				$where        = apply_filters( 'cbxrbooking_admin_form_where_before_update', $where, $data_safe,
					$form_id, $booking_id );
				$where_format = array( '%d', '%d' );
				$where_format = apply_filters( 'cbxrbooking_admin_form_where_format_before_update', $where_format,
					$data_safe, $form_id, $booking_id );


				$col_data_format = array(
					'%d', //form id
					'%s', //booking date
					'%s', ///booking time
					'%s', //name
					'%s', //email
					'%d', //party_size
					'%s', //phone
					'%s', //message
					'%s', //user_ip
					'%s', //status
					'%s', //metadata
					'%d', //mod_by
					'%s'  //mod_date
				);

				$col_data_format = apply_filters( 'cbxrbooking_admin_form_col_data_format_before_update',
					$col_data_format, $data_safe, $form_id, $booking_id );


				$page_url = add_query_arg( array( 'log_id' => $booking_id, 'form_id' => $form_id ), $page_url );


				do_action( 'cbxrbooking_admin_form_before_update', $form_id, $booking_id, $data_safe );

				if ( $wpdb->update( $rbookinglog_table, $data_safe, $where, $col_data_format,
						$where_format ) !== false ) {
					$data_safe['id']         = $booking_id;
					$data_safe['secret']     = $log_data->secret;
					$data_safe['add_by']     = $log_data->add_by;
					$data_safe['add_date']   = $log_data->add_date;
					$data_safe['activation'] = $log_data->activation;

					do_action( 'cbxrbooking_admin_form_after_update', $form_id, $booking_id, $data_safe );

					$message    = array(
						'text' => ( isset( $meta['settings']['cbxrbooking_style']['success_message'] ) && $meta['settings']['cbxrbooking_style']['success_message'] != '' ) ? str_replace( '{booking_code}',
							$secret,
							$meta['settings']['cbxrbooking_style']['success_message'] ) : sprintf( __( 'Booking request updated successfully. Booking code: <code>%s</code>',
							'cbxrbooking' ), $secret ),
						'type' => 'success'
					);
					$messages[] = $message;


					//if no status change then we skip sending any email
					if ( $old_status !== $new_status ) {
						do_action( 'cbxrbooking_log_status_to_' . $new_status, $form_id, $post_data, $data_safe,
							$old_status, $new_status, $meta );
						do_action( 'cbxrbooking_log_status_from_' . $old_status . '_to_' . $new_status, $form_id,
							$post_data, $data_safe, $old_status, $new_status, $meta );

						if ( isset( $post_data['cbxrb_email_notify'] ) && intval( $post_data['cbxrb_email_notify'] ) == 1 ) {
							$plugin_public = new CBXRBooking_Public( $this->plugin_name, $this->version );

							//send email alert to user for booking confirmation email
							if ( $new_status === 'confirmed' ) {
								$messages = $plugin_public->sendFrontendBookingUserEmailAlert( $messages,
									$data_safe, $meta, $new_status );
							}

							//send email alert to user for booking cancel email
							if ( $new_status == 'canceled' ) {
								$messages = $plugin_public->sendBookingCancelUserEmailAlert( $messages, $data_safe,
									$meta, $new_status );
							}
						}

					}

					$messages = apply_filters( 'cbxrbooking_backend_validation_success_messages', $messages, $form_id,
						$booking_id );

					/*if ( isset( $meta['settings']['cbxrbooking_misc']['showform_successful'] ) && $meta['settings']['cbxrbooking_misc']['showform_successful'] == 'off' ) {
						$show_form = 0;
					}*/

				} else {
					$message    = array(
						'text' => esc_html__( 'Sorry! Some problem during updating, please try again.',
							'cbxrbooking' ),
						'type' => 'danger'
					);
					$messages[] = $message;
					//$show_form  = 0;
				}


			} else {
				//insert

				$data_safe['user_ip']    = CBXRBookingHelper::get_ipaddress();
				$data_safe['secret']     = CBXRBookingHelper::generateBookingSecret();
				$data_safe['activation'] = '';
				$data_safe['status']     = $new_status;


				$meta_data             = array();
				$meta_data             = apply_filters( 'cbxrbooking_admin_form_meta_data_before_insert',
					$meta_data, $post_data, $form_id, $booking_id, $data_safe['secret'] );
				$data_safe['metadata'] = maybe_serialize( $meta_data );

				$data_safe['add_by']   = $user_id;
				$data_safe['add_date'] = current_time( 'mysql' );

				$data_safe = apply_filters( 'cbxrbooking_admin_form_data_before_insert', $data_safe, $form_id );


				$col_data_format = array(
					'%d', //form id
					'%s', //booking date
					'%s', ///booking time
					'%s', //name
					'%s', //email
					'%d', //party_size
					'%s', //phone
					'%s', //message
					'%s', //user_ip
					'%s', //secret
					'%s', //activation
					'%s', //status
					'%s', //metadata
					'%d', //add_by
					'%s'  //add_date
				);

				$col_data_format = apply_filters( 'cbxrbooking_admin_form_col_data_format_before_insert',
					$col_data_format, $data_safe, $form_id );

				do_action( 'cbxrbooking_admin_form_before_insert', $form_id, $booking_id );

				if ( $wpdb->insert( $rbookinglog_table, $data_safe, $col_data_format ) !== false ) {
					$booking_id = $wpdb->insert_id;

					$data_safe['id'] = $booking_id;

					do_action( 'cbxrbooking_admin_form_after_insert', $form_id, $booking_id );


					//update the form submission count
					$form_submission_count = intval( get_post_meta( $form_id, '_cbxrbookingmeta_submission_count',
						true ) );
					update_post_meta( $form_id, '_cbxrbookingmeta_submission_count',
						intval( $form_submission_count ) + 1 );


					$message    = array(

						'text' => ( isset( $meta['settings']['cbxrbooking_style']['success_message'] ) && $meta['settings']['cbxrbooking_style']['success_message'] != '' ) ? str_replace( '{booking_code}',
							$data_safe['secret'],
							$meta['settings']['cbxrbooking_style']['success_message'] ) : sprintf( __( 'Booking request submitted successfully. Booking code: <code>%s</code>',
							'cbxrbooking' ), $data_safe['secret'] ),
						'type' => 'success'
					);
					$messages[] = $message;


					if ( isset( $post_data['cbxrb_email_notify'] ) && intval( $post_data['cbxrb_email_notify'] ) == 1 ) {
						//while we insert we can send email using the public facing email alert function

						$plugin_public = new CBXRBooking_Public( $this->plugin_name, $this->version );

						//if status is pending or confirmed we will send notification to user, here we don't need to send notification to admin user, again, I don't see any reason to send email for other status
						if ( $data_safe['status'] == 'pending' || $data_safe['status'] == 'confirmed' ) {
							$messages = $plugin_public->sendFrontendBookingUserEmailAlert( $messages, $data_safe,
								$meta, $data_safe['status'] );
						}
					}


					/*if ( isset( $meta['settings']['cbxrbooking_misc']['showform_successful'] ) && $meta['settings']['cbxrbooking_misc']['showform_successful'] == 'off' ) {
						$show_form = 0;
					}*/

					$messages = apply_filters( 'cbxrbooking_backend_validation_success_messages', $messages, $form_id,
						$booking_id );

				} else {
					//failed to insert
					$message    = array(
						'text' => esc_html__( 'Sorry! Some problem during updating, please refresh and try again.',
							'cbxrbookingfrotendlogaddon' ),
						'type' => 'danger'
					);
					$messages[] = $message;
					//$show_form  = 0;


				}


			}//end insert mode

			$success_arr['messages']  = $messages;
			//$success_arr['show_form'] = $show_form;
			$success_arr['booking_id'] = $booking_id;

            $cbxrbooking_insert['error']   = '';
            $cbxrbooking_insert['success'] = $success_arr;

            wp_send_json( $cbxrbooking_insert );
		}//end submit request
	}//end rbooking_backend_entrysubmit


	/**
	 * Remove Add new menu for booking form in free
	 */
	public function remove_menus_forms() {

		$button_count = wp_count_posts( 'cbxrbooking' );

		//remove add button option if already one button is created
		if ( $button_count->publish > 0 ) {
			do_action( 'cbxrbooking_remove_multiple_form', $this );
		}
	}//end remove_menus_forms


	/**
	 * Remove sub menu for add new booking
	 */
	public function cbxrbooking_remove_core_menu_form() {
		remove_submenu_page( 'edit.php?post_type=cbxrbooking',
			'post-new.php?post_type=cbxrbooking' );        //remove add booking form menu

		$result    = stripos( $_SERVER['REQUEST_URI'], 'post-new.php' );
		$post_type = isset( $_REQUEST['post_type'] ) ? esc_attr( $_REQUEST['post_type'] ) : '';

		if ( $result !== false ) {
			if ( $post_type == 'cbxrbooking' ) {
				wp_redirect( get_option( 'siteurl' ) . '/wp-admin/edit.php?post_type=cbxrbooking&cbxrbooking_error_forms=true' );
			}
		}
	}

	/**
	 * Admin notice if user try to create new button in free version
	 */
	function cbxrbooking_error_notice_forms() {
		if ( isset( $_GET['cbxrbooking_error_forms'] ) ) {
			add_action( 'admin_notices', array( $this, 'permissions_admin_notice_forms' ) );
		}
	}

	/**
	 * Showing Admin notice
	 *
	 */
	function permissions_admin_notice_forms() {
		echo "<div id='permissions-warning' class='error fade'><p><strong>" . sprintf( __( 'Sorry, you can not create more than one booking form in free version, <a target="_blank" href="%s">Grab Pro</a>',
				'cbxrbooking' ),
				'https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/' ) . "</strong></p></div>";
	}


	/**
	 * Remove Add new menu for branch in free core version
	 */
	public function remove_menus_branch() {
		global $wpdb;
		$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

		$sql_select   = "SELECT COUNT(*) FROM $cbxrb_bm_table_name";
		$branch_count = $wpdb->get_var( "$sql_select  WHERE  1" );


		//remove add button option if already one button is created
		if ( $branch_count !== null && intval( $branch_count ) > 0 ) {
			do_action( 'cbxrbooking_remove_multiple_branch', $this );
		}
	}


	/**
	 * Redirect to branch listing if Adding multiple branches in free core version
	 */
	public function cbxrbooking_remove_core_menu_branch() {

		$post_type = isset( $_REQUEST['post_type'] ) ? esc_attr( $_REQUEST['post_type'] ) : '';
		$page      = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		$view      = isset( $_REQUEST['view'] ) ? esc_attr( $_REQUEST['view'] ) : '';
		$id        = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;


		if ( $post_type == 'cbxrbooking' && $page == 'cbxrbookingbranchmanager' && $view == 'addedit' && $id == 0 ) {
			wp_redirect( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&&cbxrbooking_error_branch=true' ) );
		}

	}

	/**
	 * Admin notice if user try to create new button in free version
	 */
	function cbxrbooking_error_notice_branch() {
		if ( isset( $_GET['cbxrbooking_error_branch'] ) ) {
			add_action( 'admin_notices', array( $this, 'permissions_admin_notice_branch' ) );
		}
	}

	/**
	 * Showing Admin notice
	 *
	 */
	function permissions_admin_notice_branch() {
		echo "<div id='permissions-warning' class='error fade'><p><strong>" . sprintf( __( 'Sorry, you can not create more than one branch in free version, <a target="_blank" href="%s">Grab Pro</a>',
				'cbxrbooking' ),
				'https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/' ) . "</strong></p></div>";
	}


	/**
	 * Before delete any cbxrbooking form
	 *
	 * @param $post_id
	 */
	function before_delete_cbxrbookingform( $post_id ) {
		global $post_type;
		if ( $post_type != 'cbxrbooking' ) {
			return;
		}

		//we are deleting a post of type "cbxrbooking"
		//if the form has booking don't allow to delete
		global $wpdb;
		$booking_logs_table = $wpdb->prefix . "cbxrbooking_log_manager"; //logs
		$sql_select         = "SELECT COUNT(*) FROM $booking_logs_table as logs";
		$where_sql          = '';

		$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'form_id=%d', $post_id );
		$count     = intval( $wpdb->get_var( "$sql_select  WHERE  $where_sql" ) );

		if ( $count > 0 ) {
			wp_redirect( admin_url( 'edit.php?post_type=cbxrbooking&cbxrbooking_fdelete_error=true&cbxrbooking_delete_form_id=' . $post_id ) );
			exit();
		}


	}


	/**
	 * Admin notice if user try to delete form having booking
	 */
	function cbxrbooking_form_delete_error_notice() {
		if ( isset( $_GET['cbxrbooking_fdelete_error'] ) ) {
			add_action( 'admin_notices', array( $this, 'form_delete_admin_notice' ) );
		}
	}

	/**
	 * Showing Admin notice if user try to delete form having  more than one booking before delete them
	 *
	 */
	function form_delete_admin_notice() {
		$post_id = intval( $_GET['cbxrbooking_delete_form_id'] );
		$url     = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&form_id=' . $post_id );
		echo "<div id='permissions-warning' class='error fade'><p><strong>" . sprintf( __( 'Sorry, you can not delete or trash this form as there is at least one booking for this form. To delete this form please delete <a href="%s">the bookings for this form.</a>',
				'cbxrbooking' ), $url ) . "</strong></p></div>";
	}

	/**
	 * show branch manager listing
	 */
	public function display_branch_manager_listing_page() {
		if ( isset( $_GET['view'] ) && $_GET['view'] == 'addedit' ) {
			wp_enqueue_script( 'cbxrbooking-cbxrbooking-admin-branch-manager' );
			//include( 'templates/add-edit-branch-manager.php' );
			include( cbxrbooking_locate_template( 'admin/add-edit-branch-manager.php' ) );
		} else {

			if ( ! class_exists( 'CBXRestaurantBookingBranchManager_List_Table' ) ) {
				require_once( plugin_dir_path( __FILE__ ) . '/../includes/class-cbxrbooking-branch-manager.php' );
				//include( 'templates/branch-manager-list.php' );
				include( cbxrbooking_locate_template( 'admin/branch-manager-list.php' ) );
			}

		}
	}

	/**
	 * Add new branch manager
	 *
	 * return string
	 */
	public function add_new_branch_manager_acc() {

		global $wpdb;
		$form_validation              = true;
		$cbxrb_bm_validation['error'] = false;
		$cbxrb_bm_validation['field'] = array();

		$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

		//verify nonce field
		if ( wp_verify_nonce( $_POST['new_acc_verifier'], 'add_new_acc' ) ) {

			$address_input = array();

			if ( isset( $_POST['address'] ) && is_array( $_POST['address'] ) ) {
				foreach ( $_POST['address'] as $address_key => $address_value ) {
					$address_input[ $address_key ] = sanitize_text_field( $address_value );
				}
			}


			$branch_name        = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
			$branch_description = isset( $_POST['description'] ) ? sanitize_text_field( $_POST['description'] ) : '';


			$branch_id = absint( $_POST['cbxrb-bm-acc-id'] );

			$name_len = mb_strlen( $branch_name );

			//check branch manager name length is not less than 5 or more than 200 char
			if ( $name_len < 5 || $name_len > 200 ) {
				$form_validation                = false;
				$cbxrb_bm_validation['error']   = true;
				$cbxrb_bm_validation['field'][] = 'name';
				$cbxrb_bm_validation['msg']     = esc_html__( 'The name field character limit must be between 5 to 200.',
					'cbxrbooking' );
			}

			//check form passes all validation rules
			if ( $form_validation ) {
				$col_data = array(
					'name'        => $branch_name,
					'description' => $branch_description,
					'address'     => maybe_serialize( $address_input ),
				);

				//edit mode
				if ( $branch_id > 0 ) {
					if ( $wpdb->get_row(
							$wpdb->prepare( "SELECT name FROM  $cbxrb_bm_table_name WHERE id = %d",
								$branch_id
							), ARRAY_A
						) != null
					) {

						$col_data['mod_by']   = get_current_user_id();
						$col_data['mod_date'] = current_time( 'mysql' );

						// name, description, address
						$col_data_format = [ '%s', '%s', '%s', '%d', '%s' ];

						$where = [
							'id' => $branch_id
						];

						$where_format = [ '%d' ];

						//matching update function return is false, then update failed.
						if ( $wpdb->update(
								$cbxrb_bm_table_name, $col_data, $where,
								$col_data_format, $where_format
							) === false
						) {
							//update failed
							$cbxrb_bm_validation['msg'] = esc_html__( 'Sorry! you don\'t have enough permission to update account.',
								'cbxrbooking' );
						} else {
							//update successful
							$msg = esc_html__( 'Branch updated.', 'cbxrbooking' );

							$edit_url = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&view=addedit&id=' . $branch_id );
							$add_url  = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&view=addedit' );

							$msg .= ' <a  data-accid="' . $branch_id . '"  href="' . $edit_url . '" class="button cbxrb-edit-bm-acc">';
							$msg .= esc_html__( 'Edit', 'cbxrbooking' );
							$msg .= '</a>';

							$msg .= ' <a  href="' . $add_url . '" class="button cbxrb-new-bm-acc">';
							$msg .= esc_html__( 'Add new', 'cbxrbooking' );
							$msg .= '</a>';

							$cbxrb_bm_validation['error']                = false;
							$cbxrb_bm_validation['msg']                  = $msg;
							$cbxrb_bm_validation['form_value']['id']     = $branch_id;
							$cbxrb_bm_validation['form_value']['status'] = 'updated';
							$cbxrb_bm_validation['form_value']['name']   = stripslashes( esc_attr( ( $col_data['name'] ) ) );
						}
					} else { //if user account doesn't exist with id
						$cbxrb_bm_validation['error'] = true;
						$cbxrb_bm_validation['msg']   = esc_html__( 'You attempted to edit the branch that doesn\'t exist. ',
							'cbxrbooking' );
					}
				} else { //if category is new then go here

					$col_data['add_by']   = get_current_user_id();
					$col_data['add_date'] = current_time( 'mysql' );


					// name, description, address
					$col_data_format = [ '%s', '%s', '%s', '%d', '%s' ];

					//insert new account
					if ( $wpdb->insert( $cbxrb_bm_table_name, $col_data, $col_data_format ) ) {

						//new account inserted successfully
						$acc_id = $wpdb->insert_id;

						$edit_url = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&view=addedit&id=' . $acc_id );

						$msg = esc_html__( 'Branch created successfully.', 'cbxrbooking' );
						$msg .= ' <a  data-accid="' . $acc_id . '"  href="' . $edit_url . '" class="button cbxrb-edit-bm-acc">';
						$msg .= esc_html__( 'Edit', 'cbxrbooking' );
						$msg .= '</a>';

						$cbxrb_bm_validation['error']                = false;
						$cbxrb_bm_validation['msg']                  = $msg;
						$cbxrb_bm_validation['form_value']['id']     = $acc_id;
						$cbxrb_bm_validation['form_value']['status'] = 'new';
						$cbxrb_bm_validation['form_value']['name']   = stripslashes( esc_attr( $col_data['name'] ) );

					} else {
						$cbxrb_bm_validation['error'] = true;
						$cbxrb_bm_validation['msg']   = esc_html__( 'Sorry! Failed to add branch.', 'cbxrbooking' );
					}
				}

			}
		} else { //if wp_nonce not verified then entry here
			$cbxrb_bm_validation['error']   = true;
			$cbxrb_bm_validation['field'][] = 'wp_nonce';
			$cbxrb_bm_validation['msg']     = esc_html__( 'Hacking attempt ?', 'cbxrbooking' );
		}

		echo json_encode( $cbxrb_bm_validation );
		wp_die();
	}//end add_new_branch_manager_acc


	/**
	 * Add "Add New Booking" link to 'cbxrbooking' post type
	 *
	 * @param $actions
	 * @param $post
	 *
	 * @return array
	 */
	function post_row_actions_cbxrbooking_forms( $actions, $post ) {
		// Check for your post type.
		if ( $post->post_type == "cbxrbooking" ) {

		    $form_id = $post_id = intval($post->ID);

			// Build your links URL.
			$add_new_booking_url = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&form_id=' . $post->ID . '&log_id=0' );
			$booking_logs = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs' );
			$booking_logs = add_query_arg('form_id', $form_id, $booking_logs);


			$actions['addnewbooking'] = sprintf(
				'<a href="%s" >%s</a>',
				$add_new_booking_url,
				esc_html__( 'New Booking', 'cbxrbooking' )
			);

			$actions['bookinglogs'] = sprintf(
				'<a href="%s" >%s</a>',
				$booking_logs,
				esc_html__( 'Booking Logs', 'cbxrbooking' )
			);
		}

		return $actions;
	}//end post_row_actions_cbxrbooking_forms

	/**
	 * Full reset the plugin data
	 *
	 *
	 */
	public function plugin_fullreset() {
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'cbxrbookingsettings' && isset( $_REQUEST['cbxrbooking_fullreset'] ) && $_REQUEST['cbxrbooking_fullreset'] == 1 ) {
			global $wpdb;


			$option_values = CBXRBookingHelper::getAllOptionNames();

			foreach ( $option_values as $key => $accounting_option_value ) {
				delete_option( $accounting_option_value['option_name'] );
			}

			//delete tables
			$table_names  = CBXRBookingHelper::getAllDBTablesList();
			$sql          = "DROP TABLE IF EXISTS " . implode( ', ', array_values( $table_names ) );
			$query_result = $wpdb->query( $sql );

			//deleted all 'cbxrbooking' type posts
			global $post;
			$args = array( 'posts_per_page' => - 1, 'post_type' => 'cbxrbooking', 'post_status' => 'any' );

			$myposts = get_posts( $args );
			foreach ( $myposts as $post ) : CBXRBookingHelper::setup_postdata( $post );
				$post_id = intval( $post->ID );
				//delete the post
				wp_delete_post( $post_id, true );
			endforeach;
			CBXRBookingHelper::wp_reset_postdata();

			//now create all again

			CBXRBookingHelper::create_table();

			//please note that, the default options will be created by default


			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );
			//initialize settings
			$this->settings_api->admin_init();

			wp_safe_redirect( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingsettings#cbxrbooking_tools' ) );
			exit();
		}

	}//end plugin_fullreset

	/**
	 * Add plugin documentation url in plugin listing
	 *
	 * @param $links
	 * @param $file
	 *
	 * @return array
	 */
	/*public function plugin_row_meta( $links, $file ) {
		if ( strpos( $file, 'cbxrbooking.php' ) !== false ) {
			$new_links = array(
				'doc'     => '<a href="https://codeboxr.com/documentation-for-cbx-restaurant-booking-for-wordpress/" target="_blank">' . esc_html__( 'Documentation',
						'cbxrbooking' ) . '</a>',
				'support' => '<a href="https://codeboxr.com/contact-us/" target="_blank">' . esc_html__( 'Support',
						'cbxrbooking' ) . '</a>'
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}*///end plugin_row_meta

	/**
	 * Filters the array of row meta for each/specific plugin in the Plugins list table.
	 * Appends additional links below each/specific plugin on the plugins page.
	 *
	 * @access  public
	 *
	 * @param array $links_array An array of the plugin's metadata
	 * @param string $plugin_file_name Path to the plugin file
	 * @param array $plugin_data An array of plugin data
	 * @param string $status Status of the plugin
	 *
	 * @return  array       $links_array
	 */
	public function plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ) {
		if ( strpos( $plugin_file_name, CBXRBOOKING_BASE_NAME ) !== false ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$links_array[] = '<a target="_blank" style="color:#9c27b0 !important; font-weight: bold;" href="https://wordpress.org/support/plugin/cbx-restaurant-booking/" aria-label="' . esc_attr__( 'Free Support', 'cbxrbooking' ) . '">' . esc_html__( 'Free Support', 'cbxrbooking' ) . '</a>';

			$links_array[] = '<a target="_blank" style="color:#9c27b0 !important; font-weight: bold;" href="https://wordpress.org/plugins/cbx-restaurant-booking/#reviews" aria-label="' . esc_attr__( 'Reviews', 'cbxrbooking' ) . '">' . esc_html__( 'Reviews', 'cbxrbooking' ) . '</a>';

			$links_array[] = '<a target="_blank" style="color:#9c27b0 !important; font-weight: bold;" href="https://codeboxr.com/documentation-for-cbx-restaurant-booking-for-wordpress/" aria-label="' . esc_attr__( 'Documentation', 'cbxrbooking' ) . '">' . esc_html__( 'Documentation', 'cbxrbooking' ) . '</a>';


			if ( in_array( 'cbxrbookingproaddon/cbxrbookingproaddon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXRBOOKINGPROADDON_PLUGIN_NAME' ) ) {

			} else {
				$links_array[] = '<a target="_blank" style="color:#9c27b0 !important; font-weight: bold;" href="https://codeboxr.com/product/cbx-restaurant-booking-for-wordpress/#downloadarea" aria-label="' . esc_attr__( 'Try Pro Addon', 'cbxrbooking' ) . '">' . esc_html__( 'Try Pro Addon', 'cbxrbooking' ) . '</a>';
			}


		}

		return $links_array;
	}//end plugin_row_meta

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 *
	 * @return  array
	 */
	public static function plugin_action_links( $links ) {

		$action_links = array(
			'settings' => '<a style="color: #2153cc !important; font-weight: bold;" href="' . admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingsettings' ) . '" aria-label="' . esc_attr__( 'View settings', 'cbxrbooking' ) . '">' . esc_html__( 'Settings', 'cbxrbooking' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}//end plugin_action_links

	/**
	 * If we need to do something in upgrader process is completed for poll plugin
	 *
	 * @param $upgrader_object
	 * @param $options
	 */
	public function plugin_upgrader_process_complete( $upgrader_object, $options ) {
		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == CBXRBOOKING_BASE_NAME ) {
					if ( ! class_exists( 'CBXRBooking_Activator' ) ) {
						require_once plugin_dir_path( __FILE__ ) . '../includes/class-cbxrbooking-activator.php';
					}

					//create tables
					CBXRBookingHelper::create_table();


					set_transient( 'cbxrbooking_upgraded_notice', 1 );

					break;
				}
			}
		}
	}//end plugin_upgrader_process_complete

	/**
	 * Show a notice to anyone who has just installed the plugin for the first time
	 * This notice shouldn't display to anyone who has just updated this plugin
	 */
	public function plugin_activate_upgrade_notices() {
		if ( get_transient( 'cbxrbooking_frontendaddon_deactivate_notice' ) ) {
			delete_transient( 'cbxrbooking_frontendaddon_deactivate_notice' );

			$plugin_version = CBXRBOOKINGFRONTENDLOGADDON_PLUGIN_VERSION;
			$version_required = '1.1.6';

			echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Frontend Addon has been deactivated. CBX Restaurant Booking Frontend Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Frontend Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';

        }

		if ( get_transient( 'cbxrbooking_proaddon_deactivate_notice' ) ) {
			delete_transient( 'cbxrbooking_proaddon_deactivate_notice' );

			$plugin_version = CBXRBOOKINGPROADDON_PLUGIN_VERSION;
			$version_required = '1.0.12';


			//if(version_compare($plugin_version,$version_required, '<') ){
				echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Pro Addon has been deactivated. CBX Restaurant Booking Pro Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Pro Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';
			//}
		}

		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxrbooking_activated_notice' ) ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . sprintf( __( 'Thanks for installing/deactivating <strong>CBX Restaurant Booking</strong> V%s - Codeboxr Team',
					'cbxrbooking' ), CBXRBOOKING_PLUGIN_VERSION ) . '</p>';
			echo '<p>' . sprintf( __( 'Check <a href="%s">Plugin Setting</a> | <a href="%s" target="_blank">Learn More</a>',
					'cbxrbooking' ), admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingsettings' ),
					'https://codeboxr.com/product/cbx-restaurant-booking-for-wordpress/' ) . '</p>';
			echo '</div>';
			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxrbooking_activated_notice' );

			$this->pro_addon_compatibility_campaign();

		}

		// Check the transient to see if we've just activated the plugin
		if ( get_transient( 'cbxrbooking_upgraded_notice' ) ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . sprintf( __( 'Thanks for upgrading <strong>CBX Restaurant Booking</strong> V%s , enjoy the new features and bug fixes - Codeboxr Team',
					'cbxrbooking' ), CBXRBOOKING_PLUGIN_VERSION ) . '</p>';
			echo '<p>' . sprintf( __( 'Check <a href="%s">Plugin Setting</a> | <a href="%s" target="_blank">Learn More</a>',
					'cbxrbooking' ), admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingsettings' ),
					'https://codeboxr.com/product/cbx-restaurant-booking-for-wordpress/' ) . '</p>';
			echo '</div>';
			// Delete the transient so we don't keep displaying the activation message
			delete_transient( 'cbxrbooking_upgraded_notice' );

			$this->pro_addon_compatibility_campaign();

		}
	}//end plugin_activate_upgrade_notices

	/**
	 * Check plugin compatibility and pro addon install campaign
	 */
	public function pro_addon_compatibility_campaign() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		//frontend addon is active and installed
		if ( in_array( 'cbxrbookingfrontendlogaddon/cbxrbookingfrontendlogaddon.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || defined( 'CBXRBOOKINGFRONTENDLOGADDON_PLUGIN_NAME' ) ) {
			//plugin is activated

			$plugin_version = CBXRBOOKINGFRONTENDLOGADDON_PLUGIN_VERSION;
			$version_required = '1.1.6';


			if(version_compare($plugin_version,$version_required, '<') ){
				echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Frontend Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Frontend Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';
			}
		} else {
			echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '<a target="_blank" href="%s">CBX Restaurant Booking Frontend Addon</a> enables frontend management for bookings, try it - Codeboxr Team',
					'cbxrbooking' ),
					'https://codeboxr.com/product/cbx-restaurant-booking-frontend-addon/' ) . '</p></div>';
		}

		//if the pro addon is active or installed
		if ( in_array( 'cbxrbookingproaddon/cbxrbookingproaddon.php', apply_filters( 'active_plugins',
				get_option( 'active_plugins' ) ) ) || defined( 'CBXRBOOKINGPROADDON_PLUGIN_NAME' ) ) {
			//plugin is activated

			$plugin_version = CBXRBOOKINGPROADDON_PLUGIN_VERSION;
			$version_required = '1.0.12';


			if(version_compare($plugin_version,$version_required, '<') ){
				echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__( 'CBX Restaurant Booking Pro Addon V%s or any previous version is not compatible with CBX Restaurant Booking V%s or later. Please update CBX Restaurant Booking Pro Addon to version V%s or later  - Codeboxr Team', 'cbxrbooking' ), $plugin_version, CBXRBOOKING_PLUGIN_VERSION, $version_required) . '</p></div>';
			}
		} else {
			echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '<a target="_blank" href="%s">CBX Restaurant Booking Pro Addon</a> has lots of pro and extended features, try it - Codeboxr Team',
					'cbxrbooking' ),
					'https://codeboxr.com/product/cbx-restaurant-booking-pro-addon/' ) . '</p></div>';
		}
	}//end pro_addon_compatibility_campaign

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function pre_set_transient_update_plugins_pro_addon( $transient ) {
		$cbxrbookingproaddon_slug = 'cbxrbookingproaddon/cbxrbookingproaddon.php';
		// Extra check for 3rd plugins
		if ( isset( $transient->response[ $cbxrbookingproaddon_slug ] ) ) {
			return $transient;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_info = array();
		$all_plugins = get_plugins();
		if ( ! isset( $all_plugins[ $cbxrbookingproaddon_slug ] ) ) {
			return $transient;
		} else {
			$plugin_info = $all_plugins[ $cbxrbookingproaddon_slug ];
		}

		$remote_version = '1.0.12';

		if ( version_compare( $plugin_info['Version'], $remote_version, '<' ) ) {
			$obj                                              = new stdClass();
			$obj->slug                                        = 'cbxrbookingproaddon';
			$obj->new_version                                 = $remote_version;
			$obj->plugin                                      = $cbxrbookingproaddon_slug;
			$obj->url                                         = '';
			$obj->package                                     = false;
			$obj->name                                        = 'CBX Restaurant Booking Pro Addon';
			$transient->response[ $cbxrbookingproaddon_slug ] = $obj;
		}

		return $transient;
	}//end pre_set_transient_update_plugins_pro_addons


	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 *
	 * @return object $ transient
	 */
	public function pre_set_transient_update_plugins_frontend_log_addon( $transient ) {
		$frontend_log_slug = 'cbxrbookingfrontendlogaddon/cbxrbookingfrontendlogaddon.php';
		// Extra check for 3rd plugins
		if ( isset( $transient->response[ $frontend_log_slug ] ) ) {
			return $transient;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_info = array();
		$all_plugins = get_plugins();
		if ( ! isset( $all_plugins[ $frontend_log_slug ] ) ) {
			return $transient;
		} else {
			$plugin_info = $all_plugins[ $frontend_log_slug ];
		}

		$remote_version = '1.1.6';

		if ( version_compare( $plugin_info['Version'], $remote_version, '<' ) ) {
			$obj                                       = new stdClass();
			$obj->slug                                 = 'cbxrbookingfrontendlogaddon';
			$obj->new_version                          = $remote_version;
			$obj->plugin                               = $frontend_log_slug;
			$obj->url                                  = '';
			$obj->package                              = false;
			$obj->name                                 = 'CBX Restaurant Booking Frontend Addon';
			$transient->response[ $frontend_log_slug ] = $obj;
		}

		return $transient;
	}//end pre_set_transient_update_plugins_frontend_log_addon

	/**
	 * Pro Addon update message
	 */
	public function plugin_update_message_pro_addons() {
		echo ' ' . sprintf( __( 'Check how to <a style="color:#9c27b0 !important; font-weight: bold;" href="%s"><strong>Update manually</strong></a> , download latest version from <a style="color:#9c27b0 !important; font-weight: bold;" href="%s"><strong>My Account</strong></a> section of Codeboxr.com', 'cbxrbooking' ), 'https://codeboxr.com/manual-update-pro-addon/', 'https://codeboxr.com/my-account/' );
	}//end plugin_update_message_pro_addons

}//end class CBXRBooking_Admin