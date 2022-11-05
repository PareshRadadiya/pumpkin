<?php
/**
 * Plugin Name: Pumkin
 * Description: WordPress Pumkin.
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Paresh Radadiya
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain: pumkin
 */

// Useful global constants.
define( 'PUMKIN_VERSION', '0.1.0' );
define( 'PUMKIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PUMKIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PUMKIN_INC', PUMKIN_PATH . 'includes/' );

// Include files.
require_once PUMKIN_INC . 'admin.php';
require_once PUMKIN_INC . 'frontend.php';

// Bootstrap.
Pumkin\Admin\setup();
Pumkin\Frontend\setup();
