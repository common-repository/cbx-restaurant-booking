<?php

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}


	class CBXRestaurantBookingLog_List_Table extends WP_List_Table {

		/**
		 * The current list of all branches.
		 *
		 * @since  3.1.0
		 * @access public
		 * @var array
		 */
		public $all_branches;

		function __construct() {

			//Set parent defaults
			parent::__construct( array(
				'singular' => 'cbxrbookinglog',     //singular name of the listed records
				'plural'   => 'cbxrbookinglogs',    //plural name of the listed records
				'ajax'     => false      //does this table support ajax?
			) );

			// get all branches
			$branches = CBXRBookingHelper::getAllBranches();

			$all_branches = array();
			foreach ( $branches as $branch ) {
				$all_branches[ $branch['id'] ] = $branch['name'];
			}

			$this->all_branches = $all_branches;
		}

		/**
		 * Callback for column 'Status'
		 *
		 * @param $item
		 *
		 * @return array
		 */
		function column_status( $item ) {
			$status_key = stripslashes( $item['status'] );

			return '<code class="cbxrbooking-status">'.CBXRBookingHelper::getBookingStatus( $status_key ).'</code>';
		}

		/**
		 * Callback for column 'Booking Date'
		 *
		 * @param array $item
		 *
		 * @return string
		 */
		function column_booking_date( $item ) {
			return CBXRBookingHelper::viewDateFormat( $item['booking_date'], CBXRBookingHelper::storedGlobalDateFormatKey() );
		}

		/**
		 * Callback for column 'Booking Time'
		 *
		 * @param array $item
		 *
		 * @return string
		 */
		function column_booking_time( $item ) {
			$booking_time = $item['booking_time'];

			$twelve_hour_booking_time = CBXRBookingHelper::twelveHourBookingTimeFormat( 0, $booking_time );
			if ( ! is_null( $twelve_hour_booking_time ) ) {
				$booking_time = $twelve_hour_booking_time;
			}

			return $booking_time;
		}

		/**
		 * Callback for column 'User Name'
		 *
		 * @param array $item
		 *
		 * @return string
		 */
		function column_user_name( $item ) {
			$user_name = stripslashes( $item['name'] );

			$user_info = get_user_by( 'email', $item['email'] );

			if ( $user_info !== false ) {

				if ( current_user_can( 'edit_user', $user_info->ID ) ) {
					$user_name = '<a target="_blank" href="' . get_edit_user_link( $user_info->ID ) . '">' . $user_name . '</a>';
				}
			}

			return $user_name;
		}

		/**
		 * Callback for column 'booking secret'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_secret( $item ) {
			return '<code class="cbxrbooking-code">' . $item['secret'] . '</code>';
		}


		/**
		 * Callback for column 'User Email'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_user_email( $item ) {
			return stripslashes( $item['email'] );
		}

		/**
		 * Callback for column 'User Phone'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_user_phone( $item ) {
			return stripslashes( $item['phone'] );
		}

		/**
		 * Callback for column 'User Message'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_user_message( $item ) {
			$message = stripslashes( $item['message'] );
			if ( strlen( $message ) > 25 ) {
				$message = substr( $message, 0, 25 ) . '...';
			}

			$message = '<p class="cbxrbooking-message-expand" data-title="' . esc_html__( 'User Message', 'cbxrbooking' ) . '" data-content="' . stripslashes( $item['message'] ) . '">' . $message . '</p>';

			return $message;
		}

		/**
		 * Callback for column 'User IP Address'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_user_ip( $item ) {
			return $item['user_ip'];
		}

		/**
		 * Callback for column 'form_id'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_branch_id( $item ) {
			$return_link = '';
			$branch_id   = get_post_meta( intval( $item['form_id'] ), '_cbxrbookingformmeta_branch', true );
			if ( $branch_id != '' ) {
				$branch_id = intval( $branch_id );
				if ( isset( $this->all_branches[ $branch_id ] ) ) {
					$edit_link   = admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&view=addedit&id=' . $branch_id );
					$return_link = stripslashes( $this->all_branches[ $branch_id ] ) . '<a title="' . esc_html__( 'Edit Branch', 'cbxrbooking' ) . '" href="' . $edit_link . '"> (' . esc_html__( 'Edit', 'cbxrbooking' ) . ')</a>';
				}
			}

			return $return_link;
		}


		/**
		 * Callback for column 'form_id'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_form_id( $item ) {
			$edit_url = esc_url( get_edit_post_link( $item['form_id'] ) );

			$edit_link = $item['form_id'];
			if ( ! is_null( $edit_url ) ) {
				$edit_link = $item['form_id'] . '<a href="' . $edit_url . '">' . esc_html__( ' (Edit)', 'cbxrbooking' ) . '</a>';
			}

			return $edit_link;
		}


		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				/*$1%s*/
				$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
				/*$2%s*/
				$item['id']                //The value of the checkbox should be the record's id
			);
		}

		/**
		 * Callback for column 'ID'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_id( $item ) {
			$form_id_post_type = get_post_type( $item['form_id'] );

			$edit_link = $item['id'];
			if ( $form_id_post_type !== false && $form_id_post_type === 'cbxrbooking' ) {
				$edit_url  = esc_url( admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookinglogs&log_id=' . $item['id'] ) );
				$edit_link = $item['id'] . '<a href="' . $edit_url . '">' . esc_html__( ' (Edit)', 'cbxrbooking' ) . '</a>';
			}

			return $edit_link;
		}

		function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'id':
					return $item[ $column_name ];
				case 'booking_date':
					return $item[ $column_name ];
				case 'form_id':
					return $item[ $column_name ];
				case 'branch_id':
					return $item[ $column_name ];
				case 'booking_time':
					return $item[ $column_name ];
				case 'party_size':
					return $item[ $column_name ];
				case 'user_name':
					return $item[ $column_name ];
				case 'user_email':
					return $item[ $column_name ];
				case 'user_phone':
					return $item[ $column_name ];
				case 'user_message':
					return $item[ $column_name ];
				case 'user_ip':
					return $item[ $column_name ];
				case 'status':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ); //Show the whole array for troubleshooting purposes
			}
		}

		/**
		 * Add extra markup in the toolbars before or after the list
		 *
		 * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
		 */
		function extra_tablenav( $which ) {
			if ( $which == "top" ) {
				$form_id   = isset( $_REQUEST['form_id'] ) ? intval( $_REQUEST['form_id'] ) : 0;
				$branch_id = isset( $_REQUEST['branch_id'] ) ? intval( $_REQUEST['branch_id'] ) : 0;

				//get the booking forms
				$booking_forms = CBXRBookingHelper::getAllBookingForms( $branch_id );

				//get the branches
				$all_branches = CBXRBookingHelper::getAllBranches();

				?>

				<!-- branch dropdown filter -->
				<div class="alignleft actions">
					<label for="branch_id"
						   class="screen-reader-text"><?php esc_html_e( 'Filter by Branch', 'cbxrbooking' ) ?></label>
					<select class="form-control branch" name="branch_id" id="branch_id">
						<option <?php echo ( $branch_id == 0 ) ? ' selected="selected" ' : ''; ?>
							value="0"><?php esc_html_e( 'Select Branch', 'cbxrbooking' ); ?></option>
						<?php
							foreach ( $all_branches as $single_branch ):
								$selected = ( ( $branch_id > 0 && $branch_id == $single_branch['id'] ) ? ' selected="selected" ' : '' );
								echo '<option  ' . $selected . ' value="' . $single_branch['id'] . '">' . stripslashes( $single_branch['name'] ) . '(' . esc_html__( 'ID:', 'cbxrbooking' ) . $single_branch['id'] . ')</option>';
								?>
							<?php endforeach; ?>
					</select>
					<input type="submit" name="filter_action" id="post-query-submit" class="button branch-filter-hit"
						   value="<?php esc_html_e( 'Filter', 'cbxrbooking' ) ?>" />
				</div>

				<!-- booking form dropdown filter -->
				<div class="alignleft actions">
					<label for="form_id"
						   class="screen-reader-text"><?php esc_html_e( 'Filter by Booking Form', 'cbxrbooking' ) ?></label>
					<select class="form-control form" name="form_id" id="form_id">
						<option <?php echo ( $form_id == 0 ) ? ' selected="selected" ' : ''; ?>
							value="0"><?php esc_html_e( 'Select Booking Form', 'cbxrbooking' ); ?></option>
						<?php
							foreach ( $booking_forms as $booking_form ):
								$selected = ( ( $form_id > 0 && $form_id == $booking_form['id'] ) ? ' selected="selected" ' : '' );
								echo '<option  ' . $selected . ' value="' . $booking_form['id'] . '">' . stripslashes( $booking_form['form_name'] ) . '(' . esc_html__( 'ID:', 'cbxrbooking' ) . $booking_form['id'] . ')</option>';
								?>
							<?php endforeach; ?>
					</select>
					<input type="submit" name="filter_action" id="post-query-submit" class="button"
						   value="<?php esc_html_e( 'Filter', 'cbxrbooking' ) ?>" />
				</div>

				<!-- log export view through hook -->
				<?php
				do_action( 'cbxrbooking_log_filter_extra', $form_id );
			}
		}


		function get_columns() {
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


		function get_sortable_columns() {
			$sortable_columns = array(
				'id'           => array( 'id', false ), //true means it's already sorted
				'form_id'      => array( 'form_id', false ),
				'secret'       => array( 'secret', false ),
				'booking_date' => array( 'booking_date', false ),
				'booking_time' => array( 'booking_time', false ),
				'user_name'    => array( 'name', false ),
				'party_size'   => array( 'party_size', false ),
				'user_email'   => array( 'email', false ),
				'user_phone'   => array( 'phone', false ),
				'status'       => array( 'status', false ),
			);

			return $sortable_columns;
		}

		/**
		 * List bulk actions for dropdown
		 *
		 * @return array|mixed|void
		 */
		function get_bulk_actions() {
			$current_status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';

			$all_booking_status = CBXRBookingHelper::getAllBookingStatus();

			$bulk_actions = apply_filters( 'cbxrbooking_bulk_action', $all_booking_status );
			if ( isset( $bulk_actions['pending'] ) ) {
				unset( $bulk_actions['pending'] );
			}
			if ( isset( $bulk_actions['unverified'] ) ) {
				unset( $bulk_actions['unverified'] );
			}

			if ( $current_status == 'trashed' ) {
				$bulk_actions_delete = array(
					'delete' => esc_html__( 'Delete', 'cbxrbooking' ),
					''       => esc_html__( '- Change Status to -', 'cbxrbooking' )
				);

				$bulk_actions = $bulk_actions_delete + $bulk_actions;
			} else {
				$bulk_actions_status = array(
					'' => esc_html__( '- Change Status to -', 'cbxrbooking' )
				);
				$bulk_actions        = $bulk_actions_status + $bulk_actions;
			}


			return $bulk_actions;
		}//end get_bulk_actions


		/**
		 * Process bulk action
		 */
		function process_bulk_action() {

			global $wpdb;

			$current_action = $this->current_action();
			$booking_table  = $wpdb->prefix . "cbxrbooking_log_manager";

			//Detect when a bulk action is being triggered...

			if ( 'delete' === $current_action ) {

				if ( ! empty( $_REQUEST['cbxrbookinglog'] ) ) {
					global $wpdb;

					$results = $_REQUEST['cbxrbookinglog'];

					foreach ( $results as $id ) {
						$id = (int) $id;

						//now delete the booking log

						$booking_table = $wpdb->prefix . "cbxrbooking_log_manager";

						//at first keep the log record
						$sql      = $wpdb->prepare( "SELECT * FROM $booking_table WHERE id=%d ", $id );
						$log_info = $wpdb->get_row( $sql, ARRAY_A );


						if ( $log_info !== null && sizeof( $log_info ) > 0 ) {

							//now delete
							$sql           = $wpdb->prepare( "DELETE FROM $booking_table WHERE id=%d", $id );
							$delete_status = $wpdb->query( $sql );

							if ( $delete_status !== false ) {
								$form_submission_count = get_post_meta( $log_info['form_id'], '_cbxrbookingmeta_submission_count', true );

								if ( $form_submission_count != '' ) {
									update_post_meta( $log_info['form_id'], '_cbxrbookingmeta_submission_count', intval( $form_submission_count ) - 1 );
								}
							}
						}

					}
				}

			} else if ( 'unverified' === $current_action || 'pending' === $current_action || 'confirmed' === $current_action
			            || 'canceled' === $current_action || 'waiting' === $current_action || 'seated' === $current_action
			            || 'departed' === $current_action || 'closed' === $current_action || 'archived' === $current_action
			            || 'trashed' === $current_action
			) {
				if ( ! empty( $_REQUEST['cbxrbookinglog'] ) ) {

					$results    = $_REQUEST['cbxrbookinglog'];
					$new_status = $current_action;

					foreach ( $results as $id ) {
						$id = (int) $id;

						$sql      = $wpdb->prepare( "SELECT * FROM $booking_table WHERE id=%d ", $id );
						$log_info = $wpdb->get_row( $sql, ARRAY_A );

						// previous status changed to new status so update
						if ( ! is_null( $log_info ) && $new_status != $log_info['status'] ) {
							$old_status = $log_info['status'];
							$form_id    = $log_info['form_id'];

							do_action( 'cbxrbooking_log_bulk_status_to_' . $new_status, $form_id, $log_info, $old_status, $new_status );
							do_action( 'cbxrbooking_log_bulk_status_from_' . $old_status . '_to_' . $new_status, $form_id, $log_info, $old_status, $new_status );

							$update = $wpdb->update(
								$booking_table,
								array(
									'status' => $new_status,
								),
								array( 'id' => $id ),
								array(
									'%s',
								),
								array( '%d' )
							);
						}

					}
				}
			}

			return;

		}//end process_bulk_action

		/**
		 * Set _column_headers property for table list
		 */
		protected function prepare_column_headers() {
			$this->_column_headers = array(
				$this->get_columns(),
				array(),
				$this->get_sortable_columns(),
			);
		}

		/**
		 * The popular and must have prepare_items method
		 */
		function prepare_items() {
			$this->prepare_column_headers();

			global $wpdb; //This is used only if making any database queries

			$user   = get_current_user_id();
			$screen = get_current_screen();



			$current_offset = get_option( 'gmt_offset' );
			$tzstring       = get_option( 'timezone_string' );

			$check_zone_info = true;

			// Remove old Etc mappings. Fallback to gmt_offset.
			if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
				$tzstring = '';
			}

			if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
				$check_zone_info = false;
				if ( 0 == $current_offset ) {
					$tzstring = '+0';
				} elseif ( $current_offset < 0 ) {
					$tzstring = '' . $current_offset;
				} else {
					$tzstring = '+' . $current_offset;
				}
			}

			$date_time_zone =  new DateTimeZone( $tzstring );


			$current_page = $this->get_pagenum();


			$option_name = $screen->get_option( 'per_page', 'option' ); //the core class name is WP_Screen

			$per_page = intval( get_user_meta( $user, $option_name, true ) );

			if ( $per_page == 0 ) {
				$per_page = intval( $screen->get_option( 'per_page', 'default' ) );
			}


			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();


			$this->_column_headers = array( $columns, $hidden, $sortable );


			$this->process_bulk_action();

			$search  = ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
			$order   = ( isset( $_REQUEST['order'] ) && $_REQUEST['order'] != '' ) ? $_REQUEST['order'] : 'desc';
			$orderby = ( isset( $_REQUEST['orderby'] ) && $_REQUEST['orderby'] != '' ) ? $_REQUEST['orderby'] : 'id';

			$form_id   = isset( $_REQUEST['form_id'] ) ? intval( $_REQUEST['form_id'] ) : 0;
			$branch_id = isset( $_REQUEST['branch_id'] ) ? intval( $_REQUEST['branch_id'] ) : 0;
			$date_from = isset( $_REQUEST['cbxrblogfromDate'] ) ? $_REQUEST['cbxrblogfromDate'] : ''; //date from

			$date_to    = isset( $_REQUEST['cbxrblogtoDate'] ) ? $_REQUEST['cbxrblogtoDate'] : ''; //date end
			$date_range = isset( $_REQUEST['date_range'] ) ? stripslashes( $_REQUEST['date_range'] ) : 'all';
			$status     = isset( $_REQUEST['status'] ) ? stripslashes( $_REQUEST['status'] ) : 'all';


			$data = CBXRBookingHelper::getLogData( $search, $form_id, $date_from, $date_to, $orderby, $order, $per_page, $current_page, $status, $date_range, $branch_id );

			$total_items = intval( CBXRBookingHelper::getLogDataCount( $search, $form_id, $date_from, $date_to, $orderby, $order, $status, $date_range, $branch_id ) );

			$this->items = $data;

			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
			) );

		}//end prepare_items

		protected function pagination( $which ) {

			if ( empty( $this->_pagination_args ) ) {
				return;
			}

			$total_items     = $this->_pagination_args['total_items'];
			$total_pages     = $this->_pagination_args['total_pages'];
			$infinite_scroll = false;
			if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
				$infinite_scroll = $this->_pagination_args['infinite_scroll'];
			}

			if ( 'top' === $which && $total_pages > 1 ) {
				$this->screen->render_screen_reader_content( 'heading_pagination' );
			}

			$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

			$current              = $this->get_pagenum();
			$removable_query_args = wp_removable_query_args();

			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

			$current_url = remove_query_arg( $removable_query_args, $current_url );

			$page_links = array();

			$total_pages_before = '<span class="paging-input">';
			$total_pages_after  = '</span></span>';

			$disable_first = $disable_last = $disable_prev = $disable_next = false;

			if ( $current == 1 ) {
				$disable_first = true;
				$disable_prev  = true;
			}
			if ( $current == 2 ) {
				$disable_first = true;
			}
			if ( $current == $total_pages ) {
				$disable_last = true;
				$disable_next = true;
			}
			if ( $current == $total_pages - 1 ) {
				$disable_last = true;
			}

			$search     = ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
			$form_id    = isset( $_REQUEST['form_id'] ) ? intval( $_REQUEST['form_id'] ) : 0;
			$branch_id  = isset( $_REQUEST['branch_id'] ) ? intval( $_REQUEST['branch_id'] ) : 0;
			$date_from  = isset( $_REQUEST['cbxrblogfromDate'] ) ? $_REQUEST['cbxrblogfromDate'] : ''; //date from
			$date_to    = isset( $_REQUEST['cbxrblogtoDate'] ) ? $_REQUEST['cbxrblogtoDate'] : ''; //date end
			$date_range = isset( $_REQUEST['date_range'] ) ? stripslashes( $_REQUEST['date_range'] ) : 'all';
			$status     = isset( $_REQUEST['status'] ) ? stripslashes( $_REQUEST['status'] ) : 'all';

			$pagination_params = array();
			if ( $search !== '' ) {
				$pagination_params['s'] = $search;
			}
			if ( $form_id !== 0 ) {
				$pagination_params['form_id'] = $form_id;
			}
			if ( $branch_id !== 0 ) {
				$pagination_params['branch_id'] = $branch_id;
			}

			if ( $date_range == 'between_dates' && $date_from != '' && $date_to != '' ) {
				$pagination_params['cbxrblogfromDate'] = $date_from;
				$pagination_params['cbxrblogtoDate']   = $date_to;
			} elseif ( $date_range != 'between_dates' && $date_range != 'all' ) {
				$pagination_params['date_range'] = $date_range;
			}

			if ( $status !== 'all' ) {
				$pagination_params['status'] = $status;
			}

			$pagination_params = apply_filters( 'cbxrbooking_admin_log_pagination_params', $pagination_params );

			if ( $disable_first ) {
				$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
			} else {
				$page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
					esc_url( remove_query_arg( 'paged', $current_url ) ),
					__( 'First page' ),
					'&laquo;'
				);
			}

			if ( $disable_prev ) {
				$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
			} else {
				$pagination_params['paged'] = max( 1, $current - 1 );

				$page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
					esc_url( add_query_arg( $pagination_params, $current_url ) ),
					__( 'Previous page' ),
					'&lsaquo;'
				);
			}

			if ( 'bottom' === $which ) {
				$html_current_page  = $current;
				$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
			} else {
				$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
					'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
					$current,
					strlen( $total_pages )
				);
			}
			$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
			$page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

			if ( $disable_next ) {
				$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
			} else {
				$pagination_params['paged'] = min( $total_pages, $current + 1 );

				$page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
					esc_url( add_query_arg( $pagination_params, $current_url ) ),
					__( 'Next page' ),
					'&rsaquo;'
				);
			}

			if ( $disable_last ) {
				$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
			} else {
				$pagination_params['paged'] = $total_pages;

				$page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
					esc_url( add_query_arg( $pagination_params, $current_url ) ),
					__( 'Last page' ),
					'&raquo;'
				);
			}

			$pagination_links_class = 'pagination-links';
			if ( ! empty( $infinite_scroll ) ) {
				$pagination_links_class = ' hide-if-js';
			}
			$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

			if ( $total_pages ) {
				$page_class = $total_pages < 2 ? ' one-page' : '';
			} else {
				//$page_class = ' no-pages';
				$page_class = ' ';
			}
			$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

			echo $this->_pagination;
		}//end pagination

		/**
		 * Generates content for a single row of the table
		 *
		 * @since  3.1.0
		 * @access public
		 *
		 * @param object $item The current item
		 */
		public function single_row( $item ) {
			$row_class = 'cbxrbooking_row';
			$row_class = apply_filters( 'cbxrbooking_row_class', $row_class, $item );
			echo '<tr id="cbxrbooking_row_' . $item['id'] . '" class="' . $row_class . '">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		/**
		 * Display the list of views available on this table.
		 *
		 * @since  3.1.0
		 * @access public
		 */
		public function views() {
			$date_range       = ( isset( $_REQUEST['date_range'] ) ? $_REQUEST['date_range'] : 'all' );
			$cbxrblogfromDate = isset( $_REQUEST['cbxrblogfromDate'] ) ? $_REQUEST['cbxrblogfromDate'] : '';
			$cbxrblogtoDate   = isset( $_REQUEST['cbxrblogtoDate'] ) ? $_REQUEST['cbxrblogtoDate'] : '';

			$views = CBXRBookingHelper::get_range_views( $date_range, $cbxrblogfromDate, $cbxrblogtoDate );
			/**
			 * Filters the list of available list table views.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * @since 3.5.0
			 *
			 * @param array $views An array of available list table views.
			 */

			$views = apply_filters( "views_{$this->screen->id}", $views );

			if ( empty( $views ) ) {
				return;
			}

			$this->screen->render_screen_reader_content( 'heading_views' );

			echo "<ul id='cbxrbooking_daterange_list' class='subsubsub'>\n";
			foreach ( $views as $class => $view ) {
				$views[ $class ] = "\t<li class='$class'>$view";
			}
			echo implode( " |</li>\n", $views ) . "</li>\n";
			echo "</ul>";

			echo '<div id="cbxrbooking_daterange_between"></div>';

			$views_status = CBXRBookingHelper::get_views_status();

			$views_status = apply_filters( "views_status_{$this->screen->id}", $views_status );

			if ( empty( $views ) ) {
				return;
			}

			//$this->screen->render_screen_reader_content( 'heading_views' );

			echo "<ul id='cbxrbooking_status_list' class='subsubsub clear'>\n";
			foreach ( $views_status as $class => $view ) {
				$views_status[ $class ] = "\t<li class='$class'>$view";
			}
			echo implode( " |</li>\n", $views_status ) . "</li>\n";
			echo "</ul>";
		}

		/**
		 * Message to be displayed when there are no items
		 *
		 * @since  3.1.0
		 * @access public
		 */
		public function no_items() {
			echo '<div class="notice notice-warning inline "><p>' . esc_html__( 'No booking found. Please change your search criteria for better result.', 'cbxrbooking' ) . '</p></div>';
		}
	}