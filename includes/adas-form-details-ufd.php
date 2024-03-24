<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Adas_Form_Details_Ufd
 *
 * This class handles the display of details for a submitted form.
 * It retrieves the form data from the database and renders an HTML page
 * to show the submitted form values, submission date, and other relevant information.
 */
class Adas_Form_Details_Ufd {


	/**
	 * The ID of the form this record is for.
	 *
	 * @var int
	 */
	private $form_id;

	/**
	 * The ID of the form post this record is associated with.
	 *
	 * @var string
	 */
	private $form_post_id;

	private $adasclientemail;
	/**
	 * Adas_Form_Details_Ufd constructor.
	 *
	 * Initializes the class and calls the form_details_page() method.
	 */
	public function __construct() {
		$this->init();
		$this->form_details_page();
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * Verifies the nonce (security token) and retrieves the form ID and submission ID
	 * from the URL parameters. If the nonce is invalid or the required parameters
	 * are missing, the script exits with an error message.
	 */
	public function init() {
		// Verify the nonce.
		$view_nonce          = isset( $_GET['view_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['view_nonce'] ) ) : '';
		$view_nonce_verified = isset( $_GET['view_nonce'] ) ? wp_verify_nonce( $view_nonce, 'view_action' ) : false;

		// Verify the nonce and retrieve the form ID and submission ID.
		if ( isset( $_GET['fid'] ) && $view_nonce_verified ) {
			$this->form_post_id = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';
			$this->form_id      = isset( $_GET['ufid'] ) ? (int) $_GET['ufid'] : '';
		} else {
			wp_die( 'No action taken' );
		}
	}

	/**
	 * Retrieves the submitted form values for the given form ID.
	 *
	 * @param string $formid The ID of the form to retrieve the values for.
	 * @return array|null An array containing the form values and other relevant information, or null if no results are found.
	 */
	public function retrieve_form_values( $formid = '' ) {
		global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}divi_table WHERE contact_form_id = %s AND id = %d ORDER BY date_submitted DESC LIMIT 1",
				$formid,
				$this->form_id
			)
		);

		if ( ! $results ) {

			return null;
		}

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

	/**
	 * Renders the form details page.
	 *
	 * Retrieves the form data from the database, formats it, and outputs an HTML structure
	 * to display the form details, including the form ID, submission date, read status,
	 * and the submitted form values.
	 */
	public function form_details_page() {
		global $wpdb;

		$result         = $this->retrieve_form_values( $this->form_post_id );
		$results        = $result['data'];
		$form_data      = $result['data'];
		$form_id        = $result['contact_form_id'];
		$read_status    = $result['read_status'];
		$read_status    = ( '1' === $read_status ) ? 'Read' : 'Not Read';
		$date_submitted = $result['date_submitted'];

		if ( empty( $results ) ) {
			wp_die( 'Not valid contact form' );
		}

		// Output the HTML structure to display the form details.
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

			// Check if the key or value is empty.
			if ( '' === $key || '' === $data ) {
				continue;
			}

			// If the value is an array, extract the 'value' key or the array itself.
			if ( is_array( $data ) ) {
				$data = $data['value'] ?? $data;
			}

			if ( filter_var( $data, FILTER_VALIDATE_EMAIL ) ) {
				$this->adasclientemail = $data;
			}

			// If the value is an array again, implode it into a comma-separated string with newlines between each value, and then convert it to a formatted string.
			if ( is_array( $data ) ) {
				$key_val      = ucfirst( $key );
				$arr_str_data = implode( ', ', $data );
				$arr_str_data = nl2br( $arr_str_data );
			} else {
				// Otherwise, just convert the value to a formatted string.
				$key_val = ucfirst( $key );
				$data    = nl2br( $data );
			}

				// If it is not, display the value as a regular string.
				echo '<p><b>' . esc_html( $key_val ) . '</b>: ' . esc_html( $data ) . '</p>';

		endforeach;

		// Add a button to send an email to the client.
		if ( isset( $this->adasclientemail ) ) {
			echo '<a href="mailto:' . esc_attr( $this->adasclientemail ) . '">
                    <button style="margin-top: 1em;color: white; border: none; padding: 0.5em 1.5em; cursor:pointer; white; background-color: #6a6ae8;" type="button">Reply to email</button>
                  </a>';
		}

		$form_data = serialize( $form_data );
		$form_id   = $result['contact_form_id'];

		// Update the read_status and read_date for the current submission.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}divi_table SET read_status = %s, read_date = NOW() WHERE id = %d",
				'1',
				$this->form_id
			)
		);

		if ( false === $result ) {
			return;
		}

		echo '</div></div></div></div>';
	}
}
