<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for database operations.
 */
class Class_Divi_KHdb {


	/**
	 * Form ID
	 *
	 * @var string
	 */
	private $formid;

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Class instance
	 *
	 * @var self
	 */
	private static $instance;


	/**
	 * Constructor for the class.
	 * Initializes the table name using WordPress database prefix and 'divi_table'.
	 * If the table is not empty, it retrieves the form ID.
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name = $wpdb->prefix . 'divi_table';
		if ( $this->is_table_empty() !== false ) {
			$this->formid = $this->retrieve_form_id();
		}
	}


	/**
	 * Check if the Divi Theme is active.
	 */
	public function is_divi_active() {
		// See if the Divi Theme is active.
		if ( 'Divi' === wp_get_theme()->get( 'Name' ) ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Count the number of items in the database table.
	 *
	 *  @param string|null $formid (Optional) The ID of the form.
	 */
	public function count_items( $formid = null ) {

		global $wpdb;
		$formid = $formid ? $formid : $this->formid;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching		
		// Check if table exists.
		$table_name = $wpdb->prefix . 'divi_table';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			return;
		}
		if ( self::getInstance()->is_table_empty() === true ) {
			return;
		}

		if ( empty( $formid ) ) {
			// Select all rows.
			$items_count = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(DISTINCT id) FROM %s',
					$this->table_name
				)
			);

		} else {
			// Formid is an array.
			if ( strpos( $formid, ',' ) !== false ) {
				$formid = str_replace( ' ', '', $formid );
				$formid = explode( ',', $formid ); // Split the string into an array of ID.

				$placeholders = array_fill( 0, count( $formid ), '%d' );
				$placeholders = implode( ', ', $placeholders );

				$items_count = $wpdb->get_var(
					$wpdb->prepare(
						// Phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
						"SELECT COUNT(DISTINCT id) FROM {$wpdb->prefix}divi_table WHERE contact_form_id IN ($placeholders)",
						$formid
					)
				);
			} else {
				$items_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(DISTINCT id) FROM {$wpdb->prefix}divi_table WHERE contact_form_id = %s",
						$formid
					)
				);
			}
		} // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching		

		return $items_count;
	}


	/**
	 * Function to retrieve form id from Database.
	 */
	public function retrieve_form_id() {

		global $wpdb;
		// Check if the table exists.
		// phpcs:ignore 
		if ( $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$this->table_name
			)
		) !== $this->table_name
		) {
			return;
		}
		// phpcs:enable 

		if ( $this->is_table_empty() === true ) {
			$divi_form_id = 0;
		}

		$divi_formid = maybe_unserialize( get_option( 'divi_form_id_setting' ) );

		if ( empty( $divi_formid ) ) {
		    // phpcs:disable 
			$results = $wpdb->get_results( "SELECT DISTINCT contact_form_id FROM {$wpdb->prefix}divi_table" );
		    // phpcs:enable 
			if ( $results === false ) {
				$divi_form_id = null;
				exit();
			}
			if ( ! empty( $results ) ) {
				// Create an array to store the number of forms for each form ID.
				$form_id = array();
				foreach ( $results as $row ) {
					$form_id[] = $row->contact_form_id;
				}
				$divi_form_id = implode( ' , ', $form_id );
			}
		} elseif ( is_array( $divi_formid ) ) {
			if ( count( $divi_formid ) === 1 ) {
				$divi_form_id = $divi_formid[0];
			} else {
				$divi_form_id = implode( ', ', $divi_formid );
			}
		}

		return $divi_form_id;
	}



	/**
	 * Function to get last three dates.
	 */
	public static function get_last_three_dates() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching		
		$results = $wpdb->get_results( "SELECT DISTINCT date_submitted FROM {$wpdb->prefix}divi_table ORDER BY date_submitted DESC LIMIT 3" );

		$dates = array();
		foreach ( $results as $result ) {
			$dates[] = $result->date_submitted;
		}
		return $dates;
	}

	/**
	 * Function to check if there is table exist.
	 *
	 * @return bool
	 */
	public function check_divi_table_existence() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'divi_table';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching		
		$result = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( $result === $table_name ) {
			// Table exists.
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Function to check if there is no data in a database table.
	 *
	 * @return bool True if the table is empty, false if it has data.
	 */
	public function is_table_empty() {
		global $wpdb;

		// Check if the table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching		
		if ( $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$this->table_name
			)
		) !== $this->table_name
		) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching		
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}divi_table" );

		if ( '0' === $count ) {
			return true;
		}
			return false;
	}


	/**
	 * Function to retrieve form values for pdf export.
	 */
	public function retrieve_form_values_pdf( $formid = '' ) {

		global $wpdb;
		// Phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}divi_table ORDER BY date_submitted DESC" );

		if ( ! $results ) {
			return;
		} else {
			foreach ( $results as $result ) {
				$date            = sanitize_text_field( $result->date_submitted );
				$serialized_data = sanitize_text_field( $result->form_values );
				$form_id         = sanitize_text_field( $result->contact_form_id );
				$id              = absint( $result->id );

				// Unserialize the serialized form value
				$unserialized_data = unserialize( $serialized_data );
				$form_values[]     = array(
					'contact_form_id' => $form_id,
					'id'              => $id,
					'date'            => $date,
					'data'            => $unserialized_data,
					'fields'          => $unserialized_data,
				);
			}
			return $form_values;
		}
	}


	/**
	 * Function to retrieve all data from the custom table.
	 */
	public function retrieve_form_values( $formid = '', $offset = '', $items_per = '', $LIMIT = '' ) {

		global $wpdb;
		if (
			$wpdb->get_var(// Phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					'SHOW TABLES LIKE %s',
					$this->table_name
				)
			) !== $this->table_name
		) {
			// Table does not exist, exit the function.
			return;
		}

		if ( ! empty( $formid ) ) {
			$formid = $formid;
		} else {
			$formid = $this->formid;
		}

		if ( ! empty( $items_per ) ) {
			$items_per = $items_per;
		}

		if ( $formid !== null ) {
			$formids = explode( ',', $formid );
		}

		$formids             = array_map( 'trim', $formids );
		$formid_placeholders = implode( ',', array_fill( 0, count( $formids ), '%s' ) ); // create placeholders for each id.

		if ( ! empty( $formid ) ) {

				$results = $wpdb->get_results(// phpcs:disable	
					$wpdb->prepare(	
						"SELECT DISTINCT id, form_values, contact_form_id, date_submitted FROM {$wpdb->prefix}divi_table WHERE contact_form_id IN ($formid_placeholders) LIMIT %d, %d",
						array_merge( $formids, array( $offset, $items_per ) )
					)
				);// phpcs:enable	
		}

		if ( ! $results ) {
			return;
		} else {
			foreach ( $results as $result ) {
				$date            = sanitize_text_field( $result->date_submitted );
				$serialized_data = sanitize_text_field( $result->form_values );
				$form_id         = sanitize_text_field( $result->contact_form_id );
				$date            = $result->date_submitted;
				$id              = absint( $result->id );

				// Unserialize the serialized form value
				$unserialized_data = unserialize( $serialized_data );
				$form_values[]     = array(
					'contact_form_id' => $form_id,
					'id'              => $id,
					'date'            => $date,
					'data'            => $unserialized_data,
					'fields'          => $unserialized_data,
				);

			}
			return $form_values;
		}
	}


	/**
	 * Static method to get the instance of the class
	 */
	public static function getInstance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

Class_Divi_KHdb::getInstance();
