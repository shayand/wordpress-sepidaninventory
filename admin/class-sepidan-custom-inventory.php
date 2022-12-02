<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Sepidan_Custom_Inventory extends WP_List_Table{
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'sepidaninventory',
            'plural'   => 'sepidaninventories',
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_title($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=sepidaninventories_form&id=%s">%s</a>', $item['id'], __('Edit', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['title'],
            $this->row_actions($actions)
        );
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
            'title'      => __('Title', 'wpbc'),
        );
        return $columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = 'sepidan_inventory';

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'title'      => array('title', true),
        );
        return $sortable_columns;
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = 'sepidan_inventory';

        $per_page = 10;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    public function inventory_entry($id = null)
    {
        global $wpdb;
        $table_name = 'sepidan_inventory';

        $title = '';
        $page_title = 'Add New Inventory';
        $query_string = '?page=sepidaninventories_form';

        if($id != null){
            $singleItem = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name where id = %d", $id), ARRAY_A);
            $title = $singleItem['title'];
            $page_title = 'Modify Inventory';
            $query_string .= '&id=' . $id;
        }

        $ret = '<div class="wrap"><div class="form-wrap">
            <h2>' . $page_title .'</h2>
            <form id="addtag" method="post" action="'. $query_string .'">
            <div class="form-field form-required term-name-wrap">
                <label for="tag-name">Title</label>
                <input name="title" id="tag-name" type="text" value="'.$title.'" size="40" aria-required="true">
                <p>The title of inventory.</p>
            </div>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="' . $page_title . '">		<span class="spinner"></span>
            </p>
            </form>
            </div>
        </div>';

        return $ret;
    }

    public function inventory_entry_post_action($id = null)
    {
        global $wpdb;
        $table_name = 'sepidan_inventory';
        if($id != null){
            $query_text = sprintf("UPDATE $table_name SET title = '%s' WHERE id = '%s'",$_POST['title'],$id);
        }else{
            $query_text = sprintf("INSERT INTO $table_name (title) values ('%s')",$_POST['title']);
        }
        $wpdb->query($query_text);
        return true;
    }
}