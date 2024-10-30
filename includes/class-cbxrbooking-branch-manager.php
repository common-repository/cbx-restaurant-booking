<?php

	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	/************************** CREATE A PACKAGE CLASS *****************************
	 *******************************************************************************
	 * Create a new list table package that extends the core WP_List_Table class.
	 * WP_List_Table contains most of the framework for generating the table, but we
	 * need to define and override some methods so that our data can be displayed
	 * exactly the way we need it to be.
	 *
	 * To display this example on a page, you will first need to instantiate the class,
	 * then call $yourInstance->prepare_items() to handle any data manipulation, then
	 * finally call $yourInstance->display() to render the table to the page.
	 *
	 * Our theme for this list table is going to be movies.
	 */
	class CBXRestaurantBookingBranchManager_List_Table extends WP_List_TableCBXRBBM {


		/** ************************************************************************
		 * REQUIRED. Set up a constructor that references the parent constructor. We
		 * use the parent reference to set some default configs.
		 ***************************************************************************/
		function __construct() {
			global $status, $page;

			//Set parent defaults
			parent::__construct( array(
				'singular' => 'cbxrbookingbm',     //singular name of the listed records
				'plural'   => 'cbxrbookingbms',    //plural name of the listed records
				'ajax'     => false      //does this table support ajax?
			) );

		}


		/**
		 * Callback for collumn 'id'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_id( $item ) {

			return $item['id'] . ' (<a target="_blank" href="' . admin_url( 'edit.php?post_type=cbxrbooking&page=cbxrbookingbranchmanager&view=addedit&id=' . $item['id'] ) . '">' . esc_html__( 'Edit', 'cbxrbooking' ) . '</a>)';
		}

		/**
		 * Callback for collumn 'poll_title'
		 *
		 * @param array $item
		 *
		 * @return string
		 */

		function column_name( $item ) {

			return stripslashes( $item['name'] );
		}

		function column_description( $item ) {

			return stripslashes( $item['description'] );
		}


		function column_address( $item ) {

			$addressinfo = maybe_unserialize( $item['address'] );

			$line    = isset( $addressinfo['line'] ) ? esc_html( $addressinfo['line'] ) : '';
			$city    = isset( $addressinfo['city'] ) ? esc_html( $addressinfo['city'] ) : '';
			$state   = isset( $addressinfo['state'] ) ? esc_html( $addressinfo['state'] ) : '';
			$postal  = isset( $addressinfo['postal'] ) ? esc_html( $addressinfo['postal'] ) : '';
			$country = isset( $addressinfo['country'] ) ? esc_html( $addressinfo['country'] ) : 'none';


			$output = ( $line != '' ) ? ( '<p>' . esc_html__( 'Address: ', 'cbxrbooking' ) . $line . '</p>' ) : '';
			$output .= ( $line != '' ) ? ( '<p>' . esc_html__( 'City: ', 'cbxrbooking' ) . $city . '</p>' ) : '';
			$output .= ( $state != '' ) ? ( '<p>' . esc_html__( 'State: ', 'cbxrbooking' ) . $state . '</p>' ) : '';
			$output .= ( $postal != '' ) ? ( '<p>' . esc_html__( 'Postal: ', 'cbxrbooking' ) . $postal . '</p>' ) : '';
			$output .= ( $country != 'none' ) ? ( '<p>' . esc_html__( 'Country: ', 'cbxrbooking' ) . CBXRBookingHelper::getCountry( $country ) . '</p>' ) : '';

			return $output;
		}


		/** ************************************************************************
		 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
		 * is given special treatment when columns are processed. It ALWAYS needs to
		 * have it's own method.
		 *
		 * @see WP_List_Table::::single_row_columns()
		 *
		 * @param array $item A singular item (one full row's worth of data)
		 *
		 * @return string Text to be placed inside the column <td> (movie title only)
		 **************************************************************************/
		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				/*$1%s*/
				$this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
				/*$2%s*/
				$item['id']                //The value of the checkbox should be the record's id
			);
		}

		/** ************************************************************************
		 * Recommended. This method is called when the parent class can't find a method
		 * specifically build for a given column. Generally, it's recommended to include
		 * one method for each column you want to render, keeping your package class
		 * neat and organized. For example, if the class needs to process a column
		 * named 'title', it would first see if a method named $this->column_title()
		 * exists - if it does, that method will be used. If it doesn't, this one will
		 * be used. Generally, you should try to use custom column methods as much as
		 * possible.
		 *
		 * Since we have defined a column_title() method later on, this method doesn't
		 * need to concern itself with any column with a name of 'title'. Instead, it
		 * needs to handle everything else.
		 *
		 * For more detailed insight into how columns are handled, take a look at
		 * WP_List_Table::single_row_columns()
		 *
		 * @param array $item        A singular item (one full row's worth of data)
		 * @param array $column_name The name/slug of the column to be processed
		 *
		 * @return string Text or HTML to be placed inside the column <td>
		 **************************************************************************/
		function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'id':
					return $item[ $column_name ];
				case 'name':
					return $item[ $column_name ];
				case 'description':
					return $item[ $column_name ];
				case 'address':
					return $item[ $column_name ];

				default:
					return print_r( $item, true ); //Show the whole array for troubleshooting purposes
			}
		}


		function get_columns() {
			$columns = array(
				'cb'          => '<input type="checkbox" />', //Render a checkbox instead of text
				'id'          => esc_html__( 'ID', 'cbxrbooking' ),
				'name'        => esc_html__( 'Branch name', 'cbxrbooking' ),
				'description' => esc_html__( 'Description', 'cbxrbooking' ),
				'address'     => esc_html__( 'Address', 'cbxrbooking' ),
			);

			return $columns;
		}


		function get_sortable_columns() {
			$sortable_columns = array(
				'id'   => array( 'id', false ),
				'name' => array( 'name', false ),     //true means it's already sorted
			);

			return $sortable_columns;
		}


		function get_bulk_actions() {

			$bulk_actions_array = array(
				'delete' => esc_html__( 'Delete', 'cbxrbooking' )
			);
			$bulk_actions       = apply_filters( 'cbxrbooking_branch_bulk_action', $bulk_actions_array );

			return $bulk_actions;
		}


		function process_bulk_action() {

			$current_action = $this->current_action();

			//Detect when a bulk action is being triggered...
			if ( 'delete' === $current_action ) {

				if ( ! empty( $_GET['cbxrbookingbm'] ) ) {
					global $wpdb;

					$results = $_GET['cbxrbookingbm'];

					foreach ( $results as $id ) {
						$id = (int) $id;
						//now delete the user log
						$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

						//now delete
						$sql = $wpdb->prepare( "DELETE FROM $cbxrb_bm_table_name WHERE id=%d", $id );
						$wpdb->query( $sql );
					}
				}
			}

			return;

		}


		function prepare_items() {
			//global $wpdb; //This is used only if making any database queries

			/**
			 * First, lets decide how many records per page to show
			 */
			$user = get_current_user_id();

			$screen = get_current_screen();


			/**
			 * REQUIRED for pagination. Let's figure out what page the user is currently
			 * looking at. We'll need this later, so you should always include it in
			 * your own package classes.
			 */
			$current_page = $this->get_pagenum();


			$option_name = $screen->get_option( 'per_page', 'option' ); //the core class name is WP_Screen


			$per_page = intval( get_user_meta( $user, $option_name, true ) );


			if ( $per_page == 0 ) {
				$per_page = intval( $screen->get_option( 'per_page', 'default' ) );
			}


			/**
			 * REQUIRED. Now we need to define our column headers. This includes a complete
			 * array of columns to be displayed (slugs & titles), a list of columns
			 * to keep hidden, and a list of columns that are sortable. Each of these
			 * can be defined in another method (as we've done here) before being
			 * used to build the value for our _column_headers property.
			 */
			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();


			/**
			 * REQUIRED. Finally, we build an array to be used by the class for column
			 * headers. The $this->_column_headers property takes an array which contains
			 * 3 other arrays. One for all columns, one for hidden columns, and one
			 * for sortable columns.
			 */
			$this->_column_headers = array( $columns, $hidden, $sortable );


			/**
			 * Optional. You can handle your bulk actions however you see fit. In this
			 * case, we'll handle them within our package just to keep things clean.
			 */
			$this->process_bulk_action();


			/**
			 * Instead of querying a database, we're going to fetch the example data
			 * property we created for use in this plugin. This makes this example
			 * package slightly different than one you might build on your own. In
			 * this example, we'll be using array manipulation to sort and paginate
			 * our data. In a real-world implementation, you will probably want to
			 * use sort and pagination data to build a custom query instead, as you'll
			 * be able to use your precisely-queried data immediately.
			 */


			$order   = ( isset( $_GET['order'] ) && $_GET['order'] != '' ) ? $_GET['order'] : 'desc';
			$orderby = ( isset( $_GET['orderby'] ) && $_GET['orderby'] != '' ) ? $_GET['orderby'] : 'id';

			$search = ( isset( $_GET['s'] ) && $_GET['s'] != '' ) ? sanitize_text_field( $_GET['s'] ) : '';


			$data        = $this->getData( $search, $orderby, $order, $per_page, $current_page );
			$total_items = intval( $this->getDataCount( $search, $orderby, $order ) );


			/**
			 * The WP_List_Table class does not handle pagination for us, so we need
			 * to ensure that the data is trimmed to only the current page. We can use
			 * array_slice() to
			 */
			//$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);


			/**
			 * REQUIRED. Now we can add our *sorted* data to the items property, where
			 * it can be used by the rest of the class.
			 */
			$this->items = $data;


			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
			) );
		}

		/**
		 * Get Data
		 *
		 * @param int $perpage
		 * @param int $page
		 *
		 * @return array|null|object
		 */
		function getData( $search = '', $orderby = 'id', $order = 'desc', $perpage = 20, $page = 1 ) {

			global $wpdb;

			$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';


			$sql_select = "SELECT * FROM $cbxrb_bm_table_name";


			$where_sql = '';

			if ( $search != '' ) {
				if ( $where_sql != '' ) {
					$where_sql .= ' AND ';
				}
				$where_sql .= $wpdb->prepare( " name LIKE '%%%s%%' OR description LIKE '%%%s%%' ", $search, $search, $search );
			}


			if ( $where_sql == '' ) {
				$where_sql = '1';
			}


			$start_point = ( $page * $perpage ) - $perpage;
			$limit_sql   = "LIMIT";
			$limit_sql   .= ' ' . $start_point . ',';
			$limit_sql   .= ' ' . $perpage;


			$sortingOrder = " ORDER BY $orderby $order ";


			$data = $wpdb->get_results( "$sql_select  WHERE  $where_sql $sortingOrder  $limit_sql", 'ARRAY_A' );


			return $data;
		}

		/**
		 * Get total data count
		 *
		 * @param int $perpage
		 * @param int $page
		 *
		 * @return array|null|object
		 */
		function getDataCount( $search = '', $orderby = 'id', $order = 'desc' ) {

			global $wpdb;

			$cbxrb_bm_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

			$sql_select = "SELECT COUNT(*) FROM $cbxrb_bm_table_name";

			$where_sql = '';


			if ( $search != '' ) {
				if ( $where_sql != '' ) {
					$where_sql .= ' AND ';
				}
				$where_sql .= $wpdb->prepare( " name LIKE '%%%s%%' OR description LIKE '%%%s%%' ", $search, $search, $search );
			}

			if ( $where_sql == '' ) {
				$where_sql = '1';
			}


			$sortingOrder = " ORDER BY $orderby $order ";


			$count = $wpdb->get_var( "$sql_select  WHERE  $where_sql $sortingOrder" );

			return $count;
		}


	}

	class WP_List_TableCBXRBBM extends WP_List_Table {
		/**
		 * Generates content for a single row of the table
		 *
		 * @since  3.1.0
		 * @access public
		 *
		 * @param object $item The current item
		 */
		/*public function single_row( $item ) {

			$row_class = 'cbxpoll_vote_row';
			$row_class = apply_filters('cbxpoll_vote_row_class', $row_class, $item);
			echo '<tr id="cbxpoll_vote_row_'.$item['id'].'" class="'.$row_class.'">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}*/

		/**
		 * Get the top links before the table listing
		 *
		 * @return array
		 */
		/* function get_views(){

			 $views = array();
			 $current = ( isset($_REQUEST['published']) ? $_REQUEST['published'] : 'all');


			 $poll_id    = isset($_GET['poll_id'])? intval($_GET['poll_id']): 0;
			 $data = cbxpollHelper::getVoteCountByStatus($poll_id);

			 //All link
			 $class      = ($current == 'all' ? ' class="current"' :'');
			 $all_url    = remove_query_arg('published');
			 $views['all'] = "<a href='{$all_url }' {$class} >".sprintf(__('All (%d)', 'cbxrbooking'), $data['total'])."</a>";

			 //published link
			 $foo_url    = add_query_arg('published','1');
			 $class      = ($current == '1' ? ' class="current"' :'');
			 $views['published'] = "<a href='{$foo_url}' {$class} >".sprintf(__('Published (%d)','cbxrbooking'), $data[1])."</a>";

			 //unpublished link
			 $bar_url    = add_query_arg('published','0');
			 $class      = ($current == '0' ? ' class="current"' :'');
			 $views['unpublished'] = "<a href='{$bar_url}' {$class} >".sprintf(__('Unpublished (%s)','cbxrbooking'), $data[0])."</a>";


			 //spam  link
			 $bar_url    = add_query_arg('published','2');
			 $class      = ($current == '2' ? ' class="current"' :'');
			 $views['spam'] = "<a href='{$bar_url}' {$class} >".sprintf(__('Spam (%d)','cbxrbooking'), $data[2])."</a>";

			 return $views;
		 }*/
	}