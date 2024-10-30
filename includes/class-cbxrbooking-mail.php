<?php

	/**
	 * Class CBXRBookingMail
	 */
	class CBXRBookingMail {
		public $mail_format;
		//public $mail_from_address;
		//public $mail_from_name;

		public function __construct( $mail_format, $mail_from_address = '', $mail_from_name = '' ) {
			$this->mail_format       = $mail_format;
			//$this->mail_from_address = $mail_from_address;
			//$this->mail_from_name    = $mail_from_name;
		}


		/**
		 * Callback for 'wp_mail_from_name' filter
		 *
		 * @param type $name
		 *
		 * @return type
		 */
		public function filter_wp_mail_from_name( $name ) {
			return str_replace( '{sitename}', get_bloginfo( 'name' ), $this->mail_from_name );
		}

		/**
		 * Callback for 'wp_mail_from' filter
		 *
		 * @param type $content_type
		 *
		 * @return type
		 */
		public function filter_wp_mail_from( $content_type ) {
			return $this->mail_from_address;
		}

		/**
		 * Filter the format of the sending mail
		 *
		 * @param type $content_type
		 *
		 * @return string
		 */
		public function filter_mail_content_type( $content_type = 'text/plain' ) {
			if ( $this->mail_format == 'html' ) {
				return 'text/html';
			} elseif ( $this->mail_format == 'plain' ) {
				return 'text/plain';
			} elseif ( $this->mail_format == 'multipart' ) {
				return 'multipart/mixed';
			} else {
				return $content_type;
			}
		}

		/**
		 * Formats the header of the mail
		 *
		 * @param $cc
		 * @param $bcc
		 * @param $reply_to
		 *
		 * @return array
		 */
		public function email_header( $cc, $bcc, $reply_to = '' ) {
			$cc_array  = explode( ',', $cc );
			$bcc_array = explode( ',', $bcc );

			foreach ( $cc_array as $key => $cc ) {
				$headers[] = 'CC: ' . $cc;
			}

			foreach ( $bcc_array as $key => $bcc ) {
				$headers[] = 'BCC: ' . $bcc;
			}

			if ( $reply_to != '' ) {
				$headers[] = 'Reply-To: ' . $reply_to;
			}

			return $headers;
		}

		/**
		 * Run email related filters before sending email
		 */
		public function applyPreMailSendFilters() {
			//add_filter( 'wp_mail_from', array( $this, 'filter_wp_mail_from' ) );
			//add_filter( 'wp_mail_from_name', array( $this, 'filter_wp_mail_from_name' ) );
			add_filter( 'wp_mail_content_type', array( $this, 'filter_mail_content_type' ) );
		}

		/**
		 * Run email related filters after sending email
		 */
		public function applyPostMailSendFilters() {
			//remove_filter( 'wp_mail_from', array( $this, 'filter_wp_mail_from' ) );
			//remove_filter( 'wp_mail_from_name', array( $this, 'filter_wp_mail_from_name' ) );
			remove_filter( 'wp_mail_content_type', array( $this, 'filter_mail_content_type' ) );
		}

		/**
		 * sends email and return email send status
		 *
		 * @param string $to
		 * @param string $subject
		 * @param string $message
		 * @param array  $header
		 *
		 * @return bool
		 */
		public function wp_mail( $to = '', $subject = '', $message = '', $header = array() ) {
			//$message = wpautop( $message );

			$this->applyPreMailSendFilters();
			//$email_status = wp_mail( $to, $subject, wpautop( $message ), $header );
			$email_status = wp_mail( $to, $subject,  $message, $header );
			$this->applyPostMailSendFilters();

			return $email_status;
		}

	}//end class CBXRBookingMail