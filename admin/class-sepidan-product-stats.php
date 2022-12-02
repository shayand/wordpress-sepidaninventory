<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Sepidan_Product_Stats extends WP_List_Table{
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'Product Stats',
            'plural'   => 'Products Stats',
        ));
    }

    function _getStockStatus(){
        return [
            1 => 'in stock',
            2 => 'out of stock',
            3 => 'discontinue',
            4 => 'soon',
        ];
    }

    function _getInventoryOptions(){
        global $wpdb;
        $final = [];
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM sepidan_inventory"), ARRAY_A);
        foreach ($results as $result){
            $final[$result['id']] = $result['title'];
        }
        return $final;
    }

    function _getSingleInventory($id){
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM sepidan_inventory WHERE id = %d",$id), ARRAY_A);
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_post_id($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="?page=sepidan_product_stats_form&product_id=%s&inventory_id=%s">%s</a>', $item['post_id'],$item['inventory_id'], __('Edit', 'wpbc')),
        );
		
		$product_title = $this->_getProductName($item['post_id']);

        return sprintf('%s %s',
            $product_title,
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

    function column_price($item){
        return number_format($item['price'],0);
    }

    function column_stock_status($item){
        $var = $this->_getStockStatus();
        return $var[$item['stock_status']];
    }

    function column_inventory_id($item){
        $var = $this->_getSingleInventory($item['inventory_id']);
        return $var['title'];
    }
	

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'post_id'      => __('Product', 'wpbc'),
            'inventory_id'      => __('Inventory', 'wpbc'),
            'price'      => __('Price', 'wpbc'),
            'qty'      => __('Qty', 'wpbc'),
            'stock_status'      => __('Stock Status', 'wpbc'),
            'updated_at'      => __('Updated At', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'inventory_id'      => array('inventory_id', true),
            'post_id'      => array('post_id', true),
            'price'      => array('price', true),
            'qty'      => array('qty', true),
            'updated_at'      => array('updated_at', true),
        );
        return $sortable_columns;
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = 'sepidan_inventory_products';

        $per_page = 50;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

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
	
	public function _getProductName($product_id){
		global $wpdb;
        $single_product = $wpdb->get_row("SELECT * FROM `wp_posts` WHERE `ID` = $product_id", ARRAY_A);
        return $single_product['post_title'];
	}

    public function product_entry($product_id = null,$inventory_id =null)
    {
        global $wpdb;
        $table_name = 'sepidan_inventory_products';
        $query_string = '?page=sepidan_product_stats_form&product_id='.$product_id;
        $product_title = $this->_getProductName($product_id);

        $first_field = '<div class="form-field form-required term-name-wrap">
                <label for="tag-name">Prodcut name</label>
                <input type="hidden" name="post_id" value="'.$product_id.'">
                <input name="product_id" readonly="readonly" id="tag-name" type="text" value="'.$product_title.'" size="40" aria-required="true">
                <p>The product name.</p>
            </div>';

        if ($inventory_id != null){
            // edit scenario
            $page_title = 'Modify Product Stats';
            $query_string .= '&inventory_id=' . $inventory_id;
            $stats_entry = $wpdb->get_row("SELECT * FROM $table_name WHERE post_id = $product_id AND inventory_id = $inventory_id", ARRAY_A);
            $price = $stats_entry['price'];
            $qty = $stats_entry['qty'];
            $stock_status = $stats_entry['stock_status'];
            $inventoryDetails = $this->_getSingleInventory($inventory_id);

            $first_field .= '<div class="form-field form-required term-name-wrap">
                <label for="inventory_id">Inventory Name</label>
                <input type="hidden" name="inventory_id" value="'.$inventory_id.'">
                <input name="product_id" readonly="readonly" id="tag-name" type="text" value="'. $inventoryDetails['title'] .'" size="40" aria-required="true">
                <p>The product name.</p>
            </div>';
        } else {
            // add senario
            $page_title = 'Add Product Stats';
            $first_field .= '<div class="form-field form-required term-name-wrap">
                <label for="inventory_id">Inventory Name</label>
                <select name="inventory_id" id="inventory_id">';
            foreach ($this->_getInventoryOptions() as $key => $inventoryOption){
                $first_field .= '<option value="'. $key .'">'. $inventoryOption .'</option>';
            }
            $first_field .= '</select><p>The inventory name.</p>
            </div>';
            $price = 0;
            $qty = 0;
            $stock_status = 1;
        }

        $ret = '<div class="wrap"><div class="form-wrap">
            <h2>' . $page_title .'</h2>
            <form id="addtag" method="post" action="'. $query_string .'">
            '.$first_field.'
            <div class="form-field form-required term-name-wrap">
                <label for="price">Price (IRR)</label>
                <input name="price" id="price" type="number" value="'. $price .'" size="40" aria-required="true">
                <p>The product price in Iranian Rial.</p>
            </div>
            <div class="form-field form-required term-name-wrap">
                <label for="qty">Quantity</label>
                <input name="qty" id="qty" type="number" value="'. $qty .'" size="40" aria-required="true">
                <p>The quantity of this product in selected inventory</p>
            </div>
            <div class="form-field form-required term-name-wrap">
                <label for="stock_status">Stock Status</label>
                <select name="stock_status" id="stock_status">';

        foreach ($this->_getStockStatus() as $key => $stockStatus){
            if($key == $stock_status){
                $is_selected = ' selected';
            }else{
                $is_selected = '';
            }
            $ret .= '<option value="'. $key .'" '. $is_selected .'>'. $stockStatus .'</option>';
        }

            $ret .= '</select><p>Choose the stock status of this product</p>
            </div>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="' . $page_title . '">
                <span class="spinner"></span>
            </p></form></div></div>';

        return $ret;
    }

    public function product_entry_post_action($id = null)
    {
        global $wpdb;
        $table_name = 'sepidan_inventory_products';
        $inventory_id = $_POST['inventory_id'];

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE post_id = $id AND inventory_id = $inventory_id");

        if($total_items > 0){
            $query_text = sprintf("UPDATE $table_name SET qty = '%s', price = '%s',stock_status = '%s',updated_at = NOW() WHERE post_id = '%s' AND inventory_id = '%s'", $_POST['qty'],$_POST['price'],$_POST['stock_status'], $id, $inventory_id);
        }else{
            $query_text = sprintf("INSERT INTO $table_name (qty,price,stock_status,post_id,inventory_id,updated_at) values ('%s','%s','%s','%s','%s',NOW())",$_POST['qty'],$_POST['price'],$_POST['stock_status'], $id, $inventory_id);
        }
        $wpdb->query($query_text);
        return true;
    }
	
	public function product_inventories_table($product_id,$variant_id)
	{
		global $wpdb;
		$product_title = $this->_getProductName($product_id);
		
		// check whether this product has custom variant
		$total_variants = $wpdb->get_var("SELECT COUNT(id) FROM wp_posts WHERE post_type = 'product_variation' AND post_parent = $product_id");		
		
        $str = '<div class="wrap"><h1 class="wp-heading-inline">'. $product_title .'</h1>';
        if ($total_variants > 0){
			$str .= '<form method="get">
					<input type="hidden" name="page" value="product_stats_table_grid" />
					<input type="hidden" name="product_id" value="'.$product_id.'" />
					<select name="variant_id" onchange="this.form.submit()"><option>_______</option>';
			$variant_array = [];
			$variant_results = $wpdb->get_results($wpdb->prepare("SELECT ID,post_title FROM wp_posts WHERE post_type = 'product_variation' AND post_parent = $product_id"), ARRAY_A);
			foreach ($variant_results as $singleVariant){
				$variant_array[$singleVariant['ID']] = $singleVariant['post_title'];
				if ($singleVariant['ID'] == $variant_id){
					$select_variant = "selected";
				} else {
					$select_variant = "";
				}
				$str .= "<option value='".$singleVariant['ID'] ."' ".$select_variant.">".$singleVariant['post_title']."</option>";
			}
			$str .= '</select></form><br />';
		}
		
		$str .= '<table class="wp-list-table widefat fixed striped table-view-list productsstats">
				<thead><tr>
					<th scope="col" id="inventory_id" class="manage-column column-inventory_id">Inventory</th>
					<th scope="col" id="price" class="manage-column column-price">Price</th>
					<th scope="col" id="qty" class="manage-column column-qty">Qty</th>
					<th scope="col" id="stock_status" class="manage-column column-stock_status">Stock Status</th>
					<th scope="col" id="stock_status" class="manage-column column-last_modified">Last Modified</th>
					<th scope="col" id="updated_at" class="manage-column column-updated_at">Action</th>
						</tr></thead><tbody id="the-list" data-wp-lists="list:productstats">';
		
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM sepidan_inventory"), ARRAY_A);

        foreach ($results as $result){
			$inventory_id = $result['id'];
            $inventory_title = $result['title'];
            
            if ($total_variants > 0){
				$inventory_products = $wpdb->get_row("SELECT * FROM `sepidan_inventory_products` WHERE `inventory_id` = $inventory_id AND `post_id` = $product_id AND `variant_id` = $variant_id", ARRAY_A);
			} else {
				$inventory_products = $wpdb->get_row("SELECT * FROM `sepidan_inventory_products` WHERE `inventory_id` = $inventory_id AND `post_id` = $product_id AND `variant_id` IS NULL", ARRAY_A);
			}
            
            if ($inventory_products != null){
				$price = $inventory_products['price'];
				$qty = $inventory_products['qty'];
				$stock_status_submited = $inventory_products['stock_status'];
				$updated_at = $inventory_products['updated_at'];
			}else{
				$price = '';
				$qty = '';
				$stock_status_submited = '';
				$updated_at = '';
			}
			
			$str .= '<tr>
					<form action="admin.php?page=product_stats_table_form" method="post">
						<td>'. $inventory_title .'</td>
						<td><input type="text" name="price" placeholder="Price" value="'.$price.'" /></td>
						<td><input type="text" name="qty" placeholder="Qty" value="'.$qty.'" /></td>
		                <td><select name="stock_status" id="stock_status">';

		                foreach ($this->_getStockStatus() as $key => $stockStatus){
		                	if($key == $stock_status_submited){
								$is_selected = ' selected';
							}else{
								$is_selected = '';
		            		}
							$str .= '<option value="'. $key .'" '. $is_selected .'>'. $stockStatus .'</option>';
		        		}

							$str .= '</select>
						</td>
						<td>'. $updated_at .'</td>
						<td>
							<input type="hidden" name="inventory_id" value="' . $inventory_id . '" />
							<input type="hidden" name="product_id" value="' . $product_id . '" />
							<input type="hidden" name="variant_id" value="' . $variant_id . '" />
							<input type="submit" value="submit" />
						</td>
					</form>
				</tr>';
			
			
        }
		
		$str .= '</tbody><tfoot><tr>
					<th scope="col" id="inventory_id" class="manage-column column-inventory_id">Inventory</th>
					<th scope="col" id="price" class="manage-column column-price">Price</th>
					<th scope="col" id="qty" class="manage-column column-qty">Qty</th>
					<th scope="col" id="stock_status" class="manage-column column-stock_status">Stock Status</th>
					<th scope="col" id="last_modified" class="manage-column column-last_modified">Last Modified</th>
					<th scope="col" id="updated_at" class="manage-column column-updated_at">Action</th>
						</tr></tfoot></table>';
		$str .= '</div>';

		echo $str;
	}
}
