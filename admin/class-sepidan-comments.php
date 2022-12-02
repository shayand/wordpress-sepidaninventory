<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Sepidan_Comments extends WP_List_Table{
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'comment',
            'plural'   => 'comments',
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_product_id($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="?page=sepidan_comments_entry&product_id=%s">%s</a>', $item['product_id'], __('Edit', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['product_id'],
            $this->row_actions($actions)
        );
    }

    function column_user_id($item)
    {
        $userdata = WP_User::get_data_by( 'id', $item['user_id'] );
        return $userdata->user_email . ' (' . $userdata->user_login . ')';
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'product_id'      => __('Product', 'wpbc'),
            'user_id'      => __('User', 'wpbc'),
            'message'      => __('Comment', 'wpbc'),
            'created_at'      => __('Created At', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'created_at'      => array('created_at', true),
        );
        return $sortable_columns;
    }

    function prepare_items()
    {
        global $wpdb;

        if(!isset($_GET['product_id'])){
            wp_redirect('?page=sepidaninventories');
        }

        $product_id = $_GET['product_id'];

        $table_name = 'sepidan_inventory_products_message';

        $per_page = 50;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE product_id = $product_id");

        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE product_id = $product_id ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    public function form_entry($product_id = null)
    {
        global $wpdb;
        $table_name = 'sepidan_inventory_products_message';
        $brand_title = $product_id;
        $page_title = 'New Comment';
        $query_string = '?page=sepidan_comments_entry&product_id='.$product_id;

        $ret = '<div class="wrap"><div class="form-wrap">
            <h2>' . $page_title .'</h2>
            <form id="addtag" method="post" action="'. $query_string .'">
            <div class="form-field form-required term-name-wrap">
                <label for="tag-name">Vendor name</label>
                <input type="hidden" name="product_id" value="'.$product_id.'">
                <input name="brand_title" readonly="readonly" id="tag-name" type="text" value="'.$brand_title.'" size="40" aria-required="true">
                <p>The product name.</p>
            </div>
            <div class="form-field form-required term-name-wrap">
                <label for="commission">Comments</label>
                <textarea name="comment" id="commission" aria-required="true" rows="5"></textarea>
                <p>The comment about this product</p>
            </div>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="' . $page_title . '">		<span class="spinner"></span>
            </p>
            </form>
            </div>
        </div>';

        return $ret;
    }

    public function comment_entry_post_action($id = null)
    {
        global $wpdb;
        $table_name = 'sepidan_inventory_products_message';

        $query_text = sprintf("INSERT INTO $table_name (product_id , user_id , message, created_at) values ('%s','%s','%s',NOW())",$_GET['product_id'],get_current_user_id(),$_POST['comment']);
        $wpdb->query($query_text);
        return true;
    }
}