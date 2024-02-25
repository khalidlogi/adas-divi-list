<?php
/**
 * WP List Table Example admin page view
 *
 * @package   WPListTableExample
 * @author    Matt van Andel
 * @copyright 2016 Matthew van Andel
 * @license   GPL-2.0+
 */

?>

<div class="wrap">
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
    </h1>

    <div style="background:#ececec;border:1px solid #ccc;padding:0 10px;margin-top:5px;border-radius:5px;">
        <p>
            Contact forms entries created using the Divi builder are stored in the database('divi_table').
        </p>
    </div>

    <!-- Forms must be manually created to enable features like bulk actions, requiring the table to be wrapped within one. -->
    <form id="entries" method="get">
        <!-- ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- render the list table -->
        <?php $test_list_table->display() ?>
    </form>

</div>