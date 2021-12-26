<?php

namespace Database;

use ErrorHandle\Error;

interface Database
{
	public static function create_table(string $name, string $options): void;
	public function insert(array $data, array $format): void;
	public function replace(array $data, array $format): void;
	public function get(string $query);
	public function update(array $data, array $where): void;
}

/**
 * db
 */
class Db implements Database
{

	/**
	 * Table name for query
	 */
	private string $table_name;

	/**
	 * Wpdb object
	 */
	private $db;

	/**
	 * When object created
	 * @param string $table
	 * @since 0.1.0
	 */
	function __construct(string $table) {
		global $wpdb;

		$this->table_name = $table;
		$this->db = $wpdb;
	}

	/**
	 * Create a table
	 * @param string $name Name of table
	 * @param string $options Options of Table. for example -> name, age ...
	 * @since 0.0.1
	 */
	public static function create_table(string $name, string $options): void {
		// Use Wordpress db var as global
		// Code reference : https://developer.wordpress.org/reference/classes/wpdb/
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// Create table name
		$table_name = $wpdb->prefix . $name;
		
		// Set query
		$sql = "CREATE TABLE IF NOT EXISTS $table_name ( $options ) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        try
        {
            dbDelta($sql);
        }
        catch (Error $ex)
        {
            echo $ex->fullErrorMessage();
        }
	}

	/**
	 * Inserts to table
	 * @param array $data
	 * @param array $format
	 * @return void
	 * @since 0.0.1
	 * @since 0.1.0 Code fixed
     * @since 0.1.3 Error handle
	 */
	public function insert(array $data, array $format): void {
		// get table
		$table = $this->db->prefix . $this->table_name;

        try
        {
            // Insert to table
            $this->db->insert($table, $data, $format);
        }
		catch (Error $ex)
        {
            echo $ex->fullErrorMessage();
        }
	}

	/**
	 * Replace to table
	 * @param array $data data to replace
	 * @param array $format format of data
	 * @return void
	 * @since 0.0.1
	 * @since 0.1.0 Code fixed
     * @since 0.1.3 Error handle
	 */
	public function replace(array $data, array $format): void {
		// get table
		$table = $this->db->prefix . $this->table_name;

        try
        {
            // Replace data
            $this->db->replace($table, $data, $format);
        }
        catch (Error $ex)
        {
            echo $ex->fullErrorMessage();
        }
	}

	/**
	 * Get result from db
	 * @param string $query
	 * @return array|object|null
	 * @since 0.0.1
	 * @since 0.1.0 Code fixed
	 * @since 0.1.3 Removed Unnecessary variable
	 */
	public function get(string $query) {
		return $this->db->get_results($query);
	}

	/**
	 * Update data in table
	 * @param array $data
	 * @param array $where
	 * @since 0.0.2
	 * @since 0.1.0 Code fixed
     * @since 0.1.3 Error handle
	 * @return void
	 */
	public function update(array $data, array $where): void {
		// get table
		$table = $this->db->prefix . $this->table_name;

        try
        {
            // Update data
            $this->db->update($table, $data, $where);
        }
        catch (Error $ex)
        {
            echo $ex->fullErrorMessage();
        }
	}
}
