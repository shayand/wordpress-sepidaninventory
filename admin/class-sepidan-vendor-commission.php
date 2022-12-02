<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Sepidan_Vendor_Commission extends WP_List_Table{
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'sepidancommission',
            'plural'   => 'sepidancommissions',
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_brand_id($item)
	{
        global $wpdb;
        $term_query = $wpdb->get_row(sprintf("SELECT * FROM wp_terms WHERE term_id = %s",$item['brand_id']), ARRAY_A);

        $link = sprintf('?page=sepidan_vendors_commission_form&brand_id=%s',$item['brand_id']);
        if($item['term_taxonomy_id'] != null){
		$link .= '&producer_id='.$item['term_taxonomy_id'];
	}

        if($term_query != null){
            $title = $term_query['name'];
            
        }else{
            $title = $item['brand_id'];
        }
        
        $actions = array(
            'edit' => sprintf('<a href="%s">%s</a>', $link, __('Edit', 'wpbc')),
        );
	if($item['term_taxonomy_id'] != null){
                $deleteLink = '?page=sepidan_vendors_commission_delete&brand_id='.$item['brand_id'].'&producer_id='.$item['term_taxonomy_id'];
		$actions['delete'] = sprintf('<a href="%s">%s</a>',$deleteLink,__('Delete','wpbc'));
	}

        return sprintf('%s %s',
            $title,
            $this->row_actions($actions)
        );
    }
    
    function column_producer_id($item)
	{
        global $wpdb;
        $term_query = $wpdb->get_row(sprintf("SELECT wp_term_taxonomy.term_id,wp_terms.name FROM wp_term_taxonomy JOIN wp_terms ON (wp_terms.term_id = wp_term_taxonomy.term_id) WHERE wp_term_taxonomy.term_taxonomy_id = %s",$item['term_taxonomy_id']), ARRAY_A);

        if($term_query != null){
            return $term_query['name'];
        }
        
        return '';
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
            'brand_id'      => __('Vendor', 'wpbc'),
            'producer_id'      => __('Producer', 'wpbc'),
            'commission'      => __('User Commission', 'wpbc'),
            'commission_vendor'      => __('Vendor Commission', 'wpbc'),
            'created_at'      => __('Created At', 'wpbc'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'brand_id'      => array('brand_id', true),
            'producer_id'      => array('producer_id', true),
        );
        return $sortable_columns;
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = 'sepidan_brand_commission';

        $per_page = 50;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';

        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    public function commission_entry($brand_id = null,$producer_id = null)
    {
        global $wpdb;
        
        $table_name = 'sepidan_brand_commission';
        $brand_title = $brand_id;
        $commission = 0;
        $page_title = 'Modify Commission';
        $query_string = '?page=sepidan_vendors_commission_form&brand_id='.$brand_id;
        if ($producer_id != null){
			$query_string .= '&producer_id=' . $producer_id;
		}
        
        $name_query = $wpdb->get_row(sprintf("SELECT * FROM wp_terms WHERE term_id = %s",$brand_id), ARRAY_A);
        if($name_query != null){
            $brand_title = $name_query['name'];
        }else{
            $brand_title = $brand_id;
        }
        
        $term_query = $wpdb->get_results("SELECT wp_term_taxonomy.*,wp_terms.name,wp_terms.slug FROM wp_term_taxonomy JOIN wp_terms ON (wp_terms.term_id = wp_term_taxonomy.term_id) WHERE wp_term_taxonomy.taxonomy = 'pa_brand'",ARRAY_A);
		
		$select_txt = '<select name="producer">';
		foreach ($term_query as $signle_term){
			$single = $signle_term;
			if ($producer_id != null && $single['term_taxonomy_id'] == $producer_id){
				$select_txt .= '<option value="'.$single['term_taxonomy_id'].'" selected="selected">'.$single['name'].'</option>';
			}else{
				$select_txt .= '<option value="'.$single['term_taxonomy_id'].'">'.$single['name'].'</option>';
			}
		}
		$select_txt .= '</select>';
		
		if ($producer_id != null){
			$commission_entry = $wpdb->get_row("SELECT * FROM $table_name WHERE brand_id = $brand_id AND term_taxonomy_id = $producer_id", ARRAY_A);
		} else {
			$commission_entry = $wpdb->get_row("SELECT * FROM $table_name WHERE brand_id = $brand_id", ARRAY_A);
		}
        if($commission_entry != null){
            $commission = $commission_entry['commission'];
            $commission_vendor = $commission_entry['commission_vendor'];
            $producer = $commission_entry['commission_vendor'];
        }

        $ret = '<div class="wrap"><div class="form-wrap">
            <h2>' . $page_title .'</h2>
            <form id="addtag" method="post" action="'. $query_string .'">
            <div class="form-field form-required term-name-wrap">
                <label for="tag-name">Vendor name</label>
                <input type="hidden" name="brand_id" value="'.$brand_id.'">
                <input name="brand_title" readonly="readonly" id="tag-name" type="text" value="'.$brand_title.'" size="40" aria-required="true">
                <p>The vendor name.</p>
            </div>
            <div class="form-field form-required term-name-wrap">
                <label for="tag-name">Producer</label>
                '.$select_txt.'
                <p>The producer name.</p>
            </div>
            <div class="form-field form-required term-name-wrap">
                <label for="commission">User commission amount</label>
                <input name="commission" id="commission" type="number" step="0.01" value="'.$commission.'" size="40" aria-required="true">
                <p>The Commission amount. (positive / negative)</p>
            </div>
            <div class="form-field form-required term-name-wrap">
                <label for="commission">Vendor commission amount</label>
                <input name="commission_vendor" id="commission_vendor" type="number" step="0.01" value="'.$commission_vendor.'" size="40" aria-required="true">
                <p>The Commission amount. (positive / negative)</p>
            </div>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="' . $page_title . '">		<span class="spinner"></span>
            </p>
            </form>
            </div>
        </div>';

        return $ret;
    }

    public function commission_entry_post_action($id = null,$producer_id = null)
    {
        global $wpdb;
        $table_name = 'sepidan_brand_commission';
        //if($producer_id == null){
			$producer_id = $_POST['producer'];
		//}

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE brand_id = $id AND term_taxonomy_id = $producer_id");

        if($total_items > 0){
            $query_text = sprintf("UPDATE $table_name SET commission = '%s',commission_vendor = '%s',term_taxonomy_id = '%s' WHERE brand_id = '%s' AND term_taxonomy_id = '%s'", $_POST['commission'],$_POST['commission_vendor'],$producer_id, $id,$producer_id);
        }else{
            $query_text = sprintf("INSERT INTO $table_name (brand_id,commission,commission_vendor,created_at,term_taxonomy_id) values ('%s','%s','%s',NOW(),'%s')",$_POST['brand_id'],$_POST['commission'],$_POST['commission_vendor'],$producer_id);
        }
        $wpdb->query($query_text);
        return true;
    }

    public function commission_entry_post_delete($id = null,$producer_id = null)
    {
	    global $wpdb;
            $table_name = 'sepidan_brand_commission';
	    $delete_query = $wpdb->get_var("DELETE FROM $table_name WHERE brand_id = $id AND term_taxonomy_id = $producer_id");
	    $wpdb->query($delete_query);
	    return true;
    }

    public function commission_calculator(){
	    global $wpdb;
	    $select_com = $wpdb->get_results("SELECT * FROM sepidan_brand_commission",ARRAY_A);
	    foreach ($select_com as $single_com){
		    $term_id = $single_com['term_taxonomy_id'];
		    $selected_prod = $wpdb->get_results(sprintf("SELECT * FROM `wp_term_relationships` WHERE `term_taxonomy_id` = %s",$term_id),ARRAY_A);
		    $user_com = $single_com['commission'];
		    foreach ($selected_prod as $single_prod){
			    $product_id = $single_prod['object_id'];
			    // is product exists on sepidan_inventory_products
			    $total_pr = $wpdb->get_var("SELECT COUNT(`id`) FROM `sepidan_inventory_products` WHERE `post_id` = $product_id");
			    
			    if($total_pr > 0){
					
					// check whether product has variant
					$total_variant = $wpdb->get_var("SELECT COUNT(`id`) FROM `sepidan_inventory_products` WHERE `post_id` = $product_id AND `variant_id` IS NOT NULL");
					if ($total_variant > 0){
						echo 'variant parent product_id: ' . $product_id . "<br />";
						$variant_prod = $wpdb->get_results(sprintf("SELECT `variant_id` FROM `sepidan_inventory_products` WHERE `post_id` = %s GROUP BY `variant_id`",$product_id),ARRAY_A);			
						foreach ($variant_prod as $single_variant){
							echo 'variant child product_id: ' . $single_variant['variant_id'] . "<br />";
							$this->_single_commission_variant($product_id,$user_com,$single_variant['variant_id']);
						}
						
					} else {
						/**
						 * without variant
						 */
						 echo 'normal product_id: ' . $product_id . "<br />";
						 $this->_single_commission($product_id,$user_com);
					}
					
					echo 'end calculation of product_id: ' . $product_id . "<br />";
				}
		    }
	    }
    }
    
    public function _single_commission($product_id,$user_com){
		global $wpdb;
		$product_obj = new WC_Product( $product_id );
						
		$normal_product_min_price_q = $wpdb->get_row(sprintf("SELECT MIN(`price`) as min_price FROM `sepidan_inventory_products` WHERE `post_id` = %s AND stock_status = 1",$product_id), ARRAY_A);
		$normal_product_min_price = $normal_product_min_price_q['min_price'];
		echo 'inner -> product_id min price: ' . $normal_product_min_price . "<br />";
		
		$product_obj = new WC_Product( $product_id );
		$orig_price = $product_obj->get_price();
		
		$product_factory = new WC_Product_Factory();
		$product_factory_object = $product_factory->get_product( $product_id );
		
		if($orig_price == ''){
			echo 'inner -> has not original price: ' . "<br />";
			// has not original price
			$product_factory_object->set_price( $normal_product_min_price );
				
		}else{
			// has original price
			echo 'inner -> has original price: ' . "<br />";
			
			$new_price = $normal_product_min_price + (($normal_product_min_price * $user_com) / 100);
			$diff_price = abs($orig_price - $normal_product_min_price);
			echo 'inner -> diff_price: ' . $diff_price . "<br />";
			echo 'inner -> new_price: ' . $new_price . "<br />";
			if($diff_price > 20000){
				echo 'inner -> diff_price > 20000 so update regular price and sale price ' . "<br />";	 
				
				$product_factory_object->set_sale_price( $new_price );
			}else{
				echo 'inner -> diff_price < 20000 so update sale price ' . "<br />";	 
				
				$product_factory_object->set_price( $new_price );
				$product_factory_object->set_regular_price( $new_price );
				$product_factory_object->set_sale_price( null );
			}
		}
		
		$product_factory_object->save();
	}
	
	public function _single_commission_variant($product_id,$user_com,$variant_id){
		global $wpdb;
		$product_obj = wc_get_product( $product_id );
		
		foreach( $product_obj->get_available_variations() as $single_variant ){
			if ( $single_variant['variation_id'] == $variant_id ){				
				$normal_product_min_price_q = $wpdb->get_row(sprintf("SELECT MIN(`price`) as min_price FROM `sepidan_inventory_products` WHERE `post_id` = %s AND `variant_id` = %s AND stock_status = 1",$product_id,$variant_id), ARRAY_A);
				$normal_product_min_price = $normal_product_min_price_q['min_price'];
				echo 'inner -> product_id min price: ' . $normal_product_min_price . "<br />";
				
				$variable_product1= new WC_Product_Variation( $single_variant['variation_id'] );
				$orig_price = $variable_product1->regular_price ;
				
				if($orig_price == ''){
					echo 'inner -> has not original price:' . "<br />";
					// has not original price
					update_post_meta( $single_variant, '_regular_price', $normal_product_min_price );
					update_post_meta( $single_variant, '_price', $normal_product_min_price );
					wc_delete_product_transients( $single_variant );
						
				}else{
					// has original price
					echo 'inner -> has original price: ' . "<br />";
					
					$new_price = $normal_product_min_price + (($normal_product_min_price * $user_com) / 100);
					$diff_price = abs($orig_price - $normal_product_min_price);
					echo 'inner -> diff_price: ' . $diff_price . "<br />";
					echo 'inner -> new_price: ' . $new_price . "<br />";
					if($diff_price > 20000){
						echo 'inner -> diff_price > 20000 so update regular price and sale price ' . "<br />";	 
						
						$product_factory_object->set_sale_price( $new_price );
						update_post_meta( $single_variant, '_sale_price', $new_price );
						wc_delete_product_transients( $single_variant );
					}else{
						echo 'inner -> diff_price < 20000 so update sale price ' . "<br />";	 
						
						
						update_post_meta( $variation_id, '_regular_price', $new_price );
						update_post_meta( $variation_id, '_price', $new_price );
						wc_delete_product_transients( $variation_id );
					}
				}
			}
		}				
	}
}
