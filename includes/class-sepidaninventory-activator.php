<?php

/**
 * Fired during plugin activation
 *
 * @link       http://davarzani.com
 * @since      1.0.0
 *
 * @package    Sepidaninventory
 * @subpackage Sepidaninventory/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sepidaninventory
 * @subpackage Sepidaninventory/includes
 * @author     Shayan Davarzani <info@shayand.com>
 */
class Sepidaninventory_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        global $table_prefix, $wpdb;
        $tblname = 'sepidan_inventory';

        if($wpdb->get_var( "show tables like '$tblname'" ) != $tblname)
        {

            $sql = "CREATE TABLE `". $tblname . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
            $sql .= "  `title`  varchar(256)   NOT NULL, ";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }

        $tblname2 = 'sepidan_inventory_products';

        if($wpdb->get_var( "show tables like '$tblname2'" ) != $tblname2)
        {

            $sql = "CREATE TABLE `". $tblname2 . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
            $sql .= "  `inventory_id`  int(11)   NOT NULL, ";
            $sql .= "  `post_id`  int(11)   NOT NULL, ";
            $sql .= "  `price`  bigint   NOT NULL, ";
            $sql .= "  `qty`  int(11)   default 0, ";
            $sql .= "  `stock_status`  smallint   default 1, ";
            $sql .= "  `updated_at`  datetime   NOT NULL, ";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }

        $tblname3 = 'sepidan_inventory_products_message';

        if($wpdb->get_var( "show tables like '$tblname3'" ) != $tblname3)
        {

            $sql = "CREATE TABLE `". $tblname3 . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
            $sql .= "  `product_id`  int(11)   NOT NULL, ";
            $sql .= "  `user_id`  int(11)   NOT NULL, ";
            $sql .= "  `message`  TEXT   NULL, ";
            $sql .= "  `created_at`  datetime   NOT NULL, ";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }

        $tblname4 = 'sepidan_brand_commission';

        if($wpdb->get_var( "show tables like '$tblname4'" ) != $tblname4)
        {

            $sql = "CREATE TABLE `". $tblname4 . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
            $sql .= "  `brand_id`  int(11)   NOT NULL, ";
            $sql .= "  `commission`  int(11)   NOT NULL, ";
            $sql .= "  `created_at`  datetime   NOT NULL, ";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }
	}

}
