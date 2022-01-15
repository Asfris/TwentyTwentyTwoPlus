<?php

/**
    * Plugin Name: TwentyTwentyTwoPlus
    * Author: The Resasadr dev team
    * Description: TwentyTwentyTwoPlus plugin
    * Version: 0.1.5
    * Requires at least: 5.8
    * License: GNU General Public License v3.0
    * License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/


declare(strict_types=1);

include 'php/version.php';
include 'php/db.php';
include 'php/update.php';

// Blocks
include 'switch/switch.php';

// Get plugin dir url
$dir = plugin_dir_url(__FILE__);

const DB_TABLE = "tttp";
const DB_TABLE_PARAMS = ["checkSecondMenu", "darkMode", "postImage"];

$db = new Database\Db(DB_TABLE);
$update = new Update\Update(PLUGIN_VERSION, "Asfris", "TwentyTwentyTwoPlus");

/**
 * Register Menu
 * @return void
 * @since 0.0.1
 */
function register_menu(): void {
    /**
     * Code refrence: https://developer.wordpress.org/reference/functions/add_menu_page/
     */
    add_menu_page( 'Twenty Twenty Plus',
        'TwTP',
        'manage_options',
        'tttp',
        'menu_page',
        '',
        90
    );
}
add_action( 'admin_menu', 'register_menu' );

/**
 * Menu page
 * include Menu file to show plugin panel page
 * @return void
 */
function menu_page(): void {
    $file_name = "php/menu.php";
    include_once plugin_dir_path( __FILE__ ) . $file_name;
}

/**
 * Enject style
 * Enqueue Css to theme
 * @param string $style_name
 * @param string $css_file_path
 * @return void
 * @since 0.0.1
 */
function inject_style(string $style_name, string $css_file_path): void {
    wp_enqueue_style( $style_name, $css_file_path );
}

/**
 * When plugin activated,
 * This function runs.
 * @since 0.0.1
 */
function main() {
    global $dir;

    // Create table in database
	Database\Db::create_table(DB_TABLE,
		"
            id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
			param VARCHAR(100) NOT NULL,
			value VARCHAR(200)
		"
	);

    // Inject css to theme
    inject_style('TwentyTwentyTwoPlusStyle', $dir . 'dist/main.bundle.css');
}

// Runs event enject action
add_action( 'wp_enqueue_scripts', 'main' );

// Plugin activated event
register_activation_hook( __FILE__, 'main' );

/**
 * Returns current version of plugin
 * @return string
 * @since 0.0.1
 */
function get_plugin_version(): string {
    return PLUGIN_VERSION;
}

/**
 * Check if dark mode enabled. then inject css
 * @since 0.0.2
 * @return void
 */
function dark_theme(): void {
    global $dir, $db;

    $result = $db->get("SELECT value FROM wp_" . DB_TABLE . " WHERE param='darkMode'");
    if ($result) {
        // Inject dark theme css
        if ($result[0]->value == "1") {
            inject_style("TTTPDark", $dir . "css/dark-theme.css");
        }
    }
}
add_action( 'wp_enqueue_scripts', 'dark_theme' );

/**
 * Check if postImage option enabled, then inject css
 * @since 0.1.1
 * @return void
 */
function posts_has_image(): void {
    global $dir, $db;

    $result = $db->get("SELECT value FROM wp_" . DB_TABLE . " WHERE param='postImage'");
    if ($result) {
        // Inject post_has_image style
        if ($result[0]->value == "1") {
            inject_style("TTTPPostImage", $dir . "css/posts-has-image.css");
        }
    }
}
add_action( 'wp_enqueue_scripts', 'posts_has_image' );

/**
 * Inject switch script to theme
 * @since 0.1.3
 * @return void
 */
function inject_switch_script(): void {
    global $dir;
    
    wp_enqueue_script('script', $dir . 'dist/bundle.js');
}
add_action('wp_enqueue_scripts', 'inject_switch_script');

?>
