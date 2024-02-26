<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

error_log( 'adas-divi.php' );

add_action( 'admin_menu', 'tt_add_menu_items' );
/**
 * REGISTER THE EXAMPLE ADMIN PAGE
 *
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */
function tt_add_menu_items() {
	add_menu_page(
		__( 'Adas Divi Contact fotm DB List', 'wp-list-adas' ), // Page title.
		__( 'Adas Entries Manager', 'wp-list-adas' ),        // Menu title.
		'activate_plugins',                                         // Capability.
		'adas_list',                                             // Menu slug.
		'adas_render_list_page'                                       // Callback function.
	);
}

/**
 * CALLBACK TO RENDER THE EXAMPLE ADMIN PAGE
 *
 * This function renders the admin page and the example list table. Although it's
 * possible to call `prepare_items()` and `display()` from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function adas_render_list_page() {

	// Getting crasy with this shit
	// this function is the switch board that will call the correct class
	// based on the action parameter

	// See what page we are in right now.
	$fid  = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';
	$ufid = isset( $_GET['ufid'] ) ? (int) $_GET['ufid'] : '';

	if ( ! empty( $fid ) && empty( $ufid ) ) {
		new Adas_form_details();
		return;
	}

	if ( ! empty( $ufid ) && ! empty( $fid ) ) {

		new ADAS_Form_Details_Ufd();
		return;
	}

	// Create an instance of our package class.
	$test_list_table = new Adas_Main_List_Table();
	$test_list_table->prepare_items();

	// Include the view markup.
	include __DIR__ . '/views/page.php';
}




/**
 * Example List Table Child Class
 * Our topic for this list table is going to be movies.
 *
 * @package WPListTableExample
 * @author  Matt van Andel
 */

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Adas_Main_List_Table extends WP_List_Table {


	private $per_page = 10;
	/**
	 * ***********************************************************************
	 *
	 * @var array
	 * ************************************************************************
	 */

	public function __construct() {

		error_log( 'main class called' );

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'contact-form',     // Singular name of the listed records.
				'plural'   => 'contact-forms',    // Plural name of the listed records.
				'ajax'     => false,       // Does this table support ajax?
			)
		);
	}

	/**
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information.
	 */
	public function get_columns() {

		$columns = array(
			'name'  => __( 'Name', 'contact-form-WPFormsDB' ),
			'count' => __( 'Count', 'contact-form-WPFormsDB' ),
		);

		return $columns;
	}

	/**
	 * @return array An associative array containing all the columns that should be sortable.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'id'             => array( 'id', false ),
			'date_submitted' => array( 'date_submitted', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Get default column value.
	 *
	 * @param object $item        A singular item (one full row's worth of data).
	 * @param string $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */

	// PS Here you should add all the columns you want to diplay values for
	protected function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Get value for checkbox column.
	 *
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%1$s"/>',
			$item['id']                // The value of the checkbox should be the record's ID.
		);
	}

	/**
	 * Get page_id column value.
	 *
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'page_id'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_page_id} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links are
	 * secured with wp_nonce_url(), as an expected security measure.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 */

	// PS to add links to the column create a function with name column_(and the name of the column)
	protected function column_id( $item ) {

		$page         = wp_unslash( $_REQUEST['page'] );
		$delete_nonce = wp_create_nonce( 'deletentry' );

		// Build edit row action.
		$edit_query_args = array(
			'page'   => $page,
			'action' => 'edit',
			'id'     => $item['id'],

		);

		$actions['edit'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $edit_query_args, 'admin.php' ), 'editentry_' . $item['id'] ) ),
			_x( 'Edit', 'List table row action', 'wp-list-adas' )
		);

		// Build delete row action.
		$delete_query_args = array(
			'page'   => $page,
			'action' => 'delete',
			'id'     => $item['id'],
		);

		$actions['delete'] = sprintf(
			'<a href="%1$s&delete_nonce=%2$s">%3$s</a>',
			esc_url( add_query_arg( $delete_query_args, 'admin.php' ) ),
			esc_attr( $delete_nonce ),
			_x( 'Delete', 'List table row action', 'wp-list-adas' )
		);

		// Return the page_id contents.
		return sprintf(
			'%2$s <span style="color:silver;">entry</span>%3$s',
			$item['page_id'],
			$item['id'],
			$this->row_actions( $actions )
		);
	}

	/**
	 *
	 * @return array An associative array containing all the bulk actions.
	 */
	protected function get_bulk_actions() {

		$actions = array(
			'delete' => __( 'Delete', 'text-domain' ),
		);

		// Add nonce to delete action
		$delete_nonce       = wp_create_nonce( 'deletentry' );
		$actions['delete'] .= sprintf(
			'<input type="hidden" name="delete_nonce" value="%s" />',
			esc_attr( $delete_nonce )
		);

		return $actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 */
	protected function process_bulk_action() {
		global $wpdb;
		error_log( 'prepare_items: ' );
		$table_name   = $wpdb->prefix . 'divi_table';
		$delete_nonce = isset( $_REQUEST['delete_nonce'] ) ? $_REQUEST['delete_nonce'] : '';

		if ( ! $this->current_action() ) {
			return;
		}

		if ( 'delete' === $this->current_action() ) {

			if ( ! wp_verify_nonce( $delete_nonce, 'deletentry' ) ) {
				wp_die( 'No action taken2' );
				exit();
			}

			if ( is_array( $_REQUEST['id'] ) ) {
				print_r( $_REQUEST['id'] );
			}
			// $ids = $this->get_user_selected_records();
			$ids = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : array();
			if ( is_array( $ids ) ) {
				$ids = implode( ',', $ids );
			}

			if ( ! empty( $ids ) ) {
				$wpdb->query( "DELETE FROM $table_name WHERE id IN($ids)" );
			}
		}
	}

	/**
	 *
	 * @global wpdb $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 */
	function prepare_items() {
		global $wpdb; // This is used only if making any database queries

		/*
		 * First, lets decide how many records per page to show
		 */

		/*
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & page_ids), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		/*
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * three other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden );

		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		/*
		 * GET THE DATA!
		 *
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our dummy data.
		 *
		 * In a real-world situation, this is probably where you would want to
		 * make your actual database query. Likewise, you will probably want to
		 * use any posted sort or pagination data to build a custom query instead,
		 * as you'll then be able to use the returned query data immediately.
		 *
		 * For information on making queries in WordPress, see this Codex entry:
		 * http://codex.wordpress.org/Class_Reference/wpdb
		 */

		// 1st Method - Declaring $wpdb as global and using it to execute an SQL query statement that returns a PHP object

		$data = $this->entries_data();

		/*
		 * This checks for sorting input and sorts the data in our array of dummy
		 * data accordingly (using a custom usort_reorder() function). It's for
		 * example purposes only.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary. In other words: remove this when
		 * you implement your own query.
		 */
		// usort($data, array($this, 'usort_reorder'));

		/*
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		/*
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count( $data );

		/*
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to do that.
		 */
		$data = array_slice( $data, ( ( $current_page - 1 ) * $this->per_page ), $this->per_page );

		/*
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                   // WE have to calculate the total number of items.
				'per_page'    => ( $this->per_page ),                         // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $this->per_page ), // WE have to calculate the total number of pages.
			)
		);
	}

	public function entries_data() {
		global $wpdb;

		$title = 'title';

		$results = $wpdb->get_results( "SELECT DISTINCT contact_form_id FROM {$wpdb->prefix}divi_table", ARRAY_A );

		foreach ( $results as $result ) {
			$form_id = $result['contact_form_id'];
			error_log( 'form_post_id: ' . print_r( $form_id, true ) );
			error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

			// get the id of the form
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}divi_table WHERE contact_form_id = '$form_id'" );
			error_log( '$count: ' . print_r( $count, true ) );
			error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
			$title = $result['contact_form_id'];
			$link  = "<a class='row-title' href=admin.php?page=adas_list&fid=$form_id>%s</a>";

			$data_value['name']  = sprintf( $link, $title );
			$data_value['count'] = sprintf( $link, $count );
			$data[]              = $data_value;

		}

		return $data;
	}


	/**
	 * Callback to allow sorting of example data.
	 *
	 * @param string $a First value.
	 * @param string $b Second value.
	 *
	 * @return int
	 */
}