<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sepidan-custom-inventory.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-sepidan-vendor-commission.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-sepidan-product-stats.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-sepidan-comments.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://davarzani.com
 * @since      1.0.0
 *
 * @package    Sepidaninventory
 * @subpackage Sepidaninventory/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sepidaninventory
 * @subpackage Sepidaninventory/admin
 * @author     Shayan Davarzani <info@shayand.com>
 */
class Sepidaninventory_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sepidaninventory_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sepidaninventory_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sepidaninventory-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sepidaninventory_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sepidaninvdatabase error Incorrect table nameentory_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sepidaninventory-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function create_menu()
    {
        add_menu_page(
            __( '3030tv Tech', 'textdomain' ),
            '3030tv Tech',
            'manage_options',
            'sepidaninventories',
            [$this,'inventory_grid'],
            'dashicons-admin-multisite',
            6
        );
        add_submenu_page(
            'sepidaninventories',
            __('Inventory Management', 'textdomain'),
            __('Inventory Management', 'textdomain'),
            'manage_options',
            'sepidaninventories',
            [$this,'inventory_grid']
        );
        add_submenu_page(
            'sepidaninventories',
            __('Add / Edit Inventory', 'textdomain'),
            __('Add / Edit new Inventory', 'textdomain'),
            'manage_options',
            'sepidaninventories_form',
            [$this,'inventory_form']
        );
        add_submenu_page(
            'sepidaninventories',
            __('Vendors Commission', 'textdomain'),
            __('Vendors Commission', 'textdomain'),
            'manage_options',
            'sepidan_vendors_commission',
            [$this,'vendor_commission_grid']
        );
        add_submenu_page(
            'sepidaninventories',
            __('Vendors Modify Commission', 'textdomain'),
            __('Vendors Modify Commission', 'textdomain'),
            'manage_options',
            'sepidan_vendors_commission_form',
            [$this,'vendor_commission_form']
    	);
	add_submenu_page(
            'sepidaninventories',
            __('Vendors Delete Commission', 'textdomain'),
            __('Vendors Delete Commission', 'textdomain'),
            'manage_options',
            'sepidan_vendors_commission_delete',
            [$this,'vendor_commission_delete']
        );

        add_submenu_page(
            'sepidaninventories',
            __('Product Stats', 'textdomain'),
            __('Product Stats', 'textdomain'),
            'manage_options',
            'sepidan_product_stats',
            [$this,'product_stats_grid']
        );
        add_submenu_page(
            'sepidaninventories',
            __('Product Modify Stats', 'textdomain'),
            __('Product Modify Stats', 'textdomain'),
            'manage_options',
            'sepidan_product_stats_form',
            [$this,'product_stats_form']
        );
        add_submenu_page(
            'sepidaninventories',
            __('Product Stats Table', 'textdomain'),
            __('Product Stats Table', 'textdomain'),
            'manage_options',
            'product_stats_table_grid',
            [$this,'product_stats_table_grid']
        );
        add_submenu_page(
            'sepidaninventories',
            __('Product Stats Table Action', 'textdomain'),
            __('Product Stats Table Action', 'textdomain'),
            'manage_options',
            'product_stats_table_form',
            [$this,'product_stats_table_form']
        );
        add_submenu_page(
            'sepidaninventories',
            __('Calculate Prices', 'textdomain'),
            __('Calculate Prices', 'textdomain'),
            'manage_options',
            'product_calculate_price',
            [$this,'product_calculate_price']
        );
	}

    public function inventory_grid()
    {
        $class = new Sepidan_Custom_Inventory();
        echo '<div class="wrap"><h1 class="wp-heading-inline">3030tv inventories</h1><a href="?page=sepidaninventories_form" class="page-title-action">Add New</a>';
        $class->prepare_items();
        $class->display();
        echo '</div>';
	}

    public function inventory_form()
    {
        $class = new Sepidan_Custom_Inventory();

        $id = null;
        if (isset($_GET['id'])){
            $id = $_GET['id'];
        }

        if(isset($_POST['title'])){
            $ret = $class->inventory_entry_post_action($id);
            if ($ret){
                wp_redirect('?page=sepidaninventories');
            }
        }
        echo $class->inventory_entry($id);
    }

    public function vendor_commission_grid()
    {	   
		$class = new Sepidan_Vendor_Commission();
		
        echo '<div class="wrap"><h1 class="wp-heading-inline">3030tv vendors</h1>';
        $class->prepare_items();
        $class->display();
        echo '</div>';
    }

    public function vendor_commission_form()
    {
        $class = new Sepidan_Vendor_Commission();

        $id = null;
        if (isset($_GET['brand_id'])){
            $id = $_GET['brand_id'];
        }
        
        $producer_id = null;
        if (isset($_GET['producer_id'])){
            $producer_id = $_GET['producer_id'];
        }

        if(isset($_POST['commission'])){
            $ret = $class->commission_entry_post_action($id,$producer_id);
            if ($ret){
                wp_redirect('?page=sepidan_vendors_commission');
            }
        }
        echo $class->commission_entry($id,$producer_id);
    }

    public function vendor_commission_delete()
    {
        $class = new Sepidan_Vendor_Commission();

        $id = null;
        if (isset($_GET['brand_id'])){
            $id = $_GET['brand_id'];
        }
        
        $producer_id = null;
        if (isset($_GET['producer_id'])){
            $producer_id = $_GET['producer_id'];
        }

        $ret = $class->commission_entry_post_delete($id,$producer_id);
        if ($ret){
                wp_redirect('?page=sepidan_vendors_commission');
        }
    }
    
    public function product_calculate_price()
    {
        $class = new Sepidan_Vendor_Commission();
        $ret = $class->commission_calculator();
        if ($ret){
                wp_redirect('?page=sepidan_vendors_commission');
        }
    }


    public function product_stats_grid()
    {
        $class = new Sepidan_Product_Stats();
        echo '<div class="wrap"><h1 class="wp-heading-inline">3030tv product stats</h1>';
        $class->prepare_items();
        $class->display();
        echo '</div>';
    }

    public function product_stats_form()
    {
        $class = new Sepidan_Product_Stats();

        $id = null;
        if (isset($_GET['product_id'])){
            $id = $_GET['product_id'];
        }

        $inventory_id = null;
        if (isset($_GET['inventory_id'])){
            $inventory_id = $_GET['inventory_id'];
        }

        if(isset($_POST['qty'])){
            $ret = $class->product_entry_post_action($id);
            if ($ret){
                wp_redirect('?page=sepidan_product_stats');
            }
        }
        echo $class->product_entry($id,$inventory_id);
    }

    public function product_stats_table_grid()
    {
        $class = new Sepidan_Product_Stats();
        $id = null;
        $variant = null;
        
        if (isset($_GET['product_id'])){
            $id = $_GET['product_id'];
        }
        if (isset($_GET['variant_id'])){
            $variant = $_GET['variant_id'];
        }
        $class->product_inventories_table($id,$variant);
    }

    public function product_stats_table_form()
    {
        global $wpdb;
        
        $inventory_id = $_POST['inventory_id'];
        $product_id = $_POST['product_id'];
        $variant_id = $_POST['variant_id'];
        $stock_status = $_POST['stock_status'];
        $qty = $_POST['qty'];
        $price = $_POST['price'];
        
        if ($variant_id == null){
			$total_items = $wpdb->get_var("SELECT COUNT(id) FROM sepidan_inventory_products WHERE post_id = $product_id AND inventory_id = $inventory_id AND variant_id IS NULL");

			if($total_items > 0){
				$query_text = sprintf("UPDATE sepidan_inventory_products SET qty = '%s', price = '%s',stock_status = '%s',updated_at = NOW() WHERE post_id = '%s' AND inventory_id = '%s'", $qty,$price,$stock_status, $product_id, $inventory_id);
			}else{
				$query_text = sprintf("INSERT INTO sepidan_inventory_products (qty,price,stock_status,post_id,inventory_id,updated_at) values ('%s','%s','%s','%s','%s',NOW())",$qty,$price,$stock_status, $product_id, $inventory_id);
			}
		} else {
			$total_items = $wpdb->get_var("SELECT COUNT(id) FROM sepidan_inventory_products WHERE post_id = $product_id AND inventory_id = $inventory_id AND variant_id = $variant_id");

			if($total_items > 0){
				$query_text = sprintf("UPDATE sepidan_inventory_products SET qty = '%s', price = '%s',stock_status = '%s',updated_at = NOW() WHERE post_id = '%s' AND inventory_id = '%s AND variant_id = %s'", $qty,$price,$stock_status, $product_id, $inventory_id,$variant_id);
			}else{
				$query_text = sprintf("INSERT INTO sepidan_inventory_products (qty,price,stock_status,post_id,inventory_id,updated_at,variant_id) values ('%s','%s','%s','%s','%s',NOW(),'%s')",$qty,$price,$stock_status, $product_id, $inventory_id,$variant_id);
			}
		}
        $wpdb->query($query_text);
        if ($variant_id == null) {
			$redirect_url = '?page=product_stats_table_grid&product_id='. $product_id;
		} else {
			$redirect_url = '?page=product_stats_table_grid&product_id='. $product_id . '&variant_id=' . $variant_id;
		}
		wp_redirect( $redirect_url );
		
    }
}
