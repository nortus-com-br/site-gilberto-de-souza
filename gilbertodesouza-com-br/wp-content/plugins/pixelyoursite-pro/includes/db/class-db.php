<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
require 'abstract-db-table.php';
require 'abstract-stat-value-table.php';
require 'class-stat-orders-table.php';
require 'class-stat-landing-table.php';
require 'class-stat-trafic-table.php';
require 'class-stat-utm_medium-table.php';
require 'class-stat-utm_source-table.php';
require 'class-stat-utm_term-table.php';
require 'class-stat-utm_campaing-table.php';
require 'class-stat-utm_content-table.php';

class DataBaseManager {

    private $db_version = "1.0.5";

    /**
     * @return DataBaseTable[]
     */
    function get_tables() {
        return apply_filters("pys_db_tables",[]);
    }

    function create_table() {
        $current = get_option("pys_db_version","0.0.0");

        if(version_compare($current, "1.0.4", '<=')) {

            $this->drop_tables();
            $current = "0.0.0";
            update_option(OrderStatistics::$woo_stat_order_imported_page, 1);
            update_option(OrderStatistics::$db_sync_status,OrderStatistics::$SYNC_STATUS_START);
        }
        if(version_compare($current, "0.0.0", '==')) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $allCreated = true;
            $tables = $this->get_tables();
            foreach ($tables as $table) {
                $status = $table->create();
                if(!$status) {
                    $allCreated = false;
                    error_log("Error create table ".$table->getName()." sql ".$table->getCreateSql());
                }
            }

            if($allCreated) {
                update_option("pys_db_version",$this->db_version);
            }
        }
    }

    public  function drop_tables() {
        global $wpdb;

        $tables = $this->get_tables();

        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$table->getName()}" );
        }
    }



}