<?php

/**
 * WPFormsDB Admin subpage
 */


// details of the form id

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class Adas_form_details {


	private $form_id;

	/**
	 * Constructor start subpage
	 */
	public function __construct() {
		$this->form_id = isset( $_REQUEST['fid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['fid'] ) ) : '';
		$this->adas_table_page();
	}

	function adas_table_page() {
		$ListTable = new ADASDB_Wp_Sub_Page();
		$ListTable->prepare_items();
		?>
<div class="wrap">
    <div id="icon-users" class="icon32"></div>
    <h2>Contact form ID:
        <?php echo esc_html( $this->form_id ); ?>
    </h2>
    <form method="post" action="">


        <?php $ListTable->display(); ?>
    </form>
</div>
<?php
	}
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * WPFormsDB_Wp_List_Table class will create the page to load the table
 */
class ADASDB_Wp_Sub_Page extends WP_List_Table {


	/**
	 * WP List Table Example class
	 *
	 * @package   WPListTableExample
	 * @author    Matt van Andel
	 * @copyright 2016 Matthew van Andel
	 * @license   GPL-2.0+
	 */

	/**
	 * Example List Table Child Class
	 *
	 * Create a new list table package that extends the core WP_List_Table class.
	 * WP_List_Table contains most of the framework for genepage_name the table, but we
	 * need to define and override some methods so that our data can be displayed
	 * exactly the way we need it to be.
	 *
	 * To display this example on a page, you will first need to instantiate the class,
	 * then call $yourInstance->prepare_items() to handle any data manipulation, then
	 * finally call $yourInstance->display() to render the table to the page.
	 *
	 * Our topic for this list table is going to be movies.
	 *
	 * @package WPListTableExample
	 * @author  Matt van Andel
	 */


	private $per_page = 2;
	private $form_id;

	/**
	 * ***********************************************************************
	 * Normally we would be querying data from a database and manipulating that
	 * for use in your list table. For this example, we're going to simplify it
	 * slightly and create a pre-built array. Think of this as the data that might
	 * be returned by $wpdb->query()
	 *
	 * In a real-world scenario, you would run your own custom query inside
	 * the prepare_items() method in this class.
	 *
	 * @var array
	 * ************************************************************************
	 */
	// protected $example_data = array();

	/**
	 * TT_Example_List_Table constructor.
	 *
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 */

	public function __construct() {

		$this->form_id = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';

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
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'page_id'
	 *
	 * REQUIRED! This method dictates the table's columns and page_ids. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's page_id text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a `column_cb()` method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information.
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />', // Render a checkbox instead of text.
			'id'             => _x( 'id', 'Column label', 'wp-list-adas' ),
			'page_id'        => _x( 'page_id', 'Column label', 'wp-list-adas' ),
			'page_name'      => _x( 'page_name', 'Column label', 'wp-list-adas' ),
			'page_url'       => _x( 'page_url', 'Column label', 'wp-list-adas' ),
			'date_submitted' => _x( 'date_submitted', 'Column label', 'wp-list-adas' ),
			'read_status'    => _x( 'Read Status', 'Column label', 'wp-list-adas' ),
		);

		return $columns;
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within `prepare_items()` and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'id'             => array( 'id', false ),
			'date_submitted' => array( 'date_submitted', false ),
			'read_status'    => array( 'read_status', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Get default column value.
	 *
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'page_id', it would first see if a method named $this->column_page_id()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_page_id() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'page_id'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param object $item        A singular item (one full row's worth of data).
	 * @param string $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */

	// PS Here you should add all the columns you want to diplay values for
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'read_status':
				$read_status = $item['read_status'];

				// Output the cell content as "Read" if read_status is 1, or "Unread" otherwise
				return ( $read_status == 1 ) ? 'Read' : 'Unread';

			case 'id':
			case 'page_id':
			case 'page_name':
			case 'page_url':
			case 'date_submitted':
			case 'contact_form_id':
				return $item[ $column_name ];
			default:
				// return print_r($item, true); // Show the whole array for troubleshooting purposes.
		}
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
		if ( isset( $item['id'] ) ) {
			return sprintf(
				'<input type="checkbox" name="id[]" value="%1$s"/>',
				$item['id']                // The value of the checkbox should be the record's ID.
			);
		}
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
		$page       = wp_unslash( $_REQUEST['page'] ); // WPCS: Input var ok.
		$view_nonce = wp_create_nonce( 'view_action' );

		// Build edit row action.
		$edit_query_args_v = array(
			'page'   => $page,
			'action' => 'edit',
			'ufid'   => $item['id'],
			'fid'    => $_REQUEST['fid'],

		);

		/*
		$actions['view'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url(wp_nonce_url(add_query_arg($edit_query_args_v, 'admin.php'), 'editentry_' . $item['id'])),
			_x('Details', 'List table row action', 'wp-list-adas')
		);*/

		// Build delete row action.
		$delete_query_args = array(
			'page'   => $page,
			'action' => 'view',
			'ufid'   => $item['id'],
			'fid'    => $_REQUEST['fid'],
		);

		$actions['view'] = sprintf(
			'<a href="%1$s&view_nonce=%2$s">%3$s</a>',
			esc_url( add_query_arg( $delete_query_args, 'admin.php' ) ),
			esc_attr( $view_nonce ),
			_x( 'Details', 'List table row action', 'wp-list-adas' )
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
	 * Get an associative array ( option_name => option_page_id ) with the list
	 * of bulk actions available on this table.
	 *
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible page_id'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions.
	 */
	protected function get_bulk_actions() {

		$actions = array(
			'delete' => __( 'Delete', 'text-domain' ),
		);

		// Add nonce to delete action
		$view_nonce         = wp_create_nonce( 'deletentry' );
		$actions['delete'] .= sprintf(
			'<input type="hidden" name="view_nonce" value="%s" />',
			esc_attr( $view_nonce )
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
		$form_id = $this->form_id;

		$table_name = $wpdb->prefix . 'divi_table';
		$view_nonce = isset( $_REQUEST['view_nonce'] ) ? $_REQUEST['view_nonce'] : '';

		if ( ! $this->current_action() ) {
			return;
		}

		if ( 'delete' === $this->current_action() ) {

			if ( ! wp_verify_nonce( $view_nonce, 'deletentry' ) ) {
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
	 * Prepares the list of items for displaying.
	 *
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here.
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
		$form_id      = $_REQUEST['fid'];
		$per_page     = 10;
		$columns      = $this->get_columns();
		$hidden       = array();
		$sortable     = $this->get_sortable_columns();
		$current_page = $this->get_pagenum();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		// 1st Method - Declaring $wpdb as global and using it to execute an SQL query statement that returns a PHP object

		$data = $this->entries_data( $current_page, $per_page );

		usort( $data, array( $this, 'usort_reorder' ) );

		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}divi_table WHERE contact_form_id = '$form_id'" );

		error_log( 'total_items: ' . print_r( $total_items, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

		/*
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to do that.
		 */
		// $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                   // WE have to calculate the total number of items.
				'per_page'    => ( $per_page ),                         // WE have to determine how many items to show on a page.
			)
		);

		error_log( 'total_items: ' . $total_items );
		error_log( 'per_page: ' . $per_page );
		error_log( 'total_pages: ' . ceil( $total_items / $this->per_page ) );
	}

	protected function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'read_status';
		$order   = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';

		switch ( $orderby ) {
			case 'read_status':
				$result = strcmp( $a['read_status'], $b['read_status'] );
				break;
			case 'id':
				$result = $a['id'] - $b['id'];
				break;
			// Add other column cases here if needed
			default:
				return 0; // Return 0 for no sorting
		}

		return ( $order === 'asc' ) ? $result : -$result;
	}

	public function entries_data( $page, $items_per_page ) {

		global $wpdb;
		$offset = ( intval( $page ) - 1 ) * intval( $items_per_page );

		global $wpdb;
		$results = array();
		$orderby = isset( $_GET['orderby'] ) ? 'date_submitted' : 'date_submitted';
		error_log( 'orderby: ' . print_r( $orderby, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		$order = isset( $_GET['order'] ) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
		error_log( 'order: ' . print_r( $order, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

		$form_id = $_REQUEST['fid'];

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}divi_table WHERE contact_form_id = %s ORDER BY %s %s LIMIT %d OFFSET %d",
				$form_id,
				$orderby,
				$order,
				$items_per_page,
				$offset
			),
			ARRAY_A
		);

		return $results;
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