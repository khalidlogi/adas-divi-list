<?php

defined( 'ABSPATH' ) || exit;

class Adas_Divi_KHwidget {

	public function __construct() {

		add_action( 'wp_dashboard_setup', array( $this, 'register_adas_table_dashboard_widget' ) );
		// AJAX handler to update the option value.
		add_action( 'wp_ajax_update_data_saving_option', array( $this, 'update_data_saving_option' ) );
	}


	/**
	 * Register widget
	 */
	public function register_adas_table_dashboard_widget() {

		wp_add_dashboard_widget(
			'my_adas_table_dashboard_widget',
			'Adas Divi Add-on',
			array( $this, 'adas_dashboard_widget_display' )
		);
	}


	/**
	 * Update data saving option value
	 */
	public function update_data_saving_option() {

		if ( ! empty( $_POST['my_form_nonce'] ) && isset( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['my_form_nonce'] )
			&& isset( $_POST['my_form_nonce'] ) && isset( $_POST['value_data_ischecked'] ) ) {

			$nonce                = sanitize_text_field( wp_unslash( $_POST['my_form_nonce'] ) );
			$value_data_ischecked = sanitize_text_field( wp_unslash( $_POST['value_data_ischecked'] ) );

			if ( $_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce( $nonce, 'update_data_saving_option' )
			) {
				if ( current_user_can( 'manage_options' ) ) {
					if ( isset( $_POST['value_data_ischecked'] ) ) {
						update_option( 'Enable_data_saving_checkbox', $value_data_ischecked );
					}
				}
				wp_die();
			}
		}
	}


	/**
	 * Diplay informations
	 */
	public function adas_dashboard_widget_display() {

		global $wpdb;
		// Output the nonce field
		$nonce = wp_create_nonce( 'update_data_saving_option' );

		$table_name = $wpdb->prefix . 'divi_table';

		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ); //phpcs:ignore

		if ( ! $table_exists ) {
			$message = __( 'Adas database data does not exist. Please try reactivating the plugin', 'adasdividb' );
			sprintf( '<br><div>%s</div>', esc_html( $message ) );
		} else {

			?>

<form class="dashboard-widget-control-form">
	<label class="switch">
		<input 
			<?php
			if ( get_option( 'Enable_data_saving_checkbox' ) !== '1' ) {
				echo 'checked';
			}
			?>
			type="checkbox" id="switch_button_data_saving">
		<span class="slider round"></span>
	</label><strong> Activate/Deactivate Data saving </strong>
	<input type="hidden" name="my_form_nonce" value="<?php echo esc_attr( $nonce ); ?>">
</form>

<br>

<script>
jQuery(document).ready(function($) {

	// Event listener for the switch button change
	$('#switch_button_data_saving').change(function() {
		UpdateDataOptionValue();
	});


	function UpdateDataOptionValue() {
		var checkboxValue = $('#switch_button_data_saving').prop('checked') ? 'null' : '1';
		var nonceValue = $('input[name="my_form_nonce"]').val();
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'update_data_saving_option',
				value_data_ischecked: checkboxValue,
				my_form_nonce: nonceValue // Include the nonce value in the AJAX request
			},
			success: function(response) {
				console.log('Data option value updated successfully.');
			},
			error: function(error) {
				console.error('Error updating data option value.');
			}
		});
	}
});
</script>

			<?php
			// Display dates and data.
			$this->get_form_counts_and_recent_dates();
			$this->adas_custom_widget_display();

		}
	}


	/**
	 * Retrieve dates
	 */
	public function get_form_counts_and_recent_dates() {
		global $wpdb;

		// Get the form IDs and count the number of forms for each form ID.
		//phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		$distinct_form_ids = $wpdb->get_results( "SELECT DISTINCT contact_form_id FROM {$wpdb->prefix}divi_table" ); //phpcs:ignore

		if ( ! empty( $distinct_form_ids ) ) { //phpcs:ignore
			$form_counts = array();

			foreach ( $distinct_form_ids as $form ) {
				$form_id = sanitize_text_field( $form->contact_form_id );

				$count_results = $wpdb->get_results( //phpcs:ignore
					$wpdb->prepare(
						"SELECT COUNT(*) AS count FROM {$wpdb->prefix}divi_table WHERE contact_form_id IN (%s)",
						$form_id
					)
				);

				$count_row = $count_results[0];

				$form_counts[ $form_id ] = $count_row->count;
			}

			echo '<br>';
			// Print the number of forms for each form ID.
			foreach ( $form_counts as $form_id => $count ) {
				$form_id_label         = __( 'Form ID:', 'adasdividb' );
				$number_of_forms_label = __( 'Number of forms:', 'adasdividb' );

				printf(
					'<strong>%s</strong> %s, <strong>%s</strong> %s<br>',
					esc_attr( $form_id_label ),
					esc_html( $form_id ),
					esc_attr( $number_of_forms_label ),
					esc_html( $count )
				);
			}

			// Get the date of last submissions
			$last_submissions_label = __( 'Last three submissions', 'adasdividb' );
			printf( '<br><h3><strong>%s</strong></h3>', esc_html( $last_submissions_label ) );
			$last_three_dates = Class_Divi_KHdb::getInstance()->get_last_three_dates();
			foreach ( $last_three_dates as $date ) {
				echo esc_attr( $date ) . '<br>';
			}
		}
	}


	/**
	 * Show last entry
	 */
	public function adas_custom_widget_display() {

		global $wpdb;

		$results = $wpdb->get_results( //phpcs:ignore
			"SELECT id, form_values, contact_form_id, date_submitted
        FROM {$wpdb->prefix}divi_table
        ORDER BY id DESC
        LIMIT 1"
		);

		if ( ! $results ) {

			if ( class_divi_KHdb::getInstance()->is_table_empty() === true ) {
				$message    = __( 'Add entries to your form and try again.', 'adasdividb' );
				$link_label = __( 'Settings DB', 'adasdividb' );
				$link_url   = admin_url( 'admin.php?page=khdiviwplist.php' );

				printf(
					'<br><div style="text-align: center; color: red;">%s <a style="text-align: center; color: black;" href="%s">%s</a></div>',
					esc_html( $message ),
					esc_url( $link_url ),
					esc_html( $link_label )
				);
			}
		} else {
			foreach ( $results as $result ) {
				$date            = $result->date_submitted;
				$serialized_data = sanitize_text_field( $result->form_values );
				$form_id         = sanitize_text_field( $result->contact_form_id );
				$id              = intval( $result->id );

				// Unserialize the serialized form value
				$unserialized_data = unserialize( $serialized_data );
				$form_values[]     = array(
					'contact_form_id' => $form_id,
					'id'              => $id,
					'date'            => $date,
					'data'            => $unserialized_data,
					'fields'          => $unserialized_data,
				);

				// Display the data.
				$recent_record_label = __( 'Last record: ', 'adasdividb' );
				printf( '<br><h3><strong>%s</strong></h3>', esc_html( $recent_record_label ) );

				foreach ( $form_values as $form_value ) {
					$id_label   = __( 'ID:', 'adasdividb' );
					$date_label = __( 'Date:', 'adasdividb' );
					printf( '<strong>%s</strong> %s<br>', esc_html( $id_label ), esc_html( $form_value['id'] ) );
					printf( '<strong>%s</strong> %s<br>', esc_html( $date_label ), esc_html( $form_value['date'] ) );

					// Access and display the unserialized data.
					foreach ( $form_value['data'] as $key => $value ) {
						if ( empty( $value ) ) {
							continue;
						}
						if ( is_array( $value ) ) {
							if ( array_key_exists( 'value', $value ) ) {
								$value = sanitize_text_field( $value['value'] );
							}
						}

						printf( '<strong>%s</strong> : %s <br>', esc_html( $key ), esc_html( $value ) );

					}

					echo '<br>';
				}
			}
		}
	}
}

new Adas_Divi_KHwidget();
