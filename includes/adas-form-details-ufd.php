<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 */
class ADAS_Form_Details_Ufd {

	/**
	 * The ID of the form this record is for
	 *
	 * @var int
	 */
	private $form_id;

	/**
	 * The ID of the form this record is associated with
	 *
	 * @var string
	 */
	private $form_post_id;


	public function __construct() {

		$this->init();

		$this->form_details_page();
	}

	public function init() {

		// Verify the nonce.
		$view_nonce          = isset( $_GET['view_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['view_nonce'] ) ) : '';
		$view_nonce_verified = isset( $_GET['view_nonce'] ) ? wp_verify_nonce( $view_nonce, 'view_action' ) : false;

		// Verify the nonce.
		if ( isset( $_GET['fid'] ) && $view_nonce_verified ) {
			$this->form_post_id = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';
			$this->form_id      = isset( $_GET['ufid'] ) ? (int) $_GET['ufid'] : '';
		} else {
			wp_die( 'No action taken' );
		}
	}


	/**
	 * Retrieves the submitted form values for the given form ID.
	 */
	public function retrieve_form_values( $formid = '' ) {

		global $wpdb;
		$formid  = $formid;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}divi_table  WHERE contact_form_id = %s AND id = %d ORDER BY date_submitted DESC LIMIT 1 ",
				$formid,
				$this->form_id
			),
		);

		if ( ! $results ) {
			error_log( 'Database error: ' );
		} else {
			foreach ( $results as $result ) {
				$date            = sanitize_text_field( $result->date_submitted );
				$serialized_data = ( $result->form_values );
				$form_id         = sanitize_text_field( $result->contact_form_id );
				$id              = absint( $result->id );

				// Unserialize the serialized form value.
				$unserialized_data = unserialize( $serialized_data );

				$form_values = array(
					'contact_form_id' => $form_id,
					'id'              => $id,
					'read_status'     => $result->read_status,
					'date_submitted'  => $result->date_submitted,
					'date'            => $date,
					'data'            => $unserialized_data,
				);
			}
			return $form_values;
		}
	}


	public function form_details_page() {
		global $wpdb;

		$result         = $this->retrieve_form_values( $this->form_post_id );
		$results        = $result['data'];
		$form_data      = $result['data'];
		$form_id        = $result['contact_form_id'];
		$read_status    = $result['read_status'];
		$read_status    = ( $read_status === '1' ) ? 'Read' : 'Not Read';
		$date_submitted = $result['date_submitted'];

		if ( empty( $results ) ) {
			wp_die( 'Not valid contact form' );
		}

		echo '<style>
            .adas-form-details-wrap {' .
				'font-size: 16px;' .
			'}' .
			'.form-information span {' .
				'margin-left: 1em;' .
			'}</style>' .
		'<div class="adas-form-details-wrapper">' .
			'<div id="welcome-panel" class="cfdb7-panel">' .
				'<div class="cfdb7-panel-content">' .
					'<div class="welcome-panel-column-container">' .
						'<h3> Form ID: <span id="form-id">' . esc_html( $form_id ) . '</span></h3>' .
						'<p><b>Submission Date:</b> ' . esc_html( $date_submitted ) . '</p>' .
						'<p><b>Read Status:</b> ' . esc_html( $read_status ) . '</p>';

		if ( ( $results ) ) {
					$form_data = ( $results );
		}

		foreach ( $form_data as $key => $data ) :

			if ( $key == ' ' ) {
				continue;
			}

			if ( is_array( $data ) ) {
				$data = $data['value'] ?? $data;
			}

			if ( is_array( $data ) ) {
				$key_val      = ucfirst( $key );
				$arr_str_data = implode( ', ', $data );
				$arr_str_data = nl2br( $arr_str_data );
				echo '<p><b>' . esc_html( $key_val ) . '</b>: ' . esc_html( $arr_str_data ) . '</p>';
			} else {

				$key_val = ucfirst( $key );
				$data    = nl2br( $data );
				echo '<p><b>' . esc_html( $key_val ) . '</b>: ' . esc_html( $data ) . '</p>';
			}

						endforeach;

						$form_data = serialize( $form_data );
						$form_id   = $result['contact_form_id'];

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}divi_table SET read_status = %s, read_date = NOW() WHERE id = %d",
						'1',
						$this->form_id
					)
				);

		if ( $result === false ) {
			// An error occurred, log the error.
			$error_message = $wpdb->last_error;
			error_log( "Database error: $error_message" );
		} else {
			// Query executed successfully, and $result contains the number of affected rows.
			if ( $result > 0 ) {
				// Rows were updated
				error_log( "Updated $result rows successfully." );
			} else {
				// No rows were updated.
				error_log( 'No rows were updated.' );
			}
		}

		?>
</div>
</div>
</div>
</div>
<?php
	}
}