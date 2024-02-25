<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 */
class ADAS_Form_Details_Ufd {

	private $form_id;
	private $form_post_id;
	protected $table_name;


	public function __construct() {

		global $wpdb;
		$this->form_post_id = sanitize_text_field( $_GET['fid'] );
		$this->form_id      = (int) $_GET['ufid'];
		// table name
		$this->table_name = $wpdb->prefix . 'divi_table';
		$this->form_details_page();
	}

	/**
	 * Retrieves the submitted form values for the given form ID.
	 */
	public function retrieve_form_values( $formid = '' ) {

		global $wpdb;
		$formid  = $formid;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table_name}  WHERE contact_form_id = %s AND id = %d ORDER BY date_submitted DESC LIMIT 1 ",
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

				// Unserialize the serialized form value
				$unserialized_data = unserialize( $serialized_data );
				error_log( 'serialized_data: ' . print_r( $unserialized_data, true ) );
				error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
				$form_values = array(
					'contact_form_id' => $form_id,
					'id'              => $id,
					'date'            => $date,
					'data'            => $unserialized_data,
				);
			}
			return $form_values;
		}
	}

	public function form_details_page() {
		global $wpdb;

		$result  = $this->retrieve_form_values( $this->form_post_id );
		$results = $result['data'];

		error_log( 'results: ' . print_r( $results, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

		if ( empty( $results ) ) {
			wp_die( 'Not valid contact form' );
		}
		?>
<div class="wrap">
    <div id="welcome-panel" class="cfdb7-panel">
        <div class="cfdb7-panel-content">
            <div class="welcome-panel-column-container">
                <h3> Form ID:
                    <?php echo esc_html( $this->form_post_id ); ?>
                </h3>
                <p></span>
                    <?php echo esc_html( $result['date'] ); ?>
                </p>
                <?php
				if ( ( $results ) ) {
							$form_data = ( $results );
				}

				foreach ( $form_data as $key => $data ) :

					if ( $key == ' ' ) {
						continue;
					}

					if ( is_array( $data ) ) {
						if ( array_key_exists( 'value', $data ) ) {
							$data = $data['value'];
						} else {
							$data = $data;
						}
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
						"UPDATE $wpdb->prefix.'divi_table' SET read_status = %s, read_date = NOW() WHERE id = %d",
						'1',
						$this->form_id
					)
				);

				if ( $result === false ) {
					// An error occurred, log the error
					$error_message = $wpdb->last_error;
					error_log( "Database error: $error_message" );
				} else {
					// Query executed successfully, and $result contains the number of affected rows
					if ( $result > 0 ) {
						// Rows were updated
						error_log( "Updated $result rows successfully." );
					} else {
						// No rows were updated
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