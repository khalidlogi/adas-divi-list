<?php

/**
 * Adas_Divi_Shortcode
 *
 * Class to display the shortcode in the front end.
 */
class Adas_Divi_Shortcode {
	/**
	 * Holds the form ID.
	 *
	 * @var string
	 */
	private $formbyid;

	/**
	 * Number of items to display per page.
	 *
	 * @var string
	 */
	private $items_per_page;

	/**
	 * Total count of forms.
	 *
	 * @var string
	 */
	private $form_count;

	/**
	 * Color for label elements.
	 *
	 * @var string
	 */
	private $khdivi_label_color;

	/**
	 * Color for text elements.
	 *
	 * @var string
	 */
	private $khdivi_text_color;

	/**
	 * Background color for export elements.
	 *
	 * @var string
	 */
	private $khdivi_exportbg_color;

	/**
	 * Background color for elements.
	 *
	 * @var string
	 */
	private $khdivi_bg_color;

	public function __construct() {

		// Get the form id.
		$this->formbyid = sanitize_text_field( Class_Divi_KHdb::getInstance()->retrieve_form_id() );
		// Get forms count.
		$this->form_count = sanitize_text_field( Class_Divi_KHdb::getInstance()->count_items( $this->formbyid ) );

		$this->khdivi_label_color    = get_option( 'khdivi_label_color', '#bfa1a1' );
		$this->khdivi_text_color     = get_option( 'khdivi_text_color', null );
		$this->khdivi_exportbg_color = get_option( 'khdivi_exportbg_color', '#408c4f' );
		$this->khdivi_bg_color       = get_option( 'khdivi_bg_color', '#f8f7f7' );
		$this->items_per_page        = get_option( 'items_per_page', 10 );

		add_action( 'init', array( &$this, 'init' ) );
	}

	public function init() {

		add_shortcode( 'adas', array( &$this, 'display_form_values_shortcode_table' ) );
	}


	/**
	 * Display the formatted value based on the key
	 */
	public function display_value( $value, $key ) {

		if ( strtoupper( $key ) === 'ADMIN_NOTE' ) {
			printf( '<span class="value" style="color: red; font-weight:bold;"> %s</span>', esc_html( strtoupper( $value ) ) );
		} elseif ( filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			printf( '<a style="color:%s;" class="adaslink" href="mailto:%s"> %s</a>', esc_attr( $this->khdivi_text_color ), esc_html( $value ), esc_html( $value ) );
		} elseif ( is_numeric( $value ) ) {
			printf( '<a style="color:%s;" class="adaslink" href="https://wa.me/%s"> %s</a>', esc_attr( $this->khdivi_text_color ), esc_html( $value ), esc_html( $value ) );
		} else {
			printf( '<span style="color:%s;" class="value"> %s</span>', esc_attr( $this->khdivi_text_color ), esc_html( stripslashes( $value ) ) );
		}
	}


	/**
	 * Display the form values as a shortcode table
	 */
	public function display_form_values_shortcode_table( $atts ) {

		$is_divi_active = Class_Divi_KHdb::getInstance()->is_divi_active();
		$is_table_exist = Class_Divi_KHdb::getInstance()->check_divi_table_existence();

		// Check if the table exists.
		if ( ! $is_table_exist ) {
			return sprintf(
				'<br><div style="text-align: center; color: red;">%s</div>',
				esc_html__( 'An error occurred while accessing the Adas plugin data.', 'adasdividb' )
			);
		}

		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			ob_start();
			// Show nothing.
			echo '';
			return ob_get_clean();

		} else {
			// Get  form id.
			if ( ! empty( $atts['id'] ) ) {
				$formbyid = $atts['id'];
			} else {
				$formbyid = $this->formbyid;
			}

			// Check if there is at least one entry.
			if ( Class_Divi_KHdb::getInstance()->is_table_empty() === true ) {
				ob_start();
				$message   = __( 'No data available! Please add entries to your form and try again.', 'adasdividb' );
				$link_text = __( 'Settings DB', 'adasdividb' );
				$link_url  = esc_url( admin_url( 'admin.php?page=khdiviwplist.php' ) );

				printf(
					'<div style="text-align: center; color: red;">%s <a style="text-align: center; color: black;" href="%s">%s</a></div>',
					esc_html( $message ),
					esc_url( $link_url ),
					esc_html( $link_text )
				);

				$output_buffer = ob_get_clean();
				return $output_buffer;
			} else {
				$current_page = max( 1, get_query_var( 'paged' ) );
				$offset       = ( $current_page - 1 ) * (int) $this->items_per_page;
				if ( 0 !== (int) $this->items_per_page ) {
					$total_pages = ceil( $this->form_count / (int) $this->items_per_page );
				}

				$form_values = Class_Divi_KHdb::getInstance()->retrieve_form_values( $this->formbyid, $offset, $this->items_per_page, '' );
				ob_start();
				// Include edit-form file.
				include_once KHFORM_PATH . '../Inc/html/edit_popup.php';
				echo '
                <div class="form-wraper">';
				if ( ! $is_divi_active ) {
					$message = __( 'Divi Theme is not ACTIVE', 'adasdividb' );
					printf(
						'<div style="color:red;"><i class="fas fa-exclamation-circle"></i> %s</div>',
						esc_html( $message )
					);
				}

				$update_form_id = __( 'To update the form ID value', 'adasdividb' );
				$settings_page  = __( 'Settings Page', 'adasdividb' );
				$visit          = __( 'Visit the', 'adasdividb' );
				$link_url       = admin_url( 'admin.php?page=khdiviwplist.php' );

				printf(
					esc_html( $visit ) . ' <a href="%s">' . esc_html( $settings_page ) . '</a> ' . esc_html( $update_form_id ) . '.<br>',
					esc_url( $link_url )
				);

				if ( $form_values ) {
					$count = sanitize_text_field( Class_Divi_KHdb::getInstance()->count_items( $formbyid ) );
					printf( '<p>' . esc_html__( 'Number of forms submitted:', 'adasdividb' ) . ' %s</p>', esc_html( $count ) );

					echo '<div class="form-data-container">';

					foreach ( $form_values as $form_value ) {
						$form_id = sanitize_text_field( $form_value['contact_form_id'] );
						$form_id = preg_replace( '/\D/', '', $form_id );
						$id      = intval( $form_value['id'] );
						$date    = $form_value['date'];

						// Delete button.
						echo '<div class="form-set-container" style="background:' . esc_attr( $this->khdivi_bg_color ) . ';"
                        data-id="' . esc_attr( $id ) . '">';

						echo '<button class="delete-btn" data-form-id="' . esc_attr( $id ) . '"
                        data-nonce="' . esc_attr( wp_create_nonce( 'ajax-nonce' ) ) . '">
                        <i class="fas fa-trash"></i></button>';

						// Edit button.
						echo '<button class="edit-btn delete-btn2" data-form-id="' . esc_attr( $form_id ) . '"
                        data-id="' . esc_attr( $id ) . '"
						data-edit_value_nonce="' . esc_attr( wp_create_nonce( 'edit_value_nonce' ) ) . '">
						<i class="fas fa-edit"></i></button>';

						echo '<div class="form-id-container">';

						$id_label = __( 'ID', 'adasdividb' );
						$id_text  = ( $id );

						printf(
							'<div class="form-id-label id"><span style="color:%s;"> %s </span>: <span style="color:%s;"> %s </span></div>',
							esc_attr( $this->khdivi_label_color ),
							esc_html( $id_label ),
							esc_attr( $this->khdivi_text_color ),
							esc_html( $id_text )
						);

						// Form ID.
						$form_id_label = __( 'Form ID:', 'adasdividb' );
						printf(
							'<span style="color:%s;" class="form-id-label">%s</span>',
							esc_attr( $this->khdivi_label_color ),
							esc_html( $form_id_label )
						);

						printf(
							'<span style="color:%s;" class="form-id-value">%s</span></div>',
							esc_attr( $this->khdivi_text_color ),
							esc_html( $form_id )
						);

						$date_label = __( 'Date:', 'adasdividb' );
						$date_text  = $date;
						printf(
							'<div id="datakey" style="color:%s;"><span class="field-label"> %s</span><span style="color:%s;" class="value"> %s</span></div>',
							esc_attr( $this->khdivi_label_color ),
							esc_html( $date_label ),
							esc_attr( $this->khdivi_text_color ),
							esc_html( $date_text )
						);

						// Key values data.
						foreach ( $form_value['data'] as $key => $value ) {

							if ( empty( $value ) ) {
								continue;
							}

							echo '<div class="form-data-container">';

							$key_label = $key;
							printf(
								'<span class="field-label" style="color:%s;">%s:</span>',
								esc_attr( $this->khdivi_label_color ),
								esc_html( $key_label )
							);

							// Check is  key is an array.
							if ( is_array( $value ) ) {
								if ( array_key_exists( 'value', $value ) ) {
									$this->display_value( $value['value'], $key );
								} else {
									foreach ( $value as $val ) {
										$this->display_value( $val, $key );
									}
								}
							} else {
								$this->display_value( $value, $key );
							}

							echo '</div>';
						}

						echo '</div>';
					}
				}
				echo '<div class="pagination-links">';

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped.
				echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XSS OK.
					array(
						'base'      => esc_url( add_query_arg( 'paged', '%#%' ) ),
						'format'    => '',
						'prev_text' => ( '&laquo; Previous' ),
						'next_text' => ( 'Next; &raquo' ),
						'total'     => $total_pages,
						'current'   => $current_page,
					)
				);
				echo '</div>';

				printf(
					'<div class="adassharebutton"><button style="background:%s;" class="export-btn">%s</button>',
					esc_attr( $this->khdivi_exportbg_color ),
					esc_html__( 'Export as CSV', 'adasdividb' )
				);
				printf(
					'<button style="background:%s;" class="export-btn export-btn-pdf">%s</button></div>',
					esc_attr( $this->khdivi_exportbg_color ),
					esc_html__( 'Export as PDF', 'adasdividb' )
				);

				return ob_get_clean();

			}
		}
	}
}
