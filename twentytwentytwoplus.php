<?php

/**
    * Plugin Name: TwentyTwentyTwoPlus
    * Author: The Resasadr dev team
    * Description: TwentyTwentyTwoPlus plugin
    * Version: 0.0.1
    * Requires at least: 5.8
    * License: GNU General Public License v3.0
    * License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/


declare(strict_types=1);

include 'log.php';
include 'version.php';

// Get plugin dir url
$dir = plugin_dir_url(__FILE__);

// Create Log object
$log = new Log();

const INFO = "INFO";

/**
 * Register Menu
 */
function register_menu(): void {
    global $log;

    /**
     * Code refrence: https://developer.wordpress.org/reference/functions/add_menu_page/
     */
    add_menu_page( 'Twenty Twenty Plus', 
        'TwTP', 
        'manage_options', 
        'twenty-twenty-two-plus-menu', 
        'menu_page', 
        '', 
        90 
    );
    $log->push(INFO, "Menu created.");
}
add_action( 'admin_menu', 'register_menu' );

/**
 * Menu page
 * include Menu file to show plugin panel page
 */
function menu_page() {
    global $log;

    $file_name = "menu.php";
    include_once plugin_dir_path( __FILE__ ) . $file_name;

    $log->push(INFO, "File loaded -> " . $file_name);
}

/**
 * Enject style
 * Enqueue Css to theme
 * @since 0.0.1
 */
function enject_style(string $style_name, string $css_file_path): void {
    wp_enqueue_style( $style_name, $css_file_path );
}

/**
 * When plugin activated,
 * This function runs.
 */
function main() {
    global $dir;
    // Enject css to theme
    enject_style('TwentyTwentyTwoPlusStyle', $dir . 'css/style.css');
}

// Runs event enject action
add_action( 'wp_enqueue_scripts', 'main' );

$log->push(INFO, "Style injected.");

// Plugin activated event
register_activation_hook( __FILE__, 'main' );

/**
 * Returns array of logs
 */
function get_logs(): array {
    global $log;

    return $log->messages;
}

function get_plugin_version(): string {
    return plugin_version;
}

?>