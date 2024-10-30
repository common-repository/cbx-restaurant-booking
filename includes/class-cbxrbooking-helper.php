<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
	/**
	 * The phelper functionality of the plugin.
	 *
	 * @link       http://codeboxr.com
	 * @since      1.0.0
	 *
	 * @package    CBXRBooking
	 * @subpackage CBXRBooking/includes
	 */

	/**
	 * Class CBXRBookingHelper
	 *
	 */
	class CBXRBookingHelper {
		/**
		 * Get all  core tables list
		 */
		public static function getAllDBTablesList() {
			global $wpdb;

			$table_names   = array();
			$table_names['cbxrbooking_log_manager'] = $wpdb->prefix . "cbxrbooking_log_manager";
			$table_names['cbxrbooking_branch_manager'] = $wpdb->prefix . "cbxrbooking_branch_manager";


			return apply_filters( 'cbxrbooking_table_list', $table_names );
		}//end getAllDBTablesList

		/**
		 * List all global option name with prefix cbxpoll_
		 */
		public static function getAllOptionNames() {
			global $wpdb;

			$prefix       = 'cbxrbooking_';
			$option_names = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '{$prefix}%'", ARRAY_A );

			return apply_filters( 'cbxrbooking_option_names', $option_names );
		}
		/**
		 * return all date format
		 *
		 * @return mixed|void
		 */
		public static function getAllDateFormat() {
			$date_format_arr = array(
				'0' => esc_html__( 'Y-m-d', 'cbxrbooking' ),
				'1' => esc_html__( 'Y-M-D', 'cbxrbooking' ),
				'2' => esc_html__( 'd-m-y', 'cbxrbooking' ),
			);

			return apply_filters( 'cbxrbooking_all_date_format', $date_format_arr );
		}//end getAllDateFormat

		/**
		 * Return single date format
		 *
		 * @param int $format_key
		 *
		 * @return mixed
		 */
		public static function getSingleDateFormat( $format_key = 0 ) {
			$date_format_arr = self::getAllDateFormat();
			if ( is_array( $date_format_arr ) && isset( $date_format_arr[ $format_key ] ) ) {
				$single_date_format = $date_format_arr[ $format_key ];
			} else {
				$single_date_format = $date_format_arr[0];
			}

			return $single_date_format;
		}

		/**
		 * global stored date format key from setting
		 * @return int
		 */
		public static function storedGlobalDateFormatKey() {
			$cbxrb_global_settings = get_option( 'cbxrbooking_global' );

			$global_date_format_key = 0;
			if ( $cbxrb_global_settings !== false && isset( $cbxrb_global_settings['global_date_format'] ) ) {
				$global_date_format_key = intval( $cbxrb_global_settings['global_date_format'] );
			}

			return $global_date_format_key;
		}

		/**
		 * form stored date format key from setting
		 * @return int
		 */
		public static function storedFormDateFormatKey( $post_id = 0 ) {

			$form_date_format_key = 0;

			$post_id = intval( $post_id );
			if ( $post_id > 0 ) {
				$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );
				if ( is_array( $cbxrbooking_meta ) && sizeof( $cbxrbooking_meta ) > 0 ) {
					if ( isset( $cbxrbooking_meta['settings']['cbxrbooking_booking_schedule']['date_format'] ) ) {
						$form_date_format_key = intval( $cbxrbooking_meta['settings']['cbxrbooking_booking_schedule']['date_format'] );
					}
				}
			}

			return $form_date_format_key;
		}

		/**
		 * view date format
		 *
		 * @param string $timestamp
		 * @param int    $date_format_key
		 *
		 * @return false|string
		 */
		public static function viewDateFormat( $timestamp = '', $date_format_key = 0 ) {

			$date_format = self::getSingleDateFormat( $date_format_key );

			return date( $date_format, strtotime( $timestamp ) );
		}


		/**
		 * Returns all booking status
		 *
		 * @return array
		 */
		public static function getAllBookingStatus() {

			$status = array(
				'unverified'     => esc_html__( 'Unverified', 'cbxrbooking' ),
				'pending'        => esc_html__( 'Pending', 'cbxrbooking' ),
				'confirmed'      => esc_html__( 'Confirmed', 'cbxrbooking' ),
				'canceled'       => esc_html__( 'Cancelled', 'cbxrbooking' ),
				'cancel-request' => esc_html__( 'Cancel Requested', 'cbxrbooking' ),
				'waiting'        => esc_html__( 'Waiting', 'cbxrbooking' ),
				'seated'         => esc_html__( 'Seated', 'cbxrbooking' ),
				'departed'       => esc_html__( 'Departed', 'cbxrbooking' ),
				'closed'         => esc_html__( 'Closed', 'cbxrbooking' ),
				'archived'       => esc_html__( 'Archived', 'cbxrbooking' ),
				'trashed'        => esc_html__( 'Trashed', 'cbxrbooking' ),
			);

			return apply_filters( 'cbxrbooking_status', $status );
		}//end getAllBookingStatus

		/**
		 * Returns booking status
		 *
		 * @return array
		 */
		public static function getBookingStatus( $key ) {
			$all_status = self::getAllBookingStatus();

			return isset( $all_status[ $key ] ) ? $all_status[ $key ] : esc_html__( 'Unknown Status', 'cbxrbooking' );
		}//end getBookingStatus

		/**
		 * Get IP Address
		 *
		 * @return string|void
		 * return ip address
		 */
		public static function get_ipaddress() {

			if ( empty( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {

				$ip_address = $_SERVER["REMOTE_ADDR"];
			} else {

				$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
			}

			if ( strpos( $ip_address, ',' ) !== false ) {

				$ip_address = explode( ',', $ip_address );
				$ip_address = $ip_address[0];
			}

			return esc_attr( $ip_address );
		}

		public static function getCountry( $key ) {
			$countries = self::getAllCountries();
			if ( $key == '' || $key == 'none' ) {
				return '';
			}

			if ( isset( $countries[ $key ] ) ) {
				return $countries[ $key ];
			} else {
				'';
			}
		}

		/**
		 * Get all countries
		 *
		 * @return array
		 */
		public static function getAllCountries() {
			$countries = array(
				'AF' => 'Afghanistan',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'DS' => 'American Samoa',
				'AD' => 'Andorra',
				'AO' => 'Angola',
				'AI' => 'Anguilla',
				'AQ' => 'Antarctica',
				'AG' => 'Antigua and Barbuda',
				'AR' => 'Argentina',
				'AM' => 'Armenia',
				'AW' => 'Aruba',
				'AU' => 'Australia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'BS' => 'Bahamas',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BB' => 'Barbados',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BZ' => 'Belize',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BT' => 'Bhutan',
				'BO' => 'Bolivia',
				'BA' => 'Bosnia and Herzegovina',
				'BW' => 'Botswana',
				'BV' => 'Bouvet Island',
				'BR' => 'Brazil',
				'IO' => 'British Indian Ocean Territory',
				'BN' => 'Brunei Darussalam',
				'BG' => 'Bulgaria',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'KH' => 'Cambodia',
				'CM' => 'Cameroon',
				'CA' => 'Canada',
				'CV' => 'Cape Verde',
				'KY' => 'Cayman Islands',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'CL' => 'Chile',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos (Keeling) Islands',
				'CO' => 'Colombia',
				'KM' => 'Comoros',
				'CG' => 'Congo',
				'CK' => 'Cook Islands',
				'CR' => 'Costa Rica',
				'HR' => 'Croatia (Hrvatska)',
				'CU' => 'Cuba',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'DJ' => 'Djibouti',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'TP' => 'East Timor',
				'EC' => 'Ecuador',
				'EG' => 'Egypt',
				'SV' => 'El Salvador',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'EE' => 'Estonia',
				'ET' => 'Ethiopia',
				'FK' => 'Falkland Islands (Malvinas)',
				'FO' => 'Faroe Islands',
				'FJ' => 'Fiji',
				'FI' => 'Finland',
				'FR' => 'France',
				'FX' => 'France, Metropolitan',
				'GF' => 'French Guiana',
				'PF' => 'French Polynesia',
				'TF' => 'French Southern Territories',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GK' => 'Guernsey',
				'GR' => 'Greece',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GU' => 'Guam',
				'GT' => 'Guatemala',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HM' => 'Heard and Mc Donald Islands',
				'HN' => 'Honduras',
				'HK' => 'Hong Kong',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IN' => 'India',
				'IM' => 'Isle of Man',
				'ID' => 'Indonesia',
				'IR' => 'Iran (Islamic Republic of)',
				'IQ' => 'Iraq',
				'IE' => 'Ireland',
				'IL' => 'Israel',
				'IT' => 'Italy',
				'CI' => 'Ivory Coast',
				'JE' => 'Jersey',
				'JM' => 'Jamaica',
				'JP' => 'Japan',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KE' => 'Kenya',
				'KI' => 'Kiribati',
				'KP' => "Korea, Democratic People's Republic of",
				'KR' => 'Korea, Republic of',
				'XK' => 'Kosovo',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => "Lao People's Democratic Republic",
				'LV' => 'Latvia',
				'LB' => 'Lebanon',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libyan Arab Jamahiriya',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MO' => 'Macau',
				'MK' => 'Macedonia',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'ML' => 'Mali',
				'MT' => 'Malta',
				'MH' => 'Marshall Islands',
				'MQ' => 'Martinique',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'TY' => 'Mayotte',
				'MX' => 'Mexico',
				'FM' => 'Micronesia, Federated States of',
				'MD' => 'Moldova, Republic of',
				'MC' => 'Monaco',
				'MN' => 'Mongolia',
				'ME' => 'Montenegro',
				'MS' => 'Montserrat',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'MM' => 'Myanmar',
				'NA' => 'Namibia',
				'NR' => 'Nauru',
				'NP' => 'Nepal',
				'NL' => 'Netherlands',
				'AN' => 'Netherlands Antilles',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NI' => 'Nicaragua',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'MP' => 'Northern Mariana Islands',
				'NO' => 'Norway',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PW' => 'Palau',
				'PS' => 'Palestine',
				'PA' => 'Panama',
				'PG' => 'Papua New Guinea',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PH' => 'Philippines',
				'PN' => 'Pitcairn',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'PR' => 'Puerto Rico',
				'QA' => 'Qatar',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RU' => 'Russian Federation',
				'RW' => 'Rwanda',
				'KN' => 'Saint Kitts and Nevis',
				'LC' => 'Saint Lucia',
				'VC' => 'Saint Vincent and the Grenadines',
				'WS' => 'Samoa',
				'SM' => 'San Marino',
				'ST' => 'Sao Tome and Principe',
				'SA' => 'Saudi Arabia',
				'SN' => 'Senegal',
				'RS' => 'Serbia',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SG' => 'Singapore',
				'SK' => 'Slovakia',
				'SI' => 'Slovenia',
				'SB' => 'Solomon Islands',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'GS' => 'South Georgia South Sandwich Islands',
				'ES' => 'Spain',
				'LK' => 'Sri Lanka',
				'SH' => 'St. Helena',
				'PM' => 'St. Pierre and Miquelon',
				'SD' => 'Sudan',
				'SR' => 'Suriname',
				'SJ' => 'Svalbard and Jan Mayen Islands',
				'SZ' => 'Swaziland',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'SY' => 'Syrian Arab Republic',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TZ' => 'Tanzania, United Republic of',
				'TH' => 'Thailand',
				'TG' => 'Togo',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TT' => 'Trinidad and Tobago',
				'TN' => 'Tunisia',
				'TR' => 'Turkey',
				'TM' => 'Turkmenistan',
				'TC' => 'Turks and Caicos Islands',
				'TV' => 'Tuvalu',
				'UG' => 'Uganda',
				'UA' => 'Ukraine',
				'AE' => 'United Arab Emirates',
				'GB' => 'United Kingdom',
				'US' => 'United States',
				'UM' => 'United States minor outlying islands',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VU' => 'Vanuatu',
				'VA' => 'Vatican City State',
				'VE' => 'Venezuela',
				'VN' => 'Vietnam',
				'VG' => 'Virgin Islands (British)',
				'VI' => 'Virgin Islands (U.S.)',
				'WF' => 'Wallis and Futuna Islands',
				'EH' => 'Western Sahara',
				'YE' => 'Yemen',
				'ZR' => 'Zaire',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe'
			);

			return $countries;
		}

		public static function generateBookingSecret() {
			return wp_generate_password( $length = 12, false, false );
		}//end generateBookingSecret

		/**
		 * Return all branches
		 *
		 * @return array|null|object
		 */
		public static function getAllBranches() {
			global $wpdb;
			$branch_manager_table_name = $wpdb->prefix . 'cbxrbooking_branch_manager';

			$all_branches = $wpdb->get_results( "SELECT id, name FROM $branch_manager_table_name", 'ARRAY_A' );

			return $all_branches;
		}//end getAllBranches

		/**
		 * Get all booking forms for any branch id
		 *
		 * @param int $branch_id
		 *
		 * @return array|object|null
		 */
		public static function getAllBookingForms( $branch_id = 0 ) {
			global $wpdb;
			$posts_table    = $wpdb->prefix . "posts";
			$postmeta_table = $wpdb->prefix . "postmeta";

			//get the booking forms
			$sql_select = "SELECT DISTINCT posts.ID as id, posts.post_title as form_name FROM $posts_table as posts ";

			$where_sql = $wpdb->prepare( "posts.post_type = %s AND posts.post_status = %s", 'cbxrbooking', 'publish' );
			$join      = '';

			// add branch id in where clause
			if ( $branch_id > 0 ) {
				$join      .= " JOIN $postmeta_table postmeta ON posts.ID = postmeta.post_id";
				$where_sql .= ( $where_sql != '' ) ? ' AND ' : '';
				$where_sql .= $wpdb->prepare( "postmeta.meta_key = %s AND postmeta.meta_value = %d", '_cbxrbookingformmeta_branch', $branch_id );
			}

			$sortingOrder  = " ORDER BY posts.post_title desc ";
			$booking_forms = $wpdb->get_results( "$sql_select $join WHERE $where_sql $sortingOrder", ARRAY_A );

			return $booking_forms;
		}//end getAllBookingForms

		/**
		 * Get Data
		 *
		 * @param string $search
		 * @param int $form_id
		 * @param string $date_from
		 * @param string $date_to
		 * @param string $orderby
		 * @param string $order
		 * @param int $perpage
		 * @param int $page
		 * @param string $status
		 * @param string $date_range
		 * @param int $branch_id
		 *
		 * @return array|object|null
		 * @throws Exception
		 */
		public static function getLogData( $search = '', $form_id = 0, $date_from = '', $date_to = '', $orderby = 'id', $order = 'asc', $perpage = 20, $page = 1, $status = 'all', $date_range = 'all', $branch_id = 0 ) {

			global $wpdb;
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
			$today_datetime_requested      = new DateTime( 'now', $date_time_zone);
			$today_date = $today_datetime_requested->format( 'Y-m-d' );



			$booking_table = $wpdb->prefix . "cbxrbooking_log_manager";

			$sql_select = "SELECT * FROM $booking_table as logs";

			$where_sql = '';

			if ( $search != '' ) {
				if ( $where_sql != '' ) {
					$where_sql .= ' AND ';
				}
				$where_sql .= $wpdb->prepare( " name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR phone LIKE '%%%s%%' OR message LIKE '%%%s%%' OR secret LIKE '%%%s%%'", $search, $search, $search, $search, $search );
			}

			$join = '';

			if ( $branch_id !== 0 ) {
				$postmeta_table = $wpdb->prefix . "postmeta"; // postmeta

				$join .= " JOIN $postmeta_table postmeta ON logs.form_id = postmeta.post_id";

				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
				$where_sql .= $wpdb->prepare( "postmeta.meta_key = %s AND postmeta.meta_value = %d", '_cbxrbookingformmeta_branch', $branch_id );
			}

			if ( $form_id !== 0 ) {
				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'form_id=%d', $form_id );
			}

			if ( $status !== 'all' ) {
				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'status=%s', $status );
			}

			if ( $date_range !== '' ) {

				if ( $date_range == 'upcoming' ) {
					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
					//$where_sql .= $wpdb->prepare( 'booking_date > %s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date > %s', $today_date );
				} elseif ( $date_range == 'today' ) {
					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
					//$where_sql .= $wpdb->prepare( 'booking_date = %s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date = %s', $today_date );
				} elseif ( $date_range == 'previous' ) {
					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
					//$where_sql .= $wpdb->prepare( 'booking_date < %s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date < %s', $today_date );
				} elseif ( $date_range == 'between_dates' && $date_from != '' && $date_to != '' ) {


					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
					//$where_sql .= " CAST(logs.booking_date AS DATE) ";
					$where_sql .= " CAST(concat(logs.booking_date, ' ', logs.booking_time) as datetime) ";
					$where_sql .= $wpdb->prepare( "between %s AND %s ", $date_from, $date_to );
				}
			}

			if ( $where_sql == '' ) {
				$where_sql = '1';
			}

			$start_point = ( $page * $perpage ) - $perpage;
			$limit_sql   = "LIMIT";
			$limit_sql   .= ' ' . $start_point . ',';
			$limit_sql   .= ' ' . $perpage;

			$sortingOrder = " ORDER BY $orderby $order ";

			$data = $wpdb->get_results( "$sql_select $join  WHERE  $where_sql $sortingOrder  $limit_sql", 'ARRAY_A' );

			return $data;
		}//end getLogData

		/**
		 * Get total data count
		 *
		 * @param string $search
		 * @param int $form_id
		 * @param string $date_from
		 * @param string $date_to
		 * @param string $orderby
		 * @param string $order
		 * @param string $status
		 * @param string $date_range
		 * @param int $branch_id
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getLogDataCount( $search = '', $form_id = 0, $date_from = '', $date_to = '', $orderby = 'id', $order = 'asc', $status = 'all', $date_range = 'all', $branch_id = 0 ) {

			global $wpdb;

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
			$today_datetime_requested      = new DateTime( 'now', $date_time_zone);
			$today_date = $today_datetime_requested->format( 'Y-m-d' );

			$booking_table = $wpdb->prefix . "cbxrbooking_log_manager";

			$sql_select = "SELECT COUNT(*) FROM $booking_table as logs";

			$where_sql = '';

			$join = '';
			if ( $branch_id !== 0 ) {

				$postmeta_table = $wpdb->prefix . "postmeta"; // postmeta

				$join .= " JOIN $postmeta_table postmeta ON logs.form_id = postmeta.post_id";

				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
				$where_sql .= $wpdb->prepare( "postmeta.meta_key = %s AND postmeta.meta_value = %d", '_cbxrbookingformmeta_branch', $branch_id );
			}

			if ( $search != '' ) {
				if ( $where_sql != '' ) {
					$where_sql .= ' AND ';
				}
				$where_sql .= $wpdb->prepare( " name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR phone LIKE '%%%s%%' OR message LIKE '%%%s%%' OR secret LIKE '%%%s%%'", $search, $search, $search, $search, $search );
			}

			if ( $form_id !== 0 ) {
				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'form_id=%d', $form_id );
			}

			if ( $status != 'all' ) {
				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' ) . $wpdb->prepare( 'status=%s', $status );
			}

			if ( $date_range != 'all' ) {
				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );

				if ( $date_range === 'upcoming' ) {
					//$where_sql .= $wpdb->prepare( 'booking_date>%s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date > %s', $today_date );
				} elseif ( $date_range === 'today' ) {
					//$where_sql .= $wpdb->prepare( 'booking_date = %s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date = %s', $today_date );
				} elseif ( $date_range === 'previous' ) {
					//$where_sql .= $wpdb->prepare( 'booking_date < %s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date < %s', $today_date );
				} elseif ( $date_range === 'between_dates' && $date_from != '' && $date_to != '' ) {

					//$where_sql .= " CAST(logs.booking_date AS DATE) ";
					$where_sql .= " CAST(concat(logs.booking_date, ' ', logs.booking_time) as datetime) ";
					$where_sql .= $wpdb->prepare( "between %s AND %s ", $date_from, $date_to );
				}
			}


			if ( $where_sql == '' ) {
				$where_sql = '1';
			}


			$sortingOrder = " ORDER BY $orderby $order ";


			$count = $wpdb->get_var( "$sql_select $join  WHERE  $where_sql $sortingOrder" );

			return $count;
		}//end getLogDataCount


		/**
		 * Get the top links(Time based items) before the table listing
		 *
		 * @return array
		 */
		public static function get_range_views( $date_range = 'all', $cbxrblogfromDate = '', $cbxrblogtoDate = '' ) {
			$views = array();

			$date_range_url = remove_query_arg( array( 'status', 's' ) );

			//All link
			$class        = ( $date_range == 'all' ? ' class="current"' : '' );
			$all_url      = add_query_arg( 'date_range', 'all', $date_range_url );
			$views['all'] = "<a href='{$all_url }' {$class} >" . esc_html__( 'All', 'cbxrbooking' ) . "</a>";

			//Upcoming link
			$class             = ( $date_range == 'upcoming' ? ' class="current"' : '' );
			$link_url          = add_query_arg( 'date_range', 'upcoming', $date_range_url );
			$views['upcoming'] = "<a href='{$link_url }' {$class} >" . esc_html__( 'Upcoming', 'cbxrbooking' ) . "</a>";

			//Today link
			$class          = ( $date_range == 'today' ? ' class="current"' : '' );
			$link_url       = add_query_arg( 'date_range', 'today', $date_range_url );
			$views['today'] = "<a href='{$link_url }' {$class} >" . esc_html__( 'Today', 'cbxrbooking' ) . "</a>";

			//Previous link
			$class             = ( $date_range == 'previous' ? ' class="current"' : '' );
			$link_url          = add_query_arg( 'date_range', 'previous', $date_range_url );
			$views['previous'] = "<a href='{$link_url }' {$class} >" . esc_html__( 'Previous', 'cbxrbooking' ) . "</a>";

			//Between dates link
			$class    = ( $date_range == 'between_dates' ? ' class="cbx-between-dates-link current"' : ' class="cbx-between-dates-link"' );
			$link_url = add_query_arg( 'date_range', 'between_dates', $date_range_url );

			if ( $date_range == 'between_dates' && $cbxrblogfromDate != '' && $cbxrblogtoDate != '' ) {
				$link_url = add_query_arg( 'cbxrblogfromDate', $cbxrblogfromDate, $link_url );
				$link_url = add_query_arg( 'cbxrblogtoDate', $cbxrblogtoDate, $link_url );

				$views['between_dates'] = "<a href='{$link_url }' {$class} >" . esc_html__( 'Between Dates', 'cbxrbooking' ) . ' (' . $cbxrblogfromDate . esc_html__( ' - ', 'cbxrbooking' ) . $cbxrblogtoDate . ')' . "</a>";
			} else {
				$views['between_dates'] = "<a href='{$link_url }' {$class} >" . esc_html__( 'Between Dates', 'cbxrbooking' ) . "</a>";
			}

			return $views;
		}//end get_range_views


		/**
		 * Get the top links(Status based items based on time filter) before the table listing
		 *
		 * @return array
		 */
		public static function get_views_status() {
			$views = array();

			$date_range = ( isset( $_REQUEST['date_range'] ) ? $_REQUEST['date_range'] : 'all' );
			$current    = ( isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all' );

			$status_arr = self::getAllBookingStatus();

			$count_by_status = self::getCountByStatus();

			$change_url = remove_query_arg( array( 's' ) );

			//All link
			$class        = ( $current == 'all' ? ' class="current"' : '' );
			$all_url      = add_query_arg( 'status', 'all', $change_url );
			$views['all'] = "<a href='{$all_url }' {$class} >" . sprintf( __( 'All (%d)', 'cbxrbooking' ), $count_by_status['total'] ) . "</a>";

			foreach ( $status_arr as $key => $value ) {
				$url           = add_query_arg( 'status', $key, $change_url );
				$class         = ( $current == $key ? ' class="current"' : '' );
				$views[ $key ] = "<a href='{$url}' {$class} >" . sprintf( __( $value . ' (%d)', 'cbxrbooking' ), $count_by_status[ $key ] ) . "</a>";
			}

			return $views;
		}//end get_views_status


		/**
		 * Return booking count respectively status
		 *
		 * @return array
		 */
		public static function getCountByStatus() {

			global $wpdb;

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

			$date_time_zone           = new DateTimeZone( $tzstring );
			$today_datetime_requested = new DateTime( 'now', $date_time_zone );
			$today_date               = $today_datetime_requested->format( 'Y-m-d' );

			$booking_table = $wpdb->prefix . "cbxrbooking_log_manager";

			$where_sql = '';

			$join = '';
			if ( isset( $_REQUEST['branch_id'] ) && intval( $_REQUEST['branch_id'] ) > 0 ) {

				$postmeta_table = $wpdb->prefix . "postmeta"; // postmeta

				$join .= " JOIN $postmeta_table postmeta ON bt.form_id = postmeta.post_id";

				$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
				$where_sql .= $wpdb->prepare( "postmeta.meta_key = %s AND postmeta.meta_value = %d", '_cbxrbookingformmeta_branch', intval( $_REQUEST['branch_id'] ) );
			}


			if ( isset( $_REQUEST['form_id'] ) && intval( $_REQUEST['form_id'] ) > 0 ) {
				if ( $where_sql != '' ) {
					$where_sql .= ' AND ';
				}
				$where_sql .= $wpdb->prepare( 'bt.form_id=%d', intval( $_REQUEST['form_id'] ) );
			}

			$date_range = ( isset( $_REQUEST['date_range'] ) ? $_REQUEST['date_range'] : 'all' );

			$search = ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
			if ( $search != '' ) {
				if ( $where_sql != '' ) {
					$where_sql .= ' AND ';
				}
				$where_sql .= $wpdb->prepare( " name LIKE '%%%s%%' OR email LIKE '%%%s%%' OR phone LIKE '%%%s%%' OR message LIKE '%%%s%%'", $search, $search, $search, $search );
			}


			$date_from = isset( $_REQUEST['cbxrblogfromDate'] ) ? $_REQUEST['cbxrblogfromDate'] : ''; //date from
			$date_to   = isset( $_REQUEST['cbxrblogtoDate'] ) ? $_REQUEST['cbxrblogtoDate'] : ''; //date end

			if ( $date_range !== '' ) {
				if ( $date_range === 'upcoming' ) {
					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
					//$where_sql .= $wpdb->prepare( 'booking_date>%s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date > %s', $today_date);
				} elseif ( $date_range === 'today' ) {
					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
					//$where_sql .= $wpdb->prepare( 'booking_date=%s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date = %s', $today_date);
				} elseif ( $date_range === 'previous' ) {
					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );

					//$where_sql .= $wpdb->prepare( 'booking_date<%s', date( "Y-m-d" ) );
					$where_sql .= $wpdb->prepare( 'booking_date < %s', $today_date );

				} elseif ( $date_range === 'between_dates' && $date_from != '' && $date_to != '' ) {
					$where_sql .= ( ( $where_sql != '' ) ? ' AND ' : '' );
					//$where_sql .= " CAST(bt.booking_date AS DATE) ";
					$where_sql .= " CAST(concat(bt.booking_date, ' ', bt.booking_time) as datetime) ";
					$where_sql .= $wpdb->prepare( "between %s AND %s ", $date_from, $date_to );
				}
			}

			if ( $where_sql === '' ) {
				$where_sql = '1';
			}


			$sql_select = "SELECT booking_date, status, COUNT(*) as status_counts FROM $booking_table as bt $join WHERE  $where_sql GROUP BY status";

			$results = $wpdb->get_results( "$sql_select", 'ARRAY_A' );

			$total           = 0;
			$count_by_status = self::getAllBookingStatus();
			foreach ( $count_by_status as $key => $value ) {
				$count_by_status[ $key ] = 0;
			}
			$count_by_status['total'] = $total;


			if ( $results != null ) {
				foreach ( $results as $result ) {
					$total                                += intval( $result['status_counts'] );
					$count_by_status[ $result['status'] ] = $result['status_counts'];
				}
				$count_by_status['total'] = $total;
			}

			return $count_by_status;
		}//end getCountByStatus

		/**
		 * am pm hour format
		 *
		 * @param string $timestamp
		 *
		 * @return false|string
		 */
		public static function ampmTimeFormat( $timestamp = '' ) {
			return date( 'h:ia', strtotime( $timestamp ) );
		}//end ampmTimeFormat


		/**
		 * form setting time format
		 *
		 * @param string $timestamp
		 *
		 * @return false|string
		 */
		public static function twelveHourBookingTimeFormat( $post_id = 0, $booking_time ) {
			$twelve_hour_booking_time = null;

			$post_id = intval( $post_id );
			if ( $booking_time != '' ) {
				if ( $post_id > 0 ) {
					$cbxrbooking_meta = get_post_meta( $post_id, '_cbxrbookingmeta', true );
					if ( is_array( $cbxrbooking_meta ) && sizeof( $cbxrbooking_meta ) > 0 ) {
						if ( isset( $cbxrbooking_meta['settings']['cbxrbooking_booking_schedule']['time_format'] ) ) {
							$time_format = intval( $cbxrbooking_meta['settings']['cbxrbooking_booking_schedule']['time_format'] );
							if ( $time_format == 12 ) {
								$twelve_hour_booking_time = self::ampmTimeFormat( $booking_time );
							}
						}
					}
				} else {
					$cbxrbooking_global_setting = get_option( 'cbxrbooking_global', null );
					if ( ! is_null( $cbxrbooking_global_setting ) ) {
						if ( isset( $cbxrbooking_global_setting['time_format'] ) ) {
							$time_format = intval( $cbxrbooking_global_setting['time_format'] );
							if ( $time_format == 12 ) {
								$twelve_hour_booking_time = self::ampmTimeFormat( $booking_time );
							}
						}
					}
				}
			}

			return $twelve_hour_booking_time;
		}//end twelveHourBookingTimeFormat

		/**
		 * Get min party size dropdown
		 *
		 * @return type
		 */
		public static function get_party_sizes() {
			$min_party_size = [];
			for ( $i = 1; $i <= 100; $i ++ ) {
				$min_party_size[ $i ] = $i;
			}

			$form_styles = $min_party_size;

			return apply_filters( 'cbxrbooking_minpartysize', $min_party_size );
		}

		/**
		 * Get the default state dropdown
		 *
		 * @return type
		 */
		public static function get_default_states() {
			$booking_states = CBXRBookingHelper::getAllBookingStatus();
			unset( $booking_states['unverified'] );
			unset( $booking_states['canceled'] );
			unset( $booking_states['waiting'] );
			unset( $booking_states['seated'] );
			unset( $booking_states['departed'] );
			unset( $booking_states['closed'] );

			return apply_filters( 'cbxrbooking_defaultstate', $booking_states );
		}//end get_default_states

		/**
		 * Checking date validity
		 *
		 * @param  string $date
		 * @param string  $format
		 *
		 * @return bool
		 */
		public static function validateDate( $date, $format = 'Y-m-d H:i:s' ) {
			$d = DateTime::createFromFormat( $format, $date );

			return $d && $d->format( $format ) == $date;
		}//end validateDate

		/**
		 * Get the early bookings dropdown
		 *
		 * @return type
		 */
		public static function get_early_bookings() {
			$early_bookings = array(
				''   => esc_html__( 'Any Time', 'cbxrbooking' ),
				'1'  => esc_html__( 'From 1 day in advance', 'cbxrbooking' ),
				'7'  => esc_html__( 'From 1 week in advance', 'cbxrbooking' ),
				'14' => esc_html__( 'From 2 weeks in advance', 'cbxrbooking' ),
				'30' => esc_html__( 'From 30 days in advance', 'cbxrbooking' ),
				'90' => esc_html__( 'From 90 days in advance', 'cbxrbooking' ),
			);

			return apply_filters( 'cbxrbooking_earlybookings', $early_bookings );
		}//end get_early_bookings

		/**
		 * Get early booking message by selected value
		 *
		 * @param string $value
		 *
		 * @return string
		 */
		public static function getEarlyBookingByValue( $value = '' ) {

			if ( $value == '' ) {
				return '';
			}

			$early_bookings = CBXRBookingHelper::get_early_bookings();

			if ( isset( $early_bookings[ $value ] ) ) {
				return $early_bookings[ $value ];
			} else {
				return '';
			}
		}//end getEarlyBookingByValue

		/**
		 * Get the late bookings dropdown
		 *
		 * @return type
		 */
		public static function get_late_bookings() {
			$late_bookings = array(
				''         => esc_html__( 'Up to the last minute', 'cbxrbooking' ),
				'15'       => esc_html__( 'At least 15 minutes in advance', 'cbxrbooking' ),
				'30'       => esc_html__( 'At least 30 minutes in advance', 'cbxrbooking' ),
				'45'       => esc_html__( 'At least 45 minutes in advance', 'cbxrbooking' ),
				'60'       => esc_html__( 'At least 1 hour in advance', 'cbxrbooking' ),
				'240'      => esc_html__( 'At least 4 hours in advance', 'cbxrbooking' ),
				'1440'     => esc_html__( 'At least 24 hours in advance', 'cbxrbooking' ),
				'same_day' => esc_html__( 'Block same-day bookings', 'cbxrbooking' ),
			);

			return apply_filters( 'cbxrbooking_latebookings', $late_bookings );
		}//end get_late_bookings

		/**
		 * Get late booking message by selected value
		 *
		 * @param string $value
		 *
		 * @return string
		 */
		public static function getLateBookingByValue( $value = '' ) {

			if ( $value == '' ) {
				return '';
			}

			$late_bookings = CBXRBookingHelper::get_late_bookings();

			if ( isset( $late_bookings[ $value ] ) ) {
				return $late_bookings[ $value ];
			} else {
				return '';
			}
		}//end getLateBookingByValue

		/**
		 * Get the time dropdown
		 *
		 * @return type
		 */
		public static function get_time_interval() {
			$booking_time_interval = array(
				'30' => esc_html__( 'Every 30 minutes', 'cbxrbooking' ),
				'15' => esc_html__( 'Every 15 minutes', 'cbxrbooking' ),
				'10' => esc_html__( 'Every 10 minutes', 'cbxrbooking' ),
				'5'  => esc_html__( 'Every 5 minutes', 'cbxrbooking' ),
			);

			return apply_filters( 'cbxrbooking_gettimeinterval', $booking_time_interval );
		}

		/**
		 * Get the week starts on dropdown
		 *
		 * @return array
		 */
		public static function get_week_starts_on() {
			$booking_week_starts_on = array(
				'0' => __( 'Sunday' ),
				'1' => __( 'Monday' ),
				'2' => __( 'Tuesday' ),
				'3' => __( 'Wednesday' ),
				'4' => __( 'Thursday' ),
				'5' => __( 'Friday' ),
				'6' => __( 'Saturday' ),
			);

			return $booking_week_starts_on;
		}//end get_week_starts_on

		/**
		 * Get the early bookings cancel dropdown
		 *
		 * @return mixed|void
		 */
		public static function get_early_cancels() {
			$booking_cancel = array(
				''         => esc_html__( 'Any Time', 'cbxrbooking' ),
				'15'       => esc_html__( 'At least 15 minutes in advance', 'cbxrbooking' ),
				'30'       => esc_html__( 'At least 30 minutes in advance', 'cbxrbooking' ),
				'45'       => esc_html__( 'At least 45 minutes in advance', 'cbxrbooking' ),
				'60'       => esc_html__( 'At least 1 hour in advance', 'cbxrbooking' ),
				'240'      => esc_html__( 'At least 4 hours in advance', 'cbxrbooking' ),
				'1440'     => esc_html__( 'At least 24 hours in advance', 'cbxrbooking' ),
				'same_day' => esc_html__( 'Block same-day Cancel', 'cbxrbooking' ),
			);

			return apply_filters( 'cbxrbooking_cancelbookings', $booking_cancel );
		}//end get_early_cancels

		/**
		 * Setup a post object and store the original loop item so we can reset it later
		 *
		 * @param obj $post_to_setup The post that we want to use from our custom loop
		 */
		public static function setup_postdata( $post_to_setup ) {

			//only on the admin side
			if ( is_admin() ) {

				//get the post for both setup_postdata() and to be cached
				global $post;

				//only cache $post the first time through the loop
				if ( ! isset( $GLOBALS['post_cache'] ) ) {
					$GLOBALS['post_cache'] = $post;
				}

				//setup the post data as usual
				$post = $post_to_setup;
				setup_postdata( $post );
			}
			else{
				setup_postdata( $post_to_setup );
			}
		}//end setup_postdata


		/**
		 * Reset $post back to the original item
		 *
		 */
		public static function wp_reset_postdata() {

			//only on the admin and if post_cache is set
			if ( is_admin() && ! empty( $GLOBALS['post_cache'] ) ) {

				//globalize post as usual
				global $post;

				//set $post back to the cached version and set it up
				$post = $GLOBALS['post_cache'];
				setup_postdata( $post );

				//cleanup
				unset( $GLOBALS['post_cache'] );
			}
			else{
				wp_reset_postdata();
			}
		}//end wp_reset_postdata

		/**
		 * Create tables as need
		 */
		public static function create_table(){
			global $wpdb;

			//old name
			$table_booking_logs_old   = $wpdb->prefix . 'rbookinglogs';
			$table_branch_manager_old = $wpdb->prefix . 'branch_manager';


			//new name
			$table_booking_logs   = $wpdb->prefix . 'cbxrbooking_log_manager';
			$table_branch_manager = $wpdb->prefix . 'cbxrbooking_branch_manager';


			//start name process
			//rename log table
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_booking_logs_old'" ) === $table_booking_logs_old ) {
				//Your code here
				$wpdb->query( "RENAME TABLE $table_booking_logs_old TO $table_booking_logs" );
			}

			//rename branch manager table
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_branch_manager_old'" ) === $table_branch_manager_old ) {
				//Your code here
				$wpdb->query( "RENAME TABLE $table_branch_manager_old TO $table_branch_manager" );
			}
			//end rename process


			//db table migration if exists
			$charset_collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) ) {
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty( $wpdb->collate ) ) {
					$charset_collate .= " COLLATE $wpdb->collate";
				}
			}


			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			//create rbookinglog table
			$table_rbookings_sql = "CREATE TABLE $table_booking_logs (
                          id bigint(11) unsigned NOT NULL AUTO_INCREMENT,
                          form_id bigint(11) unsigned NOT NULL DEFAULT 0 COMMENT 'booking form id of custom post type',
                          name varchar(255) NOT NULL DEFAULT '' COMMENT 'client name',
                          email varchar(100) NOT NULL DEFAULT '' COMMENT 'client email',
                          phone varchar(25) DEFAULT '' COMMENT 'client phone number',
                          booking_date DATE NOT NULL COMMENT 'client preferred booking date',
                          booking_time TIME NOT NULL COMMENT 'client preferred booking time',
                          party_size mediumint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'client preferred party size',
                          message text DEFAULT '' COMMENT 'Client Message',
                          user_ip varchar(45) NOT NULL,
                          add_by bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key of user table. who added this, if uest zero',
                          mod_by bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key of user table. who last modify this list',
                          add_date datetime DEFAULT NULL COMMENT 'add date',
                          mod_date datetime DEFAULT NULL COMMENT 'last modified date',
                          status VARCHAR (255) NOT NULL DEFAULT 'pending',
                          secret VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'booking secret code',                          
                          activation VARCHAR(255) DEFAULT NULL COMMENT 'activation code',                          
                          metadata text DEFAULT '' COMMENT 'any extra information needed information',                          
                          PRIMARY KEY  (id)
                        ) $charset_collate; ";


			$table_cbxrb_branch_manager_sql = "CREATE TABLE $table_branch_manager (
                          id int(11) unsigned NOT NULL AUTO_INCREMENT,
                          name varchar(200) NOT NULL COMMENT 'branch manager name',
                          description varchar(200) DEFAULT NULL COMMENT 'branch manager description',
                          address text DEFAULT NULL COMMENT 'branch manager address',
                          add_by bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key of user table. who add this branch manager',
                          mod_by bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key of user table. who modified this branch manager',
                          add_date datetime DEFAULT NULL COMMENT 'created date',
                          mod_date datetime DEFAULT NULL COMMENT 'modified date',
                          PRIMARY KEY  (id)
                        ) $charset_collate; ";

			dbDelta( array( $table_rbookings_sql, $table_cbxrb_branch_manager_sql ) );
		}
		
		
		/**
		 * Admin page slugs
		 *
		 * @return mixed|void
		 */
		public static function admin_page_slugs(){
			$slugs = array('cbxrbookingbranchmanager','cbxrbookinglogs', 'cbxrbookingsettings', 'cbxrbookingaddons');
			
			return apply_filters('cbxrbooking_admin_page_slugs', $slugs);
		}//end admin_page_slugs
		
		
		/**
		 * Is gutenberg edit page
		 *
		 * @return bool
		 */
		public static function is_gutenberg_page() {
			//if(!is_admin()) return false;
			if ( function_exists( 'is_gutenberg_page' ) &&
			     is_gutenberg_page()
			) {
				// The Gutenberg plugin is on.
				return true;
			}
			
			$current_screen = get_current_screen();
			if ( method_exists( $current_screen, 'is_block_editor' ) &&    $current_screen->is_block_editor()) {
				// Gutenberg page on 5+.
				return true;
			}
			return false;
		}//end is_gutenberg_page
		
	}//end class CBXRBookingHelper