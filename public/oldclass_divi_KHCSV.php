<?php


if ( ! class_exists( 'class_divi_KHCSV' ) ) {

	class class_divi_KHCSV {

		private $myselectedformid;
		public function __construct() {

			error_log( 'class csv called' );
			$this->myselectedformid = sanitize_text_field( Class_Divi_KHdb::getInstance()->retrieve_form_id() );
			if ( Class_Divi_KHdb::getInstance()->is_table_empty() !== true ) {
				add_action( 'wp_ajax_export_form_data', array( $this, 'export_form_data' ) );
				add_action( 'wp_ajax_nopriv_export_form_data', array( $this, 'export_form_data' ) );
			}
		}

		public function sanitizeValue( $value ) {

			// Trim whitespaces.
			$value = trim( $value );

			// Strip tags.
			$value = wp_strip_all_tags( $value );

			// Escape special characters
			$value = esc_sql( $value );

			$maxLength = 355;

			if ( strlen( $value ) > $maxLength ) {
				$value     = substr( $value, 0, $maxLength );
					$value = $value . '...';
			}

				return $value;
		}

			/**
			 * Callback function for CSV export
			 */
		public function export_form_data() {

			global $wpdb;
			$this->myselectedformid = class_divi_KHdb::getInstance()->retrieve_form_id();

			// Retrieve the form values from the database
			$form_values = Class_Divi_KHdb::getInstance()->retrieve_form_values_pdf( $this->myselectedformid );

			if ( empty( $form_values ) ) {
				wp_send_json_error( __( 'No entries found for the selected form.', 'adasdividb' ), 404 );
			}

			printf(
				"%s, %s, Field, Value\n",
				esc_html( __( 'ID', 'adasdividb' ) ),
				esc_html( __( 'Form ID', 'adasdividb' ) )
			);

			if ( $form_values ) {
				foreach ( $form_values as $form_value ) {
					$form_id = sanitize_text_field( $form_value['contact_form_id'] );
					$id      = intval( $form_value['id'] );

					foreach ( $form_value['data'] as $key => $value ) {
						$id = $form_value['id'];
						if ( is_array( $value ) ) {
							if ( array_key_exists( 'value', $value ) ) {
								$value = $this->sanitizeValue( $value['value'] );
							} else {
								$value = $this->sanitizeValue( $value );
							}
						}

						if ( is_array( $value ) && array_key_exists( 'value', $value ) ) {
							$value = str_replace( ',', '\,', $value );
							$value = str_replace( '"', '\"', $value );
						}

						// Add row to CSV table.
						$csv_table .= sprintf( "%d, %s, \"%s\", \"%s\"\n", intval( $id ), esc_html( $form_id ), esc_html( $key ), esc_html( $value ) );
					}
				}
			}

			// Set the response headers for downloading.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( sprintf( 'Content-Disposition: attachment; filename="DIVI-Contact-Entries-%s.csv";charset=utf-8', gmdate( 'Y-m-d' ) ) );

			// Output the CSV table.
			echo wp_kses_post( $csv_table );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			wp_die();
		}
	}
}

new class_divi_KHCSV();
