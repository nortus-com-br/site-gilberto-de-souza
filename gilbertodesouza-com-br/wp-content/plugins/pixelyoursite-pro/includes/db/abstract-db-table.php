<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

abstract class DataBaseTable {

    /**
     * @var \wpdb $wpdb
     */
    protected $wpdb;

    public function __construct($wpdb){
        $this->wpdb = $wpdb;
    }

    /**
     * Return table name
     * @return String
     */
    abstract function getName();

    /**
     * Get SQL statement to create table.
     * @return String
     */
    abstract function getCreateSql();

    /**
     * Create table
     * @return bool True on success or if the table already exists. False on failure.
     */
    function create() {
        $main_sql_create = $this->getCreateSql();
        return maybe_create_table( $this->getName(), $main_sql_create );

//        if(!$status) {
//            error_log("Error create {$this->getName()} {$this->wpdb->last_error}");
//        }
    }

    /**
     * Get row count
     * @return int
     */
    function getRowCount() {
        return intval($this->wpdb->get_var("SELECT count(*) FROM {$this->getName()}"));
    }

    function clear() {
        $this->wpdb->query("DELETE FROM {$this->getName()}");
    }

    function delete() {
        $this->wpdb->query( "DROP TABLE IF EXISTS {$this->getName()}" );
    }
}
